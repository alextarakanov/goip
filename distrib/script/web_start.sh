#!/bin/bash

WWW_DIR=/var/www/html

cp -fR /mnt/www/goip $WWW_DIR/goip
cp -fR /mnt/www/smb_scheduler $WWW_DIR/smb
cp -fR /mnt/www/cdr $WWW_DIR/cdr

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
?>" > $WWW_DIR/goip/inc/config.inc.php



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

?>"   > $WWW_DIR/smb/inc/config.inc.php

echo "<?php
return array(
	'db' => array(
		'type' => 'mysql',
		'host' => '${IP_MYSQL}',
		'port' => '3306',
		'user' => '${ASTERISK_MYSQL_USER}',
		'name' => '${ASTERISK_MYSQL_DATABASE}',
		'pass' => '${ASTERISK_MYSQL_PASSWORD}',
		'table' => 'cdr',
		'options' => array(
			//PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		),
	),
	'system' => array(
		'server_mode' => 0,
		'column_name' => 'filename',		
		'tmp_dir' => '/tmp',
		'monitor_dir' => '/home/calls',
		'storage_format' => 1,
		'fsize_exists' => 10,
		'audio_format' => 'mp3',
		'audio_defconv' => 0,
		//'archive_format' => 'zip',
		'csv_delim' => ';',
		'admins' => array(
		),
		'plugins' => array(
			'Расход средств' => 'my_callrates',
		),
	),
	'callrate' => array(
		'enabled' => 1,
		'free_interval' => 3,
		'csv_file' =>  'inc/plugins/my_callrates.csv',
		'currency' =>  '',
	),
	'display' => array(
		'lookup' => array(
			'url' => 'https://zvonok.octo.net/number.aspx/' . '%n',
			'num_length' => 7,
		),
		'main' => array(
			'result_limit' => 100,
			'header_step' => 30,
			'duphide' => 1,
			'rec_play' => 1,			
			'rec_delete' => 1,
			'userfield_edit' => 1,
			'entry_delete' => 1,
			'full_channel' => 0,
			'full_channel_tooltip' => 0,			
		),
		'search' => array(
			'src' => 1,
			'dst' => 1,
			'disposition' => 1,
			'billsec' => 2,
			'duration' => 0,
			'channel' => 2,
			'clid' => 0,
			'did' => 2,
			'dstchannel' => 2,
			'accountcode' => 0,
			'userfield' => 2,
			'lastapp' => 2,
			'chart_cc' => 2,
			'asr_report' => 0,
			'csv' => 2,
			'chart' => 2,
			'minutes_report' => 1,
		),
		'column' => array(
			'did' => 0,
			'durwait' => 1,				
			'billsec' => 1,		
			'duration' => 0,
			'clid' => 0,
			'accountcode' => 0,
			'callrates' => 1,
			'callrates_dst' => 0,
			'channel' => 1,
			'dstchannel' => 1,
			'lastapp' => 1,
			'file' => 1,			
			'userfield' => 1,			
		),
	),
	'site' => array(
		'main' => array(
			'title' => 'Детализация звонков',
			'desc' => 'Детализация звонков',
			'robots' => 'noindex, nofollow',
			'head' => 'Детализация звонков',
			//'logo_path' => 'img/example_logo.png',
			'main_section' => '../',
			'min_width' => '1024px',
			'max_width' => '1400px',
		),
		'js' => array(
			'player_autoplay' => 1,
			'player_title' => 1,
			'player_symbol' => '&#9835;&#9835;&#9835;',
			'scroll_show' => 1,
		),		
	),
	'cdn' => array(
		'css' => array(
			'tooltips' => 'img/simptip.min.css',
			'jquery_contextmenu' => 'img/jquery-contextmenu/jquery.contextMenu.min.css',
		),
		'js' => array(
			'player' => 'img/player.js',
			'player_skin' => 'img/player_skin.js',
			'jquery' => 'img/jquery.min.js',
			'jquery_object' => 'img/jquery.query-object.min.js',
			'clipboard_js' => 'img/clipboard.min.js',
			'jquery_contextmenu' => 'img/jquery-contextmenu/jquery.contextMenu.min.js',
			'jquery_ui_position' => 'img/jquery-contextmenu/jquery.ui.position.min.js',
			'moment_js' => 'img/moment.js/moment.min.js',
			'moment_js_locale' => 'img/moment.js/ru.js',			
		),		
	),	
);
" > /var/www/html/cdr/inc/config/config.php

exec /usr/sbin/apache2ctl -D FOREGROUND
