#
# Makefile for the Linux networking core.
#

obj-y := sock.o request_sock.o skbuff.o iovec.o datagram.o stream.o scm.o \
	 gen_stats.o gen_estimator.o net_namespace.o

obj-$(CONFIG_SYSCTL) += sysctl_net_core.o

obj-y		     += dev.o ethtool.o dev_mcast.o dst.o netevent.o \
			neighbour.o rtnetlink.o utils.o link_watch.o filter.o

obj-$(CONFIG_XFRM) += flow.o
obj-y += net-sysfs.o
obj-$(CONFIG_NET_PKTGEN) += pktgen.o
obj-$(CONFIG_NET_PPPOE_IPV6_PTHROUGH) += pthrough.o
obj-$(CONFIG_NET_PPPOE_IPV6_PTHROUGH) += pppoe_pthrough.o
obj-$(CONFIG_NET_PPPOE_IPV6_PTHROUGH) += ipv6_pthrough.o
obj-$(CONFIG_NETPOLL) += netpoll.o
obj-$(CONFIG_NET_DMA) += user_dma.o
obj-$(CONFIG_FIB_RULES) += fib_rules.o
obj-$(CONFIG_TRACEPOINTS) += net-traces.o
obj-$(CONFIG_NET_DROP_MONITOR) += drop_monitor.o

ifeq ($(CONFIG_ALPHA_HW_NAT_STATS),y)
EXTRA_CFLAGS += -DALPHA_HW_NAT_STATS
endif
