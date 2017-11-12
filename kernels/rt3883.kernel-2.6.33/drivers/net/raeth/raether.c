#include <linux/module.h>
#include <linux/version.h>
#include <linux/kernel.h>
#include <linux/types.h>
#include <linux/pci.h>
#include <linux/init.h>
#include <linux/interrupt.h>
#include <linux/skbuff.h>
#include <linux/if_vlan.h>
#include <linux/if_ether.h>
#include <asm/uaccess.h>
#include <asm/rt2880/surfboardint.h>

#if LINUX_VERSION_CODE > KERNEL_VERSION(2,6,0)
#include <asm/rt2880/rt_mmap.h>
#else
#include <linux/libata-compat.h>
#endif

#include "ra2882ethreg.h"
#include "raether.h"
#include "ra_mac.h"
#include "ra_ioctl.h"
#ifdef CONFIG_RAETH_NETLINK
#include "ra_netlink.h"
#endif
#if defined (CONFIG_RAETH_QOS)
#include "ra_qos.h"
#endif

#if defined (CONFIG_RA_HW_NAT) || defined (CONFIG_RA_HW_NAT_MODULE)
#include "../../../net/nat/hw_nat/ra_nat.h"
#endif

//+++ for pppoe case. add by siyou. 2011/3/3 04:53pm
// when wan mode is pppoe, the headroom seem not enough for pppoe+vlan+ether header.
// it will cause there is only one way hw nat.
// after enlarge the headroom, the two ways traffic now is all in hw nat.
//------------------------------------------
// update info by siyou 2011/3/10 02:50pm
// the root cause is found & is written in redmine
// please use keyword "hw nat pppoe" to search.
#if defined (CONFIG_RA_HW_NAT) || defined (CONFIG_RA_HW_NAT_MODULE)
#define HW_NAT_HEADROOM	64
#else
#define HW_NAT_HEADROOM	0
#endif


#ifdef CONFIG_RAETH_NAPI
#define ALPHA_MOD_ISR_FOR_NAPI 1//marco
static int raeth_clean(struct napi_struct *napi, int budget);
static int rt2880_eth_recv(struct net_device* dev, int *work_done, int work_to_do);
#else
static int rt2880_eth_recv(struct net_device* dev);
#endif

#if !defined(CONFIG_RA_NAT_NONE) 
/* bruce+
 */

//move from net/nat/foe_hook/hook.c by siyou.
//that's crazy to put these definitions in there.
int (*ra_sw_nat_hook_rx) (struct sk_buff * skb) = NULL;
int (*ra_sw_nat_hook_tx) (struct sk_buff * skb, int gmac_no) = NULL;

EXPORT_SYMBOL(ra_sw_nat_hook_rx);
EXPORT_SYMBOL(ra_sw_nat_hook_tx);
//extern int (*ra_sw_nat_hook_rx)(struct sk_buff *skb);
//extern int (*ra_sw_nat_hook_tx)(struct sk_buff *skb, int gmac_no);
#endif

#if defined(CONFIG_RA_CLASSIFIER)||defined(CONFIG_RA_CLASSIFIER_MODULE)
/* Qwert+
 */
#include <asm/mipsregs.h>
extern int (*ra_classifier_hook_rx)(struct sk_buff *skb, unsigned long cur_cycle);
#endif /* CONFIG_RA_CLASSIFIER */

#if defined (CONFIG_RALINK_RT3052_MP2)
int32_t mcast_rx(struct sk_buff * skb);
int32_t mcast_tx(struct sk_buff * skb);
#endif

/* Removed by David Hsieh <david_hsieh@alphanetworks.com> */
#if 0
#ifdef RA_MTD_RW_BY_NUM
int ra_mtd_read(int num, loff_t from, size_t len, u_char *buf);
#else
int ra_mtd_read_nm(char *name, loff_t from, size_t len, u_char *buf);
#endif
#endif

/* gmac driver feature set config */
#if defined (CONFIG_RAETH_NAPI) || defined (CONFIG_RAETH_QOS)
#undef DELAY_INT
#else
#define DELAY_INT	1
#endif

/* end of config */

#ifdef CONFIG_RAETH_JUMBOFRAME
#define	MAX_RX_LENGTH	4096
#elif defined (CONFIG_RTL_TAG_SUPPORT)
#define	MAX_RX_LENGTH	2048
#else
#define	MAX_RX_LENGTH	1536
#endif

//now there is no SA_INTERRUPT
#define SA_INTERRUPT IRQF_DISABLED

static struct sk_buff		*netrx_skbuf[NUM_RX_DESC];
struct net_device		*dev_raether;

static int rx_dma_owner_idx0;     /* Point to the next RXD DMA wants to use in RXD Ring#0.  */
static int rx_wants_alloc_idx0;   /* Point to the next RXD CPU wants to allocate to RXD Ring #0. */

static struct PDMA_rxdesc	*rx_ring;
static unsigned int		phy_rx_ring;

#if defined (CONFIG_ETHTOOL) && ( defined (CONFIG_RAETH_ROUTER) || defined (CONFIG_RT_3052_ESW) )
extern struct ethtool_ops	ra_ethtool_ops;
#endif

#ifdef CONFIG_RALINK_VISTA_BASIC
int is_switch_175c = 1;
#endif
//bruce debug
#if 1
void skb_dump(struct sk_buff* sk) {
        unsigned int i;

        printk("skb_dump: from %s with len %d (%d) headroom=%d tailroom=%d\n",
                sk->dev?sk->dev->name:"ip stack",sk->len,sk->truesize,
                skb_headroom(sk),skb_tailroom(sk));

        //for(i=(unsigned int)sk->head;i<=(unsigned int)sk->tail;i++) {
        for(i=(unsigned int)sk->head;i<=(unsigned int)sk->data+20;i++) {
                if((i % 20) == 0)
                        printk("\n");
                if(i==(unsigned int)sk->data) printk("{");
                //if(i==(unsigned int)sk->h.raw) printk("#");
                //if(i==(unsigned int)sk->nh.raw) printk("|");
                //if(i==(unsigned int)sk->mac.raw) printk("*");
                printk("%02x",*((unsigned char*)i));
                if(i==(unsigned int)sk->tail) printk("}");
        }
        printk("\n");
}
#endif

#if defined (CONFIG_GIGAPHY) || defined (CONFIG_GIGAPHY2) || defined (CONFIG_P5_MAC_TO_PHY_MODE)
int isMarvellGigaPHY(int ge)
{
	u32 phy_id0 = 0, phy_id1 = 0;

#ifdef CONFIG_GIGAPHY2
	if (ge == 2) {
		if (!mii_mgr_read(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR2, 2, &phy_id0)) {
			printk("\n Read PhyID 1 is Fail!!\n");
			phy_id0 =0;
		}
		if (!mii_mgr_read(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR2, 3, &phy_id1)) {
			printk("\n Read PhyID 1 is Fail!!\n");
			phy_id1 = 0;
		}
	}
	else
#endif
#ifdef CONFIG_GIGAPHY
	{
		if (!mii_mgr_read(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR, 2, &phy_id0)) {
			printk("\n Read PhyID 0 is Fail!!\n");
			phy_id0 =0;
		}
		if (!mii_mgr_read(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR, 3, &phy_id1)) {
			printk("\n Read PhyID 0 is Fail!!\n");
			phy_id1 = 0;
		}
	}
#endif
		;
	if ((phy_id0 == EV_MARVELL_PHY_ID0) && (phy_id1 == EV_MARVELL_PHY_ID1))
		return 1;
	return 0;
}

int isVtssGigaPHY(int ge)
{
	u32 phy_id0 = 0, phy_id1 = 0;

#ifdef CONFIG_GIGAPHY2
	if (ge == 2) {
		if (!mii_mgr_read(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR2, 2, &phy_id0)) {
			printk("\n Read PhyID 1 is Fail!!\n");
			phy_id0 =0;
		}
		if (!mii_mgr_read(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR2, 3, &phy_id1)) {
			printk("\n Read PhyID 1 is Fail!!\n");
			phy_id1 = 0;
		}
	}
	else
#endif
#ifdef CONFIG_GIGAPHY
	{
		if (!mii_mgr_read(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR, 2, &phy_id0)) {
			printk("\n Read PhyID 0 is Fail!!\n");
			phy_id0 =0;
		}
		if (!mii_mgr_read(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR, 3, &phy_id1)) {
			printk("\n Read PhyID 0 is Fail!!\n");
			phy_id1 = 0;
		}
	}
#endif
		;
	if ((phy_id0 == EV_VTSS_PHY_ID0) && (phy_id1 == EV_VTSS_PHY_ID1))
		return 1;
	return 0;
}
#endif	

/*
 * Set the hardware MAC address.
 */
static int ei_set_mac_addr(struct net_device *dev, void *p)
{
	END_DEVICE *ei_local=netdev_priv(dev);
	MAC_INFO *macinfo = (MAC_INFO*)ei_local->MACInfo;
	struct sockaddr *addr = p;

	printk("%s: set mac address\n", dev->name);
	memcpy(dev->dev_addr, addr->sa_data, dev->addr_len);
	
	if(netif_running(dev))
		return -EBUSY;

        ra2880MacAddressSet(macinfo, addr->sa_data);
	return 0;
}

#ifdef CONFIG_PSEUDO_SUPPORT
static int ei_set_mac2_addr(struct net_device *dev, void *p)
{
	END_DEVICE *ei_local=netdev_priv(dev);
	MAC_INFO *macinfo = (MAC_INFO*)ei_local->MACInfo;
	struct sockaddr *addr = p;

	printk("%s: set mac2 address\n", dev->name);

	memcpy(dev->dev_addr, addr->sa_data, dev->addr_len);
	
	if(netif_running(dev))
		return -EBUSY;

        ra2880Mac2AddressSet(macinfo, addr->sa_data);
	return 0;
}
#endif

void set_fe_pdma_glo_cfg(void)
{
        int fe_glo_cfg=0;
        int pdma_glo_cfg=0;

	pdma_glo_cfg = (RT2880_TX_WB_DDONE | RT2880_RX_DMA_EN | RT2880_TX_DMA_EN | PDMA_BT_SIZE_4DWORDS);
	sysRegWrite(PDMA_GLO_CFG, pdma_glo_cfg);

	//set 1us timer count in unit of clock cycle
	fe_glo_cfg = sysRegRead(FE_GLO_CFG);
	fe_glo_cfg &= ~(0xff << 8); //clear bit8-bit15

	/* Unit = MHz */
#if defined (CONFIG_RALINK_RT3352_ASIC)
	//Fix frame engine's clock at 125Mhz
	fe_glo_cfg |= (124 << 8);
#else
	fe_glo_cfg |= (((get_surfboard_sysclk()/1000000)) << 8); 
#endif

	sysRegWrite(FE_GLO_CFG, fe_glo_cfg);
}

