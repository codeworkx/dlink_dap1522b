while test
----------
<?

$i = 0;
$test = 0;
while ($i < 10)
{
	$test += $i;
	$i++;
}
if ($i==10)			echo "PASS!\n"; else echo "FAIL!\n";
if ($test==45)		echo "PASS!!\n"; else echo "FAIL!!\n";
?>
break test
----------
<?
$i		= 0;
$test	= 0;
while ($i < 10)
{
	if ($i == 5) break;
	$test += $i;
	$i++;
}
if ($i == 5)		echo "PASS!\n"; else echo "FAIL!\n";
if ($test == 10)	echo "PASS!!\n"; else echo "FAIL!!\n";
?>
continue test
-------------
<?
$i		= 0;
$test	= 0;
while ($i < 10)
{
	$i++;
	if ($i >= 5) continue;
	$test += $i;
}
if ($i == 10)		echo "PASS!\n"; else echo "FAIL!\n";
if ($test == 10)	echo "PASS!!\n"; else echo "FAIL!!\n";

$i		= 0;
$j		= 0;
$test	= 0;
$test1	= 0;
?>
nest break and continue test
----------------------------
<?
while ($i < 20)
{
	while ($j < 10)
	{
		if ($i > 1) break;
		$j++;
		if ($j >= 5) continue;
		$test1 += $j;
	}
	$i++;
	if ($i >= 10) break;
	if ($i >= 5) continue;
	$test += $i;
}
if ($i == 10)		echo "PASS!\n";		else echo "FAIL!\n";
if ($j == 10)		echo "PASS!!\n";	else echo "FAIL!!\n";
if ($test == 10)	echo "PASS!!!\n";	else echo "FAIL!!!\n";
if ($test1 == 10)	echo "PASS!!!!\n";	else echo "FAIL!!!!\n";

?>
