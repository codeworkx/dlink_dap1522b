<?
include "/htdocs/webinc/body/draw_elements.php";
include "/htdocs/phplib/wifi.php";
?>
<form id="mainform" onsubmit="return false;">
<div class="orangebox">
	<h1><?echo i18n("Wireless");?></h1>
	<p><?echo i18n("Use this section to configure the wireless settings for your D-Link Acess Point.")." ".
		i18n("Please note that changes made on this section will also need to be duplicated to your wireless clients and PC. ");?></p>
	<p><input type="button" value="<?echo i18n("Save Settings");?>" onClick="BODY.OnSubmit();" />
	<input type="button" value="<?echo i18n("Don't Save Settings");?>" onClick="BODY.OnReload();" />
	<input id="rebootitem1" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
</div>

<!-- ===================== 2.4Ghz, BG band ============================== -->
<div id="div_24G_band" >
	<div class="blackbox">
		<h2><?echo i18n("Wireless Network Settings");?></h2>
		<div class="textinput">
			<span class="name"><?echo i18n("Enable Wireless");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<input id="en_wifi" type="checkbox" onClick="PAGE.OnClickEnWLAN('');" />
				<?
				if ($FEATURE_NOSCH!="1")
				{
					DRAW_select_sch("sch", i18n("Always"), "", "", "0", "narrow");
					echo '<input id="go2sch" type="button" value="'.i18n("Add New").'" onClick="javascript:self.location.href=\'./tools_sch.php\';" />\n';
				}
				?>
			</span>
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
			<span class="name"><?echo i18n("Wireless Band");?></span>
			<span class="delimiter">:</span>
			<span id="wifi_mode" class="value"><b><?echo i18n("2.4GHz");?></b></span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Wireless Mode");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="wlan_mode" onChange="PAGE.OnChangeWLMode('');">
					<option value="n"><?echo i18n("802.11n only");?></option>
					<option value="gn"><?echo i18n("Mixed 802.11n,802.11g");?></option>
					<option value="bgn"><?echo i18n("Mixed 802.11n,802.11g and 802.11b");?></option>
				</select>
			</span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Enable Auto Channel Scan");?></span>
			<span class="delimiter">:</span>
			<span class="value"><input id="auto_ch" type="checkbox" onClick="PAGE.OnClickEnAutoChannel('');" /></span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Wireless Channel");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="channel" onClick="PAGE.OnClickChangeChannel('');">
	<?
		$clist = WIFI_getchannellist();
		$count = cut_count($clist, ",");
		
		$i = 0;
		while($i < $count)
		{
			$ch = cut($clist, $i, ',');
			
			
			echo '\t\t\t\t<option value="'.$ch.'">'.$ch.'</option>\n';
			$i++;
		}
		
	?>			</select>
			</span>
		</div>
		<div class="textinput"  style="display:none">
			<span class="name"><?echo i18n("Transmission Rate");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="txrate">
					<option value="-1"><?echo i18n("Best")." (".i18n("automatic").")";?></option>
				</select>
				(Mbit/s)
			</span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Channel Width");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="bw" onChange="PAGE.OnChangeBW('');">
					<option value="20">20 MHz</option>
					<option value="20+40"><?echo i18n("Auto");?> 20/40 MHz</option>
				</select>
			</span>
		</div>
		<div class="textinput" style="display:none">
			<span class="name"><?echo i18n("WMM Enable");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<input id="en_wmm" type="checkbox" />
				(<?echo i18n("Wireless QoS");?>)
			</span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Visibility Status");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<input id="ssid_visible" name="ssid_hidden" type="radio" value="0" checked="checked" /><?echo i18n("Visible");?>
				<input id="ssid_invisible" name="ssid_hidden" type="radio" value="1" /><?echo i18n("Invisible");?>
			</span>
		</div>
	</div>
	<div class="blackbox">
		<h2><?echo i18n("Wireless Security Mode");?></h2>
		<div class="textinput">
			<span class="name" <? if(query("/runtime/device/langcode")!="en") echo 'style="width: 28%;"';?> ><?echo i18n("Security Mode");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="security_type" onChange="PAGE.OnChangeSecurityType('');">
					<option value=""><?echo i18n("None");?></option>
					<option value="WEP"><?echo i18n("WEP");?></option>
					<option value="WPA_P"><?echo i18n("WPA-Personal");?></option>
					<option value="WPA_E"><?echo i18n("WPA-Enterprise");?></option>
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
			<span class="name"><?echo i18n("WEP Key Length");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="wep_key_len" onChange="PAGE.OnChangeWEPKey('');">
					 <option value="64"><?echo i18n("64Bit(10 hex digits/5 ascii)");?></option>
   				 <option value="128"><?echo i18n("128Bit(26 hex digits/13 ascii)");?></option>
				</select>
				&nbsp;(<?echo i18n("length applies to all keys");?>)
			</span>
		</div>
		<div id="wep_64" class="textinput" style="display:none">
			<span class="name"><?echo i18n("WEP Key 1");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<input id="wep_64_1" name="wepkey_64" type="password" size="15" maxlength="10" />
			</span>
		</div>
		<div id="wep_128" class="textinput" style="display:none">
			<span class="name"><?echo i18n("WEP Key 1");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<input id="wep_128_1" name="wepkey_128" type="password" size="31" maxlength="26" />
			</span>
		</div>
		<div class="textinput">
                        <span class="name"><?echo i18n("Authentication");?></span>
                        <span class="delimiter">:</span>
                        <span class="value">
                                <select id="auth_type">
                                        <option value="OPEN"><?echo i18n("Both");?></option>
                                        <option value="SHARED"><?echo i18n("Shared Key");?></option>
                                </select>
                        </span>
                </div>
		<div class="gap"></div>
	</div>
	<div id="wpa" class="blackbox" style="display:none;">
		<h2><?echo i18n("WPA");?></h2>
		<p>
		<?
			echo i18n("Use <strong>WPA or WPA2</strong> mode to achieve a balance of strong security and best compatibility.");
			echo i18n("This mode uses WPA for legacy clients while maintaining higher security with stations that are WPA2 capable.");
			echo i18n("Also the strongest cipher that the client supports will be used. For best security,Use <strong>WPA2 Only</strong> mode.");
			echo i18n("This mode uses AES(CCMP) cipher and legacy stations are not allowed access with WPA security. For maximum compatibility, use <strong>WPA Only</strong>.");
			echo i18n("This mode uses TKIP cipher. Some gaming and legacy devices work only in this mode.");
		?>
		</p>
		<p>
		<?echo i18n("To achieve better wireless performance use WPA2 Only security mode (or in other words AES cipher).");?>
		</p>
		<div class="textinput">
                        <span class="name"><?echo i18n("WPA Mode");?></span>
                        <span class="delimiter">:</span>
                        <span class="value">
                                <select id="wpa_mode">
                                        <option value="WPA+WPA2"><?echo i18n("Auto(WPA or WPA2)");?></option>
                                        <option value="WPA2"><?echo i18n("WPA2 Only");?></option>
                                        <option value="WPA"><?echo i18n("WPA Only");?></option>
                                </select>
                        </span>
                </div>
		<div class="textinput">
			<span class="name"><?echo i18n("Cipher Type");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="cipher_type" onChange="PAGE.OnChangeCipher('');">
					<option value="TKIP"><?echo i18n("TKIP");?></option>
					<option value="AES"><?echo i18n("AES");?></option>
					<option value="TKIP+AES"><?echo i18n("TKIP and AES");?></option>
				</select>
			</span>
		</div>
		<div class="gap"></div>
	</div>
	
	<div id="pre_shared_key" class="blackbox" style="display:none;">
                <h2><?echo i18n("pre-shared key");?></h2>
		<p>
			<?echo i18n("Enter an 8 to 64 hex or 8 to 63 character alphanumeric pass-phrase.For good security it should be of ample length and should not be a commonly known phrase.");?>
		</p>
		<div id="psk" class="textinput">
                        <span class="name"><?echo i18n("Pre-Shared Key");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="wpapsk" type="password" size="40" maxlength="64" /></span>
                </div>
		<div class="gap"></div>
	</div>
	<div id="eap802.1x" class="blackbox" style="display:none;">
		<h2><?echo i18n("EAP (802.1X)");?></h2>
		<p class="strong">
			<?echo i18n("When WPA enterprise is enabled,the AP uses EAP (802.1x) to authenticate clients via a remote RADIUS server.");?>
		</p>
		<div name="eap" class="textinput">
                        <span class="name"><?echo i18n("RADIUS Server IP Address");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="srv_ip" type="text" size="15" maxlength="15" /></span>
                </div>
                <div name="eap" class="textinput">
                        <span class="name"><?echo i18n("RADIUS Server Port");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="srv_port" type="text" size="5" maxlength="5" /></span>
                </div>
                <div name="eap" class="textinput">
                        <span class="name"><?echo i18n("RADIUS Server Shared Secret");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="srv_sec" type="password" size="50" maxlength="64" /></span>
                </div>
		<div class="emptyline"></div>
		<div class="gap"></div>
	</div>
