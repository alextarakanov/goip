#!/bin/bash


# tar xvzf /distrib/goip_install-v1.26.tar.gz -C / &&
# cp -f /goip_install/goip /usr/local/goip 

# sed -i "s/\$dbhost='localhost';/\$dbhost='${IP_MYSQL}';/" /usr/local/goip/inc/config.inc.php &&
# sed -i "s/\$dbuser='goip';/\$dbuser='${MYSQL_USER}';/" /usr/local/goip/inc/config.inc.php &&
# sed -i "s/\$dbpw='goip';/\$dbpw='${MYSQL_PASSWORD}';/" /usr/local/goip/inc/config.inc.php &&


echo "<?php 
\$dbhost='${IP_MYSQL}';
\$dbuser='${MYSQL_USER}';
\$dbpw='${MYSQL_PASSWORD}';
\$dbname='${MYSQL_DATABASE}';
\$goipcronport='${GOIPCRONPORT}';
\$goipdocker='${GOIP_DOCKER_LOCALNET_IP}';
\$charset='utf8';
\$endless_send=0;
\$re_ask_timer=3;
?>" > /config_goipcrone.php

echo 'start goipcon' 
export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/lib_goip 

while true ; do
    CHECK_PORT=`netstat -anu |grep 44444 |wc -l`
    CHECK_PROC=`ps aux  |grep goipcron |grep -v grep |wc -l`
	if [  $CHECK_PORT -eq 0 ]; then
        kill `pidof goipcron` 
		echo "goipcron exit, try start."
		/usr/local/bin/goipcron /config_goipcrone.php
         sleep 5
     elif [ $CHECK_PROC -gt 2 ]; then
        echo "goipcron too many proccess, try kill all process."
        kill `pidof goipcron`
        echo "goipcron too many proccess, try start goipcron."
		/usr/local/bin/goipcron /config_goipcrone.php
        sleep 5
     else 
        date
        echo "port open, process runing";
	fi
	sleep 10
    
done