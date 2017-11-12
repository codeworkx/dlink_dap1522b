/*
 *	Handle incoming frames
 *	Linux ethernet bridge
 *
 *	Authors:
 *	Lennert Buytenhek		<buytenh@gnu.org>
 *
 *	This program is free software; you can redistribute it and/or
 *	modify it under the terms of the GNU General Public License
 *	as published by the Free Software Foundation; either version
 *	2 of the License, or (at your option) any later version.
 */

#include <linux/kernel.h>
#include <linux/netdevice.h>
#include <linux/etherdevice.h>
#include <linux/netfilter_bridge.h>
#include "br_private.h"
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS
#include <linux/ip.h>
#include <linux/in.h>
#include <linux/igmp.h>
#endif

/* Bridge group multicast address 802.1d (pg 51). */
const u8 br_group_address[ETH_ALEN] = { 0x01, 0x80, 0xc2, 0x00, 0x00, 0x00 };

static void br_pass_frame_up(struct net_bridge *br, struct sk_buff *skb)
{
	struct net_device *indev, *brdev = br->dev;

	brdev->stats.rx_packets++;
	brdev->stats.rx_bytes += skb->len;

	indev = skb->dev;
	skb->dev = brdev;

	NF_HOOK(PF_BRIDGE, NF_BR_LOCAL_IN, skb, indev, NULL,
		netif_receive_skb);
}

#ifdef CONFIG_BRIDGE_IGMPP_PROCFS
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
static void trans_32to8(uint32_t *ip, uint8_t **a)
{
	*a = (uint8_t *)ip;
	return;
}
#endif

/* br_igmpp_blcok_grp(): block specific group address for IGMP(v4)
	return 0: this addrs is ok
	retrun 1: this addrs is not allowed */
int br_igmpp_block_grp(uint32_t *grp)
{
	uint32_t baddrs[] = { htonl(0xEFFFFFFA), htonl(0xE00000FC) };  //239.255.255.250, 224.0.0.252(LLMNR)
	uint8_t baddrs_cnt = sizeof(baddrs) / sizeof(uint32_t);

	uint8_t i = 0;
	while ( i < baddrs_cnt)
	{
		if ( *grp == baddrs[i] )
		{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			uint8_t *ip8_addr;
			trans_32to8(grp, &ip8_addr);
			printk(KERN_INFO "[BR_MAC_PROC]-> block IPv4 group address, group addr (dst addr): %u.%u.%u.%u\n",
				*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3)
			);
#endif
			return 1;
		}
		i++;
	}

	return 0;
}

/* blcok_grp6(): block specific address
	return 0: this addrs is ok
	retrun 1: this addrs is not allowed */
int br_igmpp_block_grp6( struct in6_addr * ip6_addr)
{
	
	/*********************************************************************************************/
	/* check "solicited-node multicast address" (prefix: FF020000 00000000 00000001 FFXXXXXX)   */
	if (    ip6_addr->s6_addr32[0] == htonl(0xFF020000) &&
		ip6_addr->s6_addr32[1] == htonl(0x00000000) &&
		ip6_addr->s6_addr32[2] == htonl(0x00000001) &&
		ip6_addr->s6_addr [12] == 0xFF                  )
	{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "[BR_MAC_PROC]-> block IPv6 group address, group addr (dst addr): %08X %08X %08X %08X\n",
			ntohl(ip6_addr->s6_addr32[0]), ntohl(ip6_addr->s6_addr32[1]),
			ntohl(ip6_addr->s6_addr32[2]), ntohl(ip6_addr->s6_addr32[3])
		);
#endif
		/* this addrs is not allowed */
		return 1;
	}

	/*********************************************************************************************/
	/* check LLMNR address: FF02::1:3 ==> FF020000 00000000 00000000 00010003 */
	if (    ip6_addr->s6_addr32[0] == htonl(0xFF020000) &&
		ip6_addr->s6_addr32[1] == htonl(0x00000000) &&
		ip6_addr->s6_addr32[2] == htonl(0x00000000) &&
		ip6_addr->s6_addr32[3] == htonl(0x00010003)                 )
	{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "[BR_MAC_PROC]-> block IPv6 group address, group addr (dst addr): %08X %08X %08X %08X\n",
			ntohl(ip6_addr->s6_addr32[0]), ntohl(ip6_addr->s6_addr32[1]),
			ntohl(ip6_addr->s6_addr32[2]), ntohl(ip6_addr->s6_addr32[3])
		);
#endif
		/* this addrs is not allowed */
		return 1;
	}

	/* check done, it's ok */
	return 0;
}

/* snoop_MAC() => If IP address that existed in br_mac_table ,replace it,
 * else create a new list entry and add it to list.
 * called under bridge lock */
static void snoop_MAC(struct net_bridge *br ,struct sk_buff *skb2)
{
	struct iphdr *iph  = ip_hdr(skb2);
	uint32_t ip32 =  (uint32_t) iph->saddr;

	struct br_mac_table_t *tlist;
	int find = 0;;
	list_for_each_entry(tlist,&(br->br_mac_table.list), list){
		struct ethhdr * src;
		int i;
		if ( tlist->ip_addr == ip32){
			find =1;
			src = eth_hdr(skb2);
			for (i =0; i<6; i++)
				tlist->mac_addr[i] = src->h_source[i];
			break;
		}
	}
	if (find == 0 ){
		struct br_mac_table_t * new_entry;
		new_entry = (struct br_mac_table_t *)kmalloc(sizeof(struct br_mac_table_t), GFP_ATOMIC);
		if (new_entry != NULL){
			int i;
			struct ethhdr * src = eth_hdr(skb2);
			for (i =0; i<6; i++)
				new_entry->mac_addr[i] = src->h_source[i];
			new_entry->ip_addr = ip32;
			list_add(&(new_entry->list), &(br->br_mac_table.list));
		}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_MAC_PROC]-> alloc new br_mac_table_t fail !!\n");
#endif
		}
	}
}

