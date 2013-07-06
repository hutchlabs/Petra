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

t0 = time.clock()

# Connect to MSSQL and MySQL databases
petra_db = psql.db(host="mysql.petratrust.com", user="pmmember", passwd="sh0wm3th3m0n3y", db="petramembersdb")
http = httplib2.Http()

print time.clock() - t0, "seconds"
