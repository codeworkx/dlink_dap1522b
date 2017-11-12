<?
include "/htdocs/webinc/body/draw_elements.php";
include "/htdocs/phplib/wifi.php";
?>
<form id="mainform" onsubmit="return false;">
<div class="orangebox">
	<h1><?echo i18n("Wireless Connection Setup Wizard");?></h1>
	<p><?echo i18n("If you would like to utilize our easy to use web-based wizard to assist you in connecting your DAP-1522B to the wireless network,click on the button below.");?></p>
	<div class="emptyline"></div>
	<div id="div_station" class="centerline">
		<input  type="button"  value="<?echo i18n("Launch Wireless Setup Wizard");?>" onClick="javascript:self.location.href='./wiz_wlan_br.php';" />
	</div>
	<div id="div_24G_band" class="centerline">
		<input  type="button"  value="<?echo i18n("Launch Wireless Setup Wizard");?>" onClick="javascript:self.location.href='./wiz_wlan_ap.php';" />
	</div>
	<div class="emptyline"></div>
	<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "<!--";} ?>
		<p style="color:red;"><?echo i18n("<strong>Note:</strong> Some changes made using this Setup Wizard may require you to change some settings on your wireless client adapters so they can still connect to the D-Link Access Point.");?></p>
	<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "-->";} ?>
	<? if(query("/runtime/device/switchmode") !="APCLI")    {echo "<!--";} ?>
		<p style="color:red;"><?echo i18n("<strong>Note:</strong> Some changes made using this Setup Wizard must be the same with the settings of the D-Link Access Point which you want to connect.");?></p>
	<? if(query("/runtime/device/switchmode") !="APCLI")    {echo "-->";} ?>

</div>
<div class="emptyline"></div>

</form>
<div id="pad" style="display:none;">


</div>
