<? /* vi: set sw=4 ts=4: */
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/phyinf.php";
include "/htdocs/phplib/inf.php";

function startcmd($cmd)	{fwrite(a,$_GLOBALS["START"], $cmd."\n");}
function stopcmd($cmd)	{fwrite(a,$_GLOBALS["STOP"], $cmd."\n");}
function error($err)	{startcmd("exit ".$err); stopcmd("exit ".$err); return $err;}

/***********************************************************************/

function get_dns($p)
{
	anchor($p);
	$cnt = query("dns/count")+0;
	foreach ("dns/entry")
	{
		if ($InDeX > $cnt) break;
		if ($dns=="") $dns = $VaLuE;
		else $dns = $dns." ".$VaLuE;
	}
	return $dns;
}

/***********************************************************************/

function inet_ipv6_ll($inf, $phyinf)
{
	startcmd("# inet_ipv6_ll(".$inf.",".$phyinf.")");

	/* Get the Link Local IP. */
	$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $phyinf, 0);
	if ($p=="") return error("9");

	/* Get device name */
	$devnam = query($p."/name");
	//fwrite(w, "/proc/sys/net/ipv6/conf/".$devnam."/disable_ipv6", 0);

	/* Get the link local address. */
	$ipaddr = query($p."/ipv6/link/ipaddr");
	$prefix = query($p."/ipv6/link/prefix");
	if ($ipaddr=="" || $prefix=="") return error("9");

	/* Start script */
	startcmd("phpsh /etc/scripts/IPV6.INET.php ACTION=ATTACH".
		" MODE=LL".
		" INF=".$inf.
		" DEVNAM=".$devnam.
		" IPADDR=".$ipaddr.
		" PREFIX=".$prefix
		);

	/* Stop script */
	stopcmd("phpsh /etc/scripts/IPV6.INET.php ACTION=DETACH INF=".$inf);
}

/***********************************************************************/

function inet_ipv6_static($inf, $devnam, $inetp)
{
	startcmd("# inet_start_ipv6_static(".$inf.",".$devnam.",".$inetp.")");

	//fwrite(w, "/proc/sys/net/ipv6/conf/".$devnam."/disable_ipv6", 0);
	anchor($inetp."/ipv6");

	/* Start scripts */
	startcmd("phpsh /etc/scripts/IPV6.INET.php ACTION=ATTACH".
		" MODE=STATIC INF=".$inf.
		" DEVNAM=".		$devnam.
		" IPADDR=".		query("ipaddr").
		" PREFIX=".		query("prefix").
		" GATEWAY=".	query("gateway").
		" ROUTERLFT=".	query("routerlft").
		" PREFERLFT=".	query("preferlft").
		" VALIDLFT=".	query("validlft").
		' "DNS='.get_dns($inetp."/ipv6").'"'
		);

	/* Stop script */
	stopcmd("phpsh /etc/scripts/IPV6.INET.php ACTION=DETACH INF=".$inf);
}

/************************************************************/

function inet_ipv6_auto($inf, $infp, $ifname, $phyinf, $stsp, $inetp) 
{
	startcmd('# inet_start_ipv6_auto('.$inf.','.$infp.','.$ifname.','.$phyinf.','.$stsp.','.$inetp.')');

	/* Record the device name */
	set($stsp."/devnam", $ifname);

	/* Record the child uid. */
	if (query("/runtime/device/layout")=="router")
		set($stsp."/child/uid", query($infp."/child"));

	/* Generate wait script. */
	$rawait = "/var/servd/INET.".$inf."-rawait.sh";
	fwrite(w, $rawait,
		"#!/bin/sh\n".
		"phpsh /etc/scripts/RA-WAIT.php".
			" INF=".$inf.
			" PHYINF=".$phyinf.
			" DEVNAM=".$ifname.
			" DHCPOPT=".query($inetp."/ipv6/dhcpopt").
			' "DNS='.get_dns($inetp."/ipv6").'"'.
			" ME=".$rawait.
			"\n");

	/* Start script ... */
	startcmd("chmod +x ".$rawait);
	startcmd('xmldbc -t "ra.iptest.'.$inf.':0:'.$rawait.'"');

	/* Stop script ... */
	stopcmd('rm -f '.$rawait);
	stopcmd('xmldbc -k ra.iptest.'.$inf);
	stopcmd("/etc/scripts/killpid.sh /var/servd/".$inf."-dhcp6c.pid");
	$conf = "/var/run/".$ifname;
	stopcmd("rm -f ".$conf.".ra_mflag");
	stopcmd("rm -f ".$conf.".ra_oflag");
	stopcmd("rm -f ".$conf.".ra_prefix");
	stopcmd("rm -f ".$conf.".ra_prefix_len");
	stopcmd("rm -f ".$conf.".ra_saddr");
	stopcmd("rm -f ".$conf.".ra_rdnss");
	stopcmd("killall rdisc6");
	stopcmd('phpsh /etc/scripts/IPV6.INET.php ACTION=DETACH INF='.$inf);
}

/* IPv6 *********************************************************/
fwrite(a,$START, "# INFNAME = [".$INET_INFNAME."]\n");
fwrite(a,$STOP,  "# INFNAME = [".$INET_INFNAME."]\n");

/* These parameter should be valid. */
$inf    = $INET_INFNAME;
$infp   = XNODE_getpathbytarget("", "inf", "uid", $inf, 0);
$phyinf = query($infp."/phyinf");
$default= query($infp."/defaultroute");
$inet   = query($infp."/inet");
$inetp  = XNODE_getpathbytarget("/inet", "entry", "uid", $inet, 0);
$ifname = PHYINF_getifname($phyinf);

/* Create the runtime inf. Set phyinf. */
$stsp = XNODE_getpathbytarget("/runtime", "inf", "uid", $inf, 1);
set($stsp."/phyinf", $phyinf);
set($stsp."/defaultroute", $default);

$mode = query($inetp."/ipv6/mode");
if ($mode=="STATIC")	inet_ipv6_static($inf, $ifname, $inetp);
else if	($mode=="LL")	inet_ipv6_ll($inf, $phyinf);
else if	($mode=="AUTO")	inet_ipv6_auto($inf, $infp, $ifname, $phyinf, $stsp, $inetp);
?>
