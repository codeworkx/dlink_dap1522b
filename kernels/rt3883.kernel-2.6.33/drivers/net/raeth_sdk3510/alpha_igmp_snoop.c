#include <linux/module.h>	/* Needed by all modules */
#include <linux/kernel.h>	/* Needed for KERN_INFO */
#include <linux/delay.h>	/* Needed for msleep */

#include <linux/list.h>
#include <linux/proc_fs.h>

#include <linux/version.h>
#include <linux/netdevice.h>
#include <linux/sched.h>
#include "ra2882ethreg.h"

/* comment this to be quiet */
#ifdef CONFIG_BRIDGE_ALPHA_MULTICAST_SNOOP_DEBUG
#define log printk
#else
#define log
#endif

//#define PAGE_SIZE			0x1000 	/* 4096 */ //my compiler say already defined in "include/linux/mmzone.h"
#define SWITCH_READ			0x0
#define SWITCH_WRITE		0x1
#define SWITCH_BASEADDR 	0x10110000

/* rt3052 embedded ethernet switch registers */
#define REG_ESW_VLAN_ID_BASE		0x50
#define REG_ESW_VLAN_MEMB_BASE		0x70
#define REG_ESW_TABLE_SEARCH		0x24
#define REG_ESW_TABLE_STATUS0		0x28
#define REG_ESW_TABLE_STATUS1		0x2C
#define REG_ESW_TABLE_STATUS2		0x30
#define REG_ESW_WT_MAC_AD0			0x34	/* store portmap 18 bytes ( first 6 for portmap, last 12 for agefield,etc ) */
#define REG_ESW_WT_MAC_AD1			0x38	/* store last 2 byte mac*/
#define REG_ESW_WT_MAC_AD2			0x3C	/* store first 4 byte mac */
#define REG_ESW_MAX					0xFC

//prototypes
static inline wait_switch_done(void);

unsigned int rareg(int mode, unsigned int addr, long long int new_value)
{
	unsigned int round,value=0;

	// round addr to PAGE_SIZE
	round = addr;								// keep old value
	addr = (addr / PAGE_SIZE) * PAGE_SIZE;
	round = round - addr;
	
	if(mode==WRITE)
	{
		sysRegWrite(SWITCH_BASEADDR+round, new_value);
		//printk("write 0x%08x ,0x%08x\n", SWITCH_BASEADDR+round,new_value);
	}
	else
	{
		value = sysRegRead(SWITCH_BASEADDR+round);
		//printk("read 0x%08x ,0x%08x\n", SWITCH_BASEADDR+round,value);
	}

	return value;
}

static inline int reg_read(int offset, int *value)
{
    *value = sysRegRead(SWITCH_BASEADDR+offset);
    return 0;
}

static inline int reg_write(int offset, int value)
{
    sysRegWrite(SWITCH_BASEADDR+offset, value);
    return 0;
}

static int					hook_value = 0;
#define HOOK_CHECK					if(!(hook_value & 0x3FF)) return;
#define REMOVE_GROUP_MAC 0x00

static void dump_ports(void)
{
	unsigned int value, mac1=0,mac2=0, port_map=0, i = 0;

	HOOK_CHECK;

	reg_write(REG_ESW_TABLE_SEARCH, 0x1);
	
	printk("Dump all ports\n");
	
	while( i < 0x3fe) {
		reg_read(REG_ESW_TABLE_STATUS0, &value);
		
		if (value & 0x1) { //search_rdy
			if ((value & 0x70) == 0) {
				log("*** RT3052: found an unused entry (age = 3'b000), please check!");
				reg_write(REG_ESW_TABLE_SEARCH, 0x2); //search for next address
				continue;
			}
			
			// read mac1
			reg_read(REG_ESW_TABLE_STATUS2, &mac1);
			
			// read mac2
			reg_read(REG_ESW_TABLE_STATUS1, &mac2);
			mac2 = (mac2 & 0xffff);
			
			port_map = (value & 0x0007f000) >> 12 ;
			
			printk(" 0x%08x%04x , 0x%08x\n", mac1, mac2,port_map);
			
			if (value & 0x2) {
				log("*** RT3052: end of table aa. %d", i);
				break;
			}
			reg_write(REG_ESW_TABLE_SEARCH, 0x2); //search for next address
			i++;
		}else if (value & 0x2) { //at_table_end
			//log("*** RT3052: found the last entry (not ready)aa. %d\n", i);
			return;
		}
		else
			msleep(2);
	}
}

