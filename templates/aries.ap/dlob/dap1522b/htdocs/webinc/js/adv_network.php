<?
include "/htdocs/phplib/phyinf.php";
$switch_mode = query("/runtime/device/switchmode");
if($switch_mode=="APCLI")
	$wlan_uid = "WLAN-2";
else 
	$wlan_uid = "WLAN-1.1";
?>
<script type="text/javascript">
function Page() {}
Page.prototype =
{
	services: "WIFI.PHYINF, PHYINF.WIFI, MULTICAST",		
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
		var wlan_uid = '<?=$wlan_uid?>';		
		this.wifip 			= PXML.FindModule("WIFI.PHYINF");
		this.runtime_phyinf = PXML.FindModule("RUNTIME.PHYINF");		
		this.mcast = PXML.FindModule("MULTICAST");				
					
		if (this.wifip==="") { alert("InitValue ERROR!"); return false; }
				
		this.phyinf 		= GPBT(this.wifip, "phyinf", "uid",wlan_uid, false);
		var wifi_profile 	= XG(this.phyinf+"/wifi");
		this.wifimode 			= GPBT(this.wifip+"/wifi", "entry", "uid", wifi_profile, false);
		this.opmode		 	= XG(this.wifimode+"/opmode");
		
		this.wifip = GPBT(this.wifip,"phyinf","uid",wlan_uid,false);
		
		/*transmit power*/
		COMM_SetSelectValue(OBJ("tx_power"), XG(this.wifip+"/media/txpower"));
		if(this.opmode!=="STA")
		{
			OBJ("wmm_none").style.display = "block";
			OBJ("shortgi_none").style.display = "block";
			//OBJ("dtim_none").style.display = "block";
			//OBJ("rtsthresh_none").style.display = "block";
			//OBJ("fragthresh_none").style.display = "block";
			OBJ("enhance_none").style.display = "block";
			var freq = XG(this.phyinf+"/media/freq");
			var str_Aband = "";
			if(freq == "5") 
			{
				str_Aband = "_Aband"; 
			}
			
			/*wmm enable*/
			if(XG(this.wifip+"/media/wmm/enable")==="1") OBJ("wmm").checked = true;
			else OBJ("wmm").checked = false;	
			/*11n only will enable wmm*/
//			if(XG(this.phyinf+"/media/wlmode"+str_Aband)  == "n" || XG(this.phyinf+"/media/wlmode"+str_Aband)  == "bgn") { OBJ("wmm").disabled = true;}				
			OBJ("wmm").disabled = true;
			/*shortgi enable*/										
			if(XG(this.wifip+"/media/dot11n/guardinterval")==="400") OBJ("shortgi").checked = true;
			else if(XG(this.wifip+"/media/dot11n/guardinterval")==="800") OBJ("shortgi").checked = false;
			/*dtim*/			
			OBJ("dtim").value = XG(this.wifip+"/media/dtim");							
			/*thresh*/
			OBJ("rtsthresh").value = XG(this.wifip+"/media/rtsthresh");
			OBJ("fragthresh").value = XG(this.wifip+"/media/fragthresh");
			/*igmp snooping*/						
			if (this.mcast==="") { alert("InitValue ERROR!"); return false; }										
			OBJ("enhance").checked = (XG(this.mcast+"/device/multicast/igmpsnooping")==="1");		
			/*wlan partition*/
			COMM_SetSelectValue(OBJ("isc"), XG(this.wifip+"/media/w_partition"));
			COMM_SetSelectValue(OBJ("ewa"), XG(this.wifip+"/media/e_partition"));	
			/*ht20/40*/
			OBJ("ht_en").checked = (XG(this.wifip+"/media/coexistence/enable") === "1")? true : false;
			OBJ("ht_dis").checked = (XG(this.wifip+"/media/coexistence/enable") !== "1")? true : false;
		}
		else
		{
			OBJ("wmm_none").style.display = "none";
			OBJ("shortgi_none").style.display = "none";
			OBJ("dtim_none").style.display = "none";
			OBJ("rtsthresh_none").style.display = "none";
			OBJ("fragthresh_none").style.display = "none";
			OBJ("enhance_none").style.display = "none";
			OBJ("ewa_none").style.display = "none";
			OBJ("isc_none").style.display = "none";
			OBJ("ht_none").style.display = "none";
		}
		
		var rebootflag = "<?echo query("/runtime/reboot/status");?>";
		if(rebootflag != "1"){OBJ("rebootitem1").style.display="none"; OBJ("rebootitem2").style.display="none";}
		else {OBJ("rebootitem1").style.display=""; OBJ("rebootitem2").style.display="";}

		return true;
	},
	PreSubmit: function()
	{			
		/*transmit power*/
		XS(this.wifip+"/media/txpower", OBJ("tx_power").value);
		if(this.opmode!=="STA")
		{
		/*wmm enable*/
		XS(this.wifip+"/media/wmm/enable", OBJ("wmm").checked ? "1":"0");	
		/*shortgi enable*/
		XS(this.wifip+"/media/dot11n/guardinterval", OBJ("shortgi").checked ? "400":"800");
		/*dtim*/
		XS(this.wifip+"/media/dtim", OBJ("dtim").value);
		/*thresh*/
		XS(this.wifip+"/media/rtsthresh", OBJ("rtsthresh").value);
		XS(this.wifip+"/media/fragthresh", OBJ("fragthresh").value);
		/*igmp snooping*/					
		XS(this.mcast+"/device/multicast/igmpsnooping", OBJ("enhance").checked ? "1":"0");
		XS(this.wifip+"/media/w_partition", OBJ("isc").value);
		XS(this.wifip+"/media/e_partition", OBJ("ewa").value);
		XS(this.wifip+"/media/coexistence/enable", (OBJ("ht_en").checked == true) ? "1" : "0");
		}
		
		return PXML.doc;
	},
	IsDirty: null,
	Synchronize: function() {},
	mcast: null,
	wifip: null,
	rphyinf: null,
	phyinf: null,
	runtime_phyinf: null
}
</script>
