#!/bin/sh
#echo [$0] ... > /dev/console
rtlsts=`xmldbc -g /runtime/device/rtlsts`
if [ "$rtlsts" = "1" ]; then
	rmmod rtldrv
	sleep 1
	insmod /lib/modules/rtldrv.ko
	rtlioc qos_init 0
	rtlioc enlan
	service MACCTRL restart
	service TRAFFICCTRL restart
	echo "RTL8367RB is not default"
fi
xmldbc -t "RTL8367RB":5:"sh /etc/scripts/rtl8367rb.sh"
exit 0
