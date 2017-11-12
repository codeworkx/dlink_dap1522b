<script type="text/javascript">
function Page() {}
Page.prototype =
{
	services: "TRAFFICCTRL.BRIDGE-1",
	OnLoad: function(){},
	OnUnload: function() {},
	OnReload: function(){},
	OnSubmitCallback: function (code, result)
	{
			BODY.TurnReboot(code, result);
			return true;
	},
	Initial: function()	{},
	InitValue: function(xml)
	{
		PXML.doc = xml;
		var p = PXML.FindModule("TRAFFICCTRL.BRIDGE-1");
		if (p === "")		{ alert("InitValue ERROR!"); return false; }
		var qos = GPBT(p+"/trafficctrl", "entry", "uid", "TRAFFICCTRL-1" , false);
		OBJ("qos_enable").checked = (XG(qos+"/qos/enable") === "1")? true : false;
		var qos_type =  XG(qos+"/qos/qostype")
		COMM_SetSelectValue(OBJ("QoS_Type"),qos_type);
		
		OBJ("DownlinkTraffic").value = XG(qos+"/updownlinkset/bandwidth/downlink");
		OBJ("UplinkTraffic").value = XG(qos+"/updownlinkset/bandwidth/uplink");
		
		COMM_SetSelectValue(OBJ("LanPort_1st"), (qos_type === "")? 1 : XG(qos+"/qos/port/port1priority"));
		COMM_SetSelectValue(OBJ("LanPort_2nd"), (qos_type === "")? 3 : XG(qos+"/qos/port/port2priority"));
		COMM_SetSelectValue(OBJ("LanPort_3rd"), (qos_type === "")? 5 : XG(qos+"/qos/port/port3priority"));
		COMM_SetSelectValue(OBJ("LanPort_4th"), (qos_type === "")? 7 : XG(qos+"/qos/port/port4priority"));
		
		COMM_SetSelectValue(OBJ("AUI_Priority"), (qos_type === "")? 0 : XG(qos+"/qos/protocol/aui/priority"));
		OBJ("AUIlimit").value = XG(qos+"/qos/protocol/aui/limit");
		
		COMM_SetSelectValue(OBJ("web_Priority"), (qos_type === "")? 2 : XG(qos+"/qos/protocol/web/priority"));
		OBJ("weblimit").value = XG(qos+"/qos/protocol/web/limit");
		
		COMM_SetSelectValue(OBJ("mail_Priority"), (qos_type === "")? 1 : XG(qos+"/qos/protocol/mail/priority"));
		OBJ("maillimit").value = XG(qos+"/qos/protocol/mail/limit");
		
		COMM_SetSelectValue(OBJ("ftp_Priority"), (qos_type === "")? 3 : XG(qos+"/qos/protocol/ftp/priority"));
		OBJ("ftplimit").value = XG(qos+"/qos/protocol/ftp/limit");
	
		COMM_SetSelectValue(OBJ("user1_Priority"), (qos_type === "")? 0 : XG(qos+"/qos/protocol/user1/priority"));
		OBJ("user1limit").value = XG(qos+"/qos/protocol/user1/limit");
		OBJ("user1_port_s").value = XG(qos+"/qos/protocol/user1/startport");
		OBJ("user1_port_e").value = XG(qos+"/qos/protocol/user1/endport");

		COMM_SetSelectValue(OBJ("user2_Priority"), (qos_type === "")? 0 : XG(qos+"/qos/protocol/user2/priority"));
		OBJ("user2limit").value = XG(qos+"/qos/protocol/user2/limit");
		OBJ("user2_port_s").value = XG(qos+"/qos/protocol/user2/startport");
		OBJ("user2_port_e").value = XG(qos+"/qos/protocol/user2/endport");
		
		COMM_SetSelectValue(OBJ("user3_Priority"), (qos_type === "")? 0 : XG(qos+"/qos/protocol/user3/priority"));
		OBJ("user3limit").value = XG(qos+"/qos/protocol/user3/limit");
		OBJ("user3_port_s").value = XG(qos+"/qos/protocol/user3/startport");
		OBJ("user3_port_e").value = XG(qos+"/qos/protocol/user3/endport");
		
		COMM_SetSelectValue(OBJ("user4_Priority"), (qos_type === "")? 0 : XG(qos+"/qos/protocol/user4/priority"));
		OBJ("user4limit").value = XG(qos+"/qos/protocol/user4/limit");
		OBJ("user4_port_s").value = XG(qos+"/qos/protocol/user4/startport");
		OBJ("user4_port_e").value = XG(qos+"/qos/protocol/user4/endport");

		COMM_SetSelectValue(OBJ("other_Priority"), (qos_type=== "")? 3 : XG(qos+"/qos/protocol/other/priority"));
		OBJ("otherlimit").value = XG(qos+"/qos/protocol/other/limit");
		
		var rebootflag = "<?echo query("/runtime/reboot/status");?>";
		if(rebootflag != "1"){OBJ("rebootitem1").style.display="none"; OBJ("rebootitem2").style.display="none";}
		else {OBJ("rebootitem1").style.display=""; OBJ("rebootitem2").style.display="";}

		//this.OnChangeQoSType();
		this.OnCheckEnable();
		return true;
	},

	PreSubmit: function()
	{
		var p = PXML.FindModule("TRAFFICCTRL.BRIDGE-1");
		//var cnt = 1;
		var qos = p+"/trafficctrl";
		XS(qos+"/count", 1)
                if (OBJ("qos_enable").checked)
			XS(qos+"/entry:1/qos/enable", 1);
                else
                {
			XS(qos+"/entry:1/qos/enable", 0);
                        return PXML.doc;
                }

		XS(qos+"/entry:1/qos/qostype", OBJ("QoS_Type").value);
		
		if(OBJ("QoS_Type").value === "1")
		{
			XS(qos+"/entry:1/updownlinkset/bandwidth/downlink", OBJ("DownlinkTraffic").value);
			XS(qos+"/entry:1/updownlinkset/bandwidth/uplink", OBJ("UplinkTraffic").value);
			XS(qos+"/entry:1/qos/protocol/aui/priority", OBJ("AUI_Priority").value);
			XS(qos+"/entry:1/qos/protocol/aui/limit", OBJ("AUIlimit").value);
			XS(qos+"/entry:1/qos/protocol/web/priority", OBJ("web_Priority").value);
			XS(qos+"/entry:1/qos/protocol/web/limit", OBJ("weblimit").value);
			XS(qos+"/entry:1/qos/protocol/mail/priority", OBJ("mail_Priority").value);
			XS(qos+"/entry:1/qos/protocol/mail/limit", OBJ("maillimit").value);
			XS(qos+"/entry:1/qos/protocol/ftp/priority", OBJ("ftp_Priority").value);
			XS(qos+"/entry:1/qos/protocol/ftp/limit", OBJ("ftplimit").value);
			XS(qos+"/entry:1/qos/protocol/user1/priority", OBJ("user1_Priority").value);
			XS(qos+"/entry:1/qos/protocol/user1/limit", OBJ("user1limit").value);
			XS(qos+"/entry:1/qos/protocol/user1/startport", OBJ("user1_port_s").value);
			XS(qos+"/entry:1/qos/protocol/user1/endport", OBJ("user1_port_e").value);
			XS(qos+"/entry:1/qos/protocol/user2/priority", OBJ("user2_Priority").value);
			XS(qos+"/entry:1/qos/protocol/user2/limit", OBJ("user2limit").value);
			XS(qos+"/entry:1/qos/protocol/user2/startport", OBJ("user2_port_s").value);
			XS(qos+"/entry:1/qos/protocol/user2/endport", OBJ("user2_port_e").value);
			XS(qos+"/entry:1/qos/protocol/user3/priority", OBJ("user3_Priority").value);
			XS(qos+"/entry:1/qos/protocol/user3/limit", OBJ("user3limit").value);
			XS(qos+"/entry:1/qos/protocol/user3/startport", OBJ("user3_port_s").value);
			XS(qos+"/entry:1/qos/protocol/user3/endport", OBJ("user3_port_e").value);
			XS(qos+"/entry:1/qos/protocol/user4/priority", OBJ("user4_Priority").value);
			XS(qos+"/entry:1/qos/protocol/user4/limit", OBJ("user4limit").value);
			XS(qos+"/entry:1/qos/protocol/user4/startport", OBJ("user4_port_s").value);
			XS(qos+"/entry:1/qos/protocol/user4/endport", OBJ("user4_port_e").value);

			XS(qos+"/entry:1/qos/protocol/other/priority", OBJ("other_Priority").value);
			XS(qos+"/entry:1/qos/protocol/other/limit", OBJ("otherlimit").value);
		}
		else if(OBJ("QoS_Type").value === "0")
		{
			XS(qos+"/entry:1/qos/port/port1priority", OBJ("LanPort_1st").value);
			XS(qos+"/entry:1/qos/port/port2priority", OBJ("LanPort_2nd").value);
			XS(qos+"/entry:1/qos/port/port3priority", OBJ("LanPort_3rd").value);
			XS(qos+"/entry:1/qos/port/port4priority", OBJ("LanPort_4th").value);
		}
		XS(qos+"/entry:1/trafficmgr/whichsubmit", OBJ("whichsubmit").value);				
		return PXML.doc;
	},
	IsDirty: null,
	updn: null,
	qport: null,
	qprotocol: null,
	Synchronize: function() {},
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////


	OnChangeQoSType: function()
	{
		var p = PXML.FindModule("TRAFFICCTRL.BRIDGE-1");
		if (p === "")		{ alert("InitValue ERROR!"); return false; }
		var qos = GPBT(p+"/trafficctrl", "entry", "uid", "TRAFFICCTRL-1" , false);
		OBJ("show_Port_Priority").style.display = "none";
    		OBJ("show_AdvanceQos_title").style.display = "none";
    		switch (OBJ("QoS_Type").value)
    		{
    		case "1":
    			OBJ("show_AdvanceQos_title").style.display = "block";
    			break;
    		case "0":
    			if((XG(qos+"/trafficmgr/enable") === "1") && OBJ("qos_enable").checked)
    			{
    				BODY.ShowAlert("<?echo i18n("QOS Type can not be priority by lan port for the reason that Traffic Manager enabled!");?>");
    				OBJ("QoS_Type").selectedIndex = 1;
    				OBJ("show_AdvanceQos_title").style.display = "block";
    				return false;
    			}
    			OBJ("show_Port_Priority").style.display = "block";
      			break;
    		}
//		this.OnCheckEnable();
	},
	
	OnCheckEnable: function()
	{
		this.OnChangeQoSType();
		if(OBJ("qos_enable").checked)
		{
			BODY.DisableCfgElements(false);
			OBJ("aui_port").disabled = OBJ("web_port").disabled = OBJ("mail_port").disabled = OBJ("ftp_port").disabled = true;
		}
		else
		{
			BODY.DisableCfgElements(true);
			OBJ("qos_enable").disabled = OBJ("btn1").disabled = OBJ("btn2").disabled = OBJ("btn3").disabled =OBJ("btn4").disabled =false;					
			if(OBJ("rebootitem1") !== null)
			{OBJ("rebootitem1").disabled = OBJ("rebootitem2").disabled = false;}
		}
	},
	
	OnQoSSubmit: function()
	{
		var m = {
			user1_st: OBJ("user1_port_s").value,
			user1_e: OBJ("user1_port_e").value,
			user2_st: OBJ("user2_port_s").value,
			user2_e: OBJ("user2_port_e").value,
			user3_st: OBJ("user3_port_s").value,
			user3_e: OBJ("user3_port_e").value,
			user4_st: OBJ("user4_port_s").value,
			user4_e: OBJ("user4_port_e").value
			};
			
	if(!(parseInt(m.user2_st, [10]) > parseInt(m.user1_e, [10]) || parseInt(m.user2_e, [10]) < parseInt(m.user1_st, [10])) && m.user1_e!=="0" && m.user2_e!=="0")
	{
		alert("<?echo i18n("There can't be an overlap between the port ranges !");?>");
		return;
	}
	if(!(parseInt(m.user3_st, [10]) > parseInt(m.user1_e, [10]) || parseInt(m.user3_e, [10]) < parseInt(m.user1_st, [10])) && m.user1_e!=="0" && m.user3_e!=="0")
	{
		alert("<?echo i18n("There can't be an overlap between the port ranges !");?>");
		return;
	}
	if(!(parseInt(m.user3_st, [10]) > parseInt(m.user2_e, [10]) || parseInt(m.user3_e, [10]) < parseInt(m.user2_st, [10])) && m.user2_e!=="0" && m.user3_e!=="0")
	{
		alert("<?echo i18n("There can't be an overlap between the port ranges !");?>");
		return;
	}
	if(!(parseInt(m.user4_st, [10]) > parseInt(m.user1_e, [10]) || parseInt(m.user4_e, [10]) < parseInt(m.user1_st, [10])) && m.user4_e!=="0" && m.user1_e!=="0")
	{
		alert("<?echo i18n("There can't be an overlap between the port ranges !");?>");
		return;
	}
	if(!(parseInt(m.user4_st, [10]) > parseInt(m.user2_e, [10]) || parseInt(m.user4_e, [10]) < parseInt(m.user2_st, [10])) && m.user4_e!=="0" && m.user2_e!=="0")
	{
		alert("<?echo i18n("There can't be an overlap between the port ranges !");?>");
		return;
	}
	if(!(parseInt(m.user4_st, [10]) > parseInt(m.user3_e, [10]) || parseInt(m.user4_e, [10]) < parseInt(m.user3_st, [10])) && m.user4_e!=="0" && m.user3_e!=="0")
	{
		alert("<?echo i18n("There can't be an overlap between the port ranges !");?>");
		return;
	}
		BODY.OnSubmit();
	}
}
</script>
