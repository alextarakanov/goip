<?php
define("OK", true);
session_start();
if(!isset($_SESSION['usertype'])){
        require_once ('login.php');
        exit;
}
require_once("global.php");

$action=$_REQUEST['action'];
if($action=="del"){
	$ErrMsg="";
	$Id=$_REQUEST['id'];
	if(empty($Id)){
		$num=$_POST['boxs'];
		for($i=0;$i<$num;$i++)
		{	
			if(!empty($_POST["Id$i"])){
				if($Id=="")
					$Id=$_POST["Id$i"];
				else
					$Id=$_POST["Id$i"].",$Id";
			}
		}
	}

	if(empty($Id))
		$ErrMsg ='<br><li>Please choose one</li>';
	if($ErrMsg!="")
		WriteErrMsg($ErrMsg);
	else{
		$query=$db->query("DELETE FROM call_record WHERE id IN ($Id)");

		WriteSuccessMsg("<br><li>Delete call records success</li>","call_record.php?line_name=$_REQUEST[line_name]&sim_name=$_REQUEST[sim_name]");

	}
}
else if($action=="delall"){
	if($ErrMsg!="")                                                                                           
		WriteErrMsg($ErrMsg);                                                                             
	else{ 
		if($_REQUEST[line_name])  $where="where line_name='$_REQUEST[line_name]'";
		else if($_REQUEST[sim_name])  $where="where sim_name='$_REQUEST[sim_name]'";
		$db->query("DELETE FROM call_record $where"); 

		WriteSuccessMsg("<br><li>Delete call records success</li>","call_record.php?line_name=$_REQUEST[line_name]&sim_name=$_REQUEST[sim_name]");

	}
}

$where=" where 1 ";
$column=myaddslashes($_REQUEST['column']);
$s_key=myaddslashes($_REQUEST['s_key']);
$type=myaddslashes($_REQUEST['type']);
if(!empty($_REQUEST['column'])&& !empty($_REQUEST['type']) && !empty($_REQUEST['s_key'])){
	$where.="and `$column`";
	$t_selected[$type]="selected";
	$c_selected[$column]="selected";
	if($type=="equal"){
		$where.="='$s_key'";
		//$where.=" like '%$s_key%'";
	}
	else if($type=="unequal"){
		$where.="!='$s_key'";
	}
	else if($type=="prefix"){
		$where.=" like '$s_key%'";
	}
	else if($type=="postfix"){
		$where.=" like '%$s_key'";
	}
	else if($type=="contain"){
		$where.=" like '%$s_key%'";
	}
	else if($type=="less"){
		$where.=" <= '$s_key'";
	}
	else if($type=="more"){
		$where.=" >= '$s_key'";
	}
}
else {  
	$t_selected['contain']="selected";
	$c_selected['number']="selected";
}

$start_time=$_REQUEST['start_time'];
//if(!$start_time) $start_time=date("Y-m-d")." 00:00";
if($start_time) $where.=" and time>='$start_time'";
$end_time=$_REQUEST['end_time'];
//if(!$end_time) $end_time=date("Y-m-d H:i");
if($end_time) $where.=" and time<='$end_time'";
$sim_name=$_REQUEST['sim_name'];
$line_name=$_REQUEST['line_name'];

if(!$name) {
        $sim_ch="selected";
        $sim_name_ch="All";
	
}

$select2="Sim:<select name=\"sim_name\"  style=\"width:90px\" >\n\t<option value=\"0\" $ch>All</option>\n";
$query=$db->query("select id, sim_name as name from sim where 1 order by sim_name");
while($row=$db->fetch_array($query)) {
	if($sim_name==$row['name']) {
		$row['ch'] = "selected";
		$sim_name_ch = $row['name'];
		$channel[]=$row;
		$sim_db[$row[name]]=$row;
	}
	else if(!$sim_name){
		$channel[]=$row;
		$sim_db[$row[name]]=$row;
	}
	$select2.="\t<option value=\"$row[name]\" $row[ch]>$row[name]</option>\n";
}
$select2.="</select>";

$select2.="Line:<select name=\"line_name\"  style=\"width:90px\" >\n\t<option value=\"0\" $ch>All</option>\n";
$query=$db->query("select id, line_name as name from device_line where 1  order by line_name");
while($row=$db->fetch_array($query)) {
	if($line_name==$row['name']) {
		$row['ch'] = "selected";
		$line_name_ch = $row['name'];
		$channel[]=$row;
		$line_db[$row[name]]=$row;
	}
	else if(!$line_name){
		$channel[]=$row;
		$line_db[$row[name]]=$row;
	}       
	$select2.="\t<option value=\"$row[name]\" $row[ch]>$row[name]</option>\n";
}
$select2.="</select>";

