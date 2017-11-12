<form id="mainform" onsubmit="return false;">
<div class="orangebox">
	<h1><?echo i18n("Device Information");?></h1>
	<p>
		<?echo i18n("All of your wireless and network connection details are displayed on this page. The firmware version is also displayed here.");?>
	</p>
</div>
<div class="blackbox">
	<h2><?echo i18n("General");?></h2>
	<div class="textinput">
		<span class="name"><?echo i18n("Time");?></span>
		<span class="delimiter">:</span>
		<span class="value" id="st_time"></span>
	</div>
	<div class="textinput">
		<span class="name"><?echo i18n("Firmware Version");?></span>
		<span class="delimiter">:</span>
		<span class="value"><?echo query("/runtime/device/firmwareversion").' '.query("/runtime/device/firmwarebuilddate");?></span>
	</div>
	<div class="gap"></div>
</div>
<div class="blackbox" id="ethernet_block" >
    <h2><?echo i18n("LAN");?></h2>
    <div class="textinput">
        <span class="name"><?echo i18n("MAC Address");?></span>
	    <span class="delimiter">:</span>
	    <span class="value"><?echo query("/runtime/devdata/lanmac");?></span>
    </div>
    <div class="textinput">
        <span class="name"><?echo i18n("Connection");?></span>
        <span class="delimiter">:</span>
        <span class="value" id="br_wantype"></span>
    </div>
    <div class="textinput">
        <span class="name"><?echo i18n("IP Address");?></span>
        <span class="delimiter">:</span>
        <span class="value" id="br_ipaddr"></span>
    </div>
    <div class="textinput">
        <span class="name"><?echo i18n("Subnet Mask");?></span>
        <span class="delimiter">:</span>
        <span class="value" id="br_netmask"></span>
    </div>
    <div class="textinput">
        <span class="name"><?echo i18n("Gateway Address");?></span>
        <span class="delimiter">:</span>
        <span class="value" id="br_gateway"></span>
    </div>
	<div class="gap"></div>
</div>
<div class="blackbox">
	<h2><?echo i18n("WIRELESS LAN");?></h2>
	<!--div class="textinput">
		<span class="name"><?echo i18n("Wireless Radio");?></span>
		<span class="delimiter">:</span>
		<span class="value" id="st_wireless_radio"></span>
	</div>
	<div id="bridge_state" class="textinput">
		<span class="name"><?echo i18n("Bridge State");?></span>
		<span class="delimiter">:</span>
		<span class="value" id="st_bridge_state"></span>
	</div-->	
	<div class="textinput">
		<span class="name"><?echo i18n("MAC Address");?></span>
		<span class="delimiter">:</span>
		<span class="value"><?echo query("/runtime/devdata/wlanmac");?></span>
	</div>
	<div id="80211mode" class="textinput" style="display:none;">
		<span class="name"><?echo i18n("802.11 Mode");?></span>
		<span class="delimiter">:</span>
		<span class="value" id="st_80211mode"></span>
	</div>
	<div id="Channel_Width" class="textinput" style="display:none;">
		<span class="name"><?echo i18n("Band Width");?></span>
		<span class="delimiter">:</span>
		<span class="value" id="st_Channel_Width"></span>
	</div>
	<div class="textinput">
		<span class="name"><?echo i18n("Network Name (SSID)");?></span>
		<span class="delimiter">:</span>
		<span class="value" id="st_SSID"></span>
	</div>
	<div class="textinput">
		<span class="name"><?echo i18n("Channel");?></span>
		<span class="delimiter">:</span>
		<span class="value" id="st_Channel"></span>
	</div>
	<div class="textinput">
		<span class="name"><?echo i18n("Security Mode");?></span>
		<span class="delimiter">:</span>
		<span class="value" id="st_security"></span>
	</div>
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "<!--";} ?>
	<div class="textinput">
		<span class="name"><?echo i18n("Wi-Fi Protected Setup");?></span>
		<span class="delimiter">:</span>
		<span class="value" id="st_WPS_status"></span>
	</div>
<? if(query("/runtime/device/switchmode") =="APCLI")    {echo "-->";} ?>
	<div class="gap"></div>
</div>
</form>
