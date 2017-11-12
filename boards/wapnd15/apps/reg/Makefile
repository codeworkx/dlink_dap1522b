
-include ../../arch.mk
-include ../../path.mk

APPLET:=reg

all: $(APPLET)

ifeq ($(strip $(DDEBUG)),y)
EXTRA_CFLAGS += -DDBG
endif

CFLAGS += -I$(KERNELDIR)/drivers/net/rt_rdm -I$(KERNELDIR)/include
#CFLAGS += -I$(ROOTDIR)/$(LINUXDIR)/drivers/net/rt_rdm -I$(ROOTDIR)/$(LINUXDIR)/include

$(APPLET): reg.c mmap.c
	@echo -e "\033[32mBuilding REG tools for RT2880 ... \033[0m"
	$(Q)$(CC) $(CFLAGS) $(EXTRA_CFLAGS) $(LDFLAGS) -o $@ $@.c $(LDLIBS)
	$(Q)$(STRIP) --remove-section=.note --remove-section=.comment $@

install: 
	@echo -e "\033[32mInstalling REG tools for RT2880 ... \033[0m"
	$(Q)cp $(APPLET) $(TARGET)/usr/sbin/.

clean:
	@echo -e "\033[32mCleaning REG tools for RT2880 ... \033[0m"
	$(Q)rm -f $(APPLET) *.o

.PHONY: all install clean