static void igmp_join(struct net_bridge *br, struct net_bridge_fdb_entry * fdb,
					uint32_t mca, unsigned char * src_mac, uint32_t *src_ip)
{
	if(atomic_read(&fdb->dst->wireless_interface) == 1){
		struct net_bridge_port *p;
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		uint8_t *ip8_addr,*mca8_addr;
		trans_32to8(src_ip, &ip8_addr);
		trans_32to8(&mca, &mca8_addr);
		printk(KERN_INFO "[BR_MAC_PROC]-> snooping ADD [IP: %u.%u.%u.%u, MAC: %X:%X:%X:%X:%X:%X, Multicast: %u.%u.%u.%u] =====\n",
			*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3),
			src_mac[0], src_mac[1], src_mac[2], src_mac[3], src_mac[4], src_mac[5],
			*mca8_addr, *(mca8_addr+1), *(mca8_addr+2), *(mca8_addr+3)
		);
#endif

		br_igmpp_igmp_table_add(fdb->dst, &fdb->dst->port_igmpp_table, mca, src_mac);

#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "[BR_MAC_PROC]-> snooping ADD DONE !! ==============================================================\n");
#endif

#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		/* make sure there's only one record existed */
		printk(KERN_INFO "[BR_MAC_PROC]-> checking and clean all other port_igmpp_table ... \n");
#endif
		list_for_each_entry(p, &br->port_list, list) {
			if(fdb->dst->port_no != p->port_no){
				br_igmpp_igmp_table_remove(&p->port_igmpp_table, mca, src_mac);
			}
		}
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "[BR_MAC_PROC]-> clean other port_igmpp_table DONE !!\n");
#endif
	}else{ /* report from wired interface(port), we won't transforming mulitcast to unicast, so we don't add to table here*/
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		uint8_t *ip8_addr,*mca8_addr;
		trans_32to8(src_ip, &ip8_addr);
		trans_32to8(&mca, &mca8_addr);
		printk("[BR_MAC_PROC]->[IP: %u.%u.%u.%u, MAC: %X:%X:%X:%X:%X:%X, Multicast: %u.%u.%u.%u] is belong to wired interface(port) !!\n",
			*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3),
			src_mac[0], src_mac[1], src_mac[2], src_mac[3], src_mac[4], src_mac[5],
			*mca8_addr, *(mca8_addr+1), *(mca8_addr+2), *(mca8_addr+3)
		);
		printk(KERN_INFO "[BR_MAC_PROC]-> fdb.addr =  %X:%X:%X:%X:%X:%X \n",
			fdb->addr.addr[0], fdb->addr.addr[1],
			fdb->addr.addr[2], fdb->addr.addr[3],
			fdb->addr.addr[4], fdb->addr.addr[5] );
		printk(KERN_INFO "[BR_MAC_PROC]-> fdb->dst->dev.name : %s \n", fdb->dst->dev->name);
#endif
	}
}

static void igmp_leave(struct net_bridge_fdb_entry * fdb,
						uint32_t mca, unsigned char * src_mac, uint32_t *src_ip)
{
	if(atomic_read(&fdb->dst->wireless_interface) == 1){
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		uint8_t *ip8_addr,*mca8_addr;
		trans_32to8(src_ip, &ip8_addr);
		trans_32to8(&mca, &mca8_addr);
		printk(KERN_INFO "[BR_MAC_PROC]-> snooping REMOVE [IP: %u.%u.%u.%u, MAC: %X:%X:%X:%X:%X:%X, Multicast: %u.%u.%u.%u] ===== \n",
			*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3),
			src_mac[0], src_mac[1], src_mac[2], src_mac[3], src_mac[4], src_mac[5],
			*mca8_addr, *(mca8_addr+1), *(mca8_addr+2), *(mca8_addr+3)
		);
#endif

		br_igmpp_igmp_table_remove(&fdb->dst->port_igmpp_table, mca, src_mac);

#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "[BR_MAC_PROC]-> snooping REMOVE DONE !! ===============================================================\n");
#endif
	}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		uint8_t *ip8_addr,*mca8_addr;
		trans_32to8(src_ip, &ip8_addr);
		trans_32to8(&mca, &mca8_addr);
		printk("[BR_MAC_PROC]->[IP: %u.%u.%u.%u, MAC: %X:%X:%X:%X:%X:%X, Multicast: %u.%u.%u.%u] is belong to wired interface(port) !!\n",
			*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3),
			src_mac[0], src_mac[1], src_mac[2], src_mac[3], src_mac[4], src_mac[5],
			*mca8_addr, *(mca8_addr+1), *(mca8_addr+2), *(mca8_addr+3)
		);
		printk(KERN_INFO "[BR_MAC_PROC]-> fdb.addr =  %X:%X:%X:%X:%X:%X \n",
			fdb->addr.addr[0], fdb->addr.addr[1],
			fdb->addr.addr[2], fdb->addr.addr[3],
			fdb->addr.addr[4], fdb->addr.addr[5] );
		printk(KERN_INFO "[BR_MAC_PROC]-> fdb->dst->dev.name : %s \n", fdb->dst->dev->name);
#endif
	}
}

