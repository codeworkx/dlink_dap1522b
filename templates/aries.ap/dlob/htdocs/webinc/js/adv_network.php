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
	OnSubmitCallback: function () {},
	InitValue: function(xml)
	{				
		PXML.doc = xml;		
		var wlan_uid = '<?=$wlan_uid?>';		
		this.wifip 			= PXML.FindModule("WIFI.PHYINF");
		this.runtime_phyinf = PXML.FindModule("RUNTIME.PHYINF");		
		this.mcast = PXML.FindModule("MULTICAST");				
					
		if (this.wifip==="") { alert("InitValue ERROR!"); return false; }
		this.wifip = GPBT(this.wifip,"phyinf","uid",wlan_uid,false);
		/*transmit power*/
		COMM_SetSelectValue(OBJ("tx_power"), XG(this.wifip+"/media/txpower"));
		/*wmm enable*/
		if(XG(this.wifip+"/media/wmm/enable")==="1") OBJ("wmm").checked = true;
		else OBJ("wmm").checked = false;												
		/*igmp snooping*/						
		if (this.mcast==="") { alert("InitValue ERROR!"); return false; }										
		OBJ("enhance").checked = (XG(this.mcast+"/device/multicast/wifienhance")==="1");		
		
		return true;
	},
	PreSubmit: function()
	{			
		/*transmit power*/
		XS(this.wifip+"/media/txpower", OBJ("tx_power").value);
		/*wmm enable*/
		XS(this.wifip+"/media/wmm/enable", OBJ("wmm").checked ? "1":"0");		
		/*igmp snooping*/					
		XS(this.mcast+"/device/multicast/wifienhance", OBJ("enhance").checked ? "1":"0");
		
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
