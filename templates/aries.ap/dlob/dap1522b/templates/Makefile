# vi: set sw=4 ts=4:
#
# Makefile for services of Aries.
#

-include ../../path.mk
-include ../../arch.mk
-include $(TOPDIR)/.config
-include $(TOPDIR)/Vars.mk

###########################################################################

MYNAME	:= "aries/dlob/DAP-1522B/templates"
FILES:=

# subdirectories.
DIRS    := hnap

###########################################################################

all:

rootfs:
	@echo -e "\033[32mCreating rootfs for $(MYNAME) ...\033[0m"
	$(Q)[ -d $(TARGET)/etc/templates ] || mkdir -p $(TARGET)/etc/templates

install:
	@echo -e "\033[32mInstalling $(MYNAME) ...\033[0m"
	$(Q)for i in $(FILES); do cp $$i.php $(TARGET)/etc/templates/.; done
	$(Q)for i in $(DIRS); do \
		[ -d $(TARGET)/etc/templates/$$i ] || mkdir -p $(TARGET)/etc/templates/$$i ; \
		cp $$i/*.php $(TARGET)/etc/templates/$$i/.; \
	done

clean:

.PHONY: all rootfs install clean
