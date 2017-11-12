<?
$a = "abcdefghijk";

if (substr($a,0,3)=="abc")			echo "PASS!\n"; else echo "FAIL!\n";
if (substr($a,3,"")=="defghijk")	echo "PASS!!\n"; else echo "FAIL!!\n";
if (substr($a,4,4)=="efgh")			echo "PASS!!!\n"; else echo "FAIL!!!\n";
if (substr($a,8,5)=="ijk")			echo "PASS!!!!\n"; else echo "FAIL!!!!\n";

?>
