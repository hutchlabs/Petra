#!/usr/bin/python

"""WebUpdater
A script to update the data available on the website

Copyright (C) 2012 Petra Trust
( http://www.petratrust.com )

"""

import os
import smtplib
from email.MIMEMultipart import MIMEMultipart
from email.MIMEBase import MIMEBase
from email.MIMEText import MIMEText
from email import Encoders
import datetime
import PetraObject as po

gmail_user = "pwebupdater@gmail.com"
gmail_pwd = "pw3bupd@t3r"

def mail(to, subject, text, attach=None):
   msg = MIMEMultipart()

   msg['From'] = gmail_user
   msg['To'] = to
   msg['Subject'] = subject

   msg.attach(MIMEText(text))

   if attach:
    part = MIMEBase('application', 'octet-stream')
    part.set_payload(open(attach, 'rb').read())
    Encoders.encode_base64(part)
    part.add_header('Content-Disposition',
           'attachment; filename="%s"' % os.path.basename(attach))
    msg.attach(part)

   mailServer = smtplib.SMTP("smtp.gmail.com", 587)
   mailServer.ehlo()
   mailServer.starttls()
   mailServer.ehlo()
   mailServer.login(gmail_user, gmail_pwd)
   mailServer.sendmail(gmail_user, to, msg.as_string())
   # Should be mailServer.quit(), but that crashes...
   mailServer.close()

error = []

try:
    # Update employee and employers lists 
    po.UpdateClients() 
except:
    error.append('Problems with updating clients.');

try:
    # Update funds information
    po.UpdateFunds()
except:
    error.append('Problems with updating funds.');

try:
    # Update deals information
    po.UpdateDeals()
except:
    error.append('Problems with updating deals.');

try:
    # Update fund names table information
    po.UpdateFundDealNames()
except:
    error.append('Problems with updating fund names.');

#try:
#    #po.UpdateBalanceHistory()
#except:
#    error.append('Problems with updating balance history.')

po.UpdateSysInfo()

if len(error):
    errors = "\n".join(error)
    now = datetime.datetime.now()
    mail("dhutchful@gmail.com", "[Web Updater] Error running update on "+now.strftime("%Y-%m-%d %H:%M"), "The web updater script failed to run successfully. The following problems occured:\n\n"+errors)

