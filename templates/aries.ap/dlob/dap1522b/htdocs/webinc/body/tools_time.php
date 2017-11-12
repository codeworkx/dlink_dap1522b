<form id="mainform" onsubmit="return false;">
<div class="orangebox">
	<h1><?echo i18n("Time");?></h1>
	<p><?echo i18n("The Time and Date Configuration option allows you to configure, update, and maintain the correct time on the internal system clock.")." ".
		i18n("From this section you can set the time zone you are in and set the NTP (Network Time Protocol) Server.")." ".
		i18n("Daylight Saving can also be configured to automatically adjust the time when needed.");?></p>
	<p><input type="button" value="<?echo i18n("Save Settings");?>" onclick="BODY.OnSubmit();" />
		<input type="button" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" />
		<input id="rebootitem1" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
</div>
<div class="blackbox">
	<h2><?echo i18n("Time Configuration");?></h2>
	<div class="textinput">
		<span class="name"><?echo i18n("Current Time");?></span>
		<span class="delimiter">:</span>
		<span class="value" id="st_time"></span>
	</div>
	<div class="textinput">
		<span class="name"><?echo i18n("Time Zone");?></span>
		<span class="delimiter">:</span>
		<span class="value">
			<select id="timezone" class="tzselect" onchange="PAGE.OnClicktimezone(this.value)" style="width:98%;>
<?
				foreach ("/runtime/services/timezone/zone")
				echo '\t\t\t<option value="'.$InDeX.'">'.get("h","name").'</option>\n';
