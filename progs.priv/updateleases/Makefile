-include ../arch.mk
-include ../../path.mk
-include ../../.config
#####################################################################
# source for updateleases
MYNAME      := UPDATELEASES
APPLET      := updateleases
LOCAL_SRCS  := updateleases.c
CMLIB_SRCS  := dtrace.c libxmldbc.c
#####################################################################
# Variables
CGIS :=
CMDS :=
DIRS := $(TARGET)/usr/sbin
CFLAGS += -I$(TOPDIR)/include
#####################################################################
# dtrace debug
ifeq ($(strip $(DEBUG)),y)
DDEBUG := y
DDEBUG_LEVEL := 0
endif
ifeq ($(strip $(DDEBUG)),y)
CFLAGS  += -DDDEBUG
ifdef DDEBUG_LEVEL
CFLAGS  += -DDBG_DEFAULT=$(DDEBUG_LEVEL)
endif
endif

ifeq ($(strip $(ELBOX_TEMPLATE_ARIES)),y)
CFLAGS  += -DIGNORE_WIDECHAR_HOSTNAME
endif
ifeq ($(strip $(ELBOX_TEMPLATE_ARIES_BRIDGE_AP)),y)
CFLAGS  += -DIGNORE_WIDECHAR_HOSTNAME
endif
#####################################################################
-include $(TOPDIR)/Rules.mk
#####################################################################

showconfig:
	@echo APPLET = $(APPLET)
	@echo LOCAL_SRCS = $(LOCAL_SRCS)
	@echo CMLIB_SRCS = $(CMLIB_SRCS)

clean: clean_objs
	@echo -e "\033[32mCleaning $(MYNAME) ...\033[0m"
	$(Q)rm -f libxmldbc updateleases

install:
	@echo -e "\033[32mInstalling $(MYNAME) ...\033[0m"
	$(Q)[ -d $(TARGET)/usr/sbin ] || mkdir -p $(TARGET)/usr/sbin
	$(Q)[ -z "$(STRIP)" ] || $(STRIP) $(APPLET)
	$(Q)cp $(APPLET) $(TARGET)/usr/sbin

gpl:
	rm -f *.c *.o *.h .depend
	mv Makefile.gpl Makefile

.PHONY: showconfig all clean install gpl
