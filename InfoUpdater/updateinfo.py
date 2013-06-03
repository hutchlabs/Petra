#!/usr/bin/python

import sys, getopt, re
import pyodbc, decimal
import xlrd, string

# Connect to MSSQL databases
conn = pyodbc.connect("DSN=odbc-petra")
cursor = conn.cursor()

def MSSQLQuery(sql,fetch=True):
    a = cursor.execute(sql).rowcount
    if fetch:
        return cursor.fetchall()
    return a

def testconnection(database):
    sql = "SELECT * FROM [%s].[dbo].cclv_AllEntities AE WHERE AE.EntityTypeDesc in ('Company')" % (database)
    MSSQLQuery(sql)

class Employee:
    "Model for employee"

    def __init__(self, database,eid,ekey,fname,lname,oname,dob,ssnit,empid,phone,mobile,email,addrid,addr1,addr2,addr3,addr4,addr5):
        self.entityid = eid 
        
        self.entitykey = ekey 
        regex = re.compile('^HI.*?')
        self.person = regex.match(str(ekey).strip())

        self.firstname = fname 
        self.surname = lname 
        self.middlename = oname 
        if dob != '':
            self.dob = "%s-%s-%s" % (str(dob[0]), str(dob[1]),str(dob[2]))
        else:
            self.dob = ''
        regex = re.compile('\.0')
        ssnit = regex.sub('', str(ssnit))
        self.ssnit = ssnit 
        self.employerid = empid
        self.phone = phone 
        self.mobile = mobile 
        self.email = email 
        self.addrid = addrid 
        self.addr1 = addr1 
        self.addr2 = addr2 
        self.addr3 = addr3 
        self.addr4 = addr4 
        self.addr5 = addr5 

        self.update_person = "UPDATE [%s].[dbo].[EntityPerson] SET %s WHERE EntityID = %s" % (database, "%s", str(self.entityid)) 
        self.update_field = "UPDATE [%s].[dbo].[EntityField] SET FieldValue = '%s' WHERE EntityID = %s" % (database, "%s", str(self.entityid))
        self.update_contact = "UPDATE [%s].[dbo].[EntityContact] SET %s WHERE EntityID = %s" % (database, "%s", str(self.entityid))
        self.update_address = "UPDATE [%s].[dbo].[Address] SET %s WHERE AddressID = %s" % (database, "%s", str(self.entityid))

    def update(self):
        ep_str = ''
        ef_str = ''
        ec_str = ''
        ad_str = ''

        if self.person is not None:
            # person info
            if self.firstname != '':
                ep_str += "FirstName = '"+str(self.firstname).strip()+"', "
            if self.middlename != '':
                ep_str += "SecondNames = '"+str(self.middlename).strip()+"', "
            if self.surname != '':
                ep_str += "Surname = '"+str(self.surname).strip()+"', "
            if self.dob != '':
                ep_str += "DOB = '"+str(self.dob).strip()+"', "
            if self.ssnit != '':
                ep_str += "NationalInsuranceNo = '"+str(self.ssnit).strip()+"', "

            # employee id info
            if self.employerid != '':
                ef_str = str(self.employerid).strip()
    
        # contact info info
        if self.phone != '':
            ec_str += "TelephoneNo = '"+str(self.phone).strip()+"', "
        if self.mobile != '':
            ec_str += "MobileNo = '"+str(self.mobile).strip()+"', "
        if self.email != '':
            ec_str += "Email = '"+str(self.email).strip()+"', "

        # update address info
        if self.addrid != 0:
            if self.addr1 != '':
                ad_str += "Address1 = '"+re.escape(str(self.addr1)).strip()+"', "
            if self.addr2 != '':
                ad_str += "Address2 = '"+re.escape(str(self.addr2).strip())+"', "
            if self.addr3 != '':
                ad_str += "Address3 = '"+re.escape(str(self.addr3).strip())+"', "
            if self.addr4 != '':
                ad_str += "Address4 = '"+re.escape(str(self.addr4).strip())+"', "
            if self.addr5 != '':
                ad_str += "Address5 = '"+re.escape(str(self.addr5).strip())+"', "
        else:
            print "%s's does not have an existing address. Please create on before updating\n" % str(self.entitykey)

        if ep_str != '':
            regex = re.compile(',\s*$')
            ep_str = regex.sub('', ep_str)
            sql = self.update_person % ep_str
            print "Updating %s's person info\n" % str(self.entitykey)
            MSSQLQuery(sql,False)

        if ef_str != '':
            sql = self.update_field % ef_str
            print "Updating %s's employer id info\n" % str(self.entitykey)
            MSSQLQuery(sql,False)

        if ec_str != '':
            regex = re.compile(',\s*$')
            ec_str = regex.sub('', ec_str)
            sql = self.update_contact % ec_str
            print "Updating %s's contact info\n" % str(self.entitykey)
            a = MSSQLQuery(sql,False)

        if ad_str != '':
            regex = re.compile(',\s*$')
            ad_str = regex.sub('', ad_str)
            sql = self.update_address % ad_str
            print "Updating %s's address info\n" % str(self.entitykey)
            MSSQLQuery(sql,False)


