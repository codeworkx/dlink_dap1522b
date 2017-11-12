<?
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";

function startcmd($cmd)	{fwrite(a,$_GLOBALS["START"], $cmd."\n");}
function stopcmd($cmd)	{fwrite(a,$_GLOBALS["STOP"], $cmd."\n");}
function error($err)	{startcmd("exit ".$err); stopcmd("exit ".$err); return $err;}

/**********************************************************************/
function devname($uid)
{
	if ($uid=="WLAN-1.1") {return "ra0";}
	else if ($uid=="WLAN-1.2") {return "ra1";}
    else if ($uid=="WLAN-2") {return "ra0";}
	return "ra0";
}

function wificonfig($uid)
{
    fwrite(w,$_GLOBALS["START"], "#!/bin/sh\n");
	fwrite(w,$_GLOBALS["STOP"],  "#!/bin/sh\n");
	
	$dev	= devname($uid);

	$p = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	if ($p=="" || $dev=="")		{ return error(9);}
	if (query($p."/active")!=1) { return error(8);}
	
	$wifi = XNODE_getpathbytarget("/wifi",	"entry",  "uid", query($p."/wifi"), 0);
	if 		(query($wifi."/opmode")=="AP") {$drv="RT3662";}
	else if (query($wifi."/opmode")=="STA")	{$drv="RT3662STA";}
	
	startcmd("rm -f /var/run/".$uid.".DOWN");
	startcmd("echo 1 > /var/run/".$uid.".UP");
	//we must use restart command so that schedule can work correctly.
	startcmd("service ".$drv." restart");
	
	stopcmd("phpsh /etc/scripts/delpathbytarget.php BASE=/runtime NODE=phyinf TARGET=uid VALUE=".$uid);
	stopcmd("ip link set ".$dev." down");
	stopcmd("echo 1 > /var/run/".$uid.".DOWN");
	stopcmd("rm -f /var/run/".$uid.".UP");
	stopcmd("service ".$drv." stop");
	stopcmd("xmldbc -P /etc/services/WIFI/updatewifistats.php -V PHY_UID=".$uid." > /var/run/restart_upwifistats.sh;");
	stopcmd("phpsh /var/run/restart_upwifistats.sh");
	return error(0);
}

?>
