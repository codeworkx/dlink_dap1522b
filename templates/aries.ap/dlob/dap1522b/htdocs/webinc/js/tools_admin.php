<script type="text/javascript">
function Page() {}
Page.prototype =
{
	services: "DEVICE.ACCOUNT",
	OnLoad: function(){},
	OnUnload: function() {},
	OnSubmitCallback: function (code, result)
	{
		BODY.ShowContent();
		switch (code)
		{
		case "OK":
			BODY.TurnReboot(code, result);
			break;
		case "BUSY":
			BODY.ShowAlert("<?echo i18n("Someone is configuring the device, please try again later.");?>");
			break;
		case "HEDWIG":
			BODY.ShowAlert(result.Get("/hedwig/message"));
			break;
		case "PIGWIDGEON":
			if (result.Get("/pigwidgeon/message")=="no power")
			{
				BODY.NoPower();
			}
			else
			{
				BODY.ShowAlert(result.Get("/pigwidgeon/message"));
			}
			break;
		}
		return true;
	},
	InitValue: function(xml)
	{
		PXML.doc = xml;
		if (!this.Initial()) return false;
		return true;
	},
	PreSubmit: function()
	{
		if (!this.SaveXML()) return null;
		return PXML.doc;
	},
	IsDirty: null,
	Synchronize: function() {},
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////
	admin: null,
	usr: null,
	actp: null,
	captcha: null,
	rcp: null,
	rport: null,
	rgmode: <?if (query("/runtime/device/layout")=="bridge") echo "false"; else echo "true";?>,
	Initial: function()
	{
		this.actp = PXML.FindModule("DEVICE.ACCOUNT");
		
		if (!this.actp)
		{
			BODY.ShowAlert("Initial() ERROR!!!");
			return false;
		}
		this.captcha = this.actp + "/device/session/captcha";
		this.actp += "/device/account";
		
		this.admin = OBJ("admin_p1").value = OBJ("admin_p2").value = XG(this.actp+"/entry:1/password");
		this.usr = OBJ("usr_p1").value = OBJ("usr_p2").value = XG(this.actp+"/entry:2/password");
		OBJ("en_captcha").checked = COMM_EqBOOL(XG(this.captcha), true);

		var rebootflag = "<?echo query("/runtime/reboot/status");?>";
		if(rebootflag != "1"){OBJ("rebootitem1").style.display="none"; OBJ("rebootitem2").style.display="none";}
		else {OBJ("rebootitem1").style.display=""; OBJ("rebootitem2").style.display="";}

		return true;
	},
	SaveXML: function()
	{
		if (strchk_psd(OBJ("admin_p1").value) == false)
		{
			BODY.ShowAlert("<?echo i18n("Invalid Password.");?>");
			return false;
		}
		if (!COMM_EqSTRING(OBJ("admin_p1").value, OBJ("admin_p2").value))
		{
			BODY.ShowAlert("<?echo i18n("Password and Verify Password do not match. Please reconfirm admin password.");?>");
			return false;
		}
		if (!COMM_EqSTRING(OBJ("admin_p1").value, this.admin))
		{
			XS(this.actp+"/entry:1/password", OBJ("admin_p1").value);
		}
		if (strchk_psd(OBJ("usr_p1").value) === "false")
		{
			BODY.ShowAlert("<?echo i18n("Invalid Password.");?>");
			return false;
		}
		if (!COMM_EqSTRING(OBJ("usr_p1").value, OBJ("usr_p2").value))
		{
			BODY.ShowAlert("<?echo i18n("Password and Verify Password do not match. Please reconfirm user password.");?>");
			return false;
		}
		if (!COMM_EqSTRING(OBJ("usr_p1").value, this.usr))
		{
			XS(this.actp+"/entry:2/password", OBJ("usr_p1").value);
		}
		if (OBJ("en_captcha").checked)
		{
			XS(this.captcha, "1");
			BODY.enCaptcha = true;
		}
		else
		{
			XS(this.captcha, "0");
			BODY.enCaptcha = false;
		}
		return true;
	}
}
function strchk_psd(fData)
{
    for (var i=0;i<fData.length;i++)
    {
       if ((fData.charCodeAt(i) < 0) || (fData.charCodeAt(i) > 255))
   		{
   			return false;
   		}
    }
}
</script>
