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
	<p><?echo i18n("Add wireless device to your wireless network");?>:</p>
	<br>
	<p>-PIN (<?echo i18n("Personal Identification Number");?>)</p>
	<br>
	<!--p>-PBC (<?echo i18n("Push Button Configuration");?>)</p-->
	<br>
	<div class="textinput">
		<span class="name"><input id="wps_pin_on" type="radio" onClick="PAGE.OnClickWPSSwitch('pin');" /> PIN</span>
		<span class="delimiter">:</span>
		<span id="pincode" class="value"></span>
	</div>
	<div class="textinput">
		<span class="name"></span>
		<span class="delimiter"></span>
		<span class="value">
			<input id="reset_pin" type="button" value="<?echo i18n("Reset PIN to Default");?>"
				onClick="PAGE.OnClickResetPIN();" />&nbsp;&nbsp;
			<input id="gen_pin" type="button" value="<?echo i18n("Generate New PIN");?>"
				onClick="PAGE.OnClickGenPIN();" />
		</span>
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
		<input type="button" value="<?echo i18n("Prev");?>" onClick="PAGE.OnClickPrev();" style="display:none"/>&nbsp;&nbsp;
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

<div id="wps_pbc" class="orangebox">
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
		<input type="button" value="<?echo i18n("Next");?>" onClick="PAGE.OnClickNext();" />
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

<div id="ssid" class="orangebox">
	<h1><?echo i18n("Set Wireless Network Name");?>(SSID)</h1>
	<p>
		<?echo i18n("You can enter the Wireless Network Name of AP or use site survey to find the AP.");?>
	</p>
	<div class="gap"></div>	
	<div class="textinput">
		<span class="name"><?echo i18n("Wireless Network Name ");?>(SSID)</span>
		<span class="delimiter">:</span>
		<span class="value">
			<input id="ssid_input" type="text" size="20" maxlength="32" />&nbsp;&nbsp;
			<input type="button" value="<?echo i18n("Site Survey");?>" onClick="PAGE.OnSubmit();"/>	
		</span>	
	</div>
	<div class="gap"></div>
	<div class="gap"></div>
	<div class="gap"></div>
	<div class="gap"></div>			
	<div class="centerline">
		<input type="button" value="<?echo i18n("Prev");?>" onClick="PAGE.OnClickPrev();" />&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Next");?>" onClick="PAGE.OnClickNext();" />&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Exit");?>" onClick="PAGE.OnClickCancel();" />&nbsp;&nbsp;
	</div>
	<div class="gap"></div>	
</div>

<div id="site_survey_scan" class="orangebox">
	<h1><?echo i18n("SCANNING AVAILABLE NETWORK");?>...</h1>
	<div class="gap"></div>
	<div class="gap"></div>	
	<p align="center">
		<?echo i18n("Scanning available network");?>...
	</p>	
	<div class="gap"></div>
	<div class="gap"></div>		
</div>

<div id="site_survey">
	<table id="SiteSurveyTable" name="SiteSurveyTable" border="1">
		<tr bgcolor="#66CCFF">
			<th width="200px"><?echo i18n("SSID");?></th>
			<th width="120px"><?echo i18n("BSSID");?></th>
			<th width="60px"><?echo i18n("Channel");?></th>
			<th width="40px"><?echo i18n("Type");?></th>
			<th width="150px"><?echo i18n("Encrypt");?></th>
			<th width="50px"><?echo i18n("Signal");?></th>
			<th width="50px"><?echo i18n("Select");?></th>
		</tr>			
	</table>
	<div class="gap"></div>	
	<div class="centerline">
		<input type="button" value="<?echo i18n("Next");?>" onClick="PAGE.OnClickNext();" />&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Cancel");?>" onClick="PAGE.OnClickCancel();" />&nbsp;&nbsp;
	</div>
	<div class="gap"></div>	
</div>

