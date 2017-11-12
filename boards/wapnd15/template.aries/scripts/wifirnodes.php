#!/bin/sh
<?
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";

$p		= XNODE_getpathbytarget("","phyinf", "uid", $UID, 0);
$w		= XNODE_getpathbytarget("/wifi", "entry", "uid", query($p."/wifi"), 0);
$stsp	= XNODE_getpathbytarget("/runtime", "phyinf", "uid", $UID, 0);
$dev	= $DEVNAME;

if ($p!="" && $stsp!="" || $w!="")
{	
	set($stsp."/media/wifi/uid",		query($w."/uid"));
	set($stsp."/media/wifi/ssid",		query($w."/ssid"));
	set($stsp."/media/wifi/authtype",	query($w."/authtype"));
	set($stsp."/media/wifi/encrtype",	query($w."/encrtype"));
	set($stsp."/media/wps/enable",		query($w."/wps/enable"));
	set($stsp."/media/wps/configured",	query($w."/wps/configured"));
	set($stsp."/media/wps/pin",			query($w."/wps/pin"));
	if(query($p."/media/freq") == "5")
	{
		if(query($p."/media/channel_Aband") == "0")
		{
			//For AP channel auto mode to read
			setattr($stsp."/media/channel_Aband","get", "alphawifi ".$dev." get_wlan_channel | scut -p Channel:"); 
		}
		else
		{
			set($stsp."/media/channel_Aband", query($p."/media/channel_Aband"));
		}
		set($stsp."/media/wlmode_Aband", query($p."/media/wlmode_Aband"));
		set($stsp."/media/dot11n/bandwidth_Aband", query($p."/media/dot11n/bandwidth_Aband"));
	}
	else
	{
		if(query($p."/media/channel") == "0")
		{
			//For AP channel auto mode to read
			setattr($stsp."/media/channel","get", "alphawifi ".$dev." get_wlan_channel | scut -p Channel:"); 
		}
		else
		{
			set($stsp."/media/channel", query($p."/media/channel_Aband"));
		}
		set($stsp."/media/wlmode", query($p."/media/wlmode"));
		set($stsp."/media/dot11n/bandwidth", query($p."/media/dot11n/bandwidth"));
	}
	set($stsp."/media/freq",            query($p."/media/freq"));
	set($stsp."/media/band",            query($p."/media/band"));
}

?>
exit 0
