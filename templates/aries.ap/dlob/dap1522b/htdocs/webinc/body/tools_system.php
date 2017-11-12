<div class="orangebox">
	<h1><?echo i18n("Save and Restore");?></h1>
	<p><?echo i18n("The current system settings can be saved as a file onto the local hard drive. You can upload any save settings file that was created by the DAP-1522B.");?></p>
</div>
<div class="blackbox">
	<h2><?echo i18n("Save and Restore");?></h2>
	<div class="gap"></div>
	<form id="dlcfgbin" action="dlcfg.cgi" method="post">
	<div class="textinput">
		<span class="name_l"><?echo i18n("Save Settings To Local Hard Drive");?></span>
		<span class="delimiter">:</span>
		<span>
			<input type="button" value="<?echo i18n("Save");?>" onClick="PAGE.OnClickDownload();" />
		</span>
	</div>
	</form>
	<form id="ulcfgbin" action="seama.cgi" method="post" enctype="multipart/form-data">
	<div class="textinput">
		<span class="name_l"><?echo i18n("Load Settings From Local Hard Drive");?></span>
		<span class="delimiter">:</span>
		<span>
			<input type="hidden" name="REPORT_METHOD" value="301" />
			<input type="hidden" name="REPORT" value="tools_sys_ulcfg.php" />
			<input type="file" id="ulcfg" name="sealpac" size="32" />
		</span>
	</div>
	<div class="textinput">
		<span class="name_l"></span>
		<span class="delimiter"></span>
		<span>
			<input type="button" value="<?echo i18n("Upload Settings");?>" onClick="PAGE.OnClickUpload();" />
		</span>
	</div>
	</form>
	<form>
	<div class="textinput">
		<span class="name_l"><?echo i18n("Restore To Factory Default Settings");?></span>
		<span class="delimiter">:</span>
		<span>
			<input type="button" value="<?echo i18n("Restore Device");?>" onClick="PAGE.OnClickFReset();" />
		</span>
	</div>
	</form>
	<form>
	<div class="textinput">
		<span class="name_l"><?echo i18n("Reboot The Device");?></span>
		<span class="delimiter">:</span>
		<span>
			<input type="button" value="<?echo i18n("Reboot");?>" onClick="PAGE.OnClickReboot();" />
		</span>
	</div>
	</form>
	<form action="tools_fw_rlt.php" method="post">
	<div class="textinput" <?if ($FEATURE_NOLANGPACK=="1") echo ' style="display:none;"';?> >
		<input type="hidden" name="ACTION" value="langclear" />
		<span class="name_l"><?echo i18n("Remove Language Pack");?></span>
		<span class="delimiter">:</span>
		<span>
			<input type="submit" value="<?echo i18n("Remove");?>" />
		</span>
	</div>
	</form>
	<div class="emptyline"></div>
</div>
