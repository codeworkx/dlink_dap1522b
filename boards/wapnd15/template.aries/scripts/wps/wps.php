#!/bin/sh
<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";

function update_state($uid, $state)
{
	$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $uid, 0);
	if ($p != "") {set($p."/media/wps/enrollee/state", $state);}
}

function kill_wpatalk($uid)
{
	$pidfile = "/var/run/wpatalk.".$uid.".pid";
	$pid = fread("s", $pidfile);
	if ($pid != "")
	{
		echo "kill ".$pid."\n";
		echo "rm ".$pidfile."\n";
	}
}

function update_locksecurity($uid)
{
    $p = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
    if ($p == "")
    {
        return 0;
    }
    $wifiuid = query($p."/wifi");
    $wifip = XNODE_getpathbytarget("wifi", "entry", "uid",$wifiuid, 0);
    if ($wifip == "")
    {
        return 0;
    }
	
    $locksec = query($wifip."/wps/locksecurity");
    if($locksec != 1)
    {
        set($wifip."/wps/locksecurity",1);
        return 1;
    }
	
    return 0;
}

function do_wps_sta($uid, $method)
{
	$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $uid, 0);
	if ($p == "") {return;}
	$dev = query($p."/name");
	if($method == "pbc")
	{
		//init
		echo "iwpriv ".$dev." wsc_conf_mode 1 \n";		//1: enrollee , 2: registrar
		echo "iwpriv ".$dev." wsc_mode 2\n";			//1: PIN, 2: PBC
		echo "iwpriv ".$dev." wsc_ap_band 2\n";			//0: prefer 2.4G, 1: prefer 5G  2 : prefer auto
		echo "iwpriv ".$dev." wsc_start \n";
	}
	else if($method == "pin")
	{
		echo "iwpriv ".$dev." wsc_conf_mode 1 \n";	//1: enrollee , 2: registrar
		echo "iwpriv ".$dev." wsc_mode 1\n";		//1: PIN, 2: PBC
		echo "iwpriv ".$dev." wsc_vender_pin ".query($p."/media/wps/enrollee/pin")."\n"; // update our pin code to driver
		echo "iwpriv ".$dev." wsc_ap_band 2\n";		//0: prefer 2.4G, 1: prefer 5G  2 : prefer auto
		echo "iwpriv ".$dev." wsc_start \n";
	}
	else {return;}
}

function do_wps_ap($uid, $method)
{
	$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $uid, 0);
	if ($p == "") {return;}
	$dev = query($p."/name");
	$pin = query($p."/media/wps/enrollee/pin");

	if		($method == "pbc") {$cmd = "configthem";}
	else if ($method == "pin") {$cmd = "\"configthem pin=".$pin."\"";}
	else {return;}

	kill_wpatalk($uid);
	$pidfile = "/var/run/wpatalk.".$uid.".pid";
	echo "wpatalk ".$dev." ".$cmd." &\n";
	echo "echo $! > ".$pidfile."\n";
}

function do_wps($uid, $method)
{
	$p = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	if ($p == "") {return;}
	if (query($p."/active")!="1") {return;}
	$p = XNODE_getpathbytarget("/wifi", "entry", "uid", query($p."/wifi"), 0);
	if ($p == "") {return;}
	$enable = query($p."/wps/enable");
	if ($enable!="1") {return;}
	
	update_state($UID1, "WPS_IN_PROGRESS");
	event("WPS.INPROGRESS");
	$opmode = query($p."/opmode");
	if($opmode == "STA")	{ do_wps_sta($uid, $method); 	}
	else					{ do_wps_ap($uid, $method); 	}

}

