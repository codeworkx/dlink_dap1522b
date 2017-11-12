
-include ../../arch.mk
-include ../../path.mk

MYNAME := DAP-1522B
APPLET := alphawifi
WIFI_DRIVER_INC := $(TOPDIR)/progs.priv/ralink_driver/3662_smart_antenna/rt2860v2/include
CFLAGS += -I$(WIFI_DRIVER_INC)

all: $(APPLET)

$(APPLET): alphawifi.c alphawifi.h
	@echo -e "\033[32mBuilding alphawifi tools for $(MYNAME) ... \033[0m"
	$(Q)$(CC) $(CFLAGS) $(EXTRA_CFLAGS) $(LDFLAGS) -o $@ $@.c $(LDLIBS)
	$(Q)$(STRIP) --remove-section=.note --remove-section=.comment $@

install: 
	@echo -e "\033[32mInstalling alphawifi tools for $(MYNAME) ... \033[0m"
	$(Q)cp $(APPLET) $(TARGET)/usr/sbin/.

clean:
	@echo -e "\033[32mCleaning alphawifi tools for $(MYNAME) ... \033[0m"
	$(Q)rm -f $(APPLET) *.o

.PHONY: all install clean
