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
	OnSubmitCallback: function (code, result){return false;},		
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
		this.wifip1 		= GPBT(this.wifip+"/wifi", "entry", "uid", wifi_profile, false);		
		
		var wlan_uid = "WLAN-1.2";	
		this.rphyinf 		= GPBT(this.runtime_phyinf, "phyinf","uid",wlan_uid, false);
		this.phyinf 		= GPBT(this.wifip, "phyinf", "uid",wlan_uid, false);
		var wifi_profile 	= XG(this.phyinf+"/wifi");
		this.wifip2 		= GPBT(this.wifip+"/wifi", "entry", "uid", wifi_profile, false);		
								
		
		/*----wire-------*/		
		this.macfp = PXML.FindModule("MACCTRL");	
		if (!this.macfp) { BODY.ShowAlert("<?echo i18n("Init_MAC() ERROR!");?>"); return false; }
		this.macfp += "/acl/macctrl";
		TEMP_RulesCount(this.macfp, "rmd");

		if (XG(this.macfp+"/policy") !== "")	OBJ("mode").value = XG(this.macfp+"/policy");
		else					OBJ("mode").value = "DISABLE";

		/* load table content */
		for(i=1; i<=<?=$MAC_FILTER_MAX_COUNT?>; i++)
		{
			var b = this.macfp+"/entry:" +i;
			OBJ("uid_"+i).value	= XG(b+"/uid");				
			OBJ("description_"+i).value	= XG(b+"/description");
			
			var mac = this.GetMAC(XG(b+"/mac"));
			for (var j=1; j<=6; j++)
			{
				if (mac[j].length == "1")
				{
					mac[j] = "0"+mac[j];
				}	
				OBJ("mac"+j+"_"+i).value = mac[j].toUpperCase();
			}																			
		}
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
									
			/* if the mac field is empty, it means to remove this entry,
			 * so skip this entry. */
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
		var name = "description_"+m;
		var elem = document.getElementById(name);
		elem.value ="";
		
		for(var i=1; i<=6; i++)
		{
			var id = "mac"+i+"_"+m;			
			elem = document.getElementById(id);
			elem.value ="";						
		}		
	}
}
</script>
