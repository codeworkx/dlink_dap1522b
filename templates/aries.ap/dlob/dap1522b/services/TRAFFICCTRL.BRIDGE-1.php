<?
include "/etc/services/TRAFFICCTRL/trafficctrl.php";
fwrite("w",$START,"#!/bin/sh\n");
fwrite("w",$STOP,"#!/bin/sh\n");
trafficctrl_setup("BRIDGE-1");
?>