int forward_config(struct net_device *dev)
{
	unsigned int	regVal, regCsg;
#ifdef CONFIG_PSEUDO_SUPPORT
	unsigned int	regVal2;
#endif
	regVal = sysRegRead(GDMA1_FWD_CFG);
	regCsg = sysRegRead(CDMA_CSG_CFG);
#ifdef CONFIG_PSEUDO_SUPPORT
	regVal2 = sysRegRead(GDMA2_FWD_CFG);
#endif

	//set unicast/multicast/broadcast frame to cpu
	regVal &= ~0xFFFF; 
#ifdef CONFIG_PSEUDO_SUPPORT
	regVal2 &= ~0xFFFF; 
#endif
	regCsg &= ~0x7;

#ifdef CONFIG_RAETH_CHECKSUM_OFFLOAD
	//enable ipv4 header checksum check
	regVal |= RT2880_GDM1_ICS_EN;
	regCsg |= RT2880_ICS_GEN_EN;
	
	//enable tcp checksum check
	regVal |= RT2880_GDM1_TCS_EN;
	regCsg |= RT2880_TCS_GEN_EN;
	
	//enable udp checksum check
	regVal |= RT2880_GDM1_UCS_EN;
	regCsg |= RT2880_UCS_GEN_EN;

#ifdef CONFIG_PSEUDO_SUPPORT
	regVal2 |= RT2880_GDM1_ICS_EN;
	regVal2 |= RT2880_GDM1_TCS_EN;
	regVal2 |= RT2880_GDM1_UCS_EN;
#endif

	dev->features |= NETIF_F_IP_CSUM;
#else
	//disable ipv4 header checksum check
	regVal &= ~RT2880_GDM1_ICS_EN;
	regCsg &= ~RT2880_ICS_GEN_EN;
	
	//disable tcp checksum check
	regVal &= ~RT2880_GDM1_TCS_EN;
	regCsg &= ~RT2880_TCS_GEN_EN;
	
	//disable udp checksum check
	regVal &= ~RT2880_GDM1_UCS_EN;
	regCsg &= ~RT2880_UCS_GEN_EN;

#ifdef CONFIG_PSEUDO_SUPPORT
	regVal2 &= ~RT2880_GDM1_ICS_EN;
	regVal2 &= ~RT2880_GDM1_TCS_EN;
	regVal2 &= ~RT2880_GDM1_UCS_EN;
#endif
#endif

#ifdef CONFIG_RAETH_JUMBOFRAME
	// enable jumbo frame
	regVal |= RT2880_GDM1_JMB_EN;
#ifdef CONFIG_PSEUDO_SUPPORT
	regVal2 |= RT2880_GDM1_JMB_EN;
#endif
/* Bouble - 20101209
  * When enable REALTEK switch's proprietary tag support,
  * switch will insert 8 bytes of tag data into ethernet packet.
  * If original ethernet frame size is 1514 bytes, after insert 8 bytes of data,
  * then the max packet size will be 1514 + 8 = 1522.
  * For RALINK "frame engine", it has one register to setup "forward" condition.
  * By default, it will drop packets that size over 1514 bytes.
  * So, some packets will be drop if after insert tag and it's size over 1514 bytes.
  * How to solve it?
  *   Setup register to receive jumbo frame.
  */
#elif defined (CONFIG_RTL_TAG_SUPPORT)
	// enable jumbo frame
	regVal |= RT2880_GDM1_JMB_EN;
	regVal &= ~0xf0000000; //clear bit28-bit31
	regVal |= (((MAX_RX_LENGTH/1024)&0xf) << 28);
#endif

	// test, if disable bit 16
	// regVal &= ~RT2880_GDM1_STRPCRC;

	sysRegWrite(GDMA1_FWD_CFG, regVal);
	sysRegWrite(CDMA_CSG_CFG, regCsg);
#ifdef CONFIG_PSEUDO_SUPPORT
	sysRegWrite(GDMA2_FWD_CFG, regVal2);
#endif

/*
 * 	PSE_FQ_CFG register definition -
 *
 * 	Define max free queue page count in PSE. (31:24)
 *	RT2883/RT3883 - 0xff908000 (255 pages)
 *	RT3052 - 0x80504000 (128 pages)
 *	RT2880 - 0x80504000 (128 pages)
 *
 * 	In each page, there are 128 bytes in each page.
 *
 *	23:16 - free queue flow control release threshold
 *	15:8  - free queue flow control assertion threshold
 *	7:0   - free queue empty threshold
 *
 *	The register affects QOS correctness in frame engine!
 */

#if defined(CONFIG_RALINK_RT2883) || defined(CONFIG_RALINK_RT3883)
	sysRegWrite(PSE_FQ_CFG, cpu_to_le32(INIT_VALUE_OF_RT2883_PSE_FQ_CFG));
#elif defined(CONFIG_RALINK_RT3352)
        /*use default value*/
#else
	sysRegWrite(PSE_FQ_CFG, cpu_to_le32(INIT_VALUE_OF_RT2880_PSE_FQFC_CFG));
#endif

	/*
	 *FE_RST_GLO register definition -
	 *Bit 0: PSE Rest
	 *Reset PSE after re-programming PSE_FQ_CFG.
	 */
	regVal = 0x1;
	sysRegWrite(FE_RST_GL, regVal);
	sysRegWrite(FE_RST_GL, 0);	// update for RSTCTL issue

	regCsg = sysRegRead(CDMA_CSG_CFG);
	//printk("CDMA_CSG_CFG = %0X\n",regCsg);
	regVal = sysRegRead(GDMA1_FWD_CFG);
	//printk("GDMA1_FWD_CFG = %0X\n",regVal);

#ifdef CONFIG_PSEUDO_SUPPORT
	regVal = sysRegRead(GDMA2_FWD_CFG);
	//printk("GDMA2_FWD_CFG = %0X\n",regVal);
#endif

	return 1;
}

static int rt2880_eth_setup(struct net_device *dev)
{

	int		i;
	unsigned int	regVal;
	END_DEVICE* ei_local = netdev_priv(dev);
#if defined (CONFIG_RAETH_QOS)
	int		j;
#endif

	while(1)
	{
		regVal = sysRegRead(PDMA_GLO_CFG);	
		if((regVal & RT2880_RX_DMA_BUSY))
		{
			printk("\n  RT2880_RX_DMA_BUSY !!! ");
			continue;
		}
		if((regVal & RT2880_TX_DMA_BUSY))
		{
			printk("\n  RT2880_TX_DMA_BUSY !!! ");
			continue;
		}
		break;
	}

#if defined (CONFIG_RAETH_QOS)
	for (i=0;i<NUM_TX_RINGS;i++){
		for (j=0;j<NUM_TX_DESC;j++){
			ei_local->skb_free[i][j]=0;
		}
                ei_local->free_idx[i]=0;
	}
	/*
	 * RT2880: 2 x TX_Ring, 1 x Rx_Ring
	 * RT2883: 4 x TX_Ring, 1 x Rx_Ring
	 * RT3883: 4 x TX_Ring, 1 x Rx_Ring
	 * RT3052: 4 x TX_Ring, 1 x Rx_Ring
	 */
	fe_tx_desc_init(dev, 0, 3, 1);
	if (ei_local->tx_ring0 == NULL) {
		printk("RAETH: tx ring0 allocation failed\n");
		return 0;
	}

	fe_tx_desc_init(dev, 1, 3, 1);
	if (ei_local->tx_ring1 == NULL) {
		printk("RAETH: tx ring1 allocation failed\n");
		return 0;
	}

//	printk("\nphy_tx_ring0 = %08x, tx_ring0 = %p, size: %d bytes\n", ei_local->phy_tx_ring0, ei_local->tx_ring0, sizeof(struct PDMA_txdesc));
//	printk("\nphy_tx_ring1 = %08x, tx_ring1 = %p, size: %d bytes\n", ei_local->phy_tx_ring1, ei_local->tx_ring1, sizeof(struct PDMA_txdesc));

#if defined (CONFIG_RALINK_RT2883) || defined (CONFIG_RALINK_RT3052) || defined (CONFIG_RALINK_RT3352) || defined(CONFIG_RALINK_RT3883)
	fe_tx_desc_init(dev, 2, 3, 1);
	if (ei_local->tx_ring2 == NULL) {
		printk("RAETH: tx ring2 allocation failed\n");
		return 0;
	}
	
	fe_tx_desc_init(dev, 3, 3, 1);
	if (ei_local->tx_ring3 == NULL) {
		printk("RAETH: tx ring3 allocation failed\n");
		return 0;
	}

//	printk("\nphy_tx_ring2 = %08x, tx_ring2 = %p, size: %d bytes\n", ei_local->phy_tx_ring2, ei_local->tx_ring2, sizeof(struct PDMA_txdesc));
//	printk("\nphy_tx_ring3 = %08x, tx_ring3 = %p, size: %d bytes\n", ei_local->phy_tx_ring3, ei_local->tx_ring3, sizeof(struct PDMA_txdesc));

#endif // CONFIG_RALINK_RT2883 || CONFIG_RALINK_RT3052 || CONFIG_RALINK_RT3352 || CONFIG_RALINK_RT3883 //
#else
	for (i=0;i<NUM_TX_DESC;i++){
		ei_local->skb_free[i]=0;
	}
	ei_local->free_idx =0;
    	ei_local->tx_ring0 = pci_alloc_consistent(NULL, NUM_TX_DESC * sizeof(struct PDMA_txdesc), &ei_local->phy_tx_ring0);
// 	printk("\nphy_tx_ring = 0x%08x, tx_ring = 0x%p\n", ei_local->phy_tx_ring0, ei_local->tx_ring0);
	
	for (i=0; i < NUM_TX_DESC; i++) {
		memset(&ei_local->tx_ring0[i],0,sizeof(struct PDMA_txdesc));
		ei_local->tx_ring0[i].txd_info2.LS0_bit = 1;
		ei_local->tx_ring0[i].txd_info2.DDONE_bit = 1;

	}
#endif // CONFIG_RAETH_QOS

//	printk("\nphy_rx_ring = 0x%08x, rx_ring = 0x%p\n",phy_rx_ring,rx_ring);
 
	/* Initial RX Ring */
	rx_ring = pci_alloc_consistent(NULL, NUM_RX_DESC * sizeof(struct PDMA_rxdesc), &phy_rx_ring);
	for (i = 0; i < NUM_RX_DESC; i++) {
		memset(&rx_ring[i],0,sizeof(struct PDMA_rxdesc));
	    	rx_ring[i].rxd_info2.DDONE_bit = 0;
		rx_ring[i].rxd_info2.LS0 = 1;
		rx_ring[i].rxd_info1.PDP0 = dma_map_single(NULL, skb_put(netrx_skbuf[i], 2), MAX_RX_LENGTH+2, PCI_DMA_FROMDEVICE);
	}


	rx_dma_owner_idx0 = 0;
	rx_wants_alloc_idx0 = (NUM_RX_DESC - 1);
	regVal = sysRegRead(PDMA_GLO_CFG);
	regVal &= 0x000000FF;
   	sysRegWrite(PDMA_GLO_CFG, regVal);
	regVal=sysRegRead(PDMA_GLO_CFG);

	/* Tell the adapter where the TX/RX rings are located. */
#if !defined (CONFIG_RAETH_QOS)
        sysRegWrite(TX_BASE_PTR0, phys_to_bus((u32) ei_local->phy_tx_ring0));
	sysRegWrite(TX_MAX_CNT0, cpu_to_le32((u32) NUM_TX_DESC));
	sysRegWrite(TX_CTX_IDX0, 0);
	sysRegWrite(PDMA_RST_CFG, RT2880_PST_DTX_IDX0);
#endif

	sysRegWrite(RX_BASE_PTR0, phys_to_bus((u32) phy_rx_ring));
	sysRegWrite(RX_MAX_CNT0,  cpu_to_le32((u32) NUM_RX_DESC));
	sysRegWrite(RX_CALC_IDX0, cpu_to_le32((u32) (NUM_RX_DESC - 1)));
	sysRegWrite(PDMA_RST_CFG, RT2880_PST_DRX_IDX0);

#if defined (CONFIG_RAETH_QOS)
	set_scheduler_weight();
	set_schedule_pause_condition();
	set_output_shaper();
#endif
	set_fe_pdma_glo_cfg();

	return 1;
}

#if! defined (CONFIG_RAETH_QOS)
static inline int rt2880_eth_send(struct net_device* dev, struct sk_buff *skb, int gmac_no)
{
	unsigned int	length=skb->len;
	END_DEVICE*	ei_local = netdev_priv(dev);
	unsigned long	tx_cpu_owner_idx0 = sysRegRead(TX_CTX_IDX0);
#ifdef CONFIG_PSEUDO_SUPPORT
	PSEUDO_ADAPTER *pAd;
#endif

	while(ei_local->tx_ring0[tx_cpu_owner_idx0].txd_info2.DDONE_bit == 0)
	{
		printk(KERN_ERR "%s: TX DMA is Busy !! TX desc is Empty!\n", dev->name);
#ifdef CONFIG_PSEUDO_SUPPORT
		if (gmac_no == 2) {
			if (ei_local->PseudoDev != NULL) {
				pAd = netdev_priv(ei_local->PseudoDev);
				pAd->stat.tx_errors++;
			}
		} else
#endif
			ei_local->stat.tx_errors++;
	}

	ei_local->tx_ring0[tx_cpu_owner_idx0].txd_info1.SDP0 = virt_to_phys(skb->data);
	ei_local->tx_ring0[tx_cpu_owner_idx0].txd_info2.SDL0 = length;
	ei_local->tx_ring0[tx_cpu_owner_idx0].txd_info4.PN = gmac_no; 
	ei_local->tx_ring0[tx_cpu_owner_idx0].txd_info4.QN = 3; 
	ei_local->tx_ring0[tx_cpu_owner_idx0].txd_info2.DDONE_bit = 0;

#ifdef CONFIG_RAETH_CHECKSUM_OFFLOAD
	ei_local->tx_ring0[tx_cpu_owner_idx0].txd_info4.TCO = 1; 
	ei_local->tx_ring0[tx_cpu_owner_idx0].txd_info4.UCO = 1; 
	ei_local->tx_ring0[tx_cpu_owner_idx0].txd_info4.ICO = 1; 
#endif

#if defined (CONFIG_RA_HW_NAT) || defined (CONFIG_RA_HW_NAT_MODULE) 
	if(FOE_MAGIC_TAG(skb) == FOE_MAGIC_PPE) {
	    ei_local->tx_ring0[tx_cpu_owner_idx0].txd_info4.PN = 6; /* PPE */
	} 
//+++ siyou, sdk3520 no following lines.
#if 0
#if defined (CONFIG_RALINK_RT2880) || defined (CONFIG_RALINK_RT3052) || defined (CONFIG_RALINK_RT3352)
	ei_local->tx_ring0[tx_cpu_owner_idx0].txd_info4.RXIF = FOE_ALG_RXIF(skb); /* 0: WLAN, 1: PCI */
#else
	ei_local->tx_ring0[tx_cpu_owner_idx0].txd_info4.UDF = FOE_UDF(skb); 
#endif
#endif
//---
#endif //CONFIG_RA_HW_NAT

    	tx_cpu_owner_idx0 = (tx_cpu_owner_idx0+1) % NUM_TX_DESC;
	sysRegWrite(TX_CTX_IDX0, cpu_to_le32((u32)tx_cpu_owner_idx0));
  	
#ifdef CONFIG_PSEUDO_SUPPORT
	if (gmac_no == 2) {
		if (ei_local->PseudoDev != NULL) {
			pAd = netdev_priv(ei_local->PseudoDev);
			pAd->stat.tx_packets++;
			pAd->stat.tx_bytes += length;
		}
	} else
#endif
	{
		ei_local->stat.tx_packets++;
		ei_local->stat.tx_bytes += length;
	}
#ifdef CONFIG_RAETH_NAPI
	if ( ei_local->tx_full == 1) {
		ei_local->tx_full = 0;
		netif_wake_queue(dev);
	}
#endif

	return length;
}
#endif

