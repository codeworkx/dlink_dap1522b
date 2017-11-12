/*

*/

#ifndef	__ALPHA_MODULE_H__
#define	__ALPHA_MODULE_H__

#define PHYS_TO_K1(physaddr) KSEG1ADDR(physaddr)
typedef unsigned int RA2880_REG;
#define sysRegRead(phys)        \
        (*(volatile RA2880_REG *)PHYS_TO_K1(phys))
#define sysRegWrite(phys, val)  \
        ((*(volatile RA2880_REG *)PHYS_TO_K1(phys)) = (val))
        

//for hw nat
#define HNAT_BASEADDR	0x10100400
#define WAN_TX_BYTE 	0	//LAN_RX == WAN_TX
#define WAN_TX_COUNT 	1	//LAN_RX == WAN_TX 
#define LAN_TX_BYTE 	2	//WAN_RX == LAN_TX
#define LAN_TX_COUNT 	3	//WAN_RX == LAN_TX

#define ONE_SECOND_TICKS	1000000
int AlphaTxRxStatsInit(void);
void AlphaTxRxStatsDeinit(void);
unsigned long long get_hwnat_ctr(char *devname, int type);

#endif //__ALPHA_MODULE_H__
