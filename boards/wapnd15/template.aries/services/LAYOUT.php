<?
/* We use VID 2 for WAN port, VID 1 for LAN ports.
 * by David Hsieh <david_hsieh@alphanetworks.com> */
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/phyinf.php";

function startcmd($cmd)	{fwrite(a,$_GLOBALS["START"], $cmd."\n");}
function stopcmd($cmd)	{fwrite(a,$_GLOBALS["STOP"], $cmd."\n");}
function error($errno)	{startcmd("exit ".$errno); stopcmd("exit ".$errno);}
function layout_bridge()
{
	SHELL_info($START, "LAYOUT: Start bridge layout ...");

	/* Start .......................................................................... */
	/* Config RTL8367RB as bridge mode layout. */
	//setup_switch("bridge");

	/* Using WAN MAC address during bridge mode. */
	$mac = PHYINF_getmacsetting("BRIDGE-1");

	/* Create bridge interface. */
	startcmd("brctl addbr br0; brctl stp br0 off; brctl setfd br0 0");
	startcmd("brctl addif br0 eth0");
	startcmd("brctl addif br0 ra0");
	startcmd("ip link set br0 up");

	/* Setup the runtime nodes. */
	PHYINF_setup("ETH-1", "eth", "br0");

	//+++ hendry, for wifi topology
	$p = XNODE_getpathbytarget("", "phyinf", "uid", "ETH-1", 0);
	set($p."/bridge/ports/entry:1/uid",		"MBR-1");
	set($p."/bridge/ports/entry:1/phyinf",	"WLAN-1.1");
	set($p."/bridge/ports/entry:2/uid",		"MBR-2");
	set($p."/bridge/ports/entry:2/phyinf",	"WLAN-1.2");
	set($p."/bridge/ports/entry:3/uid",		"MBR-3");
	set($p."/bridge/ports/entry:3/phyinf",	"WLAN-2");
	//--- hendry

	/* Done */
	startcmd("xmldbc -s /runtime/device/layout bridge");
//	startcmd("service ENLAN start");
	startcmd("service PHYINF.ETH-1 alias PHYINF.BRIDGE-1");
	startcmd("service PHYINF.ETH-1 start");

	/* ip alias */
	$mactmp = cut($mac, 4, ":");  $mac4 = strtoul($mactmp, 16);
	$mactmp = cut($mac, 5, ":");  $mac5 = strtoul($mactmp, 16);

	/* skip 169.254.0.0 & 169.254.255.255 */
	if($mac4 == "0" && $mac5 == "0") $aip = "169.254.0.1";
	else if($mac4 == "255" && $mac5 == "255") $aip = "169.254.0.1";
	else $aip = "169.254.".$mac4.".".$mac5;

	startcmd("ifconfig br0:1 192.168.0.50 up");
	startcmd("ifconfig br0:2 ".$aip." up");
	$p = XNODE_getpathbytarget("/runtime", "inf", "uid", "BRIDGE-1", 1);
	set($p."/ipalias/cnt",2);
	set($p."/ipalias/ipv4/ipaddr:1","192.168.0.50");
	set($p."/ipalias/ipv4/ipaddr:2",$aip);
	set($p."/devnam","br0"); 

	/* Stop ........................................................................... */
	SHELL_info($STOP, "LAYOUT: Stop bridge layout ...");
	stopcmd("service PHYINF.ETH-1 stop");
	stopcmd("service PHYINF.BRIDGE-1 delete");
	stopcmd('xmldbc -s /runtime/device/layout ""');
	stopcmd("/etc/scripts/delpathbytarget.sh /runtime phyinf uid ETH-1");
	stopcmd("brctl delif br0 rai0");
	stopcmd("brctl delif br0 ra0");
	stopcmd("brctl delif br0 eth0");
	stopcmd("ip link set eth0 down");
	stopcmd("brctl delbr br0");
	//stopcmd("rtlioc initvlan");
	return 0;
}

/* everything starts from here !! */
fwrite("w",$START, "#!/bin/sh\n");
fwrite("w", $STOP, "#!/bin/sh\n");

$ret = 9;
//
$layout	= query("/device/layout");
startcmd("ifconfig lo up");
stopcmd("ifconfig lo down");

if ($layout=="apclient")
{
	SHELL_info($STOP, "LAYOUT: to do ap client ...");
}
else if ($layout=="bridge")
{
	$ret = layout_bridge();
}

startcmd("usockc /var/gpio_ctrl GPIO_SWITCH");
startcmd("/etc/scripts/setswitch.sh `cat /var/gpio_ctrl_result`");

error($ret);
?>
