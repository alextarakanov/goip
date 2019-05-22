<?php

define("OK", true);
session_start();
if($_SESSION['usertype'] > 1)	
	die("需要admin权限！");	
require_once("global.php");
//!defined('OK') && exit('ForbIdden');
//$UserName=$_COOKIE['adminname'];

if(isset($_GET['action'])) {
	//if($_GET['action'] != "modifyself" && $_GET['action'] != "savemodifyself" && $_COOKIE['adminname']!="admin" )
	//WriteErrMsg("<br><li>需要admin权限!</li>");
	$action=$_GET['action'];

	if($action=="del")
	{
		$ErrMsg="";

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

		//$name=myaddslashes($_GET['name']);
		//print_r($name);
		if(empty($Id))
			$ErrMsg ='<br><li>Please choose one</li>';
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
                        $query=$db->query("select * from rule WHERE rule_id in ($Id)");
                        while($row=$db->fetch_array($query)) {
				$send[]=pack('LCLSC', $checksum, SCH_UPDATE, $row[goip_team_id], $row[goip_team_id],TYPE_GOIP);
			}
			$query=$db->query("DELETE FROM rule WHERE rule_id in ($Id)");
			sendto_xchanged($send);
			//$db->query("DELETE FROM record WHERE goipid IN ($Id)");
			//sendto_cron();
			WriteSuccessMsg("<br><li>规则成功</li>","rule.php");

		}
		//$id=$_GET['id'];
		//$query=$db->query("Delete  from ".$tablepre."admin WHERE Id=$id");
	}
	elseif($action=="add")
	{
		$query=$db->query("select * from goip_team where goip_team_id not in (select goip_team_id from rule) order by goip_team_name");                                                   
		while($row=$db->fetch_array($query)) {                                                            
			$grsdb[]=$row;
		}
		$query=$db->query("select * from sim_team where sim_team_id not in (select sim_team_id from rule) order by sim_team_name");
		while($row=$db->fetch_array($query)) {                                                            
			$srsdb[]=$row;
		}

/*
		$query=$db->query("select id,prov from prov ");
		while($row=$db->fetch_array($query)) {
			$prsdb[]=$row;
		}		
*/
	}
	elseif($action=="modify")
	{
		$id=$_GET['id'];
/*
		$query=$db->query("select id,prov from prov ");
		while($row=$db->fetch_array($query)) {
			$prsdb[]=$row;
		}
*/
		$rs=$db->fetch_array($db->query("SELECT rule.*,sim_team_name,goip_team_name FROM rule,sim_team,goip_team where rule.sim_team_id=sim_team.sim_team_id and rule.goip_team_id=goip_team.goip_team_id and rule_id=$id"));
		$query=$db->query("select * from goip_team where goip_team_id not in (select goip_team_id from rule) order by goip_team_name");                                                   
		while($row=$db->fetch_array($query)) {                                                            
			$grsdb[]=$row;
		}
		$query=$db->query("select * from sim_team where sim_team_id not in (select sim_team_id from rule) order by sim_team_name");                                                   
		while($row=$db->fetch_array($query)) {                                                            
			$srsdb[]=$row;
		}


		//if(!$s[0])
		//WriteErrMsg("<br><li>添加用户需要admin权限</li>"."$row[1]");
	}
	elseif($action=="saveadd")
	{
		//WriteErrMsg("'$_POST['name']'");
		$name=$_POST['name'];
		//$password=$_POST['Password'];
		//$provider=$_POST['provider'];
		//$banktype=$_POST['banktype'];
		//$info=$_POST['info'];
		$gid=$_POST['gid'];
		$sid=$_POST['sid'];

		$ErrMsg="";
		if(empty($name))
			$ErrMsg ='<br><li>请输入名称</li>';
		//if(empty($password))
			//$ErrMsg ='<br><li>请输入密码</li>';
		//if($name != $_POST['oldname']){
		if(empty($gid))
			$ErrMsg .='<br><li>请选择goip team</li>';
		if(empty($sid))
                        $ErrMsg .='<br><li>请选择sim team</li>';
		$no_t=$db->fetch_array($db->query("select rule_name from rule where rule_name='".$name."'"));
		if($no_t[0])
			$ErrMsg	.='<br><li>已存在名称: '.$name.'</li>';
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			$sql="insert into rule (rule_name, sim_team_id, goip_team_id, rule, 
				step1,step1_time,step2,step2_time,step3,step3_time,step4,step4_time,step5,step5_time) 
				values ('$name', $sid, $gid, '$rule','$_POST[step1]','$_POST[step1_time]'
				,'$_POST[step2]','$_POST[step2_time]','$_POST[step3]','$_POST[step3_time]'
				,'$_POST[step4]','$_POST[step4_time]','$_POST[step5]','$_POST[step5_time]')";	
			echo $sql;
			$db->query($sql);
			//$query=$db->query("INSERT INTO goip (name,password,provider) VALUES ('$name','$password','$provider')");
			//sendto_cron(); 
			WriteSuccessMsg("<br><li>添加规则成功</li>","rule.php");				
		}
	}
	elseif($action=="savemodify")
	{
		$id=$_POST['id'];
		//$password=$_POST['Password'];
		$name=$_POST['name'];
		$gid=$_POST['gid'];
		$sid=$_POST['sid'];
		//$oldname=$_POST['oldname'];
		//$name=$_POST['name'];
		$ErrMsg="";
		if(!isset($gid))
			$ErrMsg.='<br><li>没有选中一个goip team</li>';
		if(!isset($sid))
			$ErrMsg.='<br><li>没有选中一个sim team</li>';
		if(!isset($name))
			$ErrMsg.='<br><li>没有填写名称</li>';
		//   if($oldname != $name){
		//echo "select rule_id from rule where rule_name='$name' and rule_id!=$id";
		   $no_t=$db->fetch_array($db->query("select rule_id from rule where rule_name='$name' and rule_id!=$id" ));
		   if($no_t[0])
		   	$ErrMsg	.='<br><li>已存在名称: '.$name.'</li>';
		//   }	
		
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
				//$pas=",password='$password'";
			$db->query("UPDATE rule SET rule_name='$name',goip_team_id='$gid',sim_team_id='$sid',
				step1='$_POST[step1]',step1_time='$_POST[step1_time]',
				step2='$_POST[step2]',step2_time='$_POST[step2_time]',
				step3='$_POST[step3]',step3_time='$_POST[step3_time]',
				step4='$_POST[step4]',step4_time='$_POST[step4_time]',
				step5='$_POST[step5]',step5_time='$_POST[step5_time]' WHERE rule_id=$id");
			sendto_cron(); 
			WriteSuccessMsg("<br><li>Modify rule success</li>","rule.php");
		}
	}
		else $action="main";

	}
	else $action="main";

	//if($_COOKIE['adminname']=="admin")	
	if($action=="main")
	{
		$query=$db->query("SELECT count(*) AS count FROM rule");
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
		$query=$db->query("SELECT rule.*,sim_team_name,goip_team_name FROM rule,sim_team,goip_team where rule.sim_team_id=sim_team.sim_team_id and rule.goip_team_id=goip_team.goip_team_id ORDER BY rule_id DESC LIMIT $start_limit,$perpage");
		$step=array("","goto step1","goto step2","goto step3","goto step4","goto step5","active","stop");
		while($row=$db->fetch_array($query)) {
			if($row['sim_login'] == 1){
				$row['sim_login']="已注册";
			}
			elseif($row['sim_login'] == 0){
				$row['sim_login']="未注册";
			$row['rule']="step1:".$step["$row[step1]"]." $row[step1_time];step2:".$step["$row[step2]"]." $row[step2_time];step3:".$step["$row[step3]"]." $row[step3_time];step4:".$step["$row[step4]"]." $row[step4_time];step5:".$step["$row[step5]"]." $row[step5_time];";
				//$row['sendsms']="onClick=\"alert('GoIP logout!');return false;\"";
		}
		$rsdb[]=$row;
	}
}
require_once ('rule.htm');

?>
