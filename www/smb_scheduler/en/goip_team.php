<?php
session_start();
if(!isset($_SESSION['usertype']))
	die("需要admin权限！");

define("OK", true);
require_once("global.php");

if(isset($_GET['action'])) {
	//if($_COOKIE['permissions'] > 1 )
		//WriteErrMsg("<br><li>需要admin权限!</li>");
	$action=$_GET['action'];

	if($action=="recv"){
		if(empty($_GET['id']))
			WriteErrMsg("<br><li>没有选择一个组!</li>");
		$id=$_GET[id];
		$query=$db->query("SELECT count(*) AS count FROM goip");
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
		$query=$db->query("SELECT goipid FROM goip where goip_team_id=$id order by goipid");
		$strs0=array();
		$rcount=0;
		while($row=$db->fetch_array($query)) {
			$rcount++;
			$strs0[]=$row['goipid'];
		}
		//echo $rcount;
		$fenye=showpage2("goip_team.php?action=recv&id=$id&",$page,$count,$perpage,true,true,"编","myform","boxs");
		//if(_GET)
		$query=$db->query("(select goip.*,goip_team_name, 1 as 'in' from goip left join goip_team using (goip_team_id) where goip.goip_team_id=$id order by line_name ASC limit 99999999)
				union 
				(select *,NULL,0 from goip where goip_team_id=0 order by line_name ASC limit 99999999) 
				union
				(select goip.*,goip_team_name,2 from goip left join goip_team using (goip_team_id) where goip.goip_team_id!=0 and goip.goip_team_id!=$id order by line_name ASC limit 99999999)
					LIMIT $start_limit,$perpage");

		//$query=$db->query("SELECT receiver.*,groups.id as groupsid,groups.name as groupsname  FROM receiver LEFT JOIN (groups join recvgroup on groups.id=recvgroup.groupsid) ON recvgroup.recvid = receiver.id  ORDER BY groups.id=$id DESC,id DESC LIMIT $start_limit,$perpage");

		//select * from receiver where id not in( SELECT receiver.id  FROM receiver LEFT JOIN (groups join recvgroup on groups.id=recvgroup.groupsid) ON recvgroup.recvid = receiver.id  where groups.id=8)  


		while($row=$db->fetch_array($query)) {

			if($row[in]==1){			
				$row['yes']="是";
			}
			else if($row[in]==0){
				$row['yes']="无组";
			}
			else {
				$row['yes']="其他组";
			}
			if($row[line_status])
				$row['zhu']="已注册";
			else 
				$row['zhu']="未注册";
			//else if($row[groupsid]==$id)
			//if($row['groupsid']==$_GET['id'])
			$rsdb[]=$row;
			$strs[]=$row['goipid'];
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
$nametmp=$db->fetch_array($db->query("SELECT goip_team_name FROM goip_team where goip_team_id=$_GET[id]"));
$groupsname='<font color="#FF0000">'.$nametmp[0].'</font></a>';
}
elseif($action=="receivers"){

	//print_r($_POST);
	if(empty($_GET['id']))
		WriteErrMsg("<br><li>没有选择一个组!</li>");
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

	$query=$db->query("select goipid,line_name,goip_pass from goip where goip_team_id=$id");

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
			$send[]=pack('LCLSCa*', $checksum, DEV_ADD, $row[line_name], 0,TYPE_GOIP,$row[goip_pass]);
		}
		//else $insertstr.=$row[0].",";
	}
	if($delstrs){
		$delstrs=substr($delstrs,0,strlen($delstrs)-1);
		//WriteSuccessMsg("delete from receiver where id in ($delstrs)","groups.php?action=recv&id=$id");
		//echo "update goip set goip_team_id=0 where goipid in ($delstrs)";
		$db->query("update goip set goip_team_id=0 where goipid in ($delstrs)");
	}
	if(count($strs)){
		//echo "111111";
		//$sql="insert into recvgroup values ";
		foreach($strs as $rkey => $rgid){
			//$sql.="(NULL,$id,$rgid),";
			$insertstr.=$rgid.",";
			
		}
		$insertstr=substr($insertstr,0,strlen($insertstr)-1);
		//WriteSuccessMsg($sql,"groups.php?action=recv&id=$id");
		//echo "update goip set goip_team_id=$id where goipid in ($insertstr)";
		$db->query("update goip set goip_team_id=$id where goipid in ($insertstr)");
		$query=$db->query("select line_name,goip_pass from goip where goipid in ($insertstr)");

		while($row=$db->fetch_array($query)) {
			$send[]=pack('LCLSCa*', $checksum, DEV_ADD, $row[line_name], $id,TYPE_GOIP,$row[goip_pass]);
		}
	}
	if($send)
		sendto_xchanged($send);
	WriteSuccessMsg("<br><li>修改成功</li>","goip_team.php?action=recv&id=$id");

}
elseif($action=="del")
{
	$ErrMsg="";
	$Id=$_GET['id'];


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
	//WriteErrMsg($num."$Id");

	if(empty($Id))
		$ErrMsg ='<br><li>Please choose one</li>';
	if($ErrMsg!="")
		WriteErrMsg($ErrMsg);
	else{
                $query=$db->query("select line_name,goip_pass,goip_team_id from goip where goip_team_id IN ($Id)");

                while($row=$db->fetch_array($query)) {
                        $send[]=pack('LCLSCa*', $checksum, DEV_ADD, $row[line_name], $row[goip_team_id],TYPE_GOIP,$row[goip_pass]);
                }

		$db->query("DELETE FROM `goip_team` WHERE goip_team_id IN ($Id)");
		$db->query("update `goip` set goip_team_id=0 WHERE goip_team_id IN ($Id)");
		sendto_xchanged($send);
		//$db->query("DELETE FROM refgroup WHERE groupsid IN ($Id)");
		//$db->query("DELETE FROM recvgroup WHERE groupsid IN ($Id)");//关系


		WriteSuccessMsg("<br><li>删除成功</li>","goip_team.php");

	}
}
elseif($action=="add")
{
}
elseif($action=="modify")
{
	$id=$_GET['id'];
	$rs=$db->fetch_array($db->query("SELECT * FROM `groups` where id=$id"));
	//$query=$db->query("SELECT id,name FROM `crowd` order by id ");
	//while($row=$db->fetch_array($query)) {
		//$crowdrs[]=$row;
	//}

}
elseif($action=="saveadd")
{
	//WriteErrMsg("'$_POST['name']'");
	$name=$_POST['name'];

	$info=$_POST['info'];
	$ErrMsg="";
	if(empty($name))
		$ErrMsg ='<br><li>请输入名称</li>';
	if($ErrMsg!="")
		WriteErrMsg($ErrMsg);
	else{

		$query=$db->query("SELECT goip_team_id FROM `goip_team` WHERE goip_team_name='$name' ");
		$rs=$db->fetch_array($query);
		if(empty($rs[0])){
			$query=$db->query("INSERT INTO `goip_team` (goip_team_name) value ('$name') ");
			WriteSuccessMsg("<br><li>Add group success</li>","goip_team.php");
			/* 还要添加管理员*/
		}
		else{
			$ErrMsg=$ErrMsg."<br><li>goip team [$name] have existed</li>";
			WriteErrMsg($ErrMsg);
		}

	}
}
elseif($action=="savemodify")
{

	$Id=$_POST['Id'];
	$ErrMsg="";

	if($ErrMsg!="")
		WriteErrMsg($ErrMsg);
	else{
		/*是否改变了群*/
		//$crs=$db->fetch_array($db->query("SELECT id FROM `groups` where id=$Id and crowdid=$_POST[crowdid]"));

		$query=$db->query("UPDATE `groups` INNER JOIN crowd ON groups.crowdid = crowd.id SET groups.name='$_POST[name]',groups.info='$_POST[info]',groups.crowdid='$_POST[crowdid]' WHERE groups.id=$Id");

		//if(!$crs[0]) //	改变了群	
		//$db->query("update receiver INNER JOIN groups ON receiver.groupid = group.id set receiver.crowdid=group.crowdid where receiver.groupid = group.id" );			

		WriteSuccessMsg("<br><li>Modify administrator success</li>","groups.php");
	}
}
/*
else if($action="bind"){

	$query=$db->query("SELECT rule.*,goip_team_name,sim_team_name from rule,goip_team,sim_team WHERE goip_team.goip_team_id=$id and rule.goip_team_id=$id and sim_team.sim_team_id=rule.sim_team_id");
	$rs=$db->fetch_array($query);
	if(empty($rs[0])){
		
	}
	else {
		
	}
}
*/
else $action="main";

}
else $action="main";

if($action=="main")	
{
	$query=$db->query("SELECT count(*) AS count FROM `goip_team`");
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
	$fenye=showpage("goip_team.php?",$page,$count,$perpage,true,true,"编");
	$query=$db->query("SELECT goip_team.goip_team_id as id,goip_team.*,rule.* FROM `goip_team` left join `rule` on goip_team.goip_team_id=rule.goip_team_id ORDER BY goip_team.goip_team_id LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		
		/*找出每个组的管理员*/
		//$usersql=$db->query("select username from user,refgroup where refgroup.groupsid=$row[id] and user.id=refgroup.userid order by refgroup.id desc ");

		//$i=0;
		/*最多列出3个*/		
/*
		while($userrow=$db->fetch_array($usersql)){ 
			$i++;
			$row[username].=$userrow[0]." ";
			if($i>5){
				$row[username].="等";
				break;
			}
		}
*/
		$rsdb[]=$row;
	}
	//print_r($rsdb);
}
require_once ('goip_team.htm');

?>

