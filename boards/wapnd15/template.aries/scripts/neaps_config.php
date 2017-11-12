<?
$VENDOR= query("/runtime/device/vendor");   /*query("/sys/vendor");*/
$MODEL=query("/runtime/device/modelname"); /*query("/sys/modelname");*/
$DESCRIPTION=query("/runtime/device/description");  /*query("/sys/modeldescription");*/
$VERSION=query("/runtime/device/firmwareversion");    /*query("/runtime/sys/info/firmwareversion");*/
$IPADDR="192.168.0.50";  /*add default ip for dap1522B*/
$NETMASK="255.255.255.0"; 
$MACADDR=query("/runtime/devdata/lanmac");
$cnt = query("/device/account/count");
$i = 1;
if ($cnt>0)
{
        
        $SECRET    = query("/device/account/entry:".$i."/password");
 }
fwrite("w",$START,"vendor=".$VENDOR."\n");
fwrite("a",$START, "model=".$MODEL."\n");
fwrite("a",$START, "description=".$DESCRIPTION."\n");
fwrite("a",$START, "version=".$VERSION."\n");
fwrite("a",$START, "ipaddr=".$IPADDR."\n");
fwrite("a",$START, "netmask=".$NETMASK."\n");
fwrite("a",$START, "macaddr=".$MACADDR."\n");

fwrite("a",$START, "secret=".$SECRET."\n");
?>
