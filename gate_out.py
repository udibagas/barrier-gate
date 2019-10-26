#!/usr/bin/env pyhton3

import time
import asyncio
import websockets
import socket
import logging
import requests
import sys

API_URL = 'http://localhost/api'
LOCATION = {'name': 'PLN Jatingaleh'}

def send_notification(gate, message):
    notification = { 'parking_gate_id': gate['id'], 'message': message }
    try:
        requests.post(API_URL + '/notification', data=notification, timeout=3)
    except Exception as e:
        logging.error(gate['name'] + ' : Failed to send notification ' + str(e))
        return False

    try:
        data = { 'text': LOCATION['name'] + ' - ' + message, 'chat_id': 527538821 }
        requests.post('https://api.telegram.org/bot682525135:AAH5H-rqnDlyODgWzNpKiUZGszGz9Oys49g/sendMessage', data=data, timeout=3)
    except Exception:
        pass

    return True

def save_data(gate, data):
    try:
        r = requests.post(API_URL + '/parkingTransaction', data=data, timeout=3)
    except Exception as e:
        logging.info(gate['name'] + ' : Failed to save data ' + str(e))
        send_notification(gate, 'Pengunjung di ' + gate['name'] + ' membutuhkan bantuan Anda (gagal menyimpan data)')
        return False

    return r.json()

def take_snapshot(gate):
    if gate['camera_status'] == 0:
        return ''

    try:
        r = requests.get(API_URL + '/parkingGate/takeSnapshot/' + str(gate['id']))
    except Exception as e:
        logging.error(gate['name'] + ' : Failed to take snapshot ' + str(e))
        send_notification(gate, "Gagal mengambil snapshot di gate " + gate['name'] + " (" + str(e) + ")")
        return ''

    respons = r.json()
    return respons['filename']

def check_card(gate, card_number):
    payload = { 'card_number': card_number, 'active': 1 }
    try:
        r = requests.get(API_URL + '/parkingMember/search', params=payload, timeout=3)
    except Exception as e:
        logging.info(gate['name'] + ' : Failed to check card ' + str(e))
        return False

    if r.status_code == 200:
        return r.json()

    return False

def get_gates():
    try:
        r = requests.get(API_URL + '/parkingGate/search', params={'type': 'OUT'}, timeout=3)
    except Exception as e:
        logging.error('Failed to get gates ' + str(e))
        return False

    if r.status_code == 200:
        return r.json()[0]

    return False

def open_gate(s):
    try:
        s.sendall(b'\xa6OPEN1\xa9')
        logging.debug(gate['name'] + ' : ' + str(s.recv(1024)))
    except Exception as e:
        logging.error(gate['name'] + ' : Failed to open gate ' + str(e))
        send_notification(gate, gate['name'] + ' : Gagal membuka gate')
        return

    logging.info(gate['name'] + ' : Gate Opened')

async def ws(websocket, path):
    while True:
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
            s.settimeout(3)

            try:
                s.connect((gate['controller_ip_address'], int(gate['controller_port'])))
            except Exception as s:
                # kalau gagal konek ulang
                time.sleep(3)
                continue

            error = False

            # LOOP untuk baca kartu / barcode
            while True:
                try:
                    s.sendall(b'\xa6STAT\xa9')
                    r = r.recv(1024)
                except Exception as e:
                    error = True
                    break

                # ada input wiegand (w1 = card, w2 = barcode)
                if b'W1' in r:
                    card_number = str(r).split('W')[1].split('\\xa9')[0]
                    valid_card = check_card(gate, str(int(card_number, 16)))

                    if not valid_card:
                        try:
                            s.sendall(b'\xa6MT00003\xa9')
                        except Exception as e:
                            logging.error(gate['name'] + ' : Failed to respon invalid card ' + str(e))
                            send_notification(gate, gate['name'] + ' : Gagal merespon kartu invalid')
                            error = True
                            break
                        # kalau kartu gak valid ke loop cek kartu/ barcode
                        continue

                    data = { 'is_member': 1, }

                elif b'W2' in r:
                    data = { 'is_member': 0, }
                    # TODO: check valid atau gak barcodenya

                else:
                    time.sleep(1)

                # END LOOP cek kartu / barcode

            # kalau error sambung ulang socket
            if error:
                continue

            # TODO: proses transaksi
            snapshot = take_snapshot(gate)
            data['time_out'] = time.strftime('%Y-%m-%d %T')
            data['gate_out_id'] = gate['id']
            data['snapshot_out'] = snapshot
            saved_data = save_data(data)

            if saved_data is not False:
                await websocket.send(saved_data)

                if data['is_member'] == 1:
                    open_gate(s)

                if data['is_member'] == 0:
                    cmd = await websocket.recv()
                    if cmd == 'open':
                        open_gate(s)

gate = get_gates()

if gate == False:
    logging.error('No gate set. Exit app')
    sys.exit()

start_server = websockets.serve(ws, "127.0.0.1", 5678)

asyncio.get_event_loop().run_until_complete(start_server)
asyncio.get_event_loop().run_forever()

# buat test web socket
# python3 -m websockets wss://echo.websocket.org/
