<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclDaily'))return;

$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));

//bug fix - we must display all projects
$proj_id = 0;
$task_id = 0;

//get the passed date (context date)
$month = gbl::getMonth();
$day = gbl::getDay();
$year = gbl::getYear();
$startDayOfWeek = Common::getWeekStartDay();
$todayStamp = mktime(0, 0, 0, gbl::getMonth(), gbl::getDay(), gbl::getYear());

$todayStamp = strtotime(date("d M Y H:i:s",$todayStamp));
$tomorrowStamp = strtotime(date("d M Y H:i:s",$todayStamp) . " +1 days");

$layout = Common::getLayout();

if (isset($popup))
	PageElements::setBodyOnLoad("onLoad=window.open(\"".Config::getRelativeRoot()."/clock_popup?proj_id=".gbl::getProjId()."&task_id=$task_id\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=205\");");

ob_start();
?>
<title><?php echo Config::getMainTitle()." - ".ucfirst(JText::_('SIMPLE'));?></title>

<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/datetimepicker_css.js"></script>

<?php 
//The following line won't work:
//  echo "<script type=\"text/javascript\" src=\"".Config::getRelativeRoot()."/js/simple.js\"></script>\n";
//because there's php code in the js file, so, we can't load it like it's a straight javascript file
//and we can't separate that php stuff from the javascript file either, or the javascript can't
//see the hash table that is created by the php stuff.
require("js/newdaily.js");

PageElements::setHead(ob_get_contents());
ob_end_clean();
PageElements::setBodyOnLoad('populateExistingSelects();');
?>

<form name="dailyForm" action="<?php echo Config::getRelativeRoot(); ?>/daily_action" method="post">
<input type="hidden" name="year" value="<?php echo $year; ?>" />
<input type="hidden" name="month" value="<?php echo $month; ?>" />
<input type="hidden" name="day" value="<?php echo $day; ?>" />
<input type="hidden" name="startStamp" value="<?php echo $todayStamp; ?>" />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="left" nowrap="nowrap" class="outer_table_heading">
			<?php echo JText::_('TIMESHEET'); ?>
		</td>
		<td align="center" nowrap="nowrap" class="outer_table_heading">
			<?php
				$sdStr = date(JText::_('DFMT_MONTH_DAY_YEAR'),$todayStamp);
				echo ucfirst(JText::_('DAILY')).": $sdStr";
			?>
		</td>
		<td nowrap="nowrap" align="center">
			<input id="date1" name="date1" type="text" size="15" onclick="javascript:NewCssCal('date1', 'ddmmmyyyy')" 
			value="<?php echo date('d-M-Y', $todayStamp); ?>" />
			&nbsp;&nbsp;&nbsp;
			<input id="sub" type="submit" name="Change Date" value="<?php echo JText::_('CHANGE_DATE') ?>"></input>
		</td>
		<td align="right" nowrap="nowrap" >
			<!--prev / next buttons used to be here -->
		</td>
		<td align="right" nowrap="nowrap" >
			<input type="button" name="saveButton" id="saveButton" value="<?php echo ucwords(JText::_('SAVE_CHANGES'))?>" disabled="true" onClick="validate();" />
		</td>
	</tr>
</table>

<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
	<tr>
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body">
				<tr class="inner_table_head">
					<td class="inner_table_column_heading" align="center">
							<?php
								echo ucwords(JText::_('CLIENT')." / ".JText::_('PROJECT')." / ".JText::_('TASK'));
								if(strstr($layout, 'no work description') == '')
									echo ' / '.ucwords(JText::_('WORK_DESCRIPTION'));
							?>
						</td>
						<td align="center" width="2">&nbsp;</td>
						<?php
						$dstadj=Common::get_dst_adjustment($todayStamp);
						$minsinday = ((24*60*60) - $dstadj)/60;

						print "<input type=\"hidden\" id=\"minsinday_".($i+1)."\" value=\"$minsinday\" />";
						print "<td class=\"inner_table_column_heading\" align=\"center\" width=\"65\">" . JText::_('CLOCK_ON_NOW') .  "</td>";
						print "<td class=\"inner_table_column_heading\" align=\"center\" width=\"65\">" . JText::_('CLOCK_OFF_NOW') .  "</td>";
						print "<td class=\"inner_table_column_heading\" align=\"center\" width=\"65\">" . JText::_('START_TIME') .  "</td>";
						print "<td class=\"inner_table_column_heading\" align=\"center\" width=\"65\">" . JText::_('END_TIME') .  "</td>";
						?>
						<td align="center" width="2">&nbsp;</td>
						<td class="inner_table_column_heading" align="center" width="50">
							<?php echo ucfirst(JText::_('TOTAL')) ?>
						</td>
						<td align="center" width="2">&nbsp;</td>
						<td class="inner_table_column_heading" align="center" width="50">
							<?php echo ucfirst(JText::_('DELETE')) ?>
						</td>
					</tr>
