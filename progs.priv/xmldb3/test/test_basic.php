PHP: basic function test
includeing... query(), set(), del().
=====================================
<?set("/test/node1/entry:1/value", "value1");
set("/test/node1/entry:2/value", "value2");
set("/test/node1/entry:3/value", "value3");
set("/test/node1/entry:4/value", "value4");
set("/test/node1/entry:5/value", "value5");
set("/test/node1/entry:6/value", "value6");
set("/test/node1/entry:7/value", "value7");
set("/test/node1/entry:8/value", "value8");
set("/test/node1/entry:9/value", "value9");

set("/test/node2/entry:1/value", "value1");
set("/test/node2/entry:2/value", "value2");
set("/test/node2/entry:3/value", "value3");
set("/test/node2/entry:4/value", "value4");
set("/test/node2/entry:5/value", "value5");
set("/test/node2/entry:6/value", "value6");
set("/test/node2/entry:7/value", "value7");
set("/test/node2/entry:8/value", "value8");
set("/test/node2/entry:9/value", "value9");

set("/test/node3/entry:1/value", "value1");
set("/test/node3/entry:2/value", "value2");
set("/test/node3/entry:3/value", "value3");
set("/test/node3/entry:4/value", "value4");
set("/test/node3/entry:5/value", "value5");
set("/test/node3/entry:6/value", "value6");
set("/test/node3/entry:7/value", "value7");
set("/test/node3/entry:8/value", "value8");
set("/test/node3/entry:9/value", "value9");

$fail = 0;
$i = 1;
while ($i <= 3)
{
	$j = 1;
	while ($j < 10)
	{
		$value = query("/test/node".$i."/entry:".$j."/value");
		$answer = "value".$j;
		if ($value != $answer)
		{
			echo "Test failed at ".$i.",".$j."\n";
			$fail++;
		}
		$j += 1;
	}
	$i += 1;
}
if ($fail == 0) { echo "Test PASS !!!\n"; }
?>======================================
