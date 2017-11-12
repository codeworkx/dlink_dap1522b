#!/bin/sh
iwpriv ra0 get_site_survey > /var/ssvy.txt
Parse2DB sitesurvey -f /var/ssvy.txt -s /etc/scripts/sitesurveyhlper.sh
rm /var/ssvy.txt
exit 0
