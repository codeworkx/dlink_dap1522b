

#export BUILD_WPA2_DEBUG=y
export BUILD_WPA2_NO_EXAMPLES=y
export BUILD_WPA2_NO_APSTART=y
export BUILD_WPA2_NO_WPA_SUPPLICANT=y
#export MADWIFIPATH=$(shell cd ../../wlan/core && pwd)
#export MADWIFIINC=$(shell cd ../../wlan/core/ath_hal && pwd)

ifndef INSTALL_ROOT
INSTALL_ROOT=$(shell pwd)/install
endif

# If building only one of wpa_supplicant and hostapd, then static link
# makes most sense... else shared object file (default) makes most sense.
ifeq ($(BUILD_WPA2_NO_WPA_SUPPLICANT),y)
ifndef BUILD_WPA2_STATIC_COMMON_LIBRARY
export BUILD_WPA2_STATIC_COMMON_LIBRARY=y
endif
endif
ifeq ($(BUILD_WPA2_NO_HOSTAPD),y)
ifndef BUILD_WPA2_STATIC_COMMON_LIBRARY
export BUILD_WPA2_STATIC_COMMON_LIBRARY=y
endif
endif

include ../../path.mk
include ../../arch.mk

all: common hostapd wpatalk

common: 
	@echo -e "\033[32mBuild common hostapd_wps lib ...\033[0m" 
	$(Q)make -C common
hostapd:
	@echo -e "\033[32mBuild hostapd_wps  ...\033[0m" 
	$(Q)make -C hostapd
wpatalk:	
	@echo -e "\033[32mBuild wpatalk  ...\033[0m" 
	$(Q)make -C wpatalk

install:
	@echo -e "\033[32mInstall hostapd_wps program ...\033[0m" 
	$(Q)$(STRIP) common/libwpa_common.so common/libwpa_ctrl.so
	$(Q)$(STRIP) hostapd/hostapd
	$(Q)$(STRIP) wpatalk/wpatalk
	$(Q)cp common/libwpa_common.so common/libwpa_ctrl.so $(TARGET)/lib/ -f
	$(Q)cp hostapd/hostapd $(TARGET)/usr/sbin/ -f
	$(Q)cp wpatalk/wpatalk $(TARGET)/usr/sbin/ -f	

# Keep "update" first so it is default
update: 
	if [ ! -f .firsttime ] ; then echo MUST DO FIRSTTIME BUILD FIRST; $(MAKE) firsttime; fi
	@echo DOING UPDATE
	@echo UPDATING tags file
	ctags `find . -name '*.h' -o -name '*.c' -o -name '*.cpp'`
	mkdir -p $(INSTALL_ROOT)
	mkdir -p $(INSTALL_ROOT)/sbin
	mkdir -p $(INSTALL_ROOT)/lib
	mkdir -p $(INSTALL_ROOT)/etc/wpa2
	mkdir -p $(INSTALL_ROOT)/include
	@echo UPDATING common
	cd common && $(MAKE) install
	@echo DONE with update


firsttime:  clean 
	@echo BUILD_WPA2_DEBUG is $(BUILD_WPA2_DEBUG)
	echo Did first time inits >.firsttime
	@echo DONE with firsttime
	$(MAKE) update

clean:
	@echo DOING CLEAN
	@echo -e "\033[32mCleaning hostapd program ...\033[0m" 
	-rm -rf install tags .firsttime
	-find . -name 'junk*' -exec rm -rf '{}' ';'
	-find . -name '.deps' -exec rm -rf '{}' ';'
	-find . -name '.dirstamp' -exec rm -rf '{}' ';'
	-find . -name '.obj' -exec rm -rf '{}' ';'
	-find . -name '.moc' -exec rm -rf '{}' ';'
	-find . -name '.ui' -exec rm -rf '{}' ';'
	-find . -name '.rnd' -exec rm -rf '{}' ';'
	-find . -name '.*.swp' -exec rm -rf '{}' ';'
	-find . -name '*.log' -exec rm -rf '{}' ';'
	-(cd common && $(MAKE) -k clean)
	-(cd hostapd && $(MAKE) -k clean)
	-(cd wpatalk && $(MAKE) -k clean)
	@echo All cleaned

gpl:
	@echo DOING GPL
	$(Q)make -C common gpl
	$(Q)make -C hostapd gpl
	$(Q)make -C wpatalk gpl
	$(Q)make -C wpa_supplicant gpl
	$(Q)mv Makefile.gpl Makefile

.PHONY: install common hostapd wpatalk gpl
