<form id="mainform" onsubmit="return false;">
<input type="hidden" id="whichsubmit" name="whichsubmit" value="qos">	
<div class="orangebox">
	<h1><?echo i18n("QoS");?></h1>
	<p>
		<?echo i18n("QoS stands for Quality of Service for Wireless Intelligent Stream Handling, a technology developed to enhance the experience of using a wireless network by prioritizing the traffic of different applications The DAP-1522B supports four priority levels.");?>
	</p>
	<p><input type="button" name="btn1" id="btn1" value="<?echo i18n("Save Settings");?>" onClick="PAGE.OnQoSSubmit('');" />
	<input type="button" name="btn2" id="btn2" value="<?echo i18n("Don't Save Settings");?>" onClick="BODY.OnReload();" />
	<input id="rebootitem1" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
</div>


<div class="blackbox">
	<h2><?echo i18n("Enable QoS");?></h2>
		<div class="textinput">
			<span class="name"><?echo i18n("Enable QoS");?> </span>
			<span class="delimiter">:</span>
			<input name="qos_enable" id="qos_enable" type="checkbox" onclick="PAGE.OnCheckEnable('');" value="1">
		</div>
		
		<div class="textinput">
			<span class="name"><?echo i18n("QOS Type");?> </span>
			<span class="delimiter">:</span>
			<select name="QoS_Type" id="QoS_Type" onChange="PAGE.OnChangeQoSType('');">
				<option value="0"><?echo i18n("Priority By Lan Port");?></option>
				<option value="1"><?echo i18n("Priority By Protocol");?></option>
			</select>
		</div>
</div>
			
			
<div class="blackbox" id="show_Port_Priority">
	<h2><?echo i18n("Port Priority");?></h2>
		<div class="textinput">
			<span class="name"><?echo i18n("Lan Port 1");?> </span>
			<span class="delimiter">:</span>
			<select name="LanPort_1st" id="LanPort_1st">
				<option value="7"><?echo i18n("Voice");?></option>
				<option value="5"><?echo i18n("Video");?></option>
				<option value="3"><?echo i18n("Best Effort");?></option>
				<option value="1"><?echo i18n("Background");?></option>				
			</select>		
	</div>
	
	<div class="textinput">
			<span class="name"><?echo i18n("Lan Port 2");?> </span>
			<span class="delimiter">:</span>
			<select name="LanPort_2nd" id="LanPort_2nd">
				<option value="7"><?echo i18n("Voice");?></option>
				<option value="5"><?echo i18n("Video");?></option>
				<option value="3"><?echo i18n("Best Effort");?></option>
				<option value="1"><?echo i18n("Background");?></option>			
			</select>		
	</div>
	
	<div class="textinput">
			<span class="name"><?echo i18n("Lan Port 3");?> </span>
			<span class="delimiter">:</span>
			<select name="LanPort_3rd" id="LanPort_3rd">
				<option value="7"><?echo i18n("Voice");?></option>
				<option value="5"><?echo i18n("Video");?></option>
				<option value="3"><?echo i18n("Best Effort");?></option>
				<option value="1"><?echo i18n("Background");?></option>			
			</select>
	</div>
	
	<div class="textinput">
			<span class="name"><?echo i18n("Lan Port 4");?> </span>
			<span class="delimiter">:</span>	
			<select name="LanPort_4th" id="LanPort_4th">
				<option value="7"><?echo i18n("Voice");?></option>
				<option value="5"><?echo i18n("Video");?></option>
				<option value="3"><?echo i18n("Best Effort");?></option>
				<option value="1"><?echo i18n("Background");?></option>			
			</select>	
	</div>
</div>		


