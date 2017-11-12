<?
/* fatlady is used to validate the configuration for the specific service.
 * FATLADY_prefix was defined to the path of Session Data.
 * 3 variables should be returned for the result:
 * FATLADY_result, FATLADY_node & FATLADY_message. */
$sw_old=query($FATLADY_prefix."/runtime/device/switchmode");
$sw_new=query("/runtime/device/switchmode");
if ($sw_old!="APCLI")	$sw_old="AP";
if ($sw_new!="APCLI")	$sw_new="AP";
if ($sw_old==$sw_new)
{
	set($FATLADY_prefix."/valid", "1");
	$_GLOBALS["FATLADY_result"]  = "OK";
	$_GLOBALS["FATLADY_node"]    = "";
	$_GLOBALS["FATLADY_message"] = "";
}
else if ($sw_new=="AP")
{
	$_GLOBALS["FATLADY_result"]  = "FAILED";
	$_GLOBALS["FATLADY_node"]    = "/runtime/device/switchmode";
	$_GLOBALS["FATLADY_message"] = i18n("The switch mode is changed to AP mode. Please reload the web page.");
}
else if ($sw_new=="APCLI")
{
	$_GLOBALS["FATLADY_result"]  = "FAILED";
	$_GLOBALS["FATLADY_node"]    = "/runtime/device/switchmode";
	$_GLOBALS["FATLADY_message"] = i18n("The switch mode is changed to bridge mode. Please reload the web page.");
}
?>