#ifdef IGMP_SNOOP
typedef void (*igmp_funp)(struct sk_buff *,int);
igmp_funp igmp_snoop_input;
void register_igmp_callback(igmp_funp fun)
{	
	igmp_snoop_input = fun;
}
void unregister_igmp_callback(void)
{
	igmp_snoop_input = NULL;
}
#endif

#ifdef CONFIG_RTL_TAG_SUPPORT
/* bouble - 20101208 - 
  * For realtek switch(rtl8367R/rtl8367RB), it's proprietary tag length is 8 bytes. 
  * It's format will be:
  * 		Ether type: 2 bytes
  *          Protocol+ reason field: 2 bytes
  *          Priority : 2 bytes
  *		Incoming port index: 2 bytes
  */
#define RTL_TAG_LEN		8
/* a switch for switch port wifi priority */
int gSwPortWiFiPriEnable=0;
/* Each incoming switch port's priority */
u_int16_t gSwPortPriMap[8]={0, 0, 0, 0, 0, 0, 0, 0 };

//#define RTL_TAG_DEBUG
#ifdef RTL_TAG_DEBUG
#define DEBUGPRINT	printk
#else
#define DEBUGPRINT(x, ...)
#endif


void rtl_tag_process(struct sk_buff *skb)
{
	unsigned short  pkt_priority;
	unsigned short  portid;
#ifdef RTL_TAG_DEBUG
	static int pkt_counter=0;
	int i;
#endif

	/* Get packet's incoming switch port index */
	portid = ntohs(*(unsigned short *) &skb->data[18]);

	/* Get this packet's priority */
	if ( portid >= 0 && portid <= 7 )
		pkt_priority=gSwPortPriMap[portid];
	else
		pkt_priority=0;

	/* Set this packet's priority when enable this feature*/
	if ( gSwPortWiFiPriEnable == 1 )
	skb->priority = pkt_priority;

	/* Strip realtek's switch proprietary tag */
	memmove(skb->data + RTL_TAG_LEN , 	skb->data , 12);
	skb_pull(skb, RTL_TAG_LEN );


#ifdef RTL_TAG_DEBUG
	pkt_counter++;
	DEBUGPRINT("%d skb->priority=%d   skb->len=%04x (%d)\n", pkt_counter, skb->priority, skb->len & 0xFFFF, skb->len);
	for ( i=0; i< skb->len ; i++ )
	{
		if ( i % 16 == 0 )
			DEBUGPRINT("\n0x%04x: ", i);
		DEBUGPRINT("%02x", skb->data[i]);
	}
	DEBUGPRINT("\n\n");
#endif	
}
#endif


#ifdef CONFIG_RAETH_NAPI
static int rt2880_eth_recv(struct net_device* dev, int *work_done, int work_to_do)
#else
static int rt2880_eth_recv(struct net_device* dev)
#endif
{	
	struct sk_buff	*skb, *rx_skb;
	unsigned int	length = 0;
	unsigned long	RxProcessed;
	int bReschedule = 0;
	END_DEVICE* 	ei_local = netdev_priv(dev);
#if !defined(CONFIG_RA_NAT_NONE) 
	unsigned long flags;
#endif
#ifdef CONFIG_PSEUDO_SUPPORT
	PSEUDO_ADAPTER *pAd;
#endif

	RxProcessed = 0;

	for ( ; ; ) { 

#ifdef CONFIG_RAETH_NAPI
                if(*work_done >= work_to_do)
                        break;
                (*work_done)++;
#else
		if (RxProcessed++ > NUM_RX_MAX_PROCESS)
                {
                        // need to reschedule rx handle
                        bReschedule = 1;
                        break;
                }                                      
#endif
		

		/* Update to Next packet point that was received.
		 */ 
		rx_dma_owner_idx0 = (sysRegRead(RX_CALC_IDX0) + 1) % NUM_RX_DESC;
	
		if (rx_ring[rx_dma_owner_idx0].rxd_info2.DDONE_bit == 0)  {
			break;
		}


		/* skb processing */
		length = rx_ring[rx_dma_owner_idx0].rxd_info2.PLEN0;
		rx_skb = netrx_skbuf[rx_dma_owner_idx0];
		rx_skb->len 	= length;
		rx_skb->data	= netrx_skbuf[rx_dma_owner_idx0]->data;
		rx_skb->tail 	= rx_skb->data + length;

//printk("000....rx_skb: data[0]=%02x h_proto=%02x\n", rx_skb->data[0], ((struct ethhdr*)rx_skb->data)->h_proto);

#ifdef CONFIG_PSEUDO_SUPPORT
		if(rx_ring[rx_dma_owner_idx0].rxd_info4.SP == 2) {
		    if(ei_local->PseudoDev!=NULL) {
			rx_skb->dev 	  = ei_local->PseudoDev;
			rx_skb->protocol  = eth_type_trans(rx_skb,ei_local->PseudoDev);
		    }else {
			printk("ERROR: PseudoDev is still not initialize but receive packet from GMAC2\n");
		    }
		}else{
		    rx_skb->dev 	  = dev;
		    rx_skb->protocol	  = eth_type_trans(rx_skb,dev);
		}
#else
		rx_skb->dev 	  = dev;

#ifdef CONFIG_RTL_TAG_SUPPORT
// For Jumbo frame bug that will make system crash and restart.
// After discussion, we decide to filter out the packet lengh 
// over MAX_RX_LENGTH.
// by Freddy
		if(length > MAX_RX_LENGTH)
		{
#ifdef CONFIG_PSEUDO_SUPPORT
			if (rx_ring[rx_dma_owner_idx0].rxd_info4.SP == 2) {
				if (ei_local->PseudoDev != NULL) {
					pAd = netdev_priv(ei_local->PseudoDev);
					pAd->stat.rx_dropped++;
				}
			} 
			else
#endif
			ei_local->stat.rx_dropped++;
			rx_ring[rx_dma_owner_idx0].rxd_info2.DDONE_bit = 0;
			sysRegWrite(RX_CALC_IDX0, rx_dma_owner_idx0);
			continue;
		}

		/* Process realtek switch( RTL8367R/RTL8367RB) proprietary tag. */ 
		if (*(unsigned short *) &rx_skb->data[12] == htons(0x8899))
			rtl_tag_process(rx_skb);				   		
#endif

#ifdef IGMP_SNOOP 																		
		//note: don't put below code after eth_type_trans(), because pointer of origin data will be changed. sam_pan porting 20101206											
		if(igmp_snoop_input) igmp_snoop_input(rx_skb, -1);		
#endif	

		rx_skb->protocol  = eth_type_trans(rx_skb,dev);
#endif

#if 0
#ifdef CONFIG_RAETH_CHECKSUM_OFFLOAD
		//rx_skb->ip_summed = CHECKSUM_UNNECESSARY; 
		rx_skb->ip_summed = CHECKSUM_NONE;//add by rbj for ipv6 
#else
		rx_skb->ip_summed = CHECKSUM_NONE; 
#endif
#endif

#ifdef CONFIG_RAETH_CHECKSUM_OFFLOAD
		if(rx_ring[rx_dma_owner_idx0].rxd_info4.IPFVLD_bit){
			rx_skb->ip_summed = CHECKSUM_UNNECESSARY;
		}
		else 
#endif
		rx_skb->ip_summed = CHECKSUM_NONE; 

#ifdef CONFIG_RT2880_BRIDGING_ONLY
		rx_skb->cb[22]=0xa8;
#endif

#if defined(CONFIG_RA_CLASSIFIER)||defined(CONFIG_RA_CLASSIFIER_MODULE)
		/* Qwert+ 
		 */	
		if(ra_classifier_hook_rx!= NULL)
		{	
			ra_classifier_hook_rx(rx_skb, read_c0_count());
		}
#endif /* CONFIG_RA_CLASSIFIER */
	
#if defined (CONFIG_RA_HW_NAT)  || defined (CONFIG_RA_HW_NAT_MODULE)
		FOE_MAGIC_TAG(rx_skb)= FOE_MAGIC_GE;
		memcpy(FOE_INFO_START_ADDR(rx_skb)+2,&rx_ring[rx_dma_owner_idx0].rxd_info4, sizeof(PDMA_RXD_INFO4_T));
#endif

		/* We have to check the free memory size is big enough 
		 * before pass the packet to cpu*/
		skb = __dev_alloc_skb(MAX_RX_LENGTH + 2 + HW_NAT_HEADROOM , GFP_DMA | GFP_ATOMIC);
		if (skb == NULL)
		{
			printk(KERN_ERR "skb not available...\n");
#ifdef CONFIG_PSEUDO_SUPPORT
			if (rx_ring[rx_dma_owner_idx0].rxd_info4.SP == 2) {
				if (ei_local->PseudoDev != NULL) {
					pAd = netdev_priv(ei_local->PseudoDev);
					pAd->stat.rx_dropped++;
				}
			} else
#endif
				ei_local->stat.rx_dropped++;
                        bReschedule = 1;
            
            //+++ fix by siyou. 2010/7/30 03:26pm
            //without following code, the ether hw interrupt will not happen again
            //when too many fail of alloc skb buff.
            rx_ring[rx_dma_owner_idx0].rxd_info2.DDONE_bit = 0;	
            sysRegWrite(RX_CALC_IDX0, rx_dma_owner_idx0);
            //---
			break;
		}
		skb_reserve(skb, 2 + HW_NAT_HEADROOM);
		
#if !defined(CONFIG_RA_NAT_NONE) 
/* bruce+
 * ra_sw_nat_hook_rx return 1 --> continue
 * ra_sw_nat_hook_rx return 0 --> FWD & without netif_rx
 */
         if(ra_sw_nat_hook_rx!= NULL)
         { //sdk3520 do not use lock. siyou
	   //spin_lock_irqsave(&ei_local->page_lock, flags);
           if(ra_sw_nat_hook_rx(rx_skb)) {
#if defined (CONFIG_RALINK_RT3052_MP2)
	       if(mcast_rx(rx_skb)==0) {
		   kfree_skb(rx_skb);
	       }else 
#endif
#ifdef CONFIG_RAETH_NAPI
                netif_receive_skb(rx_skb);
#else
                netif_rx(rx_skb);
#endif
	   }
	   //spin_unlock_irqrestore(&ei_local->page_lock, flags);
         } else {
#if defined (CONFIG_RALINK_RT3052_MP2)
	     if(mcast_rx(rx_skb)==0) {
		 kfree_skb(rx_skb);
	     }else 
#endif
#ifdef CONFIG_RAETH_NAPI
                netif_receive_skb(rx_skb);
#else
                netif_rx(rx_skb);
#endif // CONFIG_RAETH_NAPI //
	 }
#else

#if defined (CONFIG_RALINK_RT3052_MP2)
	if(mcast_rx(rx_skb)==0) {
		kfree_skb(rx_skb);
	}else 
#endif // CONFIG_RALINK_RT3052_MP2 //
#ifdef CONFIG_RAETH_NAPI
                netif_receive_skb(rx_skb);
#else
                netif_rx(rx_skb);
#endif // CONFIG_RAETH_NAPI //


#endif  // CONFIG_RA_NAT_NONE //
		rx_ring[rx_dma_owner_idx0].rxd_info2.DDONE_bit = 0;	
		netrx_skbuf[rx_dma_owner_idx0] = skb;
		rx_ring[rx_dma_owner_idx0].rxd_info1.PDP0 = dma_map_single(NULL, skb->data, MAX_RX_LENGTH+2, PCI_DMA_FROMDEVICE);
		dma_cache_sync(NULL, &rx_ring[rx_dma_owner_idx0], sizeof(struct PDMA_rxdesc), DMA_FROM_DEVICE);

		/*  Move point to next RXD which wants to alloc*/
		sysRegWrite(RX_CALC_IDX0, rx_dma_owner_idx0);	
#ifdef CONFIG_PSEUDO_SUPPORT
		if (rx_ring[rx_dma_owner_idx0].rxd_info4.SP == 2) {
			if (ei_local->PseudoDev != NULL) {
				pAd = netdev_priv(ei_local->PseudoDev);
				pAd->stat.rx_packets++;
				pAd->stat.rx_bytes += length;
			}
		} else
#endif
		{
			ei_local->stat.rx_packets++;
			ei_local->stat.rx_bytes += length;	
		}
	}	/* for */


	return bReschedule;
}



///////////////////////////////////////////////////////////////////
/////
///// ra_get_stats - gather packet information for management plane
/////
///// Pass net_device_stats to the upper layer.
///// 
/////
///// RETURNS: pointer to net_device_stats
///////////////////////////////////////////////////////////////////

struct net_device_stats *ra_get_stats(struct net_device *dev)
{
	END_DEVICE *ei_local = netdev_priv(dev);
	return &ei_local->stat;
}

///////////////////////////////////////////////////////////////////
/////
///// ra2880Recv - process the next incoming packet
/////
///// Handle one incoming packet.  The packet is checked for errors and sent
///// to the upper layer.
/////
///// RETURNS: OK on success or ERROR.
///////////////////////////////////////////////////////////////////

