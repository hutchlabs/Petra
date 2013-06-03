#!/usr/bin/python

import os, sys, getopt, re
import xlrd, string
import difflib
from datetime import date
import PetraObject as po


def main(argv):
    inputfile = ''

    try:
        opts, args = getopt.getopt(argv,"hi:",["ifile="])
    except getopt.GetoptError:
        usage()
        sys.exit()

    for opt, arg in opts:
        if opt in ("-i", "--ifile"):
            inputfile = arg
        else:
            usage()
            sys.exit()

    if checkfile(inputfile):
        processfile(inputfile)
    else:
        usage("\n\nPlease specify a valid Excel file")
        sys.exit()

def SelectSheet(wb):
    if wb.nsheets > 1:
        names = ''
        for i in range(0, wb.nsheets):
            s = wb.sheet_by_index(i);
            names += "\n\t%s) %s" % (str(i+1), s.name)
        q = "Found %s sheets:\n%s\n\nWhich one would you like to use? " % (wb.nsheets, names)
        regex = "^[1-%s]$" % (wb.nsheets)
        error = "Invalid entry. Expecting a numeric value 1 and "+str(wb.nsheets)
        resp = getresponse(q,regex,error)
        return wb.sheet_by_index(int(resp)-1);
    else:
        return wb.sheet_by_index(0);

def processfile(inputfile):
    report = []
    report_new = []
    report_error = []
    report_closed = []
    ssnIdx = midIdx = -1

    sheet = 0
    print "\n\nWorking on "+inputfile
    wb = xlrd.open_workbook(inputfile)
    sh = SelectSheet(wb)

    # initilize 
    getdatabase()
    
    # get company
    company = IdentifyCompany(inputfile)
    print "\nUsing company: "+company.getname()

    # get operational mode (deal or settlement)
    mode = getmode()

    # get deal date
    date = getdate()

    # get running level
    level = getlevel()

    # load data based on company
    print "\nLoading employees, schemes and fund owners (as needed)..."
    schemes, internal, holdercomp, shareclass = po.Load(company, level, date)

    # label columns
    startrow, endrow, colnames = IdentifyColumns(sh)

    # process contributions
    print "\n\nProcessing Contributions"
    print "------------------------"

    names = getEmployeeNames(sh, startrow, endrow, colnames)

    if "Social Security Number" in colnames:
        ssnIdx = colnames.index("Social Security Number");

    if "Staff id" in colnames:
        midIdx = colnames.index("Staff id");

    if ssnIdx < 0 and midIdx  < 0:
        print "\n\nCannot process file: please make sure Social Security Information or Member Ids are included in the contributions report"
        sys.exit(2)

    uniq, duplicates = IsUniqueId(sh, startrow, endrow, ssnIdx, midIdx, colnames)

    if not uniq:
        err  = "\n\nCannot process file: duplicates exist. Please make sure Social Security Information is unique for all employees: %s" % duplicates
        print err
        sys.exit(2)

    # get contributions
    for rownum in range(startrow, endrow):
        # if no name, skip - line is probably blank
        if names[rownum]=='':
            #print "Skipping line "+str(rownum+1)
            continue

        # if no numbers provided for any of the contribution columns, skip
        ml = ['Tier 2 (5%)',
              'Pre-Employer Contributions', 'Pre-Employee Contributions',
              'Post-Employer Contributions','Post-Employee Contributions']

        validline = True
        for c in ml:
            if c in colnames:
                cIdx = colnames.index(c);
                check = isinstance(sh.cell_value(rownum,cIdx), int) or isinstance(sh.cell_value(rownum,cIdx), float) 
                if not check:
                   validline = False

        if not validline:
            print "Skipping line "+str(rownum+1)
            continue

        ssn = sh.cell_value(rownum, ssnIdx) if ssnIdx > -1 else ''
        mid = sh.cell_value(rownum, midIdx) if midIdx > -1 else ''
        mainid = ssn if ssn != '' else mid
        employee = GetEmployee(ssn, mid)

        new = 0
        n = names[rownum] if employee is None else employee.getkey()
        new = 1 if employee is None else 0

        if employee is None:
            x = None
            for mye in po.employees:
                syname = fixname(mye.getname())
                dbname = set(syname.split())
                match = dbname.intersection((names[rownum]).split())
                ngd = True if len(match) >= 2 else False
                if ngd is True:
                    try:    
                        eid = mye.getssid() if ssnIdx > -1 else mye.geteid()
                        eid = fixerrors(eid)
                        mainid = fixerrors(mainid)
                        eid = 'No ID in system' if eid is None else str(eid)
                        x = mye.getkey()+' '+eid+' '+str(syname)+' '+str(mainid)
                    except:
                        print 'Error processing string:', repr(eid)
                    continue
            mainid = fixerrors(mainid)    
            n = x if x is not None else names[rownum]+' '+str(mainid)


        # name match - if mismatch - store in error file
        namegood = True
        if employee is not None:
            syname = fixname(employee.getname())
            dbname = set(syname.split())
            match = dbname.intersection((names[rownum]).split())
            namegood = True if len(match) >= 2 else False
            if namegood is False:
                q = "File name: %s\nMicrogen name: %s\n\nIs this the same person?\n\n\t1 - Yes, this is the same person\n\t2 - No, this is NOT the same person\n\nResponse: " % (str(names[rownum]), syname)
                regex = "^1|2$"
                error = "Invalid entry. Expecting a numeric value 1 or 2."
                resp = getresponse(q,regex,error)
                if int(resp) == 1:
                   namegood = True
            n = employee.getkey()+' '+str(names[rownum])+' '+str(mainid)+' '+syname if not namegood else n

        rep = {'name':n,
               'Tier 2 (5%)':0,
               'Pre-Employer Contributions':0,
               'Pre-Employee Contributions':0,
               'Post-Employer Contributions':0,
               'Post-Employee Contributions':0, 'total':0}
        for colnum in range(0, len(colnames)):
            if colnames[colnum] in ml:
                try:
                    rep[colnames[colnum]] += sh.cell_value(rownum, colnum)
                    rep['total'] += sh.cell_value(rownum, colnum)
                except:
                    print "\nFailed processing data in cell "+str(rownum)+", "+str(colnum)+": "+ str(getCell(sh,rownum, colnum))
                    sys.exit(2)

        if not namegood:
            report_error.append(rep)
        else:
            if new==1:
                report_new.append(rep)
            else:
                if employee.getregstatus() == 'Closed':
                    report_closed.append(rep)
                else:
                    report.append(rep)

    # generate reports: first for existing clients and then for new ones
    tr = GetLastTR() 
    cid = company.getid()

    if len(report) > 0:
        tr = GenerateReports(report,schemes,mode,date,colnames,tr,0,cid)

    if len(report_new) > 0:
        rn = report_new
        tr = GenerateReports(rn,schemes,mode,date,colnames,tr,1,cid)

    if len(report_closed) > 0:
        rc = report_closed
        tr = GenerateReports(rc,schemes,mode,date,colnames,tr,2,cid)

    if len(report_error) > 0:
        re = report_error
        tr = GenerateReports(re,schemes,mode,date,colnames,tr,3,cid)

    if level > 1:
        nr = report + report_new + report_closed + report_error
        tr = GenerateOwnerReport(nr,schemes,internal, holdercomp, shareclass,mode,date,colnames,tr,level,cid)

    SaveLastTR(tr)

