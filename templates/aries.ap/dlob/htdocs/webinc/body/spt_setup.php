<div class="orangebox">
	<h1><?echo i18n("Setup Help");?></h1>
	<ul>
		<li><a href="#Internet"><?echo i18n("Setup Wizard");?></a></li>
		<li><a href="#Wireless"><?echo i18n("Wireless Settings");?></a></li>
		<li><a href="#Network"><?echo i18n("LAN Settings");?></a></li>		
	</ul>
</div>
<div class="blackbox">
	<h2><a name="Internet"><?echo i18n("Setup Wizard");?></a></h2>
	<p><?
		echo i18n('If you want to connect a new wireless network, click on "Setup Wizard" and the bridge will guide you through a few steps to get your network up and running.');
	?></p>
<!--	<dl>
		<dt><?echo i18n("Internet Connection Setup Wizard");?></dt>
		<dd><?
			echo i18n("Click this button to have the access point walk you through a few simple steps to help you connect your access point to the Internet.");
		?></dd>				
	</dl>-->
	<dt><strong><?echo i18n("PBC Settings");?></strong></dt>
	<dd>
		<p><?echo i18n("Push Button Configuration is a button that can be pressed to add the device to an existing network . A virtual button can be used on the utility while a physical button is placed on the side of the device.");?></p>
	</dd>	
	<dt><strong><?echo i18n("PIN Settings");?></strong></dt>
	<dd>
		<p><?echo i18n('A PIN is a unique number that can be used to add the device to an existing network.');?></p>
	</dd>	
	<dt><strong><?echo i18n("Set Up Wireless");?></strong></dt>
	<dd>
		<p><?echo i18n("This Wizard helps you add wireless devices to the wireless network.");?></p>
		<p><?
			echo i18n("The wizard will either display the wireless network settings to guide you through manual configuration, prompt you to enter the PIN for the device, or ask you to press the configuration button on the device. If the device supports Wi-Fi Protected Setup and has a configuration button, you can add it to the network by pressing the configuration button on the device and then the on the access point within 120 seconds. The status LED on the access point will flash three times if the device has been successfully added to the network.");
		?></p>
		<p><?
			echo i18n('There are several ways to add a wireless device to your network. Access to the wireless network is controlled by a "registrar". A registrar only allows devices onto the wireless network if you have entered the PIN, or pressed a special Wi-Fi Protected Setup button on the device. The access point acts as a registrar for the network, although other devices may act as a registrar as well.');
		?></p>
		<dl>
			<dt><strong><?echo i18n("Set Up Wireless Wizard");?></strong></dt>
			<dd><?echo i18n("Start the wizard.");?></dd>
		</dl>
	</dd>				
</div>
<div class="blackbox">
	<h2><a name="Wireless"><?echo i18n("Wireless Settings");?></a></h2>
	<p><?
		echo i18n('The Wireless Settings page contains the settings for the (Access Point) Portion of the access point. This page allows you to customize your wireless network or configure the access point to fit an existing wireless network that you may already have setup.');
	?></p>
	<dl>
		<!--
		<dt><?echo i18n('Wi-Fi Protected Setup (Also called WCN 2.0 in Windows Vista)');?></dt>
		<dd><?
			echo i18n("This feature provides users a more intuitive way of setting up wireless security. It is available in two formats: PIN number and Push button. Enter the PIN number that comes with the device in the wireless card utility or Windows Vista's wireless client utility if the wireless card has a certified Windows Vista driver to automatically set up wireless security between the access point and the client. The wireless card will have to support Wi-Fi Protected Setup in either format in order to take advantage of this feature.");
		?></dd>
		-->
		<dt><?echo i18n('Wireless Network Name');?></dt>
		<dd><?
			echo i18n('Also known as the SSID (Service Set Identifier), this is the name of your Wireless Local Area Network (WLAN). This can be easily changed to establish a new wireless network or to add the access point to an existing wireless network.');
		?></dd>
		<?
		if ($FEATURE_NOSCH!="1")
		{
			echo '<dt>'.i18n("Schedule").'</dt>\n';
			echo '<dd>'.i18n("Select a schedule for when the service will be enabled.").'\n';
			echo i18n("If you do not see the schedule you need in the list of schedules, go to the <a href='adv_sch.php'> ADVANCED --> Schedule</a> screen and create a new schedule.").'</dd>\n';
		}
		?>
