<?php
session_start();
if(!isset($_SESSION['usertype'])){
        require_once ('login.php');
        exit;
}

define("OK", true);
require_once("global.php");

if(isset($_GET['action'])) {
	$action=$_GET['action'];
	if($action=="recv"){
		if(empty($_GET['id']))
			WriteErrMsg("<br><li>plesae choose a group!</li>");
		$id=$_GET[id];
		$query=$db->query("SELECT count(*) AS count FROM sim");
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
		$query=$db->query("SELECT id FROM sim where sim_team_id=$id order by id");
		$strs0=array();
		$rcount=0;
		while($row=$db->fetch_array($query)) {
			$rcount++;
			$strs0[]=$row['id'];
		}
		//echo $rcount;
		$fenye=showpage2("sim_team.php?action=recv&id=$id&",$page,$count,$perpage,true,true,"编","myform","boxs");
		//if(_GET)
		$query=$db->query("(select sim.*,sim_team_name, 1 as 'in' from sim left join sim_team using (sim_team_id) where sim.sim_team_id=$id order by sim_name limit 999999)
				union 
				(select *,NULL,0 from sim where sim_team_id=0 order by sim_name limit 999999 ) 
				union
				(select sim.*,sim_team_name,2 from sim left join sim_team using (sim_team_id) where sim.sim_team_id!=0 and sim.sim_team_id!=$id order by sim_name limit 999999 ) 
				LIMIT $start_limit,$perpage");

		//$query=$db->query("SELECT receiver.*,groups.id as groupsid,groups.name as groupsname  FROM receiver LEFT JOIN (groups join recvgroup on groups.id=recvgroup.groupsid) ON recvgroup.recvid = receiver.id  ORDER BY groups.id=$id DESC,id DESC LIMIT $start_limit,$perpage");

		//select * from receiver where id not in( SELECT receiver.id  FROM receiver LEFT JOIN (groups join recvgroup on groups.id=recvgroup.groupsid) ON recvgroup.recvid = receiver.id  where groups.id=8)  


		while($row=$db->fetch_array($query)) {
			/*
			if($row[in]){			
				$row['yes']="是";
			}
			else 
				$row['yes']="否";
			*/
                        if($row[in]==1){
                                $row['yes']="是";
                        }
                        else if($row[in]==0){
                                $row['yes']="无组";
                        }
                        else {
                                $row['yes']="其他组";
                        }
			if($row['sim_login'] == 0 || $row['sim_login'] == 12){
				$row['alive']="OFFLINE";
			}
			elseif($row['sim_login'] == 11){
				$row['alive']="ONLINE";
			}
			elseif($row['sim_login'] == 13){
				$row['alive']="IDLE";
			}
			elseif($row['sim_login'] == 14){
				$row['alive']="BUSY";
			}
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
$nametmp=$db->fetch_array($db->query("SELECT sim_team_name FROM sim_team where sim_team_id=$_GET[id]"));
$groupsname='<font color="#FF0000">'.$nametmp[0].'</font></a>';
}
elseif($action=="receivers"){

	//print_r($_POST);
	if(empty($_GET['id']))
		WriteErrMsg("<br><li>plesae choose a group!</li>");
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

	$query=$db->query("select sim.*, password from sim left join sim_bank on sim.bank_name=sim_bank.name where sim_team_id=$id");
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
			$row[sim_team_id]=0;
			$send[]=my_pack($row, SIM_ADD);
                }

		//else $insertstr.=$row[0].",";
	}
	if($delstrs){
		$delstrs=substr($delstrs,0,strlen($delstrs)-1);
		//WriteSuccessMsg("delete from receiver where id in ($delstrs)","groups.php?action=recv&id=$id");
		//echo "update goip set goip_team_id=0 where goipid in ($delstrs)";
		$db->query("update sim set sim_team_id=0 where id in ($delstrs)");
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
		$db->query("update sim set sim_team_id=$id,plan_line_name='0' where id in ($insertstr)");
                //$db->query("update goip set goip_team_id=$id where goipid in ($insertstr)");                      
                $query=$db->query("select sim.*,password from sim left join sim_bank on sim.bank_name=sim_bank.name where sim.id in ($insertstr)");           
                                                                                                                  
                while($row=$db->fetch_array($query)) {
			$send[]=my_pack($row, SIM_ADD);
			$send[]=my_pack2(DEV_BINDING, $row[sim_name], 0);
                } 
	}
        if($send)
                sendto_xchanged($send);
	WriteSuccessMsg("<br><li>修改成功</li>","sim_team.php?action=recv&id=$id");

}
else if($action=="grecv"){
                if(empty($_GET['id']))
                        WriteErrMsg("<br><li>plesae choose a group!</li>");
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
                $query=$db->query("SELECT id FROM device_line where goip_team_id=$id order by id");
                $strs0=array();
                $rcount=0;
                while($row=$db->fetch_array($query)) {
                        $rcount++;
                        $strs0[]=$row['id'];
                }
                //echo $rcount;
                $fenye=showpage2("sim_team.php?action=grecv&id=$id&",$page,$count,$perpage,true,true,"编","myform","boxs");
                //if(_GET)
                $query=$db->query("(select device_line.*,sim_team_name, 1 as 'in' from device_line left join sim_team on sim_team.sim_team_id=device_line.goip_team_id where device_line.goip_team_id=$id order by line_name ASC limit 999999)
                                union 
                                (select *,NULL,0 from device_line where goip_team_id=0 order by line_name ASC limit 999999) 
                                union
                                (select device_line.*,sim_team_name,2 from device_line left join sim_team on sim_team.sim_team_id=device_line.goip_team_id where device_line.goip_team_id!=0 and device_line.goip_team_id!=$id order by line_name ASC limit 999999)
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
$nametmp=$db->fetch_array($db->query("SELECT sim_team_name FROM sim_team where sim_team_id=$_GET[id]"));
$groupsname='<font color="#FF0000">'.$nametmp[0].'</font></a>';
}
elseif($action=="greceivers"){

        //print_r($_POST);
        if(empty($_GET['id']))
                WriteErrMsg("<br><li>plesae choose a group!</li>");
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

        $query=$db->query("select device_line.*,password from device_line left join rm_device on device_line.goip_name = rm_device.name where goip_team_id=$id");

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
			$row[goip_team_id]=0;
			$send[]=my_pack($row, GOIP_ADD);
                }
                //else $insertstr.=$row[0].",";
        }
        if($delstrs){
                $delstrs=substr($delstrs,0,strlen($delstrs)-1);
                //WriteSuccessMsg("delete from receiver where id in ($delstrs)","groups.php?action=recv&id=$id");
                //echo "update goip set goip_team_id=0 where goipid in ($delstrs)";
                $db->query("update device_line set goip_team_id=0 where id in ($delstrs)");
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
                $db->query("update device_line set goip_team_id=$id where id in ($insertstr)");
                $query=$db->query("select device_line.line_name,device_line.goip_team_id,password,zone,sim_name from device_line left join rm_device on device_line.goip_name = rm_device.name left join sim on sim.plan_line_name=device_line.line_name where device_line.id in ($insertstr)");

                while($row=$db->fetch_array($query)) {
			$send[]=my_pack($row, GOIP_ADD);
			if($row[sim_name]) {
				$send[]=my_pack2(DEV_BINDING, $row[sim_name], 0);
				$simname.=$row[sim_name].',';
			}
			
                }
		if($simname){
			$simname=substr($simname,0,strlen($sim_name)-1);
			$db->query("update sim set plan_line_name='0' where sim_name in ($simname)");
		}
	}
	if($send)
                sendto_xchanged($send);
        WriteSuccessMsg("<br><li>修改成功</li>","sim_team.php?action=grecv&id=$id");

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
		$db->query("DELETE FROM `sim_team` WHERE sim_team_id IN ($Id)");
		$db->query("DELETE FROM `scheduler` WHERE group_id IN ($Id)");
		$db->query("update `sim` set sim_team_id=0 WHERE sim_team_id IN ($Id)");
                $db->query("update `device_line` set goip_team_id=0 WHERE goip_team_id IN ($Id)"); 

                $query=$db->query("select sim.*,password from sim left join sim_bank on sim.bank_name=sim_bank.name where sim_team_id IN ($Id)");
                while($row=$db->fetch_array($query)) {
			$send[]=my_pack($row, SIM_ADD);
                }
		$query=$db->query("select device_line.*,password,zone from device_line left join rm_device on device_line.goip_name = rm_device.name where goip_team_id IN ($Id)");
                while($row=$db->fetch_array($query)) {
			$send[]=my_pack($row, GOIP_ADD);
                }

		//$db->query("DELETE FROM refgroup WHERE groupsid IN ($Id)");
		//$db->query("DELETE FROM recvgroup WHERE groupsid IN ($Id)");//关系
		sendto_xchanged($send);

		WriteSuccessMsg("<br><li>删除成功</li>","sim_team.php");

	}
}
elseif($action=="add")
{
}
elseif($action=="modify")
{
	$id=$_GET['id'];
	$rs=$db->fetch_array($db->query("SELECT * FROM `sim_team` where sim_team_id=$id"));

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

		$query=$db->query("SELECT sim_team_id FROM `sim_team` WHERE sim_team_name='$name' ");
		$rs=$db->fetch_array($query);
		if(empty($rs[0])){
			$db->query("INSERT INTO `sim_team` (sim_team_name) value ('$name') ");
			$groupiddb=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
			$groupid=$groupiddb[0];
			$db->query("INSERT INTO `scheduler` (name,group_id,type) value ('$name', '$groupid','cycle') ");
			$schiddb=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
			$schid=$schiddb[0];
			$db->query("update sim_team set scheduler_id='$schid' where sim_team_id='$groupid'");
			$row=$db->fetch_array($db->query("select sim_team_id,r_interval,s_interval,type,period_chaos,period_fixed from scheduler,sim_team where sim_team.scheduler_id=scheduler.id and scheduler.id='$schid' order by sim_team_id"));
			$send[]=my_pack($row, SCH_UPDATE);
			sendto_xchanged($send);
			WriteSuccessMsg("<br><li>添加成功</li>","sim_team.php");
			/* 还要添加管理员*/
		}
		else{
			$ErrMsg=$ErrMsg."<br><li>sim team [$name] have existed</li>";
			WriteErrMsg($ErrMsg);
		}

	}
}
elseif($action=="savemodify")
{
/*
	$Id=$_POST['Id'];
	$ErrMsg="";

	if($ErrMsg!="")
		WriteErrMsg($ErrMsg);
	else{
		//*是否改变了群*/
/*
		//$crs=$db->fetch_array($db->query("SELECT id FROM `groups` where id=$Id and crowdid=$_POST[crowdid]"));

		$db->query("UPDATE sim_team set sleep_time='$_POST[sleep_time]',work_time='$_POST[work_time]' where sim_team_id=$Id");
		sendto_xchanged($send);
		//if(!$crs[0]) //	改变了群	
		//$db->query("update receiver INNER JOIN groups ON receiver.groupid = group.id set receiver.crowdid=group.crowdid where receiver.groupid = group.id" );			

		WriteSuccessMsg("<br><li>Modify administrator success</li>","sim_team.php");
	}
*/
}
elseif($action=="scheduler"){
	$id=$_REQUEST['id'];
	$query=$db->query("SELECT * FROM `scheduler` left join sim_team on group_id=sim_team_id  where group_id=$id order by scheduler_id,name");
	while($row=$db->fetch_array($query)) {
		echo $row['scheduler_id']." ".$row['id']."<br>";
		if($row['scheduler_id']==$row['id']) $row['selected']='selected';
		$row['period']=$row['period_chaos'];
		$rsdb[]=$row;
	}
	//print_r($rsdb);
}
elseif($action=="restart" || $action=="stop"){
	if($action=="restart") $c=1;
	if($action=="stop") $c=2;
	$send[]=my_pack2(SCH_CTL, $_REQUEST['id']);
	sendto_xchanged($send);
	WriteSuccessMsg("<br><li>The command is sended to Server.</li>","?");
}
else $action="main";

}
else $action="main";

