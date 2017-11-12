<module>
	<service><?=$GETCFG_SVC?></service>
	<wifi>
<?		echo dump(2, "/wifi");
?>	</wifi>
<?
	foreach("/phyinf")
	{
		$s = cut(query("uid"), 0, "-");
		if ($s == "WLAN" || $s == "WDS")
		{
			echo '\t<phyinf>\n';
			echo dump(2, "/phyinf:".$InDeX);
			echo '\t</phyinf>\n';
		}
	}
?></module>
