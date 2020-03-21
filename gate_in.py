#!/usr/bin/env python3

import sys
import time
import socket
import random
import string
import datetime
import requests
import threading
from escpos.printer import Network
import os
import logging

API_URL = 'http://localhost/api'
SETTING = False
GATE = False


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
        r = requests.get(API_URL + '/barrierGate/search',
                         params={'jenis': 'IN'}, timeout=3)
    except Exception as e:
        logging.error('Failed to get gate ' + str(e))
        return False

    if r.status_code == 200:
        return r.json()

    return False


def take_snapshot(data):
    if GATE['camera_status'] == 0:
        logging.info('Not taking snapshot. Camera not active')
        filename = ''

    try:
        r = requests.get(
            API_URL + '/barrierGate/takeSnapshot/' + str(GATE['id']))
        if r.status_code == 200:
            respons = r.json()
            filename = respons['filename']
        else:
            logging.error(
                'Failed to take snapshot. Status code : ' + str(r.status_code))
            filename = ''
    except Exception as e:
        logging.error('Failed to take snapshot ' + str(e))
        send_notification("Gagal mengambil snapshot di gate " +
                          GATE['nama'] + " (" + str(e) + ")")
        filename = ''

    data['snapshot_in'] = filename
    save_data(data)


def generate_nomor_barcode():
    return ''.join([random.choice(string.digits) for n in range(5)])


def print_ticket_network(data):
    try:
        p = Network(GATE['printer_ip_address'])
    except Exception as e:
        logging.error('Failed to print ticket ' +
                      data['nomor_barcode'] + ' ' + str(e))
        send_notification(
            'Pengunjung di ' + GATE['nama'] + ' gagal print tiket. Informasikan nomor barcode kepada pengunjung. ' + data['nomor_barcode'])
        return

    try:
        p.set(align='center')
        p.text("TIKET PARKIR\n")
        p.set(height=2, align='center')
        p.text(SETTING['nama_lokasi'] + "\n\n")
        p.set(align='left')
        p.text('TANGGAL      : ' + datetime.datetime.strptime(
            data['time_in'][:10], '%Y-%m-%d').strftime('%d %b %Y') + "\n")
        p.text('JAM          : ' + data['time_in'][11:] + "\n\n")
        p.set(align='center')
        p.barcode(data['nomor_barcode'], 'CODE39', function_type='A',
                  height=100, width=4, pos='BELOW', align_ct=True)
        p.text("\n")
        p.text(SETTING['info_tambahan_tiket'])
        p.cut()
    except Exception as e:
        logging.error('Failed to print ticket ' +
                      data['nomor_barcode'] + ' ' + str(e))
        send_notification(
            'Pengunjung di ' + GATE['nama'] + ' gagal print tiket. Informasikan nomor barcode kepada pengunjung. ' + data['nomor_barcode'])
        return

    logging.info('Ticket printed ' + data['nomor_barcode'])


def print_ticket_serial(data, s):
    command = [
        '\xa6PR4',  # start print command, baudrate 9600
        '\x1b\x61\x01TIKET PARKIR\n',  # align center
        '\x1b\x21\x10' + SETTING['nama_lokasi'] + '\n\n',  # double height
        '\x1b\x21\x00',  # normal height
        '\x1b\x61\x00',  # align left
        'TANGGAL      : ' + \
        datetime.datetime.strptime(
            data['time_in'][:10], '%Y-%m-%d').strftime('%d %b %Y') + '\n',
        'JAM          : ' + data['time_in'][11:] + '\n\n',
        '\x1b\x61\x01',  # align center
        '\x1dhd',  # set barcode height = 100, GS h 100
        '\x1dH\x02',  # set barcode text = below, GS H 2
        '\x1dkE',  # GS k 69
        chr(len(data['nomor_barcode'])),  # barcode length
        data['nomor_barcode'],  # barcode content
        '\n' + SETTING['info_tambahan_tiket'] + '\n',
        '\x1d\x56A',  # full cut, add 3 lines: GS V 65
        '\xa9'  # end command
    ]

    try:
        s.sendall(str.encode(''.join(command)))
        logging.debug(str(s.recv(1024)))
    except Exception as e:
        logging.error('Failed to print ticket ' +
                      data['nomor_barcode'] + ' ' + str(e))
        send_notification(
            'Pengunjung di ' + GATE['nama'] + ' gagal print tiket. Informasikan nomor barcode kepada pengunjung. ' + data['nomor_barcode'])
        return

    logging.info('Ticket printed ' + data['nomor_barcode'])


