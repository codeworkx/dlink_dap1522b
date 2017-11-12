#!/bin/sh
echo [$0] $1 $2 ... > /dev/console
if [ -f /var/run/DO_WPS_PB5 ]; then
	event WPSPB5.DONE;
	event WPSPB5.DONE add "null";
fi
exit 0 