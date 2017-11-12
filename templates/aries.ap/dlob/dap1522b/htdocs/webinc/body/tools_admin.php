<form id="mainform" onsubmit="return false;">
<div class="orangebox">
	<h1><?echo i18n("Administrator Settings");?></h1>
	<!--p><?
		if ($USR_ACCOUNTS=="1")
			echo i18n("The 'admin' account can access the management interface.")." ".
			i18n("The admin has read/write access and can change password.");
		else
			echo i18n("The 'admin' and 'user' accounts can access the management interface.")." ".
			i18n("The admin has read/write access and can change passwords, while the user has read-only access.");
	?></p>
		<p><?echo i18n("By default there is no password configured.")." ".
		i18n("It is highly recommended that you create a password to keep your AP or wireless client secure.");?></p-->
	<p>
		<?echo i18n("Enter the new password in the \"New Password\" field and again in the next field to confirm. Click on \"Save Settings\" to execute the password change. ");?>
		<?echo i18n("The Password is case-sensitive, and can be made up of any keyboard characters. The new password must be between 0 and 15 characters in length.");?>
	</p>	
	<p><input type="button" value="<?echo i18n("Save Settings");?>" onClick="BODY.OnSubmit();" />
	    <input type="button" value="<?echo i18n("Don't Save Settings");?>" onClick="BODY.OnReload();" />
		<input id="rebootitem1" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
</div>
<div class="blackbox">
	<h2><?echo i18n("Password");?></h2>
	<p class="strong"><?echo i18n("Please enter the same password into both boxes, for confirmation.");?></p>
	<div class="textinput">
		<span class="name"><?echo i18n("New Password");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="admin_p1" type="password" size="20" maxlength="15" /></span>
	</div>
	<div class="textinput">
		<span class="name"><?echo i18n("Verify Password");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="admin_p2" type="password" size="20" maxlength="15" /></span>
	</div>
	<div class="gap"></div>
</div>
<div class="blackbox" <?if ($USR_ACCOUNTS=="1") echo 'style="display:none;"';?>>
	<h2><?echo i18n("User Password");?></h2>
	<p class="strong"><?echo i18n("Please enter the same password into both boxes, for confirmation.");?></p>
	<div class="textinput">
		<span class="name"><?echo i18n("Password");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="usr_p1" type="password" size="20" maxlength="15" /></span>
	</div>
	<div class="textinput">
		<span class="name"><?echo i18n("Verify Password");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="usr_p2" type="password" size="20" maxlength="15" /></span>
	</div>
	<div class="gap"></div>
</div>
<div class="blackbox">
	<h2><?echo i18n("Administration");?></h2>
	<div class="textinput">
		<span class="name"><?echo i18n("Enable Graphical Authentication");?></span>
		<span class="delimiter">:</span>
		<span class="value"><input id="en_captcha" type="checkbox" /></span>
	</div>

	<div class="gap"></div>
</div>
<p><input type="button" value="<?echo i18n("Save Settings");?>" onclick="BODY.OnSubmit();" />
	<input type="button" value="<?echo i18n("Don't Save Settings");?>" onclick="BODY.OnReload();" />
	<input id="rebootitem2" type="button" value="<?echo i18n("Reboot Now");?>" onclick="BODY.OnClickReboot();" /></p>
<form>
