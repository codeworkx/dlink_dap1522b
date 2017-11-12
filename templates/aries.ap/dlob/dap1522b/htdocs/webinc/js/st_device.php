<script type="text/javascript">
var S2I = function(str) { var num = parseInt(str, 10); return isNaN(num)?0:num;}
var EventName=null;
function SendEvent(str)
{
	var ajaxObj = GetAjaxObj(str);
	if (EventName != null) return;

	EventName = str;
	ajaxObj.createRequest();
	ajaxObj.onCallback = function (xml)
	{
		ajaxObj.release();
		//setTimeout("OnLoadBody()", 3*1000);
		EventName = null;
	}
	ajaxObj.setHeader("Content-Type", "application/x-www-form-urlencoded");
	ajaxObj.sendRequest("service.cgi", "EVENT="+EventName);
}

function Page() {}
Page.prototype =
{
	services: "RUNTIME.TIME,RUNTIME.PHYINF,WIFI.PHYINF,RUNTIME.INF.BRIDGE-1,INET.BRIDGE-1",		
	OnLoad: function(){},
	OnUnload: function() {},
	OnSubmitCallback: function ()
	{
	},
	InitValue: function(xml)
	{		
		PXML.doc = xml;		
	
		var wlan_uid = null;		
		var swithmode_path = PXML.FindModule("RUNTIME.SWITCHMODE"); //The service is included in templates.php in DAP-1522B
		var swithmode = XG(swithmode_path+"/runtime/device/switchmode");
		if(swithmode==="APCLI")	wlan_uid = "WLAN-2";
		else	wlan_uid = "WLAN-1.1";
		
		if (!this.InitGeneral()) return false;		
		if (!this.InitBridge()) return false;				
		if (!this.InitWLAN(wlan_uid,"WIFI.PHYINF")) return false;
	
		alert	
<?		
		echo "\t\tif (!this.InitBridge()) return false;\n";
?>					
		return true;
	},
	PreSubmit: function()
	{
	},
	IsDirty: null,
	Synchronize: function() {},
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////		
	InitGeneral: function ()
	{
        this.timep = PXML.FindModule("RUNTIME.TIME");
		this.uptime = XG  (this.timep+"/runtime/device/uptime");
		if (!this.uptime)
		{
			BODY.ShowAlert("InitGeneral() ERROR!!!");
			return false;
		}
		OBJ("st_time").innerHTML  = this.uptime;
		return true;
	},
	
	InitWLAN: function ( wlan_uid, wifi_phyinf )
	{
		var wifi_phyinf_path = PXML.FindModule(wifi_phyinf);
		var phy_wlan_path = GPBT(wifi_phyinf_path, "phyinf", "uid", wlan_uid, false);
		var wifi_profile_name = XG(phy_wlan_path+"/wifi");
		var wifi_path = GPBT(wifi_phyinf_path+"/wifi", "entry", "uid", wifi_profile_name, false);
		
		if ((!wifi_path)||(!phy_wlan_path))
		{
			BODY.ShowAlert("InitWLAN() ERROR!!!");
			return false;
		}
		var opmode = XG(wifi_path+"/opmode");
		if(opmode=="AP")
		{
			if (!this.InitWLANAp(wlan_uid,"WIFI.PHYINF")) return false;
		}
		else
		{
			if (!this.InitWLANSta(wlan_uid,"WIFI.PHYINF")) return false;
		}
	},	
	
	InitWLANAp: function ( wlan_uid, wifi_phyinf )
	{
		var str_Aband = "";
		var wifi_phyinf_path = PXML.FindModule(wifi_phyinf);
		var phy_wlan_path = GPBT(wifi_phyinf_path, "phyinf", "uid", wlan_uid, false);
		var wifi_profile_name = XG(phy_wlan_path+"/wifi");
		var wifi_path = GPBT(wifi_phyinf_path+"/wifi", "entry", "uid", wifi_profile_name, false);
		
		var rphy = PXML.FindModule("RUNTIME.PHYINF");
		var rwlan1p = 	GPBT(rphy+"/runtime", "phyinf", "uid", wlan_uid, false);
		var freq = XG(phy_wlan_path+"/media/freq");
		var wifi_enable = XG(phy_wlan_path+"/active");
		
		if ((!wifi_path)||(!phy_wlan_path))
		{
			BODY.ShowAlert("InitWLAN() ERROR!!!");
			return false;
		}
		
//		OBJ("bridge_state").style.display 	= "none";
		
		if(freq=="5") { str_Aband = "_Aband";}
		<?
			$wlan1_up = fread("", "/var/run/WLAN-1.1.UP"); 
			$wlan1_up+=0;
		?>
		var schedule_wireless = <?echo $wlan1_up;?>;
		
			//Wireless active info
/*		if(XG(phy_wlan_path+"/active")== "1")
		{
			if(schedule_wireless == "1")
				OBJ("st_wireless_radio").innerHTML  = "<?echo i18n("Enabled");?>";
			else
				OBJ("st_wireless_radio").innerHTML  = "<?echo i18n("Disabled");?>";
			
		}
		else
			OBJ("st_wireless_radio").innerHTML  = "<?echo i18n("Disabled");?>";
*/		
		/*OBJ("st_wireless_radio").innerHTML  = XG(phy_wlan_path+"/active")== "1" ? "<?echo i18n("Enabled");?>":"<?echo i18n("Disabled");?>";*/
		
		//Wireless mode info
		var IEEE80211mode =  XG(phy_wlan_path+"/media/wlmode"+str_Aband);
		var check_bandwidth = 0;
        switch (IEEE80211mode)
		{
		   case "bgn":
		   		OBJ("st_80211mode").innerHTML  = "<?echo i18n("Mixed 802.11n, 802.11g and 802.11b");?>";
                check_bandwidth = 1;
				break;
		   case "bg":
		   		OBJ("st_80211mode").innerHTML  = "<?echo i18n("Mixed 802.11g and 802.11b");?>";
				break;
		   case "gn":
		   		OBJ("st_80211mode").innerHTML  = "<?echo i18n("Mixed 802.11n and 802.11g");?>";
                check_bandwidth = 1;
				break;
		   case "an":
		   		OBJ("st_80211mode").innerHTML  = "<?echo i18n("Mixed 802.11n and 802.11a");?>";
                check_bandwidth = 1;
				break;
		   case "n":
		   		OBJ("st_80211mode").innerHTML  = "<?echo i18n("802.11n only");?>";
                check_bandwidth = 1;
				break;
		   case "b":
		   		OBJ("st_80211mode").innerHTML  = "<?echo i18n("802.11b only");?>";
				break;
		   case "g":
		   		OBJ("st_80211mode").innerHTML  = "<?echo i18n("802.11g only");?>";
				break;
		   case "a":
		   		OBJ("st_80211mode").innerHTML  = "<?echo i18n("802.11a only");?>";
				break;
			default:
				OBJ("st_80211mode").innerHTML  = "<?echo i18n("N/A");?>";
				break;
		}
		
		//Bandwidth info
		var string_bandwidth = "20MHZ";
		if(wifi_enable == 1 && schedule_wireless == 1)
		{
			if (check_bandwidth==1) { string_bandwidth = XG  (rwlan1p+"/media/dot11n/bandwidth"+str_Aband)== "20+40" ? "20/40MHz":"20MHz";}
		}
		else
		{
			string_bandwidth = "<?echo i18n("N/A");?>";
		}
		OBJ("st_Channel_Width").innerHTML  = string_bandwidth;
		
		//Channel info
		var channel = XG(rwlan1p+"/media/channel"+str_Aband);
		if(wifi_enable == 1 && schedule_wireless == 1) {OBJ("st_Channel").innerHTML  = (channel != "0") ? channel : "<?echo i18n("Auto");?>";}
		else { OBJ("st_Channel").innerHTML = "<?echo i18n("N/A");?>";}
		
		//SSID info 
		if(wifi_enable == 1 && schedule_wireless == 1) { OBJ("st_SSID").innerHTML  = COMM_EscapeHTMLSC(XG(rwlan1p+"/media/wifi/ssid"));}
		else { OBJ("st_SSID").innerHTML = "<?echo i18n("N/A");?>";}
		
		//WPS active info
        var string_WPS =  "<?echo i18n("Disabled");?>";
		if(wifi_enable == 1 && schedule_wireless == 1)
		{
			if ( XG(wifi_path+"/wps/enable") == "1")
			{
				string_WPS =  "<?echo i18n("Enabled");?>"+"/"+ (XG  (wifi_path+"/wps/configured")== "1" ? "<?echo i18n("Configured");?>":"<?echo i18n("Unconfigured");?>");
			}
		}
		var swithmode_path = PXML.FindModule("RUNTIME.SWITCHMODE"); //The service is included in templates.php in DAP-1522B
		var swithmode = XG(swithmode_path+"/runtime/device/switchmode");
		if(swithmode != "APCLI")
			OBJ("st_WPS_status").innerHTML  = string_WPS;
		
		//Security info
        var string_security = "<?echo i18n("N/A");?>";
		if(wifi_enable == 1 && schedule_wireless == 1)
		{
			if (XG (wifi_path+"/encrtype") != "NONE")
			{
				switch(XG  (wifi_path+"/authtype"))
				{
					case "OPEN":
					case "SHARED":
					case "WEPAUTO":
					case "BOTH":
						string_security = "<?echo i18n("WEP");?>";
						break;
					case "WPA":
						string_security = "<?echo i18n("WPA-EAP");?>";
						break;
					case "WPA2":
						string_security = "<?echo i18n("WPA2-EAP");?>";
						break;
					case "WPA+2":
						string_security = "<?echo i18n("WPA/WPA2-EAP");?>";
						break;
					case "WPAPSK":
						string_security = "<?echo i18n("WPA-PSK");?>";
						break;
					case "WPA2PSK":
						string_security = "<?echo i18n("WPA2-PSK");?>";
						break;
					case "WPA+2PSK":
						string_security = "<?echo i18n("WPA/WPA2-PSK");?>";
						break;
				}
			}
			else
			{
				string_security = "<?echo i18n("NONE");?>";
			}
		}
		OBJ("st_security").innerHTML  = string_security;
	    return true;	
	},
	
	InitWLANSta: function ( wlan_uid, wifi_phyinf )
	{
		var str_Aband			= "";
		var wifi_phyinf_path 	= PXML.FindModule(wifi_phyinf);
		var phy_wlan_path 		= GPBT(wifi_phyinf_path, "phyinf", "uid", wlan_uid, false);
		var wifi_profile_name 	= XG(phy_wlan_path+"/wifi");
		var wifi_path 			= GPBT(wifi_phyinf_path+"/wifi", "entry", "uid", wifi_profile_name, false);
		
		var rphy 	= PXML.FindModule("RUNTIME.PHYINF");
		var rwlan1p = GPBT(rphy+"/runtime", "phyinf", "uid", wlan_uid, false);
		
		var wifi_enable = XG(phy_wlan_path+"/active");
		if ((!wifi_path)||(!phy_wlan_path))
		{
			BODY.ShowAlert("InitWLAN() ERROR!!!");
			return false;
		}
//		OBJ("bridge_state").style.display 	= "block";
		<?
			$wlan2_up = fread("", "/var/run/WLAN-2.UP"); 
			$wlan2_up+=0;
		?>
		var schedule_wireless = <?echo $wlan2_up;?>;
		//Wireless info
/*		if(wifi_enable == "1")
		{
			if(schedule_wireless == "1")
				OBJ("st_wireless_radio").innerHTML  = "<?echo i18n("Enabled");?>";
			else
				OBJ("st_wireless_radio").innerHTML  = "<?echo i18n("Disabled");?>";
			
		}
		else
			OBJ("st_wireless_radio").innerHTML  = "<?echo i18n("Disabled");?>";		
*/
		/*OBJ("st_wireless_radio").innerHTML  = (wifi_enable == 1) ? "<?echo i18n("Enabled");?>":"<?echo i18n("Disabled");?>";*/
		
		//Wireless mode info
		var IEEE80211mode =  XG(rwlan1p+"/media/wlmode");
		switch (IEEE80211mode)
		{		
			case "11B":
				OBJ("st_80211mode").innerHTML  = "<?echo i18n("802.11b only");?>";
				break;
				
			case "11B/G":
				OBJ("st_80211mode").innerHTML  = "<?echo i18n("Mixed 802.11g and 802.11b");?>";
				break;

			case "11A":
				OBJ("st_80211mode").innerHTML  = "<?echo i18n("802.11a only");?>";
				break;

			case "11A/B/G":
				OBJ("st_80211mode").innerHTML  = "<?echo i18n("Mixed 802.11a, 802.11b and 802.11g");?>";
				check_bandwidth = 1;
				break;

			case "11G":
				OBJ("st_80211mode").innerHTML  = "<?echo i18n("802.11g only");?>";
				break;

			case "11A/B/G/N":
				OBJ("st_80211mode").innerHTML  = "<?echo i18n("Mixed 802.11a, 802.11b, 802.11g and 802.11n");?>";
				check_bandwidth = 1;
				break;

			case "11N only with 2.4G":
				OBJ("st_80211mode").innerHTML  = "<?echo i18n("802.11n only");?>";
				check_bandwidth = 1;
				break;

			case "11G/N":
				OBJ("st_80211mode").innerHTML  = "<?echo i18n("Mixed 802.11g and 802.11n");?>";
				check_bandwidth = 1;
				break;

			case "11A/N":
				OBJ("st_80211mode").innerHTML  = "<?echo i18n("Mixed 802.11a and 802.11n");?>";
				check_bandwidth = 1;
				break;
				
			case "11B/G/N":
				OBJ("st_80211mode").innerHTML  = "<?echo i18n("Mixed 802.11b, 802.11g and 802.11n");?>";
				check_bandwidth = 1;
				break;

			case "11A/G/N":
				OBJ("st_80211mode").innerHTML  = "<?echo i18n("Mixed 802.11a, 802.11g and 802.11n");?>";
				check_bandwidth = 1;
				break;

			case "11N only with 5G":
				OBJ("st_80211mode").innerHTML  = "<?echo i18n("802.11n only");?>";
				check_bandwidth = 1;
				break;

			 default:
				OBJ("st_80211mode").innerHTML  = "<?echo i18n("N/A");?>";
				break;
		}
		
		//Band info
		string_bandwidth = XG(rwlan1p+"/media/dot11n/bandwidth");
		if (wifi_enable == 1 && schedule_wireless == 1) { OBJ("st_Channel_Width").innerHTML  = (string_bandwidth != "") ? string_bandwidth : "<?echo i18n("N/A");?>"; }
		else {OBJ("st_Channel_Width").innerHTML = "<?echo i18n("N/A");?>";}
		
		//Channel info
		var channel = XG(rwlan1p+"/media/channel");
		if (wifi_enable == 1 && schedule_wireless == 1) { OBJ("st_Channel").innerHTML	= (channel != "0") ? channel : "<?echo i18n("N/A");?>";}
		else {OBJ("st_Channel").innerHTML = "<?echo i18n("N/A");?>";}
		
		//SSID info
		if (wifi_enable == 1 && schedule_wireless == 1) {OBJ("st_SSID").innerHTML = COMM_EscapeHTMLSC(XG(wifi_path+"/ssid"));}
		else {OBJ("st_SSID").innerHTML = "<?echo i18n("N/A");?>";}
		
		//WPS active info
        var string_WPS =  "<?echo i18n("Disabled");?>";
    var swithmode_path = PXML.FindModule("RUNTIME.SWITCHMODE"); //The service is included in templates.php in DAP-1522B
		var swithmode = XG(swithmode_path+"/runtime/device/switchmode");
    if(swithmode != "APCLI")
    {
			if(wifi_enable == 1 && schedule_wireless == 1)
			{
				if ( XG  (wifi_path+"/wps/enable") == "1")
				{
					string_WPS =  "<?echo i18n("Enabled");?>";
				}
				OBJ("st_WPS_status").innerHTML  = string_WPS;
			}
			else
			{
				OBJ("st_WPS_status").innerHTML  = string_WPS;
			}
		}
		else
			{
				if(wifi_enable == 1 && schedule_wireless == 1)
				{
					if ( XG  (wifi_path+"/wps/enable") == "1")
					{
						string_WPS =  "<?echo i18n("Enabled");?>";
					}
				}
			}
		
		//Security info
        var string_security = "<?echo i18n("N/A");?>";
		if(wifi_enable == 1 && schedule_wireless == 1)
		{
			if (XG  (rwlan1p+"/media/wifi/encrtype") != "NONE")
			{
				switch(XG(rwlan1p+"/media/wifi/authtype"))
				{
					case "OPEN":
					case "SHARED":
					case "WEPAUTO":
					case "BOTH":
						string_security = "<?echo i18n("WEP");?>";
						break;
					case "WPA":
						string_security = "<?echo i18n("WPA-EAP");?>";
						break;
					case "WPA2":
						string_security = "<?echo i18n("WPA2-EAP");?>";
						break;
					case "WPA+2":
						string_security = "<?echo i18n("WPA/WPA2-EAP");?>";
						break;
					case "WPAPSK":
						string_security = "<?echo i18n("WPA-PSK");?>";
						break;
					case "WPA2PSK":
						string_security = "<?echo i18n("WPA2-PSK");?>";
						break;
					case "WPAPSKWPA2PSK":
						string_security = "<?echo i18n("WPA/WPA2-PSK");?>";
						break;
				}
			}
			else
			{
				string_security = "<?echo i18n("NONE");?>";
			}
		}
		OBJ("st_security").innerHTML  = string_security;
		
		//Connection info
		var state_temp = XG(rwlan1p+"/media/connectstatus");
		var state = state_temp.toLowerCase();
		if(wifi_enable == 1 && schedule_wireless == 1)
		{
			if(state.search("disconnected") != -1) { state = "<?echo i18n("Disconnected");?>";}
			else {state = "<?echo i18n("Connected");?>";}
		}
		else
		{
			state = "<?echo i18n("Disconnected");?>";
		}
//		OBJ("st_bridge_state").innerHTML = state;
		
	    return true;	
	},
	
	InitBridge: function()
	{						
		var br = PXML.FindModule("RUNTIME.INF.BRIDGE-1");		
		if (!br) { BODY.ShowAlert("InitBridge() ERROR !!!"); return false; }		
		var wantype = XG(br+"/runtime/inf/inet/addrtype");
		var wantype_str = "Unknow WAN type";
		
		var p = PXML.FindModule("INET.BRIDGE-1");
		var inf_ipv4 = GPBT(p, "inf", "uid", "BRIDGE-1", false);
		var inet_ipv4 = XG(inf_ipv4+"/inet");
		this.inetp_v4 = GPBT(p+"/inet", "entry", "uid", inet_ipv4, false);
		
		if (wantype=="ipv4")
		{
			if (XG(this.inetp_v4+"/ipv4/static") !== "1")
				wantype_str = "<?echo i18n("DHCP Client");?>";
			else
				wantype_str = "<?echo i18n("Static IP");?>";
		}
		else if (wantype=="ppp4")
			wantype_str = "PPPoE";
					
		OBJ("br_wantype").innerHTML = wantype_str;		
		if (wantype=="ipv4")
		{
			var b = br+"/runtime/inf/inet/ipv4";
			if (XG(b+"/valid")=="1")
			{
				OBJ("br_ipaddr").innerHTML = XG(b+"/ipaddr");
				OBJ("br_netmask").innerHTML= COMM_IPv4INT2MASK(XG(b+"/mask"));
				OBJ("br_gateway").innerHTML= XG(b+"/gateway");				
			}
		}
		else if (wantype=="ppp4")
		{
			var b = br+"/runtime/inf/inet/ppp4";
			if (XG(b+"/valid")=="1")
			{
				OBJ("br_ipaddr").innerHTML = XG(b+"/ipaddr");
				OBJ("br_netmask").innerHTML= COMM_IPv4INT2MASK(XG(b+"/mask"));
				OBJ("br_gateway").innerHTML= XG(b+"/gateway");				
			}
		}
		OBJ("ethernet_block").style.display = "block";
		return true;
	},
	ResetXML: function()
	{
		COMM_GetCFG(
			false,
			PAGE.services,
			function(xml) {
				PXML.doc = xml;
			}
		);
	}

}	

</script>
