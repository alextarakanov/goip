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

if($action=="del")
{
	$ErrMsg="";
	if(empty($id))
		$ErrMsg ='<br><li>请选择一项</li>';
	if($ErrMsg!="")
		WriteErrMsg($ErrMsg);
	else{
		$db->query("DELETE FROM scheduler where id='$id'");
		$query=$db->query("select sim_team_id from sim_team where scheduler_id='$id'");
		while($row=$db->fetch_array($query)) {
			$send[]=my_pack2(SCH_UPDATE, $row[sim_team_id], 0, 0, 0); //nend update scheduler
			$db->query("update sim_team set scheduler_id='0' where scheduler_id='$id'");
		}
		sendto_xchanged($send);
		WriteSuccessMsg("<br><li>删除成功</li>","?");
	}
}
elseif($action=="select")
{
	$rs=$db->fetch_array($db->query("select id,group_id from scheduler where id='$id'"));
	if($rs[0]){
		$db->query("update sim_team set scheduler_id=$rs[id] where sim_team_id=$rs[group_id]");
		$row=$db->fetch_array($db->query("select sim_team_id,r_interval,s_interval,type,period_chaos,period_fixed,period_daily from scheduler,sim_team where sim_team.scheduler_id=scheduler.id and scheduler.id='$id' order by sim_team_id"));
		$send[]=my_pack($row, SCH_UPDATE);
		sendto_xchanged($send);
		WriteSuccessMsg("<br><li>设定成功</li>","?");
	}
	else WriteErrMsg("<li>not hvae this scheduler:$name</li>");
}
elseif($action=="saveadd")
{
	$group_id=myaddslashes($_REQUEST['group_id']);
	$ErrMsg="";
	if(empty($name))        
		$ErrMsg ='<br><li>please input name</li>';
	if(!$group_id)
		$ErrMsg .='<br><li>please choose group</li>';
	$no_t=$db->fetch_array($db->query("select id from scheduler where name='$name' and group_id='$group_id'"));
	if($no_t[0])            
		$ErrMsg .='<br><li>This name already exist: '.$name.' in group id: '.$group_id.'</li>';
	if($ErrMsg!="")         
		WriteErrMsg($ErrMsg);
	else{     
		$type=$_REQUEST['type'];
		$sql="insert into scheduler set name='$name', group_id='$group_id', ";
		if($type == 'period_chaos' || $type == 'period_fixed'){
			$sql.="type='$type', $type='";
			for($i=0;$i<=6;$i++)
				for($j=1;$j<=9;$j++){
					$s=$_REQUEST['period_start'.$i.$j];
					$e=$_REQUEST['period_end'.$i.$j];
					if($s && $e && $s!=':' && $e!=':')
						$sql.="$i)$s-$e;";
				}
			$sql.="'";
		}
		else if($type == 'daily'){
			$sql.="type='$type', period_daily='";
			$i=7;
			for($j=1;$j<=9;$j++){
				$s=$_REQUEST['period_start'.$i.$j];
				$e=$_REQUEST['period_end'.$i.$j];
				if($s && $e && $s!=':' && $e!=':') $sql.="$s-$e;";
			}
			$sql.="'";
		}
		else {
			$sql.="type='cycle', r_interval='$_REQUEST[r_interval]',s_interval='$_REQUEST[s_interval]'";
		}
		$db->query($sql);                              

		WriteSuccessMsg("<br><li>添加成功</li>","?");
	}
}
else if($action=='savemodify'){
	$group_id=myaddslashes($_REQUEST['group_id']);
	if(!$id)
		$ErrMsg .='<br><li>please choose one</li>';
        if(empty($name))
                $ErrMsg .='<br><li>please input name</li>';
	if(!$group_id)
		$ErrMsg .='<br><li>please choose group</li>';
	$type=$_REQUEST['type'];
        $no_t=$db->fetch_array($db->query("select id from scheduler where name='$name' and group_id='$group_id' and id!='$id'"));
        if($no_t[0])
                $ErrMsg .='<br><li>This name already exist: '.$name.' in group id: '.$group_id.'</li>';
	if($ErrMsg!=""){
		WriteErrMsg($ErrMsg);
		die;
	}
	$type=$_REQUEST['type'];
	$sql="update scheduler set name='$name', group_id='$group_id', ";
	if($type == 'period_chaos' || $type == 'period_fixed'){
		$sql.="type='$type', $type='";
		for($i=0;$i<=6;$i++)
			for($j=1;$j<=9;$j++){
				$s=$_REQUEST['period_start'.$i.$j];
				$e=$_REQUEST['period_end'.$i.$j];
				if($s && $e && $s!=':' && $e!=':')
					$sql.="$i)$s-$e;";
			}
		$sql.="' where id='$id'";
	}
	else if($type == 'daily'){
		$sql.="type='$type', period_daily='";
		$i=7;
		for($j=1;$j<=9;$j++){
			$s=$_REQUEST['period_start'.$i.$j];
			$e=$_REQUEST['period_end'.$i.$j];
			if($s && $e && $s!=':' && $e!=':') $sql.="$s-$e;";
		}
		$sql.="' where id='$id'";
	}
	else {
		$sql.="type='cycle', r_interval='$_REQUEST[r_interval]',s_interval='$_REQUEST[s_interval]' where id='$id'";
	}
	$db->query($sql);                              
	$row=$db->fetch_array($db->query("select sim_team_id,r_interval,s_interval,type,period_chaos,period_fixed from scheduler,sim_team where sim_team.scheduler_id=scheduler.id and scheduler.name='$name' order by sim_team_id"));
	$send[]=my_pack($row, SCH_UPDATE);
	sendto_xchanged($send);

	WriteSuccessMsg("<br><li>保存成功</li>","?");
}
else if($action=='modify' || $action=="truncate" || $action=="add"){
	//if($action="reset" && !$name) $action=="add";
	//if(!$_REQUEST['id']){
		//die("please choose a scheduler!");
	//}
	if($_REQUEST[follow])
		$row=$db->fetch_array($db->query("select * from scheduler_tem where id='$_REQUEST[follow]'"));
	elseif($action=="truncate" || $action=="add")
		$row=$db->fetch_array($db->query("select * from scheduler where 0"));
	else 
		$row=$db->fetch_array($db->query("select * from scheduler where id='$_REQUEST[id]'"));

	if(!$id) $action="add";
	if($action=="truncate") $row[id]=$id;
	//echo "111111111111".$action;
	//print_r($row);
	if($row['type']=='cycle'){
		
	}
	//elseif($row['type']=='period_chaos' || $row['type']=='period_fixed'){
	if($row['period_chaos'] || $row['period']){
		if($_REQUEST[follow]) $a=explode(";", $row['period']);
		else $a=explode(";", $row['period_chaos']);
		$i=0;
		$xingqi=array();
		$start_h=array();
		$start_m=array();
		$stop_h=array();
		$stop_m=array();
		$limit_t=array();
		foreach($a as $limit){ //each period
			if($limit=='') break;
			sscanf($limit, "%1s)%2s:%2s-%2s:%2s",$xingqi0,$start_h0,$start_m0,$stop_h0,$stop_m0);
			$check++;
			$xingqi[]=$xingqi0;
			$start_h[$xingqi0][]=$start_h0;
			$start_m[$xingqi0][]=$start_m0;
			$stop_h[$xingqi0][]=$stop_h0;
			$stop_m[$xingqi0][]=$stop_m0;
			//$limit_t[$xingqi0][]=$limit0;
			//echo "111 $check $limit, $xingqi0, $start_h0,$start_m0,$stop_h0,$stop_m0,$limit0 <br>";
			$i++;
		}
		//print_r($start_h);
	}

	if($row['period_daily']){
		//if($_REQUEST[follow]) $a=explode(";", $row['period']);
		//else $a=explode(";", $row['period_chaos']);
		$a=explode(";", $row['period_daily']);
		$i=0;
		$xingqi=array();
		$d_start_h=array();
		$d_start_m=array();
		$d_stop_h=array();
		$d_stop_m=array();
		$d_limit_t=array();
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
			//$limit_t[$xingqi0][]=$limit0;
			//echo "111 $check $limit, $xingqi0, $start_h0,$start_m0,$stop_h0,$stop_m0,$limit0 <br>";
			$i++;
		}

	}

	$query=$db->query("select * from scheduler_tem order by name");
	while($row0=$db->fetch_array($query)) {
		$template_db[$row0[id]]=$row0['name'];
	}
	$query=$db->query("select * from sim_team order by sim_team_name");
	while($row0=$db->fetch_array($query)){
		$group_db[$row0[sim_team_id]]=$row0['sim_team_name'];
	}
} else {
	$action='main';
	if($_REQUEST['group_id']) $where="where group_id='".$_REQUEST['group_id']."'";
	$query=$db->query("SELECT count(*) AS count FROM scheduler $where");
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
        $query=$db->query("SELECT * FROM scheduler left join sim_team on scheduler.group_id=sim_team.sim_team_id $where ORDER BY sim_team.sim_team_name,name LIMIT $start_limit,$perpage");
            
	while($row=$db->fetch_array($query)){
/*
		$row['period']=$row[$row[type]];
		if($row['scheduler_id']==$row['id']) $row['selected']='selected';
		if($row['type']=='cycle') $row['type']='循环';
		else $row['type']='时间段';
		else if($row['type']=='daily') $row['period']=$row['period_daily'];
*/
                $row['period']=$row[$row[type]];
                if($row['scheduler_id']==$row['id']) $row['selected']='selected';
                if($row['type']=='period_chaos') $row['type']="weekly";
                else if($row['type']=='daily') $row['period']=$row['period_daily'];

		//if($row['type']=='period_chaso') $row['period']==$row['period_chaso'];
		//else if($row['type']=='period_fixed') 
		$rsdb[]=$row;
		//$template_db[$row[id]]=$row['name'];
	}
}
require_once ('scheduler.htm');
?>
