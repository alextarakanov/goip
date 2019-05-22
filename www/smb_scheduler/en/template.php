<?php
define("OK", true);
session_start();
if(!isset($_SESSION['usertype'])){
        require_once ('login.php');
        exit;
}
require_once("global.php");

//$action="main";
$action=$_REQUEST['action'];
$name=myaddslashes($_REQUEST['name']);
$id=myaddslashes($_REQUEST['id']);

if($action=="del")
{
	$ErrMsg="";
	if(empty($id))
		$ErrMsg ='<br><li>Please choose one</li>';
	if($ErrMsg!="")
		WriteErrMsg($ErrMsg);
	else{
		$db->query("DELETE FROM scheduler_tem where id='$id'");
		WriteSuccessMsg("<br><li>Delete successful</li>","?");

	}
}
elseif($action=="saveadd")
{
	$ErrMsg="";                     
	if(empty($name))        
		$ErrMsg ='<br><li>please input name</li>';
	$no_t=$db->fetch_array($db->query("select id from scheduler_tem where name='$name'"));
	if($no_t[0])            
		$ErrMsg .='<br><li>This name already exist: '.$name.'</li>';
	if($ErrMsg!="")         
		WriteErrMsg($ErrMsg);
	else{     
		$type=$_REQUEST['type'];
		$sql="insert into scheduler_tem set name='$name',";
		if($type == 'period_chaos' || $type == 'period_fixed'){
			$sql.="type='$type', period='";
			for($i=0;$i<=6;$i++)
				for($j=1;$j<=9;$j++){
					$s=$_REQUEST['period_start'.$i.$j];
					$e=$_REQUEST['period_end'.$i.$j];
					if($s && $e && $s!=':' && $e!=':')
						$sql.="$i)$s-$e;";
				}
			$sql.="'";
		}
		else {
			$sql.="type='cycle', r_interval='$_REQUEST[r_interval]',s_interval='$_REQUEST[s_interval]'";
		}
		$db->query($sql);                              

		WriteSuccessMsg("<br><li>Add success</li>","template.php");
	}
}
else if($action=='savemodify'){
	if(!$id)
		$ErrMsg .='<br><li>please choose one</li>';
        if(empty($name))
                $ErrMsg .='<br><li>please input name</li>';
	$type=$_REQUEST['type'];
        $no_t=$db->fetch_array($db->query("select id from scheduler_tem where name='$name' and id!='$id'"));
        if($no_t[0])
                $ErrMsg .='<br><li>This name already exist: '.$name.'</li>';
	if($ErrMsg!=""){
		WriteErrMsg($ErrMsg);
		die;
	}
	$type=$_REQUEST['type'];
	$sql="update scheduler_tem set name='$name',";
	if($type == 'period_chaos' || $type == 'period_fixed'){
		$sql.="type='$type', period='";
		for($i=0;$i<=6;$i++)
			for($j=1;$j<=9;$j++){
				$s=$_REQUEST['period_start'.$i.$j];
				$e=$_REQUEST['period_end'.$i.$j];
				if($s && $e && $s!=':' && $e!=':')
					$sql.="$i)$s-$e;";
			}
		$sql.="' where id='$id'";
	}
	else {
		$sql.="type='cycle', r_interval='$_REQUEST[r_interval]',s_interval='$_REQUEST[s_interval]' where id='$id'";
	}
	$db->query($sql);                              

	WriteSuccessMsg("<br><li>Add success</li>","template.php");


}
else if($action=='modify' || $action=="truncate" || $action=="add"){
	//if($action="reset" && !$name) $action=="add";
	//if(!$_REQUEST['id']){
		//die("please choose a scheduler template!");
	//}
	if($_REQUEST[follow])
		$row=$db->fetch_array($db->query("select * from scheduler_tem where name='$_REQUEST[follow]'"));
	elseif($action=="truncate" || $action=="add")
		$row=$db->fetch_array($db->query("select * from scheduler_tem where 0"));
	else 
		$row=$db->fetch_array($db->query("select * from scheduler_tem where name='$_REQUEST[name]'"));

	if(!$name) $action="add";
	else $action="modify";
	//echo "111111111111".$action;
	//print_r($row);
	if($row[type]=='cycle'){
		
	}
	elseif($row['period']){
		$a=explode(";", $row['period']);
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
	}

	$query=$db->query("select * from scheduler_tem order by name");
	while($row0=$db->fetch_array($query)) {
		$template_db[$row0[id]]=$row0['name'];
	}

} else {

	$action='main';
	$query=$db->query("SELECT count(*) AS count FROM scheduler_tem");
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
        $fenye=showpage("?",$page,$count,$perpage,true,true,"rows");
        $query=$db->query("SELECT * FROM scheduler_tem ORDER BY name LIMIT $start_limit,$perpage");
            
	while($row=$db->fetch_array($query)){
		$rsdb[]=$row;
		//$template_db[$row[id]]=$row['name'];
	}
}
require_once ('template.htm');
?>
