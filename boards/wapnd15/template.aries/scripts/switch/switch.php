<?

//1. set a/b/g/n node
//2. re-insert wifi modules 
//3. restart PHYINF.WIFI service

include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";

$wifi_mode = $PARAM1;
$wifi_ap_host 	= "WLAN-1.1";
$wifi_ap_gz 	= "WLAN-1.2";
$wifi_sta 		= "WLAN-2";

$old_wifi_mode = query("/runtime/device/switchmode");

$peth		 	= XNODE_getpathbytarget("/runtime", "phyinf", "uid", "ETH-1", 0);
$pwifi_ap_host 	= XNODE_getpathbytarget("", "phyinf", "uid", $wifi_ap_host, 0);
$pwifi_ap_gz 	= XNODE_getpathbytarget("", "phyinf", "uid", $wifi_ap_gz, 0);
$pwifi_sta 		= XNODE_getpathbytarget("", "phyinf", "uid", $wifi_sta, 0);
del($peth."/bridge");

if($wifi_mode=="APCLI")
{
	if ($pwifi_sta != "")
	{ 
		set($pwifi_sta."/media/freq", "5");
		set($pwifi_sta."/media/band", "11AN");
		if(query($pwifi_sta."/media/wlmode") == "")
		{
			set($pwifi_sta."/media/wlmode", "an");
		}
	}
	if($peth != "")
	{
		add($peth."/bridge/port",$wifi_sta);
	}	
	set("/runtime/device/switchmode", "APCLI"); 
}
else if($wifi_mode=="AP5G")
{
	if ($pwifi_ap_host != "")
	{
		set($pwifi_ap_host."/media/freq", "5");
		set($pwifi_ap_host."/media/band", "11AN");
		if(query($pwifi_ap_host."/media/wlmode_Aband") == "")
		{
			set($pwifi_ap_host."/media/wlmode_Aband", "an");
		}
	}
	if ($pwifi_ap_gz != "") 
	{ 
		set($pwifi_ap_gz."/media/freq", "5"); 	//for guestzone
	    set($pwifi_ap_gz."/media/band", "11AN");
		if(query($pwifi_ap_gz."/media/wlmode_Aband") == "")
		{
			set($pwifi_ap_gz."/media/wlmode_Aband", "an");
		}
	}
	if($peth != "")
	{
		add($peth."/bridge/port",$wifi_ap_host);	
		add($peth."/bridge/port",$wifi_ap_gz);			
	}

	set("/runtime/device/switchmode", "AP5G"); 
}
else if($wifi_mode=="AP2G")
{
	if ($pwifi_ap_host != "") 
	{ 
		set($pwifi_ap_host."/media/freq", "24");
		set($pwifi_ap_host."/media/band", "11BGN");
		if(query($pwifi_ap_host."/media/wlmode") == "")
		{
			set($pwifi_ap_host."/media/wlmode", "bgn");	
		}
	}
	if ($pwifi_ap_gz != "") 
	{ 
		set($pwifi_ap_gz."/media/freq", "24"); 	//for guestzone
		set($pwifi_ap_gz."/media/band", "11BGN");
		if(query($pwifi_ap_gz."/media/wlmode") == "")
		{
			set($pwifi_ap_gz."/media/wlmode", "bgn");	
		}
	}
	if($peth != "")
	{
		add($peth."/bridge/port",$wifi_ap_host);		
		add($peth."/bridge/port",$wifi_ap_gz);			
	}
	set("/runtime/device/switchmode", "AP2G"); 
}

/* when to re-insmod modules ? --> only when we change gpio switch button */
if($old_wifi_mode!=$wifi_mode)
{
	echo 'ifconfig ra0 down > /dev/null 2>&1\n';
	echo 'ifconfig ra1 down > /dev/null 2>&1\n';

	if($wifi_mode=="APCLI")
	{
		echo 'rmmod rt2860v2_ap 					> /dev/null 2>&1\n';
		echo 'insmod /lib/modules/rt2860v2_sta.ko	> /dev/null 2>&1\n';		
	}
	else 
	{
		echo 'rmmod rt2860v2_sta 					> /dev/null 2>&1\n';
		echo 'insmod /lib/modules/rt2860v2_ap.ko 	> /dev/null 2>&1\n';			
	}
	
	/* To initialize wireless settings*/
	echo 'xmldbc -t "Wifi:1:service PHYINF.WIFI restart"\n';
	
	/* To initialize dhcp server*/
	echo 'xmldbc -t "Wifi:1:service DHCPS4.BRIDGE-1 restart"\n';
	/* To initialize traffic control*/
	echo 'xmldbc -t "TrafficControl:2:service TRAFFICCTRL.BRIDGE-1 restart"\n';

	/* To initialize traffic control*/
	echo 'xmldbc -t "macfilter:2:service MACCTRL restart"\n';
	
	/* To change Power/Status LED status from booting to ready. */
	echo 'xmldbc -t "System:3:event STATUS.READY"\n';
}
else
{
	echo 'event STATUS.READY';
}
?>
