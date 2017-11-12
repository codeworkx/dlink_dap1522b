<div class="orangebox">
	<h1><?echo i18n("Advanced Help");?></h1>
	<ul>		
<? if(query("/runtime/device/switchmode") =="APCLI")	{echo "<!--";} ?>
		<li><a href="#NetFilter"><?echo i18n("MAC Address Filter");?></a></li>					
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "-->";} ?>
		<li><a href="#Network"><?echo i18n("Advanced Wireless");?></a></li>		
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "<!--";} ?>
		<li><a href="#Gzone"><?echo i18n("Guest Zone");?></a></li>
		<li><a href="#DhcpServer"><?echo i18n("DHCP Server");?></a></li>
		<li><a href="#Qos"><?echo i18n("QoS");?></a></li>
		<li><a href="#TraMgr"><?echo i18n("Traffic Manager");?></a></li>
		<li><a href="#WPS"><?echo i18n("Wi-Fi Protected Setup");?></a></li>
		<li><a href="#UserLimit"><?echo i18n("User Limit");?></a></li>
<? if(query("/runtime/device/switchmode") =="APCLI")	{echo "-->";} ?>
	</ul>
</div>
<? if(query("/runtime/device/switchmode") =="APCLI")	{echo "<!--";} ?>
<div class="blackbox">
	<h2><a name="NetFilter"><?echo i18n("MAC Address Filter");?></a></h2>
	<p><?
		echo i18n('Use MAC Filters to deny computers from accessing the network services via access point. You can either manually add a MAC address or select the MAC address from the list of clients that are currently connected to the unit.');
	?></p>
	<p><?
		echo i18n('Select "Turn MAC Filtering ON and ALLOW computers with MAC address listed below to access the network" if you only want selected computers to have network access and all other computers not to have network access.');
	?></p>
	<p><?
		echo i18n('Select "Turn MAC Filtering ON and DENY computers with MAC address listed below to access the network" if you want all computers to have network access except those computers in the list.');
	?></p>
	<dl>
		<dt><strong><?echo i18n("MAC Address");?></strong></dt>
		<dd><?
			echo i18n('The MAC address of the network device to be added to the MAC Filter List.');
		?></dd>
		<dt><strong><?echo i18n("Client List");?></strong></dt>
		<dd><?
			echo i18n("Computers that have connected to AP through wireless will be in the Client List. You can select the client computer you want to add to the MAC Filter List and click arrow button. This will automatically add that computer's MAC address to the appropriate field.");
		?></dd>
	  </dd>
	</dl>
</div>
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "-->";} ?>
<div class="blackbox">
	<h2><a name="Network"><?echo i18n("Advanced wireless");?></a></h2>
	<p><?
		echo i18n('This section contains settings which can change the way the access point handles certain types of traffic. We recommend that you not change any of these settings unless you are already familiar with them or have been instructed to change them by one of our support personnel.');
	?></p>
	<dl>
		<dt><strong><?echo i18n("Transmit Power");?></strong></dt>
		<dd><?
			echo i18n("You can lower the output power of the access point by selecting lower percentage Transmit Power values from the drop down. Your choices are: 100%, 50%, 25%, and 12.5%.");
		?></dd>
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "<!--";} ?>
		<dt><strong><?echo i18n("WMM Enable");?></strong></dt>
		<dd><?
			echo i18n("Enabling WMM can help control latency and jitter when transmitting multimedia content over a wireless connection.");
		?></dd>			
		<dt><strong><?echo i18n("IGMP Snooping");?></strong></dt>
		<dd><?
			echo i18n("Tick this section to enable Ethernet multicast function. It supports both IPv4 (Wireless Enhance)_and IPv6 (MLD) protocol. Enable this option to allow Multicast traffic to pass from the Internet to your network more efficiently. Enable this option if you are receiving video on demand type of service from the Internet. The access point uses the Wireless Enhance protocol to support efficient multicasting - - transmission of identical content, such as multimedia, from a source to a number of recipients. This option must be enabled if any applications on the LAN participate in a multicast group. If you have a multimedia LAN application that is not receiving content as expected, try enabling this option.");
		?></dd>
<dt><strong><?echo i18n("Short GI");?></strong></dt>
                <dd><?
                        echo i18n('Using a short (400ns) guard interval can increase throughput. However, it can also increase error rate in some installations, due to increased sensitivity to radio-frequency reflections. Select the option that works best for your installation.');
                ?></dd>
<dt><strong><?echo i18n("Internal Station Connection");?></strong></dt>
                <dd><?
                        echo i18n('The Internal Station Connection default value is "allow," which allows stations to inter-communicate by connecting to a target AP. By disabling this function, wireless stations cannot exchange data through the AP.');
                ?></dd>
