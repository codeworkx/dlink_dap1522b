###############################################################################
# this part is used to call the linux kernel to MAKE us.

-include ../../../../arch.mk
-include ../../../../path.mk
-include $(TOPDIR)/.config

KDRV := rt2860v2_ap.ko

all:
	@echo -e "\033[32mBuilding driver module of RT2860v2 with smart antenna for RT3662\033[0m"
	@echo -e "\033[32mstrip=$(STRIP)\033[0m"
	$(Q)make -C $(KERNELDIR) M=`pwd` modules V=1
	$(Q)$(STRIP) --strip-debug $(KDRV)

install:
	@echo -e "\033[32mInstalling driver module of RT2860v2 with smart antenna for RT3662\033[0m"
	$(Q)[ -d $(TARGET)/lib/modules ] || mkdir -p $(TARGET)/lib/modules
	$(Q)cp $(KDRV) $(TARGET)/lib/modules/.

clean:
	@echo -e "\033[32mCleaning driver module of RT2860v2 with smart antenna for RT3662\033[0m"
	$(Q)find $(PWD)/../ \( -name '*.o' -o -name '*.cmd' -o -name '*.symvers' -o -name '*.mod.c' -o -name '*.ko' -o -name '.tmp_versions' -o -name '*.o.d' \) | xargs rm -rf

.PHONY: all install clean

