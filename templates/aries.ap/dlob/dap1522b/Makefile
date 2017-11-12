# vi: set sw=4 ts=4:

-include ../path.mk
-include ../arch.mk
-include $(TOPDIR)/.config
-include $(TOPDIR)/Vars.mk

#####################################################################

MYNAME	:= "aries/dlob/DAP-1522B"
MYMAKE	:= make V=$(V) DEBUG=$(DEBUG)
SUBDIRS	:= htdocs defnodes scripts services templates events

all:
	$(Q)for i in $(SUBDIRS); do \
	if [ -f $$i/Makefile ]; then \
		$(MYMAKE) -C $$i all; \
	fi \
	done

rootfs:
	$(Q)for i in $(SUBDIRS); do \
	if [ -f $$i/Makefile ]; then \
		$(MYMAKE) -C $$i rootfs; \
	fi \
	done

install:
	$(Q)for i in $(SUBDIRS); do \
	if [ -f $$i/Makefile ]; then \
		$(MYMAKE) -C $$i install; \
	else \
		$(Q)[ -d $(TARGET)/$$i ] || mkdir -p $(TARGET)/$$i ;\
		cp -r --remove-destination $$i $(TARGET)/$$i/. ;\
	fi \
	done

clean:
	$(Q)for i in $(SUBDIRS); do \
	if [ -f $$i/Makefile ]; then \
		$(MYMAKE) -C $$i all; \
	fi \
	done

.PHONY: all rootfs install clean