<?php

	function printSpaceColumn() {
		print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>";
	}

	/*=======================================================================
	 ==================== Function PrintFormRow =============================
	 =======================================================================*/

	// taskId = $matchedPair->value1, daysArray = $matchedPair->value2
	// $allTasksDayTotals = int[7] and sums up the minutes for all tasks at one day
	// usage: provide an index to generate an empty row or ALL parameters to prefill the row
	function printFormRow($rowIndex, $layout, $data) {
		//get the current task properties
		$clientId = $data["client_id"];
		$projectId = $data["proj_id"];
		$taskId = $data["task_id"];
		$startStamp = $data["start_stamp"];
		$endStamp = $data["end_stamp"];
		$workDescription = $data["log_message"];
		$transNum = $data["trans_num"];

		$duration = 0;
		if(isset($data["duration"]) && ($data["duration"] > 0) ) {
			$duration = $data["duration"];
		}
/*
		$startsToday = (($startStamp >= $todayStamp ) && ( $startStamp < $tomorrowStamp ));
		$endsToday =   (($endStamp > $todayStamp) && ($endStamp <= $tomorrowStamp));
		$startsBeforeToday = ($startStamp < $todayStamp);
		$endsAfterToday = ($endStamp > $tomorrowStamp);

		if($startsToday && $endsToday ) {
			$dayDuration = $duration;
		} else if($startsToday && $endsAfterToday) {
			$dayDuration = Common::get_duration($startStamp, $tomorrowStamp);
		} else if( $startsBeforeToday && $endsToday ) {
			$dayDuration = Common::get_duration($todayStamp, $endStamp);
		} else if( $startsBeforeToday && $endsAfterToday ) {
			$dayDuration = Common::get_duration($todayStamp, $tomorrowStamp);
		}
*/
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

									<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onchange="onChangeClientSelect(this.id);" style="width: 100%;"></select> <br />
									<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onchange="onChangeProjectSelect(this.id);" style="width: 100%;"></select> <br />									
									<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onchange="onChangeTaskSelect(this.id);" style="width: 100%;"></select>
								</td>								
								<td align="left" style="width:auto;">
									<input type="hidden" id="odescription_row<?php echo $rowIndex; ?>" name="odescription_row<?php echo $rowIndex; ?>" value="<?php echo $workDescription; ?>" />
									<textarea rows="2" cols="4" style="width:98%;" id="description_row<?php echo $rowIndex; ?>" name="description_row<?php echo $rowIndex; ?>" onkeyup="onChangeWorkDescription(this.id);"><?php echo $workDescription; ?></textarea>
								</td>
								<?php
								break;


							case "old big work description field":
								// big work description field
								?>
								<td align="left" style="width:100px;">
									<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $clientId; ?>" />
									<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onchange="onChangeClientSelect(this.id);" style="width: 100%;"></select>
								</td>
								<td align="left" style="width:160px;">
									<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $projectId; ?>" />
									<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onchange="onChangeProjectSelect(this.id);" style="width: 100%;"></select>
									<br>
									<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $taskId; ?>" />
									<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onchange="onChangeTaskSelect(this.id);" style="width: 100%;"></select>
								</td>
								<td align="left" style="width:auto;">
									<input type="hidden" id="odescription_row<?php echo $rowIndex; ?>" name="odescription_row<?php echo $rowIndex; ?>" value="<?php echo $workDescription; ?>" />
									<textarea rows="2" style="width:100%;" id="description_row<?php echo $rowIndex; ?>" name="description_row<?php echo $rowIndex; ?>" onKeyUp="onChangeWorkDescription(this.id);"><?php echo $workDescription; ?></textarea>
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

		printSpaceColumn();

		$currentDay=0;
		//open the column

		print "<td class=\"calendar_cell_middle\" valign=\"top\" align=\"center\">o</td>";
		print "<td class=\"calendar_cell_middle\" valign=\"top\" align=\"center\">o</td>";

		$CfgTimeFormat = Common::getTimeFormat();
		if ($CfgTimeFormat == "12") {
			$formattedStartTime = date("g:iA",$startStamp);
			$formattedEndTime = date("g:iA",$endStamp);
		} else {
			$formattedStartTime = date("G:i",$startStamp);
			$formattedEndTime = date("G:i",$endStamp);
		}
		if($startStamp==0)
			$formattedStartTime='';
		if($endStamp==0)
			$formattedEndTime='';

		print "<td class=\"calendar_cell_middle\" valign=\"top\" align=\"left\">";
		////print "<span class=\"task_time_small\">";

		$curTaskHours = floor($duration / 60 );
		$curTaskMinutes = $duration - ($curTaskHours * 60);

		$isEmptyRow=!isset($data["duration"]);

		//create a string to be used in form input names
		$row = "_row" . $rowIndex;
		$disabled = $isEmptyRow?'disabled="disabled" ':'';

		print "<input type=\"hidden\" id=\"obt".$row."\" name=\"obt".$row."\" value=\"$formattedStartTime\" />";
		print "<span nowrap><input type=\"text\" id=\"hours" . $row . "\" name=\"hours" . $row . "\" size=\"8\" value=\"$formattedStartTime\" onchange=\"recalculateAll(this.id)\" onKeyDown=\"setDirty()\" $disabled />"."</span></span></td>";
		
		print "<td class=\"calendar_cell_middle\" valign=\"top\" align=\"left\">";
		//print "<span class=\"task_time_small\">";
		print "<input type=\"hidden\" id=\"oet".$row."\" name=\"oet".$row."\" value=\"$formattedEndTime\" />";
		print "<span nowrap><input type=\"text\" id=\"mins" . $row . "\" name=\"mins" . $row . "\" size=\"8\" value=\"$formattedEndTime\" onchange=\"recalculateAll(this.id)\" onKeyDown=\"setDirty()\" $disabled />"."</span></span></td>";


		printSpaceColumn();

		//format the weekly total
		$taskDurationStr = Common::formatMinutes($duration);

		//print the total column
		print "<td class=\"calendar_totals_line_weekly subtotal\" valign=\"bottom\" align=\"right\">";
		print "<span class=\"calendar_total_value_weekly\" style=\"text-align:right;\" id=\"subtotal_row" . $rowIndex . "\">$taskDurationStr</span></td>";

		printSpaceColumn();

		// print delete button
		print "<td class=\"calendar_delete_cell subtotal\">";
		print "<a id=\"delete_row$rowIndex\" href=\"#\" onclick=\"onDeleteRow(this.id); return false;\">x</a></td>\n";

		//end the row
		print "</tr>";
	}

	/*=======================================================================
	 ================ end Function PrintFormRow =============================
	 =======================================================================*/

	// Get the Weekly user data.
	$startStr = date("Y-m-d H:i:s",$todayStamp);
	$endStr = date("Y-m-d H:i:s",$tomorrowStamp);
	$order_by_str = "$CLIENT_TABLE.organisation, $PROJECT_TABLE.title, $TASK_TABLE.name";
	list($num5, $qh5) = Common::get_time_records($startStr, $endStr, gbl::getContextUser(), 0, 0, $order_by_str);

	$dailyTotal=0;
	$rowIndex = 0;
	//iterate through results
	while ($data = dbResult($qh5)) {
		//There are several potential problems with the date/time data comming from the database
		//because this application hasn't taken care to cast the time data into a consistent TZ.
		//See: http://jokke.dk/blog/2007/07/timezones_in_mysql_and_php & read comments
		//So, we handle it as best we can for now...
		Common::fixStartEndDuration($data);

		//add this task's duration to the daily total
		$dailyTotal += $data["duration"];

		printFormRow($rowIndex, $layout, $data);

		$rowIndex++;
	}

	/////////////////////////////////////////
	//add an extra row for new data entry
	/////////////////////////////////////////

	printFormRow($rowIndex, $layout, -1);

	////////////////////////////////////////////////////
	//Changes reequired to enter data on form -define 10 entry rows

