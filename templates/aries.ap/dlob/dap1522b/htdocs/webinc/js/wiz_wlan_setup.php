<script type="text/javascript">
function Page() {}
Page.prototype =
{
	services: "",
	OnLoad: function() {},
	OnUnload: function() {},
	OnSubmitCallback: function (code, result) { return false; },
	InitValue: function(xml)
	{
		PXML.doc = xml;
		var swithmode_path = PXML.FindModule("RUNTIME.SWITCHMODE"); //The service is included in templates.php in DAP-1525
		var swithmode = XG(swithmode_path+"/runtime/device/switchmode");
		if(swithmode==="APCLI")	
		{
		OBJ("div_station").style.display = "block";	
		OBJ("div_24G_band").style.display = "none";		
		}
		else
		{
		OBJ("div_station").style.display = "none";	
		OBJ("div_24G_band").style.display = "block";	
		}
		return true;
	},
	PreSubmit: function() { return null; },
	IsDirty: null,
	Synchronize: function() {}
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////

}
</script>

