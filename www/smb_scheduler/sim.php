<?php

define("OK", true);
session_start();
if(!isset($_SESSION['usertype'])){
        require_once ('login.php');
        exit;
}
require_once("global.php");
//!defined('OK') && exit('ForbIdden');
//$UserName=$_COOKIE['adminname'];

if(isset($_GET['action'])) {
	$action=$_GET['action'];

	if($action=="netcheck"){
		$action="main";
		$sendbuf=my_pack2(DEV_NETCHECK, $_GET[sim_name],TYPE_SIM);
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
				echo "<script language=\"javascript\">alert('测试网络 结果:\\nSlot ID:$data[sid]\\nsent:$data[sent] recv:$data[recv] lost:$data[lost] bad:$data[bad] dup:$data[dup] daley:$data[delay]ms')</script>";
				break;
			}
		}
	}
	else if($action=="modify")
	{
		$name=$_GET['name'];
                $query=$db->query("select * from sim_team order by sim_team_id ");
                while($row=$db->fetch_array($query)) {                                                            
                        $prsdb[]=$row;                                                                            
                } 
		$rs=$db->fetch_array($db->query("SELECT * FROM sim where sim_name='$name'"));
		if($rs[dev_disable]) $ck2='selected';
		else $ck1='selected';

		if($rs[imei_mode]==1) $imei_ck2='selected';
		else if($rs[imei_mode]==0) $imei_ck1='selected';
		else if($rs[imei_mode]==2) $imei_ck3='selected';
		else if($rs[imei_mode]==3) $imei_ck4='selected';

		if($rs[no_ring_disable]) $wa_ck1='checked';
		if($rs[no_answer_disable]) $wa_ck2='checked';
		if($rs[short_call_disable]) $wa_ck3='checked';
		if($rs[count_limit_no_connect]) $ci_no_connect_ck='checked';

		if($rs['remain_time']<0) $rs['remain_time'] = "";

                $query=$db->query("select line_name from device_line where goip_team_id='0' and line_name not in (select plan_line_name from sim where plan_line_name != '$rs[plan_line_name]') order by line_name ");
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
                $id=$_GET[id];
		$dev_disable = $_POST['dev_disable'];
		$sim_name=$_POST['sim_name'];
		$imei=$_POST['imei'];
		$remain_time=$_POST['remain_time'];
		$time_unit=$_POST['time_unit'];
		$imei_mode=$_POST['imei_mode'];
		if($remain_time == "" || $remain_time < -1) $remain_time=-1;
		
		$ErrMsg="";
		/*
		   if($oldname != $name){
		   $no_t=$db->fetch_array($db->query("select id from goip where name='".$name."'" ));
		   if($no_t[0])
		   $ErrMsg	.='<br><li>已存在ID: '.$name.'</li>';
		   }	
		 */
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			$sim_rs=$db->fetch_array($db->query("SELECT * FROM sim where sim_name='$sim_name'"));
			if($sim_rs['time_limit']!=$_POST['time_limit']) $t_remain.=",remain_time=$_POST[time_limit]";
			if($sim_rs['count_limit']!=$_POST['count_limit']) $t_remain.=",count_remain=$_POST[count_limit]";
			if($sim_rs['no_connected_limit']!=$_POST['no_connected_limit']) $t_remain.=",no_connected_remain=$_POST[no_connected_limit]";
                        $db->query("UPDATE sim SET imei_mode='$imei_mode', sim_team_id='$team_id',dev_disable='$dev_disable',plan_line_name='$_POST[line_name]',imei='$imei',time_limit='$_POST[time_limit]',time_unit='$time_unit',no_ring_limit='$_POST[no_ring_limit]',no_answer_limit='$_POST[no_answer_limit]',short_call_limit='$_POST[short_call_limit]',short_time='$_POST[short_time]',no_ring_disable='$_POST[no_ring_disable]',no_answer_disable='$_POST[no_answer_disable]',short_call_disable='$_POST[short_call_disable]',count_limit='$_POST[count_limit]',no_connected_limit='$_POST[no_connected_limit]',count_limit_no_connect='$_POST[count_limit_no_connect]' $t_remain WHERE sim_name='$sim_name'");

                                $query=$db->query("select sim.*,password from sim left join sim_bank on sim.bank_name=sim_bank.name where sim_name='$sim_name'");
                                while($row=$db->fetch_array($query)) {
					if($row['imei']==NULL) $row['imei'] = "000000000000000";
        				$imei_len=strlen($row['imei']);
					if($imei_len<15) $row['imei'] = sprintf("%15s",$row['imei']."000000000000000");
					if($row['remain_time']==NULL || $row['remain_time'] <-1 ) $row['remain_time'] = -1;
					$send[]=my_pack($row, SIM_ADD);
					if($row['imei_mode']==3) $send[]=my_pack($row, IMEI_IMSI_INFO);
 
                                }
                                //sendto_xchanged($send);

			if($dev_disable != $_POST[old_disable]){ //disable改变
				$query=$db->query("select * from sim where sim_name='$sim_name'");
				//unset($send);
				while($row=$db->fetch_array($query)) {
					//echo "1111111";
					$send[]=my_pack2($row[dev_disable]?DEV_DISABLE:DEV_ENABLE, $row[sim_name], TYPE_SIM);
				}
				//sendto_xchanged($send);
                        }
			//unset($send);
			//echo DEV_BINDING;
			if(!$team_id){
				$send[]=my_pack2(DEV_BINDING, $sim_name, $_POST[line_name]);
			}
			else {
				$send[]=my_pack2(DEV_BINDING, $sim_name, 0);
			}
			sendto_xchanged($send);
			WriteSuccessMsg("<br><li>修改SIM Slot成功</li>","sim.php?name=$_POST[bank_name]");
		}
	}
	else if($action=="reboot"){
		$send[]=my_pack2(MACHINE_REBOOT);
		sendto_xchanged($send);
		WriteSuccessMsg("<br><li>The commond is sended to SIM Slot($_GET[name]).</li>","sim.php?name=$_POST[bank_name]");
	}
	else if($action=="reset_limit"){
		//echo $action;
		$sim_name=$_REQUEST['sim_name'];
		$db->query("update sim set no_ring_remain=0,no_answer_remain=0,short_call_remain=0,remain_time=time_limit,count_remain=count_limit,no_connected_remain=no_connected_limit where sim_name='$sim_name'");
		
		$query=$db->query("select sim.*,password from sim left join sim_bank on sim.bank_name=sim_bank.name where sim_name='$sim_name'");
		while($row=$db->fetch_array($query)) {
			if($row['imei']==NULL) $row['imei'] = "000000000000000";
			$imei_len=strlen($row['imei']);
			if($imei_len<15) $row['imei'] = sprintf("%15s",$row['imei']."000000000000000");
			if($row['remain_time']==NULL || $row['remain_time'] <-1 ) $row['remain_time'] = -1;
			$send[]=my_pack($row, SIM_ADD);
		}
		sendto_xchanged($send);
		WriteSuccessMsg("<br><li>The commond is sended to Server.</li>","sim.php?name=$_REQUEST[bank_name]");
	}
	else if($action=="reset_period"){
		//echo $action;
		$send[]=my_pack2(RESET_LIMIT, $_REQUEST['sim_name']);
		sendto_xchanged($send);
		WriteSuccessMsg("<br><li>The commond is sended to Server.</li>","sim.php?name=$_REQUEST[bank_name]");
	}	
	else if($action=="awaken"){
		//echo $action;
		$send[]=my_pack2(DEV_ACTIVING, $_REQUEST['sim_name'], TYPE_SIM);
		sendto_xchanged($send);
		WriteSuccessMsg("<br><li>The commond is sended to Server.</li>","sim.php?name=$_REQUEST[bank_name]");
	}
	else $action="main";

	}
	else $action="main";

	//if($_COOKIE['adminname']=="admin")	
	if($action=="main")
	{
		if($_GET[name])
			$where="where bank_name='$_GET[name]'";
		$query=$db->query("SELECT count(*) AS count FROM sim $where" );
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
		$query=$db->query("SELECT sim.*,sim_team.*,sim.line_name FROM sim left join sim_team on sim.sim_team_id=sim_team.sim_team_id left join device_line on device_line.line_name=sim.line_name $where ORDER BY sim.sim_name LIMIT $start_limit,$perpage");
		while($row=$db->fetch_array($query)) {
			if($row['sim_login'] == 0 || $row['sim_login'] == 12){
				$row['alive']='<font color="#FF0000">OFFLINE</font>';
			}
			elseif($row['sim_login'] == 11){
				$row['alive']='<font color="#00FF00">ONLINE</font>';
			}
			elseif($row['sim_login'] == 13){
				$row['alive']='<font color="#00FF00">IDLE</font>';
			}
			elseif($row['sim_login'] == 14){
				$row['alive']="BUSY";
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
                        if($row['sim_team_id']){
                                $row['bind_type'] = '组调度绑定';
                        }
                        else {
                                $row['bind_type'] = '手动固定绑定';
                        }
/*
			if($row['remain_time']<0){
				$row['remain_time']='NO LIMIT';
			}
*/
			
			if($row['imei_mode']==1) $row['imei_type_name']="Random";
			else if($row['imei_mode']==0) $row['imei_type_name']="GoIP default";
			else if($row['imei_mode']==2) $row['imei_type_name']="Set with Slot";
			else if($row['imei_mode']==3) $row['imei_type_name']="Random with IMSI";
			$row['remain_limit'].="total remain time: ".$row['remain_time']."/".$row['time_limit']."\n";
                        $row['remain_limit'].="total remain count: ".$row['count_remain']."/".$row['count_limit']."\n";
                        $row['remain_limit'].="total remain no connected count: ".$row['no_connected_remain']."/".$row['no_connected_limit']."\n";

			$row['remain_limit'].="period remain time: ".$row['period_time_remain']."\n";
			$row['remain_limit'].="period remain count: ".$row['period_count_remain']."";
			
			$row['limit_title'].="no ring consecutive count: ".$row['no_ring_remain']."/".$row['no_ring_limit']."\n";
			$row['limit_title'].="no answer consecutive count: ".$row['no_answer_remain']."/".$row['no_answer_limit']."\n";
			$row['limit_title'].="short call consecutive count: ".$row['short_call_remain']."/".$row['short_call_limit']."";
			
			$limit_t_w=20;
			$limit_c_w=10;
			$row['remain_state'] = '<font color="#00FF00">Enough</font>';
			if($row['remain_time']==0 || $row['period_time_remain']==0 || $row['period_count_remain']==0 || $row['count_remain']==0 || $row['no_connected_remain']==0){
				$row['remain_state'] = '<font color="#FF0000">Exhaust</font>';
			}
			else if(($row['remain_time']<=$limit_t_w && $row['remain_time']!=-1) || ($row['period_time_remain']<=$limit_t_w && $row['period_time_remain']!=-1)|| ($row['period_count_remain']<=$limit_c_w && $row['period_count_remain']!=-1) || ($row['count_remain']<=$limit_c_w && $row['count_remain']!=-1) || ($row['no_connected_remain']<=$limit_c_w && $row['no_connected_remain']!=-1)){
				$row['remain_state'] = '<font color="#FF8000">Warning</font>';
			}
			$row['limit_state'] = '<font color="#00FF00">Enough</font>';
			if(($row['no_ring_remain'] >= $row['no_ring_limit'] && $row['no_ring_limit']!=-1)|| ($row['no_answer_remain']>=$row['no_answer_limit'] && $row['no_answer_limit']!=-1) || ($row['short_call_remain'] > $row['short_call_limit'] && $row['short_call_limit']!=-1)){
				$row['limit_state'] = '<font color="#FF8000">Warning</font>';
			}
			$rsdb[]=$row;
		}
}
require_once ('sim.htm');

?>