function set_wps($uid)
{
	TRACE_debug("SETWPS(".$uid."):\n");
	
	/* Validating the interface. */
	if ($uid=="")		{TRACE_debug("SETWPS: error - no UID!\n"); return;}
	$phy = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	if ($phy=="")		{TRACE_debug("SETWPS: error - no PHYINF!\n"); return;}
	$wifi = query($phy."/wifi");
	if ($wifi=="")		{TRACE_debug("SETWPS: error - no wifi!\n"); return;}
	$phy_wifi = XNODE_getpathbytarget("/wifi", "entry", "uid", $wifi, 0);
	if ($phy_wifi=="")	{TRACE_debug("SETWPS: error - no wifi profile!\n"); return;}
	
	/* The WPS result. */
	anchor("/runtime/wps/setting");
	$scfg	= query("selfconfig");	TRACE_debug("selfconf	= ".$scfg);
	$ssid	= query("ssid");		TRACE_debug("ssid		= ".$ssid);
	$atype	= query("authtype");	TRACE_debug("authtype	= ".$atype);
	$etype	= query("encrtype");	TRACE_debug("encrtype	= ".$etype);
	$defkey	= query("defkey");		TRACE_debug("defkey		= ".$defkey);
	$maddr	= query("macaddr");		TRACE_debug("macaddr	= ".$maddr);
	$newpwd	= query("newpassword");	TRACE_debug("newpwd		= ".$newpwd);
	$devpid	= query("devpwdid");	TRACE_debug("devpwdid	= ".$devpid);
	
	/* If we started from Unconfigured AP (self configured),
	 * change the setting to auto. */
	if		($scfg == 1)	{ $atype = 7; $etype = 4; /* WPA/WPA2 PSK & TKIP+AES */ }
	
	if		($atype == 0)	{$atype = "OPEN";}
	else if ($atype == 1)	{$atype = "SHARED";}
	else if ($atype == 2)	{$atype = "WPA";}
	else if ($atype == 3)	{$atype = "WPAPSK";}
	else if ($atype == 4)	{$atype = "WPA2";}
	else if ($atype == 5)	{$atype = "WPA2PSK";}
	else if ($atype == 6)	{$atype = "WPA+2";}
	else if ($atype == 7)	{$atype = "WPA+2PSK";}
	
	if		($etype == 0)	{$etype = "NONE";}
	else if ($etype == 1)	{$etype = "WEP";}
	else if ($etype == 2)	{$etype = "TKIP";}
	else if ($etype == 3)	{$etype = "AES";}
	else if ($etype == 4)	{$etype = "TKIP+AES";}
	
	//if we use n only mode, then we need to avoid some cipher, such WEP and TKIP.
	if(query($phy."/media/freq") == "5")
	{
		$wlmode = query($phy."/media/wlmode_Aband");
	}
	else
	{
		$wlmode = query($phy."/media/wlmode");
	}
	if($wlmode == "n" && $etype == "WEP" || $etype == "TKIP")
	{
		TRACE_error("For n only wireless mode, we can't apply WEP or TKIP cipher...\n");	
		return;
	}
	
	if ($scfg != 1)
	{
		set($phy_wifi."/ssid",		$ssid);
	}
	set($phy_wifi."/authtype",	$atype);
	set($phy_wifi."/encrtype",	$etype);

	if ($etype=="WEP")
	{
		foreach ("key")
		{
			TRACE_debug("key[".$InDeX."]");
			$idx = query("index");	TRACE_debug("key index	= ".$idx);
			$key = query("key");	TRACE_debug("key		= ".$key);
			$fmt = query("format");	TRACE_debug("format		= ".$fmt);
			$len = query("len");	TRACE_debug("len		= ".$len);
			
			if ($idx<5 && $idx>0) {set($phy_wifi."/nwkey/wep/key:".$idx, $key);}
		}
		
		if ($fmt!=1) {$fmt=0;}
		set($phy_wifi."/nwkey/wep/defkey",	$idx);
		set($phy_wifi."/nwkey/wep/ascii",	$fmt);
		/*
		 *	Ascii	 64 bits ->  5 bytes
		 *			128 bits -> 13 bytes
		 *	Hex		 64 bits -> 10 bytes
		 *			128 bits -> 26 bytes
		 *
		 * size should be filled with "64" and "128", so we derive it from above.
		 */
		if		($len==5  || $len==10)	{set($phy_wifi."/nwkey/wep/size", "64");}
		else if ($len==13 || $len==26)	{set($phy_wifi."/nwkey/wep/size", "128");}
		else {set($phy_wifi."/nwkey/wep/size", "64");} //just for default
	}
	else
	{
		/* The 2st key only. */
		$idx = query("key:1/index");	TRACE_debug("key index	= ".$idx);
		$key = query("key:1/key");		TRACE_debug("key		= ".$key);
		$fmt = query("key:1/format");	TRACE_debug("format		= ".$fmt);
		$len = query("key:1/len");		TRACE_debug("len		= ".$len);
		if($fmt == "") {$fmt=1;}
		set($phy_wifi."/nwkey/psk/passphrase", $fmt);
		set($phy_wifi."/nwkey/psk/key", $key);
	}
	
	set($phy_wifi."/wps/configured", "1");
	set($phy_wifi."/wps/locksecurity", "1");
}

/************************************************************************/

if($PARAM2!="") { $UID1 = $PARAM2; }
else
{
	TRACE_error("$UID for WPS is unspecified. Use WLAN-1.1 as default\n");
	$UID1 = "WLAN-1.1";
}

