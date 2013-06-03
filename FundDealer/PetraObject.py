#!/usr/bin/python

"""PetraObject
A wrapper around common objects in Petra db 

Copyright (C) 2012 Petra Trust
( http://www.petratrust.com )

"""

import decimal
import pyodbc
import sys
import time
import re
import difflib
import string

# Connect to MSSQL and MySQL databases
conn = pyodbc.connect("DSN=odbc-petra")
cursor = conn.cursor()
database = 'Petra5' 
employees = []
companies = [] 
prices = dict()

def MSSQLQuery(sql):
    cursor.execute(sql)
    return cursor.fetchall()

def testconnection():
    sql = "SELECT * FROM [%s].[dbo].cclv_AllEntities AE WHERE AE.EntityTypeDesc in ('Company')" % (database)
    MSSQLQuery(sql)

class Scheme:
   "Store a fund"

   def __init__(self, id, skey, name, stype, owner=None, holderkey=None, holderid=None):
       self.id = id
       self.key = skey 
       self.stype = stype 
       self.name = name.replace("'","\\'")
       self.owner = owner
       self.holders = []
       self.hashc = 0
       if holderid is not None:
           self.holders.append((holderid, holderkey))
           if re.match("^HC", holderkey):
               self.hashc = 1

   def getid(self):
       return self.id

   def getkey(self):
       return self.key 

   def getname(self):
       return self.name 

   def gettype(self):
       return self.stype 

   def getowner(self):
       return self.owner 

   def getholders(self):
       return self.holders 

   def updateHolders(self, holderkey, holderid):
       #self.holders.append((holderid, holderkey))
       if re.match("^HC", holderkey):
            self.hashc = 1

   def hasHC(self):
       return self.hashc

   def isLevelOne(self):
       return (self.owner==None and self.hashc==0)

   def isHolderCompany(self):
       return (self.owner is not None and len(self.holders)==0)

   def isInternal(self):
       return (self.owner is None and self.hashc)

class Company:
   "Store a company"

   def __init__(self, id, key, name):
       self.id = id
       self.key = key
       self.name = name.replace("'","\\'")
  
   def getid(self):
       return self.id

   def getkey(self):
       return self.key

   def getname(self):
       return self.name 

   def match(self, text):
       regex = "^"+text+"(.*?)"
       m = re.match(regex,self.name)
       return difflib.SequenceMatcher(None,self.name.lower(),text.lower()).ratio() if m is not None else 0

class Employee:
    "Petra employee object"

    def __init__(self, info):
        self.id, self.key, self.regstatus,self.ssid,self.eid,self.shortname,self.fullname = info
        p = re.compile("\(.*?\)|\.|,?")
        self.fullname = p.sub('',self.fullname)
    
    def getid(self):
        return self.id

    def getssid(self):
        return self.ssid

    def geteid(self):
        return self.eid

    def getkey(self):
        return self.key

    def getname(self):
        return string.capwords(self.fullname.strip())

    def getregstatus(self):
        return self.regstatus

    def match(self, text):
        return difflib.SequenceMatcher(None,string.capwords(self.fullname).lower(),text.lower()).ratio()

## -- Setters -- ##
def set_companies():
    company = []

    sql = "SELECT AE.EntityID, AE.EntityKey, AE.FullName FROM [%s].[dbo].cclv_AllEntities AE WHERE AE.EntityTypeDesc in ('Company') ORDER BY AE.FullName" % database
    res = MSSQLQuery(sql)

    for row in res:
        cid, ckey, cname = row
        c = Company(cid, ckey, cname)
        company.append(c)
    
    companies = company
    
    return company

