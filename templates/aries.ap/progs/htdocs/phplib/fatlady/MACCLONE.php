<? /* vi: set sw=4 ts=4: */
/* fatlady is used to validate the configuration for the specific service.
 * FATLADY_prefix was defined to the path of Session Data.
 * 3 variables should be breaked for the result:
 * FATLADY_result, FATLADY_node & FATLADY_message. */
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/phyinf.php";

function set_result($result, $node, $message)
{
	$_GLOBALS["FATLADY_result"] = $result;
	$_GLOBALS["FATLADY_node"]   = $node;
	$_GLOBALS["FATLADY_message"]= $message;
	return $result;
}

/* verify MAC address */
$ret = "OK";
$mac = query($FATLADY_prefix."/phyinf/macclone/macaddr");
if ($mac != "" && PHYINF_validmacaddr($mac) != 1) $ret = set_result("FAILED", $FATLADY_prefix."/phyinf/macclone/mac", i18n("Invalid MAC address value."));

TRACE_debug("FATLADY: MACCLONE: ret = ".$ret);
if ($ret=="OK") set($FATLADY_prefix."/valid", "1");
?>
