# Code partially taken from :
#   https://www.sqlitetutorial.net/sqlite-python/create-tables/

import socket
import re
from db_management import create_connection, uploader_exists, insert_uploader, change_uploader_state

HOST = '192.168.101.2'
PORT = 65431

def main():
    database = r"/var/www/data-diode/src/storage/app/db.sqlite"

    sql_uploader_exists = "SELECT * FROM uploaders WHERE name=?;"

    sql_insert_uploader = "INSERT INTO uploaders(name, state, port) VALUES (?, ?, ?);"

    sql_change_uploader_state = "UPDATE uploaders SET state=? WHERE name=?;"
    
    # create a database connection
    conn = create_connection(database)
 
    # create tables
    if conn is not None:
        sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        sock.bind((HOST, PORT))

        print("Listening on PORT %d" %PORT)
        while True:
            data, addr = sock.recvfrom(1024)
            pattern = '^[a-zA-Z]+:[0-1]:[0-9]+$'
            ts = data.decode('utf-8')
            uploader, state, port = ts.split(':')
            port = int(port)

            if re.match(pattern, ts):
                if uploader_exists(conn, sql_uploader_exists, uploader):
                   change_uploader_state(conn,  sql_change_uploader_state, uploader, state)
                else:
                    if 1025 <= port and port <= 65535:
                        insert_uploader(conn, sql_insert_uploader, uploader, state, port)
                    else:
                        print("The uploader's port must be between 1025 and 65535")
            else:
                print('Error! The received data is invalid.')
        
        # Close or the database will remain locked
        conn.close()
    else:
        print("Error! cannot create the database connection.")

if __name__ == '__main__':
    main()
