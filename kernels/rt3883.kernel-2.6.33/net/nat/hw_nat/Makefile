obj-$(CONFIG_RA_HW_NAT) += hw_nat.o
hw_nat-objs := ra_nat.o foe_fdb.o util.o acl_policy.o ac_policy.o mtr_policy.o
hw_nat-objs += ac_ioctl.o hwnat_ioctl.o acl_ioctl.o mtr_ioctl.o
#hendry
ifeq ($(CONFIG_ALPHA_HW_NAT_STATS),y)
hw_nat-objs += alpha_stats.o
EXTRA_CFLAGS += -DALPHA_HW_NAT_STATS
endif

ifeq ($(CONFIG_RALINK_RT3052),y)
hw_nat-objs += ra_rfrw.o
endif