#--- methods 
def GenerateOwnerReport(report,schemes,internal,holdercomp,shareclass,omode,date,colnames,trnum,level,cid):
    tr = trnum
    ifhc = None
    ifc = None
    ifid = None
    scc = None
    schc = None
    scid = None
    total_payments = 0

    for scheme in schemes:
        valcol = getNumColName(scheme.getname())
        units = 0
        payment = 0

        if valcol is not None and valcol in colnames:
            scc, schc, ifhc, ifc, scid, ifid = po.getOwnerFundCode(scheme, level, internal,holdercomp,shareclass)

            if level == 3:
                price = getprice(scid, date, omode)
                for r in report:
                    units = units + (r[valcol] / float(price))
                    payment = payment + r[valcol]
                total_payments += payment
                tr = WriteToFile('share',scc,schc,units,price,payment,date,tr,cid)
            else:
                price = getprice(ifid, date, omode)
                for r in report:
                    units = units + (r[valcol] / float(price))
                    payment = payment + r[valcol]

                tr=WriteToFile('internal',ifc,ifhc,units,price,payment,date,tr,cid)

    # fill out internal fund for only level 3
    if level == 3:
        price = getprice(ifid, date, omode)
        units = total_payments / float(price)
        tr = WriteToFile('internal',ifc,ifhc,units,price,total_payments,date,tr,cid)

    print "\nGenerated internal and share files."

    return tr

