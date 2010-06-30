#!/usr/bin/env python
import MySQLdb
from datetime import datetime
from os import system


system("rm show30")
system("wget http://maps.azstarnet.com/crime/show30 > /dev/null")
system("./extract show30 > /dev/null")
system("rm show30")



'''
db = MySQLdb.connect(\
        host="localhost",\
        user="GoogleHeatMap",\
        passwd="7ynMELdb",\
        db="GoogleHeatMap")
c = db.cursor()

for item in d['entries']:
    summary = item['summary']
    title   = item['title']
    author  = item['author']
    webURL  = item['id']

    c.execute ("""
         SELECT URL FROM Abstracts WHERE URL=%s""", (webURL))
    if c.fetchone() == None:
        c.execute ("""
             INSERT INTO Abstracts (URL, Title, Authors, Summary)
             VALUES (%s, %s, %s, %s)""",\
                (webURL, title, author, summary))

c.close ()
db.close ()
'''
