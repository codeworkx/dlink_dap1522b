get/set function test
-----------------
<?
set("/somewhere/out/there", "what");
$val = get(" ", "/somewhere/out/there");
if ($val == "what") echo "PASS!\n"; else echo "FAIL!\n";

anchor("/somewhere/out");
$val = get("", "there");
if ($val == "what") echo "PASS!!\n"; else echo "FAIL!!\n";

/* escape test */
set("/test/node", "\'\"\$\\\`\;");
if (query("/test/node")=="\'\"\$\\\`\;")		echo "PASS!!!\n";	else echo "FAIL!!!\n";
if (get("j", "/test/node")=="\\'\\\"$\\\\\`;")	echo "PASS!!!!\n";	else echo "FAIL!!!!\n";
if (get("s", "/test/node")=="'\\\"\\$\\\\\\`;")	echo "PASS!!!!!\n";	else echo "FAIL!!!!!\n";

set("/test/special", "<?xml version=\"1.0\" coding='utf-8'?>");
$val = get("h", "/test/special");
if ($val=="&lt;?xml version=&quot;1.0&quot; coding='utf-8'?&gt;") echo "PASS!!!!!!\n"; else echo "FAIL!!!!!!\n";

//set("/test/time", 1000);
//get("", "/test/time"); echo " should be : ";
//get("D", "/test/time"); echo "\n";
/**************/
?>
