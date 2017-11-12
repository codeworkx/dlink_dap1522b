/*
 *	Forwarding decision
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
#include <linux/skbuff.h>
#include <linux/if_vlan.h>
#include <linux/netfilter_bridge.h>
#include "br_private.h"
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS
#include <linux/in.h>
#include <linux/ip.h>
#include <linux/ipv6.h>
unsigned char bcast_mac_addr[6] = { 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF };
#elif CONFIG_BRIDGE_ALPHA_MULTICAST_SNOOP
unsigned char bcast_mac_addr[6] = { 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF };
#endif

#ifdef CONFIG_BRIDGE_E_PARTITION_BWCTRL
static int e_partition_deliver(const struct net_bridge_port *p,
				 const struct sk_buff *skb)
{
	struct net_bridge *br = p->br;
	unsigned char * dest;
	dest = eth_hdr(skb)->h_dest;
	struct iphdr e_partition_iph;
	struct iphdr *e_partition_ih; //add by yuda
	
	unsigned short eth_protocol = eth_hdr(skb)->h_proto;
	if((br->e_partition==1) &&(dest[0]==0xFF))
	{
		if(!(strcmp(skb->dev->name,"eth0"))){
			if(ntohs(eth_protocol)==0x0800) 
			{
				e_partition_ih = skb_header_pointer(skb, 0, sizeof(e_partition_iph), &e_partition_iph);
				if(e_partition_ih->protocol==0x11) //protocol is UDP
				{
					struct udphdr _ports, *pptr;
					pptr = skb_header_pointer(skb, e_partition_ih->ihl*4,sizeof(_ports), &_ports);
					if(pptr->dest==0x43 || pptr->dest==0x44) //pass bootps and bootpc by yuda
					{
					}
					else
					{
						return 0;
					}
				}
				else
				{
					return 0;
				}
			}
			/*pass DHCP packet by yuda end*/
			else
			{
			//other pack(arp packet) so drop it
			     return 0;
			}
		}
	}	
	/*block multicast packet if e_partition = 1 by yuda start*/
	else if(br->e_partition==1 && (dest[0]==0x01) && (dest[1]==0x00) && (dest[2]==0x5E) && (dest[3]<=0x7F))
	{
		if(!(strcmp(skb->dev->name,"eth0")))
		{
			return 0;
		}
	}
	/*block multicast packet if e_partition = 1 by yuda end*/
	return 1;
 }
#endif

/* Don't forward packets to originating port or forwarding diasabled */
static inline int should_deliver( struct net_bridge_port *p,
				 const struct sk_buff *skb)
{
#ifdef CONFIG_BRIDGE_MULTICAST_BWCTRL
	unsigned char * dest;
	struct ethhdr *eth = eth_hdr(skb);
#endif
	
	//return (((p->flags & BR_HAIRPIN_MODE) || skb->dev != p->dev) &&
	//	p->state == BR_STATE_FORWARDING);
	if ((((p->flags & BR_HAIRPIN_MODE) || skb->dev != p->dev) && p->state == BR_STATE_FORWARDING) != 1 )
		return 0;

#ifdef CONFIG_BRIDGE_MULTICAST_BWCTRL
	dest = eth->h_dest;
	/* Bouble- 20100514 - 
	  * Original code will limit bandwidth on broadcast and multicast 
	  * Current code will limit on multicase only
	  * Another, for CPU overhead consideration, will not use memncmp(),
	  * just only partial compare with dest[0] only
	  */
	//if ((dest[0] & 1) && p->bandwidth !=0) {
	if ((dest[0] & 1) && (dest[0] != 0xFF) && p->bandwidth !=0) {
		if ((p->accumulation + skb->len) > p->bandwidth) 
			return 0;
		p->accumulation += skb->len;
	}
#endif
#ifdef CONFIG_BRIDGE_E_PARTITION_BWCTRL
	if(!e_partition_deliver(p, skb))
                return 0;
#endif
	return 1;
}

