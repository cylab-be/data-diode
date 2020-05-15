# Code partially taken from :
#   https://www.sqlitetutorial.net/sqlite-python/create-tables/

import socket
import re
import subprocess
from db_management import *


def main():    
    
    # create a database connection
    conn = create_connection(database)
 
    # Create tables
    if conn is not None:
        sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        sock.bind((HOST, PORT))

        print("Listening on PORT %d" %PORT)
        while True:

            data, addr = sock.recvfrom(1024)

            name_pattern = UPLOADER_NAME_REGEX
            name_pattern_no_beg = name_pattern[1:len(name_pattern)]     # without the initial ^
            name_pattern_no_end = name_pattern[0:len(name_pattern)-1]   # without the final $
            name_pattern_no_bds = name_pattern[1:len(name_pattern)-1]   # without the boundaries ^ and $

            pattern = name_pattern_no_end + ':[0-1]:[0-9]+$'
            del_pattern = '^del:' + name_pattern_no_beg
            pipadd_pattern = '^pipadd:' + name_pattern_no_bds + ':[0-9]+$'
            pipremove_pattern = '^pipremove:' + name_pattern_no_beg
            aptadd_pattern = '^aptadd:' + name_pattern_no_bds + ':[0-9]+$'
            aptremove_pattern = '^aptremove:' + name_pattern_no_beg
            ts = data.decode('utf-8')

            if re.match(pattern, ts):
                uploader, state, port = ts.split(':')
                port = int(port)
                if uploader_exists(conn, sql_uploader_exists, uploader):
                   change_uploader_attribute(conn,  sql_change_uploader_state, uploader, state)
                else:
                    if 1025 <= port and port <= 65535:
                        result = subprocess.run("sudo /var/www/data-diode/src/app/Scripts/add-supervisor-out.sh %s %d %s" %(uploader,port,HOST), shell=True, stdout=subprocess.PIPE)
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
                    pipport = get_uploader_pipport(conn, sql_uploader_pipport, uploader)
                    change_uploader_attribute(conn, sql_change_uploader_pipport, uploader, 0)
                    result = subprocess.run("sudo /var/www/data-diode/src/app/Scripts/pipremove-out.sh %s %s" %(uploader, pipport), shell=True, stdout=subprocess.PIPE)
                    print(result.stdout)
                else:
                    print('This uploader does not exist.')

            elif re.match(aptadd_pattern, ts):
                _, uploader, aptport = ts.split(':')
                aptport = int(aptport)
                if uploader_exists(conn, sql_uploader_exists, uploader):
                    change_uploader_attribute(conn, sql_change_uploader_aptport, uploader, aptport)
                    result = subprocess.run("sudo /var/www/data-diode/src/app/Scripts/aptadd-out.sh %s %s" %(uploader, str(aptport)), shell=True, stdout=subprocess.PIPE)
                    print(result.stdout)
                else:
                    print('This uploader does not exist.')

            elif re.match(aptremove_pattern, ts):
                _, uploader = ts.split(':')
                if uploader_exists(conn, sql_uploader_exists, uploader):
                    aptport = get_uploader_aptport(conn, sql_uploader_aptport, uploader)
                    change_uploader_attribute(conn, sql_change_uploader_aptport, uploader, 0)
                    result = subprocess.run("sudo /var/www/data-diode/src/app/Scripts/aptremove-out.sh %s %s" %(uploader, aptport), shell=True, stdout=subprocess.PIPE)
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
