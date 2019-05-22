#!/bin/sh
echo 'start'
mysql -u root -h ${IP_MYSQL} -p${SMB_MYSQL_PASSWORD} < /goip.sql
echo 'stop'