def set_employees(company_id):
    sql = "SELECT indv.EntityID ,[EntityKey] ,s.Description as 'RegStatus'  ,RTRIM(LTRIM(ep.NationalInsuranceNo)) as ssid ,RTRIM(LTRIM(ssid.FieldValue)) as eid ,[ShortName] ,[FullName] FROM [%s].[dbo].[cclv_entListIndividuals] indv JOIN [%s].[dbo].[Association] a ON a.[TargetEntityID] = indv.EntityID AND a.RoleTypeId=1001 AND a.SourceEntityID= %s JOIN [%s].[dbo].[Status] s ON s.StatusID = indv.StatusID AND s.Description != 'Dead' LEFT JOIN [%s].[dbo].[EntityPerson] ep ON ep.EntityID = indv.EntityID AND ep.NationalInsuranceNo is not null LEFT JOIN [%s].[dbo].[EntityField] ssid ON ssid.EntityID = indv.EntityID" % (database,database,str(company_id),database,database,database)
    res = MSSQLQuery(sql)

    for row in res:
        employee = Employee(row)
        employees.append(employee)

    return employees


def set_fundsponsors(company_id, level):
    sql = "SELECT a.SourceEntityID, a.TargetEntityID, ae2.EntityKey, ae2.EntityTypeDesc FROM [%s].[dbo].[Association] a JOIN [%s].[dbo].[cclv_AllEntities] ae2 on ae2.EntityID = a.TargetEntityID where RoleTypeID = 1003 AND a.SourceEntityID = %s" % (database, database, str(company_id))
    res = MSSQLQuery(sql)

    sponsors = dict()
    internal = []
    holdercomp = []
    shareclass = []
    schemes = []

    if len(res):
       for row in res:
           
           cid,sid,skey,stype = row

           if sponsors.has_key(cid):
                sponsors[cid].append(sid)
           else:
                sponsors[cid] = [sid]

           sh = find_schemes(sid)
           if sh is not None:
                if sh.isInternal():
                    internal.append(sh)
                else:
                    if sh.isHolderCompany():
                        holdercomp.append(sh)
                        if level == 3:
                            scinfo = find_shareclass(sh.getid())
                            if scinfo is not None:
                                shareclass.append(scinfo)
                    else:
                        if level == 1:
                            if sh.isLevelOne():
                                schemes.append(sh)
                        else:
                            if not sh.isLevelOne():
                                schemes.append(sh)

    return sponsors, schemes, internal, holdercomp, shareclass

def find_schemes(scheme_id):
    sql = "SELECT AE.EntityID, AE.EntityKey, AE.FullName, AE.EntitySubTypeDesc, own.TargetEntityID, fh.EntityKey, fh.HolderEntityID FROM [%s].[dbo].cclv_AllEntities AE LEFT JOIN (SELECT a.SourceEntityID, a.TargetEntityID FROM [%s].[dbo].[Association] a WHERE a.SourceEntityID = %s and a.RoleTypeID = 2) own on own.SourceEntityID =%s LEFT JOIN (SELECT fh.HolderEntityID, fh.EntityFundID, ae.EntityKey FROM [%s].[dbo].[fndHolder] fh  JOIN [%s].[dbo].[cclv_AllEntities] ae ON ae.EntityID = fh.HolderEntityID WHERE fh.EntityFundID =%s) fh on fh.EntityFundID = %s WHERE AE.EntityId = %s" % (database,database,str(scheme_id),str(scheme_id),database,database,str(scheme_id),str(scheme_id),str(scheme_id))
    res = MSSQLQuery(sql)

    seen = []
    s = None
    for row in res:
        sid, skey, sname, stype, sown, shckey, shc = row
        if sid in seen:
            s.updateHolders(shckey, shc)         
        else:
            seen.append(sid)
            s = Scheme(sid, skey, sname, stype, sown, shckey, shc)

    return s