static unsigned int get_port_map(unsigned char *mymac)
{
	unsigned int value, mac1=0,mac2=0, port_map=0, i = 0;

	unsigned int mymac1=0;
	unsigned short mymac2=0;
	
	memcpy(&mymac1, mymac , 4);
	mymac1 = ntohl(mymac1);
	memcpy(&mymac2, mymac+4 , 2);
	mymac2 = ntohs(mymac2);
	HOOK_CHECK;

	reg_write(REG_ESW_TABLE_SEARCH, 0x1);
	
	while( i < 0x3fe) {
		reg_read(REG_ESW_TABLE_STATUS0, &value);
		
		if (value & 0x1) { //search_rdy
			if ((value & 0x70) == 0) {
				log("*** RT3052: found an unused entry (age = 3'b000), please check!");
				reg_write(REG_ESW_TABLE_SEARCH, 0x2); //search for next address
				continue;
			}
			
			// read mac1
			reg_read(REG_ESW_TABLE_STATUS2, &mac1);
			
			// read mac2
			reg_read(REG_ESW_TABLE_STATUS1, &mac2);
			mac2 = (mac2 & 0xffff);
			
			port_map = (value & 0x0007f000) >> 12 ;
			
			if((mac1 == mymac1) &&  (mac2 == mymac2))
			{
				//found it
				port_map = (value & 0x0007f000) >> 12 ;
				return 		port_map;
			}
			
			if (value & 0x2) {
				log("*** RT3052: end of table aa. %d", i);
				break;
			}
			reg_write(REG_ESW_TABLE_SEARCH, 0x2); //search for next address
			i++;
		}else if (value & 0x2) { //at_table_end
			//log("*** RT3052: found the last entry (not ready)aa. %d\n", i);
			return 0;
		}
		else
			msleep(2);
	}
	return 0;
}

static void set_port_map(unsigned char *mymac, unsigned int port_map)
{
	unsigned int value=0;
	unsigned int mymac1=0;
	unsigned short mymac2=0;
	
	memcpy(&mymac1, mymac , 4);
	mymac1 = ntohl(mymac1);
	memcpy(&mymac2, mymac+4 , 2);
	mymac2 = ntohs(mymac2);

	reg_write(REG_ESW_WT_MAC_AD2, mymac1);
	reg_write(REG_ESW_WT_MAC_AD1, mymac2);
	
	if(port_map)
	{
		/*
		 * force all mulicast addresses to bind with CPU.
		 */
		value = value | (0x1 << 18);
		/*
		 * fill the port map
		 */
		value = value | (port_map) << 12;
		value += (7 << 4);	//w_age_field
		value += 1;			//w_mac_cmd
		reg_write(REG_ESW_WT_MAC_AD0, value);
		wait_switch_done();
		
		/*
		 * delete the entry
		 */
		value |= 1;				//w_mac_cmd
		reg_write(REG_ESW_WT_MAC_AD0, value);
		wait_switch_done();
		
	}
	log(" set mac 0x%08x%04x port list 0x%08x\n", mymac1,mymac2, port_map);
}

static inline wait_switch_done(void)
{
	int i, value;
	for (i = 0; i < 20; i++) {
		reg_read(REG_ESW_WT_MAC_AD0, &value);
		if (value & 0x2) {	//w_mac_done
			//printf("done.\n");
			break;
		}
		msleep(1);
	}
	if (i == 20)
		log("*** RT3052: timeout.");
}

