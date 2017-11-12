<?
$time = 12345678;
set("/test/timeformat/value", $time);
echo "--------------------------------------------------\n";
echo "Default time format : ".get("TIME", "/test/timeformat/value")."\n";
echo "ISO-8601 local time : ".get("TIME.ISO8601", "/test/timeformat/value")."\n";
echo "RFC 1123 GMT time   : ".get("TIME.RFC1123", "/test/timeformat/value")."\n";
echo "RFC 850 GMT time    : ".get("TIME.RFC850",  "/test/timeformat/value")."\n";
echo "ANSI C's asctime()  : ".get("TIME.ASCTIME", "/test/timeformat/value");
echo "--------------------------------------------------\n";
echo "Default time format : ".ftime("TIME", $time)."\n";
echo "ISO-8601 local time : ".ftime("TIME.ISO8601", $time)."\n";
echo "RFC 1123 GMT time   : ".ftime("TIME.RFC1123", $time)."\n";
echo "RFC 850 GMT time    : ".ftime("TIME.RFC850",  $time)."\n";
echo "ANSI C's asctime()  : ".ftime("TIME.ASCTIME", $time);
echo "--------------------------------------------------\n";
echo "STRFTIME            : %Y/%m/%d,%T\n";
ftime("STRFTIME", "%Y/%m/%d,%T");
echo "Default time format : ".ftime("TIME", $time)."\n";
echo "--------------------------------------------------\n";
?>
