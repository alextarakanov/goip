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
                        $query=$db->query("select * from sim where bank_name='$name'");
                        while($row=$db->fetch_array($query)) {
                                $send[]=my_pack2(DEV_DEL, $row['sim_name'], $row['sim_team_id'],TYPE_SIM);
                        }
			$db->query("DELETE FROM sim WHERE bank_name='$name'");  
			$db->query("DELETE FROM sim_bank WHERE name='$name'");
			sendto_xchanged($send);
			WriteSuccessMsg("<br><li>删除成功</li>","sim_bank.php");

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
		$rs=$db->fetch_array($db->query("SELECT * FROM sim_bank where name='$name'"));
	}
	elseif($action=="saveadd")
	{
		$name=$_POST['name'];
		$password=$_POST['Password'];
		$banktype=$_POST['banktype'];
                //if($banktype!='SMB128') $banktype='SMB32';
		$team_id=$_POST['team_id'];
		$tag=$_POST['simbank_tag'];
		$imei_mode=$_POST['imei_mode'];
		$imei_prefix=$_POST['imei_prefix'];
		$remain_time=$_POST['remain_time'];
		if($remain_time == "" || $remain_time < -1) $remain_time=-1;
		if($_POST[no_ring_limit] == "" || $_POST[no_ring_limit] < -1) $_POST[no_ring_limit]=-1;
		if($_POST[no_answer_limit] == "" || $_POST[no_answer_limit] < -1) $_POST[no_answer_limit]=-1;
		if($_POST[short_call_limit] == "" || $_POST[short_call_limit] < -1) $_POST[short_call_limit]=-1;
                if($_POST[count_limit] == "" || $_POST[count_limit] < -1) $_POST[count_limit]=-1;
                if($_POST[no_connected_limit] == "" || $_POST[no_connected_limit] < -1) $_POST[no_connected_limit]=-1;
		if($_POST['month_limit_time'] == "" || $_POST['month_limit_time'] < -1) $_POST['month_limit_time']=-1;
		if($_POST['month_reset_day'] == "") $_POST['month_reset_day']=1;
		$time_unit=$_POST['time_unit'];
		$ErrMsg="";
		if(empty($name))
			$ErrMsg ='<br><li>请输入ID</li>';
		if(empty($password))
			$ErrMsg ='<br><li>请输入密码</li>';
		//if($name != $_POST['oldname']){
                if($banktype=='SMB128')
                        $no_t=$db->fetch_array($db->query("select id from sim_bank where name='$name' or (LEFT(name,CHAR_LENGTH(name)-1)='$name' and `type`!='SMB128')"));
                else
                        $no_t=$db->fetch_array($db->query("select id from sim_bank where name='$name' or (name=LEFT('$name',LENGTH('$name')-1) and `type`='SMB128') "));
		if($no_t[0])
			$ErrMsg	.='<br><li>此ID('.$name.')已经存在或者可能与其他类型的SIM Bank名字冲突</li>';
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			$db->query("insert into sim_bank (name,tag,password,`type`) values ('$name','$tag','$password','$banktype')");
			//$id_t=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));

			$sql="insert into sim (sim_name,sim_team_id,bank_name,imei_mode,imei,remain_time,time_unit,time_limit,no_ring_limit,no_answer_limit,short_call_limit,short_time, no_ring_disable, no_answer_disable, short_call_disable,count_limit,count_remain,no_connected_limit,no_connected_remain,month_limit_time,month_remain_time,month_reset_day) values";
			if($banktype=='SMB128') {
				for($i=1;$i<=128;$i++){
					if($_POST[imei_prefix])
						$imei=sprintf("%.11s", sprintf("%.11s",$_POST[imei_prefix])."000000000000").sprintf("%03d", $i)."0";
					else 
						$imei="000000000000000";
					$sql.="('$name".sprintf("%03d",$i)."','$team_id', '$name', '$_POST[imei_mode]','$imei', '$remain_time', '$time_unit','$remain_time','$_POST[no_ring_limit]','$_POST[no_answer_limit]','$_POST[short_call_limit]','$_POST[short_time]', '$_POST[no_ring_disable]','$_POST[no_answer_disable]','$_POST[short_call_disable]','$_POST[count_limit]','$_POST[count_limit]','$_POST[no_connected_limit]','$_POST[no_connected_limit]','$_POST[month_limit_time]','$_POST[month_limit_time]','$_POST[month_reset_day]')";
					if($i<128) $sql .= ",";
				}

			}
			else {
				if($banktype=="SMB1") $j=1;
				else $j=32;
				for($i=1;$i<=$j;$i++){
					if($_POST[imei_prefix])
						$imei=sprintf("%.12s", sprintf("%.12s",$_POST[imei_prefix])."000000000000").sprintf("%02d", $i)."0";
					else 
						$imei="000000000000000";
					$sql.="('$name".sprintf("%02d",$i)."','$team_id', '$name', '$_POST[imei_mode]','$imei', '$remain_time', '$time_unit','$remain_time','$_POST[no_ring_limit]','$_POST[no_answer_limit]','$_POST[short_call_limit]','$_POST[short_time]', '$_POST[no_ring_disable]','$_POST[no_answer_disable]','$_POST[short_call_disable]','$_POST[count_limit]','$_POST[count_limit]','$_POST[no_connected_limit]','$_POST[no_connected_limit]','$_POST[month_limit_time]','$_POST[month_limit_time]','$_POST[month_reset_day]')";
					if($i<$j) $sql .= ",";
				}
			}

			//echo $sql;
			$db->query($sql);
                        $query=$db->query("select sim.*,password from sim left join sim_bank on sim.bank_name=sim_bank.name where sim_bank.name='$name'");
                        while($row=$db->fetch_array($query)){
				sim_info($row, $send);
                                //$send[]=my_pack($row, SIM_ADD);
                        }
			sendto_xchanged($send);
			//$query=$db->query("INSERT INTO goip (name,password,provider) VALUES ('$name','$password','$provider')");
			//sendto_cron(); 
			WriteSuccessMsg("<br><li>添加成功</li>","sim_bank.php");				
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
		$sim_name=$_POST['sim_name'];
		$imei=$_POST['imei'];
		$remain_time=$_POST['remain_time'];
		$time_unit=$_POST['time_unit'];
		if($remain_time == "" || $remain_time < -1) $remain_time=-1;
		
		$ErrMsg="";
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			if($password){
				$pas=",password='$password'";
			}
			$db->query("UPDATE sim_bank SET tag='$_POST[simbank_tag]' $pas WHERE name='$_GET[name]'");
			
                        if($password){
                                $query=$db->query("select sim.*,password from sim left join sim_bank on sim.bank_name=sim_bank.name where sim.bank_name='$name'");
                                while($row=$db->fetch_array($query)) {
					sim_info($row, $send);
					//$send[]=my_pack($row, SIM_ADD);
                                }
				sendto_xchanged($send);

                        }

			WriteSuccessMsg("<br><li>修改成功</li>","sim_bank.php");
		}
	}
	else if($action=="reboot"){
		$send[]=my_pack2(MACHINE_REBOOT, $_GET['name'], TYPE_SIM);
		sendto_xchanged($send);
		WriteSuccessMsg("<br><li>The commond is sended to Sim Bank($_GET[name]).</li>","sim_bank.php");
	}

	else $action="main";

	}
	else $action="main";

	//if($_COOKIE['adminname']=="admin")	
	if($action=="main")
	{
		$query=$db->query("SELECT count(*) AS count FROM sim_bank");
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
		$fenye=showpage("?",$page,$count,$perpage,true,true,"编");
		$query=$db->query("SELECT * from sim_bank ORDER BY id LIMIT $start_limit,$perpage");
		while($row=$db->fetch_array($query)) {
			$rsdb[]=$row;
		}
}
require_once ('sim_bank.htm');

?>
