import socket
import time
import sys

with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
	s.settimeout(3)

	try:
	    s.connect(('192.168.1.100', 5000))
	except Exception as e:
		print('Failed to connect')
		sys.exit()

	sounds = [
		b'\xa6MT00001\xa9', b'\xa6MT00002\xa9', b'\xa6MT00003\xa9',
		b'\xa6MT00004\xa9', b'\xa6MT00005\xa9', b'\xa6MT00006\xa9',
		b'\xa6MT00007\xa9', b'\xa6MT00008\xa9', b'\xa6MT00009\xa9',
		b'\xa6MT00010\xa9', b'\xa6MT00011\xa9', b'\xa6MT00012\xa9',
		b'\xa6MT00013\xa9', b'\xa6MT00014\xa9', b'\xa6MT00015\xa9',
		b'\xa6MT00016\xa9'
	]

	for sound in sounds:
		print(time.strftime('%T'), str(sound))
		try:
			s.send(sound)
			time.sleep(10)
		except Exception as e:
			print(str(e))

		print(time.strftime('%T'), s.recv(32))
		time.sleep(1)

