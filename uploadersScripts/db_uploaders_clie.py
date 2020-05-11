# THIS SCRIPT WILL BE RUN USING Python2.7

import socket
import sys
import re
from db_management import *


def aptremove(uploader):
    conn = create_connection(database)
    if conn is not None:
        aptport = get_uploader_aptport(conn, sql_uploader_aptport, uploader)
        change_uploader_attribute(conn, sql_change_uploader_aptport, uploader, 0)

        # Information sent to "diode out"
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect((HOST, PORT))
        msg = 'aptremove' + ':' + uploader
        s.sendall(msg) # s.sendall(bytes(msg, 'utf-8')) # Working with Python3 but not Python2
        s.close()
    else:
        print("Error! cannot create the database connection.")

    # Close or the database may remain locked
    if conn is not None:
        conn.close()

def aptadd(uploader, aptport):
    conn = create_connection(database)
    if conn is not None:
        change_uploader_attribute(conn, sql_change_uploader_aptport, uploader, aptport)

        # Information sent to "diode out"
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect((HOST, PORT))
        msg = 'aptadd' + ':' + uploader + ':' + str(aptport)
        s.sendall(msg) # s.sendall(bytes(msg, 'utf-8')) # Working with Python3 but not Python2
        s.close()
    else:
        print("Error! cannot create the database connection.")

    # Close or the database may remain locked
    if conn is not None:
        conn.close()

def pipremove(uploader):
    conn = create_connection(database)
    if conn is not None:
        pipport = get_uploader_pipport(conn, sql_uploader_pipport, uploader)
        change_uploader_attribute(conn, sql_change_uploader_pipport, uploader, 0)

        # Information sent to "diode out"
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect((HOST, PORT))
        msg = 'pipremove' + ':' + uploader
        s.sendall(msg) # s.sendall(bytes(msg, 'utf-8')) # Working with Python3 but not Python2
        s.close()
    else:
        print("Error! cannot create the database connection.")

    # Close or the database may remain locked
    if conn is not None:
        conn.close()

def pipadd(uploader, pipport):
    conn = create_connection(database)
    if conn is not None:
        change_uploader_attribute(conn, sql_change_uploader_pipport, uploader, pipport)

        # Information sent to "diode out"
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect((HOST, PORT))
        msg = 'pipadd' + ':' + uploader + ':' + str(pipport)
        s.sendall(msg) # s.sendall(bytes(msg, 'utf-8')) # Working with Python3 but not Python2
        s.close()
    else:
        print("Error! cannot create the database connection.")

    # Close or the database may remain locked
    if conn is not None:
        conn.close()

def remove(uploader):

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
    if conn is not None:
        conn.close()

def add(uploader, state, port):

    # Information sent to "diode in" database
    conn = create_connection(database)
    if conn is not None:
        if uploader_exists(conn, sql_uploader_exists, uploader):
            change_uploader_attribute(conn, sql_change_uploader_state, uploader, state)
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
    if conn is not None:
        conn.close()

