<?php 
define("OK", true);
require_once("global.php");

foreach($_REQUEST as $key => $value) 
{
	$_REQUEST[$key]=myaddslashes($value);
}
/*
if(!get_magic_quotes_gpc()){
	$_REQUEST[username]=addslashes($_REQUEST[username]);
	$_REQUEST[password]=addslashes($_REQUEST[password]);
}
*/

if(!isset($_SESSION['usertype'])){
	$rs=$db->fetch_array($db->query("SELECT id FROM user WHERE name='".$_REQUEST[username]."' and password='".md5(md5($_REQUEST[password].'dbl').'yzm')."'"));
	if(empty($rs[0])){
                require_once ('login.php');
                exit;
        }
}else {
	require_once ('login.php');
	exit;
}

if($_REQUEST['get']=='sim_line'){
	$query=$db->query("select sim_name from sim");
	echo "sim name:\n";
	while($row=@$db->fetch_array($query)) {
		echo "$row[0]\n";
	}
	$query=$db->query("select line_name from device_line");

	echo "line name:\n";
	while($row=@$db->fetch_array($query)) {
		echo "$row[0]\n";
	}
}
elseif($_REQUEST['get']=='imsi'){
	$query=$db->query("select sim_name,imsi from sim");
	while($row=@$db->fetch_array($query)) {
		echo "$row[0] $row[1]\n";
	}
}
elseif($_REQUEST['get']=='iccid'){
	$query=$db->query("select sim_name,iccid from sim");
	while($row=@$db->fetch_array($query)) {
		echo "$row[0] $row[1]\n";
	}
}
elseif($_REQUEST['get']=='sim'){
        $query=$db->query("select sim_name from sim");
        while($row=@$db->fetch_array($query)) {
                echo "$row[0]\n";
        }
}
elseif($_REQUEST['get']=='line'){
        $query=$db->query("select line_name from device_line");
        while($row=@$db->fetch_array($query)) {
                echo "$row[0]\n";
        }
}
elseif($_REQUEST['get']=='bind'){
	if($_REQUEST[sim]) $where=" and sim_name=$_REQUEST[sim]";
	if($_REQUEST[line]) $where=" and line_name=$_REQUEST[line]";
	$query=$db->query("select sim_name,line_name from sim where line_name != '' $where");
        while($row=@$db->fetch_array($query)) {
                echo "$row[0] $row[1];\n";
        }
}
elseif($_REQUEST['get']=='plan_bind'){
	if($_REQUEST[sim]) $where=" and sim_name=$_REQUEST[sim]";
	if($_REQUEST[line]) $where=" and plan_line_name=$_REQUEST[line]";
        $query=$db->query("select sim_name,plan_line_name from sim where plan_line_name != '' $where");
        while($row=@$db->fetch_array($query)) {
                echo "$row[0] $row[1];\n";
        }
}
elseif($_REQUEST['set']=='bind'){
	$rs_sim=$db->fetch_array($db->query("select * from sim where sim_name='$_REQUEST[sim]'"));
	$rs_line=$db->fetch_array($db->query("select * from device_line where line_name='$_REQUEST[line]'"));
	if(empty($rs_sim[0])){
		echo "ERROR sim_name error!";
		exit;
	}
	if(empty($rs_line[0]) && $_REQUEST[line]!=0){
		echo "ERROR line_name error!";
		exit;
	}
	$rs=$db->fetch_array($db->query("select * from sim where plan_line_name='$_REQUEST[line]'"));
	if($rs[0]){
		$send[]=my_pack2(DEV_BINDING, $rs[sim_name], 0);
		$db->query("update sim set plan_line_name='' where plan_line_name='$_REQUEST[line]'");
	}
	$db->query("update sim set plan_line_name='$_REQUEST[line]' where sim_name='$_REQUEST[sim]'");
        $send[]=my_pack2(DEV_BINDING, $_REQUEST[sim], $_REQUEST[line]);
	echo "OK.bind sim:$_REQUEST[sim]; line:$_REQUEST[line]";
        sendto_xchanged($send);
}
elseif($_REQUEST['set']=='group'){
	if($_REQUEST['sim']) {
		$query=$db->query("select sim_team_id from sim_team where sim_team_name='$_REQUEST[group]'");
		if($row=$db->fetch_array($query)){
			$db->query("update sim set sim_team_id='$row[0]' where sim_name='$_REQUEST[sim]'");
			$send[]=my_pack2(DEV_BINDING, $_REQUEST['sim'], 0);
			$query=$db->query("select sim.*,password from sim left join sim_bank on sim.bank_name=sim_bank.name where sim_name='$_REQUEST[sim]'");
			if($row=$db->fetch_array($query)){
				$send[]=my_pack($row, SIM_ADD);
			}
			sendto_xchanged($send);
			echo "OK";
		}
		else echo "ERROR group error!";
	}
	if($_REQUEST['line']) {
		$query=$db->query("select sim_team_id from sim_team where sim_team_name='$_REQUEST[group]'");
		if($row=$db->fetch_array($query)){
			$db->query("update device_line set goip_team_id='$row[0]' where line_name='$_REQUEST[line]'");
			$query=$db->query("select sim_name from sim where plan_line_name='$_REQUEST[line]' and line_name='$_REQUEST[line]'");
			if($row=$db->fetch_array($query)) {
				$send[]=my_pack2(DEV_BINDING, $row['sim_name'], 0);
			}
			$query=$db->query("select device_line.*,password,zone from device_line left join rm_device on device_line.goip_name = rm_device.name where line_name='$_REQUEST[line]'");
			if($row=$db->fetch_array($query)){
				$send[]=my_pack($row, GOIP_ADD);
			}
			sendto_xchanged($send);
			echo "OK";
		}
		else echo "ERROR group error!";
	}

}
elseif($_REQUEST['set']=='disable' ||$_REQUEST['set']=='enable'){
	
	if($_REQUEST[sim]) {
		$query=$db->query("select id from sim where sim_name='$_REQUEST[sim]'");
		if($row=$db->fetch_array($query)){
			if($_REQUEST['set']=='disable') $db->query("update sim set dev_disable=1 where sim_name='$_REQUEST[sim]'");
			else $db->query("update sim set dev_disable=0 where sim_name='$_REQUEST[sim]'");
			$send[]=my_pack2(($_REQUEST['set']=='disable')?DEV_DISABLE:DEV_ENABLE, $_REQUEST[sim], TYPE_SIM);
			sendto_xchanged($send);
			echo "OK";
		}
		else echo "ERROR sim_name error!";
	}
	if($_REQUEST[line]) {
		$query=$db->query("select id from device_line where line_name='$_REQUEST[line]'");
		if($row=$db->fetch_array($query)){
			if($_REQUEST['set']=='disable') $db->query("update device_line set dev_disable=1 where line_name='$_REQUEST[line]'");
			else $db->query("update device_line set dev_disable=0 where line_name='$_REQUEST[line]'");
			$send[]=my_pack2(($_REQUEST['set']=='disable')?DEV_DISABLE:DEV_ENABLE, $_REQUEST[line], TYPE_GOIP);
			sendto_xchanged($send);
			echo "OK";
		}
		else echo "ERROR line_name error!";
	}
}

