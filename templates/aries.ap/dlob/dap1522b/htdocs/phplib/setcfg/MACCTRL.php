<?
/* setcfg is used to move the validated session data to the configuration database.
 * The variable, 'SETCFG_prefix',  will indicate the path of the session data. */
set("/acl/macctrl/policy", query($SETCFG_prefix."/acl/macctrl/policy"));
if(query($SETCFG_prefix."/acl/macctrl/policy") != "DISABLE")
{
	movc($SETCFG_prefix."/acl/macctrl", "/acl/macctrl");
}
?>