/*********************************************************************************************
snoop_IGMPV2_rep : snooping IGMPv2 report packet, add multicast address to table (port_igmpp_table),
				this function calling __br_fdb_get() to get fdb entry then calling igmp_join()
				do detailed check.
	NOTE: caller must under bridge locked
	@br:        bridge
	@igmph: pointer to igmp header
	@src_mac:   source MAC address
	@src_ip:    source IPv4 address (for debug only)
********************************************************************************************/
static void snoop_IGMPV2_rep(struct net_bridge *br, struct igmphdr * igmph,
							unsigned char *src_mac, uint32_t *src_ip)
{
	/* searching bridge_fdb_entry */
	struct net_bridge_fdb_entry *hit_fdb_entry;
	hit_fdb_entry = __br_fdb_get(br, src_mac);
	/* NOTE: The effect of successful called __br_fdb_get() also takes lock bridge and reference counts. */

	if (hit_fdb_entry != NULL){
		/* check multicast address agign. For IGMPv2 rep, it's should already checked via dst addrs at IP(v4) layer */
		if(!br_igmpp_block_grp(&igmph->group)) {
			igmp_join( br, hit_fdb_entry, igmph->group, src_mac, src_ip);
		}
		fdb_delete(hit_fdb_entry); // release br_fdb_get() locks
	}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		uint8_t *ip8_addr,*mca8_addr;
		trans_32to8(src_ip, &ip8_addr);
		trans_32to8(&igmph->group, &mca8_addr);
		printk(KERN_INFO "The return value of __br_fdb_get() is NULL->[IP:%u.%u.%u.%u, MAC:%X:%X:%X:%X:%X:%X, Multicast:%u.%u.%u.%u ]\n",
			*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3),
			src_mac[0], src_mac[1], src_mac[2], src_mac[3], src_mac[4], src_mac[5],
			*mca8_addr, *(mca8_addr+1), *(mca8_addr+2), *(mca8_addr+3)
		);
#endif
	}
}

static void snoop_IGMPV3_rep(struct net_bridge *br, struct igmphdr * igmph,
							unsigned char *src_mac, uint32_t *src_ip)
{
	struct igmpv3_report * igmpv3rep = (struct igmpv3_report *)igmph;

	/* searching bridge_fdb_entry */
	struct net_bridge_fdb_entry *hit_fdb_entry;
	hit_fdb_entry = __br_fdb_get(br, src_mac);
	/* NOTE: The effect of successful called __br_fdb_get() also takes lock bridge and reference counts. */

	if (hit_fdb_entry != NULL){
		uint16_t cnt_ngrec = ntohs(igmpv3rep->ngrec);
		uint16_t i = 0;
		while( i < cnt_ngrec )
		{
			/* for igmpv3 rep, we should check multicast address at each Mcast Address Record */
			if( !br_igmpp_block_grp(&igmpv3rep->grec[i].grec_mca))
			{
				switch (igmpv3rep->grec[i].grec_type)
				{
					case IGMPV3_MODE_IS_EXCLUDE:    /***************************************************    2: MODE_IS_EXCLUDE          */
					case IGMPV3_CHANGE_TO_EXCLUDE:  /***************************************************    4: CHANGE_TO_EXCLUDE_MODE   */
					case IGMPV3_ALLOW_NEW_SOURCES:  /*******************l*******************************    5: ALLOW_NEW_SOURCES        */
						igmp_join( br, hit_fdb_entry, igmpv3rep->grec[i].grec_mca, src_mac, src_ip);
						break;

					case IGMPV3_MODE_IS_INCLUDE:    /***************************************************    1: MODE_IS_INCLUDE          */
					case IGMPV3_CHANGE_TO_INCLUDE:  /***************************************************    3: CHANGE_TO_INCLUDE_MODE   */
					case IGMPV3_BLOCK_OLD_SOURCES:  /***************************************************    6: BLOCK_OLD_SOURCES        */
						igmp_leave(hit_fdb_entry, igmpv3rep->grec[i].grec_mca, src_mac, src_ip);
						break;

					default:
						printk(KERN_INFO "unknown IGMPv3 Group Record Type - 0x%x, ignoring!\n", igmpv3rep->grec[i].grec_type);
						break;
				}
			}//br_igmpp_block_grp() end
			i++;
		}
		fdb_delete(hit_fdb_entry); // release __br_fdb_get() locks
	}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		uint8_t *ip8_addr;
		trans_32to8(src_ip, &ip8_addr);
		printk(KERN_INFO "snoop_IGMPV3_rep() - The return value of __br_fdb_get() is NULL->[IP:%u.%u.%u.%u, MAC:%X:%X:%X:%X:%X:%X \n",
			*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3),
			src_mac[0], src_mac[1], src_mac[2], src_mac[3], src_mac[4], src_mac[5]
		);
#endif
	}
}

/*********************************************************************************************
snoop_IGMP_leave : snooping IGMPv2 leave packet, remove multicast address from table (port_igmpp_table),
				this function calling __br_fdb_get() to get fdb entry then calling igmp_leave()
				do detailed check.
	NOTE: caller must under bridge locked
	@br:        bridge
	@igmph: pointer to igmp header
	@src_mac:   source MAC address
	@src_ip:    source IPv4 address (for debug only)
********************************************************************************************/
static void snoop_IGMP_leave(struct net_bridge *br, struct igmphdr * igmph,
							unsigned char *src_mac, uint32_t *src_ip)
{
	/* searching bridge_fdb_entry */
	struct net_bridge_fdb_entry *hit_fdb_entry;
	hit_fdb_entry = __br_fdb_get(br, src_mac);
	/* NOTE: The effect of successful called __br_fdb_get() also takes lock bridge and reference counts. */

	if (hit_fdb_entry != NULL){
		igmp_leave(hit_fdb_entry, igmph->group, src_mac, src_ip);
		fdb_delete(hit_fdb_entry); // release __br_fdb_get() locks
	}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		uint8_t *ip8_addr,*mca8_addr;
		trans_32to8(src_ip, &ip8_addr);
		trans_32to8(&igmph->group, &mca8_addr);
		printk(KERN_INFO "The return value of __br_fdb_get() is NULL->[IP:%u.%u.%u.%u, MAC:%X:%X:%X:%X:%X:%X, Multicast:%u.%u.%u.%u ]\n",
			*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3),
			src_mac[0], src_mac[1], src_mac[2], src_mac[3], src_mac[4], src_mac[5],
			*mca8_addr, *(mca8_addr+1), *(mca8_addr+2), *(mca8_addr+3)
		);
