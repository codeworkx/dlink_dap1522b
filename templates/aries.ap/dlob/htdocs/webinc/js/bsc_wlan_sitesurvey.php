<script type="text/javascript">
function Page() {}
Page.prototype =
{
	services: "",
	OnLoad: function()	{},
	OnUnload: function() {},
	OnSubmitCallback: function (code, result)
	{
		switch (code)
		{
			case "OK":
				setTimeout('PAGE.SiteSurvey()',2000);
				break;
			default:
				BODY.ShowAlert(result);
				break;
		}
		return true;
	},
	InitValue: function(xml)
	{		
		OBJ("content").style.backgroundColor="#DFDFDF";
		PAGE.OnSubmit();
		return true;
	},
	PreSubmit: function(){},
	IsDirty: null,
	Synchronize: function() {},
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////
	site_survey_timer: null,
	site_survey_cnt: 0,
	site_survey_num: 0,
	SS_ssid: new Array(),
	SS_encrtype: new Array(),
	OnSubmit: function()
	{	
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
	},
	SiteSurvey: function()
	{
		COMM_GetCFG(false, "RUNTIME.SITESURVEY", function(xml) {PAGE.SiteSurveyCallback(xml);});	
	},
	SiteSurveyCallback: function(xml)
	{		
		/*Show site survey table*/
		var SiteSurveyP = "/postxml/module/runtime/wifi_tmpnode/sitesurvey/entry";
		this.site_survey_cnt = xml.Get(SiteSurveyP+"#");
		BODY.CleanTable("SiteSurveyTable");
		for (var i=1; i<=this.site_survey_cnt; i++)
		{	
			SiteSurveyPath = SiteSurveyP+":"+i; 
			this.SS_ssid[i] = xml.Get(SiteSurveyPath+"/ssid");
			var SS_bssid = xml.Get(SiteSurveyPath+"/macaddr");
			var channel = xml.Get(SiteSurveyPath+"/channel");
			var wlmode = xml.Get(SiteSurveyPath+"/wlmode");
			var SS_channel = channel+"("+wlmode.substring(2, wlmode.length)+")";
			var authtype = xml.Get(SiteSurveyPath+"/authtype");
			this.SS_encrtype[i] = xml.Get(SiteSurveyPath+"/encrtype");
			var SS_encrypt = this.SS_encrtype[i]+"/"+authtype;
			var SS_signal = xml.Get(SiteSurveyPath+"/rssi");
			var SS_radio = '<input id="Site_Survey_'+i+'" type="radio" onClick="PAGE.OnChangeSSRadio('+i+');" />';

			var data = [this.SS_ssid[i], SS_bssid, SS_channel, "AP", SS_encrypt, SS_signal, SS_radio];	
			var type = ["text","text","text","text","text","text",""];
			BODY.InjectTable("SiteSurveyTable", "SS_table_"+i, data, type);
		}
		
		this.site_survey_timer = setTimeout('PAGE.OnSubmit()',7000);
	},
	OnClickConnect: function()
	{
		window.opener.OBJ("ssid_sta").value = this.SS_ssid[this.site_survey_num];
		if(this.SS_encrtype[this.site_survey_num]==="NONE")	
		{	
			COMM_SetSelectValue(window.opener.OBJ("security_type_sta"), "");
			window.opener.OBJ("wep_sta").style.display = "none";
			window.opener.OBJ("wpa_sta").style.display = "none";
			window.opener.OBJ("pad").style.display = "block";
		}
		else if(this.SS_encrtype[this.site_survey_num]==="WEP")						
		{
			COMM_SetSelectValue(window.opener.OBJ("security_type_sta"), "wep");
			window.opener.OBJ("wep_sta").style.display = "block";
			window.opener.OBJ("wpa_sta").style.display = "none";
			window.opener.OBJ("pad").style.display = "none";
		}	
		else
		{
			COMM_SetSelectValue(window.opener.OBJ("security_type_sta"), "wpa");
			window.opener.OBJ("wep_sta").style.display = "none";
			window.opener.OBJ("wpa_sta").style.display = "block";
			window.opener.OBJ("pad").style.display = "none";			
		}	
	
		window.close();
	},	
	OnClickExit: function()
	{
		window.close();
	},	
	OnChangeSSRadio: function(i)
	{
		for (var j=1; j<=this.site_survey_cnt; j++)	if(j!==i) OBJ("Site_Survey_"+j).checked = false;
		this.site_survey_num = i;
	}		
}

</script>
