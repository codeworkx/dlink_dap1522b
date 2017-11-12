<?
include "/htdocs/phplib/phyinf.php";
include "/htdocs/phplib/xnode.php";

function get_wfa_uuid()
{
	$wfa = XNODE_getpathbytarget("/runtime/upnp", "dev", "deviceType",
			"urn:schemas-wifialliance-org:device:WFADevice:1", 0);

	if ($wfa != "") return query($wfa."/guid");
	return "";
}

$conf = "/var/lld2d.conf";
$wlaninf = PHYINF_getifname("WLAN-1.1");
$laninf = PHYINF_getruntimeifname("BRIDGE-1");
if ($laninf=="") fwrite(a,$START,"exit 9\n");
else
{

	$icon = "/etc/config/lld2d.ico";

	fwrite(w, $conf,
		"helper = /etc/scripts/libs/lld2d-helper.php\n".
		"icon = ".$icon."\n".
		"jumbo-icon = ".$icon."\n".
		);

	fwrite(w,$START,"#!/bin/sh\nlld2d -c ".$conf." ".$laninf);
	if ($wlaninf!="") fwrite(a,$START," ".$wlaninf);
	fwrite(a,$START," & > /dev/console\nexit 0\n");
}

fwrite(w,$STOP,"#!/bin/sh\nkillall lld2d\nexit 0\n");
?>
