<?php

define("OK", true);
session_start();
if(!isset($_SESSION['usertype'])){
        require_once ('login.php');
        exit;
}
require_once("global.php");
if(!$_REQUEST['order']){
        $_REQUEST['order']="asc";
}
if($_REQUEST['order']=="desc") $order_type2='asc';
else $order_type2='desc';
if(!$_REQUEST['order_key']){
        $_REQUEST['order_key']="line_name";
}

$order_type=$_REQUEST['order'];
$order_key=$_REQUEST['order_key'];

function get_id()
{       
        if($_REQUEST['chkAll0']) return "all SIM Slots";
        $num=$_REQUEST['boxs'];
        for($i=0;$i<$num;$i++)
        {
                if(!empty($_REQUEST["Id$i"])){
                        if($id=="")
                                $id=$_REQUEST["Id$i"];
                        else
                                $id=$_REQUEST["Id$i"].",$id";
                }
        }
        if($_REQUEST['rstr']) {
                if($id=="")
                        $id=$_REQUEST['rstr'];
                else
                        $id=$_REQUEST['rstr'].",$id";

        }
        return $id;
}

function gen_line_where()
{
        if($_REQUEST['chkAll0']) {
                $where=" where 1";
        }else if($_REQUEST['line_name']){
		$where=" where line_name in (".$_REQUEST['line_name'].") ";
        }else {
                $id=get_id();
                if(!$id) WriteErrMsg("Not choose one GoIP Line!");
                $where=" where line_name in ($id)";
        }
        return $where;
}

function gen_plan_where()
{
        if($_REQUEST['chkAll0']) {
                $where=" where plan_line_name!=''";
        }else if($_REQUEST['line_name']){
                $where=" where plan_line_name in (".$_REQUEST['line_name'].") ";
        }else {
                $id=get_id();
                if(!$id) WriteErrMsg("Not choose one GoIP Line!");
                $where=" where plan_line_name in ($id)";
        }
        return $where;
}

