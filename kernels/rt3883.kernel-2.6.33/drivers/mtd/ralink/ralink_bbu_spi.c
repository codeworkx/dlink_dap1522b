/*
 * MTD SPI driver for ST M25Pxx flash chips
 *
 * Author: Mike Lavender, mike@steroidmicros.com
 *
 * Copyright (c) 2005, Intec Automation Inc.
 *
 * Some parts are based on lart.c by Abraham Van Der Merwe
 *
 * Cleaned up and generalized based on mtd_dataflash.c
 *
 * This code is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2 as
 * published by the Free Software Foundation.
 *
 */

#include <linux/init.h>
#include <linux/module.h>
#include <linux/device.h>
#include <linux/interrupt.h>
#include <linux/mtd/mtd.h>
#include <linux/mtd/partitions.h>
#include <linux/semaphore.h>
#include <linux/delay.h>
#include <linux/sched.h>
#include <linux/err.h>
#include "bbu_spiflash.h"
#include "../maps/ralink-flash.h"
#include <linux/squashfs_fs.h>
#include <linux/magic.h>


//extern static int bbu_spic_trans(const u8 code, const u32 addr, u8 *buf, const size_t n_tx, const size_t n_rx, int flag);
//extern static int bbu_spic_busy_wait(void);

extern u32 get_surfboard_sysclk(void) ;
#if defined(CONFIG_MTD_ANY_RALINK)
extern int ra_check_flash_type(void);
#define BOOT_FROM_SPI   3
#endif
#if 1
/*********************************************************************/

/*
 *  Flash layout
 *
 *  |      16K      |      16K      |      16K      |      16K      |
 *  +---------------------------------------------------------------+ 0x00000000
 *  |                                                               |
 *  | U-Boot (64K x 3)                                              |
 *  |                                                               |
 *  +---------------------------------------------------------------+ 0x00030000
 *  | u-boot config | RF params     | devdata                       |
 *  +---------------------------------------------------------------+ 0x00040000
 *  | devconf (64K x 1)                                             |
 *  +---------------------------------------------------------------+ 0x00050000
 *  |                                                               |
 *  | Upgrade (linux kernel/rootfs)                                 |
 *  ~                                                               ~
 *  |                                                               |
 *  |                                                               |
 *  +---------------------------------------------------------------+ 0x003f0000
 *  | Language pack (64K x 1)                                       |
 *  +---------------------------------------------------------------+ 0x00400000
 */

//#define CONFIG_MTD_ELBOX_PHYSMAP_LEN 0x400000
//#define CONFIG_MTD_ELBOX_KERNEL_SKIP 0x90000 

#define FLASH_SIZE		CONFIG_MTD_ELBOX_PHYSMAP_LEN
#ifdef CONFIG_MTD_ELBOX_LANG_PACK
#define LANGPACK_SIZE	CONFIG_MTD_ELBOX_LANG_PACK_SIZE
#else
#define LANGPACK_SIZE	0
#endif

#define BOOTCODE_SIZE	0x30000
#define DEVCONF_SIZE	0x10000
#define DEVDATA_SIZE	0x10000
#define UPGRADE_SIZE	(FLASH_SIZE - BOOTCODE_SIZE - DEVCONF_SIZE - DEVDATA_SIZE - LANGPACK_SIZE)

#define DEVDATA_OFFSET	(BOOTCODE_SIZE)
#define DEVCONF_OFFSET	(BOOTCODE_SIZE + DEVDATA_SIZE)
#define UPGRADE_OFFSET	(BOOTCODE_SIZE + DEVDATA_SIZE + DEVCONF_SIZE)
#define LANGPACK_OFFSET	(BOOTCODE_SIZE + DEVDATA_SIZE + DEVCONF_SIZE + UPGRADE_SIZE)

static struct mtd_partition elbox_partitions[] =
{
	/* The following partitions are the "MUST" in ELBOX. */
	{name:"rootfs",		offset:0,				size:0,				mask_flags:MTD_WRITEABLE, },
	{name:"upgrade",	offset:UPGRADE_OFFSET,	size:UPGRADE_SIZE,	},
	{name:"devconf",	offset:DEVCONF_OFFSET,	size:DEVCONF_SIZE,	},
	{name:"devdata",	offset:DEVDATA_OFFSET,	size:DEVDATA_SIZE,	},
	{name:"langpack",	offset:LANGPACK_OFFSET,	size:LANGPACK_SIZE,	},
	{name:"flash",		offset:0,				size:FLASH_SIZE,	mask_flags:MTD_WRITEABLE, },

	/* The following partitions are board dependent. */
	{name:"u-boot",		offset:0,				size:BOOTCODE_SIZE,	mask_flags:MTD_WRITEABLE, },
	{name:"boot env",	offset:DEVDATA_OFFSET,	size:0x8000,		mask_flags:MTD_WRITEABLE, }
};


#define SEAMA_MAGIC 0x5EA3A417
typedef struct seama_hdr seamahdr_t;
struct seama_hdr
{
	uint32_t	magic;		/* should always be SEAMA_MAGIC. */
	uint16_t	reserved;	/* reserved for  */
	uint16_t	metasize;	/* size of the META data */
	uint32_t	size;		/* size of the image */
} __attribute__ ((packed));

