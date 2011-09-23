<?php
die('NOT CONVERTED TO OO YET');
if(!class_exists('Site'))die('Restricted Access');
// Authenticate
require("class.AuthenticationManager.php");
if (!$authenticationManager->isLoggedIn()) {
	gotoLocation(Config::getRelativeRoot()."/login?redirect=".urlencode($_SERVER['REQUEST_URI']));
	exit;
}


//load local vars from request/post/get
$trans_num = $_REQUEST['trans_num'];

//get the timeformat
$timeFormat = getTimeFormat();

$dateFormatString = ($timeFormat == "12") ? "%m/%d/%Y %h:%i%p": "%m/%d/%Y %H:%i";

$query = "SELECT DATE_FORMAT(start_time, '$dateFormatString') as formattedStartTime, ".
				"DATE_FORMAT(end_time, '$dateFormatString') as formattedEndTime, ".
				"(unix_timestamp(end_time) - unix_timestamp(start_time)) as time,".
				"log_message, " .
				"pt.title AS projectTitle, " .
				"pt.proj_status AS projectStatus, ".
				"tt.name AS taskName, " .
				"tt.status AS taskStatus, ".
				"ct.organisation, ".
				"ut.first_name, ".
				"ut.last_name " .
				"FROM ".tbl::getTimesTable()." timest, ".tbl::getProjectTable()." pt, ".tbl::getTasksTable()." tt, ".tbl::getUserTable()." ut, ".tbl::getClientTable()." ct".
			"WHERE pt.proj_id=timest.proj_id ".
				"AND tt.task_id=timest.task_id ".
				"AND timest.trans_num=$trans_num ".
				"AND pt.client_id = ct.client_id ".
				"AND ut.username = timest.uid";


//print "<PRE>$data[date]\n$data[time]\n$data[log_message]\n$data[title]\n$data[client]\n$data[first_name]\n$data[last_name]</PRE>";
?>
<html>
<head>
<title>Task Info</title>
<?php
include ("header.inc");
?>
</head>
<body width="100%" height="100%" style="margin: 0px;" <?php include ("body.inc"); ?> >
<table border="0" width="100%" height="100%" align="center" valign="center">
<?php

	list($qh, $num) = dbQuery($query);
	if ($num > 0) {
		$data = dbResult($qh);

?>
		<tr>
			<td>
				<table width="100%" border="0" class="section_body">
					<tr>
						<td>
							<span class="label">Project:</span>
							<span class="project_title"><?php echo stripslashes($data["projectTitle"]); ?></span>
							&nbsp;<span class="project_status">&lt;<?php echo $data["projectStatus"]; ?>&gt;</span>
						</td>
					</tr>
					<tr>
						<td>
							<span class="label">Task:</span>
							<span class="task_title"><?php echo stripslashes($data["taskName"]); ?></span>
							&nbsp;<span class="project_status">&lt;<?php echo $data["taskStatus"]; ?>&gt;</span>
						</td>
					</tr>
					<tr>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td>
							<span class="label">Clocked On:</span>
							<?php echo $data["formattedStartTime"]; ?>&nbsp;
							<span class="label">Clocked Off:</span>
							<?php echo $data["formattedEndTime"]; ?>
						</td>
					</tr>
					<tr>
						<td>
							<span class="label">Duration:</span>
							<?php echo formatSeconds($data["time"]); ?>
						</td>
					</tr>
					<tr>
						<td valign="top" align="left">
							<span class="label">Log Message:</span>
								<?php echo $data["log_message"]; ?>
						</td>
					<tr>
				</table>
			</td>
		</tr>

<?php
	}
?>