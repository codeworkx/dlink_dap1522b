#
# Makefile for servd
#

ifeq ($(strip $(I386)),y)
CFLAGS = -Wall -DI386=y
else
-include ../arch.mk
endif
-include ../../path.mk

#############################################################################
# source for servd
MYNAME		:= servd
APPLET		:= servd
LOCAL_SRCS	:= main.c servd.c servc.c service.c event.c pidmon.c
CMLIB_SRCS	:= dtrace.c mem_helper.c sloop.c dirutil.c xstream.c strobj.c susock.c
#############################################################################

CFLAGS += -DMPOOL_HAVE_EXTERNAL_INCREASE_TABLE
CFLAGS += -I$(TOPDIR)/include

#############################################################################
# dtrace debug
ifeq ($(strip $(DEBUG)),y)
DDEBUG:=y
DDEBUG_LEVEL:=0
endif

ifeq ($(strip $(DDEBUG)),y)
CFLAGS += -DDDEBUG
ifdef DDEBUG_LEVEL
CFLAGS += -DDBG_DEFAULT=$(DDEBUG_LEVEL)
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
	$(Q)cp $(APPLET)		$(TARGET)/usr/sbin/.
	$(Q)ln -s servd			$(TARGET)/usr/sbin/service
	$(Q)ln -s servd			$(TARGET)/usr/sbin/event
	$(Q)ln -s servd			$(TARGET)/usr/sbin/pidmon
	$(Q)[ -d $(TARGET)/etc/services ] || mkdir -p $(TARGET)/etc/services
	$(Q)cp svchlper			$(TARGET)/etc/services/.

clean: clean_objs
	@echo -e "\033[32mCleaning $(MYNAME) ...\033[0m"

gpl:
	rm -f *.c *.o *.h .depend
	mv Makefile.gpl Makefile

.PHONY: showconfig install clean gpl

links:
	ln -s servd service
	ln -s servd event
	ln -s servd pidmon

unlinks:
	rm service event pidmon

.PHONY: links unlinks

