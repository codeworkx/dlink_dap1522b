<script type="text/javascript">
function Page() {}
Page.prototype =
{
	services: "TRAFFICCTRL.BRIDGE-1",
	OnLoad: function()
	{
		BODY.CleanTable("tratable");
	},
	OnUnload: function() {},
	OnSubmitCallback: function(code, result)
	{
		var p = PXML.FindModule("TRAFFICCTRL.BRIDGE-1");
		if (p === "")		{ alert("InitValue ERROR!"); return false; }
		var qos = GPBT(p+"/trafficctrl", "entry", "uid", "TRAFFICCTRL-1" , false);
		var cnt = S2I(XG(qos+"/trafficmgr/rule/count"));
						
		if (XG(qos+"/trafficmgr/whichsubmit") === "rule" && (!this.edit || this.edit === ""))
		{
			cnt = cnt - 1;
			XS(qos+"/trafficmgr/rule/count", cnt);
		}
		BODY.ShowContent();
		switch (code)
		{
		case "OK":
			BODY.OnReload();
			BODY.TurnReboot(code, result);
			return true;
			break;
		default : 	//if fatlady return error
			if(this.org && this.cfg)
			{	/* we should load the original configs. Can't count on PXML object, since its already modified. 
				We can count on our original table */
				delete this.cfg;
				this.cfg = new Array();
				var cnt = this.org.length;
				for (var i=0; i<cnt; i+=1)
				{
					this.cfg[i] = {
						uid: this.org[i].uid,
						name:	this.org[i].name,
						clientip:	this.org[i].clientip,
						clientmac:	this.org[i].clientmac,
						downlink:	this.org[i].downlink,
						uplink:	this.org[i].uplink
					};
				}
			}
			return false;
			break;	
		}
	},
	InitValue: function(xml)
	{
		PXML.doc = xml;
		var p = PXML.FindModule("TRAFFICCTRL.BRIDGE-1");
		if (p === "")		{ alert("InitValue ERROR!"); return false; }
		var qos = GPBT(p+"/trafficctrl", "entry", "uid", "TRAFFICCTRL-1" , false);
		
		COMM_SetSelectValue(OBJ("Trafficmanage"), (XG(qos+"/trafficmgr/enable") === "1") ? "1" : "0");
		OBJ("Eth0ToWirelessPrimary").value = XG(qos+"/updownlinkset/bandwidth/downlink");
		OBJ("WirelessToEth0Primary").value = XG(qos+"/updownlinkset/bandwidth/uplink");
		OBJ("deny").checked = (XG(qos+"/trafficmgr/unlistclientstraffic") === "0")? true : false;
		OBJ("forward").checked = (XG(qos+"/trafficmgr/unlistclientstraffic") !== "0")? true : false;

		var rebootflag = "<?echo query("/runtime/reboot/status");?>";
		if(rebootflag != "1"){OBJ("rebootitem1").style.display="none"; OBJ("rebootitem2").style.display="none";}
		else {OBJ("rebootitem1").style.display=""; OBJ("rebootitem2").style.display="";}

		this.OnChangeTrafficmgr();
	},
	Initial: function()	{},
	PreSubmit: function()
	{
		var p = PXML.FindModule("TRAFFICCTRL.BRIDGE-1");
    // var cnt = 1;
    var qos = p+"/trafficctrl";
    XS(qos+"/count", "1")
    if(OBJ("whichsubmit").value === "tramgr")
		{
 	   	if (OBJ("Trafficmanage").value === "1")
  	  {	
      	XS(qos+"/entry:1/trafficmgr/enable", 1);
			}   
   	 	else
			{
      	XS(qos+"/entry:1/trafficmgr/enable", 0);
      	return PXML.doc;
			}		
			XS(qos+"/entry:1/trafficmgr/unlistclientstraffic", (OBJ("deny").checked == true) ? "0" : "1");
			XS(qos+"/entry:1/updownlinkset/bandwidth/downlink", OBJ("Eth0ToWirelessPrimary").value);
			XS(qos+"/entry:1/updownlinkset/bandwidth/uplink", OBJ("WirelessToEth0Primary").value);
			XS(qos+"/entry:1/trafficmgr/whichsubmit", OBJ("whichsubmit").value);
			return PXML.doc;
		}

		/* max rules check */
		if(64 < this.cfg.length)
		{
			BODY.ShowAlert("<?echo i18n("Invalid Traffic rule. The maximum number of permitted Traffic rules has been exceeded.");?>");
			return null;
		}
		
		var cnt = S2I(XG(qos+"/entry:1/trafficmgr/rule/entry#"));
		while (cnt > 0) {XD(qos+"/entry:1/trafficmgr/rule/entry");cnt-=1;}
		cnt = 0;
		for (var i=0; i<this.cfg.length; i+=1)
		{
			cnt = i+1;
			var n = qos+"/entry:1/trafficmgr/rule/entry:"+cnt;
					XS(n+"/name",this.cfg[i].name);
					XS(n+"/clientip",this.cfg[i].clientip);
					XS(n+"/clientmac",	this.cfg[i].clientmac);
					XS(n+"/downlink",	this.cfg[i].downlink);
					XS(n+"/uplink",	this.cfg[i].uplink);
			
		}
		XS(qos+"/entry:1/trafficmgr/rule/count", this.cfg.length);
		PXML.CheckModule("TRAFFICCTRL.BRIDGE-1", null,null, "ignore");
		XS(qos+"/entry:1/trafficmgr/whichsubmit", OBJ("whichsubmit").value);
		return PXML.doc;
	},
	IsDirty: function()
	{
		if(this.org && this.cfg)
		{
			if (this.org.length !== this.cfg.length) return true;
			for (var i=0; i<this.cfg.length; i+=1)
			{
				if (this.org[i].uid !== this.cfg[i].uid ||
					this.org[i].name !== this.cfg[i].name ||
					this.org[i].clientip!== this.cfg[i].clientip||
					this.org[i].clientmac !== this.cfg[i].clientmac ||
					this.org[i].downlink !== this.cfg[i].downlink ||
					this.org[i].uplink !== this.cfg[i].uplink) return true; 
			}
		}
		return false;
	},
	Synchronize: function() {},
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////
	phyinf: null,
	org: null,
	cfg: null,
	edit: null,
	InitEntryList: function()
	{
		BODY.CleanTable("tratable");
		for (var i=0; i<this.cfg.length; i+=1)
		{
			var data = [this.cfg[i].name, this.cfg[i].clientip, this.cfg[i].clientmac, this.cfg[i].downlink, this.cfg[i].uplink,
				'<a href="javascript:PAGE.OnEdit('+i+');"><img src="pic/img_edit.gif"></a>',
				'<a href="javascript:PAGE.OnDelete('+i+');"><img src="pic/img_delete.gif"></a>'
				];
			var type = ["text","text","text","text","text","",""];
			BODY.InjectTraTable("tratable", this.cfg[i].uid, data, type);
		}
	},
	OnClickTraSubmit: function()
	{
		var e = {
			name:	OBJ("name").value,
			clientip:	OBJ("ClientIP").value,
			clientmac:	OBJ("ClientMac").value,
			downlink:	OBJ("Eth0ToWireless").value,
			uplink:	OBJ("WirelessToEth0").value
			};
		
		var i = 0;
		var j = 0;
		var update = -1;
		if (!this.edit || this.edit === "") i = this.cfg.length;
		else
		{
			for (i=0; i<this.cfg.length; i+=1)
			{
				if (this.cfg[i].uid === this.edit)
				{
					update = i;
					break;
				}
			}
		}

		//check same rule exist or not
		for (j=0; j<this.cfg.length; j++)
		{
			if(j === update) continue;
			if((e.name !== "" && this.cfg[j].name === e.name) || (e.clientip !== "" && this.cfg[j].clientip === e.clientip)
			|| (e.clientmac !== "" && this.cfg[j].clientmac.toUpperCase() === e.clientmac.toUpperCase()))
			{
				alert('<?echo i18n("The Rule is already existed !");?>');
				return;
			}
		}

		this.cfg[i] = {
				uid:	this.edit?this.edit:"",
				name:	e.name,
				clientip:	e.clientip,
				clientmac:	e.clientmac,
				downlink:	e.downlink,
				uplink:	e.uplink
			};
		OBJ("whichsubmit").value = "rule";
		BODY.OnSubmit();
	},
	OnClickTraCancel: function()
	{
		this.edit = null;
		OBJ("trasubmit").value = "<?echo i18n("Add");?>";
		OBJ("name").value = "";
		OBJ("ClientIP").value = ""; 
		OBJ("ClientMac").value = "";
		OBJ("Eth0ToWireless").value = "";
		OBJ("WirelessToEth0").value = "";
	},
	OnEdit: function(i)
	{
		OBJ("name").value = this.cfg[i].name;
		OBJ("ClientIP").value = this.cfg[i].clientip;
		OBJ("ClientMac").value = this.cfg[i].clientmac;
		OBJ("Eth0ToWireless").value = this.cfg[i].downlink;
		OBJ("WirelessToEth0").value = this.cfg[i].uplink;

		OBJ("trasubmit").value = "<?echo i18n("Update");?>";
		this.edit = this.cfg[i].uid;
	},
	OnDelete: function(i)
	{
		this.cfg.splice(i,1);
		OBJ("whichsubmit").value = "rule";
		BODY.OnSubmit();
	},
	OnTraSubmit: function()
	{
		OBJ("whichsubmit").value = "tramgr";
		BODY.OnSubmit();
	},
	dummy: function() {},
	
	OnChangeTrafficmgr: function()
	{
		var p = PXML.FindModule("TRAFFICCTRL.BRIDGE-1");
		if (p === "")		{ alert("InitValue ERROR!"); return false; }
		var qos = GPBT(p+"/trafficctrl", "entry", "uid", "TRAFFICCTRL-1" , false);
		
		if(OBJ("Trafficmanage").value === "0")
		{
			OBJ("deny").disabled = OBJ("forward").disabled = OBJ("Eth0ToWirelessPrimary").disabled = OBJ("WirelessToEth0Primary").disabled = true;
			OBJ("add_rule_title").style.display="none";
			OBJ("show_rule_information").style.display="none";
		}
		else
		{
			if(XG(qos+"/qos/enable") === "1" && XG(qos+"/qos/qostype") === "0")
			{
				alert('<?echo i18n("The traffic manager can not be enable for the reason  that qos enabled and priority by lan port!");?>');
				COMM_SetSelectValue(OBJ("Trafficmanage"),0)
				return false;
			}
			if(XG(qos+"/trafficmgr/enable") === "0")
			{
				alert('<?echo i18n("Please press \"Save Setting\" button to take effect Traffic Manager!");?>');
			}
			OBJ("deny").disabled = OBJ("forward").disabled = OBJ("Eth0ToWirelessPrimary").disabled = OBJ("WirelessToEth0Primary").disabled = false;
			OBJ("add_rule_title").style.display="block";
			OBJ("show_rule_information").style.display="block";
			
			if (this.org) delete this.org;
			if (this.cfg) delete this.cfg;
			this.org = new Array();
			this.cfg = new Array();
		
			var cnt = S2I(XG(qos+"/trafficmgr/rule/count"));
			for (var i=0; i<cnt; i+=1)
			{
				var index = i+1;
				var s = qos+"/trafficmgr/rule/entry:"+index+"/";
				this.org[i] = {
					uid: i+1,
					name:	XG(s+"name"),
					clientip:	XG(s+"clientip"),
					clientmac:	XG(s+"clientmac"),
					downlink:	XG(s+"downlink"),
					uplink:	XG(s+"uplink")
					};
				this.cfg[i] = {
					uid: i+1,
					name:	XG(s+"name"),
					clientip:	XG(s+"clientip"),
					clientmac:	XG(s+"clientmac"),
					downlink:	XG(s+"downlink"),
					uplink:	XG(s+"uplink")
					};
			}
			this.InitEntryList();
			this.OnClickTraCancel();
		}
	}
};

</script>