<? if(query("/runtime/device/switchmode") =="APCLI")	{echo "<!--";} ?>
		<dt><?echo i18n('Enable Auto Channel Selection');?></dt>
		<dd><?
			echo i18n('Enable Auto Channel Selection let the access point can select the best possible channel for your wireless network to operate on.');
		?></dd>		
		<dt><?echo i18n('Wireless Channel');?></dt>
		<dd><?
			echo i18n('Indicates which channel the access point is operating on. By default the channel is set to 6. This can be changed to fit the channel setting for an existing wireless network or to customize your new wireless network. Click the Enable Auto Scan checkbox to have the access point automatically select the channel that it will operate on. This option is recommended because the access point will choose the channel with the least amount of interference.');
		?></dd>
		<dt><?echo i18n("Transmission (TX) Rates");?></dt>
		<dd><?
			echo i18n("Select the basic transfer rates based on the speed of wireless adapters on the WLAN (wireless local area network).");
		?></dd>
		<dt><?echo i18n('Wireless Mode');?></dt>
		<dd><?
			echo i18n('If all of the wireless devices in your wireless network can connect in the same transmission mode, you can improve performance slightly by choosing the appropriate "Only" mode. If you have some devices that use a different transmission mode, choose the appropriate "Mixed" mode.');
		?></dd>
<? if(query("/runtime/device/switchmode") =="APCLI")	{echo "-->";} ?>
		<dt><?echo i18n('Band Width');?></dt>
		<dd><?
			echo i18n('The "Auto 20/40 MHz" option is usually best. The other options are available for special circumstances.');
		?></dd>
<? if(query("/runtime/device/switchmode") =="APCLI")	{echo "<!--";} ?>
		<dt><?echo i18n('WMM');?></dt>
		<dd><?
			echo i18n('Select Enable to turn on QoS for the wireless interface of the access point.');
		?></dd>
		<dt><?echo i18n('Enable Hidden Wireless');?></dt>
		<dd><?
			echo i18n('Select Enabled if you would not like the SSID of your wireless network to be broadcasted by the access point. If this option is Enabled, the SSID of the access point will not be seen by Site Survey utilities, so when setting up your wireless clients, you will have to know the SSID of your access point and enter it manually in order to connect to the access point. This option is enabled by default.');
		?></dd>
