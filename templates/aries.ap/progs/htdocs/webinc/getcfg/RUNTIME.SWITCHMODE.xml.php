<module>
	<service><?=$GETCFG_SVC?></service>
	<runtime>
		<device>
			<switchmode><?echo get("x", "/runtime/device/switchmode");?></switchmode>
		</device>
	</runtime>
	<SETCFG>ignore</SETCFG>
	<ACTIVATE>ignore</ACTIVATE>
</module>
