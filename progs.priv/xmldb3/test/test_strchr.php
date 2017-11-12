<?
$a = "0123456789abcdef";

if (strchr($a, "0")=="0")	echo "PASS!\n"; else echo "FAIL!\n";
if (strchr($a, "b")=="11")	echo "PASS!!\n"; else echo "FAIL!!\n";
if (strchr("", "")=="")		echo "PASS!!!\n"; else echo "FAIL!!!\n";
if (strchr($a, "g")=="")	echo "PASS!!!!\n"; else echo "FAIL!!!!\n";

if (strlen($a) == "16")		echo "PASS!\n"; else echo "FAIL!\n";
if (strlen("") == "0")		echo "PASS!!\n"; else echo "FAIL!!\n";

if (strtoul("0x10","16")=="16")	echo "PASS!\n"; else echo "FAIL!\n";
if (strtoul("10","16")=="16")	echo "PASS!!\n"; else echo "FAIL!!\n";
if (strtoul("10","10")=="10")	echo "PASS!!!\n"; else echo "FAIL!!!\n";

$i = strchr($a, "zxcv");
if ($i != "")					echo "PASS!\n"; else echo "FAIL!\n";
if (charcodeat($a, $i)=="c")	echo "PASS!!\n"; else echo "FAIL!!\n";

?>
