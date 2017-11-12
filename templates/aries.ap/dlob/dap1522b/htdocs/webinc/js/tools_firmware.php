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
//		var wan	= PXML.FindModule("INET.WAN-1");
//		var rphy = PXML.FindModule("RUNTIME.PHYINF");
//		var rwanphyp = GPBT(rphy+"/runtime", "phyinf", "uid", XG(wan+"/inf/phyinf"), false);
//		if(XG(rwanphyp+"/linkstatus")=="")	{ OBJ("chkfw_btn").disabled = true; }
		
		return true;
	},
	PreSubmit: function() { return null; },
	IsDirty: null,
	Synchronize: function() {},
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////
	OnClickChkFW: function()
	{
		OBJ("chkfw_btn").disabled = "ture";
		OBJ("fw_message").style.display="block";
		OBJ("fw_message").innerHTML = "<?echo i18n("Connecting with the server for firmware information");?> ...";
		var ajaxObj = GetAjaxObj("checkfw");
		var times = 1;
		ajaxObj.createRequest();
		ajaxObj.onCallback = function(xml)
		{
			ajaxObj.release();
			setTimeout('PAGE.GetCheckReport()',5*1000);
		}
		ajaxObj.setHeader("Content-Type", "application/x-www-form-urlencoded");
		ajaxObj.sendRequest("service.cgi", "EVENT=CHECKFW");
	},
	GetCheckReport: function()
	{
		var ajaxObj = GetAjaxObj("checkreport");
		ajaxObj.createRequest();
		ajaxObj.onCallback = function(xml)
		{
			ajaxObj.release();
			var havenewfw = xml.Get("/firmware/havenewfirmware");
			if(havenewfw == "1")	OBJ("fw_message").innerHTML = "<?echo i18n("Have new version.");?>";
			else	OBJ("fw_message").innerHTML = "<?echo i18n("This firmware is the latest version.");?>";
			OBJ("chkfw_btn").disabled = "";
		}
		ajaxObj.setHeader("Content-Type", "application/x-www-form-urlencoded");
		ajaxObj.sendRequest("firmversion.php", "act=checkreport");
	}
}
</script>
