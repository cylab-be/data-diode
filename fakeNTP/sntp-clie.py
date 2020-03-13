import socket
import subprocess
import shlex

HOST = '192.168.101.2'
PORT = 65432

def run_command(command):
    process = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE)
    output = process.stdout.readline()
    return output.decode('utf-8')

date = run_command('date +"%s.%N"').strip('\n')

with socket.socket(socket.AF_INET, socket.SOCK_DGRAM) as s:
    s.connect((HOST, PORT))
    s.sendall(bytes(date, 'utf-8'))