?>
			</select>
		</span>
	</div>
	<div class="textinput">
		<span class="name"><?echo i18n("Enable Daylight Saving");?></span>
		<span class="delimiter">:</span>
		<span class="value">
			<input type="checkbox"	id="daylight" onclick="PAGE.OnClickDaylight('');"/>&nbsp;
		</span>
	</div>
	<div class="textinput">
		<span class="name"><?echo i18n("Daylight Saving Offset");?></span>
		<span class="delimiter">:</span>
		<span class="value">
							<select id="offset" name="offset" size="1" style="WIDTH: 60px">
								<option value="1">+1:00</option>
							</select>
		</span>
	</div>	
	<div class="textinput">
				<span class="name"><?echo i18n("Daylight Saving Dates");?></span>
				<span class="delimiter">:</span>
				<table>
					<tr>
						<td style="WIDTH: 50px">&nbsp;</td>
						<td style="WIDTH: 50px"><?echo i18n("Month");?></td>
						<td style="WIDTH: 50px"><?echo i18n("Week");?></td>
						<td style="WIDTH: 80px"><?echo i18n("Day of Week");?></td>
						<td style="WIDTH: 50px"><?echo i18n("Time");?></td>
					</tr>
				</table>

	</div>

					<div class="textinput">
						<span class="name"></span>
						<span class="delimiter"></span>		
				<table>
					<tr>
						<td style="WIDTH: 50px"><?echo i18n("Dst Start");?></td>
						<td style="WIDTH: 50px">
								<select id="dst_s_mon" name="dst_s_mon" size="1" style="WIDTH: 50px">
								<option value="1"><?echo i18n("Jan");?></option>
								<option value="2"><?echo i18n("Feb");?></option>
								<option value="3"><?echo i18n("Mar");?></option>
								<option value="4"><?echo i18n("Apr");?></option>
								<option value="5"><?echo i18n("May");?></option>
								<option value="6"><?echo i18n("Jun");?></option>
								<option value="7"><?echo i18n("Jul");?></option>
								<option value="8"><?echo i18n("Aug");?></option>
								<option value="9"><?echo i18n("Sep");?></option>
								<option value="10"><?echo i18n("Oct");?></option>
								<option value="11"><?echo i18n("Nov");?></option>
								<option value="12"><?echo i18n("Dec");?></option>
							</select>
							</td>
						<td style="WIDTH: 50px">
														<select id="dst_s_week" name="dst_s_week" size="1" style="WIDTH: 50px">
								<option value="1"><?echo i18n("1st");?></option>
								<option value="2"><?echo i18n("2nd");?></option>
								<option value="3"><?echo i18n("3rd");?></option>
								<option value="4"><?echo i18n("4th");?></option>
								<option value="5"><?echo i18n("5th");?></option>
							</select>
							</td>
						<td style="WIDTH: 80px">
														<select id="dst_s_day" name="dst_s_day" size="1" style="WIDTH: 50px">
								<option value="0"><?echo i18n("Sun");?></option>
								<option value="1"><?echo i18n("Mon");?></option>
								<option value="2"><?echo i18n("Tue");?></option>
								<option value="3"><?echo i18n("Wed");?></option>
								<option value="4"><?echo i18n("Thu");?></option>
								<option value="5"><?echo i18n("Fri");?></option>
								<option value="6"><?echo i18n("Sat");?></option>
							</select>
							</td>
						<td style="WIDTH: 50px">
	<select id="dst_s_time" name="dst_s_time" size="1" style="WIDTH: 60px">
								<option value="0"><?echo i18n("12 am");?></option>
								<option value="1"><?echo i18n("1 am");?></option>
								<option value="2"><?echo i18n("2 am");?></option>
								<option value="3"><?echo i18n("3 am");?></option>
								<option value="4"><?echo i18n("4 am");?></option>
								<option value="5"><?echo i18n("5 am");?></option>
								<option value="6"><?echo i18n("6 am");?></option>
								<option value="7"><?echo i18n("7 am");?></option>
								<option value="8"><?echo i18n("8 am");?></option>
								<option value="9"><?echo i18n("9 am");?></option>
								<option value="10"><?echo i18n("10 am");?></option>
								<option value="11"><?echo i18n("11 am");?></option>
								<option value="12"><?echo i18n("12 pm");?></option>
								<option value="13"><?echo i18n("1 pm");?></option>
								<option value="14"><?echo i18n("2 pm");?></option>
								<option value="15"><?echo i18n("3 pm");?></option>
								<option value="16"><?echo i18n("4 pm");?></option>
								<option value="17"><?echo i18n("5 pm");?></option>
								<option value="18"><?echo i18n("6 pm");?></option>
								<option value="19"><?echo i18n("7 pm");?></option>
								<option value="20"><?echo i18n("8 pm");?></option>
								<option value="21"><?echo i18n("9 pm");?></option>
								<option value="22"><?echo i18n("10 pm");?></option>
								<option value="23"><?echo i18n("11 pm");?></option>
							</select>
							</td>
					</tr>
				</table>							
					
					</div>
					
					
					
					<div class="textinput">
						<span class="name"></span>
						<span class="delimiter"></span>								
										<table>
					<tr>
						<td style="WIDTH: 50px"><?echo i18n("Dst End");?></td>
						<td style="WIDTH: 50px">
								<select id="dst_e_mon" name="dst_e_mon" size="1" style="WIDTH: 50px">
								<option value="1"><?echo i18n("Jan");?></option>
								<option value="2"><?echo i18n("Feb");?></option>
								<option value="3"><?echo i18n("Mar");?></option>
								<option value="4"><?echo i18n("Apr");?></option>
								<option value="5"><?echo i18n("May");?></option>
								<option value="6"><?echo i18n("Jun");?></option>
								<option value="7"><?echo i18n("Jul");?></option>
								<option value="8"><?echo i18n("Aug");?></option>
								<option value="9"><?echo i18n("Sep");?></option>
								<option value="10"><?echo i18n("Oct");?></option>
								<option value="11"><?echo i18n("Nov");?></option>
								<option value="12"><?echo i18n("Dec");?></option>
							</select>
							</td>
						<td style="WIDTH: 50px">
														<select id="dst_e_week" name="dst_e_week" size="1" style="WIDTH: 50px">
								<option value="1"><?echo i18n("1st");?></option>
								<option value="2"><?echo i18n("2nd");?></option>
								<option value="3"><?echo i18n("3rd");?></option>
								<option value="4"><?echo i18n("4th");?></option>
								<option value="5"><?echo i18n("5th");?></option>
							</select>
							</td>
						<td style="WIDTH: 80px">
														<select id="dst_e_day" name="dst_e_day" size="1" style="WIDTH: 50px">
								<option value="0"><?echo i18n("Sun");?></option>
								<option value="1"><?echo i18n("Mon");?></option>
								<option value="2"><?echo i18n("Tue");?></option>
								<option value="3"><?echo i18n("Wed");?></option>
								<option value="4"><?echo i18n("Thu");?></option>
								<option value="5"><?echo i18n("Fri");?></option>
								<option value="6"><?echo i18n("Sat");?></option>
							</select>
							</td>
						<td style="WIDTH: 50px">
	<select id="dst_e_time" name="dst_e_time" size="1" style="WIDTH: 60px">
								<option value="0"><?echo i18n("12 am");?></option>
								<option value="1"><?echo i18n("1 am");?></option>
								<option value="2"><?echo i18n("2 am");?></option>
								<option value="3"><?echo i18n("3 am");?></option>
								<option value="4"><?echo i18n("4 am");?></option>
								<option value="5"><?echo i18n("5 am");?></option>
								<option value="6"><?echo i18n("6 am");?></option>
								<option value="7"><?echo i18n("7 am");?></option>
								<option value="8"><?echo i18n("8 am");?></option>
								<option value="9"><?echo i18n("9 am");?></option>
								<option value="10"><?echo i18n("10 am");?></option>
								<option value="11"><?echo i18n("11 am");?></option>
								<option value="12"><?echo i18n("12 pm");?></option>
								<option value="13"><?echo i18n("1 pm");?></option>
								<option value="14"><?echo i18n("2 pm");?></option>
								<option value="15"><?echo i18n("3 pm");?></option>
								<option value="16"><?echo i18n("4 pm");?></option>
								<option value="17"><?echo i18n("5 pm");?></option>
								<option value="18"><?echo i18n("6 pm");?></option>
								<option value="19"><?echo i18n("7 pm");?></option>
								<option value="20"><?echo i18n("8 pm");?></option>
								<option value="21"><?echo i18n("9 pm");?></option>
								<option value="22"><?echo i18n("10 pm");?></option>
								<option value="23"><?echo i18n("11 pm");?></option>
							</select>
							</td>
					</tr>
				</table>		
					</div>

