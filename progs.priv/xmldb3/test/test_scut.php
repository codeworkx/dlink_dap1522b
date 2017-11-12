<?
$a = "  name:string   test:string1  string2	string3	\n";
//$a = "                ";
if (scut($a,2,"test:")=="string3")	echo "PASS!\n"; else echo "FAIL!\n";
if (scut($a,2,"")=="string2")		echo "PASS!!\n"; else echo "FAIL!!\n";
if (scut($a,5,"")=="")				echo "PASS!!!\n"; else echo "FAIL!!!\n";
if (scut($a,0,"target:")=="")		echo "PASS!!!!\n"; else echo "FAIL!!!!\n";

if (scut_count($a,"test:")=="3")	echo "PASS!\n"; else echo "FAIL!\n";
if (scut_count($a,"")=="4")			echo "PASS!!\n"; else echo "FAIL!!\n";
if (scut_count($a,"name:")=="4")	echo "PASS!!!\n"; else echo "FAIL!!!\n";
if (scut_count($a,"target:")=="0")	echo "PASS!!!!\n"; else echo "FAIL!!!!\n";
?>
