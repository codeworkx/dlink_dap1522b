<module>
	<service><?=$GETCFG_SVC?></service>
	<runtime>
		<phyinf>
<?
			include "/htdocs/phplib/xnode.php";
			$inf = XNODE_getpathbytarget("/runtime", "phyinf", "uid", cut($GETCFG_SVC,2,".").".".cut($GETCFG_SVC,3,"."), 0);
			echo dump(3, $inf);
?>		</phyinf>
	</runtime>
</module>
