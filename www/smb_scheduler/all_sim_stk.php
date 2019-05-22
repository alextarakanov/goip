<?php

define("OK", true);
session_start();
if(!isset($_SESSION['usertype'])){
        require_once ('login.php');
        exit;
}
require_once("global.php");

//print_r($_POST);
if(!$_REQUEST['order']){
	$_REQUEST['order']="asc";
}
if($_REQUEST['order']=="desc") $order_type2='asc';
else $order_type2='desc';
if(!$_REQUEST['order_key']){
	$_REQUEST['order_key']="sim_name";
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

function gen_sim_where()
{
	if($_REQUEST['chkAll0']) {
		$where=" where 1";
	}else if($_REQUEST['sim_name']){
		$where=" where sim_name='".$_REQUEST['sim_name']."' ";
	}else {
		$id=get_id();
		if(!$id) WriteErrMsg("Not choose one SIM Slot!");
		$where=" where sim_name in ($id)";
	}
	return $where;
}

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
				echo "<script language=\"javascript\">alert('测试网络 结果:\\nSlot Name:$data[sid]\\nsent:$data[sent] recv:$data[recv] lost:$data[lost] bad:$data[bad] dup:$data[dup] daley:$data[delay]ms')</script>";
				break;
			}
		}
	}
        else if($action=="modify")
        {       
                $name=$_GET['sim_name'];
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

                if($rs['remain_time']<0) $rs['remain_time'] = "";

                $query=$db->query("select line_name from device_line where goip_team_id='0' and line_name not in (select plan_line_name from sim where plan_line_name != '$rs[plan_line_name]') order by line_name ");
                while($row=$db->fetch_array($query)) {
                        $grsdb[]=$row;
                }       
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
		$auto_reset_remain=$_POST['auto_reset_remain'];
		$auto_reset_remain_s=$_POST['auto_reset_remain_s'];
		if($remain_time == "" || $remain_time < -1) $remain_time=-1;

		$sim_rs=$db->fetch_array($db->query("SELECT * FROM sim where sim_name='$sim_name'"));
		if($sim_rs['time_limit']!=$_POST['time_limit']) $t_remain.=",remain_time=$_POST[time_limit]";
		if($sim_rs['count_limit']!=$_POST['count_limit']) $t_remain.=",count_remain=$_POST[count_limit]";
		if($sim_rs['no_connected_limit']!=$_POST['no_connected_limit']) $t_remain.=",no_connected_remain=$_POST[no_connected_limit]";
		$db->query("UPDATE sim SET imei_mode='$imei_mode', sim_team_id='$team_id',dev_disable='$dev_disable',plan_line_name='$_POST[line_name]',imei='$imei',time_limit='$_POST[time_limit]',time_unit='$time_unit',no_ring_limit='$_POST[no_ring_limit]',no_answer_limit='$_POST[no_answer_limit]',short_call_limit='$_POST[short_call_limit]',short_time='$_POST[short_time]',no_ring_disable='$_POST[no_ring_disable]',no_answer_disable='$_POST[no_answer_disable]',short_call_disable='$_POST[short_call_disable]',count_limit='$_POST[count_limit]',no_connected_limit='$_POST[no_connected_limit]',auto_reset_remain='$auto_reset_remain',auto_reset_remain_s='$auto_reset_remain_s' $t_remain WHERE sim_name='$sim_name'");


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
                WriteSuccessMsg("<br><li>修改SIM Slot成功</li>","all_sim.php?bank_name=$_REQUEST[bank_name]&group_id=$_REQUEST[group_id]&order=$order_type&order_key=$order_key");
        }
	else if($action=="moremodify")
	{
		$sim_name=get_id();
		if(!$sim_name) WriteErrMsg("Not choose one SIM Slot!");

		//$name=$_GET['name'];
                $query=$db->query("select * from sim_team order by sim_team_id ");
                while($row=$db->fetch_array($query)) {                                                            
                        $prsdb[]=$row;                                                                            
                } 
	}
	elseif($action=="savemoremodify")
	{
		print_r($_POST);
		$password=$_POST['Password'];
		$name=$_POST['name'];
		$team_id=$_POST['team_id'];
                $id=$_GET[id];
		$dev_disable = $_POST['dev_disable'];
		$sim_name=$_POST['sim_name'];
		$imei=$_POST['imei'];
		$remain_time=$_POST['remain_time'];
		$time_unit=$_POST['time_unit'];
		$imei_mode=$_POST['imei_mode'];
		//$where=gen_sim_where();
		if($remain_time == "" || $remain_time < -1) $remain_time=-1;
                if($_POST[no_ring_limit] == "" || $_POST[no_ring_limit] < -1) $_POST[no_ring_limit]=-1;
                if($_POST[no_answer_limit] == "" || $_POST[no_answer_limit] < -1) $_POST[no_answer_limit]=-1;
                if($_POST[short_call_limit] == "" || $_POST[short_call_limit] < -1) $_POST[short_call_limit]=-1;
                if($_POST[count_limit] == "" || $_POST[count_limit] < -1) $_POST[count_limit]=-1;
                if($_POST[no_connected_limit] == "" || $_POST[no_connected_limit] < -1) $_POST[no_connected_limit]=-1;
		//echo $sim_name;
		$ErrMsg="";
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			//$sim_rs=$db->fetch_array($db->query("SELECT * FROM sim where sim_name='$sim_name'"));
			//if($sim_rs['time_limit']!=$_POST[time_limit]) $t_remain=",remain_time=$_POST[time_limit]"; //重置时间
			$sql="UPDATE sim SET ";
			if($_POST['team_id_modify']) $sql.="sim_team_id='$team_id',";
			if($_POST['imei_mode_modify']) $sql.="imei_mode='$imei_mode',";
			if($_POST['time_limit_modify']) $sql.="time_limit='$_POST[time_limit]',";
			if($_POST['count_limit_modify']) $sql.="count_limit='$_POST[count_limit]',";
			if($_POST['no_connected_limit_modify']) $sql.="no_connected_limit='$_POST[no_connected_limit]',";
			if($_POST['time_unit_modify']) $sql.="time_unit='$time_unit',";
			if($_POST['no_ring_modify']) $sql.="no_ring_limit='$_POST[no_ring_limit]',no_ring_disable='$_POST[no_ring_disable]',";
			if($_POST['no_answer_modify']) $sql.="no_answer_limit='$_POST[no_answer_limit]',no_answer_disable='$_POST[no_answer_disable]',";
			if($_POST['short_call_modify']) $sql.="short_call_limit='$_POST[short_call_limit]',short_call_disable='$_POST[short_call_disable]',";
			if($_POST['short_time_modify']) $sql.="short_time='$_POST[short_time]',";
			if($_POST['dev_disable_modify']) {
				$sql.="dev_disable='$dev_disable',";
				if($dev_disable==1){
					$sql.="stk_reboot='0',stk_status='0',";
				}
			}
			if($_POSY['auto_reset_remain_modify']) $sql.="auto_reset_remain='$_POST[auto_reset_remain]',";
			if($_POSY['auto_reset_remain_s_modify']) $sql.="auto_reset_remain_s='$_POST[auto_reset_remain_s]',";
			$sql.="sim_login=sim_login where sim_name in ($sim_name)";
			$db->query($sql);

                        $query=$db->query("select sim.*,password from sim left join sim_bank on sim.bank_name=sim_bank.name $where");
			while($row=$db->fetch_array($query)) {
				if($row['imei']==NULL) $row['imei'] = "000000000000000";
				$imei_len=strlen($row['imei']);
				if($imei_len<15) $row['imei'] = sprintf("%15s",$row['imei']."000000000000000");
				if($row['remain_time']==NULL || $row['remain_time'] <-1 ) $row['remain_time'] = -1;
				$send[]=my_pack($row, SIM_ADD);
				if($row['imei_mode']==3) $send[]=my_pack($row, IMEI_IMSI_INFO);
				if($_POST['dev_disable_modify']) $send[]=my_pack2($row[dev_disable]?DEV_DISABLE:DEV_ENABLE, $row[sim_name], TYPE_SIM);
				if($_POST['team_id_modify'] && $team_id) $send[]=my_pack2(DEV_BINDING, $sim_name, 0);

			}

			sendto_xchanged($send);
			WriteSuccessMsg("<br><li>Modify SIM Slot successful!</li>","?bank_name=$_REQUEST[bank_name]&group_id=$_REQUEST[group_id]&order=$order_type&order_key=$order_key");
		}
	}
	else if($action=="reboot"){
		$send[]=my_pack2(MACHINE_REBOOT, $_GET['name'], TYPE_SIM);
		sendto_xchanged($send);
		WriteSuccessMsg("<br><li>The commond is sended to SIM Slot($_GET[name]).</li>","?bank_name=$_REQUEST[bank_name]&group_id=$_REQUEST[group_id]&order=$order_type&order_key=$order_key");
	}

	else if($action=="reset_limit"){

		$where=gen_sim_where();

		$db->query("update sim set no_ring_remain=0,no_answer_remain=0,short_call_remain=0,remain_time=time_limit,count_remain=count_limit,no_connected_remain=no_connected_limit $where");

		$query=$db->query("select sim.*,password from sim left join sim_bank on sim.bank_name=sim_bank.name $where");
		while($row=$db->fetch_array($query)) {
			if($row['imei']==NULL) $row['imei'] = "000000000000000";
			$imei_len=strlen($row['imei']);
			if($imei_len<15) $row['imei'] = sprintf("%15s",$row['imei']."000000000000000");
			if($row['remain_time']==NULL || $row['remain_time'] <-1 ) $row['remain_time'] = -1;
			$send[]=my_pack($row, SIM_ADD);
			//$send[]=my_pack2(RESET_LIMIT, $row['sim_name']);
		}
		//print_r($send);

		sendto_xchanged($send);
		WriteSuccessMsg("<br><li>The reset limit commond is sended to Server.($sim_name)</li>","?bank_name=$_REQUEST[bank_name]&group_id=$_REQUEST[group_id]&order=$order_type&order_key=$order_key");
	}

	else if($action=="reset_period"){
		$where=gen_sim_where();
		
		$query=$db->query("select sim_name from sim $where");
		while($row=$db->fetch_array($query)) {
			$send[]=my_pack2(RESET_LIMIT, $row['sim_name']);
		}
		sendto_xchanged($send);
		WriteSuccessMsg("<br><li>The commond is sended to Server.($sim_name)</li>","?bank_name=$_REQUEST[bank_name]&group_id=$_REQUEST[group_id]&order=$order_type&order_key=$order_key");

	}	
	else if($action=="awaken"){
		$where=gen_sim_where();

		$query=$db->query("select sim_name from sim $where");
		while($row=$db->fetch_array($query)) {
			$send[]=my_pack2(DEV_ACTIVING, $row['sim_name'], TYPE_SIM);
		}
		sendto_xchanged($send);
		WriteSuccessMsg("<br><li>The commond is sended to Server.</li>","?bank_name=$_REQUEST[bank_name]&group_id=$_REQUEST[group_id]&order=$order_type&order_key=$order_key");
	}
	else $action="main";

	}
	else $action="main";

	//if($_COOKIE['adminname']=="admin")	
	if($action=="main")
	{
		$query=$db->query("SELECT * FROM system WHERE 1 ");
		$rs=$db->fetch_array($query);
		$limit_t_w=$rs['warning_remain_time'];
		$limit_c_w=$rs['warning_remain_count'];
		//print_r($_POST);
		$where="where 1 ";
		if(!empty($_GET['bank_name']))
			$where.="and bank_name='$_GET[bank_name]'";
/*
		if($_GET[sim_name])
			$where.="and sim_name='$_GET[name]'";
*/
		$bank_ch="";
		if(!empty($_GET['group_id']) && $_GET['group_id']!=-1)
			$where.="and sim.sim_team_id='$_GET[group_id]'";
		else if($_GET['group_id']==-1){
			$where.="and sim.sim_team_id='0'";
		}
		else {
			$bank_ch="checked";
		}
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
		$fenye=showpage2("?bank_name=$_GET[bank_name]&group_id=$_GET[group_id]&order_key=$order_key&order=$order&",$page,$count,$perpage,true,true,"编","myform","boxs");
		if($order_key) $orderby=" ORDER BY `$order_key` $order_type,sim.sim_name ";
		else $orderby=" ORDER BY sim.sim_name ";
		$query=$db->query("SELECT sim.*,sim_team.sim_team_name,sim.line_name FROM sim left join sim_team on sim.sim_team_id=sim_team.sim_team_id left join device_line on device_line.line_name=sim.line_name $where $orderby LIMIT $start_limit,$perpage");
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
				$row['bind_type'] = 'Group Mode';
			}
			else {
				$row['bind_type'] = 'Fixed Mode';
			}