</div>


<!-- ===================== 5Ghz, A band ============================== -->
<div id="div_5G_band" >
	<div class="blackbox">
		<h2><?echo i18n("Wireless Network Settings");?></h2>
	
		<div class="textinput">
			<span class="name"><?echo i18n("Enable Wireless");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<input id="en_wifi_Aband" type="checkbox" onClick="PAGE.OnClickEnWLAN('_Aband');" />
				<?
				if ($FEATURE_NOSCH!="1")
				{
					DRAW_select_sch("sch_Aband", i18n("Always"), "", "", "0", "narrow");
					echo '<input id="go2sch_Aband" type="button" value="'.i18n("Add New").'" onClick="javascript:self.location.href=\'./tools_sch.php\';" />\n';
				}
				?>
			</span>
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
			<span class="name"><?echo i18n("Wireless Band");?></span>
			<span class="delimiter">:</span>
			<span id="wifi_mode_Aband" class="value"><b><?echo i18n("5GHz");?></b></span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Wireless Mode");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="wlan_mode_Aband" onChange="PAGE.OnChangeWLMode('_Aband');">					
					<option value="n"><?echo i18n("802.11n only");?></option>
					<option value="an"><?echo i18n("Mixed 802.11n, 802.11a");?></option>
				</select>
			</span>
		</div>
		
		<div class="textinput">
			<span class="name"><?echo i18n("Enable Auto Channel Scan");?></span>
			<span class="delimiter">:</span>
			<span class="value"><input id="auto_ch_Aband" type="checkbox" onClick="PAGE.OnClickEnAutoChannel('_Aband');" /></span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Wireless Channel");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="channel_Aband" onClick="PAGE.OnClickChangeChannel('_Aband');">
	<?
		//++++ hendry add for dfs 
		function execute_cmd($cmd)
		{
			fwrite("w","/var/run/exec.sh",$cmd);
			event("EXECUTE");
		}
		
		function setToRuntimeNode($blocked_channel, $timeleft)
		{
			/* find blocked channel if already in runtime node */
			$blocked_chn_total = query("/runtime/dfs/blocked/entry#");
			/* if blocked channel exist before, use the old index. */
			$index = 1;
			while($index <= $blocked_chn_total)
			{
				if($blocked_chn_total == 0) {break;}
				$ch = query("/runtime/dfs/blocked/entry:".$index."/channel");
				if($ch == $blocked_channel)
				{
					break;	
				}
				$index++;
			}
			set("/runtime/dfs/blocked/entry:".$index."/channel",$blocked_channel);
			execute_cmd("xmldbc -t \"dfs-".$blocked_channel.":".$timeleft.":xmldbc -X /runtime/dfs/blocked/entry:".$index."\"");
			//execute_cmd("xmldbc -t \"dfs-".$blocked_channel.":5:xmldbc -X /runtime/dfs/blocked/entry:".$index."\"");
		}
			
		/*0. Update/delete any entry that has expired */
		/*
		$uptime = query("runtime/device/uptime");
		$index = 1;
		
		// delete any expired entry 
		while($index <= $ttl_blocked_chn)
		{
			if($ttl_blocked_chn == 0) {break;}
			$expiry = query("/runtime/dfs/blocked/entry:.".$index."/expiry");
			if($uptime > $expiry)
			{
				// delete this entry
				del("/runtime/dfs/blocked/entry:".$index);
			}
			$index++; 
		}
		*/
		
		/*1. Update new blocked channel to runtime nodes */
		$blockch_list = fread("", "/proc/dfs_blockch");
		//format is : "100,960;122,156;" --> channel 100, remaining time is 960 seconds
		//								 --> channel 122, remaining time is 156 seconds
		$ttl_block_chn = cut_count($blockch_list, ";")-1;
		$i = 0;
		while($i < $ttl_block_chn)
		{
			//assume that blocked channel can be more than one channel.
			$ch_field = cut($blockch_list, $i, ';');	//i mean each "100,960;" represent 1 field 
			$ch = cut ($ch_field, 0, ',');
			$remaining_time = cut ($ch_field, 1, ',');
			
			setToRuntimeNode($ch, $remaining_time);
			$i++;
		}
	
		/*2. Retrieve all blocked dfs channels FROM RUNTIME NODES */
		$ttl_blocked_chn = query("/runtime/dfs/blocked/entry#");
		$ct=1;
		$rchnl_list = "";
		while($ct <= $ttl_blocked_chn)
		{
			if($ttl_blocked_chn == 0) {break;}
			$rchnl = query("/runtime/dfs/blocked/entry:".$ct."/channel");
			$rchnl_list = $rchnl_list.$rchnl.",";
			$ct++;
		}
	
		/*3. Disallow user to select the blocked channels */
		$clist = WIFI_getchannellist("a");
		$count = cut_count($clist, ",");
	
		$i = 0;
		while($i < $count)
		{
			$ch = cut($clist, $i, ',');
			
			/* check all channel for A band whether it is blocked (radar detected) */
			$rct=0;
			$channel_is_blocked = 0;
			while($rct <= $ttl_blocked_chn)
			{
				if($ttl_blocked_chn == 0) {break;}
				$rch = cut($rchnl_list, $rct, ',');
				$rct++;
				if($ch == $rch)
				{
					$channel_is_blocked = 1;
				}
			}
			if($channel_is_blocked=="1")
			{
				echo '\t\t\t\t<option value="'.$ch.'">'.$ch.' (disabled) </option>\n';
			} else
			{
				echo '\t\t\t\t<option value="'.$ch.'">'.$ch.'</option>\n';
			}
			$i++;
		}
	?>			</select>
			</span>
		</div>
		<div class="textinput" style="display:none">
			<span class="name"><?echo i18n("Transmission Rate");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="txrate_Aband">
					<option value="-1"><?echo i18n("Best")." (".i18n("automatic").")";?></option>
				</select>
				(Mbit/s)
			</span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Channel Width");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="bw_Aband" onChange="PAGE.OnChangeBW('_Aband');">
					<option value="20">20 MHz</option>
					<option value="20+40"><?echo i18n("Auto");?> 20/40 MHz</option>
				</select>
			</span>
		</div>
		<div class="textinput" style="display:none">
			<span class="name"><?echo i18n("WMM Enable");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<input id="en_wmm_Aband" type="checkbox" />
				(<?echo i18n("Wireless QoS");?>)
			</span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Visibility Status");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<input id="ssid_visible_Aband" name="ssid_hidden" type="radio" value="1" checked="checked" /><?echo i18n("Visible");?>
				<input id="ssid_invisible_Aband" name="ssid_hidden" type="radio" value="0" /><?echo i18n("Invisible");?>
			</span>
		</div>
		<div class="gap"></div>
	</div>
	<div class="blackbox">
		<h2><?echo i18n("Wireless Security Mode");?></h2>
		<div class="textinput">
			<span class="name" <? if(query("/runtime/device/langcode")!="en") echo 'style="width: 28%;"';?> ><?echo i18n("Security Mode");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="security_type_Aband" onChange="PAGE.OnChangeSecurityType('_Aband');">
					<option value=""><?echo i18n("None");?></option>
					<option value="WEP"><?echo i18n("WEP");?></option>
					<option value="WPA_P"><?echo i18n("WPA-Personal");?></option>
					<option value="WPA_E"><?echo i18n("WPA-Enterprise");?></option>
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
				<p><?echo i18n("If you choose the WEP security option, this device will <strong>ONLY</strong> operate in <strong>Legacy Wireless mode(802.11A)</strong>.")." ".
						i18n("This means you will <strong>NOT</strong> get 11N performance due to the fact that WEP is not supported by theDraft 11N specification.");?></p>
                <div class="textinput">
                        <span class="name"><?echo i18n("WEP Key Length");?></span>
                        <span class="delimiter">:</span>
                        <span class="value">
                                <select id="wep_key_len_Aband" onChange="PAGE.OnChangeWEPKey('_Aband');">
                                        <option value="64"><?echo i18n("64Bit(10 hex digits/5 ascii)");?></option>
                                        <option value="128"><?echo i18n("128Bit(26 hex digits/13 ascii)");?></option>
                                </select>
                                &nbsp;(<?echo i18n("length applies to all keys");?>)
                        </span>
                </div>
                <div id="wep_64_Aband" class="textinput">
                        <span class="name"><?echo i18n("WEP Key 1");?></span>
                        <span class="delimiter">:</span>
                        <span class="value">
                                <input id="wep_64_1_Aband" name="wepkey_64" type="password" size="15"  maxlength="10" />
                        </span>
                </div>
                <div id="wep_128_Aband" class="textinput" style="display:none">
                        <span class="name"><?echo i18n("WEP Key 1");?></span>
                        <span class="delimiter">:</span>
                        <span class="value">
                                <input id="wep_128_1_Aband" name="wepkey_128" type="password" size="31" maxlength="26" />
                        </span>
                </div>
		<div class="textinput">
                        <span class="name"><?echo i18n("Authentication");?></span>
                        <span class="delimiter">:</span>
                        <span class="value">
                                <select id="auth_type_Aband">
                                        <option value="OPEN"><?echo i18n("Both");?></option>
                                        <option value="SHARED"><?echo i18n("Shared Key");?></option>
                                </select>
                        </span>
                </div>
		<div class="gap"></div>
	</div>
	<div id="wpa_Aband" class="blackbox" style="display:none;">
                <h2><?echo i18n("WPA");?></h2>
                <p>
                <?
                        echo i18n("Use <strong>WPA or WPA2</strong> mode to achieve a balance of strong security and best compatibility.");
                        echo i18n("This mode uses WPA for legacy clients while maintaining higher security with stations that are WPA2 capable.");
                        echo i18n("Also the strongest cipher that the client supports will be used. For best security,Use <strong>WPA2 Only</strong> mode.");
                        echo i18n("This mode uses AES(CCMP) cipher and legacy stations are not allowed access with WPA security. For maximum compatibility, use <strong>WPA Only</strong>.");
                        echo i18n("This mode uses TKIP cipher. Some gaming and legacy devices work only in this mode.");
                ?>
                </p>
                <p>
                <?echo i18n("To achieve better wireless performance use WPA2 Only security mode (or in other words AES cipher).");?>
                </p>
                <div class="textinput">
                        <span class="name"><?echo i18n("WPA Mode");?></span>
                        <span class="delimiter">:</span>
                        <span class="value">
                                <select id="wpa_mode_Aband">
                                        <option value="WPA+WPA2"><?echo i18n("Auto(WPA or WPA2)");?></option>
                                        <option value="WPA2"><?echo i18n("WPA2 Only");?></option>
                                        <option value="WPA"><?echo i18n("WPA Only");?></option>
                                </select>
                        </span>
                </div>
                <div class="textinput">
                        <span class="name"><?echo i18n("Cipher  Type");?></span>
                        <span class="delimiter">:</span>
                        <span class="value">
                                <select id="cipher_type_Aband" onChange="PAGE.OnChangeCipher('_Aband');">
                                        <option value="TKIP"><?echo i18n("TKIP");?></option>
                                        <option value="AES"><?echo i18n("AES");?></option>
                                        <option value="TKIP+AES"><?echo i18n("TKIP and AES");?></option>
                                </select>
                        </span>
                </div>
                <div class="gap"></div>
        </div>
	<div id="pre_shared_key_Aband" class="blackbox" style="display:none;">
                <h2><?echo i18n("pre-shared key");?></h2>
                <p>
                        <?echo i18n("Enter an 8 to 64 hex or 8 to 63 character alphanumeric pass-phrase.For good security it should be of ample length and should not be a commonly known phrase.");?>
                </p>
                <div id="psk" class="textinput">
                        <span class="name"><?echo i18n("Pre-Shared Key");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="wpapsk_Aband" type="password" size="40" maxlength="64" /></span>
                </div>
                <div class="gap"></div>
        </div>
	<div id="eap802.1x_Aband" class="blackbox" style="display:none;">
                <h2><?echo i18n("EAP (802.1X)");?></h2>
                <p class="strong">
                        <?echo i18n("When WPA enterprise is enabled,the AP uses EAP (802.1x) to authenticate clients via a remote RADIUS server.");?>
                </p>
                <div name="eap" class="textinput">
                        <span class="name"><?echo i18n("RADIUS Server IP Address");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="srv_ip_Aband" type="text" size="15" maxlength="15" /></span>
                </div>
                <div name="eap" class="textinput">
                        <span class="name"><?echo i18n("RADIUS Server Port");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="srv_port_Aband" type="text" size="5" maxlength="5" /></span>
                </div>
                <div name="eap" class="textinput">
                        <span class="name"><?echo i18n("RADIUS Server Shared Secret");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="srv_sec_Aband" type="password" size="50" maxlength="64" /></span>
                </div>
                <!--input id="eap_advanced" type="button" value="<?echo i18n("Advanced");?>" onClick="PAGE.OnClickAdvanced('');" /-->

                <p id="second_desc" style="display:none">
                        <?echo i18n("Optional backup RADIUS server:");?>
                </p>
                <div id="second_ip" name="eap_back" class="textinput" style="display:none">
                        <span class="name"><?echo i18n("Second RADIUS Server IP");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="srv_ip" type="text" size="15" maxlength="15" /></span>
                </div>
                <div id="second_port" name="eap_back" class="textinput" style="display:none">
                        <span class="name"><?echo i18n("Second RADIUS Server Port");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="srv_port" type="text" size="5" maxlength="5" /></span>
                </div>
                <div id="second_secret" name="eap_back" class="textinput" style="display:none">
                        <span class="name"><?echo i18n("Second RADIUS Server Shared Secret");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="srv_sec" type="password" size="50" maxlength="64" /></span>
                </div>
                <div class="emptyline"></div>
                <div class="gap"></div>
        </div>