/* Jack add 12/12/07 +++ */
#define IMG_MAX_DEVNAME		32
#define MAX_SIGNATURE		32
typedef struct _imgblock imgblock_t;
struct _imgblock
{
	unsigned long	magic;		/* image magic number (should be IMG_V2_MAGIC_NO in little endian. */
	unsigned long	size;		/* size of the image. */
	unsigned long	offset;		/* offset from the beginning of the storage device. */
	char			devname[IMG_MAX_DEVNAME];	/* null termiated string of the storage device name. ex. "/dev/mtd6" */
	unsigned char	digest[16];	/* MD5 digest of the image */
} __attribute__ ((packed));

typedef struct _imghdr2 imghdr2_t;
struct _imghdr2
{
	char			signature[MAX_SIGNATURE];
	unsigned long	magic;	/* should be IMG_V2_MAGIC_NO in little endian. */
} __attribute__ ((packed));
#define IMG_V2_MAGIC_NO		0x20040220	/* version 2 magic number */ 

/* the tag is 32 bytes octet,
 * first part is the tag string,
 * and the second half is reserved for future used. */
#define PACKIMG_TAG "--PaCkImGs--"
struct packtag
{
	char tag[16];
	unsigned long size;
	char reserved[12];
};

#else
static struct mtd_partition rt2880_partitions[] = {
	{
                name:           "ALL",
                size:           MTDPART_SIZ_FULL,
                offset:         0,
        },
	/* Put your own partition definitions here */
        {
                name:           "Bootloader",
                size:           MTD_BOOT_PART_SIZE,
                offset:         0,
        }, {
                name:           "Config",
                size:           MTD_CONFIG_PART_SIZE,
                offset:         MTDPART_OFS_APPEND
        }, {
                name:           "Factory",
                size:           MTD_FACTORY_PART_SIZE,
                offset:         MTDPART_OFS_APPEND
#ifdef CONFIG_RT2880_ROOTFS_IN_FLASH
        }, {
                name:           "Kernel",
                size:           MTD_KERN_PART_SIZE,
                offset:         MTDPART_OFS_APPEND,
        }, {
                name:           "RootFS",
                size:           MTD_ROOTFS_PART_SIZE,
                offset:         MTDPART_OFS_APPEND,
#ifdef CONFIG_ROOTFS_IN_FLASH_NO_PADDING
        }, {
                name:           "Kernel_RootFS",
                size:           MTD_KERN_PART_SIZE + MTD_ROOTFS_PART_SIZE,
                offset:         MTD_BOOT_PART_SIZE + MTD_CONFIG_PART_SIZE + MTD_FACTORY_PART_SIZE,
#endif
#else //CONFIG_RT2880_ROOTFS_IN_RAM
        }, {
                name:           "Kernel",
                size:           MTD_KERN_PART_SIZE,
                offset:         MTDPART_OFS_APPEND,
#endif
#ifdef CONFIG_DUAL_IMAGE
        }, {
                name:           "Kernel2",
                size:           MTD_KERN2_PART_SIZE,
                offset:         MTD_KERN2_PART_OFFSET,
#ifdef CONFIG_RT2880_ROOTFS_IN_FLASH
        }, {
                name:           "RootFS2",
                size:           MTD_ROOTFS2_PART_SIZE,
                offset:         MTD_ROOTFS2_PART_OFFSET,
#endif
#endif
        }
};
#endif


/*********************************************************************************
 * SPI FLASH elementray definition and function
 **********************************************************************************/

#define FLASH_PAGESIZE		256

/* Flash opcodes. */
#define	OPCODE_WREN		6	/* Write enable */
#define	OPCODE_RDSR		5	/* Read status register */
#define	OPCODE_WRSR		1	/* Write status register */
#define	OPCODE_READ		3	/* Read data bytes */
#define	OPCODE_PP		2	/* Page program */
#define	OPCODE_SE		0xd8	/* Sector erase */
#define	OPCODE_RES		0xab	/* Read Electronic Signature */
#define	OPCODE_RDID		0x9f	/* Read JEDEC ID */

/* Status Register bits. */
#define	SR_WIP			1	/* Write in progress */
#define	SR_WEL			2	/* Write enable latch */
#define	SR_BP0			4	/* Block protect 0 */
#define	SR_BP1			8	/* Block protect 1 */
#define	SR_BP2			0x10	/* Block protect 2 */
#define	SR_EPE			0x20	/* Erase/Program error */
#define	SR_SRWD			0x80	/* SR write protect */


//static unsigned int spi_wait_nsec = 0;

//#define SPI_DEBUG
#if !defined (SPI_DEBUG)

#define ra_inl(addr)  (*(volatile unsigned int *)(addr))
#define ra_outl(addr, value)  (*(volatile unsigned int *)(addr) = (value))
#define ra_dbg(args...) do {} while(0)
//#define ra_dbg(args...) do { if (1) printk(args); } while(0)

#else

int ranfc_debug = 1;
#define ra_dbg(args...) do { if (ranfc_debug) printk(args); } while(0)
#define _ra_inl(addr)  (*(volatile unsigned int *)(addr))
#define _ra_outl(addr, value)  (*(volatile unsigned int *)(addr) = (value))

u32 ra_inl(u32 addr)
{	
	u32 retval = _ra_inl(addr);
	
	printk("%s(%x) => %x \n", __func__, addr, retval);

	return retval;	
}

u32 ra_outl(u32 addr, u32 val)
{
	_ra_outl(addr, val);

	printk("%s(%x, %x) \n", __func__, addr, val);

	return val;	
}

#endif // SPI_DEBUG

#define ra_aor(addr, a_mask, o_value)  ra_outl(addr, (ra_inl(addr) & (a_mask)) | (o_value))

