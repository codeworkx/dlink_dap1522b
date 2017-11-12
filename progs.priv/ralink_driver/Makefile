include ../../.config

all:
ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT5592_V2700)),y)
	make -C RT5592_ap_v2700_Beta_dpa_20120410 $@
endif
ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT3883_3662_SMART_ANTENNA)),y)
	make -C 3662_smart_antenna $@
endif
ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT3352_WIFI_DRIVER_2_5_0_4)),y)
	make -C 3352_wifi_driver_2.5.0.4 $@
endif
ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT3572_AP_DRIVER_2_5_0_2)),y)
	make -C 3572_ap_driver_2.5.0.2_rev2_dfs $@
endif


install:
ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT5592_V2700)),y)
    make -C RT5592_ap_v2700_Beta_dpa_20120410 $@
endif
ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT3883_3662_SMART_ANTENNA)),y)
	make -C 3662_smart_antenna $@
endif
ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT3352_WIFI_DRIVER_2_5_0_4)),y)
	make -C 3352_wifi_driver_2.5.0.4 $@
endif
ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT3572_AP_DRIVER_2_5_0_2)),y)
	make -C 3572_ap_driver_2.5.0.2_rev2_dfs $@
endif


clean:
ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT5592_V2700)),y)
    make -C RT5592_ap_v2700_Beta_dpa_20120410 $@
endif
ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT3883_3662_SMART_ANTENNA)),y)
	make -C ./3662_smart_antenna $@
endif
ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT3352_WIFI_DRIVER_2_5_0_4)),y)
	make -C ./3352_wifi_driver_2.5.0.4 $@
endif
ifeq ($(strip $(ELBOX_PROGS_PRIV_RALINK_RT3572_AP_DRIVER_2_5_0_2)),y)
	make -C 3572_ap_driver_2.5.0.2_rev2_dfs $@
endif


.PHONY :all install clean
