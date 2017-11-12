include ../../../path.mk
include ../../../arch.mk
include ../../../.config


ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT3883_3662_SMART_ANTENNA)),y)
DRV_PATH:=.
ifdef ELBOX_PROGS_PRIV_RALINK_RT3883_3662_SMART_ANTENNA_AP_CONFIG_FILE
config_ap_mk    += $(DRV_PATH)/config/$(strip $(ELBOX_PROGS_PRIV_RALINK_RT3883_3662_SMART_ANTENNA_AP_CONFIG_FILE))
else
config_ap_mk    += $(DRV_PATH)/config/config.alpha.mk
endif

ifdef ELBOX_PROGS_PRIV_RALINK_RT3883_3662_SMART_ANTENNA_STA_CONFIG_FILE
config_sta_mk   += $(DRV_PATH)/config/$(strip $(ELBOX_PROGS_PRIV_RALINK_RT3883_3662_SMART_ANTENNA_STA_CONFIG_FILE))
else
config_sta_mk   += $(DRV_PATH)/config/config.alpha.sta.mk
endif
endif

ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT3883_3662_SMART_ANTENNA_AP)),y)
BUILDDIR    += $(DRV_PATH)/rt2860v2_ap/
endif

ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT3883_3662_SMART_ANTENNA_STA)),y)
BUILDDIR    += $(DRV_PATH)/rt2860v2_sta/
endif

all:
ifdef config_ap_mk
	$(Q)cp $(config_ap_mk) $(DRV_PATH)/config.alpha.mk
endif
ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT3883_3662_SMART_ANTENNA_AP_SW_PORT_BASE_QOS)),y)
	$(Q)echo "CONFIG_ALPHA_SW_PORT_BASE_QOS=y" >> $(DRV_PATH)/config.alpha.mk
endif
ifeq ($(strip $(ELBOX_PROGS_PRIV_WLAN_W_PARTITION)),y)
	$(Q)echo "ELBOX_PROGS_PRIV_WLAN_W_PARTITION=y" >> $(DRV_PATH)/config.alpha.mk
endif
ifeq ($(strip $(ELBOX_BRAND_ARIESAP_DAP1522B)),y)
	$(Q)echo "ELBOX_BRAND_ARIESAP_DAP1522B=y" >> $(DRV_PATH)/config.alpha.mk
endif


ifdef config_sta_mk
	$(Q)cp $(config_sta_mk) $(DRV_PATH)/config.alpha.sta.mk
endif	

ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT3883_3662_STA_DHCP_UDPCHECKSUM_REBUILD)),y)
	$(Q)echo "CONFIG_STA_DHCP_UDPCHECKSUM_REBUILD=y" >> $(DRV_PATH)/config.alpha.sta.mk
endif

ifeq ($(strip $(ELBOX_BRAND_ARIESAP_DAP1522B)),y)
	$(Q)echo "ELBOX_BRAND_ARIESAP_DAP1522B=y" >> $(DRV_PATH)/config.alpha.sta.mk
endif
	@for i in $(BUILDDIR); do make -C $$i || exit $? ; done

install:
	@for i in $(BUILDDIR); do make -C $$i install || exit $? ; done
	$(Q)[ -d $(TARGET)/etc/Wireless/ ] || mkdir -p $(TARGET)/etc/Wireless/
ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT3883_3662_SMART_ANTENNA)),y)
		$(Q)cp $(DRV_PATH)/RT3662_AP_2T2R_V0_0.bin  $(TARGET)/etc/Wireless/RT3662_AP_2T2R_V0_0.bin
endif

clean:
	 @for i in $(BUILDDIR); do make -C $$i clean || exit $? ; done
	 @rm -f *.mk
	 
