string function test
-----------------
<?
$str = "ab cd ef-gh-ij;kl;mn\\op\\qr";
$str1 = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
$str2 = "1234567890";
$str3 = "0123456789abcdefABCDEF";
$str4 = "";
$str5 = "[abcd]scut[efgh]scut[ijkl]scut[mnop]";

$cutResult = charcodeat($str1, "26");
if ($cutResult == "A") echo "charcodeat: PASS!\n"; else echo "charcodeat: FAIL!\n";
$cutResult = charcodeat($str1, "100");
if ($cutResult == "") echo "charcodeat: PASS!\n\n"; else echo "charcodeat: FAIL!\n\n";

$cutResult = cut($str, 0, " ");
if ($cutResult == "ab") echo "cut with \" \": PASS!\n"; else echo "cut with \" \": FAIL!\n";
$cutResult = cut($str, 1, "-");
if ($cutResult == "gh") echo "cut with \"-\": PASS!\n"; else echo "cut with \"-\": FAIL!\n";
$cutResult = cut($str, 2, ";");
if ($cutResult == "mn\\op\\qr") echo "cut with \";\": PASS!\n"; else echo "cut with \";\": FAIL!\n";
$cutResult = cut($str, 1, "\\");
if ($cutResult == "op") echo "cut with \"\\\": PASS!\n"; else echo "cut with \"\\\": FAIL!\n";
$cutResult = cut($str, 1, "]");
if ($cutResult == "") echo "cut with non-exist char: PASS!\n\n"; else echo "cut with non-exist char: FAIL!\n\n";

$cutResult = cut_count($str, " ");
if ($cutResult == "3") echo "cut count \" \": PASS!\n"; else echo "cut count \" \": FAIL!\n";
$cutResult = cut_count($str, "?");
if ($cutResult == "1") echo "cut count \"?\": PASS!\n\n"; else echo "cut count \"?\": FAIL!\n\n";

$cutResult = isalpha($str1);
if ($cutResult == "1") echo "isalpha: PASS!\n"; else echo "isalpha: FAIL!\n";
$cutResult = isalpha($str);
if ($cutResult == "0") echo "isalpha: PASS!\n\n"; else echo "isalpha: FAIL!\n\n";

$cutResult = isdigit($str2);
if ($cutResult == "1") echo "isdigit: PASS!\n"; else echo "isdigit: FAIL!\n";
$cutResult = isdigit($str1);
if ($cutResult == "0") echo "isdigit: PASS!\n\n"; else echo "isdigit: FAIL!\n\n";

$cutResult = isempty($str4);
if ($cutResult == "1") echo "isempty: PASS!\n"; else echo "isempty: FAIL!\n";
$cutResult = isempty($str);
if ($cutResult == "0") echo "isempty: PASS!\n\n"; else echo "isempty: FAIL!\n\n";

$cutResult = isxdigit($str3);
if ($cutResult == "1") echo "isxdigit: PASS!\n"; else echo "isxdigit: FAIL!\n";
$cutResult = isxdigit($str1);
if ($cutResult == "0") echo "isxdigit: PASS!\n\n"; else echo "isxdigit: FAIL!\n\n";

$cutResult = scut($str5, 3, "scut");
if ($cutResult == "[mnop]") echo "scut: PASS!\n"; else echo "scut: FAIL!\n";
$cutResult = scut($str5, 100, "scut");
if ($cutResult == "") echo "scut: PASS!\n\n"; else echo "scut: FAIL!\n\n";

$cutResult = scut_count($str5, "scut");
if ($cutResult == "4") echo "scut_count: PASS!\n"; else echo "scut_count: FAIL!\n";
$cutResult = scut_count($str5, "xxxx");
if ($cutResult == "1") echo "scut_count: PASS!\n\n"; else echo "scut_count: FAIL!\n\n";

$cutResult = strchr($str1, "A");
if ($cutResult == "26") echo "strchr: PASS!\n"; else echo "strchr: FAIL!\n";
$cutResult = strchr($str1, "-");
if ($cutResult == "-1") echo "strchr: PASS!\n\n"; else echo "strchr: FAIL!\n\n";

$cutResult = strlen($str1);
if ($cutResult == "52") echo "strlen: PASS!\n"; else echo "strlen: FAIL!\n";
$cutResult = strlen($str4);
if ($cutResult == "0") echo "strlen: PASS!\n\n"; else echo "strlen: FAIL!\n\n";

$cutResult = strtoul("fffff", "16");
if ($cutResult == "1048575") echo "strtoul: PASS!\n"; else echo "strtoul: FAIL!\n";
$cutResult = tolower($str1);
if ($cutResult == "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz") echo "tolower: PASS!\n"; else echo "tolower: FAIL!\n";
$cutResult = toupper($str1);
if ($cutResult == "ABCDEFGHIJKLMNOPQRSTUVWXYZABCDEFGHIJKLMNOPQRSTUVWXYZ") echo "toupper: PASS!\n"; else echo "toupper: FAIL!\n";
?>
