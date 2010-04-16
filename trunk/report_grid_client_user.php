<?php
require("class.AuthenticationManager.php");   // Authenticate
require("class.CommandMenu.php");
include("timesheet_menu.inc");                //define the command menu

/**********************************************************************************************************
 * This function assists the routine in common.inc that splits tasks into discrete days
 * called split_data_into_discrete_days();   That function needs to put things into a new 
 * array, and in order to get things out of that array in the order we want them in, we 
 * need to help that function out by telling it how to make an index for that array.
 */

function make_index($data,$order) {
	if($order == "date") {
		$index=$data["start_stamp"] . sprintf("-%03d",$data["proj_id"]) . 
			sprintf("-%03d",$data["task_id"]);
	} else {
		$index=sprintf("%03d",$data["proj_id"]) .  sprintf("-%03d-",$data["task_id"]) .
			$data["start_stamp"];
	}
	return $index;
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
$start_month = isset($_GET['start_month']) && $_GET['start_month'] ? (int)$_GET['start_month'] : $month;
$start_year  = isset($_GET['start_year'])  && $_GET['start_year']  ? (int)$_GET['start_year']  : $year;

$end_day     = isset($_GET['end_day'])     && $_GET['start_day']   ? (int)$_GET['end_day']     : date('t',strtotime("$year-$month-15"));
$end_month   = isset($_GET['end_month'])   && $_GET['start_month'] ? (int)$_GET['end_month']   : $month;
$end_year    = isset($_GET['end_year'])    && $_GET['start_year']  ? (int)$_GET['end_year']    : $year;

if(!checkdate($end_month,$end_day,$end_year)) {
	$end_day=get_last_day($end_month,$end_year);
}

//export data to excel (or not)
$export_excel = isset($_GET["export_excel"]) ? (bool)$_GET["export_excel"] : false;

//define working variables
$last_proj_id = -1;
$last_task_id = -1;
$total_time = 0;
$grand_total_time = 0;

$start_time = strtotime($start_year . '/' . $start_month . '/' . $start_day);
$end_time   = strtotime($end_year   . '/' . $end_month   . '/' . $end_day);
$end_time2   = strtotime("+1 day",$end_time);  //need last day to be inclusive...

$startStr = date("Y-m-d H:i:s",$start_time);
$endStr = date("Y-m-d H:i:s",$end_time2);

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
<?php 
	include("banner.inc"); 
	$MOTD=0;
	include("navcal/navcal_monthly_with_end_dates.inc");
?>
</div>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
<input type="hidden" name="mode" value="<?php echo $mode; ?>">
<!--input type="hidden" name="laser" value="<?php echo 'laser'; ?>"-->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
			<!-- include the timesheet face up until the heading start section -->
			<?php include("timesheet_face_part_1.inc"); ?>
				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap width="25%">
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
						<td width="25%">
							<table border="0" cellpadding="1" cellspacing="2">
								<tr nowrap>
									<td align="right" width="0" class="outer_table_heading">Start Date:</td>
									<td align="left"><?php day_button("start_day",$start_time); month_button("start_month",$start_month); year_button("start_year",$start_year); ?></td>
								</tr>
								<tr nowrap>
									<td align="right" width="0" class="outer_table_heading">End Date:</td>
									<td align="left"><?php day_button("end_day",$end_time); month_button("end_month",$end_month); year_button("end_year",$end_year); ?></td>
								</tr>
							</table>
						</td>
						<td align="center" nowrap class="outer_table_heading" width="10%">
							<?php echo date('F Y',strtotime($start_year . '/' . $start_month . '/' . $start_day));?>
						</td>
						<td  align="center" width="20%">
						<a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $_SERVER["QUERY_STRING"];?>&export_excel=1" class="export"><img src="images/export_data.gif" name="esporta_dati" border=0> Export to Excel</a>
						</td>
					</tr>
				</table>

				<!-- include the timesheet face up until the heading start section -->
				<?php include("timesheet_face_part_2.inc"); ?>

				<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
					<tr>
						<td>
<?php
} //end if(!$export_excel)