#ifndef CONFIG_RAETH_NAPI
#ifdef WORKQUEUE_BH
void ei_receive_workq(struct work_struct *work)
#else
void ei_receive(unsigned long unused)  // device structure 
#endif // WORKQUEUE_BH //
{
	unsigned long flags;
	struct net_device *dev = dev_raether;
	END_DEVICE *ei_local = netdev_priv(dev);
	unsigned long reg_int_mask=0;
	int bReschedule=0;

	spin_lock_irqsave(&(ei_local->page_lock), flags);

	bReschedule = rt2880_eth_recv(dev);
	if(bReschedule)
        {
#ifdef WORKQUEUE_BH
		schedule_work(&ei_local->rx_wq);
#else
		tasklet_hi_schedule(&ei_local->rx_tasklet);
#endif // WORKQUEUE_BH //
        }else{
    		reg_int_mask=sysRegRead(FE_INT_ENABLE);
#if defined(DELAY_INT)
                sysRegWrite(FE_INT_ENABLE, reg_int_mask|RT2880_RX_DLY_INT);
#else
                sysRegWrite(FE_INT_ENABLE, reg_int_mask|RT2880_RX_DONE_INT0);
#endif
        }
	spin_unlock_irqrestore(&(ei_local->page_lock), flags);
}
#endif

#ifdef CONFIG_RAETH_NAPI
static int
raeth_clean(struct napi_struct *napi, int budget)
{
	END_DEVICE *ei_local = container_of(napi, END_DEVICE, napi);
	//unsigned int reg_int_val;
    int work_to_do = budget;
    //int tx_cleaned;
    int work_done = 0;
	unsigned long reg_int_mask=0;


#ifdef WORKQUEUE_BH
	schedule_work(&ei_local->tx_wq);
#else

#ifndef ALPHA_MOD_ISR_FOR_NAPI //marco, I move the housekeeping to txtasklet
	ei_xmit_housekeeping(0);
#endif//ALPHA_MOD_ISR_FOR_NAPI
	
#endif // WORKQUEUE_BH //

	rt2880_eth_recv(ei_local->dev, &work_done, work_to_do);

        /* if no Tx and not enough Rx work done, exit the polling mode */
        if(( (work_done < work_to_do)) || !netif_running(ei_local->dev)) {
        	atomic_dec_and_test(&ei_local->irq_sem);
			napi_complete(napi);

#ifndef ALPHA_MOD_ISR_FOR_NAPI //marco
			sysRegWrite(FE_INT_STATUS, FE_INT_ALL);		// ack all fe interrupts
#endif//ALPHA_MOD_ISR_FOR_NAPI 
			
    		reg_int_mask=sysRegRead(FE_INT_ENABLE);
#ifdef DELAY_INT
			sysRegWrite(FE_INT_ENABLE, reg_int_mask |FE_INT_DLY_INIT);  // init delay interrupt only
#else//DELAY_INT

#ifdef ALPHA_MOD_ISR_FOR_NAPI //marco, juest enable RX intr.
			sysRegWrite(FE_INT_ENABLE,reg_int_mask|RT2880_RX_DONE_INT0 );
#else//ALPHA_MOD_ISR_FOR_NAPI

			sysRegWrite(FE_INT_ENABLE,reg_int_mask|RT2880_RX_DONE_INT0 \
			    	      		|RT2880_TX_DONE_INT0 \
				      		|RT2880_TX_DONE_INT1 \
				      		|RT2880_TX_DONE_INT2 \
				      		|RT2880_TX_DONE_INT3);
			
#endif//ALPHA_MOD_ISR_FOR_NAPI
#endif//DELAY_INT
        }

        return work_done;
}

#endif


/**
 * ei_interrupt - handle controler interrupt
 *
 * This routine is called at interrupt level in response to an interrupt from
 * the controller.
 *
 * RETURNS: N/A.
 */
#if LINUX_VERSION_CODE >= KERNEL_VERSION(2,6,21)
static irqreturn_t ei_interrupt(int irq, void *dev_id)
#else
static irqreturn_t ei_interrupt(int irq, void *dev_id, struct pt_regs * regs)
#endif
{
#if !defined(CONFIG_RAETH_NAPI)
	unsigned long reg_int_val;
	unsigned long reg_int_mask=0;
	unsigned int recv = 0;
	unsigned int transmit = 0;
	unsigned long flags; 
#endif 

	struct net_device *dev = (struct net_device *) dev_id;
	END_DEVICE *ei_local = netdev_priv(dev);
	
	//Qwert
	/*
	unsigned long old,cur,dcycle;
	static int cnt = 0; 
	static unsigned long max_dcycle = 0,tcycle = 0;
	old = read_c0_count();
	*/
	if (dev == NULL) 
	{
		printk (KERN_ERR "net_interrupt(): irq %x for unknown device.\n", RT2880_IRQ_ENET0);
		return IRQ_NONE;
	}

#ifdef CONFIG_RAETH_NAPI
		/*
        if(netif_rx_schedule_prep(dev)) {
                atomic_inc(&ei_local->irq_sem);
		sysRegWrite(FE_INT_ENABLE, 0);
                __netif_rx_schedule(dev);
        }
		*/
#ifdef ALPHA_MOD_ISR_FOR_NAPI //marco
	unsigned long reg_int_val;
	unsigned long reg_int_mask=0;
	unsigned int recv = 0;
	unsigned int transmit = 0;
	unsigned long flags; 
	spin_lock_irqsave(&(ei_local->page_lock), flags);
	reg_int_val = sysRegRead(FE_INT_STATUS);
	reg_int_mask = sysRegRead(FE_INT_ENABLE);
	
	if((reg_int_val & RT2880_RX_DONE_INT0))
	{
		atomic_inc(&ei_local->irq_sem);
		sysRegWrite(FE_INT_ENABLE, reg_int_mask& ~(RT2880_RX_DONE_INT0));	
		//marco, I will enable RX intr. in polling function
		recv = 1;
	}

	if (reg_int_val & RT2880_TX_DONE_INT0)
	{
		sysRegWrite(FE_INT_ENABLE, reg_int_mask& ~(RT2880_TX_DONE_INT0	\
		    			   |RT2880_TX_DONE_INT1 \
					   |RT2880_TX_DONE_INT2 \
					   |RT2880_TX_DONE_INT3));	
		//marco, I will enable TX intr. in tx_tasklet
		tasklet_schedule(&ei_local->tx_tasklet);
	}

	sysRegWrite(FE_INT_STATUS, reg_int_val);	

	if( recv == 1)
	{
		napi_schedule(&ei_local->napi);
	}
	spin_unlock_irqrestore(&(ei_local->page_lock), flags);
#else//ALPHA_MOD_ISR_FOR_NAPI 
		atomic_inc(&ei_local->irq_sem);
		sysRegWrite(FE_INT_ENABLE, 0);
		napi_schedule(&ei_local->napi);
#endif//ALPHA_MOD_ISR_FOR_NAPI 

#else

	spin_lock_irqsave(&(ei_local->page_lock), flags);
	reg_int_val = sysRegRead(FE_INT_STATUS);

#if defined (DELAY_INT)
	if((reg_int_val & RT2880_RX_DLY_INT))
		recv = 1;

	if (reg_int_val & RT2880_TX_DLY_INT)
		transmit = 1;
#else
	if((reg_int_val & RT2880_RX_DONE_INT0))
		recv = 1;

	if (reg_int_val & RT2880_TX_DONE_INT0)
		transmit |= RT2880_TX_DONE_INT0;
#if defined (CONFIG_RAETH_QOS)
	if (reg_int_val & RT2880_TX_DONE_INT1)
		transmit |= RT2880_TX_DONE_INT1;
	if (reg_int_val & RT2880_TX_DONE_INT2)
		transmit |= RT2880_TX_DONE_INT2;
	if (reg_int_val & RT2880_TX_DONE_INT3)
		transmit |= RT2880_TX_DONE_INT3;
#endif //CONFIG_RAETH_QOS

#endif //DELAY_INT


	sysRegWrite(FE_INT_STATUS, reg_int_val);	

	if( recv == 1)
	{
		reg_int_mask = sysRegRead(FE_INT_ENABLE);	
#if defined (DELAY_INT)
		sysRegWrite(FE_INT_ENABLE, reg_int_mask& ~(RT2880_RX_DLY_INT));
#else
		sysRegWrite(FE_INT_ENABLE, reg_int_mask& ~(RT2880_RX_DONE_INT0));
#endif //DELAY_INT

#ifdef WORKQUEUE_BH
		schedule_work(&ei_local->rx_wq);
#else
		tasklet_hi_schedule(&ei_local->rx_tasklet);
#endif // WORKQUEUE_BH //
	}	

	if ( transmit != 0)  
#ifdef WORKQUEUE_BH
		schedule_work(&ei_local->tx_wq);
#else
		ei_xmit_housekeeping(0);
#endif // WORKQUEUE_BH //
	
	//Qwert
	/*
	cur = read_c0_count();
	
	if(cur >= old)
	{
		dcycle = (cur-old)/192;
	}
	else
	{
		dcycle = (0xFFFFFFFF - (old - cur))/192;
	}
	
	if(max_dcycle < dcycle)
		max_dcycle = dcycle;
	tcycle+=dcycle;	
	cnt++;
	if(cnt==10000)
	{
		printk("avg=%d\n",tcycle/10000);		
		max_dcycle = 0;
		tcycle = 0;
		cnt = 0;
	}
	*/
	spin_unlock_irqrestore(&(ei_local->page_lock), flags);
#endif
	
	return IRQ_HANDLED;
}

#if defined (CONFIG_RALINK_RT3052) || defined (CONFIG_RALINK_RT3352)

static irqreturn_t esw_interrupt(int irq, void *dev_id)
{
	unsigned long flags; 
	unsigned long reg_int_val;
	struct net_device *dev = (struct net_device *) dev_id;
	static u32 stat;
	u32 stat_curr;
	struct file *fp;
	char pid[8];
	struct task_struct *p = NULL;

	END_DEVICE *ei_local = netdev_priv(dev);
	spin_lock_irqsave(&(ei_local->page_lock), flags);
	reg_int_val = (*((volatile u32 *)(RALINK_ETH_SW_BASE))); //Interrupt Status Register

	if (reg_int_val & PORT_ST_CHG) {
		printk("RT305x_ESW: Link Status Changed\n");

		//if port 0 link down --> link up
		stat_curr = *((volatile u32 *)(RALINK_ETH_SW_BASE+0x80));
#ifdef CONFIG_WAN_AT_P0
		if ((stat & (1<<25)) || !(stat_curr & (1<<25)))
#else
		if ((stat & (1<<29)) || !(stat_curr & (1<<29)))
#endif
			goto out;

		//read udhcpc pid from file, and send signal USR2,USR1 to get a new IP
		fp = filp_open("/var/run/udhcpc.pid", O_RDONLY, 0);
		if (IS_ERR(fp))
			goto out;
		if (fp->f_op && fp->f_op->read) {
			if (fp->f_op->read(fp, pid, 8, &fp->f_pos) > 0) {				
				p = find_task_by_pid(simple_strtoul(pid, NULL, 10));
				if (NULL != p) {
					send_sig(SIGUSR2, p, 0);
					send_sig(SIGUSR1, p, 0);
				}
			}
		}
		filp_close(fp, NULL);
out:
		stat = stat_curr;
	}
	sysRegWrite(RALINK_ETH_SW_BASE, reg_int_val);

	spin_unlock_irqrestore(&(ei_local->page_lock), flags);
	return IRQ_HANDLED;
}
#endif

