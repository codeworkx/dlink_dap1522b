<script type="text/javascript">
function Page() {}
var WLan_BAND=null;
Page.prototype =
{
	services: "WIFI.PHYINF,PHYINF.WIFI,RUNTIME.PHYINF,RUNTIME.DFS,MACCLONE.WLAN-2",
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
	
		var wlan_uid = null;
		var swithmode_path = PXML.FindModule("RUNTIME.SWITCHMODE"); //The service is included in templates.php in DAP-1522B
		var swithmode = XG(swithmode_path+"/runtime/device/switchmode");
		if(swithmode==="APCLI")	wlan_uid = "WLAN-2";
		else	wlan_uid = "WLAN-1.1";
		
		this.initial_flag=true;
		this.wifip 			= PXML.FindModule("WIFI.PHYINF");
		this.runtime_phyinf = PXML.FindModule("RUNTIME.PHYINF");
		this.rphyinf 		= GPBT(this.runtime_phyinf, "phyinf","uid",wlan_uid, false);		
		
		if (!this.wifip)
		{
			BODY.ShowAlert("Initial() ERROR!!!");
			return false;
		}
		this.phyinf 		= GPBT(this.wifip, "phyinf", "uid",wlan_uid, false);
		var wifi_profile 	= XG(this.phyinf+"/wifi");
		this.wifip 			= GPBT(this.wifip+"/wifi", "entry", "uid", wifi_profile, false);
		this.opmode		 	= XG(this.wifip+"/opmode");
		
		if(this.opmode=="STA")
		{
			OBJ("div_WPS").style.display	= "block";	
			if(!this.Initial_STA(wlan_uid,"WIFI.PHYINF","RUNTIME.PHYINF")) return false; 
		}
		else
		{
			if(!this.Initial_AP(wlan_uid,"WIFI.PHYINF","RUNTIME.PHYINF")) return false; 
			if(!this.DFSCheck("RUNTIME.DFS")) return null; 	
			OBJ("div_MACClone").style.display	= "none";
			OBJ("div_WPS").style.display	= "none";			
		}
		this.do_reset_wps=0;

		var rebootflag = "<?echo query("/runtime/reboot/status");?>";
		if(rebootflag != "1"){OBJ("rebootitem1").style.display="none"; OBJ("rebootitem2").style.display="none";}
		else {OBJ("rebootitem1").style.display=""; OBJ("rebootitem2").style.display="";}

		return true;
	},
	PreSubmit: function()
	{	
		if(!this.DFSCheck("RUNTIME.DFS")) return null; 
		//if unconfigured button is pressed, don't do save xml.
		if(this.do_reset_wps==0)
		{
			if(this.opmode=="STA")
			{
				if(!this.SaveXMLSta("WLAN-2","WIFI.PHYINF","RUNTIME.PHYINF")) return null;
				if(!this.WPSCHK("WLAN-2","WIFI.PHYINF")) return null;
			}
			else
			{
				PXML.IgnoreModule("MACCLONE.WLAN-2");
				if(!this.SaveXMLAp("WLAN-1.1","WIFI.PHYINF","RUNTIME.PHYINF")) return null;
				if(!this.WPSCHK("WLAN-1.1","WIFI.PHYINF")) return null;				
			}
		} 

		//var wep_key=OBJ("wep_def_key"+WLan_BAND).value;
		//var wep_key_len=OBJ("wep_key_len"+WLan_BAND).value;
		if(OBJ("ssid"+WLan_BAND).value.charAt(0)===" "|| OBJ("ssid"+WLan_BAND).value.charAt(OBJ("ssid"+WLan_BAND).value.length-1)===" ")
		{
			alert("<?echo I18N("h", "The prefix or postfix of the 'Wireless Network Name' could not be blank.");?>");
			return null;
		}
		if((OBJ("security_type"+WLan_BAND).value==="WPA" || OBJ("security_type"+WLan_BAND).value==="WPA2" ) && OBJ("psk_eap"+WLan_BAND).value=="PSK")
		{
			if (OBJ("wpapsk"+WLan_BAND).value.charAt(0)===" " || OBJ("wpapsk"+WLan_BAND).value.charAt(OBJ("wpapsk"+WLan_BAND).value.length-1)===" ")
			{
				alert("<?echo I18N("h", "The prefix or postfix of the 'Network Key' could not be blank.");?>");
				return null;
			}
			
			if(WLan_BAND != "_sta")
			{
				if(OBJ("wpa_rekey"+WLan_BAND).value.charAt(0)===" " || OBJ("wpa_rekey"+WLan_BAND).value.length == 0)
				{
					alert("<?echo I18N("h", "The Group key update interval could not be blank.");?>");
					return null;
				}
			}
		}
			/*
		if(OBJ("security_type"+WLan_BAND).value==="WEP") //wep_64_1_Aband
		{ 
		  
			if (OBJ("wep_"+wep_key_len+"_"+wep_key+WLan_BAND).value.charAt(0) === " "|| OBJ("wep_"+wep_key_len+"_"+wep_key+WLan_BAND).value.charAt(OBJ("wep_"+wep_key_len+"_"+wep_key+WLan_BAND).value.length-1)===" ")
			{
				alert("<?echo I18N("h", "The prefix or postfix of the 'WEP Key' could not be blank.");?>");
				return null;
			}
			
			var strlen = OBJ("wep_"+wep_key_len+"_"+wep_key+WLan_BAND).value.length;
			if(wep_key_len == "64")
			{
				if((strlen!=5) && (strlen!=10))
				{
					BODY.ShowAlert("The WEP key should be 5 or 10 characters long." );
					return null;
				}
			}else //if wep_key_len == "128"
			{
				if((strlen!=13) && (strlen!=26))
				{
					BODY.ShowAlert("The WEP key should be 13 or 26 characters long.");
					return null;
				}
			}
		}
				*/
		PXML.IgnoreModule("RUNTIME.PHYINF");
		PXML.IgnoreModule("RUNTIME.DFS");
		PXML.CheckModule("WIFI.PHYINF", null,null, "ignore");	//we now use PHYINF.WIFI to activate service
		return PXML.doc;
	},
	OnClickEnWPS: function()
	{
		var en_wlan = XG(this.phyinf+"/active");
		if (OBJ("en_wps").checked )
		{
			if(this.opmode != "STA")
			{
				OBJ("ssid_invisible"+WLan_BAND).checked = false;
				OBJ("ssid_visible"+WLan_BAND).checked = true;
				OBJ("ssid_visible"+WLan_BAND).disabled = OBJ("ssid_invisible"+WLan_BAND).disabled = true;
			}
			
/*			if (XG(this.wifip+"/wps/configured")=="0")
				OBJ("reset_cfg").disabled = true;
			else
				OBJ("reset_cfg").disabled = false;
			OBJ("reset_pin").disabled	= false;
			OBJ("gen_pin").disabled		= false;
			OBJ("pro_wps").disabled		= false;*/
		}
		else
		{
/*			OBJ("reset_cfg").disabled	= true;
			OBJ("reset_pin").disabled	= true;
			OBJ("gen_pin").disabled		= true;*/
			if(this.opmode != "STA")
				OBJ("ssid_visible"+WLan_BAND).disabled = OBJ("ssid_invisible"+WLan_BAND).disabled = false;
//			OBJ("pro_wps").disabled		= true;
		}
	},
	DFSCheck: function(runtime_dfs)
	{
		var dfs_data = PXML.FindModule(runtime_dfs);
		var currentChannel = OBJ("channel_Aband").value;
		var dfsBlckTotal = XG(dfs_data+"/dfs_blocked/#");
		var dfsBlckChannel = "";
		
		for(i=0;i<dfsBlckTotal;i++)
		{
			var myindex = i+1;
			dfsBlckChannel = XG(dfs_data+"/dfs_blocked/entry:"+myindex+"/channel");
			if(currentChannel == dfsBlckChannel)
			{
				alert("<?echo i18n("Can't select this channel ");?>"+currentChannel+"<?echo i18n(" because radar detected on this channel.");?>\n"+
					"<?echo i18n("This channel may be enabled after 30 minutes. Please select other channel !");?>");
				OBJ("channel_Aband").focus();
				return false;
			}
		}
		return true;
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
	phyinf: null,
	runtime_phyinf: null,
	rphyinf: null,
	sec_type: null,
	sec_type_Aband: null,
	sec_type_sta: null,
	wlanMode: null,
	bandWidth: null,
	shortGuard: null,
	wps: true,
	str_Aband: null,
	feature_nosch: <? if($FEATURE_NOSCH!="1")echo '0'; else echo '1'; ?>,
	mac_clone_path: null,
	ScanMAC_num: null,
	ShowMAC_cnt: null,
	MACaddr: new Array(),
	
	initial_flag: false,
	
	Initial_STA: function(wlan_uid,wifi_phyinf,runtime_phyinf)
	{
		OBJ("div_station").style.display = "block";	
		OBJ("div_24G_band").style.display = "none";	
		OBJ("div_5G_band").style.display = "none";	
		
		OBJ("en_wifi_sta").checked = COMM_ToBOOL(XG(this.phyinf+"/active"));
		WLan_BAND="_sta";
		
		OBJ("ssid_sta").value = XG(this.wifip+"/ssid");
		<? if($FEATURE_NOSCH!="1")echo 'COMM_SetSelectValue(OBJ("sch_sta"), XG(this.phyinf+"/schedule"));\n'; ?>	
		if (!OBJ("en_wifi_sta").checked)
			this.sec_type_sta = "";
		else if (XG(this.wifip+"/encrtype")=="WEP")
			this.sec_type_sta = "WEP";
		/*
		else if (/WPA/.test(XG(this.wifip+"/authtype")))
			this.sec_type = "wpa";
		*/
		else if (XG(this.wifip+"/authtype")=="WPAPSK" || 
             XG(this.wifip+"/authtype")=="WPAEAP" || 
             XG(this.wifip+"/authtype")=="WPA")
			this.sec_type_sta = "WPA"
		else if (XG(this.wifip+"/authtype")=="WPA2PSK" || 
             XG(this.wifip+"/authtype")=="WPA2EAP" || 
             XG(this.wifip+"/authtype")=="WPA2")
			 this.sec_type_sta = "WPA2"
		else if (XG(this.wifip+"/authtype")=="WPA+2PSK" ||
				 XG(this.wifip+"/authtype")=="WPA+2"  || 
				 XG(this.wifip+"/authtype")=="WPA+2EAP" )
			this.sec_type_sta = "WPA+2"		
		else
			this.sec_type_sta = "";
		///tomcat COMM_SetSelectValue(OBJ("security_type_sta"), this.sec_type_sta);

		///////////////// initial WEP /////////////////
		var auth = XG(this.wifip+"/authtype");	
		var len = (XG(this.wifip+"/nwkey/wep/size")=="")? "64" : XG(this.wifip+"/nwkey/wep/size");
		var defkey = (XG(this.wifip+"/nwkey/wep/defkey")=="")? "1" : XG(this.wifip+"/nwkey/wep/defkey");
		this.wps = COMM_ToBOOL(XG(this.wifip+"/wps/enable"));
		///tomcat OBJ("auth_type_sta").disabled = this.wps;
		if (auth!="SHARED") auth = "OPEN";
		if (auth=="SHARED")
			COMM_SetSelectValue(OBJ("auth_type_sta"),	auth);
		COMM_SetSelectValue(OBJ("wep_key_len_sta"),	len);
		///tomcat COMM_SetSelectValue(OBJ("wep_def_key_sta"),	defkey);
		///tomcat for (var i=1; i<5; i++)
		///tomcat	OBJ("wep_"+len+"_"+i+"_sta").value = XG(this.wifip+"/nwkey/wep/key:"+i);		
		if(len == "64")
			OBJ("wep_64_1_sta").value		= XG(this.wifip+"/nwkey/wep/key:1/");
		else
			OBJ("wep_128_1_sta").value		= XG(this.wifip+"/nwkey/wep/key:1/");		
		
		///////////////// initial WPA /////////////////		
		var cipher = XG(this.wifip+"/encrtype");
		if(cipher == "WEP")
			auth = "WEP";
		var type = auth;		
		switch (XG(this.wifip+"/authtype"))
		{
			case "WPA":
			case "WPA2":
			case "WPA+2":
				type = "WPA_E";
				break;
			case "WPAPSK":
			case "WPA2PSK":
			case "WPA+2PSK":
				type = "WPA_P";
				break;	
			
		}				
		//get the wpa mode		
		switch (XG(this.wifip+"/authtype"))
		{
			case "WPA":
			case "WPAPSK":			
				wpamode = "WPA";
				break;
			case "WPA2":			
			case "WPA2PSK":
				wpamode = "WPA2";
				break;			
			case "WPA+2PSK":
			case "WPA+2":
				wpamode = "WPA+WPA2";
				break;				
			default:
				wpamode = "WPA+WPA2";
		}		
		
		COMM_SetSelectValue(OBJ("cipher_type_sta"), cipher);		
		COMM_SetSelectValue(OBJ("security_type_sta"),type);		
		if(cipher == "WEP")
			COMM_SetSelectValue(OBJ("security_type_sta"),"WEP");
		
		COMM_SetSelectValue(OBJ("wpa_mode_sta"), wpamode);
		
		///tomcat COMM_SetSelectValue(OBJ("cipher_type_sta"), cipher);
		///tomcat COMM_SetSelectValue(OBJ("psk_eap_sta"), type);

		OBJ("wpapsk_sta").value		= XG(this.wifip+"/nwkey/psk/key");
		OBJ("srv_ip_sta").value		= XG(this.wifip+"/nwkey/eap/radius");
		OBJ("srv_port_sta").value	= XG(this.wifip+"/nwkey/eap/port");
		OBJ("srv_sec_sta").value	= XG(this.wifip+"/nwkey/eap/secret");
		
		//TLD suggests us to set 20/40 auto mode at APC. In order to simulate as wireless card.
		//OBJ("bw_sta").value			= XG(this.phyinf+"/media/dot11n/bandwidth");
		OBJ("bw_sta").value = "20+40";
		OBJ("bw_sta").disabled = true;
		
		//for WPS
		var wps_enable 		= XG(this.wifip+"/wps/enable");
		var wps_configured  = XG(this.wifip+"/wps/configured");
		OBJ("en_wps").checked = COMM_ToBOOL(wps_enable);
		
		if (XG(this.wifip+"/wps/pin")=="")
			this.curpin = OBJ("pin").innerHTML = this.defpin;
		else
			this.curpin = OBJ("pin").innerHTML = XG(this.wifip+"/wps/pin");
		this.OnClickEnWPS();
		if(wps_enable == "1") 		str_info =  "<?echo i18n("Enabled");?>"; else str_info = "<?echo i18n("Disabled");?>";
		if(wps_configured == "1") 	str_info += "<?echo i18n(" / Configured");?>"; else str_info += "<?echo i18n(" / Not configured");?>";			
//		OBJ("wifi_info_str").innerHTML = str_info;
		//end of WPS
		
		this.OnChangeSecurityType("_sta");
		this.OnChangeWEPKey("_sta");
		///tomcat this.OnChangeWEPKey("_sta");
		///tomcat this.OnChangeWPAAuth("_sta");
		this.OnClickEnWLANsta();
		
		/*Initial MAC clone settings*/
		this.mac_clone_path = PXML.FindModule("MACCLONE.WLAN-2");
		this.mac_clone_path = this.mac_clone_path+"/phyinf/macclone";
		var mac_clone_type = XG(this.mac_clone_path+"/type");
		
		if(mac_clone_type !== "DISABLED") 
		{
			OBJ("en_mac_clone").checked = true;
			COMM_SetSelectValue(OBJ("mac_source_type"),mac_clone_type);
		}
		else
		{
			OBJ("en_mac_clone").checked = false;
			COMM_SetSelectValue(OBJ("mac_source_type"),"AUTO");
		}
		
		var mac = this.GetMAC(XG(this.mac_clone_path+"/macaddr"));
		for (var j=1; j<=6; j++)
		{
			if (mac[j].length === "1") mac[j] = "0"+mac[j];	
			OBJ("mac"+j).value = mac[j].toUpperCase();
		}
		this.OnClickEnMacClone();
		
		return true;
	},
	Initial_AP: function(wlan_uid,wifi_phyinf,runtime_phyinf)
	{
		var freq = XG(this.phyinf+"/media/freq");		
		var str_Aband = "";
		if(freq == "5") 
		{
			str_Aband = "_Aband"; 
			WLan_BAND="_Aband";
		}
		else
		{
			str_Aband = "";
			WLan_BAND="";
		} 	
			
		if(str_Aband == "")
		{	
			OBJ("wifi_mode"+str_Aband).innerHTML = "<?echo i18n("2.4GHz");?>"; 
			OBJ("div_24G_band").style.display = "block";	
			OBJ("div_5G_band").style.display = "none";	
		}
		else 					
		{	
			OBJ("wifi_mode"+str_Aband).innerHTML = "<?echo i18n("5GHz");?>"; 
			OBJ("div_24G_band").style.display = "none";	
			OBJ("div_5G_band").style.display = "block";	
		}
		OBJ("div_WPS").style.display = "none";
		OBJ("div_station").style.display = "none";	
		COMM_SetSelectValue(OBJ("wlan_mode"+str_Aband), XG(this.phyinf+"/media/wlmode"+str_Aband));
		OBJ("en_wifi"+str_Aband).checked = COMM_ToBOOL(XG(this.phyinf+"/active"));
	
		OBJ("ssid"+str_Aband).value = XG(this.wifip+"/ssid");
		<? if($FEATURE_NOSCH!="1")echo 'COMM_SetSelectValue(OBJ("sch"+str_Aband), XG(this.phyinf+"/schedule"));\n'; ?>
		OBJ("auto_ch"+str_Aband).checked = (XG(this.phyinf+"/media/channel"+str_Aband)=="0")? true : false;
		if (OBJ("auto_ch"+str_Aband).checked)
			COMM_SetSelectValue(OBJ("channel"+str_Aband), XG(this.rphyinf+"/media/channel"+str_Aband));
		else
			COMM_SetSelectValue(OBJ("channel"+str_Aband), XG(this.phyinf+"/media/channel"+str_Aband));
			
			
		
		COMM_SetSelectValue(OBJ("bw"+str_Aband), XG(this.phyinf+"/media/dot11n/bandwidth"+str_Aband));	
		
			
		OBJ("en_wmm"+str_Aband).checked = COMM_ToBOOL(XG(this.phyinf+"/media/wmm/enable"));
		//if (/n/.test(this.wlanMode)) OBJ("en_wmm"+str_Aband).disabled = true;
		
		OBJ("ssid_visible"+str_Aband).checked = (XG(this.wifip+"/ssidhidden") === "0")? true : false;
		OBJ("ssid_invisible"+str_Aband).checked = (XG(this.wifip+"/ssidhidden") !== "0")? true : false;
		
		this.OnChangeWLMode(str_Aband);
		
		if(str_Aband == "")	//g band
		{
			if (!OBJ("en_wifi"+str_Aband).checked)
				this.sec_type = "";
			else if (XG(this.wifip+"/encrtype")=="WEP")
				this.sec_type = "WEP";
			/*else if (/WPA/.test(XG(this.wifip+"/authtype")))
				this.sec_type = "wpa";
			*/
			else if (XG(this.wifip+"/authtype")=="WPAPSK" ||
				 XG(this.wifip+"/authtype")=="WPA" || 
				 XG(this.wifip+"/authtype")=="WPAEAP")
			{
				this.sec_type = "WPA";
			}
			else if (XG(this.wifip+"/authtype")=="WPA2PSK" ||
				 XG(this.wifip+"/authtype")=="WPA2" || 
				 XG(this.wifip+"/authtype")=="WPA2EAP" )
				this.sec_type = "WPA2"
			else if (XG(this.wifip+"/authtype")=="WPA+2PSK" ||
				 XG(this.wifip+"/authtype")=="WPA+2"  || 
				 XG(this.wifip+"/authtype")=="WPA+2EAP" )
				this.sec_type = "WPA+2"
			else
				this.sec_type = "";
			
			///tomcat COMM_SetSelectValue(OBJ("security_type"+str_Aband), this.sec_type);
		}
		else //a band
		{
			if (!OBJ("en_wifi"+str_Aband).checked)
				this.sec_type_Aband = "";
			else if (XG(this.wifip+"/encrtype")=="WEP")
				this.sec_type_Aband = "WEP";
			/*
			else if (/WPA/.test(XG(this.wifip+"/authtype")))
				this.sec_type_Aband = "wpa";
			*/
			else if (XG(this.wifip+"/authtype")=="WPAPSK" ||
				 XG(this.wifip+"/authtype")=="WPA"  || 
				 XG(this.wifip+"/authtype")=="WPAEAP")
				this.sec_type_Aband = "WPA"
			else if (XG(this.wifip+"/authtype")=="WPA2PSK" ||
				 XG(this.wifip+"/authtype")=="WPA2" || 
				 XG(this.wifip+"/authtype")=="WPA2EAP")
				this.sec_type_Aband = "WPA2"
			else if (XG(this.wifip+"/authtype")=="WPA+2PSK" ||
				 XG(this.wifip+"/authtype")=="WPA+2" || 
				 XG(this.wifip+"/authtype")=="WPA+2EAP" )
				this.sec_type_Aband = "WPA+2"
			else
				this.sec_type_Aband = "";
			
			///tomcat COMM_SetSelectValue(OBJ("security_type"+str_Aband), this.sec_type_Aband);
		}
		
		
		///////////////// initial WEP /////////////////
		var auth = XG(this.wifip+"/authtype");
		var len = (XG(this.wifip+"/nwkey/wep/size")=="")? "64" : XG(this.wifip+"/nwkey/wep/size");
		var defkey = "1";//(XG(this.wifip+"/nwkey/wep/defkey")=="")? "1" : XG(this.wifip+"/nwkey/wep/defkey");
		this.wps = COMM_ToBOOL(XG(this.wifip+"/wps/enable"));
		///tomcat OBJ("auth_type"+str_Aband).disabled = this.wps;
		if (auth!="SHARED") auth = "OPEN";
		
		if(auth == "SHARED")
			COMM_SetSelectValue(OBJ("auth_type"+str_Aband),	"SHARED");
		
		COMM_SetSelectValue(OBJ("wep_key_len"+str_Aband),	len);
		
		///tomcat COMM_SetSelectValue(OBJ("wep_def_key"+str_Aband),	defkey);
		///tomcat for (var i=1; i<5; i++)
		///tomcat 	OBJ("wep_"+len+"_"+i+str_Aband).value = XG(this.wifip+"/nwkey/wep/key:"+i);
		if(false)
		{
			OBJ("wep_64"+str_Aband).style.display = "none";
			OBJ("wep_128"+str_Aband).style.display = "block";
		}
		else
		{
			OBJ("wep_64"+str_Aband).style.display = "block";
                        OBJ("wep_128"+str_Aband).style.display = "none";
		}
		
		if(len == "64")
			OBJ("wep_64_1"+str_Aband).value = XG(this.wifip+"/nwkey/wep/key:1");
		else
			OBJ("wep_128_1"+str_Aband).value = XG(this.wifip+"/nwkey/wep/key:1");
		
		///////////////// initial WPA /////////////////
		var cipher = XG(this.wifip+"/encrtype");
		var type = "";
		var wpamode =null;
		
		switch (XG(this.wifip+"/authtype"))
		{
			case "WPA":
			case "WPA2":
			case "WPA+2":
				type = "WPA_E";
				break;
			case "WPAPSK":
			case "WPA2PSK":
			case "WPA+2PSK":
				type = "WPA_P";
				break;
		}		
		//get the wpa mode		
		switch (XG(this.wifip+"/authtype"))
		{
			case "WPA":
			case "WPAPSK":			
				wpamode = "WPA";
				break;
			case "WPA2":			
			case "WPA2PSK":
				wpamode = "WPA2";
				break;			
			case "WPA+2PSK":
			case "WPA+2":
				wpamode = "WPA+WPA2";
				break;				
			default:
				wpamode = "WPA+WPA2";
		}		
		
		OBJ("wpapsk"+str_Aband).value = XG(this.wifip+"/nwkey/psk/key");
		COMM_SetSelectValue(OBJ("cipher_type"+str_Aband), cipher);		
		COMM_SetSelectValue(OBJ("security_type"+str_Aband), type);
		COMM_SetSelectValue(OBJ("wpa_mode"+str_Aband), wpamode);

		///tomcat OBJ("wpapsk"+str_Aband).value		= XG(this.wifip+"/nwkey/psk/key");
		///tomcat if(XG(this.wifip+"/nwkey/rekey/gtk") == "")
		///tomcat 	OBJ("wpa_rekey"+str_Aband).value	= 1800;
		///tomcat else
		///tomcat 	OBJ("wpa_rekey"+str_Aband).value	= XG(this.wifip+"/nwkey/rekey/gtk");
		
		OBJ("srv_ip"+str_Aband).value		= XG(this.wifip+"/nwkey/eap/radius");
	    OBJ("srv_port"+str_Aband).value		= XG(this.wifip+"/nwkey/eap/port");
		OBJ("srv_sec"+str_Aband).value		= XG(this.wifip+"/nwkey/eap/secret");
		
		/*for WPS
		var wps_enable 		= XG(this.wifip+"/wps/enable");
		var wps_configured  = XG(this.wifip+"/wps/configured");
		OBJ("en_wps").checked = COMM_ToBOOL(wps_enable);
		
		if (XG(this.wifip+"/wps/pin")=="")
			this.curpin = OBJ("pin").innerHTML = this.defpin;
		else
			this.curpin = OBJ("pin").innerHTML = XG(this.wifip+"/wps/pin");
		this.OnClickEnWPS();
		if(wps_enable == "1") 		str_info =  "Enabled"; else str_info = "Disabled";
		if(wps_configured == "1") 	str_info += " / Configured"; else str_info += " / Not configured";			
		OBJ("wifi_info_str").innerHTML = str_info;
		//end of WPS*/
	
		this.OnChangeSecurityType(str_Aband);
		///tomcat this.OnChangeWEPKey(str_Aband);
		///tomcat this.OnChangeWPAAuth(str_Aband);
		this.OnChangeWEPKey(str_Aband);
		this.OnClickEnWLAN(str_Aband);
		this.OnClickEnAutoChannel(str_Aband);
		//this.OnChangeWLMode(str_Aband);
		this.OnClickChangeChannel(str_Aband);
		this.initial_flag=false;
		return true;
	},
	WPSCHK: function(wlan1_phyinf,wifi_phyinf)
	{
		if (COMM_EqBOOL(OBJ("ssid").getAttribute("modified"),true) ||
			COMM_EqBOOL(OBJ("ssid_Aband").getAttribute("modified"),true) ||
			COMM_EqBOOL(OBJ("ssid_sta").getAttribute("modified"),true) ||
			COMM_EqBOOL(OBJ("security_type").getAttribute("modified"),true) ||
			COMM_EqBOOL(OBJ("security_type_Aband").getAttribute("modified"),true) ||
			COMM_EqBOOL(OBJ("security_type_sta").getAttribute("modified"),true))
		{
			XS(this.wifip+"/wps/configured", "1");
		}
		return true;
	},
	
	SaveXMLSta: function(wlan_uid,wifi_phyinf,runtime_phyinf)
	{
		this.wifip 			= PXML.FindModule(wifi_phyinf);
		this.phyinf 		= GPBT(this.wifip,"phyinf","uid",wlan_uid,false);
		var wifi_profile 	= XG(this.phyinf+"/wifi");
		this.wifip 			= GPBT(this.wifip+"/wifi", "entry", "uid", wifi_profile, false);

		if (OBJ("en_wifi_sta").checked)
			XS(this.phyinf+"/active", "1");
		else
		{
			XS(this.phyinf+"/active", "0");
			return true;
		}
		
		XS(this.wifip+"/ssid",		OBJ("ssid_sta").value);
		<? if ($FEATURE_NOSCH!="1")echo 'XS(this.phyinf+"/schedule",    OBJ("sch_sta").value);\n';?>
		if (OBJ("security_type_sta").value=="WEP")
		{
			if (OBJ("auth_type_sta").value=="SHARED")
			{
					if(OBJ("en_wps").checked)
						{
							  alert('<?echo i18n("Please disable wps mode or change Authentication  mode first!");?>');
							  return false;
						}				
			
				XS(this.wifip+"/authtype", "SHARED");
			}else
				XS(this.wifip+"/authtype", "OPEN");
			XS(this.wifip+"/encrtype",			"WEP");
			XS(this.wifip+"/nwkey/wep/size",	"");
			XS(this.wifip+"/nwkey/wep/ascii",	"");
			XS(this.wifip+"/nwkey/wep/defkey",	"1");
			
			if(OBJ("wep_key_len_sta").value == "64")
				XS(this.wifip+"/nwkey/wep/key:1", OBJ("wep_64_1_sta").value);
			else{			
				XS(this.wifip+"/nwkey/wep/key:1", OBJ("wep_128_1_sta").value);
				if(OBJ("wep_128_1_sta").value.length == 10 || OBJ("wep_128_1_sta").value.length == 5 )
						{
							alert('<?echo i18n("Please input 26 hex digits or 13 characters");?>');
							return false;
						}
			}
			/*for (var i=1, len=OBJ("wep_key_len_sta").value; i<5; i++)
			{
				if (i==OBJ("wep_def_key_sta").value)
					XS(this.wifip+"/nwkey/wep/key:"+i, OBJ("wep_"+len+"_"+i+"_sta").value);
				else
					XS(this.wifip+"/nwkey/wep/key:"+i, "");
			}*/
		}else if (OBJ("security_type_sta").value=="WPA_P")
		{			
			XS(this.wifip+"/encrtype", OBJ("cipher_type_sta").value);
			if (OBJ("wpa_mode_sta").value=="WPA")
			{
				XS(this.wifip+"/authtype",				"WPAPSK");
				XS(this.wifip+"/nwkey/psk/passphrase",	"");
				XS(this.wifip+"/nwkey/psk/key",			OBJ("wpapsk_sta").value);
				//XS(this.wifip+"/nwkey/rekey/gtk",		OBJ("wpa_rekey"+str_Aband).value);
			}
			else if (OBJ("wpa_mode_sta").value=="WPA2")
			{
				XS(this.wifip+"/authtype",				"WPA2PSK");
				XS(this.wifip+"/nwkey/psk/passphrase",	"");
				XS(this.wifip+"/nwkey/psk/key",			OBJ("wpapsk_sta").value);
				//XS(this.wifip+"/nwkey/rekey/gtk",		OBJ("wpa_rekey"+str_Aband).value);
			}else  //wpa+wpa2 mode
			{
				XS(this.wifip+"/authtype",				"WPA+2PSK");
				XS(this.wifip+"/nwkey/psk/passphrase",	"");
				XS(this.wifip+"/nwkey/psk/key",			OBJ("wpapsk_sta").value);
				//XS(this.wifip+"/nwkey/rekey/gtk",		OBJ("wpa_rekey"+str_Aband).value);
			}
			
		}else if(OBJ("security_type_sta").value=="WPA_E")
		{
			XS(this.wifip+"/encrtype", OBJ("cipher_type_sta").value);
			if (OBJ("wpa_mode_sta").value=="WPA")
			{
				XS(this.wifip+"/authtype",				"WPA");				
			}
			else if (OBJ("wpa_mode_sta").value=="WPA2")
			{
				XS(this.wifip+"/authtype",				"WPA2");			
			}else  //wpa+wpa2 mode
			{
				XS(this.wifip+"/authtype",				"WPA+2");				
			}			
			XS(this.wifip+"/nwkey/eap/radius",	OBJ("srv_ip_sta").value);
			XS(this.wifip+"/nwkey/eap/port",	OBJ("srv_port_sta").value);
			XS(this.wifip+"/nwkey/eap/secret",	OBJ("srv_sec_sta").value);				
		}else
		{
			XS(this.wifip+"/authtype", "OPEN");
			XS(this.wifip+"/encrtype", "NONE");
		}
		
		//for bandwidth
		XS(this.phyinf+"/media/dot11n/bandwidth",		OBJ("bw_sta").value);
		
		//for WPS setting
		XS(this.wifip+"/wps/enable", (OBJ("en_wps").checked)? "1":"0");
				
		//for MAC clone
		var macstr = "";
		for (var i=1; i<=6; i++)
		{				
			if(OBJ("mac"+i).value != "")
			{
				macstr = macstr + OBJ("mac"+i).value.toUpperCase();				
				if(i<6) macstr = macstr +":";
			}	
		}
		
		if(OBJ("en_mac_clone").checked && OBJ("mac_source_type").value === "MANUAL" && macstr==="")
		{
			BODY.ShowAlert("<?echo i18n("No MAC address value.");?>"); 
			OBJ("mac1").focus();
			return false;
		}
		
		if(OBJ("en_mac_clone").checked)
		{
			XS(this.mac_clone_path+"/type",OBJ("mac_source_type").value);
			if(OBJ("mac_source_type").value === "MANUAL")
			{
				XS(this.mac_clone_path+"/macaddr", macstr);
			}
			else
			{
				XS(this.mac_clone_path+"/macaddr", "");
			}
		}
		else
		{
			XS(this.mac_clone_path+"/type","DISABLED");
			XS(this.mac_clone_path+"/macaddr", "");
		}	
	
		return true;
	},
	
	SaveXMLAp: function(wlan_uid,wifi_phyinf,runtime_phyinf)
	{
		this.phyinf = this.wifip = PXML.FindModule(wifi_phyinf);
		this.phyinf = GPBT(this.phyinf,"phyinf","uid",wlan_uid,false);
		var wifi_profile = XG(this.phyinf+"/wifi");
		this.wifip = GPBT(this.wifip+"/wifi", "entry", "uid", wifi_profile, false);
		var freq = XG(this.phyinf+"/media/freq");

		if(freq == "5")
			str_Aband = "_Aband";
		else
			str_Aband = "";
		
		if (OBJ("en_wifi"+str_Aband).checked)
			XS(this.phyinf+"/active", "1");
		else
		{
			XS(this.phyinf+"/active", "0");
			return true;
		}
		
		XS(this.wifip+"/ssid",		OBJ("ssid"+str_Aband).value);
		//XS(this.phyinf+"/schedule",    OBJ("sch"+str_Aband).value);
		//alert(OBJ("sch"+str_Aband).value);
		<? if ($FEATURE_NOSCH!="1")echo 'XS(this.phyinf+"/schedule",    OBJ("sch"+str_Aband).value);\n';?>
		if (OBJ("auto_ch"+str_Aband).checked)
			XS(this.phyinf+"/media/channel"+str_Aband, "0");
		else
			XS(this.phyinf+"/media/channel"+str_Aband, OBJ("channel"+str_Aband).value);
		
		if (OBJ("txrate"+str_Aband).value=="-1")
		{
			XS(this.phyinf+"/media/dot11n/mcs/auto", "1");
			XS(this.phyinf+"/media/dot11n/mcs/index", "");
		}
		else
		{
			XS(this.phyinf+"/media/dot11n/mcs/auto", "0");
			XS(this.phyinf+"/media/dot11n/mcs/index", OBJ("txrate"+str_Aband).value);
		}
		XS(this.phyinf+"/media/wlmode"+str_Aband, OBJ("wlan_mode"+str_Aband).value);
		
		//11n only , enable wmm
		if( XG(this.phyinf+"/media/wlmode"+str_Aband) == "n" )
			XS(this.phyinf+"/media/wmm/enable",	"1");
		
		if (/n/.test(OBJ("wlan_mode"+str_Aband).value))
		{
			XS(this.phyinf+"/media/dot11n/bandwidth"+str_Aband, OBJ("bw"+str_Aband).value);
			this.bandWidth = OBJ("bw"+str_Aband).value;
		}		
		//XS(this.phyinf+"/media/wmm/enable",	SetBNode(OBJ("en_wmm"+str_Aband).checked));
		XS(this.wifip+"/ssidhidden",(OBJ("ssid_visible"+str_Aband).checked === true) ? 0 : 1);
		
		if( OBJ("ssid_invisible"+str_Aband).checked )
					XS(this.wifip+"/wps/enable","0");
		
		if (OBJ("security_type"+str_Aband).value=="WEP")
		{
			
			if (OBJ("auth_type"+str_Aband).value=="SHARED")
					{
						if(XG(this.wifip+"/wps/enable") == "1")
								{
									  alert('<?echo i18n("Please disable wps mode first!");?>');
									  return false;
								}				
						XS(this.wifip+"/authtype", "SHARED");
					}else
						XS(this.wifip+"/authtype", "BOTH");
				
			
				
			XS(this.wifip+"/encrtype",			"WEP");
			XS(this.wifip+"/nwkey/wep/size",	"");
			XS(this.wifip+"/nwkey/wep/ascii",	"");
			
			XS(this.wifip+"/nwkey/wep/defkey",	"1");
			if(OBJ("wep_key_len"+str_Aband).value == "64")
				XS(this.wifip+"/nwkey/wep/key:1", OBJ("wep_64_1"+str_Aband).value);
			else{
					XS(this.wifip+"/nwkey/wep/key:1", OBJ("wep_128_1"+str_Aband).value);
					if(OBJ("wep_128_1"+str_Aband).value.length == 10 || OBJ("wep_128_1"+str_Aband).value.length == 5 )
						{
							alert('<?echo i18n("Please input 26 hex digits or 13 characters");?>');
							return false;
						}
				}
			/*
			for (var i=1; i<5; i++)
			{
				
					XS(this.wifip+"/nwkey/wep/key:"+i, OBJ("wep_64_1"+str_Aband).value);
				
			}
			*/
			
		}
		else if (OBJ("security_type"+str_Aband).value=="WPA_P")
		{			
			XS(this.wifip+"/encrtype", OBJ("cipher_type"+str_Aband).value);
			if (OBJ("wpa_mode"+str_Aband).value=="WPA")
			{
				XS(this.wifip+"/authtype",				"WPAPSK");
				XS(this.wifip+"/nwkey/psk/passphrase",	"");
				XS(this.wifip+"/nwkey/psk/key",			OBJ("wpapsk"+str_Aband).value);
				//XS(this.wifip+"/nwkey/rekey/gtk",		OBJ("wpa_rekey"+str_Aband).value);
			}
			else if (OBJ("wpa_mode"+str_Aband).value=="WPA2")
			{
				XS(this.wifip+"/authtype",				"WPA2PSK");
				XS(this.wifip+"/nwkey/psk/passphrase",	"");
				XS(this.wifip+"/nwkey/psk/key",			OBJ("wpapsk"+str_Aband).value);
				//XS(this.wifip+"/nwkey/rekey/gtk",		OBJ("wpa_rekey"+str_Aband).value);
			}else  //wpa+wpa2 mode
			{
				XS(this.wifip+"/authtype",				"WPA+2PSK");
				XS(this.wifip+"/nwkey/psk/passphrase",	"");
				XS(this.wifip+"/nwkey/psk/key",			OBJ("wpapsk"+str_Aband).value);
				//XS(this.wifip+"/nwkey/rekey/gtk",		OBJ("wpa_rekey"+str_Aband).value);
			}
			
		}else if(OBJ("security_type"+str_Aband).value=="WPA_E")
		{
			XS(this.wifip+"/encrtype", OBJ("cipher_type"+str_Aband).value);
			if (OBJ("wpa_mode"+str_Aband).value=="WPA")
			{
				XS(this.wifip+"/authtype",				"WPA");				
			}
			else if (OBJ("wpa_mode"+str_Aband).value=="WPA2")
			{
				XS(this.wifip+"/authtype",				"WPA2");			
			}else  //wpa+wpa2 mode
			{
				XS(this.wifip+"/authtype",				"WPA+2");				
			}			
			XS(this.wifip+"/nwkey/eap/radius",	OBJ("srv_ip"+str_Aband).value);
			XS(this.wifip+"/nwkey/eap/port",	OBJ("srv_port"+str_Aband).value);
			XS(this.wifip+"/nwkey/eap/secret",	OBJ("srv_sec"+str_Aband).value);				
		}
		else
		{			
			XS(this.wifip+"/authtype", "OPEN");		
			XS(this.wifip+"/encrtype", "NONE");
		}
		//for WPS setting
		//XS(this.wifip+"/wps/enable", (OBJ("en_wps").checked)? "1":"0");
		//for guestzone, we always disable wps.
		var wifi_phyinf_tmp 	= PXML.FindModule(wifi_phyinf);
		guest_phyinf 		= GPBT(wifi_phyinf_tmp,"phyinf","uid","WLAN-1.2",false);
		var guest_profile 	= XG(guest_phyinf+"/wifi");
		guest_profile 		= GPBT(wifi_phyinf_tmp+"/wifi", "entry", "uid", guest_profile, false);
		XS(guest_profile+"/wps/enable", "0");	
		//return false;
		
		return true;
	},
	
	OnClickEnWLANsta:function()
	{
		var val = (OBJ("en_wifi_sta").checked)?false:true;
		
		if((OBJ("en_wifi_sta").checked) == false) 	
		{
			this.sec_type_sta="";
			OBJ("div_WPS").style.display = "none";
			OBJ("div_MACClone").style.display	= "none";
		}
		else
		{
			OBJ("div_WPS").style.display = "block";
			OBJ("div_MACClone").style.display	= "block";
		}
		
		if(this.feature_nosch==0)
		{
			OBJ("sch_sta").disabled		= val;
			OBJ("go2sch_sta").disabled	= val;
		}
		
		OBJ("ssid_sta").disabled			= val;
		//TLD suggests us to set 20/40 auto mode at APC. In order to simulate as wireless card.
		//OBJ("bw_sta").disabled			= val;
		OBJ("bw_sta").disabled				= true;
		OBJ("btn_site_survey_sta").disabled   = val;
		OBJ("security_type_sta").disabled	= val;
		///tomcat COMM_SetSelectValue(OBJ("security_type_sta"), this.sec_type_sta);
		this.OnChangeSecurityType("_sta");
	},
	
	OnClickEnWLAN: function(str_Aband)
	{
		if (AUTH.AuthorizedGroup >= 100) return;
		if (OBJ("en_wifi"+str_Aband).checked)
		{
			if(this.feature_nosch==0)
			{
				OBJ("sch"+str_Aband).disabled		= false;
				OBJ("go2sch"+str_Aband).disabled	= false;
			}
			
			OBJ("ssid"+str_Aband).disabled	= false;
			OBJ("auto_ch"+str_Aband).disabled	= false;
			if (!OBJ("auto_ch"+str_Aband).checked) OBJ("channel"+str_Aband).disabled = false;
			OBJ("txrate"+str_Aband).disabled	= false;
			OBJ("wlan_mode"+str_Aband).disabled	= false;
			if (/n/.test(OBJ("wlan_mode"+str_Aband).value))
			{
				//TLD suggests us to set 20/40 auto mode at APC. In order to simulate as wireless card.
				if(str_Aband != "_sta")
				{
					OBJ("bw"+str_Aband).disabled	= false;
					if(OBJ("channel"+str_Aband).value == "165")
					OBJ("bw"+str_Aband).disabled	= true;
				}
				OBJ("en_wmm"+str_Aband).disabled = true;
			}
			else
				OBJ("en_wmm"+str_Aband).disabled = false;
			if (OBJ("en_wps").checked)
			{
				OBJ("ssid_invisible"+WLan_BAND).checked = false;
				OBJ("ssid_visible"+WLan_BAND).checked = true;
				OBJ("ssid_visible"+WLan_BAND).disabled = OBJ("ssid_invisible"+WLan_BAND).disabled = true;	
			}
			else
				OBJ("ssid_visible"+str_Aband).disabled= OBJ("ssid_invisible"+str_Aband).disabled= false;
			OBJ("security_type"+str_Aband).disabled= false;
			
			if(str_Aband == "")
				COMM_SetSelectValue(OBJ("security_type"+str_Aband), this.sec_type);
			else
				COMM_SetSelectValue(OBJ("security_type"+str_Aband), this.sec_type_Aband);
			this.OnChangeSecurityType(str_Aband);
			
			///tomcat if(OBJ("psk_eap"+str_Aband).value != "EAP")
			///tomcat 	OBJ("div_WPS").style.display = "block";
			///tomcat else
			///tomcat 	OBJ("div_WPS").style.display = "none";
		}
		else
		{
			if(this.feature_nosch==0)
			{
				OBJ("sch"+str_Aband).disabled		= true;
				OBJ("go2sch"+str_Aband).disabled	= true;
			}
			
			OBJ("ssid"+str_Aband).disabled	= true;
			OBJ("auto_ch"+str_Aband).disabled	= true;
			OBJ("channel"+str_Aband).disabled	= true;
			OBJ("txrate"+str_Aband).disabled	= true;
			OBJ("wlan_mode"+str_Aband).disabled	= true;
			OBJ("bw"+str_Aband).disabled	= true;
			OBJ("en_wmm"+str_Aband).disabled = true;
			OBJ("ssid_visible"+str_Aband).disabled = OBJ("ssid_invisible"+str_Aband).disabled= true;
			OBJ("security_type"+str_Aband).disabled = true;
			
			if(str_Aband == "")
				this.sec_type = OBJ("security_type"+str_Aband).value;
			else 
				this.sec_type_Aband = OBJ("security_type"+str_Aband).value;

			COMM_SetSelectValue(OBJ("security_type"+str_Aband), "");
			this.OnChangeSecurityType(str_Aband);
			
			OBJ("div_WPS").style.display = "none";
		}
	},
	OnClickAdvanced:function()
	{
		
	},
	OnClickEnAutoChannel: function(str_Aband)
	{
		if (OBJ("auto_ch"+str_Aband).checked || !OBJ("en_wifi"+str_Aband).checked)
			OBJ("channel"+str_Aband).disabled = true;
		else
			OBJ("channel"+str_Aband).disabled = false;
	},
	
	OnClickChangeChannel: function(str_Aband)
	{
		if (OBJ("channel"+str_Aband).value == "165" || OBJ("channel"+str_Aband).value == "12" || OBJ("channel"+str_Aband).value == "13" || OBJ("channel"+str_Aband).value == "140")
				{			
				OBJ("bw"+str_Aband).value = "20";				
				OBJ("bw"+str_Aband).disabled = true;				
				}	
		else{
				OBJ("bw"+str_Aband).disabled = false;
				
				if( OBJ("security_type"+str_Aband).value == "WPA_P" || OBJ("security_type"+str_Aband).value == "WPA_E" )
				{
					if( OBJ("cipher_type"+str_Aband).value == "TKIP" )
						OBJ("bw"+str_Aband).disabled = true;
				}
				
				if( OBJ("security_type"+str_Aband).value == "WEP")
				{					
						OBJ("bw"+str_Aband).disabled = true;
				}
				
				
			}	
	},
	
	OnChangeSecurityType: function(postfix)
	{
		if(OBJ("en_wifi"+postfix).checked)
		{
			//TLD suggests us to set 20/40 auto mode at APC. In order to simulate as wireless card.
			//OBJ("bw"+postfix).disabled = false;
			if(postfix != "_sta")
				OBJ("bw"+postfix).disabled = false;
		}
		
		switch (OBJ("security_type"+postfix).value)
		{
			case "":
				OBJ("wep"+postfix).style.display = "none";
				OBJ("wpa"+postfix).style.display = "none";
				OBJ("pre_shared_key"+postfix).style.display = "none";
                OBJ("eap802.1x"+postfix).style.display = "none";
				break;
			case "WEP":
				OBJ("wep"+postfix).style.display = "block";
				OBJ("wpa"+postfix).style.display = "none";
				OBJ("pre_shared_key"+postfix).style.display = "none";
				OBJ("eap802.1x"+postfix).style.display = "none";
				//TLD suggests us to set 20/40 auto mode at APC. In order to simulate as wireless card.
				if(postfix != "_sta")
				{
					OBJ("bw"+postfix).value = "20";
					OBJ("bw"+postfix).disabled = true;
				}
				break;
			case "WPA_P":
				OBJ("wep"+postfix).style.display = "none";
				OBJ("wpa"+postfix).style.display = "block";
				OBJ("pre_shared_key"+postfix).style.display = "block";
				OBJ("eap802.1x"+postfix).style.display = "none";
				PAGE.OnChangeCipher(postfix);	
				break;
			case "WPA_E":
				OBJ("wep"+postfix).style.display = "none";
				OBJ("wpa"+postfix).style.display = "block";
				OBJ("eap802.1x"+postfix).style.display = "block";
				OBJ("pre_shared_key"+postfix).style.display = "none";
				PAGE.OnChangeCipher(postfix);	
				break;
		}
		var freq = XG(this.phyinf+"/media/freq");		
		var str_Aband = "";
		if(freq == "5") 
		{
			str_Aband = "_Aband"; 
			WLan_BAND="_Aband";
		}
		else
		{
			str_Aband = "";
			WLan_BAND="";
		}
		this.OnClickChangeChannel(str_Aband);
	},
	OnChangeWEPKey: function(postfix)
	{
		var no = 0;//S2I(OBJ("wep_def_key"+postfix).value) - 1;
				
		switch (OBJ("wep_key_len"+postfix).value)
		{
		
			case "64":			
				OBJ("wep_64"+postfix).style.display = "block";
				OBJ("wep_128"+postfix).style.display = "none";
				//SetDisplayStyle(null, "wepkey_64"+postfix, "none");
				//document.getElementsByName("wepkey_64"+postfix)[no].style.display = "inline";
				break;
			case "128":
				OBJ("wep_64"+postfix).style.display = "none";
				OBJ("wep_128"+postfix).style.display = "block";
				//SetDisplayStyle(null, "wepkey_128"+postfix, "none");
				//document.getElementsByName("wepkey_128"+postfix)[no].style.display = "inline";			
				break;
		}
	},
	OnChangeWPAAuth: function(postfix)
	{
		switch (OBJ("psk_eap"+postfix).value)
		{
			case "PSK":
				SetDisplayStyle("div", "psk"+postfix, "block");
				SetDisplayStyle("div", "eap"+postfix, "none");
				OBJ("div_WPS").style.display = "block";
				break;
			case "EAP":
				SetDisplayStyle("div", "psk"+postfix, "none");
				SetDisplayStyle("div", "eap"+postfix, "block");
				if(postfix != "_sta" && OBJ("en_wps").checked)
				{
					OBJ("en_wps").checked = COMM_ToBOOL("0");
					this.OnClickEnWPS();
				}
				OBJ("div_WPS").style.display = "none";
				break;
		}
	},
	OnChangeCipher: function(postfix)
	{
		//TLD suggests us to set 20/40 auto mode at APC. In order to simulate as wireless card.
		if(postfix == "_sta") return;
		
		switch (OBJ("cipher_type"+postfix).value)
		{
			case "TKIP":
				OBJ("bw"+postfix).value = "20";
				OBJ("bw"+postfix).disabled = true;
				break;
			default:
				OBJ("bw"+postfix).disabled = false;
				break;
		}
	},
	OnChangeWLMode: function(postfix)
	{	
		var phywlan = "";
		phywlan  = PXML.FindModule("WIFI.PHYINF"); 
		
		//we share same db node for 2.4G and 5G now
		phywlan = GPBT(phywlan, "phyinf", "uid","WLAN-1.1", false);
		
		if (/n/.test(OBJ("wlan_mode"+postfix).value))
		{
			//TLD suggests us to set 20/40 auto mode at APC. In order to simulate as wireless card.
			if(postfix != "_sta")
			{
				if(postfix==="") this.bandWidth = XG(phywlan+"/media/dot11n/bandwidth");
				else this.bandWidth = XG(phywlan+"/media/dot11n/bandwidth"+postfix);
				COMM_SetSelectValue(OBJ("bw"+postfix), this.bandWidth);
				OBJ("bw"+postfix).disabled	= false;
			}
			
			OBJ("en_wmm"+postfix).checked = true;
			OBJ("en_wmm"+postfix).disabled = true;
		}
		else
		{
			//TLD suggests us to set 20/40 auto mode at APC. In order to simulate as wireless card.
			if(postfix != "_sta")
			{
				OBJ("bw"+postfix).disabled	= true;
			}
			OBJ("en_wmm"+postfix).disabled = false;
		}
		this.OnChangeBW(postfix);
		DrawSecurityList(OBJ("wlan_mode"+postfix).value, postfix);
		this.OnChangeSecurityType(postfix);
	},
	OnChangeBW: function(postfix)
	{
		if(postfix != "_sta")
		{
			var phywlan = "";
			phywlan  = PXML.FindModule("WIFI.PHYINF"); 
			phywlan = GPBT(phywlan, "phyinf", "uid","WLAN-1.1", false);
			this.shortGuard = XG(phywlan+"/media/dot11n/guardinterval");
			DrawTxRateList(OBJ("bw"+postfix).value, this.shortGuard, postfix);
			if (OBJ("wlan_mode"+postfix).value === "n")
			{
				var rate = XG(phywlan+"/media/dot11n/mcs/index");
				if (rate=="") rate = "-1";
				COMM_SetSelectValue(OBJ("txrate"+postfix), rate);
			}
		}	
	},
	OnClickSiteSurvey: function()
	{
		var	options="toolbar=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=600";
		window.open("bsc_wlan_sitesurvey.php","bsc_wlan_sitesurvey",options);
	},
	OnClickResetCfg: function()
	{
		if (confirm("<?echo i18n("Are you sure you want to reset the device to Unconfigured?")."\\n";?>"))
		{
			OBJ("mainform").setAttribute("modified", "true");
			XS(this.wifip+"/wps/configured","0"		);
			this.do_reset_wps = 1;
			BODY.OnSubmit();
		}
	},
	curpin: null,
	do_reset_wps: 0,
	defpin: "<? echo query("/runtime/devdata/pin");?>",
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
	},
	OnClickEnMacClone: function()
	{	
		if(OBJ("en_mac_clone").checked)
		{
			OBJ("mac_source_type").disabled = false;
			this.OnChangeMacSourceType();
		}
		else
		{
			OBJ("mac_source_type").disabled = OBJ("ScanMACTable").disabled = OBJ("mac_clone_scan").disabled = true;
			for(var i=1; i<=6; i++) OBJ("mac"+i).disabled = true;
		}
	},
	OnChangeMacSourceType: function()
	{	
		if(OBJ("mac_source_type").value === "MANUAL")
		{
			OBJ("mac_clone_scan").disabled = OBJ("ScanMACTable").disabled = false;
			for(var i=1; i<=6; i++) OBJ("mac"+i).disabled = false;
		}
		else
		{
			OBJ("mac_clone_scan").disabled = OBJ("ScanMACTable").disabled = true;
			for(var i=1; i<=6; i++) OBJ("mac"+i).disabled = true;
		}
	},
	GetMAC: function(m)
	{
		var myMAC=new Array();
		if (m.search(":") != -1)	var tmp=m.split(":");
		else	var tmp=m.split("-");
		for (var i=0;i <= 6;i++)
		myMAC[i]="";
		if (m != "")
		{
			for (var i=1;i <= tmp.length;i++)
			myMAC[i]=tmp[i-1];
			myMAC[0]=m;
		}
		return myMAC;
	},
	OnScanMAC: function()
	{
		OBJ("mac_clone_scan").value = "<? echo i18n("Scanning");?>";
		OBJ("mac_clone_scan").disabled = true;
		
		var ajaxObj = GetAjaxObj("SHOWMAC");
		var action = "SHOWMAC";
		ajaxObj.createRequest();
		ajaxObj.onCallback = function (xml)
		{
			ajaxObj.release();
			PAGE.OnScanMACCallback(xml.Get("/showmacreport/result"), xml.Get("/showmacreport/reason"));
		}
		ajaxObj.setHeader("Content-Type", "application/x-www-form-urlencoded");
		ajaxObj.sendRequest("showmac.php", "action="+action);
		AUTH.UpdateTimeout();	
	},
	OnScanMACCallback: function (code, result)
	{
		switch (code)
		{
			case "OK":
				this.ScanMAC_num = 5;
				setTimeout('PAGE.GetScanMAC()',2000);
				break;
			default:
				BODY.ShowAlert(result);
				OBJ("mac_clone_scan").value = "<? echo i18n("Scan");?>";
				OBJ("mac_clone_scan").disabled = false;
				break;
		}
		return true;
	},
	GetScanMAC: function()
	{
		COMM_GetCFG(false, "RUNTIME.SHOWMAC", function(xml) {PAGE.GetScanMACCallback(xml);});	
	},
	GetScanMACCallback: function(xml)
	{
		/*Show site survey table*/
		var ScanMACP = "/postxml/module/runtime/phyinf_tmpnode/showmac/entry";
		var ScanMACstatus = "/postxml/module/runtime/phyinf_tmpnode/showmac/status";
		var ScanMAC_cnt = xml.Get(ScanMACP+"#");
		BODY.CleanTable("ScanMACTable");
		if(xml.Get(ScanMACstatus)==="finish")
		{
			var j=1;
			for (var i=1; i<=ScanMAC_cnt; i++)
			{	
				ScanMACPath = ScanMACP+":"+i;
				var MACport = xml.Get(ScanMACPath+"/port");
				MACport = parseInt(MACport);
				if(MACport < 1 || MACport > 4)	continue;
				
				/* just to match port num of dap1522b's physical case, sam_pan*/
				switch(MACport)
				{
					case 1:	
					        MACport = 4;
							break;
					case 2:
							MACport = 3;
							break;
					case 3:
							MACport = 2;
							break;
					case 4:	
							MACport = 1;
							break;
				}	
				
				var MACclone_radio = '<input id="Scan_MAC_'+j+'" type="radio" onClick="PAGE.OnChangeScanMACRadio('+j+');" />';
				this.MACaddr[j] = xml.Get(ScanMACPath+"/macaddr");
	
				var data = [MACclone_radio, MACport, this.MACaddr[j]];	
				var type = ["","text","text"];
				BODY.InjectTable("ScanMACTable", "SM_table_"+j, data, type);
				j++;
			}
			this.ShowMAC_cnt=--j;
			OBJ("mac_clone_scan").value = "<? echo i18n("Scan");?>";
			OBJ("mac_clone_scan").disabled = false;
		}
		else if(this.ScanMAC_num > 0)
		{
			setTimeout('PAGE.GetScanMAC()',2000);
			this.ScanMAC_num--;
		}
		else if(this.ScanMAC_num <= 0)
		{
			OBJ("mac_clone_scan").value = "<? echo i18n("Scan");?>";
			OBJ("mac_clone_scan").disabled = false;
		}		
		
	},
	OnChangeScanMACRadio: function(i)
	{
		for (var j=1; j<=this.ShowMAC_cnt; j++)	if(j!==i) OBJ("Scan_MAC_"+j).checked = false;
		var mac = this.GetMAC(this.MACaddr[i]);
		for (var j=1; j<=6; j++)
		{
			if (mac[j].length === "1") mac[j] = "0"+mac[j];	
			OBJ("mac"+j).value = mac[j].toUpperCase();
		}	
	}	
}

