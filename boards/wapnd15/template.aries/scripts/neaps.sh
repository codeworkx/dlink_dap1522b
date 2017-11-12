#!/bin/sh
echo [$0]: $1 .... > /dev/console
case "$1" in
start)
	[ -f /var/run/neaps_stop.sh ] && sh /var/run/neaps_stop.sh > /dev/console
	xmldbc -P /etc/scripts/neaps_run.php -V START=/var/run/neaps_start.sh -V STOP=/var/run/neaps_stop.sh
	xmldbc -P /etc/scripts/neaps_config.php -V START=/var/run/neaps.conf
	sh /var/run/neaps_start.sh > /dev/console
	;;
stop)
	if [ -f /var/run/neaps_stop.sh ]; then
		sh /var/run/neaps_stop.sh
		rm -f /var/run/neaps_stop.sh
	fi
	;;	
*)
	echo [$0]: Invalid argument - $1 > /dev/console
	;;
esac
