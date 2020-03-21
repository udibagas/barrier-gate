#!/usr/bin/python3

import pyautogui
import time
from serial import Serial

with Serial.open('/dev/ttyS1', 9600) as ser:
    while True:
        # data will be *CD:ABC123#
        # xxxxxxxx = 8/14 digit in hex
        x = ser.readline()
        pyautogui.typewrite(str(int(x[4:][:-1], 16)))
