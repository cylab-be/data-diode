import sqlite3
from sqlite3 import Error

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

def insert_uploader(conn, insert_uploader_sql, uploader, state, port):
    """ insert a new uploader in the uploaders table
    :param conn: Connection object
    :param insert_uploader_sql: String object of the
            query used to insert an uploader
    :param uploader: String object of the uploader name
    :param state: String object of the state value
    :param state: Integer object of the port value
    :return:
    """
    try:        
        c = conn.cursor()        
        t = (uploader, state,port,)
        c.execute(insert_uploader_sql, t)
        conn.commit()
        print("Uploader named %s added with state = %s and port = %d" %(uploader, state, port))
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
