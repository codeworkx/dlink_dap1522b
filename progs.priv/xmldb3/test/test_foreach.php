foreach test
--------------------
<?
/* create the test nodes */
$p1 = "/somewhere/out/there/";
$p2 = "entry";
$p3 = "entry2";
$path = $p1.$p2;
$path2 = $p1.$p3;
set($p1."num", "123");
$i = 0;
while($i < 10)
{
	$i++;
	set($path.":".$i."/name", "e".$i);
	set($path2.":".$i."/name", "t_name_".$i);
	set($path.":".$i."/value", "v".$i);
	set($path2.":".$i."/value", "t_value_".$i);
}

function t_nest($path)
{
	/* basic foreach function test*/
	$i = 0;
	$err = 0;
	foreach ($path)
	{
		$i++;
		if ($i != $InDeX) $err ++;
		if (query("name") != "e".$i) $err++;
		if (query("value") != "v".$i) $err++;
		foreach ($path)
		{
		}
		if ($i != $InDeX) $err ++;
	}
	if ($err == 0)	echo "PASS!\n";
	else			echo "FAIL!\n";
}

function t_anchor($p1, $p2)
{
	/* anchor and foreach test */
	anchor($p1);
	if (query("num") == "123")	echo "PASS!\n";
	else						echo "FAIL!\n";

	$i = 0;
	$err = 0;
	foreach ($p2)
	{
		$i++;
		if ($i != $InDeX) $err ++;
		if (query("name") != "e".$i) $err++;
		if (query("value") != "v".$i) $err++;
	}
	if ($err == 0)	echo "PASS!!\n";
	else			echo "FAIL!!\n";

	if (query("num") == "123")	echo "PASS!!!\n";
	else						echo "FAIL!!!\n";
}

function t_break($path)
{
	$i = 0;
	foreach ($path)
	{
		$i++;
		if ($i>=5) break;
	}
	if ($i == 5)	echo "PASS!\n";
	else			echo "FAIL!\n";
}

function t_continue($path)
{
	$i = 0;
	$j = 0;
	foreach ($path)
	{
		$i++;
		if ($j>=5) continue;
		$j++;
	}
	if ($i == 10)	echo "PASS!\n";
	else			echo "FAIL!\n";
	if ($j == 5)	echo "PASS!!\n";
	else			echo "FAIL!!\n";
}

function t_value()
{
	$path="/runtime/group";
	set($path.":1", "admin");
	set($path.":2", "poweruser");
	set($path.":3", "user");
	set($path.":4", "guest");

	$path2=$path.":3/member";
	set($path2.":1", "pooh");
	set($path2.":2", "honey");

	foreach ($path)
	{
		if ($InDeX == 2)
		{
			if($VaLuE == "poweruser")		echo "PASS!\n";		else echo "FAIL!\n";
		}
		if ($InDeX == 3)
		{
			foreach ("member")
			{
				if ($InDeX==2)
				{
					if ($VaLuE=="honey")	echo "PASS!!\n";	else echo "FAIL!!\n";
				}
			}
			if ($VaLuE == "user")			echo "PASS!!!\n";	else echo "FAIL!!!\n";
		}
	}
/*
	foreach ($path)
	{
		echo $path.":".$InDeX."=[".$VaLuE."]\n";
		if ($InDeX == 3)
		{
			foreach ("member")
			{
				echo "\tmember ".$InDeX."=[".$VaLuE."]\n";
			}
		}
	}
*/
}
echo "\nnest test:\n";					t_nest($path);
echo "\nanchor test:\n";				t_anchor($p1, $p2);
echo "\nbreak test:\n";					t_break($path);
echo "\ncontinue test:\n";				t_continue($path);
echo "\n'$InDeX', '$VaLuE' test:\n";	t_value();
?>
--------------------

