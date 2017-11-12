/*
 *	Userspace interface
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
#include <linux/ethtool.h>
#include <linux/if_arp.h>
#include <linux/module.h>
#include <linux/init.h>
#include <linux/rtnetlink.h>
#include <linux/if_ether.h>
#include <net/sock.h>
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS
#include <linux/proc_fs.h>
#endif

#include "br_private.h"

#ifdef CONFIG_BRIDGE_IGMPP_PROCFS
static void table_add(  struct net_bridge_port *p,  struct port_igmpp_table_t *pt, uint32_t ip32_addr,  unsigned char * mac_addr);
static void table_remove(struct port_igmpp_table_t *pt, uint32_t ip32_addr, unsigned char * mac_addr);

static void table_add6(struct net_bridge_port *p, struct port_igmpp_table_t *pt, struct in6_addr mca, unsigned char * mac_addr);
static void table_remove6(struct port_igmpp_table_t *pt, struct in6_addr mca, unsigned char * mac_addr);
#elif CONFIG_BRIDGE_ALPHA_MULTICAST_SNOOP

typedef void (*add_member_cb)(unsigned char *, unsigned char *);
typedef void (*del_member_cb)(unsigned char *,unsigned char *);
typedef void (*clear_group_cb)(unsigned char *);
typedef void (*snoop_init_cb)(void);
typedef void (*snoop_deinit_cb)(void);
add_member_cb add_member=NULL;
del_member_cb del_member=NULL;
clear_group_cb clear_group=NULL;
snoop_init_cb snoop_init=NULL;
snoop_deinit_cb snoop_deinit=NULL;

void register_igmp_callbacks(add_member_cb fun1, del_member_cb fun2, clear_group_cb fun3)
{
	add_member = fun1;
	del_member = fun2;
	clear_group = fun3;	
}

void unregister_igmp_callbacks()
{	
	add_member = NULL;
	del_member = NULL;
	clear_group = NULL;		
}

void register_snoop_init_callback (snoop_init_cb funa,snoop_deinit_cb funb)
{
	snoop_init = funa;
	snoop_deinit = funb;
}	

void unregister_snoop_init_callback()
{
	snoop_init = NULL;
	snoop_deinit =NULL;
}

EXPORT_SYMBOL(register_snoop_init_callback);
EXPORT_SYMBOL(unregister_snoop_init_callback);
EXPORT_SYMBOL(register_igmp_callbacks);
EXPORT_SYMBOL(unregister_igmp_callbacks);

#endif

/*
 * Determine initial path cost based on speed.
 * using recommendations from 802.1d standard
 *
 * Since driver might sleep need to not be holding any locks.
 */
static int port_cost(struct net_device *dev)
{
	if (dev->ethtool_ops && dev->ethtool_ops->get_settings) {
		struct ethtool_cmd ecmd = { .cmd = ETHTOOL_GSET, };

		if (!dev->ethtool_ops->get_settings(dev, &ecmd)) {
			switch(ecmd.speed) {
			case SPEED_10000:
				return 2;
			case SPEED_1000:
				return 4;
			case SPEED_100:
				return 19;
			case SPEED_10:
				return 100;
			}
		}
	}

	/* Old silly heuristics based on name */
	if (!strncmp(dev->name, "lec", 3))
		return 7;

	if (!strncmp(dev->name, "plip", 4))
		return 2500;

	return 100;	/* assume old 10Mbps */
}


/*
 * Check for port carrier transistions.
 * Called from work queue to allow for calling functions that
 * might sleep (such as speed check), and to debounce.
 */
void br_port_carrier_check(struct net_bridge_port *p)
{
	struct net_device *dev = p->dev;
	struct net_bridge *br = p->br;

	if (netif_carrier_ok(dev))
		p->path_cost = port_cost(dev);

	if (netif_running(br->dev)) {
		spin_lock_bh(&br->lock);
		if (netif_carrier_ok(dev)) {
			if (p->state == BR_STATE_DISABLED)
				br_stp_enable_port(p);
		} else {
			if (p->state != BR_STATE_DISABLED)
				br_stp_disable_port(p);
		}
		spin_unlock_bh(&br->lock);
	}
}

static void release_nbp(struct kobject *kobj)
{
	struct net_bridge_port *p
		= container_of(kobj, struct net_bridge_port, kobj);
	kfree(p);
}

static struct kobj_type brport_ktype = {
#ifdef CONFIG_SYSFS
	.sysfs_ops = &brport_sysfs_ops,
#endif
	.release = release_nbp,
};

static void destroy_nbp(struct net_bridge_port *p)
{
	struct net_device *dev = p->dev;

	p->br = NULL;
	p->dev = NULL;
	dev_put(dev);

	kobject_put(&p->kobj);
}

static void destroy_nbp_rcu(struct rcu_head *head)
{
	struct net_bridge_port *p =
			container_of(head, struct net_bridge_port, rcu);
	destroy_nbp(p);
}

/* Delete port(interface) from bridge is done in two steps.
 * via RCU. First step, marks device as down. That deletes
 * all the timers and stops new packets from flowing through.
 *
 * Final cleanup doesn't occur until after all CPU's finished
 * processing packets.
 *
 * Protected from multiple admin operations by RTNL mutex
 */
static void del_nbp(struct net_bridge_port *p)
{
	struct net_bridge *br = p->br;
	struct net_device *dev = p->dev;

	sysfs_remove_link(br->ifobj, dev->name);

	dev_set_promiscuity(dev, -1);

	spin_lock_bh(&br->lock);
	br_stp_disable_port(p);
	spin_unlock_bh(&br->lock);

	br_ifinfo_notify(RTM_DELLINK, p);

	br_fdb_delete_by_port(br, p, 1);

	list_del_rcu(&p->list);

	rcu_assign_pointer(dev->br_port, NULL);

	kobject_uevent(&p->kobj, KOBJ_REMOVE);
	kobject_del(&p->kobj);

	call_rcu(&p->rcu, destroy_nbp_rcu);
}

/* called with RTNL */
static void del_br(struct net_bridge *br, struct list_head *head)
{
	struct net_bridge_port *p, *n;

	list_for_each_entry_safe(p, n, &br->port_list, list) {
		del_nbp(p);
	}

	del_timer_sync(&br->gc_timer);

	br_sysfs_delbr(br->dev);
	unregister_netdevice_queue(br->dev, head);
}


#ifdef CONFIG_BRIDGE_IGMPP_PROCFS
/****************************************************************************************************/
/************* v6 table operations START ************************************************************/

/* If host MAC has been existed in group list, return index of host MAC list(positive numbe),
 * else return -1.
 * Called under bridge lock */
static int search_list_MAC6(struct port_igmpp_table_t *pt, int groupIndex, unsigned char* mac_addr)
{
	int i;
	for (i=0; i<HOSTLIST_NUMBER; ++i) {
		if (pt->group_list6[groupIndex].host_list[i].used==1)
			if ( !memcmp(pt->group_list6[groupIndex].host_list[i].mac_addr, mac_addr, sizeof(uint8_t)*6) )
				return i;
	}
	return (-1);
}

/* get empty element from MAC pool.
 * Called under bridge lock */
static int get_element_from_MAC_pool6(struct port_igmpp_table_t *pt ,int gIdx)
{
	int i;
	for(i=0; i<HOSTLIST_NUMBER; ++i){
		if (pt->group_list6[gIdx].host_list[i].used == 0) return i;
	}
	return (-1);
}

/* Add MAC */
/* Called under bridge lock */
static int add_MAC_2_pool6(struct port_igmpp_table_t *pt, int gIdx, int mIdx, unsigned char* mac_addr)
{
	pt->group_list6[gIdx].host_list[mIdx].used =1;
	memcpy(pt->group_list6[gIdx].host_list[mIdx].mac_addr, mac_addr, sizeof(uint8_t)*6 );
	return 0;
}

/* get empty group */
/* Called under bridge lock */
static int check_GROUP_pool6(struct port_igmpp_table_t *pt)
{
	int i;
	for( i=0; i<GROUPLIST_NUMBER; ++i){
		if( pt->group_list6[i].used == 0) return i;
	}
	return -1;
}

/* add group */
/* Called under bridge lock */
static int add_GROUP6(struct port_igmpp_table_t *pt, int gIdx, struct in6_addr ip6_addr)
{
	pt->group_list6[gIdx].used = 1;
	ipv6_addr_copy( &pt->group_list6[gIdx].ip6_addr, &ip6_addr);
	return 0;
}

/* remove MAC */
/* Called under bridge lock */
static int remove_MAC_from_pool6(struct port_igmpp_table_t *pt, int gIdx, int mIdx)
{
	pt->group_list6[gIdx].host_list[mIdx].used = 0;
	memset(pt->group_list6[gIdx].host_list[mIdx].mac_addr, 0, sizeof(uint8_t)*6);
	return 0;
}

/* remove group */
/* Called under bridge lock */
static int remove_GROUP_from_pool6(struct port_igmpp_table_t *pt, int gIdx)
{
	pt->group_list6[gIdx].used = 0;
	ipv6_addr_set(&pt->group_list6[gIdx].ip6_addr, 0, 0, 0, 0);
	return 0;
}

/* check group is empty and remove */
/* Called under bridge lock */
static int check_GROUP_is_empty_and_remove6(struct port_igmpp_table_t *pt, int gIdx)
{
	int i,used=0;
	for (i=0; i<HOSTLIST_NUMBER; ++i){
		if (pt->group_list6[gIdx].host_list[i].used == 1)
			++used;
	}
	if (used == 0){
		remove_GROUP_from_pool6(pt, gIdx);
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "[BR_IGMPP_PROC]-> Group (v6): %08X %08X %08X %08X remove !!\n",
			ntohl(pt->group_list6[gIdx].ip6_addr.s6_addr32[0]), ntohl(pt->group_list6[gIdx].ip6_addr.s6_addr32[1]),
			ntohl(pt->group_list6[gIdx].ip6_addr.s6_addr32[2]), ntohl(pt->group_list6[gIdx].ip6_addr.s6_addr32[3])
		);
#endif
		return 0;
	} else
		return 0;
}
/************* v6 table operations END ************************************************************/
/**************************************************************************************************/

/*****************************************************************************************************/
/************* belowing functions are externed *******************************************************/

/* for IPv4 */
/* If mulsticast group has been existed in table, return index of group list(positive numbe),
 * else return -1.
 * Called under bridge lock */
int br_igmpp_search_group_IP(struct port_igmpp_table_t *pt, uint32_t ip_addr)
{
	int i;
	for (i=0; i< GROUPLIST_NUMBER; ++i){
		if (pt->group_list[i].used == 1 )
		if (pt->group_list[i].ip_addr == ip_addr)
		return i;
	}
	return (-1);
}

/* for IPv4 */
void br_igmpp_igmp_table_add(struct net_bridge_port *p, struct port_igmpp_table_t *pt,
							uint32_t mca, unsigned char * mac_addr)
{
	table_add(p, pt, mca, mac_addr);
}

/* for IPv4 */
void br_igmpp_igmp_table_remove(struct port_igmpp_table_t *pt,
								uint32_t mca, unsigned char * mac_addr)
{
	table_remove(pt, mca, mac_addr);
}

/* for IPv6 */
/* If mulsticast group has been existed in table, return index of group list(positive numbe),
 * else return -1.
 * Called under bridge lock */
