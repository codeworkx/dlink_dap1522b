PHP: i18n() function test ......
=================================
<?

echo "sealpac():".sealpac("test/test_i18n.zhtw.slp")."\n";


echo i18n("PASS!")."\n";

//echo i18n("PASS!!")."\n";
if (i18n("PASS!!")=="pass!!")	echo "PASS!!\n";
else							echo "FAIL!!\n";

//echo i18n("PASS $1 $2 $3", "1", "2", "3")."\n";
if (i18n("PASS $1 $2 $3", "1", "2", "3")=="pass 1 2 3")	echo "PASS!!!\n";
else													echo "FAIL!!!\n";

//echo i18n("$$$$$1$$$$00000$1$2$3", "111", "222", "333")."\n";
if (i18n("$$$$$1$$$$00000$1$2$3", "111", "222", "333") == "$$$$111$$$111222333") echo "PASS!!!!\n";
else																echo "FAIL!!!!\n";

$msg1="test msg1";
//echo i18n('SHOW $2$3$2, MSG1=[$4]$1, $3$5', '!', '"', '$', $msg1, 'msg1=['.$msg1.']')."\n";
if (i18n('SHOW $2$3$2, MSG1=[$4]$1, $3$5', '!', '"', '$', $msg1, 'msg1=['.$msg1.']')
	 == 'show "$", msg1=[test msg1]!, $msg1=[test msg1]')	echo "PASS!!!!!\n" ;
else 														echo "FAIL!!!!!\n";

$msg1 = i18n("I'M MESSAGE1");
$msg2 = i18n("I'M MESSAGE2");
//echo i18n("TEST: MSG1=[$1], MSG2=[$2], DOLLAR SIGN=[$3]", $msg1, $msg2, "$")."\n";
if (i18n("TEST: MSG1=[$1], MSG2=[$2], DOLLAR SIGN=[$3]", $msg1, $msg2, "$") 
	== "test: msg1=[i'm message1], msg2=[i'm message2], dollar sign=[$]")	echo "PASS!!!!!!\n";
else																		echo "FAIL!!!!!!\n";

?>=================================
