<?
include "/htdocs/phplib/xnode.php";
$ipv6_type = "";
$ipv6_addr = "";
$ipv6_gateway = "";
$ipv6_addr_ll = "";
$ipv6_dns1 = "";
$ipv6_dns2 = "";

$uid_v6 = "BRIDGE-3";
$uid_v6_ll = "BRIDGE-2";
$inf_v6 = XNODE_getpathbytarget("", "inf", "uid", $uid_v6, 0);
$run_inf_v6 = XNODE_getpathbytarget("runtime", "inf", "uid", $uid_v6, 0);
$run_inf_v6_ll = XNODE_getpathbytarget("runtime", "inf", "uid", $uid_v6_ll, 0);

$ipv6_addr_ll = query($run_inf_v6_ll."/inet/ipv6/ipaddr")."/".query($run_inf_v6_ll."/inet/ipv6/prefix");
if($ipv6_addr_ll == "") {$ipv6_addr_ll = "none";}
if(query($inf_v6."/active")=="0")	$ipv6_type = "Link-local Only";
else if(query($run_inf_v6."/inet/ipv6/mode")=="STATIC")	$ipv6_type = "Static"; 
else if(query($run_inf_v6."/inet/ipv6/mode")=="STATELESS")	$ipv6_type = "Stateless"; 
else if(query($run_inf_v6."/inet/ipv6/mode")=="STATEFUL")	$ipv6_type = "DHCPv6"; 

if(query($inf_v6."/active")=="1")
{
	$ipv6_addr = query($run_inf_v6."/inet/ipv6/ipaddr")."/".query($run_inf_v6."/inet/ipv6/prefix");
	if($ipv6_addr == "/") {$ipv6_addr = "none";}
	$ipv6_gateway = query($run_inf_v6."/inet/ipv6/gateway");
	if($ipv6_gateway == "") {$ipv6_gateway = "none";}
	$ipv6_dns1 = query($run_inf_v6."/inet/ipv6/dns:1");
	if($ipv6_dns1 == "") {$ipv6_dns1 = "none";}
	$ipv6_dns2 = query($run_inf_v6."/inet/ipv6/dns:2");
	if($ipv6_dns2 == "") {$ipv6_dns2 = "none";}
}
else
{
	$ipv6_addr = "none";
	$ipv6_gateway = "none";
	$ipv6_dns1 = "none";
	$ipv6_dns2 = "none";
}	
?>
<form id="mainform" onsubmit="return false;">
<div class="orangebox">
	<h1><? echo i18n("IPv6 Network Information");?></h1>
	<p>
		<? echo i18n("All of your IPv6 network connection details are displayed on this page.");?>
	</p>
</div>
<div class="blackbox" id="ipv6">
    <h2><? echo i18n("IPv6 Connection Information");?></h2>
    <div class="textinput">
        <span class="name"><? echo i18n("IPv6 Connection Type");?></span>
	    <span class="delimiter">:</span>
	    <span class="value"><? echo i18n($ipv6_type);?></span>
    </div>
	<div class="textinput">
        <span class="name"><? echo i18n("LAN IPv6 Address");?></span>
        <span class="delimiter">:</span>
		<span class="value"><? echo i18n($ipv6_addr);?></span>
    </div>
    <div class="textinput">
        <span class="name"><? echo i18n("IPv6 Default Gateway");?></span>
        <span class="delimiter">:</span>
		<span class="value"><? echo i18n($ipv6_gateway);?></span>
    </div>
    <div class="textinput">
        <span class="name"><? echo i18n("LAN IPv6 Link-Local Address");?></span>
        <span class="delimiter">:</span>
		<span class="value"><? echo i18n($ipv6_addr_ll);?></span>
    </div>
    <div class="textinput">
        <span class="name"><? echo i18n("Primary DNS Server");?></span>
        <span class="delimiter">:</span>
        <span class="value"><? echo i18n($ipv6_dns1);?></span>
    </div>
    <div class="textinput" >
        <span class="name"><? echo i18n("Secondary DNS Server");?></span>
        <span class="delimiter">:</span>
        <span class="value"><? echo i18n($ipv6_dns2);?></span>
    </div>    
	<div class="gap"></div>
</div>
</form>

