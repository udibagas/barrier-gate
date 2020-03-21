#!/usr/bin/python3

import pyautogui
import time
from serial import Serial

with Serial('/dev/ttyUSB0', 9600) as ser:
    x = ''
    while True:
        # data will be *CD:ABC123#
        # xxxxxxxx = 8/14 digit in hex
        x += ser.read().decode('utf-8')
        if '#' in x:
            pyautogui.typewrite(str(int(x[4:][:-1], 16)))
        x = ''
