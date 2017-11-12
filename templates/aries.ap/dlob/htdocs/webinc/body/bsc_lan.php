<form id="mainform" onsubmit="return false;">
<div class="orangebox">
	<h1><?echo i18n("Network Settings");?></h1>
	<p>
		<?echo i18n("Use this section to configure the internal network settings of your AP or wireless stations to configure the built-in DHCP server to assign IP addresses to computers on your network.");?>
		<?echo i18n("The IP address that is configured here is the IP address that you use to access the Web-based management interface.");?>
		<?echo i18n("If you change the IP address in this section, you may need to adjust your PC's network settings to access the network again.");?>
	</p>
	<p><input type="button" value="<?echo i18n("Save Settings");?>" onClick="BODY.OnSubmit();" />
	<input type="button" value="<?echo i18n("Don't Save Settings");?>" onClick="BODY.OnReload();" /></p>
</div>

<div class="blackbox">
	<h2><?echo i18n("DEVICE NAME");?></h2>
	<div class="textinput">
		<span class="name"><?echo i18n("Device Name");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="device_name" type="text" size="20" maxlength="15" /></span>
	</div>
	<div class="gap"></div>
</div>

<!-- For IPv4-->
<div class="blackbox">
	<h2><?echo i18n("LAN Settings");?></h2>
	<p>
		<?echo i18n("Use this section to configure the internal network settings of your AP or wireless stations.");?>
		<?echo i18n("The IP address that is configured here is the IP address that you use to access the Web-based management interface.");?>
		<?echo i18n("If you change the IP address here, you may need to adjust your PC's network settings to access the network again.");?>
	</p>
	<div class="textinput">
		<span class="name"><?echo i18n("LAN Connection Type");?></span>
		<span class="delimiter">:</span>
		<span class="value">
			<select id="lan_type_v4" onChange="PAGE.OnChangeLANType(this.value);">
				<option value="lan_static">Static IP</option>
				<option value="lan_dynamic">Dynamic IP (DHCP)</option>
			</select>
		</span>
	</div>
	<div class="gap"></div>
</div>

<div class="blackbox" id="ipv4_conn_type">
	<h2><?echo i18n("STATIC IP LAN CONNECTION TYPE");?></h2>
	<p>
		<?echo i18n("Enter the IPv4 address information.");?>
	</p>
	<div class="textinput">
		<span class="name"><?echo i18n("IPv4 Address");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="ipaddr_v4" type="text" size="20" maxlength="15" /></span>
	</div>
	<div class="textinput">
		<span class="name"><?echo i18n("Subnet Mask");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="netmask_v4" type="text" size="20" maxlength="15" /></span>
	</div>
	<div class="textinput">
		<span class="name"><?echo i18n("Default Gateway");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="gateway_v4" type="text" size="20" maxlength="15" /></span>
	</div>
	<div class="textinput">
		<span class="name"><?echo i18n("Primary DNS Server");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="dns1_v4" type="text" size="20" maxlength="15" /></span>
	</div>
	<div class="textinput">
		<span class="name"><?echo i18n("Secondary DNS Server");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="dns2_v4" type="text" size="20" maxlength="15" /></span>
	</div>
	<div class="gap"></div>
</div>
<!-- For IPv4-->

<!-- For IPv6-->
<div class="blackbox">
	<h2><?echo i18n("IPv6 Connection Type");?></h2>
		<p class="strong">
			<?echo i18n("Choose the mode to be used by the access point to connect to the IPv6 Internet.");?>
		</p>
	<div class="textinput">
		<span class="name"><?echo i18n("My IPv6 Connection is ");?></span>
		<span class="delimiter">:</span>
		<span class="value">
			<select id="ipv6_mode" onchange="PAGE.OnChange_ipv6_mode(this.value);">
			<option value="LL"><?echo i18n("Link-local Only");?></option>	
			<option value="STATIC"><?echo i18n("Static IPv6");?></option>
			<option value="AUTO"><?echo i18n("Autoconfiguration(Stateless/DHCPv6)");?></option>
			</select>
		</span>
	</div>
	<div class="gap"></div>
