<?
include "/htdocs/webinc/body/draw_elements.php";
?>

<form id="mainform" onsubmit="return false;">
<div class="orangebox">
	<h1><?echo i18n("Guest Zone Selection");?></h1>
	<p><?echo i18n("Use this section to configure the guest zone settings of your AP. The guest zone provide a separate network zone for guest to access Internet.");?></p>
	<p><input type="button" value="<?echo i18n("Save Settings");?>" onClick="BODY.OnSubmit();" />
	<input type="button" value="<?echo i18n("Don't Save Settings");?>" onClick="BODY.OnReload();" />
	<input id="rebootitem1" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
</div>

<div id="div_gz_24G_band" >	
	<style type="text/css"> 
		div.textinput span.name
		{
			width: 41%;
			float: left;
			text-align: right;
			font-weight: bold;
			margin-top: 4px;
		}
		div.textinput span.value
		{
			width: 55%;
			float: left;
			text-align: left;
			margin-top: 4px;
		}
	</style>
	<div class="blackbox">
		<h2><?echo i18n("Guest Zone");?></h2>
		<div class="textinput">
			<span class="name"><?echo i18n("Enable Guest Zone");?></span>
			<span class="delimiter">:</span>
			<span class="value"><input id="en_gzone" type="checkbox" onClick="PAGE.OnClickEnGzone('');" />
			<?
				if ($FEATURE_NOSCH!="1")
				{
					DRAW_select_sch("sch_gz", i18n("Always"), "", "", "0", "narrow");
					echo '<input id="go2sch_gz" type="button" value="'.i18n("New Schedule").'" onClick="javascript:self.location.href=\'./tools_sch.php\';" />\n';
				}
			?>
			</span>
		</div>
	
		<div class="textinput">
			<span class="name"><?echo i18n("Wireless Band");?></span>
			<span class="delimiter">:</span>
			<span class="value"><b><?echo i18n("2.4GHz Band");?></b></span>
		</div>
		
		<div class="textinput">
			<span class="name"><?echo i18n("Wireless Network Name");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<input id="ssid" type="text" size="20" maxlength="32" />
				(<?echo i18n("Also called the SSID");?>)
			</span>
		</div>
		<div class="textinput">
			<span class="name"  ><?echo i18n("Security Mode");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="security_type" onChange="PAGE.OnChangeSecurityType('');">
					<option value=""><?echo i18n("None");?></option>
					<option value="WEP"><?echo i18n("WEP");?></option>
					<option value="WPA_P"><?echo i18n("WPA Personal");?></option>
					<option value="WPA_E"><?echo i18n("WPA Enterprise");?></option>
				</select>
			</span>
		</div>
		<div class="gap"></div>
	</div>		
		<div id="wep" class="blackbox" style="display:none;">
			<h2><?echo i18n("WEP");?></h2>
			<p><?echo i18n("WEP is the wireless encryption standard.")." ".
				i18n("To use it you must enter the same key(s) into the AP and the wireless client.")." ".
				i18n("For 64-bit keys you must enter 10 hex digits into each key box.")." ".
				i18n("For 128-bit keys you must enter 26 hex digits into each key box. ")." ".
				i18n("A hex digit is either a number from 0 to 9 or a letter from A to F.")." ".
				i18n('For the most secure use of WEP set the authentication type to "Shared Key" when WEP is enabled.');?></p>
			<p><?echo i18n("You may also enter any text string into a WEP key box, in which case it will be converted into a hexadecimal key using the ASCII values of the characters.")." ".
				i18n("A maximum of 5 text characters can be entered for 64-bit keys, and a maximum of 13 characters for 128-bit keys.");?></p>
			<p><?echo i18n("If you choose the WEP security option, this device will <strong>ONLY</strong> operate in <strong>Legacy Wireless mode(802.11B/G)</strong>.")." ".
				i18n("This means you will <strong>NOT</strong> get 11N performance due to the fact that WEP is not supported by the Draft 11N specification.");?></p>
			<div class="textinput">
				<span class="name"><?echo i18n("Authentication");?></span>
				<span class="delimiter">:</span>
				<span class="value">
					<select id="auth_type">
						<option value="BOTH"><?echo i18n("Both");?></option>
						<option value="SHARED"><?echo i18n("Shared Key");?></option>
					</select>
				</span>
			</div>
			<div class="textinput">
				<span class="name"><?echo i18n("WEP Key Length  ");?></span>
				<span class="delimiter">:</span>
				<span class="value">
					<select id="wep_key_len" onChange="PAGE.OnChangeWEPKey('');">
						<option value="64">64Bit(10 hex digits/5 ascii)</option>
                        <option value="128">128Bit(26 hex digits/13 ascii)</option>
					</select>
				</span>
			</div>
			<div class="textinput" style="display:none">
				<span class="name"><?echo i18n("Default WEP Key");?></span>
				<span class="delimiter">:</span>
				<span class="value">
					<select id="wep_def_key" onChange="PAGE.OnChangeWEPKey('');">
						<option value="1">WEP Key 1</option>
						<option value="2">WEP Key 2</option>
						<option value="3">WEP Key 3</option>
						<option value="4">WEP Key 4</option>
					</select>
				</span>
			</div>
			<div id="wep_64" class="textinput">
				<span class="name"><?echo i18n("WEP Key 1");?></span>
				<span class="delimiter">:</span>
				<span class="value">
					<input id="wep_64_1" name="wepkey_64" type="password" size="15" maxlength="10" />
					<input id="wep_64_2" name="wepkey_64" type="password" size="15" maxlength="10" />
					<input id="wep_64_3" name="wepkey_64" type="password" size="15" maxlength="10" />
					<input id="wep_64_4" name="wepkey_64" type="password" size="15" maxlength="10" />
					
				</span>
			</div>
			<div id="wep_128" class="textinput">
				<span class="name"><?echo i18n("WEP Key 1");?></span>
				<span class="delimiter">:</span>
				<span class="value">
					<input id="wep_128_1" name="wepkey_128" type="password" size="31" maxlength="26" />
					<input id="wep_128_2" name="wepkey_128" type="password" size="31" maxlength="26" />
					<input id="wep_128_3" name="wepkey_128" type="password" size="31" maxlength="26" />
					<input id="wep_128_4" name="wepkey_128" type="password" size="31" maxlength="26" />
					
				</span>
			</div>
			<div class="gap"></div>
		</div>
		<div id="wpa" class="blackbox" style="display:none;">
			<h2><?echo i18n("WPA/WPA2");?></h2>
			<p><?echo i18n("WPA/WPA2 requires stations to use high grade encryption and authentication.");?></p>
			<div class="textinput">
                        <span class="name"><?echo i18n("WPA Mode");?></span>
                        <span class="delimiter">:</span>
                        <span class="value">
                                <select id="wpa_mode">
                                        <option value="WPA+WPA2">Auto(WPA or WPA2)</option>
                                        <option value="WPA2">WPA2 Only</option>
                                        <option value="WPA">WPA Only</option>
                                </select>
                        </span>
                </div>
			<div class="textinput">
				<span class="name"><?echo i18n("Cipher Type");?></span>
				<span class="delimiter">:</span>
				<span class="value">
					<select id="cipher_type">
						<option value="TKIP+AES">AUTO(TKIP/AES)</option>
						<option value="TKIP">TKIP</option>
						<option value="AES">AES</option>
					</select>
				</span>
			</div>
			<div class="textinput" style="display:none">
				<span class="name"><?echo i18n("PSK / EAP");?></span>
				<span class="delimiter">:</span>
				<span class="value">
					<select id="psk_eap" onChange="PAGE.OnChangeWPAAuth('');">
						<option value="PSK">PSK</option>
						<option value="EAP">EAP</option>
					</select>
				</span>
			</div>
			<div name="psk" class="textinput">
				<span class="name"><?echo i18n("Network Key");?></span>
				<span class="delimiter">:</span>
				<span class="value"><input id="wpapsk" type="password" size="40" maxlength="64" /></span>
			</div>
			<div name="psk" class="textinput">
				<span class="name"></span>
				<span class="delimiter"></span>
				<!--span class="value">(8~62 ASCII or 63 HEX)</span-->
			</div>
			<div name="psk_Aband" class="textinput" style="display:none">
				<span class="name"><?echo i18n("Group Key Update Interval");?></span>
				<span class="delimiter">:</span>
				<span class="value"><input id="wpa_rekey" type="text" size="8" maxlength="7" />&nbsp;(<?echo i18n("Seconds");?>)</span>
			</div>
			<div name="eap" class="textinput">
				<span class="name"><?echo i18n("RADIUS Server IP Address");?></span>
				<span class="delimiter">:</span>
				<span class="value"><input id="srv_ip" type="text" size="15" maxlength="15" /></span>
			</div>
			<div name="eap" class="textinput">
				<span class="name"><?echo i18n("Port");?></span>
				<span class="delimiter">:</span>
				<span class="value"><input id="srv_port" type="text" size="5" maxlength="5" /></span>
			</div>
			<div name="eap" class="textinput">
				<span class="name"><?echo i18n("Shared Secret");?></span>
				<span class="delimiter">:</span>
				<span class="value"><input id="srv_sec" type="password" size="50" maxlength="64" /></span>
			</div>
		</div>
		<div class="blackbox">
			<h2><?echo i18n("Guest Zones Clients Isolation");?></h2>
			<p><?echo i18n("Enable this function to prevent guest clients accessing other guest clients in the Guest Zone.");?></p>
		<div class="textinput">
			<span class="name"><?echo i18n("Enable Guest Zones Clients Isolation");?></span>
			<span class="delimiter">:</span>
			<span class="value"><input id="en_isolation" type="checkbox" /></span>
		</div>	
		<div class="gap"></div>
	</div>	
	<div class="blackbox">
		<h2><?echo i18n("Routing Between Host Zone And Guest Zone");?></h2>
		<p><?echo i18n("Use this section to enable routing between the Host zone and Guest Zone. Guest clients cannot access Host clients' without enabling this function.");?></p>
		<div class="textinput">
			<span class="name"><?echo i18n("Enable Routing Between Zones");?></span>
			<span class="delimiter">:</span>
			<span class="value"><input id="en_routing" type="checkbox" /></span>
		</div>
		<div class="gap"></div>
	</div>	
	
