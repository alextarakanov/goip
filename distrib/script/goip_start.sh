#!/bin/bash

echo "<?php 
\$dbhost='${IP_MYSQL}';
\$dbuser='${GOIP_MYSQL_USER}';
\$dbpw='${GOIP_MYSQL_PASSWORD}';
\$dbname='${GOIP_MYSQL_DATABASE}';
\$goipcronport='${GOIPCRONPORT}';
\$goipdocker='${GOIP_DOCKER_LOCALNET_IP}';
\$charset='utf8';
\$endless_send=0;
\$re_ask_timer=3;
?>" > /config_goipcrone.php

echo 'start goipcon' 
# export LD_LIBRARY_PATH='$LD_LIBRARY_PATH:/lib_goip'
# exec /usr/local/bin/goipcron /config_goipcrone.php

while true ; do
    CHECK_PORT=`netstat -anu |grep 44444 |wc -l`
    CHECK_PROC=`ps aux  |grep goipcron |grep -v grep |wc -l`
    date
	if [  $CHECK_PORT -eq 0 ]; then
        kill `pidof goipcron` 
		echo "goipcron exit, try start."
        sleep 5
		/usr/local/bin/goipcron /config_goipcrone.php
        
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