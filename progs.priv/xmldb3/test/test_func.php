func test
---------
<?
/* basic test */
echo "\nbasic test:\n";

$APPEND = "_APPEND";
function sum($arg1, $arg2)
{
	$g_var1 = "should not be touched!";
	$_GLOBALS["g_var2"] = "should be touched!";
	$_GLOBALS["TEST_GLOBALS"] = "TEST_GLOBALS".$APPEND;

	$APPEND = "_APPEND_IN_FUNC";
	$_GLOBALS["TEST_APPEND"] = "TEST".$APPEND;
	return $arg1 + $arg2;
}

$g_var1 = "1";
$g_var2 = "1";

$test = sum(15, 25);
if ($test == 40) echo "PASS!\n"; else echo "FAIL!\n";
$test = sum(45, 45);
if ($test == 90) echo "PASS!!\n"; else echo "FAIL!!\n";

if ($g_var1 == "1") echo "PASS!!!\n"; else echo "FAIL!!!\n";
if ($g_var2 != "1") echo "PASS!!!!\n"; else echo "FAIL!!!!\n";
if ($_GLOBALS["TEST_GLOBALS"] == "TEST_GLOBALS")		echo "PASS!!!!!\n";		else echo "Fail!!!!!\n";
if ($_GLOBALS["TEST_APPEND"] == "TEST_APPEND_IN_FUNC")	echo "PASS!!!!!!\n";	else echo "Fail!!!!!!\n";
if ($APPEND == "_APPEND") echo "PASS!!!!!!!\n";	else echo "Fail!!!!!!!\n";


/* nest function test */
echo "\nnest function test: \n";
$n = 5;
$c = 0;
function nest($num)
{
	if ($num > 0)
	{
		$num--;	$_GLOBALS["c"]++;	nest($num);
	}
}
nest($n);
if ($c == $n) echo "PASS!\n"; else echo "FAIL!\n";


/* anchor test */
echo "\nanchor test: \n";
set("/look/here", "wow");
$path = "/somewhere/out/there/entry";
$i = 0;
while($i < 3)
{
	$i++;
	set($path.":".$i."/name", "e".$i);
}
function t_anchor()
{
	anchor("/look");
	if (query("here")=="wow") return 1;	else return 0;
}
$b_err=0;
$a_err=0;
foreach ("/somewhere/out/there/entry")
{
	if (query("name")!="e".$InDeX)	$b_err++;
	if (t_anchor() != 1)			$a_err++;
	if (query("here")=="wow")		$a_err++;
	if (query("name")!="e".$InDeX)	$a_err++;
}
if ($b_err == 0) echo "PASS!\n";	else echo "FAIL!\n";
if ($a_err == 0) echo "PASS!!\n";	else echo "FAIL!!\n";
?>