#define ra_and(addr, a_mask)  ra_aor(addr, a_mask, 0)
#define ra_or(addr, o_value)  ra_aor(addr, -1, o_value)

void usleep(unsigned int usecs)
{
        unsigned long timeout = usecs_to_jiffies(usecs);

        while (timeout)
                timeout = schedule_timeout_interruptible(timeout);
}

static int bbu_spic_busy_wait(void)
{
	int n = 100000;
	do {
		if ((ra_inl(SPI_REG_CTL) & SPI_CTL_BUSY) == 0)
			return 0;
		udelay(1);
	} while (--n > 0);

	printk("%s: fail \n", __func__);
	return -1;
}

#define SPIC_READ_BYTES (1<<0)
#define SPIC_WRITE_BYTES (1<<1)


int spic_init(void)
{
	// enable SMC bank 0 alias addressing
	ra_or(RALINK_SYSCTL_BASE + 0x38, 0x80000000);
	
	return 0;
}


/****************************************************************************/
struct chip_info {
	char		*name;
	u8		id;
	u32		jedec_id;
	unsigned	sector_size;
	unsigned	n_sectors;
};

static struct chip_info chips_data [] = {
	/* REVISIT: fill in JEDEC ids, for parts that have them */
	{ "AT25DF321",		0x1f, 0x47000000, 64 * 1024, 64 },
	{ "AT26DF161",		0x1f, 0x46000000, 64 * 1024, 32 },
	{ "FL016AIF",		0x01, 0x02140000, 64 * 1024, 32 },
	{ "FL064AIF",       0x01, 0x02160000, 64 * 1024, 128 },
	{ "MX25L1605D",		0xc2, 0x2015c220, 64 * 1024, 32 },
	{ "MX25L3205D",		0xc2, 0x2016c220, 64 * 1024, 64 }, /* MX25L3206E */
	{ "MX25L6405D",		0xc2, 0x2017c220, 64 * 1024, 128 },
	{ "MX25L12805D",	0xc2, 0x2018c220, 64 * 1024, 256 },
	{ "S25FL128P",		0x01, 0x20180301, 64 * 1024, 256 },
	{ "S25FL129P",      0x01, 0x20184D01, 64 * 1024, 256 },
	{ "S25FL032P",      0x01, 0x02154D00, 64 * 1024, 64 },
	{ "S25FL064P",      0x01, 0x02164D00, 64 * 1024, 128 },
	{ "25064BVSIG",		0xef, 0x40170000, 64 * 1024, 128 },
	{ "EN25F16",		0x1c, 0x31151c31, 64 * 1024, 32 },
	{ "EN25F32",        0x1c, 0x31161c31, 64 * 1024, 64 },
	{ "EN25Q32B",       0x1c, 0x30161c30, 64 * 1024, 64 },
	{ "EN25F64",        0x1c, 0x20171c20, 64 * 1024, 128 }, //EN25P64
    { "EN25Q64",        0x1c, 0x30171c30, 64 * 1024, 128 },
    { "W25Q32BV",       0xef, 0x30160000, 64 * 1024, 64 },
    { "W25Q32BV",       0xef, 0x40160000, 64 * 1024, 64 },
    { "W25Q64BV",       0xef, 0x40170000, 64 * 1024, 128}, //S25FL064K
    { "W25Q128BV",      0xef, 0x40180000, 64 * 1024, 256 },
	{ "F25L32PA",       0x8c, 0x20168c20, 64 * 1024, 64 }
};


struct flash_info {
	struct semaphore	lock;
	struct mtd_info		mtd;
	struct chip_info	*chip;
	u8			command[4];
};

struct flash_info *flash;


#ifdef MORE_BUF_MODE
static int bbu_mb_spic_trans(const u8 code, const u32 addr, u8 *buf, const size_t n_tx, const size_t n_rx, int flag)
{
	u32 reg;
	int i, q, r;
	int rc = -1;

	if (flag != SPIC_READ_BYTES && flag != SPIC_WRITE_BYTES) {
		printk("we currently support more-byte-mode for reading and writing data only\n");
		return -1;
	}

	/* step 0. enable more byte mode */
	ra_or(SPI_REG_MASTER, (1 << 2));

	bbu_spic_busy_wait();

	/* step 1. set opcode & address, and fix cmd bit count to 32 (or 40) */
#ifdef MX_4B_MODE 
	if (flash && flash->chip->addr4b) {
		ra_and(SPI_REG_CTL, ~SPI_CTL_ADDREXT_MASK);
		ra_or(SPI_REG_CTL, (code << 24) & SPI_CTL_ADDREXT_MASK);
		ra_outl(SPI_REG_OPCODE, addr);
	}
	else
#endif
	{
		ra_outl(SPI_REG_OPCODE, (code << 24) & 0xff000000);
		ra_or(SPI_REG_OPCODE, (addr & 0xffffff));
	}
	ra_and(SPI_REG_MOREBUF, ~SPI_MBCTL_CMD_MASK);
#ifdef MX_4B_MODE 
	if (flash && flash->chip->addr4b)
		ra_or(SPI_REG_MOREBUF, (40 << 24));
	else
#endif
		ra_or(SPI_REG_MOREBUF, (32 << 24));

	/* step 2. write DI/DO data #0 ~ #7 */
	if (flag & SPIC_WRITE_BYTES) {
		if (buf == NULL) {
			printk("%s: write null buf\n", __func__);
			goto RET_MB_TRANS;
		}
		for (i = 0; i < n_tx; i++) {
			q = i / 4;
			r = i % 4;
			if (r == 0)
				ra_outl(SPI_REG_DATA(q), 0);
			ra_or(SPI_REG_DATA(q), (*(buf + i) << (r * 8)));
		}
	}

	/* step 3. set rx (miso_bit_cnt) and tx (mosi_bit_cnt) bit count */
	ra_and(SPI_REG_MOREBUF, ~SPI_MBCTL_TX_RX_CNT_MASK);
	ra_or(SPI_REG_MOREBUF, (n_rx << 3 << 12));
	ra_or(SPI_REG_MOREBUF, n_tx << 3);

	/* step 4. kick */
	ra_or(SPI_REG_CTL, SPI_CTL_START);

	/* step 5. wait spi_master_busy */
	bbu_spic_busy_wait();
	if (flag & SPIC_WRITE_BYTES) {
		rc = 0;
		goto RET_MB_TRANS;
	}

	/* step 6. read DI/DO data #0 */
	if (flag & SPIC_READ_BYTES) {
		if (buf == NULL) {
			printk("%s: read null buf\n", __func__);
			return -1;
		}
		for (i = 0; i < n_rx; i++) {
			q = i / 4;
			r = i % 4;
			reg = ra_inl(SPI_REG_DATA(q));
			*(buf + i) = (u8)(reg >> (r * 8));
		}
	}

	rc = 0;
RET_MB_TRANS:
	/* step #. disable more byte mode */
	ra_and(SPI_REG_MASTER, ~(1 << 2));
	return rc;
}
#endif // MORE_BUF_MODE //