def WriteToFile(wtype,fundcode,fundholder,units,price,payment,date,tr,cid):
    fname = ''
    fname2 = ''
    fh = None
    fh2 = None

    if wtype=='share':
        fname = "results\\funddeals1-"+str(fundcode)+".txt"
        mode = 'a+' if os.path.exists(fname) else 'w'
        fh = open(fname,mode)

        fname2 = "results\\funddealsettlements1-"+str(fundcode)+".txt"
        mode2 = 'a+' if os.path.exists(fname2) else 'w'
        fh2 = open(fname2,mode2)

    if wtype=='internal':
        fname = "results\\funddeals2-"+str(fundcode)+".txt"
        mode = 'a+' if os.path.exists(fname) else 'w'
        fh = open(fname,mode)

        fname2 = "results\\funddealsettlements2-"+str(fundcode)+".txt"
        mode2 = 'a+' if os.path.exists(fname2) else 'w'
        fh2 = open(fname2,mode2)
    
    if fh.mode == 'w':
        fh.write(fileheader())
        
    if fh2.mode == 'w':
        fh2.write("TransReference\n")
    
 
    t, tr = NextTR(tr,cid)
    s = fileline()
    s = s.replace('{fundcode}',str(fundcode))
    s = s.replace('{fundholder}',str(fundholder))
    s = s.replace('{tr}',t)
    s = s.replace('{units}','%.4f' % round(units, 4))
    s = s.replace('{navprice}','%.6f' % round(price, 6))
    s = s.replace('{payment}','%.2f' % round(payment, 2))
    s = s.replace('{date}', date)
    fh.write(s)
    fh2.write(t+"\n")

    fh.close()
    fh2.close()

    return tr

def GenerateReports(report, schemes, mode, date, colnames, trnum, new,cid):
    for scheme in schemes:
        valcol = getNumColName(scheme.getname())
        if valcol is not None and valcol in colnames:
            sid = scheme.getid() if scheme is not None else None;
            price = getprice(sid, date, mode)
            trnum = GenerateHolderReport(scheme, date, price, report, valcol, trnum, new,cid)
    return trnum

def GenerateHolderReport(scheme, date, price, report, valcol, tr, new,cid):

    suffix = '-new' if new==1 else ''
    suffix = '-closed' if new==2 else suffix
    suffix = '-error' if new==3 else suffix
    fname = "results\\funddeals"+str(scheme.getid())+suffix+".txt"
    fname2 = "results\\funddealsettlements"+str(scheme.getid())+suffix+".txt"

    mode = 'a+' if os.path.exists(fname) else 'w'
    mode2 = 'a+' if os.path.exists(fname2) else 'w'

    fh = open(fname,mode)
    fh2 = open(fname2,mode2)

    if mode == 'w':
        fh.write(fileheader())
    if mode2 == 'w':
        fh2.write("TransReference\n")

    if valcol is not None:
        for r in report:
            t, tr = NextTR(tr,cid)
            units = (r[valcol] / float(price))
            payment = r[valcol]
            name = unicode(r['name']).encode('Utf8')
            s = fileline()
            s = s.replace('{fundcode}',str(scheme.getkey()))
            s = s.replace('{fundholder}',name)
            s = s.replace('{tr}',t)
            s = s.replace('{units}','%.4f' % round(units, 4))
            s = s.replace('{navprice}','%.6f' % round(price, 6))
            s = s.replace('{payment}','%.2f' % round(payment, 2))
            s = s.replace('{date}', date)
            fh.write(s)
            fh2.write(t+"\n")
        ls = "\nGenerated file '"+fname+"' using price GHC %.6f and date "+date
        print ls % round(price,6)
    else:
        print "\nFile not generated: Cannot identify column to use for calculations based on scheme name."

    fh.close()
    fh2.close()

    return tr


