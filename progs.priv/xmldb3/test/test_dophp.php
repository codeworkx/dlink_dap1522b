<?

$var1 = "Hello";

$ret = dophp("fork", "test/test_dophp_set.php");
echo "var1=".$var1.", var2=".$var2."\n";
if ($ret == "") echo "PASS!\n"; else echo "FAIL!\n";
if ($var1 == "Hello") echo "PASS!!\n"; else echo "FAIL!!\n";
if ($var2 == "") echo "PASS!!!\n"; else echo "FAIL!!!\n";

$ret = dophp("load", "test/test_dophp_set.php");
echo "var1=".$var1.", var2=".$var2."\n";
if ($ret == "") echo "PASS!\n"; else echo "FAIL!\n";
if ($var1 != "Hello") echo "PASS!!\n"; else echo "FAIL!!\n";
if ($var2 == "Hello") echo "PASS!!!\n"; else echo "FAIL!!!\n";


?>