static int bbu_spic_trans(const u8 code, const u32 addr, u8 *buf, const size_t n_tx, const size_t n_rx, int flag)
{
	u32 reg;

	bbu_spic_busy_wait();

	/* step 1. set opcode & address */
#ifdef MX_4B_MODE 
	if (flash && flash->chip->addr4b) {
		ra_and(SPI_REG_CTL, ~SPI_CTL_ADDREXT_MASK);
		ra_or(SPI_REG_CTL, addr & SPI_CTL_ADDREXT_MASK);
	}
#endif
	ra_outl(SPI_REG_OPCODE, ((addr & 0xffffff) << 8));
	ra_or(SPI_REG_OPCODE, code);

	/* step 2. write DI/DO data #0 */
	if (flag & SPIC_WRITE_BYTES) {
		if (buf == NULL) {
			printk("%s: write null buf\n", __func__);
			return -1;
		}
		ra_outl(SPI_REG_DATA0, 0);
		switch (n_tx) {
		case 8:
			ra_or(SPI_REG_DATA0, (*(buf+3) << 24));
		case 7:
			ra_or(SPI_REG_DATA0, (*(buf+2) << 16));
		case 6:
			ra_or(SPI_REG_DATA0, (*(buf+1) << 8));
		case 5:
		case 2:
			ra_or(SPI_REG_DATA0, *buf);
			break;
		default:
			printk("%s: fixme, write of length %d\n", __func__, n_tx);
			return -1;
		}
	}

	/* step 3. set mosi_byte_cnt */
	ra_and(SPI_REG_CTL, ~SPI_CTL_TX_RX_CNT_MASK);
	ra_or(SPI_REG_CTL, (n_rx << 4));
#ifdef MX_4B_MODE 
	if (flash && flash->chip->addr4b && n_tx >= 4)
		ra_or(SPI_REG_CTL, (n_tx + 1));
	else
#endif
		ra_or(SPI_REG_CTL, n_tx);

	/* step 4. kick */
	ra_or(SPI_REG_CTL, SPI_CTL_START);

	/* step 5. wait spi_master_busy */
	bbu_spic_busy_wait();
	if (flag & SPIC_WRITE_BYTES)
		return 0;

	/* step 6. read DI/DO data #0 */
	if (flag & SPIC_READ_BYTES) {
		if (buf == NULL) {
			printk("%s: read null buf\n", __func__);
			return -1;
		}
		reg = ra_inl(SPI_REG_DATA0);
		switch (n_rx) {
		case 4:
			*(buf+3) = (u8)(reg >> 24);
		case 3:
			*(buf+2) = (u8)(reg >> 16);
		case 2:
			*(buf+1) = (u8)(reg >> 8);
		case 1:
			*buf = (u8)reg;
			break;
		default:
			printk("%s: fixme, read of length %d\n", __func__, n_rx);
			return -1;
		}
	}
	return 0;
}


/****************************************************************************/


static int raspi_read_deviceid(u8 *rxbuf, int n_rx)
{
	u8 code = OPCODE_RDID;
	int retval;

	retval = bbu_spic_trans(code, 0, rxbuf, 1, 3, SPIC_READ_BYTES);
	if (!retval)
		retval = n_rx;
		
	if (retval != n_rx) {
		printk("%s: ret: %x \n", __func__, retval);
		return retval;
	}

	return retval;
	
}

/*
 * Internal helper functions
 */

/*
 * Read the status register, returning its value in the location
 */
static int raspi_read_sr(u8 *val)
{
	ssize_t retval;
	u8 code = OPCODE_RDSR;

	retval = bbu_spic_trans(code, 0, val, 1, 1, SPIC_READ_BYTES);
	return retval;
}

/*
 * write status register
 */