int br_igmpp_search_group_IP6(struct port_igmpp_table_t *pt, struct in6_addr ip6_addr)
{
	int i;
	for (i=0; i< GROUPLIST_NUMBER; ++i){
		if (pt->group_list6[i].used == 1 )
		if (ipv6_addr_equal( &(pt->group_list6[i].ip6_addr), &ip6_addr) )
		return i;
	}
	return (-1);
}

/* for IPv6 */
void br_igmpp_mld_table_add(struct net_bridge_port *p, struct port_igmpp_table_t *pt,
							struct in6_addr mca, unsigned char * mac_addr)
{
	table_add6(p, pt, mca, mac_addr);
}

/* for IPv6 */
void br_igmpp_mld_table_remove(struct port_igmpp_table_t *pt,
								struct in6_addr mca, unsigned char * mac_addr)
{
	table_remove6(pt, mca, mac_addr);
}
/************* externed funstions END ****************************************************************/
/*****************************************************************************************************/

static void trans_32to8(uint32_t *ip, uint8_t **a)
{
	*a = (uint8_t *)ip;
	return;
}

/* If host MAC has been existed in group list, return index of host MAC list(positive numbe),
 * else return -1.
 * Called under bridge lock */
static int search_list_MAC(struct port_igmpp_table_t *pt, int groupIndex, unsigned char* mac_addr)
{
	int i;
	for (i=0; i<HOSTLIST_NUMBER; ++i) {
		if (pt->group_list[groupIndex].host_list[i].used==1)
		if ( !memcmp(pt->group_list[groupIndex].host_list[i].mac_addr, mac_addr, sizeof(uint8_t)*6) )
		return i;
	}
	return (-1);
}

/* get empty element from MAC pool.
* Called under bridge lock */
static int get_element_from_MAC_pool(struct port_igmpp_table_t *pt ,int gIdx)
{
	int i;
	for(i=0; i<HOSTLIST_NUMBER; ++i){
		if (pt->group_list[gIdx].host_list[i].used == 0) return i;
	}
	return (-1);
}
static uint8_t * hex2dec(char *ch)
{
	if ( *ch == '0') *ch=0;
	if ( *ch == '1') *ch=1;
	if ( *ch == '2') *ch=2;
	if ( *ch == '3') *ch=3;
	if ( *ch == '4') *ch=4;
	if ( *ch == '5') *ch=5;
	if ( *ch == '6') *ch=6;
	if ( *ch == '7') *ch=7;
	if ( *ch == '8') *ch=8;
	if ( *ch == '9') *ch=9;
	if ( *ch == 'A' || *ch == 'a')  *ch=10;
	if ( *ch == 'B' || *ch == 'b')  *ch=11;
	if ( *ch == 'C' || *ch == 'c')  *ch=12;
	if ( *ch == 'D' || *ch == 'd')  *ch=13;
	if ( *ch == 'E' || *ch == 'e')  *ch=14;
	if ( *ch == 'F' || *ch == 'f')  *ch=15;
	return ch;
}

/* Add MAC */
/* Called under bridge lock */
static int add_MAC_2_pool(struct port_igmpp_table_t *pt, int gIdx, int mIdx, unsigned char* mac_addr)
{
	pt->group_list[gIdx].host_list[mIdx].used =1;
	memcpy(pt->group_list[gIdx].host_list[mIdx].mac_addr, mac_addr, sizeof(uint8_t)*6 );
	return 0;
}

/* get empty group */
/* Called under bridge lock */
static int check_GROUP_pool(struct port_igmpp_table_t *pt)
{
	int i;
	for( i=0; i<GROUPLIST_NUMBER; ++i){
		if( pt->group_list[i].used == 0) return i;
	}
	return -1;
}

/* add group */
/* Called under bridge lock */
static int add_GROUP(struct port_igmpp_table_t *pt, int gIdx, uint32_t ip_addr)
{
	pt->group_list[gIdx].used = 1;
	pt->group_list[gIdx].ip_addr = ip_addr;
	return 0;
}

/* remove MAC */
/* Called under bridge lock */
static int remove_MAC_from_pool(struct port_igmpp_table_t *pt, int gIdx, int mIdx)
{
	pt->group_list[gIdx].host_list[mIdx].used = 0;
	memset(pt->group_list[gIdx].host_list[mIdx].mac_addr, 0, sizeof(uint8_t)*6);
	return 0;
}

/* remove group */
/* Called under bridge lock */
static int remove_GROUP_from_pool(struct port_igmpp_table_t *pt, int gIdx)
{
	pt->group_list[gIdx].used = 0;
	pt->group_list[gIdx].ip_addr = 0;
	return 0;
}

/* check group is empty and remove */
/* Called under bridge lock */
static int check_GROUP_is_empty_and_remove(struct port_igmpp_table_t *pt, int gIdx)
{
	int i,used=0;
	for (i=0; i<HOSTLIST_NUMBER; ++i){
		if (pt->group_list[gIdx].host_list[i].used == 1)
			++used;
	}
	if (used == 0){
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		uint32_t ip32_addr = pt->group_list[gIdx].ip_addr;
		uint8_t *ip8_addr;
		trans_32to8(&ip32_addr, &ip8_addr);
		printk(KERN_INFO "[BR_IGMPP_PROC]-> Group IP: %u.%u.%u.%u remove !!\n",
			*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3));
#endif
		remove_GROUP_from_pool(pt, gIdx);
		return 0;
	} else
		return 0;
}

static int proc_read_br_igmpp (char *buf, char **start, off_t offset,
								int len, int *eof, void *data)
{
	int count =0;
	struct net_bridge *br = (struct net_bridge *) data;
	struct net_bridge_port *p ;

	spin_lock_bh(&br->lock); // bridge lock
	printk( "**********************************************************************\n");
	printk( "* bridge name: %s\n",br->dev->name);
	printk( "* br_igmpp_table_enable-> %d\n",atomic_read(&br->br_igmpp_table_enable));
	printk( "**********************************************************************\n");
	list_for_each_entry_rcu(p, &br->port_list, list) {
		int i,j,k;
		printk( "* ==============================================================\n");
		printk( "* <%d> port name : %s\n", p->port_no, p->dev->name);
		printk( "* <%d> table size: %u x %u\n", p->port_no, GROUPLIST_NUMBER, HOSTLIST_NUMBER );
		printk( "* <%d> wireless_interface -> %d\n", p->port_no, atomic_read(&p->wireless_interface) );
		printk( "* <%d> port_igmpp_table -> %d\n", p->port_no, p->port_igmpp_table.enable );
		printk( "* ==============================================================\n");

		/* v4 table */
		for( i=0; i<GROUPLIST_NUMBER; i++){
			if(p->port_igmpp_table.group_list[i].used == 1){
				uint32_t ip32_addr = p->port_igmpp_table.group_list[i].ip_addr;
				uint8_t *ip8_addr;
				trans_32to8(&ip32_addr, &ip8_addr);
				printk( "*     <%d> => group_list[%u]= %u %u %u %u\n", p->port_no,
					i, *ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3));
				for (j=0; j<HOSTLIST_NUMBER; j++){
					if(p->port_igmpp_table.group_list[i].host_list[j].used == 1){
						printk("*     <%d> ->    ", p->port_no);
						for (k=0; k<6; k++)
							printk( " %02X ", p->port_igmpp_table.group_list[i].host_list[j].mac_addr[k] );
						printk("\n");
					}
				}
				printk( "*     - - - - - - - - - - - - - - - - - -\n");
			}
		}
		/* v6 table */
		for( i=0; i<GROUPLIST_NUMBER; i++){
			if(p->port_igmpp_table.group_list6[i].used == 1){
				//struct in6_addr ip6_addr = p->port_igmpp_table.group_list6[i].ip6_addr;
				struct in6_addr ip6_addr;
				ipv6_addr_copy( &ip6_addr, &p->port_igmpp_table.group_list6[i].ip6_addr);
				printk( "*     <%d> => group_list6[%u]= %08X %08X %08X %08X\n", p->port_no, i,
					ntohl(ip6_addr.s6_addr32[0]), ntohl(ip6_addr.s6_addr32[1]),
					ntohl(ip6_addr.s6_addr32[2]), ntohl(ip6_addr.s6_addr32[3]));
				for (j=0; j<HOSTLIST_NUMBER; j++){
					if(p->port_igmpp_table.group_list6[i].host_list[j].used == 1){
						printk("*     <%d> ->    ", p->port_no);
						for (k=0; k<6; k++)
							printk( " %02X ", p->port_igmpp_table.group_list6[i].host_list[j].mac_addr[k] );
						printk("\n");
					}
				}
				printk( "*     - - - - - - - - - - - - - - - - - - - - - - - - - - - - -\n");
			}
		}
	} // list_for_each_entry_rcu() - END
	printk( "**********************************************************************\n");
	spin_unlock_bh(&br->lock); // bridge unlock

	*eof = 1;
	return count;
}
static void split_IP(uint32_t *ip32_addr, char * token)
{
	char *ipDelim = IP_DELIM;
	char **pIP = &token;
	char *ipField_char[4];
	uint8_t ipField_int[4];
	int i;
	uint8_t *ip8_addr;
	for (i=0; i<4; ++i)
	{
		ipField_char[i] = strsep(pIP, ipDelim);
		ipField_int[i] = (uint8_t) simple_strtoul( ipField_char[i], NULL, 10);
	}
	trans_32to8(ip32_addr, &ip8_addr);
	for (i =0; i<4; ++i)
		*(ip8_addr+i) = ipField_int[i];
	return;
}
static void split_MAC(unsigned char * mac_addr, char * token)
{
	char *macDelim = MAC_DELIM;
	char **pMAC = &token;
	char * macField_char[6];
	int i;
	for (i=0; i<6; ++i)
	{
		int j;
		char temp[2];
		macField_char[i] = strsep(pMAC, macDelim);
		/* copy each char byte and convert to dec number */
		for(j=0; j<2; ++j) {
			memcpy(&temp[j],macField_char[i]+j, sizeof(char));
			hex2dec(&temp[j]);
		}

		temp[0] = temp[0] << 4;
		*(mac_addr + i)= (temp[0]^temp[1]);
	}
}

/**********************************************************************************************************/
/************* Basic v4 table operations START ************************************************************/

/* Add member/group to port_igmpp_table_t */
/* called under bridge lock */
static void table_add(  struct net_bridge_port *p,  struct port_igmpp_table_t *pt,
						uint32_t ip32_addr,     unsigned char * mac_addr)
{
	/* search group IP */
	int groupIdx,ipIdx;
	groupIdx = br_igmpp_search_group_IP(pt, ip32_addr);
	if(groupIdx >= 0){ /* group existed */
		/* search MAC in group*/
		ipIdx = search_list_MAC(pt, groupIdx, mac_addr);

		if(ipIdx >= 0){ /* MAC existed */
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_IGMPP_PROC]-> MAC: %X:%X:%X:%X:%X:%X has been existed !!\n",
				*mac_addr,*(mac_addr+1),*(mac_addr+2),*(mac_addr+3),*(mac_addr+4),*(mac_addr+5) );
#endif
		}else{ /* MAC doesn't existed */
			/* check MAC pool */
			int macPoolIdx;
			macPoolIdx = get_element_from_MAC_pool(pt, groupIdx);
			if (macPoolIdx >=0 ){
				/* add MAC to pool */
				add_MAC_2_pool(pt, groupIdx, macPoolIdx, mac_addr);
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
				printk(KERN_INFO "[BR_IGMPP_PROC]-> MAC: %X:%X:%X:%X:%X:%X add !!\n",
					*mac_addr,*(mac_addr+1),*(mac_addr+2),*(mac_addr+3),*(mac_addr+4),*(mac_addr+5) );
#endif
			}
			else{
				/* MAC pool is full */
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
				uint8_t *ip8_addr;
				trans_32to8(&ip32_addr, &ip8_addr);
				printk(KERN_INFO "[BR_IGMPP_PROC]-> the pool of Group IP: %u.%u.%u.%u has been full !!\n",
					*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3));
#endif
			}
		}
	}else{ /* group doesn't existed */
		/* check group pool */
		int groupPoolIdx;
		groupPoolIdx = check_GROUP_pool(pt);
		if(groupPoolIdx >= 0){
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			uint8_t *ip8_addr;
#endif
			/* add group and host */
			add_GROUP(pt, groupPoolIdx, ip32_addr); // add group
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			trans_32to8(&ip32_addr, &ip8_addr);
			printk(KERN_INFO "[BR_IGMPP_PROC]-> Group IP: %u.%u.%u.%u add !!\n",
				*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3));
#endif

			add_MAC_2_pool(pt, groupPoolIdx, 0, mac_addr); //add host(MAC)
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_IGMPP_PROC]-> MAC: %X:%X:%X:%X:%X:%X add !!\n",
				*mac_addr,*(mac_addr+1),*(mac_addr+2),*(mac_addr+3),*(mac_addr+4),*(mac_addr+5) );
