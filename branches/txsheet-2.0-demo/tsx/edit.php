<?php

if(!class_exists('Site'))die('Restricted Access');

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('Open'))return;
ppr($_POST);
//load local vars from request/post/get
$save_changes = isset($_REQUEST['save_changes']) ? $_REQUEST['save_changes']: false;
$data['task_id'] = $_REQUEST['task_id'];
$data['proj_id'] = $_REQUEST['proj_id'];
$client_id = $_REQUEST['client_id'];
$data['trans_num'] = $_REQUEST['trans_num'];
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
	$data['log_message'] = $_REQUEST['log_message'];

	$data['clock_on_time_string'] = "$clock_on_date_year-$clock_on_date_month-$clock_on_date_day $clock_on_time_hour:$clock_on_time_min:00";
	$data['clock_off_time_string'] = "$clock_off_date_year-$clock_off_date_month-$clock_off_date_day $clock_off_time_hour:$clock_off_time_min:00";

	$data['duration'] = Common::get_duration(strtotime($data['clock_on_time_string']),strtotime($data['clock_off_time_string']));
  
  $data = recursive_mysql_real_escape_string($data);
	
  $q = "UPDATE ".tbl::getTimesTable()." SET start_time='".$data['clock_on_time_string']."', ".
								"end_time='".$data['clock_off_time_string']."', ".
								"duration=".$data['duration'].", " .
								"log_message='".$data['log_message']."', ".
								"task_id='".$data['task_id']."', " .
								"proj_id='".$data['proj_id']."' " .
								"WHERE ".
								"trans_num='".$data['trans_num']."'";

	 if(debug::getSqlStatement()==1)ppr($q,'SQL');
	 $retval['status'] = Database::getInstance()->query($q);
	 $retval['id'] = mysql_insert_id(Database::getInstance()->getConnection());

	 if($retval['status'] == false && debug::getSqlError()==1){
	   Debug::ppr(mysql_error(),'sqlError');
	 }

	gotoLocation(Config::getRelativeRoot()."/daily?client_id=$client_id&amp;proj_id=".$data['proj_id']."&amp;task_id=".$data['task_id']."&amp;month=$month&amp;year=$year&amp;day=$day");
	exit;
}

//get trans info
$trans_info = Common::get_trans_info($data['trans_num']);

//There are several potential problems with the date/time data comming from the database
//because this application hasn't taken care to cast the time data into a consistent TZ.
//See: http://jokke.dk/blog/2007/07/timezones_in_mysql_and_php & read comments
//So, we handle it as best we can for now...
Common::fixStartEndDuration($trans_info);

if ($action != "saveChanges") {
	$data['proj_id'] = $trans_info["proj_id"];
	$data['task_id'] = $trans_info["task_id"];
	$client_id = $trans_info["client_id"];
}

include("include/tsx/form_input.inc");

PageElements::setHead("<title>".Config::getMainTitle()." | Edit Work Log Record for ".gbl::getContextUser()."</title>");

ob_start();
include('tsx/client_proj_task_javascript.class.php');
$js = new ClientProjTaskJavascript();
$js->printJavascript();
?>
<script type="text/javascript">
	function onSubmit() {
		//set the action
		document.editForm.submit();
	}
</script>
<?php
PageElements::setHead(PageElements::getHead().ob_get_contents());
ob_end_clean();
PageElements::setBodyOnLoad("doOnLoad();");

?>

<table width="500" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">


				<table width="100%" border="0" class="table_head">
					<tr>
						<td align="left" class="outer_table_heading">
							Edit Work Log Record:
						</td>
					</tr>
				</table>

		<form name="editForm" action="<?php echo Rewrite::getShortUri(); ?>" method="post" id="theForm">
		<input type="hidden" name="year" value="<?php echo $year; ?>" />
		<input type="hidden" name="month" value="<?php echo $month; ?>" />
		<input type="hidden" name="day" value="<?php echo $day; ?>" />
		<input type="hidden" id="client_id" name="client_id" value="<?php echo $client_id; ?>" />
		<input type="hidden" id="proj_id" name="proj_id" value="<?php echo $data['proj_id']; ?>" />
		<input type="hidden" id="task_id" name="task_id" value="<?php echo $data['task_id']; ?>" />
		<input type="hidden" name="trans_num" value="<?php echo $data['trans_num']; ?>" />
		<input type="hidden" name="action" value="saveChanges" />
	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">



		<tr>
			<td>
				<table width="100%" border="0" cellpadding="1" cellspacing="2" class="table_body">
					<tr>
						<td align="left">
							<table width="100%" border="0">
								<tr>
									<td align="left" width="100%">
											<table width="100%" border="0" cellspacing="0" cellpadding="0">
												<tr>
													<td><table width="50"><tr><td>Client:</td></tr></table></td>
													<td width="100%">
														<select id="clientSelect" name="clientSelect" onchange="onChangeClientSelect();" style="width: 100%;" />
													</td>
												</tr>
											</table>
									</td>
								</tr>
								<tr>
									<td align="left" width="100%">
											<table width="100%" border="0" cellspacing="0" cellpadding="0">
												<tr>
													<td><table width="50"><tr><td>Project:</td></tr></table></td>
													<td width="100%">
														<select id="projectSelect" name="projectSelect" onchange="onChangeProjectSelect();" style="width: 100%;" />
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
														<select id="taskSelect" name="taskSelect" onchange="onChangeTaskSelect();" style="width: 100%;" />
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
												<td align="left" class="clock_on_text">
													Start time:
												</td>
												<td align="left">
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
													<img src="images/clock-green-sml.gif" alt="" />
												</td>
											</tr>
											<tr>
												<td align="left" class="clock_off_text">
													End time:
												</td>
												<td align="left">
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
													<img src="images/clock-red-sml.gif" alt="" />
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

	</table>
		</form>

		</td>
	</tr>
</table>

