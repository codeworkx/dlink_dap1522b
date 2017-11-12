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
		return true;
	},
	PreSubmit: function() { return null; },
	IsDirty: null,
	Synchronize: function() {},
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////
	OnClickChkFW: function()
	{
		// TO DO!
		OBJ("fw_message").style.display="block";
		OBJ("fw_message").innerHTML = "<?echo i18n("Connecting with the server for firmware information");?> ...";
		setTimeout('PAGE.GetCheckReport()',1000);
	},
	GetCheckReport: function()
	{
		OBJ("fw_message").innerHTML = "<?echo i18n("This firmware is the latest version.");?>";
	}
}
</script>
