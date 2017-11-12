<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/inet.php";

/* fatlady is used to validate the configuration for the specific service.
 * FATLADY_prefix was defined to the path of Session Data.
 * 3 variables should be returned for the result:
 * FATLADY_result, FATLADY_node & FATLADY_message. */
 
function set_result($result, $node, $message)
{
    $_GLOBALS["FATLADY_result"] = $result;
    $_GLOBALS["FATLADY_node"]   = $node;
    $_GLOBALS["FATLADY_message"]= $message;
}

 
function valid_mac($validMac)
{
    if ($validMac=="") return 0;

    $num = cut_count($validMac, ":");
    if ($num != 6) return 0;
    $num--;
    while ($num >= 0)
    {
        $tmpMac = cut($validMac, $num, ":");
        if (isxdigit($tmpMac) == 0) return 0;
                if (strlen($tmpMac) > 2) return 0;
        $num--;
    }
        $validMac = tolower($validMac);
        if ($validMac=="00:00:00:00:00:00" || $validMac=="ff:ff:ff:ff:ff:ff") return 0;
    return 1;
}

function check_limit($item)
{
	if($item=="")
	{
		set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol",i18n("Limit value can't be blank."));
		return false;
	}
	else 
	{
		if(isdigit($item) == "0")
		{
			set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol",i18n("Invalid limit value."));
			return false;
		}
		else if($item <"1" || $item >"100")
		{
			set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol",i18n("Invalid limit value."));
			return false;
		}
		return true;
	}
}



function check_valid_port($item1,$item2)
{

	if($item1=="")
	{
		set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol",i18n("The port field can not be blank."));
		return false;
	}
	
	else 
	{
		if(isdigit($item1)=="0")
		{
			set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol",i18n("Invalid Port value."));
			return false;
		}
		else if($item1 <"0" || $item1 >"65534")
		{
			set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol",i18n("Port value must be 0~65534."));
			return false;
		} 
	}

	if($item2=="")
	{
		set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol",i18n("The port field can not be blank."));
		return false;
	}
		
	else 
	{
		if(isdigit($item2)=="0")
		{
			set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol",i18n("Invalid Port value."));
			return false;
		}
		else if($item2 <"0" || $item2 >"65534")
		{
			set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol",i18n("Port value must be 0~65534."));
			return false;
		}
	}
			
	if($item2!="0" && $item1=="0")
	{
		set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol",i18n("Start port can't be 0."));
		return false;
	}
		
	if($item1 > $item2)
	{
		set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol",i18n("Start port can't be larger than end port."));
		return false;
	}

	return true;
}

function compare_default_and_defined_ports($port)
{
	if($port == "20"||$port == "21"||$port == "25"||$port == "53"
		||$port == "67"||$port == "68"||$port == "80"||$port == "110"
		||$port == "443"||$port == "465"||$port == "546"||$port == "547"
		||$port == "995"||$port == "3128"||$port == "8080")	
	{
		return false;
	}	
}

function compare_ports($user1_st_port,$user1_e_port,$user2_st_port,$user2_e_port,$user3_st_port,$user3_e_port,
                                        $user4_st_port,$user4_e_port)
{
	if(compare_default_and_defined_ports($user1_st_port)==false
		||compare_default_and_defined_ports($user1_e_port)==false
		||compare_default_and_defined_ports($user2_st_port)==false
		||compare_default_and_defined_ports($user2_e_port)==false
		||compare_default_and_defined_ports($user3_st_port)==false
		||compare_default_and_defined_ports($user3_e_port)==false
		||compare_default_and_defined_ports($user4_st_port)==false
		||compare_default_and_defined_ports($user4_e_port)==false)
	{
		set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol/user1/startport",i18n("Can't use default port!"));
		return false;
	}	
	/*
	if(!($user2_st_port > $user1_e_port || $user2_e_port < $user1_st_port) && $user1_e_port!="0" && $user2_e_port!="0" )
	{
		set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol/user2/startport",i18n("There can't be an overlap between the port ranges !"));
		return false;
	}
	if(!($user3_st_port > $user1_e_port || $user3_e_port < $user1_st_port$) && $user1_e_port!="0" && $user3_e_port!="0")
	{
		set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol/user3/startport",i18n("There can't be an overlap between the port ranges !"));
		return false;
	}
	if(!($user3_st_port > $user2_e_port || $user3_e_port < $user2_st_port) && $user2_e_port!="0" && $user3_e_port!="0")
	{
		set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol/user3/startport",i18n("There can't be an overlap between the port ranges !"));
		return false;
	}
	if(!($user4_st_port > $user1_e_port || $user4_e_port < $user1_st_port) && $user4_e_port!="0" && $user1_e_port!="0")
	{
		set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol/user4/startport",i18n("There can't be an overlap between the port ranges !"));
		return false;
	}
	if(!($user4_st_port > $user2_e_port || $user4_e_port < $user2_st_port) && $user4_e_port!="0" && $user2_e_port!="0")
	{
		set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol/user4/startport",i18n("There can't be an overlap between the port ranges !"));
		return false;
	}
	if(!($user4_st_port > $user3_e_port || $user4_e_port < $user3_st_port) && $user4_e_port!="0" && $user3_e_port!="0")
	{
		set_result("FAILED",$path."/trafficctrl/entry:1/qos/protocol/user4/startport",i18n("There can't be an overlap between the port ranges !"));
		return false;
	}*/
	return true;
}

