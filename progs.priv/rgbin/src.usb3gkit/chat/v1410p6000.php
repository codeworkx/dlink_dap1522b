ABORT 'NO CARRIER'
ABORT 'ERROR'
ABORT 'NO DIALTONE'
ABORT 'BUSY'
ABORT 'NO ANSWER'
'' \rATZ
<?
include "/htdocs/phplib/xnode.php";

$devp = XNODE_getpathbytarget("/runtime/tty", "entry", "devnum", $DEVNUM, 0);
if ($devp!="")
{
        $slot = query($devp."/slot");
        $tty = XNODE_getpathbytarget("", "phyinf", "slot",$slot, 0);
        $uid = query($tty."/uid");
        $inf = XNODE_getpathbytarget("", "inf", "phyinf",$uid, 0);
        $inet = query($inf."/inet");
        $phyinf = XNODE_getpathbytarget("/inet", "entry", "uid",$inet, 0);
        if ($phyinf != "")
        {
                if (query($phyinf."/ppp4/tty/qos_enable")=="1")
                {
                        echo "OK \\rAT+CGEQREQ=1,2,".
                                query($phyinf."/ppp4/tty/qos_upstream").",".
                                query($phyinf."/ppp4/tty/qos_downstream")."\n";
                }
        }
}
$apn = query($devp."/apn");
$dno = query($devp."/dialno");
?>
OK-AT-OK ATD<?=$dno?>
CONNECT \d\c
