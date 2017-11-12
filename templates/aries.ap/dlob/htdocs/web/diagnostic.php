HTTP/1.1 200 OK
Content-Type: text/xml

<?
include "/htdocs/phplib/inet6.php";
if ($_POST["act"] == "ping")
{
	set("/runtime/diagnostic/ping", $_POST["dst"]);
	$result = "OK";
}
else if ($_POST["act"] == "ping6")
{
	if(INET_globalv6addr($_POST["dst"]) == 0)
	{
		set("/runtime/diagnostic/ping6", $_POST["dst"]."%br0");
	}
	else
	{
		set("/runtime/diagnostic/ping6", $_POST["dst"]);
	}
	$result = get("x", "/runtime/diagnostic/ping6");
}
else if ($_POST["act"] == "pingreport")
{
	$result = get("x", "/runtime/diagnostic/ping");
}
echo '<?xml version="1.0"?>\n';
?><diagnostic>
	<report><?=$result?></report>
</diagnostic>
