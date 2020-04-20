# Code partially taken from :
#   https://www.sqlitetutorial.net/sqlite-python/create-tables/

import socket
import re
import subprocess
from db_management import create_connection, uploader_exists, insert_uploader, change_uploader_attribute, delete_uploader

HOST = '192.168.101.2'
PORT = 65431

def main():
    database = r"/var/www/data-diode/src/storage/app/db.sqlite"

    sql_uploader_exists = "SELECT * FROM uploaders WHERE name=?;"
    sql_insert_uploader = "INSERT INTO uploaders(name, state, port) VALUES (?, ?, ?);"
    sql_change_uploader_state = "UPDATE uploaders SET state=? WHERE name=?;"
    sql_delete_uploader = "DELETE FROM uploaders WHERE name=?;"
    sql_change_uploader_pipport = "UPDATE uploaders SET pipport=? WHERE name=?;"
    
    # create a database connection
    conn = create_connection(database)
 
    # Create tables
    if conn is not None:
        sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        sock.bind((HOST, PORT))

        print("Listening on PORT %d" %PORT)
        while True:

            data, addr = sock.recvfrom(1024)

            pattern = '^[a-zA-Z0-9]+:[0-1]:[0-9]+$'
            del_pattern = '^del:[a-zA-Z0-9]+$'
            pipadd_pattern = '^pipadd:[a-zA-Z0-9]+:[0-9]+$'
            pipremove_pattern = '^pipremove:[a-zA-Z0-9]+$'
            ts = data.decode('utf-8')

            if re.match(pattern, ts):
                uploader, state, port = ts.split(':')
                port = int(port)
                if uploader_exists(conn, sql_uploader_exists, uploader):
                   change_uploader_attribute(conn,  sql_change_uploader_state, uploader, state)
                else:
                    if 1025 <= port and port <= 65535:
                        result = subprocess.run("sudo /var/www/data-diode/src/app/Scripts/add-supervisor-out.sh %s %d" %(uploader,port), shell=True, stdout=subprocess.PIPE)
                        if result.returncode == 0:
                            insert_uploader(conn, sql_insert_uploader, uploader, state, port)
                            print(result.stdout)
                        else:
                            print(result.stdout)
                    else:
                        print("The uploader's port must be between 1025 and 65535.")

            elif re.match(del_pattern, ts):
                _, uploader = ts.split(':')
                if uploader_exists(conn, sql_uploader_exists, uploader):
                    delete_uploader(conn, sql_delete_uploader, uploader)
                    result = subprocess.run("sudo /var/www/data-diode/src/app/Scripts/del-supervisor-out.sh %s" %(uploader), shell=True, stdout=subprocess.PIPE)
                    print(result.stdout)
                else:
                    print('This uploader does not exist.')

            elif re.match(pipadd_pattern, ts):
                _, uploader, pipport = ts.split(':')
                pipport = int(pipport)
                if uploader_exists(conn, sql_uploader_exists, uploader):
                    change_uploader_attribute(conn, sql_change_uploader_pipport, uploader, pipport)
                    result = subprocess.run("sudo /var/www/data-diode/src/app/Scripts/pipadd-out.sh %s %s" %(uploader, str(pipport)), shell=True, stdout=subprocess.PIPE)
                    print(result.stdout)
                else:
                    print('This uploader does not exist.')

            elif re.match(pipremove_pattern, ts):
                _, uploader = ts.split(':')
                if uploader_exists(conn, sql_uploader_exists, uploader):
                    change_uploader_attribute(conn,  sql_change_uploader_pipport, uploader, 0)
                    result = subprocess.run("", shell=True, stdout=subprocess.PIPE)
                    print(result.stdout)
                else:
                    print('This uploader does not exist.')

            else:
                print('Error! The received data is invalid.')
        
        # Close or the database may remain locked
        conn.close()
    else:
        print("Error! cannot create the database connection.")

if __name__ == '__main__':
    main()
