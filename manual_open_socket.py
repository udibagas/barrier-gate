#!/usr/bin/env python3

import time
import asyncio
import datetime
import random
import websockets
from serial import Serial
import logging
import os
import sys
import configparser
import json

CFG = configparser.ConfigParser()
CFG.read_file(open(os.path.join(os.path.dirname(__file__), 'controller.cfg')))

async def open_gate(websocket, path):
    while True:
        cmd = await websocket.recv()
        if (cmd == 'open'):
            try:
                ser = Serial(CFG['controller']['device'], int(CFG['controller']['baudrate']), timeout=1)
            except Exception as e:
                await websocket.send(json.dumps({"status" : False, "message": "Gagal membuka serial " + str(e)}))
                continue

            try:
                ser.write(CFG['cmd']['open'].encode())

                if CFG['cmd']['close'] is not None:
                    time.sleep(1)
                    ser.write(CFG['cmd']['close'].encode())

                ser.close()
                await websocket.send(json.dumps({"status" : True, "message": "Gate berhasil dibuka"}))
            except Exception as e:
                await websocket.send(json.dumps({"status" : False, "message": "Gate gagal dibuka " + str(e)}))
        else:
            await websocket.send(json.dumps({"status" : False, "message": "Perintah tidak dikenal"}))

start_server = websockets.serve(open_gate, CFG['websocket']['ip'], int(CFG['websocket']['port']))

asyncio.get_event_loop().run_until_complete(start_server)
asyncio.get_event_loop().run_forever()


# $ python -m websockets wss://echo.websocket.org/