static int raspi_write_sr(u8 *val)
{
	ssize_t retval;
	u8 code = OPCODE_WRSR;

	retval = bbu_spic_trans(code, 0, val, 2, 0, SPIC_WRITE_BYTES);
	if (retval != 1) {
		printk("%s: ret: %x\n", __func__, retval);
		return -EIO;
	}
	return 0;
}

/*
 * Set write enable latch with Write Enable command.
 * Returns negative if error occurred.
 */
static inline int raspi_write_enable(void)
{
	u8 code = OPCODE_WREN;

	return bbu_spic_trans(code, 0, NULL, 1, 0, 0);
}

/*
 * Set all sectors (global) unprotected if they are protected.
 * Returns negative if error occurred.
 * Returns 1 if unprotecting process is needed and done. Leon.
 */
static inline int raspi_unprotect(void)
{
	u8 sr = 0;

	if (raspi_read_sr(&sr) < 0) {
		printk("%s: read_sr fail: %x\n", __func__, sr);
		return -1;
	}

	if ((sr & SR_BP0) == SR_BP0) {
		sr = 0;
		raspi_write_sr(&sr);
		return 1;
	}
	return 0;
}

/*
 * Service routine to read status register until ready, or timeout occurs.
 * Returns non-zero if error.
 */
static int raspi_wait_ready(int sleep_ms)
{
	int count;
	int sr;
	
	int timeout = sleep_ms * HZ / 1000;

	while (timeout) 
		timeout = schedule_timeout (timeout);

	/* one chip guarantees max 5 msec wait here after page writes,
	 * but potentially three seconds (!) after page erase.
	 */
	for (count = 0;  count < ((sleep_ms+1) *1000); count++) {
		if ((raspi_read_sr((u8 *)&sr)) < 0)
			break;
		else if (!(sr & (SR_WIP | SR_EPE))) {
			return 0;
		}

		udelay(500);
		/* REVISIT sometimes sleeping would be best */
	}

	printk("%s: read_sr fail :%x\n", __func__, sr);
	return -EIO;
}

static int raspi_wait_sleep_ready(int sleep_ms)
{
	int count;
	int sr = 0;

	/*int timeout = sleep_ms * HZ / 1000;
	while (timeout) 
		timeout = schedule_timeout (timeout);*/

	/* one chip guarantees max 5 msec wait here after page writes,
	 * but potentially three seconds (!) after page erase.
	 */
	for (count = 0; count < ((sleep_ms+1) *1000); count++) {
		if ((raspi_read_sr((u8 *)&sr)) < 0)
			break;
		else if (!(sr & (SR_WIP | SR_EPE))) {
			return 0;
		}

		usleep(1);
		/* REVISIT sometimes sleeping would be best */
	}

	printk("%s: read_sr fail: %x\n", __func__, sr);
	return -EIO;
}

/*
 * Erase one sector of flash memory at offset ``offset'' which is any
 * address within the sector which should be erased.
 *
 * Returns 0 if successful, non-zero otherwise.
 */
static int raspi_erase_sector(u32 offset)
{

	/* Wait until finished previous write command. */
	if (raspi_wait_ready(950))
		return -EIO;

	/* Send write enable, then erase commands. */
	raspi_write_enable();
	if (raspi_unprotect() == 1) {
	/* If unprotecting process is done, WEL bit is resed, need to enable it again. Leon */
		raspi_write_enable();
	}

	bbu_spic_trans(STM_OP_SECTOR_ERASE, offset, NULL, 4, 0, 0);
	//raspi_wait_ready(950);
	raspi_wait_sleep_ready(950);

	return 0;
}

int raspi_set_lock (struct mtd_info *mtd, loff_t to, size_t len, int set)
{
	u32 page_offset, page_size;
	int retval;

	/*  */
	while (len > 0) {
		/* write the next page to flash */
		flash->command[0] = (set == 0)? 0x39 : 0x36;
		flash->command[1] = to >> 16;
		flash->command[2] = to >> 8;
		flash->command[3] = to;

		raspi_wait_ready(1);
			
		raspi_write_enable();

		//retval = spic_write(flash->command, 4, 0, 0);
		retval = 0;
		
		if (retval < 0) {
			return -EIO;
		}
			
		page_offset = (to & (mtd->erasesize-1));
		page_size = mtd->erasesize - page_offset;
				
		len -= mtd->erasesize;
		to += mtd->erasesize;
	}

	return 0;
	
}


/****************************************************************************/

/*
 * MTD implementation
 */

/*
 * Erase an address range on the flash chip.  The address range may extend
 * one or more erase sectors.  Return an error is there is a problem erasing.
 */
static int ramtd_erase(struct mtd_info *mtd, struct erase_info *instr)
{
	u32 addr,len;
	u32 modevalue=0;
	//printk("%s: addr:%x len:%x\n", __func__, instr->addr, instr->len);
	/* sanity checks */
	if (instr->addr + instr->len > flash->mtd.size)
		return -EINVAL;
	//modevalue = (instr->addr)%(mtd->erasesize);
	
	//if (((instr->addr%mtd->erasesize) != 0) || ((instr->len%mtd->erasesize) != 0)) 
	if (modevalue)
	{
		return -EINVAL;
	}
	addr = instr->addr;
	len = instr->len;

  	down(&flash->lock);

	/* now erase those sectors */
	while (len) {
		if (raspi_erase_sector(addr)) {
			instr->state = MTD_ERASE_FAILED;
			up(&flash->lock);
			return -EIO;
		}

		addr += mtd->erasesize;
		len -= mtd->erasesize;
	}

  	up(&flash->lock);

	instr->state = MTD_ERASE_DONE;
	mtd_erase_callback(instr);
	return 0;
}

