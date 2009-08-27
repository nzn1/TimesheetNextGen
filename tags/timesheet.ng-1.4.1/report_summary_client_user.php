<?php
require("class.AuthenticationManager.php");   // Authenticate
require("class.CommandMenu.php");
include("timesheet_menu.inc");                //define the command menu

/**********************************************************************************************************
 * FUNCTIONS
 *
 *
 * Format the number of seconds in HH:MI format, or in H.F format
 */
function format_seconds($seconds,$mode = 'time')
{
	$temp = $seconds;
	$hour = (int) ($temp / (60*60));

	if ($hour < 10)
		$hour = '0'. $hour;

	$temp -= (60*60)*$hour;
	$minutes = (int) ($temp / 60);

	if ($minutes < 10)
		$minutes = '0'. $minutes;

	$temp -= (60*$minutes);
	$sec = $temp;

	if ($sec < 10)
		$sec = '0'. $sec;		// Totally wierd PHP behavior.  There needs to
						// be a space after the . operator for this to work.
	//return "$hour:$minutes:$sec";
	if($mode == 'time')
		return "$hour:$minutes";
	else
		return round(($hour * 60 + $minutes)/60,1);
}

/*
 * Print the next/previous month links.
 *
 * There is a similar function in common.inc, but
 * I've got my own function here because I wanted the url params to be differenct, 
 * but didn't want to modify the base function
 */
function printPrevNextLinks($next_week, $prev_week, $next_month, $prev_month, $post, $mode = 'all')
{
	print "<a href=\"$_SERVER[PHP_SELF]?";
	if ($post) print "$post";
	print "&start_month=".date('n',$prev_month).
			"&start_year=".date('Y',$prev_month).
			"&start_day=1".
			"&mode=$mode\" class=\"outer_table_action\">Prev Month</a>&nbsp;/&nbsp;";
	print "<a HREF=\"$_SERVER[PHP_SELF]?";
	if ($post) print "$post";
	print "&start_month=".date('n',$next_month).
			"&start_year=".date('Y',$next_month).
			"&start_day=1".
			"&mode=".$mode."\" class=\"outer_table_action\">Next Month</a>";
}



/**********************************************************************************************************
 * AUTHENTICATION
 *
 * Redirect if the user is not logged in, or doesn't not have appropriate permission
 */
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasAccess('aclReports')) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&clearanceRequired=" . get_acl_level('aclReports'));
	exit;
}



//=========================================================================================================
// SETUP VARIABLES
//
// Connect to database.
$dbh = dbConnect();

//default client
if ($client_id == 0)
	$client_id = 2;

//load local vars from superglobals
if (isset($_REQUEST['uid']))
	$uid = $_REQUEST['uid'];
else
	$uid = strtolower($_SESSION['contextUser']);

//get start and end dates for the calendars
$start_day   = isset($_GET['start_day'])   && $_GET['start_day']   ? (int)$_GET['start_day']   : 1;
$start_month = isset($_GET['start_month']) && $_GET['start_month'] ? (int)$_GET['start_month'] : date('n');
$start_year  = isset($_GET['start_year'])  && $_GET['start_year']  ? (int)$_GET['start_year']  : date('Y');

$end_day     = isset($_GET['end_day'])     && $_GET['start_day']   ? (int)$_GET['end_day']     : (isset($_GET['start_month']) ? date('t',strtotime(date('Y') . '/' . (int)$_GET['start_month'] . '/' . 1)) : date('t'));
$end_month   = isset($_GET['end_month'])   && $_GET['start_month'] ? (int)$_GET['end_month']   : (isset($_GET['start_month']) ? (int)$_GET['start_month'] : date('n'));
$end_year    = isset($_GET['end_year'])    && $_GET['start_year']  ? (int)$_GET['end_year']    : (isset($_GET['start_year'])  ? (int)$_GET['start_year']  : date('Y'));

