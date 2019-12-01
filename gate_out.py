#!/usr/bin/env python3

import time
import socket
import logging
import requests
import sys

API_URL = 'http://localhost/api'
GATE = False
SETTING = False

def get_setting():
    try:
        r = requests.get(API_URL + '/setting', timeout=3)
    except Exception as e:
        logging.error('Failed to get setting ' + str(e))
        return False

    if r.status_code == 200:
        return r.json()

    return False

def get_gate():
    try:
        r = requests.get(API_URL + '/barrierGate/search', params={'jenis': 'OUT'}, timeout=3)
    except Exception as e:
        logging.error('Failed to get gate ' + str(e))
        return False

    if r.status_code == 200:
        return r.json()

    return False

def send_notification(message):
    notification = { 'barrier_gate_id': GATE['id'], 'message': message }
    try:
        requests.post(API_URL + '/notification', data=notification, timeout=3)
    except Exception as e:
        logging.error(GATE['nama'] + ' : Failed to send notification ' + str(e))
        return False

    return True

def notify_vehicle_detected():
    pass

def save_data(id, data):
    try:
        r = requests.put(API_URL + '/accessLog/' + str(id), data=data, timeout=3)
    except Exception as e:
        logging.info(GATE['nama'] + ' : Failed to save data ' + str(e))
        send_notification('Pengunjung di ' + GATE['nama'] + ' membutuhkan bantuan Anda (gagal menyimpan data)')
        return False

    return r.json()

def take_snapshot():
    if GATE['camera_status'] == 0:
        logging.info('Not taking snapshot. Camera not active')
        return ''

    try:
        r = requests.get(API_URL + '/barrierGate/takeSnapshot/' + str(GATE['id']))

        if r.status_code != 200:
            logging.error('Failed to take snapshot. Status code : ' + str(r.status_code))
            send_notification("Gagal mengambil snapshot di gate " + GATE['nama'] + ". Status Code : " + str(r.status_code))
            return ''

        respons = r.json()
        return respons['filename']
    except Exception as e:
        logging.error('Failed to take snapshot ' + str(e))
        send_notification("Gagal mengambil snapshot di gate " + GATE['nama'] + " (" + str(e) + ")")
        return ''

def check_card(nomor_kartu):
    payload = { 'nomor_kartu': nomor_kartu, 'status': 1 }
    try:
        r = requests.get(API_URL + '/user/search', params=payload, timeout=3)
    except Exception as e:
        logging.info('Failed to check card ' + str(e))
        return False

    if r.status_code == 200:
        return r.json()

    return False

def get_last_access(field, value):
    try:
        r = requests.get(API_URL + '/accessLog/search', params={field: value}, timeout=3)
    except Exception as e:
        logging.info('Failed to get last access log ' + str(e))
        return False

    if r.status_code == 200:
        return r.json()

    return False

