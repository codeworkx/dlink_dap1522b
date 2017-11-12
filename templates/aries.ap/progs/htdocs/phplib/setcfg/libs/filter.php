<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";

function set_result($result, $node, $message)
{
	$_GLOBALS["FATLADY_result"] = $result;
	$_GLOBALS["FATLADY_node"]   = $node;
	$_GLOBALS["FATLADY_message"]= $message;
	return $result;
}

function filter_setcfg($prefix, $inf)
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
	$dst_phyinfp = XNODE_getpathbytarget("", "phyinf", "uid", $filter_uid, 0);
	set($dst_phyinfp."/filter",		filter_uid);	
	/* move the filter profile. */
	if ($filterp != "")
	{		
		$dst = XNODE_getpathbytarget("", "filter", "uid", $filter_uid, 0);		
		$src = XNODE_getpathbytarget($prefix, "filter", "uid", $filter_uid, 0);
		if ($src!="" || $dst!="")
		{
			 movc($src, $dst);
		}		
		//set($to."/entry:".$bwc_index."/enable",		query("enable"));
	}
}

?>
