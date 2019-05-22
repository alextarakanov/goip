<?php
session_start();
if(!isset($_SESSION['userid'])){
        require_once ('login.php');
        exit;
}
define("OK", true);
require_once("global.php");
//!defined('OK') && exit('ForbIdden');
//$UserName=$_SESSION['adminname'];
if(isset($_GET['action'])) {
	if($_GET['action'] != "modifyself" && $_GET['action'] != "savemodifyself" && $_GET['action'] != "modifymsg" && $_GET['action'] != "savemodifymsg" && $_SESSION['permissions'] != 1 )
		WriteErrMsg("<br><li>需要admin权限!</li>");
	$action=$_GET['action'];

	if($action=="del")
	{
		$ErrMsg="";
		$Id=$_GET['id'];
		if(($Id=$_GET['id']) == "1")
			$ErrMsg="<br><li>超级用户不能删除</li>";

		if(empty($Id)){
			$num=$_POST['boxs'];
			for($i=0;$i<$num;$i++)
			{	
				if(!empty($_POST["Id$i"])){

					if($_POST["Id$i"] == "1"){
						$ErrMsg="<br><li>超级用户不能删除</li>";
						WriteErrMsg($ErrMsg);
						break;
					}

					if($Id=="")
						$Id=$_POST["Id$i"];
					else
						$Id=$_POST["Id$i"].",$Id";
				}
			}
		}
		//WriteErrMsg($num."$Id");

		if(empty($Id))
			$ErrMsg ='<br><li>Please choose one</li>';
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			$query=$db->query("DELETE FROM user WHERE id IN ($Id)");

			WriteSuccessMsg("<br><li>删除用户成功</li>","user.php");

		}
		//$id=$_GET['id'];
		//$query=$db->query("Delete  from ".$tablepre."admin WHERE Id=$id");
	}
	elseif($action=="add")
	{

	}
	elseif($action=="modify")
	{
		$id=$_GET['id'];
		//if($id)
		$rs=$db->fetch_array($db->query("SELECT * FROM user where id=$id"));
		//if(!$s[0])
		//WriteErrMsg("<br><li>添加用户需要admin权限</li>"."$row[1]");
	}
	elseif($action=="modifyself")
	{
		$rs=$db->fetch_array($db->query("SELECT * FROM user where name='".$_SESSION['username']."'"));
	}
	elseif($action=="savemodifyself")
	{
		$UserName=$_SESSION['username'];
		$password=myaddslashes($_POST['Password']);
		$_POST[info]=myaddslashes($_POST[info]);
		if($password){
			$password=md5(md5($password.'dbl').'yzm');
			$pas=",password='$password'";
		}
		//else WriteErrMsg("密码不能为空");
		$query=$db->query("UPDATE user SET info='$_POST[info]'".$pas."  WHERE name='$UserName'");
		//$query=$db->query("UPDATE user SET password='$password' WHERE username='$UserName'");
		WriteSuccessMsg("<br><li>Change password</li>","user.php");

	}
	elseif($action=="modifymsg")
	{
		$rs=$db->fetch_array($db->query("SELECT * FROM user where name='".$_SESSION['username']."'"));
	}
	elseif($action=="savemodifymsg")
	{
		$sql="update user set ";
		for($i=1;$i<=10;$i++){
			//addslashes($_POST["msg$i"]);
			if(!get_magic_quotes_gpc())
				$msg=addslashes($_POST["msg$i"]);
			else 
				$msg=$_POST["msg$i"];
			$sql.=" msg$i='$msg',";
		}
		$sql.=" id=id where name='$_SESSION[username]'";
		$query=$db->query($sql);
		WriteSuccessMsg("<br><li>修改常用语成功</li>","user.php?action=modifymsg");
	}
	elseif($action=="saveadd")
	{
		//WriteErrMsg("'$_POST['name']'");
		$username=myaddslashes($_POST['username']);
		$password=myaddslashes($_POST['Password']);
		$provider=$_POST['provider'];
		$permissions=$_POST['permissions'];

		$info=$_POST['info'];
		$ErrMsg="";
		if(empty($username))
			$ErrMsg ='<br><li>请输入名称</li>';
					if(empty($password))
						$ErrMsg ='<br><li>请输入密码</li>';
					if($ErrMsg!="")
						WriteErrMsg($ErrMsg);
					else{
					
						$query=$db->query("SELECT id FROM user WHERE name='$username' ");
						$rs=$db->fetch_array($query);
						if(empty($rs[0])){
							$password=md5(md5($password.'dbl').'yzm');
							$query=$db->query("INSERT INTO user (name,password,permissions,info) VALUES ('$username','$password','$permissions','$info')");
							WriteSuccessMsg("<br><li>Add administrator success</li>","user.php");
						}
						else{
							$ErrMsg=$ErrMsg."<br><li>Administrator [$username] have existed</li>";
							WriteErrMsg($ErrMsg);
						}
								
					}
	}
	elseif($action=="savemodify")
	{
					$password=myaddslashes($_POST['Password']);
					$Id=$_GET['id'];
					$ErrMsg="";
					//if($Id == '1')
						//$ErrMsg = "初始系统管理员不能修改";
					/*
					if(empty($password))
						$ErrMsg ='<br><li>Your password should not be empty</li>';
					*/
					if($ErrMsg!="")
						WriteErrMsg($ErrMsg);
					else{
						if($password){
							$password=md5(md5($password.'dbl').'yzm');
							$pas=",password='$password'";
						}
						$_POST[info]=myaddslashes($_POST[info]);
						$query=$db->query("UPDATE user SET permissions='$_POST[permissions]',info='$_POST[info]'".$pas."  WHERE id='$Id'");

						WriteSuccessMsg("<br><li>Modify administrator success:</li>","user.php");
					}
	}
	else $action="main";
	
}
else $action="main";

if($_SESSION['permissions'] < 2)	
{
	$query=$db->query("SELECT count(*) AS count FROM user");
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
	$fenye=showpage("user.php?",$page,$count,$perpage,true,true,"编");
	$query=$db->query("SELECT * FROM user ORDER BY id LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		$rsdb[]=$row;
	}
}
	require_once ('user.htm');

?>