<dt><strong><?echo i18n("Ethernet to WLAN Access");?></strong></dt>
                <dd><?
                        echo i18n('The Ethernet to WLAN Access default value is "allow," which allows data flow from the Ethernet to wireless stations connected to the AP. By disabling this function, all data from the Ethernet to associated wireless devices is blocked while wireless stations can still send data to the Ethernet through the AP.');
                ?></dd>
<dt><strong><?echo i18n("HT20/40 Coexistence");?></strong></dt>
                <dd><?
                        echo i18n('Enable HT20/40 Coexistence can increase throughput, and reduce the compatibility.');
                ?></dd>
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "-->";} ?>
	</dl>
</div>
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "<!--";} ?>
<div class="blackbox">
<h2><a name="Gzone"><?echo i18n("Guest Zone");?></a></h2>
<p>
<? 
echo i18n("Use this section to configure the guest zone settings of your access point. The guest zone provides a separate network zone for guests to access the Internet.");
?>
</p>
<dt><strong><?echo i18n("Enable Guest Zone ");?></strong></dt>
		<dd><?
			echo i18n("Tick this checkbox to enable the Guest Zone feature.");
		?></dd>
		<dt><strong><?echo i18n("Wireless Network Name");?></strong></dt>
		<dd><?
			echo i18n("Enter a wireless network name (SSID) that is different from your main wireless network.");
		?></dd>
		<dt><strong><?echo i18n("Security Mode");?></strong></dt>
		<dd><?
			echo i18n("Select the type of security or encryption you would like to enable for the guest zone.");
		?></dd>
<dt><strong><?echo i18n("Enable Guest Zone clients isolation");?></strong></dt>
		<dd><?
			echo i18n("Enable this function to prevent guest clients accessing other guest clients in the Guest Zone.");
		?></dd>
<dt><strong><?echo i18n("Enable Routing Between Zones");?></strong></dt>
		<dd><?
			echo i18n("Use this section to enable routing between the Host zone and Guest Zone. Guest clients cannot access Host clients' data without enabling this function.");
		?></dd>
</div>
<div class="blackbox">
<h2><a name="DhcpServer"><?echo i18n("DHCP Server");?></a></h2>
<dt><strong><?echo i18n("DHCP Server");?></strong></dt>
                <dd><?
                        echo i18n('DHCP stands for Dynamic Host Control Protocol. The DHCP server assigns IP addresses to devices on the network that request them. These devices must be set to "Obtain the IP address automatically". By default, the DHCP Server is disabled on the DAP-1522B. The DHCP address pool contains the range of the IP address that will automatically be assigned to the clients on the network.');
                ?></dd>
<dt><strong><?echo i18n("Enable DHCP Server");?></strong></dt>
                <dd><?
                        echo i18n("Click this button to enable DHCP Server function.");
                ?></dd>               
<dt><strong><?echo i18n("DHCP IP Address Range");?></strong></dt>
                <dd><?
                        echo i18n("Assign the IP Address range for the DHCP server's IP assignment.");
                ?></dd>
<dt><strong><?echo i18n("Default Subnet Mask");?></strong></dt>
                <dd><?
                        echo i18n("Define the subnet mask of the IP address.");
                ?></dd>
<dt><strong><?echo i18n("Default Gateway");?></strong></dt>
                <dd><?
                        echo i18n("Specify the gateway IP address for the wireless network.");
                ?></dd>
<dt><strong><?echo i18n("Default Wins");?></strong></dt>
                <dd><?
                        echo i18n("Enter the WINS server IP address for the wireless network.");
                 ?></dd>
<dt><strong><?echo i18n("Default DNS");?></strong></dt>
                <dd><?
                        echo i18n("Enter the Domain Name Service IP address for the wireless network.");
                 ?></dd>
<dt><strong><?echo i18n("DHCP Lease Time");?></strong></dt>
                <dd><?
                        echo i18n("The length of time in minutes for the IP lease.");
                ?></dd>     
<dt><strong><?echo i18n("DHCP Reservation List");?></strong></dt>
                <dd><?
                        echo i18n("List the Reservation rules for DHCP.");
                ?></dd>   
<dt><strong><?echo i18n("Number of Dynamic DHCP Clients");?></strong></dt>
                <dd><?
                        echo i18n("Dynamic DHCP client computers connected to the unit will have their information displayed in the Dynamic DHCP Client Table. The table will show the Host Name, IP Address, MAC Address, and Expired Time of the DHCP lease for each client computer.");
                ?></dd>   
<dt><strong><?echo i18n("DHCP Reservation");?></strong></dt>
                <dd><?
                        echo i18n('Enter the "Computer Name", "IP Address" and "MAC Address" manually for the PC that you desire to have the AP to statically assign the same IP to or choose the PC from the drop down menu which shows current DHCP clients.');
                ?></dd>

