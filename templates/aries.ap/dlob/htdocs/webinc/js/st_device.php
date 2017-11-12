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
	services: "RUNTIME.TIME,RUNTIME.DEVICE,RUNTIME.PHYINF,WIFI.PHYINF,RUNTIME.INF.BRIDGE-1",		
	OnLoad: function(){},
	OnUnload: function() {},
	OnSubmitCallback: function ()
	{
	},
	InitValue: function(xml)
	{		
		PXML.doc = xml;		
		if (!this.InitGeneral()) return false;		
		if (!this.InitBridge()) return false;				
		if (!this.InitWLAN("WLAN-1.1","WIFI.PHYINF")) return false;
		//if (!this.InitWLAN("WLAN-1.2","WIFI.PHYINF")) return false;	
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
	InitWLAN: function ( wlan_phyinf, wifi_phyinf )
	{
		var str_Aband;
		
		var wifi_phyinf_path = PXML.FindModule(wifi_phyinf);
		var phy_wlan_path = GPBT(wifi_phyinf_path, "phyinf", "uid", wlan_phyinf, false);
		var wifi_profile_name = XG(phy_wlan_path+"/wifi");
		var wifi_path = GPBT(wifi_phyinf_path+"/wifi", "entry", "uid", wifi_profile_name, false);
		
		var rphy = PXML.FindModule("RUNTIME.PHYINF");
		var rwlan1p = 	GPBT(rphy+"/runtime", "phyinf", "uid", wlan_phyinf, false);
		var freq = XG(phy_wlan_path+"/media/freq");
		
		if ((!wifi_path)||(!phy_wlan_path))
		{
			BODY.ShowAlert("InitWLAN() ERROR!!!");
			return false;
		}
		
		//if(freq=="5")
		//	str_Aband = "_Aband";
		//else 
			str_Aband = "";
			
		OBJ("st_wireless_radio"+str_Aband).innerHTML  = XG(phy_wlan_path+"/active")== "1" ? "<?echo i18n("Enabled");?>":"<?echo i18n("Disabled");?>";
		var IEEE80211mode =  XG(phy_wlan_path+"/media/wlmode");
		var string_bandwidth = "20MHZ";
		var check_bandwidth = 0;
        switch (IEEE80211mode)
		{
		   case "bgn":
		   		OBJ("st_80211mode"+str_Aband).innerHTML  = "<?echo i18n("Mixed 802.11n, 802.11g and 802.11b");?>";
                check_bandwidth = 1;
				break;
		   case "bg":
		   		OBJ("st_80211mode"+str_Aband).innerHTML  = "<?echo i18n("Mixed 802.11g and 802.11b");?>";
				break;
		   case "gn":
		   		OBJ("st_80211mode"+str_Aband).innerHTML  = "<?echo i18n("Mixed 802.11n and 802.11g");?>";
                check_bandwidth = 1;
				break;
		   case "an":
		   		OBJ("st_80211mode"+str_Aband).innerHTML  = "<?echo i18n("Mixed 802.11n and 802.11a");?>";
                check_bandwidth = 1;
				break;
		   case "n":
		   		OBJ("st_80211mode"+str_Aband).innerHTML  = "<?echo i18n("802.11n only");?>";
                check_bandwidth = 1;
				break;
		   case "b":
		   		OBJ("st_80211mode"+str_Aband).innerHTML  = "<?echo i18n("802.11b only");?>";
				break;
		   case "g":
		   		OBJ("st_80211mode"+str_Aband).innerHTML  = "<?echo i18n("802.11g only");?>";
				break;
		   case "a":
		   		OBJ("st_80211mode"+str_Aband).innerHTML  = "<?echo i18n("802.11a only");?>";
				break;
		}
		if (check_bandwidth==1)
		{
			string_bandwidth = XG  (phy_wlan_path+"/media/dot11n/bandwidth")== "20+40" ? "20/40MHz":"20MHz";
		}
		OBJ("st_Channel_Width"+str_Aband).innerHTML  = string_bandwidth;
		OBJ("st_Channel"+str_Aband).innerHTML  = XG  (rwlan1p+"/media/channel");
		
		var channel = XG  (rwlan1p+"/media/channel");
		OBJ("st_Channel"+str_Aband).innerHTML  = channel ? channel : "N/A";
		
		OBJ("st_SSID"+str_Aband).innerHTML  = XG  (wifi_path+"/ssid");
        var string_WPS =  "<?echo i18n("Disabled");?>";
		if ( XG  (wifi_path+"/wps/enable") == "1")
		{
        	string_WPS =  "<?echo i18n("Enabled");?>"+"/"+ (XG  (wifi_path+"/wps/configured")== "1" ? "<?echo i18n("Configured");?>":"<?echo i18n("Unconfigured");?>");
		}
		OBJ("st_WPS_status"+str_Aband).innerHTML  = string_WPS;
        var string_security = "<?echo i18n("Disabled");?>"; 
        if (XG  (wifi_path+"/encrtype") != "NONE")
		{
		    switch(XG  (wifi_path+"/authtype"))
			{
				case "OPEN":
				case "SHARED":
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
		OBJ("st_security"+str_Aband).innerHTML  = string_security;
	    return true;
	},	
	InitBridge: function()
	{						
		var br = PXML.FindModule("RUNTIME.INF.BRIDGE-1");		
		if (!br) { BODY.ShowAlert("InitBridge() ERROR !!!"); return false; }		
		var wantype = XG(br+"/runtime/inf/inet/addrtype");
		var wantype_str = "Unknow WAN type";
		
		if (wantype=="ipv4")
		{
			if (XG(br+"/runtime/inf/udhcpc/inet")!="")
				wantype_str = "DHCP Client";
			else
				wantype_str = "Static IP";
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
