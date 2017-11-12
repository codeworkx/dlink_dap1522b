PHP: testing file read/write
========================================
<?

$fail=0;
$file = "./xmldb_php_test/test_";
unlink($file."fwrite");

fwrite("w+", $file."fwrite", "\"@#$%fwrite\"");
$var1 = fread("", $file."fwrite");
$var2 = fread("j", $file."fwrite");

if ($var1 != "\"@#$%fwrite\"") { $fail++; }
if ($var2 != "\\\"@#$%fwrite\\\"") { $fail++; }
if ($fail > 0) echo "FAIL!\n"; else echo "PASS!\n";

fwrite("w+", $file."fwrite", "abcdefg");
if (fread("", $file."fwrite") == "abcdefg") echo "PASS!!\n"; else echo "FAIL!!\n";
fwrite("a+", $file."fwrite", "ABCDEFG");
if (fread("", $file."fwrite") == "abcdefgABCDEFG") echo "PASS!!!\n"; else echo "FAIL!!!\n";

?>========================================