</div>
<div class="blackbox">
<h2><a name="Qos">QoS</a></h2>
<p>
<?
echo i18n("QoS stands for Quality of Service for Wireless Intelligent Stream Handling, a technology developed to enhance your experience of using a wireless network by prioritizing the traffic of different applications. Enable this option to allow QoS to prioritize the traffic. There are two options available for the special application.");
?>
</p>
<dt><strong><?echo i18n("Enable QoS");?></strong></dt>
                <dd><?
                        echo i18n("Enable this option if you want to allow QoS to prioritize your traffic.");
                ?></dd>
                <dt><strong><?echo i18n("QoS Type");?></strong></dt>
                <dd><?
                        echo i18n("There are two options available for your special application.");
                ?></dd>
                <dt><strong><?echo i18n("Port QoS");?></strong></dt>
                <dd><?
                        echo i18n("There are four priorities level for all Lan Ports, which are defined as following: Voice, Video, Best Effort, Background. The priorities level value we assigned is that 1 for Background, 3 for Best Effort, 5 for Video and 7 for Vioce.");
                ?></dd>
<dt><strong><?echo i18n("Advanced QoS");?></strong></dt>
                <dd>
			<dl>
                        	<dt><?echo i18n('Wireless to Ethernet');?></dt>
                                        <dd><?echo i18n('The value that you enter here will indicate Wireless to Ethernet speed. When wireless mode acts as access point, you can set 51200kbps as a default value. ');?>
					</dd>
				<dt><strong><?echo i18n("Ethernet to Wireless");?></strong></dt>
                			<dd><?
                        echo i18n("The value that you enter here will indicate Ethernet to Wireless speed. When wireless mode acts as access point, you can set 51200kbps as a default value.");
                ?></dd>
 <dt><strong><?echo i18n("ACK/DHCP/ICMP/DNS Priority");?></strong></dt>
                <dd><?
                        echo i18n("Denote a priority value which will apply for ACK,DHCP,ICMP,DNS packet delivering.");
                ?></dd>
 <dt><strong><?echo i18n("Web Traffic Priority");?></strong></dt>
                <dd><?
                        echo i18n("Denote a priority value which will apply for http packet delivering.");
                ?></dd>
 <dt><strong><?echo i18n("Mail Traffic Priority");?></strong></dt>
                <dd><?
                        echo i18n("Denote a priority value which will apply for some packets, which related with mail traffic, delivering.");
                ?></dd>
 <dt><strong><?echo i18n("Ftp Traffic Priority");?></strong></dt>
                <dd><?
                        echo i18n("Denote a priority value which will apply for ftp packet delivering.");
                ?></dd>
 <dt><strong><?echo i18n("Other Traffic Priority");?></strong></dt>
                <dd><?
                        echo i18n("Denote a priority value which will apply for other packets delivering.");
                ?></dd>

			</dl>
		</dd>
<p>
<?
echo i18n("Normally the device transmits application data packets with the wireless to ethernet speed and ethernet to wireless speed, we can regard the two speeds as system transmission bandwidth, and based on assigned priorities, all applications will share the whole system bandwidth.");
?>
</p>
</div>
<div class="blackbox">
<h2><a name="TraMgr"><?echo i18n("Traffic Manager");?></a></h2>
<p>
<?
echo i18n("Via Traffic Manager, you can control the bandwidth of the device by setting, wireless to ethernet speed and/or ethernet to wireless speed. It is also possible to add/del rules for different clients. For such clients which are not listed on the rule table, you may choose to deny or forward packets for them.");
?>
</p>
<dt><strong><?echo i18n("Traffic Manager");?></strong></dt>
 		<dd>
                        <dl>
                                <dt><?echo i18n('Enable Taffic Manager');?></dt>
                                        <dd><?echo i18n('Enable this option to allow traffic control.');?>
                                        </dd>
                                <dt><strong><?echo i18n("Unlisted Clients Traffic");?></strong></dt>
                                        <dd><?
                        echo i18n("There are two options available for Unlisted Clients Traffic: deny or forward.");
                ?></dd>
 <dt><strong><?echo i18n("Ethernet to Wireless");?></strong></dt>
                <dd><?
                        echo i18n("Which indicates the MAX bandwidth from Ethernet to Wireless for the device.");
                ?></dd>
 <dt><strong><?echo i18n("Wireless to Ethernet");?></strong></dt>
                <dd><?
                        echo i18n("Which indicates the MAX bandwidth from Wireless to Ethernet for the device.");
                ?></dd>
                        </dl>
                </dd>