echo $s_key;
$select="Search Column<select name=\"column\"  style=\"width:120px\" >";
$select.="\t<option value='number' $c_selected[number]>Call Number</option>\n";
$select.="\t<option value='duration' $c_selected[duration]>Expiry(s)</option>\n";
$select.="\t<option value='imei' $c_selected[imei]>IMEI</option>\n";
$select.="\t<option value='imsi' $c_selected[imsi]>IMSI</option>\n";
$select.="\t<option value='iccid' $c_selected[iccid]>ICCID</option>\n";
$select.="\t<option value='disconnect_cause' $c_selected[disconnect_cause]>Disconnect Cause</option>\n";
$select.="</select>\n"; 
$select.="Search Type<select name=\"type\"  style=\"width:95px\" >";
$select.="\t<option value='equal' $t_selected[equal]>equal</option>\n";
$select.="\t<option value='less' $t_selected[less]>less than</option>\n";
$select.="\t<option value='more' $t_selected[more]>more than</option>\n";
$select.="</select>\n";
$select.="Key<input type=\"text\" name=\"s_key\" value='$_REQUEST[s_key]' size=16>&nbsp;\n";
$select.="<input type=\"submit\" name=\"submit_value\" value=\"Search\">\n";

	if($line_name)  {
		$where.="and line_name='$line_name' ";
		$pages.="line_name=$line_name";
		$t_info=" Line name:$line_name";
	}
	if($sim_name)  {
		$where.="and sim_name='$sim_name' ";
		$pages.="&sim_name=$sim_name";
		$t_info.=" SIM Name:$sim_name";
	}
	if(!$t_info) $t_info="All"; 
	//echo ("SELECT count(*) AS count FROM call_record $where ");

