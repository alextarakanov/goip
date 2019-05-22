<?php
define("OK", true);
require_once('inc/conn.inc.php');
session_start();

$lan=$_POST['lan'];
$FoundErr=false;
$username=str_replace("'","",trim($_POST['username']));
$password=str_replace("'","",trim($_POST['password']));

if ($username==''){
	$FoundErr=true;
	$ErrMsg= "<br><li>没有输入用户名</li>";
}
if ($password==''){
	$FoundErr=true;
	$ErrMsg=$ErrMsg."<br><li>没有输入密码</li>";
}
if($FoundErr!=true){
	$password=md5(md5($password.'dbl').'yzm');
	//echo "SELECT id,permissions FROM user WHERE name='$username' and password='$password' ";
	$query=$db->query("SELECT id,permissions FROM user WHERE name='$username' and password='$password' ");
	$rs=$db->fetch_array($query);
	$adminId=$rs[0];
	$usertype=$rs['permissions'];
	if(empty($adminId)){
		$FoundErr=true;
		$ErrMsg=$ErrMsg."<br><li>用户名或密码错误</li>";
	}
	else{
		//$db->query("update user set logintime=now() where userid=$adminId");
		$_SESSION['username'] = $username;
		$_SESSION['userid'] = $adminId;
		$_SESSION['usertype'] = $usertype;
		$_SESSION['permissions'] = $usertype;

		switch($usertype){
			case 2:  //普通
				$_SESSION['typename']='普通管理员';
				$_SESSION['typename_e']='Normal';
				break;
			case 1: //系统管理员
				$_SESSION['typename']='高级管理员';
				$_SESSION['typename_e']='Super';
				Header("Location: admin.php");
				break;
			default:
				$_SESSION['typename']='普通管理员';
				$_SESSION['typename_e']='Normal';
				break;

		}
		switch($lan){
			case 2:
				Header("Location: tw/index.php");
				break;
			case 3:
				Header("Location: en/index.php");
				break;
			default:
				Header("Location: index.php");
				break;
		}
		//Header("Location: index.php");

	}
}

if($FoundErr==true){
	$strErr="<html><head><title>Error Information</title><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" ;
	$strErr=$strErr."<link href='style.css' rel='stylesheet' type='text/css'></head><body>" ;
	$strErr=$strErr."<table cellpadding=2 cellspacing=1 border=0 wIdth=400 class='border' align=center>"; 
	$strErr=$strErr."  <tr align='center'><td height='22' class='title'><strong>Wrong message</strong></td></tr>" ;
	$strErr=$strErr."  <tr><td height='100' class='tdbg' valign='top'><b> Reasons:</b><br>$ErrMsg</td></tr>" ;
	$strErr=$strErr."  <tr align='center'><td class='tdbg'><a href=javascript:history.back();>&lt;&lt; Return</a></td></tr>" ;
	$strErr=$strErr."</table>" ;
	$strErr=$strErr."</body></html>" ;
	echo $strErr;
}


?>
