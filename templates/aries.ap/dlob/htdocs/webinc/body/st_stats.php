<?
include "/htdocs/phplib/xnode.php";
function get_runtime_eth_path($uid)
{
	$p = XNODE_getpathbytarget("", "inf", "uid", $uid, 0);
	if($p == "") return $p;

	return XNODE_getpathbytarget("/runtime", "phyinf", "uid", query($p."/phyinf"));
}
function get_runtime_wifi_path($uid)
{
	$p = XNODE_getpathbytarget("", "phyinf", "wifi", $uid);
	if($p == "") return $p;

	return XNODE_getpathbytarget("/runtime", "phyinf", "uid", query($p."/uid"));
}

/* we reset the counters and get them immediately here, it may has the synchronization issue. */
/* Do RESET count */
if ($_POST["act"]!="")
{
	$p = get_runtime_eth_path("BRIDGE-1");		if ($p != "") set($p."/stats/reset", "dummy");
	$p = get_runtime_wifi_path("WIFI-1");	if ($p != "") set($p."/stats/reset", "dummy");	
}

$txp = "/stats/tx/packets";
$rxp = "/stats/rx/packets";
$txb = "/stats/tx/bytes";
$rxb = "/stats/rx/bytes";
$txe = "/stats/tx/error";
$rxe = "/stats/rx/error";


$p = get_runtime_eth_path("BRIDGE-1");
if ($p == "")
{
	$lan1_tx = i18n("N/A"); $lan1_rx = i18n("N/A");
}
else
{
	$lan1_txp = query($p.$txp);	$lan1_rxp = query($p.$rxp);
	$lan1_txb = query($p.$txb);	$lan1_rxb = query($p.$rxb);
	$lan1_txe = query($p.$txe);	$lan1_rxe = query($p.$rxe);
}

$p = get_runtime_wifi_path("WIFI-1");
if ($p == "")
{
	$wifi_tx = i18n("N/A");	$wifi_rx = i18n("N/A");
}
else		 
{
	$tx_cnt = query($p.$txp); if ($tx_cnt == "") $tx_cnt = "0";
	$rx_cnt = query($p.$rxp); if ($rx_cnt == "") $rx_cnt = "0";
	$wifi_txp = $tx_cnt;	$wifi_rxp = $rx_cnt;
	$tx_cnt = query($p.$txb); if ($tx_cnt == "") $tx_cnt = "0";
	$rx_cnt = query($p.$rxb); if ($rx_cnt == "") $rx_cnt = "0";
	$wifi_txb = $tx_cnt;	$wifi_rxb = $rx_cnt;
	$tx_cnt = query($p.$txe); if ($tx_cnt == "") $tx_cnt = "0";
	$rx_cnt = query($p.$rxe); if ($rx_cnt == "") $rx_cnt = "0";
	$wifi_txe = $tx_cnt;	$wifi_rxe = $rx_cnt;	
}

?>
<div class="orangebox">
	<h1><?echo i18n("Traffic Statistics");?></h1>
	<p>
	<?
		echo i18n("Traffic Statistics displays Receive and Transmit packets passing through the device.");		
	?>
	</p>
	&nbsp;
	<form id="mainform" name="resetcount" action="<?=$TEMP_MYNAME?>.php" method="POST">
			<input type="button" value="<?echo i18n("Refresh");?>" onclick="(function(){self.location='<?=$TEMP_MYNAME?>.php?r='+COMM_RandomStr(5);})();">&nbsp;
			<input type="submit" name="act" value="<?echo i18n("Reset");?>">
	</form>	
</div>
<div class="blackbox">
	<h2><?echo i18n("LAN Statistics");?></h2>
	<div class="centerline" align="center">		
		<table id="client_list" align="center" border="0" cellpadding="2" cellspacing="5" width="525">
		<tr>
			<th width="150" align="right"><?echo i18n("TX Packet Numbers:");?></th>
			<td align="left"><?echo i18n($lan1_txp);?></td>			
			<th width="150" align="right"><?echo i18n("RX Packet Numbers:");?></th>
			<td align="left"><?echo i18n($lan1_rxp);?></td>			
		</tr>		
		<tr>
			<th align="right"><?echo i18n("TX Packets Dropped:");?></th>
			<td align="left"><?echo i18n($lan1_txe);?></td>			
			<th align="right"><?echo i18n("RX Packets Dropped:");?></th>
			<td align="left"><?echo i18n($lan1_rxe);?></td>			
		</tr>
		<tr>
			<th align="right"><?echo i18n("TX Packets Bytes:");?></th>
			<td align="left"><?echo i18n($lan1_txb);?></td>			
			<th align="right"><?echo i18n("RX Packets Bytes:");?></th>
			<td align="left"><?echo i18n($lan1_rxb);?></td>			
		</tr>
		</table>
	</div>
	<div class="gap"></div>
	<h2><?echo i18n("Wireless Statistics");?></h2>
	<div class="centerline" align="center">		
		<table id="client_list" align="center" border="0" cellpadding="2" cellspacing="5" width="525">
		<tr>
			<th width="150" align="right"><?echo i18n("TX Packet Numbers:");?></th>
			<td align="left"><?echo i18n($wifi_txp);?></td>			
			<th width="150" align="right"><?echo i18n("RX Packet Numbers:");?></th>
			<td align="left"><?echo i18n($wifi_rxp);?></td>			
		</tr>		
		<tr>
			<th align="right"><?echo i18n("TX Packets Dropped:");?></th>
			<td align="left"><?echo i18n($wifi_txe);?></td>		
			<th align="right"><?echo i18n("RX Packets Dropped:");?></th>
			<td align="left"><?echo i18n($wifi_rxe);?></td>			
		</tr>
		<tr>
			<th align="right"><?echo i18n("TX Packets Bytes:");?></th>
			<td align="left"><?echo i18n($wifi_txb);?></td>		
			<th align="right"><?echo i18n("RX Packets Bytes:");?></th>
			<td align="left"><?echo i18n($wifi_rxb);?></td>		
		</tr>
		</table>
	</div>
</div>
