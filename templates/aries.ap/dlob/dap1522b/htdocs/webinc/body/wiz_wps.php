<form id="mainform">

<div id="wiz_start" class="orangebox">
	<h1><?echo i18n("WIRELESS CONNECTION SETUP WIZARD");?></h1>
	<div class="gap"></div>
	<p>
		<?echo i18n("This wizard is designed to assist you in your wireless network setup. It will guide you through step-by-step instructions on how to set up your wireless network and how to make it secure.");?>
	</p>
	<div class="gap"></div>
	<div class="gap"></div>
	<div class="gap"></div>		
	<div class="centerline">
		<input type="button" value="<?echo i18n("Next");?>" onClick="PAGE.OnClickNext();" />&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Cancel");?>" onClick="PAGE.OnClickCancel();" />&nbsp;&nbsp;
	</div>
	<div class="gap"></div>	
</div>

<div id="set_config" class="orangebox">
	<h1><?echo i18n("SELECT CONFIGURATION METHOD");?></h1>
	<div class="gap"></div>
	<p>
		<?echo i18n("Please select one of the following configuration methods and click <b>next</b> to continue.");?>
	</p>
	<div class="gap"></div>
	<table align="center">
		<tr>
			<td>
				<input id="wps_mode" type="radio" onClick="PAGE.OnChangeConfig('wps');" />&nbsp;
				<b>WPS</b> -- <?echo i18n("Select this option if your wireless device supports WPS (Wi-Fi Protected Setup)");?>
				<br>
				<br>				
				<input id="manual_mode" type="radio" onClick="PAGE.OnChangeConfig('manual');" />&nbsp;
				<b>Manual</b> -- <?echo i18n("Select this option if you want to setup your network manually ");?>
			</td>
		</tr>
	</table>
	<div class="gap"></div>
	<div class="gap"></div>
	<div class="centerline">
		<input type="button" value="<?echo i18n("Prev");?>" onClick="PAGE.OnClickPrev();" />&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Next");?>" onClick="PAGE.OnClickNext();" />&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Cancel");?>" onClick="PAGE.OnClickCancel();" />&nbsp;&nbsp;
	</div>
	<div class="gap"></div>	
</div>

<div id="wps_config" class="orangebox">
	<h1><?echo i18n("CONNECT TO WIRELESS DEVICE WITH WPS");?></h1>
	<br>
	<p><?echo i18n("There are two ways to add wireless device to your wireless network:");?>:</p>
	<br>
	<p>-PIN (<?echo i18n("Personal Identification Number");?>)</p>
	<br>
	<p>-PBC (<?echo i18n("Push Button Configuration");?>)</p>
	<br>
	<div class="textinput">
		<span class="name"><input id="wps_pin_on" type="radio" onClick="PAGE.OnClickWPSSwitch('pin');" /> PIN</span>
		<span class="delimiter">:</span>
		<span class="value"><input id="pincode" type="text" size="20" maxlength="8" onClick="PAGE.OnClickWPSSwitch('pin');" /></span>
	</div>
	<p align="center"><?echo i18n('Please Enter the above PIN information into your Access Point and click the below "Connect" button.');?></p>
	<br>
	<div class="textinput">
		<span class="name"><input id="wps_pbc_on" type="radio" onClick="PAGE.OnClickWPSSwitch('pbc');" /> PBC</span>
		<span class="delimiter"></span>
		<span class="value"></span>
	</div>	
	<p align="center"><?echo i18n('Please press the push button on your wireless device and press the "Connect" button below within 120 seconds.');?></p>
	<br>
	<div class="centerline">
		<!--input type="button" value="<?echo i18n("Prev");?>" onClick="PAGE.OnClickPrev();" /-->&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Connect");?>" onClick="PAGE.OnClickNext();" />&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Exit");?>" onClick="PAGE.OnClickCancel();" />&nbsp;&nbsp;
	</div>
	<div class="gap"></div>
	<div class="gap"></div>
</div>

<div id="wps_pin" class="orangebox">
	<h1><?echo i18n("USING PIN NUMBER");?></h1>
	<div class="gap"></div>	
	<div class="centerline">	
		<span><?echo i18n("Please start WPS on the wireless device you are adding to your wireless network within");?></span>
		<span id="ct_pin" style="color:red;">120</span>
		<span><?echo i18n("second");?>...</span>
	</div>
	<div class="gap"></div>
	<div class="gap"></div>
	<div class="gap"></div>	
</div>

<div id="wps_pbc" class="orangebox"  style="display:none">
	<h1><?echo i18n("VIRTUAL PUSH BUTTON");?></h1>
	<div class="gap"></div>	
	<div class="centerline">	
		<span><?echo i18n("Please press down the Push Button (physical or virtual) on the wireless device you are adding to your wireless network within");?></span>
		<span id="ct_pbc" style="color:red;">120</span>
		<span><?echo i18n("second");?>...</span>
	</div>
	<div class="gap"></div>
	<div class="gap"></div>
	<div class="gap"></div>	
</div>