def send_notification(message):
    notification = {'barrier_gate_id': GATE['id'], 'message': message}
    try:
        requests.post(API_URL + '/notification', data=notification, timeout=3)
    except Exception as e:
        logging.info('Failed to send notification ' + str(e))
        return False

    return True


def check_card(nomor_kartu):
    payload = {'nomor_kartu': nomor_kartu, 'status': 1}
    try:
        r = requests.get(API_URL + '/user/search', params=payload, timeout=3)
    except Exception as e:
        logging.info('Failed to check card ' + str(e))
        return False

    if r.status_code == 200:
        return r.json()

    return False


def save_data(data):
    logging.debug(str(data))
    try:
        r = requests.post(API_URL + '/accessLog', data=data, timeout=3)
    except Exception as e:
        logging.info('Failed to save data ' + str(e))
        send_notification(
            'Pengunjung di ' + GATE['nama'] + ' membutuhkan bantuan Anda (gagal menyimpan data)')
        return False

    return r.json()


def gate_in_thread():
    while True:
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
            s.settimeout(3)

            logging.info(
                'Connecting to ' + GATE['controller_ip_address'] + ':' + str(GATE['controller_port']))

            try:
                s.connect((GATE['controller_ip_address'],
                           GATE['controller_port']))
            except Exception:
                time.sleep(3)
                continue

            logging.info('CONNECTED')
            send_notification(GATE['nama'] + ' Terhubung')

            while True:
                try:
                    s.sendall(b'\xa6STAT\xa9')
                    vehicle_detection = s.recv(1024)
                except Exception as e:
                    logging.error('Failed to detect vehicle ' + str(e))
                    send_notification(
                        GATE['nama'] + ' : Gagal deteksi kendaraan')
                    # keluar dari loop cek kendaraan untuk sambung ulang controller
                    break

                logging.debug('Detecting vehicle ' + str(vehicle_detection))

                if b'IN1ON' in vehicle_detection or b'STAT1' in vehicle_detection:
                    logging.info('Vehicle detected')
                    try:
                        logging.debug('Playing welcome')
                        time.sleep(.1)
                        s.sendall(b'\xa6MT00007\xa9')
                        # logging.debug(GATE['nama'] + ' : ' + str(s.recv(1024)))
                    except Exception as e:
                        logging.error(
                            'Failed to play Selamat Datang ' + str(e))
                        send_notification(
                            GATE['nama'] + ' : Gagal play Selamat Datang ')
                        # keluar dari loop cek kendaraan untuk sambung ulang controller
                        break
                else:
                    time.sleep(2)
                    continue

                reset = False
                error = False

                # detect push button or card
                while True:
                    try:
                        time.sleep(.1)
                        s.sendall(b'\xa6STAT\xa9')
                        push_button_or_card = s.recv(1024)
                    except Exception:
                        logging.error('Failed to sense button and card')
                        send_notification(
                            GATE['nama'] + ' : Gagal mendeteksi tombol tiket')
                        error = True
                        break

                    logging.debug('Detecting button or card ' +
                                  str(push_button_or_card))

                    if b'W' in push_button_or_card:
                        nomor_kartu = str(push_button_or_card).split('W')[
                            1].split('\\xa9')[0]
                        staff = check_card(str(int(nomor_kartu, 16)))
                        time.sleep(.1)  # kasih jeda biar audio bisa play

                        if not staff:
                            try:
                                # kartu tidak dapat digunakan
                                s.sendall(b'\xa6MT00009\xa9')
                            except Exception as e:
                                logging.error(
                                    'Failed to respon invalid card ' + str(e))
                                send_notification(
                                    GATE['nama'] + ' : Gagal merespon kartu invalid')
                                error = True
                                break

                            continue

                        if staff['expired']:
                            try:
                                # masa aktif habis
                                s.sendall(b'\xa6MT00003\xa9')
                            except Exception as e:
                                logging.error(
                                    'Failed to respon card expired ' + str(e))
                                send_notification(
                                    GATE['nama'] + ' : Gagal merespon kartu expired')
                                error = True
                                break

                            continue

                        if not staff['expired'] and staff['expired_in'] == 5:
                            try:
                                s.sendall(b'\xa6MT00001\xa9')
                                time.sleep(6)
                            except Exception as e:
                                logging.error(
                                    'Failed to respon card expired in 5 days ' + str(e))
                                send_notification(
                                    GATE['nama'] + ' : Gagal merespon kartu expired dalam 5 hari')
                                error = True
                                break

                        if not staff['expired'] and staff['expired_in'] == 1:
                            try:
                                s.sendall(b'\xa6MT00002\xa9')
                                time.sleep(6)
                            except Exception as e:
                                logging.error(
                                    'Failed to respon card expired in 1 day ' + str(e))
                                send_notification(
                                    GATE['nama'] + ' : Gagal merespon kartu expired dalam 1 hari')
                                error = True
                                break

                        data = {'is_staff': 1, 'nomor_kartu': staff['nomor_kartu'],
                                'user_id': staff['id'], 'plat_nomor': staff['plat_nomor']}
                        logging.info('Card detected : ' + staff['nomor_kartu'])
                        break

                    elif b'IN2ON' in push_button_or_card or b'STAT11' in push_button_or_card:
                        logging.info('Ticket button pressed')
                        data = {'is_staff': 0}
                        break

                    elif b'IN3' in push_button_or_card:
                        logging.info('Reset')
                        reset = True
                        break

                    elif b'IN4ON' in push_button_or_card:
                        logging.info('Help button pressed')
                        reset = True
                        try:
                            time.sleep(.1)
                            s.sendall(b'\xa6MT00011\xa9')
                            time.sleep(10)
                        except Exception as e:
                            logging.error(
                                'Failed to respon help button ' + str(e))
                            send_notification(
                                GATE['nama'] + ': Gagal merespon tombol bantuan')
                            error = True
                            break

                        send_notification(
                            GATE['nama'] + ': Pengunjung membutuhkan bantuan Anda')
                        break

                    elif b'IN1OFF' in push_button_or_card:
                        logging.info('Vehicle turn back')
                        reset = True
                        break

                    else:
                        # delay 1 detik baru cek lagi status button
                        time.sleep(1)

                # END LOOP SENSING BUTTON OR CARD WHEN VEHICLE DETECTED

                # kalau error keluar dari loop cek kendaraan biar sambung ulang controller
                if error:
                    break

                # kalau reset kembali ke loop cek kendaraan
                if reset:
                    continue

                # lengkapi data kemudian simpan
                data['time_in'] = time.strftime('%Y-%m-%d %T')
                data['nomor_barcode'] = generate_nomor_barcode()
                x = threading.Thread(target=take_snapshot, args=(data,))
                x.start()

                # kalau bukan staff cetak struk
                if data['is_staff'] == 0:
                    if GATE['printer_type'] == 'network':
                        print_ticket_network(data)
                    elif GATE['printer_type'] == 'local':
                        print_ticket_serial(data, s)

                    # play silakan ambil tiket
                    try:
                        s.sendall(b'\xa6MT00008\xa9')
                        logging.debug(str(s.recv(1024)))
                    except Exception as e:
                        logging.error(
                            'Failed to play silakan ambil tiket. ' + str(e))
                        send_notification(
                            GATE['nama'] + ' : Gagal play silakan ambil tiket')
                        break

                # play terimakasih
                try:
                    s.sendall(b'\xa6MT00012\xa9')
                    logging.debug(str(s.recv(1024)))
                except Exception:
                    logging.error('Failed to play terimakasih. ' + str(e))
                    send_notification(
                        GATE['nama'] + ' : Gagal play terimakasih')
                    break

                time.sleep(1)

                # open gate
                try:
                    s.sendall(b'\xa6TRIG1\xa9')
                    logging.debug(str(s.recv(1024)))
                except Exception as e:
                    logging.error('Failed to open gate ' + str(e))
                    send_notification(GATE['nama'] + ' : Gagal membuka gate')
                    break

                logging.info('Gate Opened')

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
                        vehicle_in = s.recv(1024)
                        logging.debug(str(vehicle_in))
                    except Exception as e:
                        logging.error('Failed to sense loop 2 ' + str(e))
                        send_notification(
                            GATE['nama'] + ' : Gagal deteksi kendaraan sudah masuk')
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
    gate_in_thread()


if __name__ == "__main__":
    log_file = '/var/log/gate_in.log'
    logging.basicConfig(filename=log_file, filemode='a', level=logging.DEBUG,
                        format='%(asctime)s - %(levelname)s - %(message)s')
    start_app()
