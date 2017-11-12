<?
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/phyinf.php";

function startcmd($cmd)	{fwrite(a,$_GLOBALS["START"], $cmd."\n");}
function stopcmd($cmd)	{fwrite(a,$_GLOBALS["STOP"], $cmd."\n");}
function error($errno)	{startcmd("exit ".$errno); stopcmd("exit ".$errno);}

/**************************************************************************/
function devname($uid)
{
	return "ra0";
}

function find_brdev($phyinf)
{
	foreach ("/runtime/phyinf")
	{
		if (query("type")!="eth") {continue;}
		foreach ("bridge/port") if ($VaLuE==$phyinf) {$find = "yes"; break;}
		if ($find=="yes") {return query("name");}
	}
	return "";
}

/**************************************************************************/

fwrite("w",$START, "#!/bin/sh\n");
fwrite("w", $STOP, "#!/bin/sh\n");

$uid = "WLAN-2";
startcmd("xmldbc -P /etc/services/WIFI/rtcfg_sta.php -V PHY_UID=".$uid." > /var/run/RT2860.dat");

$p = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
if ($p=="") {error(8);}

$active = query($p."/active");
$dev = devname($uid);

$down = fread("", "/var/run/".$uid.".DOWN"); $down+=0;
if ($down=="1") startcmd("# ".$uid." has been shutdown.");
else
{
	if ($active!=1) {startcmd("# ".$uid." is inactive!");}
	else
	{
		PHYINF_setup($uid, "wifi", $dev);
		$brdev = find_brdev($uid);

		startcmd("ip link set ".$dev." up");
		if ($brdev!="") {startcmd("brctl addif ".$brdev." ".$dev);}
		startcmd("phpsh /etc/scripts/wifirnodes_sta.php DEVNAME=".$dev." UID=".$uid);
		startcmd("phpsh /etc/scripts/wpsevents.php ACTION=ADD TYPE=WPS UID=".$uid);
		startcmd("phpsh /etc/scripts/wpsevents.php ACTION=ADD TYPE=PBC5 UID=".$uid);
		stopcmd("phpsh /etc/scripts/wpsevents.php ACTION=FLUSH TYPE=WPS UID=".$uid);
		stopcmd("phpsh /etc/scripts/wpsevents.php ACTION=FLUSH TYPE=PBC5 UID=".$uid);
	}
}

//To control bridge LED
startcmd("event BRIDGE.LED.ON");
stopcmd("event BRIDGE.LED.OFF");

startcmd("service MULTICAST restart");
stopcmd("service MULTICAST restart");

error(0);

?>