/*
 * Read an address range from the flash chip.  The address range
 * may be any size provided it is within the physical boundaries.
 */
static int ramtd_read(struct mtd_info *mtd, loff_t from, size_t len,
	size_t *retlen, u_char *buf)
{
	size_t rdlen = 0;

	//printk("%s: from:%llx len:%x\n", __func__, from, len);
	/* sanity checks */
	if (!len)
		return 0;

	if (from + len > flash->mtd.size)
		return -EINVAL;

	/* Byte count starts at zero. */
	if (retlen)
		*retlen = 0;

	down(&flash->lock);

	/* Wait till previous write/erase is done. */
	if (raspi_wait_ready(1)) {
		/* REVISIT status return?? */
		up(&flash->lock);
		return -EIO;
	}

	/* NOTE:  OPCODE_FAST_READ (if available) is faster... */

	do {
		int rc, more;
		#ifdef MORE_BUF_MODE
		more = 32;
		#else
		more = 4;
		#endif
		if (len - rdlen <= more) {
			#ifdef MORE_BUF_MODE
			rc = bbu_mb_spic_trans(STM_OP_RD_DATA, from, (buf+rdlen), 0, (len-rdlen), SPIC_READ_BYTES);
			#else
			rc = bbu_spic_trans(STM_OP_RD_DATA, from, (buf+rdlen), 4, (len-rdlen), SPIC_READ_BYTES);
			#endif
			if (rc != 0) {
				printk("%s: failed\n", __func__);
				break;
			}
			rdlen = len;
		}
		else {
			#ifdef MORE_BUF_MODE
			rc = bbu_mb_spic_trans(STM_OP_RD_DATA, from, (buf+rdlen), 0, more, SPIC_READ_BYTES);
			#else
			rc = bbu_spic_trans(STM_OP_RD_DATA, from, (buf+rdlen), 4, more, SPIC_READ_BYTES);
			#endif
			if (rc != 0) {
				printk("%s: failed\n", __func__);
				break;
			}
			rdlen += more;
			from += more;
		}
	} while (rdlen < len);
	
	
  	up(&flash->lock);

	if (retlen) 
		*retlen = rdlen;

	if (rdlen != len) 
		return -EIO;

	return 0;
}


inline int ramtd_lock (struct mtd_info *mtd, loff_t to, uint64_t len)
{
	return raspi_set_lock(mtd, to, len, 1);
}

inline int ramtd_unlock (struct mtd_info *mtd, loff_t to, uint64_t len)
{
	return raspi_set_lock(mtd, to, len, 0);
}


/*
 * Write an address range to the flash chip.  Data must be written in
 * FLASH_PAGESIZE chunks.  The address range may be any size provided
 * it is within the physical boundaries.
 */
static int ramtd_write(struct mtd_info *mtd, loff_t to, size_t len,
	size_t *retlen, const u_char *buf)
{
	u32 page_offset, page_size;
	int rc = 0;
	int wrto, wrlen, more;
	u_char *wrbuf;

	//printk("%s: to:%llx len:%x\n", __func__, to, len);
	if (retlen)
		*retlen = 0;

	/* sanity checks */
	if (!len)
		return(0);

	if (to + len > flash->mtd.size)
		return -EINVAL;


  	down(&flash->lock);

	/* Wait until finished previous write command. */
	if (raspi_wait_ready(2)) {
		up(&flash->lock);
		return -1;
	}

	/* what page do we start with? */
	page_offset = to % FLASH_PAGESIZE;

#ifdef MX_4B_MODE
	if (flash->chip->addr4b)
		raspi_4byte_mode(1);
#endif

	/* write everything in PAGESIZE chunks */
	while (len > 0) {
		page_size = min_t(size_t, len, FLASH_PAGESIZE-page_offset);
		page_offset = 0;

		raspi_wait_ready(3);
		raspi_write_enable();
		if (raspi_unprotect() == 1) {
		/* If unprotecting process is done, WEL bit is resed, need to enable it again. Leon */
			raspi_write_enable();
		}

		wrto = to;
		wrlen = page_size;
		wrbuf = (u_char *) buf;
		rc = wrlen;
		do {
		#ifdef MORE_BUF_MODE
			more = 32;
		#else
			more = 4;
		#endif
			if (wrlen <= more) {
				#ifdef MORE_BUF_MODE
				bbu_mb_spic_trans(STM_OP_PAGE_PGRM, wrto, wrbuf, wrlen, 0, SPIC_WRITE_BYTES);
				#else
				bbu_spic_trans(STM_OP_PAGE_PGRM, wrto, wrbuf, wrlen+4, 0, SPIC_WRITE_BYTES);
				#endif
				wrlen = 0;
			}
			else {
				#ifdef MORE_BUF_MODE
				bbu_mb_spic_trans(STM_OP_PAGE_PGRM, wrto, wrbuf, more, 0, SPIC_WRITE_BYTES);
				#else
				bbu_spic_trans(STM_OP_PAGE_PGRM, wrto, wrbuf, more+4, 0, SPIC_WRITE_BYTES);
				#endif
				wrto += more;
				wrlen -= more;
				wrbuf += more;
			}
			if (wrlen > 0) {
				raspi_wait_ready(3);
				raspi_write_enable();
			}
		} while (wrlen > 0);

		//printk("%s : to:%llx page_size:%x ret:%x\n", __func__, to, page_size, retval);

		if (rc > 0) {
			if (retlen)
				*retlen += rc;
				
			if (rc < page_size) {
				up(&flash->lock);
				printk("%s: retval:%x return:%x page_size:%x \n", 
				       __func__, rc, rc, page_size);

				return -EIO;
			}
		}
			
		len -= page_size;
		to += page_size;
		buf += page_size;
	}


	up(&flash->lock);

	return 0;
}