<dt><strong><?echo i18n("Add Traffic Manager Rule");?></strong></dt>
                <dd>
                        <dl>
                                <dt><?echo i18n('Name');?></dt>
                                        <dd><?echo i18n('The name of the rule.');?>
                                        </dd>
                                <dt><strong><?echo i18n("Client IP(optional)");?></strong></dt>
                                        <dd><?
                        echo i18n("The IP address assigned to the client.");
                ?></dd>
 <dt><strong><?echo i18n("Client Mac(optional)");?></strong></dt>
                <dd><?
                        echo i18n("The Mac address assigned to the client.
For each rule being added, mac address and IP address stands for unique client, and at least one of them should be filled in.");
                ?></dd>
 <dt><strong><?echo i18n("Ethernet to Wireless");?></strong></dt>
                <dd><?
                        echo i18n("Which denotes the available bandwidth for client data delivering from Ethernet to Wireless.");
                ?></dd>
 <dt><strong><?echo i18n("Wireless to Ethernet");?></strong></dt>
                <dd><?
                        echo i18n("Which denotes the available bandwidth for client data delivering from Wireless to Ethernet.");
                ?></dd>
                        </dl>
                </dd>
</div>
<div class="blackbox">
        <h2><a name="WPS"><?echo i18n("Wi-Fi Protected Setup");?></a></h2>
        <dl>
                <dt><strong><?echo i18n("Wi-Fi Protected Setup");?></strong></dt>
                <dd>
                        <dl>
                                <dt><strong><?echo i18n("Enable");?></strong></dt>
                                <dd><?echo i18n("Enable the Wi-Fi Protected Setup feature.");?></dd>
                                <dt><strong><?echo i18n("Disable WPS-PIN Method");?></strong></dt>
                                <dd><?echo i18n("Disable the WPS-PIN Method prevents the settings from being changed by any new external registrar using its PIN. Devices can still be added to the wireless network using Wi-Fi Protected Setup Push Button Configuration (WPS-PIN). It is still possible to change wireless network settings with <a href='bsc_wlan.php'>Manual Wireless Network Setup</a> or <a href='wiz_wlan.php'>Wireless Network Setup Wizard</a>.");?></dd>
                                <dt><strong><?echo i18n("Reset to Unconfigured");?></strong></dt>
                                <dd><?echo i18n("Press this button to reset the device to Unconfigured.");?></dd>
                        </dl>
                </dd>
                <dt><strong><?echo i18n("PIN Settings");?></strong></dt>
                <dd>
                        <p><?
                                echo i18n('A PIN is a unique number that can be used to add the access point to an existing network or to create a new network. The default PIN may be printed on the bottom of the access point. For extra security, a new PIN can be generated. You can restore the default PIN at any time. Only the Administrator ("admin" account) can change or reset the PIN.');
                        ?></p>
                        <dl>
                                <dt><strong><?echo i18n("Current PIN");?></strong></dt>
                                <dd><?echo i18n("Shows the current value of the access point's PIN.");?></dd>
                                <dt><strong><?echo i18n("Reset PIN to Default");?></strong></dt>
                                <dd><?echo i18n("Restore the default PIN of the access point.");?></dd>
                                <dt><strong><?echo i18n("Generate New PIN");?></strong></dt>
                                <dd><?
                                        echo i18n("Create a random number that is a valid PIN. This becomes the access point's PIN. You can then copy this PIN to the user interface of the registrar.");
                                ?></dd>
                        </dl>
                </dd>
                <dt><strong><?echo i18n("Add Wireless Station");?></strong></dt>
                <dd>
                        <p><?echo i18n("This Wizard helps you add wireless devices to the wireless network.");?></p>
                        <p><?
                                echo i18n("The wizard will either display the wireless network settings to guide you through manual configuration, prompt you to enter the PIN for the device, or ask you to press the configuration button on the device. If the device supports Wi-Fi Protected Setup and has a configuration button, you can add it to the network by pressing the configuration button on the device and then the configuration button on the access point within 120 seconds. The status LED on the access point will flash three times if the device has been successfully added to the network.");
                        ?></p>
                        <p><?
                                echo i18n('There are several ways to add a wireless device to your network. Access to the wireless network is controlled by a "registrar". A registrar only allows devices onto the wireless network if you have entered the PIN, or pressed a special Wi-Fi Protected Setup button on the device. The access point acts as a registrar for the network, although other devices may act as a registrar as well.');
                        ?></p>
                        <dl>
                                <dt><strong><?echo i18n("Add Wireless Device With WPS");?></strong></dt>
                                <dd><?echo i18n("Start the wizard.");?></dd>
                        </dl>
                </dd>
        </dl>
</div>
<div class="blackbox">
<h2><a name="UserLimit"><?echo i18n("User Limit");?></a></h2>
<p>
<?
echo i18n('Set the maximum amount of users allowed per access point. "20" is recommended for the typical user.');
?>
</p>
</div>
<? if(query("/runtime/device/switchmode") =="APCLI")	{echo "-->";} ?>


