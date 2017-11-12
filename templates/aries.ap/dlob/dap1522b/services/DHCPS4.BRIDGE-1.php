<?
include "/etc/services/DHCPS/dhcpserver.php";
fwrite("w",$START,"#!/bin/sh\n");
fwrite("w",$STOP,"#!/bin/sh\n");
dhcps4setup("BRIDGE-1");
fwrite("a", $START, "exit 0\n");
fwrite("a", $STOP,  "exit 0\n");
?>