static inline unsigned packet_length(const struct sk_buff *skb)
{
	return skb->len - (skb->protocol == htons(ETH_P_8021Q) ? VLAN_HLEN : 0);
}

int br_dev_queue_push_xmit(struct sk_buff *skb)
{
	/* drop mtu oversized packets except gso */
	if (packet_length(skb) > skb->dev->mtu && !skb_is_gso(skb))
	{
		kfree_skb(skb);
	}
	else {
		/* ip_refrag calls ip_fragment, doesn't copy the MAC header. */
		if (nf_bridge_maybe_copy_header(skb))
			kfree_skb(skb);
		else {
			skb_push(skb, ETH_HLEN);

			dev_queue_xmit(skb);
		}
	}

	return 0;
}

int br_forward_finish(struct sk_buff *skb)
{
	return NF_HOOK(PF_BRIDGE, NF_BR_POST_ROUTING, skb, NULL, skb->dev,
		       br_dev_queue_push_xmit);

}

static void __br_deliver(const struct net_bridge_port *to, struct sk_buff *skb)
{
	skb->dev = to->dev;
	NF_HOOK(PF_BRIDGE, NF_BR_LOCAL_OUT, skb, NULL, skb->dev,
			br_forward_finish);
}

static void __br_forward(const struct net_bridge_port *to, struct sk_buff *skb)
{
	struct net_device *indev;

	if (skb_warn_if_lro(skb)) {
		kfree_skb(skb);
		return;
	}


#ifdef CONFIG_BRIDGE_PORT_GROUP
	//printk("check groups %x %x %s\n",to->groups,skb->dev->br_port,to->dev->name);
	//if(skb->dev->br_port) printk("skb groups %x %s\n",skb->dev->br_port->groups,skb->dev->name);
	if(skb->dev->br_port)
	{
		struct ethhdr *ethh = eth_hdr(skb);
		const unsigned char *dest = ethh->h_dest;
		extern int can_passthrough(struct sk_buff *skb);	
		
		if(!skb->dev->br_port->br->GroupIsAllow && (to->groups & skb->dev->br_port->groups) &&!can_passthrough(skb))//same group and drop same group
		{
			//printk("drop packer from %s(group %x)->%s(group %x)\n",skb->dev->name,skb->dev->br_port->groups,to->dev->name,to->groups);
			kfree_skb(skb);
			return;	
		}
		else if(skb->dev->br_port->br->GroupIsAllow && !(to->groups & skb->dev->br_port->groups) &&!can_passthrough(skb))
		{
			//printk("drop packer from %s(group %x)->%s(group %x)\n",skb->dev->name,skb->dev->br_port->groups,to->dev->name,to->groups);
			kfree_skb(skb);
			return;	
		}
	}
#endif

	indev = skb->dev;
	skb->dev = to->dev;
	skb_forward_csum(skb);

	NF_HOOK(PF_BRIDGE, NF_BR_FORWARD, skb, indev, skb->dev,
			br_forward_finish);
}

/* called with rcu_read_lock */
void br_deliver( struct net_bridge_port *to, struct sk_buff *skb)
{
	if (should_deliver(to, skb)) {
		__br_deliver(to, skb);
		return;
	}

	kfree_skb(skb);
}

/* called with rcu_read_lock */
void br_forward( struct net_bridge_port *to, struct sk_buff *skb)
{
	if (should_deliver(to, skb)) {
		__br_forward(to, skb);
		return;
	}

	kfree_skb(skb);
}
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS
static void copy_mac(unsigned char* to, unsigned char * from)
{
#if 0
	int i;
	for(i=0; i<6; i++)
		*(to+i)=*(from+i);
#endif
	memcpy(to, from, sizeof(uint8_t)*6 );
	return;
}
#endif

