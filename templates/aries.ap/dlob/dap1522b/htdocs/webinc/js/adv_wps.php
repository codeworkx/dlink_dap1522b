<script type="text/javascript">
function Page() {}
Page.prototype =
{
	services: "WIFI.PHYINF,PHYINF.WIFI,RUNTIME.WPS",
	OnLoad: function() {},
	OnUnload: function() {},
	OnSubmitCallback: function (code, result) 
	{ 
		BODY.TurnReboot(code, result);		
		return true; 
	},
	InitValue: function(xml)
	{
		PXML.doc = xml;
		this.wifip = PXML.FindModule("WIFI.PHYINF");
		this.wpsp  = PXML.FindModule("RUNTIME.WPS");
		if (!this.wifip  || !this.wpsp)
		{
			BODY.ShowAlert("Initial() ERROR!!!");
			return false;
		}
		this.wifip = GPBT(this.wifip+"/wifi", "entry", "uid", "WIFI-1", false);
		this.wpsp += "/runtime/wps/setting";
		OBJ("en_wps").checked = COMM_ToBOOL(XG(this.wifip+"/wps/enable"));
		if(  XG(this.wifip+"/ssidhidden") == 1  || XG("/postxml/module:1/phyinf:1/active") == 0 )
			{
				OBJ("en_wps").checked = false;
				OBJ("en_wps").disabled = true;
			}		
		if( (XG(this.wifip+"/authtype") == "WPA" ) ||  (XG(this.wifip+"/authtype") == "WPA2")  ||  (XG(this.wifip+"/authtype") == "WPA+2") )
			{				
				OBJ("en_wps").checked = false;
				OBJ("en_wps").disabled = true;
			}
			
		if (XG(this.wifip+"/wps/pin")=="")
			this.curpin = OBJ("pin").innerHTML = this.defpin;
		else
			this.curpin = OBJ("pin").innerHTML = XG(this.wifip+"/wps/pin");

		if (XG(this.wpsp+"/aplocked") != "1")
		{
			OBJ("lock_security").disabled = true;	
			OBJ("lock_security").checked = false;
		}
		else
		{
			OBJ("lock_security").disabled = false;
			OBJ("lock_security").checked = COMM_ToBOOL(XG(this.wpsp+"/aplocked"));
		}

		this.OnClickEnWPS();

		var rebootflag = "<?echo query("/runtime/reboot/status");?>";
		if(rebootflag != "1"){OBJ("rebootitem1").style.display="none"; OBJ("rebootitem2").style.display="none";}
		else {OBJ("rebootitem1").style.display=""; OBJ("rebootitem2").style.display="";}

		return true;
	},
	PreSubmit: function()
	{
		XS(this.wifip+"/wps/enable", (OBJ("en_wps").checked)? "1":"0");
		
		if(XG(this.wifip+"/authtype") == "SHARED" && OBJ("en_wps").checked)
		{
			  alert("<?echo i18n("Please change Authentication mode first!");?>");
			  return null;
		}				
		
		XS(this.wpsp+"/aplocked", (OBJ("lock_security").checked)? "1":"0");
		PXML.CheckModule("PHYINF.WIFI", "ignore","ignore", null); //we now use PHYINF.WIFI to activate service
		return PXML.doc;
	},
	IsDirty: null,
	Synchronize: function()
	{
		if (OBJ("pin").innerHTML!=this.curpin)
		{
			OBJ("mainform").setAttribute("modified", "true");
			XS(this.wifip+"/wps/pin", OBJ("pin").innerHTML);
		}
	},
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////
	wifip: null,
	defpin: "<?echo query("/runtime/devdata/pin");?>",
	curpin: null,
	OnClickEnWPS: function()
	{
		if (OBJ("en_wps").checked)
		{
			if (XG(this.wifip+"/wps/configured")=="0")
				OBJ("reset_cfg").disabled = true;
			else
				OBJ("reset_cfg").disabled = false;
			OBJ("reset_pin").disabled	= false;
			OBJ("gen_pin").disabled		= false;
			OBJ("go_wps").disabled		= false;
		}
		else
		{
			OBJ("reset_cfg").disabled	= true;
			OBJ("reset_pin").disabled	= true;
			OBJ("gen_pin").disabled		= true;
			OBJ("go_wps").disabled		= true;
			OBJ("lock_security").disabled	= true;
		}
	},
	OnClickResetCfg: function()
	{
		if (confirm("<?echo i18n("Are you sure you want to reset the device to Unconfigured?")."\\n";?>"))
		{
			OBJ("mainform").setAttribute("modified", "true");
			XS(this.wifip+"/ssid",			"dlink"	);
			XS(this.wifip+"/authtype",		"OPEN"	);
			XS(this.wifip+"/encrtype",		"NONE"	);
			XS(this.wifip+"/wps/configured","0"		);
			OBJ("lock_security").checked = false;
			BODY.OnSubmit();
		}
	},
	OnClickResetPIN: function()
	{
		OBJ("pin").innerHTML = this.defpin;
	},
	OnClickGenPIN: function()
	{
		var pin = "";
		var sum = 0;
		var check_sum = 0;
		var r = 0;
		for(var i=0; i<7; i++)
		{
			r = (Math.floor(Math.random()*9));
			pin += r;
			sum += parseInt(r, [10]) * (((i%2)==0) ? 3:1);
		}
		check_sum = (10-(sum%10))%10;
		pin += check_sum;
		OBJ("pin").innerHTML = pin;
	}
}
</script>
