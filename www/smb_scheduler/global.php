<?php

//!defined('OK') && exit('ForbIdden');
$perpage='50';
require_once('inc/conn.inc.php');
require_once('sqlsend.php');
require_once("excel_class.php");
/*
if(!isset($_COOKIE['username'])) {
	require_once ('login.php');
}
*/
//$PHP_SELF=$_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
//$URL='http://'.$_SERVER['HTTP_HOST'].substr($PHP_SELF,0,strrpos($PHP_SELF, '/')+1);
//echo $URL;
//print_r($_SERVER);
$URL=$_SERVER['HTTP_REFERER'];
//echo $URL;

function sendto_xchanged($send)
{
        global $phpsvrport;
        global $smbdocker;
	if(!$send) return;
        $flag=0;
        if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {
                echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
                exit;
        }
        foreach($send as $sendbuf){
                //echo "s:$sendbuf,".strlen($sendbuf);
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
                                        if($buf==$sendbuf){
                                                //echo "get !";
                                                $flag=1;
                                                break;
                                        }
                                }
                        }
                }
        }
        //if($flag)
                //echo "已更新";
        //else 
                //echo "已更新,但xchanged或schedule进程未响应，请检查进程";
}

function multi($count,$page,$numofpage,$url) {
	if ($numofpage<=1){
		return ;
	}else{
		$fengye="<a href=\"{$url}&page=1\"><< </a>";
		$flag=0;
		for($i=$page-3;$i<=$page-1;$i++)
		{
			if($i<1) continue;
			$fengye.=" <a href={$url}&page=$i>&nbsp;$i&nbsp;</a>";
		}
		$fengye.="&nbsp;&nbsp;<b>$page</b>&nbsp;";
		if($page<$numofpage)
		{
			for($i=$page+1;$i<=$numofpage;$i++)
			{
				$fengye.=" <a href={$url}&page=$i>&nbsp;$i&nbsp;</a>";
				$flag++;
				if($flag==4) break;
			}
		}
		$fengye.=" <input type='text' size='2' style='height: 16px; border:1px solId #E7E3E7' onkeydown=\"javascript: if(event.keyCode==13) location='{$url}&page='+this.value;\"> <a href=\"{$url}&page=$numofpage\"> >></a> &nbsp;(共 $numofpage 页)";
		return $fengye;
	}
}

//**************************************************
//过程名:showpage
//作  用:显示“上一页 下一页”等信息
//参  数:sfilename  ----链结位址
//$CurrentPage
//       totalnumber ----总数量
//       maxperpage  ----每页数量
//       ShowTotal   ----是否显示总数量
//       ShowAllPages ---是否用下拉清单显示所有页面以供跳转。有某些页面不能使用，否则会出现JS错误。
//       strUnit     ----计数单位
//**************************************************
function showpage($sfilename,$CurrentPage,$totalnumber,$maxperpage,$ShowTotal,$ShowAllPages,$strUnit){
	if($totalnumber%$maxperpage==0)
    	$n= $totalnumber / $maxperpage;
  	else
    	$n= (int)($totalnumber / $maxperpage)+1;
  	
  	$strTemp= "<table align='center'><tr><td>";
	if($ShowTotal==true)
		$strTemp=$strTemp . "共 <b>" . $totalnumber . "</b> " . $strUnit . " &nbsp;&nbsp;";
	if($CurrentPage<2)
    	$strTemp=$strTemp . "首页 上一页&nbsp;";
  	else{
    	$strTemp=$strTemp . "<a href='" . $sfilename . "page=1'>首页</a>&nbsp;";
    	$strTemp=$strTemp . "<a href='" . $sfilename . "page=" . ($CurrentPage-1) . "'>上一页</a>&nbsp;";
  	}

  	if ($n-$CurrentPage<1)
    		$strTemp=$strTemp . "下一页 尾页";
  	else{
    		$strTemp=$strTemp . "<a href='" . $sfilename . "page=" . ($CurrentPage+1) . "'>下一页</a>&nbsp;";
    		$strTemp=$strTemp . "<a href='" . $sfilename . "page=" . $n . "'>尾页</a>";
  	}
	
   	$strTemp=$strTemp . " &nbsp;页次：<strong><font color=red>" . $CurrentPage . "</font>/" . $n . "</strong>页 ";
    $strTemp=$strTemp . " &nbsp;<b>" . $maxperpage . "</b>" . $strUnit . "/页";
	
	if( $ShowAllPages=true){
		$strTemp=$strTemp . " &nbsp;转到：<select name='page' size='1' onchange=javascript:window.location='" . $sfilename . "page=" . "'+this.options[this.selectedIndex].value;>" ;
		$c_page=500;
		$i=$CurrentPage-$c_page;
		if($i<1) $i=1;
		if($n>$CurrentPage+$c_page) $n=$CurrentPage+$c_page;
		for($i=1;$i<=$n;$i++){
			$strTemp=$strTemp . "<option value='" . $i . "'";
			if( (int)($CurrentPage)==(int)($i))
				$strTemp=$strTemp . " selected ";
			$strTemp=$strTemp . ">第" . $i . "页</option>"   ;
		}
		$strTemp=$strTemp . "</select>";
	}
	$strTemp=$strTemp . "</td></tr></table>";
	return $strTemp;
}

