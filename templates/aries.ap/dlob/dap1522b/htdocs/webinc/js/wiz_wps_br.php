<script type="text/javascript">
function Page() {}
Page.prototype =
{
	services: "WIFI.PHYINF,PHYINF.WIFI,RUNTIME.WPS.WLAN-2",
	OnLoad: function()
	{
		this.ShowCurrentStage();		
	},
	OnUnload: function() {},
	OnSubmitCallback: function (code, result)
	{
		if (this.stages[this.currentStage]==="wps_config")
		{
			switch (code)
			{
				case "OK":
					if (OBJ("wps_pin_on").checked)	
					{
						for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "wps_pin") { this.currentStage=i; break;}}
					}
					else	
					{
						for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "wps_pbc") { this.currentStage=i; break;}}
					}
					this.ShowCurrentStage();				
					this.WPSInProgress();
					break;
				default:
					for(var i=0; i<this.stages.length; i++)	
					{
						if(this.stages[i] === "wps_fail")	
						{
							this.currentStage = i;
							break;
						}
					}
					this.ShowCurrentStage();
					break;
			}
		}
		else if (this.stages[this.currentStage]==="site_survey_scan" || this.stages[this.currentStage]==="site_survey")
		{
			switch (code)
			{
				case "OK":
					this.SiteSurvey();
					break;
				default:
					BODY.ShowAlert(result);
					for(var i=0; i<this.stages.length; i++)	{if(this.stages[i] === "ssid")	{this.currentStage = i; break;}};
					this.ShowCurrentStage();
					break;
			}			
		}	
		else if (this.stages[this.currentStage]==="final")
		{	
			switch (code)
			{
			case "OK":
				setTimeout('self.location.href = "./wiz_wlan_setup.php"', 8000);
				break;
			case "BUSY":
				BODY.ShowAlert("<?echo i18n("Someone is configuring the device, please try again later.");?>");
				break;
			case "HEDWIG":
				if (result.Get("/hedwig/result")=="FAILED")
				{
					BODY.ShowAlert(result.Get("/hedwig/message"));
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
		this.run_phyinf_br = PXML.FindModule("RUNTIME.WPS.WLAN-2");

		if (!this.wifip || !this.run_phyinf_br)
		{
			BODY.ShowAlert("Initial() ERROR!!!");
			return false;
		}
		
		this.phyinf_br = GPBT(this.wifip, "phyinf", "uid", "WLAN-2", false);
		var wifi_br = XG(this.phyinf_br+"/wifi");
		this.wifip_br = GPBT(this.wifip+"/wifi", "entry", "uid", wifi_br, false);
		
		var wlan_enable = XG(this.phyinf_br+"/active");
		var wps_enable = XG(this.wifip_br+"/wps/enable");
		if(wlan_enable !== "1" ||  wps_enable !== "1") 
		{
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
		OBJ("wps_pbc_on").checked = false;
		
		if (XG(this.wifip_br+"/wps/pin")=="")
			this.curpin = OBJ("pincode").innerHTML = this.defpin;
		else
			this.curpin = OBJ("pincode").innerHTML = XG(this.wifip_br+"/wps/pin");
			
		this.OnChangeSecurity("wpa_auto");		
		
		PAGE.OnClickNext();
		PAGE.OnClickNext();
		
		return true;
	},
	PreSubmit: function()
	{
		XS(this.wifip_br+"/ssidhidden", "0");
		XS(this.wifip_br+"/wps/configured", "1");
		XS(this.phyinf_br+"/active", "1");
		if(OBJ("manual_mode").checked)
		{
			XS(this.wifip_br+"/ssid", OBJ("ssid_input").value);
			if(OBJ("security_none").checked)
			{
				XS(this.wifip_br+"/authtype", "OPEN");
				XS(this.wifip_br+"/encrtype", "NONE");
			}	
			else if(OBJ("security_wep").checked) 
			{
				XS(this.wifip_br+"/authtype", "BOTH");
				XS(this.wifip_br+"/encrtype", "WEP");
				XS(this.wifip_br+"/nwkey/wep/size", "");
				XS(this.wifip_br+"/nwkey/wep/ascii", "");	
				XS(this.wifip_br+"/nwkey/wep/defkey", "1");
				XS(this.wifip_br+"/nwkey/wep/key", OBJ("wep_input").value);				
			}
			else if(OBJ("security_wpa").checked || OBJ("security_wpa2").checked || OBJ("security_wpa_auto").checked) 
			{
				if(OBJ("security_wpa").checked)	
					XS(this.wifip_br+"/authtype", "WPAPSK");
				else if(OBJ("security_wpa2").checked) 
					XS(this.wifip_br+"/authtype", "WPA2PSK");
				else 
					XS(this.wifip_br+"/authtype", "WPA+2PSK");	
				
				if(this.SS_encrtype[this.site_survey_num]==="TKIP")	
					XS(this.wifip_br+"/encrtype", "TKIP");
				else if(this.SS_encrtype[this.site_survey_num]==="AES")
					XS(this.wifip_br+"/encrtype", "AES");
				else
					XS(this.wifip_br+"/encrtype", "TKIP+AES");
					
				XS(this.wifip_br+"/nwkey/psk/passphrase", "");
				XS(this.wifip_br+"/nwkey/psk/key", OBJ("wpa_input").value);
			}
		}
		else if(OBJ("wps_mode").checked)
		{
			setTimeout('self.location.href = "./wiz_wlan_setup.php"', 8000);
		}
		PXML.CheckModule("WIFI.PHYINF", null,null, "ignore");
		PXML.IgnoreModule("RUNTIME.WPS.WLAN-2");
		return PXML.doc;
	},
	Synchronize: function() {},
	IsDirty: function() 
	{
		return this.Todirty;
	},
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////
	Todirty: false,
	wifip: null,
	wifip_br: null,		
	phyinf_br: null,
	run_phyinf_br: null,	
	start_count_down: false,
	wps_timer: null,
	cd_timer: null,
	site_survey_timer: null,
	site_survey_cnt: 0,
	site_survey_num: 0,
	SS_ssid: new Array(),
	SS_authtype: new Array(),
	SS_encrtype: new Array(),
	randomkey: null,
	stages: new Array ("wiz_start", "set_config", "wps_config", "wps_pin", "wps_pbc", "wps_ok", "wps_fail","wps_result", "ssid", "site_survey_scan", "site_survey", "security", "wpa_key", "wep_key", "final"),
	currentStage: 0,	// 0 ~ this.stages.length
	defpin: "<? echo query("/runtime/devdata/pin");?>",
	curpin: null,
	ShowCurrentStage: function()
	{
		for (var i=0; i<this.stages.length; i++)
		{
			if (i==this.currentStage)
				OBJ(this.stages[i]).style.display = "block";
			else
				OBJ(this.stages[i]).style.display = "none";
		}
		
		if (this.stages[this.currentStage]==="site_survey")	OBJ("content").style.backgroundColor="#DFDFDF";
		else	OBJ("content").style.backgroundColor="white";
	},
	CheckStage: function()
	{		
		if (this.stages[this.currentStage] === "ssid") 
		{
			if (OBJ("ssid_input").value === "")
			{
				alert("<?echo i18n("The SSID should not be blank.");?>");
				return false;
			}
		}
		
		if (OBJ("ssid_input").value.charAt(0)===" " || OBJ("ssid_input").value.charAt(OBJ("ssid_input").value.length-1)===" ")
		{
			alert("<?echo i18n("The prefix or postfix of the SSID could not be blank.");?>");
			return false;
		}
		
		if (this.stages[this.currentStage] === "site_survey")
		{
			OBJ("ssid_input").value = this.SS_ssid[this.site_survey_num];
			if(this.SS_encrtype[this.site_survey_num]==="NONE")	this.OnChangeSecurity("none");
			else if(this.SS_encrtype[this.site_survey_num]==="WEP")	this.OnChangeSecurity("wep");
			else if(this.SS_authtype[this.site_survey_num]==="WPAPSK")	this.OnChangeSecurity("wpa");
			else if(this.SS_authtype[this.site_survey_num]==="WPA2PSK")	this.OnChangeSecurity("wpa2");			
			else	this.OnChangeSecurity("wpa_auto");
			if(this.site_survey_timer)	clearTimeout(this.site_survey_timer);			
		}	
		
		if (this.stages[this.currentStage] === "wpa_key") 
		{
			if (OBJ("wpa_input").value.length < 8 || OBJ("wpa_input").value.length > 63)
			{
				alert("<?echo i18n("The network key should be 8~63 characters.");?>");
				return false;
			}
		}
		
		if (this.stages[this.currentStage] === "wep_key")
		{
			if (OBJ("wep_type").value === "hex_10" && (OBJ("wep_input").value.length !== 10 || !IsHexadecimal(OBJ("wep_input").value)))
			{
				alert("<?echo i18n("The WEP key should be")." 10 ".i18n("hexadecimal numbers");?>");
				return false;
			}
			if (OBJ("wep_type").value === "ascii_5" && OBJ("wep_input").value.length !== 5)
			{
				alert("<?echo i18n("The WEP key should be")." 5 ".i18n("characters long");?>");
				return false;
			}
			if (OBJ("wep_type").value === "hex_26" && (OBJ("wep_input").value.length !== 26 || !IsHexadecimal(OBJ("wep_input").value)))
			{
				alert("<?echo i18n("The WEP key should be")." 26 ".i18n("hexadecimal numbers");?>");
				return false;
			}
			if (OBJ("wep_type").value === "ascii_13" && OBJ("wep_input").value.length !== 13)
			{
				alert("<?echo i18n("The WEP key should be")." 13 ".i18n("characters long");?>");
				return false;
			}
		}
		
		return true;
	},
	OnSubmit: function()
	{	
		if (this.stages[this.currentStage]==="wps_config" || this.stages[this.currentStage]==="wps_fail")
		{
			var ajaxObj = GetAjaxObj("WPS");
			var action = (OBJ("wps_pin_on").checked)? "PIN":"PBC";
			var uid = "WLAN-2";
			var value = (OBJ("wps_pin_on").checked)? OBJ("pincode").innerHTML:"00000000";
			ajaxObj.createRequest();
			ajaxObj.onCallback = function (xml)
			{
				ajaxObj.release();
				PAGE.OnSubmitCallback(xml.Get("/wpsreport/result"), xml.Get("/wpsreport/reason"));
			}
			
			ajaxObj.setHeader("Content-Type", "application/x-www-form-urlencoded");
			ajaxObj.sendRequest("wpsacts.php", "action="+action+"&uid="+uid+"&pin="+value);
			AUTH.UpdateTimeout();
		}
		else if (this.stages[this.currentStage]==="ssid" || this.stages[this.currentStage]==="site_survey_scan" || this.stages[this.currentStage]==="site_survey")
		{
			if (this.stages[this.currentStage]==="ssid")
			{
				for(var i=0; i<this.stages.length; i++) if(this.stages[i] === "site_survey_scan") {this.currentStage=i; break;}
				this.ShowCurrentStage();			
			}
	
			var ajaxObj = GetAjaxObj("SITESURVEY");
			var action = "SITESURVEY";
			ajaxObj.createRequest();
			ajaxObj.onCallback = function (xml)
			{
				ajaxObj.release();
				PAGE.OnSubmitCallback(xml.Get("/sitesurveyreport/result"), xml.Get("/sitesurveyreport/reason"));
			}
			
			ajaxObj.setHeader("Content-Type", "application/x-www-form-urlencoded");
			ajaxObj.sendRequest("sitesurvey.php", "action="+action);
			AUTH.UpdateTimeout();
		}
		else if(this.stages[this.currentStage]==="final")
		{
			BODY.OnSubmit();
		}
	},
	WPSInProgress: function()
	{
		if (!this.start_count_down)
		{
			this.start_count_down = true;
			this.cd_timer = setTimeout('PAGE.WPSCountDown()',1000);
		}
		setTimeout('COMM_GetCFG(false, "RUNTIME.WPS.WLAN-2", function(xml) {PAGE.WPSInProgressCallBack(xml);})',1000);
	},
	WPSInProgressCallBack: function(xml)
	{
		if (this.statep==null)	this.statep = "/postxml/module/runtime/phyinf/media/wps/enrollee/state";
		var state = xml.Get(this.statep);
		//hendry, sometimes this node is null since hostapd not quick enough to set node.
		if (state==="WPS_IN_PROGRESS" || state==="")	this.wps_timer = setTimeout('PAGE.WPSInProgress()',2000);
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
		if (this.cd_timer)	clearTimeout(this.cd_timer);
		if (this.wps_timer)	clearTimeout(this.wps_timer);
		this.start_count_down = false;
		OBJ("ct_pin").innerHTML = OBJ("ct_pbc").innerHTML = 120;
		this.ShowCurrentStage();
	},
	SiteSurvey: function()
	{
		COMM_GetCFG(false, "RUNTIME.SITESURVEY", function(xml) {PAGE.SiteSurveyCallback(xml);});	
	},
	SiteSurveyCallback: function(xml)
	{
		if (this.stages[this.currentStage]==="site_survey_scan")
		{
			for(var i=0; i<this.stages.length; i++) if(this.stages[i] === "site_survey") {this.currentStage=i; break;}
			this.ShowCurrentStage();
		}	
		
		/*Show site survey table*/
		var SiteSurveyP = "/postxml/module/runtime/wifi_tmpnode/sitesurvey/entry";
		this.site_survey_cnt = xml.Get(SiteSurveyP+"#");
		BODY.CleanTable("SiteSurveyTable");
		for (var i=1; i<=this.site_survey_cnt; i++)
		{	
			SiteSurveyPath = SiteSurveyP+":"+i; 
			this.SS_ssid[i] = xml.Get(SiteSurveyPath+"/ssid");
			/*Modify the length of column in site survey table for very long SSID name.*/		
			var SS_bssid = xml.Get(SiteSurveyPath+"/macaddr");
			var channel = xml.Get(SiteSurveyPath+"/channel");
			var wlmode = xml.Get(SiteSurveyPath+"/wlmode");
			var SS_channel = channel+"("+wlmode.substring(2, wlmode.length)+")";
			this.SS_authtype[i] = xml.Get(SiteSurveyPath+"/authtype");
			this.SS_encrtype[i] = xml.Get(SiteSurveyPath+"/encrtype");
			var SS_encrypt = this.SS_encrtype[i]+"/"+this.SS_authtype[i];
			var SS_signal = xml.Get(SiteSurveyPath+"/rssi")+"%";
			/* If we meet hidden SSID AP, we just disable radio button. 
			   The window can be a viewer to see what APs in the space.
			*/
			var strlen_SS_ssid=this.SS_ssid[i].length;
			var rows_ssid = Math.floor(strlen_SS_ssid/16);
			var suffix_ssid = Math.ceil(strlen_SS_ssid/16);
			var SS_ssidm="";
			
			var SS_radio;
			if(strlen_SS_ssid > 0)
			{
				for(var j=0; j <= rows_ssid; j++)
				{
					if(j < rows_ssid) SS_ssidm = SS_ssidm + COMM_EscapeHTMLSC(this.SS_ssid[i].substring(16*j, 16 + 16*j))+"<br>";
					else if(suffix_ssid !== 0) SS_ssidm = SS_ssidm + COMM_EscapeHTMLSC(this.SS_ssid[i].substring(16*j, strlen_SS_ssid));
				}
				SS_radio = '<input id="Site_Survey_'+i+'" type="radio" onClick="PAGE.OnChangeSSRadio('+i+');" />';
			}
			else
			{
				SS_ssidm = "<font color=\"#FF0000\"><?echo i18n("Hidden AP");?></font>";
				SS_radio = '<input id="Site_Survey_'+i+'" name="Site_Survey_'+i+'" type="radio" onClick="PAGE.OnChangeSSRadio('+i+');" disabled />';
			}

			var data = [SS_ssidm, SS_bssid, SS_channel, "AP", SS_encrypt, SS_signal, SS_radio];	
			var type = ["","text","text","text","text","text",""];
			BODY.InjectTable("SiteSurveyTable", "SS_table_"+i, data, type);
		}
		this.site_survey_timer = setTimeout('PAGE.OnSubmit()',10000);
	},
	WPSResultCallBack: function(xml)
	{
		PXML.doc = xml;
		this.wifip = PXML.FindModule("WIFI.PHYINF");
		this.phyinf_br = GPBT(this.wifip, "phyinf", "uid", "WLAN-2", false);
		var wifi_br = XG(this.phyinf_br+"/wifi");
		this.wifip_br = GPBT(this.wifip+"/wifi", "entry", "uid", wifi_br, false);
		

		OBJ("ssid_wps").innerHTML = COMM_EscapeHTMLSC(XG(this.wifip_br+"/ssid"));
		if (XG(this.wifip_br+"/authtype")==="WPAPSK")
		{
			if(XG(this.wifip_br+"/encrtype") === "TKIP")
				OBJ("security_wps").innerHTML = "<?echo i18n("WPA TKIP");?>";
			else if(XG(this.wifip_br+"/encrtype") === "AES")
				OBJ("security_wps").innerHTML = "<?echo i18n("WPA AES");?>";
			else
				OBJ("security_wps").innerHTML = "<?echo i18n("WPA TKIP/AES");?>";
			OBJ("key_wps").innerHTML = XG(this.wifip_br+"/nwkey/psk/key");				
		}
		else if (XG(this.wifip_br+"/authtype")==="WPA2PSK")
		{
			if(XG(this.wifip_br+"/encrtype") === "TKIP")
				OBJ("security_wps").innerHTML = "<?echo i18n("WPA2 TKIP");?>";
			else if(XG(this.wifip_br+"/encrtype") === "AES")
				OBJ("security_wps").innerHTML = "<?echo i18n("WPA2 AES");?>";
			else
				OBJ("security_wps").innerHTML = "<?echo i18n("WPA2 TKIP/AES");?>";
			OBJ("key_wps").innerHTML = XG(this.wifip_br+"/nwkey/psk/key");		
		}
		else if (XG(this.wifip_br+"/authtype")==="WPA+2PSK")
		{
			OBJ("security_wps").innerHTML = "<?echo i18n("Auto (WPA or WPA2) TKIP/AES");?>";
			OBJ("key_wps").innerHTML = XG(this.wifip_br+"/nwkey/psk/key");				
		}	
		else if ((XG(this.wifip_br+"/authtype")==="OPEN" && XG(this.wifip_br+"/encrtype")==="WEP") ||
				 (XG(this.wifip_br+"/authtype")==="SHARED") ||
				 (XG(this.wifip_br+"/authtype")==="WEPAUTO") || 
				 (XG(this.wifip_br+"/authtype")==="BOTH"))
		{
			OBJ("security_wps").innerHTML = "WEP";
			var wep_defkey = XG(this.wifip_br+"/nwkey/wep/defkey");
			OBJ("key_wps").innerHTML = XG(this.wifip_br+"/nwkey/wep/key:"+wep_defkey);
		}
		else if (XG(this.wifip_br+"/authtype")==="OPEN" && XG(this.wifip_br+"/encrtype")==="NONE")
		{
			OBJ("security_wps").innerHTML = "NONE";
			OBJ("key_wps").innerHTML = "";								
		}
		
		if(XG(this.wifip_br+"/wps/pin") != this.curpin)
			XS(this.wifip_br+"/wps/pin", this.curpin);
		for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "wps_result") {this.currentStage=i; break;}}
		this.ShowCurrentStage();
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
			case "security":
				for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "ssid") {this.currentStage=i; break;}}
				break;
			case "wps_config":
			case "ssid":
				for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "set_config") {this.currentStage=i; break;}}
				break;
			default:
				this.currentStage--;
				break;
		}

		if(this.currentStage < 0)
			this.currentStage = 0;
		this.ShowCurrentStage();
	},
	OnClickNext: function()
	{	
	switch (this.stages[this.currentStage])
		{
			
			case "wiz_start":
				this.currentStage++;
				break;
			case "set_config":
				if (true)	
				{
					for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "wps_config") {this.currentStage=i; break;}}
				}	
				else if	(OBJ("manual_mode").checked)
				{	
					for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "ssid") {this.currentStage=i; break;}}
				}
				break;
			case "wps_config":
				PAGE.OnSubmit();		
				break;
			case "wps_ok":
				COMM_GetCFG(false, "WIFI.PHYINF", function(xml) {PAGE.WPSResultCallBack(xml);});
				this.Todirty = true;
				return;
				break;
			case "wps_fail":
				for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "wps_config") {this.currentStage=i; break;}}
				break;
			case "wps_result":
				for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "final") {this.currentStage=i; break;}}
				break;
			case "ssid":
				if(!this.CheckStage()) return;
				for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "security") {this.currentStage=i; break;}}
				break;	
			case "site_survey":
				var No_select = true;
				for (var i=1; i<=this.site_survey_cnt; i++)	{if(OBJ("Site_Survey_"+i).checked) {No_select = false; break;}}
				if(No_select === false)	
				{
					this.CheckStage();
					for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "security") {this.currentStage=i; break;}}
				}
				break;		
			case "security":			
				if (OBJ("security_none").checked)
				{
					for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "final") {this.currentStage=i; break;}}
				}
				else if	(OBJ("security_wep").checked) 
				{
					for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "wep_key") {this.currentStage=i; break;}}
				}
				else if	(OBJ("security_wpa").checked || OBJ("security_wpa2").checked || OBJ("security_wpa_auto").checked)
				{
					for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "wpa_key") {this.currentStage=i; break;}}
				}
				break;	
			case "wpa_key":			
				if(!this.CheckStage()) return;
				for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "final") {this.currentStage=i; break;}}
				break;
			case "wep_key":			
				if(!this.CheckStage()) return;
				for(var i=0; i<this.stages.length; i++) {if(this.stages[i] === "final") {this.currentStage=i; break;}}
				break;				
			case "final":
    		/*if(confirm('<?echo i18n("Do you want to bookmark \"D-Link AP Web Management\"");?>'))
	    		addBookmark("D-Link AP Web Management",document.URL);	*/				
				PAGE.OnSubmit();
				break;	
			default:
				break;
		}
		this.ShowCurrentStage();
	},
	OnClickCancel: function()
	{
		if (!COMM_IsDirty(false)||confirm("<?echo i18n("Do you want to abandon all changes you made to this wizard?");?>"))
			self.location.href = "./wiz_wlan_setup.php";
	},	
	OnChangeConfig: function(method)
	{
		if(method === "wps")
		{	
			OBJ("wps_mode").checked = true;
			OBJ("manual_mode").checked = false;
		}
		else if(method === "manual")	
		{	
			OBJ("wps_mode").checked = false;
			OBJ("manual_mode").checked = true;
		}
	},	
	OnChangeSecurity: function(method)
	{
		if(method === "none")
		{	
			OBJ("security_none").checked = true;
			OBJ("security_wep").checked = false;
			OBJ("security_wpa").checked = false;
			OBJ("security_wpa2").checked = false;
			OBJ("security_wpa_auto").checked = false;
		}
		else if(method === "wep")	
		{	
			OBJ("security_none").checked = false;
			OBJ("security_wep").checked = true;
			OBJ("security_wpa").checked = false;
			OBJ("security_wpa2").checked = false;
			OBJ("security_wpa_auto").checked = false;
		}
		else if(method === "wpa")	
		{	
			OBJ("security_none").checked = false;
			OBJ("security_wep").checked = false;
			OBJ("security_wpa").checked = true;
			OBJ("security_wpa2").checked = false;
			OBJ("security_wpa_auto").checked = false;
            OBJ("wpa_input_str").innerHTML = "WPA Personal Passphrase";
		}
		else if(method === "wpa2")	
		{	
			OBJ("security_none").checked = false;
			OBJ("security_wep").checked = false;
			OBJ("security_wpa").checked = false;
			OBJ("security_wpa2").checked = true;
			OBJ("security_wpa_auto").checked = false;
            OBJ("wpa_input_str").innerHTML = "<?echo i18n("WPA2 Personal Passphrase");?>";
		}
		else if(method === "wpa_auto")
		{
			OBJ("security_none").checked = false;
			OBJ("security_wep").checked = false;
			OBJ("security_wpa").checked = false;
			OBJ("security_wpa2").checked = false;
			OBJ("security_wpa_auto").checked = true;
            OBJ("wpa_input_str").innerHTML = "<?echo i18n("WPA/WPA2 Personal Passphrase");?>";
		}
	},
	OnChangeSSRadio: function(i)
	{
		for (var j=1; j<=this.site_survey_cnt; j++)	if(j!==i) OBJ("Site_Survey_"+j).checked = false;
		this.site_survey_num = i;
	},
	OnClickResetPIN: function()
	{
		OBJ("pincode").innerHTML = this.defpin;
		this.curpin = this.defpin;
		XS(this.wifip_br+"/wps/pin", this.defpin);
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
		OBJ("pincode").innerHTML = pin;
		this.curpin = pin;
	}
}

function IsHexadecimal(str)
{
	var c = "0123456789abcdef";
	for(var i=0; i<str.length; i++)	
	{
		var str_hex=0;
		for(var j=0; j<c.length; j++)	if(str.substr(i,1)===c.substr(j,1))	{str_hex=1;break;}			
		if(str_hex===0) return false;
	}
	return true;
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
