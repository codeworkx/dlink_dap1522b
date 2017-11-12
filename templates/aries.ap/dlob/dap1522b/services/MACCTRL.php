<?
include "/htdocs/phplib/xnode.php";
$DEBUG = 0;
$FILE= "/var/maclistfile";


  $mode = query("/runtime/device/switchmode");
  if($mode != "AP2G" && $mode != "AP5G")
     {
      fwrite("w",$START, "#!/bin/sh\n");
	fwrite("w",$STOP,  "#!/bin/sh\n");
        fwrite("a",$START, "echo acl: Only support for AP mode.");
        fwrite("a",$STOP,  "echo acl : Only support for AP mode.");
    
     }else
     {

fwrite("w",$START, "#!/bin/sh\n");
fwrite("w",$STOP,  "#!/bin/sh\n");
fwrite("w",$FILE,  "");

$cmd = "rtlioc macfilter reset\n";
if($DEBUG == 1) 
{
	fwrite("a",$STOP, "echo ".$cmd);
	fwrite("a",$START, "echo ".$cmd);
}	
fwrite("a",$STOP, $cmd);			
fwrite("a",$START, $cmd);

foreach("/acl/macctrl/entry")
{
	$uid = query("uid");
	$enable = query("enable");
	$mac = query("mac");
	if($enable == 1) fwrite("a",$FILE, $mac."\n");		
}

$policy = query("/acl/macctrl/policy");
if($DEBUG == 1) fwrite("a",$START, "echo ".$policy."\n");

if($policy == "ACCEPT")
{																
	$cmd = "rtlioc macfilter drop ".$FILE."\n";
	if($DEBUG == 1) fwrite("a",$START, "echo ".$cmd);
	fwrite("a",$START, $cmd);						
	
	$cpumac = query("/runtime/devdata/lanmac");
	$cmd = "rtlioc cpumac ".$cpumac."\n";
	if($DEBUG == 1) fwrite("a",$START, "echo ".$cmd);
	fwrite("a",$START, $cmd);
}
else if($policy == "DROP")
{		
	$cmd = "rtlioc macfilter allow ".$FILE."\n";
	if($DEBUG == 1) fwrite("a",$START, "echo ".$cmd);
	fwrite("a",$START, $cmd);						
	
	$cpumac = query("/runtime/devdata/lanmac");
	$cmd = "rtlioc cpumac ".$cpumac."\n";
	if($DEBUG == 1) fwrite("a",$START, "echo ".$cmd);
	fwrite("a",$START, $cmd);
}
}
			
?>
