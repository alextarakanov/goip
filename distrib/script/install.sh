#!/bin/bash
echo 'wait when start mysql 15 sec.'
sleep 15
echo 'start smb'
# echo "mysql -u root -h ${IP_MYSQL} -p${MYSQL_ROOT_PASSWORD} -e \"GRANT all ON ${SMB_MYSQL_DATABASE}.* TO ${SMB_MYSQL_USER}@'%' IDENTIFIED BY ${SMB_MYSQL_PASSWORD}\"
# "
mysql -u root -h ${IP_MYSQL} -p${MYSQL_ROOT_PASSWORD} -e "GRANT all ON ${SMB_MYSQL_DATABASE}.* TO ${SMB_MYSQL_USER}@'%' IDENTIFIED BY '${SMB_MYSQL_PASSWORD}'"
mysql -u root -h ${IP_MYSQL} -p${MYSQL_ROOT_PASSWORD} -e "DROP database IF EXISTS ${SMB_MYSQL_DATABASE}"
mysql -u root -h ${IP_MYSQL} -p${MYSQL_ROOT_PASSWORD} -e "create database ${SMB_MYSQL_DATABASE} CHARACTER SET utf8 COLLATE utf8_general_ci"
echo "add tables in ${SMB_MYSQL_DATABASE} database"
mysql -u ${SMB_MYSQL_USER} ${SMB_MYSQL_DATABASE} -h ${IP_MYSQL} -p${SMB_MYSQL_PASSWORD} < /scheduler.sql
mysql -u ${SMB_MYSQL_USER} ${SMB_MYSQL_DATABASE} -h ${IP_MYSQL} -p${SMB_MYSQL_PASSWORD} -e "truncate user";
mysql -u ${SMB_MYSQL_USER} ${SMB_MYSQL_DATABASE} -h ${IP_MYSQL} -p${SMB_MYSQL_PASSWORD} -e "insert into user set name='${SMB_WEB_USER}', password=MD5(CONCAT(MD5('${SMB_WEB_PASSWORD}dbl'),'yzm')), permissions=1, info='docker user'";



echo 'start goip'
sleep 1

mysql -u root -h ${IP_MYSQL} -p${MYSQL_ROOT_PASSWORD} -e "GRANT all ON ${GOIP_MYSQL_DATABASE}.* TO ${GOIP_MYSQL_USER}@'%' IDENTIFIED BY '${GOIP_MYSQL_PASSWORD}'"
mysql -u root -h ${IP_MYSQL} -p${MYSQL_ROOT_PASSWORD} -e "DROP database IF EXISTS ${GOIP_MYSQL_DATABASE}"
mysql -u root -h ${IP_MYSQL} -p${MYSQL_ROOT_PASSWORD} -e "create database ${GOIP_MYSQL_DATABASE} CHARACTER SET utf8 COLLATE utf8_general_ci"
echo "add tables in ${MYSQL_DATABASE} database"
mysql -u ${GOIP_MYSQL_USER} ${GOIP_MYSQL_DATABASE} -h ${IP_MYSQL} -p${GOIP_MYSQL_PASSWORD} < /goip.sql
mysql -u ${GOIP_MYSQL_USER} ${GOIP_MYSQL_DATABASE} -h ${IP_MYSQL} -p${GOIP_MYSQL_PASSWORD} -e "truncate user";
mysql -u ${GOIP_MYSQL_USER} ${GOIP_MYSQL_DATABASE} -h ${IP_MYSQL} -p${GOIP_MYSQL_PASSWORD} -e "insert into user set username='${GOIP_WEB_USER}', password=MD5('${GOIP_WEB_PASSWORD}'), permissions=0, info='docker user'";


sleep 1
echo 'start asterisk cdr and cel'
mysql -u root -h ${IP_MYSQL} -p${MYSQL_ROOT_PASSWORD} -e "GRANT all ON ${ASTERISK_MYSQL_DATABASE}.* TO ${ASTERISK_MYSQL_USER}@'%' IDENTIFIED BY '${ASTERISK_MYSQL_PASSWORD}'"
mysql -u root -h ${IP_MYSQL} -p${MYSQL_ROOT_PASSWORD} -e "DROP database IF EXISTS ${ASTERISK_MYSQL_DATABASE}"
mysql -u root -h ${IP_MYSQL} -p${MYSQL_ROOT_PASSWORD} -e "create database ${ASTERISK_MYSQL_DATABASE} CHARACTER SET utf8 COLLATE utf8_general_ci"


echo "add tables in ${ASTERISK_MYSQL_DATABASE} database"
mysql -u ${ASTERISK_MYSQL_USER} ${ASTERISK_MYSQL_DATABASE} -h ${IP_MYSQL} -p${ASTERISK_MYSQL_PASSWORD} < /asterisk.sql


echo 'stop'
