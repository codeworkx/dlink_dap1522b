<form id="mainform" onsubmit="return false;">
<div class="orangebox">
        <h1><?echo i18n("LOGS");?></h1>
        <p>
                <?echo i18n("Use this option to view the device logs. You can define what types of events you want to view and the event levels to view.");?>
        </p>
</div>

<!--
<div class="orangebox">
	<h1><?echo i18n("View Log");?></h1>
	<p>
	<?echo i18n("The View Log displays the activities occurring on the")." ";echo query("/runtime/device/modelname");?>.
	</p>
	<div class="gap"></div>
	<p>
		<input type="button" value="<?echo i18n("Save Settings");?>" onclick="BODY.OnSubmit();" />
		<input type="button" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" />
	</p>
	<div class="gap"></div>
</div>
-->
<!--
<div class="blackbox">
	<h2><?echo i18n("Save Log File");?></h2>
	<p><?echo i18n("Save Log File To Local Hard Drive.");?> <input name="save_log" value="<?echo i18n("Save");?>" onclick="window.location.href='/log_get.php';" type="button"/></p>
	<div class="gap"></div>
</div>
-->
<div class="blackbox">
	<h2><?echo i18n("Log Option");?></h2>
	<table cellpadding="2" cellspacing="1" width="525"  style="display:none;">
	<tbody>
	<tr>
		<td align="right"><?echo i18n("Log Type");?>:</td>
		<td align="left"><input type="radio" id="sysact" name="Type" onclick='PAGE.OnClickChangeType("sysact");' modified="ignore"><?echo i18n("System Activity");?></td>
		<td align="left"><input type="radio" id="wlanact" name="Type" onclick='PAGE.OnClickChangeType("wlanact");' modified="ignore"><?echo i18n("Wireless Activity");?></td>		
	</tr>
	<tr>
		<td colspan="2"></td>
	</tr>
	<tr style="display:none;">
		<td align="right"><?echo i18n("Log Level");?>:</td>
		<td align="left"><input type="radio" id="LOG_dbg" name="Level"><?echo i18n("Critical");?></td>
		<td align="left"><input type="radio" id="LOG_warn" name="Level"><?echo i18n("Warning");?></td>
		<td align="left"><input type="radio" id="LOG_info" name="Level"><?echo i18n("Information");?></td>
	</tr>
	</tbody></table>
	<table cellpadding="2" cellspacing="1" width="525">
	<tbody>
	<tr>
		<td align="right"><?echo i18n("Log Type");?>:</td>
    <td align="left"><input type="checkbox" id="sys" name="sys"><?echo i18n("System Activity");?></td>
    <td align="left"><input type="checkbox" id="debug" name="debug"><?echo i18n("Debug Information");?></td>
    <td align="left"><input type="checkbox" id="attack" name="attack"><?echo i18n("Attacks");?></td>
	</tr>
	<tr>
		<td></td>
		<td align="left"><input type="checkbox" id="drop" name="drop"><?echo i18n("Dropped Packets");?></td>
    <td align="left"><input type="checkbox" id="notice" name="notice"><?echo i18n("Notice");?></td>		
    <td></td>
	</tr>	
	<tr>
		<td></td>		
		<td colspan=3> <input type="button" value="<?echo i18n("Apply Log Settings Now");?>" onClick="BODY.OnSubmit();"></td>
	</tr>		
	</tbody></table>			
	<div class="gap"></div>
</div>

<div class="blackbox">
	<h2><?echo i18n("Log Details");?></h2>
	<table cellpadding="1" cellspacing="1" border="0" width="525">
	<tr>
		<td align="left">
		<div>
			<input type=button value="<?echo i18n("First Page");?>" id="fp"  onclick="PAGE.OnClickToPage('1')">
			<input type=button value="<?echo i18n("Last Page");?>" id="lp"  onclick="PAGE.OnClickToPage('0')">
			<input type=button value="<?echo i18n("Previous");?>" id="pp"  onclick="PAGE.OnClickToPage('-1')">
			<input type=button value="<?echo i18n("Next");?>" id="np" onclick="PAGE.OnClickToPage('+1')">
			<input type=button value="<?echo i18n("Clear");?>" id="clear" onclick="PAGE.OnClickClear()">
			<input type=button value="<?echo i18n("Save log");?>" id="save_log" onclick="window.location.href='/log_get.php';">
			<input type=button value="<?echo i18n("Refresh");?>" id="refresh" onclick="window.location.href='/st_log.php';">
		</div>
		</td>
	</tr>
	<tr><td class=l_tb><div id="sLog"></div></td></tr>
	</table>
</div>
</form>