elseif($_REQUEST['set']=='imei'){
	if(!$_REQUEST[imei]) {
		echo "ERROR imei error!";
		exit;
	}
	if($_REQUEST[sim]) {
		$rs=$db->fetch_array($db->query("select * from sim where sim_name='$_REQUEST[sim]'"));
		if(empty($rs[0])){
			echo "ERROR sim_name error!";
			exit;
		}
	}
	else if($_REQUEST[line]){
		$rs=$db->fetch_array($db->query("select * from sim where line_name='$_REQUEST[line]'"));
		if(empty($rs[0])){
			echo "ERROR this line not bind sim!";
			exit;
                }
	}
	else {
		echo "ERROR sim_name and line_name none!";
		exit;
	}
	$db->query("update sim set imei_mode='2', imei='$_REQUEST[imei]' where sim_name='$rs[sim_name]'");
	$query=$db->query("select sim.*,password from sim left join sim_bank on sim.bank_name=sim_bank.name where sim_name='$_REQUEST[sim]'");
	if($row=$db->fetch_array($query)) {
		if($row['imei']==NULL) $row['imei'] = "000000000000000";
		$imei_len=strlen($row['imei']);
		if($imei_len<15) $row['imei'] = sprintf("%15s",$row['imei']."000000000000000");
		if($row['remain_time']==NULL || $row['remain_time'] <-1 ) $row['remain_time'] = -1;
		$send[]=my_pack($row, SIM_ADD);
		if($row['line_name']) $send[]=my_pack2(MODULE_REBOOT, $row['line_name']);
		sendto_xchanged($send);
		echo "OK";
	}
	else echo "ERROR sim_name error!";
}
elseif($_REQUEST['reset']=='limit'){

	$db->query("update sim set no_ring_remain=0,no_answer_remain=0,short_call_remain=0,remain_time=time_limit,count_remain=count_limit,no_connected_remain=no_connected_limit");

	$query=$db->query("select sim.*,password from sim left join sim_bank on sim.bank_name=sim_bank.name");
	while($row=$db->fetch_array($query)) {
		if($row['imei']==NULL) $row['imei'] = "000000000000000";
		$imei_len=strlen($row['imei']);
		if($imei_len<15) $row['imei'] = sprintf("%15s",$row['imei']."000000000000000");
		if($row['remain_time']==NULL || $row['remain_time'] <-1 ) $row['remain_time'] = -1;
		$send[]=my_pack($row, SIM_ADD);
	}
	echo "OK.reset limit";
	sendto_xchanged($send);
}
elseif($_REQUEST['get']=="hbfail"){
	if($_REQUEST['t']){
		sscanf($_REQUEST['t'], "%d:%d-%d:%d", $h,$m,$h1,$m1);
		$t=$h*3600+$m*60;
		$t1=$h1*3600+$m1*60;
		$query=$db->query("select log,value from logs where `date`>=(CURDATE()+INTERVAL $t SECOND) and `date`<(CURDATE()+INTERVAL $t1 SECOND) and log like 'auto dial:%'");
	}else $query=$db->query("select log,value from logs where `date`>now()-3600 and log like 'auto dial:%'");
	while($row=$db->fetch_array($query)) {
		if($row['value']=='0') {
			sscanf($row['log'], "auto dial:%[^ ]", $num);
			$num_ok[$num]=1;
		}
		else if($row['value']=='-1'){
			sscanf($row['log'], "auto dial:%[^ ]", $num);
			$num_fail[$num]=1;
		}
	}
	foreach($num_fail as $num => $value){
		//if(!array_key_exists($num, $num_ok)) 
		echo "$num\n";
	}
}
elseif($_REQUEST['get']=='rbin'){
	//if($_REQUEST[sig]) $where=" and csq=$_REQUEST[sig]";
	if($_REQUEST['line']) $where.=" and line_name='$_REQUEST[line]'";
	if($_REQUEST['sig']) $where.=" and csq='$_REQUEST[sig]'";
	if($_REQUEST['op']) $where.=" and oper='$_REQUEST[op]'";
	if($_REQUEST['lst']) $where.=" and line_status='$_REQUEST[lst]'";
	if($_REQUEST['gst']) $where.=" and gsm_status='$_REQUEST[gst]'";
	if($_REQUEST['state']) $where.=" and call_state='$_REQUEST[state]'";
	$query=$db->query("select line_name,csq,oper,line_status,gsm_status,call_state from device_line where line_name != '' $where");
	while($row=@$db->fetch_array($query)) {
		//echo "$row[0] $row[1] $row[2] $row[3] $row[4] $row[5];\n";
		echo $row[0].",".$row[1].",".$row[2].",".$row[3].",".$row[4].",".$row[5].";";
	}
}
elseif($_REQUEST['set']=="dial"){
	if(!$_REQUEST['line']) die("ERROR none line");
	if(!$_REQUEST['num']) die("ERROR none number");
	if(!$_REQUEST['duration']) die("ERROR none duration");
	$query=$db->query("select * from device_line where line_name='$_REQUEST[line]' and gsm_status=31 and (call_state='IDLE' or call_state='')");
	if($row=@$db->fetch_array($query)) {
		$send[]=my_pack2(AUTO_DIAL, $_REQUEST['line'], $_REQUEST['duration'], $_REQUEST['num']);
		sendto_xchanged($send);
		$db->query("insert into logs set line_name='$_REQUEST[line]', log='api dial:$_REQUEST[num] duration:$_REQUEST[duration]'");
		echo "OK.Do dial line:$_REQUEST[line], num:$_REQUEST[num],duration:$_REQUEST[duration]";
	}else echo "ERROR not find that line or busy";

}
elseif($_REQUEST['get']=="apidial"){
	if($_REQUEST['t']){
		sscanf($_REQUEST['t'], "%d:%d-%d:%d", $h,$m,$h1,$m1);
		$t=$h*3600+$m*60;
		$t1=$h1*3600+$m1*60;
		$query=$db->query("select * from call_record where `time`>=(CURDATE()+INTERVAL $t SECOND) and `time`<(CURDATE()+INTERVAL $t1 SECOND) and type=2 order by id desc");
	}else $query=$db->query("select * from call_record where `time`>now()-3600 and type=2 order by id desc");
	echo "Time,Line,Number,Duration:\n";
	while($row=$db->fetch_array($query)) {
		echo "$row[time],$row[line_name],$row[number],$row[duration]\n";
	}
}
?>
