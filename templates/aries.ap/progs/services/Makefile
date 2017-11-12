# vi: set sw=4 ts=4:
#
# Makefile for services of Aries.
#

-include ../path.mk
-include ../arch.mk
-include $(TOPDIR)/.config
-include $(TOPDIR)/Vars.mk

###########################################################################

MYNAME	:= "AriesAP/services"
SERVICES:= LOGD HTTP HTTP.BRIDGE-1 HTTP.BRIDGE-2 HTTP.BRIDGE-3 BRIDGE INET.BRIDGE-1 INET.BRIDGE-2 \
		   INET.BRIDGE-3 INFSVCS.BRIDGE-1 INFSVCS.BRIDGE-2 INFSVCS.BRIDGE-3 LOGD \
		   NAMERESOLV.BRIDGE-1 NAMERESOLV.BRIDGE-2 NAMERESOLV.BRIDGE-3 DEVICE.ACCOUNT DEVICE.TIME MACCTRL \
		   MULTICAST LLD2 UPNP.BRIDGE-1 UPNP.BRIDGE-2 
DIRS	:= HTTP LOG INET INFSVCS NAMERESOLV

ifeq ($(ELBOX_PROGS_PRIV_NAMERESOLV),y)
	SERVICES += NAMERESOLV
endif
###########################################################################

all:

rootfs:
	@echo -e "\033[32mCreating rootfs for $(MYNAME) ...\033[0m"
	$(Q)[ -d $(TARGET)/etc/services ] || mkdir -p $(TARGET)/etc/services
	$(Q)[ -d $(TARGET)/etc/config ] || mkdir -p $(TARGET)/etc/config

install:
	@echo -e "\033[32mInstalling $(MYNAME) ...\033[0m"
	$(Q)for i in $(SERVICES); do cp $$i.php $(TARGET)/etc/services/.; done
	$(Q)for i in $(DIRS); do \
	if [ -f $$i/Makefile ]; then \
		$(MYMAKE) -C $$i install; \
	else \
		[ -d $(TARGET)/etc/services/$$i ] || mkdir -p $(TARGET)/etc/services/$$i ; \
		cp $$i/*.php $(TARGET)/etc/services/$$i/.; \
	fi \
	done
ifeq ($(ELBOX_ARIES_USE_DANIELS_CONE_NAT),y)
	$(Q)echo "Daniel's NAT" > $(TARGET)/etc/config/nat
endif
ifeq ($(ELBOX_PROGS_PRIV_BWC),y)
	$(Q)echo "BWC" > $(TARGET)/etc/config/bwc
endif
clean:

.PHONY: all rootfs install clean