/*
			if($row['remain_time']<0){
				$row['remain_time']='NO LIMIT';
			}
*/
			
			if($row['imei_mode']==1) $row['imei_type_name']="Random";
			else if($row['imei_mode']==0) $row['imei_type_name']="GoIP default";
			else if($row['imei_mode']==2) $row['imei_type_name']="Set with SIM";
			$row['remain_limit']="total remain time: ".$row['remain_time']."/".$row['time_limit']."\n";
                        $row['remain_limit'].="total remain count: ".$row['count_remain']."/".$row['count_limit']."\n";
                        $row['remain_limit'].="total remain no connected count: ".$row['no_connected_remain']."/".$row['no_connected_limit']."\n";
			$row['remain_limit'].="period remain time: ".$row['period_time_remain']."\n";
			$row['remain_limit'].="period remain count: ".$row['period_count_remain']."";
			
			$row['limit_title']="no ring consecutive count: ".$row['no_ring_remain']."/".$row['no_ring_limit']."\n";
			$row['limit_title'].="no answer consecutive count: ".$row['no_answer_remain']."/".$row['no_answer_limit']."\n";
			$row['limit_title'].="short call consecutive count: ".$row['short_call_remain']."/".$row['short_call_limit']."";
			
			$row['remain_state'] = '<font color="#00FF00">Enough</font>';
			if($row['stk_status']==-1){
				$row['remain_state'] = '<font color="#FF8000">Warning</font>';
			}
			else if($row['remain_time']==0 || $row['period_time_remain']==0 || $row['period_count_remain']==0 || $row['count_remain']==0 || $row['no_connected_remain']==0){
				$row['remain_state'] = '<font color="#FF0000">Exhaust</font>';
			}
			else if(($row['remain_time']<=$limit_t_w && $row['remain_time']!=-1) || ($row['period_time_remain']<=$limit_t_w && $row['period_time_remain']!=-1)|| ($row['period_count_remain']<=$limit_c_w && $row['period_count_remain']!=-1) || ($row['count_remain']<=$limit_c_w && $row['count_remain']!=-1) || ($row['no_connected_remain']<=$limit_c_w && $row['no_connected_remain']!=-1)){
				$row['remain_state'] = '<font color="#FF8000">Warning</font>';
			}
			$row['limit_state'] = '<font color="#00FF00">Enough</font>';
			if(($row['no_ring_remain'] >= $row['no_ring_limit'] && $row['no_ring_limit']!=-1)|| ($row['no_answer_remain']>=$row['no_answer_limit'] && $row['no_answer_limit']!=-1) || ($row['short_call_remain'] >= $row['short_call_limit'] && $row['short_call_limit']!=-1) || $row['stk_status']==4|| $row['stk_status']==5){
				$row['limit_state'] = '<font color="#FF8000">Warning</font>';
			}
			$rsdb[]=$row;
			$strs[]=$row['sim_name'];
			//$bank_db[bank_name]=$row[bank_name];
			
		}
		
		$bank_select="<select name=\"name\"  style=\"width:80px\" onchange=\"javascript:window.location='?bank_name='+this.options[this.selectedIndex].value+'&group_id=$_REQUEST[group_id]'\">\n\t<option value=\"0\">All</option>\n";
		$query=$db->query("SELECT name from sim_bank ORDER BY name");
		while($row=$db->fetch_array($query)) {
			$bank_db['name']=$row['name'];
			if($_GET['bank_name']==$row['name']) 
				$bank_select.="\t<option value=\"$row[name]\" selected>$row[name]</option>\n";
			else 
				$bank_select.="\t<option value=\"$row[name]\">$row[name]</option>\n";
		}
		$bank_select.="</select>";

		$group_select="<select name=\"group_id\"  style=\"width:80px\" onchange=\"javascript:window.location='?bank_name=$_REQUEST[bank_name]&sim_name=$_REQUEST[sim_name]&group_id='+this.options[this.selectedIndex].value\">\n\t<option value=\"\">All</option>\n";
		$query=$db->query("SELECT * from sim_team ORDER BY sim_team_id");
		while($row=$db->fetch_array($query)) {
			$group_db['id']=$row['sim_team_name'];
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
//print_r();

$str=substr($str,0,strlen($str)-1);

	}
require_once ('all_sim.htm');

?>
