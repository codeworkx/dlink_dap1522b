<?
if (isalpha("alpha432")=="0")	echo "PASS!\n"; else echo "FAIL!\n";
if (isalpha("!@#$")=="0")		echo "PASS!!\n"; else echo "FAIL!!\n";
if (isalpha("alpha@")=="0")		echo "PASS!!!\n"; else echo "FAIL!!!\n";
if (isalpha("12345")=="0")		echo "PASS!!!!\n"; else echo "FAIL!!!!\n";

if (isdigit("alpha432")=="0")	echo "PASS!\n"; else echo "FAIL!\n";
if (isdigit("!@#$")=="0")		echo "PASS!!\n"; else echo "FAIL!!\n";
if (isdigit("alpha@")=="0")		echo "PASS!!!\n"; else echo "FAIL!!!\n";
if (isdigit("12345")=="1")		echo "PASS!!!!\n"; else echo "FAIL!!!!\n";

if (isxdigit("abcdef")=="1")	echo "PASS!\n"; else echo "FAIL!\n";
if (isxdigit("!@#$")=="0")		echo "PASS!!\n"; else echo "FAIL!!\n";
if (isxdigit("alpha@")=="0")	echo "PASS!!!\n"; else echo "FAIL!!!\n";
if (isxdigit("12345abcd")=="1")	echo "PASS!!!!\n"; else echo "FAIL!!!!\n";
if (isxdigit("0x12345ab")=="1")	echo "PASS!!!!!\n"; else echo "FAIL!!!!!\n";
?>
