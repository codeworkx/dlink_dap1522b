<?
$ipaddr = "192.168.0.20";
$mask = "24";
$network = ipv4networkid($ipaddr, $mask);
$hostid  = ipv4hostid($ipaddr, $mask);
if ($network=="192.168.0.0") echo "PASS!\n"; else echo "FAIL!\n";
if ($hostid=="20") echo "PASS!\n"; else echo "FAIL!\n";

$ipaddr = "192.168.0.245";
$mask = "28";
$network = ipv4networkid($ipaddr, $mask);
$hostid  = ipv4hostid($ipaddr, $mask);
if ($network=="192.168.0.240") echo "PASS!\n"; else echo "FAIL!\n";
if ($hostid=="5") echo "PASS!\n"; else echo "FAIL!\n";

$ipaddr = "10.255.20.20";
$mask = "8";
$network = ipv4networkid($ipaddr, $mask);
$hostid  = ipv4hostid($ipaddr, $mask);
if ($network=="10.0.0.0") echo "PASS!\n"; else echo "FAIL!\n";
if ($hostid=="16716820") echo "PASS!\n"; else echo "FAIL!\n";

$mask = ipv4int2mask("8");	if ($mask=="255.0.0.0") echo "PASS!\n"; else echo "FAIL!\n";
$mask = ipv4int2mask("16");	if ($mask=="255.255.0.0") echo "PASS!\n"; else echo "FAIL!\n";
$mask = ipv4int2mask("24");	if ($mask=="255.255.255.0") echo "PASS!\n"; else echo "FAIL!\n";
$mask = ipv4int2mask("32");	if ($mask=="255.255.255.255") echo "PASS!\n"; else echo "FAIL!\n";
$mask = ipv4int2mask("28");	if ($mask=="255.255.255.240") echo "PASS!\n"; else echo "FAIL!\n";

$mask = ipv4mask2int("255.0.0.0");			if ($mask=="8") echo "PASS!\n"; else echo "FAIL!\n";
$mask = ipv4mask2int("255.255.0.0");		if ($mask=="16") echo "PASS!\n"; else echo "FAIL!\n";
$mask = ipv4mask2int("255.255.255.0");		if ($mask=="24") echo "PASS!\n"; else echo "FAIL!\n";
$mask = ipv4mask2int("255.255.255.255");	if ($mask=="32") echo "PASS!\n"; else echo "FAIL!\n";
$mask = ipv4mask2int("255.255.255.240");	if ($mask=="28") echo "PASS!\n"; else echo "FAIL!\n";
$mask = ipv4mask2int("255.255.123.0");		if ($mask=="") echo "PASS!\n"; else echo "FAIL!\n";

$ipaddr = ipv4ip("192.168.20.0", "24", "20");	if ($ipaddr == "192.168.20.20") echo "PASS!\n"; else echo "FAIL!\n";
$ipaddr = ipv4ip("192.168.257.0", "24", "20");	if ($ipaddr == "") echo "PASS!\n"; else echo "FAIL!\n";
$ipaddr = ipv4ip("10.0.0.0", "8", "16716820");	if ($ipaddr == "10.255.20.20") echo "PASS!\n"; else echo "FAIL!\n";

$maxhost = ipv4maxhost("24");	if ($maxhost == "255") echo "PASS!\n"; else echo "FAIL!\n";
$maxhost = ipv4maxhost("28");	if ($maxhost == "15") echo "PASS!\n"; else echo "FAIL!\n";
?>
