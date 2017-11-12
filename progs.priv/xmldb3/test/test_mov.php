<?
del("/test/node");
add("/test/node/first/entry", "first test item 1");
add("/test/node/first/entry", "first test item 2");
add("/test/node/first/entry", "first test item 3");
add("/test/node/second/entry", "second test item 1");
add("/test/node/second/entry", "second test item 2");
add("/test/node/second/entry", "second test item 3");
add("/test/node/third/entry", "third test item 1");
add("/test/node/third/entry", "third test item 2");
add("/test/node/third/entry", "third test item 3");

echo dump(0, "/test");
$ret = mov("/test/node", "/test/node/third");
if ($ret == "0") echo "PASS!\n"; else echo "FAIL!\n";
echo dump(0, "/test");
$ret = mov("/test/node/third", "/test/node/first");
if ($ret == "1") echo "PASS!!\n"; else echo "FAIL!!\n";
echo dump(0, "/test");

del("/test/node");
add("/test/node/first/entry", "first test item 1");
add("/test/node/first/entry", "first test item 2");
add("/test/node/first/entry", "first test item 3");
add("/test/node/second/entry", "second test item 1");
add("/test/node/second/entry", "second test item 2");
add("/test/node/second/entry", "second test item 3");
add("/test/node/third/entry", "third test item 1");
add("/test/node/third/entry", "third test item 2");
add("/test/node/third/entry", "third test item 3");

echo dump(0, "/test");
$ret = movc("/test/node", "/test/node/second");
if ($ret == "0") echo "PASS!!!\n"; else echo "FAIL!!!\n";
echo dump(0, "/test");
$ret = movc("/test/node/first", "/test/node/second");
if ($ret == "1") echo "PASS!!!!\n"; else echo "FAIL!!!!\n";
echo dump(0, "/test");

$ret = mov("", "/test");
if ($ret == "0") echo "PASS!!!!!\n"; else echo "FAIL!!!!!\n";
$ret = movc("", "/test");
if ($ret == "0") echo "PASS!!!!!!\n"; else echo "FAIL!!!!!!\n";

?>
