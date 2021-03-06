<?/* vi: set sw=4 st=4: */
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/wifi.php";
include "/htdocs/phplib/trace.php";

function wds_setcfg($from, $to)
{
	$active = query($from."/active");
	if ($active != "")	set ($to."/active",	$active);
	set($to."/media/peermac",	query($from."/media/peermac"));
}
function phyinf_setcfg($prefix_phyinf, $minor)
{
	/* phyinf */
	$wifi   = XNODE_getpathbytarget("/wifi", "entry", "uid", query($prefix_phyinf."/wifi"), 0);
	$opmode = query($wifi."/opmode");
	anchor($prefix_phyinf);

	$uid = query("uid");
	$to = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	if ($to == "") 
	{
		TRACE_error("wifi_setcfg: CAN NOT find the path of /phyinf/uid =".$uid);
		return;
	}
	$freq = query("media/freq");
	set($to."/active", query("active"));
	set($to."/wifi", query("wifi"));
	set($to."/schedule", query("schedule"));
	
	if($opmode=="AP")
	{	
		/* The physical device will have the minor 1, greater than 1 are multiple BSSID. */
		if ($minor==1)
		{
			set($to."/media/e_partition",				query("media/e_partition"));
			set($to."/media/w_partition",				query("media/w_partition"));
			set($to."/media/coexistence/enable",		query("media/coexistence/enable"));
			$str_Aband = "";
			if($freq=="5")
			{
				$str_Aband = "_Aband";
			}
			$wlmode = query("media/wlmode".$str_Aband);
			set($to."/media/wlmode".$str_Aband, $wlmode);
			set($to."/media/beacon",			query("media/beacon"));
			set($to."/media/fragthresh",		query("media/fragthresh"));
			set($to."/media/rtsthresh",			query("media/rtsthresh"));
			set($to."/media/ctsmode",			query("media/ctsmode"));
			set($to."/media/maxstanum/enable",			query("media/maxstanum/enable"));
			if(query("media/maxstanum/enable") == "1")
			{
				set($to."/media/maxstanum/maxstanum",			query("media/maxstanum/maxstanum"));
			}
			
			set($to."/media/channel".$str_Aband,		  query("media/channel".$str_Aband));
			set($to."/media/dot11n/bandwidth".$str_Aband, query("media/dot11n/bandwidth".$str_Aband));
			set($to."/media/dot11n/guardinterval",	query("media/dot11n/guardinterval"));
			if (strchr($wlmode,"n")!="")
			{
				set($to."/media/dot11n/mcs/auto",	query("media/dot11n/mcs/auto"));
				set($to."/media/dot11n/mcs/index",	query("media/dot11n/mcs/index"));
				set($to."/media/txrate",			query("media/txrate"));
			}
			else
			{
				set($to."/media/txrate",			query("media/txrate"));
			}
			set($to."/media/txpower",			query("media/txpower"));
			set($to."/media/preamble",			query("media/preamble"));
			set($to."/media/dtim",				query("media/dtim"));
			set($to."/media/wmm/enable",		query("media/wmm/enable"));
			if (query("media/wmm/acbk#")=="1")
			{
				set($to."/media/wmm/acbk/cwmin",	query("media/wmm/acbk/cwmin"));
				set($to."/media/wmm/acbk/cwmax",	query("media/wmm/acbk/cwmax"));
				set($to."/media/wmm/acbk/aifsn",	query("media/wmm/acbk/aifsn"));
				set($to."/media/wmm/acbk/txop",		query("media/wmm/acbk/txop"));
				set($to."/media/wmm/acbe/cwmin",	query("media/wmm/acbe/cwmin"));
				set($to."/media/wmm/acbe/cwmax",	query("media/wmm/acbe/cwmax"));
				set($to."/media/wmm/acbe/aifsn",	query("media/wmm/acbe/aifsn"));
				set($to."/media/wmm/acbe/txop",		query("media/wmm/acbe/txop"));
				set($to."/media/wmm/acvi/cwmin",	query("media/wmm/acvi/cwmin"));
				set($to."/media/wmm/acvi/cwmax",	query("media/wmm/acvi/cwmax"));
				set($to."/media/wmm/acvi/aifsn",	query("media/wmm/acvi/aifsn"));
				set($to."/media/wmm/acvi/txop",		query("media/wmm/acvi/txop"));
				set($to."/media/wmm/acvo/cwmin",	query("media/wmm/acvo/cwmin"));
				set($to."/media/wmm/acvo/cwmax",	query("media/wmm/acvo/cwmax"));
				set($to."/media/wmm/acvo/aifsn",	query("media/wmm/acvo/aifsn"));
				set($to."/media/wmm/acvo/txop",		query("media/wmm/acvo/txop"));
			}
		}
	} 
	else if ($opmode=="STA")
	{
		set($to."/media/dot11n/bandwidth",		query("media/dot11n/bandwidth"));
		set($to."/media/txpower",		query("media/txpower"));
	}
	else
	{
		TRACE_error("phyinf_setcfg : we don't support this \'".$opmode."\' opmode for now.");	
	}
}

