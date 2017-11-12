<?
/* setcfg is used to move the validated session data to the configuration database.
 * The variable, 'SETCFG_prefix',  will indicate the path of the session data. */
include "/htdocs/phplib/xnode.php";
$infp = XNODE_getpathbytarget("", "inf", "uid", "LAN-1", 0);
if ($infp!="")
{
	del($infp."/upnp");
	$cnt = query($SETCFG_prefix."/inf/upnp/count");
	set($infp."/upnp/count", $cnt);
	$i = 0;
	while ($i < $cnt)
	{
		$i++;
		set($infp."/upnp/entry:".$i, query($SETCFG_prefix."/inf/upnp/entry:".$i));
	}
}
?>