//export data to excel (or not)
$export_excel = isset($_GET["export_excel"]) ? (bool)$_GET["export_excel"] : false;

//define working variables
$last_proj_id = -1;
$last_task_id = -1;
$total_time = 0;
$grand_total_time = 0;



//=========================================================================================================
// Calculate the previous month.
//
// This function is a bit odd since it modifies it's parameters. Send it a copy of the start date instead
// because we don't want that to be modified
$start_day_tmp = $start_day;

setReportDate($start_year, $start_month, $start_day_tmp, $next_week, $prev_week, $next_month, $prev_month, $time);



// if exporting data to excel, print appropriate headers. Ensure the numbers written in the spreadsheet
// are in H.F format rather than HH:MI
if($export_excel){
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=\"Timesheet_" . date("Y-m").".xls" . "\"");
	header("Pragma: no-cache"); 
	$time_format_mode = 'integer';
}
else
	$time_format_mode = 'time';

?>
<html>
<head>
<title>Report: Timesheet Summary, <?php echo date('F Y');?></title>
<?php if(!$export_excel){include ("header.inc");} ?>
	<style type="text/css">
		/*
		 * These CSS styles should be moved to an external stylesheet. For now,
		 * I wanted this report to be stand-alone, so I have included them inline.
		 */
		table.report {
			border-collapse:collapse;
			margin:1em;
		}
		table.report th.project{
			text-align:center;
			font-style:italic;
		}
		table.report td, table.report th{
			padding:0.2em 0.7em;
			text-align:right;
			border:1px solid #5271CC;
		}
		table.report th.first{
			border:none;
		}
		table.report tr.weekend td{
			background-color:#CCCCCC;
		}
		table.report th {
			text-align:left;
			font-size:0.7em;
		}
		table.report td.grandtotal{
			color:red;
			font-size:0.8em;
		}

		@media print{
			div#header { display:none; }
			div#footer { display:none; }
			a.export { display:none; }
			td.print { display:none; }
			td.next_prev_links {display:none;}
		}
	</style>
	<?php if(!$export_excel){?>
	<script type="text/javascript">
	   /*
		* Setup Javascript events for date drop-down lists. Again, this should be included in
		* an external js file, but for now I want this report to be self-contained
		*/

		//run init function on window load
		window.onload = init;

		//gets a DOM object by it's name
		function getObjectByName(sName){				
			if(document.getElementsByName){
				oElements = document.getElementsByName(sName);

				if(oElements.length > 0)
					return oElements[0];
				else
					return null;
			}
			else if(document.all)
				return document.all[sName][0];
			else if(document.layers)
				return document.layers[sName][0];
			else
				return null;
		}

		//apply auto-submit behaviour when changing date values
		function init(){
			var start_day   = getObjectByName('start_day');
			var start_month = getObjectByName('start_month');
			var start_year  = getObjectByName('start_year');
			var end_day     = getObjectByName('end_day');
			var end_month   = getObjectByName('end_month');
			var end_year    = getObjectByName('end_year');

			start_day.onchange   = function (){this.form.submit();};
			start_month.onchange = function (){this.form.submit();};
			start_year.onchange  = function (){this.form.submit();};
			end_day.onchange     = function (){this.form.submit();};
			end_month.onchange   = function (){this.form.submit();};
			end_year.onchange    = function (){this.form.submit();};
		}
	</script>
	<?php } ?>