#endif
	}
}

/* snoop_MAC6() => If IP address that existed in br_mac_table6 ,replace it,
 * else create a new list entry and add it to list.
 * called under bridge lock */
static void snoop_MAC6(struct net_bridge *br ,struct sk_buff *skb2)
{
	struct br_mac_table6_t *tlist;
	int find = 0;
	int i;
	struct ethhdr * src;
	struct ipv6hdr *ipv6h  = ipv6_hdr(skb2);
	list_for_each_entry(tlist,&(br->br_mac_table6.list), list){
		if ( ipv6_addr_equal(   &tlist->ip6_addr,  &ipv6h->saddr ) ){
			find =1;
			src = eth_hdr(skb2);
			for (i =0; i<6; i++)
				tlist->mac_addr[i] = src->h_source[i];
			break;
		}
	}
	if (find == 0 ){
		struct br_mac_table6_t * new_entry;
		new_entry = (struct br_mac_table6_t *)kmalloc(sizeof(struct br_mac_table6_t), GFP_ATOMIC);
		if (new_entry != NULL){
			int i;
			struct ethhdr * src = eth_hdr(skb2);
			for (i =0; i<6; i++)
				new_entry->mac_addr[i] = src->h_source[i];
			ipv6_addr_copy( &new_entry->ip6_addr, &ipv6h->saddr);
			list_add( &(new_entry->list), &(br->br_mac_table6.list) );
		}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_MAC_PROC]-> alloc new br_mac_table6_t fail !!\n");
#endif
		}
	}
}

static void mld_join(struct net_bridge *br, struct net_bridge_fdb_entry * fdb,
					struct in6_addr mca, unsigned char * src_mac, struct in6_addr *src_ip6)
{
	if(atomic_read(&fdb->dst->wireless_interface) == 1){
		struct net_bridge_port *p;
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "[BR_MAC_PROC]-> snooping ADD [IP: %08X %08X %08X %08X, MAC: %X:%X:%X:%X:%X:%X, Multicast: %08X %08X %08X %08X] ===\n",
			ntohl(src_ip6->s6_addr32[0]), ntohl(src_ip6->s6_addr32[1]),
			ntohl(src_ip6->s6_addr32[2]), ntohl(src_ip6->s6_addr32[3]),
			src_mac[0], src_mac[1], src_mac[2], src_mac[3], src_mac[4], src_mac[5],
			ntohl(mca.s6_addr32[0]), ntohl(mca.s6_addr32[1]),
			ntohl(mca.s6_addr32[2]), ntohl(mca.s6_addr32[3])
		);
#endif

		br_igmpp_mld_table_add(fdb->dst, &fdb->dst->port_igmpp_table, mca, src_mac);

#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "[BR_MAC_PROC]-> snooping ADD DONE !! =============================================================================\n");
#endif

#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		/* make sure there's only one record existed */
		printk(KERN_INFO "[BR_MAC_PROC]-> checking and clean all other port_igmpp_table ... \n");
#endif
		list_for_each_entry(p, &br->port_list, list) {
			if(fdb->dst->port_no != p->port_no){
				br_igmpp_mld_table_remove(&p->port_igmpp_table, mca, src_mac);
			}
		}
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "[BR_MAC_PROC]-> clean other port_igmpp_table DONE !!\n");
#endif
	}else{ /* report from wired interface(port), we won't transforming mulitcast to unicast, so we don't add to table here*/
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk("[BR_MAC_PROC]->[IP: %08X %08X %08X %08X, MAC: %X:%X:%X:%X:%X:%X, Multicast: %08X %08X %08X %08X] is belong to wired interface(port) !!\n",
			ntohl(src_ip6->s6_addr32[0]), ntohl(src_ip6->s6_addr32[1]),
			ntohl(src_ip6->s6_addr32[2]), ntohl(src_ip6->s6_addr32[3]),
			src_mac[0], src_mac[1], src_mac[2], src_mac[3], src_mac[4], src_mac[5],
			ntohl(mca.s6_addr32[0]), ntohl(mca.s6_addr32[1]),
			ntohl(mca.s6_addr32[2]), ntohl(mca.s6_addr32[3])
		);
		printk(KERN_INFO "[BR_MAC_PROC]-> fdb.addr =  %X:%X:%X:%X:%X:%X \n",
			fdb->addr.addr[0], fdb->addr.addr[1],
			fdb->addr.addr[2], fdb->addr.addr[3],
			fdb->addr.addr[4], fdb->addr.addr[5] );
		printk(KERN_INFO "[BR_MAC_PROC]-> fdb->dst->dev.name : %s \n", fdb->dst->dev->name);
#endif
	}
}

