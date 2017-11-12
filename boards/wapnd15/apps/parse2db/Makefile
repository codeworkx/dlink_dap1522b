
-include ../../arch.mk
-include ../../path.mk

APPLET      := Parse2DB
LOCAL_SRCS  := main.c parse_ssvy.c parse_macs.c
CMLIB_SRCS  := libxmldbc.c dtrace.c
all: $(APPLET)

ifeq ($(strip $(DDEBUG)),y)
EXTRA_CFLAGS += -DDBG
endif

CFLAGS += -I$(KERNELDIR)/drivers/net/rt_rdm -I$(KERNELDIR)/include


-include $(TOPDIR)/Rules.mk

all: $(APPLET)

install: 
	@echo -e "\033[32mInstalling $(APPLET) tools for WAP-ND15 ... \033[0m"
	$(Q)cp $(APPLET) $(TARGET)/usr/sbin/.
	

clean:
	@echo -e "\033[32mCleaning $(APPLET) tools for WAP-ND15 ... \033[0m"
	$(Q)rm -f $(APPLET) *.o .depend

.PHONY: all install clean
