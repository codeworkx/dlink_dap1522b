<?
/* setcfg is used to move the validated session data to the configuration database.
 * The variable, 'SETCFG_prefix',  will indicate the path of the session data. */
include "/htdocs/phplib/xnode.php";
function macclone_setcfg($prefix, $uid)
{
	$macclone_path = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	movc($prefix."/phyinf/macclone", $macclone_path."/macclone");	
}
?>