function SetBNode(value)
{
	if (COMM_ToBOOL(value))
		return "1";
	else
		return "0";
}

function SetDisplayStyle(tag, name, style)
{
	if (tag)	var obj = GetElementsByName_iefix(tag, name);
	else		var obj = document.getElementsByName(name);
	for (var i=0; i<obj.length; i++)
	{
		obj[i].style.display = style;
	}
}
function GetElementsByName_iefix(tag, name)
{
	var elem = document.getElementsByTagName(tag);
	var arr = new Array();
	for(i = 0,iarr = 0; i < elem.length; i++)
	{
		att = elem[i].getAttribute("name");
		if(att == name)
		{
			arr[iarr] = elem[i];
			iarr++;
		}
	}
	return arr;
}

function DrawTxRateList(bw, sgi, str_Aband)
{
	var listOptions = null;
	var cond = bw+":"+sgi;
	
	switch(cond)
	{
	case "20:800":
		listOptions = new Array("0 - 6.5","1 - 13.0","2 - 19.5","3 - 26.0","4 - 39.0","5 - 52.0","6 - 58.5","7 - 65.0"<?
						$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", "WLAN-1.1", 0);
						$ms = query($p."/media/multistream");
						if ($ms != "1T1R")
							echo ',"8 - 13.0","9 - 26.0","10 - 39.0","11 - 52.0","12 - 78.0","13 - 104.0","14 - 117.0","15 - 130.0"';
						?>);
		break;
	case "20:400":
		listOptions = new Array("0 - 7.2","1 - 14.4","2 - 21.7","3 - 28.9","4 - 43.3","5 - 57.8","6 - 65.0","7 - 72.0"<?
						if ($ms != "1T1R")
							echo ',"8 - 14.444","9 - 28.889","10 - 43.333","11 - 57.778","12 - 86.667","13 - 115.556","14 - 130.000","15 - 144.444"';
						?>);
		break;
	case "20+40:800":
		listOptions = new Array("0 - 13.5","1 - 27.0","2 - 40.5","3 - 54.0","4 - 81.0","5 - 108.0","6 - 121.5","7 - 135.0"<?
						if ($ms != "1T1R")
							echo ',"8 - 27.0","9 - 54.0","10 - 81.0","11 - 108.0","12 - 162.0","13 - 216.0","14 - 243.0","15 - 270.0"';
						?>);
		break;
	case "20+40:400":
		listOptions = new Array("0 - 15.0","1 - 30.0","2 - 45.0","3 - 60.0","4 - 90.0","5 - 120.0","6 - 135.0","7 - 150.0"<?
						if ($ms != "1T1R")
							echo ',"8 - 30.0","9 - 60.0","10 - 90.0","11 - 120.0","12 - 180.0","13 - 240.0","14 - 270.0","15 - 300.0"';
						?>);
		break;
	}

	var tr_length = OBJ("txrate"+str_Aband).length;
	for(var idx=1; idx<tr_length; idx++)
	{
		OBJ("txrate"+str_Aband).remove(1);
	}
	if (OBJ("wlan_mode"+str_Aband).value === "n")
	{
		for(var idx=0; idx<listOptions.length; idx++)
		{
			var item = document.createElement("option");
			item.value = idx;
			item.text = listOptions[idx];
			try		{ OBJ("txrate"+str_Aband).add(item, null); }
			catch(e){ OBJ("txrate"+str_Aband).add(item); }
		}
	}	
}
	
