<form id="mainform" onsubmit="return false;">
<div class="orangebox">
	<h1><?echo i18n("USER LIMIT SETTINGS");?></h1>
	<p>
		<?echo i18n("Please Apply the settings to limit how many wireless stations connecting to AP.");?>
	</p>
	<p><input type="button" value="<?echo i18n("Save Settings");?>" onclick="BODY.OnSubmit();" />
		<input type="button" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" />
		<input id="rebootitem1" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
</div>


<div class="blackbox">
	<h2><?echo i18n("USER LIMIT SETTINGS");?></h2>
	<div class="textinput">
			<span class="name"><?echo i18n("Enable User Limit");?> </span>
			<span class="delimiter">:</span>
			<input name="limit_enable" id="limit_enable" type="checkbox" onClick="PAGE.OnCheckLimit('');">
	</div>
  
  <div class="textinput">
			<span class="name"><?echo i18n("User Limit(1 - 32)");?> </span>
			<span class="delimiter">:</span>
			<input name="limit_value" id="limit_value">
	</div>
	<div class="gap"></div>
</div>


<p><input type="button" value="<?echo i18n("Save Settings");?>" onclick="BODY.OnSubmit();" />
<input type="button" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" />
<input id="rebootitem2" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
</form>
