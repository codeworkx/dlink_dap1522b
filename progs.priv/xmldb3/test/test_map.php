PHP: map() function test ......
=======================================
<?

set("/test/node/entry1", "node1");
set("/test/node/entry2", "node2");
set("/test/node/entry3", "PASS !");

echo "entry 1 value is ".query("/test/node/entry1")."\n";
echo "entry 2 value is ".query("/test/node/entry2")."\n";
echo "entry 3 value is ".query("/test/node/entry3")."\n";

$message = "Test Pass!";

echo map("/test/node/entry1",	"node1","PASS!",	"node2",	"FAIL!",	*,"FAIL!").		"\n";
echo map("/test/node/entry2",	"node1","FAIL!!",	"node2",	"PASS!!",	*,"FAIL!!").	"\n";
echo map("/test/node/entry3",	"node1","FAIL!!!",	"node2",	"FAIL!!!",	*,"PASS!!!").	"\n";
echo map("/test/node/entry1",	node1,	$message,	node2,		"Fail!",	*,"Fail!").		"\n";
echo map("/test/node/entry2",	node1,	"Fail!",	node2,		$message,	*,"Fail!").		"\n";
echo map("/test/node/entry3",	node1,	"Fail!",	node2,		"Fail!").	"\n";
?>=======================================
