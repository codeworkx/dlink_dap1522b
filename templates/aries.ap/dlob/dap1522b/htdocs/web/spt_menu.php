HTTP/1.1 200 OK

<?
if(query("/runtime/device/switchmode") != "APCLI")
{
	$TEMP_MYNAME    = "spt_menu";
}
else
{
	$TEMP_MYNAME    = "spt_menu_br";
}
$TEMP_MYGROUP   = "help";
$TEMP_STYLE	= "help";
include "/htdocs/webinc/templates.php";
?>
