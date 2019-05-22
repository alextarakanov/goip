<?php
session_start();
if(!isset($_SESSION['username'])){
	require_once ('login.php');
	exit;
}

//if($_COOKIE['permissions'] > 1)	
//	die("需要admin权限！");	
define("OK", true);
require_once("global.php");
require_once('inc/conn.inc.php');


function sendto_xchanged2($send)
{
        global $phpsvrport;
        if(!$send) return;
        $flag=0;
        if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {
                echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
                exit;
        }
        foreach($send as $sendbuf){
                echo "s:$sendbuf,".strlen($sendbuf);
                if (socket_sendto($socket,$sendbuf, strlen($sendbuf), 0, $smbdocker, $phpsvrport)===false){
                        echo ("sendto error");
                        exit;
                }
                for($i=0;$i<1;$i++){
                        $read=array($socket);
                        $err=socket_select($read, $write = NULL, $except = NULL, 5);
                        if($err>0){
                                if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port))==false){
                                        echo("recvform error".socket_strerror($ret)."<br>");
                                        continue;
                                }
                                else{   
                                        if($buf=="try to reboot"){
                                                //echo "get !";
                                                $flag=1;
                                                break;
                                        }
                                }
                        }
                }
        }
        if($flag)
                echo "已发送重启指令";
        else {
		passthru("./run_scheduler");
                echo "已发送重启命令";
	}
}

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
	$buf=file_get_contents("./inc/server_type.cfg");
	sscanf($buf, "TYPE='%[^']'", $type);
	if($rs['auto_disable_s_call']) $auto_disable_s_call_checked="checked";
	else $auto_disable_s_call_display="none";
	if($rs['auto_disable_low_asr']) $auto_disable_low_asr_checked="checked";
	else $auto_disable_low_asr_display="none";
	if($rs['auto_disable_low_acd']) $auto_disable_low_acd_checked="checked";
	else $auto_disable_low_acd_display="none";
	if($rs['auto_disable']) $gsm_logout_checked="checked";
	else $gsm_logout_display="none";

	/*
	if(empty($adminId)){
		$FoundErr=true;
		$ErrMsg=$ErrMsg."<br><li>用户名或密码错误!</li> $password";
	}
	else{

	}
*/
  }
}
else {
	$sysname=$_POST['sysname'];
	$maxword=$_POST['maxword'];
	$lan=$_POST['lan'];
	if($_POST['auto_disable_s_call']=='on') $_POST['auto_disable_s_call']=1;
	else $_POST['auto_disable_s_call']=0;
	if($_POST['auto_disable_low_asr']=='on') $_POST['auto_disable_low_asr']=1;
	else $_POST['auto_disable_low_asr']=0;
	if($_POST['auto_disable_low_acd']=='on') $_POST['auto_disable_low_acd']=1;
	else $_POST['auto_disable_low_acd']=0;
        if($_POST['gsm_logout_enable']=='on') $_POST['gsm_logout_enable']=1;
        else $_POST['gsm_logout_enable']=0;

	$query=$db->query("UPDATE system SET sysname='$sysname',lan=$lan, warning_remain_time='$_POST[warning_remain_time]',warning_remain_count='$_POST[warning_remain_count]',auto_disable_s_call='$_POST[auto_disable_s_call]',auto_disable_s_call_msg='$_POST[auto_disable_s_call_msg]',auto_disable_low_asr='$_POST[auto_disable_low_asr]',auto_disable_asr_threshold='$_POST[auto_disable_asr_threshold]',auto_disable_asr_number='$_POST[auto_disable_asr_number]',auto_disable_low_acd='$_POST[auto_disable_low_acd]',auto_disable_acd_threshold='$_POST[auto_disable_acd_threshold]',auto_disable_acd_number='$_POST[auto_disable_acd_number]',auto_disable='$_POST[gsm_logout_enable]',auto_disable_logout_min='$_POST[gsm_logout_time_limit]' where 1");
	if($_POST['type']==1) $type=1;
	else $type=0;
        $buf=file_get_contents("./inc/server_type.cfg");
        sscanf($buf, "TYPE='%[^']'", $type0);
	if($type!=$type0){
		file_put_contents("./inc/server_type.cfg", "TYPE='$type'");
		$send[]=pack('La*', $checksum, "reboot");
		//sendto_xchanged2($send);
	}
	WriteSuccessMsg("<br><li>保存成功</li>","sys.php");	
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="style.css" rel="stylesheet" type="text/css">
<title>系统参数设置</title>
<script src="check.js"></script>
<script language="JavaScript" type="text/JavaScript">
function check()
{
  var dec_num=/^[0-9]+$/;
  if (document.myform.maxword.value=="" || !dec_num.test(document.myform.maxword.value))
  {
    alert("信息最大字数输入错误!");
	document.myform.maxword.focus();
	return false;
  }
}
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
    <td width="92%" height="25"><strong>当前位置：系统参数设置</strong></td>
  </tr>
</table>
<form method="post" action="sys.php?action=save" name="myform" onSubmit="javascript:return check();">
  <br>
  <br>
  <table wIdth="600" border="0" align="center" cellpadding="2" cellspacing="1" class="border" >
    <tr class="title"> 
      <td height="22" colspan="2"> <div align="center"><strong>系统参数参数设定</strong></div></td>
    </tr>
    <tr>
      <td wIdth="300" align="right" class="tdbg"><strong>底层状态:</strong></td>
      <td class="tdbg"><?php if($alive == 1) echo '已连接';else echo '未连接';?></td>
    </tr>
    <tr> 
      <td wIdth="300" align="right" class="tdbg"><strong>系统名称:</strong></td>
      <td class="tdbg"><input type="input" name="sysname" value="<?php echo $sysname ?>"></td>
    </tr>
    <tr> 
      <td wIdth="300" align="right" class="tdbg"><strong>默认语言:</strong></td>
      <td class="tdbg"><select name="lan">
	  <option value="1" <?php if($lan==1) echo 'selected' ?>>简体中文</option>
	  <option value="3" <?php if($lan==3) echo 'selected' ?>>英语</option>
	</select>
      </td>
    </tr>
    <tr> 
      <td wIdth="300" align="right" class="tdbg"><strong>服务器模式(修改后需手动重启后台):</strong></td>
      <td class="tdbg"><select name="type">
          <option value="0" <?php if($type!=1) echo 'selected' ?>>完整的服务器</option>
          <option value="1" <?php if($type==1) echo 'selected' ?>>SIM Bank做数据中转</option>
      </select></td>
    </tr>

    <tr>
      <td wIdth="300" align="right" class="tdbg"><strong>剩余通话时间的警告界限(分):</strong></td>
      <td class="tdbg"><input type="input" name="warning_remain_time" value="<?php echo $rs['warning_remain_time'] ?>"></td>
    </tr>    
    <tr>
      <td wIdth="300" align="right" class="tdbg"><strong>剩余呼叫次数的警告界限(次):</strong></td>
      <td class="tdbg"><input type="input" name="warning_remain_count" value="<?php echo $rs['warning_remain_count'] ?>"></td>
    </tr>
<?php
print <<<EOT
    <tr>
      <td wIdth="300" align="right" class="tdbg"><strong>当收到特定挂机信息时禁用卡:</strong></td>
      <td class="tdbg"><input type="checkbox" name="auto_disable_s_call" id="auto_disable_s_call" onclick="show_tr(this.checked, 'auto_disable_s_call_div')" $auto_disable_s_call_checked></td>
    </tr>
    <tr style="display:{$auto_disable_s_call_display}" Id='auto_disable_s_call_div'>
      <td wIdth="300" align="right" class="tdbg"><strong>特定的挂机信息:</strong></td>
      <td class="tdbg"><input type="input" name="auto_disable_s_call_msg" value="$rs[auto_disable_s_call_msg]"> </td>
    </tr>
    <tr>
      <td wIdth="300" align="right" class="tdbg"><strong>当ASR低时禁用卡:</strong></td>
      <td class="tdbg"><input type="checkbox" name="auto_disable_low_asr" id="auto_disable_low_asr" onclick="show_tr(this.checked, 'auto_disable_low_asr_div')" $auto_disable_low_asr_checked></td>
    </tr>
    <tr style="display:{$auto_disable_low_asr_display}" Id='auto_disable_low_asr_div'>
      <td wIdth="300" align="right" class="tdbg"><strong>ASR低的判定:</strong></td>
      <td class="tdbg">ASR低于<input type="input" name="auto_disable_asr_threshold" value="$rs[auto_disable_asr_threshold]" size="1" onblur="onfocus_check_integer(this, 1, 100)">%于近<input type="input" name="auto_disable_asr_number" value="$rs[auto_disable_asr_number]" size="1" onblur="onfocus_check_integer(this, 1, 10000)">次呼叫中</td>
    </tr>
    <tr>
      <td wIdth="300" align="right" class="tdbg"><strong>当ACD低时禁用卡:</strong></td>
      <td class="tdbg"><input type="checkbox" name="auto_disable_low_acd" id="auto_disable_low_acd" onclick="show_tr(this.checked, 'auto_disable_low_acd_div')" $auto_disable_low_acd_checked></td>
    </tr>
    <tr style="display:{$auto_disable_low_acd_display}" Id='auto_disable_low_acd_div'>
      <td wIdth="300" align="right" class="tdbg"><strong>ACD低的判定:</strong></td>
      <td class="tdbg">ACD低于<input type="input" name="auto_disable_acd_threshold" value="$rs[auto_disable_acd_threshold]" size="1" onblur="onfocus_check_integer(this, 1, 10000)">秒于近<input type="input" name="auto_disable_acd_number" value="$rs[auto_disable_acd_number]" size="1" onblur="onfocus_check_integer(this, 1, 10000)">次通话中 </td>
    </tr>
    <tr>
      <td wIdth="300" align="right" class="tdbg"><strong>启用GSM掉线自动禁用卡:</strong></td>
      <td class="tdbg"><input type="checkbox" name="gsm_logout_enable" id="gsm_logout_enable" onclick="show_tr(this.checked, 'gsm_logout_enable_div')" {$gsm_logout_checked}></td>
    </tr>
    <tr style="display:{$gsm_logout_display}" Id='gsm_logout_enable_div'>
      <td wIdth="200" align="right" class="tdbg"><strong>GSM掉线的时限（分钟）:</strong></td>
      <td class="tdbg"><input type="input" name="gsm_logout_time_limit" value="$rs[auto_disable_logout_min]"> </td>
    </tr> 
    <tr> 
      <td height="40" colspan="2" align="center" class="tdbg"><input name="Action" type="hIdden" Id="Action" value="Modify"> 
        <input  type="submit" name="Submit" value="保 存" style="cursor:hand;"> 
 
        &nbsp;<input name="Cancel" type="button" Id="Cancel" value="取 消" onClick="window.location.href='sys.php'" style="cursor:hand;"></td>
    </tr>
  </table>
  </form>
					  </td> 
					</tr>
</table>
				
</body>
</html>
<!--
EOT;
?>
