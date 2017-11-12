<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/phyinf.php";

/********************************************************************/
function devname($uid)
{
	if ($uid=="WLAN-1.1") {return "ra0";}
	else if ($uid=="WLAN-1.2") {return "ra1";}
    else if ($uid=="WLAN-2") {return "ra0";}
	return "ra0";
}

/********************************************************************/

if(isfile("/usr/sbin/updatewifistats")!=1)
{
	TRACE_error("/usr/sbin/updatewifistats doesn't exist \n");
	return ;	
}	

if ($PHY_UID == "") $PHY_UID="WLAN-1.1";
$prefix = cut($PHY_UID, 0,"-");

$upwifistats_pidfile = "/var/run/upwifistats.pid";
$helper_script 		 = "/etc/scripts/upwifistatshlper.sh";

/* restart upwifistats 
 * 1. kill previous pid
 * 2. restart the upwifistats
*/

$pid = fread("", $upwifistats_pidfile);
if($pid != "") {echo "kill ".$pid."\n";}

$upwifi_attr0 	= "updatewifistats -s ".$helper_script."  -m RT2800 -i ".devname($PHY_UID)." ";
$upwifi_attr1 	= "-x ";	//for upwifistats argument (-x --> /phyinf:#)
$upwifi_attr2 	= "-r ";	//for upwifistats argument (-r --> /runtime/phyinf:#)
$found = 0;
/* for each interface. */
$i=1;
while ($i>0)
{
	$uid = $prefix."-1.".$i;
	
	$p = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	if ($p=="") {$i=0; break;}

	$r = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $uid, 0);	
	if ($r!="") 
	{
		$upwifi_attr1 = $upwifi_attr1.$p." ";	
		$upwifi_attr2 = $upwifi_attr2.$r." ";
		$found = 1;
	}
	$i++; 
}

if($found==1) 
{
	$cmd = $upwifi_attr0.$upwifi_attr1.$upwifi_attr2." &";
	TRACE_error($cmd);
	echo $cmd."\n";
	echo "echo $! > ".$upwifistats_pidfile."\n";
}
?>