#-- Employee related methods --#
def IsUniqueId(sh, start, end, ssnIdx, midIdx, colnames):

    fndlist = []
    good = True
    duplicates = '' 

    ml = ['Tier 2 (5%)',
          'Pre-Employer Contributions', 'Pre-Employee Contributions',
          'Post-Employer Contributions','Post-Employee Contributions']

    for rownum in range(start, end):
        ssn = sh.cell_value(rownum, ssnIdx) if ssnIdx > -1 else ''
        mid = sh.cell_value(rownum, midIdx) if midIdx > -1 else ''

        p = re.compile("\.\d+")
        try:
            ssn = p.sub('',str(ssn).encode('ascii'))
            mid = p.sub('',str(mid).encode('ascii'))
        except:
            pass

        validline = True
        for c in ml:
            if c in colnames:
                cIdx = colnames.index(c);
                check = isinstance(sh.cell_value(rownum,cIdx), int) or isinstance(sh.cell_value(rownum,cIdx), float) 
                if not check:
                   validline = False

        if not validline:
            continue

        if ssn != '':
            if ssn in fndlist:
                good = False
                duplicates += "\n"+str(ssn)+" at location "+str(rownum+1)
            fndlist.append(ssn)
        elif ssn=='' and ssnIdx > -1:
           good = False
           duplicates += "\nBlank SSNIT No. at location "+str(rownum+1)

        if mid != '':
            if str(mid) in fndlist:
                good = False
                duplicates += "\n"+str(mid)+" at location "+str(rownum+1)
            fndlist.append(str(mid))
        elif mid=='' and midIdx > -1:
             good = False
             duplicates += "\nBlank Employee ID No. at location "+str(rownum+1)

    return good, duplicates 

def getEmployeeNames(sh, start, end, colnames):
    fullnameIdx = firstnameIdx = lastnameIdx = middlenameIdx = -1
    names = {}

    if "Full name" in colnames:
        fullnameIdx = colnames.index("Full name")

    if "First name" in colnames:
        firstnameIdx = colnames.index("First name")

    if "Middle name" in colnames:
        middlenameIdx = colnames.index("Middle name")

    if "Last name" in colnames:
        lastnameIdx = colnames.index("Last name")

    for rownum in range(start, end):
        name = ''
        if fullnameIdx > -1:
            name = getCell(sh,rownum, fullnameIdx)
        else:
            if firstnameIdx > -1:
                name = str(getCell(sh,rownum, firstnameIdx))

            if middlenameIdx > -1:
                name += ' '+str(getCell(sh,rownum, middlenameIdx))

            if lastnameIdx > -1:
                name += ' '+str(getCell(sh,rownum, lastnameIdx))

        names[rownum] = fixname(name)

    return names

def getCell(sh, row, col):
    content = sh.cell_value(row, col) 
    content = fixerrors(content)
    return unicode(content).encode('Utf8')

def GetEmployee(ssn, mid):
    employee = None

    try:
        ssn = ssn.strip()
        mid = mid.strip()
    except:
        ssn = str(ssn).strip()
        mid = str(ssn).strip()
    ssn = ssn.replace('.0','')
    mid = mid.replace('.0','')

    if ssn != '':
        for e in po.employees:
            if CoFix(e.getssid()) == CoFix(ssn):
                employee = e
    
    if employee is None:
        if mid != '':
            for e in po.employees:
                if e.geteid() == str(mid):
                    employee = e

    return employee

def CoFix(name):
    try:
        f = str(name).strip()
        subst = f[0]+"O"
        p = re.compile("^\w(0|O)")
        name = p.sub(subst,str(name))
        return name
    except:
        return name

def fixerrors(tobe):
    try:
        tobe= re.sub(r'[\xc2\xa0]'," ",tobe)
        tobe= re.sub(r'[\xe1]'," ",tobe)
    except:
        print type(tobe)
        print repr(tobe)

    return tobe.strip()

def fixname(name):
    p = re.compile("\(.*?\)|\.")
    y = re.compile("\-")
    x = re.compile("\s*,\s*")
    try:
        name = p.sub('',name)
        name = y.sub(' ',name)
        name = x.sub(' ',name)
    except:
        pass
    
    try:
        name= re.sub(r'[\xc2\xa0]'," ",name)
        name= re.sub(r'[\xe1]'," ",name)
    except:
        print type(name)
        print repr(name)

    return string.capwords(name.strip())


#-- Column related methods --#
def IdentifyColumns(sh):
    print "\n\nIdentifying the column names\n"
    print "----------------------------"

    # get data start & end rows
    startrow = getrow(sh, "\nEnter row number of first contribution: ",1)
    startrow = startrow-1
    endrow = getrow(sh, "\nEnter row number of last contribution: ",startrow+1)

    cols = sh.ncols
    colnames = []

    # set default column names
    for colnum in range(0, cols):
        colnames.append("[Column "+str(colnum+1)+"]")

    # Identify column types
    print "\n\nIdentify column types\n"
    print "---------------------"

    for colnum in range(0, cols):
        colnames[colnum] = GetColName(colnum)

    return [startrow, endrow, colnames]