/* called under bridge lock */
static void br_flood(struct net_bridge *br, struct sk_buff *skb,
	void (*__packet_hook)(const struct net_bridge_port *p,
			      struct sk_buff *skb))
{
	struct net_bridge_port *p;
	struct net_bridge_port *prev;

#ifdef CONFIG_BRIDGE_IGMPP_PROCFS
	if( (atomic_read(&br->br_igmpp_table_enable) == 1)  &&              // wireless enhance enable ?!
		(memcmp(eth_hdr(skb)->h_dest, bcast_mac_addr, 6) != 0) &&       // non-broadcast packet ?!
		( (eth_hdr(skb)->h_proto == htons(ETH_P_IP)) || (eth_hdr(skb)->h_proto == htons(ETH_P_IPV6)))   ) // either IPv4 or IPv6
	{
		if (eth_hdr(skb)->h_proto == htons(ETH_P_IP) ) { // IPv4 --------------------------------------------------------------------
			list_for_each_entry_rcu(p, &br->port_list, list) {
				struct sk_buff *skb2;
				struct iphdr *iph  = ip_hdr(skb);

				if ( (atomic_read(&p->wireless_interface) == 1)  // wireless interface
					&& (iph->protocol == IPPROTO_UDP) ) //  only allow UDP packets ( IPPROTO_UDP: 17)
				{
					/*  does group address stored in table ? */
					int groupIdx;
					groupIdx = br_igmpp_search_group_IP( &p->port_igmpp_table, iph->daddr);
					if (groupIdx >=0){
						/* skb_copy for each host*/
						int i;
						for(i=0; i<HOSTLIST_NUMBER; i++){
							if (p->port_igmpp_table.group_list[groupIdx].host_list[i].used ==1){
								struct ethhdr * dest;
								if ((skb2 = skb_copy(skb, GFP_ATOMIC)) == NULL) {
									br->dev->stats.tx_dropped++;
									kfree_skb(skb);
									return;
								}
								dest = eth_hdr(skb2);
								copy_mac( dest->h_dest, p->port_igmpp_table.group_list[groupIdx].host_list[i].mac_addr);
								if (should_deliver(p, skb2))
									__packet_hook(p, skb2);
								else
									kfree_skb(skb2);
							}// if used - END
						}// for loop - END
					}else { /* skb's destination IP address does't match in port_igmpp_table */
						/* check specific address, if skb's dest IP address match specific address, we flooding it */
						if (br_igmpp_block_grp(&iph->daddr))
						{
							if ((skb2 = skb_clone(skb, GFP_ATOMIC)) == NULL) {
								br->dev->stats.tx_dropped++;
								kfree_skb(skb);
								return;
							}
							if (should_deliver(p,skb2))
								__packet_hook(p, skb2);
							else
								kfree_skb(skb2);
						}else{ /* else, do nothing, drop!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! */
						}
					}// groupIdx >=0 - END
				}else{
					/* it's wired interface or non-UDP packets*/
					/* skb_clone.....(flooding) */
					if ((skb2 = skb_clone(skb, GFP_ATOMIC)) == NULL) {
						br->dev->stats.tx_dropped++;
						kfree_skb(skb);
						return;
					}
					if (should_deliver(p,skb2))
						__packet_hook(p, skb2);
					else
						kfree_skb(skb2);
				}// interface & UDP check - END
			} //list_for_each_entry_rcu() - END
		} // end of IPv4 ------------------------------------------------------------------------------------------------------------
		else { // IPv6 --------------------------------------------------------------------------------------------------------------
			list_for_each_entry_rcu(p, &br->port_list, list) {
				struct sk_buff *skb2;
				struct ipv6hdr *ipv6h  = ipv6_hdr(skb);
				if ( (atomic_read(&p->wireless_interface) == 1)  // wireless interface
					&& (ipv6h->nexthdr == NEXTHDR_UDP) ) // only allow UDP packets ( NEXTHDR_UDP: 17)
				{
					/*  does group address stored in table ? */
					int groupIdx;
					groupIdx = br_igmpp_search_group_IP6( &p->port_igmpp_table, ipv6h->daddr);
					if (groupIdx >=0){
						/* skb_copy for each host*/
						int i;
						for(i=0; i<HOSTLIST_NUMBER; i++){
							if (p->port_igmpp_table.group_list6[groupIdx].host_list[i].used ==1){
								struct ethhdr * dest;
								if ((skb2 = skb_copy(skb, GFP_ATOMIC)) == NULL) {
									br->dev->stats.tx_dropped++;
									kfree_skb(skb);
									return;
								}
								dest = eth_hdr(skb2);
								copy_mac( dest->h_dest, p->port_igmpp_table.group_list6[groupIdx].host_list[i].mac_addr);
								if (should_deliver(p, skb2))
									__packet_hook(p, skb2);
								else
									kfree_skb(skb2);
							}// if used - END
						}// for loop - END
					}else { /* skb's destination IP address does't match in port_igmpp_table */
						/* check specific address, if skb's dest IP address match specific address, we flooding it */
						if (br_igmpp_block_grp6(&ipv6h->daddr))
						{
							if ((skb2 = skb_clone(skb, GFP_ATOMIC)) == NULL) {
								br->dev->stats.tx_dropped++;
								kfree_skb(skb);
								return;
							}
							if (should_deliver(p,skb2))
								__packet_hook(p, skb2);
							else
								kfree_skb(skb2);
						}else{ /* else, do nothing, drop!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! */
						}
					}// groupIdx >=0 - END
				}else{
					/* it's wired interface or non-UDP packets*/
					/* skb_clone..... */
					if ((skb2 = skb_clone(skb, GFP_ATOMIC)) == NULL) {
						br->dev->stats.tx_dropped++;
						kfree_skb(skb);
						return;
					}
					if (should_deliver(p,skb2))
						__packet_hook(p, skb2);
					else
						kfree_skb(skb2);
				}// interface & UDP check - END
			} //list_for_each_entry_rcu() - END
		} // end of IPv6 ------------------------------------------------------------------------------------------------------------
		kfree_skb(skb);
	}
	else
	{ // (wireless enhance mode disabled) or (broadcast packet) or (neither IPv4 nor IPv6)
		prev = NULL;

		list_for_each_entry_rcu(p, &br->port_list, list) {
			if (should_deliver(p, skb)) {
				if (prev != NULL) {
					struct sk_buff *skb2;

					if ((skb2 = skb_clone(skb, GFP_ATOMIC)) == NULL) {
						br->dev->stats.tx_dropped++;
						kfree_skb(skb);
						return;
					}

					__packet_hook(prev, skb2);
				}

				prev = p;
			}
		}

		if (prev != NULL) {
			__packet_hook(prev, skb);
			return;
		}

		kfree_skb(skb);
	}
#elif CONFIG_BRIDGE_ALPHA_MULTICAST_SNOOP
	if( (memcmp(eth_hdr(skb)->h_dest, bcast_mac_addr, 6) != 0) &&       // non-broadcast packet ?!
		( (eth_hdr(skb)->h_proto == htons(ETH_P_IP)) || (eth_hdr(skb)->h_proto == htons(ETH_P_IPV6)))   ) // either IPv4 or IPv6
	{
		do_alpha_multicast(br, skb,__packet_hook);
	}
	else
	{ 
		prev = NULL;
		list_for_each_entry_rcu(p, &br->port_list, list) {
			if (should_deliver(p, skb)) {
				if (prev != NULL) {
					struct sk_buff *skb2;

					if ((skb2 = skb_clone(skb, GFP_ATOMIC)) == NULL) {
						br->dev->stats.tx_dropped++;
						kfree_skb(skb);
						return;
					}

					__packet_hook(prev, skb2);
				}

				prev = p;
			}
		}

		if (prev != NULL) {
			__packet_hook(prev, skb);
			return;
		}

		kfree_skb(skb);
	}
#else
	prev = NULL;

