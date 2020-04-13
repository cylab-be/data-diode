# THIS SCRIPT WILL BE RUN USING Python2.7

import socket
import sys
import re
from db_management import create_connection, uploader_exists, insert_uploader, change_uploader_state, delete_uploader

HOST = '192.168.101.2'
PORT = 65431

MSG1 = """
Send a delete message to diode out:
    This script needs 1 argument: uploader_name(alphabetical only)
    Example: python3 %s ftp
OR
Add/update an uploader:
    This script needs three arguments: uploader_name(alphabetical only) uploader_state(0 or 1) port(1025-65535)
    Example: python3 %s ftp 0 36016
"""
MSG3 = """
Add/update an uploader:
    This script needs three arguments: uploader_name(alphabetical only) uploader_state(0 or 1) port(1025-65535)
    Example: python3 %s ftp 0 36016
"""

def main1(uploader):
    database = r"/var/www/data-diode/src/storage/app/db.sqlite"
    
    sql_delete_uploader = "DELETE FROM uploaders WHERE name=?;"

    conn = create_connection(database)
    if conn is not None:
        delete_uploader(conn, sql_delete_uploader, uploader)

        # Information sent to "diode out"
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect((HOST, PORT))
        msg = 'del' + ':' + uploader
        s.sendall(msg) # s.sendall(bytes(msg, 'utf-8')) # Working with Python3 but not Python2
        s.close()
    else:
        print("Error! cannot create the database connection.")

    # Close or the database may remain locked
    conn.close()

def main3(uploader, state, port):

    database = r"/var/www/data-diode/src/storage/app/db.sqlite"

    sql_uploader_exists = "SELECT * FROM uploaders WHERE name=?;"

    sql_insert_uploader = "INSERT INTO uploaders(name, state, port) VALUES (?, ?, ?);"

    sql_change_uploader_state = "UPDATE uploaders SET state=? WHERE name=?;"

    sql_delete_uploader = "DELETE FROM uploaders WHERE name=?;"
        
    # Information sent to "diode in" database
    conn = create_connection(database)
    if conn is not None:
        if uploader_exists(conn, sql_uploader_exists, uploader):
            change_uploader_state(conn,  sql_change_uploader_state, uploader, state)
        else:
            if port != 0:
                insert_uploader(conn, sql_insert_uploader, uploader, state, port)
            else:
                print("The uploader's port must be between 1025 and 65535")

        # Information sent to "diode out" (that'll apply it to its database)
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect((HOST, PORT))
        msg = uploader + ':' + state + ':' + str(port)
        s.sendall(msg) # s.sendall(bytes(msg, 'utf-8')) # Working with Python3 but not Python2
        s.close()
    else:
        print("Error! cannot create the database connection.")

    # Close or the database may remain locked
    conn.close()

if __name__ == '__main__':
    name_pattern = '^[a-zA-Z0-9]+$'
    state_pattern = '^[0-1]$'
    send = False
    if len(sys.argv) == 1:
        send = True
        print(MSG1 %(sys.argv[0], sys.argv[0]))
    if len(sys.argv) == 2:
        if not re.match(name_pattern, sys.argv[1]):
            print("The uploader's name must be composed of alphabetical characters only")
        else:
            main1(sys.argv[1])
    if len(sys.argv) >= 3:
        if not re.match(name_pattern, sys.argv[1]):
            print("The uploader's name must be composed of alphabetical characters only")
        elif not re.match(state_pattern, sys.argv[2]):
            print("The uploader's state must be 0 or 1")        
        else:
            if len(sys.argv) == 3:
                main3(sys.argv[1], sys.argv[2], 0)
    else:
        if not send:
            print(MSG3 %sys.argv[0])
    if len(sys.argv) >= 4:
        if not (1025 <= int(sys.argv[3]) and int(sys.argv[3]) <= 65355):
            print("The uploader's port must be between 1025 and 65535")
        else:
            main3(sys.argv[1], sys.argv[2], int(sys.argv[3]))
