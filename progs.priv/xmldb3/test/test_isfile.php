PHP: testing isfile()
========================================
<?

$file = "./xmldb_php_test/test_file";
fwrite("w+", $file, "\"@#$%fwrite\"");

if (isfile($file) == 1) echo "PASS!!\n"; else echo "FAIL!!\n";

?>========================================
