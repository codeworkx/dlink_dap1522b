<?
include "/htdocs/phplib/phyinf.php";
$switch_mode = query("/runtime/device/switchmode");
if($switch_mode=="APCLI")
	$wlan_uid = "WLAN-2";
else 
	$wlan_uid = "WLAN-1.1";
?>
<style>
/* The CSS is only for this page.
 * Notice:
 *  If the items are few, we put them here,
 *  If the items are a lot, please put them into the file, htdocs/web/css/$TEMP_MYNAME.css.
 */
select.broad	{ width: 110px; }
select.narrow	{ width: 65px; }
</style>

<script type="text/javascript">
function Page() {}
Page.prototype =
{
	services: "DEVICE.HOSTNAME,INET.BRIDGE-1,INET.BRIDGE-2,INET.BRIDGE-3,RUNTIME.PHYINF,WIFI.PHYINF,PHYINF.WIFI",
	OnLoad: function()
	{
		SetDelayTime(500);	//add delay for event updatelease finished
					
		OBJ("ipaddr_v4").value 		= "";
		OBJ("netmask_v4").value 	= "";
		OBJ("gateway_v4").value 	= "";
		OBJ("dns1_v4").value 		= "";
		OBJ("dns2_v4").value 		= "";
		if(this.opmode!=="STA")
		{
			OBJ("ipaddr_v6").value 		= "";
			OBJ("sprefix_v6").value 	= "";
			OBJ("gateway_v6").value 	= "";
			OBJ("dns1_v6").value 		= "";
			OBJ("dns2_v6").value 		= "";	
			OBJ("ipv6_dhcp_pdns").value	= "";
			OBJ("ipv6_dhcp_sdns").value	= "";	
		}
	},
	OnUnload: function() {},
	OnSubmitCallback: function (code, result)
	{
		BODY.ShowContent();
		switch (code)
		{
		case "OK":
			if (this.ipdirty)
			{
				var msgArray_static =
				[
					'<?echo i18n("The LAN IP address of this device is changing.");?>',
					'<?echo i18n("It needs several seconds for the changes to take effect.");?>',
					'<?echo i18n("You may need to change the IP address of your computer to access the device.");?>',
					'<?echo i18n("You can access the device by clicking the link below.");?>',
					'<a href="http://'+OBJ("ipaddr_v4").value+'" style="color:#0000ff;">http://'+OBJ("ipaddr_v4").value+'</a>'
				];
				var msgArray_DHCP =
				[
					'<?echo i18n("The LAN IP address of this device is changing.");?>',
					'<?echo i18n("It needs several seconds for the changes to take effect.");?>',
					'<?echo i18n("You may need to change the IP address of your computer to access the device.");?>'
				];				
				SetDelayTime(4000);//To wait LAN start up. 
				if(XG(this.inetp_v4+"/ipv4/static") === "1")
					var msgArray = msgArray_static;
				else
					var msgArray = msgArray_DHCP;
				BODY.ShowMessage('<?echo i18n("LAN IP Address Changed");?>', msgArray);
			}
			else
			{
				BODY.OnReload();
				BODY.TurnReboot(code, result);
				return true;
			}
			break;
		case "BUSY":
			BODY.ShowAlert("<?echo i18n("Someone is configuring the device, please try again later.");?>");
			break;
		case "HEDWIG":
			if (result.Get("/hedwig/result")=="FAILED")
			{
				//FocusObj(result);
				BODY.ShowAlert(result.Get("/hedwig/message"));
			}
			break;
		case "PIGWIDGEON":
			if (result.Get("/pigwidgeon/message")=="no power")
			{
				BODY.NoPower();
			}
			else
			{
				this.ShowAlert(result.Get("/pigwidgeon/message"));
			}
			break;
		}
		return true;
	},
	InitValue: function(xml)
	{
		PXML.doc = xml;
		this.devp = PXML.FindModule("DEVICE.HOSTNAME");
		PXML.CheckModule("DEVICE.HOSTNAME", null, null, "ignore");
		OBJ("device_name").value = XG(this.devp+"/device/hostname");
		/* For IPv4 Start */
		var p = PXML.FindModule("INET.BRIDGE-1");
		var inf_ipv4 = GPBT(p, "inf", "uid", "BRIDGE-1", false);
		var inet_ipv4 = XG(inf_ipv4+"/inet");
		this.inetp_v4 = GPBT(p+"/inet", "entry", "uid", inet_ipv4, false);
		if(XG(this.inetp_v4+"/ipv4/static") === "1")
		{
			this.lan_type = "lan_static";
			OBJ("lan_type_v4").value	= "lan_static";
			OBJ("ipaddr_v4").value 		= XG(this.inetp_v4+"/ipv4/ipaddr");
			OBJ("netmask_v4").value 	= COMM_IPv4INT2MASK(XG(this.inetp_v4+"/ipv4/mask"));
			OBJ("gateway_v4").value 	= XG(this.inetp_v4+"/ipv4/gateway");
			var cnt = XG(this.inetp_v4+"/ipv4/dns/count");
			OBJ("dns1_v4").value		= cnt > 0 ? XG(this.inetp_v4+"/ipv4/dns/entry:1") : "";
			OBJ("dns2_v4").value		= cnt > 1 ? XG(this.inetp_v4+"/ipv4/dns/entry:2") : "";
			OBJ("ipv4_conn_type").style.display	= "block";
		}
		else
		{
			this.lan_type = "lan_dynamic";			
			OBJ("lan_type_v4").value = "lan_dynamic";
			OBJ("ipv4_conn_type").style.display	= "none"; 
		}			 
		/* For IPv4 End */
		/* For IPv6 Start */
		var wlan_uid = '<?=$wlan_uid?>';		
		this.wifip 			= PXML.FindModule("WIFI.PHYINF");
					
		if (this.wifip==="") { alert("InitValue ERROR!"); return false; }
				
		this.phyinf 		= GPBT(this.wifip, "phyinf", "uid",wlan_uid, false);
		var wifi_profile 	= XG(this.phyinf+"/wifi");
		this.wifimode 			= GPBT(this.wifip+"/wifi", "entry", "uid", wifi_profile, false);
		this.opmode		 	= XG(this.wifimode+"/opmode");
		
	if(this.opmode!=="STA")
	{
		var p6_ll = PXML.FindModule("INET.BRIDGE-2");
		var inf_ipv6_ll = GPBT(p6_ll, "inf", "uid", "BRIDGE-2", false);
		var inet_ipv6_ll = XG(inf_ipv6_ll+"/inet");
		var phy_ipv6_ll = XG(inf_ipv6_ll+"/phyinf");
		this.inetp_v6_ll = GPBT(p6_ll+"/inet", "entry", "uid", inet_ipv6_ll, false);
		var rp_ll = PXML.FindModule("RUNTIME.PHYINF");
		this.rphyp_v6_ll = GPBT(rp_ll+"/runtime", "phyinf", "uid", phy_ipv6_ll, false);
		
		var p6 = PXML.FindModule("INET.BRIDGE-3");
		this.inf_ipv6 = GPBT(p6, "inf", "uid", "BRIDGE-3", false);
		var inet_ipv6 = XG(this.inf_ipv6+"/inet");
		this.inetp_v6 = GPBT(p6+"/inet", "entry", "uid", inet_ipv6, false);
		
		OBJ("lan_ll").innerHTML    = XG(this.rphyp_v6_ll+"/ipv6/link/ipaddr");
		OBJ("lan_ll_pl").innerHTML = "/"+XG(this.rphyp_v6_ll+"/ipv6/link/prefix");
		OBJ("ipv6_dhcp_dns_auto").checked = true;
		OBJ("ipv6_dhcp_pdns").disabled = OBJ("ipv6_dhcp_sdns").disabled = true;
		
		if(XG(this.inf_ipv6+"/active") === "0")
		{
			OBJ("ipv6_mode").value = "LL";
			OBJ("ipv6_linklocal_body").style.display = "block";
			OBJ("ipv6_dhcp_body").style.display =	OBJ("ipv6_static_body").style.display = "none";
		}
		else if(XG(this.inetp_v6+"/ipv6/mode") === "STATIC")
		{
			OBJ("ipv6_mode").value = "STATIC";
			OBJ("ipaddr_v6").value 		= XG(this.inetp_v6+"/ipv6/ipaddr");
			OBJ("sprefix_v6").value 	= XG(this.inetp_v6+"/ipv6/prefix");
			OBJ("gateway_v6").value 	= XG(this.inetp_v6+"/ipv6/gateway");
			var cnt = XG(this.inetp_v6+"/ipv6/dns/count");
			OBJ("dns1_v6").value		= cnt > 0 ? XG(this.inetp_v6+"/ipv6/dns/entry:1") : "";
			OBJ("dns2_v6").value		= cnt > 1 ? XG(this.inetp_v6+"/ipv6/dns/entry:2") : "";
			OBJ("ipv6_static_body").style.display = "block";
			OBJ("ipv6_dhcp_body").style.display =	OBJ("ipv6_linklocal_body").style.display = "none";			
		}
		else if(XG(this.inetp_v6+"/ipv6/mode") === "AUTO")
		{
			OBJ("ipv6_mode").value = "AUTO";
			var cnt = XG(this.inetp_v6+"/ipv6/dns/count");
			if(cnt > 0)
			{
				OBJ("ipv6_dhcp_dns_auto").checked 	= false;
				OBJ("ipv6_dhcp_dns_manual").checked = true;
				OBJ("ipv6_dhcp_pdns").disabled = OBJ("ipv6_dhcp_sdns").disabled = false;
				OBJ("ipv6_dhcp_pdns").value	= XG(this.inetp_v6+"/ipv6/dns/entry:1");
				if(cnt > 1)	OBJ("ipv6_dhcp_sdns").value = XG(this.inetp_v6+"/ipv6/dns/entry:2");
			}	
			OBJ("ipv6_dhcp_body").style.display = "block";
			OBJ("ipv6_static_body").style.display =	OBJ("ipv6_linklocal_body").style.display = "none";			
		}		
	}	
	else
		{
			OBJ("ipv6_conntype_body").style.display = "none";
			OBJ("ipv6_linklocal_body").style.display = "none";
			OBJ("ipv6_static_body").style.display = "none";
			OBJ("ipv6_dhcp_body").style.display = "none";
		}	
		/* For IPv6 End */

		var rebootflag = "<?echo query("/runtime/reboot/status");?>";
		if(rebootflag != "1"){OBJ("rebootitem1").style.display="none"; OBJ("rebootitem2").style.display="none";}
		else {OBJ("rebootitem1").style.display=""; OBJ("rebootitem2").style.display="";}
	
		return true;
	},
	PreSubmit: function()
	{
		if(this.IsInValidChar(OBJ("device_name").value))
		{
			BODY.ShowAlert("<?echo I18N("j", "Invalid device name");?>");
			OBJ("device_name").focus();
			return null;
		}
		if(OBJ("device_name").value.match(" ") !== null)
		{
			BODY.ShowAlert("<?echo I18N("j", "Device name format error; can not contain space.");?>");
			OBJ("device_name").focus();
			return null;
		}
		XS(this.devp+"/device/hostname", OBJ("device_name").value);
		/* For IPv4 Start */
		if(OBJ("lan_type_v4").value === "lan_static")	
		{
			XS(this.inetp_v4+"/ipv4/static", 		"1");
			XS(this.inetp_v4+"/ipv4/ipaddr", OBJ("ipaddr_v4").value);
			XS(this.inetp_v4+"/ipv4/mask", 	COMM_IPv4MASK2INT(OBJ("netmask_v4").value));
			XS(this.inetp_v4+"/ipv4/gateway",OBJ("gateway_v4").value);
								
			var cnt = 0;
			if (OBJ("dns1_v4").value !== "")
			{
				XS(this.inetp_v4+"/ipv4/dns/entry:1", OBJ("dns1_v4").value);
				cnt+=1;
				if (OBJ("dns2_v4").value !== "")
				{
					XS(this.inetp_v4+"/ipv4/dns/entry:2",OBJ("dns2_v4").value);
					cnt+=1;
				}
			}
			XS(this.inetp_v4+"/ipv4/dns/count", cnt);
			if(COMM_EqBOOL(OBJ("ipaddr_v4").getAttribute("modified"), true))	
			{
				this.ipdirty = true;
				PXML.DelayActiveModule("INET.BRIDGE-1", "1");//Run LAN service after the result of Pigwidgeon is come back. 
			}
		}
		else
		{
			XD(this.inetp_v4+"/ipv4");
			XS(this.inetp_v4+"/ipv4/static", "0");
			XS(this.inetp_v4+"/ipv4/mtu", 	"1500");
		}		
		if(OBJ("lan_type_v4").value !=  this.lan_type)
			this.ipdirty = true;
		/* For IPv4 End */
		/* For IPv6 Start */
	if(this.opmode!=="STA")
	{
		if(OBJ("ipv6_mode").value === "LL")
		{	
			XS(this.inf_ipv6+"/active",	"0");
		}
		else if(OBJ("ipv6_mode").value === "STATIC")
		{
			XS(this.inf_ipv6+"/active",	"1");
			XS(this.inetp_v6+"/ipv6/mode",		"STATIC");
			XS(this.inetp_v6+"/ipv6/ipaddr",	OBJ("ipaddr_v6").value);
			XS(this.inetp_v6+"/ipv6/prefix",	OBJ("sprefix_v6").value);
			XS(this.inetp_v6+"/ipv6/gateway",	OBJ("gateway_v6").value);
			var cnt = 0;
			if (OBJ("dns1_v6").value !== "")
			{
				XS(this.inetp_v6+"/ipv6/dns/entry:1", OBJ("dns1_v6").value);
				cnt+=1;
				if (OBJ("dns2_v6").value !== "")
				{
					XS(this.inetp_v6+"/ipv6/dns/entry:2",OBJ("dns2_v6").value);
					cnt+=1;
				}
			}
			XS(this.inetp_v6+"/ipv6/dns/count", cnt);			
		}
		else if(OBJ("ipv6_mode").value === "AUTO")
		{
			XS(this.inf_ipv6+"/active",	"1");
			
			XS(this.inetp_v6+"/ipv6/mode",	"AUTO");
			if(OBJ("ipv6_dhcp_dns_auto").checked)
			{
				XS(this.inetp_v6+"/ipv6/dns/count", "0");
				XS(this.inetp_v6+"/ipv6/dns/entry:1","");
				XS(this.inetp_v6+"/ipv6/dns/entry:2","");
			}	
			else if(OBJ("ipv6_dhcp_dns_manual").checked)
			{
				XS(this.inetp_v6+"/ipv6/dns/entry:1",	OBJ("ipv6_dhcp_pdns").value);
				if(OBJ("ipv6_dhcp_sdns").value !== "")
				{
					XS(this.inetp_v6+"/ipv6/dns/count", "2");
					XS(this.inetp_v6+"/ipv6/dns/entry:2",	OBJ("ipv6_dhcp_sdns").value);
				}	
				else	XS(this.inetp_v6+"/ipv6/dns/count", "1");		
			}
		}
	}
		/* For IPv6 End */
		return PXML.doc;
	},
	IsDirty: null,
	Synchronize: function() {},
	// The above are MUST HAVE methods ...
	///////////////////////////////////////////////////////////////////////
	inetp_v4: null,
	inetp_v6: null,
	inf_ipv6: null,
	inetp_v6_ll: null,
	rphyp_v6_ll: null,
	devp: null,
	ipdirty: false,
	lan_type: null,
	OnChangeLANType: function(lantype)
	{
		if(lantype === "lan_static")	OBJ("ipv4_conn_type").style.display	= "block";
		else	OBJ("ipv4_conn_type").style.display	= "none";		
	},
	OnChange_ipv6_mode: function(ipv6_mode)
	{
		if(ipv6_mode === "LL")	
		{
			OBJ("ipv6_linklocal_body").style.display = "block";
			OBJ("ipv6_static_body").style.display =	OBJ("ipv6_dhcp_body").style.display = "none";
		}
		else if(ipv6_mode === "STATIC")
		{
			OBJ("ipv6_static_body").style.display = "block";
			OBJ("ipv6_linklocal_body").style.display =	OBJ("ipv6_dhcp_body").style.display = "none";			
		}		
		else
		{
			OBJ("ipv6_dhcp_body").style.display = "block";
			OBJ("ipv6_linklocal_body").style.display =	OBJ("ipv6_static_body").style.display = "none";			
		}	
	},	
	OnClickIPv6DHCPDNS: function(ipv6_dns_set)
	{
		if(ipv6_dns_set === "auto")	
		{
			OBJ("ipv6_dhcp_dns_manual").checked = false;
			OBJ("ipv6_dhcp_pdns").disabled = OBJ("ipv6_dhcp_sdns").disabled = true;
		}
		else
		{
			OBJ("ipv6_dhcp_dns_auto").checked = false;
			OBJ("ipv6_dhcp_pdns").disabled = OBJ("ipv6_dhcp_sdns").disabled = false;
		}		
	},
	IsInValidChar: function(str)
	{
		var character_set = "`~!@#$%^&*()=+_[]{}\|;:.¡¦¡¨,<>/?";
		var c;
		for (i = 0; i < str.length; i++)
		{
			c= str.charAt(i);
			if(character_set.indexOf(c) != -1)
			{
				return true;
			}
		}
		return false;
	}	
}

function SetDelayTime(millis)
{
	var date = new Date();
	var curDate = null;
	curDate = new Date();
	do { curDate = new Date(); }
	while(curDate-date < millis);
}

</script>
