#!/usr/bin/env python3

import time
import asyncio
import websockets
import socket
import logging
import requests
import sys

API_URL = 'http://localhost/pln/api'
GATE = False

def send_notification(message):
    notification = { 'barrier_gate_id': GATE['id'], 'message': message }
    try:
        requests.post(API_URL + '/notification', data=notification, timeout=3)
    except Exception as e:
        logging.error(GATE['name'] + ' : Failed to send notification ' + str(e))
        return False

    return True

def save_data(id, data):
    try:
        r = requests.put(API_URL + '/accessLog/' + str(id), data=data, timeout=3)
    except Exception as e:
        logging.info(GATE['name'] + ' : Failed to save data ' + str(e))
        send_notification('Pengunjung di ' + GATE['name'] + ' membutuhkan bantuan Anda (gagal menyimpan data)')
        return False

    return r.json()

def take_snapshot():
    if GATE['camera_status'] == 0:
        return ''

    try:
        r = requests.get(API_URL + '/barrierGate/takeSnapshot/' + str(GATE['id']))
    except Exception as e:
        logging.error(GATE['name'] + ' : Failed to take snapshot ' + str(e))
        send_notification("Gagal mengambil snapshot di gate " + GATE['name'] + " (" + str(e) + ")")
        return ''

    respons = r.json()
    return respons['filename']

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
                        s.sendall(b'\xa6STAT\xa9')
                        r = s.recv(1024)
                    except Exception as e:
                        error = True
                        break

                    # ada input wiegand (w1 = card, w2 = barcode)
                    if b'W1' in r:
                        nomor_kartu = str(r).split('W1')[1].split('\\xa9')[0]
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

                    elif b'W2' in r:
                        # TODO: sesuaikan line ini
                        nomor_barcode = str(r).split('W2')[1].split('\\xa9')[0]
                        access_log = get_last_access('nomor_barcode', nomor_barcode)

                        if not access_log:
                            # Play tiket invalid
                            # TODO: sesuaikan command-nya
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

                # kalau staff langsung buka
                if access_log['is_staff'] == 1 or access_log['is_staff'] == 0:
                    try:
                        s.sendall(b'\xa6OPEN1\xa9')
                    except Exception as e:
                        logging.error('Failed to open gate ' + str(e))
                        send_notification(GATE['nama'] + 'Gagal membuka gate')
                        # sambung ulang controller
                        break

                    logging.info('Gate Opened')

                # if access_log['is_staff'] == 0:
                #     await websocket.send(saved_data)
                #     cmd = await websocket.recv()
                #     if cmd == 'open':
                #         try:
                #             s.sendall(b'\xa6OPEN1\xa9')
                #         except Exception as e:
                #             logging.error('Failed to open gate ' + str(e))
                #             send_notification(GATE['nama'] + 'Gagal membuka gate')
                #             # sambung ulang controller
                #             break

                #         logging.info('Gate Opened')

                # wait until vehicle in
                counter = 0

                while True:
                    # 5x cek aja biar ga kelamaan
                    if counter > 5:
                        logging.info('Waiting too long')
                        break

                    counter += 1

                    try:
                        s.sendall(b'\xa6STAT\xa9')
                        vehicle_in = s.recv(64)
                        logging.debug(str(vehicle_in))
                    except Exception as e:
                        logging.error('Failed to sense loop 2 ' + str(e))
                        send_notification(GATE['nama'] + ' : Gagal deteksi kendaraan sudah masuk')
                        error = True
                        # break sensing loop 2
                        break

                    if b'IN3OFF' in vehicle_in:
                        logging.info('Vehicle in')
                        break

                    time.sleep(3)

                if error:
                    # break loop cek kendaraan, sambung ulang controller
                    break

def start_app():
    global GATE

    try:
        r = requests.get(API_URL + '/barrierGate/search', params={'jenis': 'OUT'}, timeout=3)
    except Exception as e:
        logging.error('Failed to get gate ' + str(e))
        sys.exit()

    if r.status_code == 200:
        GATE = r.json()

    else:
        logging.error('Gate not set. Exit application.')
        sys.exit()

    logging.info('Gate set : ' + GATE['nama'])
    logging.info('Starting application...')
    gate_out_thread()

    # start_server = websockets.serve(ws, "127.0.0.1", 5678)
    # asyncio.get_event_loop().run_until_complete(start_server)
    # asyncio.get_event_loop().run_forever()

if __name__ == "__main__":
    log_file = '/var/log/gate_out.log'
    logging.basicConfig(filename=log_file, filemode='a', level=logging.DEBUG, format='%(asctime)s - %(levelname)s - %(message)s')
    start_app()

# buat test web socket
# python3 -m websockets wss://echo.websocket.org/
