Test php variable assignment by xmldbc -V.
<?
/* xmldbc -V testing. */
echo "var1 = [".$var1."]\n";
echo "var2 = [".$var2."]\n";
echo "var3 = [".$var3."]\n";
echo "var4 = [".$var4."]\n";
echo "var5 = [".$var5."]\n";
echo "var6 = [".$var6."]\n";

/* indirect variable testing */
$i = 0;
while ($i < 10)
{
		$varname = "var_".$i;
		$$varname = "value_".$i;
		$i++;
}

$i = 0;
while ($i < 10)
{
		$varname = "var_".$i;
		echo "Test ".$i." ";
		if ($$varname == "value_".$i) echo "PASS!\n"; else echo "FAIL!\n";
		$i++;
}
?>----------------<?
$test1 = "Test 1";
$test2 = "Test 2";
?>
Test1 = <?=$test1?><?=$test2?>
Test2 = <?=$test2?><?=$test1?>
====================
