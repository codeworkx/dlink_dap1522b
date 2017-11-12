<? /* vi: set sw=4 ts=4: */
$vendor		= query("/runtime/device/vendor");
$model		= query("/runtime/device/modelname");
$url		= query("/runtime/device/producturl");
$ver		= query("/runtime/device/firmwareversion");
$modeldesc	= query("/runtime/device/description");
$hostname	= query("/device/hostname");	// michael_lee
$sn			= "01234567";

$Genericname = query("/runtime/device/upnpmodelname");
if($Genericname == ""){ $Genericname = $model; }

/* find out the root device path. */
$pbase		= "/runtime/upnp/dev";
$i			= query($pbase."#") + 1;
$dev_root	= $pbase.":".$i;
$dtype	  	= "urn:schemas-upnp-org:device:Basic:1";		// Added for Bridge mode.
	

/********************************************************************/
/* root device: Basic Device */
/* create $dev_root */
set($dev_root, "");			anchor($dev_root);
/* set extension nodes. */
setattr("mac",  "get", "devdata get -e wanmac");
setattr("guid", "get", "genuuid -s \"".$dtype."\" -m \"".query("mac")."\"");
$udn = "uuid:".query("guid");

/* set IGD nodes. */
set("UDN",					$udn);
set("deviceType",			$dtype);
set("port",					"49152");
set("location", 			"BasicDevice.xml");
set("maxage",				"1800");
set("server",				"Linux, UPnP/1.0, ".$model." Ver ".$ver);
	
/* set the description file names */
add("xmldoc",				"BasicDevice.xml");	
	
/********************************************************************/
/* set the device description nodes */
$desc_root = $dev_root."/devdesc";

/* devdesc/specVersion */
set($desc_root."/specVersion",	"");	anchor($desc_root."/specVersion");
	set("major",				"1");
	set("minor",				"0");

/* devdesc/URLBase */
set($desc_root."/URLBase",		"");

/* devdesc/device */
/* root device */
set($desc_root."/device",		"");	anchor($desc_root."/device");
	set("deviceType",			$dtype);
	set("friendlyName",			$model);
	set("manufacturer",			$vendor);
	set("manufacturerURL",		$url);
	set("modelDescription",		$modeldesc);
	set("modelName",			$Genericname);
	set("modelNumber",			$model);
	set("modelURL",				$url);
	set("serialNumber",			$sn);
	set("UDN",					$udn);
	
/* devdesc/device/iconList */
$sub_root = $desc_root."/device/iconList/icon:1";
set($sub_root, "");				anchor($sub_root);
	set("mimetype",				"image/gif");
	set("width",				"118");
	set("height",				"119");
	set("depth",				"8");
	set("url",					"/ligd.gif");
	
/* devdesc/device/presentationURL */
/* We keep the 'presentationURL' & 'URLBase' empty here,
 * and set the real value in when 'elbox/progs.template/htdocs/upnpdevdesc/BasicDevice.xml.php' is called. */
set($desc_root."/device/presentationURL","");

?>
