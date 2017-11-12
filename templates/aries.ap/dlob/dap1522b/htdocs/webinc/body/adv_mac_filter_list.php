<? include "/htdocs/webinc/body/draw_elements.php"; ?>
		<tr>
			<td class="centered">
				<!-- the uid of MAC_FILTER -->
				<input type="hidden" id="uid_<?=$INDEX?>" value="">				
				<?=$INDEX?>
			</td>			
			<td align=center>								
				<input type=text id="mac_<?=$INDEX?>" size=18 maxlength=17>											
			</td>
			<td class="centered">				
			 <input type="button" value="<<" class="arrow" onclick="PAGE.OnClickArrowKey(<?=$INDEX?>);" modified="ignore" />
			</td>
			<td align=center>									
 				<select id="client_list_<?=$INDEX?>" modified="ignore" style="width: 120px;">
        	<option value=""><?echo i18n("MAC Address");?></option>
          <?
          $rphyinf = XNODE_getpathbytarget("/runtime", "phyinf", "uid", "WLAN-1.1", false);
          if ($rphyinf != "")
          {
          	foreach ($rphyinf."/media/clients/entry")
            echo '<option value="'.query("macaddr").'">'.query("macaddr").'</option>';
          }
          ?>
        </select>

			<td align=center>
				<input type="button" value="<?echo i18n("Clear");?>" onclick="PAGE.ClearMAC(<?=$INDEX?>);">
			</td>			
		</tr>
