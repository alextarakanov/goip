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
$sim_name=$_REQUEST['sim_name'];
if($action=='modify'){

	//print_r($_POST);
	$sim_name=$_REQUEST['sim_name'];
	$sql="update sim set period_limit='";
	for($i=0;$i<=6;$i++)
		for($j=1;$j<=9;$j++){
			$s=$_REQUEST['period_start'.$i.$j];
			$e=$_REQUEST['period_end'.$i.$j];
			$l=$_REQUEST['remain'.$i.$j];
			$c=$_REQUEST['remain_count'.$i.$j];
			$s1=$_REQUEST['remain_sms'.$i.$j];
			if(!isset($l) || $l==='') $l=-1;
			if(!isset($c) || $c==='') $c=-1;
			if(!isset($s1) || $s1==='') $s1=-1;
			if($s && $e && ($l>=0 || $c>=0 || $s1>=0) && $s!=':' && $e!=':' )
				$sql.="$i)$s-$e,$l,$c,$s1;";
	}
	$sql.="' where sim_name in ($sim_name)";
	$db->query($sql);
	//echo "select sim_name,period_limit from sim where sim_name in ($sim_name)";
	$query=$db->query("select sim_name,period_limit from sim where sim_name in ($sim_name)");
	while($row=$db->fetch_array($query)) {
		$send[]=my_pack($row, SIM_PERIOD);
	}
	sendto_xchanged($send);
	//WriteSuccessMsg("<br><li>保存成功</li>","?sim_name=$sim_name");
	WriteSuccessMsg("<br><li>保存成功</li>","all_sim.php");
}
else if($action=='modifymore'){
        $num=$_POST['boxs'];
        for($i=0;$i<$num;$i++)
        {       
                if(!empty($_POST["Id$i"])){
                        if($sim_name=="")
                                $sim_name=$_POST["Id$i"];
                        else    
                                $sim_name=$_POST["Id$i"].",$sim_name";
                }
        }
        if($_POST['rstr']) {
                if($sim_name=="")
                        $sim_name=$_POST['rstr'];
                else    
                        $sim_name=$_POST['rstr'].",$sim_name";
        
        }
        //echo "1".$sim_name;
        echo "<script language='javascript'>";
        echo "window.location = '?sim_name=$sim_name'";
        echo "</script>";
        die;
}
else{
	if($action=="reset")
		$row=$db->fetch_array($db->query("select period_limit from sim where 0"));
	elseif($_REQUEST[follow])
		$row=$db->fetch_array($db->query("select period_limit from sim where sim_name='$_GET[follow]'"));
	else 
		$row=$db->fetch_array($db->query("select period_limit from sim where sim_name='$_GET[sim_name]'"));

	$action="main";
	if($row['period_limit']){
		$a=explode(";", $row['period_limit']);
		$i=0;
		$xingqi=array();
		$start_h=array();
		$start_m=array();
		$stop_h=array();
		$stop_m=array();
		$limit_t=array();
		foreach($a as $limit){ //each period
			if($limit=='') break;
			//$xingqi=$limit[0];
			//$limit=substr($limit,1,strlen($limit));
			sscanf($limit, "%1s)%2s:%2s-%2s:%2s,%d,%d,%d",$xingqi0,$start_h0,$start_m0,$stop_h0,$stop_m0,$limit0,$count0,$sms0);
			$check++;
			$xingqi[]=$xingqi0;
			$start_h[$xingqi0][]=$start_h0;
			$start_m[$xingqi0][]=$start_m0;
			$stop_h[$xingqi0][]=$stop_h0;
			$stop_m[$xingqi0][]=$stop_m0;
			$limit_t[$xingqi0][]=$limit0;
			$limit_c[$xingqi0][]=$count0;
			$limit_s[$xingqi0][]=$sms0;
			//echo "111 $check $limit, $xingqi0, $start_h0,$start_m0,$stop_h0,$stop_m0,$limit0 <br>";
			$i++;
		}
	}

	//$query=$db->query("select sim_name from sim where period_limit!='' order by sim_name");
	$query=$db->query("select sim_name from sim order by sim_name");
	while($row=$db->fetch_array($query)) {
		$sim_db[]=$row['sim_name'];
	}
	//print_r($sim_db);

}
require_once ('sim_period.htm');
?>
