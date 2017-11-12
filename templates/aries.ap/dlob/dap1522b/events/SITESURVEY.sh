#!/bin/sh
status=`xmldbc -g /runtime/phyinf:2/media/connectstatus`
if [ "$status" = "Connected" ]; then
	alphawifi ra0 sta_scan_update
	sleep 5;
fi
iwpriv ra0 get_site_survey > /var/ssvy.txt
Parse2DB sitesurvey -f /var/ssvy.txt -d
rm /var/ssvy.txt
exit 0
