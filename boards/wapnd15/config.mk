############################################################################
# Board dependent Makefile for WAP-ND15
############################################################################

MYNAME	:= WAP-ND15
KERNELCONFIG := kernel.config
MKSQFS	:= ./tools/squashfs-tools-4.0/mksquashfs
#MKSQFS_BLOCK := -b 512k
MKSQFS_BLOCK := -b 64k


SEAMA	:= ./tools/seama/seama
PIMGS	:= ./tools/buildimg/packimgs
LZMA	:= ./tools/lzma/lzma
MYMAKE	:= $(Q)make V=$(V) DEBUG=$(DEBUG)
FWDEV	:= /dev/mtdblock/2
LANGDEV := /dev/mtdblock/5
##########################################################################
## follow the dlink f/w naming rule.but we add the build number for develope
##########################################################################
BUILDNO := $(shell cat buildno)
FIRMWAREREV= $(shell echo $(ELBOX_FIRMWARE_VERSION) | cut -d. --output-delimiter=\"\" -f1,2)
MODELNAME_UPPER = $(shell echo $(ELBOX_MODEL_NAME) | tr '[:lower:]' '[:upper:]' | cut -d- --output-delimiter=\"\" -f1,2)
HARDWARE_VER = 1
FIRMWARE_REVISION = $(ELBOX_FIRMWARE_REVISION)

ifeq ($(strip $(FIRMWARE_REVISION)),"N/A")
FIRMWARE_REVISION = ""
endif

RELIMAGE:=$(shell echo $(MODELNAME_UPPER)$(HARDWARE_VER)_FW$(FIRMWAREREV)$(FIRMWARE_REVISION)_$(BUILDNO))
 
#############################################################################
# This one will be make in fakeroot.
fakeroot_rootfs_image:
	@rm -f fakeroot.rootfs.img
	@./progs.board/template.aries/makedevnodes rootfs
	$(Q)$(MKSQFS) rootfs fakeroot.rootfs.img $(MKSQFS_BLOCK)

.PHONY: rootfs_image

#############################################################################
# The real image files


$(ROOTFS_IMG): strip_all_file $(MKSQFS)
	@echo -e "\033[32m$(MYNAME): building squashfs (LZMA)!\033[0m"
	$(Q)make clean_CVS
	$(Q)fakeroot make -f progs.board/config.mk fakeroot_rootfs_image
	$(Q)mv fakeroot.rootfs.img $(ROOTFS_IMG)
	$(Q)chmod 664 $(ROOTFS_IMG)

$(KERNEL_IMG): ./tools/lzma/lzma $(KERNELDIR)/vmlinux
	@echo -e "\033[32m$(MYNAME): building kernel image (LZMA)...\033[0m"
	$(Q)rm -f vmlinux.bin $(KERNEL_IMG)
	$(Q)mipsel-linux-objcopy -O binary -R .note -R .comment -S $(KERNELDIR)/vmlinux vmlinux.bin
	$(Q)$(LZMA) -9 -f -S .lzma vmlinux.bin
	$(Q)mv vmlinux.bin.lzma $(KERNEL_IMG)

$(KERNELDIR)/vmlinux:
	$(MYMAKE) kernel

./tools/sqlzma/sqlzma-3.2-443-r2/mksquashfs:
	$(Q)make -C ./tools/sqlzma/sqlzma-3.2-443-r2

./tools/squashfs-tools-4.0/mksquashfs:
	$(Q)make -C ./tools/squashfs-tools-4.0

./tools/seama/seama:
	$(Q)make -C ./tools/seama

./tools/buildimg/packimgs:
	$(Q)make -C ./tools/buildimg

./tools/lzma/lzma:
	$(Q)make -C ./tools/lzma

strip_all_file: libcreduction_clean libcreduction
#strip_all_file: 
	make -f progs.board/template.aries/strip_all.mk strip_all

##########################################################################

kernel_image:
	@echo -e "\033[32m$(MYNAME): creating kernel image\033[0m"
	$(Q)rm -f $(KERNEL_IMG)
	$(MYMAKE) $(KERNEL_IMG)

rootfs_image:
	@echo -e "\033[32m$(MYNAME): creating rootfs image ...\033[0m"
	$(Q)rm -f $(ROOTFS_IMG)
	$(MYMAKE) $(ROOTFS_IMG)

.PHONY: rootfs_image kernel_image

##########################################################################
#
#	Major targets: kernel, kernel_clean, release & tftpimage
#
##########################################################################

kernel_clean:
	@echo -e "\033[32m$(MYNAME): cleaning kernel ...\033[0m"
	$(Q)make -C kernel mrproper

kernel: kernel_clean
	@echo -e "\033[32m$(MYNAME) Building kernel ...\033[0m"
	$(Q)cp progs.board/$(KERNELCONFIG) kernel/.config
	$(Q)make -C kernel oldconfig
	$(Q)make -C kernel dep
	$(Q)make -C kernel

ifeq (buildno, $(wildcard buildno))
BUILDNO := $(shell cat buildno)

release: kernel_image rootfs_image ./tools/buildimg/packimgs ./tools/seama/seama
	@echo -e "\033[32m"; \
	echo "=====================================";	\
	echo "You are going to build release image.";	\
	echo "=====================================";	\
	echo -e "\033[32m$(MYNAME) make release image... \033[0m"
	$(Q)[ -d images ] || mkdir -p images
	@echo -e "\033[32m$(MYNAME) prepare image...\033[0m"
	$(Q)$(PIMGS) -o raw.img -i $(KERNEL_IMG) -i $(ROOTFS_IMG)
	$(Q)$(SEAMA) -i raw.img -m dev=$(FWDEV) -m type=firmware 
	$(Q)$(SEAMA) -s web.img -i raw.img.seama -m signature=$(ELBOX_SIGNATURE)
	$(Q)$(SEAMA) -d web.img
	$(Q)rm -f raw.img raw.img.seama
	$(Q)./tools/release.sh web.img $(RELIMAGE).bin
	$(Q)make sealpac_template
	$(Q)if [ -f sealpac.slt ]; then ./tools/release.sh sealpac.slt $(RELIMAGE).slt; fi

magic_release: kernel_image rootfs_image ./tools/buildimg/packimgs ./tools/seama/seama
	@echo -e "\033[32m"; \
	echo "===========================================";	\
	echo "You are going to build magic release image.";	\
	echo "===========================================";	\
	echo -e "\033[32m$(MYNAME) make magic release image... \033[0m"
	$(Q)[ -d images ] || mkdir -p images
	@echo -e "\033[32m$(MYNAME) prepare image...\033[0m"
	$(Q)$(PIMGS) -o raw.img -i $(KERNEL_IMG) -i $(ROOTFS_IMG)
	$(Q)$(SEAMA) -i raw.img -m dev=$(FWDEV) -m type=firmware 
	$(Q)$(SEAMA) -s web.img -i raw.img.seama -m signature=$(ELBOX_BOARD_NAME)_aLpHa
	$(Q)$(SEAMA) -d web.img
	$(Q)rm -f raw.img raw.img.seama
	$(Q)./tools/release.sh web.img $(RELIMAGE).magic.bin
tftpimage:
	@echo -e "\033[32mtftp image not build!\033[0m"
	@echo -e "\033[32mwe do not want it!\033[0m"
#tftpimage: kernel_image rootfs_image ./tools/buildimg/packimgs ./tools/seama/seama
#	@echo -e "\033[32mThe tftpimage of $(MYNAME) is identical to the release image!\033[0m"
#	$(Q)$(PIMGS) -o raw.img -i $(KERNEL_IMG) -i $(ROOTFS_IMG)
#	$(Q)$(SEAMA) -i raw.img -m dev=$(FWDEV) -m type=firmware
#	$(Q)rm -f raw.img; mv raw.img.seama raw.img
#	$(Q)$(SEAMA) -d raw.img
#	$(Q)./tools/tftpimage.sh $(TFTPIMG)
#
else
release tftpimage:
	@echo -e "\033[32m$(MYNAME): Can not build image, ROOTFS is not created yet !\033[0m"
endif

.PHONY: kernel release tftpimage kernel_clean magic_release

###################################################################
ifeq ($(strip $(LIB_REDUCTION)), y)
libcreduction:
	$(Q)make -C ./tools/libcreduction install

libcreduction_clean:
	@echo -e "\033[32m libcreduction $(TARGET) !!!!\033[0m"
	$(Q)make -C ./tools/libcreduction clean
else
libcreduction:
libcreduction_clean:
endif

.PHONY: libcreduction libcreduction_clean 