function check_updnlink($downbandwidth, $upbandwidth)
{
			if(isdigit($downbandwidth)  == "0")
			{
				set_result("FAILED",$path."/trafficctrl/entry:1/updownlinkset/bandwidth/downlink",i18n("Invalid value for bandwidth!"));
				return false;
			}
			if(isdigit($upbandwidth)  == "0")
			{
				set_result("FAILED",$path."/trafficctrl/entry:1/updownlinkset/bandwidth/uplink",i18n("Invalid value for bandwidth!"));
				return false;
			}	
			$cnt = query("/trafficctrl/entry:1/trafficmgr/rule/count");
			$maxdownlink = "1";
			$maxuplink = "1";
			foreach ("/trafficctrl/entry:1/trafficmgr/rule/entry")
			{
				$comdownlink = query("downlink");
				if($comdownlink > $maxdownlink) $maxdownlink = $comdownlink;	
				$comuplink = query("uplink");
				if($comuplink > $maxuplink) $maxuplink = $comuplink;
			}
			if($downbandwidth < $maxdownlink)
			{
				set_result("FAILED",$path."/trafficctrl/entry:1/updownlinkset/bandwidth/downlink",i18n("Bandwidth should not less than maximal bandwidth for the traffic manager rules!"));
				return false;
			}
			if($upbandwidth < $maxuplink)
			{
				set_result("FAILED",$path."/trafficctrl/entry:1/updownlinkset/bandwidth/uplink",i18n("Bandwidth should not less than maximal bandwidth for the traffic manager rules!"));
				return false;
			}
}