<div class="blackbox" id="show_AdvanceQos_title">
	<h2><?echo i18n("Advanced Qos");?></h2>
		<div class="textinput">
			<span class="name"><?echo i18n("Ethernet to Wireless");?> </span>
			<span class="delimiter">:</span>
			<input maxlength=6 name="DownlinkTraffic" id="DownlinkTraffic" size=6>Mbits/sec 
		</div>
		
		<div class="textinput">
			<span class="name"><?echo i18n("Wireless to Ethernet");?> </span>
			<span class="delimiter">:</span>
			<input maxlength=6 name="UplinkTraffic" id="UplinkTraffic" size=6>Mbits/sec 
		</div>
		
		<div class="textinput">       
			<span class="name"><?echo i18n("ACK/DHCP/ICMP/DNS Priority");?> </span>
			<span class="delimiter">:</span>
			<select name="AUI_Priority" id="AUI_Priority" style="width: 100px;">
				<option value="0"><?echo i18n("Highest Priority");?></option>
				<option value="1"><?echo i18n("Second Priority");?></option>
				<option value="2"><?echo i18n("Third Priority");?></option>
				<option value="3"><?echo i18n("Low Priority");?></option>			
			</select>
				<?echo i18n("Limit");?> :
			<input maxlength=3 name="AUIlimit" id="AUIlimit" size=3>%
				<?echo i18n("Port");?> :
			<input name="aui_port" id="aui_port" size="15" value="53,67,68,546,547">	
		</div>
		
		<div class="textinput">                
			<span class="name"><?echo i18n("Web Traffic Priority");?> </span>
			<span class="delimiter">:</span>
			<select name="web_Priority" id="web_Priority" style="width: 100px;">
				<option value="0"><?echo i18n("Highest Priority");?></option>
				<option value="1"><?echo i18n("Second Priority");?></option>
				<option value="2"><?echo i18n("Third Priority");?></option>
				<option value="3"><?echo i18n("Low Priority");?></option>			
			</select>				
				<?echo i18n("Limit");?> :
			<input maxlength=3 name="weblimit" id="weblimit" size=3>%			
 				<?echo i18n("Port");?> :
			<input name="web_port" id="web_port" size="15" value="80,443,3128,8080">
		</div>
		
		<div class="textinput">		            
			<span class="name"><?echo i18n("Mail Traffic Priority");?> </span>
			<span class="delimiter">:</span>
			<select name="mail_Priority" id="mail_Priority" style="width: 100px;">
				<option value="0"><?echo i18n("Highest Priority");?></option>
				<option value="1"><?echo i18n("Second Priority");?></option>
				<option value="2"><?echo i18n("Third Priority");?></option>
				<option value="3"><?echo i18n("Low Priority");?></option>			
			</select>				
				<?echo i18n("Limit");?> :
			<input maxlength=3 name="maillimit" id="maillimit" size=3>%			
				<?echo i18n("Port");?> :
			<input name="mail_port" id="mail_port" size="15" value="25,110,465,995">
		</div>
		
		<div class="textinput">					                   
			<span class="name"><?echo i18n("Ftp Traffic Priority");?> </span>
			<span class="delimiter">:</span>
			<select name="ftp_Priority" id="ftp_Priority" style="width: 100px;">
				<option value="0"><?echo i18n("Highest Priority");?></option>
				<option value="1"><?echo i18n("Second Priority");?></option>
				<option value="2"><?echo i18n("Third Priority");?></option>
				<option value="3"><?echo i18n("Low Priority");?></option>			
			</select>				
				<?echo i18n("Limit");?> :
			<input maxlength=3 name="ftplimit" id="ftplimit" size=3>%			
				<?echo i18n("Port");?> :
			<input name="ftp_port" id="ftp_port" size="15" value="20,21">
		</div>
		
		<div class="textinput">					                   
			<span class="name"><?echo i18n("User Defined-1 Priority");?> </span>
			<span class="delimiter">:</span>
			<select name="user1_Priority" id="user1_Priority" style="width: 100px;">
				<option value="0"><?echo i18n("Highest Priority");?></option>
				<option value="1"><?echo i18n("Second Priority");?></option>
				<option value="2"><?echo i18n("Third Priority");?></option>
				<option value="3"><?echo i18n("Low Priority");?></option>			
			</select>				
				<?echo i18n("Limit");?> :
			<input maxlength=3 name="user1limit" id="user1limit" size=3>%			
				<?echo i18n("Port");?> :
			<input name="user1_port_s" id="user1_port_s" size="4">
				-
			<input name="user1_port_e" id="user1_port_e" size="4">
		</div>

		<div class="textinput">					                   
			<span class="name"><?echo i18n("User Defined-2 Priority");?> </span>
			<span class="delimiter">:</span>
			<select name="user2_Priority" id="user2_Priority" style="width: 100px;">
				<option value="0"><?echo i18n("Highest Priority");?></option>
				<option value="1"><?echo i18n("Second Priority");?></option>
				<option value="2"><?echo i18n("Third Priority");?></option>
				<option value="3"><?echo i18n("Low Priority");?></option>			
			</select>				
				<?echo i18n("Limit");?> :
			<input maxlength=3 name="user2limit" id="user2limit" size=3>%			
				<?echo i18n("Port");?> :
			<input name="user2_port_s" id="user2_port_s" size="4">
				-
			<input name="user2_port_e" id="user2_port_e" size="4">
		</div>

		<div class="textinput">					                   
			<span class="name"><?echo i18n("User Defined-3 Priority");?> </span>
			<span class="delimiter">:</span>
			<select name="user3_Priority" id="user3_Priority" style="width: 100px;">
				<option value="0"><?echo i18n("Highest Priority");?></option>
				<option value="1"><?echo i18n("Second Priority");?></option>
				<option value="2"><?echo i18n("Third Priority");?></option>
				<option value="3"><?echo i18n("Low Priority");?></option>			
			</select>				
				<?echo i18n("Limit");?> :
			<input maxlength=3 name="user3limit" id="user3limit" size=3>%			
				<?echo i18n("Port");?> :
			<input name="user3_port_s" id="user3_port_s" size="4">
				-
			<input name="user3_port_e" id="user3_port_e" size="4">
		</div>

		<div class="textinput">					                   
			<span class="name"><?echo i18n("User Defined-4 Priority");?> </span>
			<span class="delimiter">:</span>
			<select name="user4_Priority" id="user4_Priority" style="width: 100px;">
				<option value="0"><?echo i18n("Highest Priority");?></option>
				<option value="1"><?echo i18n("Second Priority");?></option>
				<option value="2"><?echo i18n("Third Priority");?></option>
				<option value="3"><?echo i18n("Low Priority");?></option>			
			</select>				
				<?echo i18n("Limit");?> :
			<input maxlength=3 name="user4limit" id="user4limit" size=3>%			
				<?echo i18n("Port");?> :
			<input name="user4_port_s" id="user4_port_s" size="4">
				-
			<input name="user4_port_e" id="user4_port_e" size="4">
		</div>

		<div class="textinput">
			<span class="name"><?echo i18n("Other Traffic Priority");?> </span>
			<span class="delimiter">:</span>
			<select name="other_Priority" id="other_Priority" style="width: 100px;">
				<option value="0"><?echo i18n("Highest Priority");?></option>
				<option value="1"><?echo i18n("Second Priority");?></option>
				<option value="2"><?echo i18n("Third Priority");?></option>
				<option value="3"><?echo i18n("Low Priority");?></option>			
			</select>
				<?echo i18n("Limit");?> :
			<input maxlength=3 name="otherlimit" id="otherlimit" size=3>%				
		</div>								
</div>

<p><input type="button" name="btn3" id="btn3" value="<?echo i18n("Save Settings");?>" onClick="PAGE.OnQoSSubmit('');" />
	<input type="button" name="btn4" id="btn4" value="<?echo i18n("Don't Save Settings");?>" onClick="BODY.OnReload();" />
	<input id="rebootitem2" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
</form>

