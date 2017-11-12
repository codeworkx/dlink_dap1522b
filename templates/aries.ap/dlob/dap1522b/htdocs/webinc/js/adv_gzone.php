<script type="text/javascript">
function Page() {}
var WLan_BAND=null;
Page.prototype =
{
	services: "WIFI.PHYINF, PHYINF.WIFI, FIREWALL-2",		
	OnLoad: function() {},
	OnUnload: function() {},
	OnSubmitCallback: function (code, result) 
	{ 
		BODY.TurnReboot(code, result);
		return true; 
	},
	feature_nosch: null,
	
	InitValue: function(xml)
	{
		PXML.doc = xml;
		this.wifi_module = PXML.FindModule("WIFI.PHYINF");		
		this.gz_fw2_module = PXML.FindModule("FIREWALL-2");			//enable/disable routing zones 

		var rebootflag = "<?echo query("/runtime/reboot/status");?>";
		if(rebootflag != "1"){OBJ("rebootitem1").style.display="none"; OBJ("rebootitem2").style.display="none";}
		else {OBJ("rebootitem1").style.display=""; OBJ("rebootitem2").style.display="";}

		<? if($FEATURE_NOSCH!="1")echo 'this.feature_nosch=0;'; else echo 'this.feature_nosch=1;'; ?>
		
		if(!this.Initial("WLAN-1.2")) return false; 
	},
	
	Initial: function(uid_wlan)
	{
		var phyinf 	= GPBT(this.wifi_module	,"phyinf", "uid",uid_wlan, false);
		var wifip 	= GPBT(this.wifi_module+"/wifi"	,"entry", "uid" ,XG(phyinf+"/wifi"), false); 
		if (!wifip)
		{
			BODY.ShowAlert("Initial() ERROR!!!");
			return false;
		}
		
		var freq = XG(phyinf+"/media/freq");
		var str_Aband = (freq=="5")	? "_Aband" : "" ;
		
		if(str_Aband == "")
		{		
			OBJ("div_gz_24G_band").style.display = "block";	
			OBJ("div_gz_5G_band").style.display = "none";	
			WLan_BAND="";
		}
		else 					
		{	
			OBJ("div_gz_24G_band").style.display = "none";	
			OBJ("div_gz_5G_band").style.display = "block";	
			WLan_BAND="_Aband";
		}
		
		if(   XG("/postxml/module:1/phyinf:1//media/wlmode"+str_Aband) == "n")
		{		
			OBJ("security_type"+str_Aband).options.remove(1);
			OBJ("cipher_type"+str_Aband).options.remove(0);
			OBJ("cipher_type"+str_Aband).options.remove(0);			
		}
		
		var str_sec_type = "";
		if (XG(wifip+"/encrtype")=="WEP")
			str_sec_type = "WEP";
		else if (/WPA/.test(XG(wifip+"/authtype")))
			str_sec_type = "WPA";
		COMM_SetSelectValue(OBJ("security_type"+str_Aband), str_sec_type);
	
		///////////////// initial WEP /////////////////
		var auth = XG(wifip+"/authtype");
		var len = (XG(wifip+"/nwkey/wep/size")=="")? "64" : XG(wifip+"/nwkey/wep/size");
		var defkey = (XG(wifip+"/nwkey/wep/defkey")=="")? "1" : XG(wifip+"/nwkey/wep/defkey");
		this.wps = COMM_ToBOOL(XG(wifip+"/wps/enable"));
		OBJ("auth_type"+str_Aband).disabled = this.wps;
		if (auth!="SHARED") auth = "BOTH";
		COMM_SetSelectValue(OBJ("auth_type"+str_Aband)	,	auth);
		COMM_SetSelectValue(OBJ("wep_key_len"+str_Aband),	len);
		COMM_SetSelectValue(OBJ("wep_def_key"+str_Aband),	defkey);
		for (var i=1; i<5; i++)
			OBJ("wep_"+len+"_"+i+str_Aband).value = XG(wifip+"/nwkey/wep/key:"+i);
		
		
		///////////////// initial WPA /////////////////
		var cipher = XG(wifip+"/encrtype");
		var type = XG(wifip+"/authtype");		
		switch (XG(wifip+"/authtype"))
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
		switch (XG(wifip+"/authtype"))
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
		COMM_SetSelectValue(OBJ("cipher_type"+str_Aband), cipher);
		
		COMM_SetSelectValue(OBJ("security_type"+str_Aband)	, type);
		COMM_SetSelectValue(OBJ("wpa_mode"+str_Aband), wpamode);
		
		OBJ("wpapsk"+str_Aband).value	= XG(wifip+"/nwkey/psk/key");
		if(XG(wifip+"/nwkey/rekey/gtk") == "")
			OBJ("wpa_rekey"+str_Aband).value	= 1800;
		else
			OBJ("wpa_rekey"+str_Aband).value	= XG(wifip+"/nwkey/rekey/gtk");
		OBJ("srv_ip"+str_Aband).value	= XG(wifip+"/nwkey/eap/radius");
		OBJ("srv_port"+str_Aband).value	= XG(wifip+"/nwkey/eap/port");
		OBJ("srv_sec"+str_Aband).value	= XG(wifip+"/nwkey/eap/secret");
		
		OBJ("en_gzone"+str_Aband).checked 		= COMM_ToBOOL(XG(phyinf+"/active"));
		OBJ("ssid"+str_Aband).value 			= XG(wifip+"/ssid");
		<? if($FEATURE_NOSCH!="1")echo 'COMM_SetSelectValue(OBJ("sch_gz"+str_Aband), XG(phyinf+"/schedule"));\n'; ?>			
		/* for routing zone */
		/* Note : 
		 *  rule FWL-1 -> Enable/Disable routing between zones
		 *  rule FWL-2 -> Enable/Disable guest zone clients isolation
		 */	
		var fw_uid1 = "FWL-1";
		var drop_rule1 = GPBT(this.gz_fw2_module+"/acl/firewall2","entry","uid",fw_uid1,false);		
		OBJ("en_routing"+str_Aband).checked = COMM_ToBOOL(XG(drop_rule1+"/enable"));
		
		var fw_uid2 = "FWL-2";
		var drop_rule2 = GPBT(this.gz_fw2_module+"/acl/firewall2","entry","uid",fw_uid2,false);		
		OBJ("en_isolation"+str_Aband).checked = COMM_ToBOOL(XG(drop_rule2+"/enable"));
		
				
		<? if($FEATURE_NOSCH!="1")echo 'this.feature_nosch=0;'; else echo 'this.feature_nosch=1;'; ?>

		this.OnClickEnGzone(str_Aband);
		this.OnChangeSecurityType(str_Aband);
		this.OnChangeWEPKey(str_Aband);
		this.OnChangeWPAAuth(str_Aband);
		return true;
	},
	PreSubmit: function()
	{
		if(!this.SaveXML("WLAN-1.2")) return null; 		
		PXML.CheckModule("FIREWALL-2", null, null, "ignore");
		PXML.CheckModule("WIFI.PHYINF", null, null, "ignore");
		var wep_key=OBJ("wep_def_key"+WLan_BAND).value;
		var wep_key_len=OBJ("wep_key_len"+WLan_BAND).value;
		
		if(OBJ("ssid"+WLan_BAND).value.charAt(0)===" "|| OBJ("ssid"+WLan_BAND).value.charAt(OBJ("ssid"+WLan_BAND).value.length-1)===" ")
		{
			alert("<?echo I18N("h", "The prefix or postfix of the 'Wireless Network Name' could not be blank.");?>");
			return null;
		}
		if((OBJ("security_type"+WLan_BAND).value==="WPA")  && OBJ("psk_eap"+WLan_BAND).value=="PSK")
		{
			if (OBJ("wpapsk"+WLan_BAND).value.charAt(0)===" " || OBJ("wpapsk"+WLan_BAND).value.charAt(OBJ("wpapsk"+WLan_BAND).value.length-1)===" ")
			{
				alert("<?echo I18N("h", "The prefix or postfix of the 'Network Key' could not be blank.");?>");
				return null;
			}
			
			if(OBJ("wpa_rekey"+WLan_BAND).value.charAt(0)===" " || OBJ("wpa_rekey"+WLan_BAND).value.length == 0)
			{
				alert("<?echo I18N("h", "The Group key update interval could not be blank.");?>");
				return null;
			}
		}

		if(OBJ("security_type"+WLan_BAND).value==="WEP") //wep_64_1_Aband
		{
				if (OBJ("wep_"+wep_key_len+"_"+wep_key+WLan_BAND).value.charAt(0) === " "|| OBJ("wep_"+wep_key_len+"_"+wep_key+WLan_BAND).value.charAt(OBJ("wep_"+wep_key_len+"_"+wep_key+WLan_BAND).value.length-1)===" ")
				{
					alert("<?echo I18N("h", "The prefix or postfix of the 'WEP Key' could not be blank.");?>");
					return null;
				}
			
		}	
		return PXML.doc;
	},
	IsDirty: null,
	Synchronize: function()
	{
	},
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////
		
	SaveXML: function(uid_wlan)
	{
		var phyinf = GPBT(this.wifi_module,"phyinf","uid",uid_wlan,false);
		var wifip = GPBT(this.wifi_module+"/wifi", "entry", "uid", XG(phyinf+"/wifi"), false);

		var freq = XG(phyinf+"/media/freq");
		var str_Aband = (freq=="5") ? "_Aband" : "" ;
		
		if (OBJ("en_gzone"+str_Aband).checked)
			XS(phyinf+"/active", "1");
		else
		{
			XS(phyinf+"/active", "0");
			return true;
		}
    <? if ($FEATURE_NOSCH!="1")echo 'XS(phyinf+"/schedule",    OBJ("sch_gz"+str_Aband).value);\n';?>

		XS(wifip+"/ssid", OBJ("ssid"+str_Aband).value);

		if (OBJ("security_type"+str_Aband).value=="WEP")
		{
			if (OBJ("auth_type"+str_Aband).value=="SHARED")
				XS(wifip+"/authtype", "SHARED");
			else
				XS(wifip+"/authtype", "BOTH");
			XS(wifip+"/encrtype",			"WEP");
			XS(wifip+"/nwkey/wep/size",	"");
			XS(wifip+"/nwkey/wep/ascii",	"");
			XS(wifip+"/nwkey/wep/defkey",	OBJ("wep_def_key"+str_Aband).value);
			for (var i=1, len=OBJ("wep_key_len"+str_Aband).value; i<5; i++)
			{
				if (i==OBJ("wep_def_key"+str_Aband).value)
					XS(wifip+"/nwkey/wep/key:"+i, OBJ("wep_"+len+"_"+i+str_Aband).value);
				else
					XS(wifip+"/nwkey/wep/key:"+i, "");
			}
		}else if (OBJ("security_type"+str_Aband).value=="WPA_P")
		{			
			XS(wifip+"/encrtype", OBJ("cipher_type"+str_Aband).value);
			if (OBJ("wpa_mode"+str_Aband).value=="WPA")
			{
				XS(wifip+"/authtype",				"WPAPSK");
				XS(wifip+"/nwkey/psk/passphrase",	"");
				XS(wifip+"/nwkey/psk/key",			OBJ("wpapsk"+str_Aband).value);
				//XS(wifip+"/nwkey/rekey/gtk",		OBJ("wpa_rekey"+str_Aband).value);
			}
			else if (OBJ("wpa_mode"+str_Aband).value=="WPA2")
			{
				XS(wifip+"/authtype",				"WPA2PSK");
				XS(wifip+"/nwkey/psk/passphrase",	"");
				XS(wifip+"/nwkey/psk/key",			OBJ("wpapsk"+str_Aband).value);
				//XS(wifip+"/nwkey/rekey/gtk",		OBJ("wpa_rekey"+str_Aband).value);
			}else  //wpa+wpa2 mode
			{
				XS(wifip+"/authtype",				"WPA+2PSK");
				XS(wifip+"/nwkey/psk/passphrase",	"");
				XS(wifip+"/nwkey/psk/key",			OBJ("wpapsk"+str_Aband).value);
				//XS(wifip+"/nwkey/rekey/gtk",		OBJ("wpa_rekey"+str_Aband).value);
			}
			
		}else if(OBJ("security_type"+str_Aband).value=="WPA_E")
		{
			
			XS(wifip+"/encrtype", OBJ("cipher_type"+str_Aband).value);
			if (OBJ("wpa_mode"+str_Aband).value=="WPA")
			{
				XS(wifip+"/authtype",				"WPA");				
			}
			else if (OBJ("wpa_mode"+str_Aband).value=="WPA2")
			{
				XS(wifip+"/authtype",				"WPA2");			
			}else  //wpa+wpa2 mode
			{
				XS(wifip+"/authtype",				"WPA+2");				
			}			
			XS(wifip+"/nwkey/eap/radius",	OBJ("srv_ip"+str_Aband).value);
			XS(wifip+"/nwkey/eap/port",	OBJ("srv_port"+str_Aband).value);
			XS(wifip+"/nwkey/eap/secret",	OBJ("srv_sec"+str_Aband).value);			
		}
		else
		{
			XS(wifip+"/authtype", "OPEN");
			XS(wifip+"/encrtype", "NONE");
		}
		
		/* for enable routing */
		
		var fw_uid1 = "FWL-1";
		var fw_uid2 = "FWL-2";
		
		var drop_rule1 = GPBT(this.gz_fw2_module+"/acl/firewall2","entry","uid",fw_uid1,false);		
		var drop_rule2 = GPBT(this.gz_fw2_module+"/acl/firewall2","entry","uid",fw_uid2,false);
		
		XS(drop_rule1+"/enable", OBJ("en_routing"+str_Aband).checked?"1":"0");		
		XS(drop_rule2+"/enable", OBJ("en_isolation"+str_Aband).checked?"1":"0");
		
		return true;
	},
	OnClickEnGzone: function(str_Aband)
	{
		if (OBJ("en_gzone"+str_Aband).checked)
		{
			OBJ("ssid"+str_Aband).disabled			= false;
			OBJ("security_type"+str_Aband).disabled	= false;
			if(this.feature_nosch==0)
			{
				OBJ("sch_gz"+str_Aband).disabled		= false;
				OBJ("go2sch_gz"+str_Aband).disabled	= false;
			}
		}
		else
		{
			OBJ("ssid"+str_Aband).disabled			= true;
			OBJ("security_type"+str_Aband).disabled	= true;
			if(this.feature_nosch==0)
			{
				OBJ("sch_gz"+str_Aband).disabled	= true;
				OBJ("go2sch_gz"+str_Aband).disabled	= true;
			}
		}
		//for enable routing zones between hostzone and guestzone		
		if(OBJ("en_gzone"+str_Aband).checked)
		{			
			OBJ("en_isolation"+str_Aband).disabled = false;
			OBJ("en_routing"+str_Aband).disabled	= false;
		}	
		else
		{
			OBJ("en_isolation"+str_Aband).disabled = true;
			OBJ("en_routing"+str_Aband).disabled	= true;		
			var str_sec_type = "";
			var str_sec_type_Aband="";
				if(str_Aband == "")
					COMM_SetSelectValue(OBJ("security_type"+str_Aband), str_sec_type);
				else
					COMM_SetSelectValue(OBJ("security_type"+str_Aband), str_sec_type_Aband);
			this.OnChangeSecurityType(str_Aband);
		}	
		
	},
	OnChangeSecurityType: function(str_Aband)
	{
		switch (OBJ("security_type"+str_Aband).value)
		{
			case "":
				OBJ("wep"+str_Aband).style.display = "none";
				OBJ("wpa"+str_Aband).style.display = "none";
				//OBJ("pad").style.display = "block";
				break;
			case "WEP":
				OBJ("wep"+str_Aband).style.display = "block";
				OBJ("wpa"+str_Aband).style.display = "none";
				//OBJ("pad").style.display = "none";
				break;
			case "WPA_P":			
				OBJ("wep"+str_Aband).style.display = "none";
				OBJ("wpa"+str_Aband).style.display = "block";				
				SetDisplayStyle("div", "psk"+str_Aband, "block");
				
				SetDisplayStyle("div", "eap"+str_Aband, "none");
				//OBJ("pad").style.display = "none";
				break;
			case "WPA_E":			
				OBJ("wep"+str_Aband).style.display = "none";
				OBJ("wpa"+str_Aband).style.display = "block";
				
				SetDisplayStyle("div", "psk"+str_Aband, "none");
				SetDisplayStyle("div", "eap"+str_Aband, "block");
				
				break;				
		}
	},
	OnChangeWEPKey: function(str_Aband)
	{
		var no = S2I(OBJ("wep_def_key"+str_Aband).value) - 1;
		
		switch (OBJ("wep_key_len"+str_Aband).value)
		{
			case "64":
				OBJ("wep_64"+str_Aband).style.display = "block";
				OBJ("wep_128"+str_Aband).style.display = "none";
				SetDisplayStyle(null, "wepkey_64"+str_Aband, "none");
				document.getElementsByName("wepkey_64"+str_Aband)[no].style.display = "inline";
				break;
			case "128":
				OBJ("wep_64"+str_Aband).style.display = "none";
				OBJ("wep_128"+str_Aband).style.display = "block";
				SetDisplayStyle(null, "wepkey_128"+str_Aband, "none");
				document.getElementsByName("wepkey_128"+str_Aband)[no].style.display = "inline";
		}
	},
	OnChangeWPAAuth: function(str_Aband)
	{
		switch (OBJ("security_type"+str_Aband).value)
		{
			case "WPA_P":
				SetDisplayStyle("div", "psk"+str_Aband, "block");			
				SetDisplayStyle("div", "eap"+str_Aband, "none");
				break;
			case "WPA_E":
				SetDisplayStyle("div", "psk"+str_Aband, "none");
				SetDisplayStyle("div", "eap"+str_Aband, "block");
		}
	}
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
	
</script>
