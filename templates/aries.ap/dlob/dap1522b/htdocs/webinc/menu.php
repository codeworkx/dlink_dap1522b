<? /* vi: set sw=4 ts=4: */
/* The menu definitions */
$switchmode = query("/runtime/device/switchmode");

if ($switchmode == "APCLI")
{
	if ($TEMP_MYNAME=="wiz_wlan_ap" || $TEMP_MYNAME=="adv_mac_filter" || $TEMP_MYNAME== "adv_gzone" || $TEMP_MYNAME== "adv_dhcpserver" ||
	    $TEMP_MYNAME=="adv_qos" || $TEMP_MYNAME=="adv_trafficmanage"|| $TEMP_MYNAME== "adv_userlimit" || $TEMP_MYNAME== "tools_sch" ||
	    $TEMP_MYNAME=="adv_wps" || $TEMP_MYNAME=="tools_check" || $TEMP_MYNAME=="st_wlan" || $TEMP_MYNAME=="st_ipv6" || $TEMP_MYNAME=="wiz_wps")
	{
		$TEMP_MYNAME    = "st_device";
		$TEMP_MYGROUP   = "status";
		$TEMP_STYLE		= "complex";
	}
}
else
{
	if ($TEMP_MYNAME=="wiz_wlan_br" || $TEMP_MYNAME=="wiz_wps_br")
	{
		$TEMP_MYNAME    = "st_device";
		$TEMP_MYGROUP   = "status";
		$TEMP_STYLE		= "complex";
	}
}

if($TEMP_MYGROUP=="basic")
{
  if($switchmode!="APCLI")
  {
    $menu = i18n("SETUP WIZARD")."|".
			      i18n("WIRELESS SETUP")."|".
			      i18n("LAN SETUP");
			      
	  $link = "wiz_wlan_setup.php"."|".
			      "bsc_wlan.php"."|".
			      "bsc_lan.php";
  }
  else
  {
    $menu = i18n("SETUP WIZARD")."|".
			      i18n("WIRELESS SETUP")."|".
			      i18n("LAN SETUP");
			      
	  $link = "wiz_wlan_setup.php"."|".
			      "bsc_wlan.php"."|".
			      "bsc_lan.php";
  }
}
else if($TEMP_MYGROUP=="advanced")
{
  if($switchmode!="APCLI")
  {
    $menu = i18n("MAC ADDRESS FILTER")."|".
			      i18n("ADVANCED WIRELESS")."|".
			      i18n("GUEST ZONE")."|".
			      i18n("DHCP SERVER")."|".
			      i18n("QOS")."|".
			      i18n("TRAFFIC MANAGER")."|".
			      i18n("WI-FI PROTECTED SETUP")."|".
			      i18n("USER LIMIT");
			      
	   $link = "adv_mac_filter.php"."|".
	   				 "adv_network.php"."|".
			       "adv_gzone.php"."|".
			       "adv_dhcpserver.php"."|".
			       "adv_qos.php"."|".
			       "adv_trafficmanage.php"."|".
			       "adv_wps.php"."|".
			       "adv_userlimit.php";
  }
  else
  {
     $menu = i18n("ADVANCED WIRELESS");
	   $link =	"adv_network.php";
  }
}	
else if ($TEMP_MYGROUP=="maintenance")
{
  if($switchmode!="APCLI")
  {

   $menu = i18n("ADMIN")."|".
			     i18n("SYSTEM")."|".
			     i18n("FIRMWARE")."|".
			     i18n("TIME")."|".
			     i18n("SYSTEM CHECK")."|".
			     i18n("SCHEDULES");
			     
	 $link = "tools_admin.php"."|".
			     "tools_system.php"."|".
			     "tools_firmware.php"."|".
			     "tools_time.php"."|".
			     "tools_check.php"."|".
			     "tools_sch.php";
  }
  else
  {
   $menu = i18n("Admin")."|".                             
                             i18n("SYSTEM")."|".
                             i18n("FIRMWARE")."|".
                             i18n("TIME");

         $link = "tools_admin.php"."|". 
                             "tools_system.php"."|".
                             "tools_firmware.php"."|".
                             "tools_time.php";

  }
}
else if ($TEMP_MYGROUP=="status")
{
  if($switchmode!="APCLI")
  {
     $menu = i18n("DEVICE INFO")."|".
			       i18n("LOGS")."|".
			       i18n("STATISTICS")."|".
			       i18n("WIRELESS")."|".
			       i18n("IPv6");
			       
	   $link = "st_device.php"."|".
			       "st_log.php"."|".
			       "st_stats.php"."|".
			       "st_wlan.php"."|".
			       "st_ipv6.php";
  }
  else
  {
    $menu = i18n("DEVICE INFO")."|".
			      i18n("LOGS")."|".
				  i18n("STATISTICS");
			      
	  $link = "st_device.php"."|".
			      "st_log.php"."|".
				  "st_stats.php";
  }
}
else if ($TEMP_MYGROUP=="help")
{
	$menu = i18n("MENU");
	$link = "spt_menu.php";
}

?>
