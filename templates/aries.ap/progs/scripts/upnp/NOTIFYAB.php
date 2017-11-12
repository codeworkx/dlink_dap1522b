#!/bin/sh
<?/* vi : set sw=4 ts=4: */
include "/etc/scripts/upnp/ssdp.php";

$path = "/runtime/upnp/dev";
foreach($path)
{
	$max_age = query("maxage");	if ($max_age == "") $max_age = 1800;
	$location = "http://".$IPADDR.":".query("port")."/".query("location");
	$server = query("server");


	$dtype	= query("devdesc/device/deviceType");
	$uuid	= query("devdesc/device/UDN");

	echo "# root:".$InDeX." = [".$dtype."], [".$uuid."]\n";
	if ($dtype != "")
	{
		/* Three discovery message for root device. */
		$nt	= "upnp:rootdevice";
		$usn= $uuid."::upnp:rootdevice";
		SSDP_nt_send_reqs_by_srcip(2, $PHYINF, $max_age, $location, $server, $NTS, $nt, $usn, $IPADDR);

		$nt	= $uuid;
		$usn= $uuid;
		SSDP_nt_send_reqs_by_srcip(2, $PHYINF, $max_age, $location, $server, $NTS, $nt, $usn, $IPADDR);

		$nt	= $dtype;
		$usn= $uuid."::".$dtype;
		SSDP_nt_send_reqs_by_srcip(2, $PHYINF, $max_age, $location, $server, $NTS, $nt, $usn, $IPADDR);
		
		foreach ("devdesc/device/serviceList/service")
		{
			$stype = query("serviceType");
			echo "# service:".$InDeX." = [".$stype."]\n";
			if ($stype != "")
			{
				$nt	= $stype;
				$usn= $uuid."::".$stype;
				SSDP_nt_send_reqs_by_srcip(2, $PHYINF, $max_age, $location, $server, $NTS, $nt, $usn, $IPADDR);
			}
		}

		$child_path = $path.":".$InDeX."/devdesc/device/deviceList/device";
		SSDP_nt_walk_all_devices_by_srcip($PHYINF, $child_path, $max_age, $location, $server, $NTS, $uuid, $IPADDR);
	}
}
?>
