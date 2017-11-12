<form id="mainform" onsubmit="return false;">
<div class="orangebox">
	<h1><?echo i18n("Advanced Network Settings");?></h1>
	<p><?echo i18n("These options are for users that wish to change the LAN settings. We do not recommend changing these settings from factory default. ");?>
	<?echo i18n("Changing these settings may affect the behavior of your network.");?>
	</p>
	<p>
		<input type="button" value="<?echo i18n("Save Settings");?>" onclick="BODY.OnSubmit();" />
		<input type="button" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" />
	</p>
</div>
<div class="blackbox">
	<h2><?echo i18n("Transmit Power");?></h2>
	<p><?echo i18n("Normally the wireless transmitter operates at 100% power. In some circumstances, there might be a need to isolate specific frequencies to a smaller area.");?></p>
	<div class="textinput">
		<span class="name"><?echo i18n("Transmit Power");?></span>
		<span class="delimiter">:</span>
		<span class="value">
			<select id="tx_power">
				<option value="100">100%</option>
				<option value="50">50%</option>
				<option value="25">25%</option>
				<option value="12.5">12.5%</option>
			</select>
		</span>
	</div>
	<div class="gap"></div>
</div>

<div class="blackbox">
	<h2><?echo i18n("WMM Enable");?></h2>
	<p><?echo i18n("Enabling WMM can help control latency and jitter when transmitting multimedia content over a wireless connection.");?></p>
	<div class="textinput">
		<span class="name"><?echo i18n("WMM Enable");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="wmm" type="checkbox" /></span>
	</div>
	<div class="gap"></div>
</div>

<div class="blackbox">
	<h2><?echo i18n("Multicast Streams");?></h2>
	<p><?echo i18n("This section is to allow to enable or disable the Ethernet multicast function.");?></p>
	<!--
	<div class="textinput">
		<span class="name"><?echo i18n("WMM Enable");?></span>
		<span class="delimiter">:</span>
		<span class="value">
			<input id="en_wmm" type="checkbox" />
			(<?echo i18n("Wireless QoS");?>)
		</span>
	</div>
	-->
	<div class="textinput">
		<span class="name"><?echo i18n("IGMP Snooping");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="enhance" type="checkbox" /></span>
	</div>
	<div class="gap"></div>
</div>
<p><input type="button" value="<?echo i18n("Save Settings");?>" onclick="BODY.OnSubmit();" />
<input type="button" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" /></p>
</form>
