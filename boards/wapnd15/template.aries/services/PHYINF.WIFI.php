<?
include "/htdocs/phplib/xnode.php";
include "/etc/services/PHYINF/phywifi.php";
function schcmd($uid)
{
        /* Get schedule setting */
        $p = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
        $sch = XNODE_getschedule($p);
        if ($sch=="") $cmd = "start";
        else
        {
                $days = XNODE_getscheduledays($sch);
                $start = query($sch."/start");
                $end = query($sch."/end");
                if (query($sch."/exclude")=="1") $cmd = 'schedule!';
                else $cmd = 'schedule';
                $cmd = $cmd.' "'.$days.'" "'.$start.'" "'.$end.'"';
        }
        return $cmd;
}
function schcmd_ori($uid)
{
	/* Get schedule setting */
	$p = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	$sch_cnt =query($p."/schedule/count");
	if ($sch_cnt == "0" || $sch_cnt == "")
	{ 
		$cmd = "start";
	}
	else
	{
		foreach($p."/schedule/entry")
		{
			$uid=query("uid");
			$exclude=query("exclude");
			$sun=query("sun");
            $mon=query("mon");
            $tue=query("tue");
            $wed=query("wed");
            $thu=query("thu");
            $fri=query("fri");
            $sat=query("sat");
            $starttime=query("start");
            $endtime=query("end");
			if($InDeX == 1)
			{
				if (query($sch."/exclude")=="1") $cmd = 'schedule! ';
				else 							 $cmd = 'schedule ';
			}
			
			$comma="0";
			if ($sun=="1") {$cmd=$cmd."Sun";$comma="1";}
            if ($mon=="1") 
            {   
                if ($comma=="1") {$cmd=$cmd.",";}
                $cmd=$cmd."Mon";$comma="1";
            }
            if ($tue=="1")
            {   
                if ($comma=="1") {$cmd=$cmd.",";}
                $cmd=$cmd."Tue";$comma="1";
            }
            if ($wed=="1") 
            {   
                if ($comma=="1") {$cmd=$cmd.",";}
                $cmd=$cmd."Wed";$comma="1";
            }
            if ($thu=="1") 
            {   
                if ($comma=="1") {$cmd=$cmd.",";}
                $cmd=$cmd."Thu";$comma="1";
            }
            if ($fri=="1") 
            {   
                if ($comma=="1") {$cmd=$cmd.",";}
                $cmd=$cmd."Fri";$comma="1";
            }
            if ($sat=="1") 
            {   
                if ($comma=="1") {$cmd=$cmd.",";}
                $cmd=$cmd."Sat";$comma="1";
            }
            if ($allday=="1")
            {
				$cmd=$cmd." 00:00 24:00";
			}
            else
            {
				$cmd=$cmd." ".$starttime." ".$endtime;
			}
		}
	}
	return $cmd;
}

/********************************************************************/
fwrite("w",$START, "#!/bin/sh\n");
fwrite("w", $STOP, "#!/bin/sh\n");

//TODO : mode
$mode= query("/runtime/device/switchmode");

//TODO : schedule

if($mode=="AP2G" || $mode=="AP5G")
{
	fwrite("a",$START,"service WLAN-1.1 ".schcmd("WLAN-1.1")."\n");
    fwrite("a",$START,"service WLAN-1.2 ".schcmd("WLAN-1.2")."\n");

	fwrite("a",$STOP,"service WLAN-1.1 stop\n");
	fwrite("a",$STOP,"service WLAN-1.2 stop\n");
}
else //	if($mode=="APCLI")
{
	fwrite("a",$START,"service WLAN-2 ".schcmd("WLAN-2")."\n");
	fwrite("a",$STOP,"service WLAN-2 stop\n");
}

//If we restart wireless, we should turn off wps led anyway.
fwrite("a",$STOP,	"event WPS.NONE\n");

fwrite("a",$START,	"exit 0\n");
fwrite("a",$STOP,	"exit 0\n");
?>
