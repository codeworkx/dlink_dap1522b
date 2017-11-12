<?
$str1 = "IA-NA+IA-PD";
$str2 = "IA-NA";
$ret = strstr($str1, $str2);
if ($ret==0) echo "PASS!"; else echo "FAIL!";
echo "\n";
?>