static int ei_start_xmit(struct sk_buff* skb, struct net_device *dev, int gmac_no)
{
	END_DEVICE *ei_local = netdev_priv(dev);
	unsigned long flags;
	unsigned long tx_cpu_owner_idx;
	unsigned int tx_cpu_owner_idx_next;
#if	!defined(CONFIG_RAETH_QOS)
	struct PDMA_txdesc* tx_desc;
#else
	int ring_no, queue_no, port_no;
#endif
#ifdef CONFIG_RALINK_VISTA_BASIC
	struct vlan_ethhdr *veth;
#endif
#ifdef CONFIG_PSEUDO_SUPPORT
	PSEUDO_ADAPTER *pAd;
#endif
	
#if !defined(CONFIG_RA_NAT_NONE)
/* bruce+
 */
         if(ra_sw_nat_hook_tx!= NULL)
         {
	   spin_lock_irqsave(&ei_local->page_lock, flags);
           if(ra_sw_nat_hook_tx(skb, gmac_no)==1){
	   	spin_unlock_irqrestore(&ei_local->page_lock, flags);
	   }else{
	        kfree_skb(skb);
	   	spin_unlock_irqrestore(&ei_local->page_lock, flags);
	   	return 0;
	   }
         }
#endif

#if defined (CONFIG_RALINK_RT3052_MP2)
	mcast_tx(skb);
#endif

#ifdef IGMP_SNOOP					
	if(igmp_snoop_input) igmp_snoop_input(skb, 1/*set value > 0, send packet to cpu port*/);									
#endif

#if defined (CONFIG_RT_3052_ESW) || defined (CONFIG_RALINK_RT2883)
#define MIN_PKT_LEN  64
	 if (skb->len < MIN_PKT_LEN) {
	     if (skb_padto(skb, MIN_PKT_LEN)) {
		 printk("raeth: skb_padto failed\n");
		 return 0;
	     }
	     skb_put(skb, MIN_PKT_LEN - skb->len);
	 }
#endif //CONFIG_RT_3052_ESW

	dev->trans_start = jiffies;	/* save the timestamp */
	spin_lock_irqsave(&ei_local->page_lock, flags);
#if defined( CONFIG_RT2880_ENHANCE) || defined (CONFIG_RT2880_BRIDGING_ONLY)
	if ((unsigned char)skb->cb[22] == 0xa9)
		dma_cache_sync(dev, skb->data, 60, DMA_TO_DEVICE);
	else if ((unsigned char)skb->cb[22] == 0xa8) {
		dma_cache_sync(dev, skb->data, 16, DMA_TO_DEVICE);
	}
	else
		dma_cache_sync(dev, skb->data, skb->len, DMA_TO_DEVICE);
#else
	dma_cache_sync(NULL, skb->data, skb->len, DMA_TO_DEVICE);
#endif
	
#ifdef CONFIG_RALINK_VISTA_BASIC
	veth = (struct vlan_ethhdr *)(skb->data);
	if (is_switch_175c && veth->h_vlan_proto == __constant_htons(ETH_P_8021Q)) {
		if ((veth->h_vlan_TCI & __constant_htons(VLAN_VID_MASK)) == 0) {
			veth->h_vlan_TCI |= htons(VLAN_DEV_INFO(dev)->vlan_id);
		}
	}
#endif

#if defined (CONFIG_RAETH_QOS)
	if(pkt_classifier(skb, gmac_no, &ring_no, &queue_no, &port_no)) {
		get_tx_ctx_idx(ring_no, &tx_cpu_owner_idx);

	if(tx_cpu_owner_idx== NUM_TX_DESC-1)
		tx_cpu_owner_idx_next = 0;
	else
		tx_cpu_owner_idx_next = tx_cpu_owner_idx +1;


	  if(((ei_local->skb_free[ring_no][tx_cpu_owner_idx]) ==0) && (ei_local->skb_free[ring_no][tx_cpu_owner_idx_next]==0)){
	    fe_qos_packet_send(dev, skb, ring_no, queue_no, port_no);
	  }else{
	    ei_local->stat.tx_dropped++;
	    kfree_skb(skb);
	    spin_unlock_irqrestore(&ei_local->page_lock, flags);
	    return 0;	
	  }
	} 
#else
	tx_cpu_owner_idx = *(unsigned long*)TX_CTX_IDX0;
	if(tx_cpu_owner_idx== NUM_TX_DESC-1)
		tx_cpu_owner_idx_next = 0;
	else
		tx_cpu_owner_idx_next = tx_cpu_owner_idx +1;

	if(((ei_local->skb_free[tx_cpu_owner_idx]) ==0) && (ei_local->skb_free[tx_cpu_owner_idx_next]==0))
		rt2880_eth_send(dev, skb, gmac_no);
	else {
#ifdef CONFIG_PSEUDO_SUPPORT
		if (gmac_no == 2) {
			if (ei_local->PseudoDev != NULL) {
				pAd = netdev_priv(ei_local->PseudoDev);
				pAd->stat.tx_dropped++;
			}
		} else
#endif
			ei_local->stat.tx_dropped++;
		kfree_skb(skb);
		spin_unlock_irqrestore(&ei_local->page_lock, flags);
		return 0;	
	}
	tx_desc = ei_local->tx_ring0;
	ei_local->skb_free[tx_cpu_owner_idx] = skb;	

	*(unsigned long*)TX_CTX_IDX0 = ((tx_cpu_owner_idx+1) % NUM_TX_DESC);
#endif
	spin_unlock_irqrestore(&ei_local->page_lock, flags);
	return 0;	
}

static int ei_start_xmit_fake(struct sk_buff* skb, struct net_device *dev)
{
	return ei_start_xmit(skb, dev, 1);
}


int ei_ioctl(struct net_device *dev, struct ifreq *ifr, int cmd)
{
#if defined(CONFIG_RT_3052_ESW)
	esw_reg reg;
#endif
#if defined(CONFIG_RALINK_RT3352)
        esw_rate ratelimit;
	unsigned int offset = 0;
	unsigned int value = 0;
#endif
	ra_mii_ioctl_data mii;
	switch (cmd) {
		case RAETH_MII_READ:
			copy_from_user(&mii, ifr->ifr_data, sizeof(mii));
			mii_mgr_read(mii.phy_id, mii.reg_num, &mii.val_out);
			//printk("phy %d, reg %d, val 0x%x\n", mii.phy_id, mii.reg_num, mii.val_out);
			copy_to_user(ifr->ifr_data, &mii, sizeof(mii));
			break;

		case RAETH_MII_WRITE:
			copy_from_user(&mii, ifr->ifr_data, sizeof(mii));
			//printk("phy %d, reg %d, val 0x%x\n", mii.phy_id, mii.reg_num, mii.val_in);
			mii_mgr_write(mii.phy_id, mii.reg_num, mii.val_in);
			break;
#if defined(CONFIG_RT_3052_ESW)
#define _ESW_REG(x)	(*((volatile u32 *)(RALINK_ETH_SW_BASE + x)))
		case RAETH_ESW_REG_READ:
			copy_from_user(&reg, ifr->ifr_data, sizeof(reg));
			if (reg.off > REG_ESW_MAX)
				return -EINVAL;
			reg.val = _ESW_REG(reg.off);
			//printk("read reg off:%x val:%x\n", reg.off, reg.val);
			copy_to_user(ifr->ifr_data, &reg, sizeof(reg));
			break;
		case RAETH_ESW_REG_WRITE:
			copy_from_user(&reg, ifr->ifr_data, sizeof(reg));
			if (reg.off > REG_ESW_MAX)
				return -EINVAL;
			_ESW_REG(reg.off) = reg.val;
			//printk("write reg off:%x val:%x\n", reg.off, reg.val);
			break;
#endif // CONFIG_RT_3052_ESW
#ifdef CONFIG_RALINK_RT3352
		case RAETH_ESW_INGRESS_RATE:
			copy_from_user(&ratelimit, ifr->ifr_data, sizeof(ratelimit));
			offset = 0x11c + (4 * (ratelimit.port / 2));
                        value = _ESW_REG(offset);
			
			if((ratelimit.port % 2) == 0)
			{    
				value &= 0xffff0000;
				if(ratelimit.on_off == 1)
				{    
					value |= (ratelimit.on_off << 14);
					value |= (0x07 << 10);
					value |= ratelimit.bw;
				}
			}
			else if((ratelimit.port % 2) == 1)
			{
				value &= 0x0000ffff;
				if(ratelimit.on_off == 1)
				{
					value |= (ratelimit.on_off << 30);
					value |= (0x07 << 26);
					value |= (ratelimit.bw << 16);
				}
			}
			printk("offset = 0x%4x value=0x%x\n\r", offset, value);
			_ESW_REG(offset) = value;
			break;

		case RAETH_ESW_EGRESS_RATE:
			copy_from_user(&ratelimit, ifr->ifr_data, sizeof(ratelimit));
			offset = 0x140 + (4 * (ratelimit.port / 2));
                        value = _ESW_REG(offset);
			
			if((ratelimit.port % 2) == 0)
			{    
				value &= 0xffff0000;
				if(ratelimit.on_off == 1)
				{    
					value |= (ratelimit.on_off << 12);
					value |= (0x03 << 10);
					value |= ratelimit.bw;
				}
			}
			else if((ratelimit.port % 2) == 1)
			{
				value &= 0x0000ffff;
				if(ratelimit.on_off == 1)
				{
					value |= (ratelimit.on_off << 28);
					value |= (0x03 << 26);
					value |= (ratelimit.bw << 16);
				}
			}
			printk("offset = 0x%4x value=0x%x\n\r", offset, value);
			_ESW_REG(offset) = value;
			break;
#endif

	}
	return 0;
}

/*
 * Set new MTU size
 * Change the mtu of Raeth Ethernet Device
 */
static int ei_change_mtu(struct net_device *dev, int new_mtu)
{
	unsigned long flags;
	END_DEVICE *ei_local = netdev_priv(dev);  // get priv ei_local pointer from net_dev structure

	if ( ei_local == NULL ) {
		printk(KERN_EMERG "%s: ei_change_mtu passed a non-existent private pointer from net_dev!\n", dev->name);
		return -ENXIO;
	}

	spin_lock_irqsave(&ei_local->page_lock, flags);

// added by Freddy	
#if defined(CONFIG_RAETH_JUMBOFRAME) || defined(CONFIG_RTL_TAG_SUPPORT)
	if ((new_mtu > MAX_RX_LENGTH) || (new_mtu < 64)) {
		spin_unlock_irqrestore(&ei_local->page_lock, flags);
		return -EINVAL;
	}
#else
	if ( new_mtu > 1500 ) {
		spin_unlock_irqrestore(&ei_local->page_lock, flags);
		return -EINVAL;
	}
#endif

// Original codes here!! We use upper codes as substitute.
// Modified by freddy 
// 2011/04/26
/*
	if ((new_mtu > 4096) || (new_mtu < 64)) {
		spin_unlock_irqrestore(&ei_local->page_lock, flags);
		return -EINVAL;
	}

#ifndef CONFIG_RAETH_JUMBOFRAME
	if ( new_mtu > 1500 ) {
		spin_unlock_irqrestore(&ei_local->page_lock, flags);
		return -EINVAL;
	}
#endif
*/

	dev->mtu = new_mtu;

	spin_unlock_irqrestore(&ei_local->page_lock, flags);
	return 0;
}

#if LINUX_VERSION_CODE >= KERNEL_VERSION(2,6,29)
static const struct net_device_ops rt_netdev_ops = {
	.ndo_init		= rather_probe,
	.ndo_open		= ei_open,
	.ndo_stop		= ei_close,
	.ndo_validate_addr	= eth_validate_addr,
	.ndo_set_mac_address 	= ei_set_mac_addr,
	//.ndo_set_multicast_list	= ?  //we no need, we are router, we rx all.
	.ndo_get_stats		= ra_get_stats,
	.ndo_do_ioctl		= ei_ioctl,
	.ndo_start_xmit		= ei_start_xmit_fake,
	.ndo_tx_timeout		= ei_tx_timeout,
	.ndo_change_mtu		= ei_change_mtu,
};
#endif

void ra2880_setup_dev_fptable(struct net_device *dev)
{
END_DEVICE *ei_local = netdev_priv(dev);

	RAETH_PRINT(__FUNCTION__ "is called!\n");
#if LINUX_VERSION_CODE < KERNEL_VERSION(2,6,29)
	dev->open		= ei_open;
	dev->stop		= ei_close;
	dev->hard_start_xmit	= ei_start_xmit_fake;
	dev->tx_timeout		= ei_tx_timeout;
	dev->get_stats		= ra_get_stats;
	dev->set_mac_address	= ei_set_mac_addr;
	dev->change_mtu		= ei_change_mtu;
	//dev->mtu		= MAX_RX_LENGTH;
#else
	dev->netdev_ops = &rt_netdev_ops;
#endif
	dev->mtu = 1500;

#ifdef CONFIG_RAETH_NAPI
#if LINUX_VERSION_CODE < KERNEL_VERSION(2,6,29)
	dev->poll = &raeth_clean;
#else
	//#error "NAPI not ported yet. siyou\n"
	ei_local->dev = dev; 
	netif_napi_add(dev, &ei_local->napi, raeth_clean, 32);
#endif    

/* mark by siyou.
#if defined (CONFIG_RAETH_ROUTER)
	dev->weight = 32;
#elif defined (CONFIG_RT_3052_ESW)
	dev->weight = 32;
#else
	dev->weight = 128;
#endif
*/
#endif

#if defined (CONFIG_ETHTOOL) && ( defined (CONFIG_RAETH_ROUTER) || defined (CONFIG_RT_3052_ESW) )
	dev->ethtool_ops	= &ra_ethtool_ops;
#endif
	//dev->do_ioctl		= ei_ioctl;
}


#define TX_TIMEOUT (20*HZ/100)
void ei_tx_timeout(struct net_device *dev)
{
	END_DEVICE* ei_local = netdev_priv(dev); 
#ifndef WORKQUEUE_BH
	tasklet_schedule(&ei_local->tx_tasklet);
#endif // WORKQUEUE_BH //
	return;
}

void setup_statistics(END_DEVICE* ei_local)
{
	ei_local->stat.tx_packets	= 0;
	ei_local->stat.tx_bytes 	= 0;
	ei_local->stat.tx_dropped 	= 0;
	ei_local->stat.tx_errors	= 0;
	ei_local->stat.tx_aborted_errors= 0;
	ei_local->stat.tx_carrier_errors= 0;
	ei_local->stat.tx_fifo_errors	= 0;
	ei_local->stat.tx_heartbeat_errors = 0;
	ei_local->stat.tx_window_errors	= 0;
	
	ei_local->stat.rx_packets	= 0;
	ei_local->stat.rx_bytes 	= 0;
	ei_local->stat.rx_dropped 	= 0;	
	ei_local->stat.rx_errors	= 0;
	ei_local->stat.rx_length_errors = 0;
	ei_local->stat.rx_over_errors	= 0;
	ei_local->stat.rx_crc_errors	= 0;
	ei_local->stat.rx_frame_errors	= 0;
	ei_local->stat.rx_fifo_errors	= 0;
	ei_local->stat.rx_missed_errors	= 0;

	ei_local->stat.collisions	= 0;
#if defined (CONFIG_RAETH_QOS)
	ei_local->tx3_full = 0;
	ei_local->tx2_full = 0;
	ei_local->tx1_full = 0;
	ei_local->tx0_full = 0;
#else
	ei_local->tx_full = 0;
#endif
#ifdef CONFIG_RAETH_NAPI
	atomic_set(&ei_local->irq_sem, 1);
#endif
	
}

