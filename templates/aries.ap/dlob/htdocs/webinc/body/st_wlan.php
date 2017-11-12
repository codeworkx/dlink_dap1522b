<form id="mainform" onsubmit="return false;">
<div class="orangebox">
	<h1><?echo i18n("Connected Wireless Client List");?></h1>
	<p><?
		echo i18n("View the wireless clients that are connected to the access point. (A client might linger in the list for a few minutes after an unexpected disconnect.)");
	?></p>
</div>
<div class="blackbox">
	<h2><?echo i18n("Number Of Wireless Clients");?>: <span id="client_cnt"></span></h2>
	<div class="centerline">
		<table id="client_list" class="general" width="535px">
		<tr>
			<th width="150px"><?echo i18n("SSID");?></th>
			<th width="120px"><?echo i18n("MAC Address");?></th>
			<th width="120px"><?echo i18n("Uptime");?></th>
			<th width="60px"><?echo i18n("Mode");?></th>			
			<th width="60px"><?echo i18n("Rssi");?> (%)</th>
		</tr>
		</table>
	</div>
	<div class="gap"></div>
</div>
</form>
