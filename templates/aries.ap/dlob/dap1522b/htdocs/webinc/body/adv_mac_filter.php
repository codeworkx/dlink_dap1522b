<form id="mainform" onsubmit="return false;">
<div class="orangebox">
	<h1><?echo i18n("MAC Address Filter");?></h1>
	<p>
		<?echo i18n("The MAC (Media Access Controller) Address filter option is used to control network access based on the MAC Address of the network adapter.");?>
		<?echo i18n("A MAC address is a unique ID assigned by the manufacturer of the network adapter.");?>
		<?echo i18n("This feature can be configured to ALLOW or DENY network/wireless access.");?>
	</p>
	<p><input type="button" name="btn1" id="btn1" value="<?echo i18n("Save Settings");?>" onclick="BODY.OnSubmit();" />
		<input type="button" name="btn2" id="btn2" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" />
		<input id="rebootitem1" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
</div>
<div class="blackbox">
	<h2><?echo i18n("Wireless Access Settings");?></h2>
	<p><?echo i18n("Configure MAC Filtering below:");?>
	
		<select id="mode" onchange="PAGE.OnChangeFilterStatus();">
			<option value="DISABLE"><?echo i18n("Turn MAC Filtering OFF");?></option>
			<option value="DROP"><?echo i18n("Turn MAC Filtering ON and ALLOW computers listed to access the network");?></option>
			<option value="ACCEPT"><?echo i18n("Turn MAC Filtering ON and DENY computers listed to access the network");?></option>
		</select>
	</p>
	<div class="centerline" align="center">
		<table id="" class="general_NoBreakWord">
		<tr  align="center">
			<td width="15px">&nbsp;</td>
			<td width="70px"><?echo i18n("MAC Address");?></td>
			<td width="13px">&nbsp;</td>		
			<td width="70px"><?echo i18n("MAC List");?></td>			
			<td width="29px">&nbsp;</td>		
		</tr>
<?
$INDEX = 1;
while ($INDEX <= $MAC_FILTER_MAX_COUNT) {	dophp("load", "/htdocs/webinc/body/adv_mac_filter_list.php");	$INDEX++; }
?>
		</table>
	</div>
	<div class="gap"></div>
</div>
<p><input type="button" name="btn3" id="btn3" value="<?echo i18n("Save Settings");?>" onclick="BODY.OnSubmit();" />
<input type="button" name="btn4" id="btn4" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" />
<input id="rebootitem2" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
</form>