	list_for_each_entry_rcu(p, &br->port_list, list) {
		if (should_deliver(p, skb)) {
			if (prev != NULL) {
				struct sk_buff *skb2;

				if ((skb2 = skb_clone(skb, GFP_ATOMIC)) == NULL) {
					br->dev->stats.tx_dropped++;
					kfree_skb(skb);
					return;
				}

				__packet_hook(prev, skb2);
			}

			prev = p;
		}
	}

	if (prev != NULL) {
		__packet_hook(prev, skb);
		return;
	}

	kfree_skb(skb);
#endif
}


/* called with rcu_read_lock */
void br_flood_deliver(struct net_bridge *br, struct sk_buff *skb)
{
	br_flood(br, skb, __br_deliver);
}

/* called under bridge lock */
void br_flood_forward(struct net_bridge *br, struct sk_buff *skb)
{
	br_flood(br, skb, __br_forward);
}

#ifdef CONFIG_BRIDGE_ALPHA_MULTICAST_SNOOP
void do_alpha_multicast(struct net_bridge *br, struct sk_buff *skb,
			void (*__packet_hook)(const struct net_bridge_port *p, struct sk_buff *skb))
{
	struct net_bridge_port *p;
	list_for_each_entry_rcu(p, &br->port_list, list) 
	{
		struct sk_buff *skb2;
		struct iphdr *iph  = ip_hdr(skb);

		if ( (atomic_read(&p->wireless_interface) == 1))  // wireless interface
			//&& (iph->protocol == IPPROTO_UDP) ) //  only allow UDP packets ( IPPROTO_UDP: 17)   //rbj
		{
			do_enhance(p, br, skb,__packet_hook);
		}// interface & UDP check - END
		else 
		{
			
			/* it's wired interface or non-UDP packets*/
			/* skb_clone.....(flooding) */
			if ((skb2 = skb_clone(skb, GFP_ATOMIC)) == NULL) {
				br->dev->stats.tx_dropped++;
				kfree_skb(skb);
				return;
			}
			if (should_deliver(p,skb2))
				__packet_hook(p, skb2);
			else
				kfree_skb(skb2);
		}
	} //list_f	
	kfree_skb(skb);
}

