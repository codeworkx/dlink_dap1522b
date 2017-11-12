<div class="orangebox">
	<h1><?echo i18n("Status Help");?></h1>
	<ul>
		<li><a href="#Device"><?echo i18n("Device Info");?></a></li>
		<li><a href="#Logs"><?echo i18n("Logs");?></a></li>
		<li><a href="#Statistics"><?echo i18n("Statistics");?></a></li>	
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "<!--";} ?>
		<li><a href="#Wireless"><?echo i18n("Wireless");?></a></li>
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "-->";} ?>
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "<!--";} ?>
		<li><a href="#Ipv6"><?echo i18n("IPv6");?></a></li>		
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "-->";} ?>
	</ul>
</div>
<div class="blackbox">
	<h2><a name="Device"><?echo i18n("Device Info");?></a></h2>
	<p><?
		echo i18n("This page displays the current information for the access point. The page will show the version of the firmware currently loaded in the device. ");
	?></p>
	<dl>
		<dt><?echo i18n("LAN (Local Area Network)");?></dt>
		<dd><?
			echo i18n("This displays the MAC Address of the Ethernet LAN interface, the IP Address and Subnet Mask of the LAN interface.");
		?></dd>
		<dt><?echo i18n("Wireless LAN");?></dt>
		<dd><?
			echo i18n("This displays the SSID, Channel, and whether or not Encryption is enabled on the Wireless interface.");
		?></dd>
	</dl>
</div>
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "<!--";} ?>
<div class="blackbox">
	<h2><a name="Wireless"><?echo i18n("Wireless");?></a></h2>
	<p><?
		echo i18n("Use this page in order to view how many wireless clients have associated with the access point. This page shows the MAC address of each associated client, and the mode they are connecting in (802.11a or 802.11b or 802.11g or 802.11n).");
	?></p>
</div>
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "-->";} ?>
<div class="blackbox">
	<h2><a name="Logs"><?echo i18n("Logs");?></a></h2>
	<p><?
		echo i18n("You can save the log file to a local drive which can later be used to send to a network administrator for troubleshooting.");
	?></p>
	<dl>
		<dt><?echo i18n("Save log");?></dt>
		<dd><?echo i18n("Click this button to save the log entries to a text file.");?></dd>
	</dl>
	<p><?
		echo i18n("The access point keeps a running log of events and activities occurring on it at all times. The log will display up to 400 recent logs. Newer log activities will overwrite the older logs.");
	?></p>
	<dl>
		<dt><?echo i18n("First Page");?></dt>
		<dd><?echo i18n("Click this button to go to the first page of the log.");?></dd>
		<dt><?echo i18n("Last Page");?></dt>
		<dd><?echo i18n("Click this button to go to the last page of the log.");?></dd>
		<dt><?echo i18n("Previous");?></dt>
		<dd><?echo i18n("Moves back one log page.");?></dd>
		<dt><?echo i18n("Next");?></dt>
		<dd><?echo i18n("Moves forward one log page.");?></dd>
		<dt><?echo i18n("Clear");?></dt>
		<dd><?echo i18n("Clears the logs completely.");?></dd>
		<dt><?echo i18n("Refresh");?></dt>
		<dd><?echo i18n("Refresh to get the lasted logs.");?></dd>
		<dt><?echo i18n("Log Type");?></dt>
		<dd><?echo i18n("Select the type of information you would like the DAP-1522B to log.");?></dd>
		<!--dt><?echo i18n("Log Level");?></dt>
		<dd><?echo i18n("Select the level of information you would like the DAP-1522B to log.");?></dd-->
	</dl>
</div>
<div class="blackbox">
	<h2><a name="Statistics"><?echo i18n("Statistics");?></a></h2>
	<p><?
		echo i18n("The access point keeps statistic of the data traffic that it handles. You are able to view the amount of packets that the access point has Received and Transmitted on the LAN and Wireless interfaces.");
	?></p>
	<dl>
		<dt><?echo i18n("Refresh Statistics");?></dt>
		<dd><?echo i18n("Click this button to update the counters.");?></dd>
		<dt><?echo i18n("Clear Statistics");?></dt>
		<dd><?echo i18n("Click this button to clear the counters. The traffic counter will reset when the device is rebooted.");?></dd>
	</dl>
</div>
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "<!--";} ?>
<div class="blackbox">
	<h2><a name="Ipv6"><?echo i18n("IPv6");?></a></h2>
	<p><?
		echo i18n("This section is to provide the details and status of the IPv6 Internet connection.");
	?></p>
	<dl>
		<dt><strong><?echo i18n("IPV6 CONNECTION INFORMATION");?> </strong></dt>
			<dd><?echo i18n("This displays the connection type of the LAN interface, as well as the IPv6 Address, Default Gateway, and DNS server information.");?></dd>	
	</dl>	
</div>
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "-->";} ?>

