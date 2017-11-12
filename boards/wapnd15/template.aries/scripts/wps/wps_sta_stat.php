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

if ($STAT != "")
{
	event($STAT);
	if($STAT=="WPS.INPROGRESS") {set($p."/media/wps/enrollee/state","WPS_IN_PROGRESS");}
	else if($STAT=="WPS.ERROR") {set($p."/media/wps/enrollee/state","WPS_ERROR");}
	else if($STAT=="WPS.OVERLAP") {set($p."/media/wps/enrollee/state","WPS_OVERLAP");}
	else if($STAT=="WPS.SUCCESS") {set($p."/media/wps/enrollee/state","WPS_SUCCESS");}
	else if($STAT=="WPS.NONE") {set($p."/media/wps/enrollee/state",	"WPS_NONE");}
}
else
{
	TRACE_error("Error: A variable of $STAT is unspicified for wps state!");
}
?>