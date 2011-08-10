<?php
  require("include/tsx/taskinfo.class.php");
  
class SimplePage{


  private $allTasksDayTotals;
  
  public function getAllTasksDayTotals(){
    return $this->allTasksDayTotals;
  }
  public function setAllTasksDayTotals($a){
    $this->allTasksDayTotals = $a;
  }
  
  public function __construct(){
  
  }

	public function printSpaceColumn() {
		print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>";
	}

  	/*=======================================================================
	 ==================== Function PrintFormRow =============================
	 =======================================================================*/

	// taskId = $matchedPair->value1, daysArray = $matchedPair->value2
	// $allTasksDayTotals = int[7] and sums up the minutes for all tasks at one day
	// usage: provide an index to generate an empty row or ALL parameters to prefill the row
	public function printFormRow($rowIndex, $layout, $projectId = "", $taskId = "", $workDescription = "", $startDate = null, $daysArray = NULL) {
		// print project, task and optionally work description

		$clientId="";
		?>
		<tr id="row<?php echo $rowIndex; ?>">
			<td class="calendar_cell_middle" valign="top">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr id="clientProjectTaskDescrArea<?php echo $rowIndex;?>">
					<?php
						switch ($layout) {
							case "no work description field":
								?>
								<td align="left" style="width:33%;">
									<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $clientId; ?>" />
									<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onchange="onChangeClientSelect(this.id);" style="width: 100%;"></select>
								</td>
								<td align="left" style="width:33%;">
									<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $projectId; ?>" />
									<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onchange="onChangeProjectSelect(this.id);" style="width: 100%;"></select>
								</td>
								<td align="left" style="width:33%;">
									<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $taskId; ?>" />
									<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onchange="onChangeTaskSelect(this.id);" style="width: 100%;"></select>
								</td>
								<?php
								break;

							case "big work description field":
								// big work description field
								?>
								<td align="left" style="width:50px;">
									<p>Client:</p>                  
									<p>Project:</p>					
									<p>Task:</p>
								</td>
								<td align="left" style="width:160px;">
									<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $clientId; ?>" />
									<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $projectId; ?>" />
                  <input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $taskId; ?>" />
									<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onchange="onChangeClientSelect(this.id);" style="width: 100%;"></select>
                  <br />
									<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onchange="onChangeProjectSelect(this.id);" style="width: 100%;"></select>
									<br />									
									<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onchange="onChangeTaskSelect(this.id);" style="width: 100%;"></select>
								</td>								
								<td align="left" style="width:auto;">
									<input type="hidden" id="odescription_row<?php echo $rowIndex; ?>" name="odescription_row<?php echo $rowIndex; ?>" value="<?php echo $workDescription; ?>" />
									<textarea rows="2" cols="4" style="width:98%;" id="description_row<?php echo $rowIndex; ?>" name="description_row<?php echo $rowIndex; ?>" onkeyup="onChangeWorkDescription(this.id);"><?php echo $workDescription; ?></textarea>
								</td>
								<?php
								break;

							case "small work description field":
							default:
								// small work description field = default layout
								?>
								<td align="left" style="width:100px;">
									<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $clientId; ?>" />
									<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onchange="onChangeClientSelect(this.id);" style="width: 100%;"></select>
								</td>
								<td align="left" style="width:100px;">
									<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $projectId; ?>" />
									<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onchange="onChangeProjectSelect(this.id);" style="width: 100%;"></select>
								</td>
								<td align="left" style="width:140px;">
									<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $taskId; ?>" />
									<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onchange="onChangeTaskSelect(this.id);" style="width: 100%;"></select>
								</td>
								<td align="left" style="width:auto;">
									<input type="hidden" id="odescription_row<?php echo $rowIndex; ?>" name="odescription_row<?php echo $rowIndex; ?>" value="<?php echo $workDescription; ?>" />
									<input type="text" id="description_row<?php echo $rowIndex; ?>" name="description_row<?php echo $rowIndex; ?>" onchange="onChangeWorkDescription(this.id);" value="<?php echo $workDescription; ?>" style="width: 100%;" />
								</td>
								<?php
								break;
						}

					?>
					</tr>
				</table>
			</td>
		<?php

		$this->printSpaceColumn();

		$weeklyTotal = 0;
		$isEmptyRow = ($daysArray == null);

		//print_r($daysArray); print "<br />";

		//print hours and minutes input field for each day

		for ($currentDay = 0; $currentDay < 7; $currentDay++) {
			//open the column
			print "<td class=\"calendar_cell_middle\" valign=\"top\" align=\"left\">";

			//while we are printing times set the style
			print "<span class=\"task_time_small\">";

			//declare current days vars
			$curDaysTotal = 0;
			$curDaysHours = "";
			$curDaysMinutes = "";
			$times = 0; // used to store the trans_num of each time record.

			// if there is an $daysArray calculate current day's minutes and hours

			if (!$isEmptyRow) {
				$currentDayArray = $daysArray[$currentDay];

				foreach ($currentDayArray as $taskDuration) {
					$curDaysTotal += $taskDuration;
				}
				$curDaysHours = floor($curDaysTotal / 60 );
				$curDaysMinutes = $curDaysTotal - ($curDaysHours * 60);
			}

			// write summary and totals of this row

			//create a string to be used in form input names
			$rowCol = "_row" . $rowIndex . "_col" . ($currentDay+1);
			$disabled = $isEmptyRow?'disabled="disabled" ':'';

			print "<input type=\"hidden\" id=\"ohours".$rowCol."\" name=\"ohours".$rowCol."\" value=\"$curDaysHours\" />";
			print "<input type=\"hidden\" id=\"omins".$rowCol."\" name=\"omins".$rowCol."\" value=\"$curDaysMinutes\" />";
			print "<input type=\"hidden\" id=\"tid".$rowCol."\" name=\"tid".$rowCol."\" value=\"$times\" />";
			print "<span><input type=\"text\" id=\"hours" . $rowCol . "\" name=\"hours" . $rowCol . "\" size=\"1\" value=\"$curDaysHours\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" $disabled />".JText::_('HR')."</span>";
			print "<span><input type=\"text\" id=\"mins" . $rowCol . "\" name=\"mins" . $rowCol . "\" size=\"1\" value=\"$curDaysMinutes\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" $disabled />".JText::_('MN')."</span>";

			//close the times class
			print "</span>";

			//end the column
			print "</td>";

			//add this days total to the weekly total
			$weeklyTotal += $curDaysTotal;

			// add this days total to the all tasks total for this day
			// if an array is provided by the caller
			if ($this->allTasksDayTotals != null) {
				$this->allTasksDayTotals[$currentDay] += $curDaysTotal;
			}
		}

		$this->printSpaceColumn();

		//format the weekly total
		$weeklyTotalStr = Common::formatMinutes($weeklyTotal);

		//print the total column
		print "<td class=\"calendar_totals_line_weekly subtotal\" valign=\"bottom\" align=\"right\">";
		print "<span class=\"calendar_total_value_weekly\" style=\"text-align:right;\" id=\"subtotal_row" . $rowIndex . "\">$weeklyTotalStr</span></td>";

		$this->printSpaceColumn();

		// print delete button
		print "<td class=\"calendar_delete_cell subtotal\" >";
		print "<a id=\"delete_row$rowIndex\" href=\"#\" onclick=\"onDeleteRow(this.id); return false;\">x</a></td>\n";

		//end the row
		print "</tr>";
	}

	/*=======================================================================
	 ================ end Function PrintFormRow =============================
	 =======================================================================*/

}


?>