/**
 * rather_probe - pick up ethernet port at boot time
 * @dev: network device to probe
 *
 * This routine probe the ethernet port at boot time.
 *
 *
 */

int __init rather_probe(struct net_device *dev)
{
	int i;
        unsigned int regValue = 0;
        END_DEVICE *ei_local = netdev_priv(dev);
	struct sockaddr addr;
	unsigned char zero[6]={0xFF,0xFF,0xFF,0xFF,0xFF,0xFF};

	dev->base_addr = RA2882ETH_BASE;
        regValue |= FE_RESET_BIT;
        sysRegWrite(FE_RESET, regValue);
        sysRegWrite(FE_RESET, 0);

        /* receiving packet buffer allocation - NUM_RX_DESC x MAX_RX_LENGTH */
        for (i = 0; i < NUM_RX_DESC; i++) {      		
                netrx_skbuf[i] = NULL;
        }	// kmalloc

/* Removed by David Hsieh <david_hsieh@alphanetworks.com> */
#if 0
	//Get mac0 address from flash
#ifdef RA_MTD_RW_BY_NUM
	i = ra_mtd_read(2, GMAC0_OFFSET, 6, addr.sa_data);
#else
	i = ra_mtd_read_nm("Factory", GMAC0_OFFSET, 6, addr.sa_data);
#endif
#else
	i = -1;
#endif

	//If reading mtd failed or mac0 is empty, generate a mac address
	if (i < 0 || (memcmp(addr.sa_data, zero, 6) == 0)) {
		unsigned char mac_addr01234[5] = {0x00, 0x0C, 0x43, 0x28, 0x80};
		net_srandom(jiffies);
		memcpy(addr.sa_data, mac_addr01234, 5);
		addr.sa_data[5] = net_random()&0xFF;
	}

	ei_set_mac_addr(dev, &addr);
	spin_lock_init(&ei_local->page_lock);	
	ether_setup(dev);

	#if LINUX_VERSION_CODE < KERNEL_VERSION(2,6,29)
	ra2880_setup_dev_fptable(dev);
	#endif

	dev->watchdog_timeo = TX_TIMEOUT;

	setup_statistics(ei_local);

	for (i = 0; i < NUM_RX_DESC; i++)
		netrx_skbuf[i] = NULL;

	return 0;
}

#ifdef WORKQUEUE_BH
void ei_xmit_housekeeping_workq(struct work_struct *work)
#else
void ei_xmit_housekeeping(unsigned long unused)
#endif // WORKQUEUE_BH //
{
    struct net_device *dev = dev_raether;
    END_DEVICE *ei_local = netdev_priv(dev);
    struct PDMA_txdesc *tx_desc;
    unsigned long skb_free_idx;
    unsigned long tx_dtx_idx;
#ifndef CONFIG_RAETH_NAPI
    unsigned long reg_int_mask=0;
#endif

#ifdef CONFIG_RAETH_QOS
    int i;
    for (i=0;i<NUM_TX_RINGS;i++){
        skb_free_idx = ei_local->free_idx[i];
    	if((ei_local->skb_free[i][skb_free_idx])==0){
		continue;
	}

	get_tx_desc_and_dtx_idx(ei_local, i, &tx_dtx_idx, &tx_desc);

	while(tx_desc[skb_free_idx].txd_info2.DDONE_bit==1 && (ei_local->skb_free[i][skb_free_idx])!=0 ){
	
	    dev_kfree_skb_irq((ei_local->skb_free[i][skb_free_idx]));
	    ei_local->skb_free[i][skb_free_idx]=0;
	    skb_free_idx++;
	    if(skb_free_idx >= NUM_TX_DESC)
                    skb_free_idx =0;
	}
	ei_local->free_idx[i] = skb_free_idx;
    }
#else
	tx_dtx_idx = *(unsigned long*)TX_DTX_IDX0;
	tx_desc = ei_local->tx_ring0;
	skb_free_idx = ei_local->free_idx;
	if ((ei_local->skb_free[skb_free_idx]) != 0) {
		while(tx_desc[skb_free_idx].txd_info2.DDONE_bit==1 && (ei_local->skb_free[skb_free_idx])!=0 ){
			dev_kfree_skb_irq((ei_local->skb_free[skb_free_idx]));
			ei_local->skb_free[skb_free_idx]=0;
			skb_free_idx++;
			if(skb_free_idx >= NUM_TX_DESC)
       				skb_free_idx =0;
		}
		ei_local->free_idx = skb_free_idx;
	}  /* if skb_free != 0 */
#endif	

#ifndef CONFIG_RAETH_NAPI
    reg_int_mask=sysRegRead(FE_INT_ENABLE);
#if defined (DELAY_INT)
    sysRegWrite(FE_INT_ENABLE, reg_int_mask|RT2880_TX_DLY_INT);
#else
    
    sysRegWrite(FE_INT_ENABLE, reg_int_mask|RT2880_TX_DONE_INT0	\
		    			   |RT2880_TX_DONE_INT1 \
					   |RT2880_TX_DONE_INT2 \
					   |RT2880_TX_DONE_INT3);
#endif
#else//CONFIG_RAETH_NAPI
#ifdef ALPHA_MOD_ISR_FOR_NAPI //marco
	 unsigned long reg_int_mask=0;
	reg_int_mask=sysRegRead(FE_INT_ENABLE);
	sysRegWrite(FE_INT_ENABLE, reg_int_mask|RT2880_TX_DONE_INT0	\
		    			   |RT2880_TX_DONE_INT1 \
					   |RT2880_TX_DONE_INT2 \
					   |RT2880_TX_DONE_INT3);
#endif
					   
#endif //CONFIG_RAETH_NAPI//
}


#ifdef CONFIG_PSEUDO_SUPPORT
int VirtualIF_ioctl(struct net_device * net_dev,
		    struct ifreq * rq, int cmd)
{
	return 0;
}

struct net_device_stats *VirtualIF_get_stats(struct net_device *dev)
{
	PSEUDO_ADAPTER *pAd = netdev_priv(dev);
	return &pAd->stat;
}

int VirtualIF_open(struct net_device * dev)
{
    PSEUDO_ADAPTER *pPesueoAd = netdev_priv(dev);


    *(unsigned long *)MDIO_CFG2= 0x3E000000; //auto-polling

    printk("%s: ===> VirtualIF_open\n", dev->name);

    netif_start_queue(pPesueoAd->PseudoDev);

    return 0;
}

int VirtualIF_close(struct net_device * dev)
{
    PSEUDO_ADAPTER *pPesueoAd = netdev_priv(dev);

    printk("%s: ===> VirtualIF_close\n", dev->name);

    netif_stop_queue(pPesueoAd->PseudoDev);

    return 0;
}

int VirtualIFSendPackets(struct sk_buff * pSkb,
			 struct net_device * dev)
{
    PSEUDO_ADAPTER *pPesueoAd = netdev_priv(dev);

    //printk("VirtualIFSendPackets --->\n");

    if (!(pPesueoAd->RaethDev->flags & IFF_UP)) {
	dev_kfree_skb_any(pSkb);
	return 0;
    }
    //pSkb->cb[40]=0x5a;
    pSkb->dev = pPesueoAd->RaethDev;
    ei_start_xmit(pSkb, pPesueoAd->RaethDev, 2);
    return 0;
}

void virtif_setup_statistics(PSEUDO_ADAPTER* pAd)
{
	pAd->stat.tx_packets	= 0;
	pAd->stat.tx_bytes 	= 0;
	pAd->stat.tx_dropped 	= 0;
	pAd->stat.tx_errors	= 0;
	pAd->stat.tx_aborted_errors= 0;
	pAd->stat.tx_carrier_errors= 0;
	pAd->stat.tx_fifo_errors	= 0;
	pAd->stat.tx_heartbeat_errors = 0;
	pAd->stat.tx_window_errors	= 0;

	pAd->stat.rx_packets	= 0;
	pAd->stat.rx_bytes 	= 0;
	pAd->stat.rx_dropped 	= 0;	
	pAd->stat.rx_errors	= 0;
	pAd->stat.rx_length_errors = 0;
	pAd->stat.rx_over_errors	= 0;
	pAd->stat.rx_crc_errors	= 0;
	pAd->stat.rx_frame_errors	= 0;
	pAd->stat.rx_fifo_errors	= 0;
	pAd->stat.rx_missed_errors	= 0;

	pAd->stat.collisions	= 0;
}

#if LINUX_VERSION_CODE >= KERNEL_VERSION(2,6,29)
static const struct net_device_ops pseudo_netdev_ops = {
	.ndo_open		= VirtualIF_open,
	.ndo_stop		= VirtualIF_close,
	.ndo_validate_addr	= eth_validate_addr,
	.ndo_set_mac_address 	= ei_set_mac2_addr,
	//.ndo_set_multicast_list	= ?  //we no need, we are router, we rx all.
	.ndo_get_stats		= VirtualIF_get_stats,
	.ndo_do_ioctl		= VirtualIF_ioctl,
	.ndo_start_xmit		= VirtualIFSendPackets,
};
#endif

// Register pseudo interface
void RAETH_Init_PSEUDO(pEND_DEVICE pAd, struct net_device *net_dev)
{
    int index;
    struct net_device *dev;
    PSEUDO_ADAPTER *pPseudoAd;
    int i = 0;
    char slot_name[16];
    struct net_device *device;
    struct sockaddr addr;
    unsigned char zero[6]={0xFF,0xFF,0xFF,0xFF,0xFF,0xFF};

    for (index = 0; index < MAX_PSEUDO_ENTRY; index++) {

	dev = alloc_etherdev(sizeof(PSEUDO_ADAPTER));

	{			// find available 
		for (i = 3; i < 32; i++) {
			sprintf(slot_name, "eth%d", i);
			#if 0
			for (device = dev_base; device != NULL; device = device->next) {
		    	if (strncmp(device->name, slot_name, 4) == 0)
					break;
				}
			if (device == NULL)
		    	break;
			#else
			device = dev_get_by_name(dev_net(dev), slot_name);
			if (device != NULL)
				dev_put(device);
			if (device == NULL)
				break;
			#endif
	    }

	    if (i != 32) {
		sprintf(dev->name, "eth%d", i);
	    } else {
		printk("Ethernet interface number overflow...\n");
		break;
	    }
	}

/* Removed by David Hsieh <david_hsieh@alphanetworks.com> */
#if 0
	//Get mac2 address from flash
#ifdef RA_MTD_RW_BY_NUM
	i = ra_mtd_read(2, GMAC2_OFFSET, 6, addr.sa_data);
#else
	i = ra_mtd_read_nm("Factory", GMAC2_OFFSET, 6, addr.sa_data);
#endif
#else
	i = -1;
#endif

	//If reading mtd failed or mac0 is empty, generate a mac address
	if (i < 0 || (memcmp(addr.sa_data, zero, 6) == 0)) 
	{
		unsigned char mac_addr01234[5] = {0x00, 0x0C, 0x43, 0x28, 0x82};
		net_srandom(jiffies);
		memcpy(addr.sa_data, mac_addr01234, 5);
		addr.sa_data[5] = net_random()&0xFF;
	}

	ei_set_mac2_addr(dev, &addr);
	ether_setup(dev);
	pPseudoAd = netdev_priv(dev);

	pPseudoAd->PseudoDev = dev;
	pPseudoAd->RaethDev = net_dev;
	virtif_setup_statistics(pPseudoAd);
	pAd->PseudoDev = dev;

	//fix by siyou,
	//now the dev->dev_addr is a pointer, not an array in 2.6.21.
	//memcpy(&dev->dev_addr, &net_dev->dev_addr, 6);
	memcpy(dev->dev_addr, net_dev->dev_addr, 6);

#if LINUX_VERSION_CODE < KERNEL_VERSION(2,6,29)
	dev->hard_start_xmit = VirtualIFSendPackets;
	dev->stop = VirtualIF_close;
	dev->open = VirtualIF_open;
	dev->do_ioctl = VirtualIF_ioctl;
	dev->set_mac_address = ei_set_mac2_addr;
	dev->get_stats = VirtualIF_get_stats;
#else
	dev->netdev_ops = &pseudo_netdev_ops;
#endif

	// Register this device
	register_netdevice(dev);
    }
}
#endif



/**
 * ei_open - Open/Initialize the ethernet port.
 * @dev: network device to initialize
 *
 * This routine goes all-out, setting everything
 * up a new at each open, even though many of these registers should only need to be set once at boot.
 */