if($_REQUEST['submit_value']=='Export'){

	$filename="CallList_SLOT";
	if($sim_name==0) $filename.="ALL";
	else $filename="$sim_name";
	$filename.="_LINE";
	if($line_name==0) $filename.="ALL";
	else $filename="$line_name";
	if($start_time || $end_time) $filename.="($start_time~$end_time)";
	$filename.=".xls";


	$return[0][0]="DateTime";
	$return[0][1]="Slot ID";
	$return[0][2]="Line ID";
	$return[0][3]="IMEI";
	$return[0][4]="IMSI";
	$return[0][5]="ICCID";
	$return[0][6]="Expiry(s)";
	$return[0][7]="Direction";
	$return[0][8]="Call Number";
	$return[0][9]="Disconnect Cause";


	$query=$db->query("SELECT * FROM call_record $where ORDER BY id DESC");
	while($row=$db->fetch_array($query)) {
		if($row['dir']=="1")
			$row['dir1']='INCOMING';
		else if($row['dir']=="0")
			$row['dir1']='OUTGOING';
		else $row[dir1]='UNKNOWN';
		$rsdb[]=$row;
	}
	$i=1;
	foreach($rsdb as $rs){
		$return[$i][0]="".$rs['time'];
		$return[$i][1]="".$rs['sim_name'];
		$return[$i][2]="".$rs['line_name'];
		$return[$i][3]="".$rs['imei'];
		$return[$i][4]="".$rs['imsi'];
		$return[$i][5]="".$rs['iccid'];
		$return[$i][6]="".$rs['duration'];
		$return[$i][7]="".$rs['dir1'];
		$return[$i][8]="".$rs['number'];
		$return[$i][9]="".$rs['disconnect_cause'];
		$i++;
	}
	Create_Excel_File($filename,$return);
	exit;
}

	$query=$db->query("SELECT count(*) AS count FROM call_record $where ");
	$row=$db->fetch_array($query);
	$count=$row['count'];
	$numofpage=ceil($count/$perpage);
	$totlepage=$numofpage;
	if(isset($_REQUEST['page'])) {
		$page=$_REQUEST['page'];
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
	$fenye=showpage("?$pages&action=$action&column=$column&type=$type&s_key=$_REQUEST[s_key]&order_key=$_REQUEST[order_key]&order=$_REQUEST[order]&start_time=$start_time&end_time=$end_time&",$page,$count,$perpage,true,true,"rows");
	//echo ("SELECT * FROM call_record $where ORDER BY id DESC LIMIT $start_limit,$perpage");
	$query=$db->query("SELECT * FROM call_record $where ORDER BY id DESC LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		if($row['dir']=="1")
			$row['dir1']='INCOMING';
		else if($row['dir']=="0")
			$row['dir1']='OUTGOING';
		else $row[dir1]='UNKNOWN';
		$rsdb[]=$row;
	}



?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="../style.css" rel="stylesheet" type="text/css">
<title>Call Record</title>
<script language="javascript">
function unselectall()
	{
	    if(document.myform.chkAll.checked){
		document.myform.chkAll.checked = document.myform.chkAll.checked&0;
	    } 	
	}

function CheckAll(form)
	{
		var trck;
		var e;
		for (var i=0;i<form.elements.length;i++)
	    {
		    e = form.elements[i];
		    if (e.type == 'checkbox' && e.id != "chkAll" && e.disabled==false){
				e.checked = form.chkAll.checked;
		 		do {e=e.parentNode} while (e.tagName!="TR") 
		 		if(form.chkAll.checked)
		 			e.className = 'even marked';
		 		else
		 			e.className = 'even';
			}
	    }
		//form.chkAll.classname = 'even';
	}

function mouseover(obj) {
                obj.className += ' hover';
				//alert(obj.className);
            	
			}

function mouseout(obj) {
            	obj.className = obj.className.replace( ' hover', '' );
				//alert(obj.className);
			}

function trclick(obj) {
		//alert("ddddd");
        var checkbox = obj.getElementsByTagName( 'input' )[0];
        //if ( checkbox && checkbox.type == 'checkbox' ) 
        checkbox.checked ^= 1;
		if(checkbox.checked)
			obj.className = 'even marked';
		else obj.className = 'even';
//		var ckpage=document.modifyform.elements['chkAll'+num];
	    if(document.myform.chkAll.checked){
		document.myform.chkAll.checked = document.myform.chkAll.checked&0;
	    } 	
		

		}

</script>
<script src="time.js"></script>
</head>
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">
<table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
  <tr class="topbg"> 
    <td height="22" colspan="2" align="center"><strong>(<?php echo $t_info ?>)Call Records</strong></td>
  </tr>
  <tr class="tdbg"> 
<td wIdth="70" height="30"><strong>Navigation:</strong></td>
    <td height="30"><a href="<?php echo "?line_name=$line_name&sim_name=$sim_name&start_time=$start_time&end_time=$end_time&column=$column&type=$type&s_key=$_REQUEST[s_key]" ?>" target=main>Refresh</a>&nbsp;|&nbsp;<a href="call_record.php" target=main>All Record</a></td>
  </tr>
</table>
<table width="100%" height="25"  border="0" cellpadding="0" cellspacing="0">
<form action="?action=search" method="post">
<tr><td> 
<?php echo $select2 ?>
Start:
<input type="text" name="start_time"  readOnly onClick="SelectDate(this,'yyyy-MM-dd hh:mm')" value="<?php echo $start_time ?>">
End:
<input type="text" name="end_time"  readOnly onClick="SelectDate(this,'yyyy-MM-dd hh:mm')" value="<?php echo $end_time ?>">
</td></tr>
<tr><td>
<?php echo $select ?> <input type="submit" name="submit_value" value="Export">
</td></tr>
</form>
</table>
<form action="call_record.php?action=del&<?php echo "line_name=$_REQUEST[line_name]&sim_name=$_REQUEST[sim_name]" ?>" method=post name=myform onSubmit="return confirm('Sure to Delete?')">
<table wIdth="100%"  border="0" cellspacing="2" cellpadding="2">
	<tr class=title>
		<td wIdth="35" align=center height="25"><b>choose</b></td>
		<td align="center"><b>DateTime</b></td>
		<td align="center"><b>Slot ID</b></td>
		<td align="center"><b>Line ID</b></td>
		<td align="center"><b>IMEI</b></td>
		<td align="center"><b>IMSI</b></td>
		<td align="center"><b>ICCID</b></td>
		<td align="center"><b>Expiry(s)</b></td>
		<td align="center"><b>Direction</b></td>
		<td align="center"><b>Call Number</b></td>
		<td align="center"><b>Disconnect Cause</b></td>
		<td wIdth="80" align=center><b>Operations</b></td>
	</tr>
<!--
<?php 
$j=0;
foreach($rsdb as $rs) {
print <<<EOT
-->
	<tr class="even" onMouseOver="mouseover(this)" onMouseOut="mouseout(this)" onMouseDown="trclick(this)">
		<td align=center wIdth="35"><input name="Id{$j}" type='checkbox' onClick="return false" value="{$rs['id']}"></td>
		<td align="center">{$rs['time']}</td>
		<td align="center">{$rs['sim_name']}</td>
		<td align="center">{$rs['line_name']}</td>
		<td align="center">{$rs['imei']}</td>
		<td align="center">{$rs['imsi']}</td>
		<td align="center">{$rs['iccid']}</td>
		<td align="center">{$rs['duration']}</td>
		<td align="center">{$rs['dir1']}</td>
		<td align="center">{$rs['number']}</td>
		<td align="center">{$rs['disconnect_cause']}</td>
				
		<td align=center wIdth="80"><a href="call_record.php?id={$rs['id']}&action=del&line_name={$_REQUEST['line_name']}&sim_name={$_REQUEST['sim_name']}" onClick="return confirm('Sure to delete?')">Delete</a></td>
    </tr>

<!--
EOT;
$j++;
}
print <<<EOT
-->
</table>
<input type="hIdden" name="boxs" value="{$j}">
<table wIdth="100%"  border="0" cellspacing="2" cellpadding="2">


					<tr>
						<td height="30" ><input name="chkAll" type="checkbox" Id="chkAll" onclick=CheckAll(this.form) value="checkbox"> 
					  Choice current page<input name="submit" type='submit' value='Delete selected'>
<input name="button" type='button' value='Delete All' onClick="if(confirm('Suer to delete all call records?')) window.location='?action=delall&line_name=$_REQUEST[line_name]&sim_name=$_REQUEST[sim_name]'"></td>
					</tr>
					<tr>
						<td  align=center>{$fenye}</td>
					</tr>
</table>
<!--
EOT;
?>
-->
</form>

					  </td> 
					</tr>
</table>
				
</body>
</html>