static void mld_leave(struct net_bridge_fdb_entry * fdb,
					struct in6_addr mca, unsigned char * src_mac,
					struct in6_addr *src_ip6)
{
	if(atomic_read(&fdb->dst->wireless_interface) == 1){
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "[BR_MAC_PROC]-> snooping REMOVE [IP: %08X %08X %08X %08X, MAC: %X:%X:%X:%X:%X:%X, Multicast: %08X %08X %08X %08X] === \n",
			ntohl(src_ip6->s6_addr32[0]), ntohl(src_ip6->s6_addr32[1]),
			ntohl(src_ip6->s6_addr32[2]), ntohl(src_ip6->s6_addr32[3]),
			src_mac[0], src_mac[1], src_mac[2], src_mac[3], src_mac[4], src_mac[5],
			ntohl(mca.s6_addr32[0]), ntohl(mca.s6_addr32[1]),
			ntohl(mca.s6_addr32[2]), ntohl(mca.s6_addr32[3])
		);
#endif

		br_igmpp_mld_table_remove(&fdb->dst->port_igmpp_table, mca, src_mac);

#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "[BR_MAC_PROC]-> snooping REMOVE DONE !! =============================================================================\n");
#endif
	}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk("[BR_MAC_PROC]->[IP: %08X %08X %08X %08X, MAC: %X:%X:%X:%X:%X:%X, Multicast: %08X %08X %08X %08X] is belong to wired interface(port) !!\n",
			ntohl(src_ip6->s6_addr32[0]), ntohl(src_ip6->s6_addr32[1]),
			ntohl(src_ip6->s6_addr32[2]), ntohl(src_ip6->s6_addr32[3]),
			src_mac[0], src_mac[1], src_mac[2], src_mac[3], src_mac[4], src_mac[5],
			ntohl(mca.s6_addr32[0]), ntohl(mca.s6_addr32[1]),
			ntohl(mca.s6_addr32[2]), ntohl(mca.s6_addr32[3])
		);
		printk(KERN_INFO "[BR_MAC_PROC]-> fdb.addr =  %X:%X:%X:%X:%X:%X \n",
			fdb->addr.addr[0], fdb->addr.addr[1],
			fdb->addr.addr[2], fdb->addr.addr[3],
			fdb->addr.addr[4], fdb->addr.addr[5] );
		printk(KERN_INFO "[BR_MAC_PROC]-> fdb->dst->dev.name : %s \n", fdb->dst->dev->name);
#endif
	}
}

/*********************************************************************************************
snoop_MLD_rep : snooping MLDv1 report packet, add multicast address to table (port_igmpp_table),
				this function calling "br_igmpp_mld_table_add()" and "br_igmpp_mld_table_remove"
				do detailed check.
	NOTE: caller must under bridge locked
	@br:        bridge
	@ip6icmp6:  pointer to icmpv6 header
	@src_mac:   source MAC address
	@src_ip6:   source IPv6 address (for debug only)
********************************************************************************************/
static void snoop_MLD_rep(struct net_bridge *br, struct icmp6hdr * ip6icmp6,
							unsigned char *src_mac, struct in6_addr *src_ip6)
{
	struct igmpp_mldhdr * mldrep = (struct igmpp_mldhdr *)ip6icmp6;

	/* searching bridge_fdb_entry */
	struct net_bridge_fdb_entry *hit_fdb_entry;
	hit_fdb_entry = __br_fdb_get(br, src_mac);
	/* NOTE: The effect of successful called __br_fdb_get() also takes lock bridge and reference counts. */

	if (hit_fdb_entry != NULL){
		/* check multicast address agign. For MLDv1 rep, it's should already checked via dst addrs at IP(v6) layer */
		if(!br_igmpp_block_grp6(&mldrep->mca)) {
			mld_join( br, hit_fdb_entry, mldrep->mca, src_mac, src_ip6);
		}
		fdb_delete(hit_fdb_entry); // release __br_fdb_get() locks
	}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "The return value of __br_fdb_get() is NULL->[IP:%08X %08X %08X %08X, MAC:%X:%X:%X:%X:%X:%X, Multicast:%08X %08X %08X %08X]\n",
			ntohl(src_ip6->s6_addr32[0]), ntohl(src_ip6->s6_addr32[1]),
			ntohl(src_ip6->s6_addr32[2]), ntohl(src_ip6->s6_addr32[3]),
			src_mac[0], src_mac[1], src_mac[2], src_mac[3], src_mac[4], src_mac[5],
			ntohl(mldrep->mca.s6_addr32[0]), ntohl(mldrep->mca.s6_addr32[1]),
			ntohl(mldrep->mca.s6_addr32[2]), ntohl(mldrep->mca.s6_addr32[3])
		);
#endif
	}
}

