-- MySQL dump 10.11
--
-- Host: localhost    Database: scheduler
-- ------------------------------------------------------
-- Server version	5.0.77

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

GRANT all ON scheduler.* TO scheduler@localhost IDENTIFIED BY 'scheduler';
DROP database IF EXISTS `scheduler`;
create database scheduler;
use scheduler;

--
-- Table structure for table `call_record`
--

DROP TABLE IF EXISTS `call_record`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `call_record` (
  `id` int(11) NOT NULL auto_increment,
  `line_name` int(11) NOT NULL,
  `sim_name` int(11) NOT NULL,
  `dir` tinyint(1) NOT NULL default '0',
  `number` varchar(64) NOT NULL,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `duration` int(11) NOT NULL default '-1',
  `iccid` varchar(32),
  `imsi` varchar(32),
  `imei` varchar(15),
  `disconnect_cause` varchar(64),
  `type` int(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `sim_name` (`sim_name`),
  KEY `time` (`time`),
  KEY `duration` (`duration`),
  KEY `line_name` (`line_name`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `call_record`
--

LOCK TABLES `call_record` WRITE;
/*!40000 ALTER TABLE `call_record` DISABLE KEYS */;
/*!40000 ALTER TABLE `call_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_line`
--

DROP TABLE IF EXISTS `device_line`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `device_line` (
  `id` int(11) NOT NULL auto_increment,
  `line_name` int(11) NOT NULL,
  `goip_name` int(11) NOT NULL,
  `goip_team_id` int(11) NOT NULL,
  `line_status` int(11) NOT NULL,
  `gsm_status` int(11) NOT NULL,
  `imei` varchar(15) default NULL,
  `dev_disable` int(11) NOT NULL default '0',
  `sms_client_id` varchar(64) NOT NULL,
  `csq` int(2) NOT NULL default '0',
  `oper` varchar(32) NOT NULL,
  `call_state` varchar(32) NOT NULL,
  `sleep` tinyint(1) NOT NULL default '0',
  `last_call_record_id` int(11) NOT NULL,
  `auto_simulation_id` int(11) NOT NULL,
  `next_auto_dial_time` int(10) unsigned NOT NULL,
  `last_auto_dial_time` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `last_call_record_id` (`last_call_record_id`),
  KEY `line_name` (`line_name`),
  KEY `auto_simulation_id` (`auto_simulation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `device_line`
--

LOCK TABLES `device_line` WRITE;
/*!40000 ALTER TABLE `device_line` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_line` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL auto_increment,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `team_id` int(11) NOT NULL,
  `sim_name` int(11) NOT NULL,
  `line_name` int(11) NOT NULL,
  `type` varchar(64) NOT NULL,
  `log` varchar(64) NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `sim_name` (`sim_name`),
  KEY `line_name` (`line_name`),
  KEY `date` (`date`),
  KEY `team_id` (`team_id`),
  KEY `value` (`value`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rm_device`
--

DROP TABLE IF EXISTS `rm_device`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `rm_device` (
  `id` int(11) NOT NULL auto_increment,
  `type` varchar(32) NOT NULL,
  `name` int(10) unsigned NOT NULL,
  `tag` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  `zone` int(10) unsigned NOT NULL,
  `zone_tag` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `rm_device`
--

LOCK TABLES `rm_device` WRITE;
/*!40000 ALTER TABLE `rm_device` DISABLE KEYS */;
/*!40000 ALTER TABLE `rm_device` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scheduler`
--

DROP TABLE IF EXISTS `scheduler`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `scheduler` (
  `id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL,
  `name` varchar(32) character set utf8 NOT NULL,
  `type` varchar(16) NOT NULL,
  `period_chaos` varchar(1000) NOT NULL,
  `period_fixed` mediumtext,
  `r_interval` int(11) default '0',
  `s_interval` int(11) default '0',
  `period_daily` varchar(1000) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `scheduler`
--

LOCK TABLES `scheduler` WRITE;
/*!40000 ALTER TABLE `scheduler` DISABLE KEYS */;
/*!40000 ALTER TABLE `scheduler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scheduler_tem`
--

DROP TABLE IF EXISTS `scheduler_tem`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `scheduler_tem` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(32) character set utf8 NOT NULL,
  `type` varchar(16) character set utf8 NOT NULL,
  `r_interval` int(11) default NULL,
  `s_interval` int(11) default NULL,
  `period` varchar(1000) character set utf8 default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `scheduler_tem`
--

LOCK TABLES `scheduler_tem` WRITE;
/*!40000 ALTER TABLE `scheduler_tem` DISABLE KEYS */;
/*!40000 ALTER TABLE `scheduler_tem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sim`
--

DROP TABLE IF EXISTS `sim`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `sim` (
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
  `period_limit` varchar(2000) default NULL,
  `time_limit` int(11) default '-1',
  `no_ring_limit` int(11) default '-1',
  `no_answer_limit` int(11) default '-1',
  `short_call_limit` int(11) default '-1',
  `no_ring_remain` int(11) default '-1',
  `no_answer_remain` int(11) default '-1',
  `short_call_remain` int(11) default '-1',
  `short_time` int(11) default '-1',
  `period_time_remain` int(11) default '-1',
  `period_count_remain` int(11) default '-1',
  `sleep` tinyint(1) NOT NULL default '0',
  `no_ring_disable` tinyint(1) NOT NULL default '0',
  `no_answer_disable` tinyint(1) NOT NULL default '0',
  `short_call_disable` tinyint(1) NOT NULL default '0',
  `call_state` int(1) NOT NULL default '0',
  `imsi` varchar(32) NOT NULL,
  `last_imei` varchar(15) default NULL,
  `count_limit` int(11) default '-1',
  `count_remain` int(11) default '-1',
  `no_connected_limit` int(11) default '-1',
  `no_connected_remain` int(11) default '-1',
  `iccid` varchar(32),
  `logout_time` int(11) NOT NULL,
  `s_no_connect_call_c` int(11) NOT NULL,
  `auto_reset_remain` int(1) NOT NULL default '0',
  `auto_reset_remain_s` int(11) NOT NULL default '60',
  `last_call_msg` varchar(256) NOT NULL,
  `count_limit_no_connect` BOOL NOT NULL,
  `remark` varchar(32) NOT NULL,
  `limit_sms` int(11) NOT NULL default '-1',
  `remain_sms` int(11) NOT NULL default '-1',
  `period_remain_sms` int(11) NOT NULL default '-1',
  `month_remain_time` int(11) NOT NULL default '-1',
  `month_limit_time` int(11) NOT NULL default '-1',
  `month_reset_day` int(11) NOT NULL default '1',
  `month_last_reset_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `simnum` VARCHAR( 32 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `sim_name` (`sim_name`),
  KEY `line_name` (`line_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `sim`
--

LOCK TABLES `sim` WRITE;
/*!40000 ALTER TABLE `sim` DISABLE KEYS */;
/*!40000 ALTER TABLE `sim` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sim_bank`
--

DROP TABLE IF EXISTS `sim_bank`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `sim_bank` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` int(10) unsigned NOT NULL,
  `tag` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  `type` varchar(32) NOT NULL default 'SMB32',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `sim_bank`
--

LOCK TABLES `sim_bank` WRITE;
/*!40000 ALTER TABLE `sim_bank` DISABLE KEYS */;
/*!40000 ALTER TABLE `sim_bank` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sim_team`
--

DROP TABLE IF EXISTS `sim_team`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `sim_team` (
  `sim_team_id` int(11) NOT NULL auto_increment,
  `sim_team_name` varchar(64) NOT NULL default '',
  `work_time` int(10) unsigned NOT NULL default '0',
  `sleep_time` int(10) unsigned NOT NULL default '0',
  `imei_random` tinyint(1) NOT NULL default '0',
  `imei_type` int(1) NOT NULL default '0',
  `scheduler_id` int(11) NOT NULL,
  `status` varchar(16) NOT NULL,
  `next_time` varchar(64) NOT NULL,
  PRIMARY KEY  (`sim_team_id`),
  KEY `scheduler_id` (`scheduler_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `sim_team`
--

LOCK TABLES `sim_team` WRITE;
/*!40000 ALTER TABLE `sim_team` DISABLE KEYS */;
/*!40000 ALTER TABLE `sim_team` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system`
--

DROP TABLE IF EXISTS `system`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `system` (
  `sysname` varchar(64) NOT NULL default '',
  `lan` int(1) NOT NULL default '0',
  `bottom_alive` tinyint(1) NOT NULL default '0',
  `warning_remain_time` int(11) NOT NULL default '20',
  `warning_remain_count` int(11) NOT NULL default '10',
  `version` int(11) NOT NULL default '0',
  `auto_disable` tinyint(1) NOT NULL,
  `auto_disable_logout_min` int(11) NOT NULL default '5',
  `auto_disable_s_call` tinyint(1) NOT NULL,
  `auto_disable_s_call_c` int(11) unsigned NOT NULL default '1',
  `auto_disable_s_call_msg` varchar(256) NOT NULL default 'Unassigned (unallocated) number',
  `auto_disable_low_asr` tinyint(1) NOT NULL default '0',
  `auto_disable_asr_number` int(11) unsigned NOT NULL default '10',
  `auto_disable_asr_threshold` int(11) unsigned NOT NULL default '15',
  `auto_disable_low_acd` tinyint(1) NOT NULL default '0',
  `auto_disable_acd_number` int(11) unsigned NOT NULL default '10',
  `auto_disable_acd_threshold` int(11) unsigned NOT NULL default '30',
  `auto_reboot_s_call` tinyint(1) NOT NULL,
  `auto_reboot_s_call_msg` varchar(256) NOT NULL default 'Unassigned (unallocated) number'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `system`
--

LOCK TABLES `system` WRITE;
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
INSERT INTO `system` VALUES ('Simbank Server',3,1,20,10,113,0,5,0,1,'Unassigned (unallocated) number',0, 10, 15, 0, 10, 30, 0, 'Unassigned (unallocated) number');
/*!40000 ALTER TABLE `system` ENABLE KEYS */;
UNLOCK TABLES;

CREATE TABLE IF NOT EXISTS `auto_simulation` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) character set utf8 NOT NULL,
  `disable` tinyint(1) NOT NULL default '0',
  `dial_num` varchar(4000) NOT NULL,
  `period_min` int(11) NOT NULL,
  `period_max` int(11) NOT NULL,
  `talk_time_min` int(11) NOT NULL,
  `talk_time_max` int(11) NOT NULL,
  `next_time` datetime NOT NULL,
  `last_time` datetime NOT NULL,
  `period_type` int(11) NOT NULL default '0',
  `period_setting` varchar(2000) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `imei_db` (
  `id` int(11) NOT NULL auto_increment,
  `imei` varchar(15) NOT NULL,
  `sim_name` int(11) NOT NULL default '0',
  `used` int(11) NOT NULL default '0',
  `used_time` datetime NOT NULL,
  `imsi` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `imei` (`imei`),
  KEY `sim_name` (`sim_name`),
  KEY `used` (`used`),
  KEY `imsi` (`imsi`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `permissions` int(1) NOT NULL default '2',
  `password` varchar(64) NOT NULL default '',
  `info` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `user`
--

CREATE TABLE IF NOT EXISTS `human_ref` (
  `id` int(11) NOT NULL auto_increment,
  `line_id` int(11) NOT NULL,
  `auto_simulation_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `sim_name` (`line_id`,`auto_simulation_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'admin',1,'8801cc331d4af0d20c1cf138672c7115','1111');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-07-08 13:13:35