#endif
		}else {
			/* group pool is full */
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_IGMPP_PROC]-> the pool of group on \" %s \" has been full !!", p->dev->name);
#endif
		}
	}

	return;
}

/* remove member/group */
/* Called under bridge lock */
static void table_remove(struct port_igmpp_table_t *pt,
						uint32_t ip32_addr,
						unsigned char * mac_addr)
{
	/* search group IP */
	int groupIdx,ipIdx;
	groupIdx = br_igmpp_search_group_IP(pt, ip32_addr);
	if(groupIdx >= 0){
		/* search list MAC */
		ipIdx = search_list_MAC(pt, groupIdx, mac_addr);
		if(ipIdx >= 0){
			/* remove MAC and check group member*/
			remove_MAC_from_pool(pt, groupIdx, ipIdx);
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_IGMPP_PROC]-> MAC: %X:%X:%X:%X:%X:%X remove !!\n",
				*mac_addr,*(mac_addr+1),*(mac_addr+2),*(mac_addr+3),*(mac_addr+4),*(mac_addr+5) );
#endif
			check_GROUP_is_empty_and_remove(pt, groupIdx);
		}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_IGMPP_PROC]-> MAC: %X:%X:%X:%X:%X:%X does't exist !!\n",
				*mac_addr,*(mac_addr+1),*(mac_addr+2),*(mac_addr+3),*(mac_addr+4),*(mac_addr+5) );
#endif
		}
	}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		uint8_t *ip8_addr;
		trans_32to8(&ip32_addr, &ip8_addr);
		printk(KERN_INFO "[BR_IGMPP_PROC]-> Group IP: %u.%u.%u.%u does't exist !!\n",
			*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3));
#endif
	}

	return;
}
/************* Basic v4 table operations END   ************************************************************/
/**********************************************************************************************************/

/**********************************************************************************************************/
/************* Basic v6 table operations START ************************************************************/
static void table_add6(struct net_bridge_port *p, struct port_igmpp_table_t *pt,
						struct in6_addr mca, unsigned char * mac_addr)
{
	/* search group IP */
	int groupIdx,ipIdx;
	groupIdx = br_igmpp_search_group_IP6(pt, mca);
	if(groupIdx >= 0){ /* group existed */
		/* search MAC in group*/
		ipIdx = search_list_MAC6(pt, groupIdx, mac_addr);

		if(ipIdx >= 0){ /* MAC existed */
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_IGMPP_PROC]-> v6 host MAC: %X:%X:%X:%X:%X:%X has been existed !!\n",
				*mac_addr,*(mac_addr+1),*(mac_addr+2),*(mac_addr+3),*(mac_addr+4),*(mac_addr+5) );

#endif
		}else{ /* MAC doesn't existed */
			/* check MAC pool */
			int macPoolIdx;
			macPoolIdx = get_element_from_MAC_pool6(pt, groupIdx);
			if (macPoolIdx >=0 ){
				/* add MAC to pool */
				add_MAC_2_pool6(pt, groupIdx, macPoolIdx, mac_addr);
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
				printk(KERN_INFO "[BR_IGMPP_PROC]-> v6 host MAC: %X:%X:%X:%X:%X:%X add !!\n",
					*mac_addr,*(mac_addr+1),*(mac_addr+2),*(mac_addr+3),*(mac_addr+4),*(mac_addr+5) );
#endif
			}
			else{
				/* MAC pool is full */
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
				printk(KERN_INFO "[BR_IGMPP_PROC]-> the pool of Group(v6): %08X %08X %08X %08X has been full !!\n",
					ntohl(mca.s6_addr32[0]), ntohl(mca.s6_addr32[1]),
					ntohl(mca.s6_addr32[2]), ntohl(mca.s6_addr32[3])
				);
#endif
			}
		}
	}else{ /* group doesn't existed */
		/* check group pool */
		int groupPoolIdx;
		groupPoolIdx = check_GROUP_pool6(pt);
		if(groupPoolIdx >= 0){
			/* add group and host */
			add_GROUP6(pt, groupPoolIdx, mca); // add group
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_IGMPP_PROC]-> Group(v6): %08X %08X %08X %08X add !!\n",
				ntohl(mca.s6_addr32[0]), ntohl(mca.s6_addr32[1]),
				ntohl(mca.s6_addr32[2]), ntohl(mca.s6_addr32[3])
			);
#endif

			add_MAC_2_pool6(pt, groupPoolIdx, 0, mac_addr); //add host(MAC)
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_IGMPP_PROC]-> v6 host MAC: %X:%X:%X:%X:%X:%X add !!\n",
				*mac_addr,*(mac_addr+1),*(mac_addr+2),*(mac_addr+3),*(mac_addr+4),*(mac_addr+5) );
#endif
		}else {
			/* group pool is full */
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_IGMPP_PROC]-> the pool of group(v6) on \" %s \" has been full !!", p->dev->name);
#endif
		}
	}

	return;
}

static void table_remove6(struct port_igmpp_table_t *pt,
						struct in6_addr mca, unsigned char * mac_addr)
{
	/* search group IP */
	int groupIdx,ipIdx;
	groupIdx = br_igmpp_search_group_IP6(pt, mca);
	if(groupIdx >= 0){
		/* search list MAC */
		ipIdx = search_list_MAC6(pt, groupIdx, mac_addr);
		if(ipIdx >= 0){
			/* remove MAC and check group member*/
			remove_MAC_from_pool6(pt, groupIdx, ipIdx);
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_IGMPP_PROC]-> v6 host MAC: %X:%X:%X:%X:%X:%X remove !!\n",
				*mac_addr,*(mac_addr+1),*(mac_addr+2),*(mac_addr+3),*(mac_addr+4),*(mac_addr+5) );
#endif
			check_GROUP_is_empty_and_remove6(pt, groupIdx);
		}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_IGMPP_PROC]-> v6 host MAC: %X:%X:%X:%X:%X:%X does't exist !!\n",
				*mac_addr,*(mac_addr+1),*(mac_addr+2),*(mac_addr+3),*(mac_addr+4),*(mac_addr+5) );
#endif
		}
	}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "[BR_IGMPP_PROC]-> Group(v6): %08X %08X %08X %08X does't exist !!\n",
			ntohl(mca.s6_addr32[0]), ntohl(mca.s6_addr32[1]),
			ntohl(mca.s6_addr32[2]), ntohl(mca.s6_addr32[3])
		);
#endif
	}

	return;
}
/************* Basic v6 table operations END   ************************************************************/
/**********************************************************************************************************/

/* For different platform,
 * kernel API may don't support strcmp() originally,
 * we copy strcmp() and named strcmp1() for conveniently.
 *
 * strcmp - Compare two strings
 * @cs: One string
 * @ct: Another string
 */
static int strcmp1(char * cs,char * ct)
{
	register signed char __res;
	while (1) {
		if ((__res = *cs - *ct++) != 0 || !*cs++)
			break;
	}
	return __res;
}

/* For different platform,
 * kernel API may don't  support strspn() originally,
 * we copy strspn() and named strspn1() for conveniently.
 *
 * strspn1 - Calculate the length of the initial substring of @s which only
 *      contain letters in @accept
 * @s: The string to be searched
 * @accept: The string to search for
 */
static size_t strspn1(char *s, char *accept)
{
	char *p;
	char *a;
	size_t count = 0;

	for (p = s; *p != '\0'; ++p) {
		for (a = accept; *a != '\0'; ++a) {
			if (*p == *a)
				break;
		}
		if (*a == '\0')
			return count;
		++count;
	}
	return count;
}
/* check_str()
 * type => 0, only accept ".0123456789"
 * type => 1, only accept ":0123456789ABCDEFabcdef"
 */
static int check_str(char *s, int type)
{
	char * accept = NULL;
	switch (type){
		case 0:
			accept = IP_ACCEPT_CHAR;
			break;
		case 1:
			accept = MAC_ACCEPT_CHAR;
			break;
	}

	if (accept != NULL)
		if ( strlen(s) == strspn1(s, accept) )
			return 0;
	return (-1);
}

/* search device'name that matched */
/* called under bridge lock */
static struct net_bridge_port * search_device(struct net_bridge * br, char* name)
{
	struct net_bridge_port *p;
	list_for_each_entry(p, &br->port_list, list) {
		if (strcmp1(p->dev->name, name) == 0 ){
			return p;
		}
	}
	return NULL;
}

/* look for MAC address via IP */
/* called under bridge lock */
static int get_MAC(unsigned char * mac_addr, struct net_bridge *br, uint32_t ip_addr)
{
	struct br_mac_table_t *tlist;
	int find = -1;

	list_for_each_entry(tlist,&(br->br_mac_table.list), list){
		if ( tlist->ip_addr == ip_addr){
			int i;
			find = 0;
			for (i =0; i<6; i++)
				mac_addr[i] = tlist->mac_addr[i];
			break;
		}
	}

	return find;
}

/* enable each port's port_igmpp_table */
/* called under bridge lock */
static void table_all_enable(struct net_bridge *br)
{
	struct net_bridge_port *p;

	/* set bridge */
	atomic_set(&br->br_igmpp_table_enable, 1);

	/* set each port */
	list_for_each_entry(p, &br->port_list, list) {
		/* always clean table and unset all wireless identifier */
		memset( &p->port_igmpp_table, 0, sizeof( struct port_igmpp_table_t));
		atomic_set(&p->wireless_interface, 0);

		p->port_igmpp_table.enable = 1;
	}
}

/* disable each port's port_igmpp_table */
/* called under bridge lock */
static void table_all_disable(struct net_bridge *br)
{
	struct net_bridge_port *p;

	/* set bridge */
	atomic_set(&br->br_igmpp_table_enable, 0);

	/* set each port */
	list_for_each_entry(p, &br->port_list, list) {
		/* always clean table and unset all wireless identifier */
		memset( &p->port_igmpp_table, 0, sizeof( struct port_igmpp_table_t));
		atomic_set(&p->wireless_interface, 0);

		p->port_igmpp_table.enable = 0;
	}
}

/* set wireless identifier */
/* called under bridge lock */
static int table_setwl(struct net_bridge *br, char * name)
{
	struct net_bridge_port *hit_port;
	hit_port = search_device(br, name);
	if (hit_port != NULL){
		atomic_set(&hit_port->wireless_interface, 1);
		return 0;
	}else
		return (-1);
}

