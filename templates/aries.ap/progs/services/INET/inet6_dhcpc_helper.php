#!/bin/sh
<? /* vi: set sw=4 ts=4: */
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/phyinf.php";

function cmd($cmd) {echo $cmd."\n";}
function msg($msg) {cmd("echo [DHCP6C]: ".$msg." > /dev/console");}

/*****************************************/
function add_each($list, $path, $node)
{
	//echo "# add_each(".$list.",".$path.",".$node.")\n";
	$i = 0;
	$cnt = scut_count($list, "");
	while ($i < $cnt)
	{
		$val = scut($list, $i, "");
		if ($val!="") add($path."/".$node, $val);
		$i++;
	}
	return $cnt;
}
/*****************************************/

function handle_stateful($inf, $devnam, $opt)
{
	$stsp = XNODE_getpathbytarget("/runtime",  "inf", "uid", $inf, 0);
	if ($stsp=="" || $devnam=="") return;

	/* Preparing ... */
	$conf	= "/var/run/".$devnam;

	/* Strip the tailing spaces. */
	$DNS			= strip($_GLOBALS["DNS"]);
	$NAMESERVERS	= strip($_GLOBALS["NAMESERVERS"]);
	$NEW_ADDR		= strip($_GLOBALS["NEW_ADDR"]);

	/* Get RA info */
	$conf = "/var/run/".$devnam;
	$pfxlen = fread("e", $conf.".ra_prefix_len");
	$router = fread("e", $conf.".ra_saddr");
	if($router=="" || $router=="NULL") {msg("no ra_saddr"); return;}
	if($pfxlen=="") $pfxlen = 64;

	/* Combine the user config and DHCP server setting. */
	$dns = $DNS;
	if ($NAMESERVERS!="")
	{
		if ($dns=="")	$dns = $NAMESERVERS;
		else			$dns = $dns." ".$NAMESERVERS;
	}

	/* Check renew, do nothing if we got the same prefix and prefix len as previous ones */
	$ip = query($stsp."/inet/ipv6/ipaddr");
	$pfl = query($stsp."/inet/ipv6/prefix");	

	if($opt=="IA-NA") 
	{
		if ($NEW_ADDR!="" && $NEW_PD_PREFIX=="" && $NEW_PD_PLEN== "") { $renew=1;msg("STATEFUL - opt: ".$opt." RENEW IANA...");}
	}

	if (strstr($opt,"IA-NA")=="") {msg("no IA-NA"); return;}

	if($renew==1 && $ip==$NEW_ADDR){msg("STATEFUL - opt: ".$opt." Renew but do nothing"); return;}

	if ($NEW_ADDR=="") {msg("no NEW_ADDR"); return;}
	$ipaddr = $NEW_ADDR;
	//$pfxlen = 128;

	set($stsp."/inet/ipv6/dhcpopt", $opt);
	cmd("phpsh /etc/scripts/IPV6.INET.php ACTION=ATTACH".
		" INF=".$_GLOBALS["INF"].
		" MODE=".$_GLOBALS["MODE"].
		" DEVNAM=".$devnam.
		" IPADDR=".$ipaddr.
		" PREFIX=".$pfxlen.
		" GATEWAY=".$router.
		' "DNS='.$dns.'"');

	return;
}

function handle_infoonly($inf)
{
	$stsp = XNODE_getpathbytarget("/runtime", "inf", "uid", $inf, 0);
	if ($stsp=="") {msg("INFOONLY - no runtime for ".$inf); return;}

	$DNS			= strip($_GLOBALS["DNS"]);
	$NAMESERVERS	= strip($_GLOBALS["NAMESERVERS"]);
	/* Combine the user config and DHCP server setting. */
	$dns = $DNS;
	if ($NAMESERVERS!="")
	{
		if ($dns=="")	$dns = $NAMESERVERS;
		else			$dns = $dns." ".$NAMESERVERS;
	}
	msg("INFOONLY - DNS: ".$dns);

	add_each($dns, $stsp."/inet/ipv6", "dns");
}

/**************************************************************/
/* dhcpv6c has tailing space character in the arguments to the callback script.
 * strip the extra space characters with strip(). */
if ($_GLOBALS["MODE"]=="STATEFUL")
	handle_stateful($_GLOBALS["INF"],$_GLOBALS["DEVNAM"], $_GLOBALS["DHCPOPT"]);
else if ($_GLOBALS["MODE"]=="INFOONLY")
	handle_infoonly($_GLOBALS["INF"]);
else msg("Unknown mode - ".$_GLOBALS["MODE"]);
?>
