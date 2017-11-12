<?



function stopQoS()
{
//	stopcmd("rtlioc qos_init 0");
}


function startQoSByPort($trafficctrlp)
{
        anchor($trafficctrlp);

	$port1priority  = query("qos/port/port1priority");
	$port2priority  = query("qos/port/port2priority");
	$port3priority  = query("qos/port/port3priority");
	$port4priority  = query("qos/port/port4priority");

	if($port1priority=="" || $port2priority=="" || $port3priority=="" || $port4priority=="")
	{
			startcmd("echo Port Qos Setting invalid.");
	}

      startcmd("echo Port Setting :".$port1priority." ".$port2priority." ".$port3priority." ".$port4priority." ");
	//#rtlioc portpri_set [port1 pri] [port2 pri] [port3 pri] [port4 pri]
	//# pirority value 1-4:related to BK/BE/VI/VO
	startcmd("rtlioc portpri_set ".$port1priority." ".$port2priority." ".$port3priority." ".$port4priority."\n");
	startcmd("rtlioc qos_init 0");

	stopcmd("rtlioc portpri_set 1 1 1 1\n");
	stopcmd("rtlioc qos_init 0");

	$port1priority  =  $port1priority;
	$port2priority  =  $port2priority;
	$port3priority  =  $port3priority;
	$port4priority  =  $port4priority;
	
	startcmd("echo \"1 ".$port4priority."\" > /proc/rt3883/sw_port_wifi_pri");// #LAN port4
	startcmd("echo \"2 ".$port3priority."\" > /proc/rt3883/sw_port_wifi_pri");// #LAN port3
	startcmd("echo \"3 ".$port2priority."\" > /proc/rt3883/sw_port_wifi_pri");// #LAN port2
	startcmd("echo \"4 ".$port1priority."\" > /proc/rt3883/sw_port_wifi_pri");// #LAN port1
	startcmd("echo  1 > /proc/rt3883/sw_port_wifi_pri_enable");// #LAN port1

	stopcmd("echo  0 > /proc/rt3883/sw_port_wifi_pri_enable");// #LAN port1

}
function startQoSByProtocol($trafficctrlp)
{	
	$TC="tc";
	$K=kbit;

	$UPLINK_INF="veth0";
	$DOWNLINK_INF="veth1";
 	anchor($trafficctrlp);
	

	$up_link = query("updownlinkset/bandwidth/uplink");
	$down_link = query("updownlinkset/bandwidth/downlink");
	if($up_link == "" || $up_link == 0 || $down_link == "" || $down_link == 0){
		startcmd( "echo uplink ,downlink bandwidth set error. \n");
		return;
	}


	anchor($trafficctrlp."/qos/protocol");

	/* Mbps -> kbps */
	$up_link = $up_link * 1024;
	$down_link = $down_link * 1024;

	startcmd( "echo  up_link=".$up_link."k down_link=".$down_link."k \n");

	
	$ack_max	= query("aui/limit");
	

	
	if($ack_max == "" || $ack_max > 100){ $ack_max = 100; }
	$ack_prio	= query("aui/priority");
	if($ack_prio == "" ){ $ack_prio = 3; }

	$web_max	= query("web/limit");
	if($web_max == "" || $web_max > 100){ $web_max = 100; }
	$web_prio	= query("web/priority");
	if($web_prio == "" ){ $web_prio = 3; }

	$mail_max	= query("mail/limit");
	if($mail_max == "" || $mail_max > 100){ $mail_max = 100; }
	$mail_prio	= query("mail/priority");
	if($mail_prio == "" ){ $mail_prio = 3; }

	$ftp_max	= query("ftp/limit");
	if($ftp_max == "" || $ftp_max > 100){ $ftp_max = 100; }
	$ftp_prio	= query("ftp/priority");
	if($ftp_prio == "" ){ $ftp_prio = 3; }

	$other_max	= query("other/limit");
	if($other_max == "" || $other_max > 100){ $other_max = 100; }
	$other_prio	= query("other/priority");
	if($other_prio == "" ){ $other_prio = 3; }

	/* user defined .*/
	$user1_max	= query("user1/limit");
	if($user1_max == "" || $user1_max > 100){ $user1_max = 100; }
	$user1_prio	= query("user1/priority");
	if($user1_prio == "" ){ $user1_prio = 3; }
	$user1_startport = query("user1/startport");
	$user1_endport = query("user1/endport");
	if($user1_startport == ""){$user1_startport = 0; }
	if($user1_endport == ""){$user1_endport = 0; }
	/* user define 1 priority configuration */
	$user1_configed = 0;
	$user1_range = 0;
	if($user1_startport <= $user1_endport && $user1_startport != 0 && $user1_endport != 0){
		$user1_configed = 1;
		$user1_range = $user1_endport - $user1_startport + 1;
	}
	
	$user2_max	= query("user2/limit");
	if($user2_max == "" || $user2_max > 100){ $user2_max = 100; }
	$user2_prio	= query("user2/priority");
	if($user2_prio == "" ){ $user2_prio = 3; }
	$user2_startport = query("user2/startport");
	$user2_endport = query("user2/endport");
	if($user2_startport == ""){$user2_startport = 0; }
	if($user2_endport == ""){$user2_endport = 0; }
	$user2_configed = 0;
	$user2_range = 0;
	if($user2_startport <= $user2_endport && $user2_startport != 0 && $user2_endport != 0){
		$user2_configed = 1;
		$user2_range = $user2_endport - $user2_startport + 1;
	}
	
	$user3_max	= query("user3/limit");
	if($user3_max == "" || $user3_max > 100){ $user3_max = 100; }
	$user3_prio	= query("user3/priority");
	if($user3_prio == "" ){ $user3_prio = 3; }
	$user3_startport = query("user3/startport");
	$user3_endport = query("user3/endport");
	if($user3_startport == ""){$user3_startport = 0; }
	if($user3_endport == ""){$user3_endport = 0; }
	$user3_configed = 0;
	$user3_range = 0;
	if($user3_startport <= $user3_endport && $user3_startport != 0 && $user3_endport != 0){
		$user3_configed = 1;
		$user3_range = $user3_endport - $user3_startport + 1;
	}
	
	$user4_max	= query("user4/limit");
	if($user4_max == "" || $user4_max > 100){ $user4_max = 100; }
	$user4_prio	= query("user4/priority");
	if($user4_prio == "" ){ $user4_prio = 3; }
	$user4_startport = query("user4/startport");
	$user4_endport = query("user4/endport");
	if($user4_startport == ""){$user4_startport = 0; }
	if($user4_endport == ""){$user4_endport = 0; }
	$user4_configed = 0;
	$user4_range = 0;
	if($user4_startport <= $user4_endport && $user4_startport != 0 && $user4_endport != 0){
		$user4_configed = 1;
		$user4_range = $user4_endport - $user4_startport + 1;
	}

	/* min bandwidth alloc for each queue
	 * highest/second/third/lowest = 4/3/2/1
	 */


	 $whole_prio = 4*9 - $ack_prio - $web_prio - $mail_prio - $ftp_prio - $other_prio - $user1_prio - $user2_prio - $user3_prio - $user4_prio;

	$PRIO_ALL	= $up_link;

	$percent	= 4 - $ack_prio;
	$ACK_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$ACK_MAX	= $PRIO_ALL * $ack_max / 100 ;
	if($ACK_MIN > $ACK_MAX){ $ACK_MIN = $ACK_MAX; }
	if($ACK_MIN == 0){ $ACK_MIN = 1; }
	if($ACK_MAX == 0){ $ACK_MAX = 1; }

	$percent	= 4 - $web_prio;
	$WEB_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$WEB_MAX	= $PRIO_ALL * $web_max / 100 ;
	if($WEB_MIN > $WEB_MAX){ $WEB_MIN = $WEB_MAX; }
	if($WEB_MIN == 0){ $WEB_MIN = 1; }
	if($WEB_MAX == 0){ $WEB_MAX = 1; }

	$percent	= 4 - $mail_prio;
	$MAIL_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$MAIL_MAX	= $PRIO_ALL * $mail_max / 100 ;
	if($MAIL_MIN > $MAIL_MAX){ $MAIL_MIN = $MAIL_MAX; }
	if($MAIL_MIN == 0){ $MAIL_MIN = 1; }
	if($MAIL_MAX == 0){ $MAIL_MAX = 1; }

	$percent	= 4 - $ftp_prio;
	$FTP_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$FTP_MAX	= $PRIO_ALL * $ftp_max / 100 ;
	if($FTP_MIN > $FTP_MAX){ $FTP_MIN = $FTP_MAX; }
	if($FTP_MIN == 0){ $FTP_MIN = 1; }
	if($FTP_MAX == 0){ $FTP_MAX = 1; }

	$percent	= 4 - $other_prio;
	$OTHER_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$OTHER_MAX	= $PRIO_ALL * $other_max / 100 ;
	if($OTHER_MIN > $OTHER_MAX){ $OTHER_MIN = $OTHER_MAX; }
	if($OTHER_MIN == 0){ $OTHER_MIN = 1; }
	if($OTHER_MAX == 0){ $OTHER_MAX = 1; }

	$percent	= 4 - $user1_prio;
	$USER1_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$USER1_MAX	= $PRIO_ALL * $user1_max / 100 ;
	if($USER1_MIN > $USER1_MAX){ $USER1_MIN = $USER1_MAX; }
	if($USER1_MIN == 0){ $USER1_MIN = 1; }
	if($USER1_MAX == 0){ $USER1_MAX = 1; }

	$percent	= 4 - $user2_prio;
	$USER2_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$USER2_MAX	= $PRIO_ALL * $user2_max / 100 ;
	if($USER2_MIN > $USER2_MAX){ $USER2_MIN = $USER2_MAX; }
	if($USER2_MIN == 0){ $USER2_MIN = 1; }
	if($USER2_MAX == 0){ $USER2_MAX = 1; }

	$percent	= 4 - $user3_prio;
	$USER3_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$USER3_MAX	= $PRIO_ALL * $user3_max / 100 ;
	if($USER3_MIN > $USER3_MAX){ $USER3_MIN = $USER3_MAX; }
	if($USER3_MIN == 0){ $USER3_MIN = 1; }
	if($USER3_MAX == 0){ $USER3_MAX = 1; }

	$percent	= 4 - $user4_prio;
	$USER4_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$USER4_MAX	= $PRIO_ALL * $user4_max / 100 ;
	if($USER4_MIN > $USER4_MAX){ $USER4_MIN = $USER4_MAX; }
	if($USER4_MIN == 0){ $USER4_MIN = 1; }
	if($USER4_MAX == 0){ $USER4_MAX = 1; }

	
	/* create uplink queue */
	/* dispatch packet to 'other' queue default */
	startcmd($TC." qdisc add dev ".$UPLINK_INF." handle 1: root htb default 15 ");

	startcmd($TC." class add dev ".$UPLINK_INF." parent 1:0 classid 1:1 htb rate ".$PRIO_ALL.$K." ceil ".$PRIO_ALL.$K."");

	startcmd($TC." class add dev ".$UPLINK_INF." parent 1:1 classid 1:11 htb prio ".$ack_prio."  rate ".$ACK_MIN.$K." ceil ".$ACK_MAX.$K."");
	startcmd($TC." class add dev ".$UPLINK_INF." parent 1:1 classid 1:12 htb prio ".$web_prio."  rate ".$WEB_MIN.$K." ceil ".$WEB_MAX.$K."");
	startcmd($TC." class add dev ".$UPLINK_INF." parent 1:1 classid 1:13 htb prio ".$mail_prio."  rate ".$MAIL_MIN.$K." ceil ".$MAIL_MAX.$K."");
	startcmd($TC." class add dev ".$UPLINK_INF." parent 1:1 classid 1:14 htb prio ".$ftp_prio."  rate ".$FTP_MIN.$K." ceil ".$FTP_MAX.$K."");
	startcmd($TC." class add dev ".$UPLINK_INF." parent 1:1 classid 1:15 htb prio ".$other_prio."  rate ".$OTHER_MIN.$K." ceil ".$OTHER_MAX.$K."");
	startcmd($TC." class add dev ".$UPLINK_INF." parent 1:1 classid 1:16 htb prio ".$user1_prio."  rate ".$USER1_MIN.$K." ceil ".$USER1_MAX.$K."");
	startcmd($TC." class add dev ".$UPLINK_INF." parent 1:1 classid 1:17 htb prio ".$user2_prio."  rate ".$USER2_MIN.$K." ceil ".$USER2_MAX.$K."");
	startcmd($TC." class add dev ".$UPLINK_INF." parent 1:1 classid 1:18 htb prio ".$user3_prio."  rate ".$USER3_MIN.$K." ceil ".$USER3_MAX.$K."");
	startcmd($TC." class add dev ".$UPLINK_INF." parent 1:1 classid 1:19 htb prio ".$user4_prio."  rate ".$USER4_MIN.$K." ceil ".$USER4_MAX.$K."");

	startcmd($TC." qdisc add dev ".$UPLINK_INF." parent 1:11 handle 110: pfifo ");
	startcmd($TC." qdisc add dev ".$UPLINK_INF." parent 1:12 handle 120: pfifo ");
	startcmd($TC." qdisc add dev ".$UPLINK_INF." parent 1:13 handle 130: pfifo ");
	startcmd($TC." qdisc add dev ".$UPLINK_INF." parent 1:14 handle 140: pfifo ");
	startcmd($TC." qdisc add dev ".$UPLINK_INF." parent 1:15 handle 150: pfifo ");
	startcmd($TC." qdisc add dev ".$UPLINK_INF." parent 1:16 handle 160: pfifo ");
	startcmd($TC." qdisc add dev ".$UPLINK_INF." parent 1:17 handle 170: pfifo ");
	startcmd($TC." qdisc add dev ".$UPLINK_INF." parent 1:18 handle 180: pfifo ");
	startcmd($TC." qdisc add dev ".$UPLINK_INF." parent 1:19 handle 190: pfifo ");


	$PRIO_ALL	= $down_link;

	$percent	= 4 - $ack_prio;
	$ACK_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$ACK_MAX	= $PRIO_ALL * $ack_max / 100 ;
	if($ACK_MIN > $ACK_MAX){ $ACK_MIN = $ACK_MAX; }
	if($ACK_MIN == 0){ $ACK_MIN = 1; }
	if($ACK_MAX == 0){ $ACK_MAX = 1; }

	$percent	= 4 - $web_prio;
	$WEB_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$WEB_MAX	= $PRIO_ALL * $web_max / 100 ;
	if($WEB_MIN > $WEB_MAX){ $WEB_MIN = $WEB_MAX; }
	if($WEB_MIN == 0){ $WEB_MIN = 1; }
	if($WEB_MAX == 0){ $WEB_MAX = 1; }

	$percent	= 4 - $mail_prio;
	$MAIL_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$MAIL_MAX	= $PRIO_ALL * $mail_max / 100 ;
	if($MAIL_MIN > $MAIL_MAX){ $MAIL_MIN = $MAIL_MAX; }
	if($MAIL_MIN == 0){ $MAIL_MIN = 1; }
	if($MAIL_MAX == 0){ $MAIL_MAX = 1; }

	$percent	= 4 - $ftp_prio;
	$FTP_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$FTP_MAX	= $PRIO_ALL * $ftp_max / 100 ;
	if($FTP_MIN > $FTP_MAX){ $FTP_MIN = $FTP_MAX; }
	if($FTP_MIN == 0){ $FTP_MIN = 1; }
	if($FTP_MAX == 0){ $FTP_MAX = 1; }

	$percent	= 4 - $other_prio;
	$OTHER_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$OTHER_MAX	= $PRIO_ALL * $other_max / 100 ;
	if($OTHER_MIN > $OTHER_MAX){ $OTHER_MIN = $OTHER_MAX; }
	if($OTHER_MIN == 0){ $OTHER_MIN = 1; }
	if($OTHER_MAX == 0){ $OTHER_MAX = 1; }

	$percent	= 4 - $user1_prio;
	$USER1_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$USER1_MAX	= $PRIO_ALL * $user1_max / 100 ;
	if($USER1_MIN > $USER1_MAX){ $USER1_MIN = $USER1_MAX; }
	if($USER1_MIN == 0){ $USER1_MIN = 1; }
	if($USER1_MAX == 0){ $USER1_MAX = 1; }

	$percent	= 4 - $user2_prio;
	$USER2_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$USER2_MAX	= $PRIO_ALL * $user2_max / 100 ;
	if($USER2_MIN > $USER2_MAX){ $USER2_MIN = $USER2_MAX; }
	if($USER2_MIN == 0){ $USER2_MIN = 1; }
	if($USER2_MAX == 0){ $USER2_MAX = 1; }

	$percent	= 4 - $user3_prio;
	$USER3_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$USER3_MAX	= $PRIO_ALL * $user3_max / 100 ;
	if($USER3_MIN > $USER3_MAX){ $USER3_MIN = $USER3_MAX; }
	if($USER3_MIN == 0){ $USER3_MIN = 1; }
	if($USER3_MAX == 0){ $USER3_MAX = 1; }

	$percent	= 4 - $user4_prio;
	$USER4_MIN	= $PRIO_ALL * $percent / $whole_prio;
	$USER4_MAX	= $PRIO_ALL * $user4_max / 100 ;
	if($USER4_MIN > $USER4_MAX){ $USER4_MIN = $USER4_MAX; }
	if($USER4_MIN == 0){ $USER4_MIN = 1; }
	if($USER4_MAX == 0){ $USER4_MAX = 1; }

	/* create downlink queue */
	/* dispatch packet to 'other' queue default */
	startcmd($TC." qdisc add dev ".$DOWNLINK_INF." handle 2: root htb default 15 ");

	startcmd($TC." class add dev ".$DOWNLINK_INF." parent 2:0 classid 2:1 htb rate ".$PRIO_ALL.$K." ceil ".$PRIO_ALL.$K."");

	startcmd($TC." class add dev ".$DOWNLINK_INF." parent 2:1 classid 2:11 htb prio ".$ack_prio."  rate ".$ACK_MIN.$K." ceil ".$ACK_MAX.$K."");
	startcmd($TC." class add dev ".$DOWNLINK_INF." parent 2:1 classid 2:12 htb prio ".$web_prio."  rate ".$WEB_MIN.$K." ceil ".$WEB_MAX.$K."");
	startcmd($TC." class add dev ".$DOWNLINK_INF." parent 2:1 classid 2:13 htb prio ".$mail_prio."  rate ".$MAIL_MIN.$K." ceil ".$MAIL_MAX.$K."");
	startcmd($TC." class add dev ".$DOWNLINK_INF." parent 2:1 classid 2:14 htb prio ".$ftp_prio."  rate ".$FTP_MIN.$K." ceil ".$FTP_MAX.$K."");
	startcmd($TC." class add dev ".$DOWNLINK_INF." parent 2:1 classid 2:15 htb prio ".$other_prio."  rate ".$OTHER_MIN.$K." ceil ".$OTHER_MAX.$K."");
	startcmd($TC." class add dev ".$DOWNLINK_INF." parent 2:1 classid 2:16 htb prio ".$user1_prio."  rate ".$USER1_MIN.$K." ceil ".$USER1_MAX.$K."");
	startcmd($TC." class add dev ".$DOWNLINK_INF." parent 2:1 classid 2:17 htb prio ".$user2_prio."  rate ".$USER2_MIN.$K." ceil ".$USER2_MAX.$K."");
	startcmd($TC." class add dev ".$DOWNLINK_INF." parent 2:1 classid 2:18 htb prio ".$user3_prio."  rate ".$USER3_MIN.$K." ceil ".$USER3_MAX.$K."");
	startcmd($TC." class add dev ".$DOWNLINK_INF." parent 2:1 classid 2:19 htb prio ".$user4_prio."  rate ".$USER4_MIN.$K." ceil ".$USER4_MAX.$K."");

	startcmd($TC." qdisc add dev ".$DOWNLINK_INF." parent 2:11 handle 110: pfifo ");
	startcmd($TC." qdisc add dev ".$DOWNLINK_INF." parent 2:12 handle 120: pfifo ");
	startcmd($TC." qdisc add dev ".$DOWNLINK_INF." parent 2:13 handle 130: pfifo ");
	startcmd($TC." qdisc add dev ".$DOWNLINK_INF." parent 2:14 handle 140: pfifo ");
	startcmd($TC." qdisc add dev ".$DOWNLINK_INF." parent 2:15 handle 150: pfifo ");
	startcmd($TC." qdisc add dev ".$DOWNLINK_INF." parent 2:16 handle 160: pfifo ");
	startcmd($TC." qdisc add dev ".$DOWNLINK_INF." parent 2:17 handle 170: pfifo ");
	startcmd($TC." qdisc add dev ".$DOWNLINK_INF." parent 2:18 handle 180: pfifo ");
	startcmd($TC." qdisc add dev ".$DOWNLINK_INF." parent 2:19 handle 190: pfifo ");

    /*Classifiers, adv qos rule */

	/* ack/dhcp/dns/icmp */
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 300 u32 match ip protocol 6 0xff match u8 0x05 0x0f at 0 match u16 0x0000 0xffc0 at 2 match u8 0x10 0xff at 33 flowid 1:11");
	
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 300 u32 match u8 0x01 0xff at 9 flowid 1:11");
	
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 300 u32 match ip protocol 17 0xff match ip sport 67 1 flowid 1:11");
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 300 u32 match ip protocol 17 0xff match ip dport 67 1 flowid 1:11");
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 300 u32 match ip protocol 17 0xff match ip sport 546 1 flowid 1:11");
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 300 u32 match ip protocol 17 0xff match ip dport 546 1 flowid 1:11");
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 300 u32 match ip protocol 17 0xff match ip sport 68 1 flowid 1:11");
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 300 u32 match ip protocol 17 0xff match ip dport 68 1 flowid 1:11");
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 300 u32 match ip protocol 17 0xff match ip sport 547 1 flowid 1:11");
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 300 u32 match ip protocol 17 0xff match ip dport 547 1 flowid 1:11");

	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 300 u32 match ip sport 53 1 flowid 1:11");
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 300 u32 match ip dport 53 1 flowid 1:11");

	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 300 u32 match ip protocol 6 0xff match u8 0x05 0x0f at 0 match u16 0x0000 0xffc0 at 2 match u8 0x10 0xff at 33 flowid 2:11");
	
	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 300 u32 match u8 0x01 0xff at 9 flowid 2:11");
	
	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 300 u32 match ip protocol 17 0xff match ip sport 67 1 flowid 2:11");
	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 300 u32 match ip protocol 17 0xff match ip dport 67 1 flowid 2:11");
	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 300 u32 match ip protocol 17 0xff match ip sport 546 1 flowid 2:11");
	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 300 u32 match ip protocol 17 0xff match ip dport 546 1 flowid 2:11");
	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 300 u32 match ip protocol 17 0xff match ip sport 68 1 flowid 2:11");
	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 300 u32 match ip protocol 17 0xff match ip dport 68 1 flowid 2:11");
	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 300 u32 match ip protocol 17 0xff match ip sport 547 1 flowid 2:11");
	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 300 u32 match ip protocol 17 0xff match ip dport 547 1 flowid 2:11");
	
	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 300 u32 match ip sport 53 1 flowid 2:11");
	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 300 u32 match ip dport 53 1 flowid 2:11");
	
	/* web traffic (port 80 443 3128 8080) */
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 210 u32 match ip protocol 6 0xff match ip sport 80 1 flowid 1:12");
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 210 u32 match ip protocol 6 0xff match ip dport 80 1 flowid 1:12");
   	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 210 u32 match ip protocol 6 0xff match ip sport 8080 1 flowid 1:12");
   	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 210 u32 match ip protocol 6 0xff match ip dport 8080 1 flowid 1:12");
   	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 210 u32 match ip protocol 6 0xff match ip sport 443 1 flowid 1:12");
   	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 210 u32 match ip protocol 6 0xff match ip dport 443 1 flowid 1:12");
   	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 210 u32 match ip protocol 6 0xff match ip sport 3128 1 flowid 1:12");
   	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 210 u32 match ip protocol 6 0xff match ip dport 3128 1 flowid 1:12");
	
   	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 210 u32 match ip protocol 6 0xff match ip sport 80 1 flowid 2:12");
   	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 210 u32 match ip protocol 6 0xff match ip dport 80 1 flowid 2:12");
   	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 210 u32 match ip protocol 6 0xff match ip sport 8080 1 flowid 2:12");
   	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 210 u32 match ip protocol 6 0xff match ip dport 8080 1 flowid 2:12");
   	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 210 u32 match ip protocol 6 0xff match ip sport 443 1 flowid 2:12");
   	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 210 u32 match ip protocol 6 0xff match ip dport 443 1 flowid 2:12");
   	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 210 u32 match ip protocol 6 0xff match ip sport 3128 1 flowid 2:12");
   	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 210 u32 match ip protocol 6 0xff match ip dport 3128 1 flowid 2:12");
	
	/* mail traffic (port 25 110 465 995) */
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 220 u32 match ip protocol 6 0xff match ip sport 25 1 flowid 1:13");
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 220 u32 match ip protocol 6 0xff match ip dport 25 1 flowid 1:13");
   	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 220 u32 match ip protocol 6 0xff match ip sport 110 1 flowid 1:13");
   	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 220 u32 match ip protocol 6 0xff match ip dport 110 1 flowid 1:13");
   	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 220 u32 match ip protocol 6 0xff match ip sport 465 1 flowid 1:13");
   	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 220 u32 match ip protocol 6 0xff match ip dport 465 1 flowid 1:13");
   	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 220 u32 match ip protocol 6 0xff match ip sport 995 1 flowid 1:13");
   	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 220 u32 match ip protocol 6 0xff match ip dport 995 1 flowid 1:13");
	
	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 220 u32 match ip protocol 6 0xff match ip sport 25 1 flowid 2:13");
	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 220 u32 match ip protocol 6 0xff match ip dport 25 1 flowid 2:13");
   	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 220 u32 match ip protocol 6 0xff match ip sport 110 1 flowid 2:13");
   	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 220 u32 match ip protocol 6 0xff match ip dport 110 1 flowid 2:13");
   	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 220 u32 match ip protocol 6 0xff match ip sport 465 1 flowid 2:13");
   	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 220 u32 match ip protocol 6 0xff match ip dport 465 1 flowid 2:13");
   	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 220 u32 match ip protocol 6 0xff match ip sport 995 1 flowid 2:13");
   	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 220 u32 match ip protocol 6 0xff match ip dport 995 1 flowid 2:13");

	/* ftp traffic (port 20 21) not dynamic port! */
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 230 u32 match ip protocol 6 0xff match ip sport 20 1 flowid 1:14");
	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 230 u32 match ip protocol 6 0xff match ip dport 20 1 flowid 1:14");
   	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 230 u32 match ip protocol 6 0xff match ip sport 21 1 flowid 1:14");
   	startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 230 u32 match ip protocol 6 0xff match ip dport 21 1 flowid 1:14");
	
	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 230 u32 match ip protocol 6 0xff match ip sport 20 1 flowid 2:14");
	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 230 u32 match ip protocol 6 0xff match ip dport 20 1 flowid 2:14");
   	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 230 u32 match ip protocol 6 0xff match ip sport 21 1 flowid 2:14");
   	startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 230 u32 match ip protocol 6 0xff match ip dport 21 1 flowid 2:14");
	
	/* default dispatch to other traffic */

	/* user define port filter */
	if($user1_configed == 1){
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 200 u32 match ip protocol 6 0xff match ip sport ".$user1_startport." ".$user1_range." flowid 1:16");
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 200 u32 match ip protocol 6 0xff match ip dport ".$user1_startport." ".$user1_range." flowid 1:16");
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 200 u32 match ip protocol 17 0xff match ip sport ".$user1_startport." ".$user1_range." flowid 1:16");
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 200 u32 match ip protocol 17 0xff match ip dport ".$user1_startport." ".$user1_range." flowid 1:16");

		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 200 u32 match ip protocol 6 0xff match ip sport ".$user1_startport." ".$user1_range." flowid 2:16");
		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 200 u32 match ip protocol 6 0xff match ip dport ".$user1_startport." ".$user1_range." flowid 2:16");
		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 200 u32 match ip protocol 17 0xff match ip sport ".$user1_startport." ".$user1_range." flowid 2:16");
		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 200 u32 match ip protocol 17 0xff match ip dport ".$user1_startport." ".$user1_range." flowid 2:16");
	}

	if($user2_configed == 1){
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 200 u32 match ip protocol 6 0xff match ip sport ".$user2_startport." ".$user2_range." flowid 1:17");
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 200 u32 match ip protocol 6 0xff match ip dport ".$user2_startport." ".$user2_range." flowid 1:17");
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 200 u32 match ip protocol 17 0xff match ip sport ".$user2_startport." ".$user2_range." flowid 1:17");
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 200 u32 match ip protocol 17 0xff match ip dport ".$user2_startport." ".$user2_range." flowid 1:17");

		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 200 u32 match ip protocol 6 0xff match ip sport ".$user2_startport." ".$user2_range." flowid 2:17");
		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 200 u32 match ip protocol 6 0xff match ip dport ".$user2_startport." ".$user2_range." flowid 2:17");
		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 200 u32 match ip protocol 17 0xff match ip sport ".$user2_startport." ".$user2_range." flowid 2:17");
		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 200 u32 match ip protocol 17 0xff match ip dport ".$user2_startport." ".$user2_range." flowid 2:17");
	}

	if($user3_configed == 1){
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 200 u32 match ip protocol 6 0xff match ip sport ".$user3_startport." ".$user3_range." flowid 1:18");
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 200 u32 match ip protocol 6 0xff match ip dport ".$user3_startport." ".$user3_range." flowid 1:18");
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 200 u32 match ip protocol 17 0xff match ip sport ".$user3_startport." ".$user3_range." flowid 1:18");
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 200 u32 match ip protocol 17 0xff match ip dport ".$user3_startport." ".$user3_range." flowid 1:18");

		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 200 u32 match ip protocol 6 0xff match ip sport ".$user3_startport." ".$user3_range." flowid 2:18");
		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 200 u32 match ip protocol 6 0xff match ip dport ".$user3_startport." ".$user3_range." flowid 2:18");
		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 200 u32 match ip protocol 17 0xff match ip sport ".$user3_startport." ".$user3_range." flowid 2:18");
		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 200 u32 match ip protocol 17 0xff match ip dport ".$user3_startport." ".$user3_range." flowid 2:18");
	}

	if($user4_configed == 1){
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 200 u32 match ip protocol 6 0xff match ip sport ".$user4_startport." ".$user4_range." flowid 1:19");
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 200 u32 match ip protocol 6 0xff match ip dport ".$user4_startport." ".$user4_range." flowid 1:19");
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 200 u32 match ip protocol 17 0xff match ip sport ".$user4_startport." ".$user4_range." flowid 1:19");
		startcmd($TC." filter add dev ".$UPLINK_INF." parent 1: protocol ip prio 200 u32 match ip protocol 17 0xff match ip dport ".$user4_startport." ".$user4_range." flowid 1:19");

		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 200 u32 match ip protocol 6 0xff match ip sport ".$user4_startport." ".$user4_range." flowid 2:19");
		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 200 u32 match ip protocol 6 0xff match ip dport ".$user4_startport." ".$user4_range." flowid 2:19");
		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 200 u32 match ip protocol 17 0xff match ip sport ".$user4_startport." ".$user4_range." flowid 2:19");
		startcmd($TC." filter add dev ".$DOWNLINK_INF." parent 2: protocol ip prio 200 u32 match ip protocol 17 0xff match ip dport ".$user4_startport." ".$user4_range." flowid 2:19");
	}


}

function startQoS($trafficctrlp)
{

	$QOS_ENABLE  = query($trafficctrlp."/qos/enable");
	$qostype  = query($trafficctrlp."/qos/qostype");
	if($QOS_ENABLE != 1){
		startcmd("echo QOS is disabled.");
		return;
	}
	if($qostype==1) //by protocol
	{
	       startcmd("echo QOS is by protocol.");
		startQoSByProtocol($trafficctrlp);
	}else //by port
	{
	 	startcmd("echo QOS is by port.");
	       startQoSByPort($trafficctrlp);
	}
}
?>

