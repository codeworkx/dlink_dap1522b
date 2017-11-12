<?
$str1 = " ABCD,EDGH ";
$str2 = "ABCDEFG,GFEDCBA\t\t\n";
$str3 = "  ABCDEFG  ABCDEFG  ";

if (strip($str1)=="ABCD,EDGH")			echo "PASS!"; else echo "FAIL!";		echo "\n";
if (strip($str2)=="ABCDEFG,GFEDCBA")	echo "PASS!!"; else echo "FAIL!!";		echo "\n";
if (strip($str3)=="ABCDEFG  ABCDEFG")	echo "PASS!!!"; else echo "FAIL!!!";	echo "\n";
?>