def GetColName(col):
    types = {'0':'Ignore', '1':'Staff id', '2':'Social Security Number',
             '3':'Full name','4':'First name', '5':'Middle name',
             '6':'Last name', '7':'Salary',
             '8':'Pre-Employee Contributions',
             '9':'Post-Employee Contributions',
             '10':'Pre-Employer Contributions',
             '11':'Post-Employer Contributions',
             '12':'Tier 2 (5%)',
             '13':'Total'}

    colletters = [chr(i) for i in xrange(ord('A'), ord('Z')+1)]

    print "\n\nID - Data Type\n"
    for k in sorted(types.keys(), key=int):
        print "%s - %s" % (str(k), types[k])

    resp = None
    req = "\n\nEnter the id of data type for Column "+str(colletters[col])+": "
    while not resp:
        resp = raw_input(req)
        if not re.match("\d+", resp) or resp not in types.keys():
            print "\nInvalid entry. Expecting a valid data type id"
            resp = None

    return types[resp]

def getNumColName(scheme_name):
    if re.match("(.*?)Pre(.*?)Employee\s*$", scheme_name):
        return 'Pre-Employee Contributions'

    if re.match("(.*?)Pre(.*?)Employer\s*$", scheme_name):
        return 'Pre-Employer Contributions'

    if re.match("(.*?)Post(.*?)Employee\s*$", scheme_name):
        return 'Post-Employee Contributions'

    if re.match("(.*?)Post(.*?)Employer\s*$", scheme_name):
        return 'Post-Employer Contributions'

    if re.match("(.*?)Tier 2(.*?)", scheme_name):
        return 'Tier 2 (5%)'

    if re.match("(.*?)Employee\s*(.*?)", scheme_name):
        return 'Pre-Employee Contributions'

    if re.match("(.*?)Employer\s*(.*?)", scheme_name):
        return 'Pre-Employer Contributions'

    return None


#-- Company related methods --#
def IdentifyCompany(filename):
    print "\n\nIdentifying the company\n"
    print "-----------------------"

    po.companies = po.set_companies()
    company = None

    f = re.sub(r"(data\\)?\d+\s*\-\s*\w{3}\s*\-\s*(.*?)\b",'',filename)
    f = re.sub(r'\.\w+','',f)
    #l = str(f).split()
    pattern = "(.*?)"+str(f)+"(.*?)"

    for c in po.companies:
        m = re.match(pattern,c.getname(),re.IGNORECASE)
        if m is not None: 
            company = c

    if company == None:
        company = CompanyOptions()
    else:
        resp = None
        while not resp:
            resp = raw_input("\nFound company '"+company.getname()+"'. Is this correct [y/n]: ")
            if not re.match("y|n", resp):
                print "Invalid entry. Expecting a 'y' or 'n'"
                resp = None
        if re.match("no?", resp):
            company = CompanyOptions()

    return company

def CompanyOptions():
    print "\n\nCannot find company name. Please select one from the list below:\n\n"
    print "ID - Name\n"

    ids = []
    for c in po.companies:
        print str(c.getid())+' - '+c.getname()
        ids.append(c.getid())

    resp = None
    while not resp:
        resp = raw_input("\nEnter company id: ")
        if not re.match("\d+", resp) or int(resp) not in ids:
            print "\nInvalid entry. Expecting a valid company id"
            resp = None

    company = None
    for c in po.companies:
        if int(resp) == c.getid():
            company = c

    return company

#-- User input methods --#
def getrow(sh, text, l):
    maxrow = sh.nrows
    row = None
    while not row:
        row = int(raw_input(text))
        if not re.match("\d+", str(row)) or row < l or row > maxrow:
            print "\nInvalid entry. Expecting a number between "+str(l)+" and "+str(maxrow)
            row = None
    return row

def getdate():
    q = "Enter deal date (yyyy-mm-dd): "
    regex = "[1-2][0-9]{3}-(0[1-9]|1[0-2])-([0-2][0-9]|3[0-1])"
    error = "Invalid entry. Expecting a date in the format yyyy-mm-dd"
    date = getresponse(q,regex,error)
    return date

