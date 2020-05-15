import socket
import subprocess
import datetime
import re

# .env
import os
from dotenv import load_dotenv
# Get the path to the directory this file is in
BASEDIR = os.path.abspath(os.path.dirname(__file__))
# Connect the path with your '.env' file name
load_dotenv(os.path.join(BASEDIR, '../src/.env'))
if (os.getenv("DIODE_IN", "true") == "true"):
    HOST = os.getenv("DIODE_OUT_IP")
else:
    HOST = os.getenv("INTERNAL_IP")
PORT = 65432

sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
sock.bind((HOST, PORT))
while True:
    data, addr = sock.recvfrom(1024)
    # ts = xxxxxxxxxx.yyyyyyyyyy because of the date +"%s.%N" command sent by client
    ts = data.decode('utf-8')
    pattern = '^[0-9]+.[0-9]+$'
    if re.match(pattern, ts):
        # xxxxxxxxxx part of the date = YYYY-MM-dd HH-mm-ss (UTC)
        ts_s = ts.split('.')[0]
        # yyyyyyyyyy part of the date = nanoseconds
        ts_N = ts.split('.')[1]
        # full_date = YYYY-MM-dd HH-mm-ss.nanoseconds (UTC)
        unix_date = datetime.datetime.utcfromtimestamp(int(ts_s)).strftime('%Y-%m-%d %H:%M:%S')
        full_date = str(unix_date) + '.' + str(ts_N)
        subprocess.run(["sudo", "date", "--set", full_date])
    else:
        print('No valid date received')