/* unset wireless identifier */
/* called under bridge lock */
static int table_unsetwl(struct net_bridge *br, char * name)
{
	struct net_bridge_port *hit_port;
	hit_port = search_device(br, name);
	if (hit_port != NULL){
		atomic_set(&hit_port->wireless_interface, 0);
		return 0;
	}else
		return (-1);
}
static int proc_write_br_igmpp (struct file *file, const char *buf,
								unsigned long count, void *data)
{
	int len = MESSAGE_LENGTH+1;
	char message[len];
	char *msgDelim = MESSAGE_DELIM;
	char *pmesg;
	char *action_token, *action;
	struct net_bridge *br;

	if(count > MESSAGE_LENGTH) {len = MESSAGE_LENGTH;}
	else {len = count; }
	if(copy_from_user(message, buf, len))
		return -EFAULT;
	message[len-1] = '\0';

	/* split input message that get from user space
	 * token[0] => action token --> add or remove
	 * token[1] => multicast group IP address
	 * token[2] => MAC address of host IP
	 */
	pmesg = message ;

	action_token = strsep(&pmesg, msgDelim);

	br = (struct net_bridge *) data;

	/* ============================  enable each port's port_igmpp_table ====================*/
	/* NOTE: Each port_igmpp_table will be clean.                                            */
	action = ACTION_ENABLE_TABLE;
	if ( memcmp(action_token, action, sizeof(ACTION_ENABLE_TABLE) )== 0){
		spin_lock_bh(&br->lock); // bridge lock
		table_all_enable(br);
		spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "[BR_IGMPP_PROC]->ENABLE %s\n",br->dev->name);
#endif
		goto proc_write_br_igmpp_out;
	}

	/* ============================  disable each port's port_igmpp_table ===================*/
	action = ACTION_DISABLE_TABLE;
	if ( memcmp(action_token, action, sizeof(ACTION_DISABLE_TABLE) )== 0){
		spin_lock_bh(&br->lock); // bridge lock
		table_all_disable(br);
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "[BR_IGMPP_PROC]->DISABLE %s\n",br->dev->name);
#endif
		spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
		goto proc_write_br_igmpp_out;
	}

	/* ============================  set wireless ============================*/
	action = ACTION_SET_WL;
	if ( memcmp(action_token, action, sizeof(ACTION_SET_WL) )== 0){
		spin_lock_bh(&br->lock); // bridge lock
		if (table_setwl(br,pmesg) == 0){
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_IGMPP_PROC]->SETWL-> %s \n",pmesg);
#endif
		}else{
			printk(KERN_INFO "[BR_IGMPP_PROC]->WARNING SETWL FAILURE-> %s\n",pmesg);
		}
		spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
		goto proc_write_br_igmpp_out;
	}

	/* ============================  unset wireless ==========================*/
	action = ACTION_UNSET_WL;
	if ( memcmp(action_token, action, sizeof(ACTION_UNSET_WL) )== 0){
		spin_lock_bh(&br->lock); // bridge lock
		if (table_unsetwl(br,pmesg) == 0){
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_IGMPP_PROC]->UNSETWL-> %s \n",pmesg);
#endif
		}else{
			printk(KERN_INFO "[BR_IGMPP_PROC]->WARNING UNSETWL FAILURE-> %s\n",pmesg);
		}
		spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
		goto proc_write_br_igmpp_out;
	}

	/* ============================  add - START =====================================*/
	action = ACTION_ADD;
	if ( memcmp(action_token, action, sizeof(ACTION_ADD) )== 0){
		/********** add - START of processing input string **********/
		char *token[2];
		int i;
		uint32_t grp_ip32_addr;
		unsigned char mac_addr[6];
		uint32_t host_ip32_addr;
		struct net_bridge_fdb_entry *hit_fdb_entry;

		for(i=0; i<2; ++i)
			token[i] = strsep(&pmesg, msgDelim);
		/* split multicast group IP address */
		if (check_str(token[0], 0) == 0 )
			split_IP(&grp_ip32_addr,token[0]);
		else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_IGMPP_PROC]-> Group IP address:%s is illegal !! \n",token[0]);
#endif
			goto proc_write_br_igmpp_out;
		}

		/* Judge address of Host either IP adress or MAC address */

		spin_lock_bh(&br->lock); // bridge lock

		if (atomic_read(&br->br_mac_table_enable) == 1 ){
			/* Only accept IP, split host IP address */

			if (check_str(token[1], 0) == 0 )
				split_IP(&host_ip32_addr,token[1]);
			else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
				printk(KERN_INFO "[BR_IGMPP_PROC]-> Host IP address: %s is illegal !!\n",token[1]);
				printk(KERN_INFO "br_mac_table_enable is 1, Host address only accept char: \".1234567890\"\n");
#endif
				spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
				goto proc_write_br_igmpp_out;
			}
			/* look for MAC address via IP */
			if ( get_MAC(mac_addr, br, host_ip32_addr) != 0 ){
				uint8_t *ip8_addr;
				trans_32to8(&host_ip32_addr, &ip8_addr);
				printk(KERN_INFO "[BR_IGMPP_PROC]-> Could not find MAC with IP: %u.%u.%u.%u !!\n",
					*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3));
				spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
				goto proc_write_br_igmpp_out;
			}
		}else {
			/* Only accept MAC, split host MAC address */
			if (check_str(token[1], 1) == 0 )
				split_MAC(mac_addr, token[1]);
			else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
				printk(KERN_INFO "[BR_IGMPP_PROC]-> Host MAC address: %s is illegal !!\n",token[1]);
				printk(KERN_INFO "br_mac_table_enable is 0, Host address only accept char: \":1234567890ABCDEFabcdef\"\n");
#endif
				spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
				goto proc_write_br_igmpp_out;
			}
		}
		/********** add - END of processing input string **********/

		/* searching bridge_fdb_entry */
		hit_fdb_entry = __br_fdb_get(br, mac_addr);
		/* NOTE: The effect of successful called br_fdb_get() also takes lock bridge and reference counts. */

		if (hit_fdb_entry != NULL){
			if(atomic_read(&hit_fdb_entry->dst->wireless_interface) == 1){
				struct net_bridge_port *p;
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
				printk(KERN_INFO "[BR_IGMPP_PROC]-> ADD ...\n");
#endif

				table_add(hit_fdb_entry->dst, &hit_fdb_entry->dst->port_igmpp_table,
					grp_ip32_addr, mac_addr);

#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
				printk(KERN_INFO "[BR_IGMPP_PROC]-> ADD DONE !!\n");
#endif

#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
				/* make sure there's only one record existed */
				printk(KERN_INFO "[BR_IGMPP_PROC]-> checking and clean all other port_igmpp_table ... \n");
#endif
				list_for_each_entry(p, &br->port_list, list) {
					if(hit_fdb_entry->dst->port_no != p->port_no){
						table_remove(&p->port_igmpp_table, grp_ip32_addr, mac_addr);
					}
				}
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
				printk(KERN_INFO "[BR_IGMPP_PROC]-> clean other port_igmpp_table DONE !!\n");
#endif
			}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
				uint8_t *ip8_addr;
				trans_32to8(&host_ip32_addr, &ip8_addr);
				printk(KERN_INFO "[BR_IGMPP_PROC]-> The host IP: %u.%u.%u.%u is belong to wired interface(port) !!\n",
					*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3));
				printk(KERN_INFO "[BR_IGMPP_PROC]-> hit_fdb_entry.addr =  %X:%X:%X:%X:%X:%X \n",
					hit_fdb_entry->addr.addr[0], hit_fdb_entry->addr.addr[1],
					hit_fdb_entry->addr.addr[2], hit_fdb_entry->addr.addr[3],
					hit_fdb_entry->addr.addr[4], hit_fdb_entry->addr.addr[5] );
				printk(KERN_INFO "[BR_IGMPP_PROC]-> hit_fdb_entry->dst->dev.name : %s \n", hit_fdb_entry->dst->dev->name);
#endif
				fdb_delete(hit_fdb_entry); // release br_fdb_get() locks
				spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
				goto proc_write_br_igmpp_out;
			}
			fdb_delete(hit_fdb_entry); // release br_fdb_get() locks
		}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "The return value of __br_fdb_get() is NULL -> MAC: %X:%X:%X:%X:%X:%X \n",
				mac_addr[0], mac_addr[1], mac_addr[2], mac_addr[3], mac_addr[4], mac_addr[5] );
#endif
		}
		spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
		goto proc_write_br_igmpp_out;
	}

	/* ============================  add - END  =================================*/
	/* ============================  remove - START =================================*/
	action = ACTION_REMOVE;
	if ( memcmp(action_token, action, sizeof(ACTION_REMOVE) ) == 0){
		/********** remove - START of processing input string **********/
		char *token[2];
		int i;
		uint32_t grp_ip32_addr;
		unsigned char mac_addr[6];
		uint32_t host_ip32_addr;
		struct net_bridge_fdb_entry *hit_fdb_entry;

		for(i=0; i<2; ++i)
			token[i] = strsep(&pmesg, msgDelim);
		/* split multicast group IP address */
		if (check_str(token[0], 0) == 0 )
			split_IP(&grp_ip32_addr,token[0]);
		else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "[BR_IGMPP_PROC]-> Group IP address:%s is illegal !! \n",token[0]);
#endif
			goto proc_write_br_igmpp_out;
		}

		/* Judge address of Host either IP adress or MAC address */
		spin_lock_bh(&br->lock); // bridge lock

		if (atomic_read(&br->br_mac_table_enable) == 1 ){
			/* Only accept IP, split host IP address */

			if (check_str(token[1], 0) == 0 )
				split_IP(&host_ip32_addr,token[1]);
			else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
				printk(KERN_INFO "[BR_IGMPP_PROC]-> Host IP address: %s is illegal !!\n",token[1]);
				printk(KERN_INFO "br_mac_table_enable is 1, Host address only accept char: \".1234567890\"\n");
#endif
				spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
				goto proc_write_br_igmpp_out;
			}
			/* look for MAC address with IP */
			if ( get_MAC(mac_addr, br, host_ip32_addr) != 0 ){
				uint8_t *ip8_addr;
				trans_32to8(&host_ip32_addr, &ip8_addr);
				printk(KERN_INFO "[BR_IGMPP_PROC]-> Could not find MAC with IP: %u.%u.%u.%u !!\n",
					*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3));
				spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
				goto proc_write_br_igmpp_out;
			}
		}else {
			/* Only accept MAC, split host MAC address */
			if (check_str(token[1], 1) == 0 )
				split_MAC(mac_addr, token[1]);
			else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
				printk(KERN_INFO "[BR_IGMPP_PROC]-> Host MAC address: %s is illegal !!\n",token[1]);
				printk(KERN_INFO "br_mac_table_enable is 0, Host address only accept char: \":1234567890ABCDEFabcdef\"\n");
#endif
				spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
				goto proc_write_br_igmpp_out;
			}
		}
		/********** remove - END of processing input string **********/

		/* searching bridge_fdb_entry */
		hit_fdb_entry = __br_fdb_get(br, mac_addr);
		/* NOTE: The effect of successful called __br_fdb_get() also takes lock bridge and reference counts. */

		if (hit_fdb_entry != NULL){
			if(atomic_read(&hit_fdb_entry->dst->wireless_interface) == 1){
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
				printk(KERN_INFO "[BR_IGMPP_PROC]-> REMOVE ...\n");
#endif

				table_remove(&hit_fdb_entry->dst->port_igmpp_table, grp_ip32_addr, mac_addr);

#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
				printk(KERN_INFO "[BR_IGMPP_PROC]-> REMOVE DONE !!\n");
#endif
			}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
				uint8_t *ip8_addr;
				trans_32to8(&host_ip32_addr, &ip8_addr);
				printk(KERN_INFO "[BR_IGMPP_PROC]-> The host IP: %u.%u.%u.%u is belong to wired interface(port) !!\n",
					*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3));
				printk(KERN_INFO "[BR_IGMPP_PROC]-> hit_fdb_entry.addr =  %X:%X:%X:%X:%X:%X \n",
					hit_fdb_entry->addr.addr[0], hit_fdb_entry->addr.addr[1],
					hit_fdb_entry->addr.addr[2], hit_fdb_entry->addr.addr[3],
					hit_fdb_entry->addr.addr[4], hit_fdb_entry->addr.addr[5] );
				printk(KERN_INFO "[BR_IGMPP_PROC]-> hit_fdb_entry->dst->dev.name : %s \n", hit_fdb_entry->dst->dev->name);
