<?
include "/htdocs/phplib/xnode.php";

/********************************************************************/
fwrite("w",$START, "#!/bin/sh\n");
fwrite("w", $STOP, "#!/bin/sh\n");

/*
$qos_prio_enable = query("/device/qos_prio/enable");
if($qos_prio_enable=="1")
	fwrite("a",$START, "echo 1 > /proc/rt3883/sw_port_wifi_pri_enable\n" );
else
	fwrite("a",$START, "echo 0 > /proc/rt3883/sw_port_wifi_pri_enable\n" );
*/	
fwrite("a",$STOP, "");
fwrite("a",$START,	"exit 0\n");
fwrite("a",$STOP,	"exit 0\n");
?>
