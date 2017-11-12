# vi: set sw=4 ts=4:

-include ../path.mk
-include ../arch.mk
-include $(TOPDIR)/.config
-include $(TOPDIR)/Vars.mk

###########################################################################

MYNAME	:= "Aries/scripts"

SCRIPTFILES	:=	dbload.sh dbsave.sh dlcfg_hlper.sh IPV4.INET.php setdate.sh settime.sh prefwupdate.sh ping.sh killpid.sh delpathbytarget.sh delpathbytarget.php
DIRS		:=	libs upnp
SCRIPTDIRS	:=

ifeq ($(ELBOX_USE_IPV6),y)
SCRIPTFILES +=  IPV6.INET.php RA-WAIT.php
endif

###########################################################################
INSSH	:= $(TARGET)/etc/scripts/iptables_insmod.sh
RMSH	:= $(TARGET)/etc/scripts/iptables_rmmod.sh
###########################################################################

all:

rootfs:
	@echo -e "\033[32mCreating rootfs for $(MYNAME) ...\033[0m"
	$(Q)[ -d $(TARGET)/etc/scripts ] || mkdir -p $(TARGET)/etc/scripts

install:
	@echo -e "\033[32mInstalling $(MYNAME) ...\033[0m"
	$(Q)for i in $(SCRIPTFILES); do \
			cp $$i --remove-destination $(TARGET)/etc/scripts/$$i; \
			chmod +x $(TARGET)/etc/scripts/$$i; \
		done
	$(Q)for i in $(DIRS); do \
		[ -d $(TARGET)/etc/scripts/$$i ] || mkdir -p $(TARGET)/etc/scripts/$$i ; \
		cp --remove-destination $$i/* $(TARGET)/etc/scripts/$$i/. ; \
	done
	$(Q)for i in $(SCRIPTDIRS); do \
		[ -d $(TARGET)/etc/scripts/$$i ] || mkdir -p $(TARGET)/etc/scripts/$$i ; \
		cp --remove-destination $$i/* $(TARGET)/etc/scripts/$$i/. ; \
		chmod +x $(TARGET)/etc/scripts/$$i/*.sh ; \
	done 
clean:

.PHONY: all rootfs install clean
