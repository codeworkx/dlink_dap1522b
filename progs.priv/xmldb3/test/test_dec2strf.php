<?
$ip0 = "1"; $ip1 = "67"; $ip2 = "1"; $ip3 = "99";
echo "IPv4: 1.67.1.99,"." 6TO4 IP: 2002:"
	 .dec2strf("%02x", $ip0).dec2strf("%02x", $ip1).":".dec2strf("%02x", $ip2).dec2strf("%02x", $ip3)."::1"."\n";

$val = "192";
echo "dec2strf('%#x', ".$val." ) => ".dec2strf("%#x", $val)."\n";

$val = "192";
echo "dec2strf('%05u', ".$val." ) => ".dec2strf("%05u", $val)."\n";

$val = "2147483647";
echo "dec2strf('%d', ".$val." ) => ".dec2strf("%d", $val)."\n";


$val = "2147483648";
echo "dec2strf('%d', ".$val." ) => ".dec2strf("%d", $val)."\n";


$val = "-2147483648";
echo "dec2strf('%d', ".$val." ) => ".dec2strf("%d", $val)."\n";

$val = "-2147483649";
echo "dec2strf('%d', ".$val." ) => ".dec2strf("%d", $val)."\n";

$val = "-1111";
echo "dec2strf('%d', ".$val." ) => ".dec2strf("%d", $val)."\n";

$val = "abcde";
echo "dec2strf('%d', ".$val." ) => ".dec2strf("%d", $val)."\n";

$val = "abcde";
echo "dec2strf('test:%d', ".$val." ) => ".dec2strf("%d", $val)."\n";


?>
