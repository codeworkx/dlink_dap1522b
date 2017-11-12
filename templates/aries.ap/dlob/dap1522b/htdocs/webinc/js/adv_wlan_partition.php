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
	services: "WIFI.PHYINF, PHYINF.WIFI",
	OnLoad: function(){},
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
		if (this.wifip==="") { alert("InitValue ERROR!"); return false; }
		this.wifip = GPBT(this.wifip,"phyinf","uid",wlan_uid,false);
		
		COMM_SetSelectValue(OBJ("isc"), XG(this.wifip+"/media/w_partition"));
		COMM_SetSelectValue(OBJ("ewa"), XG(this.wifip+"/media/e_partition"));

		var rebootflag = "<?echo query("/runtime/reboot/status");?>";
		if(rebootflag != "1"){OBJ("rebootitem1").style.display="none"; OBJ("rebootitem2").style.display="none";}
		else {OBJ("rebootitem1").style.display=""; OBJ("rebootitem2").style.display="";}

		return true;
	},
	Initial: function() {},
	PreSubmit: function()
	{
		XS(this.wifip+"/media/w_partition", OBJ("isc").value);
		XS(this.wifip+"/media/e_partition", OBJ("ewa").value);
		return PXML.doc;
	},
	IsDirty: null,
	wifip: null,
	Synchronize: function() {}
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////
}
</script>