</div> <!--<div id="div_gz_24G_band" >	 --> 

<div id="div_gz_5G_band" >	
	<style type="text/css"> 
		div.textinput span.name
		{
			width: 41%;
			float: left;
			text-align: right;
			font-weight: bold;
			margin-top: 4px;
		}
		div.textinput span.value
		{
			width: 55%;
			float: left;
			text-align: left;
			margin-top: 4px;
		}
	</style>
	<div class="blackbox">
		<h2><?echo i18n("Guest Zone");?></h2>
		<div class="textinput">
			<span class="name"><?echo i18n("Enable Guest Zone");?></span>
			<span class="delimiter">:</span>
			<span class="value"><input id="en_gzone_Aband" type="checkbox" onClick="PAGE.OnClickEnGzone('_Aband');" />
			<?
				if ($FEATURE_NOSCH!="1")
				{
					DRAW_select_sch("sch_gz_Aband", i18n("Always"), "", "", "0", "narrow");
					echo '<input id="go2sch_gz_Aband" type="button" value="'.i18n("New Schedule").'" onClick="javascript:self.location.href=\'./tools_sch.php\';" />\n';
				}
			?>	
			</span>
		</div>
	
		<div class="textinput">
			<span class="name"><?echo i18n("Wireless Band");?></span>
			<span class="delimiter">:</span>
			<span class="value"><b><?echo i18n("5GHz Band");?></b></span>
		</div>
		
		<div class="textinput">
			<span class="name"><?echo i18n("Wireless Network Name");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<input id="ssid_Aband" type="text" size="20" maxlength="32" />
				(<?echo i18n("Also called the SSID");?>)
			</span>
		</div>
		<div class="textinput">
			<span class="name" ><?echo i18n("Security Mode");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="security_type_Aband" onChange="PAGE.OnChangeSecurityType('_Aband');">
					<option value=""><?echo i18n("None");?></option>
					<option value="WEP"><?echo i18n("WEP");?></option>
					<option value="WPA_P"><?echo i18n("WPA Personal");?></option>
					<option value="WPA_E"><?echo i18n("WPA Enterprise");?></option>
				</select>
			</span>
		</div>
		<div class="gap"></div>
	</div>	
		<div id="wep_Aband" class="blackbox" style="display:none;">
		<h2><?echo i18n("WEP");?></h2>
		<p><?echo i18n("WEP is the wireless encryption standard.")." ".
			i18n("To use it you must enter the same key(s) into the AP and the wireless client.")." ".
			i18n("For 64-bit keys you must enter 10 hex digits into each key box.")." ".
			i18n("For 128-bit keys you must enter 26 hex digits into each key box. ")." ".
			i18n("A hex digit is either a number from 0 to 9 or a letter from A to F.")." ".
			i18n('For the most secure use of WEP set the authentication type to "Shared Key" when WEP is enabled.');?></p>
		<p><?echo i18n("You may also enter any text string into a WEP key box, in which case it will be converted into a hexadecimal key using the ASCII values of the characters.")." ".
			i18n("A maximum of 5 text characters can be entered for 64-bit keys, and a maximum of 13 characters for 128-bit keys.");?></p>
		<p><?echo i18n("If you choose the WEP security option, this device will <strong>ONLY</strong> operate in <strong>Legacy Wireless mode(802.11B/G)</strong>.")." ".
				i18n("This means you will <strong>NOT</strong> get 11N performance due to the fact that WEP is not supported by the Draft 11N specification.");?></p>
		<div class="textinput">
			<span class="name"><?echo i18n("Authentication");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="auth_type_Aband">
					<option value="BOTH"><?echo i18n("Both");?></option>
					<option value="SHARED"><?echo i18n("Shared Key");?></option>
				</select>
			</span>
		</div>
		<div class="textinput" >
			<span class="name"><?echo i18n("WEP Encryption");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="wep_key_len_Aband" onChange="PAGE.OnChangeWEPKey('_Aband');">
					<option value="64">64Bit(10 hex digits/5 ascii)</option>
                    <option value="128">128Bit(26 hex digits/13 ascii)</option>
				</select>
			</span>
		</div>
		<div class="textinput" style="display:none">
			<span class="name"><?echo i18n("Default WEP Key");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="wep_def_key_Aband" onChange="PAGE.OnChangeWEPKey('_Aband');">
					<option value="1">WEP Key 1</option>
					<option value="2">WEP Key 2</option>
					<option value="3">WEP Key 3</option>
					<option value="4">WEP Key 4</option>
				</select>
			</span>
		</div>
		<div id="wep_64_Aband" class="textinput">
			<span class="name"><?echo i18n("WEP Key 1");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<input id="wep_64_1_Aband" name="wepkey_64_Aband" type="password" size="15" maxlength="10" />
				<input id="wep_64_2_Aband" name="wepkey_64_Aband" type="password" size="15" maxlength="10" />
				<input id="wep_64_3_Aband" name="wepkey_64_Aband" type="password" size="15" maxlength="10" />
				<input id="wep_64_4_Aband" name="wepkey_64_Aband" type="password" size="15" maxlength="10" />
				
			</span>
		</div>
		<div id="wep_128_Aband" class="textinput">
			<span class="name"><?echo i18n("WEP Key 1");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<input id="wep_128_1_Aband" name="wepkey_128_Aband" type="password" size="31" maxlength="26" />
				<input id="wep_128_2_Aband" name="wepkey_128_Aband" type="password" size="31" maxlength="26" />
				<input id="wep_128_3_Aband" name="wepkey_128_Aband" type="password" size="31" maxlength="26" />
				<input id="wep_128_4_Aband" name="wepkey_128_Aband" type="password" size="31" maxlength="26" />
				
			</span>
		</div>
		<div class="gap"></div>
		</div>
	
		<div id="wpa_Aband" class="blackbox" style="display:none;">
			<h2><?echo i18n("WPA/WPA2");?></h2>
			<p><?echo i18n("WPA/WPA2 requires stations to use high grade encryption and authentication.");?></p>
			<div class="textinput">
                        <span class="name"><?echo i18n("WPA Mode");?></span>
                        <span class="delimiter">:</span>
                        <span class="value">
                                <select id="wpa_mode_Aband">
                                        <option value="WPA+WPA2">Auto(WPA or WPA2)</option>
                                        <option value="WPA2">WPA2 Only</option>
                                        <option value="WPA">WPA Only</option>
                                </select>
                        </span>
                </div>
			<div class="textinput">
				<span class="name"><?echo i18n("Cipher Type");?></span>
				<span class="delimiter">:</span>
				<span class="value">
					<select id="cipher_type_Aband">
						<option value="TKIP+AES">AUTO(TKIP/AES)</option>
						<option value="TKIP">TKIP</option>
						<option value="AES">AES</option>
					</select>
				</span>
			</div>
			<div class="textinput" style="display:none">
				<span class="name"><?echo i18n("PSK / EAP");?></span>
				<span class="delimiter">:</span>
				<span class="value">
					<select id="psk_eap_Aband" onChange="PAGE.OnChangeWPAAuth('_Aband');">
						<option value="PSK">PSK</option>
						<option value="EAP">EAP</option>
					</select>
				</span>
			</div>
			<div name="psk_Aband" class="textinput">
				<span class="name"><?echo i18n("Network Key");?></span>
				<span class="delimiter">:</span>
				<span class="value"><input id="wpapsk_Aband" type="password" size="40" maxlength="64" /></span>
			</div>
			<div name="psk_Aband" class="textinput">
				<span class="name"></span>
				<span class="delimiter"></span>
				<!--span class="value">(8~62 ASCII or 63 HEX)</span-->
			</div>
			<!--div name="psk_Aband" class="textinput" style="display:none">
				<span class="name"><?echo i18n("Group  Key Update Interval");?></span>
				<span class="delimiter">:</span>
				<span class="value"><input id="wpa_rekey_Aband" type="text" size="8" maxlength="7" />&nbsp;(<?echo i18n("Seconds");?>)</span>
			</div-->
			<input id="wpa_rekey_Aband" type="hidden" size="8" maxlength="7"/>
			<div name="eap_Aband" class="textinput">
				<span class="name"><?echo i18n("RADIUS Server IP Address");?></span>
				<span class="delimiter">:</span>
				<span class="value"><input id="srv_ip_Aband" type="text" size="15" maxlength="15" /></span>
			</div>
			<div name="eap_Aband" class="textinput">
				<span class="name"><?echo i18n("Port");?></span>
				<span class="delimiter">:</span>
				<span class="value"><input id="srv_port_Aband" type="text" size="5" maxlength="5" /></span>
			</div>
			<div name="eap_Aband" class="textinput">
				<span class="name"><?echo i18n("Shared Secret");?></span>
				<span class="delimiter">:</span>
				<span class="value"><input id="srv_sec_Aband" type="password" size="50" maxlength="64" /></span>
			</div>
		</div>
		<div class="blackbox">
		<h2><?echo i18n("Guest Zones Clients Isolation");?></h2>
		<p><?echo i18n("Enable this function to prevent guest clients accessing other guest clients in the Guest Zone.");?></p>
		<div class="textinput">
			<span class="name"><?echo i18n("Enable Guest Zones Clients Isolation");?></span>
			<span class="delimiter">:</span>
			<span class="value"><input id="en_isolation_Aband" type="checkbox" /></span>
		</div>	
	</div>		
	<div class="blackbox">
		<h2><?echo i18n("Routing Between Host Zone And Guest Zone");?></h2>
		<p><?echo i18n("Use this section to enable routing between the Host zone and Guest Zone. Guest clients cannot access Host clients' without enabling this function.");?></p>
		<div class="textinput">
			<span class="name"><?echo i18n("Enable Routing Between Zones");?></span>
			<span class="delimiter">:</span>
			<span class="value"><input id="en_routing_Aband" type="checkbox" /></span>
		</div>
	</div>	
	
</div> <!-- <div id="div_gz_5G_band" >	 -->	
	
<p><input type="button" value="<?echo i18n("Save Settings");?>" onclick="BODY.OnSubmit();" />
	<input type="button" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" />
	<input id="rebootitem2" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
</form>
