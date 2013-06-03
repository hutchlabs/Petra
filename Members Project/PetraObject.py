#!/usr/bin/python

"""PetraObject
A wrapper around common objects in Petra db 
Copyright (C) 2012 Petra Trust
( http://www.petratrust.com )

"""

import re
import pyodbc
import datetime
import httplib2
import PySQLObject as psql

# Connect to MSSQL and MySQL databases
conn = pyodbc.connect("DSN=odbc-petra")
cursor = conn.cursor()
petra_db = psql.db(host="mysql.petratrust.com", user="pmmember", passwd="sh0wm3th3m0n3y", db="petramembersdb")

http = httplib2.Http()

class Deal:
   "Store a deal"

   def __init__(self, row):
       self.did,self.dtype,self.fid,self.cid,self.ddate,self.dunits,self.dprice,self.dpayment, self.cancelid = row
       if self.cancelid is None:
	       self.cancelid = 0

   def tosql(self):
       return "("+str(self.did)+",'"+self.dtype+"','"+str(self.fid)+"','"+str(self.cid)+"','"+str(self.ddate)+"','"+str(self.dunits)+"','"+str(self.dprice)+"','"+str(self.dpayment)+"','"+str(self.cancelid)+"'),"


class Fund:
   "Store a fund"

   def __init__(self, id, name, ftype, phone, email, desc, status):
       self.id = id
       self.name = name.replace("'","\\'")
       self.ftype = ftype if ftype else 'None'
       regex = re.compile('^0')
       if phone is not None:
          if not regex.match(phone):
             phone = '0'+str(phone)
          self.phone = phone 
       else:
          self.phone = '' 
       self.email = email if phone else ''
       self.desc = desc if desc else ''
       self.status = status
       tier3 = re.compile('.*?Tier 3 Pre.*?')
       tier4 = re.compile('.*?Tier 3 Post.*?')
       self.tier = 'Tier 2'
       if tier3.match(name):
          self.tier = 'Tier 3'
       if tier4.match(name):
          self.tier = 'Tier 4'

   def update(self, u):
	pass

   def getid(self):
        return self.id

   def tostring(self):
       return str(self.id)+" "+self.name+" "+self.ftype+" "+self.status

   def tosql(self):
       return "("+str(self.id)+",'"+re.escape(self.name)+"','"+self.ftype+"','"+str(self.tier)+"','"+str(self.phone)+"','"+self.email+"','"+self.desc+"','"+self.status+"'),"

class Client:
    "Store a Petra client"

    def __init__(self, id, ekey, name, address, mobile, email, status, utype, compid):
        self.id = id
        self.name = name
        self.ekey = ekey
        self.address = address
        self.phone = []
        self.setphone(mobile)
        self.email = []
        self.setemail(email)
        self.status = status
        self.utype = utype
        self.compid = compid if compid is not None else 0 
    
    def update(self, u):
        if (len((u.getphone())) > 0):
             self.phone.append((u.getphone())[0])
        if (len((u.getemail())) > 0):
             self.email.append((u.getemail())[0])

    def getid(self):
        return self.id

    def getname(self):
        return self.name

    def getphone(self):
        return self.phone

    def getemail(self):
        return self.email

    def getstatus(self):
        return self.status

    def gettype(self):
        return self.utype

    def setphone(self, phone):
        regex = re.compile('^0')
        if phone is not None:
           if not regex.match(phone):
              phone = '0'+str(phone)
           self.phone.append(phone)

    def setemail(self, email):
        if email is not None:
           self.email.append(email)

    def tostring(self):
        print str(self.id)+" | "+self.name+"  Email: "+str(self.email).strip('[]')+"  Phone: "+str(self.phone).strip('[]')
    
    def tosql(self, t):
        return "("+str(self.getid())+",'"+str(self.ekey)+"','"+re.escape(self.getname())+"','"+t+"','"+self.gettype()+"','"+self.getstatus()+"','"+re.escape(str(self.compid))+"'),"

    def attributesql(self):
        sql = ""
        if len(self.getphone()) > 0:
           for ph in self.getphone():
               sql += "("+str(self.getid())+",'phone','"+str(ph)+"'),"

        if len(self.getemail()) > 0:
           for em in self.getemail():
               sql += "("+str(self.getid())+",'email','"+str(em)+"'),"
        
        if self.address != '':
               sql += "("+str(self.getid())+",'address','"+re.escape(str(self.address))+"'),"

        return sql

def CallBalanceApi(client_id):
    url = 'http://www.petratrust.com/members/api/getbh?params[id]='+str(client_id)
    response, content = http.request(url)
    return content

def MSSQLQuery(sql):
    cursor.execute(sql)
    return cursor.fetchall()

def MYSQLQuery(sql, table=None):
    regex = re.compile(',$')
    sql = regex.sub('', sql)

    if table:
       petra_db.query("TRUNCATE TABLE `"+table+"`")
    a = petra_db.query(sql)
    petra_db.commit()
    return a

