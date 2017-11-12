<?
$ipaddr = "3ffe:501:ffff:100:21d:6aff:fe12:1015";
$prefix_length = "64";

/*
$subnet_px 	= ipv6networkid($ipaddr, $prefix_length);
$iid 		= ipv6hostid($ipaddr, $prefix_length);
if ($subnet_px=="3ffe:501:ffff:100") echo "PASS!\n"; else echo "FAIL!\n";
if ($iid=="21d:6aff:fe12:1015") echo "PASS!\n"; else echo "FAIL!\n";
*/
/* check global addrs */
$ipaddr = "3ffe:501:ffff:100:21d:6aff:fe12:1015";
if ( ipv6checkip($ipaddr)=="1" ) echo "PASS!\t"; else echo "FAIL!\t";
echo $ipaddr."\n";

/* check more than 128 bits */
$ipaddr = "3ffe:501:ffff:100:21d:6aff:fe12:1015:1111";
if ( ipv6checkip($ipaddr)=="" ) echo "PASS!\t"; else echo "FAIL!\t";
echo $ipaddr."\n";

/* check less than 128 bits */
$ipaddr = "3ffe:501:ffff:100:21d:6aff:fe12";
if ( ipv6checkip($ipaddr)=="" ) echo "PASS!\t"; else echo "FAIL!\t";
echo $ipaddr."\n";

/* check ll addrs and "::" appear once*/
$ipaddr = "fe80::21d:6aff:fe12:1015";
if ( ipv6checkip($ipaddr)=="1" ) echo "PASS!\t"; else echo "FAIL!\t";
echo $ipaddr."\n";

/* check "::" appear more than once */
$ipaddr = "fe80::21d:6aff:fe12::1015";
if ( ipv6checkip($ipaddr)=="" ) echo "PASS!\t"; else echo "FAIL!\t";
echo $ipaddr."\n";

/* check "::" appear at prefix*/
$ipaddr = "::21d:6aff:fe12:1015";
if ( ipv6checkip($ipaddr)=="1" ) echo "PASS!\t"; else echo "FAIL!\t";
echo $ipaddr."\n";

/* check unspecifiedc addr */
$ipaddr = "::";
if ( ipv6checkip($ipaddr)=="1" ) echo "PASS!\t"; else echo "FAIL!\t";
echo $ipaddr."\n";

/* check loopback addrs */
$ipaddr = "::1";
if ( ipv6checkip($ipaddr)=="1" ) echo "PASS!\t"; else echo "FAIL!\t";
echo $ipaddr."\n";

/* check ipv6 addrs with embedded ipv4 addrs */
$ipaddr = "fe80::21d:6aff:192.168.67.1";
if ( ipv6checkip($ipaddr)=="1" ) echo "PASS!\t"; else echo "FAIL!\t";
echo $ipaddr."\n";

/**********************************************************************/
/* check another ipv4 addrs with embedded ipv4 addrs,
NOTE: this format is not acceptable for now!!!, so we got FAIL !!!  */
/**********************************************************************/
$ipaddr = "fe80::21d:6aff:192.168.067.01";
if ( ipv6checkip($ipaddr)=="1" ) echo "PASS!\t"; else echo "FAIL!\t";
echo $ipaddr."\n";
/**********************************************************************/


/* check invalid ipv4 mapped ipv6 addrs */
$ipaddr = "fe80::21d:6aff:192.168.0.256";
if ( ipv6checkip($ipaddr)=="" ) echo "PASS!\t"; else echo "FAIL!\t";
echo $ipaddr."\n";

/* check network ID */
$ipaddr = "2001:dbc:ff3d:cc:123:e4f3:132:4\n\t
";
$prelen = 48;
$nid = ipv6networkid($ipaddr, $prelen);
if ($nid == "2001:dbc:ff3d::") echo "PASS!\t"; else echo "FAIL!\t";
echo $nid."\n";

/* check host ID */
$hid = ipv6hostid($ipaddr, $prelen);
if ($hid == "::cc:123:e4f3:132:4") echo "PASS!\t"; else echo "FAIL!\t";
echo $hid."\n";

/* check escape */
$ipaddr = "2001:dbc:ff3d:cc:123:e4f3:135:43\n\t
";
$nip = ipv6ip($ipaddr, 128, 0, 0, 0);
if ($nip == "2001:dbc:ff3d:cc:123:e4f3:135:43") echo "PASS!\t"; else echo "FAIL!\t";
echo $nip."\n";

/* check simplify address */
$ipaddr = "2001:dbc:ff3d:cc:0:0:0:43";
$nip = ipv6ip($ipaddr, 128, 0, 0, 0);
if ($nip == "2001:dbc:ff3d:cc::43") echo "PASS!\t"; else echo "FAIL!\t";
echo $nip."\n";

/* check SLA ID */
$ipaddr = "2001:dbc:ff3d:cc:0:0:0:43";
$nip = ipv6ip($ipaddr, 48, "a1", 43981, 16);
if ($nip == "2001:dbc:ff3d:abcd::a1") echo "PASS!\t"; else echo "FAIL!\t";
echo $nip."\n";

/* check SLA ID */
$ipaddr = "2001:dbc:ff3d:cc:0:0:0:43";
$nip = ipv6ip($ipaddr, 60, "a2", 10, 4);
if ($nip == "2001:dbc:ff3d:ca::a2") echo "PASS!\t"; else echo "FAIL!\t";
echo $nip."\n";

/* check SLA ID */
$ipaddr = "2001:dbc:ff3d:cc:0:0:0:43";
$nip = ipv6ip($ipaddr, 64, "a3", 1, 16);
if ($nip == "2001:dbc:ff3d:cc:1::a3") echo "PASS!\t"; else echo "FAIL!\t";
echo $nip."\n";

/* check SLA ID */
$ipaddr = "2001:dbc:193:c4c::";
$nip = ipv6ip($ipaddr, 64, "207:2a48:5:a4", 1, 1);
if ($nip == "2001:dbc:193:c4c:8207:2a48:5:a4") echo "PASS!\t"; else echo "FAIL!\t";
echo $nip."\n";

/* check SLA ID */
$ipaddr = "2001:dbc:193:c4c::";
$nip = ipv6ip($ipaddr, 5, "207:2a48:5:a5", 1, 1);
if ($nip == "2400::207:2a48:5:a5") echo "PASS!\t"; else echo "FAIL!\t";
echo $nip."\n";

/* check SLA ID */
$ipaddr = "2001:dbc:193:c4c::";
$nip = ipv6ip($ipaddr, 20, "207:2a48:5:a6", 1, 1);
if ($nip == "2001:800::207:2a48:5:a6") echo "PASS!\t"; else echo "FAIL!\t";
echo $nip."\n";

/* check SLA ID */
$ipaddr = "2001:238:20::";
$sla = ipv4hostid("70.80.36.99", 16);
$nip = ipv6ip($ipaddr, 44, "a7", $sla, 16);
if ($nip == "2001:238:22:4630::a7") echo "PASS!\t"; else echo "FAIL!\t";
echo $nip."\n";

/* check SLA ID */
$ipaddr = "2001:238:20::";
$nip = ipv6ip($ipaddr, 9, "a8", 1, 1);
if ($nip == "2040::a8") echo "PASS!\t"; else echo "FAIL!\t";
echo $nip."\n";
?>
