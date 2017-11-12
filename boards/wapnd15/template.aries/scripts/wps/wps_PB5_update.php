<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";

$uid = "WLAN-1.1";
/*
1. set SSID, AUTHMODE, ENCRTYPE, KEYINDEX, KEYSTR, KEYTYPE to AP settings
2. save to flash
*/

$p 	= XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
$wp = XNODE_getpathbytarget("/wifi", "entry", "uid", query($p."/wifi"), 0);

if($SSID!="") 							
{
	set($wp."/ssid", $SSID);
}
if($AUTHMODE!="")
{
	set($wp."/authtype",			$AUTHMODE);
	set($wp."/encrtype",			$ENCRTYPE);
	
	$len = strlen($KEYSTR);
	if($ENCRTYPE=="WEP")
	{
		set($wp."/nwkey/wep/defkey"			,$KEYINDEX);
		set($wp."/nwkey/wep/key:".$KEYINDEX ,$KEYSTR);

		if		($len==5  || $len==10)	set($wp."/nwkey/wep/size", "64"); 
		else if ($len==13 || $len==26)	set($wp."/nwkey/wep/size", "128");
		else set($wp."/nwkey/wep/size", "64"); //just for default

		if		($len==5  || $len==13)	set($wp."/nwkey/wep/ascii",	"1");	//ascii
		else if ($len==10 || $len==26)	set($wp."/nwkey/wep/ascii",	"0");	//hex
		else set($wp."/nwkey/wep/ascii",	"");

	}
	else if($ENCRTYPE=="TKIP" || $ENCRTYPE=="AES" || $ENCRTYPE=="TKIPAES")
	{
		
		if($len == 64) { $passphrase = 0;}
		else 		   { $passphrase = 1;}
		
		set($wp."/nwkey/psk/key", $KEYSTR);
		set($wp."/nwkey/psk/passphrase", $passphrase);	
	}
	
	/* we always set wireless mode to b/g/n and channel width to auto 20/40, if we are TKIP */
	if($ENCRTYPE=="TKIP" || $ENCRTYPE=="TKIPAES" || $ENCRTYPE=="WEP")
	{
		set($p."/media/wlmode", "bgn");
		set($p."/media/wlmode_Aband", "an");
		set($p."/media/dot11n/bandwidth", "20");
		set($p."/media/dot11n/bandwidth_Aband", "20");
	}
}

//well, since PBC5 actually for AP, then we should set "configured".
set($wp."/wps/configured", "1");

event("DBSAVE");
$file = fread("", "/var/run/DO_WPS_PB5");
if ($file!="")
{
	//echo "rm -f /var/run/DO_WPS_PB5\n";
	event("WPSPB5.DONE");
}
TRACE_error("DO_WPS_PB5 success... !!!!");

?>