<div id="wps_ok" class="orangebox">
	<h1><?echo i18n("ADD WIRELESS DEVICE WITH WPS");?></h1>
	<div class="gap"></div>	
	<p align="center">
		<?echo i18n("You have succeeded to add the wireless device to your wireless network.");?>
	</p>
	<div class="gap"></div>
	<div class="gap"></div>	
	<div class="centerline">
		<input type="button" value="<?echo i18n("Finish");?>" onClick="PAGE.OnClickNext();" />
	</div>
	<div class="gap"></div>	
</div>

<div id="wps_fail" class="orangebox">
	<h1><?echo i18n("ADD WIRELESS DEVICE WITH WPS");?></h1>
	<div class="gap"></div>	
	<p>
		<?echo i18n("You have failed to add the wireless device to your wireless network within the given timeframe, please click on the button below to do it again.");?>
	</p>
	<div class="gap"></div>
	<div class="gap"></div>	
	<div class="centerline">
		<input type="button" value="<?echo i18n("Retry");?>" onClick="PAGE.OnClickNext();" />&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Cancel");?>" onClick="PAGE.OnClickCancel();" />&nbsp;&nbsp;
	</div>
	<div class="gap"></div>	
</div>

<div id="wps_result" class="orangebox">
	<h1><?echo i18n("SETUP COMPLETE!");?></h1>
	<p>
		<?echo i18n("Please keep a note of the following settings for future reference.");?>
	</p>
	<div class="gap"></div>
	<div class="textinput">
		<span class="name"><?echo i18n("Wireless Network Name ");?>(SSID)</span>
		<span class="delimiter">:</span>
		<span class="value" id="ssid_wps"></span>	
	</div>
	<div class="gap"></div>
		<div class="textinput">
		<span class="name"><?echo i18n("Wireless Security Mode");?></span>
		<span class="delimiter">:</span>
		<span class="value" id="security_wps"></span>
	</div>
	<div class="gap"></div>
		<div class="textinput">
		<span class="name"><?echo i18n("Network Key");?></span>
		<span class="delimiter">:</span>
		<span class="value" id="key_wps" style="word-wrap:break-word;"></span>
	</div>
	<div class="gap"></div>
	<div class="centerline">
		<input type="button" value="<?echo i18n("End");?>" onClick="PAGE.OnClickNext();" />
	</div>
	<div class="gap"></div>	
</div>

<div id="manual" class="orangebox">
	<h1><?echo i18n("WELCOME TO THE D-LINK WIRELESS SETUP WIZARD");?></h1>
	<p>
		<?echo i18n("Give your network a name, using up to 32 characters.");?>
	</p>
	<div class="textinput">
		<span class="name"><?echo i18n("Network Name ");?>(SSID)</span>
		<span class="delimiter">:</span>
		<span class="value"><input id="ssid_input" type="text" size="32" maxlength="32" /></span>
	</div>
	<div class="gap"></div>	
	<div>
		<input id="assign_key" type="checkbox" onClick="PAGE.OnChangeNetworkKey();" />
		<?echo i18n("Assign a network key");?>
	</div>
	<div class="gap"></div>	
	<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<?echo i18n("The WPA (Wi-Fi Protected Access) key must meet the following guidelines");?>
	</div>
	<div class="gap"></div>	
	<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<?echo i18n("- Between 8 and 63 characters (A longer WPA key is more secure than a short one)");?>
	</div>
	<div class="gap"></div>	
	<div class="textinput">
		<span class="name"><?echo i18n("Network Key");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="key_input" type="text" size="63" maxlength="63" /></span>
	</div>
	<div class="gap"></div>	
	<div class="centerline">
		<input type="button" value="<?echo i18n("Prev");?>" onClick="PAGE.OnClickPrev();" />&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Next");?>" onClick="PAGE.OnClickNext();" />&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Exit");?>" onClick="PAGE.OnClickCancel();" />&nbsp;&nbsp;
	</div>
	<div class="gap"></div>	
</div>

<div id="manual_result" class="orangebox">
	<h1><?echo i18n("SETUP COMPLETE!");?></h1>
	<p>
		<?echo i18n("Please keep a note of the following settings for future reference.");?>
	</p>
	<div class="gap"></div>
	<div class="textinput">
		<span class="name"><?echo i18n("Wireless Network Name ");?>(SSID)</span>
		<span class="delimiter">:</span>
		<span class="value" id="ssid_manual"></span>	
	</div>
	<div class="gap"></div>
		<div class="textinput">
		<span class="name"><?echo i18n("Wireless Security Mode");?></span>
		<span class="delimiter">:</span>
		<span class="value"><?echo i18n("Auto (WPA or WPA2) TKIP/AES");?></span>
	</div>
	<div class="gap"></div>
		<div class="textinput">
		<span class="name"><?echo i18n("Network Key");?></span>
		<span class="delimiter">:</span>
		<span class="value" id="key_manual" style="word-wrap:break-word;"></span>
	</div>
	<div class="gap"></div>
	<div class="centerline">
		<input type="button" value="<?echo i18n("Prev");?>" onClick="PAGE.OnClickPrev();" />&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Save");?>" onClick="PAGE.OnClickNext();" />&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Exit");?>" onClick="PAGE.OnClickCancel();" />&nbsp;&nbsp;
	</div>
	<div class="gap"></div>	
</div>

</form>