def gate_out_thread():
    while True:
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
            s.settimeout(3)

            try:
                s.connect((GATE['controller_ip_address'], int(GATE['controller_port'])))
            except Exception as s:
                # kalau gagal konek ulang
                time.sleep(3)
                continue

            logging.info('CONNECTED')
            send_notification(GATE['nama'] + ' Terhubung')

            # loop buat detect loop 1
            while True:
                try:
                    s.sendall(b'\xa6STAT\xa9')
                    vehicle_detection = s.recv(64)
                except Exception as e:
                    logging.error('Failed to detect vehicle ' + str(e))
                    send_notification(GATE['nama'] + ' : Gagal deteksi kendaraan')
                    # keluar dari loop cek kendaraan untuk sambung ulang controller
                    break

                logging.debug('Detecting vehicle ' + str(vehicle_detection))

                if b'IN1ON' in vehicle_detection or b'STAT1' in vehicle_detection:
                    logging.info('Vehicle detected')
                    try:
                        logging.debug('Playing welcome')
                        time.sleep(.1)
                        s.sendall(b'\xa6MT00007\xa9')
                        # logging.debug(GATE['nama'] + ' : ' + str(s.recv(64)))
                    except Exception as e:
                        logging.error('Failed to play Selamat Datang ' + str(e))
                        send_notification(GATE['nama'] + ' : Gagal play Selamat Datang ')
                        # keluar dari loop cek kendaraan untuk sambung ulang controller
                        break
                else:
                    time.sleep(2)
                    continue

                reset = False
                error = False

                # LOOP untuk baca kartu / barcode
                while True:
                    try:
                        time.sleep(.1)
                        s.sendall(b'\xa6STAT\xa9')
                        r = s.recv(1024)
                    except Exception as e:
                        error = True
                        break

                    logging.debug('Detecting barcode or card ' + str(r))

                    # ada input wiegand (W = card, X = barcode)
                    if b'W' in r:
                        nomor_kartu = str(r).split('W')[1].split('\\xa9')[0]
                        staff = check_card(str(int(nomor_kartu, 16)))
                        time.sleep(.1)

                        if not staff:
                            try:
                                s.sendall(b'\xa6MT00003\xa9')
                            except Exception as e:
                                logging.error('Failed to respon invalid card ' + str(e))
                                send_notification(GATE['nama'] + ' : Gagal merespon kartu invalid')
                                error = True
                                break

                            continue

                        if staff['expired']:
                            try:
                                s.sendall(b'\xa6MT00013\xa9')
                            except Exception as e:
                                logging.error('Failed to respon card expired ' + str(e))
                                send_notification(GATE['nama'] + ' : Gagal merespon kartu expired')
                                error = True
                                break

                            continue

                        if not staff['expired'] and staff['expired_in'] == 5:
                            try:
                                s.sendall(b'\xa6MT00011\xa9')
                                time.sleep(6)
                            except Exception as e:
                                logging.error('Failed to respon card expired in 5 days ' + str(e))
                                send_notification(GATE['nama'] + ' : Gagal merespon kartu expired dalam 5 hari')
                                error = True
                                break

                        if not staff['expired'] and staff['expired_in'] == 1:
                            try:
                                s.sendall(b'\xa6MT00012\xa9')
                                time.sleep(6)
                            except Exception as e:
                                logging.error('Failed to respon card expired in 1 day ' + str(e))
                                send_notification(GATE['nama'] + ' : Gagal merespon kartu expired dalam 1 hari')
                                error = True
                                break

                        logging.info('Card detected :' + staff['nomor_kartu'])
                        # cari data transaksi terakhir, kalau ada nanti buat update, kalau gak ada create baru di backend
                        access_log = get_last_access('nomor_kartu', staff['nomor_kartu'])

                        # kemungkinan kecil bisa terjadi. balik loop cek kartu/tiket lagi
                        if not access_log:
                            continue

                        break

                    elif b'X' in r:
                        nomor_barcode = str(r).split('X')[1].split('\\xa9')[0]
                        access_log = get_last_access('nomor_barcode', str(int(nomor_barcode, 16)))
                        logging.info('Barcode detected' + str(int(nomor_barcode, 16)))

                        if not access_log:
                            # Play tiket invalid
                            # TODO: sesuaikan audio-nya
                            try:
                                time.sleep(.1)
                                s.sendall(b'\xa6MT00012\xa9')
                                time.sleep(6)
                            except Exception as e:
                                logging.error('Failed to play barcode invalid ' + str(e))
                                send_notification(GATE['nama'] + 'Gagal play barcode invalid')
                                error = True
                                break

                            continue

                    elif b'IN3' in r:
                        logging.info('Reset')
                        reset = True
                        break

                    elif b'IN1OFF' in r:
                        logging.info('Vehicle turn back')
                        reset = True
                        break

                    elif b'IN4ON' in r:
                        reset = True
                        try:
                            time.sleep(.1)
                            s.sendall(b'\xa6MT00005\xa9')
                            time.sleep(10)
                        except Exception as e:
                            logging.error('Failed to respon help button ' + str(e))
                            send_notification(GATE['nama'] +'Gagal merespon tombol bantuan')
                            error = True
                            break

                        logging.info('Help button pressed')
                        send_notification(GATE['nama'] +'Pengunjung membutuhkan bantuan Anda')
                        break

                    else:
                        time.sleep(1)

                    # END LOOP cek kartu / barcode

                # kalau error keluar dari loop cek kendaraan biar sambung ulang controller
                if error:
                    break

                # kalau reset kembali ke loop cek kendaraan
                if reset:
                    continue

                data = {
                    'time_out': time.strftime('%Y-%m-%d %T'),
                    'snapshot_out': take_snapshot()
                }

                save_data(access_log['id'], data)

                # buka gate sesuai setingan
                if (access_log['is_staff'] == 1 and SETTING['staff_buka_otomatis'] == 1) or (access_log['is_staff'] == 0 and SETTING['pengunjung_buka_otomatis'] == 1):
                    try:
                        s.sendall(b'\xa6TRIG1\xa9')
                    except Exception as e:
                        logging.error('Failed to open gate ' + str(e))
                        send_notification(GATE['nama'] + 'Gagal membuka gate')
                        # sambung ulang controller
                        break

                    logging.info('Gate Opened')

                # sensing INP2 (buka dari operator)
                else:
                    while True:
                        try:
                            s.sendall(b'\xa6STAT\xa9')
                            open_gate = s.recv(64)
                            logging.debug(str(open_gate))
                        except Exception as e:
                            logging.error('Failed to sense loop 2 ' + str(e))
                            send_notification(GATE['nama'] + ' : Gagal deteksi open manual')
                            break

                        if b'IN2ON' in open_gate:
                            try:
                                s.sendall(b'\xa6TRIG1\xa9')
                            except Exception as e:
                                logging.error('Failed to open gate ' + str(e))
                                send_notification(GATE['nama'] + 'Gagal membuka gate')
                                # sambung ulang controller
                            break

                        time.sleep(1)

                # wait until vehicle in
                counter = 0

                while True:
                    # 5x cek aja biar ga kelamaan
                    if counter > 10:
                        logging.info('Waiting too long')
                        break

                    counter += 1

                    try:
                        s.sendall(b'\xa6STAT\xa9')
                        vehicle_in = s.recv(64)
                        logging.debug(str(vehicle_in))
                    except Exception as e:
                        logging.error('Failed to sense loop 2 ' + str(e))
                        send_notification(GATE['nama'] + ' : Gagal deteksi kendaraan sudah keluar')
                        error = True
                        # break sensing loop 2
                        break

                    if b'IN3OFF' in vehicle_in:
                        logging.info('Vehicle in')
                        break

                    time.sleep(1)

                if error:
                    # break loop cek kendaraan, sambung ulang controller
                    break

def start_app():
    global SETTING
    global GATE

    SETTING = get_setting()

    if SETTING == False:
        logging.info('Location not set. Exit application.')
        sys.exit()

    logging.info('Location set: ' + SETTING['nama_lokasi'])

    GATE = get_gate()

    if GATE == False:
        logging.info('Gate not set. Exit application.')
        sys.exit()

    logging.info('Gate set : ' + GATE['nama'])
    logging.info('Starting application...')
    gate_out_thread()

if __name__ == "__main__":
    log_file = '/var/log/gate_out.log'
    logging.basicConfig(filename=log_file, filemode='a', level=logging.DEBUG, format='%(asctime)s - %(levelname)s - %(message)s')
    start_app()
