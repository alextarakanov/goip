#!/bin/bash
echo 'wait when start mysql 15 sec.'
sleep 15
echo 'start'
echo "mysql -u root -p${MYSQL_ROOT_PASSWORD} < /scheduler.sql"
echo 'add mysql scheduler database'
mysql -u root -h ${IP_MYSQL} -p${MYSQL_ROOT_PASSWORD} < /scheduler.sql
echo 'add mysql goip database'
mysql -u root -h ${IP_MYSQL} -p${MYSQL_ROOT_PASSWORD} < /goip.sql
echo 'stop'
