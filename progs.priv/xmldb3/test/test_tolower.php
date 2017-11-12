<?
$a = "AbCdEfGhIjKlMnOpQrStUvWxYz12345678";
$u = "ABCDEFGHIJKLMNOPQRSTUVWXYZ12345678";
$l = "abcdefghijklmnopqrstuvwxyz12345678";

if (tolower($a)==$l)	echo "PASS!\n"; else echo "FAIL!\n";
if (toupper($a)==$u)	echo "PASS!!\n"; else echo "FAIL!!\n";
?>