</div> 

<div id="div_station" >
	<div class="blackbox">
		<h2><?echo i18n("Wireless Network Settings");?></h2>
		<div class="textinput">
			<span class="name"><?echo i18n("Enable Wireless");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<input id="en_wifi_sta" type="checkbox" onClick="PAGE.OnClickEnWLANsta();" />
				<?
				if ($FEATURE_NOSCH!="1")
				{
					DRAW_select_sch("sch_sta", i18n("Always"), "", "", "0", "narrow");
					echo '<input id="go2sch_sta" type="button" value="'.i18n("Add New").'" onClick="javascript:self.location.href=\'./tools_sch.php\';" />\n';
				}
				?>
			</span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Wireless Mode");?></span>
			<span class="delimiter">:</span>
			<span id="sta_wifi_mode" class="value">
				<b><?echo i18n("Bridge Mode");?></b>
				<input id="btn_site_survey_sta" type="button" value="<?echo i18n("Site Survey");?>" onClick="PAGE.OnClickSiteSurvey('');" />
			</span>
		</div>	
			
		<div class="textinput">
			<span class="name"><?echo i18n("Wireless Network Name");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<input id="ssid_sta" type="text" size="20" maxlength="32" />
				(<?echo i18n("Also called the SSID");?>)
			</span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Channel Width");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="bw_sta" onChange="PAGE.OnChangeBW('_sta');">
					<option value="20">20 MHz</option>
					<option value="20+40"><?echo i18n("Auto");?> 20/40 MHz</option>
				</select>
			</span>
		</div>
		<div class="gap"></div>
	</div>
	
	<!-- MAC Clone start -->
