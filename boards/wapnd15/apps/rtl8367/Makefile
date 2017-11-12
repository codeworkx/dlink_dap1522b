###############################################################################
# this part is used to call the linux kernel to MAKE us.

-include ../../arch.mk
-include ../../path.mk
-include $(TOPDIR)/.config

# bouble-20101208- add cflag- ALPHA_PORT_BASE_QOS_PATCH for port base qos.
CFLAGS += -DALPHA_PORT_BASE_QOS_PATCH

KDRV := rtldrv.ko

all: rtlioc
	@echo -e "\033[32mBuilding driver module of RTL8367RB for WAP-ND15\033[0m"
	$(Q)make -C $(KERNELDIR) M=`pwd` modules
	$(Q)$(STRIP) -g $(KDRV)

install:
	@echo -e "\033[32mInstalling driver module of RTL8367RB for WAP-ND15\033[0m"
	$(Q)[ -d $(TARGET)/lib/modules ] || mkdir -p $(TARGET)/lib/modules
	$(Q)cp $(KDRV) $(TARGET)/lib/modules/.
	$(Q)$(STRIP) --remove-section=.note --remove-section=.comment rtlioc
	$(Q)cp rtlioc $(TARGET)/usr/sbin/.
	$(Q)ln -f -s ./rtlioc $(TARGET)/usr/sbin/psts
	$(Q)ln -f -s ./rtlioc $(TARGET)/usr/sbin/slinktype

clean:
	@echo -e "\033[32mCleaning driver module of RTL8367RB for WAP-ND15\033[0m"
	$(Q)make -C $(KERNELDIR) M=`pwd` clean
	$(Q)rm -f Module.symvers rtlioc

.PHONY: all install clean

rtlioc: rtlioc.c
	$(CC) -I$(TOPDIR)/include $(CFLAGS) -o rtlioc rtlioc.c