int portLookUpByMac(char *mac)
{
	unsigned int value = get_port_map(mac);
	//mapping
	switch(value){
		case 0x1:
			return 0;
		case 0x2:
			return 1;
		case 0x4:
			return 2;
		case 0x8:
			return 3;
		case 0x10:
			return 4;
		case 0x40:      /* CPU Only */
			return -1;
			break;
		default:
			log(" portLookUpByMac error, 0x%08x\n", value);
			return -1;
	}
}

void rt3052_fini(void)
{
	/*
	 *  handle RT3052 registers
	 */
	/* 1011009c */
	unsigned int value;

	HOOK_CHECK;
	//value = rareg(SWITCH_READ, 0x1011009c, 0);
	//value = value & 0xF7FFFFFF;
	//rareg(WRITE, 0x1011009c, value);
	/* 10110014 */
	value = rareg(SWITCH_READ, 0x10110014, 0);
	value = value & 0xFF7FFFFF;
	rareg(WRITE, 0x10110014, value);

	/* del 224.0.0.1( 01:00:5e:00:00:01) from mac table */
	//destory_all_hosts_rule();

	/*	delete all mac tables */
	//remove_all_groups();

}

void rt3052_init(void)
{
	/*
	 *  handle RT3052 registers
	 */
	unsigned int value, value2, value3;
	value = rareg(SWITCH_READ, 0x10000000, 0);
	value2= rareg(SWITCH_READ, 0x10000004, 0);
	if((value & 0x30335452) && (value2 & 0x00003235)){
		value3= (rareg(SWITCH_READ, 0x1000000C, 0) & 0x000003FF);
		if( (value == 0x33335452)  && (value2 & 0x000003FF) ){
			hook_value = value2 & 0x000003FF;
		}else
			hook_value = ((value3 & 0x0000FFFF) < 0x103) ? 0: value3 & 0x000000FF;

		if(hook_value > 0x107)
			hook_value |= 0x2000;
	}else{
		hook_value = value2 && 0x0000FFFF;
	}
	rareg(WRITE, 0x1000000c, hook_value);
	HOOK_CHECK;

	/* 1011009c */
	value = rareg(SWITCH_READ, 0x1011009c, 0);
	//value = value | 0x08000000;
	value = value & 0xE7FFFFFF;
	rareg(WRITE, 0x1011009c, value);

	/* 10110014 */
	value = rareg(SWITCH_READ, 0x10110014, 0);
	value = value | 0x00800000;
	rareg(WRITE, 0x10110014, value);

	/* add 224.0.0.1( 01:00:5e:00:00:01) to mac table */
	//create_all_hosts_rule();
}

static void add_member(unsigned char *g_mac, unsigned char *c_mac);
static void del_member(unsigned char *g_mac, unsigned char *c_mac);
static void clear_group(unsigned char *g_mac);
static int snoop_init(void);
static int snoop_deinit(void);

extern void register_snoop_init_callback (void * funa,void * funb);
extern void unregister_snoop_init_callback(void);
extern void register_igmp_callbacks(void * fun1, void * fun2, void * fun3);
extern void unregister_igmp_callbacks(void);

int __init sw_init(void)
{
	register_snoop_init_callback(snoop_init,snoop_deinit);
	return 0;
}

void __exit sw_deinit(void)
{
	rt3052_fini();
	unregister_igmp_callbacks();
	unregister_snoop_init_callback();
}

int snoop_deinit()
{
	rt3052_fini();
	unregister_igmp_callbacks();
	return 0;
}

int snoop_init()
{
	rt3052_init();	
	register_igmp_callbacks(add_member,del_member,clear_group);
	return 0;
}

//=== igmp ====
LIST_HEAD(grp_list) ;

struct grp_mac {
	struct list_head list;
	struct list_head member_list;
	unsigned char grpmac[6];
};

struct client_mac {
	struct list_head list;
	unsigned char member_mac[6];
};

