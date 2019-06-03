#!/bin/bash

echo 'start goipcon' 
echo "
<?php 
\$dbhost='${IP_MYSQL}';	//database server 
\$dbuser='${SMB_MYSQL_USER}';		//database username 
\$dbpw='${SMB_MYSQL_PASSWORD}';		//database password 
\$dbname='${SMB_MYSQL_DATABASE}';		//database name
\$goipcronport='44444';  //xchange port
\$msvrport='${SMB_XCHANGE_SMBMSVR_UDP}';
\$phpsvrport='${SMB_XCHANGE_PHPSVR_UDP}';
\$disable_log='0';
\$disable_call_record='0';
\$smbdocker='${XCHANGED_DOCKER_LOCALNET_IP}';

\$checksum=0x56781234;
\$version='V1.9';
\$bdate='Build 201712';
\$vflag='112';

define('__FOR_ARM__', 1);
define('TYPE_SIM', 1);
define('TYPE_GOIP', 2);
define('TYPE_RULE', 3);

define('SIM_ADD', 10);
define('GOIP_ADD', 11);
define('DEV_ADD', 1);
define('DEV_DEL', 2);

define('SCH_UPDATE', 3);
define('MACHINE_REBOOT', 7);
define('MODULE_REBOOT', 8);

define('DEV_ENABLE', 40);
define('DEV_DISABLE', 41);

define('DEV_BINDING', 50);

define('SIM_PERIOD', 80);
define('SCH_CTL', 81);
define('CHECK_RUNNING', 90);


define('DEV_NETC', 70);
define('DEV_NETCHECK', 71);
define('RESET_LIMIT', 73);
define('DEV_ACTIVING', 74);

define('DEV_CALLED_TIME', 60);
define('LOGS', 91);
define('IMEI', 81);
define('SMS_CLIENT_ID', 82);

define('IMEI_IMSI_INFO', 75);
define('AUTO_DIAL', 131);

?>" > /config_xchange.conf

# exec export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/lib_goip 

while true ; do
    CHECK_PROC=`ps axf | grep '/usr/local/bin/xchanged' | grep -v grep | wc -l`
	if [  $CHECK_PROC -lt 1 ]; then
        echo 'kill process xchanged'
        kill `pidof xchanged` 
        sleep 1
		echo "start process xchanged"
		exec /usr/local/bin/xchanged -f /config_xchange.conf
        sleep 1
    else
        date
        echo "smb_scheduler is runing";
	fi
	sleep 10
    
done