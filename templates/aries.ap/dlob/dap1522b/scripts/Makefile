# vi: set sw=4 ts=4:

-include ../../path.mk
-include ../../arch.mk
-include $(TOPDIR)/.config
-include $(TOPDIR)/Vars.mk

###########################################################################

MYNAME	:= "aries.ap/dlob/dap1522b/scripts"

SCRIPTFILES	:= ping.sh
DIRS		:=
SCRIPTDIRS	:=

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