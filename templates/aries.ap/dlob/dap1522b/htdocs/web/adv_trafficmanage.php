HTTP/1.1 200 OK

<?
/* The variables are used in js and body both, so define them here. */
include "/htdocs/phplib/inf.php";
include "/htdocs/phplib/phyinf.php";
include "/htdocs/phplib/xnode.php";
$i = 1;
while ($i<4)
{
	$WANSTR = "WAN-".$i;
	$WANDEV = PHYINF_getruntimeifname($WANSTR);
	if ( $WANDEV!="" )break;
	$i++;
}
$WANPTR= XNODE_getpathbytarget("", "inf", "uid", $WANSTR, 0);
$BWC_NAME = query($WANPTR."/bwc");
$BWCPTR= XNODE_getpathbytarget("/bwc", "entry", "uid", $BWC_NAME, 0);
$QOS_MAX_COUNT = query($BWCPTR."/rules/max");
if ($QOS_MAX_COUNT == "") $QOS_MAX_COUNT = 10;
/*necessary and basic definition */
$TEMP_MYNAME    = "adv_trafficmanage";
$TEMP_MYGROUP   = "advanced";
$TEMP_STYLE		= "complex";
include "/htdocs/webinc/templates.php";
?>
