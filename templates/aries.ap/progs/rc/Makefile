# vi: set sw=4 ts=4:
#
# Makefile for RC scripts of Aries.
#

-include ../path.mk
-include ../arch.mk
-include $(TOPDIR)/.config
-include $(TOPDIR)/Vars.mk

###########################################################################

MYNAME	:= "Aries/rc"
INIT	:= S10init.sh S14devpts.sh S19init.sh S20init.sh S45gpiod.sh phpsh
INIT0	:= S40event.br.sh S41inf.br.sh 

###########################################################################

ifeq ($(ELBOX_ARIES_USE_UDEV),y)
INIT	+= S13usb.sh S15udev.sh
endif

ifeq ($(ELBOX_USE_IPV6),y)
INIT	+= S16ipv6.sh
endif

###########################################################################

all:

rootfs:
	@echo -e "\033[32mCreating rootfs for $(MYNAME) ...\033[0m"
	$(Q)[ -d $(TARGET)/etc/init.d ] || mkdir -p $(TARGET)/etc/init.d
	$(Q)[ -d $(TARGET)/etc/init0.d ] || mkdir -p $(TARGET)/etc/init0.d
	$(Q)[ -d $(TARGET)/usr/sbin ] || mkdir -p $(TARGET)/usr/sbin
	$(Q)cp --remove-destination rcS	$(TARGET)/etc/init.d/rcS
	$(Q)cp --remove-destination rcS0 $(TARGET)/etc/init0.d/rcS
	$(Q)cp --remove-destination phpsh $(TARGET)/usr/sbin/.
	$(Q)ln -sf /var/etc/iproute2 $(TARGET)/etc/iproute2

install:
	@echo -e "\033[32mInstalling $(MYNAME) ...\033[0m"
	$(Q)for i in $(INIT); do \
		cp --remove-destination init.$$i $(TARGET)/etc/init.d/$$i; \
		chmod +x $(TARGET)/etc/init.d/$$i; \
		done
	$(Q)for i in $(INIT0); do \
		cp --remove-destination init0.$$i $(TARGET)/etc/init0.d/$$i; \
		chmod +x $(TARGET)/etc/init0.d/$$i; \
		done

clean:

.PHONY: all rootfs install clean
