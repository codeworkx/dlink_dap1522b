# Makefile for ifresetcnt
-include ../../path.mk
-include $(TOPDIR)/.config

#############################################################################
# linux 2.6.x
ifeq ($(strip $(ELBOX_PROGS_PRIV_IFRESETCNT_26)),y)
BIN		:= ifresetcnt.ko
PWD		:= $(shell pwd)
# Clear CFLAGS & LDFLAGS
CFLAGS	:=
LDFLAGS	:=

all:
	@echo -e "\033[32mBuilding driver for kernel 2.6.x (Network interface reset count)...\033[0m"
	$(Q)make -C $(KERNELDIR) M=$(PWD) modules

clean:
	@echo -e "\033[32mCleaning driver (Network interface reset count)...\033[0m"
	$(Q)make -C $(KERNELDIR) M=$(PWD) clean
	$(Q)rm -f modules.order

#############################################################################
# linux 2.4.x
else
BIN := ifresetcnt.o
CFLAGS = $(KCFLAGS) -I$(KERNELDIR)/include
%.o: %.c
	@echo -e "\033[32mBuilding driver for kernel 2.4(Network interface reset count)...\033[0m"
	$(Q)$(KCC) $(CFLAGS) -c $< -o $@

all: $(BIN)

clean:
	@echo -e "\033[32mCleaning driver (Network interface reset count)...\033[0m"
	$(Q)rm -f *~ *.o

endif
#############################################################################

install:
	@echo -e "\033[32mInstalling driver (Network interface reset count)...\033[0m"
	$(Q)[ -d $(TARGET)/lib/modules ] || mkdir -p $(TARGET)/lib/modules
	$(Q)[ -z $(STRIP) ] || $(STRIP) -g ./$(BIN)
	$(Q)cp -a ./$(BIN) $(TARGET)/lib/modules/

gpl:
	rm -f *.c *.o *.h
	rm -f Kbuild modules.order Module.symvers
	mv Makefile.gpl Makefile

.PHONY: all install clean gpl
