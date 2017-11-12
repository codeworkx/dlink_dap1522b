<?
include "/htdocs/phplib/phyinf.php";

$useselectmode=1;

$logselectioncnt=query("/device/log/selection/count");
if($logselectioncnt>0) {
$useselectmode=1;
$selectrule="";
foreach("/device/log/selection/entry")
{
   $rulename="";
   $rulename=query("/device/log/selection/entry:".$InDeX."/name");
   $active=query("/device/log/selection/entry:".$InDeX."/active");
   if($rulename!="" && $active=="1")
   {
      $selectrule =$selectrule." -f ".$rulename;
   }
}
}
$loglevel = query("/device/log/level");
if		($loglevel == "WARNING")	$loglevel = "warn";
else if	($loglevel == "NOTICE")		$loglevel = "notice";
else if	($loglevel == "DEBUG")		$loglevel = "debug";
else $loglevel = "notice";

$cmd = "";
foreach("/runtime/inf")
{
	$uid = query("uid");
	$phyinf = query("phyinf");
	$ifdev = PHYINF_getifname($phyinf);

	if ($uid!="" && $ifdev!="") $cmd = $cmd." -e ".$ifdev."=".$uid;
}


fwrite(w, $START, "#!/bin/sh\n");
if($useselectmode==1)
{
fwrite(a, $START, "logd".$selectrule." &\n");
}else
{
fwrite(a, $START, "logd -p ".$loglevel." &\n");
}
fwrite(a, $START, "klogd -p ".$loglevel.$cmd." &\n");

fwrite(w, $STOP, "#!/bin/sh\n");
fwrite(a, $STOP, "killall klogd\n");
fwrite(a, $STOP, "killall logd\n");
?>
