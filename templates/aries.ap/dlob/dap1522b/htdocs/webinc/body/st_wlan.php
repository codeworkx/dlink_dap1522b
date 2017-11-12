<form id="mainform" onsubmit="return false;">
<div class="orangebox">
	<h1><?echo i18n("Wireless");?></h1>
	<p><?
		echo i18n("The Wireless Client table below displays Wireless clients connected to the AP(Access Point). In Wireless Client mode it displays the connected AP's MAC address and connected Time.");
	?></p>
</div>
<div class="blackbox">
	<h2><?echo i18n("Number Of Wireless Clients");?>: <span id="client_cnt"></span></h2>
	<div class="centerline">
		<table id="client_list" class="general" width="535px">
		<tr>
			<!--th width="150px"><?echo i18n("SSID");?></th>
			<th width="120px"><?echo i18n("MAC Address");?></th>
			<th width="120px"><?echo i18n("Uptime");?></th>
			<th width="60px"><?echo i18n("Mode");?></th>			
			<th width="60px"><?echo i18n("Rssi");?> (%)</th-->
			<th width="300px"><?echo i18n("Connected Time");?></th>
			<th width="210px"><?echo i18n("MAC Address");?></th>
		</tr>
		</table>
	</div>
	<div class="gap"></div>
</div>
</form>
