<?php
  require("include/tsx/taskinfo.class.php");
  
class SimplePage{


  private $allTasksDayTotals;
  
  public function __construct(){
  
  }

	function printSpaceColumn() {
		echo "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>";
	}
	function getAllTasksDayTotals(){
    	return $allTasksDayTotals;
  }
  function setAllTasksDayTotals($a){
    $allTasksDayTotals = $a;
  }

// usage: provide an index to generate an empty row or ALL parameters to prefill the row
/*=======================================================================
==================== Function PrintFormRow =============================
=======================================================================*/

// taskId = $matchedPair->value1, daysArray = $matchedPair->value2

// usage: provide an index to generate an empty row or ALL parameters to prefill the row
function printFormRow($rowIndex, $layout, $data) {
	// print project, task and optionally work description
	//LogFile::write("printFormRow Layout: ". $layout);
	?>
	<tr id="row<?php echo $rowIndex; ?>">

			<?php
				switch ($layout) {
					case "no work description field":
						?>
						<td align="left" style="width:33%;">
							<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $data['client_id']; ?>" />
							<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onChange="onChangeClientSelect(this.id);" style="width: 100%;" />
						</td>
						<td align="left" style="width:33%;">
							<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $data['proj_id']; ?>" />
							<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onChange="onChangeProjectSelect(this.id);" style="width: 100%;" />
						</td>
						<td align="left" style="width:33%;">
							<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $data['task_id']; ?>" />
							<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onChange="onChangeTaskSelect(this.id);" style="width: 100%;" />
						</td>
						<?php
						break;

					case "big work description field":
						// big work description field
						?>
						<td align="left" style="width:100px;">
							<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $data['client_id']; ?>" />
							<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onChange="onChangeClientSelect(this.id);" style="width: 100%;" />
						</td>
						<td align="left" style="width:160px;">
							<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $data['proj_id']; ?>" />
							<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onChange="onChangeProjectSelect(this.id);" style="width: 100%;" />
							<br/>
							<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $data['task_id']; ?>" />
							<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onChange="onChangeTaskSelect(this.id);" style="width: 100%;" />
						</td>
						<td align="left" style="width:auto;">
							<textarea rows="2" style="width:100%;" id="description_row<?php echo $rowIndex; ?>" name="description_row<?php echo $rowIndex; ?>" onKeyUp="onChangeWorkDescription(this.id);"><?php echo $data['log_message']; ?></textarea>
						</td>
						<?php
						break;

					case "small work description field":
					default:
						// small work description field = default layout
						?>
						<td align="left">
							<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $data['client_id']; ?>" />
							<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onChange="onChangeClientSelect(this.id);" />
						</td>
						<td align="left">
							<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $data['proj_id']; ?>" />
							<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onChange="onChangeProjectSelect(this.id);"  />
						</td>
						<td align="left" >
							<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $data['task_id']; ?>" />
							<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onChange="onChangeTaskSelect(this.id);" />
						</td>
						<td align="left" >
							<input type="text" id="description_row<?php echo $rowIndex; ?>" name="description_row<?php echo $rowIndex; ?>" onChange="onChangeWorkDescription(this.id);" value="<?php echo $data['log_message']; ?>" />
						</td>
						<?php
						break;
				}

	
	} // End function printFormRow
	
 
  	function finishRow($rowIndex, $colIndex, $rowTotal, $disable) {
		$allTasksDayTotals =null;
  		if($disable == "yes" )
  			$disabled = 'disabled="disabled" ';
  		else 
  			$disabled = '';
  		for ($currentDay = $colIndex; $currentDay < 8; $currentDay++) {
			//open the column
			print "<td class=\"calendar_cell_middle\" valign=\"top\" align=\"left\">";
				//while we are printing times set the style
			print "<span class=\"task_time_small\">";
				//create a string to be used in form input names
			$rowCol = "_row" . $rowIndex . "_col" . ($currentDay);
		
			print "<input type=\"hidden\" id=\"tid".$rowCol."\" name=\"tid".$rowCol."\" value=\"0\" />";
			print "<span><input type=\"text\" id=\"hours" . $rowCol . "\" name=\"hours" . $rowCol . "\" size=\"1\" value=\"\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\"". $disabled . "/>".JText::_('HR')."</span>";
			print "<span><input type=\"text\" id=\"mins" . $rowCol . "\" name=\"mins" . $rowCol . "\" size=\"1\" value=\"\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\"". $disabled . "/>".JText::_('MN')."</span>";
				//close the times class
			print "</span>";
				//end the column
			print "</td>";

		}
		$this->printSpaceColumn();
		//print the total column
		$weeklyTotalStr = Common::formatMinutes($rowTotal);
		print "<td class=\"calendar_totals_line_weekly\" valign=\"bottom\" align=\"right\" class=\"subtotal\">";
		print "<span class=\"calendar_total_value_weekly\" align=\"right\" id=\"subtotal_row" . $rowIndex . "\">$weeklyTotalStr</span></td>";
		
		$this->printSpaceColumn();
		// print delete button
		print "<td class=\"calendar_delete_cell\" class=\"subtotal\">";
		print "<a id=\"delete_row$rowIndex\" href=\"#\" onclick=\"onDeleteRow(this.id); return false;\">x</a></td>";
	
		//end the row
		print "</tr>";
	}


  public function printTime($rowIndex, $currentDay, $trans_num, $hours, $minutes) {
  		
		//open the column
		echo "<td class=\"calendar_cell_middle\" valign=\"top\" align=\"left\">\n";

		//while we are printing times set the style
		echo "<span class=\"task_time_small\">\n";

		//create a string to be used in form input names
		$rowCol = "_row" . $rowIndex . "_col" . ($currentDay);
		if ($trans_num ==  -1)
			$disabled = 'disabled="disabled" ';
		else 
			$disabled = '';

		echo "<input type=\"hidden\" id=\"tid".$rowCol."\" name=\"tid".$rowCol."\" value=\"$trans_num\" />";
		if ($trans_num != 0) { //print a valid field 
			echo "<span><input type=\"text\" id=\"hours" . $rowCol . "\" name=\"hours" . $rowCol . "\" size=\"1\" value=\"$hours\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" $disabled />".JText::_('HR')."</span>\n";
			echo "<span><input type=\"text\" id=\"mins" . $rowCol . "\" name=\"mins" . $rowCol . "\" size=\"1\" value=\"$minutes\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" $disabled />".JText::_('MN')."</span>\n";
		}
		else { // print an empty field
			echo "<span><input type=\"text\" id=\"hours" . $rowCol . "\" name=\"hours" . $rowCol . "\" size=\"1\" value=\"\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" $disabled />".JText::_('HR')."</span>\n";
			echo "<span><input type=\"text\" id=\"mins" . $rowCol . "\" name=\"mins" . $rowCol . "\" size=\"1\" value=\"\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" $disabled />".JText::_('MN')."</span>\n";
		}
		//close the times class
		echo "</span>\n";

		//end the column
		echo "</td>\n";
	}
	
}
?>