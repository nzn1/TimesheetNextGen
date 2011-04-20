<?php

if(!class_exists('Site'))die('Restricted Access');

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;


$loggedInUser = strtolower($_SESSION['loggedInUser']);

//load local vars from request/post/get
$save_changes = isset($_REQUEST['save_changes']) ? $_REQUEST['save_changes']: false;
$task_id = $_REQUEST['task_id'];
$proj_id = $_REQUEST['proj_id'];
$client_id = $_REQUEST['client_id'];
$trans_num = $_REQUEST['trans_num'];
$month = $_REQUEST['month'];
$day = $_REQUEST['day'];
$year = $_REQUEST['year'];
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"]: "edit";

if ($action == "saveChanges") {
	$clock_on_date_year = $_REQUEST['clock_on_date_year'];
	$clock_on_date_month = $_REQUEST['clock_on_date_month'];
	$clock_on_date_day = $_REQUEST['clock_on_date_day'];
	$clock_off_date_year = $_REQUEST['clock_off_date_year'];
	$clock_off_date_month = $_REQUEST['clock_off_date_month'];
	$clock_off_date_day = $_REQUEST['clock_off_date_day'];
	$clock_on_time_hour = $_REQUEST['clock_on_time_hour'];
	$clock_on_time_min = $_REQUEST['clock_on_time_min'];
	$clock_off_time_hour = $_REQUEST['clock_off_time_hour'];
	$clock_off_time_min = $_REQUEST['clock_off_time_min'];
	$log_message = $_REQUEST['log_message'];

	$clock_on_time_string = "$clock_on_date_year-$clock_on_date_month-$clock_on_date_day $clock_on_time_hour:$clock_on_time_min:00";
	$clock_off_time_string = "$clock_off_date_year-$clock_off_date_month-$clock_off_date_day $clock_off_time_hour:$clock_off_time_min:00";

	$duration = get_duration(strtotime($clock_on_time_string),strtotime($clock_off_time_string));

	$queryString = "UPDATE ".tbl::getTimesTables()." SET start_time='$clock_on_time_string', ".
								"end_time='$clock_off_time_string', ".
								"duration=$duration, " .
								"log_message='$log_message', ".
								"task_id='$task_id', " .
								"proj_id='$proj_id' " .
								"WHERE ".
								"trans_num='$trans_num'";

	list($qh,$num) = dbQuery($queryString);

	gotoLocation(Config::getRelativeRoot()."/daily?client_id=$client_id&amp;proj_id=$proj_id&amp;task_id=$task_id&amp;month=$month&amp;year=$year&amp;day=$day");
	exit;
}

//get trans info
$trans_info = Common::get_trans_info($trans_num);

//There are several potential problems with the date/time data comming from the database
//because this application hasn't taken care to cast the time data into a consistent TZ.
//See: http://jokke.dk/blog/2007/07/timezones_in_mysql_and_php & read comments
//So, we handle it as best we can for now...
Common::fixStartEndDuration($trans_info);

if ($action != "saveChanges") {
	$proj_id = $trans_info["proj_id"];
	$task_id = $trans_info["task_id"];
	$client_id = $trans_info["client_id"];
}

include("include/tsx/form_input.inc");

?>
<html>
<head>
<title>Edit Work Log Record for <?php echo gbl::getContextUser(); ?></title>
<?php
include('tsx/client_proj_task_javascript.class.php');
$js = new ClientProjTaskJavascript();
$js->printJavascript();
?>
</head>
<body  onload="doOnLoad();">


