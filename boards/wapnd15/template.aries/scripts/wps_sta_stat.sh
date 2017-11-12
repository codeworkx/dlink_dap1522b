#!/bin/sh
xmldbc -P /etc/scripts/wps/wps_sta_stat.php -V STAT=$1  > /dev/null
exit 0 