<div id="div_MACClone" class="blackbox">
		<h2><?echo i18n("Wireless MAC Clone");?></h2>
		<div class="textinput">
			<span class="name"><?echo i18n("Enable");?></span>
			<span class="delimiter">:</span>
			<span class="value"><input id="en_mac_clone" type="checkbox" onClick="PAGE.OnClickEnMacClone();" /></span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("MAC Source");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="mac_source_type" onchange="PAGE.OnChangeMacSourceType();">
					<option value="AUTO"><?echo i18n("Auto");?></option>
					<option value="MANUAL"><?echo i18n("Manual");?></option>
				</select>
			</span>
        </div>
		<div class="textinput">
			<span class="name"><?echo i18n("MAC Address");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<input type=text id="mac1" size=2 maxlength=2>&nbsp;:
				<input type=text id="mac2" size=2 maxlength=2>&nbsp;:
				<input type=text id="mac3" size=2 maxlength=2>&nbsp;:
				<input type=text id="mac4" size=2 maxlength=2>&nbsp;:
				<input type=text id="mac5" size=2 maxlength=2>&nbsp;:
				<input type=text id="mac6" size=2 maxlength=2>
			</span>
		</div>	
		<br>
		<br>
		<div class="centerline">
			<input id="mac_clone_scan" type="button" value="<? echo i18n("Scan");?>" onClick="PAGE.OnScanMAC();" />
		</div>
		<br>
		<div class="centerline">
			<table id="ScanMACTable" align="center" rules="none" border="1">
				<tr>
					<th width="20px"></th>
					<th width="100px"><? echo i18n("Port");?></th>
					<th width="200px"><? echo i18n("MAC Address");?></th>
				</tr>			
			</table>
		</div>
		<br>	
