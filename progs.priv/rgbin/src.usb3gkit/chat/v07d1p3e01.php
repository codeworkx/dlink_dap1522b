TIMEOUT 10
'' \rAT
<?
include "/htdocs/phplib/xnode.php";
$devp = XNODE_getpathbytarget("/runtime/tty", "entry", "devnum", $DEVNUM, 0);
$apn = query($devp."/apn");
$dno = query($devp."/dialno");
?>
OK \rAT+CFUN=1
OK \rATDT<?=$dno?>\r
CONNECT ''
