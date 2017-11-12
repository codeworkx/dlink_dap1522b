<? /* vi: set sw=4 ts=4: */
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/phyinf.php";
fwrite("w", $START, "");
fwrite("w", $STOP, "");

$HOSTNAME = query("/device/hostname");

$INF = "br0";
$MDNS_CONF   = "/var/rendezvous.conf";
fwrite("w", $MDNS_CONF, "");
$ahostname_arg="";

if (query("/device/mdnsresponder/enable")!="0")
{
	foreach ("/runtime/services/mdnsresponder/server")
	{
		if(strstr(query("uid"), "MDNSRESPONDER")!="") 
		{
			fwrite("a", $MDNS_CONF, query("srvname")."\n");
			fwrite("a", $MDNS_CONF, query("srvcfg")."\n");
			fwrite("a", $MDNS_CONF, query("port")."\n");
			fwrite("a", $MDNS_CONF, "\n");
		
	  }
	}
	$mac = PHYINF_getmacsetting("BRIDGE-1");
	if ( $mac != "" )
	{
		$macstr = cut($mac, 4, ":").cut($mac, 5, ":");
		$ahostname_arg = " -e ".$HOSTNAME.$macstr;
	}

	fwrite("a", $START, "echo \"mdnsresponder server start !\" > /dev/console\n");
	fwrite("a", $START, "hostname ".$HOSTNAME."\n");
	fwrite("a", $START, "mDNSResponderPosix -b -i ".$INF.$ahostname_arg." -f ".$MDNS_CONF."\n");
	fwrite("a", $STOP, "echo \"mdnsresponder server stop !\" > /dev/console\n");
	fwrite("a", $STOP, "killall -9 mDNSResponderPosix\n");
}
else
{
	fwrite("a", $START, "echo \"mdnsresponder server is disabled !\" > /dev/console\n");
	fwrite("a", $STOP, "echo \"mdnsresponder server is disabled !\" > /dev/console\n");
}

?>