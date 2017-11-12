#
#	Makefile for Netbios
#
include ../../.config
ifneq ($(strip $(I386)),y)
-include ../arch.mk
else
CFLAGS+=-Wall
endif
-include ../../path.mk
ifeq ($(strip $(ELBOX_PROGS_PRIV_NETBIOS_LINK_LOCAL_IP)),y)
	CFLAGS += -DELBOX_PROGS_PRIV_NETBIOS_LINK_LOCAL_IP
endif
ifeq ($(strip $(ELBOX_MODEL_DAP1150)),y)
	CFLAGS += -DELBOX_MODEL_DAP1150
endif
ifeq ($(strip $(ELBOX_PROGS_PRIV_NETBIOS_MULTI_NAME)),y)
	CFLAGS += -DELBOX_PROGS_PRIV_NETBIOS_MULTI_NAME
endif

###########################################################################
# source for Netbios
MYNAME		:= Netbios
APPLET		:= netbios
ifeq ($(strip $(ELBOX_PROGS_PRIV_NETBIOS_ARIES)),y)
LOCAL_SRCS	:= netbios_aries.c #new netbios using the argument for setting without get frm rgdb
CMLIB_SRCS	:= 
else
LOCAL_SRCS	:= netbios.c
CMLIB_SRCS	:= libxmldbc.c
endif
###########################################################################

CFLAGS+=-DMPOOL_HAVE_EXTERNAL_INCREASE_TABLE=y
CFLAGS+=-I$(TOPDIR)/include

###########################################################################
# dtrace debug
ifeq ($(strip $(DEBUG)),y)
DDEBUG:=y
DDEBUG_LEVEL:=0
endif

ifeq ($(strip $(DDEBUG)),y)
CFLAGS+=-DDDEBUG
ifdef DDEBUG_LEVEL
CFLAGS+=-DDBG_DEFAULT=$(DDEBUG_LEVEL)
endif
endif
#############################################################################

-include $(TOPDIR)/Rules.mk

showconfig:
	@echo "APPLET     = "$(APPLET)
	@echo "LOCAL_SRCS = "$(LOCAL_SRCS)
	@echo "CMLIB_SRCS = "$(CMLIB_SRCS)
	@echo "CFLAGS     = "$(CFLAGS)

install:
	@echo -e "\033[32mInstalling $(MYNAME) ...\033[0m"
	$(Q)$(STRIP) $(APPLET)
	@cp $(APPLET) $(TARGET)/usr/sbin

clean: clean_objs

gpl:
	rm -f *.c *.o *.h
	mv Makefile.gpl Makefile

.PHONY: showconfig install clean gpl

#############################################################################
