<div class="orangebox">
	<h1><?echo i18n("Advanced Help");?></h1>
	<ul>		
<? if(query("/runtime/device/switchmode") =="APCLI")	{echo "<!--";} ?>
		<li><a href="#NetFilter"><?echo i18n("MAC Address Filter");?></a></li>						
		<li><a href="#Network"><?echo i18n("Advanced network");?></a></li>		
		<li><a href="#Gzone"><?echo i18n("Guest Zone");?></a></li>
<? if(query("/runtime/device/switchmode") =="APCLI")	{echo "-->";} ?>
		<?if ($FEATURE_NOSCH!="1")echo '<li><a href="#Schedule">'.i18n("Schedule").'</a></li>\n';?>
	</ul>
</div>
<? if(query("/runtime/device/switchmode") =="APCLI")	{echo "<!--";} ?>
<div class="blackbox">
	<h2><a name="NetFilter"><?echo i18n("MAC Address Filter");?></a></h2>
	<p><?
		echo i18n('Use MAC Filters to deny computers within the local area network from accessing the Internet. You can either manually add a MAC address or select the MAC address from the list of clients that are currently connected to the unit.');
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
		<dt><strong><?echo i18n("DHCP Client List");?></strong></dt>
		<dd><?
			echo i18n("DHCP clients will have their hostname in the Computer Name drop down menu. You can select the client computer you want to add to the MAC Filter List and click arrow button. This will automatically add that computer's MAC address to the appropriate field.");
		?></dd>
	  </dd>
	</dl>
	<p<?if ($FEATURE_NOSCH=="1") echo ' style="display:none;"';?>><?
		echo i18n('Users can use the <strong>Always</strong> drop down menu to select a previously defined schedule or click the <strong>New Schedule</strong> button to add a new schedule.');
	?></p>
	<p><?
		echo i18n('The check box is used to enable or disable a particular entry.');
	?></p>
</div>
<div class="blackbox">
	<h2><a name="Network"><?echo i18n("Advanced network");?></a></h2>
	<p><?
		echo i18n('This section contains settings which can change the way the access point handles certain types of traffic. We recommend that you not change any of these settings unless you are already familiar with them or have been instructed to change them by one of our support personnel.');
	?></p>
	<dl>
		<dt><strong><?echo i18n("Transmit Power");?></strong></dt>
		<dd><?
			echo i18n("You can lower the output power of the access point by selecting lower percentage Transmit Power values from the drop down. Your choices are: 100%, 50%, 25%, and 12.5%.");
		?></dd>
		<dt><strong><?echo i18n("WMM Enable");?></strong></dt>
		<dd><?
			echo i18n("Enabling WMM can help control latency and jitter when transmitting multimedia content over a wireless connection.");
		?></dd>			
		<dt><strong><?echo i18n("Multicast Streams - IGMP Snooping");?></strong></dt>
		<dd><?
			echo i18n("Tick this section to enable Ethernet multicast function. It supports both IPv4 (IGMP)_and IPv6 (MLD) protocol. Enable this option to allow Multicast traffic to pass from the Internet to your network more efficiently. Enable this option if you are receiving video on demand type of service from the Internet. The access point uses the IGMP protocol to support efficient multicasting - - transmission of identical content, such as multimedia, from a source to a number of recipients. This option must be enabled if any applications on the LAN participate in a multicast group. If you have a multimedia LAN application that is not receiving content as expected, try enabling this option.");
		?></dd>
	</dl>
</div>
<div class="blackbox">
<h2><a name="Gzone">Guest Zone</a></h2>
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
<? if(query("/runtime/device/switchmode") =="APCLI")	{echo "-->";} ?>

<div class="blackbox"<?if($FEATURE_NOSCH=="1")echo ' style="display:none"';?>>
	<h2><a name="Schedule"><?echo i18n("Schedule");?></a></h2>
	<p><?
		echo i18n("This page is used to configure global schedules for the access point. Once defined, these schedules can later be applied to the features of the access point that support scheduling.");
	?></p>
	<dl>
		<dt><?echo i18n("Name");?></dt>
		<dd><?echo i18n('The name of the schedule being defined.');?></dd>
		<dt><?echo i18n("Day(s)");?></dt>
		<dd><?echo i18n("Select a day, range of days, or select the All Week checkbox to have this schedule apply every day.");?></dd>
		<dt><?echo i18n("All Day - 24 hrs");?></dt>
		<dd><?echo i18n("Check this box to have the schedule active the entire 24 hours on the days specified.");?></dd>
		<dt><?echo i18n("Start Time");?></dt>
		<dd><?echo i18n("Select the time at which you would like the schedule being defined to become active.");?></dd>
		<dt><?echo i18n("End Time");?></dt>
		<dd><?echo i18n("Select the time at which you would like the schedule being defined to become inactive.");?></dd>
		<dt><?echo i18n("Schedule Rules List");?></dt>
		<dd><?echo i18n("This displays all the schedules that have been defined. ");?></dd>
	</dl>
</div>
