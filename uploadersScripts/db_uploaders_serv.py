# Code partially taken from :
#   https://www.sqlitetutorial.net/sqlite-python/create-tables/

import sqlite3
from sqlite3 import Error
import socket
import re

HOST = '192.168.101.2'
PORT = 65431

def create_connection(db_file):
    """ create a database connection to the SQLite database
        specified by db_file
    :param db_file: database file
    :return: Connection object or None
    """
    conn = None
    try:
        conn = sqlite3.connect(db_file)
        return conn
    except Error as e:
        print(e)
 
    return conn
	
def uploader_exists(conn, uploader_exists_sql, uploader):
    """ check if the uploader name is already in the
            uploaders table
    :param conn: Connection object
    :param uploader_exists_sql: String object of the
            query used to check the condition
    :param uploader: String object of the uploader name
    :return: True if the uploader name is already in the
            uploaders table, False otherwise
    """
    try:        
        c = conn.cursor()
        t = (uploader,)
        rows = c.execute(uploader_exists_sql, t)
        nb = 0
        for r in rows:
            nb +=1
        return nb
    except Error as e:
        print(e)
        return False

def insert_uploader(conn, insert_uploader_sql, uploader, state):
    """ insert a new uploader in the uploaders table
    :param conn: Connection object
    :param insert_uploader_sql: String object of the
            query used to insert an uploader
    :param uploader: String object of the uploader name
    :param state: String object of the state value
    :return:
    """
    try:        
        c = conn.cursor()        
        t = (uploader, state,)
        c.execute(insert_uploader_sql, t)
        conn.commit()
        print("Uploader named %s added with state = %s" %(uploader, state))
    except Error as e:
        print(e)

def change_uploader_state(conn, change_uploader_state_sql, uploader, state):
    """ change the state value of a specific uploader in
            the uploaders table
    :param conn: Connection object
    :param change_uploader_state_sql: String object of
            the query used to update an uploader
    :param uploader: String object of the uploader name
    :param state: String object of the new state value
    :return:
    """
    try:
        c = conn.cursor()
        t = (state, uploader,)
        c.execute(change_uploader_state_sql, t)
        conn.commit()
        print("Uploader named %s added has now state = %s" %(uploader, state))
    except Error as e:
        print(e)

def main():
    database = r"/var/www/data-diode/src/storage/app/db.sqlite"

    sql_uploader_exists = "SELECT * FROM uploaders WHERE name=?;"

    sql_insert_uploader = "INSERT INTO uploaders(name, state) VALUES (?, ?);"

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
            pattern = '^[a-zA-Z]*:[0-1]$'
            ts = data.decode('utf-8')
            uploader, state = ts.split(':')

            if re.match(pattern, ts):
                if uploader_exists(conn, sql_uploader_exists, uploader):
                   change_uploader_state(conn,  sql_change_uploader_state, uploader, state)
                else:
                    insert_uploader(conn, sql_insert_uploader, uploader, state)
            else:
                print('Error! The received data is invalid.')
        
        # Close or the database will remain locked
        conn.close()
    else:
        print("Error! cannot create the database connection.")

if __name__ == '__main__':
    main()
