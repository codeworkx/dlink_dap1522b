<?
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";

echo "#!/bin/sh\n";

//we now have $UID from outside 
if($UID=="")
{
	TRACE_error("wpsevents.php error : $UID is not specified.");	
	echo "exit 0\n";
}

$p1 = XNODE_getpathbytarget("", "phyinf", "uid", $UID, 0);
if ($p1=="") {echo "exit 0\n";}
$wifi1 = XNODE_getpathbytarget("/wifi", "entry", "uid", query($p1."/wifi"),0);

if($TYPE == "WPS")
{
	$authtype = query($wifi1."/authtype");
	if($authtype=="WPA" || $authtype=="WPA2" || $authtype=="WPA+2") { $eap = 1; }
	//we add more checking. If we are EAP (radius server, disable WPS)
	if($eap==1) 
	{ 
		echo "event WPSPIN flush\n";
		echo "event WPSPBC.PUSH flush\n";
		echo "exit 0\n";
		return;
	}
}

$wps = 0;
if (query($p1."/active")==1 && query($wifi1."/wps/enable")==1) {$wps++;}

if ($ACTION == "ADD")
{
	/* Someone uses wps, so add the events for WPS. */
	if($TYPE == "PBC5")
	{
		$switchmode = fread("","var/run/DO_WPS_PB5");
		$switchmode = strip($switchmode);
		if($switchmode == "")
		{
			if($wps > 0)
			{
				echo 'event WPSPB5.START add "/etc/scripts/wps.sh pb5 '.$UID.'"\n';
			}
		}
		else
		{
			echo "event WPSPB5.DONE add \"/etc/scripts/setswitch.sh ".$switchmode.";rm -f /var/run/DO_WPS_PB5;\"\n";
		}
	}
	else if($TYPE == "WPS")
	{
		if ($wps > 0)
		{
			echo 'event WPSPIN add "/etc/scripts/wps.sh pin '.$UID.'"\n';
			echo 'event WPSPBC.PUSH add "/etc/scripts/wps.sh pbc '.$UID.'"\n';
		}
	}
}
else if ($ACTION == "FLUSH")
{
	if($TYPE == "PBC5")
	{
		echo 'event WPSPB5.START flush\n';
		echo 'event WPSPB5.DONE	flush\n';
	}
	else if($TYPE == "WPS")
	{
		echo "event WPSPIN flush\n";
		echo "event WPSPBC.PUSH flush\n";
	}
}
echo "exit 0\n";
?>
