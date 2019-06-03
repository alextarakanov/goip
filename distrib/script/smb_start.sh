#!/bin/bash

echo 'start goipcon' 

echo "server=${XCHANGED_DOCKER_LOCALNET_IP};" > /etc/smbsvr.conf

while true ; do
    CHECK_PROC=`ps axf | grep '/usr/local/bin/smb_scheduler' | grep -v grep | wc -l`
	if [  $CHECK_PROC -lt 1 ]; then
        echo 'kill process smb_scheduler'
        kill `pidof smb_scheduler` 
        sleep 1
        echo "start process smb_scheduler"
        exec /usr/local/bin/smb_scheduler 
        sleep 1
    else
        date
        echo "smb_scheduler is runing";
	fi
	sleep 10
    
done