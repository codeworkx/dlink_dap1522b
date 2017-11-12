PHP: empty value test for set() ...
===================================
<?
anchor("/somewhere");
set("temp/test1", "");
set("temp/test2", "");

if (query("temp/test1")=="") echo "PASS!\n"; else echo "FAIL!\n";
if (query("temp/test2")=="") echo "PASS!!\n"; else echo "FAIL!!\n";
?>===================================
