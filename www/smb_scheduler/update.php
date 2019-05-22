<?php
	error_reporting(0);
	define("OK", true);
	require_once("global.php");
	/*1.00.1 201109*/
	@$db->updatequery("ALTER TABLE `goip` ADD `dev_disable` tinyint(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `sim_team` ADD `imei_random` tinyint(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `simbank` ADD `dev_disable` tinyint(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `simbank` ADD `plan_line_name` int(10) unsigned NOT NULL default '0'");
	//@$db->updatequery("ALTER TABLE `simbank` ADD INDEX ( `plan_line_name` )");

	/*1.00.2 201109*/
	@$db->updatequery("ALTER TABLE `goip` ADD `goip_tag` VARCHAR( 30 )");
	@$db->updatequery("ALTER TABLE `goip` ADD `zone_tag` VARCHAR( 30 )");
	@$db->updatequery("ALTER TABLE `simbank` ADD `simbank_tag` VARCHAR( 30 )");
	
	/*1.00.3 201110 */
	@$db->updatequery("ALTER TABLE `simbank` ADD `imei` VARCHAR( 15 )");
	@$db->updatequery("ALTER TABLE `simbank` ADD `remain_time` int(11) NOT NULL default '-1'");
	@$db->updatequery("ALTER TABLE `sim_team` ADD `imei_type` int(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `simbank` ADD `time_unit` int(11) NOT NULL default '60'");

	/*1.01 201202*/
	$rs=@$db->fetch_array(@$db->updatequery("select version from system"));
	if(!$rs || $rs[0] < 101){ //version 1.01 must add table 
		//echo "$rs[0] 11111<br>";
                @$db->updatequery("CREATE TABLE `device_line` (
  `id` int(11) NOT NULL auto_increment,
  `line_name` int(11) NOT NULL,
  `goip_name` int(11) NOT NULL,
  `goip_team_id` int(11) NOT NULL,
  `line_status` int(11) NOT NULL,
  `gsm_status` int(11) NOT NULL,
  `imei` varchar(15) default NULL,
  `dev_disable` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
                @$db->updatequery("CREATE TABLE `rm_device` (
  `id` int(11) NOT NULL auto_increment,
  `type` varchar(32) NOT NULL,
  `name` int(10) unsigned NOT NULL,
  `tag` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  `zone` int(10) unsigned NOT NULL,
  `zone_tag` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
                @$db->updatequery("CREATE TABLE `sim` (
  `id` int(11) NOT NULL auto_increment,
  `sim_name` int(11) NOT NULL default '0',
  `bank_name` int(11) NOT NULL,
  `sim_login` int(11) NOT NULL default '0',
  `sim_team_id` int(11) NOT NULL default '0',
  `goipid` int(11) NOT NULL default '0',
  `line_name` int(11) NOT NULL default '0',
  `dev_disable` int(11) NOT NULL default '0',
  `plan_line_name` int(11) NOT NULL default '0',
  `imei_mode` int(11) NOT NULL default '0',
  `imei` varchar(15) default NULL,
  `remain_time` int(11) NOT NULL default '-1',
  `time_unit` int(11) NOT NULL default '60',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
                @$db->updatequery("CREATE TABLE `sim_bank` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` int(10) unsigned NOT NULL,
  `tag` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		$query=@$db->updatequery("select * from goip order by line_name");
		while($row=$db->fetch_array($query)){
			switch($row[goip_type]){
				case 1:
					$row[type]='GoIPx1';
					break;
				case 4:
					$row[type]='GoIPx4';
					break;
				case 8:
					$row[type]='GoIPx8';
					break;
				case 16:
					$row[type]='GoIPx16';
					break;
			}
			if($row[goip_name]!=$name){
				$name=$row[goip_name];
				$sql_rm.="('$row[type]','$row[goip_name]','$row[goip_tag]','$row[goip_pass]',$row[goip_zone],'$row[zone_tag]'),";
			}
			$sql_line.="('$row[line_name]','$row[goip_name]','$row[goip_team_id]','$row[line_status]','$row[gsm_status]','$row[imei]','$row[dev_disable]'),";
		}
		if($sql_rm) {
			$sql_rm[strlen($sql_rm)-1]=' ';
			$sql_line[strlen($sql_line)-1]=' ';
			$sql_rm="insert into rm_device (type,name,tag,password,zone,zone_tag) values ".$sql_rm;
			$sql_line="insert into device_line (line_name,goip_name,goip_team_id,line_status,gsm_status,imei,dev_disable) values ".$sql_line;
			@$db->updatequery($sql_rm);
			@$db->updatequery($sql_line);
		}
		//echo $sql_rm;
		//echo $sql_line;
		$query=@$db->updatequery("select * from simbank order by sim_name");
		while($row=$db->fetch_array($query)){
			if($row[bank_name]!=$bank_name){
				$bank_name=$row[bank_name];
				$sql_bank.="('$row[bank_name]','$row[simbank_tag]','$row[bank_pass]'),";
			}
			$sql_slot.="('$row[sim_name]','$row[bank_name]','$row[sim_team_id]','$row[sim_login]','$row[goipid]','$row[line_name]','$row[plan_line_name]','$row[remain_time]','$row[time_unit]',$row[dev_disable]),";
		}
		if($sql_bank) {
			$sql_bank[strlen($sql_bank)-1]=' ';
			$sql_slot[strlen($sql_slot)-1]=' ';
			$sql_bank="insert into sim_bank (name,tag,password) values ".$sql_bank;
			$sql_slot="insert into sim (sim_name,bank_name,sim_team_id,sim_login,goipid,line_name,plan_line_name,remain_time,time_unit,dev_disable) values ".$sql_slot;
			@$db->updatequery($sql_bank);
			@$db->updatequery($sql_slot);
		}
		//echo $sql_bank;
		//echo $sql_slot;
		@$db->updatequery("ALTER TABLE `system` ADD `version` int(11) NOT NULL default '0'");
		@$db->updatequery("update `system` set `version`='101'");
	}//endif version<101

	/*201207 v1.2*/
	if(!$rs || $rs[0] < 102){
                @$db->updatequery("CREATE TABLE `call_record` (
  `id` int(11) NOT NULL auto_increment,
  `line_name` int(11) NOT NULL,
  `sim_name` int(11) NOT NULL,
  `dir` tinyint(1) NOT NULL default '0',
  `number` varchar(64) NOT NULL,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `duration` int(11) NOT NULL default '-1',
  PRIMARY KEY  (`id`),
  KEY `line_name` (`line_name`,`sim_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");	

		@$db->updatequery("CREATE TABLE `logs` (
  `id` int(11) NOT NULL auto_increment,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `team_id` int(11) NOT NULL,
  `sim_name` int(11) NOT NULL,
  `line_name` int(11) NOT NULL,
  `type` varchar(64) NOT NULL,
  `log` varchar(64) NOT NULL,
  `value` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `team_id` (`team_id`,`sim_name`,`line_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

		@$db->updatequery("CREATE TABLE `scheduler` (
  `id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL,
  `name` varchar(32) character set utf8 NOT NULL,
  `type` varchar(16) NOT NULL,
  `period_chaos` varchar(1000) NOT NULL,
  `period_fixed` mediumtext,
  `r_interval` int(11) default '0',
  `s_interval` int(11) default '0',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

		@$db->updatequery("CREATE TABLE `scheduler_tem` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(32) character set utf8 NOT NULL,
  `type` varchar(16) character set utf8 NOT NULL,
  `r_interval` int(11) default NULL,
  `s_interval` int(11) default NULL,
  `period` varchar(1000) character set utf8 default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

		@$db->updatequery("ALTER TABLE `device_line` ADD `sms_client_id` varchar(64) NOT NULL");
		@$db->updatequery("ALTER TABLE `device_line` ADD `csq` int(2) NOT NULL default '0'");
		@$db->updatequery("ALTER TABLE `device_line` ADD `oper` varchar(32) NOT NULL");
		@$db->updatequery("ALTER TABLE `device_line` ADD `call_state` varchar(32) NOT NULL");
		@$db->updatequery("ALTER TABLE `device_line` ADD `sleep` tinyint(1) NOT NULL default '0'");

		@$db->updatequery("ALTER TABLE `sim` ADD `period_limit` varchar(2000) default NULL");
		@$db->updatequery("ALTER TABLE `sim` ADD `time_limit` int(11) default '-1'");
		@$db->updatequery("ALTER TABLE `sim` ADD `no_ring_limit` int(11) default '-1'");
		@$db->updatequery("ALTER TABLE `sim` ADD `no_answer_limit` int(11) default '-1'");
		@$db->updatequery("ALTER TABLE `sim` ADD `short_call_limit` int(11) default '-1'");
		@$db->updatequery("ALTER TABLE `sim` ADD `no_ring_remain` int(11) default '-1'");
		@$db->updatequery("ALTER TABLE `sim` ADD `no_answer_remain` int(11) default '-1'");
		@$db->updatequery("ALTER TABLE `sim` ADD `short_call_remain` int(11) default '-1'");
		@$db->updatequery("ALTER TABLE `sim` ADD `short_time` int(11) default '-1'");
		@$db->updatequery("ALTER TABLE `sim` ADD `period_time_remain` int(11) default '-1'");
		@$db->updatequery("ALTER TABLE `sim` ADD `period_count_remain` int(11) default '-1'");
		@$db->updatequery("ALTER TABLE `sim` ADD `sleep` tinyint(1) NOT NULL default '0'");

		@$db->updatequery("ALTER TABLE `sim_team` ADD `scheduler_id` int(11) NOT NULL");
		@$db->updatequery("ALTER TABLE `sim_team` ADD `status` varchar(16) NOT NULL");
		@$db->updatequery("ALTER TABLE `sim_team` ADD `next_time` varchar(64) NOT NULL");

		$query=@$db->updatequery("select * from sim_team order by sim_team_id");
		while($row=$db->fetch_array($query)){
			@$db->updatequery("insert into scheduler set group_id='$row[sim_team_id]', name='$row[sim_team_name]',type='cycle', r_interval='$row[work_time]',s_interval='$row[sleep_time]'");
			$insert_id=$db->fetch_array(@$db->updatequery("SELECT LAST_INSERT_ID()"));
			@$db->updatequery("update sim_team set scheduler_id=$insert_id[0] where sim_team_id='$row[sim_team_id]'");
		}
		//@$db->updatequery("update `sim` set `time_limit`='remain_time'");
		@$db->updatequery("update `system` set `version`='102'");

	}//endif version<102
	@$db->updatequery("ALTER TABLE `sim` ADD `no_ring_disable` tinyint(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `sim` ADD `no_answer_disable` tinyint(1) NOT NULL default '0'"); 
	@$db->updatequery("ALTER TABLE `sim` ADD `short_call_disable` tinyint(1) NOT NULL default '0'"); 
	@$db->updatequery("ALTER TABLE `sim` ADD `call_state` int(1) NOT NULL default '0'"); 	

	@$db->updatequery("ALTER TABLE `sim` ADD `imsi` varchar(32) NOT NULL");
	@$db->updatequery("ALTER TABLE `sim` ADD `last_imei` varchar(15) default NULL");
	@$db->updatequery("ALTER TABLE `scheduler` ADD `period_daily` varchar(1000) NOT NULL");
/*
	$rs=@$db->fetch_array(@$db->updatequery("select lan from system"));
	if($rs[0]) echo "$rs[0]<br>";
*/
	/*v1.2beta11 1212*/
	@$db->updatequery("ALTER TABLE `sim` ADD `count_limit` int(11) default '-1'");
	@$db->updatequery("ALTER TABLE `sim` ADD `count_remain` int(11) default '-1'");
	@$db->updatequery("ALTER TABLE `sim` ADD `no_connected_limit` int(11) default '-1'");
	@$db->updatequery("ALTER TABLE `sim` ADD `no_connected_remain` int(11) default '-1'");
	

	/*1301*/
	@$db->updatequery("ALTER TABLE `sim` ADD `iccid` varchar(32) ");
	@$db->updatequery("ALTER TABLE `call_record` ADD `iccid` varchar(32) ");
	@$db->updatequery("ALTER TABLE `call_record` ADD `imsi` varchar(32) ");
	@$db->updatequery("ALTER TABLE `call_record` ADD `imei` varchar(15) ");
	@$db->updatequery("ALTER TABLE `call_record` ADD `disconnect_cause` varchar(64) ");
	if(!$rs || $rs[0] < 103){	
		@$db->updatequery("update `system` set `version`='103'");
		@$db->updatequery("ALTER TABLE `logs` ADD INDEX (`sim_name`)");
		@$db->updatequery("ALTER TABLE `logs` ADD INDEX (`line_name`)");
		@$db->updatequery("ALTER TABLE `logs` ADD INDEX (`team_id`)");
		@$db->updatequery("ALTER TABLE `logs` ADD INDEX (`date`)");
		
		@$db->updatequery("ALTER TABLE `call_record` ADD INDEX (`sim_name`)");
		@$db->updatequery("ALTER TABLE `call_record` ADD INDEX (`line_name`)");
		@$db->updatequery("ALTER TABLE `call_record` ADD INDEX (`time`)");
		
	}


	/*1303 v1.3-t2*/
	@$db->updatequery("ALTER TABLE `device_line` ADD `last_call_record_id` int(11) NOT NULL");
	if(!$rs || $rs[0] < 104){
		@$db->updatequery("update `system` set `version`='104'");
		@$db->updatequery("ALTER TABLE `call_record` ADD INDEX (`duration`)");
		@$db->updatequery("ALTER TABLE `device_line` ADD INDEX (`line_name`)");
		@$db->updatequery("ALTER TABLE `device_line` ADD INDEX (`last_call_record_id`)");
		@$db->updatequery("ALTER TABLE `sim` ADD INDEX (`sim_name`)");
		@$db->updatequery("ALTER TABLE `sim` ADD INDEX (`line_name`)");
		
	}

	/*v1.5*/
        @$db->updatequery("ALTER TABLE `system` ADD `auto_disable` tinyint(1) NOT NULL");
        @$db->updatequery("ALTER TABLE `system` ADD `auto_disable_logout_min` int(11) NOT NULL default '5'");
        @$db->updatequery("ALTER TABLE `sim` ADD `logout_time` int(11) NOT NULL");
	@$db->updatequery("ALTER TABLE `system` ADD `warning_remain_time` int(11) NOT NULL default '20'");
	@$db->updatequery("ALTER TABLE `system` ADD `warning_remain_count` int(11) NOT NULL default '10'");
        @$db->updatequery("ALTER TABLE `system` ADD `auto_disable_s_call` tinyint(1) NOT NULL");
        @$db->updatequery("ALTER TABLE `system` ADD `auto_disable_s_call_c` int(11) NOT NULL default '1'");
	@$db->updatequery("ALTER TABLE `system` ADD `auto_disable_s_call_msg` varchar(256) NOT NULL default 'Unassigned (unallocated) number'");
	@$db->updatequery("ALTER TABLE `system` ADD `auto_disable_low_asr` tinyint(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `system` ADD `auto_disable_asr_number` int(11) unsigned NOT NULL default '10'");
	@$db->updatequery("ALTER TABLE `system` ADD `auto_disable_asr_threshold` int(11) unsigned NOT NULL default '15'");
	@$db->updatequery("ALTER TABLE `system` ADD `auto_disable_low_acd` tinyint(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `system` ADD `auto_disable_acd_number` int(11) unsigned NOT NULL default '10'");
	@$db->updatequery("ALTER TABLE `system` ADD `auto_disable_acd_threshold` int(11) unsigned NOT NULL default '30'");

	@$db->updatequery("ALTER TABLE `sim` ADD `s_no_connect_call_c` int(11) NOT NULL");
	@$db->updatequery("ALTER TABLE `sim` ADD `auto_reset_remain` int(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `sim` ADD `auto_reset_remain_s` int(11) NOT NULL default '60'");
	@$db->updatequery("ALTER TABLE `sim_bank` ADD  `type` varchar(32) NOT NULL default 'SMB32'");

	@$db->updatequery("ALTER TABLE  `system` CHANGE  `auto_disable_s_call_msg`  `auto_disable_s_call_msg` VARCHAR( 256 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Unassigned (unallocated) number'");

	@$db->updatequery("ALTER TABLE `sim` ADD `last_call_msg` varchar(256) NOT NULL");

/*1.6*/
	if(!$rs || $rs[0] < 106){
		@$db->updatequery("update `system` set `version`='106'");
		@$db->updatequery("CREATE TABLE IF NOT EXISTS `auto_simulation` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) character set utf8 NOT NULL,
  `disable` tinyint(1) NOT NULL default '0',
  `dial_num` varchar(256) NOT NULL,
  `period_min` int(11) NOT NULL,
  `period_max` int(11) NOT NULL,
  `talk_time_min` int(11) NOT NULL,
  `talk_time_max` int(11) NOT NULL,
  `next_time` datetime NOT NULL,
  `last_time` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
		@$db->updatequery("ALTER TABLE `device_line` ADD `auto_simulation_id` int(11) NOT NULL");
		@$db->updatequery("ALTER TABLE `device_line` ADD `next_auto_dial_time` int(10) unsigned NOT NULL");
		@$db->updatequery("ALTER TABLE `device_line` ADD `last_auto_dial_time` datetime NOT NULL");
		@$db->updatequery("ALTER TABLE `device_line` ADD INDEX (`auto_simulation_id`)");
	}
	@$db->updatequery("CREATE TABLE IF NOT EXISTS `imei_db` (
  `id` int(11) NOT NULL auto_increment,
  `imei` varchar(15) NOT NULL,
  `sim_name` int(11) NOT NULL default '0',
  `used` int(11) NOT NULL default '0',
  `used_time` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `sim_name` (`sim_name`),
  KEY `used` (`used`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

/*2015-3-26 18:50:52 */
	if(!$rs || $rs[0] < 107){
		@$db->updatequery("update `system` set `version`='108'");
		@$db->updatequery("ALTER TABLE `imei_db` ADD `imsi` varchar(32) NOT NULL");
		@$db->updatequery("ALTER TABLE `imei_db` ADD INDEX (`imsi`)");
	}

/*1.6.1 */
	@$db->updatequery("ALTER TABLE  `auto_simulation` CHANGE  `dial_num`  `dial_num` VARCHAR( 4000 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");
	@$db->updatequery("ALTER TABLE  `auto_simulation` ADD `period_type` int(11) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE  `auto_simulation` ADD `period_setting` varchar(2000) NOT NULL");

/*1.6.3 2015-6-15 17:21:37*/
	if(!$rs || $rs[0] < 109){
		@$db->updatequery("update `system` set `version`='109'");
        	@$db->updatequery("CREATE TABLE IF NOT EXISTS `human_ref` (
  `id` int(11) NOT NULL auto_increment,
  `line_id` int(11) NOT NULL,
  `auto_simulation_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `sim_name` (`line_id`,`auto_simulation_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
		$query=@$db->updatequery("select id,auto_simulation_id from device_line where auto_simulation_id!=0");
		while($row=$db->fetch_array($query)){
			$db->updatequery("insert into human_ref set line_id='$row[id]', auto_simulation_id='$row[auto_simulation_id]'");
		}
	}
	
/*1.6.4 add hb fail*/
	@$db->updatequery("ALTER TABLE  `logs` CHANGE  `value`  `value` INT( 32 ) NOT NULL");
	if(!$rs || $rs[0] < 110){
		@$db->updatequery("ALTER TABLE `logs` ADD INDEX (`value`)");
		@$db->updatequery("update `system` set `version`='110'");
	}
	
/*1.6.5*/
        @$db->updatequery("ALTER TABLE `system` ADD `auto_reboot_s_call` tinyint(1) NOT NULL");
	@$db->updatequery("ALTER TABLE `system` ADD `auto_reboot_s_call_msg` varchar(256) NOT NULL default 'Unassigned (unallocated) number'");
	
/*1.6.6 add api of get iccid ,imsi and change group; add sim period buffer*/
	
/*1.7 fixed memory of xchanged */

/*1.7 add no connected call in call limit */
	@$db->updatequery("ALTER TABLE  `sim` ADD  `count_limit_no_connect` BOOL NOT NULL");
/*1.7.2 fixed bug of set fixed IMEI*/
/*1.7.2-t add dial api*/
	@$db->updatequery("ALTER TABLE  `call_record` ADD  `type` int(1) NOT NULL");
	if(!$rs || $rs[0] < 111){
		@$db->updatequery("ALTER TABLE `call_record` ADD INDEX (`type`)");
		@$db->updatequery("update `system` set `version`='111'");
	}
/*1.8 add limit sms*/
	@$db->updatequery("ALTER TABLE  `sim` ADD `limit_sms` int(11) NOT NULL default '-1'");
	@$db->updatequery("ALTER TABLE  `sim` ADD `remain_sms` int(11) NOT NULL default '-1'");
	@$db->updatequery("ALTER TABLE  `sim` ADD `period_remain_sms` int(11) NOT NULL default '-1'");
	if(!$rs || $rs[0] < 112){
		$query=@$db->updatequery("select period_limit,id from sim where 1");
		while($row=$db->fetch_array($query)){
			if($row['period_limit']){
				$limit_all="";
				$a=explode(";", $row['period_limit']);
				foreach($a as $limit){ //each period
					if(strlen($limit ))$limit_all.=$limit.",-1;";
				}
				//echo "update sim set period_limit='".$limit_all."' where id=$row[id]";
				if(strlen($limit_all)) @$db->updatequery("update sim set period_limit='".$limit_all."' where id=$row[id]");
			}
		}
		
		@$db->updatequery("update `system` set `version`='112'");
	}


/*
	for($i=1;$i<=100;$i++){
			$name="dbl".$i;
                        $db->query("INSERT INTO `sim_team` (sim_team_name) value ('$name') ");
                        $groupiddb=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
                        $groupid=$groupiddb[0];
                        $db->query("INSERT INTO `scheduler` (name,group_id,type) value ('$name', '$groupid','cycle') ");
                        $schiddb=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
                        $schid=$schiddb[0];
                        $db->query("update sim_team set scheduler_id='$schid' where sim_team_id='$groupid'");
	}
*/
/*1.8.1-t1 disable select auto dial*/
/*1.9 add count no connected call, disable call logs*/
/*1.9.1 add fd limit*/

/*1.9.2 add imei_imsi file*/
        if(!$rs || $rs[0] < 113){
                @$db->updatequery("ALTER TABLE `imei_db` ADD UNIQUE ( `imei` ) ");
                @$db->updatequery("update `system` set `version`='113'");
        }
	@$db->updatequery("ALTER TABLE `sim` ADD `month_remain_time` INT NOT NULL DEFAULT '-1'");
	@$db->updatequery("ALTER TABLE `sim` ADD `month_limit_time` INT NOT NULL DEFAULT '-1'");
	@$db->updatequery("ALTER TABLE `sim` ADD `month_reset_day` INT NOT NULL DEFAULT '1'");
	@$db->updatequery("ALTER TABLE `sim` ADD `month_last_reset_time` timestamp NOT NULL default CURRENT_TIMESTAMP");
/* 1.10-t1 set IMEI befor bind when IMEI MODE=IMEI-IMSI datebase */
/* 1.10-t2 add simnum*/
	@$db->updatequery("ALTER TABLE `sim` ADD `simnum` VARCHAR( 32 ) NOT NULL");
/* 1.10-t3 fixed bug of dial in api.php */
/* 1.11 fixed bug of period time*/
/* 1.12 remove &$ */
/* 1.13 fixed bug of 1.11 */
/* 1.14 fixed bug of month remain time*/
/* 1.15 fixed bug of include excel class */
/* 1.16 fixed bug of remain count when add  */
	echo "update done! <a href=''  target=_top>Return</a>";

?>