function showpage2($sfilename,$CurrentPage,$totalnumber,$maxperpage,$ShowTotal,$ShowAllPages,$strUnit,$form,$post){
	if($totalnumber%$maxperpage==0)
    	$n= $totalnumber / $maxperpage;
  	else
    	$n= (int)($totalnumber / $maxperpage)+1;
  	
  	$strTemp= "<table align='center'><tr><td>";
	if($ShowTotal==true)
		$strTemp=$strTemp . "共 <b>" . $totalnumber . "</b> " . $strUnit . " &nbsp;&nbsp;";
	if($CurrentPage<2)
    	$strTemp=$strTemp . "首页 上一页&nbsp;";
  	else{
    	$strTemp=$strTemp . "<span class='spanpage' onclick='return changepage(\"".$sfilename."page=1\", ".$form.")'>首页</span>&nbsp;";
    	$strTemp=$strTemp . "<span class='spanpage' onclick='return changepage(\"".$sfilename."page=".($CurrentPage-1)."\", ".$form.")'>上一页</span>&nbsp;";
  	}

  	if ($n-$CurrentPage<1)
    		$strTemp=$strTemp . "下一页 尾页";
  	else{
    		$strTemp=$strTemp . "<span class='spanpage' onclick='return changepage(\"".$sfilename."page=".($CurrentPage+1)."\", ".$form.")'>下一页</span>&nbsp;";
    		$strTemp=$strTemp . "<span class='spanpage' onclick='return changepage(\"".$sfilename."page=".$n."\", ".$form.")'>尾页</span>&nbsp;";
  	}
	
   	$strTemp=$strTemp . " &nbsp;页次：<strong><font color=red>" . $CurrentPage . "</font>/" . $n . "</strong>页 ";
    $strTemp=$strTemp . " &nbsp;<b>" . $maxperpage . "</b>" . $strUnit . "/页";
	
	if( $ShowAllPages=true){
		$strTemp=$strTemp . " &nbsp;转到：<select name='page' size='1' onchange='return selectchangepage(\"".$sfilename."\",this.options[this.selectedIndex].value, ".$form.")'>" ;
    	for($i=1;$i<=$n;$i++){
    		$strTemp=$strTemp . "<option value='" . $i . "'";
			if( (int)($CurrentPage)==(int)($i))
				$strTemp=$strTemp . " selected ";
			$strTemp=$strTemp . ">第" . $i . "页</option>"   ;
	    }
		$strTemp=$strTemp . "</select>";
	}
	$strTemp=$strTemp . "</td></tr></table>";
	return $strTemp;
}



function template($template) {
	return "../template/admin/$template.htm";
}

function WriteErrMsg($ErrMsg1)
{
	$strErr="<html><head><title>Error Information</title><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" ;
	$strErr=$strErr."<link href='style.css' rel='stylesheet' type='text/css'></head><body>" ;
	$strErr=$strErr."<table cellpadding=2 cellspacing=1 border=0 wIdth=400 class='border' align=center>"; 
	$strErr=$strErr."  <tr align='center'><td height='22' class='title'><strong>错误信息</strong></td></tr>" ;
	$strErr=$strErr."  <tr><td height='100' class='tdbg' valign='top'><b>原因:</b><br> $ErrMsg1</td></tr>" ;
	$strErr=$strErr."  <tr align='center'><td class='tdbg'><a href=javascript:history.back();>&lt;&lt; 返回</a></td></tr>" ;
	$strErr=$strErr."</table>" ;
	$strErr=$strErr."</body></html>" ;
	echo $strErr;
	exit;
}

//'**************************************************
////'过程名:WriteSuccessMsg
//'作  用:显示成功提示资讯
//'参  数:无
//**************************************************
function WriteSuccessMsg($SuccessMsg,$URL)
{
	$strErr="<html><head><title>Success Information</title><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" ;
	$strErr=$strErr."<link href='style.css' rel='stylesheet' type='text/css'></head><body>" ;
	$strErr=$strErr."<table cellpadding=2 cellspacing=1 border=0 wIdth=400 class='border' align=center>"; 
	$strErr=$strErr."  <tr align='center'><td height='22' class='title'><strong>恭喜你</strong></td></tr>" ;
	$strErr=$strErr."  <tr><td height='100' class='tdbg' valign='top'>$SuccessMsg</td></tr>" ;
	$strErr=$strErr."  <tr align='center'><td class='tdbg'><a href=$URL>确定</a></td></tr>" ;
	$strErr=$strErr."</table>" ;
	$strErr=$strErr."</body></html>" ;
	echo $strErr;
	exit;
}

function export_sim($where, $orderby)
{
	global $db;
	$query=$db->query("SELECT sim.* FROM sim  $where $orderby");
	//echo "SELECT sim.* FROM sim  $where $orderby";
	$filename="SIM_".date("YmdHim").".xls";

	$return[0][0]="Slot ID";
	$return[0][1]="ONLINE";
	$return[0][2]="IMSI";
	$return[0][3]="ICCID";
	$return[0][4]="NUMBER";

	$i=1;
	while($rs=$db->fetch_array($query)) {
		if($rs['sim_login'] == 0 || $rs['sim_login'] == 12){
			$rs['alive']=0;
		}else $rs['alive']=1;
		$rs['imsi']=str_replace('"','',$rs['imsi']);
		$rs['iccid']=str_replace(' ','',$rs['iccid']);
		$return[$i][0]="".$rs['sim_name'];
		$return[$i][1]="".$rs['alive'];
		$return[$i][2]="".$rs['imsi'];
		$return[$i][3]="".$rs['iccid'];
		$return[$i][4]="".$rs['simnum'];
		$i++;
	}
	Create_Excel_File($filename,$return);
	//exit;
}
?>
