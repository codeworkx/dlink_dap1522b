<script type="text/javascript">
function Page() {}
Page.prototype =
{
	services: "WIFI.PHYINF",
	OnLoad: function() {},
	OnUnload: function() {},
	OnSubmitCallback: function (code, result) { return false; },
	InitValue: function(xml)
	{
		PXML.doc = xml;
		var swithmode_path = PXML.FindModule("RUNTIME.SWITCHMODE"); //The service is included in templates.php in DAP-1522B
		var swithmode = XG(swithmode_path+"/runtime/device/switchmode");
		this.wifip = PXML.FindModule("WIFI.PHYINF");
		PXML.CheckModule("WIFI.PHYINF", null,null, "ignore");
		
		GetLangcode();
		EncodeHex();
		GetQueryUrl();
		Configured();
		if(swithmode != "APCLI")
		{
			var path = GPBT(this.wifip+"/wifi", "entry", "uid", "WIFI-1", "0"); 
			OBJ("ssid").innerHTML = COMM_EscapeHTMLSC(XG(path+"/ssid"));
		}
		else
		{
			var path = GPBT(this.wifip+"/wifi", "entry", "uid", "WIFI-3", "0"); 
			OBJ("ssid_sta").innerHTML = COMM_EscapeHTMLSC(XG(path+"/ssid"));
		}
		return true;
	},
	PreSubmit: function() { return null; },
	wifip: null,
	IsDirty: null,
	Synchronize: function() {}
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////
}

function GetLangcode()
{
	var langcode = "<?echo query("/runtime/device/langcode");?>";
	OBJ("langcode").innerHTML = (langcode=="")? "en":langcode;
}

function toHex( n )
{
	var digitArray = new Array('0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f');
	var result = ''
	var start = true;

	for (var i=32; i>0;)
	{
		i -= 4;
		var digit = ( n >> i ) & 0xf;

		if (!start || digit != 0)
		{
			start = false;
			result += digitArray[digit];
		}
	}

	return ( result == '' ? '0' : result );
}
function pad( str, len, pad )
{
	var result = str;

	for (var i=str.length; i<len; i++)
	{
		result = pad + result;
	}

	return  result;
}
function EncodeHex()
{
	var str = "<?echo cut(fread("", "/etc/config/builddaytime"), "0", "\n");?>";
	var result = "";

	for (var i=0; i<str.length; i++)
	{
		if (str.substring(i,i+1).match(/[^\x00-\xff]/g) != null)
		{
			result += escape(str.substring(i,i+1), 1).replace(/%/g,'\\');
		}
		else
		{
			result += pad(toHex(str.substring(i,i+1).charCodeAt(0)&0xff),2,'0');
		}
	}
	OBJ("checksum").innerHTML = result.substring(result.length-8,result.length);
}

function GetQueryUrl()
{
	var fwsrv = "<?echo query("/runtime/device/fwinfosrv");?>";
	var fwpath= "<?echo query("/runtime/device/fwinfopath");?>";
	var model = "<?echo query("/runtime/device/modelname");?>";
	var fwver = "<?echo query("/runtime/device/firmwareversion");?>";
	var hwstr = "<?echo query("/runtime/devdata/hwrev");?>";
	var hwver = "Ax";
	// Get sw ver
	fwstr = fwver.split(".");
	fwver = "0" + fwstr[0] + fwstr[1]; //0112

	// Get hw revision
	for(i=0; i<hwstr.length; i++)
	{
		char_code = hwstr.charAt(i);
		if ((char_code >= 'a' && char_code <= 'z') ||
				(char_code >= 'A' && char_code <= 'Z'))
		{
			hwver=char_code.toUpperCase()+"x";
			break;
		}
	}

	//OBJ("fwq").innerHTML = "http:\/\/"+fwsrv+fwpath+"?model="+model+"_"+hwver+"_Default_FW_"+fwver;
	OBJ("fwq").innerHTML = "http:\/\/"+fwsrv+fwpath+"?model="+model+"_"+hwver+"_Default";
}

function Configured()
{
	OBJ("configured").innerHTML = "<?

	$size = query("/runtime/device/devconfsize");
	if		($size == "")	echo I18N("h", "N/A");
	else if ($size > 0)		echo i18n(" = 0");
	else					echo i18n(" = 1");

	?>";
}
</script>
