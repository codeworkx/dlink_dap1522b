#!/bin/sh
<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/phyinf.php";

function cmd($cmd)	{echo $cmd."\n";}
function msg($msg)	{cmd("echo [DHCP6C]: ".$msg." > /dev/console");}
function error($m)	{cmd("echo [RA-WAIT]: ERROR: ".$m); return 9;}

/***********************************************************************/
function dhcp_client($mode, $inf, $devnam, $opt, $router, $dns)
{
	$hlp = "/var/servd/".$inf."-dhcp6c.sh";
	$pid = "/var/servd/".$inf."-dhcp6c.pid";
	$cfg = "/var/servd/".$inf."-dhcp6c.cfg";

	msg("dhcpopt: ".$opt);

	/* Gererate DHCP-IAID from 32-bit of mac address*/
	$mac = PHYINF_getphymac($inf);
	$mac1 = cut($mac, 3, ":"); $mac2 = cut($mac, 0, ":"); $mac3 = cut($mac, 1, ":"); $mac4 = cut($mac, 2, ":");
	$iaidstr = $mac1.$mac2.$mac3.$mac4;
	$iaid = strtoul($iaidstr, 16);	
		
	/* Generate configuration file. */
	if ($mode=="INFOONLY")
	{
		$send="\tinformation-only;\n";
		$idas="";
	}
	else
	{
		//if (strstr($opt,"IA-NA")!="") {$send=$send."\tsend ia-na 0;\n"; $idas=$idas."id-assoc na {\n};\n";}
		if (strstr($opt,"IA-NA")!="") {$send=$send."\tsend ia-na ".$iaid.";\n"; $idas=$idas."id-assoc na ".$iaid."{\n};\n";}
		if (strstr($opt,"IA-PD")!="") {$send=$send."\tsend ia-pd 0;\n"; $idas=$idas."id-assoc pd {\n};\n";}
	}

	fwrite(w, $cfg,
		"interface ".$devnam." {\n".
		$send.
		"\trequest domain-name-servers;\n".
		"\trequest domain-name;\n".
		"\tscript \"".$hlp."\";\n".
		"};\n".
		$idas);

	/* generate callback script */
	fwrite(w, $hlp,
		"#!/bin/sh\n".
		"echo [$0]: [$new_addr] [$new_pd_prefix] [$new_pd_plen] > /dev/console\n".
		"phpsh /etc/services/INET/inet6_dhcpc_helper.php".
			" INF=".$inf.
			" MODE=".$mode.
			" DEVNAM=".$devnam.
			" GATEWAY=".$router.
			" DHCPOPT=".$opt.
			' "NAMESERVERS=$new_domain_name_servers"'.
			' "NEW_ADDR=$new_addr"'.
			' "NEW_PD_PREFIX=$new_pd_prefix"'.
			' "NEW_PD_PLEN=$new_pd_plen"'.
			' "DNS='.$dns.'"'.
			"\n");

	/* Start DHCP client */
	$conf = "/var/run/".$devnam;
	$ra_pfxlen = fread("e", $conf.".ra_prefix_len");
	if($ra_pfxlen=="" || $ra_pfxlen=="NULL") $lenmsg = "";
	else $lenmsg = " -l ".$ra_pfxlen;
	cmd("chmod +x ".$hlp);
	cmd("dhcp6c -c ".$cfg." -p ".$pid.$lenmsg." -t LL ".$devnam);
	return 0;
}

/***********************************************************************/

function main_entry($inf, $phyinf, $devnam, $dhcpopt, $dns, $me)
{
	/* generate callback script */
	$hlp = "/var/servd/".$inf."-rdisc6.sh";
	fwrite(w, $hlp,
		"#!/bin/sh\n".
		"echo [$0]: [$IFNAME] [$MFLAG] [$OFLAG] > /dev/console\n".
		"phpsh /etc/services/INET/inet6_rdisc6_helper.php".
			' "IFNAME=$IFNAME"'.
			' "MFLAG=$MFLAG"'.
			' "OFLAG=$OFLAG"'.
			' "PREFIX=$PREFIX"'.
			' "PFXLEN=$PFXLEN"'.
			' "LLADDR=$LLADDR"'.
			' "RDNSS=$RDNSS"'.
			"\n");

	/* Start DHCP client */
	cmd("chmod +x ".$hlp);

	/* run rdisc */
	//cmd("killall rdisc6");
	//cmd("sleep 1");
	
	/* INF status path. */
	$stsp = XNODE_getpathbytarget("/runtime", "inf", "uid", $inf, 0);
	if ($stsp=="") return error($inf." has not runtime nodes !");

	cmd("rdisc6 -c ".$hlp." -q ".$devnam." &");

	cmd("sleep 5");

	/* Clear any old records. */
	del($stsp."/stateless");
	/* Preparing & Get M flag */
	$child	= query($stsp."/child/uid");
	$conf = "/var/run/".$devnam;
	$mflag	= fread("e", $conf.".ra_mflag");
	$oflag	= fread("e", $conf.".ra_oflag"); 
	$rdnss  = fread("e", $conf.".ra_rdnss");

	msg($inf."/".$devnam.", M=[".$mflag."], O=[".$oflag."]");

	if ($mflag=="1")
	{
		/* Stateful ... */
		if ($dhcpopt=="")
		{
			$dhcpopt = "IA-NA";
		}
		dhcp_client("STATEFUL", $inf, $devnam, $dhcpopt, $router, $dns);
		return 0;
	}
	else if ($mflag=="0")
	{
		/* Stateless */
		$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $phyinf, 0);
		if ($p=="") return error($phyinf." has not runtime nodes!");
		/* Get self-configured IP address. */
		/*$ipaddr = query($p."/ipv6/global/ipaddr");*/
		/*$prefix = query($p."/ipv6/global/prefix");*/
		
		$mac = PHYINF_getphymac($inf);
		$hostid = ipv6eui64($mac);
		$ra_prefix = fread("e", $conf.".ra_prefix");
		$prefix = fread("e", $conf.".ra_prefix_len");
		$ipaddr = ipv6ip($ra_prefix, $prefix, $hostid, 0, 0);
		$router	= fread("e", $conf.".ra_saddr");
			
		if ($ipaddr!="")
		{
			if ($oflag=="0" && $dns=="" && $rdnss!="") {$dns=$rdnss;}

			msg("Stateless Self-Config IP: ".$ipaddr."/".$prefix);
			cmd("phpsh /etc/scripts/IPV6.INET.php ACTION=ATTACH".
						" MODE=STATELESS".
						" INF=".$inf.
						" DEVNAM=".$devnam.
						" IPADDR=".$ipaddr.
						" PREFIX=".$prefix.
						" GATEWAY=".$router.
						' "DNS='.$dns.'"');
			if ($oflag=="1" && $dns=="")
			{
				msg("STATELESS DHCP: information only.");
				dhcp_client("INFOONLY", $inf, $devnam, "", "", "");
			}
			return 0;
		}
	}

	cmd("killall rdisc6");
	cmd("sleep 1");
	
	/* Not configured, try later. */
	cmd('xmldbc -t "ra.iptest.'.$inf.':5:'.$me.'"');

	return 0;
}

/* Main entry */
main_entry(
	$_GLOBALS["INF"],
	$_GLOBALS["PHYINF"],
	$_GLOBALS["DEVNAM"],
	$_GLOBALS["DHCPOPT"],
	$_GLOBALS["DNS"],
	$_GLOBALS["ME"]
	);
?>