def add_fundholders_to_web():
    sql = "SELECT a.SourceEntityID, a.TargetEntityID FROM [Petra5].[dbo].[Association] a JOIN petra5.dbo.cclv_AllEntities ae1 on ae1.EntityID = a.SourceEntityID JOIN petra5.dbo.cclv_AllEntities ae2 on ae2.EntityID = a.TargetEntityID AND ae2.EntityTypeDesc = 'Company' where RoleTypeID = 1003 UNION SELECT EntityFundID, HolderEntityID FROM petra5.dbo.fndHolder fn JOIN petra5.dbo.cclv_AllEntities e on e.EntityID = fn.HolderEntityID JOIN petra5.dbo.cclv_AllEntities e1 on e1.EntityID = fn.EntityFundID UNION SELECT a.TargetEntityID, a.SourceEntityID FROM [Petra5].[dbo].[Association] a JOIN petra5.dbo.cclv_AllEntities ae1 on ae1.EntityID = a.SourceEntityID AND ae1.EntityTypeDesc = 'Company' JOIN petra5.dbo.cclv_AllEntities ae2 on ae2.EntityID = a.TargetEntityID  where RoleTypeID = 1003" 
    
    res = MSSQLQuery(sql)

    if len(res):
        ins_sql = "INSERT INTO funds_clients (fund_id,client_id) VALUES "
        for row in res:
            fid,cid = row
            ins_sql += "("+str(fid)+","+str(cid)+"),"

        MYSQLQuery(ins_sql,'funds_clients')

def add_pricehistory_to_web():
    sql = "SELECT [PriceHistoryID],[EntityFundID],[PriceDate],[BidNAV] FROM [Petra5].[dbo].[fndPriceHistory]" 
    res = MSSQLQuery(sql)

    ins_sql = "INSERT INTO prices (id,fund_id,price,date) VALUES "
    for row in res:
        pid,fid,date,price = row
        ins_sql += "("+str(pid)+","+str(fid)+",'"+str(price)+"','"+str(date)+"'),"
    MYSQLQuery(ins_sql,'prices')

def add_funds_to_web(funds):
    if len(funds):
        sql = "INSERT INTO funds (id, name, type, tier, phone, email, description, status) VALUES "

        for f in funds:
            sql += f.tosql()

        MYSQLQuery(sql,'funds')

def add_deals_to_web(deals):
    if len(deals):
        sql = "INSERT INTO deals (id, type, fund_id, client_id, deal_date, units, price, payment,cancelid) VALUES "

        for d in deals:
            sql += d.tosql()

        MYSQLQuery(sql,'deals')

def add_clients_to_web(clients, ctype):
    if len(clients):
        list1 = []
        list2 = []
        for c in clients:
            sql = "INSERT INTO clients (id, entitykey, name, type, description, status,companyid) VALUES " +c.tosql(ctype)
            list1.append(sql)
            q = c.attributesql()
            if q != '':
                sql2 = "INSERT INTO attributes_clients (client_id,attribute,value) VALUES " + q 
                list2.append(sql2)

        for a in list1:
            MYSQLQuery(a)

        for a in list2:
            MYSQLQuery(a)


## -- Getters -- ##

def get_deals_from_server():
    deals = []

    sql = "SELECT fd.DealID,dt.Description,fd.EntityFundID,fh.HolderEntityID,fd.DealingDate,Group1Units,DealingPrice,SettlementAmountPayCcy, fd.CancellingDealID FROM [Petra5].[dbo].[fndDeal] fd LEFT JOIN [Petra5].[dbo].[fndDealType] dt on dt.DealTypeID = fd.DealTypeID LEFT JOIN [Petra5].[dbo].[fndDealStatus] ds on ds.DealStatusID = fd.DealStatusID LEFT JOIN [Petra5].[dbo].[fndProduct] pd on pd.ProductID = fd.ProductID JOIN [Petra5].[dbo].[fndHolder] fh on fh.FundHolderID = fd.FundHolderID WHERE ds.Description NOT IN ('Deleted')"
    res = MSSQLQuery(sql)

    for row in res:
        deal = Deal(row)
        deals.append(deal)

    return deals

def get_funds_from_server():
    funds = []

    sql = "SELECT AE.EntityID, AE.EntityTypeDesc, AE.EntitySubTypeDesc, AE.FullName, REPLACE(REPLACE(EC.TelephoneNo,'-',''),' ','') , EC.Email, AE.StatusCategoryDesc FROM petra5.dbo.cclv_AllEntities AE LEFT JOIN petra5.dbo.EntityContact EC WITH(NOLOCK) ON EC.EntityID = AE.EntityID WHERE AE.EntityTypeDesc in ('Fund','Trust','Master Trust') AND AE.EntitySubTypeDesc IN ('Corporate Scheme','Stand Alone - Sole Corporate Trust Scheme') AND AE.StatusCategoryDesc='Active' GROUP BY AE.EntityID, AE.EntityTypeDesc, AE.EntitySubTypeDesc, AE.FullName, EC.TelephoneNo, EC.Email, AE.StatusCategoryDesc"
    res = MSSQLQuery(sql)

    for row in res:
        cid,ctype,cdesc,cname,cmobile,cemail,cstatus = row
        fund = Fund(cid, cname, ctype, cmobile, cemail, cdesc, cstatus)
	seen = 0
	for f in funds:
	    if f.getid() == fund.getid():
	       f.update(fund)
	       seen=1
	if seen==0:
              funds.append(fund)
    return funds

