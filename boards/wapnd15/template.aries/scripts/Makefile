# vi: set sw=4 ts=4:
###########################################################################
-include ../../path.mk
-include ../../arch.mk
include $(TOPDIR)/.config
###########################################################################

MYNAME	:= "WAP-ND15/ARIES/SCRIPTS"
FILES	= wifirnodes.php setswitch.sh delpathbytarget.php upwifistatshlper.sh \
		  resetstats.sh	wpsevents.php wps.sh wps_sta.sh sitesurveyhlper.sh wps_sta_stat.sh \
          bridgemacshlper.sh wifirnodes_sta.php neaps.sh neaps_run.php neaps_config.php \
		  rtl8367rb.sh
DIRS := mfc switch wifi wps mac 

###########################################################################

#all:

rootfs:
	@echo -e "\033[32mCreating scripts for $(MYNAME) ...\033[0m"
	$(Q)[ -d $(TARGET)/etc/scripts ] || mkdir -p $(TARGET)/etc/scripts

install: 
	@echo -e "\033[32mInstalling scripts for $(MYNAME) ...\033[0m"
	$(Q)for i in $(FILES); do \
		cp --remove-destination $$i $(TARGET)/etc/scripts/.; \
		chmod +x $(TARGET)/etc/scripts/$$i; \
	done
	$(Q)for i in $(DIRS); do \
		if [ ! -d $(TARGET)/etc/scripts/$$i ]; then \
			mkdir -p $(TARGET)/etc/scripts/$$i; \
		fi ; \
		cp -r --remove-destination $$i $(TARGET)/etc/scripts/. ; \
		chmod +x $(TARGET)/etc/scripts/$$i/*.sh; \
	done
	chmod +x $(TARGET)/etc/scripts/*.sh;
	

clean:

.PHONY: all rootfs install clean
