#!/bin/sh
if test -f "/proc/net/if_inet6" ;
then
	echo 2 > /proc/sys/net/ipv6/conf/eth0/accept_dad
	echo 1 > /proc/sys/net/ipv6/conf/eth0/disable_ipv6
fi
insmod /lib/modules/rtldrv.ko
#Builder
#If RTL8367RB resets itself because of ETD or others this timer can detect and recover it.
xmldbc -t "RTL8367RB":5:"sh /etc/scripts/rtl8367rb.sh"
MACADDR=`devdata get -e lanmac`
[ "$MACADDR" != "" ] && ip link set eth0 addr $MACADDR
ip link set eth0 up

#sam_pan 
insmod /lib/modules/ifresetcnt.ko

# bouble - 20101209 - enable switch port base qos
# Enable QoS and use WFQ by default
# rtlioc qos_init 0	<< --- ..WFQ
# rtlioc qos_init 1	<< --- ..SPQ
rtlioc qos_init 0
# set each switch port's priority here, those priority values will be used when switch driver receive packets
# from switch. Driver will inspect packet's incoming switch port via realtek's proprietary tag, 
# then set this packet's priority. When this packet send out through wireless interface,
# wireless driver will do QoS base on packet's priority. 
#do not init on here, move to qos init
#echo "0 0" > /proc/rt3883/sw_port_wifi_pri #no used
#echo "1 6" > /proc/rt3883/sw_port_wifi_pri #LAN port4(uplink)
#echo "2 1" > /proc/rt3883/sw_port_wifi_pri #LAN port3
#echo "3 3" > /proc/rt3883/sw_port_wifi_pri #LAN port2
#echo "4 5" > /proc/rt3883/sw_port_wifi_pri #LAN port1
#echo "5 0" > /proc/rt3883/sw_port_wifi_pri #no used
#echo "6 0" > /proc/rt3883/sw_port_wifi_pri #no used
#echo "7 0" > /proc/rt3883/sw_port_wifi_pri #no used
#enable this function,dap1522B need disable this function as defaunt, just only qos by lan port is enabled
#echo 0 > /proc/rt3883/sw_port_wifi_pri_enable
#xmldbc -s /device/qos_prio/enable 1