def main(argv):
    inputfile = ''

    try:
        opts, args = getopt.getopt(argv,"hi:",["ifile="])
    except getopt.GetoptError:
        usage()

    for opt, arg in opts:
        if opt in ("-i", "--ifile"):
            inputfile = arg
        else:
            usage()

    if checkfile(inputfile):
        database = getdatabase()
        processfile(database,inputfile)
    else:
        usage("\n\nPlease specify a valid Excel file")

def processfile(database,inputfile):
    employees = []
    idmapping = dict()
    addridmapping = dict()

    print "\n\nWorking on "+inputfile
    wb = xlrd.open_workbook(inputfile)
    sh = wb.sheet_by_index(0)
    startrow = 1
    maxrows = sh.nrows

    # process updates
    print "\n\nProcessing Updates"
    print "------------------"

    uniq = IsUniqueId(sh, startrow, maxrows)

    if uniq != True:
        error("Cannot process file: "+uniq+" entity key is a duplicate. Please make sure entity keys are unique for all employees in column 1",True)

    # Get entity ids
    sql = "SELECT ae.EntityID ,ae.EntityKey, ISNULL(ea.AddressID,0) FROM [%s].[dbo].[cclv_AllEntities] ae LEFT JOIN  [Petra5].[dbo].[EntityAddress] ea ON ea.EntityID = ae.EntityID WHERE EntityTypeDesc in ('Holder - Individual','Holder - Company')" % (database)
    res = MSSQLQuery(sql)

    for row in res:
        idmapping[row[1]] = row[0]
        addridmapping[row[1]] = row[2]

    # get contributions
    for rownum in range(startrow, maxrows):
        ekey = sh.cell_value(rownum, 0) 

        # make sure entity key is valid
        if ekey=='':
            continue

        if ekey not in idmapping:
            error("updating record with entity key: "+str(ekey)+". Entity key was not found in Microgen.\n",False)
            continue

        eid = idmapping[ekey]
        fname = sh.cell_value(rownum, 1)
        lname = sh.cell_value(rownum, 2)
        oname = sh.cell_value(rownum, 3)
        try:
            dob = xlrd.xldate_as_tuple(sh.cell_value(rownum, 4),0)
        except:
            dob = ''
        ssnit = sh.cell_value(rownum, 5)
        empid = sh.cell_value(rownum, 6)
        phone = sh.cell_value(rownum, 7)
        mobile = sh.cell_value(rownum, 8)
        email = sh.cell_value(rownum, 9)

        addrid = addridmapping[ekey]
        addr1 = sh.cell_value(rownum, 10)
        addr2 = sh.cell_value(rownum, 11)
        addr3 = sh.cell_value(rownum, 12)
        addr4 = sh.cell_value(rownum, 13)
        addr5 = sh.cell_value(rownum, 14)

        e = Employee(database,eid,ekey,fname,lname,oname,dob,ssnit,empid,phone,mobile,email,addrid,addr1,addr2,addr3,addr4,addr5)
        employees.append(e)

    # update records 
    for e in employees:
        e.update()

    cursor.commit()
    print "\n\nDone."

#-- Utility methods --#
def getresponse(q,regex,err):
    resp = None
    while not resp:
        resp = raw_input("\n"+q)
        if not re.match(regex, resp.strip()):
            print "\n"+err
            resp = None
    return resp

def getdatabase():
    q = "Select database to connect to:\n\n\t1 - Production (Petra5)\n\t2 - Test (Petra5_Test)\n\nDatabase: "
    regex = "^1|2$"
    error = "Invalid entry. Expecting a 1 or 2"
    dbase = getresponse(q,regex,error)
   
    try:
       database = 'Petra5' if int(dbase)==1 else 'Petra5_Test'
       testconnection(database)
    except:
        print "\nError: Cannot connect to database"
        database = getdatabase()

    return database

def IsUniqueId(sh, startrow, endrow):
    
    fndlist = []

    for rownum in range(startrow, endrow):
        entitykey = sh.cell_value(rownum, 0) 

        if entitykey != '':
            if entitykey in fndlist:
                return entitykey
            fndlist.append(entitykey)

    return True

def checkfile(filename):
    excel = re.compile('.*?\.xlsx?')
    return excel.match(filename)

def usage(msg='',exit=True):
    print msg+"\n\nusage: updateinfo.py -i <excelfile>"
    if exit:
        sys.exit()

def error(msg,exit=False):
    print "\n\nError "+msg
    if exit:
        sys.exit()

if __name__ == "__main__":
   main(sys.argv[1:])
