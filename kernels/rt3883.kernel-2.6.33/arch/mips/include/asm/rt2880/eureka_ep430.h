#ifndef _EUREKA_EP430_H
#define _EUREKA_EP430_H


#include <asm/addrspace.h>		/* for KSEG1ADDR() */
#include <asm/byteorder.h>		/* for cpu_to_le32() */
#include <asm/rt2880/rt_mmap.h>


/*
 * Because of an error/peculiarity in the Galileo chip, we need to swap the
 * bytes when running bigendian.
 */

#define MV_WRITE(ofs, data)  \
        *(volatile u32 *)(RALINK_PCI_BASE+(ofs)) = cpu_to_le32(data)
#define MV_READ(ofs, data)   \
        *(data) = le32_to_cpu(*(volatile u32 *)(RALINK_PCI_BASE+(ofs)))
#define MV_READ_DATA(ofs)    \
        le32_to_cpu(*(volatile u32 *)(RALINK_PCI_BASE+(ofs)))

#define MV_WRITE_16(ofs, data)  \
        *(volatile u16 *)(RALINK_PCI_BASE+(ofs)) = cpu_to_le16(data)
#define MV_READ_16(ofs, data)   \
        *(data) = le16_to_cpu(*(volatile u16 *)(RALINK_PCI_BASE+(ofs)))

#define MV_WRITE_8(ofs, data)  \
        *(volatile u8 *)(RALINK_PCI_BASE+(ofs)) = data
#define MV_READ_8(ofs, data)   \
        *(data) = *(volatile u8 *)(RALINK_PCI_BASE+(ofs))

#define MV_SET_REG_BITS(ofs,bits) \
	(*((volatile u32 *)(RALINK_PCI_BASE+(ofs)))) |= ((u32)cpu_to_le32(bits))
#define MV_RESET_REG_BITS(ofs,bits) \
	(*((volatile u32 *)(RALINK_PCI_BASE+(ofs)))) &= ~((u32)cpu_to_le32(bits))

#define RALINK_PCI_CONFIG_ADDR 		    	0x20
#define RALINK_PCI_CONFIG_DATA_VIRTUAL_REG   	0x24

#if defined(CONFIG_RALINK_RT2880) || defined(CONFIG_RALINK_RT2883)
#define RALINK_PCI_PCICFG_ADDR 		*(volatile u32 *)(RALINK_PCI_BASE + 0x0000)
#define RALINK_PCI_PCIRAW_ADDR 		*(volatile u32 *)(RALINK_PCI_BASE + 0x0004)
#define RALINK_PCI_PCIINT_ADDR 		*(volatile u32 *)(RALINK_PCI_BASE + 0x0008)
#define RALINK_PCI_PCIMSK_ADDR 		*(volatile u32 *)(RALINK_PCI_BASE + 0x000C)
#define RALINK_PCI_BAR0SETUP_ADDR 	*(volatile u32 *)(RALINK_PCI_BASE + 0x0010)
#define RALINK_PCI_IMBASEBAR0_ADDR 	*(volatile u32 *)(RALINK_PCI_BASE + 0x0018)
#define RALINK_PCI_IMBASEBAR1_ADDR 	*(volatile u32 *)(RALINK_PCI_BASE + 0x001C)
#define RALINK_PCI_MEMBASE 		*(volatile u32 *)(RALINK_PCI_BASE + 0x0028)
#define RALINK_PCI_IOBASE 		*(volatile u32 *)(RALINK_PCI_BASE + 0x002C)
#define RALINK_PCI_ID 			*(volatile u32 *)(RALINK_PCI_BASE + 0x0030)
#define RALINK_PCI_CLASS 		*(volatile u32 *)(RALINK_PCI_BASE + 0x0034)
#define RALINK_PCI_SUBID 		*(volatile u32 *)(RALINK_PCI_BASE + 0x0038)
#define RALINK_PCI_ARBCTL 		*(volatile u32 *)(RALINK_PCI_BASE + 0x0080)
#define RALINK_PCI_STATUS		*(volatile u32 *)(RALINK_PCI_BASE + 0x0050)

#elif defined(CONFIG_RALINK_RT3883)

#define RALINK_PCI_PCICFG_ADDR 		*(volatile u32 *)(RALINK_PCI_BASE + 0x0000)
#define RALINK_PCI_PCIRAW_ADDR 		*(volatile u32 *)(RALINK_PCI_BASE + 0x0004)
#define RALINK_PCI_PCIINT_ADDR 		*(volatile u32 *)(RALINK_PCI_BASE + 0x0008)
#define RALINK_PCI_PCIMSK_ADDR 		*(volatile u32 *)(RALINK_PCI_BASE + 0x000C)
#define RALINK_PCI_IMBASEBAR1_ADDR 	*(volatile u32 *)(RALINK_PCI_BASE + 0x001C)
#define RALINK_PCI_MEMBASE 		*(volatile u32 *)(RALINK_PCI_BASE + 0x0028)
#define RALINK_PCI_IOBASE 		*(volatile u32 *)(RALINK_PCI_BASE + 0x002C)
#define RALINK_PCI_ARBCTL 		*(volatile u32 *)(RALINK_PCI_BASE + 0x0080)

/*
PCI0 --> PCI 
PCI1 --> PCIe
*/
#define RT3883_PCI_OFFSET	0x1000
#define RT3883_PCIE_OFFSET	0x2000

#define RALINK_PCI0_BAR0SETUP_ADDR 	*(volatile u32 *)(RALINK_PCI_BASE + RT3883_PCI_OFFSET + 0x0010)
#define RALINK_PCI0_IMBASEBAR0_ADDR 	*(volatile u32 *)(RALINK_PCI_BASE + RT3883_PCI_OFFSET + 0x0018)
#define RALINK_PCI0_ID 			*(volatile u32 *)(RALINK_PCI_BASE + RT3883_PCI_OFFSET + 0x0030)
#define RALINK_PCI0_CLASS 		*(volatile u32 *)(RALINK_PCI_BASE + RT3883_PCI_OFFSET + 0x0034)
#define RALINK_PCI0_SUBID 		*(volatile u32 *)(RALINK_PCI_BASE + RT3883_PCI_OFFSET + 0x0038)

#define RALINK_PCI1_BAR0SETUP_ADDR 	*(volatile u32 *)(RALINK_PCI_BASE + RT3883_PCIE_OFFSET + 0x0010)
#define RALINK_PCI1_IMBASEBAR0_ADDR 	*(volatile u32 *)(RALINK_PCI_BASE + RT3883_PCIE_OFFSET + 0x0018)
#define RALINK_PCI1_ID 			*(volatile u32 *)(RALINK_PCI_BASE + RT3883_PCIE_OFFSET + 0x0030)
#define RALINK_PCI1_CLASS 		*(volatile u32 *)(RALINK_PCI_BASE + RT3883_PCIE_OFFSET + 0x0034)
#define RALINK_PCI1_SUBID 		*(volatile u32 *)(RALINK_PCI_BASE + RT3883_PCIE_OFFSET + 0x0038)
#define RALINK_PCI1_STATUS		*(volatile u32 *)(RALINK_PCI_BASE + RT3883_PCIE_OFFSET + 0x0050)

#elif defined(CONFIG_RALINK_RT3052) || defined(CONFIG_RALINK_RT3352) || defined(CONFIG_RALINK_RT5350)
#else
#error "undefined in PCI"
#endif

#endif