def getlevel():
    q = "Select scheme structure:\n\n\t1 - One level\n\t2 - Two levels\n\t3 - Three levels\n\nScheme structure: "
    regex = "^1|2|3$"
    error = "Invalid entry. Expecting a 1, 2 or 3"
    level = getresponse(q,regex,error)
    return int(level)

def getdatabase():
    q = "Select database to connect to:\n\n\t1 - Production (Petra5)\n\t2 - Test (Petra5_Test)\n\nDatabase: "
    regex = "^1|2$"
    error = "Invalid entry. Expecting a 1 or 2"
    dbase = getresponse(q,regex,error)
   
    try:
        po.database = 'Petra5' if int(dbase)==1 else 'Petra5_Test'
        po.testconnection()
    except:
        print "\nError: Cannot connect to database"
        getdatabase()

    return int(dbase)

def getprice(scheme, date, mode):
     p = None
     price = None

     if int(mode)==2:
         price = 1.000000
     else:
        if scheme is not None:
            price = po.getPrice(scheme, date)

        if price is None:
            nf = "Cannot find price for given date. "
            q = "Please enter price: "
            q = nf+q if scheme is not None else ""
            regex = "^\d+(\.\d+)?$"
            error = "Invalid entry. Expecting a numeric value."
            price = getresponse(q,regex,error)
            price = round(float(price),6)

     return price

def getmode():
     q = "Select operational mode\n\n\t1 - Dealt Deals\n\t2 - Settlement (price is set to GHC 1)\n\nOperational mode: "
     regex = "^1|2$"
     error = "Invalid entry. Expecting a numeric value 1 or 2."
     mode = getresponse(q,regex,error)
     return mode

def getresponse(q,regex,err):
    resp = None
    while not resp:
        resp = raw_input("\n"+str(q.encode('ascii','ignore')))
        if not re.match(regex, resp.strip()):
            print "\n"+err
            resp = None
    return resp

#-- Output files methods --#
def fileheader():
    return "FundCode	FundHolderCode	HolderAccDesig	TransTypeDesc	TransDirection	ProductCode	WrapperCode	TransReference	TransUnitsGrp1	TransUnitsGrp2	NAVPrice	QuotedPrice	DealingPrice	DealCcyCode	DealCcyPayAmnt	DealCcyDealAmnt	PayCcyCode	PayCcyPayAmnt	PayCcyDealAmnt	ExchangeRate	DealDate	ValueDate	BookDate	PriceDate	FEFRate	FEFDealCcy	FEFPayCcy	DiscRate	DiscDealCcy	DiscPayCcy	ExitFeeRate	ExitFeeDealCcy	ExitFeePayCcy	SettlementBasis\n"

def fileline():
    return "{fundcode}	{fundholder}		Issue	In	Long Term Savings		{tr}	{units}		{navprice}	{navprice}	{navprice}	GHS	{payment}	{payment}	GHS	{payment}	{payment}	1	{date}	{date}	{date}	{date}	0	0	0	0	0	0	0	0	0	N\n"

#-- Utility methods --#
def NextTR(tr,cid):
    c = 100000000000
    n = c + tr
    tr = tr + 1
    today = date.today()
    t = "TR"+today.strftime("%d%m%y")+"-"
    return (str(n).replace('1',t,1), tr)


def GetLastTR():
    trfile = "lasttr.txt"
    tr = None
    if os.path.exists(trfile):
        fh = open(trfile,'r')
        last = ''
        for l in fh:
            last = l
        p = re.compile('\d+')
        m = p.search(last)
        if m is not None:
            tr = int(m.group()) + 1
        else:
            print 'Can\'t find help'
        fh.close()
    
    if tr is None:
        q = "\nTR number not found. Please enter a number to start from: "
        regex = "^\d{0,11}$"
        error = "Invalid entry. Expecting a number between 0 and 99,999,999,999 (no commas)"
        tr = getresponse(q,regex,error)
        SaveLastTR(tr)
        
    return int(tr)

def SaveLastTR(tr):
    trfile = "lasttr.txt"
    fh = open(trfile,'w')
    fh.write(str(tr))
    fh.close()

def checkfile(filename):
    excel = re.compile('.*?\.xlsx?')
    return excel.match(filename)

def usage(msg=''):
    print msg+"\n\nusage: processor.py -i <excelfile>"

if __name__ == "__main__":
    try:
        main(sys.argv[1:])
    except KeyboardInterrupt:
        print "\n\nKilling program. Good bye.\n";