<table width="500" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">


				<table width="100%" border="0" class="table_head">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Edit Work Log Record:
						</td>
					</tr>
				</table>


	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">

		<form name="editForm" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="theForm">
		<input type="hidden" name="year" value="<?php echo $year; ?>" />
		<input type="hidden" name="month" value="<?php echo $month; ?>" />
		<input type="hidden" name="day" value="<?php echo $day; ?>" />
		<input type="hidden" id="client_id" name="client_id" value="<?php echo $client_id; ?>" />
		<input type="hidden" id="proj_id" name="proj_id" value="<?php echo $proj_id; ?>" />
		<input type="hidden" id="task_id" name="task_id" value="<?php echo $task_id; ?>" />
		<input type="hidden" name="trans_num" value="<?php echo $trans_num; ?>" />
		<input type="hidden" name="action" value="saveChanges" />

		<tr>
			<td>
				<table width="100%" border="0" cellpadding="1" cellspacing="2" class="table_body">
					<tr>
						<td align="left">
							<table width="100%" border="0">
								<tr>
									<td align="left" width="100%" nowrap>
											<table width="100%" border="0" cellspacing="0" cellpadding="0">
												<tr>
													<td><table width="50"><tr><td>Client:</td></tr></table></td>
													<td width="100%">
														<select id="clientSelect" name="clientSelect" onChange="onChangeClientSelect();" style="width: 100%;" />
													</td>
												</tr>
											</table>
									</td>
								</tr>
								<tr>
									<td align="left" width="100%" nowrap>
											<table width="100%" border="0" cellspacing="0" cellpadding="0">
												<tr>
													<td><table width="50"><tr><td>Project:</td></tr></table></td>
													<td width="100%">
														<select id="projectSelect" name="projectSelect" onChange="onChangeProjectSelect();" style="width: 100%;" />
													</td>
												</tr>
											</table>
									</td>
								</tr>
								<tr>
									<td align="left" width="100%">
											<table width="100%" border="0" cellspacing="0" cellpadding="0">
												<tr>
													<td><table width="50"><tr><td>Task:</td></tr></table></td>
													<td width="100%">
														<select id="taskSelect" name="taskSelect" onChange="onChangeTaskSelect();" style="width: 100%;" />
													</td>
												</tr>
											</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<table>
								<tr>
									<td>
										<table class="clock_on_box">
											<tr>
												<td align="left" class="clock_on_text" nowrap>
													Start time:
												</td>
												<td align="left" nowrap>
													<?php $hourInput = new HourInput("clock_on_time_hour");
														$hourInput->create(date("G", $trans_info["start_stamp"])); ?>
													:
													<?php $minuteInput = new MinuteInput("clock_on_time_min");
														$minuteInput->create(date("i", $trans_info["start_stamp"])); ?>
													&nbsp;on&nbsp;
													<?php $monthInput = new MonthInput("clock_on_date_month");
														$monthInput->create(date("n", $trans_info["start_stamp"])); ?>
													,
													<?php $dayInput= new DayInput("clock_on_date_day");
														$dayInput->create(date("d", $trans_info["start_stamp"])); ?>
													&nbsp;
													<input type="text" name="clock_on_date_year" size="4" value="<?php echo date("Y", $trans_info["start_stamp"]); ?>" />
												</td>
												<td align="left">
													<img src="images/clock-green-sml.gif" alt="" >
												</td>
											</tr>
											<tr>
												<td align="left" class="clock_off_text" nowrap>
													End time:
												</td>
												<td align="left" nowrap>
													<?php $hourInput = new HourInput("clock_off_time_hour");
														$hourInput->create(date("G", $trans_info["end_stamp"])); ?>
															:
													<?php $minuteInput = new MinuteInput("clock_off_time_min");
														$minuteInput->create(date("i", $trans_info["end_stamp"])); ?>
													&nbsp;on&nbsp;
													<?php $monthInput = new MonthInput("clock_off_date_month");
														$monthInput->create(date("n", $trans_info["end_stamp"])); ?>
													,
													<?php $dayInput= new DayInput("clock_off_date_day");
														$dayInput->create(date("d", $trans_info["end_stamp"])); ?>
													&nbsp;
													<input type="text" name="clock_off_date_year" size="4" value="<?php echo date("Y", $trans_info["end_stamp"]); ?>" />
												</td>
												<td align="left">
													<img src="images/clock-red-sml.gif" alt="" >
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td colspan="3">
										Log Message:
									</td>
								</tr>
								<tr>
									<td colspan="3" align="center">
										<textarea name="log_message" cols="60" rows="5" style="width: 100%;"><?php echo trim(stripslashes($trans_info["log_message"])); ?></textarea>
									</td>
								</tr>
								<tr>
									<td>
										<table width="100%" border="0" class="table_bottom_panel">
											<tr>
												<td align="center">
													<input type="button" value="Save Changes" name="submitButton" id="submitButton" onclick="onSubmit();" />
												</td>
										</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		</form>
	</table>


		</td>
	</tr>
</table>