int ei_open(struct net_device *dev)
{
	int i;
	unsigned long flags;
	END_DEVICE *ei_local;
#if LINUX_VERSION_CODE >= KERNEL_VERSION(2,5,0)
	if (!try_module_get(THIS_MODULE))
	{
		printk("%s: Cannot reserve module\n", __FUNCTION__);
		return -1;
	}
#else
	MOD_INC_USE_COUNT;
#endif

  	ei_local = netdev_priv(dev); // get device pointer from System
	// unsigned int flags;

	if (ei_local == NULL)
	{
		printk(KERN_EMERG "%s: ei_open passed a non-existent device!\n", dev->name);
		return -ENXIO;
	}

        /* receiving packet buffer allocation - NUM_RX_DESC x MAX_RX_LENGTH */
        for ( i = 0; i < NUM_RX_DESC; i++)
        {
                netrx_skbuf[i] = dev_alloc_skb(MAX_RX_LENGTH+2+HW_NAT_HEADROOM);
                if (netrx_skbuf[i] == NULL )
                        printk("rx skbuff buffer allocation failed!");
		skb_reserve(netrx_skbuf[i], 2+HW_NAT_HEADROOM);
        }       // kmalloc

	spin_lock_irqsave(&(ei_local->page_lock), flags);
	request_irq( dev->irq, ei_interrupt, SA_INTERRUPT, dev->name, dev);	// try to fix irq in open

	rt2880_eth_setup(dev);
#if defined (CONFIG_RALINK_RT3052) || defined (CONFIG_RALINK_RT3352)
	//INTENA: Interrupt enabled for ESW
	*((volatile u32 *)(RALINK_INTCL_BASE + 0x34)) = (1<<17); 
	*((volatile u32 *)(RALINK_ETH_SW_BASE + 0x04)) &= ~(ESW_INT_ALL);

	request_irq(SURFBOARDINT_ESW, esw_interrupt, SA_INTERRUPT, "Ralink_ESW", NULL);	
#endif // CONFIG_RALINK_RT3052 || CONFIG_RALINK_RT3352 //


#ifdef DELAY_INT
	sysRegWrite(DLY_INT_CFG, DELAY_INT_INIT);
    	sysRegWrite(FE_INT_ENABLE, FE_INT_DLY_INIT);
#else
    	sysRegWrite(FE_INT_ENABLE, FE_INT_ALL);
#endif

#ifdef WORKQUEUE_BH
	INIT_WORK(&ei_local->tx_wq, ei_xmit_housekeeping_workq);
#ifndef CONFIG_RAETH_NAPI
 	INIT_WORK(&ei_local->rx_wq, ei_receive_workq);
#endif // CONFIG_RAETH_NAPI //
#else
	tasklet_init(&ei_local->tx_tasklet, ei_xmit_housekeeping , 0);
#ifndef CONFIG_RAETH_NAPI
	tasklet_init(&ei_local->rx_tasklet, ei_receive, 0);
#endif // CONFIG_RAETH_NAPI //
#endif // WORKQUEUE_BH //

	netif_start_queue(dev);

#ifdef CONFIG_RAETH_NAPI
	atomic_dec(&ei_local->irq_sem);
    //netif_poll_enable(dev);
	napi_enable(&ei_local->napi);
#endif

	spin_unlock_irqrestore(&(ei_local->page_lock), flags);
#ifdef CONFIG_PSEUDO_SUPPORT 
	if(ei_local->PseudoDev==NULL) {
	    RAETH_Init_PSEUDO(ei_local, dev);
	}

	VirtualIF_open(ei_local->PseudoDev);
#endif

	forward_config(dev);
	return 0;
}

/**
 * ei_close - shut down network device
 * @dev: network device to clear
 *
 * This routine shut down network device.
 *
 *
 */
int ei_close(struct net_device *dev)
{
	int i;
	END_DEVICE *ei_local = netdev_priv(dev);	// device pointer
	unsigned long flags;
	spin_lock_irqsave(&ei_local->page_lock, flags);

#ifdef CONFIG_PSEUDO_SUPPORT 
	VirtualIF_close(ei_local->PseudoDev);
#endif

	netif_stop_queue(dev);
	ra2880stop(ei_local);
	
#ifndef WORKQUEUE_BH
	tasklet_kill(&ei_local->tx_tasklet);
	tasklet_kill(&ei_local->rx_tasklet);
#endif // WORKQUEUE_BH //

        for ( i = 0; i < NUM_RX_DESC; i++)
        {
                if (netrx_skbuf[i] != NULL) {
                        dev_kfree_skb(netrx_skbuf[i]);
			netrx_skbuf[i] = NULL;
		}
        } 


#if defined (CONFIG_RAETH_QOS)
       if (ei_local->tx_ring0 != NULL) {
	   pci_free_consistent(NULL, NUM_TX_DESC*sizeof(struct PDMA_txdesc), ei_local->tx_ring0, ei_local->phy_tx_ring0);
       }

       if (ei_local->tx_ring1 != NULL) {
	   pci_free_consistent(NULL, NUM_TX_DESC*sizeof(struct PDMA_txdesc), ei_local->tx_ring1, ei_local->phy_tx_ring1);
       }
       
#if defined (CONFIG_RALINK_RT2883) || defined (CONFIG_RALINK_RT3052) || defined (CONFIG_RALINK_RT3352) || defined(CONFIG_RALINK_RT3883)
       if (ei_local->tx_ring2 != NULL) {
	   pci_free_consistent(NULL, NUM_TX_DESC*sizeof(struct PDMA_txdesc), ei_local->tx_ring2, ei_local->phy_tx_ring2);
       }
       
       if (ei_local->tx_ring3 != NULL) {
	   pci_free_consistent(NULL, NUM_TX_DESC*sizeof(struct PDMA_txdesc), ei_local->tx_ring3, ei_local->phy_tx_ring3);
       }
#endif
#else
	pci_free_consistent(NULL, NUM_TX_DESC*sizeof(struct PDMA_txdesc), ei_local->tx_ring0, ei_local->phy_tx_ring0);
#endif
        pci_free_consistent(NULL, NUM_RX_DESC*sizeof(struct PDMA_rxdesc), rx_ring, phy_rx_ring);
	printk("Free TX/RX Ring Memory!\n");
	free_irq(dev->irq, dev);

#ifdef CONFIG_RAETH_NAPI
	atomic_inc(&ei_local->irq_sem);
    //netif_poll_disable(dev);
	napi_disable(&ei_local->napi);
#endif
	spin_unlock_irqrestore(&(ei_local->page_lock), flags);

#if LINUX_VERSION_CODE >= KERNEL_VERSION(2,5,0)
	module_put(THIS_MODULE);
#else
	MOD_DEC_USE_COUNT;
#endif
	return 0;
}

#if defined (CONFIG_RALINK_RT3052) || defined (CONFIG_RALINK_RT3352)
void rt305x_esw_init(void)
{
	int i=0;
	u32 my_val;

	/*
	 * FC_RLS_TH=200, FC_SET_TH=160
	 * DROP_RLS=120, DROP_SET_TH=80
	 */
        *(unsigned long *)(0xb0110008) = 0xC8A07850;
        *(unsigned long *)(0xb01100E4) = 0x00000000;
        *(unsigned long *)(0xb0110014) = 0x00405555;
        *(unsigned long *)(0xb0110050) = 0x00002001;
        *(unsigned long *)(0xb0110090) = 0x00007f7f;
        *(unsigned long *)(0xb0110098) = 0x00007f3f; //disable VLAN
        *(unsigned long *)(0xb01100CC) = 0x00d6500c;
        *(unsigned long *)(0xb011009C) = 0x0008a301; //hashing algorithm=XOR48, aging interval=300sec
        *(unsigned long *)(0xb011008C) = 0x02404040;
#if defined (CONFIG_RT3052_ASIC) || defined (CONFIG_RT3352_ASIC)
        *(unsigned long *)(0xb01100C8) = 0x3f502b28; //Change polling Ext PHY Addr=0x1F
        *(unsigned long *)(0xb0110084) = 0x00000000;
        *(unsigned long *)(0xb0110110) = 0x7d000000; //1us cycle number=125 (FE's clock=125Mhz)
#elif defined (CONFIG_RT3052_FPGA) || defined (CONFIG_RT3352_FPGA)
        *(unsigned long *)(0xb01100C8) = 0x20f02b28; //Change polling Ext PHY Addr=0x0
        *(unsigned long *)(0xb0110084) = 0xffdf1f00;
        *(unsigned long *)(0xb0110110) = 0x0d000000; //1us cycle number=13 (FE's clock=12.5Mhz)

	/* In order to use 10M/Full on FPGA board. We configure phy capable to
	 * 10M Full/Half duplex, so we can use auto-negotiation on PC side */
        for(i=0;i<5;i++){
	    mii_mgr_write(i, 4, 0x0461);   //Capable of 10M Full/Half Duplex, flow control on/off
	    mii_mgr_write(i, 0, 0xB100);   //reset all digital logic, except phy_reg
	}
#endif

        mii_mgr_write(0, 31, 0x8000);   //---> select local register
        for(i=0;i<5;i++){
                mii_mgr_write(i, 26, 0x1600);   //TX10 waveform coefficient  //LSB=0 disable PHY
#if defined(CONFIG_RALINK_RT3350)
                mii_mgr_write(i, 29, 0x7015);   //TX100/TX10 AD/DA current bias
                mii_mgr_write(i, 30, 0x0038);   //TX100 slew rate control
#else
                mii_mgr_write(i, 29, 0x7058);   //TX100/TX10 AD/DA current bias
                mii_mgr_write(i, 30, 0x0018);   //TX100 slew rate control
#endif
        }
        /* PHY IOT */
        mii_mgr_write(0, 31, 0x0);   //select global register
	mii_mgr_write(0, 1, 0x4a40); //enlarge agcsel threshold 3 and threshold 2
	mii_mgr_write(0, 2, 0x6254); //enlarge agcsel threshold 5 and threshold 4
	mii_mgr_write(0, 3, 0xa17f); //enlarge agcsel threshold 6
#if defined(CONFIG_RALINK_RT3350)
	mii_mgr_write(0, 12, 0x7eaa); //100% link down power saving & tx10 link up 50%
        mii_mgr_write(0, 22, 0x252f); //tune TP_IDL tail and head waveform, enable power down slew rate control
#else
        mii_mgr_write(0, 22, 0x052f); //tune TP_IDL tail and head waveform
#endif
        mii_mgr_write(0, 14, 0x65);   //longer TP_IDL tail length
	mii_mgr_write(0, 16, 0x0684);  //increased squelch pulse count threshold
        mii_mgr_write(0, 17, 0x0fe0); //set TX10 signal amplitude threshold to minimum
        mii_mgr_write(0, 18, 0x40ba); //set squelch amplitude to higher threshold
	mii_mgr_write(0, 27, 0x2fc3); //set PLL/Receive bias current are calibrated(RT3350)
	mii_mgr_write(0, 28, 0xc410); //change PLL/Receive bias current to internal(RT3350)
	mii_mgr_write(0, 29, 0x598b); //change PLL bias current to internal(RT3052_MP3)
        mii_mgr_write(0, 31, 0x8000); //select local register
	for(i=0;i<5;i++){
		//LSB=1 enable PHY
		mii_mgr_read(i, 26, &my_val);
		my_val |= 0x0001;
		mii_mgr_write(i, 26, my_val);
	}

	/* 
	 * set port 5 force to 1000M/Full when connecting to switch or iNIC
	 */
#if defined (CONFIG_P5_RGMII_TO_MAC_MODE)
	*(unsigned long *)(0xb0000060) &= ~(1 << 9); //set RGMII to Normal mode
        *(unsigned long *)(0xb01100C8) &= ~(1<<29); //disable port 5 auto-polling
        *(unsigned long *)(0xb01100C8) |= 0x3fff; //force 1000M full duplex
        *(unsigned long *)(0xb01100C8) &= ~(0xf<<20); //rxclk_skew, txclk_skew = 0
#elif defined (CONFIG_P5_MII_TO_MAC_MODE)
	*(unsigned long *)(0xb0000060) &= ~(1 << 9); //set RGMII to Normal mode
        *(unsigned long *)(0xb01100C8) &= ~(1<<29); //disable port 5 auto-polling
        *(unsigned long *)(0xb01100C8) |= 0x3ffd; //force 100M full duplex
#elif defined (CONFIG_P5_MAC_TO_PHY_MODE)
	*(unsigned long *)(0xb0000060) &= ~(1 << 9); //set RGMII to Normal mode
	enable_auto_negotiate(1);
        if (isMarvellGigaPHY(1)) {
                printk("\n MARVELL Phy\n");
                mii_mgr_write(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR, 20, 0x0ce0);
                mii_mgr_write(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR, 0, 0x9140);
        }

#elif defined (CONFIG_P5_RMII_TO_MAC_MODE)
	*(unsigned long *)(0xb0000060) &= ~(1 << 9); //set RGMII to Normal mode
#else // Port 5 Disabled //
        *(unsigned long *)(0xb01100C8) &= ~(1 << 29); //port5 auto polling disable
        *(unsigned long *)(0xb0000060) |= (1 << 9); //set RGMII to GPIO mode (GPIO41-GPIO50)
        *(unsigned long *)(0xb0000674) = 0xFFF; //GPIO41-GPIO50 output mode
        *(unsigned long *)(0xb0000670) = 0x0; //GPIO41-GPIO50 output low
#endif // CONFIG_P5_RGMII_TO_MAC_MODE //

}
#endif

/**
 * ra2882eth_init - Module Init code
 *
 * Called by kernel to register net_device
 *
 */
