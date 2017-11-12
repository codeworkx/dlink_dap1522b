#!/bin/sh
#echo [$0] [$1] [$2] [$3] [$4] > /dev/console
xmldbc -P /etc/scripts/mac/showmac.php -V ACTION=$1 -V INDEX=$2 -V PORT=$3 -V MACADDR=$4 > /dev/null
exit 0
