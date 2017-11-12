HTTP/1.1 200 OK
Content-Type:test/plain
Content-Disposition:attachment;filename="log.txt"

<?
echo "[System]\n";
echo "+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
$index_system = 1;
foreach("/runtime/log/sysact/entry")
{
	$time = get("TIME.ASCTIME", "/runtime/log/sysact/entry:".$InDeX."/time");
	$msg = get("x", "/runtime/log/sysact/entry:".$InDeX."/message");
	if(substr($msg, 0, 5) != "WLAN:")
	{
		echo "[Time]".$time;
		echo "[Message:".$index_system."]".$msg."\n";
		echo "--------------------------------------------------------------------------------------------\n";
		$index_system++;
	}	
}
echo "\n[Wireless]\n";
echo "+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
$index_wireless = 1;
foreach("/runtime/log/sysact/entry")
{
	$time = get("TIME.ASCTIME", "/runtime/log/sysact/entry:".$InDeX."/time");
	$msg = get("x", "/runtime/log/sysact/entry:".$InDeX."/message");
	if(substr($msg, 0, 5) == "WLAN:")
	{
		$msg_wireless = substr($msg, 5, strlen($msg)-5);
		echo "[Time]".$time;
		echo "[Message:".$index_wireless."]".$msg_wireless."\n";
		echo "--------------------------------------------------------------------------------------------\n";
		$index_wireless++;
	}
}
?>