//	for ($i=0; $i<10; $i

	////////////////////////////////////////////////////

	//create a new totals row
	print "<tr id=\"totalsRow\">\n";
	print "<td class=\"calendar_cell_disabled_middle\" align=\"right\">";
	print ucwords(JText::_('TOTAL_HOURS')).":</td>\n";
	print "<td class=\"calendar_cell_disabled_middle\" colspan=\"6\">&nbsp;</td>\n";

	//store a hidden form field containing the number of existing rows
	print "<input type=\"hidden\" id=\"existingRows\" name=\"existingRows\" value=\"$rowIndex\" />";
	//store a hidden form field containing the total number of rows
	print "<input type=\"hidden\" id=\"totalRows\" name=\"totalRows\" value=\"" . ($rowIndex+1) . "\" />";


	//iterate through day totals for all tasks
	$grandTotal = 0;
	$col = 1;
	$formattedTotal = Common::formatMinutes($dailyTotal);
	print "<td class=\"calendar_totals_line_weekly subtotal\" nowrap=\"nowrap\" valign=\"bottom\" align=\"right\">";
	print "<span class=\"calendar_total_value_weekly\" style=\"text-align:right;\" id=\"daily_total\" >$formattedTotal</span></td>";
	printSpaceColumn();
	//print "<td class=\"calendar_totals_line_weekly_right\" align=\"right\">\n";
	//print "<span class=\"calendar_total_value_weekly\" id=\"subtotal_col" . $col . "\">$formattedTotal</span></td>";
	//print "<td class=\"calendar_cell_disabled_right\">&nbsp;</td>\n";
	print "</tr>";


?>

			</table>
		</td>
	</tr>
</table>

</form>
