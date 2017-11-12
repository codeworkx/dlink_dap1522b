<?
$a = "";
$b = " ";
if (isempty($a)=="1")			echo "PASS!\n"; else echo "FAIL!\n";
if (isempty("")=="1")			echo "PASS!!\n"; else echo "FAIL!!\n";
if (isempty("adslkfj")=="0")	echo "PASS!!!\n"; else echo "FAIL!!!\n";
if (isempty($b)=="0")			echo "PASS!!!!\n"; else echo "FAIL!!!!\n";
?>