</div>
<div class="blackbox">
	<h2><?echo i18n("Automatic Time Configuration");?></h2>
	<div class="textinput">
		<span class="name"><?echo i18n("Enable NTP Server");?></span>
		<span class="delimiter">:</span>
		<input name="ntp_enable" id="ntp_enable" onclick="PAGE.OnClickNtpEnb();" type="checkbox">
	</div>
	<div class="textinput">
		<span class="name"><?echo i18n("NTP Server Used");?></span>
		<span class="delimiter">:</span>
		<span class="value">
			<input id="ntp_display" type="text" value="" /> <<
			<select id="ntp_server" onchange="PAGE.OnChangeNtpDisplay();">
				<option value=""><?echo i18n("Select NTP Server");?></option>
				<option value="61.67.210.241">ntp1.dlink.com</option>
				<option value="218.211.253.172">ntp.dlink.com.tw</option>
			</select>
			<!--input id="ntp_sync" type="button" value="<?echo i18n("Update Now");?>" onclick="PAGE.OnClickNTPSync()"-->
		</span>
	</div>
	<p id=sync_msg></P>
	<div class="gap"></div>
</div>


<div class="blackbox">
	<h2><?echo i18n("Set the Time and Date Manually");?></h2>
<div class="textinput">
		<span class="name"><?echo i18n("Date And Time");?></span>
		<span class="delimiter">:</span>
<table>
	<tr>
		<td style="WIDTH: 30px"><?echo i18n("Year");?></td>
		<td style="WIDTH: 50px">
		  <select id="year" onchange="PAGE.OnChangeYear()" style="WIDTH: 50px"><?

			$i=2001;
			while ($i<2050) { $i++; echo "<option value=".$i.">".$i."</option>\n"; }

		  ?></select>
		</td>
		<td style="WIDTH: 40px"><?echo i18n("Month");?></td>
		<td style="WIDTH: 50px">
			<select id="month" onchange="PAGE.OnChangeMonth()"  style="WIDTH: 50px">
				<option value=1><? echo i18n("Jan"); ?></option>
				<option value=2><? echo i18n("Feb"); ?></option>
				<option value=3><? echo i18n("Mar"); ?></option>
				<option value=4><? echo i18n("Apr"); ?></option>
				<option value=5><? echo i18n("May"); ?></option>
				<option value=6><? echo i18n("Jun"); ?></option>
				<option value=7><? echo i18n("Jul"); ?></option>
				<option value=8><? echo i18n("Aug"); ?></option>
				<option value=9><? echo i18n("Sep"); ?></option>
				<option value=10><? echo i18n("Oct"); ?></option>
				<option value=11><? echo i18n("Nov"); ?></option>
				<option value=12><? echo i18n("Dec"); ?></option>
			</select>
		</td>
		<td style="WIDTH: 40px"><?echo i18n("Day");?></td>
		<td style="WIDTH: 50px">
			<select id="day" style="WIDTH: 50px"></select>
		</td>
	</tr>
	</table>	
	</div>	
<div class="textinput">
		<span class="name"></span>
		<span class="delimiter"></span>
<table>
	<tr>
		<td style="WIDTH: 30px"><?echo i18n("Hour");?></td>
		<td style="WIDTH: 50px">
			<select id="hour" style="WIDTH: 50px"><?
				$i=0;
				while ($i<24) { echo "<option value=".$i.">".$i."</option>\n"; $i++; }
			?></select>
		</td>
		<td style="WIDTH: 40px"><?echo i18n("Minute");?></td>
		<td style="WIDTH: 50px">
			<select id="minute" style="WIDTH: 50px"><?
				$i=0;
				while ($i<60) { echo "<option value=".$i.">".$i."</option>\n"; $i++; }
			?></select>
		</td>
		<td style="WIDTH: 40px"><?echo i18n("Second");?></td>
		<td style="WIDTH: 50px">
			<select id="second" style="WIDTH: 50px"><?
				$i=0;
				while ($i<60) { echo "<option value=".$i.">".$i."</option>\n"; $i++; }
			?></select>
		</td>
	</tr>
	</table>	
	</div>	
<div class="textinput">
		<span class="name"></span>
		<span class="delimiter"></span>
<table>
	<tr>
		<td style="WIDTH: 280px">
			<input type="button"	id="manual_sync" <? if(query("/runtime/device/langcode")!="en") echo 'style="width: 100%;"';?> value="<?echo i18n("Copy Your Computer's Time Settings");?>" onclick="PAGE.onClickManualSync();">
		</td>
	</tr>
	</table>
	</div>
	
	<div class="gap"></div> 
</div>
<p><input type="button" value="<?echo i18n("Save Settings");?>" onclick="BODY.OnSubmit();" />
	<input type="button" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" />
	<input id="rebootitem2" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
</form>
