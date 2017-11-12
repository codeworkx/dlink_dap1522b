<?
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/phyinf.php";

function startcmd($cmd)	{fwrite(a,$_GLOBALS["START"], $cmd."\n");}
function stopcmd($cmd)	{fwrite(a,$_GLOBALS["STOP"], $cmd."\n");}
function error($errno)	{startcmd("exit ".$errno); stopcmd("exit ".$errno);}

/**************************************************************************/
function devname($uid)
{
	if ($uid=="WLAN-1.1") {return "ra0";}
	else if ($uid=="WLAN-1.2") {return "ra1";}
    else if ($uid=="WLAN-2") {return "ra0";}
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

startcmd("killall hostapd > /dev/null 2>&1; sleep 1");
startcmd("xmldbc -P /etc/services/WIFI/rtcfg.php -V PHY_UID=WLAN-1.1 > /var/run/RT2860.dat");
//stopcmd("killall hostapd > /dev/null 2>&1; sleep 1");

/* for each interface. */
$i= 1;
while ($i>0)
{
	$uid = "WLAN-1.".$i;
	$phy = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	if ($phy=="") {$i=0; break;}
	
	$active = query($phy."/active");
	$dev = devname($uid);
	if ($i==1 && $active!=1)
	{
		startcmd("# The major interface (".$uid.") is not active.");
		$i++;
		continue;
	}
	
	$down = fread("", "/var/run/".$uid.".DOWN"); $down+=0;
	if ($down=="1") 
	{ 
		startcmd("# ".$uid." has been shutdown.");
	}
	else if($active!=1)
	{
		startcmd("# ".$uid." is inactive!");
	}
	else
	{
		// secondary ssid
		if($i >= 2)
		{
			/* special command for guestzone 
			 * Guestzone interface can only be brought up IF hostzone interface HAS BEEN BROUGHT UP BEFORE ! 
			 * So when we meet condition :
			 * 	1. hostzone intf hasn't up
			 *  2. guestzone need to up 
			 *  --> We just bring the hostzone intf up and bring it down again. 
			 *
			 * Remember that this kind of situation happens when : 
			 *  1. Hostzone is disabled from web, while guestzone is enabled. 
			 *  2. Hostzone is disabled because of schedule, while guestzone is enabled.
			 */
			$up = fread("", "/var/run/WLAN-1.1.UP");
			if($up=="")
			{
				startcmd("ip link set ".devname('WLAN-1.1')." up");
				startcmd("ip link set ".devname('WLAN-1.1')." down");
			}
		}
		
		startcmd("# ".$uid.", dev=".$dev);
		PHYINF_setup($uid, "wifi", $dev);
		$brdev = find_brdev($uid);
		startcmd("ip link set ".$dev." up");
		if ($brdev!="") 
		{ 
			startcmd("brctl addif ".$brdev." ".$dev);
			if ($uid!="WLAN-1.1")
			{
				stopcmd("echo -n \"group_clear\" > /proc/net/br_group_".$brdev."");
				startcmd("echo -n \"group_clear\" > /proc/net/br_group_".$brdev."");
				startcmd("echo -n \"group 0 ".$brdev." ".$dev."\" > /proc/net/br_group_".$brdev."");
			}
		}
		startcmd("phpsh /etc/scripts/wifirnodes.php DEVNAME=".$dev." UID=".$uid);
		/*upwifistats */
		startcmd("xmldbc -P /etc/services/WIFI/updatewifistats.php -V PHY_UID=".$uid." > /var/run/restart_upwifistats.sh;");
		startcmd("phpsh /var/run/restart_upwifistats.sh");
		
		//primary ssid
		if($i == 1)
		{
			$e_partition = query($phy."/media/e_partition");
			if ($e_partition == 1)
			{
				startcmd("brctl e_partition ".$brdev." 1");
			}else
			{
				startcmd("brctl e_partition ".$brdev." 0");
			}

			$w_partition = query($phy."/media/w_partition");
			if ($w_partition == 2)
			{
				startcmd("iwpriv ".$dev." set WlanPartition=2");
			}else if ($w_partition ==1)
				{
					startcmd("iwpriv ".$dev." set WlanPartition=1");
				}
			else
			{
				startcmd("iwpriv ".$dev." set WlanPartition=0");
			}
			startcmd("phpsh /etc/scripts/wpsevents.php ACTION=ADD TYPE=WPS UID=".$uid);
			startcmd("phpsh /etc/scripts/wpsevents.php ACTION=ADD TYPE=PBC5 UID=".$uid);
			stopcmd("phpsh /etc/scripts/wpsevents.php ACTION=FLUSH TYPE=WPS UID=".$uid);
			stopcmd("phpsh /etc/scripts/wpsevents.php ACTION=FLUSH TYPE=PBC5 UID=".$uid);
		}
	}
	$i++;
}

/* define WFA related info for hostapd */
$dtype	= "urn:schemas-wifialliance-org:device:WFADevice:1";
setattr("/runtime/hostapd/mac",  "get", "devdata get -e lanmac");
setattr("/runtime/hostapd/guid", "get", "genuuid -s \"".$dtype."\" -m \"".query("/runtime/hostapd/mac")."\"");

startcmd("xmldbc -P /etc/services/WIFI/hostapdcfg.php > /var/topology.conf");
startcmd("hostapd /var/topology.conf &");
startcmd("sleep 1");
startcmd("service TRAFFICCTRL.BRIDGE-1 restart"); //harry_shen add
stopcmd("service TRAFFICCTRL.BRIDGE-1 stop"); //harry_shen add
startcmd("service MULTICAST restart");
stopcmd("service MULTICAST stop");
error(0);

?>