void show_gentry()
{
	struct grp_mac *pgentry;
	struct client_mac *pcentry;
	printk("Show All entries \n");
	list_for_each_entry(pgentry, &grp_list, list)
	{
		printk(" mac 0x%02x%02x%02x%02x%02x%02x\n",pgentry->grpmac[0],
														pgentry->grpmac[1],
														pgentry->grpmac[2],
														pgentry->grpmac[3],
														pgentry->grpmac[4],
														pgentry->grpmac[5]);
		
		list_for_each_entry(pcentry, &pgentry->member_list, list)
		{
			printk("   cmac 0x%02x%02x%02x%02x%02x%02x\n",pcentry->member_mac[0],
														pcentry->member_mac[1],
														pcentry->member_mac[2],
														pcentry->member_mac[3],
														pcentry->member_mac[4],
														pcentry->member_mac[5]);
		}
	}
}

/*
1. find old group if exist
2. find old client mac if exist
3. snooping : update the group port list 
*/
static void add_member(unsigned char *g_mac, unsigned char *c_mac)
{
	struct grp_mac *pgentry;
	struct client_mac *pcentry;
	int found=0, cport=-1,gport_list=-1;
	spinlock_t igmp_lock;
	unsigned long flags;
	
	if(g_mac==NULL || c_mac==NULL)
		return;
	
	spin_lock_irqsave(&igmp_lock,flags);
	//1. find old group if exist
	list_for_each_entry(pgentry, &grp_list, list)
	{
		if(!memcmp(pgentry->grpmac,g_mac, 6))
		{
			found = 1;
			break;
		}
	}
	if(!found)	//create new group
	{
		pgentry = (struct grp_mac *)kmalloc(sizeof(struct grp_mac), GFP_ATOMIC);
		INIT_LIST_HEAD(&pgentry->list);
		INIT_LIST_HEAD(&pgentry->member_list);
		list_add(&pgentry->list, &grp_list);
		memcpy(pgentry->grpmac , g_mac , 6);
		log("sw : Create new group aa 0x%02x%02x%02x%02x%02x%02x\n", 
			g_mac[0],g_mac[1],g_mac[2],g_mac[3],g_mac[4],g_mac[5]);
			
	}
	//2. find old client mac if exist
	found = 0;
	list_for_each_entry(pcentry, &pgentry->member_list, list)
	{
		if(!memcmp(pcentry->member_mac,c_mac, 6))
		{	/* member already exist, do nothing ~*/
			found = 1;
			break;
		}
	}

	if(!found)
	{	/* member NOT exist, create NEW ONE and attached it to this group-mac linked list ~*/
		pcentry	= (struct client_mac *)kmalloc(sizeof(struct client_mac), GFP_ATOMIC);
		INIT_LIST_HEAD(&pcentry->list);
		list_add(&pcentry->list, &pgentry->member_list);
		memcpy( pcentry->member_mac ,c_mac , 6);
		log("sw : Added client mac 0x%02x%02x%02x%02x%02x%02x to group 0x%02x%02x%02x%02x%02x%02x\n", 
			pcentry->member_mac[0],pcentry->member_mac[1],pcentry->member_mac[2],pcentry->member_mac[3],pcentry->member_mac[4],pcentry->member_mac[5],
			pgentry->grpmac[0],pgentry->grpmac[1],pgentry->grpmac[2],pgentry->grpmac[3],pgentry->grpmac[4],pgentry->grpmac[5]
		);
	}

	spin_unlock_irqrestore (&igmp_lock, flags);	
	
	/*
		TODO : what if user unplugged cable without sending leave packet ? The port will be flooded with multicast
		packets. So, in add_member, we should check ALL client_macs and update ALL PORTS !! 
	*/
	gport_list = 0x00;
	list_for_each_entry(pcentry, &pgentry->member_list, list)
	{
		cport=portLookUpByMac(pcentry->member_mac);
		if(cport==-1)
		{
			log("Can't find mac in which port. \n");
			continue;
		}
		gport_list = gport_list | (0x1 << cport);
	}
	set_port_map( g_mac, gport_list );
}

