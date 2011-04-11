<!-- clock on/off form -->
<table width="436" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">


				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							<?php echo JText::_('CLOCK_ON_OFF'); ?>
						</td>
					</tr>
				</table>

<?php 
	$fromPopup="false";
	$destination=$_SERVER["PHP_SELF"];
	include('clockOnOff_core_new.inc')
?>

			</td>
		</tr>
	</table>

<?php
// vim:ai:ts=4:sw=4:filetype=php
?>