void do_enhance(struct net_bridge_port *p, struct net_bridge *br, struct sk_buff *skb,
				void (*__packet_hook)(const struct net_bridge_port *p, struct sk_buff *skb))
{
	struct port_group_mac *g;
	struct sk_buff *skb2;
	int found =0;
	/*  does group address stored in table ? */
	list_for_each_entry(g, &p->igmp_group_list, list)
	{
		struct ethhdr * dest;
		struct port_member_mac *m;
		dest = eth_hdr(skb);
		if(!memcmp( dest->h_dest, g->grpmac, 6))
		{
			list_for_each_entry(m, &g->member_list, list)
			{
				if ((skb2 = skb_copy(skb, GFP_ATOMIC)) == NULL)
				{
					br->dev->stats.tx_dropped++;
					//kfree_skb(skb);
					return;
				}

				dest = eth_hdr(skb2);					
				memcpy(dest->h_dest, m->member_mac, sizeof(uint8_t)*6);
				if (should_deliver(p, skb2))
				{
					__packet_hook(p, skb2);
					found=1;
				}
				else
					kfree_skb(skb2);
			}
		}
	}
	if(!found)
	{
			/* it's wired interface or non-UDP packets*/
			/* skb_clone.....(flooding) */
			if ((skb2 = skb_clone(skb, GFP_ATOMIC)) == NULL) {
				br->dev->stats.tx_dropped++;
				//kfree_skb(skb);
				return;
			}
			if (should_deliver(p,skb2))
			{
				__packet_hook(p, skb2);
			}
			else
				kfree_skb(skb2);

	}
}
#endif
