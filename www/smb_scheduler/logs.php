<?php

define("OK", true);
session_start();
if(!isset($_SESSION['usertype'])){
        require_once ('login.php');
        exit;
}
require_once("global.php");
$action=$_GET['action'];
//$action="main";

//if($_COOKIE['adminname']=="admin")	
if($action=="del"){
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

	if(empty($Id))
		$ErrMsg ='<br><li>Please choose one</li>';
	if($ErrMsg!="")
		WriteErrMsg($ErrMsg);
	else{
		$query=$db->query("DELETE FROM logs WHERE id IN ($Id)");

		WriteSuccessMsg("<br><li>Delete logs success</li>","logs.php?line_name=$_GET[line_name]&sim_name=$_GET[sim_name]");

	}
}
	else if($action=="delall"){
		if($_GET[line_name])  $where="where line_name='$_GET[line_name]'";
		else if($_GET[sim_name])  $where="where sim_name='$_GET[sim_name]'";
		$db->query("DELETE FROM logs $where"); 

		WriteSuccessMsg("<br><li>Delete logs success</li>","logs.php?line_name=$_GET[line_name]&sim_name=$_GET[sim_name]");

	}

else
{
	$action='main';

	$t_info="All";
	if($_GET[team_id]){
		$where="where team_id='$_GET[team_id]'";
		$pages="team_id=$_GET[team_id]";
	}
	else if($_GET[line_name])  {
		$where="where line_name='$_GET[line_name]'";
		$pages="line_name=$_GET[line_name]";
		$t_info="GoIP线路ID:$_GET[line_name]";
	}
	else if($_GET[sim_name])  {
		$where="where sim_name='$_GET[sim_name]'";
		$pages="sim_name=$_GET[sim_name]";
		$t_info="SIM Slot ID:$_GET[sim_name]";
	}
	$query=$db->query("SELECT count(*) AS count FROM logs $where" );
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
	$fenye=showpage("?$pages&",$page,$count,$perpage,true,true,"row(s)");
	$query=$db->query("SELECT * from logs left join sim_team on logs.team_id=sim_team.sim_team_id $where ORDER BY id desc LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		if($_GET[team_id]){
			$t_info="组名称:$row[sim_team_name]";
		}
		$rsdb[]=$row;
	}
}

require_once ('logs.htm');

?>