static void snoop_MLD2_rep(struct net_bridge *br, struct icmp6hdr * ip6icmp6,
							unsigned char *src_mac, struct in6_addr *src_ip6)
{
	struct igmpp_mld2_report * mld2rep = (struct igmpp_mld2_report *)ip6icmp6;

	/* searching bridge_fdb_entry */
	struct net_bridge_fdb_entry *hit_fdb_entry;
	hit_fdb_entry = __br_fdb_get(br, src_mac);
	/* NOTE: The effect of successful called __br_fdb_get() also takes lock bridge and reference counts. */

	if (hit_fdb_entry != NULL){
		uint16_t cnt_ngrec = ntohs(mld2rep->ngrec);
		uint16_t i = 0;
		while( i < cnt_ngrec )
		{
			/* for mldv2 rep, we should check multicast address at each Mcast Address Record */
			if( !br_igmpp_block_grp6(&mld2rep->grec[i].grec_mca))
			{
				switch (mld2rep->grec[i].grec_type)
				{
					case MLD2_MODE_IS_INCLUDE:    /***************************************************    1: MODE_IS_INCLUDE          */
					case MLD2_CHANGE_TO_EXCLUDE:  /***************************************************    4: CHANGE_TO_EXCLUDE_MODE   */
					case MLD2_ALLOW_NEW_SOURCES:  /*******************l*******************************    5: ALLOW_NEW_SOURCES        */
						mld_join( br, hit_fdb_entry, mld2rep->grec[i].grec_mca, src_mac, src_ip6);
						break;

					case MLD2_MODE_IS_EXCLUDE:    /***************************************************    2: MODE_IS_EXCLUDE          */
					case MLD2_CHANGE_TO_INCLUDE:  /***************************************************    3: CHANGE_TO_INCLUDE_MODE   */
					case MLD2_BLOCK_OLD_SOURCES:  /***************************************************    6: BLOCK_OLD_SOURCES        */
						mld_leave(hit_fdb_entry, mld2rep->grec[i].grec_mca, src_mac, src_ip6);
						break;

					default:
						printk(KERN_INFO "unknown MLDv2 Group Record Type - 0x%x, ignoring!\n", mld2rep->grec[i].grec_type);
						break;
				}
			}//br_igmpp_block_grp6() end
			i++;
		}
		fdb_delete(hit_fdb_entry); // release __br_fdb_get() locks
	}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "snoop_MLD2_rep() - The return value of __br_fdb_get() is NULL->[IP:%08X %08X %08X %08X, MAC:%X:%X:%X:%X:%X:%X \n",
			ntohl(src_ip6->s6_addr32[0]), ntohl(src_ip6->s6_addr32[1]),
			ntohl(src_ip6->s6_addr32[2]), ntohl(src_ip6->s6_addr32[3]),
			src_mac[0], src_mac[1], src_mac[2], src_mac[3], src_mac[4], src_mac[5]
		);
#endif
	}
}

/*********************************************************************************************
snoop_MLD_done : snooping MLD done packet, remove multicast address from table (port_igmpp_table),
				this function calling "br_igmpp_mld_table_remove" do detailed check.
	NOTE: caller must under bridge locked
	@br:        bridge
	@ip6icmp6:  pointer to icmpv6 header
	@src_mac:   source MAC address
	@src_ip6:   source IPv6 address (for debug only)
********************************************************************************************/
static void snoop_MLD_done(struct net_bridge *br, struct icmp6hdr * ip6icmp6,
							unsigned char *src_mac, struct in6_addr *src_ip6)
{
	struct igmpp_mldhdr * mldrep = (struct igmpp_mldhdr *)ip6icmp6;

	/* searching bridge_fdb_entry */
	struct net_bridge_fdb_entry *hit_fdb_entry;
	hit_fdb_entry = __br_fdb_get(br, src_mac);
	/* NOTE: The effect of successful called __br_fdb_get() also takes lock bridge and reference counts. */

	if (hit_fdb_entry != NULL){
		mld_leave(hit_fdb_entry, mldrep->mca, src_mac, src_ip6);
		fdb_delete(hit_fdb_entry); // release __br_fdb_get() locks
	}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "The return value of __br_fdb_get() is NULL->[IP:%08X %08X %08X %08X, MAC:%X:%X:%X:%X:%X:%X, Multicast:%08X %08X %08X %08X]\n",
			ntohl(src_ip6->s6_addr32[0]), ntohl(src_ip6->s6_addr32[1]),
			ntohl(src_ip6->s6_addr32[2]), ntohl(src_ip6->s6_addr32[3]),
			src_mac[0], src_mac[1], src_mac[2], src_mac[3], src_mac[4], src_mac[5],
			ntohl(mldrep->mca.s6_addr32[0]), ntohl(mldrep->mca.s6_addr32[1]),
			ntohl(mldrep->mca.s6_addr32[2]), ntohl(mldrep->mca.s6_addr32[3])
		);
