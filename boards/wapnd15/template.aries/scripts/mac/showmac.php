<?
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";

$stsp = "/runtime/phyinf_tmpnode/showmac/entry:".$INDEX;
if($ACTION=="ADD")
{
	set($stsp."/port",		$PORT);
	set($stsp."/macaddr",	$MACADDR);
} 
else if ($ACTION=="CLEAN")
{
	del("/runtime/phyinf_tmpnode/showmac");
} 
else 
{
	TRACE_error("showmac.php :  UNKNOWN COMMAND !!");
}


?>
