<?php

session_start();
if(!isset($_SESSION['userid'])){
	define("OK", true);
	require_once("inc/conn.inc.php");
	if(isset($_GET['lan']))
		$language=$_GET['lan'];
	else {
		$query=$db->query("SELECT * FROM system WHERE 1 ");
		//echo "2323232";
		$rs=$db->fetch_array($query);
		$language=$rs['lan'];		
	}
	switch($language){
		case 1:
			require_once ('login.php');
			break;
/*
		case 2:
			//header("Location: tw"); 
			require_once ('tw/login.php');
			//exit;
			break;
*/
		case 3:
			//header("Location: en"); 
			require_once ('en/login.php');
			//exit;
			break;
		default:
			require_once ('login.php');
			break;
	}
	
	exit;
}

?>

<html>
<meta name="Author" content="Gaby_chen">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Sim Bank Scheduler Server</title>
</head>
<frameset Id="frame" framespacing="0" border="false" cols="160,*" frameborder="1" scrolling="yes">
	<frame name="left" scrolling="auto" marginwIdth="0" marginheight="0" src="left.php">
	<frameset framespacing="0" border="false" rows="35,*" frameborder="0" scrolling="yes">
		<frame name="top" scrolling="no" src="top.php">
		<frame name="main" scrolling="auto" src="main.php" onload="window.top.frames[1].location.reload()">
	</frameset>
</frameset><noframes></noframes>
</html>
