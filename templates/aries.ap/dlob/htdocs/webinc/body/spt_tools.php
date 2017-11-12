<div class="orangebox">
	<h1><?echo i18n("Tools Help");?></h1>
	<ul>
		<li><a href="./spt_tools.php#Admin"><?echo i18n("Admin");?></a></li>
		<li><a href="./spt_tools.php#Time"><?echo i18n("Time");?></a></li>		
		<li><a href="./spt_tools.php#System"><?echo i18n("System");?></a></li>
		<li><a href="./spt_tools.php#Firmware"><?echo i18n("Firmware");?></a></li>		
		<li><a href="./spt_tools.php#SystemCheck"><?echo i18n("System Check");?></a></li>		
	</ul>
</div>
<div class="blackbox">
	<h2><a name="Admin"><?echo i18n("Admin");?></a></h2>
	<dl>
		<dt><?echo i18n("Administrator password");?></dt>
		<dd><?
			echo i18n("Enter and confirm the password that the <strong>admin</strong> account will use to access the access point's management interface.");
		?></dd>
	<!--	<dt><?echo i18n("Remote Management");?></dt>
		<dd><?
			echo i18n("Remote Management allows the device to be configured through the WAN (Wide Area Network) port from the Internet using a web browser. A username and password is still required to access the access point's management interface.");
		?></dd>
		<dt><?echo i18n("IP Allowed to Access");?></dt>
		<dd><?
			echo i18n("This option allows users to specify a particular IP address from the Internet to be allowed to access the access point remotely. This field is left blank by default which means any IP address from the Internet can access the access point remotely once remote management is enabled.");
		?></dd>
		<div class="help_example">
		<dl>
			<dt><?echo i18n("Example");?>:</dt>
			<dd><?
				echo i18n("http://x.x.x.x:8080 whereas x.x.x.x is the WAN IP address of the access point and 8080 is the port used for the Web-Management interface.");
			?></dd>
		</dl>
		</div>
		-->
	</dl>
</div>
<div class="blackbox">
	<h2><a name="Time"><?echo i18n("Time");?></a></h2>
	<p><?
		echo i18n('The Time Configuration settings are used by the access point for synchronizing scheduled services and system logging activities. You will need to set the time zone corresponding to your location. The time can be set manually or the device can connect to a NTP (Network Time Protocol) server to retrieve the time. You may also set Daylight Saving dates and the system time will automatically adjust on those dates.');
	?></p>
	<dl>
		<dt><?echo i18n("Time Zone");?></dt>
		<dd><?
			echo i18n("Select the Time Zone for the region you are in.");
		?></dd>
		<dt><?echo i18n("Daylight Saving");?></dt>
		<dd><?
			echo i18n("If the region you are in observes Daylight Savings Time, enable this option and specify the Starting and Ending Month, Week, Day, and Time for this time of the year.");
		?></dd>
		<dt><?echo i18n("Automatic Time Configuration");?></dt>
		<dd><?
			echo i18n("Select a D-Link time server which you would like the access point to synchronize its time with. The interval at which the access point will communicate with the D-Link NTP server is set to 7 days.");
		?></dd>
		<dt><?echo i18n("Set the Date and Time Manually");?></dt>
		<dd><?
			echo i18n("Select this option if you would like to specify the time manually. You must specify the Year, Month, Day, Hour, Minute, and Second, or you can click the Copy Your Computer's Time Settings button to copy the system time from the computer being used to access the management interface.");
		?></dd>
	</dl>
</div>
<div class="blackbox">
	<h2><a name="System"><?echo i18n("System");?></a></h2>
	<p><?
		echo i18n("The current system settings can be saved as a file onto the local hard drive. The saved file or any other saved setting file created by device can be uploaded into the unit. To reload a system settings file, click on <strong>Browse</strong> to search the local hard drive for the file to be used. The device can also be reset back to factory default settings by clicking on <strong>Restore Device</strong>. Use the restore feature only if necessary. This will erase previously save settings for the unit. Make sure to save your system settings before doing a factory restore.");
	?></p>
	<dl>
		<dt><?echo i18n("Save");?></dt>
		<dd><?
			echo i18n("Click this button to save the configuration file from the access point.");
		?></dd>
		<dt><?echo i18n("Browse");?></dt>
		<dd><?
			echo i18n("Click Browse to locate a saved configuration file and then click to Load to apply these saved settings to the access point.");
		?></dd>
		<dt><?echo i18n("Restore Device");?></dt>
		<dd><?
			echo i18n("Click this button to restore the access point to its factory default settings.");
		?></dd>
		<dt><?echo i18n("Reboot the device");?></dt>
		<dd><?
			echo i18n("Click the button if you would like to restart the device.");
		?></dd>
		<dt><?echo i18n("Clear language pack");?></dt>
		<dd><?
			echo i18n("Click this button to remove the language pack that is currently used.");
		?></dd>
	</dl>
</div>
<div class="blackbox">
	<h2><a name="Firmware"><?echo i18n("Firmware");?></a></h2>
	<p><?
		echo i18n('You can upgrade the firmware of the device using this tool. Make sure that the firmware you want to use is saved on the local hard drive of the computer. Click on <strong>Browse</strong> to search the local hard drive for the firmware to be used for the update. Upgrading the firmware will not change any of your system settings but it is recommended that you save your system settings before doing a firmware upgrade. Please check the D-Link <a href="http://support.dlink.com.tw">support site</a> for firmware updates or you can click on <strong>Check Now</strong> button to have the access point check the new firmware automatically.');
	?></p>
</div>
<div class="blackbox">
	<h2><a name="SystemCheck"><?echo i18n("System Check");?></a></h2>
	<dl>
		<dt><?echo i18n("Ping Test");?></dt>
		<dd><?
			echo i18n('This useful diagnostic utility can be used to check if a computer is on the Internet. It sends ping packets and listens for replies from the specific host. Enter in a host name or the IP address that you want to ping (Packet Internet Groper) and click <strong>Ping</strong>. The status of your Ping attempt will be displayed in the Ping Result box.');
		?></dd>
	</dl>
</div>
