<?
$test = "1234567890";
$a = charcodeat($test, 0);
if ($a == "1") echo "PASS!\n"; else echo "FAIL!\n";
if (charcodeat($test, 9)=="0") echo "PASS!!\n"; else echo "FAIL!!\n";
$a = charcodeat($test, 11);
if ($a == "") echo "PASS!!!\n"; else echo "FAL!!!\n";
?>
