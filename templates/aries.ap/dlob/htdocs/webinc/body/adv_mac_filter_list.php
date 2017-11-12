<? include "/htdocs/webinc/body/draw_elements.php"; ?>
		<tr>
			<td class="centered">
				<!-- the uid of MAC_FILTER -->
				<input type="hidden" id="uid_<?=$INDEX?>" value="">				
				<?=$INDEX?>
			</td>			
			<td class="centered">								
				<input type=text id="description_<?=$INDEX?>" value="" size=10 maxlength=10>												
			</td>
			<td align=center>									
				<input type=text id="mac1_<?=$INDEX?>" size=2 maxlength=2>:
				<input type=text id="mac2_<?=$INDEX?>" size=2 maxlength=2>:
				<input type=text id="mac3_<?=$INDEX?>" size=2 maxlength=2>:
				<input type=text id="mac4_<?=$INDEX?>" size=2 maxlength=2>:
				<input type=text id="mac5_<?=$INDEX?>" size=2 maxlength=2>:
				<input type=text id="mac6_<?=$INDEX?>" size=2 maxlength=2>
			</td>
			<td align=center>
				<input type="button" value="clear" onclick="PAGE.ClearMAC(<?=$INDEX?>);">
			</td>			
		</tr>