#endif
				fdb_delete(hit_fdb_entry); // release __br_fdb_get() locks
				spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
				goto proc_write_br_igmpp_out;
			}
			fdb_delete(hit_fdb_entry); // release br_fdb_get() locks
		}else{
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
			printk(KERN_INFO "The return value of __br_fdb_get() is NULL -> MAC: %X:%X:%X:%X:%X:%X \n",
				mac_addr[0], mac_addr[1], mac_addr[2], mac_addr[3], mac_addr[4], mac_addr[5] );
#endif
		}
		spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
		goto proc_write_br_igmpp_out;
	}
	/* ============================= remove - END ======================================*/
	proc_write_br_igmpp_out:
	return len;
}

/* procfs write/read for br_mac_table */
static int proc_read_br_mac (char *buf, char **start, off_t offset,
							int len, int *eof, void *data)
{
	int count =0;
	struct net_bridge *br = (struct net_bridge *) data;
	struct br_mac_table_t *tlist;
	struct br_mac_table6_t *tlist6;

	spin_lock_bh(&br->lock); // bridge lock

	printk( "*************************************************************************\n");
	printk( "* br->br_mac_table_enable : %d\n",atomic_read(&br->br_mac_table_enable));
	printk( "*************************************************************************\n");

	/* for IPv4-MAC */
	printk( "* IPv4 address\t\tMAC\n");
	list_for_each_entry_rcu(tlist, &(br->br_mac_table.list), list){
		uint32_t ip32_addr = tlist->ip_addr;
		uint8_t *ip8_addr;
		trans_32to8(&ip32_addr, &ip8_addr);
		printk("* %u.%u.%u.%u \t %X:%X:%X:%X:%X:%X \n",
			*ip8_addr, *(ip8_addr+1), *(ip8_addr+2), *(ip8_addr+3),
			tlist->mac_addr[0], tlist->mac_addr[1], tlist->mac_addr[2], tlist->mac_addr[3],
			tlist->mac_addr[4], tlist->mac_addr[5]
		);
	}

	/* for IPv6-MAC */
	printk( "* IPv6 address\t\t\t\tMAC\n");
	list_for_each_entry_rcu(tlist6, &(br->br_mac_table6.list), list){
		printk("* %08X %08X %08X %08X \t\t %X:%X:%X:%X:%X:%X \n",
			ntohl(tlist6->ip6_addr.s6_addr32[0]), ntohl(tlist6->ip6_addr.s6_addr32[1]),
			ntohl(tlist6->ip6_addr.s6_addr32[2]), ntohl(tlist6->ip6_addr.s6_addr32[3]),
			tlist6->mac_addr[0], tlist6->mac_addr[1], tlist6->mac_addr[2], tlist6->mac_addr[3],
			tlist6->mac_addr[4], tlist6->mac_addr[5]
		);
	}

	printk( "*************************************************************************\n");
	spin_unlock_bh(&br->lock); // bridge unlock

	*eof = 1;
	return count;
}

static int proc_write_br_mac (struct file *file, const char *buf,
								unsigned long count, void *data)
{
	int len = MESSAGE_LENGTH + 1;
	char *action;
	struct net_bridge *br;
	char message[len];
	
	if(count > MESSAGE_LENGTH) {len = MESSAGE_LENGTH;}
	else {len = count; }
	if(copy_from_user(message, buf, len))
		return -EFAULT;
	message[len-1] = '\0';

	br = (struct net_bridge *) data;

	spin_lock_bh(&br->lock); // bridge lock

	/* ============================  enable br_mac_table ===================*/
	/* NOTE: The br_mac_table will be clean.                                  */
	action = ACTION_ENABLE_TABLE;
	if ( memcmp(message, action, sizeof(ACTION_ENABLE_TABLE) )== 0){
		/* reset br_mac_table */
		struct br_mac_table_t *tlist;
		struct br_mac_table6_t *tlist6;
		struct list_head *pos, *q;
		/* del br_mac_table.list */
		list_for_each_safe(pos, q, &(br->br_mac_table.list) ){
			tlist= list_entry(pos, struct br_mac_table_t, list);
			list_del(pos);
			kfree(tlist);
		}
		/* del br_mac_table6.list */
		list_for_each_safe(pos, q, &(br->br_mac_table6.list) ){
			tlist6= list_entry(pos, struct br_mac_table6_t, list);
			list_del(pos);
			kfree(tlist6);
		}
		memset( &(br->br_mac_table), 0, sizeof( struct br_mac_table_t));
		INIT_LIST_HEAD(&br->br_mac_table.list);
		INIT_LIST_HEAD(&br->br_mac_table6.list);
		atomic_set(&br->br_mac_table_enable, 1);
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "[BR_MAC_PROC]->ENABLE %s\n", br->dev->name);
		printk(KERN_INFO "br_mac_table_enable = %d\n", atomic_read(&br->br_mac_table_enable) );
#endif
	}

	/* ============================  disable br_mac_table ===================*/
	action = ACTION_DISABLE_TABLE;
	if ( memcmp(message, action, sizeof(ACTION_DISABLE_TABLE) )== 0){
		atomic_set(&br->br_mac_table_enable, 0);
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS_DEBUG
		printk(KERN_INFO "[BR_MAC_PROC]->DISABLE %s\n", br->dev->name);
		printk(KERN_INFO "br_mac_table_enable = %d\n", atomic_read(&br->br_mac_table_enable) );
#endif
	}

	spin_unlock_bh(&br->lock); // bridge unlock

	return len;
}

static int proc_read_br_igmp_member (char *buf, char **start, off_t offset,
								int len, int *eof, void *data)
{
	/* Nothing */
	int rtn = sprintf(buf, "N/A\n");
	return rtn;
}

static int proc_write_br_igmp_member (struct file *file, const char *buf,
								unsigned long count, void *data)
{
	char *message = (char *)kmalloc(count, GFP_ATOMIC);
//marco, the strsep in previous will chg the postion of message, so I add a pointer
//orig_message to record the inital position of message
	char *orig_message=message;	
	
	char *action;
	struct net_bridge *br = (struct net_bridge *) data;

	if ( copy_from_user(message, buf, count) )
	{
		kfree(message);
		return -EFAULT;
	}
	message[count-1] = '\0';

	action = strsep(&message, MESSAGE_DELIM);

	/* ============================  add - START =====================================*/
		// Todo Maybe
	/* ============================  add - END  =================================*/

	/* ============================  remove - START =================================*/
	if ( memcmp(action, ACTION_REMOVE, sizeof(ACTION_REMOVE)) == 0 )
	{
		unsigned char mac_addr[6];
		struct net_bridge_fdb_entry *hit_fdb_entry;

		spin_lock_bh(&br->lock); // bridge lock

		if ( check_str(message, 1) == 0 )
		{
			split_MAC(mac_addr, message);
		}
		else
		{
			printk(KERN_INFO "[BR_IGMP_MEMBER_PROC]-> Host MAC address: %s is illegal !!\n", message);
			printk(KERN_INFO "\tHost address only accept char: \":1234567890ABCDEFabcdef\"\n");
			spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
			goto proc_write_br_igmp_member_error;
		}

		/* searching bridge_fdb_entry */
		hit_fdb_entry = __br_fdb_get(br, mac_addr);
		/* NOTE: The effect of successful called __br_fdb_get() also takes lock bridge and reference counts. */

		if ( hit_fdb_entry != NULL )
		{
			if ( atomic_read(&hit_fdb_entry->dst->wireless_interface) == 1 )
			{
				//printk(KERN_INFO "[BR_IGMP_MEMBER_PROC]-> REMOVE ...\n");

				struct port_igmpp_table_t *pt = &hit_fdb_entry->dst->port_igmpp_table;
				int gid, mid;

				for ( gid=0; gid<GROUPLIST_NUMBER; gid++ )
				{
					// IPv4
					if ( pt->group_list[gid].used == 1 )
					{
						printk(KERN_INFO "Group.v4: %d\n", gid);
						for ( mid=0; mid<HOSTLIST_NUMBER; mid++ )
						{
							if ( pt->group_list[gid].host_list[mid].used == 1 )
							{
								printk(KERN_INFO "Member.v4: %d\n", mid);
								if ( !memcmp(pt->group_list[gid].host_list[mid].mac_addr, mac_addr, sizeof(uint8_t)*6) )
								{
									// remove
									remove_MAC_from_pool(pt, gid, mid);
								}
							}
						}
						check_GROUP_is_empty_and_remove(pt, gid);
					}

					// IPv6
					if ( pt->group_list6[gid].used == 1 )
					{
						printk(KERN_INFO "Group.v6: %d\n", gid);
						for ( mid=0; mid<HOSTLIST_NUMBER; mid++ )
						{
							if ( pt->group_list6[gid].host_list[mid].used == 1 )
							{
								printk(KERN_INFO "Member.v6: %d\n", mid);
								if ( !memcmp(pt->group_list6[gid].host_list[mid].mac_addr, mac_addr, sizeof(uint8_t)*6) )
								{
									// remove
									remove_MAC_from_pool6(pt, gid, mid);
								}
							}
						}
						check_GROUP_is_empty_and_remove6(pt, gid);
					}
				}
				//printk(KERN_INFO "[BR_IGMP_MEMBER_PROC]-> REMOVE DONE !!\n");
			}
			fdb_delete(hit_fdb_entry); // release __br_fdb_get() locks
		}
		else
		{
			printk(KERN_INFO "The return value of __br_fdb_get() is NULL -> MAC: %X:%X:%X:%X:%X:%X \n",
				mac_addr[0], mac_addr[1], mac_addr[2], mac_addr[3], mac_addr[4], mac_addr[5] );
		}

		spin_unlock_bh(&br->lock);

		goto proc_write_br_igmp_member_error;
	}
	/* ============================= remove - END ======================================*/

proc_write_br_igmp_member_error:
//	kfree(message);
//marco, the strsep in previous will chg the postion of message, so I add a pointer
//orig_message to record the inital position of message
	kfree(orig_message);
	return count;
}

#elif CONFIG_BRIDGE_ALPHA_MULTICAST_SNOOP