<? if(query("/runtime/device/switchmode") =="APCLI")	{echo "-->";} ?>
		<dt><?echo i18n('Wireless Security Mode');?></dt>
		<dd><?
			echo i18n('Securing your wireless network is important as it is used to protect the integrity of the information being transmitted over your wireless network. The access point is capable of 2 types of wireless security; WEP and WPA/WPA2 (auto-detect)');
			?>
			<dl>
				<dt><?echo i18n('WEP');?></dt>
				<dd><?
					echo i18n('Wired Equivalent Protocol (WEP) is a wireless security protocol for Wireless Local Area Networks (WLAN). WEP provides security by encrypting the data that is sent over the WLAN. The access point supports 2 levels of WEP Encryption: 64-bit and 128-bit. WEP is disabled by default. The WEP setting can be changed to fit an existing wireless network or to customize your wireless network.');
					?>
					<dl>
						<dt><?echo i18n('Authentication');?></dt>
						<dd><?
						echo i18n('Authentication is a process by which the access point verifies the identity of a network device that is attempting to join the wireless network. There are two types authentication for this device when using WEP.');
						?></dd>
						<dl>
							<dt><?echo i18n('Open System');?></dt>
							<dd><?
								echo i18n('Select this option to allow all wireless devices to communicate with the access point before they are required to provide the encryption key needed to gain access to the network.');
							?></dd>
							<dt><?echo i18n('Shared Key');?></dt>
							<dd><?
								echo i18n('Select this option to require any wireless device attempting to communicate with the access point to provide the encryption key needed to access the network before they are allowed to communicate with the access point.');
							?></dd>
						</dl>
						<dt><?echo i18n('WEP Encryption');?></dt>
						<dd><?
							echo i18n('Select the level of WEP Encryption that you would like to use on your network. The two supported levels of WEP encryption are 64-bit and 128-bit.');
						?></dd>
						<dt><?echo i18n('Key Type');?></dt>
						<dd><?
							echo i18n('The Key Types that are supported by the access point are HEX (Hexadecimal) and ASCII (American Standard Code for Information Interchange.) The Key Type can be changed to fit an existing wireless network or to customize your wireless network.');
						?></dd>
						<dt><?echo i18n('Keys');?></dt>
						<dd><?
							echo i18n('Keys 1-4 allow you to easily change wireless encryption settings to maintain a secure network. Simply select the specific key to be used for encrypting wireless data on the network.');
						?></dd>
					</dl>
				</dd>
				<dt><?echo i18n('WPA/WPA2');?></dt>
				<dd><?
					echo i18n('Wi-Fi Protected Access (2) authorizes and authenticates users onto the wireless network. WPA(2) uses stronger security than WEP and is based on a key that changes automatically at regular intervals.');
					?>
					<dl>
						<dt><?echo i18n('Cipher Type');?></dt>
						<dd><?
							echo i18n('The access point supports two different cipher types when WPA is used as the Security Type. These two options are TKIP (Temporal Key Integrity Protocol) and AES (Advanced Encryption Standard).');
						?></dd>
						<dt><?echo i18n('PSK/EAP');?></dt>
						<dd><?
							echo i18n('When PSK is selected, your wireless clients will need to provide a Passphrase for authentication. When EAP is selected, you will need to have a RADIUS server on your network which will handle the authentication of all your wireless clients.');
						?></dd>
						<dt><?echo i18n('Network Key');?></dt>
						<dd><?
							echo i18n('This is what your wireless clients will need in order to communicate with your access point, When PSK is selected enter 8-63 alphanumeric characters. Be sure to write this Passphrase down as you will need to enter it on any other wireless devices you are trying to add to your network.');
						?></dd>
						<dt><?echo i18n('RADIUS server');?></dt>
						<dd><?
							echo i18n('This means that WPA authentication will be used in conjunction with a RADIUS server that must be present on your network. Enter the IP address, port, and Shared Secret that your RADIUS is configured for. You also have the option to enter information for a second RADIUS server in the event that there are two on your network that you are using to authenticate wireless clients.');
						?></dd>
					</dl>
				</dd>
			</dl>
		</dd>
<? if(query("/runtime/device/switchmode") =="APCLI")	{echo "<!--";} ?>
		<dt><strong><?echo i18n("Wi-Fi Protected Setup");?></strong></dt>
		<dd>
			<dl>
				<dt><strong><?echo i18n("Enable");?></strong></dt>
				<dd><?echo i18n("Enable the Wi-Fi Protected Setup feature.");?></dd>
			</dl>
			<dl>
				<dt><strong><?echo i18n("PIN Settings");?></strong></dt>
				<dd><?
					echo i18n('A PIN is a unique number that can be used to add the access point to an existing network or to create a new network. The default PIN may be printed on the bottom of the access point. For extra security, a new PIN can be generated. You can restore the default PIN at any time. Only the Administrator ("admin" account) can change or reset the PIN.');
				?></dd>
				<dl>
					<dt><strong><?echo i18n("PIN");?></strong></dt>
					<dd><?echo i18n("Shows the current value of the access point's PIN.");?></dd>
					<dt><strong><?echo i18n("Reset PIN to Default");?></strong></dt>
					<dd><?echo i18n("Restore the default PIN of the access point.");?></dd>
					<dt><strong><?echo i18n("Generate New PIN");?></strong></dt>
					<dd><?
						echo i18n("Create a random number that is a valid PIN. This becomes the access point's PIN. You can then copy this PIN to the user interface of the registrar.");
					?></dd>
				</dl>	
			</dl>
		</dd>
