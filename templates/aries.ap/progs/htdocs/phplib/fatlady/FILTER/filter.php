<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/inet.php";
include "/htdocs/phplib/inf.php";

function seterr($err, $node)
{
    $_GLOBALS["FATLADY_result"] = "FAILED";
    $_GLOBALS["FATLADY_node"]   = $node;
    $_GLOBALS["FATLADY_message"]= $err;
	return "FAILED";
}

function set_result($result, $node, $message)
{
	$_GLOBALS["FATLADY_result"]	= $result;
	$_GLOBALS["FATLADY_node"]	= $node;
	$_GLOBALS["FATLADY_message"]= $message;
}

function verify_ip_range($ipaddr1, $ipaddr2, $inf)
{
//TRACE_debug("H 111 ".$ipaddr1." ~ ".$ipaddr1);
	if ($ipaddr1=="" && $ipaddr2=="")	return "";
	if($ipaddr1!="")
	{
		if (INET_validv4addr($ipaddr1)!=1)
		{
				return i18n("Invalid format of the start IP address.");
			}
	}
	if ($ipaddr2!="")
	{
		if (INET_validv4addr($ipaddr2)!=1)	return i18n("Invalid format of the end IP address.");
		/* We only allow the range for 255 hosts. */
		$hostid1 = ipv4hostid($ipaddr1, 0);
		$hostid2 = ipv4hostid($ipaddr2, 0);
		$delta = $hostid2-$hostid1;
		if ($delta < 0)				return i18n("The end IP address should be greater than the start address.");
		if ($delta > 255)				return i18n("The IP address range is too large.");
	}
	$inf0 = cut($inf, 0, "-");
	if ($inf0 == "LAN")
	{
		$_INET  = INF_getinfinfo($inf, "inet");
		$lanip  = INET_getinetinfo($_INET, "ipv4/ipaddr");
		$lanmask   = INET_getinetinfo($_INET, "ipv4/mask");
		if($ipaddr1==$lanip)	return i18n("The start IP address can't be the same as device IP.");
		if(INET_validv4host($ipaddr1, $lanmask)==0)	return i18n("The start IP address is invalid.");
		if(INET_validv4network($ipaddr1, $lanip, $lanmask)==0) 
			return i18n("The start IP address and router address should be in the same network subnet.");
		if($ipaddr2!="")
		{
			if($ipaddr2==$lanip)	return i18n("The end IP address can't be the same as device IP.");
			if(INET_validv4host($ipaddr2, $lanmask)==0)	return i18n("The end IP address is invalid.");
			if(INET_validv4network($ipaddr2, $lanip, $lanmask)==0) 
				return i18n("The end IP address and router address should be in the same network subnet.");
		}
	}
	return "";
}

function fatlady_filter($prefix, $inf)
{
	/* Check FILTER */
	$filter_uid = query($prefix."/phyinf/filter");
	$filterp = XNODE_getpathbytarget($prefix, "filter", "uid", $filter_uid, 0);
	//if ($filterp == "")
	//{
		/* internet error, no i18n(). */
	//	set_result("FAILED", $prefix."/inf/phyinf", "Invalid filter");
	//	return;
	//}	
	
	$ipv4_type = query($filterp."/ipv4/type");
	if ($ipv4_type == "ALLOW" || $ipv4_type == "DENY" || $ipv4_type == "DISABLED")
	{		
		$ipv4_cnt = query($filterp."/ipv4/count");
		$ipv4_max = query($filterp."/ipv4/max");
		if ($ipv4_cnt>=$ipv4_max) 
		{
			set_result("FAILED", $prefix."/inf/phyinf", "Max entries are ".$ipv4_max);
			return;
		}
		/* Walk through each entry */
		foreach ($filterp."/ipv4/entry")
		{
			$start = query("start");
			$end = query("end");
			
			/* IP range */
			$err = verify_ip_range($start, $end, $phy);
			if ($err!="")
			{
				$err = i18n("Incorrect IP address.")." ".$err;
				seterr($err, $filterp."/entry:".$InDeX."/start");
				set($prefix."/valid", 0);				
				set_result("FAILED",$filterp."/entry:".$InDeX, $err);
				return;
			}
		}
	}
	//else
	//{
	//	set_result("FAILED", $prefix."/inf/phyinf", "Invalid type");
	//	return;
	//}		
	/* We only validate the 'macaddr' & 'media' here,
	 * so be sure to save 'macaddr' & 'media' only at 'setcfg' */
	set($prefix."/valid", 1);
	set_result("OK", "", "");
}

?>