static int check_mac(char *s)
{
	char * accept = MAC_ACCEPT_CHAR;
	if(!s || !(*s))	return (-1);
	if ( strlen(s) == strspn(s, accept) )
		return 0;
	return (-1);
}
/* search device'name that matched */
/* called under bridge lock */
static struct net_bridge_port * search_device(struct net_bridge * br, char* name)
{
	struct net_bridge_port *p;
	list_for_each_entry(p, &br->port_list, list) {
		if (strcmp(p->dev->name, name) == 0 ){
			return p;
		}
	}
	return NULL;
}
static uint8_t * hex2dec(char *ch)
{
	if ( *ch >= '0' && *ch <= '9') *ch=*ch-'0';
	else if ( *ch >= 'A' && *ch <= 'F')  *ch=*ch-'A' +10;
	else if ( *ch >= 'a' && *ch <= 'f')  *ch=*ch-'a' +10;
	return ch;
}
static void split_MAC(unsigned char * mac_addr, char * token)
{
	char *macDelim = MAC_DELIM;
	char **pMAC = &token;
	char * macField_char[6];
	int i;
	for (i=0; i<6; ++i)
	{
		int j;
		char temp[2];
		macField_char[i] = strsep(pMAC, macDelim);
		/* copy each char byte and convert to dec number */
		for(j=0; j<2; ++j) {
			memcpy(&temp[j],macField_char[i]+j, sizeof(char));
			hex2dec(&temp[j]);
		}

		temp[0] = temp[0] << 4;
		*(mac_addr + i)= (temp[0]^temp[1]);
	}
}

/* called under bridge lock */
static int table_setsnoop(struct net_bridge *br, int type)
{
	switch(type)
	{
		case ENABLE :
			br->snooping = 1;
			if(snoop_init) 
				snoop_init();
			else 
			{
				printk("No snooping implementation. Please check !! \n");
				return (-1);
			}
			break;
		case DISABLE : 
			br->snooping = 0;
			if(snoop_deinit) 
				snoop_deinit();
			else
			{
				printk("No snooping implementation. Please check !! \n");
				return (-1);
			}
			break;
	}
	return 0;
}


/* set wireless identifier */
/* called under bridge lock */
static int table_setwl(struct net_bridge *br, char * name, int type)
{
	struct net_bridge_port *hit_port;
	hit_port = search_device(br, name);
	if (hit_port != NULL){
		if(type==ENABLE)
			atomic_set(&hit_port->wireless_interface, 1);
		else 
			atomic_set(&hit_port->wireless_interface, 0);
		return 0;
	}else
		return (-1);
}

static void table_add(  struct net_bridge_port *p, unsigned char * group_addr, unsigned char * member_addr)
{
	int found=0;
	unsigned long flags;
	spinlock_t lock;
	struct port_group_mac *pgentry;
	struct port_member_mac *pcentry;
	
	if(group_addr==NULL || member_addr==NULL)
		return;	

	spin_lock_irqsave(&lock,flags);

	//1. find old group if exist
	list_for_each_entry(pgentry, &p->igmp_group_list, list)
	{
		if(!memcmp(pgentry->grpmac,group_addr, 6))
		{
			found = 1;
			break;
		}
	}

	if(!found)	//create new group
	{
		pgentry = (struct port_group_mac *)kmalloc(sizeof(struct port_group_mac), GFP_ATOMIC);
		INIT_LIST_HEAD(&pgentry->list);
		INIT_LIST_HEAD(&pgentry->member_list);
		list_add(&pgentry->list, &p->igmp_group_list);
		memcpy(pgentry->grpmac , group_addr , 6);
		print_debug("brg : Create new group 0x%02x%02x%02x%02x%02x%02x\n", 
			group_addr[0],group_addr[1],group_addr[2],group_addr[3],group_addr[4],group_addr[5]);
	}
	//2. find old client mac if exist
	found = 0;
	list_for_each_entry(pcentry, &pgentry->member_list, list)
	{
		if(!memcmp(pcentry->member_mac,member_addr, 6))
		{	/* member already exist, do nothing ~*/
			found = 1;
			break;
		}
	}
	if(!found)
	{	/* member NOT exist, create NEW ONE and attached it to this group-mac linked list ~*/
		pcentry	= (struct port_member_mac *)kmalloc(sizeof(struct port_member_mac), GFP_ATOMIC);
		INIT_LIST_HEAD(&pcentry->list);
		list_add(&pcentry->list, &pgentry->member_list);
		memcpy( pcentry->member_mac ,member_addr , 6);
		print_debug("brg : Added client mac 0x%02x%02x%02x%02x%02x%02x to group 0x%02x%02x%02x%02x%02x%02x\n", 
			pcentry->member_mac[0],pcentry->member_mac[1],pcentry->member_mac[2],pcentry->member_mac[3],pcentry->member_mac[4],pcentry->member_mac[5],
			pgentry->grpmac[0],pgentry->grpmac[1],pgentry->grpmac[2],pgentry->grpmac[3],pgentry->grpmac[4],pgentry->grpmac[5]
		);
	}
	spin_unlock_irqrestore (&lock, flags);
}

/*
1. find old group 
2. find old client mac 
3. if group is empty, delete group
4. snooping : update the group port list 
*/
static void table_remove(struct net_bridge_port *p, unsigned char * group_addr, unsigned char * member_addr)
{
	struct port_group_mac *pgentry;
	struct port_member_mac *pcentry;
	int found = 0;
	
	//0. sanity check
	if(group_addr==NULL || member_addr==NULL)
		return;

	//1. find old group 
	list_for_each_entry(pgentry, &p->igmp_group_list, list)
	{
		if(!memcmp(pgentry->grpmac,group_addr, 6))
		{
			found = 1;
			break;
		}
	}
	if(!found)
	{
		print_debug("dbg : Can't delete 0x%02x%02x%02x%02x%02x%02x, group NOT FOUND.\n", 
			group_addr[0],group_addr[1],group_addr[2],group_addr[3],group_addr[4],group_addr[5] );
		return;
	}

	//2. find old client mac 
	found = 0;
	list_for_each_entry(pcentry, &pgentry->member_list, list)
	{
		if(!memcmp(pcentry->member_mac,member_addr, 6))
		{	
			found = 1;
			break;
		}
	}

	if(found)
	{
		/* member to be deleted FOUND, DELETE IT ! */
		list_del(&pcentry->list);
		kfree(pcentry);
		print_debug("dbg : Delete client 0x%02x%02x%02x%02x%02x%02x in group 0x%02x%02x%02x%02x%02x%02x\n", 
			member_addr[0],member_addr[1],member_addr[2],member_addr[3],member_addr[4],member_addr[5],
			group_addr[0],group_addr[1],group_addr[2],group_addr[3],group_addr[4],group_addr[5] );	
	}else
	{	/* do nothing, just debug */
		print_debug("dbg : Can't delete client 0x%02x%02x%02x%02x%02x%02x, client NOT FOUND in group 0x%02x%02x%02x%02x%02x%02x\n", 
			member_addr[0],member_addr[1],member_addr[2],member_addr[3],member_addr[4],member_addr[5],
			group_addr[0],group_addr[1],group_addr[2],group_addr[3],group_addr[4],group_addr[5] );
	}

	//3. if group is empty, delete group
	if(list_empty(&pgentry->member_list))
	{
		list_del(&pgentry->member_list);
		list_del(&pgentry->list);
		kfree(pgentry);
		//remove group mac from port_list
		print_debug("dbg : Delete group 0x%02x%02x%02x%02x%02x%02x since its empty \n", 
			group_addr[0],group_addr[1],group_addr[2],group_addr[3],group_addr[4],group_addr[5] );	
		return;
	}
}

static int proc_read_alpha_multicast (char *buf, char **start, off_t offset,
								int len, int *eof, void *data)
{
	int count =0;
	struct net_bridge *br = (struct net_bridge *) data;
	struct net_bridge_port *p;
	struct port_group_mac *pgentry;
	struct port_member_mac *pmentry;	

	spin_lock_bh(&br->lock); // bridge lock
	printk( "**********************************************************************\n");
	printk( "* bridge name    : %s\n",br->dev->name);
	printk( "* snooping         : %d\n",br->snooping);
	printk( "**********************************************************************\n");
	list_for_each_entry_rcu(p, &br->port_list, list) {
		printk( "* ==============================================================\n");
		printk( "* <%d> port name : %s\n", p->port_no, p->dev->name);
		printk( "* <%d> wireless_interface -> %d\n", p->port_no, atomic_read(&p->wireless_interface) );
		
		//traverse through all group list, list all the members inside 
		list_for_each_entry(pgentry, &p->igmp_group_list, list)
		{
			printk(" Group Mac  0x%02x%02x%02x%02x%02x%02x\n",pgentry->grpmac[0],
															pgentry->grpmac[1],
															pgentry->grpmac[2],
															pgentry->grpmac[3],
															pgentry->grpmac[4],
															pgentry->grpmac[5]);

			list_for_each_entry(pmentry, &pgentry->member_list, list)
			{
				printk("   membermac 0x%02x%02x%02x%02x%02x%02x\n",pmentry->member_mac[0],
															pmentry->member_mac[1],
															pmentry->member_mac[2],
															pmentry->member_mac[3],
															pmentry->member_mac[4],
															pmentry->member_mac[5]);
			}
		}
		printk( "* ==============================================================\n");
	} // list_for_each_entry_rcu() - END
	printk( "**********************************************************************\n");
	spin_unlock_bh(&br->lock); // bridge unlock

	*eof = 1;
	return count;
}

