#!/bin/sh
mount -t tmpfs tmpfs /dev
mount -t sysfs sysfs /sys 
mount -t usbfs usbfs /proc/bus/usb