if ($PARAM1=="pin" || $PARAM1=="pbc")
{
	TRACE_debug("PIN/PBC:".$PARAM1);
	do_wps($UID1, $PARAM1);
}
else if ($PARAM1=="restartap")
{
	set_wps($UID1);
	event("DBSAVE");
	echo 'xmldbc -t "WPS:3:service PHYINF.WIFI restart"\n';
}
else if ($PARAM1=="pb5")
{
	/*
		1. remove ap modules, insert station modules, and do station wps as enrollee
		2. if pb5 is success, it will directly switch back to origin mode (AP5G or AP2G)
		3. we check for pb5 after 120 seconds, if no success, then we switch back to origin mode. 
	*/
	$switchmode = query("/runtime/device/switchmode");
	if($switchmode=="APCLI")
	{
		TRACE_error("wps_PB5 should be in AP mode, not in bridge/APCLI mode. Cancel operation ... \n");	
		return;
	}
	else if ($switchmode=="AP2G") 	{ $prefer = 0; }	//0: prefer 2.4G, 1: prefer 5G, 2: prefer auto
	else if ($switchmode=="AP5G")	{ $prefer = 1; }
	else 							{ $prefer = 2; }
	
	echo "echo ".$switchmode." > /var/run/DO_WPS_PB5\n";
	echo "/etc/scripts/setswitch.sh APCLI\n";
	echo "event WPSPB5.DONE add \"/etc/scripts/setswitch.sh ".$switchmode.";rm -f /var/run/DO_WPS_PB5;\"\n";
	//if suceess, the wps_PB5_update.php will directly bring our switch back to origin.
	//if after 120 seconds no success, we'll also bring our switch back to origin.
	echo "xmldbc -t pb5_wps_check:120:\"/etc/scripts/wps/wps_PB5_timeoutchk.sh\"\n";

	//we wait for interface to up before we do wps in station
	echo "while [ \"`ifconfig ra0 | grep \"RUNNING\"`\" == \"\" ]; do\n";
	echo "echo \"sleep 1, wait for station interface up\" > /dev/console\n";
	echo "sleep 1\n";
	echo "done\n";

	echo "iwpriv ra0 wsc_conf_mode 1 \n";					//1: enrollee , 2: registrar
	echo "iwpriv ra0 wsc_mode 2\n";							//1: PIN, 2: PBC
	echo "iwpriv ra0 wsc_ap_band ".$prefer."\n";			//0: prefer 2.4G, 1: prefer 5G, 2: prefer auto
	echo "iwpriv ra0 wsc_start \n";
}
else if ($PARAM1=="WPS_NONE")			{update_state($UID1,"WPS_NONE");		event("WPS.NONE");}
else if ($PARAM1=="WPS_IN_PROGRESS")	{update_state($UID1,"WPS_IN_PROGRESS");	event("WPS.INPROGRESS");}
else if ($PARAM1=="WPS_ERROR")			{update_state($UID1,"WPS_ERROR");		event("WPS.ERROR");}
else if ($PARAM1=="WPS_OVERLAP")		{update_state($UID1,"WPS_OVERLAP");		event("WPS.OVERLAP");}
else if ($PARAM1=="WPS_SUCCESS")
{
	/* Validating the interface. */
	if ($UID1=="")		{TRACE_debug("SETWPS: error - no UID!\n"); return;}
	$phy = XNODE_getpathbytarget("", "phyinf", "uid", $UID1, 0);
	if ($phy=="")		{TRACE_debug("SETWPS: error - no PHYINF!\n"); return;}
	$wifi = query($phy."/wifi");
	if ($wifi=="")		{TRACE_debug("SETWPS: error - no wifi!\n"); return;}
	$phy_wifi = XNODE_getpathbytarget("/wifi", "entry", "uid", $wifi, 0);
	if ($phy_wifi=="")	{TRACE_debug("SETWPS: error - no wifi profile!\n"); return;}

	update_state($UID1, "WPS_SUCCESS");
	event("WPS.SUCCESS");
	kill_wpatalk($UID1);
	$dosave = update_locksecurity($UID1);
    if($dosave == 1)
    {
        event("DBSAVE");
        echo 'xmldbc -t "WPS:2:service PHYINF.WIFI restart"\n';
    }
}
else
{
	$err = "usage: wps.sh [pin|pbc|WPS_NONE|WPS_IN_PROGRESS|WPS_ERROR|WPS_OVERLAP|WPS_SUCCESS]";
	echo 'echo "'.$err.'" > /dev/console\n';
}
?>
