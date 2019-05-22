<?php

define("OK", true);
session_start();
if(!isset($_SESSION['usertype'])){
        require_once ('login.php');
        exit;
}
require_once("global.php");
//!defined('OK') && exit('ForbIdden');
//$UserName=$_COOKIE['adminname'];
/*
function sendto_xchanged($send)
{
	global $phpsvrport;
	//echo $phpsvrport;
	//if(!$phpsvrport) $phpsvrport=44444;
	$flag=0;

	if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {
		echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
		exit;
	}
	foreach($send as $sendbuf){
		//echo "s:$sendbuf,".strlen($sendbuf);
		if (socket_sendto($socket,$sendbuf, strlen($sendbuf), 0, $smbdocker, $phpsvrport)===false)
			echo ("sendto error");
		for($i=0;$i<3;$i++){
			$read=array($socket);
			$err=socket_select($read, $write = NULL, $except = NULL, 5);
			if($err>0){		
				if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port))==false){
					//echo("recvform error".socket_strerror($ret)."<br>");
					continue;
				}
				else{
					if($buf==$sendbuf){
						echo "get !";
						$flag=1;
						break;
					}
				}
			}
		}
	}
	if($flag)
		echo "已更新";
	else 
		echo "已更新,但xchanged进程未响应，请检查该进程";
}
*/
if(isset($_GET['action'])) {
	//if($_GET['action'] != "modifyself" && $_GET['action'] != "savemodifyself" && $_COOKIE['adminname']!="admin" )
	//WriteErrMsg("<br><li>需要admin权限!</li>");
	$action=$_GET['action'];

	if($action=="del")
	{
		$ErrMsg="";
/*
		$Id=$_GET['id'];

		if(empty($Id)){
			$num=$_POST['boxs'];
			for($i=0;$i<$num;$i++)
			{	
				if(!empty($_POST["Id$i"])){
					if($Id=="")
						$Id=$_POST["Id$i"];
					else
						$Id=$_POST["Id$i"].",$Id";
				}
			}
		}
		//WriteErrMsg($num."$Id");
*/
		$name=myaddslashes($_GET['name']);
		//print_r($name);
		if(empty($name))
			$ErrMsg ='<br><li>Please choose one</li>';
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{

			$query=$db->query("select * from goip where goip_name='$name'");
			while($row=$db->fetch_array($query)) {
				$send[]=pack('LCLSC', $checksum, DEV_DEL, $row[line_name], $row[goip_team_id],TYPE_GOIP);
			}
			$query=$db->query("DELETE FROM goip WHERE goip_name='$name'");
			sendto_xchanged($send);
			WriteSuccessMsg("<br><li>Delete success</li>","goip.php");

		}
		//$id=$_GET['id'];
		//$query=$db->query("Delete  from ".$tablepre."admin WHERE Id=$id");
	}
	elseif($action=="add")
	{

		$query=$db->query("select * from sim_team order by sim_team_id ");
		while($row=$db->fetch_array($query)) {
			$prsdb[]=$row;
		}		

	}
	elseif($action=="modify")
	{
		$name=$_GET['name'];

		$query=$db->query("select * from sim_team order by sim_team_id ");
		while($row=$db->fetch_array($query)) {
			$prsdb[]=$row;
		}

		$rs=$db->fetch_array($db->query("SELECT * FROM goip where line_name='$name'"));
		if($rs[dev_disable]) $ck2='selected';
		else $ck1='selected';

		$query=$db->query("select sim_name,plan_line_name from simbank where sim_team_id='0' and (plan_line_name='0' or plan_line_name='$name')  order by sim_name");
		while($row=$db->fetch_array($query)) {
			$grsdb[]=$row;
		}
		//if(!$s[0])
		//WriteErrMsg("<br><li>添加用户需要admin权限</li>"."$row[1]");
	}
	elseif($action=="saveadd")
	{
		//WriteErrMsg("'$_POST['name']'");
		$name=$_POST['name'];
		$password=$_POST['Password'];
		//$provider=$_POST['provider'];
		$goiptype=$_POST['goiptype'];
		$team_id=$_POST['team_id'];
		$zone=$_POST['zone'];
		//$info=$_POST['info'];
		$ErrMsg="";
		if(empty($name))
			$ErrMsg ='<br><li>please input name</li>';
		if(empty($password))
			$ErrMsg ='<br><li>please input pasword</li>';
		//if($name != $_POST['oldname']){
		$no_t=$db->fetch_array($db->query("select goipid from goip where goip_name='".$name."'"));
		if($no_t[0])
			$ErrMsg	.='<br><li>This ID already exist: '.$name.'</li>';
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			if($goiptype != 4 && $goiptype != 8) $goiptype = 1;
			$sql="insert into goip (goip_name,goip_pass,goip_type,line_name,goip_team_id,goip_zone,goip_tag,zone_tag) values ";
			for($i=1;$i<$goiptype;$i++){
				$sql.="('$name', '$password', $goiptype, '$name".sprintf("%02d",$i)."','$team_id', '$zone','$_POST[goip_tag]','$_POST[zone_tag]'),";
				$send[]=pack('LCLSSCa15lla*', $checksum, DEV_ADD, "$name".sprintf("%02d",$i),$team_id,$zone,TYPE_GOIP,"000000000000000",-1, 0, $password);
			}
			$sql.="('$name', '$password', $goiptype, '$name".sprintf("%02d",$i)."','$team_id','$zone','$_POST[goip_tag]','$_POST[zone_tag]')";
			$send[]=pack('LCLSSCa15lla*', $checksum, DEV_ADD, "$name".sprintf("%02d",$i),$team_id,$zone,TYPE_GOIP,"000000000000000", -1,0, $password);
			//echo $sql;
			$db->query($sql);
			//print_r($send);
			sendto_xchanged($send);
			//$query=$db->query("INSERT INTO goip (name,password,provider) VALUES ('$name','$password','$provider')");
			//sendto_cron(); 
			WriteSuccessMsg("<br><li>Add success</li>","goip.php");				
		}
	}
	elseif($action=="savemodify")
	{
		$password=$_POST['Password'];
		$name=$_POST['name'];
		$oldname=$_POST['oldname'];
		$team_id=$_POST['team_id'];
		$old_team_id=$_POST['old_team_id'];
		$id=$_GET['id'];
		$zone=$_POST['zone'];
		$oldzone = $_POST['old_zone'];
		$dev_disable = $_POST['dev_disable'];
		$sim_name=$_POST['sim_name'];
		//$name=$_POST['name'];
		//echo "$_POST[zone], $_PSOT[old_zone]<br>";
		$ErrMsg="";
		/*
		   if($oldname != $name){
		   $no_t=$db->fetch_array($db->query("select id from goip where name='".$name."'" ));
		   if($no_t[0])
		   $ErrMsg .='<br><li>已存在ID: '.$name.'</li>';
		   }
		 */
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			if($password) $pas=",goip_pass='$password'";
			$db->query("UPDATE goip SET goip_zone='$zone',zone_tag='$_POST[zone_tag]',goip_tag='$_POST[goip_tag]' $pas  WHERE goip_name='$oldname'");
			$db->query("UPDATE goip SET goip_team_id='$team_id',dev_disable='$dev_disable'  WHERE goipid='$id'");
			$db->query("UPDATE simbank SET plan_line_name='0' WHERE plan_line_name='$_POST[line_name]'");
			$db->query("UPDATE simbank SET plan_line_name='$_POST[line_name]' WHERE sim_name='$sim_name'");

			if($password || $zone != $oldzone){
				//echo $zone != $oldzone;
				//echo "select * from goip where goip_name='$oldname'";
				$query=$db->query("select * from goip where goip_name='$oldname'");
				while($row=$db->fetch_array($query)) {
					$send[]=pack('LCLSSCa15lla*', $checksum, DEV_ADD, $row[line_name], $row[goip_team_id],$zone,TYPE_GOIP,"000000000000000", -1,0,$row[goip_pass]);
				}
				//sendto_xchanged($send);

			}

//			elseif($team_id != $old_team_id){
				$query=$db->query("select * from goip where goipid='$id'");
				while($row=$db->fetch_array($query)) {
					$send[]=pack('LCLSSCa15lla*', $checksum, DEV_ADD, $row[line_name], $row[goip_team_id],$zone,TYPE_GOIP,"000000000000000", -1,0,$row[goip_pass]);
				}
				//sendto_xchanged($send);
//			}
			if($dev_disable != $_POST['old_disable']){ //disable改变
				$query=$db->query("select * from goip where goipid='$id'");
				//unset($send);
				while($row=$db->fetch_array($query)) {
					$send[]=pack('LCLC', $checksum, $row[dev_disable]?DEV_DISABLE:DEV_ENABLE, $row[line_name], TYPE_GOIP);
				}
				//sendto_xchanged($send);
			}
			//unset($send);
			if(!$team_id)
				$send[]=pack('LCLL', $checksum, DEV_BINDING, $sim_name, $_POST[line_name]);
			else {
				//echo "old:".$old_plan_sim_name;
				$send[]=pack('LCLL', $checksum, DEV_BINDING, $_POST[old_plan_sim_name], 0);
			}
			sendto_xchanged($send);
			WriteSuccessMsg("<br><li>Modify administrator success</li>","goip.php");
		}
	}
		else if($action=="reboot"){
			$send[]=pack('LCLC', $checksum, MACHINE_REBOOT, $_GET['name'], TYPE_GOIP);
			sendto_xchanged($send);
			WriteSuccessMsg("<br><li>The commond is sended to goip($_GET[name]).</li>","goip.php");
		}
		else if($action=="reboot_module"){
			$send[]=pack('LCL', $checksum, MODULE_REBOOT, $_GET['name']);
			sendto_xchanged($send);
			WriteSuccessMsg("<br><li>The commond is sended to goip's module($_GET[name]).</li>","goip.php");
		}
		else $action="main";

	}
	else $action="main";

	//if($_COOKIE['adminname']=="admin")	
	if($action=="main")
	{
		$query=$db->query("SELECT count(*) AS count FROM goip");
		$row=$db->fetch_array($query);
		$count=$row['count'];
		$numofpage=ceil($count/$perpage);
		$totlepage=$numofpage;
		if(isset($_GET['page'])) {
			$page=$_GET['page'];
		} else {
			$page=1;
		}
		if($numofpage && $page>$numofpage) {
			$page=$numofpage;
		}
		if($page > 1) {
			$start_limit=($page - 1)*$perpage;
		} else{
		$start_limit=0;
		$page=1;
	}
	$fenye=showpage("?",$page,$count,$perpage,true,true,"rows");
	$query=$db->query("SELECT goip.*,sim_team.*,simbank.sim_name FROM goip left join sim_team on goip.goip_team_id=sim_team.sim_team_id left join simbank on goip.line_name=simbank.line_name ORDER BY goipid  LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		if($row['line_status'] == 0 || $row['line_status'] == 12){
			$row['alive']='<font color="#FF0000">OFFLINE</font>';
		}
		elseif($row['line_status'] == 11){
			$row['alive']="ONLINE";
		}
		elseif($row['line_status'] == 20){
			$row['alive']='<font color="#00FF00">IDEL</font>';
		}
		elseif($row['line_status'] == 21){
			$row['alive']="BUSY";
		}

		if($row['gsm_status'] == 0 || $row['gsm_status'] == 30){
			$row['gsm']='<font color="#FF0000">LOGOUT</font>';
		}
		else if($row['gsm_status'] == 31){
			$row['gsm']='<font color="#00FF00">LOGIN</font>';
		}
		if($row['dev_disable'] == 0){
			$row['disable'] = 'Enable';
		}
		else {
			$row['disable'] = '<font color="#FF0000">Disable</font>';
		}

		if($row['goip_team_id']){
			$row['bind_type'] = 'Group mode';
		}
		else {
			$row['bind_type'] = 'Fixed mode';
		}
		$rsdb[]=$row;
	}
}
require_once ('goip.htm');

?>