/****************************************************************************/

/*
 * SPI device driver setup and teardown
 */
struct chip_info *chip_prob(void)
{
	struct chip_info *info, *match;
	u8 buf[5];
	u32 jedec, weight;
	int i;

	raspi_read_deviceid(buf, 5);
	jedec = (u32)((u32)(buf[1] << 24) | ((u32)buf[2] << 16) | ((u32)buf[3] <<8) | (u32)buf[4]);

	printk("deice id : %x %x %x %x %x (%x)\n", buf[0], buf[1], buf[2], buf[3], buf[4], jedec);
	
	//++++ modify by siyou.	To prevent wrong match in same ic vendor.
	//weight = 0xffffffff;
	weight = 0;
	//---

	// FIXME, assign default as AT25D
	match = &chips_data[0];
	for (i = 0; i < ARRAY_SIZE(chips_data); i++) {
		info = &chips_data[i];
		if (info->id == buf[0]) {
		#if 1 //from ralink's code
			if ((u8)(info->jedec_id >> 24 & 0xff) == buf[1] &&
		        (u8)(info->jedec_id >> 16 & 0xff) == buf[2])
		#else
			if (info->jedec_id == jedec)
		#endif
				return info;

			if (weight > (info->jedec_id ^ jedec)) {
				weight = info->jedec_id ^ jedec;
				match = info;
			}
		}
	}
	printk("Warning: un-recognized chip ID, please update SPI driver!\n");

	return match;
}


/*
 * board specific setup should have ensured the SPI clock used here
 * matches what the READ command supports, at least until this driver
 * understands FAST_READ (for clocks over 25 MHz).
 */
static int __devinit raspi_prob(void)
{
	struct chip_info		*chip;
	unsigned			i;
char buf[128];
int off = elbox_partitions[1].offset;
size_t len;
struct squashfs_super_block * squashfsb;
struct packtag * ptag = NULL;
#ifdef CONFIG_ROOTFS_IN_FLASH_NO_PADDING
	loff_t offs;
	struct __image_header {
		uint8_t unused[60];
		uint32_t ih_ksz;
	} hdr;
#endif
#if defined(CONFIG_MTD_ANY_RALINK)	
	if(ra_check_flash_type()!=BOOT_FROM_SPI) { /* SPI */
	    return 0;
	}
#endif
printk(KERN_ERR "chip probe\n");
	chip = chip_prob();

printk(KERN_ERR "raspi_prob\n");

	flash = kzalloc(sizeof *flash, GFP_KERNEL);
	if (!flash)
		return -ENOMEM;

	init_MUTEX(&flash->lock);

	flash->chip = chip;
	flash->mtd.name = "raspi";

	flash->mtd.type = MTD_NORFLASH;
	flash->mtd.writesize = 1;
	flash->mtd.flags = MTD_CAP_NORFLASH;
	flash->mtd.size = chip->sector_size * chip->n_sectors;
	flash->mtd.erasesize = chip->sector_size;
	flash->mtd.erase = ramtd_erase;
	flash->mtd.read = ramtd_read;
	flash->mtd.write = ramtd_write;
	flash->mtd.lock = ramtd_lock;
	flash->mtd.unlock = ramtd_unlock;

	printk("%s(%02x %04x) (%lu Kbytes)\n", 
	       chip->name, chip->id, chip->jedec_id,(unsigned long)( flash->mtd.size / 1024));

	printk("mtd .name = %s, .size = 0x%.8lx (%luM) "
			".erasesize = 0x%.8lx (%luK) .numeraseregions = %d\n",
		flash->mtd.name,
		(unsigned long)flash->mtd.size, (unsigned long)(flash->mtd.size / (1024*1024)),
		(unsigned long)flash->mtd.erasesize, (unsigned long)(flash->mtd.erasesize / 1024),
		flash->mtd.numeraseregions);

	if (flash->mtd.numeraseregions)
		for (i = 0; i < flash->mtd.numeraseregions; i++)
			printk("mtd.eraseregions[%d] = { .offset = 0x%.8lx, "
				".erasesize = 0x%.8x (%uK), "
				".numblocks = %d }\n",
				i, (unsigned long)flash->mtd.eraseregions[i].offset,
				flash->mtd.eraseregions[i].erasesize,
				flash->mtd.eraseregions[i].erasesize / 1024,
				flash->mtd.eraseregions[i].numblocks);

#if defined (CONFIG_RT2880_ROOTFS_IN_FLASH) && defined (CONFIG_ROOTFS_IN_FLASH_NO_PADDING)
	offs = MTD_BOOT_PART_SIZE + MTD_CONFIG_PART_SIZE + MTD_FACTORY_PART_SIZE;
	ramtd_read(NULL, offs, sizeof(hdr), (size_t *)&i, (u_char *)(&hdr));
	if (hdr.ih_ksz != 0) {
		rt2880_partitions[4].size = ntohl(hdr.ih_ksz);
		rt2880_partitions[5].size = IMAGE1_SIZE - (MTD_BOOT_PART_SIZE +
				MTD_CONFIG_PART_SIZE + MTD_FACTORY_PART_SIZE +
				ntohl(hdr.ih_ksz));
	}
#endif
///////////////////////////////////////////
//
	/* Try to read the SEAMA header */
	memset(buf, 0xa5, sizeof(buf));
	if ((ramtd_read(NULL, off, sizeof(buf), &len, buf) == 0) && (len == sizeof(buf)))
	{
		
		imghdr2_t *hdrv2;
		seamahdr_t * seama;
		
		hdrv2 = (imghdr2_t *)buf;
		seama = (seamahdr_t *)buf;
		if(hdrv2->magic==IMG_V2_MAGIC_NO)
		{
			printk("v2 skip the header\n");
			off += (sizeof(imgblock_t)+sizeof(imghdr2_t));
		}else if(ntohl(seama->magic) == SEAMA_MAGIC)
		{
			printk("seama skip the header\n");
			/* We got SEAMA, the offset should be shift. */
			off += sizeof(seamahdr_t);
			if (ntohl(seama->size) > 0) off += 16;
			off += ntohs(seama->metasize);
		}
	}
	/* Looking for PACKIMG_TAG in the 64K boundary. */
	for (off += CONFIG_MTD_ELBOX_KERNEL_SKIP; off < CONFIG_MTD_ELBOX_PHYSMAP_LEN; off += (64*1024))
	{
		/* Find the tag. */
		memset(buf, 0xa5, sizeof(buf));
		if (ramtd_read(NULL, off, sizeof(buf), &len, buf) || len != sizeof(buf)) continue;
		if (memcmp(buf, PACKIMG_TAG, 12)) continue;
		/* We found the tag, check for the supported file system. */
		squashfsb = (struct squashfs_super_block *)(buf + sizeof(struct packtag));
		if (squashfsb->s_magic == SQUASHFS_MAGIC_LZMA || squashfsb->s_magic == SQUASHFS_MAGIC)
		{
			printk(KERN_NOTICE "xxxxxxxx: squashfs filesystem found at offset %d, magic %x \n",  off, squashfsb->s_magic);
			ptag = (struct packtag *)buf;
			elbox_partitions[0].offset = off + 32;
			elbox_partitions[0].size = ntohl(ptag->size);
			break;
			//return elbox_partitions;
		}
	}
	add_mtd_device(&flash->mtd);
	return add_mtd_partitions(&flash->mtd, elbox_partitions, ARRAY_SIZE(elbox_partitions));
}

