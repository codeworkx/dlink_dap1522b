PHP: testing isdir()
========================================
<?

$file = "./xmldb_php_test/test_file";
fwrite("w+", $file, "\"@#$%fwrite\"");

if (isdir("./xmldb_php_test") == 1) echo "PASS!!\n"; else echo "FAIL!!\n";

?>========================================