def find_shareclass(scheme_id):
    sql = "SELECT fh1.HolderEntityID, scinfo.shareclass, scinfo.shareid, scinfo.shareholder, scinfo.Internalfund, scinfo.IFID FROM [%s].[dbo].[fndHolder] fh1 JOIN (SELECT a.SourceEntityID, fh.EntityFundID, ae1.EntityKey as 'shareclass', ae1.EntityID as 'shareid', ae2.EntityKey as 'shareholder', ae3.EntityKey as 'Internalfund', ae3.EntityID as 'IFID' 	FROM [%s].[dbo].[Association] a JOIN [%s].[dbo].[fndHolder] fh on fh.HolderEntityID = a.TargetEntityID JOIN [%s].[dbo].[cclv_AllEntities] ae1 on ae1.EntityID = a.SourceEntityID JOIN [%s].[dbo].[cclv_AllEntities] ae2 on ae2.EntityID = a.TargetEntityID JOIN [%s].[dbo].[cclv_AllEntities] ae3 on ae3.EntityID = fh.EntityFundID where a.RoleTypeID = 2) scinfo on scinfo.SourceEntityID = fh1.EntityFundID and scinfo.EntityFundID != fh1.EntityFundID WHERE fh1.HolderEntityID = %s" % (database, database, database, database,database,database,str(scheme_id))
    res = MSSQLQuery(sql)

    for row in res:
        hcid, shareclass, shid, shareholder, internalfund, ifid = row
        return (hcid, shareclass, shid, shareholder, internalfund, ifid)
    return None

def set_prices(date):
    date = date + ' 00:00:00'

    try:
        sql = "SELECT [PriceHistoryID],[EntityFundID],[PriceDate],[BidNAV] FROM [%s].[dbo].[fndPriceHistory] WHERE PriceDate = '%s'" % (database, date)
        res = MSSQLQuery(sql)

        for row in res:
            pid,fid,date,price = row
            idx = (fid, str(date).replace(' 00:00:00',''))
            prices[idx] = price
    except:
        print "\nInvalid date provided. Exiting."
        sys.exit()

    return prices

## -- Getters -- ##
def Load(company, level, date):

    # get schemes, internal fund, holder companies, shareclass info
    sponsors, cschemes, internal, holdercomp, shareclass = set_fundsponsors(company.getid(), level)

    #print sponsors
    #print internal
    #print holdercomp
    #print shareclass

    if len(cschemes):
        print "\nFound the following schemes: "
        for s in cschemes:
            print s.getname(),':',s.getkey()
    else:
        print "\nError: Found no matching funds for "+company.getname().strip()+". Cannot proceed."
        sys.exit()

    # get scheme owners
    if level > 1:
        owners = []
        for h in holdercomp:
            if h.getowner() is not None:
                owners.append(h)
        if len(owners):
            print "\nFound the following company owners: "
            for h in owners:
                print h.getname(),': ',h.getkey()
        else:
            print "\nError: Found no company holder found for schemes. Cannot proceed."
            sys.exit()

    # get employees
    employees = set_employees(company.getid())

    # set prices
    prices = set_prices(date)

    return cschemes, internal, holdercomp, shareclass

def getOwnerFundCode(scheme, level, internal, holders, shareclass):
    scc = None      # share class code
    scid = None     # share class id
    schc = None     # share class holder code

    ifc = None      # internal fund code
    ifhc = None     # internal fund holder code
    ifid = None     # internal fund id
    holdercompany = None

    # get holder company id
    hcid = scheme.getowner()
    for holder in holders:
        if holder.getid() == hcid:
          holdercompany = holder

    if level==2:
        try:
            ifc = (internal[0]).getkey()
            ifid = (internal[0]).getid()
            ifhc = holdercompany.getkey()
        except:
            print "\nCan't generate Share and Internal files. You chose level 2, however this does not seem to be a level 2 client. Please restart and pick the right level."
            sys.exit(2)

    if level==3:
        schc = holdercompany.getkey()
        for l in shareclass:
            hid, share, shid, sharehc, intfund, intfid = l
            if hid==hcid:
                scc = share
                scid = shid
                ifc = intfund
                ifid = intfid 
                ifhc = sharehc

    return (scc, schc, ifhc, ifc, scid, ifid)

def getPrice(fid, date):
    idx = (fid, date)
    try:
        p = prices[idx]
        return p
    except:
        return None


if __name__ ==  "__main__":
    print "This file should not be executed"
