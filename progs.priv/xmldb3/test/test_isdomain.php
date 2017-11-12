<?
$d1 = "LENGTH63-TEST-12345678902234567890323456789042345678905234567890.TEST";	// the length of subdomain_1 is 64.
$d2 = "LENGTH63-TEST-123456789022345678903234567890423456789052345678.TEST";	// the length of subdomain_1 is 63.
$d3 = "LABEL-TEST-NOT-BEGIN-WITH-LETTER.1AA.BBB";
$d4 = "LABEL-TEST-BEGIN-WITH-LETTER.AAA.BBB";
$d5 = "LABEL-TEST-CONTENT-NOT-BELONG-TO-LETTER.DIGIT.HYPHEN.A@A.BBB";
$d6 = "LABEL-TEST-CONTENT-NOT-BELONG-TO-LETTER.DIGIT.HYPHEN.A_A.BBB";
$d7 = "LABEL-TEST-CONTENT-NOT-BELONG-TO-LETTER.DIGIT.HYPHEN.A A.BBB";
$d8 = "LABEL-TEST-NOT-END-WITH-LETTER.DIGIT.AAA.BB-";
$d9 = "LABEL-TEST-NOT-END-WITH-LETTER.DIGIT.AAA.BB.";
if (isdomain($d1)=="0")			echo "PASS!\n"; else echo "FAIL!\n";
if (isdomain($d2)=="1")			echo "PASS!\n"; else echo "FAIL!\n";
if (isdomain($d3)=="1")			echo "PASS!\n"; else echo "FAIL!\n";
if (isdomain($d4)=="1")			echo "PASS!\n"; else echo "FAIL!\n";
if (isdomain($d5)=="0")			echo "PASS!\n"; else echo "FAIL!\n";
if (isdomain($d6)=="0")			echo "PASS!\n"; else echo "FAIL!\n";
if (isdomain($d7)=="0")			echo "PASS!\n"; else echo "FAIL!\n";
if (isdomain($d8)=="0")			echo "PASS!\n"; else echo "FAIL!\n";
if (isdomain($d9)=="0")			echo "PASS!\n"; else echo "FAIL!\n";
if (isdomain($d2.".".$d2.".".$d2.".".$d2)=="0")		echo "PASS!\n"; else echo "FAIL!\n";
?>
