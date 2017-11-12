<? /* vi: set sw=4 ts=4: */
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/phyinf.php";
include "/htdocs/phplib/xnode.php";

function Addigmpsnoop($ifname, $infp)
{
	$phyinf = query($infp."/phyinf");
	$brname = query($infp."/devnam");
	if($brname!="")
{
	fwrite("a",$_GLOBALS["START"],
		'echo enable > /proc/net/br_igmpp_'.$brname.'\n'.
		'echo enable > /proc/net/br_mac_'.$brname.'\n'
		  );
	fwrite("a",$_GLOBALS["STOP"],
				'echo disable > /proc/net/br_igmpp_'.$brname.'\n'.
				'echo disable > /proc/net/br_mac_'.$brname.'\n'
		  );
	}

	if ($phyinf=="") continue;
	$phyp = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $phyinf, 0);
	if ($phyp=="") continue;
	foreach ($phyp."/bridge/port") 
	{
		if ($VaLuE!="")
		{
			$s = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $VaLuE, 0);
			if($s != "")
			{
			$wlaninf = query($s."/name");
			fwrite("a",$_GLOBALS["START"],	'echo "setwl '.$wlaninf.'" > /proc/net/br_igmpp_'.$brname.'\n');
		}
	}
}
}

$igmpsnooping = query("/device/multicast/igmpsnooping");
$layout = query("/runtime/device/switchmode");

fwrite("w",$START, "#!/bin/sh\n");
fwrite("w",$STOP,  "#!/bin/sh\n");
if($layout != "APCLI") 
{ 	
	if ($igmpsnooping == "1")
	{				
		$ifname = "BRIDGE-1";
		$p = XNODE_getpathbytarget("/runtime", "inf", "uid", $ifname, 0);
		if($p!="")	
		{
			Addigmpsnoop($ifname, $p);
		}
		else 
		{ 
			TRACE_error(" Addigmpsnoop failed. Can't find ".$ifname); 
		}		
	}
}

fwrite("a",$START, "exit 0\n");
fwrite("a",$STOP,  "exit 0\n");
?>
