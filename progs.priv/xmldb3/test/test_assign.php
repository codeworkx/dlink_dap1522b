PHP: testing assign ...
===============================
<?
$var0 = "abc.php";
$var1 = "abc.def.ghi";
$var2 = "123.456".$var0;
$var3 = "test ".$var0." test!";
$var4 = "test ".$var0." &nbsp;";
$var5 = 123;
$var5++;
$var5 = $var5/3;

$fail = 0;

if ($var0 != "abc.php") echo "FAIL!\n"; else echo "PASS!\n";
if ($var1 != "abc.def.ghi") echo "FAIL!!\n"; else echo "PASS!!\n";
if ($var2 != "123.456abc.php" ) echo "FAIL!!!\n"; else echo "PASS!!!\n";
if ($var3 != "test abc.php test!") echo "FAIL!!!!\n"; else echo "PASS!!!!\n";
if ($var4 != "test abc.php &nbsp;") echo "FAIL!!!!!\n"; else echo "PASS!!!!!\n";
if ($var5 != 41) echo "FAIL!!!!!!\n"; else echo "PASS!!!!!!\n";

$var5--;
if ($var5 != 40) echo "FAIL!!!!!!!\n"; else echo "PASS!!!!!!!\n";

$hostid1=3232235521;
$hostid2=3232235620;
$delta = $hostid2 - $hostid1;
if ($delta == "99") echo "PASS!!!!!!!!\n"; else echo "FAIL!!!!!!!!\n";

?>===============================