int __init ra2882eth_init(void)
{
	int ret;
	struct net_device *dev = alloc_etherdev(sizeof(END_DEVICE));
#if defined (CONFIG_GIGAPHY)  || defined (CONFIG_GIGAPHY2)
        unsigned int regValue = 0;
#endif

	if (!dev)
		return -ENOMEM;

	strcpy(dev->name, DEV_NAME);
	dev->irq  = RT2880_IRQ_ENET0;
	dev->addr_len = 6;
	dev->base_addr = RA2882_ENET0;	

	#if LINUX_VERSION_CODE < KERNEL_VERSION(2,6,29)
	dev->init =  rather_probe;
	#else
	//dev->init is move to net_device_ops .ndo_init
	ra2880_setup_dev_fptable(dev);
	#endif

	/* net_device structure Init */
	hard_init(dev);
	printk("Ralink APSoC Ethernet Driver Initilization. %s  %d rx/tx descriptors allocated, mtu = %d!\n", RAETH_VERSION, NUM_RX_DESC, dev->mtu);

/*
#ifdef CONFIG_RAETH_NAPI
	printk("NAPI enable, weight = %d, Tx Ring = %d, Rx Ring = %d\n", dev->weight, NUM_TX_DESC, NUM_RX_DESC);
#endif
*/

	/* Register net device for the driver */
	if ( register_netdev(dev) != 0) {
		printk(KERN_WARNING " " __FILE__ ": No ethernet port found.\n");
		return -ENXIO;
	}

#ifdef CONFIG_RAETH_NETLINK
	csr_netlink_init();
#endif
	ret = debug_proc_init();

// GigaPhy
#if defined (CONFIG_GIGAPHY) 
	enable_auto_negotiate(1);
	if (isMarvellGigaPHY(1)) {
#if defined (CONFIG_RT3883_FPGA)
		mii_mgr_read(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR, 9, &regValue);
		regValue &= ~(3<<8); //turn off 1000Base-T Advertisement
		mii_mgr_write(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR, 9, regValue);
#endif
		printk("\n Reset MARVELL phy\n");
		mii_mgr_read(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR, 20, &regValue);
		regValue |= 1<<7; //Add delay to RX_CLK for RXD Outputs
		mii_mgr_write(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR, 20, regValue);

		mii_mgr_read(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR, 0, &regValue);
		regValue |= 1<<15; //PHY Software Reset
	 	mii_mgr_write(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR, 0, regValue);

	}
	if (isVtssGigaPHY(1)) {
		mii_mgr_write(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR, 31, 1);
		mii_mgr_read(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR, 28, &regValue);
		printk("Vitesse phy skew: %x --> ", regValue);
		regValue |= (0x3<<12);
		regValue &= ~(0x3<<14);
		printk("%x\n", regValue);
		mii_mgr_write(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR, 28, regValue);
		mii_mgr_write(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR, 31, 0);
	}

// RT3052/3352 + EmbeddedSW
#elif defined (CONFIG_RT_3052_ESW) 
	rt305x_esw_init();
// RT2880 + GigaSW
#elif defined (CONFIG_MAC_TO_MAC_MODE)
	// force cpu port is 1000F
	sysRegWrite(MDIO_CFG, 0x1F01DC01);
#elif defined (CONFIG_MAC_TO_RTL8367_MODE)
	/* RT3883 + GigaSW (via I2C), force cpu port to 1000F */
	sysRegWrite(MDIO_CFG, 0x1F01DC01);
#elif defined (CONFIG_MAC_TO_MAC_ICPLUS175D_MII_MODE)	
	/* RT3883 + MII MAC, force cpu port to 100F */
	{
		unsigned int regValue;
		//set ge1 set RGMII to Normal mode 
		regValue = sysRegRead(0xb0000060);
		regValue &= ~(1 << 9); //
		sysRegWrite(0xb0000060,regValue);
		
		//set ge1 as mii mode
		regValue = sysRegRead(0xb0000014);
		// Ge1 as mii
		regValue |= (1<<12);
		regValue &= ~(1<<13);
		sysRegWrite(0xb0000014,regValue);
		//0x1F01BC75 is patch from ralink..0x1F01BC01
		sysRegWrite(MDIO_CFG, 0x1F01BC75);
		//enable the ic+175d turbo mii
		mii_mgr_read(21,22,&regValue);
		//printk("read 21 22 value = %x\n",regValue);
		regValue |= (1<<10);
		regValue |= (1<<11);
		mii_mgr_write(21,22,regValue);		
		//the ic plus tell us the phy4 reg16 bit 15 set to 1 for 802.3 test
		mii_mgr_read(4,16,&regValue);
		regValue |= (1<<15);
		//printk("set 175d phy 4 mii 16 bit 15 as 1\n");
		mii_mgr_write(4,16,regValue);

	}
// RT2880 + 100PHY
#elif defined (CONFIG_RAETH_ROUTER) || defined (CONFIG_ICPLUS_PHY)

	sysRegWrite(MDIO_CFG, INIT_VALUE_OF_ICPLUS_PHY_INIT_VALUE);

#if defined (CONFIG_RAETH_ROUTER)
#ifdef CONFIG_RALINK_VISTA_BASIC
	int sw_id=0;
	mii_mgr_read(29, 31, &sw_id);
	if (sw_id == 0x175c) {
		is_switch_175c = 1;
	} else {
		is_switch_175c = 0;
	}
#endif // CONFIG_RALINK_VISTA_BASIC

	// due to the flaws of RT2880 GMAC implementation (or IC+ SW ?) we use the
	// fixed capability instead of auto-polling.
	// force cpu port is 100F
	//mii_mgr_write(29, 22, 0x8420);
#endif // CONFIG_RAETH_ROUTER //
#endif // CONFIG_GIGAPHY //  

#ifdef CONFIG_PSEUDO_SUPPORT
#if CONFIG_100M_PHY2//GE2 + 100 PHY
		//init the ge2 as mii mode.
		{
			unsigned int regValue;
			//set ge2 set RGMII to Normal mode 
			regValue = sysRegRead(0xb0000060);
			regValue &= ~(1 << 10); //
			sysRegWrite(0xb0000060,regValue);
			
			//set ge1 as mii mode
			regValue = sysRegRead(0xb0000014);
			// Ge2 as mii
			regValue |= (1<<14);
			regValue &= ~(1<<15);
			sysRegWrite(0xb0000014,regValue);
			//set ge2 as auto negotiation
			sysRegWrite(MDIO_CFG2,0x3E000000);
		   	printk("\n GMAC2 100 phy\n");
		}
#else
// RT3883 GE2 + GigaPhy
#if defined (CONFIG_GIGAPHY2) 
	enable_auto_negotiate(2);
	if (isMarvellGigaPHY(2)) {
#if defined (CONFIG_RT3883_FPGA)
		mii_mgr_read(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR2, 9, &regValue);
		regValue &= ~(3<<8); //turn off 1000Base-T Advertisement
		mii_mgr_write(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR2, 9, regValue);
#endif
		printk("\n GMAC2 Reset MARVELL phy\n");
		mii_mgr_read(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR2, 20, &regValue);
		regValue |= 1<<7; //Add delay to RX_CLK for RXD Outputs
		mii_mgr_write(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR2, 20, regValue);

		mii_mgr_read(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR2, 0, &regValue);
		regValue |= 1<<15; //PHY Software Reset
		mii_mgr_write(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR2, 0, regValue);

	}
	if (isVtssGigaPHY(2)) {
		mii_mgr_write(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR2, 31, 1);
		mii_mgr_read(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR2, 28, &regValue);
		printk("Vitesse phy skew: %x --> ", regValue);
		regValue |= (0x3<<12);
		regValue &= ~(0x3<<14);
		printk("%x\n", regValue);
		mii_mgr_write(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR2, 28, regValue);
		mii_mgr_write(CONFIG_MAC_TO_GIGAPHY_MODE_ADDR2, 31, 0);
	}
#endif
#endif
#endif

	dev_raether = dev;
	return ret;
}

/**
 * ra2882eth_cleanup_module - Module Exit code
 *
 * Cmd 'rmmod' will invode the routine to exit the module
 *
 */
void ra2882eth_cleanup_module(void)
{
	int i;
	struct net_device *dev = dev_raether;
	END_DEVICE *ei_local;

	if (netdev_priv(dev) != NULL)
	{
		ei_local = netdev_priv(dev);
		if ( ei_local->MACInfo != NULL )
		{
			RAETH_PRINT("Free MACInfo...\n");
			kfree(ei_local->MACInfo);

		} 
		else
			RAETH_PRINT("MACInfo is null\n");	

#ifdef CONFIG_PSEUDO_SUPPORT
		//kfree(ei_local->PseudoDev->priv);
		kfree(netdev_priv(ei_local->PseudoDev));
		unregister_netdev(ei_local->PseudoDev);
#endif
		kfree(netdev_priv(dev));
		unregister_netdev(dev);
		RAETH_PRINT("Free ei_local and unregister netdev...\n");
  	} /* dev->priv */
        for ( i = 0; i < NUM_RX_DESC; i++)
        {
                if (netrx_skbuf[i] != NULL) {
                        dev_kfree_skb(netrx_skbuf[i]);
                        netrx_skbuf[i] = NULL;
                }
        }       // dev_kfree_skb

	free_netdev(dev);
	debug_proc_exit();
#ifdef CONFIG_RAETH_NETLINK
	csr_netlink_end();
#endif
}
#if defined (CONFIG_MAC_TO_MAC_ICPLUS175D_MII_MODE)	
/* Link Type */
#define TYPE_DISCONNECT		0
#define TYPE_100FULL		1
#define TYPE_100HALF		2
#define TYPE_10FULL			3
#define TYPE_10HALF			4
#define TYPE_UNKNOWN		99
#define TYPE_AN				0

/*MII18 is not in datasheet,copy from board/wrgn16 icplus 175c*/
#define MII18_Speed100M		(1<<11)
#define MII18_FullDuplex	(1<<10)
#define MII0_AUTONEG		(1<<12)
#define MII0_FullDuplex		(1<<8)
#define MII0_SPEED100		(1<<13)
#define MII1_LINKUP			(1<<2)

char alpha_get_phy_status(char port)
{
	u32 value;
	char ret = TYPE_UNKNOWN;
	if(port > 4 || port < 0 )
		return ret;
	/* read twice to clear the latch */
	mii_mgr_read(port,1,&value);
	mii_mgr_read(port,1,&value);
	//printk("port %d,value=%x\n",port,value);
	if((value&MII1_LINKUP)==0)
		ret = TYPE_DISCONNECT;
	else
	{
		//is link up check it is an or fix
		mii_mgr_read(port,0,&value);
		if(value&MII0_AUTONEG)/*auto*/
		{
			mii_mgr_read(port,18,&value);
			if (value & MII18_FullDuplex)
				ret = (value & MII18_Speed100M) ? TYPE_100FULL : TYPE_10FULL;
			else
				ret = (value & MII18_Speed100M) ? TYPE_100HALF : TYPE_10HALF;
			//printk("auto %x\n",value);
		}
		else/*fix mode*/
		{
			if((value&MII0_SPEED100))
			{	/*100M*/
				if((value&MII0_FullDuplex))/*full*/
					ret = TYPE_100FULL;
				else
					ret = TYPE_100HALF;
			}
			else
			{
				if((value&MII0_FullDuplex))/*full*/
					ret = TYPE_10FULL;
				else
					ret = TYPE_10HALF;
			}
		}
	}
	return ret;
}
void alpha_set_phy(char port,char type)
{
	u32 value;
	char ret = TYPE_UNKNOWN;
	
	if(port > 4 || port < 0 )
		return ;
	mii_mgr_read(port,0,&value);
	//printk("port %d,value=%x\n",port,value);
	if(type == TYPE_AN)/*auto*/
		value |= MII0_AUTONEG;	
	else
	{	
		value &= ~MII0_AUTONEG;
		value &= ~(MII0_FullDuplex|MII0_SPEED100);/* clear bit 8 full(1)/half(0) 13:100(1)/10(0)*/
		if(type == TYPE_100FULL)
			value |= (MII0_FullDuplex|MII0_SPEED100);
		else if(type == TYPE_100HALF)
			value |= (MII0_SPEED100);
		else if(type == TYPE_10FULL)
			value |= (MII0_FullDuplex);
		else if(type == TYPE_10HALF)
			;//do notthing
		else
		{
			printk("unknow link type %d,set auto\n",port);	
			value |= MII0_AUTONEG;
		}
	}
	//printk("port %d,value=%x\n",port,value);
	value |= (1<<11);//power down
	mii_mgr_write(port,0,value);
	value &= ~(1<<11);//power up
	mii_mgr_write(port,0,value);
	return ;
}
#endif
//////////////////////////////////////

#ifdef IGMP_SNOOP
EXPORT_SYMBOL(register_igmp_callback);
EXPORT_SYMBOL(unregister_igmp_callback);
#endif
#ifdef CONFIG_RTL_TAG_SUPPORT
/* bouble - 20101208 - 
  * For realtek switch(rtl8367R/rtl8367RB), it's proprietary tag length is 8 bytes. 
  * It's format will be:
  * 		Ether type: 2 bytes
  *          Protocol+ reason field: 2 bytes
  *          Priority : 2 bytes
  *		Incoming port index: 2 bytes
  */
EXPORT_SYMBOL(gSwPortWiFiPriEnable);
#endif

module_init(ra2882eth_init);
module_exit(ra2882eth_cleanup_module);
MODULE_LICENSE("GPL");
