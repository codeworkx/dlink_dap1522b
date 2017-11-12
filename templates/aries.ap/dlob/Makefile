# vi: set sw=4 ts=4:

-include path.mk
-include arch.mk
-include $(TOPDIR)/.config
-include $(TOPDIR)/Vars.mk

#####################################################################
# targets for main Makefile

MYNAME	:= "Aries/Dlob"
MYMAKE	:= make V=$(V) DEBUG=$(DEBUG)
SUBDIRS	:= defnodes htdocs scripts services

ifeq ($(strip $(ELBOX_CREATE_LANGUAGE_PACK)),y)
LTARGET	:= $(TOPDIR)/language
else
LTARGET	:= $(TARGET)/www/locale
endif


#####################################################################
SUBDIRS	+= $(ELBOX_MODEL_NAME)

all:
	@echo -e "\033[32mBuilding applets for $(MYNAME) ...\033[0m"
	$(Q)for i in $(SUBDIRS); do $(MYMAKE) -C $$i; done

prepare:
	@echo -e "\033[32mPreparing configuration for $(MYNAME) ...\033[0m"

rootfs:
	@echo -e "\033[32mCreate rootfs for $(MYNAME) ...\033[0m"
	$(Q)[ -d $(LTARGET) ] || mkdir -p $(LTARGET)
	$(Q)for i in $(SUBDIRS); do $(MYMAKE) -C $$i rootfs; done

install:
	@echo -e "\033[32mInstalling $(MYNAME) ...\033[0m"
	$(Q)for i in $(SUBDIRS); do $(MYMAKE) -C $$i install; done
	$(Q)$(MYMAKE) inst_language

clean:
	@echo -e "\033[32mCleaning board related programs for $(MYNAME) ...\033[0m"
	$(Q)for i in $(SUBDIRS); do $(MYMAKE) -C $$i clean; done

.PHONY: all install clean prepare rootfs

#####################################################################

inst_language:
	$(Q)for i in $(LANGUAGE); do \
		echo -e "\033[32mInstall language $$i ...\033[0m" ; \
		cp -r --remove-destination locale/$$i $(LTARGET)/. ; \
		if [ -d $(ELBOX_MODEL_NAME)/locale/$$i ]; then \
			cp -r --remove-destination $(ELBOX_MODEL_NAME)/locale/$$i $(LANG_TARGET)/. ; \
		fi; \
		echo $$i > $(LTARGET)/$$i/langcode ; \
		done
	$(Q)find $(TARGET) -name .svn -type d | xargs rm -rf
	$(Q)find $(TARGET) -name CVS -type d | xargs rm -rf

.PHONY: inst_language
