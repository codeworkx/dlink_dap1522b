obj-$(CONFIG_RAETH_SDK3510) += raeth.o
raeth-objs := raether.o ra_mac.o mii_mgr.o ra_rfrw.o

#EXTRA_CFLAGS   += -DCONFIG_RAETH_MULTIPLE_RX_RING

ifeq ($(CONFIG_RAETH_QOS),y)
raeth-objs += ra_qos.o
endif

ifeq ($(CONFIG_ETHTOOL),y)
ifeq ($(CONFIG_RAETH_ROUTER),y)
raeth-objs += ra_ethtool.o
else
ifeq ($(CONFIG_RT_3052_ESW),y)
raeth-objs += ra_ethtool.o
else
ifeq ($(CONFIG_RALINK_RT3883),y)
raeth-objs += ra_ethtool.o
endif
endif
endif
endif

ifeq ($(CONFIG_RALINK_RT3052_MP2),y)
raeth-objs += mcast.o
endif

ifeq ($(CONFIG_RT3x52),y)
raeth-objs += mcast.o
raeth-objs += rt3052_access.o
endif

ifeq ($(CONFIG_RTxx50),y)
raeth-objs += mcast.o
raeth-objs += rt3052_access.o
endif

#+++ Bouble: 20110523 
# rt_3052_access.c include system_reset and Soc's switch (vlan) config support.
ifeq ($(CONFIG_RALINK_RT3352),y)
raeth-objs += mcast.o
raeth-objs += rt3052_access.o
endif
#--- Bouble: 20110523 

#+++ Hendry: 20110712
ifeq ($(CONFIG_BRIDGE_ALPHA_MULTICAST_SNOOP),y)
ifeq ($(CONFIG_RT_3052_ESW),y)
raeth-objs += alpha_igmp_snoop.o
endif
endif
#--- Hendry: 20110712

#+++ Hendry: 20110803
ifeq ($(CONFIG_ALPHA_HW_NAT_STATS),y)
EXTRA_CFLAGS   += -DALPHA_HW_NAT_STATS
endif
#--- Hendry: 20110803


ifeq ($(CONFIG_RAETH_NETLINK),y)
raeth-objs += ra_netlink.o
endif

ifeq ($(CONFIG_RAETH_NAPI),y)
EXTRA_CFLAGS   += -DCONFIG_RAETH_NAPI 
endif

ifeq ($(CONFIG_RALINK_BRIDGING_ONLY),y)
EXTRA_CFLAGS   += -DCONFIG_RALINK_BRIDGING_ONLY
endif

ifeq ($(CONFIG_RALINK_ENHANCE),y)
EXTRA_CFLAGS += -DCONFIG_RALINK_ENHANCE
endif

ifeq ($(CONFIG_RAETH_GMAC2),y)
EXTRA_CFLAGS += -DCONFIG_PSEUDO_SUPPORT
endif

ifeq ($(CONFIG_RAETH_JUMBOFRAME),y)
EXTRA_CFLAGS += -DCONFIG_RAETH_JUMBOFRAME
endif

ifeq ($(CONFIG_RT2860V2_AP_MEMORY_OPTIMIZATION),y)
EXTRA_CFLAGS   += -DMEMORY_OPTIMIZATION
endif

ifeq ($(CONFIG_RA_NETWORK_WORKQUEUE_BH),y)
EXTRA_CFLAGS   += -DWORKQUEUE_BH
endif
