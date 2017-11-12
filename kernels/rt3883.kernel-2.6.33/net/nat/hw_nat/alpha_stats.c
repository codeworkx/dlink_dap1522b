#include <linux/proc_fs.h> 
#include <linux/module.h>	/* Needed by all modules */
#include <linux/timer.h>

#include "ac_policy.h"
#include "ac_ioctl.h"
#include "acl_ioctl.h"
#include "acl_policy.h"
#include "alpha_stats.h"

extern unsigned int AcBndryCheck(AcPlcyNode *NewNode);

extern unsigned long long g_vid1_tx_bytes ;
extern unsigned long long g_vid2_tx_bytes ;
extern unsigned long long g_vid1_tx_count ;
extern unsigned long long g_vid2_tx_count ;

unsigned int get(int type)
{
	unsigned int offset=0;
	switch(type)
	{
		case WAN_TX_BYTE:
			offset = 0x00;
			break;
		case WAN_TX_COUNT:
			offset = 0x04;
			break;
		case LAN_TX_BYTE:
			offset = 0x08;
			break;
		case LAN_TX_COUNT:
			offset = 0x0C;
			break;
		default : 
			break;
	}
	return sysRegRead( HNAT_BASEADDR + offset );
}

static struct timer_list my_timer;

static void update_stats(unsigned long data)
{
	//update to 
	g_vid1_tx_bytes += get(WAN_TX_BYTE);
	g_vid2_tx_bytes += get(LAN_TX_BYTE);
	g_vid1_tx_count += get(WAN_TX_COUNT);
	g_vid2_tx_count += get(LAN_TX_COUNT);
	
	mod_timer(&my_timer,jiffies + 3*HZ);
}

unsigned int set_vid_rule(int vid)
{
	AcPlcyNode node;
	unsigned int result=0;
	memset(&node, 0x00, sizeof(AcPlcyNode));
    node.VLAN=vid;
    node.Type=PRE_AC;
    node.RuleType=AC_VLAN_GROUP;
    
	result=AcBndryCheck(&node);
	if(result!=AC_TBL_FULL)
		result=AcAddNode(&node);
	return result;	
}

extern char g_wanif[1024] __initdata;
extern char g_lanif[1024] __initdata;

enum TYPE
{
    WAN,       
    LAN
};

enum VALUES_TYPE
{
    TX_BYTES,       
    TX_COUNT,
    RX_BYTES,
    RX_COUNT
};


/*
before reading this function and get headache, keep in mind that : 
1. we only can get TX (bytes and packets) from cpu 
2. means if we want RX of wan, we can add it from TX of lan, vice versa
3. argument 'devname' is transfered from net/core/dev.c 
4. we record our wan and lan interface from 'insmod hw_nat.ko wanif="eth2.2" lanif="br0" 
5. why not use vlan id ? 
	vlan id = 1 is set as eth2.1 (which is our actual lan)
	vlan id = 2 is set as eth2.2 (which is our actual wan)
   But our device treat br0 as lan if. And when test win7 logo, we return lan statistics from br0 interface. 
   So.. we record br0 and eth2.2
*/

unsigned long long get_hwnat_ctr(char *devname, int type )
{
	int v=-1;
	unsigned long val=0;
	
	if(!strcmp(devname, g_wanif))
		v=WAN;
	else if(!strcmp(devname, g_lanif))
		v=LAN;
	else
		return 0;
	
	switch(type)
	{
		case TX_BYTES: 
			if(v==WAN)
				return (g_vid1_tx_bytes += get(WAN_TX_BYTE));
			else
				return (g_vid2_tx_bytes += get(LAN_TX_BYTE));
		case TX_COUNT: 
			if(v==WAN)
				return (g_vid1_tx_count += get(WAN_TX_COUNT));
			else 
				return (g_vid2_tx_count += get(LAN_TX_COUNT));
		case RX_BYTES: 
			if(v==WAN) 
				return (g_vid2_tx_bytes += get(LAN_TX_BYTE));
			else 
				return (g_vid1_tx_bytes += get(WAN_TX_BYTE));
		case RX_COUNT: 
			if(v==WAN) 
				return (g_vid2_tx_count += get(LAN_TX_COUNT));
			else
				return (g_vid1_tx_count += get(WAN_TX_COUNT));
		default : 
			return 0;
	}
}

static int proc_read_alpha_hnat (char *buffer, char **start, off_t offset,
								int len, int *eof, void *data)
{
	int ret;
	if (offset > 0) {
		/* we have finished to read, return 0 */
		ret  = 0;
	} else {
		update_stats(NULL);
		/* fill the buffer, return the buffer size */
		ret = sprintf(buffer, 		"lan_tx_packets: %10llu lan_tx_bytes: %25llu\n",g_vid2_tx_count, g_vid2_tx_bytes);
		ret += sprintf(buffer+ret, 	"wan_tx_packets: %10llu wan_tx_bytes: %25llu\n",g_vid1_tx_count, g_vid1_tx_bytes);
	}
	return ret;
}

static int proc_write_alpha_hnat (struct file *file, const char *buffer,
								unsigned long count, void *data)
{
	unsigned char buf[128];  
	memset(buf, 0, 128);  	
	if(count > 0 && count < 128)
	{  
		copy_from_user(buf, buffer, count);  
		if( !strncmp(buf,"flush", strlen("flush")))
		{
			g_vid1_tx_bytes = 0;
			g_vid2_tx_bytes = 0;
			g_vid1_tx_count = 0;
			g_vid2_tx_count = 0;
			printk("\nReset hnat bytes and counters...\n");
		}
	}
	return count;
}

int AlphaTxRxStatsInit(void)
{
	char alpha_proc_name[128];
	struct proc_dir_entry *alpha_hnat_proc;

    init_timer(&my_timer);
   my_timer.expires = jiffies + 3*HZ;
    my_timer.function = &update_stats;
	
	snprintf(alpha_proc_name, sizeof(alpha_proc_name), "alpha/hnat");
	alpha_hnat_proc = create_proc_entry (alpha_proc_name, 0644, NULL);
	if (alpha_hnat_proc == NULL)
	{
		printk("create proc %s FAILED \n", alpha_proc_name);
		return (-1);
	}
	alpha_hnat_proc->read_proc = proc_read_alpha_hnat;
	alpha_hnat_proc->write_proc = proc_write_alpha_hnat;
	
	set_vid_rule(1);	//set rule for upload vid==1 (means packet from Lan rx to Wan tx)
	set_vid_rule(2);	//set rule for upload vid==2 (means packet from Wan rx to Lan tx)
	
	add_timer(&my_timer);
	return 0;
}

void AlphaTxRxStatsDeinit(void)
{
	 del_timer(&my_timer);
	 remove_proc_entry("alpha/hnat",NULL);
}

