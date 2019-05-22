<?php
function my_pack($row, $action){
if(defined('__FOR_ARM__')){
	global $checksum;
	switch ($action){
		case SIM_ADD:
		//echo "$row[sim_name], $row[password]";
return pack('LLLSSSSa16llCCCCLa*', $checksum, DEV_ADD, $row[sim_name], $row[sim_team_id],0,TYPE_SIM,$row['imei_mode'],$row['imei'],$row['remain_time'],$row['time_unit'],$row[no_ring_limit],$row[no_answer_limit],$row[short_call_short_call_limit],$row[short_time],$row[remain_sms],$row[password]);
		case GOIP_ADD:
		//echo "$row[line_name], $row[goip_team_id]; ";
return pack('LLLSSSSa16llCCCCLa*', $checksum, DEV_ADD, $row[line_name], $row[goip_team_id],$row[zone],TYPE_GOIP,0,"000000000000000",-1,0,-1,-1,-1,-1,-1,$row[password]);
		case SCH_UPDATE:
		if($row[type]=="cycle")  $type=0;
		else if($row[type]=="period_chaos") $type=1;
		else if($row[type]=="daily") {
			$type=1;
			$row['period_chaos']='';
			if($row['period_daily']){
				$t_db=explode(";", $row['period_daily']);
				for($i=0;$i<7;$i++)
					foreach($t_db as $t){
						if($t) $row['period_chaos'].="$i)".$t.";";
					}
			}
			//echo $row[period_chaos];
		}
return pack('LLSSLLa*', $checksum, SCH_UPDATE, $row[sim_team_id], $type, $row[r_interval]*60, $row[s_interval],$row[period_chaos]);
		case SIM_PERIOD:
		//echo $row[sim_name].$row[period_limit];
return pack('LLLa*', $checksum, SIM_PERIOD, $row[sim_name], $row[period_limit]);
		case IMEI_IMSI_INFO:
return pack('LLLa16a*', $checksum, IMEI_IMSI_INFO, $row[sim_name], $row[last_imei], $row[imsi]);
		//case AUTO_DIAL:
//return pack('LLLa16a*', $checksum, AUTO_DIAL, mt_rand(1,200000000), $row[line_name], $row[duration], $num);
	}

}
else {
	global $checksum;
	switch ($action){
		case SIM_ADD:
		//echo "2 $row[sim_name], $row[password]";
return pack('LCLSSCCa15llCCCCLa*', $checksum, DEV_ADD, $row[sim_name], $row[sim_team_id],0,TYPE_SIM,$row['imei_mode'],$row['imei'],$row['remain_time'],$row['time_unit'],$row[no_ring_limit],$row[no_answer_limit],$row[short_call_short_call_limit],$row[short_time],$row['remain_sms'],$row[password]);
		case GOIP_ADD:
		//echo "$row[line_name], $row[goip_team_id]; ";
return pack('LCLSSCCa15llCCCCLa*', $checksum, DEV_ADD, $row[line_name], $row[goip_team_id],$row[zone],TYPE_GOIP,0,"000000000000000",-1,0,-1,-1,-1,-1,-1,$row[password]);
		case SCH_UPDATE:
		if($row[type]=="cycle")  $type=0;
		else if($row[type]=="period_chaos") $type=1;
		else if($row[type]=="daily") {
			$type=1;
			$row['period_chaos']='';
			if($row['period_daily']){
				$t_db=explode(";", $row['period_daily']);
				for($i=0;$i<7;$i++)
					foreach($t_db as $t){
						if($t) $row['period_chaos'].="$i)".$t.";";
					}
			}
			//echo $row[period_chaos];
		
}
return pack('LCSCLLa*', $checksum, SCH_UPDATE, $row[sim_team_id], $type, $row[r_interval]*60, $row[s_interval],$row[period_chaos]);
		case SIM_PERIOD:
		//echo $row[sim_name].$row[period_limit];
return pack('LCLa*', $checksum, SIM_PERIOD, $row[sim_name], $row[period_limit]);
		case IMEI_IMSI_INFO:
return pack('LCLa15a*', $checksum, IMEI_IMSI_INFO, $row[sim_name], $row[last_imei], $row[imsi]);

	}

}
}

