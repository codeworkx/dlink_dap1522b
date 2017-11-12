#what is different standard config
# 1. multiple ssid
# 2. 2.4G only
# 3. beam forming (CONFIG_RT2860V2_AP_TXBF)
# 4.Ralink Video TURBINE          (CONFIG_RT2860V2_AP_VIDEO_TURBINE)

########################################################################
# smart antanne object code for this config.
#######################################################################
SMART_ANTENNA_OBJECT=smartant_ap_dir645.obj
########################################################################


#if you want want to see other flags,please refrent the build/Kconfig
CONFIG_RT2860V2_AP_MBSS=y
# CONFIG_RT2860V2_AP_IGMP_SNOOP is not set
CONFIG_RT2860V2_AP_DLS=y
CONFIG_RT2860V2_AP_INTELLIGENT_RATE_ADAPTION=y
CONFIG_RT2860V2_80211N_DRAFT3=y
##################################################################
# CONFIG_RA_NETWORK_WORKQUEUE_BH is not set
# CONFIG_RALINK_RT2880 is not set
# CONFIG_RALINK_RT3052 is not set
# CONFIG_RALINK_RT3352 is not set
CONFIG_RALINK_RT3883=y
CONFIG_RT2860V2_AP_LED=y
#CONFIG_RT2860V2_AP_WSC=y
CONFIG_RT2860V2_AP_LLTD=y
CONFIG_RT2860V2_AP_DFS=y
CONFIG_ALPHA_HOSTAPD=y
CONFIG_SMART_ANTENNA=y
CONFIG_RT2860V2_EXT_CHANNEL_LIST=y
CONFIG_RT2860V2_AP_TXBF=y
CONFIG_RT2860V2_AP_VIDEO_TURBINE=y
CONFIG_WLAN_LED_NOBLINK=y
