#!/usr/bin/python

"""PetraObject
A wrapper around common objects in Petra db 
Copyright (C) 2012 Petra Trust
( http://www.petratrust.com )

"""

import re
import datetime
import httplib2
import PySQLObject as psql

import time
import sys

# Connect to MSSQL and MySQL databases
petra_db = psql.db(host="localhost", user="root", passwd="", db="petramembersdb")
http = httplib2.Http()

def CallBalanceApi(client_id):
    url = 'http://localhost/members/api/balance_history?params[id]='+str(client_id)
    response, content = http.request(url)
    return content
    
def MYSQLQuery(sql, table=None):
    regex = re.compile(',$')
    sql = regex.sub('', sql)

    if table:
       petra_db.query("TRUNCATE TABLE `"+table+"`")
    a = petra_db.query(sql)
    petra_db.commit()
    return a

def UpdateBalanceHistory():
    clients = []
    queries = []
    
    # get clients 
    sql = "SELECT distinct(fc.client_id) FROM `funds_clients` fc JOIN clients c ON c.id = fc.client_id WHERE fc.client_id";
    a = MYSQLQuery(sql);

    for client in a.fetchall():
        cid = client[0]
        con = CallBalanceApi(cid)
        q =  "INSERT INTO balance_history2 (client_id, data) VALUES ("+str(cid)+",'"+str(con)+"'),"
        queries.append(q)
    
    MYSQLQuery('SELECT COUNT(*) FROM balance_history','balance_history')
    for q in queries:
        MYSQLQuery(q)

t0 = time.clock()
UpdateBalanceHistory()
print time.clock() - t0, "seconds"
