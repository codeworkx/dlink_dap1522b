#!/bin/sh
echo [$0] $1 $2 ... > /dev/console
phpsh /etc/scripts/switch/switch.php PARAM1=$1 PARAM2=$2
exit 0
