<?php
define("OK", true);
session_start();
if(!isset($_SESSION['usertype'])){
        require_once ('login.php');
        exit;
}
require_once("global.php");
//require_once("sqlsend.php");
//$action="main";
$action=$_REQUEST['action'];
$name=myaddslashes($_REQUEST['name']);
$id=myaddslashes($_REQUEST['id']);
//print_r($_POST);
//die;
if($action=="del")
{
	$ErrMsg="";
	if(empty($id))
		$ErrMsg ='<br><li>Please choose one</li>';
	if($ErrMsg!="")
		WriteErrMsg($ErrMsg);
	else{
		$db->query("DELETE FROM auto_simulation where id='$id'");
/*
		$query=$db->query("select sim_team_id from sim_team where auto_simulation_id='$id'");
		while($row=$db->fetch_array($query)) {
			$send[]=my_pack2('LCSCLL', $checksum, SCH_UPDATE, $row[sim_team_id], 0, 0, 0); //nend update scheduler
		}
*/
		//$db->query("update device_line set auto_simulation_id='0',next_auto_dial_time='0' where auto_simulation_id='$id'");
		$db->query("delete from human_ref where auto_simulation_id='$id'");
		$send[]=pack('La*', $checksum, "auto_update");
		sendto_xchanged($send);
		WriteSuccessMsg("<br><li>Delete successful</li>","?");
	}
}
elseif($action=="saveadd")
{
	$ErrMsg="";
	if(empty($name))        
		$ErrMsg ='<br><li>please input name</li>';
	$no_t=$db->fetch_array($db->query("select id from auto_simulation where name='$name'"));
	if($no_t[0])            
		$ErrMsg .='<br><li>This name already exist: '.$name.'</li>';
	if($ErrMsg!="")         
		WriteErrMsg($ErrMsg);
	else{    
		$i=7;
		for($j=1;$j<=9;$j++){
			$s=$_REQUEST['period_start'.$i.$j];
			$e=$_REQUEST['period_end'.$i.$j];
			if($s && $e && $s!=':' && $e!=':') $sql_p.="$s-$e|";
                } 
		$db->query("insert into auto_simulation set name='$name',dial_num='$_POST[dial_num]',period_min='$_POST[period_min]',period_max='$_POST[period_max]',talk_time_min='$_POST[talk_time_min]',talk_time_max='$_POST[talk_time_max]',next_time='$_POST[next_time]',disable='$_POST[disable]',period_setting='$sql_p',period_type='$_POST[period_type]'");
		$send[]=pack('La*', $checksum, "auto_update");
		sendto_xchanged($send);
		WriteSuccessMsg("<br><li>Add Success</li>","?");
	}
}
else if($action=='savemodify'){
	//print_r($_POST);
	if(!$id)
		$ErrMsg .='<br><li>please choose one</li>';
        if(empty($name))
                $ErrMsg .='<br><li>please input name</li>';
	$type=$_REQUEST['type'];
        $no_t=$db->fetch_array($db->query("select id from auto_simulation where name='$name'and id!='$id'"));
        if($no_t[0])
                $ErrMsg .='<br><li>This name already exist: '.$name.'</li>';
	if($ErrMsg!=""){
		WriteErrMsg($ErrMsg);
		die;
	}
	$i=7;
	for($j=1;$j<=9;$j++){
		$s=$_REQUEST['period_start'.$i.$j];
		$e=$_REQUEST['period_end'.$i.$j];
		if($s && $e && $s!=':' && $e!=':') $sql_p.="$s-$e|";
	}
	$sql="update auto_simulation set name='$name', dial_num='$_POST[dial_num]',period_min='$_POST[period_min]', period_max='$_POST[period_max]',talk_time_min='$_POST[talk_time_min]',talk_time_max='$_POST[talk_time_max]',next_time='$_POST[next_time]',disable='$_POST[disable]',period_setting='$sql_p',period_type='$_POST[period_type]' where id='$id'";
	$db->query($sql);                              
	$send[]=pack('La*', $checksum, "auto_update");
	sendto_xchanged($send);

	WriteSuccessMsg("<br><li>Save Success</li>","?");
}
else if($action=='modify' || $action=="add"){
	if($$action=="add")
		$rs=$db->fetch_array($db->query("select * from auto_simulation where 0"));
	else 
		$rs=$db->fetch_array($db->query("select * from auto_simulation where id='$_REQUEST[id]'"));
	//print_r($rs);
	$ch[$rs[period_type]]="selected";
	if(!$id) {
		$action="add";
		$ch[0]="selected";
	}       
	else $ch[$rs[period_type]]="selected";
	$a=explode("|", $rs['period_setting']);
	$i=0;   
	$xingqi=array();
	$d_start_h=array();
	$d_start_m=array();
	$d_stop_h=array();
	$d_stop_m=array();
	$xingqi0=0;
	foreach($a as $limit){ //each period
		if($limit=='') break;
		sscanf($limit, "%2s:%2s-%2s:%2s",$start_h0,$start_m0,$stop_h0,$stop_m0);
		$check++;
		//$xingqi[]=$xingqi0;
		$d_start_h[$xingqi0][]=$start_h0;
		$d_start_m[$xingqi0][]=$start_m0;
                $d_stop_h[$xingqi0][]=$stop_h0;
                $d_stop_m[$xingqi0][]=$stop_m0;
                //echo "111 $check $limit, $xingqi0, $start_h0,$start_m0,$stop_h0,$stop_m0,$limit0 <br>";
                $i++;
        }
}
else if($action=="grecv"){
                if(empty($_GET['id']))
                        WriteErrMsg("<br><li>plesae choose a human simulation!</li>");
                $id=$_GET[id];
                $query=$db->query("SELECT count(*) AS count FROM device_line");
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
                $query=$db->query("SELECT device_line.id FROM device_line right join human_ref on device_line.id=human_ref.line_id where human_ref.auto_simulation_id=$id order by device_line.id");
                $strs0=array();
                $rcount=0;
                while($row=$db->fetch_array($query)) {
                        $rcount++;
                        $strs0[]=$row['id'];
                }
                //echo $rcount;
                $fenye=showpage2("human.php?action=grecv&id=$id&",$page,$count,$perpage,true,true,"线","myform","boxs");
                //if(_GET)
                $query=$db->query("(select device_line.*, 1 as 'in' from device_line where id in (select line_id from human_ref where human_ref.auto_simulation_id=$id ) order by line_name ASC limit 99999999)
                                union all
                                (select device_line.*,0 from device_line where id not in (select line_id from human_ref where human_ref.auto_simulation_id=$id ) order by line_name ASC limit 99999999) 
                                        LIMIT $start_limit,$perpage");
                while($row=$db->fetch_array($query)) {

                        if($row[in]==1){
                                $row['yes']="in";
                        }
                        else if($row[in]==0){
                                $row['yes']="none";
                        }
                        else {
                                $row['yes']="other";
                        }
                        if($row['line_status'] == 0 || $row['line_status'] == 12){
                                $row['alive']="OFFLINE";
                        }
                        elseif($row['line_status'] == 11){
                                $row['alive']="ONLINE";
                        }
                        elseif($row['line_status'] == 20){
                                $row['alive']="IDLE";
                        }
                        elseif($row['line_status'] == 21){
                                $row['alive']="BUSY";
                        }
/*
                        elseif($row['line_status'] == 13){
                                $row['alive']="IDLE";
                        }
                        elseif($row['line_status'] == 14){
                                $row['alive']="BUSY";
                        }
*/
                        //else if($row[groupsid]==$id)
                        //if($row['groupsid']==$_GET['id'])
                        $rsdb[]=$row;
                        $strs[]=$row['id'];
                }

                //$strs=array();
                $rsdblen=count($rsdb);
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

                        //$nrcount=count($strs0);
                        //if(count($strs0)) $strs0=array_unique($strs0);
                        //$strs0=&$strs;

                }
                else {
                        $nrcount=0;
                        $rsdblen=count($rsdb);
                        //print_r();
                        //print_r()
                        //for($i=0;$i<$rsdblen&& $rsdb[$i]['in'];$i++){
                        //unset($strs[$i]);
                        //$str.=$rsdb[$i]['id'].',';
                        ////$strs0[]=$rsdb[$i]['id'];
                        //$nrcount++;
                        //}
                }
                /*
                   for($i=0;$i<$rsdblen&& $rsdb[$i]['in'];$i++){

//$nrcount++;
}
                 */
//echo $nrcount;
foreach($strs0 as $v){
        $nrcount++;
        if(in_array($v,$strs)) continue;
        //$nrcount++;
        $str.=$v.",";

}
//print_r();
$str=substr($str,0,strlen($str)-1);
$nametmp=$db->fetch_array($db->query("SELECT name FROM auto_simulation where id=$_GET[id]"));
$groupsname='<font color="#FF0000">'.$nametmp[0].'</font></a>';
}
elseif($action=="greceivers"){

        if(empty($_GET['id']))
                WriteErrMsg("<br><li>plesae choose a human simulation!</li>");
        $id=$_GET[id];
        $strs=array();
        if($_POST['rstr']) $strs=explode(",",$_POST['rstr']);
        $num=$_POST['boxs'];
        for($i=0;$i<$num;$i++)
        {
                if(!empty($_POST["Id$i"])){
                        $strs[]=$_POST["Id$i"];
                }
        }
        //print_r($strs);
        //$strs=array_unique($strs);

        //$query=$db->query("select device_line.*,password from device_line left join rm_device on device_line.goip_name = rm_device.name where auto_simulation_id=$id");
	$query=$db->query("select line_id from human_ref where auto_simulation_id=$id");

        while($row=$db->fetch_array($query)) {
                $flag=0;
                foreach($strs as $rkey => $rvalue){
                        if($row[0]==$rvalue){
                                unset($strs[$rkey]); //不用insert了；
                                $flag=1;
                                break;
                        }
                        //else $insertstr.=$row[0].",";
                }
                if(!$flag) {
                        $delstrs.=$row[0].","; //数据库有，post没有，需要删除
                        //$row[goip_team_id]=0;
                        //$send[]=my_pack($row, GOIP_ADD);
                }
                //else $insertstr.=$row[0].",";
        }
        if($delstrs){
                $delstrs=substr($delstrs,0,strlen($delstrs)-1);
                //WriteSuccessMsg("delete from receiver where id in ($delstrs)","groups.php?action=recv&id=$id");
                //echo "update goip set goip_team_id=0 where goipid in ($delstrs)";
                //$db->query("update device_line set auto_simulation_id=0,next_auto_dial_time='0' where id in ($delstrs)");
		$db->query("delete from human_ref where auto_simulation_id=$id and line_id in ($delstrs)");
        }
        if(count($strs)){
                //echo "111111";
                //$sql="insert into recvgroup values ";
                foreach($strs as $rkey => $rgid){
                        //$sql.="(NULL,$id,$rgid),";
                        $insertstr.=$rgid.",";
			$db->query("insert into human_ref set auto_simulation_id=$id,line_id=$rgid");
                }
                $insertstr=substr($insertstr,0,strlen($insertstr)-1);
                //WriteSuccessMsg($sql,"groups.php?action=recv&id=$id");
                //echo "update goip set goip_team_id=$id where goipid in ($insertstr)";
                //$db->query("update device_line set auto_simulation_id=$id where id in ($insertstr)");
        }
        if($send)
                sendto_xchanged($send);
        WriteSuccessMsg("<br><li>Successfully Modified</li>","?action=grecv&id=$id");

}
 else {
	$action='main';
	$query=$db->query("SELECT count(*) AS count FROM auto_simulation");
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
        $fenye=showpage("?",$page,$count,$perpage,true,true,"项");
        $query=$db->query("SELECT * FROM auto_simulation order by name LIMIT $start_limit,$perpage");
            
	while($row=$db->fetch_array($query)){
		$row['period']=$row['period_min']."-".$row['period_max'];
		$row['talk_time']=$row['talk_time_min']."-".$row['talk_time_max'];
		if($row['disable']) $row['enable']='N';
		else $row['enable']='Y';
		$channel_on_c=0; $channel_all_c=0;
		$query1=$db->query("select line_status, count(*) as c from device_line where id in (select line_id from human_ref where auto_simulation_id=$row[id]) group by line_status"); 
		while($row1=$db->fetch_array($query1)){
                        if($row1['line_status']!=0 &&$row1['line_status']!=12) $channel_on_c+=$row1['c'];
                        $channel_all_c+=$row1['c'];
                }
		$row['channel_c']=$channel_on_c."/".$channel_all_c;
		$rsdb[]=$row;
	}
}
require_once ('human.htm');
?>