function check_trafficctrl_setting($path)
{
	$whichsubmit = query($path."/trafficctrl/entry:1/trafficmgr/whichsubmit");
	
	if($whichsubmit == "tramgr")
	{
		$trafficmfr_enable = query($path."/trafficctrl/entry:1/trafficmgr/enable");
		if($trafficmfr_enable == "1")
		{
			$downbandwidth = query($path."/trafficctrl/entry:1/updownlinkset/bandwidth/downlink");
			$upbandwidth = query($path."/trafficctrl/entry:1/updownlinkset/bandwidth/uplink");
			if(check_updnlink($downbandwidth, $upbandwidth)==false){return "FAILED";}
		}
		return "OK";
	}
	else if($whichsubmit == "rule")
	{
		$rlt = "0";
		$cnt = query($path."/trafficctrl/entry:1/trafficmgr/rule/count");
		foreach ($path."/trafficctrl/entry:1/trafficmgr/rule/entry")
		{
			if($InDeX > $cnt) break;
		$name = query("name");
		$clientip = query("clientip");
		$clientmac = query("clientmac");
		$downlink = query("downlink");
		$uplink = query("uplink");
		//check name is blank or not
		if($name == "")
		{
			set_result("FAILED",$path."/trafficctrl/entry:1/trafficmgr/rule/entry:".$InDeX."/name",i18n("The 'Name' field can not be blank."));
			$rlt = "-1";
			break;		
		}
	
		//check ip and mac
		if($clientip == "" && $clientmac == "")
		{
			set_result("FAILED",$path."/trafficctrl/entry:1/trafficmgr/rule/entry:".$InDeX."/clientip",i18n("Please inpunt IP address or MAC address!"));
			$rlt = "-1";
			break;	
		}
		if($clientip != "")
		{
			if(INET_validv4addr($clientip)!= "1")
			{
				set_result("FAILED",$path."/trafficctrl/entry:1/trafficmgr/rule/entry:".$InDeX."/clientip",i18n("Invalid IP address!"));
				$rlt = "-1";
				break;
			}
		}
		if($clientmac != "")
		{
			if(valid_mac($clientmac) == "0")
			{
				set_result("FAILED",$path."/trafficctrl/entry:1/trafficmgr/rule/entry:".$InDeX."/clientmac",i18n("Invalid Mac address!"));
				$rlt = "-1";
				break;
			}
		}

		//check link values
		$downbandwidth = query("/trafficctrl/entry:1/updownlinkset/bandwidth/downlink");
		$upbandwidth = query("/trafficctrl/entry:1/updownlinkset/bandwidth/uplink");
		if($downlink == "" && $uplink == "")
		{
			set_result("FAILED",$path."/trafficctrl/entry:1/trafficmgr/rule/entry:".$InDeX."/downlink",i18n("Please input Downlink Speed or Uplink Speed!"));
			$rlt = "-1";
			break;
		}
		if($downlink != "")
		{
			if(isdigit($downlink) != "1")
			{
				set_result("FAILED",$path."/trafficctrl/entry:1/trafficmgr/rule/entry:".$InDeX."/downlink",i18n("Invalid value for the rate!"));
				$rlt = "-1";
				break;
			}
			else
			{
				if($downlink < "1" || $downlink > $downbandwidth)
				{
					set_result("FAILED",$path."/trafficctrl/entry:1/trafficmgr/rule/entry:".$InDeX."/downlink",i18n("The bandwidth for the traffic manager rules should not less than 1 Mbits/sec,or greater than traffic manager bandwidth!"));
					$rlt = "-1";
					break;
				}
			}
		}
		if($uplink != "")
		{
			if(isdigit($uplink) != "1")
			{
				set_result("FAILED",$path."/trafficctrl/entry:1/trafficmgr/rule/entry:".$InDeX."/uplink",i18n("Invalid value for the rate!"));
				$rlt = "-1";
				break;
			}
			else
			{
				if($uplink < "1" || $uplink > $upbandwidth)
				{
					set_result("FAILED",$path."/trafficctrl/entry:1/trafficmgr/rule/entry:".$InDeX."/uplink",i18n("The bandwidth for the traffic manager rules should not less than 1 Mbits/sec,or greater than traffic manager bandwidth!"));
					$rlt = "-1";
					break;
				}
			}
		}
		}	
		if($rlt != "0") return "FAILED";
		return "OK";
	}

	else 
	if($whichsubmit == "qos")
	{
		$qos = query($path."/trafficctrl/entry:1/qos/enable");
		if($qos == "1")
		{
			$aui_lim = query($path."/trafficctrl/entry:1/qos/protocol/aui/limit");
			$web_lim = query($path."/trafficctrl/entry:1/qos/protocol/web/limit");
			$mail_lim = query($path."/trafficctrl/entry:1/qos/protocol/mail/limit");
			$ftp_lim = query($path."/trafficctrl/entry:1/qos/protocol/ftp/limit");
			$user1_lim = query($path."/trafficctrl/entry:1/qos/protocol/user1/limit");
			$user2_lim = query($path."/trafficctrl/entry:1/qos/protocol/user2/limit");
			$user3_lim = query($path."/trafficctrl/entry:1/qos/protocol/user3/limit");
			$user4_lim = query($path."/trafficctrl/entry:1/qos/protocol/user4/limit");
			$other_lim = query($path."/trafficctrl/entry:1/qos/protocol/other/limit");
			$downbandwidth = query($path."/trafficctrl/entry:1/updownlinkset/bandwidth/downlink");
			$upbandwidth = query($path."/trafficctrl/entry:1/updownlinkset/bandwidth/uplink");
			
			if(check_updnlink($downbandwidth, $upbandwidth)==false){return "FAILED";}
			
			if(check_limit($aui_lim)==false){return "FAILED";}
			if(check_limit($web_lim)==false){return "FAILED";}
			if(check_limit($mail_lim)==false){return "FAILED";}
			if(check_limit($ftp_lim)==false){return "FAILED";}
			if(check_limit($user1_lim)==false){return "FAILED";}
			if(check_limit($user2_lim)==false){return "FAILED";}
			if(check_limit($user3_lim)==false){return "FAILED";}
			if(check_limit($user4_lim)==false){return "FAILED";}
			if(check_limit($other_lim)==false){return "FAILED";}
		
			$user1_st_port = query($path."/trafficctrl/entry:1/qos/protocol/user1/startport");
			$user1_e_port = query($path."/trafficctrl/entry:1/qos/protocol/user1/endport");
			$user2_st_port = query($path."/trafficctrl/entry:1/qos/protocol/user2/startport");
			$user2_e_port = query($path."/trafficctrl/entry:1/qos/protocol/user2/endport");
			$user3_st_port = query($path."/trafficctrl/entry:1/qos/protocol/user3/startport");
			$user3_e_port = query($path."/trafficctrl/entry:1/qos/protocol/user3/endport");
			$user4_st_port = query($path."/trafficctrl/entry:1/qos/protocol/user4/startport");
			$user4_e_port = query($path."/trafficctrl/entry:1/qos/protocol/user4/endport");		
										
			if(check_valid_port($user1_st_port,$user1_e_port)==false){return "FAILED";}
			if(check_valid_port($user2_st_port,$user2_e_port)==false){return "FAILED";}
			if(check_valid_port($user3_st_port,$user3_e_port)==false){return "FAILED";}
			if(check_valid_port($user4_st_port,$user4_e_port)==false){return "FAILED";}
	
			if(compare_ports($user1_st_port,$user1_e_port,$user2_st_port,$user2_e_port,$user3_st_port,$user3_e_port,
					$user4_st_port,$user4_e_port)==false)
			{
				return "FAILED";
			}
		}
	  return "OK";
	}

}

if(check_trafficctrl_setting($FATLADY_prefix)=="OK")
{
	set($FATLADY_prefix."/valid", "1");
	set_result("OK", "", "");
}
?>