#!/bin/sh
echo [$0] ["$1"] [$2] [$3] [$4] [$5] [$6] [$7] [$8] [$9] > /dev/console

if [ -f /var/run/DO_WPS_PB5 ]; then
	echo "We do wps PB5 now" > /dev/console
	xmldbc -P /etc/scripts/wps/wps_PB5_update.php -V SSID="$1" -V AUTHMODE=$2 -V ENCRTYPE=$3 -V KEYINDEX=$4 -V KEYSTR="$5" -V KEYTYPE=$6 > /dev/null
else
	xmldbc -P /etc/scripts/wps/wps_sta_update.php -V SSID="$1" -V AUTHMODE=$2 -V ENCRTYPE=$3 -V KEYINDEX=$4 -V KEYSTR="$5" -V KEYTYPE=$6 > /dev/null
fi
exit 0 
