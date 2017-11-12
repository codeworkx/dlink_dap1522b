#
# gpiod for WRG-ND06
#

include ../../../../path.mk
include ../../../../arch.mk
include $(TOPDIR)/.config

#####################################################################
# source files

MYNAME		:= GPIOD for Aries

APPLET      := gpiod
LOCAL_SRCS  := gpiod.c gpio.c
CMLIB_SRCS  := sloop.c dtrace.c unixsock.c libxmldbc.c
CPLUS_SRCS  :=

OTHER_TARGETS:=

CFLAGS += -DSLOOP_USE_GETTIMEOFDAY
CFLAGS += -I$(KERNELDIR)/drivers/char -I$(KERNELDIR)/include  -I$(KERNELDIR)/arch/mips/include -I$(TOPDIR)/include

#####################################################################
# flags to turn on debug mode

ifeq ($(strip $(DEBUG)),y)
DDEBUG:=y
DDEBUG_LEVEL:=0
endif

ifeq ($(strip $(DDEBUG)),y)
CFLAGS += -DDDEBUG
#CFLAGS += -DDEBUG_SLOOP=1
ifdef DDEBUG_LEVEL
CFLAGS += -DDBG_DEFAULT=$(DDEBUG_LEVEL)
endif
endif

#####################################################################

-include $(TOPDIR)/Rules.mk

all: $(APPLET)

install:
	@echo -e "\033[32mInstalling $(MYNAME) ...\033[0m"
	$(Q)cp gpiod $(TARGET)/usr/sbin/.

clean: 
	$(Q)rm -f gpiod *.o .depend

.PHONY: all install clean
#####################################################################

