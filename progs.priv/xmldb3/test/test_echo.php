PHP: echo test ...
You should see the following 4 line printed.
============================================
123456;7890
var1 = 1
var2 = 1
test; test;
============================================
<?

$fail = 0;

set("/test/echo/node1",1);

$var1=0;
$var1 = $var1 + 1;
$var2 = query("/test/echo/node1");

$string = "123456;7890\n";
echo $string;

echo "var1 = ".$var1."\n";
echo "var2 = ".$var2."\n";
echo "test; test;"."\n";

?>============================================
