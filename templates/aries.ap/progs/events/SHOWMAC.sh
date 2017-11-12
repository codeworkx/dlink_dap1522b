#!/bin/sh
brctl showmacs br0 > /var/showmac.txt
Parse2DB bridgemacs -f /var/showmac.txt -s /etc/scripts/bridgemacshlper.sh
rm /var/showmac.txt
xmldbc -s /runtime/phyinf_tmpnode/showmac/status "finish"
echo "/etc/events/SHOWMAC.sh is completed."
exit 0