#endif
	}
}
#endif
struct BLOCK_LIST
{
	unsigned char proto;
	unsigned short port;	
}block_list[]=
{
	{IPPROTO_ICMP,0},
//	{NEXTHDR_ICMP,0},
	{IPPROTO_TCP,80}		
};
int g_sizeof_blklist=sizeof(block_list)/sizeof(struct BLOCK_LIST);
int can_passthrough(struct sk_buff *skb)
{
	int ret=1;
	int i=0;

	if(eth_hdr(skb)->h_proto==htons(ETH_P_IP))
	{
		struct iphdr *iphdr;
		iphdr=ip_hdr(skb);

		unsigned short *l4hdr=(unsigned char *)iphdr+iphdr->ihl*4;
		unsigned short dport=htons(l4hdr[1]);	
	
		for(i=0;i<=g_sizeof_blklist;i++)
		{
			//marco, for v4 block tcp dest port==80 and icmp packet only
			if(iphdr->protocol==block_list[i].proto && (!block_list[i].port|| dport==block_list[i].port))
			{
				ret=0;
			}
		}
	}
	else if( eth_hdr(skb)->h_proto==htons(ETH_P_IPV6) )
	{
		struct ipv6hdr *v6hdr;
		v6hdr = ipv6_hdr(skb);
		
		unsigned short *l4hdr=(unsigned char *)v6hdr + sizeof(struct ipv6hdr);
		unsigned short dport=htons(l4hdr[1]);	
		for(i=0;i<=g_sizeof_blklist;i++)
		{
			//marco, for v6 block tcp dest port==80
			if(v6hdr->nexthdr==block_list[i].proto && (!block_list[i].port|| dport==block_list[i].port))
			{
				ret=0;
			}
		}
	}
	return ret;
}
/* note: already called with rcu_read_lock (preempt_disabled) */
int br_handle_frame_finish(struct sk_buff *skb)
{
	const unsigned char *dest = eth_hdr(skb)->h_dest;
	struct net_bridge_port *p = rcu_dereference(skb->dev->br_port);
	struct net_bridge *br;
	struct net_bridge_fdb_entry *dst;
	struct sk_buff *skb2;

	if (!p || p->state == BR_STATE_DISABLED)
		goto drop;

	/* insert into forwarding database after filtering to avoid spoofing */
	br = p->br;
	br_fdb_update(br, p, eth_hdr(skb)->h_source);

	if (p->state == BR_STATE_LEARNING)
		goto drop;

	/* The packet skb2 goes to the local host (NULL to skip). */
	skb2 = NULL;

	if (br->dev->flags & IFF_PROMISC)
		skb2 = skb;

	dst = NULL;

	if (is_multicast_ether_addr(dest)) {
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS
		spin_lock_bh(&br->lock); // bridge lock

		if (atomic_read(&br->br_mac_table_enable) == 1 )
		{
			/* check IP version */
			/* IPv4 ***********************************************************************************/
			if ( eth_hdr(skb)->h_proto == htons(ETH_P_IP) ) {
				/* IPv4 IGMP snooping MAC */
				if(ip_hdr(skb)->protocol == IPPROTO_IGMP){ // IGMP protocol number: 0x02
					struct sk_buff *skb2;
					if ((skb2 = skb_clone(skb, GFP_ATOMIC)) != NULL) {
						struct igmphdr *ih;
						skb_pull(skb2, ip_hdr(skb2)->ihl<<2);
						ih = (struct igmphdr *) skb2->data;
						if (ih->type == IGMP_HOST_MEMBERSHIP_REPORT     ||      // IGMPv1 REPORT
							ih->type == IGMPV2_HOST_MEMBERSHIP_REPORT   ||      // IGMPv2 REPORT
							ih->type == IGMPV3_HOST_MEMBERSHIP_REPORT   ||      // IGMPv3 REPORT
							ih->type == IGMP_HOST_LEAVE_MESSAGE             )   // IGMP LEAVE
						{
							snoop_MAC(br, skb2);

							if ( !br_igmpp_block_grp(&ip_hdr(skb2)->daddr) )
							{
								switch (ih->type)
								{
									case IGMPV2_HOST_MEMBERSHIP_REPORT:
										snoop_IGMPV2_rep(br, ih, eth_hdr(skb2)->h_source, &ip_hdr(skb2)->saddr);
										break;
									case IGMPV3_HOST_MEMBERSHIP_REPORT:
										snoop_IGMPV3_rep(br, ih, eth_hdr(skb2)->h_source, &ip_hdr(skb2)->saddr);
										break;
									case IGMP_HOST_LEAVE_MESSAGE:
										snoop_IGMP_leave(br, ih, eth_hdr(skb2)->h_source, &ip_hdr(skb2)->saddr);
										break;
									default:
										break;
								}
							}
						}// end - IGMP v1/v2/v3 report and leave check
						kfree_skb(skb2);
					}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
						printk(KERN_INFO "[BR_MAC_PROC]-> alloc new sk_buff fail !!\n");
#endif
					}// end - skb clone fail check
				}// end of IPv4 IGMP check
			}
			/* end of IPv4 ****************************************************************************/

			/* IPv6 ***********************************************************************************/
			/* check next header is IPv6 at MAC layer first */
			if ( eth_hdr(skb)->h_proto == htons(ETH_P_IPV6) ) {
				/* check next header is IPv6 hop-by-hop at IP layer and not wildcard address*/
				if( (ipv6_hdr(skb)->nexthdr == IPPROTO_HOPOPTS) &&      // check IPv6 hop-by-hop and
					!ipv6_addr_any(&ipv6_hdr(skb)->saddr)           )   // src addr not a wildcard address (::)
				{
					struct sk_buff *skb2;
					if ((skb2 = skb_clone(skb, GFP_ATOMIC)) != NULL) {
						struct ipv6_hopopt_hdr *ip6hopopt;
						/* strip ipv6 header, ipv6hdr len :40, meaning point skb2->data to ipv6 hop-by-hop */
						skb_pull(skb2, sizeof(struct ipv6hdr));

						ip6hopopt = (struct ipv6_hopopt_hdr *) skb2->data;

						/* check next header is icmpv6 at hop-by-hop header (still at IP layer)*/
						if (ip6hopopt->nexthdr == IPPROTO_ICMPV6 ){
							struct icmp6hdr *ip6icmp6;
							/* strip ipv6 hop-by-hop header,  point skb2->data to icmpv6 */
							skb_pull(skb2,ipv6_optlen(ip6hopopt));
							ip6icmp6 = (struct icmp6hdr *) skb2->data;

							/* check icmpv6 is MLD  */
							if (ip6icmp6->icmp6_type == ICMPV6_MGM_REPORT       ||      // MLDv1 report: 131
								ip6icmp6->icmp6_type == ICMPV6_MLD2_REPORT      ||      // or MLDv2 report: 143
								ip6icmp6->icmp6_type == ICMPV6_MGM_REDUCTION        )   // or MLD Done: 132
							{
								snoop_MAC6(br, skb2);

								if ( !br_igmpp_block_grp6(&ipv6_hdr(skb2)->daddr) )
								{
									switch (ip6icmp6->icmp6_type)
									{
										case ICMPV6_MGM_REPORT:
											snoop_MLD_rep(br, ip6icmp6, eth_hdr(skb2)->h_source, &ipv6_hdr(skb2)->saddr);
											break;
										case ICMPV6_MLD2_REPORT:
											snoop_MLD2_rep(br, ip6icmp6, eth_hdr(skb2)->h_source, &ipv6_hdr(skb2)->saddr);
											break;
										case ICMPV6_MGM_REDUCTION:
											snoop_MLD_done(br, ip6icmp6, eth_hdr(skb2)->h_source, &ipv6_hdr(skb2)->saddr);
											break;
										default:
											break;
									}
								}
							} // end - MLDv1/MLDv2 check
						} // end - icmpv6 check

						kfree_skb(skb2);
					}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
						printk(KERN_INFO "[BR_MAC_PROC]-> alloc new sk_buff fail !!\n");
#endif
					} // end - skb clone fail check
				}// end - IPv6 hop-by-hop check
			}
			/* end of IPv6 ****************************************************************************/
		} // END

		spin_unlock_bh(&br->lock); // bridge unlock
