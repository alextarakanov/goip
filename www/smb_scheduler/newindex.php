<?php
require_once("header.php");
	define("OK", true);
	require_once("inc/conn.inc.php");
if(!isset($_COOKIE['username'])) {

	if(isset($_GET['lan']))
		$language=$_GET['lan'];
	else {
		$query=$db->query("SELECT * FROM system WHERE 1 ");
		$rs=$db->fetch_array($query);
		$language=$rs['lan'];		
	}
	switch($language){
		case 1:
			require_once ('login.php');
			break;
		case 2:
			//header("Location: tw"); 
			require_once ('tw/login.php');
			//exit;
			break;
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

		$query=$db->query("SELECT * FROM user WHERE id=$_COOKIE[userid] ");
		$rs=$db->fetch_array($query);
		//$language=$rs['lan'];	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="../style.css" type="text/css" />
<link rel="stylesheet" href="style.css" type="text/css" />
<title><?=$page_title?></title>
</head>
<body onload="">
<style type="text/css">
.button_title{
	background:url('images/BG/navbar_bg.png') repeat-x;
	height:30px;
	font-size:13px;
	font-weight:bolder;
	vertical-align:middle;
	border-style:solid;
	border-color:#999999;
	border-width:1px;
	text-indent:20px;
	font-family:Arial;
	color: #FFFFFF;
}
.Title8 {

	font-family: "Helvetica";
	font-size: 18px;
	color: #66b1f7;
}
.nav {
  margin: 0;
  padding: 0;
  background-image: url('images/BG/navbar_bg.png');
  font-family:Arial;
  list-style-type: none;

  width: 100%;
  float: left; /* Contain foated list items */
}
.nav li {
  margin: 0;
  padding: 0;
  float: left;
}
.nav a {
  float: left;
  padding:8px;
  text-align: center;
  color: #FFF;
  font-family:Arial;
  font-size:13px;

  text-decoration: none;
  border-right: 1px solid #FFF;
}
.nav a:hover {
  color: #666;
  font-size:13px;
  border-right: 1px solid #FFF;
}
</style>
<div id="div_top_logo">
	<div style="position:absolute;left: 8px;top:5px;height:30px;width:50%">
		<span class="Title8"><?=$page_title?></span></div>
	<div style="width:200px;float:right;height:30px;top:0px" align="right"><?php echo $_COOKIE['username'] ?><a href="myself.php">[Account]</a> | <a href="logout.php">[Logout]</a>
	</div>
</div>

<div id="div_top_menu" style="position:absolute;left: 0px;top:31px;width:100%">
<ul class="nav">
	<li><a href="index.php">Home</a></li>
	<li><a href="contact/index.php">Contact</a></li>
	<li><a href="sms/index.php">SMS Management</a></li>
	<li><a href="admin/index.php">System Management</a></li>
	<li><a href="goip_prov/index.php">Goip & Provider</a></li>
	<li></li>
</ul>
</div>

<div id="div_status" name="div_status">

</div>
<!--begin right-->
<div style="position:absolute; left: 10px; top:90px;">
<!--begin right-->
<br>

<div><h1><u>Account Info</u></h1></div>
<div id="div_error_msg" class="error_msg"></div>
Name : <?php echo $_COOKIE['username'] ?><br/>
Mobile :<?php echo $rs['tel'] ?><br/>
Email :<?php echo $rs['email'] ?><br/>
<!-- Number message sended :<?php echo $rs['sms_count'] ?><br/> -->
</div>
<!--end right-->
</td>
</tr>
</table>
</form>
</body>
</html>