</div>

<div class="blackbox" id="ipv6_linklocal_body">
	<div> 
		<h2><?echo i18n("LAN IPv6 ADDRESS SETTINGS");?></h2>
			<p>
				<?echo i18n("Use the section to configure the internal network settings of your AP or wireless stations.");?>
				<?echo i18n("The LAN IPv6 Link-Local Address is the IPv6 Address that you use to access the Web-based management interface.");?>
			</p>
	</div>
	<div class="gap"></div>
	<div>
		<div class="textinput">
			<span class="name"><?echo i18n("LAN IPv6 Link-Local Address");?></span>
			<span class="delimiter">:</span>
			<span class="value">
				<span id="lan_ll"></span>	
				<span id="lan_ll_pl"></span>
			</span>
		</div>
	</div>
	<div class="gap"></div>
	<div class="gap"></div>
</div>

<div class="blackbox" id="ipv6_static_body">
	<div>
		<h2><?echo i18n("LAN IPv6 ADDRESS SETTINGS");?></h2>
		<p class="strong"><?echo i18n(" Enter the IPv6 address information provided by your Internet Service Provider (ISP).");?> </p>
	</div>
	<div>
		<div class="textinput">
			<span class="name"><?echo i18n("IPv6 Address");?></span>
			<span class="delimiter">:</span>
			<span class="value"><input id="ipaddr_v6" type="text" size="42" maxlength="45" /></span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Subnet Prefix Length");?></span>
			<span class="delimiter">:</span>
			<span class="value"><input id="sprefix_v6" type="text" size="4" maxlength="3" /></span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Default Gateway");?></span>
			<span class="delimiter">:</span>
			<span class="value"><input id="gateway_v6" type="text" size="42" maxlength="45" /></span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Primary DNS Server");?></span>
			<span class="delimiter">:</span>
			<span class="value"><input id="dns1_v6" type="text" size="42" maxlength="45" /></span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Secondary DNS Server");?></span>
			<span class="delimiter">:</span>
			<span class="value"><input id="dns2_v6" type="text" size="42" maxlength="45" /></span>
		</div>
	</div>
	<div class="gap"></div>
</div>

<div class="blackbox" id="ipv6_dhcp_body">
	<div>
		<h2><?echo i18n("IPv6 DNS SETTINGS");?></h2>
		<p class="strong"><?echo i18n("Obtain DNS server address automatically or enter a specific DNS server address.");?> </p>
	</div>
	<div>
		<div class="textinput">
			<span class="name"><input type="radio" id="ipv6_dhcp_dns_auto" value="auto" onclick="PAGE.OnClickIPv6DHCPDNS(this.value);" /></span>
			<span class="value"><strong><?echo i18n("Obtain IPv6 DNS Servers automatically");?></strong></span>
		</div>
		<div class="textinput">
			<span class="name"><input type="radio" id="ipv6_dhcp_dns_manual" value="manual" onclick="PAGE.OnClickIPv6DHCPDNS(this.value);" /></span>
			<span class="value"><strong><?echo i18n("Use the following IPv6 DNS Servers");?></strong></span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Primary DNS Server");?></span>
			<span class="delimiter">:</span>
			<span class="value"><input id="ipv6_dhcp_pdns" type="text" size="42" maxlength="45" /></span>
		</div>
		<div class="textinput">
			<span class="name"><?echo i18n("Secondary DNS Server");?></span>
			<span class="delimiter">:</span>
			<span class="value"><input id="ipv6_dhcp_sdns" type="text" size="42" maxlength="45" /></span>
		</div>
	</div>
	<div class="gap"></div>
</div>
<!-- For IPv6 -->

<p><input type="button" value="<?echo i18n("Save Settings");?>" onClick="BODY.OnSubmit();" />
<input type="button" value="<?echo i18n("Don't Save Settings");?>" onClick="BODY.OnReload();" /></p>

</form>
