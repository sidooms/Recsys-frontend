#!/usr/bin/python
#  Recsys-frontend, Copyright (c) 2013, Simon Dooms
#  http://github.com/sidooms/recsys-frontend
#  MIT License
# 
import ConfigParser, os
import sys

config = ConfigParser.ConfigParser()
thispath = os.path.abspath( __file__).replace('settings.py', '')
inipath = os.path.expanduser(thispath + '/../config/settings.ini')
config.read(inipath)
 
db_username = config.get('database', 'db_username').replace('"','')
db_password = config.get('database', 'db_password').replace('"','')
db_db = config.get('database', 'db_db').replace('"','')
db_hostname = config.get('database', 'db_hostname').replace('"','')
db_port = config.get('database', 'db_port').replace('"','')
