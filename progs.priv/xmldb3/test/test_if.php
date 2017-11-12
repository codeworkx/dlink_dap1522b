if() test
---------
<?
$var1 = 1;
$var2 = 2;
$var3 = "-1";

if ($var1 == 1)
{
	/********************/
	/////////////////////
	echo "PASS!\n";
}
else
{
	echo "FAIL!\n";
}
if ($var1 != 1)					echo "FAIL!!\n"; else echo "PASS!!\n";
if ($var1 > 0)					echo "PASS!!!\n"; else echo "FAIL!!!\n";
if ($var1 < 2)					echo "PASS!!!!\n"; else echo "FAIL!!!!\n";
if ($var1 >=1)					echo "PASS!!!!!\n"; else echo "FAIL!!!!!\n";
if ($var1 <=1)					echo "PASS!!!!!!\n"; else echo "FAIL!!!!!!\n";
if ($var1==1 && $var2==2)		echo "PASS!!!!!!!\n"; else echo "FAIL!!!!!!!\n";
if ($var1==1 || $var2==3)		echo "PASS!!!!!!!!\n"; else echo "FAIL!!!!!!!!\n";
if ($var1 > 5)					echo "FAIL!!!!!!!!!\n"; else echo "PASS!!!!!!!!!\n";
if ($var1 == 1 && $var2 > 1)	echo "PASS!!!!!!!!!!\n"; else echo "FAIL!!!!!!!!!!\n";
if ($var1%2==0)					echo "FAIL!!!!!!!!!!!\n"; else echo "PASS!!!!!!!!!!!\n";
if ($var2%2==0)					echo "PASS!!!!!!!!!!!!\n"; else echo "FAIL!!!!!!!!!!!!\n";
if ($var3 >= 0)					echo "FAIL!!!!!!!!!!!!!\n"; else echo "PASS!!!!!!!!!!!!!\n";
?>