<div id="security" class="orangebox">
	<h1><?echo i18n("SELECT WIRELESS SECURITY MODE");?></h1>
	<div class="gap"></div>
	<p align="center">
		<?echo i18n("Please select the wireless security mode.");?>
	</p>
	<div class="gap"></div>
	<table align="center">
		<tr>
			<td><input id="security_none" type="radio" onClick="PAGE.OnChangeSecurity('none');" /></td>
			<td><?echo i18n("None");?></td>			
		</tr>
		<tr>
			<td><input id="security_wep" type="radio" onClick="PAGE.OnChangeSecurity('wep');" /></td>
			<td>&nbsp;WEP</td>
		</tr>
		<tr>
			<td><input id="security_wpa" type="radio" onClick="PAGE.OnChangeSecurity('wpa');" /></td>
			<td>&nbsp;WPA</td>
		</tr>
		<tr>
			<td><input id="security_wpa2" type="radio" onClick="PAGE.OnChangeSecurity('wpa2');" /></td>
			<td>&nbsp;WPA2</td>
		</tr>
		<tr>
			<td><input id="security_wpa_auto" type="radio" onClick="PAGE.OnChangeSecurity('wpa_auto');" /></td>
			<td>&nbsp;WPA Auto</td>
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

<div id="wpa_key" class="orangebox">
	<h1><?echo i18n("SET YOUR WPA PERSONAL PASSPHRASE");?></h1>
	<p>
		<?echo i18n("Please enter the WPA/WPA2-Auto personal passphrase to establish wireless connection.");?>
	</p>
	<div class="gap"></div>	
	<div class="textinput">
		<span id="wpa_input_str" class="name"><?echo i18n("WPA/WPA2-Auto Personal Passphrase");?></span>
		<span class="delimiter">:</span>
		<span class="value">
			<input id="wpa_input" type="text" size="45" maxlength="63" />&nbsp;&nbsp;(8 to 63 characters)	
		</span>	
	</div>
	<div class="gap"></div>
	<div class="gap"></div>
	<div class="gap"></div>
	<div class="gap"></div>			
	<div class="centerline">
		<input type="button" value="<?echo i18n("Prev");?>" onClick="PAGE.OnClickPrev();" />&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Next");?>" onClick="PAGE.OnClickNext();" />&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Exit");?>" onClick="PAGE.OnClickCancel();" />&nbsp;&nbsp;
	</div>
	<div class="gap"></div>	
</div>

<div id="wep_key" class="orangebox">
	<h1><?echo i18n("SET YOUR WIRELESS SECURITY PASSWORD");?></h1>
	<p align="center">
		<?echo i18n("Please enter the wireless security password to establish wireless connection.");?>
	</p>
	<div class="gap"></div>
	<div class="gap"></div>		
	<div class="textinput">
		<span class="name"><?echo i18n("Key Size");?></span>
		<span class="delimiter">:</span>
		<span class="value">
			<select id="wep_type">
				<option value="hex_10">64Bit(10 hex digits)</option>
				<option value="ascii_5">64Bit(5 ascii characters)</option>
				<option value="hex_26">128Bit(26 hex digits)</option>
				<option value="ascii_13">128Bit(13 ascii characters)</option>			
			</select>
		</span>
	</div>
	<div class="textinput">
		<span class="name"><?echo i18n("WEP Key ");?>1</span>
		<span class="delimiter">:</span>
		<span class="value">
			<input id="wep_input" type="text" size="20" maxlength="26" />&nbsp;&nbsp;	
		</span>	
	</div>
	<div class="gap"></div>
	<div class="gap"></div>
	<div class="gap"></div>
	<div class="gap"></div>			
	<div class="centerline">
		<input type="button" value="<?echo i18n("Prev");?>" onClick="PAGE.OnClickPrev();" />&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Next");?>" onClick="PAGE.OnClickNext();" />&nbsp;&nbsp;
		<input type="button" value="<?echo i18n("Exit");?>" onClick="PAGE.OnClickCancel();" />&nbsp;&nbsp;
	</div>
	<div class="gap"></div>	
</div>

<div id="final" class="orangebox">
	<h1><?echo i18n("CONNECT TO WIRELESS DEVICE");?></h1>
	<div class="gap"></div>	
	<p align="center">
		<?echo i18n("The wireless setup wizard has completed.");?>
	</p>
	<div class="gap"></div>
	<div class="gap"></div>	
	<div class="centerline">
		<input type="button" value="<?echo i18n("Finish");?>" onClick="PAGE.OnClickNext();" />
	</div>
	<div class="gap"></div>	
</div>

</form>
