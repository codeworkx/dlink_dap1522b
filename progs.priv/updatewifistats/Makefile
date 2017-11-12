#
# Makefile for updatewifistats.
#

-include ../arch.mk
-include ../../path.mk
-include ../../.config

#############################################################
#DEBUG=y
#DEBUG_CLIENT_LIST=y

ifeq ($(strip $(DEBUG_CLIENT_LIST)),y)
CFLAGS+=-DDEBUG_CLIENT_LIST
endif

ifeq ($(strip $(DEBUG)),y)
DDEBUG := y
DDEBUG_LEVEL := 0
endif
ifeq ($(strip $(DDEBUG)),y)
CFLAGS+=-DDDEBUG
ifdef DDEBUG_LEVEL
CFLAGS+=-DDBG_DEFAULT=$(DDEBUG_LEVEL)
endif
endif

APPLET     := updatewifistats
LOCAL_SRCS := updatewifistats.c 
CMLIB_SRCS := libxmldbc.c dtrace.c
ifeq ($(strip $(ELBOX_PROGS_PRIV_MADWIFI_AR724X)),y)
WIRELESS_TOOLS_PATH=$(TOPDIR)/progs.priv/madwifi_ar724x/_build/apps/wireless_tools.28
else
WIRELESS_TOOLS_PATH=$(TOPDIR)/progs.gpl/wireless_tools
endif

ifneq ($(strip $(ELBOX_PROGS_PRIV_UPDATEWIFISTATS_BROADCOM)),y)
CFLAGS+=-I$(WIRELESS_TOOLS_PATH)
LDFLAGS:=-L$(WIRELESS_TOOLS_PATH) -liw
endif


ifeq ($(strip $(ELBOX_PROGS_PRIV_UPDATEWIFISTATS_RALINK_RT2800)),y)
LOCAL_SRCS += rt2800.c
endif

ifeq ($(strip $(ELBOX_PROGS_PRIV_UPDATEWIFISTATS_ATHEROS_AT7240)),y)
LOCAL_SRCS += madwifi.c
endif

ifeq ($(strip $(ELBOX_PROGS_PRIV_UPDATEWIFISTATS_BROADCOM)),y)
LOCAL_SRCS += bcmwifi.c
endif

ifeq ($(strip $(ELBOX_PROGS_PRIV_UPDATEWIFISTATS_ATHEROS_AT7240)),y)
MADWIFI_PATH:=$(TOPDIR)/progs.priv/madwifi_ar724x/_build/wlan/linux
CFLAGS+=-D_BYTE_ORDER=_BIG_ENDIAN
endif


ifeq ($(strip $(ELBOX_PROGS_PRIV_UPDATEWIFISTATS_ATHEROS_AT7240)),y)
CFLAGS+=-I$(WIRELESS_TOOLS_PATH) -I$(MADWIFI_PATH)
else
CFLAGS+=-I$(WIRELESS_TOOLS_PATH)
endif


ifeq ($(strip $(ELBOX_PROGS_PRIV_UPDATEWIFISTATS_RALINK_SDK3300_V2XXX)),y)
CFLAGS+= -DRTMP_RBUS_SUPPORT
endif

ifeq ($(strip $(ELBOX_PROGS_PRIV_UPDATEWIFISTATS_BROADCOM)),y)
# Broadcom wifi utilities need share and nvram 2 shared lib. 
# follow generic compile flow, it will be built until make -C progs.boards/...
# It is too late.
# Those 2 shared libs shall be build here first.
PREBUILD := bcm_share_lib

ifdef ELBOX_BSP_KERNEL_PATH
SDK_ROOT:=$(TOPDIR)/kernels/$(strip $(subst ",,$(ELBOX_BSP_KERNEL_PATH)))/../..
BROADCOM_SHARED_LIB:=$(SDK_ROOT)/router/shared
BROADCOM_NVRAM_LIB:=$(SDK_ROOT)/router/nvram
BROADCOM_LIB_HEADER:=$(SDK_ROOT)/include
BROADCOM_WL_LIB_HEADER:=$(SDK_ROOT)/wl/bcmwifi/include
SRCBASE := $(SDK_ROOT)
TOP=$(SRCBASE)/router
WLAN_ComponentsInUse := bcmwifi clm
include $(SRCBASE)/makefiles/WLAN_Common.mk
CFLAGS  += -fPIC -I$(BROADCOM_WL_LIB_HEADER)
export SRCBASE TOP CFLAGS
else
BROADCOM_SHARED_LIB:=$(KERNELDIR)/broadcom_rc/router/shared
BROADCOM_LIB_HEADER:=$(KERNELDIR)/broadcom_rc/include
BROADCOM_NVRAM_LIB:=$(KERNELDIR)/broadcom_rc/router/nvram
export SRCBASE := $(KERNELDIR)/broadcom_rc
endif

CFLAGS+=-I$(BROADCOM_LIB_HEADER) -I$(BROADCOM_SHARED_LIB) 
LDFLAGS:=-L$(BROADCOM_SHARED_LIB) -lshared -L$(BROADCOM_NVRAM_LIB) -lnvram
endif

# For broadcom wifi driver, it will not use libiw.a
ifeq ($(strip $(ELBOX_PROGS_PRIV_UPDATEWIFISTATS_BROADCOM)),y)
-include $(TOPDIR)/Rules.mk

bcm_share_lib:
	make -C $(BROADCOM_SHARED_LIB)
	make -C $(BROADCOM_NVRAM_LIB)

else
ifeq ($(wildcard $(WIRELESS_TOOLS_PATH)/libiw.a),)
all:
	@echo -e "\033[31updatewifistats:\033[0m"
	@echo -e "\033[31mlibrary iw is not available, please build wireless tools first!\033[0m"
	$(Q)exit 9
else
-include $(TOPDIR)/Rules.mk
endif
endif

install:
	@echo -e "\033[32mInstalling updatewifistats ...\033[0m"
	$(Q)[ -d $(TARGET)/usr/sbin ] || mkdir -p $(TARGET)/usr/sbin
	$(Q)$(STRIP) $(APPLET)
	$(Q)cp -f $(APPLET) $(TARGET)/usr/sbin/.

clean:
	$(Q)rm -f *.o .depend $(APPLET) *~
ifeq ($(strip $(ELBOX_PROGS_PRIV_UPDATEWIFISTATS_BROADCOM)),y)
	make -C $(BROADCOM_SHARED_LIB) clean
	make -C $(BROADCOM_NVRAM_LIB) clean
endif

gpl:
	rm -f *.c *.o *.h .depend
	mv Makefile.gpl Makefile

.PHONY: all install clean gpl

showconfig:
	@echo cflags=$(CFLAGS)