if(isset($_GET['action'])) {
	$action=$_GET['action'];

	if($action=="netcheck"){
		$action="main";
		$sendbuf=my_pack2(DEV_NETCHECK, $_GET[line_name],TYPE_GOIP);
		if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {
			echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
			exit;
		}
		$socks[]=$socket;
		//echo "s:$sendbuf,".strlen($sendbuf);
		if (socket_sendto($socket,$sendbuf, strlen($sendbuf), 0, $smbdocker, $phpsvrport)===false)
			echo ("sendto error");
		for($i=0;$i<2;$i++){
			$read=array($socket);
			$err=socket_select($read, $write = NULL, $except = NULL, 5);
			if($err>0){
				if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port))==false){
					//echo("recvform error".socket_strerror($ret)."<br>");
					continue;
				}
				else{
					if($buf==$sendbuf){
						$flag=1;
						break;
					}
				}
			}
		}
		if(!$flag)
			die("Mydify Success,but cannot get response from process named 'xchange' or 'scheduler'. please check process.");
		$timer=2;
		$timeout=7;
		for(;;){
			$read=$socks;
			flush();
			$err=socket_select($read, $write = NULL, $except = NULL, $timeout);
			if($err===false)
				echo "select error!";
			elseif($err==0){ //全体超时
				if(--$timer <= 0){
					echo "timeout!";
					break;
				}
			}
			else {
				if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port1))==false){
					//echo("recvform error".socket_strerror($ret)."<br>");
					continue;
				}
				$data=my_unpack_net_check($buf);
				$data[delay]=floor($data[delay])/1000;
				echo "<script language=\"javascript\">alert('网络测试结果:\\nLine Name:$data[sid]\\nsent:$data[sent] recv:$data[recv] lost:$data[lost] bad:$data[bad] dup:$data[dup] daley:$data[delay]ms')</script>";
				break;
			}
		}
	}
	elseif($action=="modify")
	{
		$name=$_GET['line_name'];

		$query=$db->query("select * from sim_team order by sim_team_id ");
		while($row=$db->fetch_array($query)) {
			$prsdb[]=$row;
		}

		$rs=$db->fetch_array($db->query("SELECT * FROM device_line where line_name='$name'"));
		if($rs[dev_disable]) $ck2='selected';
		else $ck1='selected';

		$query=$db->query("select sim_name,plan_line_name from sim where sim_team_id='0' and (plan_line_name='0' or plan_line_name='$name')  order by sim_name");
		while($row=$db->fetch_array($query)) {
			$grsdb[]=$row;
		}
		//if(!$s[0])
		//WriteErrMsg("<br><li>添加用户需要admin权限</li>"."$row[1]");
	}
	elseif($action=="savemodify")
	{
		$password=$_POST['Password'];
		$name=$_POST['name'];
		$oldname=$_POST['oldname'];
		$team_id=$_POST['team_id'];
		$old_team_id=$_POST['old_team_id'];
		$id=$_GET['id'];
		$zone=$_POST['zone'];
		$oldzone = $_POST['old_zone'];
		$dev_disable = $_POST['dev_disable'];
		$sim_name=$_POST['sim_name'];
		//$name=$_POST['name'];
		//echo "$_POST[zone], $_PSOT[old_zone]<br>";
		$ErrMsg="";
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			$db->query("UPDATE device_line SET goip_team_id='$team_id',dev_disable='$dev_disable' WHERE line_name='$_POST[line_name]'");
			$query=$db->query("select sim_name from sim where plan_line_name='$_POST[line_name]'");
			if($row=$db->fetch_array($query)) {
				if($team_id || $row['sim_name']!=$sim_name) $send[]=my_pack2(DEV_BINDING, $row['sim_name'], 0);
			}
			$db->query("UPDATE sim SET plan_line_name='0' WHERE plan_line_name='$_POST[line_name]'");
			$db->query("UPDATE sim SET plan_line_name='$_POST[line_name]' WHERE sim_name='$sim_name'");
			//echo "UPDATE sim SET plan_line_name='$_POST[line_name]' WHERE sim_name='$sim_name'";
			$query=$db->query("select device_line.*,password,zone from device_line left join rm_device on device_line.goip_name = rm_device.name where line_name='$_POST[line_name]'");
			while($row=$db->fetch_array($query)) {
				$send[]=my_pack($row, GOIP_ADD);
					if($dev_disable != $_POST['old_disable']){
						$send[]=my_pack2($row[dev_disable]?DEV_DISABLE:DEV_ENABLE, $row[line_name], TYPE_GOIP);
					}
                                }
				//sendto_xchanged($send);
			//unset($send);
			if(!$team_id)
				$send[]=my_pack2(DEV_BINDING, $sim_name, $_POST[line_name]);
			//else {
				//$send[]=my_pack2(DEV_BINDING, $_POST[old_plan_sim_name], 0);
			//}
			sendto_xchanged($send);
			WriteSuccessMsg("<br><li>Modify Successful</li>","all_device_line.php?goip_name=$_REQUEST[goip_name]&group_id=$_REQUEST[group_id]&order=$order_type&order_key=$order_key");
		}
	}
	elseif($action=="moremodify")
	{
                $line_name=get_id();
                if(!$line_name) WriteErrMsg("Not choose any GoIP channel!");

		$query=$db->query("select * from sim_team order by sim_team_id ");
		while($row=$db->fetch_array($query)) {
			$prsdb[]=$row;
		}
	}
	elseif($action=="savemoremodify")
	{
		$password=$_POST['Password'];
		$name=$_POST['name'];
		$oldname=$_POST['oldname'];
		$team_id=$_POST['team_id'];
		$old_team_id=$_POST['old_team_id'];
		$id=$_GET['id'];
		$zone=$_POST['zone'];
		$oldzone = $_POST['old_zone'];
		$dev_disable = $_POST['dev_disable'];
		$sim_name=$_POST['sim_name'];
		$line_name=$_POST['line_name'];
		$where=gen_line_where();
		$plan_where=gen_plan_where();
		//$name=$_POST['name'];
		//echo "$_POST[zone], $_PSOT[old_zone]<br>";
		$ErrMsg="";
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			$sql="UPDATE device_line SET ";
			if($_POST['team_id_modify']) $sql.="goip_team_id='$team_id',";
			if($_POST['dev_disable_modify']) $sql.="dev_disable='$dev_disable',";
			if($_POST['team_id_modify'] || $_POST['dev_disable_modify']){
				$sql.="line_name=line_name $where";
				$db->query($sql);

				$query=$db->query("select device_line.*,password,zone from device_line left join rm_device on device_line.goip_name = rm_device.name $where");
				while($row=$db->fetch_array($query)) {
					$send[]=my_pack($row, GOIP_ADD);
					if($_POST['dev_disable_modify']){
						$send[]=my_pack2($row[dev_disable]?DEV_DISABLE:DEV_ENABLE, $row[line_name], TYPE_GOIP);
					}
				}
				if($_POST['team_id_modify'] && !$team_id){
					$db->query("select sim_name,plan_line_name from sim $plan_where");
					if($row=$db->fetch_array($query)) {
						$send[]=my_pack2(DEV_BINDING, $row['sim_name'], $row['plan_line_name']);
					}
				}
				else if($_POST['team_id_modify'] && $team_id){
					$db->query("select sim_name from sim $paln_where");
					if($row=$db->fetch_array($query)) {
						$send[]=my_pack2(DEV_BINDING, $row['sim_name'], 0);
					}
					//$db->query("UPDATE sim SET plan_line_name='0' WHERE plan_line_name in ($line_name)");
                                }
				sendto_xchanged($send);
			}
			WriteSuccessMsg("<br><li>Modify Successful</li>","all_device_line.php?goip_name=$_REQUEST[goip_name]&group_id=$_REQUEST[group_id]&order=$order_type&order_key=$order_key");
		}
	}

	else if($action=="reboot"){
		$where=gen_line_where();
		$query=$db->query("select line_name from device_line $where");
		while($row=$db->fetch_array($query)) {
			$send[]=my_pack2(MACHINE_REBOOT, $row['line_name'], TYPE_GOIP);
		}
		sendto_xchanged($send);
		WriteSuccessMsg("<br><li>The commond is sended to goip().</li>","all_device_line.php?goip_name=$_REQUEST[goip_name]&group_id=$_REQUEST[group_id]&order=$order_type&order_key=$order_key");
	}
	else if($action=="reboot_module"){
		$where=gen_line_where();
                $query=$db->query("select line_name from device_line $where");
                while($row=$db->fetch_array($query)) {
			$send[]=my_pack2(MODULE_REBOOT, $row['line_name']);
		}
		sendto_xchanged($send);
		WriteSuccessMsg("<br><li>The commond is sended to goip's module($_GET[name]).","all_device_line.php?goip_name=$_REQUEST[goip_name]&group_id=$_REQUEST[group_id]&order=$order_type&order_key=$order_key");
	}
	else $action="main";

}
else $action="main";