if __name__ == '__main__':
    
    options = ['help', 'add', 'update', 'remove', 'pipadd', 'pipremove', 'aptadd', 'aptremove']

    name_pattern = UPLOADER_NAME_REGEX
    state_pattern = '^[0-1]$'

    if len(sys.argv) == 1:
        print('Error: no option specified. Use "%s help" to see all options' %sys.argv[0])
    
    else:
        
        if sys.argv[1] not in options:        
            print('Error: invalid option. Use "%s help" to see all options' %sys.argv[0])
        
        elif sys.argv[1] == 'help':
            print('%s help\n\tshow this help\n' %sys.argv[0])
            print('%s add name state port\n\tadd an uploader with:\n\t\tname regex ' + name_pattern + '\n\t\tstate in [0, 1]\n\t\tport in [1025, 65535]\n' %sys.argv[0])
            print('%s update name state\n\tupdate an uploader\'s state with:\n\t\tname regex ' + name_pattern + '\n\t\tstate in [0, 1]\n' %sys.argv[0])
            print('%s remove name\n\tremove an uploader with:\n\t\t name regex ' + name_pattern + '\n' %sys.argv[0])
            print('%s pipadd name port\n\tadd a pip module to an uploader with:\n\t\tname regex ' + name_pattern + '\n\t\tport in [1025, 65535]\n' %sys.argv[0])
            print('%s pipremove name\n\tremove a pip module from an uploader with:\n\t\tname regex ' + name_pattern + '\n' %sys.argv[0])
            print('%s aptadd name port\n\tadd an apt module to an uploader with:\n\t\tname regex ' + name_pattern + '\n\t\tport in [1025, 65535]\n' %sys.argv[0])
            print('%s aptremove name\n\tremove an apt module from an uploader with:\n\t\tname regex ' + name_pattern + '\n' %sys.argv[0])
    
        elif sys.argv[1] == 'add':
            if not len(sys.argv) == 5:
                print('Error: invalid number of parameters. Use "%s help" to see all options' %sys.argv[0])
            elif not re.match(name_pattern, sys.argv[2]) or sys.argv[3] not in ['0', '1'] or not (1025 <= int(sys.argv[4]) and int(sys.argv[4]) <= 65355):
                print('Error: one or more invalid parameters. Use "%s help" to see all options' %sys.argv[0])
            else:
                add(sys.argv[2], sys.argv[3], int(sys.argv[4]))

        elif sys.argv[1] == 'update':
            if not len(sys.argv) == 4:
                print('Error: invalid number of parameters. Use "%s help" to see all options' %sys.argv[0])
            elif not re.match(name_pattern, sys.argv[2]) or sys.argv[3] not in ['0', '1']:
                print('Error: one or more invalid parameters. Use "%s help" to see all options' %sys.argv[0])
            else:
                add(sys.argv[2], sys.argv[3], 0)

        elif sys.argv[1] == 'remove':
            if not len(sys.argv) == 3:
                print('Error: invalid number of parameters. Use "%s help" to see all options' %sys.argv[0])
            elif not re.match(name_pattern, sys.argv[2]):
                print('Error: one or more invalid parameters. Use "%s help" to see all options' %sys.argv[0])
            else:
                remove(sys.argv[2])

        elif sys.argv[1] == 'pipadd':
            if not len(sys.argv) == 4:
                print('Error: invalid number of parameters. Use "%s help" to see all options' %sys.argv[0])
            elif not re.match(name_pattern, sys.argv[2]) or not (1025 <= int(sys.argv[3]) and int(sys.argv[3]) <= 65355):
                print('Error: one or more invalid parameters. Use "%s help" to see all options' %sys.argv[0])
            else:
                pipadd(sys.argv[2], int(sys.argv[3]))

        elif sys.argv[1] == 'pipremove':
            if not len(sys.argv) == 3:
                print('Error: invalid number of parameters. Use "%s help" to see all options' %sys.argv[0])
            elif not re.match(name_pattern, sys.argv[2]):
                print('Error: one or more invalid parameters. Use "%s help" to see all options' %sys.argv[0])
            else:
                pipremove(sys.argv[2])

        elif sys.argv[1] == 'aptadd':
            if not len(sys.argv) == 4:
                print('Error: invalid number of parameters. Use "%s help" to see all options' %sys.argv[0])
            elif not re.match(name_pattern, sys.argv[2]) or not (1025 <= int(sys.argv[3]) and int(sys.argv[3]) <= 65355):
                print('Error: one or more invalid parameters. Use "%s help" to see all options' %sys.argv[0])
            else:
                aptadd(sys.argv[2], int(sys.argv[3]))

        elif sys.argv[1] == 'aptremove':
            if not len(sys.argv) == 3:
                print('Error: invalid number of parameters. Use "%s help" to see all options' %sys.argv[0])
            elif not re.match(name_pattern, sys.argv[2]):
                print('Error: one or more invalid parameters. Use "%s help" to see all options' %sys.argv[0])
            else:
                aptremove(sys.argv[2])
