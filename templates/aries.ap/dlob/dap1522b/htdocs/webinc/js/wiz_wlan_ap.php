<script type="text/javascript">
function Page() {}
Page.prototype =
{
	services: "WIFI.PHYINF,PHYINF.WIFI,RUNTIME.WPS.WLAN-1.1",
	OnLoad: function()
	{
		this.ShowCurrentStage();
	},
	OnUnload: function() {},
	OnSubmitCallback: function (code, result)
	{
/*		if (this.stages[this.currentStage]==="wps_config")
		{	
			switch (code)
			{
				case "OK":
					if (OBJ("wps_pin_on").checked)	{for(var i=0; i<this.stages.length; i++) if(this.stages[i] === "wps_pin") {this.currentStage=i;break;}}
					else	{for(var i=0; i<this.stages.length; i++) if(this.stages[i] === "wps_pbc") {this.currentStage=i;break;}}
					this.ShowCurrentStage();				
					this.WPSInProgress();
					break;
				default:
					BODY.ShowAlert(result);
					break;
			}
		}
		else */if (this.stages[this.currentStage]==="manual_result")
		{	
			switch (code)
			{
			case "OK":
				setTimeout('self.location.href = "./wiz_wlan_setup.php"', 3000);
				break;
			case "BUSY":
				BODY.ShowAlert("<?echo i18n("Someone is configuring the device, please try again later.");?>");
				break;
			case "HEDWIG":
				if (result.Get("/hedwig/result")=="FAILED")
				{
					//FocusObj(result);
					//BODY.ShowAlert(result.Get("/hedwig/message"));
          this.currentStage--;
          this.ShowCurrentStage();			
          return false;					
				}
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
		}
		
		return true;
	},
	InitValue: function(xml)
	{
		PXML.doc = xml;
		
		this.wifip = PXML.FindModule("WIFI.PHYINF");
		PXML.CheckModule("WIFI.PHYINF", null, null, "ignore");		
		this.run_phyinf1 = PXML.FindModule("RUNTIME.WPS.WLAN-1.1");
		PXML.IgnoreModule("RUNTIME.WPS.WLAN-1.1");

		if (!this.wifip || !this.run_phyinf1)
		{
			BODY.ShowAlert("Initial() ERROR!!!");
			return false;
		}
		
		this.phyinf1 = GPBT(this.wifip, "phyinf", "uid", "WLAN-1.1", false);
		var wifi_ap = XG(this.phyinf1+"/wifi");
		this.wifip1 = GPBT(this.wifip+"/wifi", "entry", "uid", wifi_ap, false);		
		var wlan_enable = XG(this.phyinf1+"/active");
		var wps_enable = XG(this.wifip1+"/wps/enable");
		
/*		if(wps_enable !== "1" || wlan_enable !== "1") {
			OBJ("wps_mode").checked = false;
			OBJ("wps_mode").disabled = true;
			OBJ("manual_mode").checked = true;
		}
		else 
		{
			OBJ("wps_mode").disabled = false;
			OBJ("wps_mode").checked = true;
		}
		
		OBJ("wps_pin_on").checked = true;
		OBJ("wps_pbc_on").checked = true;
		OBJ("assign_key").checked = true;*/
		
		return true;
	},
	PreSubmit: function()
	{
		XS(this.wifip1+"/ssid", OBJ("ssid_input").value);
		XS(this.wifip1+"/ssidhidden", "0");
		XS(this.wifip1+"/authtype", "WPA+2PSK");
		XS(this.wifip1+"/encrtype", "TKIP+AES");
		XS(this.wifip1+"/nwkey/psk/passphrase", "");
		XS(this.wifip1+"/nwkey/psk/key", OBJ("key_input").value);
		XS(this.wifip1+"/wps/configured", "1");
		XS(this.phyinf1+"/active", "1");
    if(confirm('<?echo i18n("Do you want to bookmark \"D-Link AP Web Management\"");?>'))
	    addBookmark("<?echo i18n("D-Link AP Web Management");?>",document.URL);			
		return PXML.doc;
	},
	IsDirty: null,
	Synchronize: function() {},
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////
	wifip: null,
	wifip1: null,		
	phyinf1: null,
	run_phyinf1: null,	
	start_count_down: false,
	wps_timer: null,
	cd_timer: null,
	randomkey: null,
//	stages: new Array ("wiz_start", "set_config", "wps_config", "wps_pin", "wps_pbc", "wps_ok", "wps_fail", "wps_result", "manual", "manual_result"),
	stages: new Array ("wiz_start", "manual", "manual_result"),
	currentStage: 0,	// 0 ~ this.stages.length
	ShowCurrentStage: function()
	{
		for (var i=0; i<this.stages.length; i++)
		{
			if (i==this.currentStage)
				OBJ(this.stages[i]).style.display = "block";
			else
				OBJ(this.stages[i]).style.display = "none";
		}
		if (this.stages[this.currentStage] === "manual_result")
		{
			OBJ("ssid_manual").innerHTML = COMM_EscapeHTMLSC(OBJ("ssid_input").value);
			OBJ("key_manual").innerHTML = OBJ("key_input").value;
		}
	},
	CheckStage: function()
	{		
		if (this.stages[this.currentStage] === "manual")
		{
			if (OBJ("ssid_input").value === "")
			{
				alert("<?echo i18n("The SSID should not be blank.");?>");
				return false;
			}
			
			if (OBJ("ssid_input").value.charAt(0)===" " || OBJ("ssid_input").value.charAt(OBJ("ssid_input").value.length-1)===" ")
			{
				alert("<?echo i18n("The prefix or postfix of the SSID could not be blank.");?>");
				return false;
			}
			
			if (OBJ("key_input").value.length < 8 || OBJ("key_input").value.length > 63)
			{
				alert("<?echo i18n("The network key should be 8~63 characters.");?>");
				return false;
			}
		}	
		
		return true;
	},
	OnSubmit: function()
	{
		var ajaxObj = GetAjaxObj("WPS");
		var action = (OBJ("wps_pin_on").checked)? "PIN":"PBC";
		var uid = "WLAN-1.1";
		var value = (OBJ("wps_pin_on").checked)? OBJ("pincode").value:"00000000";
		ajaxObj.createRequest();
		ajaxObj.onCallback = function (xml)
		{
			ajaxObj.release();
			PAGE.OnSubmitCallback(xml.Get("/wpsreport/result"), xml.Get("/wpsreport/reason"));
		}
		
		ajaxObj.setHeader("Content-Type", "application/x-www-form-urlencoded");
		ajaxObj.sendRequest("wpsacts.php", "action="+action+"&uid="+uid+"&pin="+value);
		AUTH.UpdateTimeout();
	},
	WPSInProgress: function()
	{
		if (!this.start_count_down)
		{
			this.start_count_down = true;
			setTimeout('PAGE.WPSCountDown()',1000);
		}
		COMM_GetCFG(false, "RUNTIME.WPS.WLAN-1.1", function(xml) {PAGE.WPSInProgressCallBack(xml);});
	},	
	WPSInProgressCallBack: function(xml)
	{
		if (this.statep==null)	this.statep = "/postxml/module/runtime/phyinf/media/wps/enrollee/state";
		var state = xml.Get(this.statep);
		//hendry, sometimes this node is null since hostapd not quick enough to set node.
		if (state==="WPS_IN_PROGRESS" || state==="")	this.wps_timer = setTimeout('PAGE.WPSInProgress()',3000);
		else	this.ShowWPSMessage(state);
	},
	WPSCountDown: function()
	{
		var time = (OBJ("wps_pin_on").checked)? parseInt(OBJ("ct_pin").innerHTML, 10):parseInt(OBJ("ct_pbc").innerHTML, 10);
		if (time > 0)
		{		
			time--;
			this.cd_timer = setTimeout('PAGE.WPSCountDown()',1000);
			if(OBJ("wps_pin_on").checked)	OBJ("ct_pin").innerHTML = time;
			else	OBJ("ct_pbc").innerHTML = time;
		}
		else
		{
			clearTimeout(this.cd_timer);
			this.ShowWPSMessage("WPS_NONE");
		}
	},
	ShowWPSMessage: function(state)
	{
		switch (state)
		{
			case "WPS_NONE":
				for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "wps_fail") {this.currentStage=i;break;}}
				break;
			case "WPS_ERROR":
				for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "wps_fail") {this.currentStage=i;break;}}		
				break;
			case "WPS_OVERLAP":
				for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "wps_fail") {this.currentStage=i;break;}}
				break;
			case "WPS_IN_PROGRESS":
				break;
			case "WPS_SUCCESS":
				for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "wps_ok") {this.currentStage=i;break;}}
				break;
		}
		this.ShowCurrentStage();
		if (this.cd_timer)	clearTimeout(this.cd_timer);
		if (this.wps_timer)	clearTimeout(this.wps_timer);
		PAGE.start_count_down = false;
		OBJ("ct_pin").innerHTML = OBJ("ct_pbc").innerHTML = 120;
	},
	WPSResultCallBack: function(xml)
	{
		PXML.doc = xml;
		this.wifip = PXML.FindModule("WIFI.PHYINF");		
		
		var wlan11p = GPBT(this.wifip, "phyinf", "uid", "WLAN-1.1", false);
		var wifi = XG(wlan11p+"/wifi");
		var wifi_wps = GPBT(this.wifip+"/wifi", "entry", "uid", wifi, false);
		OBJ("ssid_wps").innerHTML = COMM_EscapeHTMLSC(XG(wifi_wps+"/ssid"));
		if (XG(wifi_wps+"/authtype")==="WPAPSK")
		{
			if(XG(wifi_wps+"/encrtype") === "TKIP")
				OBJ("security_wps").innerHTML = "<?echo i18n("WPA TKIP");?>";
			else if(XG(wifi_wps+"/encrtype") === "AES")
				OBJ("security_wps").innerHTML = "<?echo i18n("WPA AES");?>";
			else
				OBJ("security_wps").innerHTML = "<?echo i18n("WPA TKIP/AES");?>";
			OBJ("key_wps").innerHTML = XG(wifi_wps+"/nwkey/psk/key");				
		}
		else if (XG(wifi_wps+"/authtype")==="WPA2PSK")
		{
			if(XG(wifi_wps+"/encrtype") === "TKIP")
				OBJ("security_wps").innerHTML = "<?echo i18n("WPA2 TKIP");?>";
			else if(XG(wifi_wps+"/encrtype") === "AES")
				OBJ("security_wps").innerHTML = "<?echo i18n("WPA2 AES");?>";
			else
				OBJ("security_wps").innerHTML = "<?echo i18n("WPA2 TKIP/AES");?>";
			OBJ("key_wps").innerHTML = XG(wifi_wps+"/nwkey/psk/key");		
		}
		else if (XG(wifi_wps+"/authtype")==="WPA+2PSK")
		{
			OBJ("security_wps").innerHTML = "<?echo i18n("Auto (WPA or WPA2) TKIP/AES");?>";
			OBJ("key_wps").innerHTML = XG(wifi_wps+"/nwkey/psk/key");				
		}	
		else if ((XG(wifi_wps+"/authtype")==="OPEN" && XG(wifi_wps+"/encrtype")==="WEP") ||
				 (XG(wifi_wps+"/authtype")==="SHARED") ||
				 (XG(wifi_wps+"/authtype")==="WEPAUTO") ||
				 (XG(wifi_wps+"/authtype")==="BOTH"))
		{
			OBJ("security_wps").innerHTML = "WEP";
			var wep_defkey = XG(wifi_wps+"/nwkey/wep/defkey");
			OBJ("key_wps").innerHTML = XG(wifi_wps+"/nwkey/wep/key:"+wep_defkey);
		}
		else if (XG(wifi_wps+"/authtype")==="OPEN" && XG(wifi_wps+"/encrtype")==="NONE")
		{
			OBJ("security_wps").innerHTML = "NONE";
			OBJ("key_wps").innerHTML = "";								
		}		
	},
	OnClickWPSSwitch: function(method)
	{
		if(method === "pin")
		{	
			OBJ("wps_pin_on").checked = true;
			OBJ("wps_pbc_on").checked = false;
		}
		else if(method === "pbc")	
		{	
			OBJ("wps_pin_on").checked = false;
			OBJ("wps_pbc_on").checked = true;
		}
	},	
	OnClickPrev: function()
	{
		switch (this.stages[this.currentStage])
		{
			default:
				this.currentStage--;
				break;
		}
		this.ShowCurrentStage();
	},
	OnClickNext: function()
	{
		switch (this.stages[this.currentStage])
		{
/*			case "set_config":
				if (OBJ("wps_mode").checked)	
				{
					this.currentStage++;
					this.ShowCurrentStage();
				}	
				else if	(OBJ("manual_mode").checked)
				{	
					for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "manual") {this.currentStage=i;break;}}
					this.ShowCurrentStage();
				}
				break;
			case "wps_config":
				PAGE.OnSubmit();				
				break;	
			case "wps_ok":
				for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "wps_result") {this.currentStage=i;break;}}
				COMM_GetCFG(false, "WIFI.PHYINF", function(xml) {PAGE.WPSResultCallBack(xml);});
				this.ShowCurrentStage();
				break;
			case "wps_fail":
				for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "wps_config") {this.currentStage=i;break;}}
				this.ShowCurrentStage();
				break;					
			case "wps_result":
        if(confirm('<?echo i18n("Do you want to bookmark \"D-Link AP Web Management\"");?>'))
	        addBookmark("<?echo i18n("D-Link AP Web Management");?>",document.URL);
				self.location.href = "./wiz_wlan_setup.php";
				break;*/
			case "manual":
				if(!this.CheckStage()) return;
				for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "manual_result") {this.currentStage=i;break;}}
				this.ShowCurrentStage();
				break;	
			case "manual_result":
				BODY.OnSubmit();
				break;					
			default:
				this.currentStage++;
				this.ShowCurrentStage();
				break;
		}
	},
	OnClickCancel: function()
	{
		if (!COMM_IsDirty(false)||confirm("<?echo i18n("Do you want to abandon all changes you made to this wizard?");?>"))
			self.location.href = "./wiz_wlan_setup.php";
	},	
	OnChangeConfig: function(method)
	{
/*		if(method === "wps")
		{	
			OBJ("wps_mode").checked = true;
			OBJ("manual_mode").checked = false;
		}
		else if(method === "manual")	
		{	*/
			OBJ("wps_mode").checked = false;
			OBJ("manual_mode").checked = true;
//		}
	}
/*	OnChangeNetworkKey: function()
	{
		if(OBJ("assign_key").checked)	OBJ("key_input").value = "";
		else	OBJ("key_input").value = RandomHex(63);
	}	*/
}

function RandomHex(len)
{
	var c = "0123456789abcdef";
	var str = '';
	for (var i = 0; i < len; i+=1)
	{
		var rand_char = Math.floor(Math.random() * c.length);
		str += c.substring(rand_char, rand_char + 1);
	}
	return str;
}
function addBookmark(title,url) 
{
	if (window.sidebar) { 
		window.sidebar.addPanel(title, url,""); 
	}
	else if( document.all ) 
	{
		window.external.AddFavorite( url, title);
	}
	else if( window.opera && window.print ) 
	{
		return true;
	}	
}

</script>