//if($_COOKIE['adminname']=="admin")	
if($action=="main")
{
	$where="where 1 ";
	if(!empty($_GET['goip_name']))
		$where.="and goip_name='$_GET[goip_name]'";
/*
	if($_GET[line_name])
		$where.="and device_line.line_name='$_GET[line_name]'";
*/
	if($_GET[group_id] && $_GET[group_id]!=-1)
		$where.="and device_line.goip_team_id='$_GET[group_id]'";
	else if($_GET[group_id]==-1){
		$where.="and device_line.goip_team_id='0'";
	}

	$query=$db->query("SELECT count(*) AS count FROM device_line $where");
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
	$fenye=showpage2("?goip_name=$_GET[goip_name]&group_id=$_GET[group_id]&order_key=$order_key&order=$order&",$page,$count,$perpage,true,true,"编","myform","boxs");
	if($order_key) $orderby=" ORDER BY `$order_key` $order_type,device_line.line_name ";
	else $orderby=" ORDER BY device_line.line_name ";
	$query=$db->query("SELECT device_line.*,sim_team.*,sim.sim_name,zone,zone_tag,tag FROM device_line left join rm_device on device_line.goip_name = rm_device.name left join sim_team on device_line.goip_team_id=sim_team.sim_team_id left join sim on device_line.line_name=sim.line_name $where $orderby  LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		if($row['line_status'] == 0 || $row['line_status'] == 12){
			$row['alive']='<font color="#FF0000">OFFLINE</font>';
		}
		elseif($row['line_status'] == 11){
			$row['alive']='<font color="#00FF00">ONLINE</font>';
		}
		elseif($row['line_status'] == 20){
			$row['alive']='<font color="#00FF00">IDLE</font>';
		}
		elseif($row['line_status'] == 21){
			$row['alive']="BUSY";
		}

		if($row['gsm_status'] == 0 || $row['gsm_status'] == 30){
			$row['gsm']='<font color="#FF0000">LOGOUT</font>';
		}
		else if($row['gsm_status'] == 31){
			$row['gsm']='<font color="#00FF00">LOGIN</font>';
		}

		if($row['dev_disable'] == 0){
			$row['disable'] = '<font color="#00FF00">Enable</font>';
		}
		else {
			$row['disable'] = '<font color="#FF0000">Disable</font>';
		}

		if($row['sleep'] == 0){
			$row['sleep'] = '<font color="#00FF00">Active</font>';
		}
		else {
			$row['sleep'] = '<font color="#FF0000">Sleeping</font>';
		}

		if($row['goip_team_id']){
			$row['bind_type'] = 'Group';
		}
		else {
			$row['bind_type'] = 'Fixed';
		}
		$rsdb[]=$row;
	}

	$goip_select="<select name=\"name\"  style=\"width:80px\" onchange=\"javascript:window.location='?goip_name='+this.options[this.selectedIndex].value+'&sim_name=$_REQUEST[sim_name]&group_id=$_REQUEST[group_id]'\">\n\t<option value=\"0\" $bank_ch>All</option>\n";
	$query=$db->query("SELECT name from rm_device ORDER BY name");
	while($row=$db->fetch_array($query)) {
		$goip_db[name]=$row[name];
		if($_REQUEST['goip_name']==$row['name']) 
			$goip_select.="\t<option value=\"$row[name]\" selected>$row[name]</option>\n";
		else 
			$goip_select.="\t<option value=\"$row[name]\">$row[name]</option>\n";
	}
	$goip_select.="</select>";

	$group_select="<select name=\"group_id\"  style=\"width:80px\" onchange=\"javascript:window.location='?goip_name=$_REQUEST[goip_name]&sim_name=$_REQUEST[sim_name]&group_id='+this.options[this.selectedIndex].value\">\n\t<option value=\"\" $group_ch>All</option>\n";
	$query=$db->query("SELECT * from sim_team ORDER BY sim_team_id");
	while($row=$db->fetch_array($query)) {
		$group_db[id]=$row[sim_team_name];
		if($_GET['group_id']==$row['sim_team_id'])
			$group_select.="\t<option value=\"$row[sim_team_id]\" selected>$row[sim_team_name]</option>\n";
		else
			$group_select.="\t<option value=\"$row[sim_team_id]\">$row[sim_team_name]</option>\n";
	}
	if($_GET['group_id']==-1)
		$group_select.="\t<option value=\"-1\" selected>None</option>\n";
	else 
		$group_select.="\t<option value=\"-1\">None</option>\n";
	$group_select.="</select>";


	$strs0=array();
	if(isset($_POST['rstr'])){

		$nrcount=0;
		unset($strs0);
		$strs0=array();
		if($_POST['rstr']) $strs0=explode(",",$_POST['rstr']);

		$num=$_POST['boxs'];
		for($i=0;$i<$num;$i++)
		{   
			if(!empty($_POST["Id$i"])){
				$strs0[]=$_POST["Id$i"];
			}
		}

	}else {
		$nrcount=0;
		$rsdblen=count($rsdb);
	}
	$str="";
	foreach($strs0 as $v){
		$nrcount++;
		if(in_array($v,$strs)) continue;
		//$nrcount++;
		$str.=$v.",";

	}

	$str=substr($str,0,strlen($str)-1);

}
require_once ('all_device_line.htm');

?>