// ==========================================================================================================
// FETCH REPORT DATA AND DISPLAY

list($num, $qh) = get_time_records($startStr, $endStr, $uid, 0, $client_id);

//no records were found
if ($num == 0) {
	print '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table_body">';
	print "	<tr>\n";
	print "		<td align=\"center\">\n";
	print "			<i><br>No hours recorded.<br></i>\n";
	if($end_time2 <= $start_time)
		print "			<i><b><br><font color=\"red\">End time is before Start time!</font><br></b></i>\n";
	print "		<br></td>\n";
	print "	</tr>\n";
	print " </table>\n";
} else {
	$darray = array();
	$crosstab = array();
	$projects = array();

	//=========================================================================================================================
	//sort the data into an array which we can more easily traverse in order to build the cross-tab report
	while ($data = dbResult($qh)) {
		//if entry doesn't have an end time or duration, it's an incomplete entry
		//fixStartEndDuration returns a 0 if the entry is incomplete.
		if(!fixStartEndDuration($data)) continue;

		//Since we're allowing entries that may span date boundaries, this complicates
		//our life quite a lot.  We need to "pre-process" the results to split those
		//entries that do span date boundaries into multiple entries that stop and then
		//re-start on date boundaries.
		//NOTE: there must be a make_index() function defined in this file for the following function to, well, function
		$orderby = 'date';
		split_data_into_discrete_days($data,$orderby,$darray,0);
	}

	ksort($darray);
	unset($data);

	foreach($darray as $dary){
		foreach($dary as $data){
			//need to make sure date is in range of what we want...
			if($data["start_stamp"] < $start_time) continue;
			if($data["start_stamp"] >= $end_time2) continue;

			if(!isset($crosstab[$data['start_stamp']]))
				$crosstab[$data['start_stamp']] = array();

			$crosstab[$data['start_stamp']][$data['task_id']] = $data['duration'];

			if(!isset($crosstab[$data['start_stamp']]['total']))
				$crosstab[$data['start_stamp']]['total'] = 0;

			$crosstab[$data['start_stamp']]['total'] += $data['duration'];

			if(!array_key_exists($data['proj_id'], $projects))
				$projects[$data['proj_id']] = array('title' => $data['projectTitle'], 'total' => 0, 'tasks' => array());

			if(!array_key_exists($data['task_id'], $projects[$data['proj_id']]['tasks']))
				$projects[$data['proj_id']]['tasks'][$data['task_id']] = array('title' => $data['taskName'], 'total' => 0);
		}
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
		if(array_key_exists($start_time,$crosstab))
			$data = $crosstab[$start_time];
		else
			$data = array();

		if(in_array(date('N',$start_time), array(6,7)))
			echo '<tr class="weekend">';
		else
			echo '<tr>';

		echo '<td class="date"><strong>' . date('D, jS M, Y',$start_time) . '</strong></td>';

		if($data)
			echo '<td><strong>' . format_minutes($data['total']) . '</strong></td>';
		else
			echo '<td>&nbsp;</td>';

		foreach($projects as $project_id => $project){
			foreach($project['tasks'] as $task_id => $task){
				echo '<td class="cell">';

				if(array_key_exists($task_id, $data)){
					echo htmlentities(format_minutes($data[$task_id]));

					$projects[$project_id]['tasks'][$task_id]['total'] += $data[$task_id];
					$grand_total_time                                  += $data[$task_id];
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
	echo '<td class="grandtotal"><strong>' . format_minutes($grand_total_time) . '</strong></td>';

	foreach($projects as $project_id => $project){
		foreach($project['tasks'] as $task_id => $task)
			echo '<td class="total"><strong>' . htmlentities(format_minutes($task['total'])) . '</strong></td>';
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
<?php
// vim:ai:ts=4:sw=4:filetype=php
?>
