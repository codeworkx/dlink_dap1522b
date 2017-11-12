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
	/* for station status, we set extension attribute in runtime node*/
	setattr($stsp."/media/connectstatus",       "get", "iwpriv ".$dev." connStatus | grep connStatus: | scut -p connStatus:");
	setattr($stsp."/media/channel",  			"get", "iwpriv ".$dev." show Channel | scut -p show:");
	setattr($stsp."/media/wifi/authtype",  		"get", "iwpriv ".$dev." show AuthMode | scut -p show:");
	setattr($stsp."/media/wifi/encrtype",  		"get", "iwpriv ".$dev." show EncrypType | scut -p show:");
	setattr($stsp."/media/wlmode",  			"get", "iwpriv ".$dev." show WirelessMode | scut -p show:");
	setattr($stsp."/media/dot11n/bandwidth",  	"get", "iwpriv ".$dev." show HtBw | scut -p show:");
	
	set($stsp."/media/wifi/uid",		query($w."/uid"));
	set($stsp."/media/freq",            query($p."/media/freq"));
	set($stsp."/media/band",            query($p."/media/band"));
}

?>
exit 0
