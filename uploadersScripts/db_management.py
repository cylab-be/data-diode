import sqlite3
from sqlite3 import Error

HOST = '192.168.101.2'
PORT = 65431

database = r"/var/www/data-diode/src/storage/app/db.sqlite"

sql_uploader_exists = "SELECT * FROM uploaders WHERE name=?;"
sql_uploader_pipport = "SELECT pipport FROM uploaders WHERE name=?;"
sql_uploader_aptport = "SELECT aptport FROM uploaders WHERE name=?;"
sql_insert_uploader = "INSERT INTO uploaders(name, state, port) VALUES (?, ?, ?);"
sql_change_uploader_state = "UPDATE uploaders SET state=? WHERE name=?;"
sql_delete_uploader = "DELETE FROM uploaders WHERE name=?;"
sql_change_uploader_pipport = "UPDATE uploaders SET pipport=? WHERE name=?;"
sql_change_uploader_aptport = "UPDATE uploaders SET aptport=? WHERE name=?;"


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
    except Error as e:
        print(e)

def change_uploader_attribute(conn, change_uploader_attribute_sql, uploader, attribute):
    """ change an attribute value of a specific uploader in
            the uploaders table
    :param conn: Connection object
    :param change_uploader_attribute_sql: String object of
            the query used to update an uploader
    :param uploader: String object of the uploader name
    :param attribute: String object of the new attribute value
    :return:
    """
    try:
        c = conn.cursor()
        t = (attribute, uploader,)
        c.execute(change_uploader_attribute_sql, t)
        conn.commit()
    except Error as e:
        print(e)

def delete_uploader(conn, delete_uploader_sql, uploader):
    """ delete a specific uploader in the uploaders
        table
    :param conn: Connection object
    :param delete_uploader_sql: String object of the
            query used to delete an uploader
    :param uploader: String object of the uploader name
    :return:
    """
    try:
        c = conn.cursor()
        t = (uploader,)
        c.execute(delete_uploader_sql, t)
        conn.commit()
    except Error as e:
        print(e)

def get_uploader_pipport(conn, uploader_pipport_sql, uploader):
    """ get the pipport of a specific uploader from
        the uploaders table
    :param conn: Connection object
    :param uploader_pipport_sql: String object of the
            query used to get the pipport
    :param uploader: String object of the uploader name
    :return:
    """    
    try:
        c = conn.cursor()
        t = (uploader,)
        c.execute(uploader_pipport_sql, t)
        return c.fetchone()[0]
    except Error as e:
        print(e)

def get_uploader_aptport(conn, uploader_aptport_sql, uploader):
    """ get the aptport of a specific uploader from
        the uploaders table
    :param conn: Connection object
    :param uploader_aptport_sql: String object of the
            query used to get the aptport
    :param uploader: String object of the uploader name
    :return:
    """    
    try:
        c = conn.cursor()
        t = (uploader,)
        c.execute(uploader_aptport_sql, t)
        return c.fetchone()[0]
    except Error as e:
        print(e)