def get_clients_from_server(ctype):
    clients = []
    selectlist =  "AE.EntityID, AE.EntityKey, AE.EntityTypeDesc, AE.FullName, a.Address1, a.Address2, a.Address3, a.Address4, REPLACE(REPLACE(EC.TelephoneNo,'-',''),' ','') , EC.Email, AE.StatusCategoryDesc"
    join = ""
    groupby = ""

    if ctype == "employee":
        ctype = "('Holder - Individual')"
        selectlist += ", ass.SourceEntityID, ass2.TargetEntityID"
        join = "LEFT JOIN petra5.dbo.Association ass ON ass.TargetEntityID = ae.EntityID and ass.RoleTypeID=1001 and ass.ParentID is null"
        join += " LEFT JOIN petra5.dbo.Association ass2 ON ass2.SourceEntityID = ae.EntityID and ass.RoleTypeID=1001 and ass2.ParentID is null"
        groupby =  "GROUP BY %s" % selectlist
    else:
        ctype = "('Company')"
        selectlist += ", NULL, NULL"

    sql = "SELECT "+selectlist+" FROM petra5.dbo.cclv_AllEntities AE "+join+" LEFT JOIN [Petra5].[dbo].[EntityAddress] ea ON ea.EntityID = AE.EntityID LEFT JOIN petra5.dbo.Address a ON a.AddressID = ea.AddressID LEFT OUTER JOIN petra5.dbo.EntityContact EC WITH(NOLOCK) ON EC.EntityID = AE.EntityID WHERE AE.EntityTypeDesc in "+ctype+" "+groupby
    res = MSSQLQuery(sql)

    for row in res:
        cid,ekey,ctype,cname,a1,a2,a3,a4,cmobile,cemail,cstatus,companyid1,companyid2 = row
        companyid = companyid1 if companyid2 is None else companyid2
        address = ''
        address += a1 if a1 is not None else ''
        address += "<br>"+a2 if a2 is not None else ''
        address += "<br>"+a3 if a3 is not None else ''
        address += "<br>"+a4 if a4 is not None else ''
        client = Client(cid, ekey,cname, address, cmobile, cemail,cstatus, ctype,companyid)
        seen = 0    
        for c in clients:
            if c.getid() == client.getid():
               c.update(client)
               seen = 1
        if seen==0:
           clients.append(client)
    return clients

def UpdateClients():
    MYSQLQuery('TRUNCATE TABLE `clients`');
    MYSQLQuery('TRUNCATE TABLE `attributes_clients`');
    add_clients_to_web(get_clients_from_server('employer'),'employer')
    add_clients_to_web(get_clients_from_server('employee'),'employee')

def UpdateFunds():
    add_funds_to_web(get_funds_from_server())
    add_fundholders_to_web()
    add_pricehistory_to_web()

def UpdateDeals():
    MYSQLQuery('TRUNCATE TABLE `deals`');
    add_deals_to_web(get_deals_from_server())

def UpdateFundDealNames():
    sql = "INSERT IGNORE INTO funds_names SELECT * FROM ( SELECT CONCAT(c.id,'-',f.id) as id, c.id as client_id, c.name as company, f.id as fund_id, f.name as fund, f.name as displayname FROM clients c JOIN funds_clients fc ON fc.client_id = c.id JOIN funds f ON f.id = fc.fund_id WHERE c.description = 'Company') t"
    MYSQLQuery(sql);
    

def UpdateBalanceHistory():
    clients = []
    queries = []
    
    # get clients 
    sql = "SELECT distinct(fc.client_id) FROM `funds_clients` fc JOIN clients c ON c.id = fc.client_id WHERE fc.client_id";
    a = MYSQLQuery(sql);

    for client in a.fetchall():
        cid = client[0]
        con = CallBalanceApi(cid)
        q =  "INSERT INTO balance_history (client_id, data) VALUES ("+str(cid)+",'"+str(con)+"'),"
        queries.append(q)
    
    MYSQLQuery('SELECT COUNT(*) FROM balance_history','balance_history')
    print len(queries) 
    for q in queries:
        MYSQLQuery(q)

def UpdateSysInfo():
    now = datetime.datetime.now()
    t = now.strftime("%Y-%m-%d %H:%M:%S")
    MYSQLQuery('UPDATE sys_info SET value="'+t+'" WHERE property="last_refresh_date"');

if __name__ ==  "__main__":
    print "This file should not be executed"