<? if(query("/runtime/device/switchmode") =="APCLI")	{echo "-->";} ?>
<? if(query("/runtime/device/switchmode") !="APCLI")	{echo "<!--";} ?>
		<dt><?echo i18n('Wireless Mac Cloning');?></dt>
		<dl>
			<dt><?echo i18n('Enable');?></dt>
			<dd><?
				echo i18n('If MAC clone function is enabled, before the device forwards a packet which is generated by an Ethernet port station, the source MAC address of the packet will be replaced by the clone MAC address; Otherwise, the packet source MAC address will not be changed.');
			?></dd>
			<dt><?echo i18n('Mac Source');?></dt>
			<dd><?
				echo i18n('Auto or manually select the clone MAC address.');
			?></dd>
			<dt><?echo i18n('Mac Address');?></dt>
			<dd><?
				echo i18n('The mac address selected by mac clone function.');
			?></dd>
			<dt><?echo i18n('Scan');?></dt>
			<dd><?
				echo i18n('The Scan button will help to discover the MAC address of all ethernet port stations.');
			?></dd>
		</dl>
<? if(query("/runtime/device/switchmode") !="APCLI")	{echo "-->";} ?>
	</dl>
</div>
<div class="blackbox">
	<h2><a name="Network" name="Network"><?echo i18n("LAN Settings");?></a></h2>
	<dl>
		<dt><?echo i18n('LAN Connection Type');?></dt>
		<dd><?
			echo i18n('Select DHCP to get the IP settings from a DHCP server on your network. Select Static to use the IP settings specified on this page.');
			?>
			<dl>
			<p><?
				echo '<strong>'.i18n('IP Address').'</strong><br/>';
				echo i18n('The IP address of your access point on the local area network. Your local area network settings are based on the address assigned here. For example, 192.168.0.1.');
			?></p>
			<p><?
				echo '<strong>'.i18n('Subnet Mask').'</strong><br/>';
				echo i18n('The subnet mask of your access point on the local area network.');
			?></p>
			<p><?
				echo '<strong>'.i18n('Gateway').'</strong><br/>';
				echo i18n('Specify the gateway IP address of the local network.');
			?></p>		
			<p><?
				echo '<strong>'.i18n('Local Domain Name').'</strong> ('.i18n('optional').')<br/>';
				echo i18n('Enter in the local domain name for your network.');
			?></p>			
			</dl>			 
		</dd>
		<dt><? echo i18n('Device Name (NetBIOS Name)'); ?></dt>
		<dd>
			<? echo I18N("h","Device Name(NetBIOS NAME) allows you to configure this deice more easily when your network using TCP/IP protocol. You can enter the device name of the AP, instead of IP address, into your web browser to access for configuration. Recommend to change the device name if there's more than one D-Link devices within the subnet."); ?>
			</dl>			 
		</dd>		
		<dt><strong><?echo i18n("IPv6 Connection Type");?> </strong></dt>
		<dd>
			<?
			echo i18n('There are several connection types to choose from: Link-local, Static IPv6, Autoconfiguration(Stateless/DHCPv6). If you are unsure of your connection method, please contact your IPv6 Internet Service Provider.');
			?>
			<dl>
			<p><?
				echo '<strong>'.i18n('Link-local Only').'</strong><br/>';
				echo i18n("The Link-local address is used by nodes and device when communicating with neighboring nodes on the same link. This mode enables IPv6-capable devices to communicate with each other on the LAN side.");
			?></p>
			<p><?
				echo '<strong>'.i18n('Static IPv6 Mode').'</strong><br/>';
				echo i18n("This mode is used when your ISP provides you with a set IPv6 addresses that does not change. The IPv6 information is manually entered in your IPv6 configuration settings. You must enter the IPv6 address, Subnet Prefix Length, Default Gateway, Primary DNS Server, and Secondary DNS Server. Your ISP provides you with all this information. ");
			?></p>		
			<p><?
				echo '<strong>'.i18n('Stateless Mode').'</strong><br/>';
				echo i18n("This method allows a host to generate its own address using a combination of locally available information and information advertised by access point.");
			?></p>		
			<p><?
				echo '<strong>'.i18n('DHCPv6 Mode').'</strong><br/>';
				echo i18n("This is a method of connection where the ISP assigns your IPv6 address when your device requests one from the ISP's server.");
			?></p>			
		</dd>
	</dl>
</div>