function DrawSecurityList(wlan_mode, str_Aband)
{
	var security_list = null;
	var cipher_list = null;
	if (wlan_mode === "n")
	{
		security_list = ['WPA_P', '<?echo I18N("j", "WPA-Personal");?>',
						 'WPA_E', '<?echo I18N("j", "WPA-Enterprise");?>'];
		cipher_list = ['AES'];
	}
	else
	{
		security_list = ['WEP', '<?echo I18N("j", "WEP");?>',
						 'WPA_P', '<?echo I18N("j", "WPA-Personal");?>',
						 'WPA_E', '<?echo I18N("j", "WPA-Enterprise");?>'];
		cipher_list = ['TKIP+AES','TKIP','AES'];
	}
	//modify security_type
	var sec_length = OBJ("security_type"+str_Aband).length;
	for(var idx=1; idx<sec_length; idx++)
	{
		OBJ("security_type"+str_Aband).remove(1);
	}
	for(var idx=0; idx<security_list.length; idx++)
	{
		var item = document.createElement("option");
		item.value = security_list[idx++];
		item.text = security_list[idx];
		try		{ OBJ("security_type"+str_Aband).add(item, null); }
		catch(e){ OBJ("security_type"+str_Aband).add(item); }
	}
	// modify cipher_type
	var ci_length = OBJ("cipher_type"+str_Aband).length;
	for(var idx=0; idx<ci_length; idx++)
	{
		OBJ("cipher_type"+str_Aband).remove(0);
	}
	for(var idx=0; idx<cipher_list.length; idx++)
	{
		var item = document.createElement("option");
		item.value = cipher_list[idx];
		if (item.value=="TKIP+AES") item.text = "<?echo i18n("TKIP and AES");?>";
		else						item.text = cipher_list[idx];
		try		{ OBJ("cipher_type"+str_Aband).add(item, null); }
		catch(e){ OBJ("cipher_type"+str_Aband).add(item); }
	}
}
</script>