static int proc_write_alpha_multicast (struct file *file, const char *buf,
								unsigned long count, void *data)
{
	int len = MESSAGE_LENGTH+1;
	char message[len];
	char *msgDelim = MESSAGE_DELIM;
	char *pmesg;
	char *action_token, *action;
	struct net_bridge *br;

	if(count > MESSAGE_LENGTH) {len = MESSAGE_LENGTH;}
	else {len = count; }
	if(copy_from_user(message, buf, len))
		return -EFAULT;
	message[len-1] = '\0';

	/* split input message that get from user space
	 * token[0] => action token --> add or remove
	 * token[1] => multicast group mac address
	 * token[2] => member MAC address of host
	 */
	pmesg = message ;

	action_token = strsep(&pmesg, msgDelim);

	br = (struct net_bridge *) data;

	/* ============================  set wireless enhance =====================*/
	action = ACTION_SET_ENHANCE;
	if (memcmp(action_token, action, sizeof(ACTION_SET_ENHANCE) )== 0){
		spin_lock_bh(&br->lock); // bridge lock
		if (table_setwl(br,pmesg, ENABLE) != 0){
			print_debug("[BR_IGMPP_PROC]->WARNING SETWL FAILURE-> %s\n",pmesg);
		}
		spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
		goto proc_write_br_igmpp_out;
	}

	/* ============================  unset wireless enhance  ===================*/
	action = ACTION_UNSET_ENHANCE;
	if ( memcmp(action_token, action, sizeof(ACTION_UNSET_ENHANCE) )== 0){
		spin_lock_bh(&br->lock); // bridge lock
		if (table_setwl(br,pmesg, DISABLE) != 0){
			print_debug(KERN_INFO "[BR_IGMPP_PROC]->WARNING SETWL FAILURE-> %s\n",pmesg);
		}
		spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
		goto proc_write_br_igmpp_out;
	}

	/* ============================  set snooping ============================*/
	action = ACTION_SET_SNOOPING;
	if (memcmp(action_token, action, sizeof(ACTION_SET_SNOOPING) )== 0){
		spin_lock_bh(&br->lock); // bridge lock
		if (table_setsnoop(br, ENABLE) != 0){
			print_debug(KERN_INFO "[BR_IGMPP_PROC]->WARNING SET snooping FAILURE-> %s\n",pmesg);
		}
		spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
		goto proc_write_br_igmpp_out;
	}

	/* ============================  unset snooping ==========================*/
	action = ACTION_UNSET_SNOOPING;
	if ( memcmp(action_token, action, sizeof(ACTION_UNSET_SNOOPING) )== 0){
		spin_lock_bh(&br->lock); // bridge lock
		if (table_setsnoop(br, DISABLE) != 0){
			print_debug(KERN_INFO "[BR_IGMPP_PROC]->WARNING UNSET snooping FAILURE-> %s\n",pmesg);
		}
		spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
		goto proc_write_br_igmpp_out;
	}

	/* ============================  add - START =====================================*/
	action = ACTION_ADD;
	if ( memcmp(action_token, action, sizeof(ACTION_ADD) )== 0){
		/********** add - START of processing input string **********/
		char *token[2]={0,0};
		int i;
		unsigned char mac_addr[6];
		unsigned char grp_mac_addr[6];	
		struct net_bridge_fdb_entry *hit_fdb_entry;

		for(i=0; i<2; ++i)
			token[i] = strsep(&pmesg, msgDelim);

		/* Only accept MAC, split host MAC address */
		if ( check_mac(token[0]) == -1 || check_mac(token[1]) == -1)
		{
			print_debug(KERN_INFO "[BR_IGMPP_PROC]-> Host MAC address: %s,%s is illegal !!\n",
				(token[0])?(token[0]):"null",
				(token[1])?(token[1]):"null");
			goto proc_write_br_igmpp_out;
		}

		spin_lock_bh(&br->lock); // bridge lock
		split_MAC(grp_mac_addr, token[0]);
		split_MAC(mac_addr, token[1]);
		
		print_debug("brg : group 0x%02x%02x%02x%02x%02x%02x member 0x%02x%02x%02x%02x%02x%02x\n", 
			grp_mac_addr[0],grp_mac_addr[1],grp_mac_addr[2],grp_mac_addr[3],grp_mac_addr[4],grp_mac_addr[5],
			mac_addr[0],mac_addr[1],mac_addr[2],mac_addr[3],mac_addr[4],mac_addr[5]);

		/* searching bridge_fdb_entry */
		hit_fdb_entry = __br_fdb_get(br, mac_addr);
		/* NOTE: The effect of successful called br_fdb_get() also takes lock bridge and reference counts. */

		if (hit_fdb_entry != NULL)
		{
			table_add(hit_fdb_entry->dst, grp_mac_addr, mac_addr);
			fdb_delete(hit_fdb_entry); // release br_fdb_get() locks
		}
		else
		{
			print_debug(KERN_INFO "The return value of __br_fdb_get() is NULL -> MAC: %X:%X:%X:%X:%X:%X \n",
				mac_addr[0], mac_addr[1], mac_addr[2], mac_addr[3], mac_addr[4], mac_addr[5] );
		}

		spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out
		//do snoop if implemented in switch
		if(add_member) add_member(grp_mac_addr,mac_addr);
		
		goto proc_write_br_igmpp_out;
	}


	action = ACTION_REMOVE;
	if ( memcmp(action_token, action, sizeof(ACTION_REMOVE) ) == 0)
	{
		char *token[2]={0,0};
		int i;
		unsigned char mac_addr[6];
		struct net_bridge_fdb_entry *hit_fdb_entry;
		unsigned char grp_mac_addr[6];

		for(i=0; i<2; ++i)
			token[i] = strsep(&pmesg, msgDelim);

		/* Only accept MAC, split host MAC address */
		if (check_mac(token[0]) == -1 || check_mac(token[1]) == -1)
		{
			print_debug(KERN_INFO "[BR_IGMPP_PROC]-> Host MAC address: %s,%s is illegal !!\n",
				(token[0])?(token[0]):"null",
				(token[1])?(token[1]):"null");
			goto proc_write_br_igmpp_out;
		}

		spin_lock_bh(&br->lock); // bridge lock			
		split_MAC(grp_mac_addr, token[0]);
		split_MAC(mac_addr, token[1]);
		
		print_debug("brg : group 0x%02x%02x%02x%02x%02x%02x member 0x%02x%02x%02x%02x%02x%02x\n", 
			grp_mac_addr[0],grp_mac_addr[1],grp_mac_addr[2],grp_mac_addr[3],grp_mac_addr[4],grp_mac_addr[5],
			mac_addr[0],mac_addr[1],mac_addr[2],mac_addr[3],mac_addr[4],mac_addr[5]);

		/* searching bridge_fdb_entry */
		hit_fdb_entry = __br_fdb_get(br, mac_addr);
		/* NOTE: The effect of successful called __br_fdb_get() also takes lock bridge and reference counts. */

		if (hit_fdb_entry != NULL)
		{
			table_remove(hit_fdb_entry->dst, grp_mac_addr, mac_addr);
			fdb_delete(hit_fdb_entry); // release br_fdb_get() locks
		}
		else
		{
			print_debug(KERN_INFO "The return value of __br_fdb_get() is NULL -> MAC: %X:%X:%X:%X:%X:%X \n",
				mac_addr[0], mac_addr[1], mac_addr[2], mac_addr[3], mac_addr[4], mac_addr[5] );
		}
		spin_unlock_bh(&br->lock); // bridge unlock for goto proc_write_br_igmpp_out

		//do snoop if implemented in switch
		if(del_member) del_member(grp_mac_addr, mac_addr);
		
		goto proc_write_br_igmpp_out;
	}
	/* ============================= remove - END ======================================*/

	proc_write_br_igmpp_out:
	return len;
}
#endif
void bridge_groups_proc(int create,struct net_bridge *br,const char *name, struct net *net);
static struct net_device *new_bridge_dev(struct net *net, const char *name)
{
	struct net_bridge *br;
	struct net_device *dev;
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS
	char igmpp_proc_name[32];
#endif

#ifdef CONFIG_BRIDGE_ALPHA_MULTICAST_SNOOP
	char alpha_proc_name[32];
#endif
	dev = alloc_netdev(sizeof(struct net_bridge), name,
			   br_dev_setup);

	if (!dev)
		return NULL;
	dev_net_set(dev, net);

	br = netdev_priv(dev);
	br->dev = dev;

	spin_lock_init(&br->lock);
	INIT_LIST_HEAD(&br->port_list);
	spin_lock_init(&br->hash_lock);

	br->bridge_id.prio[0] = 0x80;
	br->bridge_id.prio[1] = 0x00;

	memcpy(br->group_addr, br_group_address, ETH_ALEN);

	br->feature_mask = dev->features;
	br->stp_enabled = BR_NO_STP;
	br->designated_root = br->bridge_id;
	br->root_path_cost = 0;
	br->root_port = 0;
	br->bridge_max_age = br->max_age = 20 * HZ;
	br->bridge_hello_time = br->hello_time = 2 * HZ;
	br->bridge_forward_delay = br->forward_delay = 15 * HZ;
	br->topology_change = 0;
	br->topology_change_detected = 0;
	br->ageing_time = 300 * HZ;
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS
	snprintf(igmpp_proc_name, sizeof(igmpp_proc_name), "br_igmpp_%s", name);
	br->br_igmpp_proc = create_proc_entry (igmpp_proc_name, 0644, net->proc_net);
	if (br->br_igmpp_proc == NULL)
		return ERR_PTR(-ENOMEM);
	br->br_igmpp_proc->data = (void*)br;
	br->br_igmpp_proc->read_proc = proc_read_br_igmpp;
	br->br_igmpp_proc->write_proc = proc_write_br_igmpp;

	snprintf(igmpp_proc_name, sizeof(igmpp_proc_name), "br_mac_%s", name);
	br->br_mac_proc = create_proc_entry (igmpp_proc_name, 0644, net->proc_net);
	if (br->br_mac_proc == NULL)
		return ERR_PTR(-ENOMEM);
	br->br_mac_proc->data = (void*)br;
	br->br_mac_proc->read_proc = proc_read_br_mac;
	br->br_mac_proc->write_proc = proc_write_br_mac;

	snprintf(igmpp_proc_name, sizeof(igmpp_proc_name), "br_igmp_member_%s", name);
	br->br_igmp_member_proc = create_proc_entry (igmpp_proc_name, 0644, net->proc_net);
	if (br->br_igmp_member_proc == NULL)
		return ERR_PTR(-ENOMEM);
	br->br_igmp_member_proc->data = (void*)br;
	br->br_igmp_member_proc->read_proc  = proc_read_br_igmp_member;
	br->br_igmp_member_proc->write_proc = proc_write_br_igmp_member;

	INIT_LIST_HEAD(&br->br_mac_table.list);
	INIT_LIST_HEAD(&br->br_mac_table6.list);
#elif CONFIG_BRIDGE_ALPHA_MULTICAST_SNOOP
	snprintf(alpha_proc_name, sizeof(alpha_proc_name), "alpha/multicast_%s", name);
	br->alpha_multicast_proc = create_proc_entry (alpha_proc_name, 0644, NULL);
	if (br->alpha_multicast_proc == NULL)
	{
		printk("create  proc FAILED %s\n", name);
		return ERR_PTR(-ENOMEM);
	}	
	br->alpha_multicast_proc->data = (void*)br;
	br->alpha_multicast_proc->read_proc = proc_read_alpha_multicast;
	br->alpha_multicast_proc->write_proc = proc_write_alpha_multicast;
	br->snooping = 0;
#endif

#ifdef CONFIG_BRIDGE_E_PARTITION_BWCTRL
	br->e_partition = 0;
#endif
#ifdef CONFIG_BRIDGE_PORT_GROUP
	bridge_groups_proc(1,br,name, net);
	br->groups=0;
#endif
	br_netfilter_rtable_init(br);

	INIT_LIST_HEAD(&br->age_list);

	br_stp_timer_init(br);

	return dev;
}

/* find an available port number */
static int find_portno(struct net_bridge *br)
{
	int index;
	struct net_bridge_port *p;
	unsigned long *inuse;

	inuse = kcalloc(BITS_TO_LONGS(BR_MAX_PORTS), sizeof(unsigned long),
			GFP_KERNEL);
	if (!inuse)
		return -ENOMEM;

	set_bit(0, inuse);	/* zero is reserved */
	list_for_each_entry(p, &br->port_list, list) {
		set_bit(p->port_no, inuse);
	}
	index = find_first_zero_bit(inuse, BR_MAX_PORTS);
	kfree(inuse);

	return (index >= BR_MAX_PORTS) ? -EXFULL : index;
}

/* called with RTNL but without bridge lock */
static struct net_bridge_port *new_nbp(struct net_bridge *br,
				       struct net_device *dev)
{
	int index;
	struct net_bridge_port *p;

	index = find_portno(br);
	if (index < 0)
		return ERR_PTR(index);

	p = kzalloc(sizeof(*p), GFP_KERNEL);
	if (p == NULL)
		return ERR_PTR(-ENOMEM);

	p->br = br;
	dev_hold(dev);
	p->dev = dev;
	p->path_cost = port_cost(dev);
	p->priority = 0x8000 >> BR_PORT_BITS;
	p->port_no = index;
	p->flags = 0;
	br_init_port(p);
	p->state = BR_STATE_DISABLED;
	br_stp_port_timer_init(p);

	return p;
}

static struct device_type br_type = {
	.name	= "bridge",
};

