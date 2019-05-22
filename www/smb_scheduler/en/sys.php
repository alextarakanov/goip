<?php
session_start();
if(!isset($_SESSION['username'])){
	require_once ('../login.php');
	exit;
}

//if($_COOKIE['permissions'] > 1)	
//	die("需要admin权限！");	
define("OK", true);
require_once("global.php");
require_once('../inc/conn.inc.php');


if($_GET['action'] != "save"){
/*
if ($username==''){
	$FoundErr=true;
	$ErrMsg= "<br><li>用户名不能为空!</li>";
}
if ($password==''){
	$FoundErr=true;
	$ErrMsg=$ErrMsg."<br><li>密码不能为空!</li>";
}
*/
  if($FoundErr!=true){
  	$query=$db->query("SET NAMES 'utf8'");
	$query=$db->query("SELECT * FROM system WHERE 1 ");
	$rs=$db->fetch_array($query);
	$sysname=$rs['sysname'];
	$maxword=$rs['maxword'];
	$lan=$rs['lan'];
	$alive=$rs['bottom_alive'];
	$buf=file_get_contents("../inc/server_type.cfg");
	sscanf($buf, "TYPE='%[^']'", $type);
        if($rs['auto_disable_s_call']) $auto_disable_s_call_checked="checked";
        else $auto_disable_s_call_display="none";
        if($rs['auto_reboot_s_call']) $auto_reboot_s_call_checked="checked";
        else $auto_reboot_s_call_display="none";
        if($rs['auto_disable_low_asr']) $auto_disable_low_asr_checked="checked";
        else $auto_disable_low_asr_display="none";
        if($rs['auto_disable_low_acd']) $auto_disable_low_acd_checked="checked";
        else $auto_disable_low_acd_display="none";
  }
}
else {
	$sysname=$_POST['sysname'];
	$maxword=$_POST['maxword'];
	$lan=$_POST['lan'];
        if($_POST['auto_disable_s_call']=='on') $_POST['auto_disable_s_call']=1;
        else $_POST['auto_disable_s_call']=0;
        if($_POST['auto_reboot_s_call']=='on') $_POST['auto_reboot_s_call']=1;
        else $_POST['auto_reboot_s_call']=0;

        if($_POST['auto_disable_low_asr']=='on') $_POST['auto_disable_low_asr']=1;
        else $_POST['auto_disable_low_asr']=0;
        if($_POST['auto_disable_low_acd']=='on') $_POST['auto_disable_low_acd']=1;
        else $_POST['auto_disable_low_acd']=0;

        $query=$db->query("UPDATE system SET sysname='$sysname',lan=$lan, warning_remain_time='$_POST[warning_remain_time]',warning_remain_count='$_POST[warning_remain_count]',auto_disable_s_call='$_POST[auto_disable_s_call]',auto_disable_s_call_msg='$_POST[auto_disable_s_call_msg]',auto_disable_low_asr='$_POST[auto_disable_low_asr]',auto_disable_asr_threshold='$_POST[auto_disable_asr_threshold]',auto_disable_asr_number='$_POST[auto_disable_asr_number]',auto_disable_low_acd='$_POST[auto_disable_low_acd]',auto_disable_acd_threshold='$_POST[auto_disable_acd_threshold]',auto_disable_acd_number='$_POST[auto_disable_acd_number]',auto_reboot_s_call='$_POST[auto_reboot_s_call]',auto_reboot_s_call_msg='$_POST[auto_reboot_s_call_msg]' where 1");
	if($_POST['type']==1) $type=1;
	else $type=0;
	$buf=file_get_contents("../inc/server_type.cfg");
	sscanf($buf, "TYPE='%[^']'", $type0);
	if($type!=$type0){
		file_put_contents("../inc/server_type.cfg", "TYPE='$type'");
		$send[]=pack('La*', $checksum, "reboot");
		//sendto_xchanged2($send);
	}
	WriteSuccessMsg("<br><li>Save success!</li>","sys.php");	
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="../style.css" rel="stylesheet" type="text/css">
<title>System settings</title>
<script src="check.js"></script>
<script language="JavaScript" type="text/JavaScript">
function show_tr(value,id){
        if(!value) document.getElementById(id).style.display='none';
        else document.getElementById(id).style.display='';
}
</script>
</head>
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">
<table width="100%" height="25"  border="0" cellpadding="0" cellspacing="0">
  <tr class="topbg">
    <td width="8%">&nbsp;</td>
    <td width="92%" height="25"><strong>Current Location: System Settings</strong></td>
  </tr>
</table>
<form method="post" action="sys.php?action=save" name="myform" onSubmit="javascript:return check();">
  <br>
  <table wIdth="600" border="0" align="center" cellpadding="2" cellspacing="1" class="border" >
    <tr class="title"> 
      <td height="22" colspan="2"> <div align="center"><strong>System Settings</strong></div></td>
    </tr>
    <tr>
      <td wIdth="300" align="right" class="tdbg"><strong>Status of Scheduler:</strong></td>
      <td class="tdbg"><?php if($alive == 1) echo 'On';else echo 'Off';?></td>
    </tr>
    <tr> 
      <td wIdth="300" align="right" class="tdbg"><strong>System Name:</strong></td>
      <td class="tdbg"><input type="input" name="sysname" value="<?php echo $sysname ?>"></td>
    </tr>
    <tr> 
      <td wIdth="300" align="right" class="tdbg"><strong>Default Language:</strong></td>
      <td class="tdbg"><select name="lan">
	  <option value="1" <?php if($lan==1) echo 'selected' ?>>Simplified Chinese</option>
	  <option value="3" <?php if($lan==3) echo 'selected' ?>>English</option>
        </select>
      </td>
    </tr>
    <tr> 
      <td wIdth="300" align="right" class="tdbg"><strong>Server Mode:(need to run ./run_scheduler after change)</strong></td>
      <td class="tdbg"><select name="type">
          <option value="0" <?php if($type!=1) echo 'selected' ?>>Whole Server</option> 
          <option value="1" <?php if($type==1) echo 'selected' ?>>SIM Bank Data Relay</option>
      </select></td>
    </tr>

    <tr>
      <td wIdth="300" align="right" class="tdbg"><strong>Warning limit of remain call time:</strong></td>
      <td class="tdbg"><input type="input" name="warning_remain_time" value="<?php echo $rs['warning_remain_time'] ?>"></td>
    </tr>

    <tr>
      <td wIdth="300" align="right" class="tdbg"><strong>Warning limit of remain call count:</strong></td>
      <td class="tdbg"><input type="input" name="warning_remain_count" value="<?php echo $rs['warning_remain_count'] ?>"></td>
    </tr>

<?php
print <<<EOT
    <tr>
      <td wIdth="300" align="right" class="tdbg"><strong>Disable SIM when specific disconected cause:</strong></td>
      <td class="tdbg"><input type="checkbox" name="auto_disable_s_call" id="auto_disable_s_call" onclick="show_tr(this.checked, 'auto_disable_s_call_div')" $auto_disable_s_call_checked></td>
    </tr>
    <tr style="display:{$auto_disable_s_call_display}" Id='auto_disable_s_call_div'>
      <td wIdth="300" align="right" class="tdbg"><strong>Specific disconected cause:</strong></td>
      <td class="tdbg"><input type="input" name="auto_disable_s_call_msg" value="$rs[auto_disable_s_call_msg]"> </td>
    </tr>
    <tr>
      <td wIdth="300" align="right" class="tdbg"><strong>Reboot Moudle when specific disconected cause:</strong></td>
      <td class="tdbg"><input type="checkbox" name="auto_reboot_s_call" id="auto_reboot_s_call" onclick="show_tr(this.checked, 'auto_reboot_s_call_div')" $auto_reboot_s_call_checked></td>
    </tr>
    <tr style="display:{$auto_reboot_s_call_display}" Id='auto_reboot_s_call_div'>
      <td wIdth="300" align="right" class="tdbg"><strong>Specific disconected cause:</strong></td>
      <td class="tdbg"><input type="input" name="auto_reboot_s_call_msg" value="$rs[auto_reboot_s_call_msg]"> </td>
    </tr>

    <tr>
      <td wIdth="300" align="right" class="tdbg"><strong>Disable SIM when low ASR:</strong></td>
      <td class="tdbg"><input type="checkbox" name="auto_disable_low_asr" id="auto_disable_low_asr" onclick="show_tr(this.checked, 'auto_disable_low_asr_div')" $auto_disable_low_asr_checked></td>
    </tr>
    <tr style="display:{$auto_disable_low_asr_display}" Id='auto_disable_low_asr_div'>
      <td wIdth="300" align="right" class="tdbg"><strong>Low ASR threshold:</strong></td>
      <td class="tdbg">lower than <input type="input" name="auto_disable_asr_threshold" value="$rs[auto_disable_asr_threshold]" size="1" onblur="onfocus_check_integer(this, 1, 100)">% in last<input type="input" name="auto_disable_asr_number" value="$rs[auto_disable_asr_number]" size="1" onblur="onfocus_check_integer(this, 1, 10000)">calls</td>
    </tr>
    <tr>
      <td wIdth="300" align="right" class="tdbg"><strong>Disable SIM when low ACD:</strong></td>
      <td class="tdbg"><input type="checkbox" name="auto_disable_low_acd" id="auto_disable_low_acd" onclick="show_tr(this.checked, 'auto_disable_low_acd_div')" $auto_disable_low_acd_checked></td>
    </tr>
    <tr style="display:{$auto_disable_low_acd_display}" Id='auto_disable_low_acd_div'>
      <td wIdth="300" align="right" class="tdbg"><strong>Low ACD threshold:</strong></td>
      <td class="tdbg">Less than<input type="input" name="auto_disable_acd_threshold" value="$rs[auto_disable_acd_threshold]" size="1" onblur="onfocus_check_integer(this, 1, 10000)">Seconds in last<input type="input" name="auto_disable_acd_number" value="$rs[auto_disable_acd_number]" size="1" onblur="onfocus_check_integer(this, 1, 10000)">connected calls </td>
    </tr>
EOT;
?>
    <tr> 
      <td height="40" colspan="2" align="center" class="tdbg"><input name="Action" type="hIdden" Id="Action" value="Modify"> 
        <input  type="submit" name="Submit" value="Modify" style="cursor:hand;"> 
 
        &nbsp;<input name="Cancel" type="button" Id="Cancel" value="Cancel" onClick="window.location.href='sys.php'" style="cursor:hand;"></td>
    </tr>
  </table>
  </form>
					  </td> 
					</tr>
</table>
				
</body>
</html>