#endif
		br->dev->stats.multicast++;
		skb2 = skb;
	} else if ((dst = __br_fdb_get(br, dest)) && dst->is_local) {
		skb2 = skb;
		/* Do not forward the packet since it's local. */
		skb = NULL;
	}

	if (skb2 == skb)
		skb2 = skb_clone(skb, GFP_ATOMIC);
#ifdef CONFIG_BRIDGE_PORT_GROUP	
	
	if (skb2)
	{
		//printk(" local check groups %x %s\n",br->groups,br->dev->name);
		//if(skb->dev->br_port) printk("loacl skb groups %x %s\n",skb->dev->br_port->groups,skb->dev->name);
		if(skb2->dev->br_port && !br->GroupIsAllow && (br->groups & skb2->dev->br_port->groups) &&!can_passthrough(skb2) )
		{
			//printk("%s %d\n",__FUNCTION__,__LINE__);
			//printk("drop packer from %s(group %x)->%s(group %x)\n",skb2->dev->name,skb2->dev->br_port->groups,br->dev->name,br->groups);
			kfree_skb(skb2);
		}
		else if(skb2->dev->br_port && br->GroupIsAllow && !(br->groups & skb2->dev->br_port->groups) &&!can_passthrough(skb2) )
		{
			//printk("%s %d\n",__FUNCTION__,__LINE__);
			//printk("drop packer from %s(group %x)->%s(group %x)\n",skb2->dev->name,skb2->dev->br_port->groups,br->dev->name,br->groups);
			kfree_skb(skb2);
		}
		else
		br_pass_frame_up(br, skb2);
	}
#else
	if (skb2)
		br_pass_frame_up(br, skb2);
#endif
	if (skb) {
		if (dst)
			br_forward(dst->dst, skb);
		else
			br_flood_forward(br, skb);
	}

out:
	return 0;
drop:
	kfree_skb(skb);
	goto out;
}

/* note: already called with rcu_read_lock (preempt_disabled) */
static int br_handle_local_finish(struct sk_buff *skb)
{
	struct net_bridge_port *p = rcu_dereference(skb->dev->br_port);

	if (p)
		br_fdb_update(p->br, p, eth_hdr(skb)->h_source);
	return 0;	 /* process further */
}

/* Does address match the link local multicast address.
 * 01:80:c2:00:00:0X
 */
static inline int is_link_local(const unsigned char *dest)
{
	__be16 *a = (__be16 *)dest;
	static const __be16 *b = (const __be16 *)br_group_address;
	static const __be16 m = cpu_to_be16(0xfff0);

	return ((a[0] ^ b[0]) | (a[1] ^ b[1]) | ((a[2] ^ b[2]) & m)) == 0;
}

/*
 * Called via br_handle_frame_hook.
 * Return NULL if skb is handled
 * note: already called with rcu_read_lock (preempt_disabled)
 */
struct sk_buff *br_handle_frame(struct net_bridge_port *p, struct sk_buff *skb)
{
	const unsigned char *dest = eth_hdr(skb)->h_dest;
	int (*rhook)(struct sk_buff *skb);

	if (!is_valid_ether_addr(eth_hdr(skb)->h_source))
		goto drop;

	skb = skb_share_check(skb, GFP_ATOMIC);
	if (!skb)
		return NULL;

	if (unlikely(is_link_local(dest))) {
		/* Pause frames shouldn't be passed up by driver anyway */
		if (skb->protocol == htons(ETH_P_PAUSE))
			goto drop;

		/* If STP is turned off, then forward */
		if (p->br->stp_enabled == BR_NO_STP && dest[5] == 0)
			goto forward;

		if (NF_HOOK(PF_BRIDGE, NF_BR_LOCAL_IN, skb, skb->dev,
			    NULL, br_handle_local_finish))
			return NULL;	/* frame consumed by filter */
		else
			return skb;	/* continue processing */
	}

forward:
	switch (p->state) {
	case BR_STATE_FORWARDING:
		rhook = rcu_dereference(br_should_route_hook);
		if (rhook != NULL) {
			if (rhook(skb))
				return skb;
			dest = eth_hdr(skb)->h_dest;
		}
		/* fall through */
	case BR_STATE_LEARNING:
		if (!compare_ether_addr(p->br->dev->dev_addr, dest))
			skb->pkt_type = PACKET_HOST;

		NF_HOOK(PF_BRIDGE, NF_BR_PRE_ROUTING, skb, skb->dev, NULL,
			br_handle_frame_finish);
		break;
	default:
drop:
		kfree_skb(skb);
	}
	return NULL;
}