int br_add_bridge(struct net *net, const char *name)
{
	struct net_device *dev;
	int ret;

	dev = new_bridge_dev(net, name);
	if (!dev)
		return -ENOMEM;

	rtnl_lock();
	if (strchr(dev->name, '%')) {
		ret = dev_alloc_name(dev, dev->name);
		if (ret < 0)
			goto out_free;
	}

	SET_NETDEV_DEVTYPE(dev, &br_type);

	ret = register_netdevice(dev);
	if (ret)
		goto out_free;

	ret = br_sysfs_addbr(dev);
	if (ret)
		unregister_netdevice(dev);
 out:
	rtnl_unlock();
	return ret;

out_free:
	free_netdev(dev);
	goto out;
}

int br_del_bridge(struct net *net, const char *name)
{
	struct net_device *dev;
	int ret = 0;
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS
	char igmpp_proc_name[32];
#endif

#ifdef CONFIG_BRIDGE_ALPHA_MULTICAST_SNOOP
	char alpha_proc_name[32];
#endif
	rtnl_lock();
	dev = __dev_get_by_name(net, name);
	if (dev == NULL)
		ret =  -ENXIO; 	/* Could not find device */

	else if (!(dev->priv_flags & IFF_EBRIDGE)) {
		/* Attempt to delete non bridge device! */
		ret = -EPERM;
	}

	else if (dev->flags & IFF_UP) {
		/* Not shutdown yet. */
		ret = -EBUSY;
	}

	else
		del_br(netdev_priv(dev), NULL);

	rtnl_unlock();
#ifdef CONFIG_BRIDGE_IGMPP_PROCFS
	snprintf(igmpp_proc_name, sizeof(igmpp_proc_name), "net/br_igmpp_%s", name);
	remove_proc_entry(igmpp_proc_name, 0);

	snprintf(igmpp_proc_name, sizeof(igmpp_proc_name), "net/br_mac_%s", name);
	remove_proc_entry(igmpp_proc_name, 0);
#elif CONFIG_BRIDGE_ALPHA_MULTICAST_SNOOP
	print_debug("remove proc entry %s\n", name);
	snprintf(alpha_proc_name, sizeof(alpha_proc_name), "alpha/multicast_%s", name);
	remove_proc_entry(alpha_proc_name, 0);
#endif
	return ret;
}

/* MTU of the bridge pseudo-device: ETH_DATA_LEN or the minimum of the ports */
int br_min_mtu(const struct net_bridge *br)
{
	const struct net_bridge_port *p;
	int mtu = 0;

	ASSERT_RTNL();

	if (list_empty(&br->port_list))
		mtu = ETH_DATA_LEN;
	else {
		list_for_each_entry(p, &br->port_list, list) {
			if (!mtu  || p->dev->mtu < mtu)
				mtu = p->dev->mtu;
		}
	}
	return mtu;
}

/*
 * Recomputes features using slave's features
 */
void br_features_recompute(struct net_bridge *br)
{
	struct net_bridge_port *p;
	unsigned long features, mask;

	features = mask = br->feature_mask;
	if (list_empty(&br->port_list))
		goto done;

	features &= ~NETIF_F_ONE_FOR_ALL;

	list_for_each_entry(p, &br->port_list, list) {
		features = netdev_increment_features(features,
						     p->dev->features, mask);
	}

done:
	br->dev->features = netdev_fix_features(features, NULL);
}

/* called with RTNL */
int br_add_if(struct net_bridge *br, struct net_device *dev)
{
	struct net_bridge_port *p;
	int err = 0;

	/* Don't allow bridging non-ethernet like devices */
	if ((dev->flags & IFF_LOOPBACK) ||
	    dev->type != ARPHRD_ETHER || dev->addr_len != ETH_ALEN)
		return -EINVAL;

	/* No bridging of bridges */
	if (dev->netdev_ops->ndo_start_xmit == br_dev_xmit)
		return -ELOOP;

	/* Device is already being bridged */
	if (dev->br_port != NULL)
		return -EBUSY;

	/* No bridging devices that dislike that (e.g. wireless) */
	if (dev->priv_flags & IFF_DONT_BRIDGE)
		return -EOPNOTSUPP;

	p = new_nbp(br, dev);
	if (IS_ERR(p))
		return PTR_ERR(p);

	err = dev_set_promiscuity(dev, 1);
	if (err)
		goto put_back;

	err = kobject_init_and_add(&p->kobj, &brport_ktype, &(dev->dev.kobj),
				   SYSFS_BRIDGE_PORT_ATTR);
	if (err)
		goto err0;

	err = br_fdb_insert(br, p, dev->dev_addr);
	if (err)
		goto err1;

	err = br_sysfs_addif(p);
	if (err)
		goto err2;

	rcu_assign_pointer(dev->br_port, p);
	dev_disable_lro(dev);

	list_add_rcu(&p->list, &br->port_list);

	spin_lock_bh(&br->lock);

	#ifdef CONFIG_BRIDGE_ALPHA_MULTICAST_SNOOP
	INIT_LIST_HEAD(&p->igmp_group_list);	
	atomic_set(&p->wireless_interface, 0);
	#endif

	br_stp_recalculate_bridge_id(br);
	br_features_recompute(br);

	if ((dev->flags & IFF_UP) && netif_carrier_ok(dev) &&
	    (br->dev->flags & IFF_UP))
		br_stp_enable_port(p);
	spin_unlock_bh(&br->lock);

	br_ifinfo_notify(RTM_NEWLINK, p);

	dev_set_mtu(br->dev, br_min_mtu(br));

	kobject_uevent(&p->kobj, KOBJ_ADD);

	return 0;
err2:
	br_fdb_delete_by_port(br, p, 1);
err1:
	kobject_put(&p->kobj);
	p = NULL; /* kobject_put frees */
err0:
	dev_set_promiscuity(dev, -1);
put_back:
	dev_put(dev);
	kfree(p);
	return err;
}

/* called with RTNL */
int br_del_if(struct net_bridge *br, struct net_device *dev)
{
	struct net_bridge_port *p = dev->br_port;

	if (!p || p->br != br)
		return -EINVAL;

	del_nbp(p);

	spin_lock_bh(&br->lock);
	br_stp_recalculate_bridge_id(br);
	br_features_recompute(br);
	spin_unlock_bh(&br->lock);

	return 0;
}

void br_net_exit(struct net *net)
{
	struct net_device *dev;
	LIST_HEAD(list);

	rtnl_lock();
	for_each_netdev(net, dev)
		if (dev->priv_flags & IFF_EBRIDGE)
			del_br(netdev_priv(dev), &list);

	unregister_netdevice_many(&list);
	rtnl_unlock();

}
#ifdef CONFIG_BRIDGE_PORT_GROUP
static int proc_read_br_port_group (char *buf, char **start, off_t offset,
								int len, int *eof, void *data)
{
	int i;
	struct net_bridge *br = (struct net_bridge *) data;
	struct net_bridge_port *p ;
	
	printk("%s packet in same group\n",br->GroupIsAllow?"Allow":"Drop");
	printk("x is block,* is allow\n");
	spin_lock_bh(&br->lock); // bridge lock
	printk(" interface\\gid 0 1 2 3 4 5 6 7 \n");
	printk("%14s ",br->dev->name);
	for(i=0;i<8;i++)
	{
		if(br->groups&(1<<i)) printk("%s",br->GroupIsAllow?"*":"x");
		else printk("%s",br->GroupIsAllow?"x":"*");
		printk(" ");		
	}				
	printk("\n");		
	list_for_each_entry_rcu(p, &br->port_list, list) 
	{
		printk("%14s ",p->dev->name);		
		for(i=0;i<8;i++)
		{
			if(p->groups&(1<<i)) printk("%s",br->GroupIsAllow?"*":"x");
			else printk("%s",br->GroupIsAllow?"x":"*");
			printk(" ");		
		}				
		printk("\n");		
	}
		
	spin_unlock_bh(&br->lock); // bridge unlock
	*eof = 1;
	return 0;
}
static int proc_write_br_port_group (struct file *file, const char *buf,
								unsigned long count, void *data)
{
	char *token,*p;
	char message[256];
	int groupindex=0,match=0;
	struct net_bridge *br = (struct net_bridge *) data;
	struct net_bridge_port *br_port;
	
	memset(message,0,sizeof(message));
	/*input as ra0 1 2 3 4 5 6 7 8*/
	if(count > sizeof(message))
	{
		printk("message too long\n");	
		return -EFAULT;
	}
	if(copy_from_user(message, buf, sizeof(message)-1))
        return -EFAULT;
    //printk("got message ==%s==\n",message);
    p = message;
    token = strsep(&p, " \n");
    //printk("token ==%s==\n",token);
    if(strcmp1(token,"group_clear")==0)
	{
		spin_lock_bh(&br->lock); // bridge lock
		br->groups = 0x00;
		list_for_each_entry_rcu(br_port, &br->port_list, list) 
		{
			br_port->groups = 0x00;
		}
		spin_unlock_bh(&br->lock); // bridge unlock								
	}
	else if(strcmp1(token,"group_drop")==0)
	{
		spin_lock_bh(&br->lock); // bridge lock
		br->GroupIsAllow = 0;
		spin_unlock_bh(&br->lock); // bridge unlock								
	}
	else if(strcmp1(token,"group_allow")==0)
	{
		spin_lock_bh(&br->lock); // bridge lock
		br->GroupIsAllow = 1;
		spin_unlock_bh(&br->lock); // bridge unlock								
	}
	else if(strcmp1(token,"group")==0)
	{
		//formate group gid eth1 eth2 eth3 .....
		token = strsep(&p, " \n");
		
		if(token!=NULL && (token[0] < '0' || token[0] > '7'))
		{
			printk("group id error\nformat:group gid[0-7] ifname1 ifname2 ...");
			return count;
		}
		groupindex = token[0]-'0';
		//clear this groupid
		spin_lock_bh(&br->lock); // bridge lock
		br->groups &=~(1<<groupindex);
		list_for_each_entry_rcu(br_port, &br->port_list, list) 
		{
			br_port->groups &=~(1<<groupindex);
		}
		spin_unlock_bh(&br->lock); // bridge unlock
		while((token = strsep(&p, " \n"))!=NULL)
		{
			printk("token = %s\n",token);
			if(token[0]==0)
				break;
			spin_lock_bh(&br->lock); // bridge lock
			//bridge interface(br0,br1....)
			if(strcmp1(br->dev->name,token)==0)
			{
				br->groups |=(1<<groupindex);
			}
			else //interface in bridge
			{
				match=0;
				list_for_each_entry_rcu(br_port, &br->port_list, list) 
				{
					if(strcmp1(br_port->dev->name,token)==0)	
					{
						match=1;
						break;
					}
				}
				if(match)
					br_port->groups |=(1<<groupindex);
				else
					printk("error ifname %s not found ignore this interface\n",token);
			}
			spin_unlock_bh(&br->lock); // bridge unlock
		}
	}
	else
	{
		printk("unknow command\n");
		printk("valid command:\n");
		printk("	group gid ifname1 ifname2 ...\n");
		printk("	group_allow\n");
		printk("	group_drop\n");
		printk("	group_clear\n");
	}
	return count;
}			
void bridge_groups_proc(int create,struct net_bridge *br,const char *name, struct net *net)
{
	char procname[128];
	sprintf(procname,"br_group_%s",name);
	if(create)
	{
		br->br_port_group_proc = create_proc_entry (procname, 0644, net->proc_net);
		if(!br->br_port_group_proc) return;
		br->br_port_group_proc->data = (void*)br;
		br->br_port_group_proc->read_proc  = proc_read_br_port_group;
		br->br_port_group_proc->write_proc = proc_write_br_port_group;
	}
	else//delete
	{
		remove_proc_entry(procname, NULL);		
	}
}
#endif
