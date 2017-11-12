<?

function stopTrafficctrl($trafficctrlp)
{
	$create_downlink = "/var/run/create_downlink";
	$create_uplink = "/var/run/create_uplink";
	$tm_downlink = "/var/run/tm_downlink";
	$tm_uplink = "/var/run/tm_uplink";
   stopcmd("rm -f ".$create_downlink." ".$create_uplink." ".$tm_downlink." ".$tm_uplink."");
}

function startTrafficctrl($trafficctrlp)
{	
	anchor($trafficctrlp);
	$create_downlink = "/var/run/create_downlink";
	$create_uplink = "/var/run/create_uplink";
	$tm_downlink = "/var/run/tm_downlink";
	$tm_uplink = "/var/run/tm_uplink";
	$TC="tc";
	$K=kbit;
	$UPLINK_INF = "veth2";
	$DOWNLINK_INF = "veth3";

	
	$trafficmgr_enable = query("trafficmgr/enable");
	if($trafficmgr_enable!= 1){ 
		startcmd("echo traffic manager is disabled. ");
		return;
	}
	$up_link = query("updownlinkset/bandwidth/uplink");
	$down_link = query("updownlinkset/bandwidth/downlink");
	if($up_link == "" || $up_link == 0 || $down_link == "" || $down_link == 0){
		startcmd("echo uplink ,downlink bandwidth set error.  ");
		return;
	}

	$up_link = $up_link * 1024;
	$down_link = $down_link * 1024;
	startcmd("echo traffic manager=".$trafficmgr_enable." up_link=".$up_link." down_link=".$down_link);

	$UPLINK  = $up_link;
	$DOWNLINK  = $down_link;

	/*---------------Create queues------------------------------------------------------*/
	startcmd($TC." qdisc add dev ".$UPLINK_INF." root handle 1: htb default 165 ;");
	startcmd($TC." class add dev ".$UPLINK_INF." parent 1: classid 1:1 htb rate ".$UPLINK.$K." ceil ".$UPLINK.$K." burst 250k cburst 250k ;");

	startcmd($TC." qdisc add dev ".$DOWNLINK_INF." root handle 2: htb default 165 ;");
	startcmd($TC." class add dev ".$DOWNLINK_INF." parent 2: classid 2:1 htb rate ".$DOWNLINK.$K." ceil ".$DOWNLINK.$K." burst 250k cburst 250k ;");
	
	$index = 0;
	$all_uplink = 0;
	$all_downlink = 0;

	startcmd("rm -f ".$create_downlink." ".$create_uplink." ".$tm_downlink." ".$tm_uplink.);
	startcmd("touch ".$tm_downlink." ".$tm_uplink);

	$rule_path = $trafficctrlp."/trafficmgr";



	
	/*---- query rules into 2 temp file /var/run/tm_downlink tm_uplink ----*/
	foreach($rule_path."/rule/entry")
	{
		$index++;
		if($index <= 64)
		{
            
		$uplink=query($rule_path."/rule/entry:".$InDeX."/uplink");
		$downlink=query($rule_path."/rule/entry:".$InDeX."/downlink");
		$ip=query($rule_path."/rule/entry:".$InDeX."/clientip");
		$mac=query($rule_path."/rule/entry:".$InDeX."/clientmac");
		
		if($ip == "" && $mac == ""){exit;} /* error */
		if($uplink == "" && $downlink == ""){exit;} /* error */

		$uplink = $uplink * 1024;
		$downlink = $downlink * 1024;

		/* client's limit can't larger than whole limit */
		if($uplink >= $UPLINK || $uplink == "" || $uplink == 0) { $uplink = $UPLINK; }
		if($downlink >= $DOWNLINK || $downlink == "" || $downlink == 0) { $downlink = $DOWNLINK; }
		
		if($ip == 0 || $ip == "")
		{
			$ip = "0.0.0.0";
		}
		if($mac == 0 || $mac == "")
		{
			$mac = "00:00:00:00:00:00";
		}

		if($uplink != 0 && $uplink != "")
		{
			startcmd("echo ".$uplink."	".$ip."	".$mac."	 >> ".$tm_uplink."");
			$all_uplink += $uplink;
		}
		
		if($downlink != 0 && $downlink != "")
		{
			startcmd("echo ".$downlink."	".$ip."	".$mac." >> ".$tm_downlink."");
			$all_downlink += $downlink;
		}

		}
		
	}


	/* sort and add line(prio) num */
	startcmd("sort ".$tm_uplink." > /var/run/tm_tmp");
	startcmd("mv /var/run/tm_tmp ".$tm_uplink." ");
	startcmd("sort ".$tm_downlink." > /var/run/tm_tmp");
	startcmd("mv /var/run/tm_tmp ".$tm_downlink."");
	startcmd("grep -n . ".$tm_uplink." | sed 's/:/	/' > /var/run/tm_tmp");
	startcmd("mv /var/run/tm_tmp ".$tm_uplink."");
	startcmd("grep -n . ".$tm_downlink." | sed 's/:/	/' > /var/run/tm_tmp");
	startcmd("mv /var/run/tm_tmp ".$tm_downlink."");
	
	/* create DOWNLINK queues and filters, line num is the filter priority */
	startcmd("echo while read prio limit ip mac >> ".$create_downlink."");
	startcmd("echo do >> ".$create_downlink."");
	startcmd("echo { >> ".$create_downlink."");
	//startcmd("echo	".$TC." class add dev ".$UPLINK_INF " parent 1:1 classid 1:1$prio htb prio 8 rate ".$uplink.$K." ceil ".$uplink.$K. \n" >> /var/run/tm_createqueue";
	startcmd("echo \"	\"".$TC." class add dev ".$DOWNLINK_INF." parent 2:1 classid 2:1'\"$prio\"' htb prio 8 rate '\"$limit\"'".$K." ceil '\"$limit\"'".$K." burst 200k cburst 200k >> ".$create_downlink."");
	startcmd("echo \"	\"opt_src_ip='\"\"' >> ".$create_downlink."");
	startcmd("echo \"	\"opt_dst_ip='\"\"' >> ".$create_downlink."");
	startcmd("echo \"	\"opt_src_mac='\"\"' >> ".$create_downlink."");
	startcmd("echo \"	\"opt_dst_mac='\"\"' >> ".$create_downlink."");
	/* ip filter option */
	startcmd("echo \"	\"if [ '\"$ip\"' != \\\"0.0.0.0\\\" ] >> ".$create_downlink."");
	startcmd("echo \"	\"then >> ".$create_downlink."");
	startcmd("echo \"		\"opt_src_ip='\"match ip src $ip/1\"' >> ".$create_downlink."");
	startcmd("echo \"		\"opt_dst_ip='\"match ip dst $ip/1\"' >> ".$create_downlink."");
	startcmd("echo \"	\"fi >> ".$create_downlink."");
	/* mac filter option */
	startcmd("echo \"	\"if [ '\"$mac\"' != \\\"00:00:00:00:00:00\\\" ] >> ".$create_downlink."");
	startcmd("echo \"	\"then >> ".$create_downlink."");
	startcmd("echo \"		\"tfmgr_mac='\"$mac\"' >> ".$create_downlink."");
	startcmd("echo \"		\"tfmgr_mac1='`echo $tfmgr_mac | cut -c1-2`'  >> ".$create_downlink."");
	startcmd("echo \"		\"tfmgr_mac2='`echo $tfmgr_mac | cut -c4-5`'  >> ".$create_downlink."");
	startcmd("echo \"		\"tfmgr_mac3='`echo $tfmgr_mac | cut -c7-8`'  >> ".$create_downlink."");
	startcmd("echo \"		\"tfmgr_mac4='`echo $tfmgr_mac | cut -c10-11`'  >> ".$create_downlink."");
	startcmd("echo \"		\"tfmgr_mac5='`echo $tfmgr_mac | cut -c13-14`'  >> ".$create_downlink."");
	startcmd("echo \"		\"tfmgr_mac6='`echo $tfmgr_mac | cut -c16-17`'  >> ".$create_downlink."");	
	startcmd("echo \"		\"opt_dst_mac='`echo match u8 0x$tfmgr_mac1 0xFF at -14 match u8 0x$tfmgr_mac2 0xFF at -13 match u8 0x$tfmgr_mac3 0xFF at -12 match u8 0x$tfmgr_mac4 0xFF at -11 match u8 0x$tfmgr_mac5 0xFF at -10 match u8 0x$tfmgr_mac6 0xFF at -9`' >> ".$create_downlink."");
	startcmd("echo \"		\"opt_src_mac='`echo match u8 0x$tfmgr_mac1 0xFF at -8 match u8 0x$tfmgr_mac2 0xFF at -7 match u8 0x$tfmgr_mac3 0xFF at -6 match u8 0x$tfmgr_mac4 0xFF at -5 match u8 0x$tfmgr_mac5 0xFF at -4 match u8 0x$tfmgr_mac6 0xFF at -3`' >> ".$create_downlink."");
	startcmd("echo \"	\"fi >> ".$create_downlink."");
	/* add filter */
	startcmd("echo \"	\"".$TC." filter add dev ".$DOWNLINK_INF." protocol ip parent 2: prio '$prio' u32 '$opt_src_ip' '$opt_src_mac' flowid 2:1'$prio' >> ".$create_downlink."");
	startcmd("echo \"	\"".$TC." filter add dev ".$DOWNLINK_INF." protocol ip parent 2: prio '$prio' u32 '$opt_dst_ip' '$opt_dst_mac' flowid 2:1'$prio' >> ".$create_downlink."");
	startcmd("echo } >> ".$create_downlink."");
	startcmd("echo done >> ".$create_downlink."");
	
	startcmd("sh ".$create_downlink." < ".$tm_downlink."");
	
	
	/* create UPLINK queues and filters, line num is the filter priority */
	startcmd("echo while read prio limit ip mac >> ".$create_uplink."");
	startcmd("echo do >> ".$create_uplink."");
	startcmd("echo { >> ".$create_uplink."");
	startcmd("echo \"	\"".$TC." class add dev ".$UPLINK_INF." parent 1:1 classid 1:1'\"$prio\"' htb prio 8 rate '\"$limit\"'".$K." ceil '\"$limit\"'".$K." burst 200k cburst 200k >> ".$create_uplink."");
	startcmd("echo \"	\"opt_src_ip='\"\"' >> ".$create_uplink."");
	startcmd("echo \"	\"opt_dst_ip='\"\"' >> ".$create_uplink."");
	startcmd("echo \"	\"opt_src_mac='\"\"' >> ".$create_uplink."");
	startcmd("echo \"	\"opt_dst_mac='\"\"' >> ".$create_uplink."");
	/* ip filter option */
	startcmd("echo \"	\"if [ '\"$ip\"' != \\\"0.0.0.0\\\" ] >> ".$create_uplink."");
	startcmd("echo \"	\"then >> ".$create_uplink."");
	startcmd("echo \"		\"opt_src_ip='\"match ip src $ip/1\"' >> ".$create_uplink."");
	startcmd("echo \"		\"opt_dst_ip='\"match ip dst $ip/1\"' >> ".$create_uplink."");
	startcmd("echo \"	\"fi >> ".$create_uplink."");
	/* mac filter option */
	startcmd("echo \"	\"if [ '\"$mac\"' != \\\"00:00:00:00:00:00\\\" ] >> ".$create_uplink."");
	startcmd("echo \"	\"then >> ".$create_uplink."");
	startcmd("echo \"		\"tfmgr_mac='\"$mac\"' >> ".$create_uplink."");
	startcmd("echo \"		\"tfmgr_mac1='`echo $tfmgr_mac | cut -c1-2`'  >> ".$create_uplink."");
	startcmd("echo \"		\"tfmgr_mac2='`echo $tfmgr_mac | cut -c4-5`'  >> ".$create_uplink."");
	startcmd("echo \"		\"tfmgr_mac3='`echo $tfmgr_mac | cut -c7-8`'  >> ".$create_uplink."");
	startcmd("echo \"		\"tfmgr_mac4='`echo $tfmgr_mac | cut -c10-11`'  >> ".$create_uplink."");
	startcmd("echo \"		\"tfmgr_mac5='`echo $tfmgr_mac | cut -c13-14`'  >> ".$create_uplink."");
	startcmd("echo \"		\"tfmgr_mac6='`echo $tfmgr_mac | cut -c16-17`'  >> ".$create_uplink."");	
	startcmd("echo \"		\"opt_dst_mac='`echo match u8 0x$tfmgr_mac1 0xFF at -14 match u8 0x$tfmgr_mac2 0xFF at -13 match u8 0x$tfmgr_mac3 0xFF at -12 match u8 0x$tfmgr_mac4 0xFF at -11 match u8 0x$tfmgr_mac5 0xFF at -10 match u8 0x$tfmgr_mac6 0xFF at -9`' >> ".$create_uplink."");
	startcmd("echo \"		\"opt_src_mac='`echo match u8 0x$tfmgr_mac1 0xFF at -8 match u8 0x$tfmgr_mac2 0xFF at -7 match u8 0x$tfmgr_mac3 0xFF at -6 match u8 0x$tfmgr_mac4 0xFF at -5 match u8 0x$tfmgr_mac5 0xFF at -4 match u8 0x$tfmgr_mac6 0xFF at -3`' >> ".$create_uplink."");
	startcmd("echo \"	\"fi >> ".$create_uplink."");
	/* add filter */
	startcmd("echo \"	\"".$TC." filter add dev ".$UPLINK_INF." protocol ip parent 1: prio '$prio' u32 '$opt_src_ip' '$opt_src_mac' flowid 1:1'$prio' >> ".$create_uplink."");
	startcmd("echo \"	\"".$TC." filter add dev ".$UPLINK_INF." protocol ip parent 1: prio '$prio' u32 '$opt_dst_ip' '$opt_dst_mac' flowid 1:1'$prio' >> ".$create_uplink."");
	startcmd("echo } >> ".$create_uplink."");
	startcmd("echo done >> ".$create_uplink."");
	
	startcmd("sh ".$create_uplink." < ".$tm_uplink."");

	/*--- the default queue ---*/
	$default_index = 65;
	if($all_uplink >= $UPLINK){
		$default_uplink = 1;
	}
	else{
		$default_uplink = $UPLINK - $all_uplink;
	}
	if($all_downlink >= $DOWNLINK){
		$default_downlink = 1;
	}
	else{
		$default_downlink = $DOWNLINK - $all_downlink;
	}

	startcmd($TC." class add dev ".$UPLINK_INF." parent 1:1 classid 1:1".$default_index." htb prio 8 rate ".$default_uplink.$K." ceil ".$UPLINK.$K." burst 200k cburst 200k");

	startcmd($TC." class add dev ".$DOWNLINK_INF." parent 2:1 classid 2:1".$default_index." htb prio 8 rate ".$default_downlink.$K." ceil ".$DOWNLINK.$K." burst 200k cburst 200k");

	/*--- forword unlisted client? ----*/
	$forward_unlist = query($rule_path."/unlistclientstraffic"); /* 0 - deny , 1 - forward */
	if($forward_unlist == 0)
	{
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio ".$default_index." u32 match u8 00 00 flowid 1:1".$default_index." police rate 1bps burst 1 drop");
		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio ".$default_index." u32 match u8 00 00 flowid 2:1".$default_index." police rate 1bps burst 1 drop");
	}
}

?>


