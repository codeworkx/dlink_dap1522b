<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";

$uid = "WLAN-2";
/*
1. set SSID, AUTHMODE, ENCRTYPE, KEYINDEX, KEYSTR, KEYTYPE
2. save to flash
*/

$p 	= XNODE_getpathbytarget("/runtime", "phyinf", "uid", $uid, 0);
if ($p == "") 
{
	TRACE_error("wps_sta_update :  phyinf not exist.");	
	return;
}

$p 	= XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
$wp = XNODE_getpathbytarget("/wifi", "entry", "uid", query($p."/wifi"), 0);

if($SSID!="") 							
{
	set($wp."/ssid",$SSID);
}
if($AUTHMODE!="")
{
	set($wp."/authtype",$AUTHMODE);
	set($wp."/encrtype",$ENCRTYPE);
	
	$len = strlen($KEYSTR);
	
	if($ENCRTYPE=="WEP")
	{
		set($wp."/nwkey/wep/defkey",$KEYINDEX);
		set($wp."/nwkey/wep/key:".$KEYINDEX ,$KEYSTR);
		/*
		 *	Ascii	 64 bits ->  5 bytes
		 *			128 bits -> 13 bytes
		 *	Hex		 64 bits -> 10 bytes
		 *			128 bits -> 26 bytes
		 *
		 * size should be filled with "64" and "128", so we derive it from above.
		 */
		if		($len==5 || $len==10) {set($wp."/nwkey/wep/size", "64");}
		else if ($len==13 || $len==26) {set($wp."/nwkey/wep/size", "128");}
		else {set($wp."/nwkey/wep/size", "64");} //just for default
		
		if ($len==5  || $len==13) {set($wp."/nwkey/wep/ascii", "1");}
		else {set($wp."/nwkey/wep/ascii", "0");}
	}
	else if($ENCRTYPE=="TKIP" || $ENCRTYPE=="AES" || $ENCRTYPE=="TKIPAES")
	{
		if($len == 64) { $passphrase = 0;}
		else 		   { $passphrase = 1;}
		
		set($wp."/nwkey/psk/key", $KEYSTR);
		set($wp."/nwkey/psk/passphrase", $passphrase);
	}
	
	set($wp."/wps/configured", "1");
}

event("DBSAVE");
?>
