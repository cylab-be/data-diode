# THIS SCRIPT WILL BE RUN USING Python2.7

import socket
import sys
import re

HOST = '192.168.101.2'
PORT = 65431

def main(name, state):
    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    s.connect((HOST, PORT))
    msg = name + ':' + state
    # s.sendall(bytes(msg, 'utf-8')) # Working with Python3 but not Python2
    s.sendall(msg)
    print(msg)
    s.close()
        
    
if __name__ == '__main__':
    name_pattern = '^[a-zA-Z]*$'
    state_pattern = '^[0-1]$'
    if len(sys.argv) >= 3:
        if not re.match(name_pattern, sys.argv[1]):
            print("The uploader's name must be composed of alphabetical characters only")
        elif not re.match(state_pattern, sys.argv[2]):
            print("The uploader's state must be 0 or 1")
        else:
            main(sys.argv[1], sys.argv[2])
    else:
        print('Error! This script needs two arguments: uploader_name(alphabetical only) uploader_state(0 or 1)\nExample: python3 %s aptmirror 0' %sys.argv[0])
