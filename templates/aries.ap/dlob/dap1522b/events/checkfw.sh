#!/bin/sh
echo "Check FW Now ..."
fwinfo="/tmp/fwinfo.xml"
fwsrv=`xmldbc -g /runtime/device/fwinfosrv`
fwpath=`xmldbc -g /runtime/device/fwinfopath`
model=`xmldbc -g /runtime/device/modelname`
#fwver_major=`xmldbc -g /runtime/device/firmwareversion |cut -d'.' -f1`
#fwver_minor=`xmldbc -g /runtime/device/firmwareversion |cut -d'.' -f2|cut -c1-2`
hwver=`xmldbc -g /runtime/devdata/hwrev |cut -c 1| tr '[a-z]' '[A-Z]' `
global=$hwver"x_Default"
reqstr=$fwpath"?model=$model\_$global"
reqstr="GET $reqstr HTTP/1.1
Accept:*/*
User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)
Host: $srv
Connection: Close

"
rm -f $fwinfo
xmldbc -X /runtime/firmware
tcprequest "$reqstr" "$fwsrv" 80 -f "$fwinfo" -t 5 -s
if [ -f $fwinfo ]; then
#get firmware information
new_major=`grep Major /tmp/fwinfo.xml |sed 's/^[ \t]*//'|sed 's/<Major>//'|sed 's/<\/Major>//'`
new_minor=`grep Minor /tmp/fwinfo.xml |sed 's/^[ \t]*//'|sed 's/<Minor>//'|sed 's/<\/Minor>//'`
xmldbc -s /runtime/firmware/fwversion/Major $new_major
xmldbc -s /runtime/firmware/fwversion/Minor $new_minor
fi
buildver=`cat /etc/config/buildver`
#old_major=`cat /etc/config/buildver|sed 's/.[0-9][0-9]//g'`
#old_minor=`cat /etc/config/buildver|sed 's/.*\.//g'`
old_major=`cat /etc/config/buildver|cut -d'.' -f1`
old_minor=`cat /etc/config/buildver|cut -d'.' -f2|cut -c1-2`
if [ -n $new_major ]; then
	if [ $new_major -gt $old_major -o $new_major -eq $old_major -a $new_minor -gt $old_minor ]; then
	echo "Have new Firmware"
	xmldbc -s /runtime/firmware/havenewfirmware 1
	fi
fi
