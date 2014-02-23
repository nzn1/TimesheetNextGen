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

// usage: provide an index to generate an empty row or ALL parameters to prefill the row with client/proj/task/log entry
function printFormRow($rowIndex, $layout, $data) {
	// print project, task and optionally work description
	//LogFile::write("printFormRow Layout: ". $layout);
	if (($rowIndex % 2) == 1)
			echo "<tr id=\"row".$rowIndex."\" class=\"diff\">\n";
		else
			echo "<tr id=\"row".$rowIndex."\">\n";
  ?>

			<?php
				switch ($layout) {
					case "no work description field":
						?>
						<td>
							<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $data['client_id']; ?>" />
							<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onchange="onChangeClientSelect(this.id);" style="width: 100%;">
							<option value="-1">None</option></select>
						</td>
						<td>
							<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $data['proj_id']; ?>" />
							<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onchange="onChangeProjectSelect(this.id);" style="width: 100%;">
							<option value="-1">None</option></select>
						</td>
						<td>
							<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $data['task_id']; ?>" />
							<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onchange="onChangeTaskSelect(this.id);" style="width: 100%;">
							<option value="-1">None</option></select>
						</td>
						<?php
						break;

					case "big work description field":
						// big work description field
						?>
						<td align="left" style="width:100px;">
							<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $data['client_id']; ?>" />
							<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onchange="onChangeClientSelect(this.id);" style="width: 100%;">
							<option value="-1">None</option></select>
						</td>
						<td align="left" style="width:160px;">
							<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $data['proj_id']; ?>" />
							<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onchange="onChangeProjectSelect(this.id);" style="width: 100%;">
							<option value="-1">None</option></select>
							<br/>
							<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $data['task_id']; ?>" />
							<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onchange="onChangeTaskSelect(this.id);" style="width: 100%;">
							<option value="-1">None</option></select>
						</td>
						<td align="left" style="width:auto;">
							<textarea rows="2" style="width:100%;" id="description_row<?php echo $rowIndex; ?>" name="description_row<?php echo $rowIndex; ?>" onKeyUp="onChangeWorkDescription(this.id);"><?php echo nl2br($data['log_message']); ?></textarea>
						</td>
						<?php
						break;

					case "small work description field":
					default:
						// small work description field = default layout
						?>
						<td class="calendar_cell_middle">
							<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $data['client_id']; ?>" />
							<select style="width:100%;" id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onchange="onChangeClientSelect(this.id);">
							<option value="-1">None</option></select>
						</td>
						<td class="calendar_cell_middle">
							<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $data['proj_id']; ?>"/>
							<select style="width:100%;" id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onchange="onChangeProjectSelect(this.id);">
							<option value="-1">None</option></select>
						</td>
						<td class="calendar_cell_middle">
							<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $data['task_id']; ?>" />
							<select style="width:100%;" id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onchange="onChangeTaskSelect(this.id);">
							<option value="-1">None</option></select>
						</td>
						<td class="calendar_cell_middle" style="padding-left:5px;padding-right:5px;">
							<input type="hidden" id="desc_row<?php echo $rowIndex; ?>" name="desc_row<?php echo $rowIndex; ?>" value="0" />
							<input style="width:99%;" type="text" id="description_row<?php echo $rowIndex; ?>" name="description_row<?php echo $rowIndex; ?>" onchange="onChangeWorkDescription(this.id);" value="<?php echo $data['log_message']; ?>" />
						</td>
						<?php
						break;
				}

	
	} // End function printFormRow
	
 
  	function finishRow($rowIndex, $colIndex, $rowTotal, $status, $disable) {
		$allTasksDayTotals =null;
  		if($disable == "yes" )
  			$disabled = 'disabled="disabled" ';
  		else 
  			$disabled = '';
  		if ($colIndex <= 7) { // not at end of a row, fill empty columns
	  		for ($currentDay = $colIndex; $currentDay < 8; $currentDay++) {
				$rowCol = "_row" . $rowIndex . "_col" . ($currentDay);
        //open the column
        //while we are printing times set the style
        //create a string to be used in form input names
        ?>
				<td class="calendar_cell_middle">
				  <span class="task_time_small">
  				<?php			
  				print "<input type=\"hidden\" class=task_time_small id=\"tid".$rowCol."\" name=\"tid".$rowCol."\" value=\"0\"/>";
  				print "<input type=\"text\" class=task_time_small id=\"hours" . $rowCol . "\" name=\"hours" . $rowCol . "\" size=\"1\" value=\"\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" ". $disabled . "/>".JText::_('HR')."";
  				print "<input type=\"text\" class=task_time_small id=\"mins" . $rowCol . "\" name=\"mins" . $rowCol . "\" size=\"1\" value=\"\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" ". $disabled . "/>".JText::_('MN');
  				?>
          </span>
				</td>
	    <?php
			 }
  		}
		$this->printSpaceColumn();
		//print the total column
		$weeklyTotalStr = Common::formatMinutes($rowTotal);
		print "<td class=\"calendar_totals_line_weekly_right\" valign=\"bottom\" align=\"right\">";
		print "<span class=\"calendar_total_value_weekly\" id=\"subtotal_row" . $rowIndex . "\">$weeklyTotalStr</span></td>";

		$this->printSpaceColumn();
		
		// print delete button
		print "<td class=\"calendar_delete_cell\">";
		print "<a id=\"delete_row$rowIndex\" href=\"#\" onclick=\"onDeleteRow(this.id); return false;\">x</a></td>";
	
		//end the row
		print "</tr>";
	}


  public function printTime($rowIndex, $currentDay, $trans_num, $hours, $minutes, $status) {
  		
  		// select the style depending on the ststus of the times record
		switch ($status) {
			case "Open":
				$style = "task_time_small";
				$color = "#00066F";
				break;
			case "Submitted":
				$style = "task_time_small_subbed";
				$color = "#FFC60C";
				break;
			case "Approved":
				$style = "task_time_small_appr";
				$color = "#20CD3A";
		}
		//open the column
		//echo "<td class=\"calendar_cell_middle\" valign=\"top\" align=\"left\">\n";
  		echo "<td class=\"calendar_cell_middle\">\n";
  		
  		//while we are printing times set the style
		echo "<span class=\"$style\">";
		//create a string to be used in form input names
		$rowCol = "_row" . $rowIndex . "_col" . ($currentDay);
	
		if ($trans_num == "n") { // if copy previous data is selected
			$trans_num = 1; // ensure the data is printed, and set trans_num to zero in hidden field to simulate new data
			echo "<input type=\"hidden\" id=\"tid".$rowCol."\" name=\"tid".$rowCol."\" value=\"0\"/>";
		}
		else  
			echo "<input type=\"hidden\" id=\"tid".$rowCol."\" name=\"tid".$rowCol."\" value=\"".$trans_num."\"/>";
		$disabled = '';

		if ($trans_num != 0) { //print a valid field 
			echo "<input type=\"text\" class=\"$style\" id=\"hours" . $rowCol . "\" name=\"hours" . $rowCol . "\" size=\"1\" value=\"$hours\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" $disabled/>".JText::_('HR')."";
			echo "<input type=\"text\" class=\"$style\" id=\"mins" . $rowCol . "\" name=\"mins" . $rowCol . "\" size=\"1\" value=\"$minutes\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" $disabled/>".JText::_('MN')."";
		}
		else { // print an empty field
			echo "<input type=\"text\" class=\"$style\" id=\"hours" . $rowCol . "\" name=\"hours" . $rowCol . "\" size=\"1\" value=\"\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" $disabled/>".JText::_('HR')."";
			echo "<input type=\"text\" class=\"$style\" id=\"mins" . $rowCol . "\" name=\"mins" . $rowCol . "\" size=\"1\" value=\"\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" $disabled/>".JText::_('MN')."";
		}
		//close the times class
		echo "</span>";

		//end the column
		echo "</td>\n";
	}
	
  public function printEmpty($rowIndex, $currentDay) {
  		
		//open the column
		//echo "<td class=\"calendar_cell_middle\" valign=\"top\" align=\"left\">\n";
		echo "<td class=\"calendar_cell_middle\">";
  	
		//while we are printing times set the style
		echo "<span class=task_time_small>\n";
		//create a string to be used in form input names
		$rowCol = "_row" . $rowIndex . "_col" . ($currentDay);
		echo "<input type=\"hidden\" id=\"tid".$rowCol."\" name=\"tid".$rowCol."\" value=\"0\"/>";
		$disabled = '';

		echo "<input type=\"text\" class=task_time_small id=\"hours" . $rowCol . "\" name=\"hours" . $rowCol . "\" size=\"1\" value=\"\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" $disabled/>".JText::_('HR')."";
		echo "<input type=\"text\" class=task_time_small id=\"mins" . $rowCol . "\" name=\"mins" . $rowCol . "\" size=\"1\" value=\"\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" $disabled/>".JText::_('MN')."";

		//close the times class
		echo "</span>\n";

		//end the column
		echo "</td>\n";
	}
}
?>