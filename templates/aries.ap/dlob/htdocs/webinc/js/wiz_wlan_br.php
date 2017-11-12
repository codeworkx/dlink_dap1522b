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
		if (this.stages[this.currentStage]==="wps")
		{
			switch (code)
			{
				case "OK":
					this.WPSInProgress();
					break;
				default:
					for(var i=0; i<this.stages.length; i++)	if(this.stages[i] === "wps_fail")	this.currentStage = i;
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
					for(var i=0; i<this.stages.length; i++)	if(this.stages[i] === "ssid")	this.currentStage = i;
					this.ShowCurrentStage();
					break;
			}			
		}	
		else if (this.stages[this.currentStage]==="final")
		{	
			switch (code)
			{
			case "OK":
				setTimeout('self.location.href = "./status.php"', 7000);
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
				BODY.ShowAlert(result.Get("/pigwidgeon/message"));
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
		this.run_phyinf_br = PXML.FindModule("RUNTIME.WPS.WLAN-2");
		PXML.IgnoreModule("RUNTIME.WPS.WLAN-2");
			
		if (!this.wifip || !this.run_phyinf_br)
		{
			BODY.ShowAlert("Initial() ERROR!!!");
			return false;
		}
		
		this.phyinf_br = GPBT(this.wifip, "phyinf", "uid", "WLAN-2", false);
		var wifi_br = XG(this.phyinf_br+"/wifi");
		this.wifip_br = GPBT(this.wifip+"/wifi", "entry", "uid", wifi_br, false);		
		
		this.OnChangeConfig("wps");
		this.OnChangeSecurity("wpa2");
				
		return true;
	},
	PreSubmit: function()
	{
		XS(this.wifip_br+"/ssid", OBJ("ssid_input").value);
		XS(this.wifip_br+"/ssidhidden", "0");
		XS(this.wifip_br+"/wps/configured", "1");
		XS(this.phyinf_br+"/active", "1");
		
		if(OBJ("security_none").checked)
		{
			XS(this.wifip_br+"/authtype", "OPEN");
			XS(this.wifip_br+"/encrtype", "NONE");
		}	
		else if(OBJ("security_wep").checked) 
		{
			XS(this.wifip_br+"/authtype", "OPEN");
			XS(this.wifip_br+"/encrtype", "WEP");
			XS(this.wifip_br+"/nwkey/wep/size", "");
			XS(this.wifip_br+"/nwkey/wep/ascii", "");	
			XS(this.wifip_br+"/nwkey/wep/defkey", "1");
			XS(this.wifip_br+"/nwkey/wep/key", OBJ("wep_input").value);				
		}
		else if(OBJ("security_wpa").checked || OBJ("security_wpa2").checked) 
		{
			if(OBJ("security_wpa").checked)	XS(this.wifip_br+"/authtype", "WPAPSK");
			else XS(this.wifip_br+"/authtype", "WPA2PSK");			
			if(this.SS_encrtype[this.site_survey_num]==="TKIP")	XS(this.wifip_br+"/encrtype", "TKIP");
			else XS(this.wifip_br+"/encrtype", "AES");
			XS(this.wifip_br+"/nwkey/psk/passphrase", "");
			XS(this.wifip_br+"/nwkey/psk/key", OBJ("wpa_input").value);
		}		
			
		return PXML.doc;
	},
	IsDirty: null,
	Synchronize: function() {},
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////
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
	stages: new Array ("wiz_start", "set_config", "wps", "wps_ok", "wps_fail", "ssid", "site_survey_scan", "site_survey", "security", "wpa_key", "wep_key", "final"),
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
		
		if (this.stages[this.currentStage] === "site_survey")
		{
			this.currentStage++;
			OBJ("ssid_input").value = this.SS_ssid[this.site_survey_num];
			if(this.SS_encrtype[this.site_survey_num]==="NONE")	this.OnChangeSecurity("none");
			else if(this.SS_encrtype[this.site_survey_num]==="WEP")	this.OnChangeSecurity("wep");
			else if(this.SS_authtype[this.site_survey_num]==="WPAPSK")	this.OnChangeSecurity("wpa");							
			else	this.OnChangeSecurity("wpa2");
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
		if (this.stages[this.currentStage]==="wps" || this.stages[this.currentStage]==="wps_fail")
		{
			var ajaxObj = GetAjaxObj("WPS");
			var action = "PBC";
			var uid = "WLAN-2";
			var value = "00000000";
			ajaxObj.createRequest();
			ajaxObj.onCallback = function (xml)
			{
				ajaxObj.release();
				PAGE.OnSubmitCallback(xml.Get("/wpsreport/result"));
			}
			
			ajaxObj.setHeader("Content-Type", "application/x-www-form-urlencoded");
			ajaxObj.sendRequest("wpsacts.php", "action="+action+"&uid="+uid+"&pin="+value);
			AUTH.UpdateTimeout();
		}
		else if (this.stages[this.currentStage]==="ssid" || this.stages[this.currentStage]==="site_survey_scan" || this.stages[this.currentStage]==="site_survey")
		{
			if (this.stages[this.currentStage]==="ssid")
			{
				this.currentStage++;
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
	},
	WPSInProgress: function()
	{
		if (!this.start_count_down)
		{
			this.start_count_down = true;
			setTimeout('PAGE.WPSCountDown()',1000);
			setTimeout('COMM_GetCFG(false, "RUNTIME.WPS.WLAN-2", function(xml) {PAGE.WPSInProgressCallBack(xml);})',3000);
			return;
		}
		COMM_GetCFG(false, "RUNTIME.WPS.WLAN-2", function(xml) {PAGE.WPSInProgressCallBack(xml);});
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
		var time = parseInt(OBJ("ct").innerHTML, 10);
		if (time > 0)
		{		
			time--;
			this.cd_timer = setTimeout('PAGE.WPSCountDown()',1000);
			OBJ("ct").innerHTML = time;
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
				this.currentStage+=2;		
				break;
			case "WPS_ERROR":
				this.currentStage+=2;		
				break;
			case "WPS_OVERLAP":
				this.currentStage+=2;
				break;
			case "WPS_IN_PROGRESS":
				break;
			case "WPS_SUCCESS":
				this.currentStage++;
				break;
		}
		this.ShowCurrentStage();
		if (this.cd_timer)	clearTimeout(this.cd_timer);
		if (this.wps_timer)	clearTimeout(this.wps_timer);
		PAGE.start_count_down = false;
		OBJ("ct").innerHTML = 120;
	},
	SiteSurvey: function()
	{
		COMM_GetCFG(false, "RUNTIME.SITESURVEY", function(xml) {PAGE.SiteSurveyCallback(xml);});	
	},
	SiteSurveyCallback: function(xml)
	{
		if (this.stages[this.currentStage]==="site_survey_scan")
		{
			this.currentStage++;
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
			var strlen_SS_ssid=this.SS_ssid[i].length;
			var rows_ssid = Math.floor(strlen_SS_ssid/16);
			var suffix_ssid = Math.ceil(strlen_SS_ssid/16);
			var SS_ssidm="";
			for(var j=0; j <= rows_ssid; j++)
			{
				if(j < rows_ssid) SS_ssidm = SS_ssidm + this.SS_ssid[i].substring(16*j, 16 + 16*j)+"<br>";
				else if(suffix_ssid !== 0) SS_ssidm = SS_ssidm + this.SS_ssid[i].substring(16*j, strlen_SS_ssid);
			}
			
			var SS_bssid = xml.Get(SiteSurveyPath+"/macaddr");
			var channel = xml.Get(SiteSurveyPath+"/channel");
			var wlmode = xml.Get(SiteSurveyPath+"/wlmode");
			var SS_channel = channel+"("+wlmode.substring(2, wlmode.length)+")";
			this.SS_authtype[i] = xml.Get(SiteSurveyPath+"/authtype");
			this.SS_encrtype[i] = xml.Get(SiteSurveyPath+"/encrtype");
			var SS_encrypt = this.SS_encrtype[i]+"/"+this.SS_authtype[i];
			var SS_signal = xml.Get(SiteSurveyPath+"/rssi");
			var SS_radio = '<input id="Site_Survey_'+i+'" type="radio" onClick="PAGE.OnChangeSSRadio('+i+');" />';

			var data = [SS_ssidm, SS_bssid, SS_channel, "AP", SS_encrypt, SS_signal, SS_radio];	
			var type = ["","text","text","text","text","text",""];
			BODY.InjectTable("SiteSurveyTable", "SS_table_"+i, data, type);
		}
		
		this.site_survey_timer = setTimeout('PAGE.OnSubmit()',7000);
	},
	OnClickPrev: function()
	{
		switch (this.stages[this.currentStage])
		{
			case "ssid":
				this.currentStage-=4;
				this.ShowCurrentStage();
				break;
			case "security":
				this.currentStage-=3;
				this.ShowCurrentStage();
				break;
			case "wep_key":
				this.currentStage-=2;
				this.ShowCurrentStage();
				break;				
			default:
				this.currentStage--;
				this.ShowCurrentStage();
				break;
		}
	},
	OnClickNext: function()
	{
		switch (this.stages[this.currentStage])
		{
			case "set_config":
				if (OBJ("wps_mode").checked)	
				{
					this.currentStage++;
					this.ShowCurrentStage();
					PAGE.OnSubmit();
				}	
				else if	(OBJ("manual_mode").checked)
				{	
					this.currentStage+=4;
					this.ShowCurrentStage();
				}
				break;
			case "wps_ok":
				self.location.href = "./status.php";
				break;
			case "wps_fail":
				this.currentStage-=2;
				this.ShowCurrentStage();
				PAGE.OnSubmit();
				break;					
			case "ssid":
				if(!this.CheckStage()) return;
				this.currentStage+=3;
				this.ShowCurrentStage();
				break;	
			case "site_survey":
				var No_select = true;
				for (var i=1; i<=this.site_survey_cnt; i++)	if(OBJ("Site_Survey_"+i).checked) No_select = false;
				if(No_select === false)	this.CheckStage();
				else	this.currentStage-=2;
				this.ShowCurrentStage();
				break;		
			case "security":			
				if (OBJ("security_none").checked)	this.currentStage+=3;
				else if	(OBJ("security_wep").checked)	this.currentStage+=2;
				else if	(OBJ("security_wpa").checked || OBJ("security_wpa2").checked)	this.currentStage++;
				this.ShowCurrentStage();
				break;	
			case "wpa_key":			
				if(!this.CheckStage()) return;
				this.currentStage+=2;
				this.ShowCurrentStage();
				break;
			case "wep_key":			
				if(!this.CheckStage()) return;
				this.currentStage++;
				this.ShowCurrentStage();
				break;				
			case "final":
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
			self.location.href = "./status.php";
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
		}
		else if(method === "wep")	
		{	
			OBJ("security_none").checked = false;
			OBJ("security_wep").checked = true;
			OBJ("security_wpa").checked = false;
			OBJ("security_wpa2").checked = false;
		}
		else if(method === "wpa")	
		{	
			OBJ("security_none").checked = false;
			OBJ("security_wep").checked = false;
			OBJ("security_wpa").checked = true;
			OBJ("security_wpa2").checked = false;
		}
		else if(method === "wpa2")	
		{	
			OBJ("security_none").checked = false;
			OBJ("security_wep").checked = false;
			OBJ("security_wpa").checked = false;
			OBJ("security_wpa2").checked = true;
		}		
	},
	OnChangeSSRadio: function(i)
	{
		for (var j=1; j<=this.site_survey_cnt; j++)	if(j!==i) OBJ("Site_Survey_"+j).checked = false;
		this.site_survey_num = i;
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

</script>
