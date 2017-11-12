<?
set("/trafficctrl/entry:1/trafficmgr/whichsubmit", query($SETCFG_prefix."/trafficctrl/entry:1/trafficmgr/whichsubmit"));
if(query($SETCFG_prefix."/trafficctrl/entry:1/trafficmgr/whichsubmit") == "tramgr")
{
	set("/trafficctrl/entry:1/trafficmgr/enable", query($SETCFG_prefix."/trafficctrl/entry:1/trafficmgr/enable")); 
	if(query($SETCFG_prefix."/trafficctrl/entry:1/trafficmgr/enable") == "1")
	{
		movc($SETCFG_prefix."/trafficctrl/entry:1/updownlinkset", "/trafficctrl/entry:1/updownlinkset"); 
		set("/trafficctrl/entry:1/trafficmgr/unlistclientstraffic", query($SETCFG_prefix."/trafficctrl/entry:1/trafficmgr/unlistclientstraffic")); 
	}
}
else if(query($SETCFG_prefix."/trafficctrl/entry:1/trafficmgr/whichsubmit") == "rule")
{
	movc($SETCFG_prefix."/trafficctrl/entry:1/trafficmgr", "/trafficctrl/entry:1/trafficmgr"); 
}
else if(query($SETCFG_prefix."/trafficctrl/entry:1/trafficmgr/whichsubmit") == "qos")
{
	set("/trafficctrl/entry:1/qos/enable", query($SETCFG_prefix."/trafficctrl/entry:1/qos/enable"));
	if(query($SETCFG_prefix."/trafficctrl/entry:1/qos/enable") == "1")
	{
		movc($SETCFG_prefix."/trafficctrl", "/trafficctrl"); 
	}
}
?>