</div>
<!-- MAC Clone end -->
	
	<div class="blackbox">
		<h2><?echo i18n("Wireless sta Security Mode");?></h2>
		<div class="textinput">
			<span class="name" <? if(query("/runtime/device/langcode")!="en") echo 'style="width: 28%;"';?> ><?echo i18n("Security Mode");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<select id="security_type_sta" onChange="PAGE.OnChangeSecurityType('_sta');">
					<option value=""><?echo i18n("None");?></option>
					<option value="WEP"><?echo i18n("WEP");?></option>
					<option value="WPA_P"><?echo i18n("WPA-Personal");?></option>
					<!--option value="WPA_E"><?echo i18n("WPA-Enterprise");?></option-->
				</select>
			</span>
		</div>
		<div class="gap"></div>
	</div>
	<div id="wep_sta" class="blackbox" style="display:none;">
		<h2><?echo i18n("WEP");?></h2>
                <p><?echo i18n("WEP is the wireless encryption standard.")." ".
                        i18n("To use it you must enter the same key(s) into the AP and the wireless client.")." ".
                        i18n("For 64-bit keys you must enter 10 hex digits into each key box.")." ".
                        i18n("For 128-bit keys you must enter 26 hex digits into each key box. ")." ".
                        i18n("A hex digit is either a number from 0 to 9 or a letter from A to F.")." ".
                        i18n('For the most secure use of WEP set the authentication type to "Shared Key" when WEP is enabled.');?></p>
                <p><?echo i18n("You may also enter any text string into a WEP key box, in which case it will be converted into a hexadecimal key using the ASCII values of the characters.")." ".
                        i18n("A maximum of 5 text characters can be entered for 64-bit keys, and a maximum of 13 characters for 128-bit keys.");?></p>
				<p><?echo i18n("If you choose the WEP security option, this device will <strong>ONLY</strong> operate in <strong>Legacy Wireless mode(802.11A/B/G)</strong>.")." ".
						i18n("This means you will <strong>NOT</strong> get 11N performance due to the fact that WEP is not supported by theDraft 11N specification.");?></p>
                <div class="textinput">
                        <span class="name"><?echo i18n("WEP Key Length");?></span>
                        <span class="delimiter">:</span>
                        <span class="value">
                                <select id="wep_key_len_sta" onChange="PAGE.OnChangeWEPKey('_sta');">					
                                         <option value="64"><?echo i18n("64Bit(10 hex digits/5 ascii)");?></option>
                                        <option value="128"><?echo i18n("128Bit(26 hex digits/13 ascii)");?></option>
                                </select>
                                &nbsp;(<?echo i18n("length applies to all keys");?>)
                        </span>
                </div>
                <div id="wep_64_sta" class="textinput">
                        <span class="name"><?echo i18n("WEP Key 1");?></span>
                        <span class="delimiter">:</span>
                        <span class="value">
                                <input id="wep_64_1_sta" name="wepkey_64" type="password" size="15" maxlength="10" />
                        </span>
                </div>              
                <div id="wep_128_sta" class="textinput" style="display:none">
                        <span class="name"><?echo i18n("WEP Key 1");?></span>
                        <span class="delimiter">:</span>
                        <span class="value">
                                <input id="wep_128_1_sta" name="wepkey_128" type="password" size="31" maxlength="26" />
                        </span>
                </div>
		<div class="textinput">
                        <span class="name"><?echo i18n("Authentication");?></span>
                        <span class="delimiter">:</span>
                        <span class="value">
                                <select id="auth_type_sta">
                                        <option value="OPEN"><?echo i18n("Both");?></option>
                                        <option value="SHARED"><?echo i18n("Shared Key");?></option>
                                </select>
                        </span>
                </div>
                <div class="gap"></div>
	</div>
	<div id="wpa_sta" class="blackbox" style="display:none;">
                <h2><?echo i18n("WPA");?></h2>
                <p>
                <?
                        echo i18n("Use <strong>WPA or WPA2</strong> mode to achieve a balance of strong security and best compatibility.");
                        echo i18n("This mode uses WPA for legacy clients while maintaining higher security with stations that are WPA2 capable.");
                        echo i18n("Also the strongest cipher that the client supports will be used. For best security,Use <strong>WPA2 Only</strong> mode.");
                        echo i18n("This mode uses AES(CCMP) cipher and legacy stations are not allowed access with WPA security. For maximum compatibility, use <strong>WPA Only</strong>.");
                        echo i18n("This mode uses TKIP cipher. Some gaming and legacy devices work only in this mode.");
                ?>
                </p>
                <p>
                <?echo i18n("To achieve better wireless performance use WPA2 Only security mode (or in other words AES cipher).");?>
                </p>
                <div class="textinput">
                        <span class="name"><?echo i18n("WPA Mode");?></span>
                        <span class="delimiter">:</span>
                        <span class="value">
                                <select id="wpa_mode_sta">
                                        <option value="WPA+WPA2"><?echo i18n("Auto(WPA or WPA2)");?></option>
                                        <option value="WPA2"><?echo i18n("WPA2 Only");?></option>
                                        <option value="WPA"><?echo i18n("WPA Only");?></option>
                                </select>
                        </span>
                </div>
                <div class="textinput">
                        <span class="name"><?echo i18n("Cipher Type");?></span>
                        <span class="delimiter">:</span>
                        <span class="value">
                                <select id="cipher_type_sta" onChange="PAGE.OnChangeCipher('_sta');">
                                        <option value="TKIP"><?echo i18n("TKIP");?></option>
                                        <option value="AES"><?echo i18n("AES");?></option>
                                        <option value="TKIP+AES"><?echo i18n("TKIP and AES");?></option>
                                </select>
                        </span>
                </div>
                <div class="gap"></div>
        </div>
	<div id="pre_shared_key_sta" class="blackbox" style="display:none;">
                <h2><?echo i18n("pre-shared key");?></h2>
                <p>
                        <?echo i18n("Enter an 8 to 64 hex or 8 to 63 character alphanumeric pass-phrase.For good security it should be of ample length and should not be a commonly known phrase.");?>
                </p>
                <div id="psk" class="textinput">
                        <span class="name"><?echo i18n("Pre-Shared Key");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="wpapsk_sta" type="password" size="40" maxlength="64" /></span>
                </div>
                <div class="gap"></div>
        </div>
	<div id="eap802.1x_sta" class="blackbox" style="display:none;">
                <h2><?echo i18n("EAP (802.1X)");?></h2>
                <p class="strong">
                        <?echo i18n("When WPA enterprise is enabled,the AP uses EAP (802.1x) to authenticate clients via a remote RADIUS server.");?>
                </p>
                <div name="eap" class="textinput">
                        <span class="name"><?echo i18n("RADIUS Server IP Address");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="srv_ip_sta" type="text" size="15" maxlength="15" /></span>
                </div>
                <div name="eap" class="textinput">
                        <span class="name"><?echo i18n("RADIUS Server Port");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="srv_port_sta" type="text" size="5" maxlength="5" /></span>
                </div>
                <div name="eap" class="textinput">
                        <span class="name"><?echo i18n("RADIUS Server Shared Secret");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="srv_sec_sta" type="password" size="50" maxlength="64" /></span>
                </div>
                <!--input id="eap_advanced" type="button" value="<?echo i18n("Advanced");?>" onClick="PAGE.OnClickAdvanced('');" /-->

                <p id="second_desc" style="display:none">
                        <?echo i18n("Optional backup RADIUS server:");?>
                </p>
                <!--div id="second_ip" name="eap_back" class="textinput" style="display:none">
                        <span class="name"><?echo i18n("Second RADIUS Server IP");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="srv_ip" type="text" size="15" maxlength="15" /></span>
                </div>
                <div id="second_port" name="eap_back" class="textinput" style="display:none">
                        <span class="name"><?echo i18n("Second RADIUS Server Port");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="srv_port" type="text" size="5" maxlength="5" /></span>
                </div>
                <div id="second_secret" name="eap_back" class="textinput" style="display:none">
                        <span class="name"><?echo i18n("Second RADIUS Server Shared Secret");?></span>
                        <span class="delimiter">:</span>
                        <span class="value"><input id="srv_sec" type="password" size="50" maxlength="64" /></span>
                </div-->
		<div class="emptyline"></div>
                <div class="gap"></div>
        </div>
