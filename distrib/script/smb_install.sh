#!/bin/bash
echo 'start'
mysql -u root -h ${IP_MYSQL} -p${MYSQL_ROOT_PASSWORD} < /scheduler.sql
echo 'stop'
