<? /* vi: set sw=4 ts=4: */
/********************************************************************************
 *	NOTE: 
 *		The commands in this configuration generator is based on 
 *		Ralink RT2860 Linux SoftAP Drv1.9.0.0 Release Note and User's Guide.	 
 *		Package Name : rt2860v2_SDK3100_v1900.tar.bz2
 *******************************************************************************/
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";

/**********************************************************************************/
/* prepare the needed path */
if ($PHY_UID == "") {$PHY_UID="WLAN-2";}
$phy	= XNODE_getpathbytarget("",			"phyinf", "uid", $PHY_UID);
$phyrp	= XNODE_getpathbytarget("/runtime",	"phyinf", "uid", $PHY_UID);
$wifi	= XNODE_getpathbytarget("/wifi",	"entry",  "uid", query($phy."/wifi"));

/* ----------------------------- get configuration -----------------------------------*/
/* country code */
$ccode = query("/runtime/devdata/countrycode");
if (isdigit($ccode)==1)
{
	TRACE_debug("PHYINF.WIFI service [rtcfg.php (ralink conf)]:".
				"Your country code (".$ccode.") is in number format. ".
				"Please change the country code as ISO name. ".
				"Use 'US' as country code.");
	$ccode = "US";
}
if ($ccode == "")
{
	TRACE_error("PHYINF.WIFI service: no country code! ".
				"Please check the initial value of this board! ".
				"Use 'US' as country code.");
	$ccode = "US";
}

/* we know that GB = EU, but driver doesn't recognize EU. */
if ($ccode == "EU")
{
	TRACE_error("Country code is set to EU. Change it to GB so that driver can recognize it\n");
	$ccode = "GB";
}

$RDRegion = "FCC";

if		($ccode == "JP") {$a_region = 9; $c_region = 1; $RDRegion = "JAP";}
else if	($ccode == "GB") {$a_region = 1; $c_region = 1; $RDRegion = "CE";}
else if ($ccode == "KR") {$a_region = 5; $c_region = 1; $RDRegion = "CE";}
else if ($ccode == "CA") {$a_region = 9; $c_region = 0;}
else if ($ccode == "AU") {$a_region = 9; $c_region = 1;}
else if ($ccode == "CN") {$a_region = 4; $c_region = 1;}
else if ($ccode == "IL") {$a_region = 2; $c_region = 1;}
else if ($ccode == "LA") {$a_region = 5; $c_region = 0;}
else if ($ccode == "TW") {$a_region = 23; $c_region = 0;}
/* use 'US' as default value of $ccode. */
else {$a_region = 0;	$c_region = 0;}

/* -------- RT2860AP.dat -------*/
echo "Default"."\n";	/* The word of "Default" must not be removed. */

if ($c_region != "") {echo "CountryRegion=".$c_region."\n";}
if ($a_region != "") {echo "CountryRegionABand=".$a_region."\n";}
echo "CountryCode=".$ccode."\n";
echo "RDRegion=".$RDRegion."\n";
echo "NetworkType=Infra\n";
echo "WirelessMode=10\n";//as station, we support A band and G band as default. //PHY_11AGN_MIXED

//MAC clone
$macclone_type =query($phy."/macclone/type");
if($macclone_type != "DISABLED")
{
	if($macclone_type == "MANUAL")
	{
		$macclone_addr =query($phy."/macclone/macaddr");
		echo "EthConvertMode=hybrid\n";
		echo "EthCloneMac=".$macclone_addr."\n";
	}
	else
	{
		echo "EthConvertMode=hybrid\n";
		echo "EthCloneMac=FF:FF:FF:FF:FF:FF\n";
	}
}
else
{
	echo "EthConvertMode=Dongle\n";
}

/* authtype && encrtype */
$auth = query($wifi."/authtype");
$encrypt = query($wifi."/encrtype");

/* authtype */
if		($auth == "OPEN") { if($encrypt == "WEP" ) {$authtype = "WEPAUTO";} else {$authtype = "OPEN";}}
else if ($auth == "SHARED") {$authtype = "SHARED";}
else if ($auth == "BOTH") {$authtype = "WEPAUTO";}
//else if ($auth == "WPA") {$authtype = "WPA";}
else if ($auth == "WPAPSK") {$authtype = "WPAPSK";}
//else if ($auth == "WPA2") {$authtype = "WPA2";}
else if ($auth == "WPA2PSK") {$authtype = "WPA2PSK";}
//else if ($auth == "WPA+2") {$authtype = "WPA1WPA2";}
else if ($auth == "WPA+2PSK") {$authtype = "WPAPSKWPA2PSK";}
else 
{
	TRACE_error("hendry : unknown authtype ".$auth);
}

