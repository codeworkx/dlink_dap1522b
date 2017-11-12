<div class="orangebox">
	<h1><?echo i18n("Maintenance Help");?></h1>
	<ul>
		<li><a href="./spt_tools.php#Admin"><?echo i18n("Admin");?></a></li>
		<li><a href="./spt_tools.php#System"><?echo i18n("System");?></a></li>
		<li><a href="./spt_tools.php#Firmware"><?echo i18n("Firmware");?></a></li>	
		<li><a href="./spt_tools.php#Time"><?echo i18n("Time");?></a></li>	
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "<!--";} ?>
		<li><a href="./spt_tools.php#SystemCheck"><?echo i18n("System Check");?></a></li>		
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "-->";} ?>
		<?if ($FEATURE_NOSCH!="1")echo '<li><a href="#Schedule">'.i18n("Schedules").'</a></li>\n';?>
	</ul>
</div>
<div class="blackbox">
	<h2><a name="Admin"><?echo i18n("Admin");?></a></h2>
	<dl>
		<dt><?echo i18n("Administrator password");?></dt>
		<dd><?
			echo i18n("Enter and confirm the password that the <strong>admin</strong> account will use to access the access point's management interface.");
		?></dd>
    		<dt><?echo i18n("Enable Graphical Authentication");?></dt>
                <dd><?
                        echo i18n("Graphical authentication (CAPTCHA) scheme generates letters randomly when you log in. This extra security deters hackers from using brute force password cracking software.");
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
		echo i18n('You can upgrade the firmware or language pack of the device using this tool. Make sure that the firmware or language pack you want to use is saved on the local hard drive of the computer. Click on <strong>Browse</strong> to search the local hard drive for the firmware or language pack and to be used for the update. Upgrading the firmware will not change any of your system settings but it is recommended that you save your system settings before doing a firmware upgrade. Please check the D-Link <a href="http://support.dlink.com">support site</a> for firmware or language pack updates or you can click on <strong>Check Now</strong> button to have the access point check the new firmware or language pack automatically.');
	?></p>
</div>
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "<!--";} ?>
<div class="blackbox">
	<h2><a name="SystemCheck"><?echo i18n("System Check");?></a></h2>
	<dl>
		<dt><?echo i18n("Ping Test");?></dt>
		<dd><?
			echo i18n('This useful diagnostic utility can be used to check if a computer is on the Internet. It sends ping packets and listens for replies from the specific host. Enter in a host name or the IP address that you want to ping (Packet Internet Groper) and click <strong>Ping</strong>. The status of your Ping attempt will be displayed in the Ping Result box.');
		?></dd>
	</dl>
</div>
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "-->";} ?>
<div class="blackbox"<?if($FEATURE_NOSCH=="1")echo ' style="display:none"';?>>
	<h2><a name="Schedule"><?echo i18n("Schedules");?></a></h2>
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
		<dt><?echo i18n("Time Format");?></dt>
		<dd><?echo i18n("Select 24-hour or 12-hour.");?></dd>
		<dt><?echo i18n("Start Time");?></dt>
		<dd><?echo i18n("Select the time at which you would like the schedule being defined to become active.");?></dd>
		<dt><?echo i18n("End Time");?></dt>
		<dd><?echo i18n("Select the time at which you would like the schedule being defined to become inactive.");?></dd>
		<dt><?echo i18n("Schedule Rules List");?></dt>
		<dd><?echo i18n("This displays all the schedules that have been defined. ");?></dd>
	</dl>
</div>