/*
1. find old group 
2. find old client mac 
3. if group is empty, delete group
4. snooping : update the group port list 
*/
static void del_member(unsigned char *g_mac, unsigned char *c_mac)
{
	struct grp_mac *pgentry;
	struct client_mac *pcentry;
	int found = 0, gport_list=-1, cport=-1;

	//0. sanity check
	if(g_mac==NULL || c_mac==NULL)
		return;

	//1. find old group 
	list_for_each_entry(pgentry, &grp_list, list)
	{
		if(!memcmp(pgentry->grpmac,g_mac, 6))
		{
			found = 1;
			break;
		}
	}

	if(!found)
	{
		log("sw : Can't delete 0x%02x%02x%02x%02x%02x%02x, group NOT FOUND.\n", 
			g_mac[0],g_mac[1],g_mac[2],g_mac[3],g_mac[4],g_mac[5] );
		return;
	}

	//2. find old client mac 
	found = 0;
	list_for_each_entry(pcentry, &pgentry->member_list, list)
	{
		if(!memcmp(pcentry->member_mac,c_mac, 6))
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
		log("sw : Delete client 0x%02x%02x%02x%02x%02x%02x in group 0x%02x%02x%02x%02x%02x%02x\n", 
			c_mac[0],c_mac[1],c_mac[2],c_mac[3],c_mac[4],c_mac[5],
			g_mac[0],g_mac[1],g_mac[2],g_mac[3],g_mac[4],g_mac[5] );	
	}else
	{	/* do nothing, just debug */
		log("sw : Can't delete client 0x%02x%02x%02x%02x%02x%02x, client NOT FOUND in group 0x%02x%02x%02x%02x%02x%02x\n", 
			c_mac[0],c_mac[1],c_mac[2],c_mac[3],c_mac[4],c_mac[5],
			g_mac[0],g_mac[1],g_mac[2],g_mac[3],g_mac[4],g_mac[5] );
	}

	//3. if group is empty, delete group
	if(list_empty(&pgentry->member_list))
	{
		list_del(&pgentry->member_list);
		list_del(&pgentry->list);
		kfree(pgentry);
		//remove group mac from port_list
		set_port_map( g_mac, REMOVE_GROUP_MAC);
		log("sw : Delete group 0x%02x%02x%02x%02x%02x%02x since its empty \n", 
			g_mac[0],g_mac[1],g_mac[2],g_mac[3],g_mac[4],g_mac[5] );	
		return;
	}

	//4. snooping : update the group port list
	cport=portLookUpByMac(c_mac);
	if(cport==-1)
	{
		log("Can't find mac in which port. \n");
		return;
	}
	//get current group map port map
	gport_list = get_port_map(g_mac);
	//update portmap to group mac 
	gport_list = gport_list & ~(0x1 << cport);
	set_port_map( g_mac, gport_list);

}

static void clear_group(unsigned char *g_mac)
{
	struct grp_mac *pgentry;
	struct list_head *pos, *q, *tlist;
	int found = 0;	
	//0. sanity check
	if(g_mac==NULL)
		return;
	
	//1. find the group
	list_for_each_entry(pgentry, &grp_list, list)
	{
		if(!memcmp(pgentry->grpmac,g_mac, 6))
		{
			found = 1;
			break;
		}
	}
	if(!found)
	{
		log("sw : Can't clear group 0x%02x%02x%02x%02x%02x%02x, NOT FOUND.\n", 
			g_mac[0],g_mac[1],g_mac[2],g_mac[3],g_mac[4],g_mac[5] );
		return;
	}
	
	//2. delete all the group members.
	list_for_each_safe(pos, q, &pgentry->member_list)
	{
		tlist= list_entry(pos, struct client_mac, list);
		list_del(pos);
		kfree(tlist);
	}
	//3. delete the group
	list_del(&pgentry->member_list);
	list_del(&pgentry->list);
	kfree(pgentry);
	
	//4. remove group mac from port_list
	set_port_map( g_mac, REMOVE_GROUP_MAC);
}

module_init(sw_init)
module_exit(sw_deinit)
