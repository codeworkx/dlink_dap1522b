<? /* vi: set sw=4 ts=4: */
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/phyinf.php";
include "/htdocs/phplib/inf.php";

function startcmd($cmd)    {fwrite(a,$_GLOBALS["START"], $cmd."\n");}
function stopcmd($cmd)     {fwrite(a,$_GLOBALS["STOP"], $cmd."\n");}


include "/etc/services/TRAFFICCTRL/lib_qos.php";
include "/etc/services/TRAFFICCTRL/lib_trafficctrl.php";
include "/etc/services/TRAFFICCTRL/lib_common.php";

function trafficctrl_error($errno)
{
	startcmd("exit ".$errno."\n");
	stopcmd( "exit ".$errno."\n");
}


function copy_trafficctrl_entry($from, $to)
{
	
}

function trafficctrl_setup($name)
{
  	$mode = query("/runtime/device/switchmode");
	if($mode != "AP2G" && $mode != "AP5G")
	{
			startcmd("echo traffic control : Only support for AP mode.");
			stopcmd("echo traffic control : Only support for AP mode.");
			trafficctrl_error("9");
			return;
	}
        
    startcmd("echo ".$name." Start Qos and Traffic Control system ...\n");

    $infp = XNODE_getpathbytarget("", "inf", "uid", $name, 0);
    if ($infp=="")
	{
		SHELL_info($_GLOBALS["START"], "trafficctrl_setup: (".$name.") no interface.");
		SHELL_info($_GLOBALS["STOP"],  "trafficctrl_setup: (".$name.") no interface.");
		trafficctrl_error("9");
		return;
	}
	/* Is this interface active ? */
	$active	= query($infp."/active");
	$trafficctrls	= query($infp."/trafficctrl");
	if ($active!="1" || $trafficctrls == "")
	{
		SHELL_info($_GLOBALS["START"], "trafficctrl_setup: (".$name.") not active.");
		SHELL_info($_GLOBALS["STOP"],  "trafficctrl_setup: (".$name.") not active.");
		trafficctrl_error("8");
		return;
	}
	
	/* Get the profile */
	$trafficctrlp = XNODE_getpathbytarget("/trafficctrl", "entry", "uid", $trafficctrls, 0);
	if ($trafficctrlp=="")
	{
		SHELL_info($_GLOBALS["START"], "trafficctrl_setup: (".$name.") no profile.");
		SHELL_info($_GLOBALS["STOP"],  "trafficctrl_setup: (".$name.") no profile.");
		trafficctrl_error("9");
		return;
	}
	
	start_setup($trafficctrlp);
	stop_setup($trafficctrlp);
}


?>
