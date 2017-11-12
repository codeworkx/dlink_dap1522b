PHP: i18n() function test ......
=================================
<?

echo "sealpac():".sealpac("/mnt/david/test_i18n.zhtw.slp")."\n";

echo I18N("","PASS!")."\n";

//echo I18N("","PASS!!")."\n";
if (I18N("", "PASS!!")=="pass!!") echo "PASS!!\n";
else echo "FAIL!!\n";

//echo I18N("","PASS $1 $2 $3", "1", "2", "3")."\n";
if (I18N("","PASS $1 $2 $3", "1", "2", "3")=="pass 1 2 3") echo "PASS!!!\n";
else echo "FAIL!!!\n";
//echo I18N("","$$$$$1$$$$00000$1$2$3", "111", "222", "333")."\n";
if (I18N("","$$$$$1$$$$00000$1$2$3", "111", "222", "333")=="$$$$111$$$111222333") echo "PASS!!!!\n";
else echo "FAIL!!!!\n";

$msg1="test msg1";
//echo I18N("",'SHOW $2$3$2, MSG1=[$4]$1, $3$5', '!', '"', '$', $msg1, 'msg1=['.$msg1.']')."\n";
if (I18N("",'SHOW $2$3$2, MSG1=[$4]$1, $3$5', '!', '"', '$', $msg1, 'msg1=['.$msg1.']')
	== 'show "$", msg1=[test msg1]!, $msg1=[test msg1]') echo "PASS!!!!!\n";
else echo "FAIL!!!!!\n";

$msg1 = I18N("j","I'M MESSAGE1");
$msg2 = I18N("x","I'M MESSAGE2");
//echo I18N("","TEST: MSG1=[$1], MSG2=[$2], DOLLAR SIGN=[$3]", $msg1, $msg2, "$")."\n";
if (I18N("","TEST: MSG1=[$1], MSG2=[$2], DOLLAR SIGN=[$3]", $msg1, $msg2, "$") 
	== "test: msg1=[i\\'m message1], msg2=[i&apos;m message2], dollar sign=[$]") echo "PASS!!!!!!\n";
else echo "FAIL!!!!!!\n";

$ret = I18N("","<>!@#$%^&*('\");");	//echo $ret."\n";
if ($ret=="<>!@#$%^&*('\");")		echo "PASS!!!!!!!\n"; else echo "FAIL!!!!!!!\n";
$ret = I18N("j","<>!@#$%^&*('\");"); //echo $ret."\n";
if ($ret=="<>!@#$%^&*(\\'\\\");")	echo "PASS!!!!!!!!\n"; else echo "FAIL!!!!!!!!\n";
$ret = I18N("s","<>!@#$%^&*('\");"); //echo $ret."\n";
if ($ret=="<>!@#\\$%^&*('\\\");")	echo "PASS!!!!!!!!!\n"; else echo "FAIL!!!!!!!!!\n";
$ret = I18N("h","<>!@#$%^&*('\")"); //echo "[".$ret."]\n";
if ($ret=="&lt;&gt;!@#$%^&amp;*('&quot;)")	echo "PASS!!!!!!!!!!\n"; else echo "FAIL!!!!!!!!!!\n";
$ret = I18N("x","<>!@#$%^&*('\")"); //echo "[".$ret."]\n";
if ($ret=="&lt;&gt;!@#$%^&amp;*(&apos;&quot;)")	echo "PASS!!!!!!!!!!!\n"; else echo "FAIL!!!!!!!!!!!\n";

?>=================================
