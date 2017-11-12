#!/bin/sh
event STATUS.CRITICAL
echo "Reboot in 3 seconds ..."
sleep 1
echo "Reboot in 2 seconds ..."
sleep 1
echo "Reboot in 1 seconds ..."
sleep 1
echo "Rebooting ..."

if [ "`xmldbc -g /runtime/device/layout`" != "router" ]; then
	reboot	
else
	event WAN-1.DOWN add reboot
	service INET.WAN-2 stop
	service INET.WAN-1 stop
	xmldbc -t "reboot:10:reboot"
fi