if($action=="main")	
{
	$query=$db->query("SELECT count(*) AS count FROM `sim_team`");
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
	$fenye=showpage("sim_team.php?",$page,$count,$perpage,true,true,"编");
	$query=$db->query("SELECT sim_team.sim_team_id as id,sim_team.* from sim_team ORDER BY sim_team.sim_team_id LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		$sim_on_c=0; $sim_all_c=0; $channel_on_c=0; $channel_all_c=0;
		$query1=$db->query("select sim_login, count(*) from sim where sim_team_id=$row[sim_team_id] group by sim_login");
		while($row1=$db->fetch_array($query1)){
			if($row1['sim_login']==11 || $row1['sim_login']==13 || $row1['sim_login']==14) $sim_on_c+=$row1[1];
			$sim_all_c+=$row1[1];
		}
		$query1=$db->query("select line_status, count(*) as c from device_line where goip_team_id=$row[sim_team_id] group by line_status");
		while($row1=$db->fetch_array($query1)){
			if($row1['line_status']!=0 &&$row1['line_status']!=12) $channel_on_c+=$row1['c'];
			$channel_all_c+=$row1['c'];
		}
		$s_c=$db->fetch_array($db->query("select count(*) from scheduler where group_id=$row[sim_team_id]"));
		$row['scheduler_c']=$s_c[0];
		$scheduler=$db->fetch_array($db->query("select * from scheduler,sim_team where scheduler_id=scheduler.id and sim_team_id=$row[sim_team_id]"));
		//print_r($scheduler);
		if($scheduler[0]){
			$row['scheduler_type']=$scheduler['type'];
			if($scheduler['type']=='cycle'){
				$row['scheduler_type']='循环';
				$row['r_interval']=$scheduler['r_interval'];
				$row['s_interval']=$scheduler['s_interval'];
			}
			else {
				/*
				$i=0;$j=0;
				while($scheduler['period_chaos'][$i]){
					if($scheduler['period_chaos'][$i]==';') $row['period'][$i]='\n';
					else $row['period'][$i]=$scheduler['period_chaos'][$i];
					$i++;
				}
				*/
				$row['scheduler_type']='时间段';
				$row['period']=str_replace(';', "\n", $scheduler['period_chaos']);
				
				if(strlen($row['period']) > 20)
					$row['period_head']=sprintf("%.20s...", $row['period']);
				else 
					$row['period_head']=$row['period'];
			}
		}
/*
		else {
			$row['type']='cycle';
			$row['r_interval']=$scheduler['r_interval'];
			$row['s_interval']=$scheduler['s_interval'];
		}
*/
		$row['sim_c']=$sim_on_c."/".$sim_all_c;
		$row['channel_c']=$channel_on_c."/".$channel_all_c;
		$rsdb[]=$row;

	}
	//print_r($rsdb);
}
require_once ('sim_team.htm');

?>