</div>

<!-- WPS start -->
<div id="div_WPS" class="blackbox">
	<h2><?echo i18n("Wi-Fi Protected Setup(Also called WCN 2.0 In Windows Vista)");?></h2>
	<div class="textinput">
		<span class="name"><?echo i18n("Enable");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="en_wps" type="checkbox" onClick="PAGE.OnClickEnWPS();" /></span>
	</div>
	<!--div class="textinput">
		<span class="name"><?echo i18n("WiFi Protected Setup");?></span>
		<span class="delimiter">:</span>
		<span id="wifi_info_str" class="value"></span>
	</div>
	<div class="textinput">
		<span class="name"></span>
		<span class="delimiter"></span>
		<span class="value">
			<input id="reset_cfg" type="button" value="<?echo i18n("Reset to Unconfigured");?>"
				onClick="PAGE.OnClickResetCfg();" />
		</span>
	</div-->
	<div class="textinput">
		<span class="name"><?echo i18n("Current PIN");?></span>
		<span class="delimiter">:</span>
		<span id="pin" class="value"></span>
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
	<BR>
	<div class="textinput">
		<span class="name"></span>
		<span class="delimiter"></span>
		<span class="value">
			<input id="pro_wps" type="button" value="<?echo i18n("Process WPS");?>" onClick="javascript:self.location.href='./wiz_wps_br.php';" />
				
		</span>
	</div>
	<div class="emptyline"></div>
</div>
<!-- WPS end -->

<p><input type="button" value="<?echo i18n("Save Settings");?>" onClick="BODY.OnSubmit();" />
<input type="button" value="<?echo i18n("Don't Save Settings");?>" onClick="BODY.OnReload();" />
<input id="rebootitem2" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>

</form>
