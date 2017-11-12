PHP: test anchor()...
===================================
<?
$fail = 0;

del("/runtime/layout");
set("/wan/rg/inf:1/static", 2);
set("/runtime/layout/lanif", "br0");

anchor("/runtime/layout");
$lanif = query("lanif");
if ($lanif != "br0") echo "FAIL!\n"; else echo "PASS!\n";

$wanif = query("wanif");
if ($wanif != "") echo "FAIL!!\n"; else echo "PASS!!\n";

set("wanif", "ppp0");
$wanif = query("wanif");
if ($wanif != "ppp0") echo "FAIL!!!\n"; else echo "PASS!!!\n";

/* If anchor node is deleted, access related node should not crash xmldb. */
del("/runtime/layout/lanif");
$lanif = query("lanif");
if ($lanif == "") echo "PASS!!!!\n"; else echo "FAIL!!!!\n";

set("/runtime/layout/lanif", "br0");
anchor("/runtime/layout");
$lanif = query("lanif");
if ($lanif == "br0") echo "PASS!!!!!\n"; else echo "FAIL!!!!!\n";
anchor("");
$lanif = query("lanif");
if ($lanif == "") echo "PASS!!!!!\n"; else echo "FAIL!!!!!\n";

?>===================================
