<?php
define("OK", true);
session_start();
if(!isset($_SESSION['usertype'])){
        require_once ('login.php');
        exit;
}

require_once("global.php");
require_once("excel_class.php");

if(!$_REQUEST['order']){
	$_REQUEST['order']="asc";
}
if($_REQUEST['order']=="desc") $order_type2='asc';
else $order_type2='desc';
if(!$_REQUEST['order_key']){
	$_REQUEST['order_key']="name";
}

$order_type=$_REQUEST['order'];
$order_key=$_REQUEST['order_key'];

function my_cmp($a, $b)
{
	global $order_type;
	global $order_key;
	
	//echo "$a[name] $b[name] $a[$order_key] $b[$order_key]<br>";
	//if (empty($a[$order_key]) && empty($b[$order_key])) return 0;
	//if(empty($b[$order_key])) return -1;
	if ($a[$order_key] == $b[$order_key]) return 0;
	else if($order_type=="desc") return ($a[$order_key] < $b[$order_key])?1:-1 ;
	else return ($a[$order_key] > $b[$order_key])?1:-1;
}

function second_to_time($t)
{
        $h=floor($t/3600);
        $m=floor(($t-3600*$h)/60);
        $s=$t-3600*$h-60*$m;
	if($h) $n.="{$h}h ";
	if($m) $n.="{$m}m ";
	$n.="{$s}s";
        return $n;
}
if($_REQUEST['submit_value'] == '一小时'){
	$start_time=date("Y-m-d H:i", time()-3600);
	$end_time=date("Y-m-d H:i");
}
else if($_REQUEST['submit_value'] == '半小时'){
	$start_time=date("Y-m-d H:i", time()-1800);
	$end_time=date("Y-m-d H:i");
}
else { 
	$start_time=$_REQUEST['start_time'];
	if(!$start_time) $start_time=date("Y-m-d")." 00:00";
	$end_time=$_REQUEST['end_time'];
	if(!$end_time) $end_time=date("Y-m-d H:i");
}
$type=$_REQUEST['type'];
if(!$type) $type="sim";
//$line_name=$_REQUEST['line_name'];
$name=$_REQUEST['name'];

if(!$name) {
	$ch="selected";
	$name_ch="全部";
}
if($name) $wh="and ".$type."_name=$name";

$select="<select name=\"name\"  style=\"width:80px\" >\n\t<option value=\"0\" $ch>All</option>\n";
if($type=="line")
	$query=$db->query("select id, ".$type."_name as name from device_line where 1  order by line_name");
else 
	$query=$db->query("select id, ".$type."_name as name from sim where 1 order by sim_name");
while($row=$db->fetch_array($query)) {
	if($name==$row['name']) {
		$row['ch'] = "selected";
		$name_ch = $row['name'];
		$channel[]=$row;
		$rsdb[$row[name]]=$row;
	}
	else if(!$name){
		$channel[]=$row;
		$rsdb[$row[name]]=$row;
	}
	//$rsdb[$row[name]]=$row;
	$select.="\t<option value=\"$row[name]\" $row[ch]>$row[name]</option>\n";
}
$select.="</select>";


$sql="SELECT ".$type."_name as name,sum(duration) as calltime,count(id) as callcount from call_record where dir=0 and duration>0 and time>'$start_time' and time<'$end_time' $wh group by ".$type."_name";
$query=$db->query($sql);
while($row=$db->fetch_array($query)){
	$calltime+=$row[1];
	$callcount+=$row[2];
	$row['acd']=round($row[1]/$row[2]);
	$row['acd_s']=second_to_time($row['acd']);;	
	$row['calltime_s']=second_to_time($row[1]);
	$rsdb[$row[name]]=$row;
}
$calltime_s=second_to_time($calltime);
$acd=round($calltime/$callcount);
$acd_s=second_to_time($acd);
/*
$sql="SELECT ".$type."_name as name,count(id) as failcount from call_record where dir=0 and duration>0 and time>'$start_time' and time<'$end_time' $wh group by ".$type."_name";
$query=$db->query($sql);
while($row=$db->fetch_array($query)){
	$calltime+=$row[1];
	$callcount+=$row[2];
	$row['acd']=round($row[1]/$row[2]);
	$row['acd_s']=second_to_time($row['acd']);;	
	$row['calltime_s']=second_to_time($row[1]);
	$rsdb[$row[name]]=$row;
}
*/
$sql="SELECT ".$type."_name as name,count(id) from call_record where dir=0 and duration>=0 and time>'$start_time' and time<'$end_time' $wh group by ".$type."_name";

$query=$db->query($sql);
while($row=$db->fetch_array($query)){
	$rsdb[$row[name]]['asr']=(round($rsdb[$row[name]][callcount]/$row[1],3)*100)."%";
        $rsdb[$row[name]][tcount]=$row[1];
        $rsdb[$row[name]][failcount]=$row[1]-$row['callcount'];
        $tcount+=$row[1];
}
$asr=(round($callcount/$tcount,3)*100)."%";
//echo "asr:".round($rs[1]/$rs1[0],3);
//print_r($rsdb);

if($order_key) uasort($rsdb, "my_cmp");


if($_REQUEST['submit_value']=='导出'){

	if($name==0) $filename="CDR_".$type."_ALL($start_time~$end_time).xls";
	else $filename="CDR_".$type.$name."($start_time~$end_time).xls";
	
	$return[0][0]=$type;
	$return[0][1]="ASR";
	$return[0][2]="ACD";
	$return[0][3]="Call Time";
	$return[0][4]="Call Count";

	$i=1;
	foreach($rsdb as $rs){
		$return[$i][0]="".$rs['name'];
		$return[$i][1]="".$rs['asr'];
		$return[$i][2]="".$rs['acd_s'];
		$return[$i][3]="".$rs['calltime_s'];
		$return[$i][4]="".$rs['callcount'];
		$i++;
	}

	Create_Excel_File($filename,$return);
	exit;
}

require_once ('cdr.htm');

?>
