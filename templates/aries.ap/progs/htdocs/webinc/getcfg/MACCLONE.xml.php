<module>
	<service><?=$GETCFG_SVC?></service>
	<phyinf>
		<macclone><?
		include "/htdocs/phplib/xnode.php";
		$phyinf = XNODE_getpathbytarget("", "phyinf", "uid", cut($GETCFG_SVC,1,"."), 0);
		if ($phyinf!="") echo dump(3, $phyinf."/macclone");
?>		</macclone>
	</phyinf>
	<ACTIVATE>ignore</ACTIVATE>	
</module>