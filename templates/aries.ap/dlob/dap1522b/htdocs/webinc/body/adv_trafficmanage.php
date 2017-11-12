<form id="mainform" onsubmit="return false;">
<input type="hidden" id="whichsubmit" name="whichsubmit" value="tramgr">
<div class="orangebox">
	<h1><?echo i18n("TRAFFIC MANAGER");?></h1>
	<p><?echo i18n("");?>
	<?echo i18n("");?>
	</p>
	<p>
		<input type="button" value="<?echo i18n("Save Settings");?>" onclick="PAGE.OnTraSubmit('');" />
		<input type="button" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" />
		<input id="rebootitem1" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" />
	</p>
</div>


<div class="blackbox">
	<h2><span><?echo i18n("TRAFFIC MANAGER");?></span></h2>
		<div class="textinput">
      <span class="name"><?echo i18n("Enable Traffic Manager");?> </span>
			<span class="delimiter">:</span>				
			<select name="Trafficmanage" id="Trafficmanage" onChange="PAGE.OnChangeTrafficmgr('');">
				<option value="0"><?echo i18n("Disable");?></option>
				<option value="1"><?echo i18n("Enable");?></option>
			</select>
		</div>
		
		<div class="textinput">
			<span class="name"><?echo i18n("Unlisted Clients Traffic");?> </span>
			<span class="delimiter">:</span>	
			<input id="deny" type=radio  value="0" name="UCT"><?echo i18n(" Deny");?>
			<input id="forward" type=radio  value="1" name="UCT"><?echo i18n(" Forward");?>
		</div>
		
		<div class="textinput">
			<span class="name"><?echo i18n("Ethernet to Wireless");?> </span>
			<span class="delimiter">:</span>
			<input maxlength=6 name="Eth0ToWirelessPrimary" id="Eth0ToWirelessPrimary" size=6 >Mbits/sec
		</div>
		
		<div class="textinput">
			<span class="name"><?echo i18n("Wireless to Ethernet");?> </span>
			<span class="delimiter">:</span>
			<input maxlength=6 name="WirelessToEth0Primary" id="WirelessToEth0Primary" size=6 >Mbits/sec															
		</div>
</div>

<div class="blackbox" id="add_rule_title">
	<h2><span id="add_edit_title"><?echo i18n("Add Traffic Manager Rule");?></span></h2>
		<div class="textinput">
			<span class="name"><?echo i18n("Name");?> </span>
			<span class="delimiter">:</span>
			<input maxlength=15 name="name" id="name" size=15 value="">	
		</div>
		
		<div class="textinput">
			<span class="name"><?echo i18n("Client IP");?>(optional)</span>
			<span class="delimiter">:</span>
			<input maxlength=15 name="ClientIP" id="ClientIP" size=15 value="">		
		</div>
		
		<div class="textinput">				
			<span class="name"><?echo i18n("Client Mac");?>(optional)</span>
			<span class="delimiter">:</span>
			<input maxlength=17 name="ClientMac" id="ClientMac" size=17 value="">
		</div>
		
		<div class="textinput">	
			<span class="name"><?echo i18n("Ethernet to Wireless");?></span>
			<span class="delimiter">:</span>
			<input maxlength=6 name="Eth0ToWireless" id="Eth0ToWireless" size=6 value="">Mbits/sec
		</div>
		
		<div class="textinput">
			<span class="name"><?echo i18n("Wireless to Ethernet");?> </span>
			<span class="delimiter">:</span>
			<input maxlength=6 name="WirelessToEth0" id="WirelessToEth0" size=6 value="">Mbits/sec
		</div>
		
		<div class="textinput">	
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;					
			<input type=button id="trasubmit" value='<?echo i18n("Add");?>' onClick="PAGE.OnClickTraSubmit('');">&nbsp;&nbsp;&nbsp;
			<input type=button id="tracancel" value='<?echo i18n("Cancel");?>' onClick="PAGE.OnClickTraCancel('');">
		</div>
</div>
		
				
<div class="blackbox" id="show_rule_information">
	<h2><?echo i18n("Traffic Manager List");?></h2>
			<table class="general" id="tratable">			
			<tr>
				<td width="80"><center><?echo i18n("Name");?></center></td>
				<td width="90"><center><?echo i18n("Client IP");?></center></td>
				<td width="120"><center><?echo i18n("Client Mac");?></center></td>				
				<td width="75"><center><?echo i18n("Ethernet to Wireless");?></center></td>
				<td width="75"><center><?echo i18n("Wireless to Ethernet");?></center></td>
				<td width="30"><center><?echo i18n("Edit");?></td>
				<td width="20"><center><?echo i18n("Del");?></td>
			</tr>
			</table>
		<div class="gap"></div>
</div>

<p><input type="button" value="<?echo i18n("Save Settings");?>" onclick="PAGE.OnTraSubmit('');" />
<input type="button" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" />
<input id="rebootitem2" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
</form>