/* encrtype */
if		($encrypt == "NONE") {$encrtype = "NONE";}
else if ($encrypt == "WEP") {$encrtype = "WEP";}
else if ($encrypt == "TKIP") {$encrtype = "TKIP";}
else if ($encrypt == "AES") {$encrtype = "AES";}
else if ($encrypt == "TKIP+AES") {$encrtype = "TKIPAES";}
else 
{
	TRACE_error("hendry : unknown encrypt ".$encrtype);	
}

echo "AuthMode=".$authtype."\n";
echo "EncrypType=".$encrtype."\n";

if ($encrypt == "WEP")
{
	$def = query($wifi."/nwkey/wep/defkey");
	$defkeyid = $def;
	$wepkeytp = query($wifi."/nwkey/wep/ascii");
	echo "Key".$def."Str=".query($wifi."/nwkey/wep/key:".$def)."\n";
	echo "Key".$def."Type=".$wepkeytp."\n";
	echo "DefaultKeyID=".$defkeyid	."\n";
}
if ($authtype=="WPAPSK" || $authtype=="WPA2PSK" || $authtype=="WPAPSKWPA2PSK")
{
	echo "WPAPSK=".query($wifi."/nwkey/psk/key")."\n";
}

if (query($phy."/media/dot11n/bandwidth") == "20") {$bw = 0;}
else											   {$bw = 1;}

echo "HT_BW=".$bw."\n";
echo "SSID=".query($wifi."/ssid")."\n";
$rtsthresh	= query($phy."/media/rtsthresh");
$fragthresh	= query($phy."/media/fragthresh");
$guardinterval	= query($phy."/media/guardinterval");
if ($guardinterval != "400" && $guardinterval != "800" ) {$guardinterval ="400";}

echo "BeaconPeriod=100
TxPower=100
BGProtection=0
TxPreamble=0
TxBurst=1
Channel=0
PktAggregate=0
WmmCapable=1
AckPolicy=0;0;0;0
PSMode=CAM
AutoRoaming=0
RoamThreshold=70
APSDCapable=0
APSDAC=0;0;0;0
HT_RDG=1
HT_OpMode=0
HT_MpduDensity=4
HT_AutoBA=1
HT_BADecline=0
HT_AMSDU=0
HT_BAWinSize=64
HT_MCS=33
HT_MIMOPSMode=3
IEEE80211H=1
TGnWifiTest=0
WirelessEvent=0
MeshId=MESH
MeshAutoLink=1
MeshAuthMode=OPEN
MeshEncrypType=NONE
MeshWPAKEY=
MeshDefaultkey=1
MeshWEPKEY=
CarrierDetect=0
AntDiversity=0
BeaconLostTime=4
FtSupport=0
Wapiifname=ra0
WapiPsk=
WapiPskType=
WapiUserCertPath=
WapiAsCertPath=
PSP_XLINK_MODE=0
WscManufacturer=
WscModelName=
WscDeviceName=
WscModelNumber=
WscSerialNumber="."\n";

/*
Note:
We need to assign 0 to HT_DisallowTKIP parameter. If it is assigned 1 and bridge set WEP or TKIP,
then it will cause wps failure when root ap use n mode only. This should be a bug in ralink driver.
by Freddy 
*/
echo "HT_DisallowTKIP=0\n";

/* STBC */
if ( query($phy."/media/stbc") == "1" )         {echo "HT_STBC=1\n";}
else					        				{echo "HT_STBC=0\n";}
/* videoturbine */
if ( query($phy."/media/videoturbine") == "1")	{echo "VideoTurbine=1\n";}
else											{echo "VideoTurbine=0\n";}

if ($guardinterval == "400")		{$sgi = 1;}
else								{$sgi = 0;}

/* Ralink's recommendation: Remember modify the TxStream and RxStream according the board supported. */
echo "HT_TxStream=2\n";
echo "HT_RxStream=2\n";
echo "RTSThreshold=".$rtsthresh."\n";
echo "FragThreshold=".$fragthresh."\n";
echo "HT_GI=".$sgi."\n";
/*
Note:
we set as indoor. The parameter must be added. 
if we remove it, then it would be unable to hear some beacons on some channels.
*/
echo "ChannelGeography=1\n";
?>
