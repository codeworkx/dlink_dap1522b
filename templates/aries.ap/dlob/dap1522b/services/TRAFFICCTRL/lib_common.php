<?


function stop_setup($trafficctrlp)
{
   	$TC="tc";
   	$UPLINK_INF="veth0";
	$DOWNLINK_INF="veth1";
	
	stopcmd("ifconfig veth3 down");
	stopcmd("ifconfig veth2 down");
	stopcmd("ifconfig veth1 down");
	stopcmd("ifconfig veth0 down");
	
	stopcmd("  if [ \"`ifconfig ".$UPLINK_INF." | grep ".$UPLINK_INF." | cut -b 0-5`\" = \"".$UPLINK_INF."\" ]; then");
	stopcmd($TC." qdisc del dev ".$UPLINK_INF." root");
	stopcmd(" fi");

	stopcmd("  if [ \"`ifconfig ".$DOWNLINK_INF." | grep ".$DOWNLINK_INF." | cut -b 0-5`\" = \"".$DOWNLINK_INF."\" ]; then");
	stopcmd($TC." qdisc del dev ".$DOWNLINK_INF." root");
	stopcmd(" fi");

	stopcmd("  if [ \"`lsmod | grep vethdev | grep -v grep| cut -b 0-7`\" = \"vethdev\" ]; then");
	stopcmd("rmmod vethdev");
	stopcmd(" fi");
	
	stopQoS();
	stopTrafficctrl();
}
//set eth as uplink, wifi as downlink.
function bindinf($trafficctrlp)
{

	$INSMOD="insmod /lib/modules/";
	$QOS_ENABLE  = query($trafficctrlp."/qos/enable");
	$qostype  = query($trafficctrlp."/qos/qostype");
	$trafficmgr_enable = query($trafficctrlp."/trafficmgr/enable");

	$needbininf=0;
	if($trafficmgr_enable==1){$needbininf=1;}
	if($QOS_ENABLE==1 && $qostype==1){$needbininf=1;}

	if($needbininf==0){return;}
	$rule_path = $trafficctrlp."/updownlinkinf";
   	startcmd($INSMOD."vethdev.ko devnum=4");

	foreach($rule_path."/uplinkinf/entry")
	{

		$uplinkifname=query($rule_path."/uplinkinf/entry:".$InDeX."/ifname");

              if($uplinkifname!="")
              {
                  startcmd("vethctl -a veth0 ".$uplinkifname."");
              }
	}

	
	foreach($rule_path."/downlinkinf/entry")
	{
		
		$downlinkifname=query($rule_path."/downlinkinf/entry:".$InDeX."/ifname");

              if($downlinkifname!="")
              {
                  startcmd("vethctl -a veth1 ".$downlinkifname."");
              }
	}	
	
	startcmd("vethctl -a veth2 veth0");
	startcmd("vethctl -a veth3 veth1");
	startcmd("ifconfig veth3 up");
	startcmd("ifconfig veth2 up");
	startcmd("ifconfig veth1 up");
	startcmd("ifconfig veth0 up");
}


function start_setup($trafficctrlp)
{

       //if runtime==0
       //check if wlan is run
       //To check operation mode
      
   	bindinf($trafficctrlp);
   	startQoS($trafficctrlp);
   	startTrafficctrl($trafficctrlp);
   	//runtime=1
}

?>
