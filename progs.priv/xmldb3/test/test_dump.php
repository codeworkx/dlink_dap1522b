EPHP: dump() function test
=====================================================================
I'll set /somewhere/out/there as "what", then dump this DOM tree...
It will print out the dump instruction and result
like {dump(indent, path)=[the DOM tree]} following:

<?
set("/somewhere/out/there", "what");
setattr("/somewhere/in/here", "get", "echo Hello");

echo "start dump....\n";
echo "=============================================================\n";
echo "dump(0, \"/\")=[".dump(0,"/")."]\n";
echo "------------------------------------\n";
echo "dump(1, \"/somewhre\")=[".dump(1,"/somewhere")."]\n";
echo "------------------------------------\n";
echo "dump(2, \"/somewhre/out\")=[".dump(2,"/somewhere/out")."]\n";
echo "------------------------------------\n";
echo "dump(3, \"/somewhere/out/there\")=[".dump(3,"/somewhere/out/there")."]\n";
echo "------------------------------------\n\n";

echo "=============================================================\n";
echo "test anchor and dump.\n";
echo "anchor at \"/somewhere\", and dump \"out\"...\n";
echo "=============================================================\n";
anchor("/somewhere");
echo "dump(2, \"/somewhre/out\")=[".dump(2,"out")."]\n";
?>