</head>
<body <?php include ("body.inc"); ?> >
<?php if(!$export_excel){ ?>
<div id="header">
<?php include ("banner.inc"); ?>
</div>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
<input type="hidden" name="mode" value="<?php echo $mode; ?>">
<input type="hidden" name="laser" value="<?php echo 'laser'; ?>">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
			<!-- include the timesheet face up until the heading start section -->
			<?php include("timesheet_face_part_1.inc"); ?>
				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap>
							<table width="100%" height="100%" border="0" cellpadding="1" cellspacing="2">
								<tr>
									<td align="right" width="0" class="outer_table_heading">Client:</td>
									<td align="left" width="100%">
										<?php client_select_droplist($client_id, false); ?>
									</td>
								</tr>
								<tr>
									<td align="right" width="0" class="outer_table_heading">User:</td>
									<td align="left" width="100%">
										<?php user_select_droplist($uid, false); ?>
									</td>
								</tr>
							</table>
						</td>
						<td>
							<table border="0" cellpadding="1" cellspacing="2">
								<tr>
									<td align="right" width="0" class="outer_table_heading">Start Date:</td>
									<td align="left"><?php day_button("start_day",$start_day); month_button("start_month",$start_month); year_button("start_year",$start_year); ?></td>
								</tr>
								<tr>
									<td align="right" width="0" class="outer_table_heading">End Date:</td>
									<td align="left"><?php day_button("end_day",$end_day); month_button("end_month",$end_month); year_button("end_year",$end_year); ?></td>
								</tr>
							</table>
						</td>
						<td align="center" nowrap class="outer_table_heading">
							<?php echo date('F Y',strtotime($start_year . '/' . $start_month . '/' . $start_day));?>
						</td>
						<td  align="center" >
						<a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $_SERVER["QUERY_STRING"];?>&export_excel=1" class="export"><img src="images/export_data.gif" name="esporta_dati" border=0> Export to Excel</a>
						</td>
						<td align="right" class="next_prev_links" nowrap>
						<?php
							printPrevNextLinks($next_week, $prev_week, $next_month, $prev_month, "client_id=$client_id&uid=$uid", $mode);
						?>
						</td>
					</tr>
				</table>

				<!-- include the timesheet face up until the heading start section -->
				<?php include("timesheet_face_part_2.inc"); ?>

				<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
					<tr>
						<td>
<?php
} //end export_excel


// ==========================================================================================================
// FETCH REPORT DATA AND DISPLAY
//
// Change the date-format for internationalization
$query = "  SELECT DATE_FORMAT(t.start_time, '%Y/%m/%d') AS starting_date,
				   t.proj_id,
				   p.title as proj_title,
				   s.task_id,
				   s.name as task_title,
				   SUM(UNIX_TIMESTAMP(t.end_time) - UNIX_TIMESTAMP(t.start_time)) AS diff,
				   SEC_TO_TIME(SUM(UNIX_TIMESTAMP(t.end_time) - UNIX_TIMESTAMP(t.start_time))) AS diff_time
			FROM       $USER_TABLE    u
			INNER JOIN $TIMES_TABLE   t ON t.uid     = u.username
			INNER JOIN $PROJECT_TABLE p ON p.proj_id = t.proj_id
			INNER JOIN $TASK_TABLE    s ON s.task_id = t.task_id
			WHERE p.client_id='$client_id'
			AND   t.uid='$uid'
			AND   t.end_time   >  0
			AND   t.start_time >= '$start_year-$start_month-$start_day'
			AND   t.start_time <=  '$end_year-$end_month-$end_day'
			GROUP BY starting_date, t.proj_id, p.title, s.task_id, s.name
			ORDER BY starting_date, p.title";

//run the query
list($qh,$num) = dbQuery($query);

