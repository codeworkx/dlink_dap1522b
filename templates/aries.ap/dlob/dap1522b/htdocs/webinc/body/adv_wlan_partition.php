<form id="mainform" onsubmit="return false;">
<div class="orangebox">
	<h1><?echo i18n("WLAN PARTITION SETTINGS");?></h1>
	<p>
		<?echo i18n("");?>
	</p>
	<p><input type="button" value="<?echo i18n("Save Settings");?>" onclick="BODY.OnSubmit();" />
		<input type="button" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" />
		<input id="rebootitem1" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
</div>


<div class="blackbox">
	<h2><?echo i18n("WLAN PARTITION SETTINGS");?></h2>
	<div class="textinput">
    <span class="name"><?echo i18n("Internal Station Connection");?></span>
		<span class="delimiter">:</span>
        <select id="isc">
            <option value="0"><?echo i18n("Allow");?></option>
            <option value="1"><?echo i18n("Deny");?></option>
        </select>
    </p>
  </div>
  
  <div class="textinput">
	<span class="name"><?echo i18n("Ethernet to WLAN Access");?></span>
		<span class="delimiter">:</span>
        <select id="ewa">
            <option value="0"><?echo i18n("Allow");?></option>
            <option value="1"><?echo i18n("Deny");?></option>
        </select>
    </p>
  </div>
	<div class="gap"></div>
</div>


<p><input type="button" value="<?echo i18n("Save Settings");?>" onclick="BODY.OnSubmit();" />
<input type="button" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" />
<input id="rebootitem2" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
</form>
