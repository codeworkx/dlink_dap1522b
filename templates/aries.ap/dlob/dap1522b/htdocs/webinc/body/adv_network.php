<form id="mainform" onsubmit="return false;">
<div class="orangebox">
	<h1><?echo i18n("Advanced Wireless");?></h1>
	<p><?echo i18n("These options are for users that wish to change the behaviour of their 802.11n wireless radio from the standard setting. ");?>
		 <?echo i18n("D-link does not recommend changing these settings from factory default. Incorrect settings may impair the performance of wireless radio. The default settings should provide the best wireless radio performance in most enviroments.");?>
	</p>
	<p>
		<input type="button" value="<?echo i18n("Save Settings");?>" onclick="BODY.OnSubmit();" />
		<input type="button" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" />
		<input id="rebootitem1" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" />
	</p>
</div>
<div class="blackbox">
	<h2><?echo i18n("Advanced Wireless Settings");?></h2>
	<!--<h2><?echo i18n("Transmit Power");?></h2>
	<p><?echo i18n("Normally the wireless transmitter operates at 100% power. In some circumstances, there might be a need to isolate specific frequencies to a smaller area.");?></p>-->
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
	<!--<div class="gap"></div>
</div>

<div class="blackbox">
	<h2><?echo i18n("Advanced Wireless Settings");?></h2>
	<!--<h2><?echo i18n("Transmit Power");?></h2>
	<p><?echo i18n("Normally the wireless transmitter operates at 100% power. In some circumstances, there might be a need to isolate specific frequencies to a smaller area.");?></p>-->
	<div class="textinput" style="display:none">
		<span class="name"><?echo i18n("Transmission Rate");?></span>
		<span class="delimiter">:</span>
		<span class="value">
			<select id="txrate_bak">
					<option value="-1"><?echo i18n("Best")." (".i18n("automatic").")";?></option>
				</select>
				(Mbit/s)
		</span>
	</div>
	<!--<div class="gap"></div>
</div>
<div class="blackbox">
	<h2><?echo i18n("WMM Enable");?></h2>
	<p><?echo i18n("Enabling WMM can help control latency and jitter when transmitting multimedia content over a wireless connection.");?></p>-->
	<div class="textinput" id="wmm_none">
		<span class="name"><?echo i18n("WMM Enable");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="wmm" type="checkbox" /></span>
	</div>
	<!--<div class="gap"></div>
</div>

<div class="blackbox">
	<h2><?echo i18n("Short GI");?></h2>
	<p><?echo i18n("Using a short (400ns) guard interval can increase throughput. However, it can also increase error rates in some installations");?>
	<?echo i18n(" due to increased sensitivity to radio-frequency reflections. Select the option that works best for your installation.");?></p>-->
	<div class="textinput" id="shortgi_none">
		<span class="name"><?echo i18n("Short GI");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="shortgi" type="checkbox" /></span>
	</div>
	<!--<div class="gap"></div>
</div>

<div class="blackbox">
	<h2><?echo i18n("Multicast Streams");?></h2>
	<p><?echo i18n("This section is to allow to enable or disable the wireless multicast function.");?></p>-->
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
	<div class="textinput" id="enhance_none">
		<span class="name"><?echo i18n("IGMP Snooping");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="enhance" type="checkbox" /></span>
	</div>
	<div class="textinput" id="isc_none">
  	<span class="name"><?echo i18n("Internal Station Connection");?></span>
		<span class="delimiter">:</span>
		<span class="value">
  	    <select id="isc">
    	      <option value="0"><?echo i18n("Allow");?></option>
      	    <option value="1"><?echo i18n("Deny");?></option>
      	</select>
	  </span>
	</div>
  
	<div class="textinput" id="ewa_none">
  	<span class="name"><?echo i18n("Ethernet to WLAN Access");?></span>
		<span class="delimiter">:</span>
		<span class="value">
  	    <select id="ewa">
    	      <option value="0"><?echo i18n("Allow");?></option>
      	    <option value="1"><?echo i18n("Deny");?></option>
	      </select>
  	</span>
	</div>
	
	<div class="textinput" id="ht_none">
  	<span class="name"><?echo i18n("HT20/40 Coexistence");?></span>
		<span class="delimiter">:</span>
		<span class="value">
    	   <input type="radio" id="ht_en" name="ht_status" value="1"><?echo i18n("Enable");?>
	       <input type="radio" id="ht_dis" name="ht_status" value="0"><?echo i18n("Disable");?>
  	</span>
	</div>
	<!--<div class="gap"></div>
</div>

<div class="blackbox">
	<h2><?echo i18n("Others");?></h2>
	<p><?echo i18n("");?></p>-->
	<div class="textinput" id="dtim_none" style="display:none">
		<span class="name"><?echo i18n("DTIM");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="dtim" maxlength=4 size=4 /></span>
	</div>

	<div class="textinput" id="rtsthresh_none" style="display:none">
		<span class="name"><?echo i18n("Rtsthresh");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="rtsthresh" maxlength=4 size=4 /></span>
	</div>
	
	<div class="textinput" id="fragthresh_none" style="display:none">
		<span class="name"><?echo i18n("Fragthresh");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="fragthresh" maxlength=4 size=4 /></span>
	</div>
	<div class="gap"></div>
</div>

<p><input type="button" value="<?echo i18n("Save Settings");?>" onclick="BODY.OnSubmit();" />
<input type="button" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" />
<input id="rebootitem2" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
</form>
