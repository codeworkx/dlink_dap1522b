<?
fwrite("w",$START, "#!/bin/sh\n");

// When sw port enable, start a timer to re-send upnp keep alive packets
//fwrite("a",$START, "xmldbc -k upnp_re_alive\n");
fwrite("a",$START, 'event upnp_re_alive add "event UPNP.ALIVE.BRIDGE-1"\n');
fwrite("a",$START, 'xmldbc -t "upnp_re_alive:5:event upnp_re_alive"\n');

// enable switch port here
fwrite("a",$START, "rtlioc enlan\n");
fwrite("a",$START, "exit 0\n");

fwrite("w",$STOP,  "#!/bin/sh\nexit 0\n");
?>
