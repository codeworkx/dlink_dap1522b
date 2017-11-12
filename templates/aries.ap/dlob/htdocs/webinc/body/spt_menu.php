<div class="orangebox">
	<h1><?echo i18n("Support Menu");?></h1>
	<ul>
		<li><a href="./support.php#Setup"><?echo i18n("Setup");?></a></li>
		<li><a href="./support.php#Advanced"><?echo i18n("Advanced");?></a></li>
		<li><a href="./support.php#Tools"><?echo i18n("Tools");?></a></li>
		<li><a href="./support.php#Status"><?echo i18n("Status");?></a></li>
	</ul>
</div>
<div class="blackbox">
	<h2><a name="Setup"><?echo i18n("Setup Help");?></a></h2>
	<ul>
		<li><a href="./spt_setup.php#Internet"><?echo i18n("Setup Wizard");?></a></li>
		<li><a href="./spt_setup.php#Wireless"><?echo i18n("Wireless Settings");?></a></li>
		<li><a href="./spt_setup.php#Network"><?echo i18n("LAN Settings");?></a></li>		
	</ul>
</div>
<div class="blackbox">
	<h2><a name="Advanced"><?echo i18n("Advanced Help");?></a></h2>
	<ul>		
<? if(query("/runtime/device/switchmode") =="APCLI")	{echo "<!--";} ?>
		<li><a href="./spt_adv.php#NetFilter"><?echo i18n("MAC address Filter");?></a></li>	
		<li><a href="./spt_adv.php#Network"><?echo i18n("Advanced network");?></a></li>		
		<li><a href="./spt_adv.php#Firewall"><?echo i18n("Guest Zone");?></a></li>
<? if(query("/runtime/device/switchmode") =="APCLI")	{echo "-->";} ?>
		<?if ($FEATURE_NOSCH!="1")echo '<li><a href="./spt_adv.php#Schedule">'.i18n("Schedule").'</a></li>\n';?>
	</ul>
</div>
<div class="blackbox">
	<h2><a name="Tools"><?echo i18n("Tools Help");?></a></h2>
	<ul>
		<li><a href="./spt_tools.php#Admin"><?echo i18n("Admin");?></a></li>
		<li><a href="./spt_tools.php#Time"><?echo i18n("Time");?></a></li>		
		<li><a href="./spt_tools.php#System"><?echo i18n("System");?></a></li>
		<li><a href="./spt_tools.php#Firmware"><?echo i18n("Firmware");?></a></li>		
		<li><a href="./spt_tools.php#SystemCheck"><?echo i18n("System Check");?></a></li>		
	</ul>
</div>
<div class="blackbox">
	<h2><a name="Status"><?echo i18n("Status Help");?></a></h2>
	<ul>
		<li><a href="./spt_status.php#Device"><?echo i18n("Device Info");?></a></li>
		<li><a href="./spt_status.php#Wireless"><?echo i18n("Wireless");?></a></li>
		<li><a href="./spt_status.php#Logs"><?echo i18n("Logs");?></a></li>
		<li><a href="./spt_status.php#Statistics"><?echo i18n("Statistics");?></a></li>		
		<li><a href="./spt_status.php#Ipv6"><?echo i18n("IPv6");?></a></li>			
	</ul>
</div>
