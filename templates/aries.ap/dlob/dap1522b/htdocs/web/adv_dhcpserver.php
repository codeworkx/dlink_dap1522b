HTTP/1.1 200 OK

<?
/* The variables are used in js and body both, so define them here.
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/inf.php";
$p = XNODE_getpathbytarget("/nat", "entry", "uid", "NAT-1", 0);
$VSVR_MAX_COUNT = query($p."/virtualserver/max");
if ($VSVR_MAX_COUNT == "") $VSVR_MAX_COUNT = 5;
 */
/* necessary and basic definition */
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/inf.php";
$p = XNODE_getpathbytarget("/dhcps4", "entry", "uid", "DHCPS4-1", 0);
$DHCP_MAX_COUNT = query($p."/staticleases/max");

$TEMP_MYNAME    = "adv_dhcpserver";
$TEMP_MYGROUP   = "advanced";
$TEMP_STYLE		= "complex";
include "/htdocs/webinc/templates.php";
?>
