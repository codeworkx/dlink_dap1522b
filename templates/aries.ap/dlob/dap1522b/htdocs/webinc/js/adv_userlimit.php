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
		var wlan_uid = 'WLAN-1.1';
		this.wifip 			= PXML.FindModule("WIFI.PHYINF");
		if (this.wifip==="") { alert("InitValue ERROR!"); return false; }
		this.wifip = GPBT(this.wifip,"phyinf","uid",wlan_uid,false);
		
		if(XG(this.wifip+"/media/maxstanum/enable")==="1") OBJ("limit_enable").checked = true;
		else OBJ("limit_enable").checked = false;	
		OBJ("limit_value").value = XG(this.wifip+"/media/maxstanum/maxstanum");
		this.OnCheckLimit();
	},
	Initial: function()	{},
	PreSubmit: function()
	{
		XS(this.wifip+"/media/maxstanum/enable", (OBJ("limit_enable").checked === true) ? "1" : "0");
		XS(this.wifip+"/media/maxstanum/maxstanum", OBJ("limit_value").value);
		return PXML.doc;
	},
	OnCheckLimit: function()
	{
		var rebootflag = "<?echo query("/runtime/reboot/status");?>";
		if(rebootflag != "1"){OBJ("rebootitem1").style.display="none"; OBJ("rebootitem2").style.display="none";}
		else {OBJ("rebootitem1").style.display=""; OBJ("rebootitem2").style.display="";}
		
		OBJ("limit_value").disabled = true;
		if(OBJ("limit_enable").checked === true){OBJ("limit_value").disabled = false;}
	},
	IsDirty: null,
	wifip: null,
	Synchronize: function() {}
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////
}
</script>
