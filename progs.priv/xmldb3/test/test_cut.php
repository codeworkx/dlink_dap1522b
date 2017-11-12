<?
$test1 = "string";
$test2 = "test,string";
$test3 = "test,string,token";

$a = cut("","",",");
if ($a == "") echo "PASS!\n"; else echo "FAIL!\n";
$a = cut($test1, "0", ",");
if ($a == "string") echo "PASS!!\n"; else echo "FAIL!!\n";
$a = cut($test2, "0", ",");
if ($a == "test") echo "PASS!!!\n"; else echo "FAIL!!!\n";
$a = cut($test2, "1", ",");
if ($a == "string") echo "PASS!!!!\n"; else echo "FAIL!!!!\n";
$a = cut($test3, "5", ",");
if ($a == "") echo "PASS!!!!!\n"; else echo "FAIL!!!!!\n";

if (cut_count("", "")=="0") echo "PASS!\n"; else echo "FAIL!\n";
if (cut_count($test1, ",")==1) echo "PASS!!\n"; else echo "FAIL!!\n";
if (cut_count($test2, ",")==2) echo "PASS!!!\n"; else echo "FAIL!!!\n";
if (cut_count($test3, ",")==3) echo "PASS!!!!\n"; else echo "FAIL!!!!\n";

?>
