<?
include "/htdocs/phplib/phyinf.php";
$configfile="/var/run/neaps.conf";
$neaps_pid = "/var/run/neaps.pid"; //2009_10_27 sandy

fwrite("w",$START, "#!/bin/sh\n");
fwrite("w",$STOP,  "#!/bin/sh\n");


$LANIF="br0";
fwrite("a",$START, "echo Start Neap Server ...> /dev/console \n");
fwrite("a",$START,  "neaps -i ".$LANIF." -c ".$configfile."  &> /dev/console\n");
fwrite("a",$START,  "echo $! > ".$neaps_pid."\n"); //2009_10_27 sandy
//fwrite("a",$START,  "brctl block_neap br0 1\n"); //phelpsll for admin IP 2010/4/19
        
fwrite("a",$STOP,  "echo Stop Neap Server ... > /dev/console\n");
        /*2009_10_27 sandy start */
fwrite("a",$STOP,  "if [ -f ".$neaps_pid." ]; then\n");
fwrite("a",$STOP,  "kill \`cat ".$neaps_pid."\` > /dev/null 2>&1\n");
fwrite("a",$STOP,  "rm -f ".$neaps_pid."\n");
fwrite("a",$STOP,  "fi\n\n");
                /*2009_10_27 sandy end */
//fwrite("a",$STOP,  "brctl block_neap br0 0\n"); //phelpsll for admin IP 2010/4/19
        
fwrite("a",$START,"exit 0\n");
fwrite("a",$STOP, "exit 0\n");
?>
