-include ../../arch.mk
-include ../../path.mk
include $(TOPDIR)/.config

.PHONY: all
all: module applet

.PHONY: module
module:
	@echo -e "\033[32mBuilding kernel module: vethdev\033[0m"
	@make -C $(KERNELDIR) M=$(shell pwd)/vethdev modules

.PHONY: applet
applet:
	@echo -e "\033[32mBuilding applet: vethctl\033[0m"
	@make -C vethctl CC="$(CC)" CFLAGS="$(CFLAGS)" LDFLAGS="$(LDFLAGS)" STRIP="$(STRIP)"

.PHONY: clean
clean: module_clean applet_clean

.PHONY: module_clean
module_clean:
	@echo -e "\033[32mCleaning kernel module\033[0m"
	@cd ./vethdev && rm -rf *.o *.ko *.mod.c .*.cmd .*.o.d .tmp_versions *.symvers

.PHONY: applet_clean
applet_clean:
	@echo -e "\033[32mCleaning user applet\033[0m"
	@make -C vethctl clean

.PHONY: distclean
distclean: clean

.PHONY: install
install:
	@echo -e "\033[32mInstalling veth kernel module and utility\033[0m"
	@cp ./vethdev/vethdev.ko $(TARGET)/lib/modules/.
	@cp ./vethctl/vethctl $(TARGET)/usr/sbin/.