function wifi_profile_setcfg($from, $to)
{
	anchor($from);
	set($to."/opmode",						query("opmode"));
	set($to."/ssid",						query("ssid"));
	set($to."/ssidhidden",					query("ssidhidden"));
	$authtype = query("authtype");
	$encrtype = query("encrtype");
	set($to."/authtype",					$authtype);
	set($to."/encrtype",					$encrtype);
	if ($encrtype == "WEP")
	{
		$ascii	= query("nwkey/wep/ascii");
		$size	= query("nwkey/wep/size");
		$defkey	= query("nwkey/wep/defkey");
		$key	= query("nwkey/wep/key:".$defkey);
		if ($ascii == "" ||$size == "")
		{
			$len = strlen($key);
			if		($len == 5)		{$ascii = 1;	$size = 64;}
			else if ($len == 13)	{$ascii = 1;	$size = 128;}
			else if ($len == 10)	{$ascii = 0;	$size = 64;}
			else if ($len == 26)	{$ascii = 0;	$size = 128;}
		}

		set($to."/nwkey/wep/size",			$size);
		set($to."/nwkey/wep/ascii",			$ascii);
		set($to."/nwkey/wep/defkey",		$defkey);
		set($to."/nwkey/wep/key:1",			query("nwkey/wep/key:1"));
		set($to."/nwkey/wep/key:2",			query("nwkey/wep/key:2"));
		set($to."/nwkey/wep/key:3",			query("nwkey/wep/key:3"));
		set($to."/nwkey/wep/key:4",			query("nwkey/wep/key:4"));
	}
	else if ($authtype == "WPA" || $authtype == "WPA2" || $authtype == "WPA+2")
	{
		TRACE_error("wifi_setcfg: radius config.");
		set($to."/nwkey/eap/radius",		query("nwkey/eap/radius"));
		set($to."/nwkey/eap/port",			query("nwkey/eap/port"));
		set($to."/nwkey/eap/secret",		query("nwkey/eap/secret"));
	}
	else if ($authtype == "WAPI")
	{
		TRACE_error("wifi_setcfg: wapias config.");
		set($to."/nwkey/wapi/as",        query("nwkey/wapi/as"));
		set($to."/nwkey/wapi/port",      query("nwkey/wapi/port"));
	}
	else if ($authtype == "WPAPSK" || $authtype == "WPA2PSK" || $authtype == "WPA+2PSK" || $authtype == "WAPIPSK")
	{
		$passphrase 		= query("nwkey/psk/passphrase");
		$key				= query("nwkey/psk/key");
		$rekey_gtk_interval		= query("nwkey/rekey/gtk");
		if ($passphrase == "")
		{
			if (strlen($key) == 64) $passphrase = 0;
			else					$passphrase = 1;
		}
		set($to."/nwkey/psk/passphrase",	$passphrase);
		set($to."/nwkey/psk/key",			$key);
		if ($rekey_gtk_interval!="") {set($to."/nwkey/rekey/gtk", 	$rekey_gtk_interval);}
	}
	set($to."/wps/enable",					query("wps/enable"));
	set($to."/wps/pin",						query("wps/pin"));
	set($to."/wps/configured",				query("wps/configured"));
	if(query("acl/policy") != "")
	{
		movc($from."/acl", $to."/acl");
	}
}

function wifi_setcfg($prefix)
{
	foreach($prefix."/phyinf")
	{
		if (query("type")!="wifi") continue;

		$uid = query("uid");
		$pfx = cut($uid, 0, '-');
		$idx = cut($uid, 1, '-');
		$major = cut($idx, 0, '.');
		$minor = cut($idx, 1, '.');

		TRACE_debug("SETCFG: uid=".$uid.", pfx=".$pfx.", idx=".$idx.", maj/min=".$major."/".$minor);
		phyinf_setcfg($prefix."/phyinf:".$InDeX, $minor);

		if ($pfx=="WDS24G" || $pfx=="WDS5G")
		{
			$from = $prefix."/phyinf:".$InDeX;
			$to = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0); 
			if ($to == "")
			{
				TRACE_error("wifi_setcfg: CAN NOT find the path of /phyinf/uid =".$wds_uid);
				return;
			}
			wds_setcfg($from, $to);
		}

		/* wifi */
		$wifi = query("wifi");
		$from = XNODE_getpathbytarget($prefix."/wifi", "entry", "uid", $wifi, 0);
		$to   = XNODE_getpathbytarget("/wifi", "entry", "uid", $wifi, 0);
		if ($from=="" || $to=="")
		{	
			TRACE_error("wifi_setcfg: Can not find wifi profile - ".$wifi.", from=".$from.", to=".$to);
			return;
		}
		wifi_profile_setcfg($from, $to);
	}
}
?>
