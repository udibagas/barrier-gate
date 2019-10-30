#!/usr/bin/env pyhton3

import time
import asyncio
import websockets
import socket
import logging
import requests
import sys

API_URL = 'http://localhost/api'
GATE = False

def send_notification(message):
    notification = { 'barrier_gate_id': GATE['id'], 'message': message }
    try:
        requests.post(API_URL + '/notification', data=notification, timeout=3)
    except Exception as e:
        logging.error(GATE['name'] + ' : Failed to send notification ' + str(e))
        return False

    return True

def save_data(data):
    try:
        r = requests.post(API_URL + '/accessLog', data=data, timeout=3)
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

async def ws(websocket, path):
    while True:
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
            s.settimeout(3)

            try:
                s.connect((GATE['controller_ip_address'], int(GATE['controller_port'])))
            except Exception as s:
                # kalau gagal konek ulang
                time.sleep(3)
                continue

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
                    nomor_kartu = str(r).split('W')[1].split('\\xa9')[0]
                    valid_card = check_card(str(int(nomor_kartu, 16)))

                    if not valid_card:
                        try:
                            time.sleep(.1)
                            s.sendall(b'\xa6MT00003\xa9')
                        except Exception as e:
                            logging.error('Failed to respon invalid card ' + str(e))
                            send_notification(GATE['name'] + ' : Gagal merespon kartu invalid')
                            error = True
                            break
                        # kalau kartu gak valid ke loop cek kartu/ barcode
                        continue

                    data = { 'is_staff': 1, }

                elif b'W2' in r:
                    data = { 'is_staff': 0, }
                    # TODO: check valid atau gak barcodenya

                else:
                    time.sleep(1)

                # END LOOP cek kartu / barcode

            # kalau error sambung ulang socket
            if error:
                continue

            # TODO: proses transaksi
            snapshot = take_snapshot()
            data['time_out'] = time.strftime('%Y-%m-%d %T')
            data['gate_out_id'] = GATE['id']
            data['snapshot_out'] = snapshot
            saved_data = save_data(data)

            if saved_data is not False:
                await websocket.send(saved_data)

                if data['is_staff'] == 1:
                    try:
                        s.sendall(b'\xa6OPEN1\xa9')
                    except Exception as e:
                        logging.error('Failed to open gate ' + str(e))
                        send_notification(GATE['name'] + ' : Gagal membuka gate')

                    logging.info('Gate Opened')

                if data['is_staff'] == 0:
                    cmd = await websocket.recv()
                    if cmd == 'open':
                        try:
                            s.sendall(b'\xa6OPEN1\xa9')
                        except Exception as e:
                            logging.error('Failed to open gate ' + str(e))
                            send_notification(GATE['name'] + ' : Gagal membuka gate')

def start_app():
    global GATE

    try:
        r = requests.get(API_URL + '/barrierGate/search', params={'type': 'OUT'}, timeout=3)
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

    start_server = websockets.serve(ws, "127.0.0.1", 5678)
    asyncio.get_event_loop().run_until_complete(start_server)
    asyncio.get_event_loop().run_forever()

if __name__ == "__main__":
    log_file = '/var/log/gate_out.log'
    logging.basicConfig(filename=log_file, filemode='a', level=logging.DEBUG, format='%(asctime)s - %(levelname)s - %(message)s')
    start_app()

# buat test web socket
# python3 -m websockets wss://echo.websocket.org/
