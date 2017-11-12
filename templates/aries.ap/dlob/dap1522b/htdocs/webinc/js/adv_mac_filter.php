<style>
/* The CSS is only for this page.
 * Notice:
 *	If the items are few, we put them here,
 *	If the items are a lot, please put them into the file, htdocs/web/css/$TEMP_MYNAME.css.
 */
div.blackbox table.general_NoBreakWord
{
	table-layout:fixed;
	width: 535px;
	text-align: left;
	margin-left: 7px;
	margin-right: 7px;
	border: 0px;
	border-spacing: 1px 1px;
	background-color: #dfdfdf;
}
div.blackbox table.general_NoBreakWord tr
{
	margin: 0px;
	padding: 1px;
	border-color: inherit;
}
div.blackbox table.general_NoBreakWord tr td
{
	background-color: #dfdfdf;
	border: 1px solid white;
	padding: 2px 1px 4px 1px;
}
</style>

<script type="text/javascript">
function Page() {}
Page.prototype =
{
	services: "MACCTRL, WIFI.PHYINF, PHYINF.WIFI, RUNTIME.PHYINF",	
	OnLoad:   function()
	{
		if (!this.apmode)
		{
			BODY.DisableCfgElements(true);
		}
	},
	OnUnload: function() {},
	OnSubmitCallback: function (code, result)
	{
		BODY.TurnReboot(code, result);
		return true;
	},		
	InitValue: function(xml)
	{						
		PXML.doc = xml;
		
		/*----wireless----*/
		
		this.wifip 			= PXML.FindModule("WIFI.PHYINF");
		this.runtime_phyinf = PXML.FindModule("RUNTIME.PHYINF");		
		
		if (!this.wifip)
		{
			BODY.ShowAlert("Initial() ERROR!!!");
			return false;
		}
		
		
		var wlan_uid = "WLAN-1.1";	
		this.rphyinf 		= GPBT(this.runtime_phyinf, "phyinf","uid",wlan_uid, false);
		this.phyinf 		= GPBT(this.wifip, "phyinf", "uid",wlan_uid, false);
		var wifi_profile 	= XG(this.phyinf+"/wifi");
		this.wifip1 		    = GPBT(this.wifip+"/wifi", "entry", "uid", wifi_profile, false);		
		
		var wlan_uid = "WLAN-1.2";	
		this.rphyinf 		= GPBT(this.runtime_phyinf, "phyinf","uid",wlan_uid, false);
		this.phyinf 		= GPBT(this.wifip, "phyinf", "uid",wlan_uid, false);
		var wifi_profile 	= XG(this.phyinf+"/wifi");
		this.wifip2 		= GPBT(this.wifip+"/wifi", "entry", "uid", wifi_profile, false);		
								
		
		/*----wire-------*/		
		this.macfp = PXML.FindModule("MACCTRL");	
		if (!this.macfp) { BODY.ShowAlert("<?echo i18n("Init_MAC() ERROR!");?>"); return false; }
		this.macfp += "/acl/macctrl";
//		TEMP_RulesCount(this.macfp, "rmd");

		if (XG(this.macfp+"/policy") !== "")	OBJ("mode").value = XG(this.macfp+"/policy");
		else					OBJ("mode").value = "DISABLE";

		/* load table content */
		for(i=1; i<=<?=$MAC_FILTER_MAX_COUNT?>; i++)
		{
			var b = this.macfp+"/entry:" +i;
			OBJ("uid_"+i).value	= XG(b+"/uid");				
			//OBJ("description_"+i).value	= XG(b+"/description");
			
			var mac = this.GetMAC(XG(b+"/mac"));
			OBJ("mac_"+i).value     = XG(b+"/mac");
			OBJ("client_list_"+i).value  = "";									
		}
		
		var rebootflag = "<?echo query("/runtime/reboot/status");?>";
		if(rebootflag != "1"){OBJ("rebootitem1").style.display="none"; OBJ("rebootitem2").style.display="none";}
		else {OBJ("rebootitem1").style.display=""; OBJ("rebootitem2").style.display="";}
			
		PAGE.OnChangeFilterStatus();
		return true;
	},

	PreSubmit: function()
	{						
		XS(this.wifip1+"/acl/policy", OBJ("mode").value);
		XS(this.wifip2+"/acl/policy", OBJ("mode").value);			
		XS(this.macfp+"/policy", OBJ("mode").value);

		var old_count = XG(this.macfp+"/count");
		var cur_count = 0;
		/* delete the old entries
		 * Notice: Must delte the entries from tail to head */
		while(old_count > 0)
		{
			XD(this.macfp+"/entry:"+old_count);
			old_count -= "1";
		}

		/* update the entries */			
		/*
		for (var i=1; i<=<?=$MAC_FILTER_MAX_COUNT?>; i+=1)
		{						
						
			var macstr = "";
			for (var j=1; j<=6; j++)
			{				
				if(OBJ("mac"+j+"_"+i).value != "")
				{
					macstr = macstr + OBJ("mac"+j+"_"+i).value.toUpperCase();				
					if(j<6) macstr = macstr +":";
				}	
			}
		*/						
			/* if the mac field is empty, it means to remove this entry,
			 * so skip this entry. */
		/*
			if (macstr!=="")
			{																								
				cur_count+=1;
				
				var w1 = this.wifip1+"/acl/entry:"+cur_count;
				XS(w1+"/uid",			"MACF-"+i);								
				XS(w1+"/mac",			macstr);								
				
				var w2 = this.wifip2+"/acl/entry:"+cur_count;
				XS(w2+"/uid",			"MACF-"+i);								
				XS(w2+"/mac",			macstr);
				
				var b = this.macfp+"/entry:"+cur_count;
				XS(b+"/uid",			"MACF-"+i);				
				XS(b+"/enable",			(macstr !="") ? "1" : "0");
				XS(b+"/mac",			macstr);				
				XS(b+"/description",	OBJ("description_"+i).value); 
												
			}
		}
		*/
                /* update the entries */
                for (var i=1; i<=<?=$MAC_FILTER_MAX_COUNT?>; i+=1)
                {
                        /* if the mac field is empty, it means to remove this entry,
                         * so skip this entry. */
                        if (OBJ("mac_"+i).value!=="")
                        {
                                var mac = this.GetMAC(OBJ("mac_"+i).value);
                                for (var j=1; j<=6; j++)
                                {
                                        if (mac[j].length == "1")
                                        mac[j] = "0"+mac[j];
                                }
                                OBJ("mac_"+i).value = mac[1].toUpperCase()+":"+mac[2].toUpperCase()+":"+mac[3].toUpperCase()+":"+mac[4].toUpperCase()+":"+mac[5].toUpperCase()+":"+mac[6].toUpperCase();

                                cur_count+=1;
                                var b = this.macfp+"/entry:"+cur_count;

                                XS(b+"/uid",                    "MACF-"+i);
                                XS(b+"/enable",                	"1");
                                XS(b+"/mac",                    OBJ("mac_"+i).value);
                                //XS(b+"/description",    "<?echo query("/runtime/device/modelname");?>");
                        }
                }
		
		XS(this.wifip1+"/acl/count", cur_count);
		XS(this.wifip2+"/acl/count", cur_count);
		XS(this.macfp+"/count", cur_count);				
		
		PXML.ActiveModule("MACCTRL");

		return PXML.doc;
	},

	IsDirty: null,
	Synchronize: function() {},
	
	apmode: <?if (query("/runtime/device/switchmode")=="APCLI") echo "false"; else echo "true";?>,

	macfp : null,

	OnClickArrowKey: function(index)
	{
		var dhcp_client = OBJ("client_list_"+index);

		if (dhcp_client.value === "")
		{
			BODY.ShowAlert("<?echo i18n("Please select a machine first !");?>");
			return false;
		}

		OBJ("mac_"+index).value = dhcp_client.value;
	},
	GetMAC: function(m)
	{
		var myMAC=new Array();
		if (m.search(":") != -1)	var tmp=m.split(":");
		else				var tmp=m.split("-");
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
	ClearMAC: function(m)
	{
		var mac = "mac_"+m;
		var elem = document.getElementById(mac);
		elem.value ="";

	},
	OnChangeFilterStatus: function()
	{
		if(OBJ("mode").value === "DISABLE")
		{
			BODY.DisableCfgElements(true);
			OBJ("mode").disabled = false;
			OBJ("btn1").disabled = OBJ("btn2").disabled = OBJ("btn3").disabled = OBJ("btn4").disabled = false;
			if(OBJ("rebootitem1") !== null)
			{OBJ("rebootitem1").disabled = OBJ("rebootitem2").disabled = false;}
		}
		else
			BODY.DisableCfgElements(false);
	}
}
</script>
