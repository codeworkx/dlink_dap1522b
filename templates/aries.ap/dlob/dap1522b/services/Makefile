# vi: set sw=4 ts=4:
#
# Makefile for services of Aries.
#

-include ../../path.mk
-include ../../arch.mk
-include $(TOPDIR)/.config
-include $(TOPDIR)/Vars.mk

###########################################################################

MYNAME	:= "aries/dlob/DAP-1522B/services"
SERVICES:= HTTP MULTICAST DHCPS4.INF DHCPS4.BRIDGE-1 TRAFFICCTRL.BRIDGE-1 LOGD DEVICE.TIME MACCTRL

# subdirectories.
DIRS	:= HTTP INET INFSVCS NAMERESOLV DHCPS TRAFFICCTRL

###########################################################################

all:

rootfs:
	@echo -e "\033[32mCreating rootfs for $(MYNAME) ...\033[0m"
	$(Q)[ -d $(TARGET)/etc/services ] || mkdir -p $(TARGET)/etc/services

install:
	@echo -e "\033[32mInstalling services for $(MYNAME) ...\033[0m"
	$(Q)for i in $(SERVICES); do cp $$i.php $(TARGET)/etc/services/.; done
	    $(Q)for i in $(DIRS); do \
	        if [ ! -d $(TARGET)/etc/service/$$i ]; then \
    	        mkdir -p $(TARGET)/etc/services/$$i; \
	        fi ; \
		      cp $$i/*.php $(TARGET)/etc/services/$$i/.; \
    done

clean:

.PHONY: all rootfs install clean