//no records were found
if ($num == 0) {
	print '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table_body">';
	print "	<tr>\n";
	print "		<td align=\"center\">\n";
	print "			<i><br>No hours recorded.<br><br></i>\n";
	print "		</td>\n";
	print "	</tr>\n";
	print " </table>\n";
}
else {
	$crosstab = array();
	$projects = array();

	$start_time = strtotime($start_year . '/' . $start_month . '/' . $start_day);
	$end_time   = strtotime($end_year   . '/' . $end_month   . '/' . $end_day);

	//=========================================================================================================================
	//sort the data into an array which we can more easily traverse in order to build the cross-tab report
	while ($data = dbResult($qh)) {
		if(!isset($crosstab[$data['starting_date']]))
			$crosstab[$data['starting_date']] = array();

		$crosstab[$data['starting_date']][$data['task_id']] = array('diff' => $data['diff'], 'diff_time' => $data['diff_time']);

		if(!isset($crosstab[$data['starting_date']]['total']))
			$crosstab[$data['starting_date']]['total'] = 0;

		$crosstab[$data['starting_date']]['total'] += $data['diff'];

		if(!array_key_exists($data['proj_id'], $projects))
			$projects[$data['proj_id']] = array('title' => $data['proj_title'], 'total' => 0, 'tasks' => array());

		if(!array_key_exists($data['task_id'], $projects[$data['proj_id']]['tasks']))
			$projects[$data['proj_id']]['tasks'][$data['task_id']] = array('title' => $data['task_title'], 'total' => 0);
	}

	asort($projects);

	echo '<table border="1" cellpadding="0" cellspacing="0" class="table_body report">';
	echo '<thead>';

	echo '<tr>';
	echo '<th class="first">&nbsp;</th>';
	echo '<th class="first">&nbsp;</th>';
		foreach($projects as $project_id => $project){
			echo '<th class="project" colspan="' . count($project['tasks']) . '">' . htmlentities($project['title']) . '</th>';
		}
	echo '</tr>';

	echo '<tr>';
	echo '<th class="first">&nbsp;</th>';
	echo '<th>Total</th>';

	foreach($projects as $project_id => $project){
		foreach($project['tasks'] as $task_id => $task)
			echo '<th>' . htmlentities($task['title']) . '</th>';
	}

	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	while($start_time <= $end_time){
		if(array_key_exists(date('Y/m/d',$start_time),$crosstab))
			$data = $crosstab[date('Y/m/d',$start_time)];
		else
			$data = array();

		if(in_array(date('N',$start_time), array(6,7)))
			echo '<tr class="weekend">';
		else
			echo '<tr>';

		echo '<td class="date"><strong>' . date('D, jS M, Y',$start_time) . '</strong></td>';

		if($data)
			echo '<td><strong>' . format_seconds($data['total'],$time_format_mode) . '</strong></td>';
		else
			echo '<td>&nbsp;</td>';

		foreach($projects as $project_id => $project){
			foreach($project['tasks'] as $task_id => $task){
				echo '<td class="cell">';

				if(array_key_exists($task_id, $data)){
					echo htmlentities(format_seconds($data[$task_id]['diff'],$time_format_mode));

					$projects[$project_id]['tasks'][$task_id]['total'] += $data[$task_id]['diff'];
					$grand_total_time                                  += $data[$task_id]['diff'];
				}
				else
					echo '&nbsp;';

				echo '</td>';
			}
		}

		echo '</tr>';

		$start_time = strtotime(date('d-M-Y',$start_time) . ' + 1 Day');
	}

	echo '</tbody>';
	echo '<tfoot>';
	echo '<tr>';
	echo '<td class="grandtotal"><strong>TOTAL</strong></td>';
	echo '<td class="grandtotal"><strong>' . format_seconds($grand_total_time,$time_format_mode) . '</strong></td>';

	foreach($projects as $project_id => $project){
		foreach($project['tasks'] as $task_id => $task)
			echo '<td class="total"><strong>' . htmlentities(format_seconds($task['total'],$time_format_mode)) . '</strong></td>';
	}

	echo '</tr>';
	echo '</tfoot>';
	echo '</table>';
}

//====================================================================================================================
// close off report
if(!$export_excel){
?>
					</td>
				</tr>
			</table>

			<!-- include the timesheet face up until the end -->
			<?php include("timesheet_face_part_3.inc"); ?>

		</td>
	</tr>
</table>
</form>
<div id="footer">
<?php
include ("footer.inc");
?>
</div>
<?php }?>

</BODY>
</HTML>
