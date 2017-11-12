#
# Makefile for logd
#
ifneq ($(strip $(I386)),y)
-include ../arch.mk
endif
-include ../../path.mk

MYNAME	:= logd

###########################################################################
# source for logd
APPLET		:= logd
LOCAL_SRCS	:= logd.c
CMLIB_SRCS	:= dtrace.c sloop.c mem_helper.c libxmldbc.c xstream.c strobj.c

OTHER_TARGETS	:= logger klogd

###########################################################################

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

###########################################################################

-include $(TOPDIR)/Rules.mk

logger: logger.o
	@echo -e "\033[32mbuiling logger\033[0m"
	$(Q)$(CC) $(LDFLAGS) -o $@ $<

klogd: klogd.o
	@echo -e "\033[32mbuilding klogd\033[0m"
	$(Q)$(CC) $(LDFLAGS) -o $@ $< dtrace.o

logger.o: logger.c
	$(Q)$(CC) -I$(COMINC) $(CFLAGS) -c -o $@ $<

klogd.o: klogd.c
	$(Q)$(CC) -I$(COMINC) $(CFLAGS) -c -o $@ $<

clean: clean_objs
	$(Q)rm -f *.o logger klogd

install:
	@echo -e "\033[32m\Installing $(MYNAME) ...\033[0m"
	$(Q)$(STRIP) logd logger
	$(Q)cp logd		$(TARGET)/usr/sbin/.
	$(Q)cp logger	$(TARGET)/usr/sbin/.
	$(Q)cp klogd	$(TARGET)/usr/sbin/.

.PHONY: all clean install