function my_pack2($action, $f1=0,$f2=0,$f3=0,$f4=0,$f5=0,$f6=0,$f7=0,$f8=0)
{
	if(defined('__FOR_ARM__')){
		global $checksum;
		switch ($action){
			case DEV_NETCHECK:
				return pack('LLLL', $checksum, DEV_NETCHECK, $f1, $f2);
			case DEV_DISABLE:
				return pack('LLLL', $checksum, DEV_DISABLE, $f1, $f2);
			case DEV_ENABLE:
				return pack('LLLL', $checksum, DEV_ENABLE, $f1, $f2);
			case MACHINE_REBOOT:
				return pack('LLLL', $checksum, MACHINE_REBOOT, $f1, $f2);
			case RESET_LIMIT:
				return pack('LLL', $checksum, RESET_LIMIT, $f1);
			case DEV_ACTIVING:
				return pack('LLLL', $checksum, DEV_ACTIVING, $f1, $f2);
			case DEV_BINDING:
				return pack('LLLL', $checksum, DEV_BINDING, $f1, $f2);
			case MODULE_REBOOT:
				return pack('LLL', $checksum, MODULE_REBOOT, $f1);
			case DEV_DEL:
				return pack('LLLSS', $checksum, DEV_DEL, $f1, $f2, $f3);
			case SCH_UPDATE:
				return pack('LLSSLL', $checksum, SCH_UPDATE, $f1, $f2, $f3, $f4);
			case AUTO_DIAL:
				return pack('LLLLLa*', $checksum, AUTO_DIAL, mt_rand(1,200000000), $f1, $f2, $f3);
		}
	}
	else {
		global $checksum;
		switch ($action){
			case DEV_NETCHECK:
				return pack('LCLC', $checksum, DEV_NETCHECK, $f1, $f2);
			case DEV_DISABLE:
				return pack('LCLC', $checksum, DEV_DISABLE, $f1, $f2);
			case DEV_ENABLE:
				return pack('LCLC', $checksum, DEV_ENABLE, $f1, $f2);
			case MACHINE_REBOOT:
				return pack('LCLC', $checksum, MACHINE_REBOOT, $f1, $f2);
			case RESET_LIMIT:
				return pack('LCL', $checksum, RESET_LIMIT, $f1);
			case DEV_ACTIVING:
				return pack('LCLC', $checksum, DEV_ACTIVING, $f1, $f2);
			case DEV_BINDING:
				return pack('LCLL', $checksum, DEV_BINDING, $f1, $f2);
			case MODULE_REBOOT:
				return pack('LCL', $checksum, MODULE_REBOOT, $f1);
			case DEV_DEL:
				return pack('LCLSC', $checksum, DEV_DEL, $f1, $f2, $f3);
			case SCH_UPDATE:
				return pack('LCSCLL', $checksum, SCH_UPDATE, $f1, $f2, $f3, $f4);
			case AUTO_DIAL:
				return pack('LCLSLa*', $checksum, AUTO_DIAL, mt_rand(1,200000000), $f1, $f2, $f3);

		}
	}
}

function my_unpack_net_check($buf)
{
	if(defined('__FOR_ARM__')){
		return unpack("La/Cb/Cd/Ce/Cf/Lsid/Ctype/Csent/Crecv/Clost/Cbad/Cdup/Cg/Ch/Ldelay", $buf);
		//return unpack("La/Lb/Lsid/Ctype/Csent/Crecv/Clost/Cbad/Cdup/Cg/Ch/Ldelay", $buf);
	}
	else {
		return unpack("La/Cb/Lsid/Ctype/Csent/Crecv/Clost/Cbad/Cdup/Ldelay", $buf);
	}
}

function sim_info($row, &$send)
{
	if($row['imei_mode']==4) $row['imei_mode']=2;
	if($row['imei']==NULL) $row['imei'] = "000000000000000";
	$imei_len=strlen($row['imei']);
	if($imei_len<15) $row['imei'] = sprintf("%15s",$row['imei']."000000000000000");
	if($row['remain_time']==NULL || $row['remain_time'] <-1 ) $row['remain_time'] = -1;
	if($row['month_remain_time']==NULL || $row['month_remain_time'] <-1 ) $row['month_remain_time'] = -1;
	if($row['month_remain_time']==0) $row['dev_disable']=1;
	//echo "disable:$row[dev_disable]";
	$send[]=my_pack($row, SIM_ADD); 
	$send[]=my_pack2($row['dev_disable']?DEV_DISABLE:DEV_ENABLE, $row['sim_name'], TYPE_SIM);
	if($row['imei_mode']==3) $send[]=my_pack($row, IMEI_IMSI_INFO);
}
?>
