import socket
import subprocess
import shlex

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

def run_command(command):
    process = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE)
    output = process.stdout.readline()
    return output.decode('utf-8')

date = run_command('date +"%s.%N"').strip('\n')

with socket.socket(socket.AF_INET, socket.SOCK_DGRAM) as s:
    s.connect((HOST, PORT))
    s.sendall(bytes(date, 'utf-8'))
