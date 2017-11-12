#############################################################################
# Makefile for WAP-ND15 of Aries
#############################################################################

MYNAME	:= "WAP-ND15/Aries"
MYMAKE	:= make V=$(V) DEBUG=$(DEBUG)
APPS	:= gpiod ubcfg ated rtl8367 reg parse2db veth alphawifi

# build board dependent programs
apps:
	$(Q)$(MYMAKE) -C template.aries

# setup board dependent program configuration
prepare:
	@echo -e "\033[32mPreparing configuration for $(MYNAME) ...\033[0m"

# build board dependent rootfs
rootfs:
	@echo -e "\033[32mCreate rootfs for $(MYNAME) ...\033[0m"
	$(Q)[ -d $(TARGET)/etc/config ] || mkdir -p $(TARGET)/etc/config
	$(Q)[ -d $(TARGET)/usr/bin ] || mkdir -p $(TARGET)/usr/bin
	$(Q)[ -d $(TARGET)/usr/sbin ] || mkdir -p $(TARGET)/usr/sbin
	$(Q)echo "/dev/mtdblock/3" >> $(TARGET)/etc/config/devconf
	$(Q)echo "/dev/mtdblock/4" >> $(TARGET)/etc/config/devdata
ifeq ($(strip $(ELBOX_CREATE_LANGUAGE_PACK)),y)
	$(Q)echo "/dev/mtdblock/5" >> $(TARGET)/etc/config/langpack
endif
	$(Q)echo "#!/bin/sh" >> $(TARGET)/bin/reboot
	$(Q)echo "echo 1 > /proc/system_reset" >> $(TARGET)/bin/reboot
	$(Q)chmod +x $(TARGET)/bin/reboot
	$(Q)$(MYMAKE) -C template.aries rootfs

# install board dependent programs
install:
	@echo -e "\033[32mInstalling applets for $(MYNAME) ...\033[0m"
	$(Q)for i in $(APPS); do $(MYMAKE) -C apps/$$i install; done
	$(Q)$(MYMAKE) -C template.aries install

# clean board dependent programs
clean:
	@echo -e "\033[32mCleaning applets for $(MYNAME) ...\033[0m"
	$(Q)$(MYMAKE) -C template.aries clean

.PHONY: apps install clean prepare rootfs

#####################################################################
