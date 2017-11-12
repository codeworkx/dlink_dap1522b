PHP: del() test for php .............
=====================================
<?

$fail = 0;

set('/test/node/entry1', "value1");
set('/test/node/entry2', "value2");

if (query("/test/node/entry1") != "value1" ) { $fail++; }
if (query("/test/node/entry2") != "value2" ) { $fail++; }

del("/test/node");

if (query("/test/node/entry1") != "") { $fail++; }
if (query("/test/node/entry2") != "") { $fail++; }

/* v0.3.1 fix this problem */
set("/test/nodes/entry1", "value1");
set("/test/nodes/entry2", "value2");
anchor("/test/nodes");
if (query("entry1") != "value1") { $fail++; }
if (query("entry2") != "value2") { $fail++; }
del("/test/nodes");
if (query("entry1") != "") { $fail++; }
if (query("entry2") != "") { $fail++; }

if ($fail > 0)  { echo "Test FAILED!!!\n"; }
else        { echo "Test PASS!!\n"; }
?>=====================================