static void __devexit raspi_remove(void)
{
	/* Clean up MTD stuff. */
	del_mtd_partitions(&flash->mtd);

	kfree(flash);
	
	flash = NULL;
}
int raspi_init(void)
{
	spic_init();
	return raspi_prob();
}
module_init(raspi_init);
module_exit(raspi_remove);

#if !defined (CONFIG_MTD_ELBOX_PHYSMAP)//this function also define in CONFIG_MTD_ELBOX_PHYSMAP,so CONFIG_MTD_ELBOX_PHYSMAP

/*********************************************************************/
/* Ralink driver support. Ralink's Wi-Fi will need to read/write the
 * flash for Radio config data. We append the functions here. */

int ra_mtd_write_nm(char *name, loff_t to, size_t len, const u_char *buf)
{
	int ret = -1;
	size_t rdlen, wrlen;
	struct mtd_info *mtd;
	struct erase_info ei;
	u_char *bak = NULL;

	mtd = get_mtd_device_nm(name);
	if (IS_ERR(mtd))
		return (int)mtd;
	if (len > mtd->erasesize) {
		put_mtd_device(mtd);
		return -E2BIG;
	}

	bak = kmalloc(mtd->erasesize, GFP_KERNEL);
	if (bak == NULL) {
		put_mtd_device(mtd);
		return -ENOMEM;
	}

	ret = mtd->read(mtd, 0, mtd->erasesize, &rdlen, bak);
	if (ret != 0) {
		put_mtd_device(mtd);
		kfree(bak);
		return ret;
	}
	if (rdlen != mtd->erasesize)
		printk("warning: ra_mtd_write: rdlen is not equal to erasesize\n");

	memcpy(bak + to, buf, len);

	ei.mtd = mtd;
	ei.callback = NULL;
	ei.addr = 0;
	ei.len = mtd->erasesize;
	ei.priv = 0;
	ret = mtd->erase(mtd, &ei);
	if (ret != 0) {
		put_mtd_device(mtd);
		kfree(bak);
		return ret;
	}

	ret = mtd->write(mtd, 0, mtd->erasesize, &wrlen, bak);

	put_mtd_device(mtd);
	kfree(bak);
	return ret;
}

int ra_mtd_read_nm(char *name, loff_t from, size_t len, u_char *buf)
{
	int ret;
	size_t rdlen;
	struct mtd_info *mtd;

	mtd = get_mtd_device_nm(name);
	if (IS_ERR(mtd))
		return (int)mtd;

	ret = mtd->read(mtd, from, len, &rdlen, buf);
	if (rdlen != len)
		printk("warning: ra_mtd_read_nm: rdlen is not equal to len\n");

	put_mtd_device(mtd);
	return ret;
}

EXPORT_SYMBOL(ra_mtd_write_nm);
EXPORT_SYMBOL(ra_mtd_read_nm); 
#endif

MODULE_LICENSE("GPL");
MODULE_AUTHOR("Mike Lavender");
MODULE_DESCRIPTION("MTD SPI driver for ST M25Pxx flash chips");
