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

if(isset($_GET['action'])) {
	$action=$_GET['action'];

	if($action=="del")
	{
		$ErrMsg="";
		$name=myaddslashes($_GET['name']);
		//print_r($name);
		if(empty($name))
			$ErrMsg ='<br><li>Please choose one</li>';
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
                        $query=$db->query("select * from device_line where goip_name='$name'");
                        while($row=$db->fetch_array($query)) {
                                $send[]=my_pack2(DEV_DEL, $row[goip_name], $row[goip_team_id],TYPE_GOIP);
				if($line_names) $line_names.=",";
				$line_names.=$row[line_name];
				if($line_ids) $line_ids.=",";
				$line_ids.=$row['id'];
			}
  			if($line_names) {
				$query=$db->query("select sim_name from  sim where sim_team_id=0 and plan_line_name in ($line_names)");
				while($row=$db->fetch_array($query)) {
					$send[]=my_pack2(DEV_BINDING, $row['sim_name'], 0);
				}
				$db->query("update sim set plan_line_name='',line_name='' where sim_team_id=0 and plan_line_name in ($line_names)"); 
				$db->query("delete from human_ref where line_id in ($line_ids)");
			}

			$db->query("DELETE FROM device_line WHERE goip_name='$name'");
			$db->query("DELETE FROM rm_device WHERE name='$name'");
			sendto_xchanged($send);
			WriteSuccessMsg("<br><li>删除成功</li>","rm_device.php");
		}
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
		$rs=$db->fetch_array($db->query("SELECT * FROM rm_device where name='$name'"));
	}
	elseif($action=="saveadd")
	{
		$name=$_POST['name'];
		$password=$_POST['Password'];
		//$banktype=$_POST['banktype'];
		$team_id=$_POST['team_id'];
		$tag=$_POST['goip_tag'];
		$goiptype=$_POST['goiptype'];
		$ErrMsg="";
		if(empty($name))
			$ErrMsg ='<br><li>Please input GoIP ID</li>';
		if(empty($password))
			$ErrMsg ='<br><li>Please input Password</li>';
		//if($name != $_POST['oldname']){
		$no_t=$db->fetch_array($db->query("select id from rm_device where name='".$name."'"));
		if($no_t[0])
			$ErrMsg	.='<br><li>该ID已经存在: '.$name.'</li>';
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			$db->query("insert into rm_device (name,tag,password,type,zone,zone_tag) values ('$name','$tag','$password','$_POST[goiptype]','$_POST[zone]','$_POST[zone_tag]')");
			//$id_t=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
			$sql="insert into device_line (line_name,goip_team_id,goip_name) values";
			if($goiptype == "GoIPx4") $lines=4;
			else if($goiptype == "GoIPx16") $lines=16;
			else if($goiptype == "GoIPx8") $lines=8;
			else if($goiptype == "GoIPx1") $lines=1;
			else if($goiptype == "GoIPx32") $lines=32;
			else $lines=1;  
			for($i=1;$i<=$lines;$i++){
				$sql.="('$name".sprintf("%02d",$i)."','$team_id', '$name')";
				if($i<$lines) $sql .= ",";


			}
			$db->query($sql);
			$query=$db->query("select line_name,goip_team_id,password,zone from device_line left join rm_device on device_line.goip_name = rm_device.name where goip_name='$name' order by line_name");
			while($row=$db->fetch_array($query)){
				$send[]=my_pack($row, GOIP_ADD);
			}
			sendto_xchanged($send);
			//$query=$db->query("INSERT INTO goip (name,password,provider) VALUES ('$name','$password','$provider')");
			WriteSuccessMsg("<br><li>添加成功</li>","rm_device.php");				
		}
	}
	elseif($action=="savemodify")
	{
		$password=$_POST['Password'];
		$name=$_REQUEST['name'];
		$oldname=$_POST['oldname'];
		$team_id=$_POST['team_id'];
		$old_team_id=$_POST['old_team_id'];
                $id=$_GET[id];
		$dev_disable = $_POST['dev_disable'];
/*
		$sim_name=$_POST['sim_name'];
		$imei=$_POST['imei'];
		$remain_time=$_POST['remain_time'];
		$time_unit=$_POST['time_unit'];
		if($remain_time == "" || $remain_time < -1) $remain_time=-1;
*/		
		$ErrMsg="";
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			if($password){
				$pas=",password='$password'";
			}
			$db->query("UPDATE rm_device SET tag='$_POST[goip_tag]',zone='$_POST[zone]',zone_tag='$_POST[zone_tag]' $pas WHERE name='$name'");
			
			$query=$db->query("select device_line.*, password, zone from device_line join rm_device on device_line.goip_name = rm_device.name where goip_name='$name'");
			while($row=$db->fetch_array($query)) {
				$send[]=my_pack($row, GOIP_ADD);
			}
			sendto_xchanged($send);

			WriteSuccessMsg("<br><li>修改成功</li>","rm_device.php");
		}
	}
	else if($action=="reboot"){
		$send[]=my_pack2(MACHINE_REBOOT, $_GET['name'], TYPE_GOIP);
		sendto_xchanged($send);
		WriteSuccessMsg("<br><li>命令已经发往GoIP($_GET[name]).</li>","rm_device.php");
	}

	else $action="main";

	}
	else $action="main";

	//if($_COOKIE['adminname']=="admin")	
	if($action=="main")
	{
		$query=$db->query("SELECT count(*) AS count FROM rm_device");
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
		$fenye=showpage("?",$page,$count,$perpage,true,true,"台");
		$query=$db->query("SELECT * from rm_device ORDER BY id LIMIT $start_limit,$perpage");
		while($row=$db->fetch_array($query)) {
			$rsdb[]=$row;
		}
}
require_once ('rm_device.htm');

?>
