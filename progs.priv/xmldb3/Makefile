# Makefile for xmldb version 3

ifneq ($(strip $(I386)),y)
-include ../arch.mk
else
CFLAGS = -Wall
endif
-include ../../path.mk

MYNAME	:= xmldb version 3

#############################################################################
# sources for xmldb
ifeq ($(strip $(XMLNODE)),y)
CFLAGS += -DXMLNODE_STANDALONE -Wall
APPLET     := xmlnode
LOCAL_SRCS := xmlnode.c
CMLIB_SRCS := sloop.c mem_helper.c dirutil.c dtrace.c libxmldbc.c xstream.c strobj.c
else
APPLET     := xmldb
LOCAL_SRCS := main.c xmldb.c xmldbc.c xmlnode.c ephp.c \
				ephp_bifunc.c ephp_bif_ipv4.c ephp_bif_ipv6.c ephp_bif_string.c
CMLIB_SRCS := sloop.c mem_helper.c dirutil.c dtrace.c libxmldbc.c xstream.c strobj.c md5.c sha1.c
endif
#
#############################################################################

CFLAGS += -DMPOOL_HAVE_EXTERNAL_INCREASE_TABLE

ifeq ($(strip $(DEBUG)),y)
DDEBUG:=y
DDEBUG_LEVEL:=0
endif

#############################################################################
# dtrace debug
ifeq ($(strip $(DDEBUG)),y)
CFLAGS += -DDDEBUG
ifdef DDEBUG_LEVEL
CFLAGS += -DDBG_DEFAULT=$(DDEBUG_LEVEL)
endif
endif

#############################################################################
ifeq ($(strip $(DEBUG_SLOOP)),y)
CFLAGS += -DDEBUG_SLOOP
endif
ifeq ($(strip $(DEBUG_LIBXMLDBC)),y)
CFLAGS += -DDEBUG_LIBXMLDBC
endif
ifeq ($(strip $(DEBUG_XMLNODE)),y)
CFLAGS += -DDEBUG_XMLNODE
endif
ifeq ($(strip $(DEBUG_XMLDB)),y)
CFLAGS += -DDEBUG_XMLDB
endif
ifeq ($(strip $(DEBUG_EPHP)),y)
CFLAGS += -DDEBUG_EPHP
endif
ifeq ($(strip $(DEBUG_EPHP_SBLIST)),y)
CFLAGS += -DDEBUG_EPHP -DDEBUG_EPHP_SBLIST
endif

#############################################################################

-include $(TOPDIR)/Rules.mk

showconfig:
	@echo APPLET     = $(APPLET)
	@echo LOCAL_SRCS = $(LOCAL_SRCS)
	@echo CMLIB_SRCS = $(CMLIB_SRCS)

clean: clean_objs
	@echo -e "\033[32mCleaning $(MYNAME) ...\033[0m"
	$(Q)rm -f rgdb xmldbc

install:
	@echo -e "\033[32mInstalling $(MYNAME) ...\033[0m"
	$(Q)[ -d $(TARGET)/usr/sbin ] || mkdir -p $(TARGET)/usr/sbin
	$(Q)[ -z "$(STRIP)" ] || $(STRIP) $(APPLET)
	$(Q)cp $(APPLET) $(TARGET)/usr/sbin
	$(Q)ln -sf ./xmldb $(TARGET)/usr/sbin/xmldbc
ifeq ($(strip $(ELBOX_CREATE_RGDB_LINK_TO_XMLDBC)),y)
	$(Q)ln -sf ./xmldb $(TARGET)/usr/sbin/rgdb
endif

gpl:
	rm -f *.c *.o *.h .depend
	mv Makefile.gpl Makefile

.PHONY: showconfig all clean install gpl

#############################################################################
# For Debugging ...

links:
	ln -s ./xmldb xmldbc
	ln -s ./xmldb rgdb

.PHONY: link

#############################################################################
