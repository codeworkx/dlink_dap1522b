<? include "/htdocs/phplib/xnode.php"; ?>
<script type="text/javascript">
function Page() {}
Page.prototype =
{
	services: "RUNTIME.PHYINF,WIFI.PHYINF",
	OnLoad: function() { BODY.CleanTable("client_list"); },
	OnUnload: function() {},
	OnSubmitCallback: function (code, result) { return false; },
	InitValue: function(xml)
	{
		/*		
		PXML.doc = xml;				
		this.runtime_phyinf = PXML.FindModule("RUNTIME.PHYINF");
		this.phyinf = PXML.FindModule("WIFI.PHYINF");
		if (!this.runtime_phyinf || !this.phyinf)
		{
			BODY.ShowAlert("Initial() ERROR!!!");
			return false;
		}				
		var wlan_uid = "WLAN-1.1";
		this.wlan_phyinf = GPBT(this.phyinf, "phyinf", "uid", wlan_uid, false);
		var wlan_freq = XG(this.wlan_phyinf+"/media/freq");		
		this.rphyinf = GPBT(this.runtime_phyinf+"/runtime", "phyinf", "uid", wlan_uid, false);		
		this.ssid = XG(this.rphyinf+"/media/wifi/ssid");				
		this.clients = this.rphyinf+"/media/clients";
		var cnt = XG(this.clients+"/entry#");								
		if (cnt=="") cnt = 0;
		OBJ("client_cnt").innerHTML = cnt;
		
		for (var i=1; i<=cnt; i++)
		{			
			var mac		= XG(this.clients+"/entry:"+i+"/macaddr");			
			var mode	= XG(this.clients+"/entry:"+i+"/band");
			if(wlan_freq==="5") 
			{
				if(mode=="11g") mode="11a";
				else if(mode=="11n") mode="11n(5GHz)";
			}	
			//var rate	= XG(this.clients+"/entry:"+i+"/rate");
			var rssi	= XG(this.clients+"/entry:"+i+"/rssi");
			var uptime	= this.Getuptime(this.ruptime - XG(this.clients+"/entry:"+i+"/uptime"));			
			var data	= [this.ssid, mac, uptime, mode, rssi];
			var type	= ["text", "text", "text", "text", "text"];
			BODY.InjectTable("client_list", "row"+i, data, type);
		}
		*/
		PXML.doc = xml;
		PAGE.FillTable("WLAN-1.1", "WIFI.PHYINF", "RUNTIME.PHYINF");
        PAGE.FillTable("WLAN-1.2", "WIFI.PHYINF", "RUNTIME.PHYINF");
		return true;
	},
	FillTable : function (wlan_phyinf, wifi_phyinf ,runtime_phyinf)
	{
		var wifi_module 	= PXML.FindModule(wifi_phyinf);
		var rwifi_module 	= PXML.FindModule(runtime_phyinf);
		var phyinf 			= GPBT(wifi_module, "phyinf", "uid",wlan_phyinf, false);

		var wifi_profile = XG(phyinf+"/wifi");
		var wifip = GPBT(wifi_module+"/wifi", "entry", "uid", wifi_profile, false);
		
		var rphyinf = GPBT(rwifi_module+"/runtime","phyinf","uid",wlan_phyinf, false);
		rphyinf += "/media/clients";
		
		if (!phyinf  || !rwifi_module)
		{
			BODY.ShowAlert("Initial() ERROR!!!");
			return false;
		}
		var wlan_freq = XG(phyinf+"/media/freq");

		/* Fill table */
		var cnt = XG(rphyinf +"/entry#");
		var ssid = XG(wifip+"/ssid");
		if(cnt == "") cnt = 0; 
		var idx = this.idx;

		if(idx==null)	idx = 1;
		for (var i=1; i<=cnt; i++)
		{
			var uid		= "DUMMY-"+idx;	idx++;
			var mac		= XG(rphyinf+"/entry:"+i+"/macaddr");
			var mode	= XG(rphyinf+"/entry:"+i+"/band");
			//var uptime	= XG(rphyinf+"/entry:"+i+"/uptime");
			var uptime	= this.Getuptime(this.ruptime - XG(rphyinf+"/entry:"+i+"/uptime"));

			if(wlan_freq==="5") 
			{
				if(mode=="11g") mode="11a";
				else if(mode=="11n") mode="11n(5GHz)";
			}
			
			//var rate	= XG(rphyinf+"/entry:"+i+"/rate");
			var rssi	= XG(rphyinf+"/entry:"+i+"/rssi");
/*			var data	= [ssid, mac, uptime, mode, rssi];
			var type	= ["text", "text", "text", "text", "text"];
			BODY.InjectTable("client_list", uid, data, type);*/
			var data	= [uptime, mac];
			var type	= ["text", "text"];
			BODY.InjectTable("client_list", uid, data, type);
		}
		this.idx = idx;
		OBJ("client_cnt").innerHTML = idx-1;
	},	


	PreSubmit: function() { return null; },
	IsDirty: null,
	Synchronize: function() {},
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////
	inf: null,
	phyinf: null,
	ruptime: <? echo query("/runtime/device/uptime");?>,
	Getuptime: function(seconds) 
	{
		var time= COMM_SecToStr(seconds);
		var howlong = "";
		if (time["day"]>1) howlong = time["day"]+"<?echo I18N("h", "Days");?> ";
		else if (time["day"]>0) howlong = time["day"]+"<?echo I18N("h", "Day");?> ";
		if (time["hour"]>1) howlong += time["hour"]+"<?echo I18N("h", "Hours");?> ";
		else if (time["hour"]>0) howlong += time["hour"]+"<?echo I18N("h", "Hour");?> ";
		if (time["min"]>1) howlong += time["min"]+"<?echo I18N("h", "Minutes");?> ";
		else if (time["min"]>0) howlong += time["min"]+"<?echo I18N("h", "Minute");?> ";
		howlong += time["sec"]+"<?echo I18N("h", "Seconds");?>"; 	
		return howlong;
	}	
}
</script>
