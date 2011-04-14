<?php
if(!class_exists('Site'))die('Restricted Access');

// Authenticate
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclDaily'))return;

//load local vars from request/post/get
$action = $_REQUEST["action"];
$proj_id = $_REQUEST["proj_id"];

//print "<p>isAdministrator='$isAdministrator'</p>";

include("table_names.inc");

if ($action == "show_users") {
	$username_array = array();
	$firstname_array = array();
	$lastname_array = array();

	//check whether the project id exists in database
	list($qh,$num) = dbQuery("SELECT c.organisation, p.title FROM " .
								" ".tbl::getClientTable()."  c,  ".tbl::getProjectTable()."  p " .
								"WHERE p.proj_id='$proj_id' AND c.client_id = p.client_id");
	//if there is a match
	if ($data = dbResult($qh)) {
		$proj_title = $data['title'];
		$client_name = $data['organisation'];

		// Get the list of users who are assigned on this project
		list($qh,$num) = dbQuery("SELECT u.username, u.first_name, u.last_name " .
									"FROM  ".tbl::getUserTable()."  u,  ".tbl::getAssignmentsTable()."  a,  ".tbl::getProjectTable()."  p " .
									"WHERE p.proj_id='$proj_id' " .
									"AND a.proj_id = p.proj_id " .
									"AND a.username = u.username");
		$n = 0;
		while ($data = dbResult($qh)) {
			// Store user details for future use
			$username_array[$n] = $data['username'];
			$firstname_array[$n] = $data['first_name'];
			$lastname_array[$n] = $data['last_name'];
			$n++;
		}
	}
} elseif ($action == "update_rates") { // Update database with choses rates
	$usercount = $_REQUEST["usercount"];
	$proj_id = $_REQUEST["proj_id"];
	if (empty($proj_id) || empty($usercount)) {
		gotoLocation(Config::getRelativeRoot()."/project_user_rates");
		exit(0);
	}

	// Find out which users are assigned to given project
	list($qh,$num) = dbQuery("SELECT username FROM ".tbl::getAssignmentsTable()." " .
								"WHERE proj_id = '$proj_id' ");
	$user_array = array();
	while ($data = dbResult($qh)) {
		$user_array[$data["username"]] = 1;
	}

	$n = 1;
	while ($n <= $usercount) {
		$rateid = $_REQUEST["rateid_" . strval($n)];
		$username = $_REQUEST["username_" . strval($n)];
		if (empty($rateid) || empty($username)) {
			continue;
		}
		//if (array_key_exists($username, $user_array)) {
			$query = "UPDATE  ".tbl::getAssignmentsTable()."  SET rate_id = '$rateid' WHERE proj_id = '$proj_id' AND username = '$username'";
		//} else {
		//	$query = "insert into ".tbl::getProjectUserRateTable()." (proj_id, username, rate_id) values ('$proj_id', '$username', '$rateid')";
		//}
		list($qh,$num) = dbQuery($query);

		$n++;
	}

	//redirect back to the rate management page
	gotoLocation(Config::getRelativeRoot()."/project_user_rates");
	exit(0);
} else {
	//redirect back to the rate management page
	gotoLocation(Config::getRelativeRoot()."/project_user_rates");
	exit(0);
}

?>
<head><title>Project-User-Rates Management Page</title>

<script>
	function updateRate() {
		document.userRateForm.action.value = "update_rates";
		document.userRateForm.submit();
	}
	function goBack() {
		document.userRateForm.action.value = "";
		document.userRateForm.submit();
	}
</script>
</head>

<form action="<?php echo Config::getRelativeRoot(); ?>/projects/project_user_rates_action" name="userRateForm" method="post">

	<input type="hidden" name="action" value="" />
	<input type="hidden" name="proj_id" value="<?php print $proj_id; ?>" />

	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">


		<table width="100%" border="0">
			<tr>
			<td align="left" nowrap class="outer_table_heading">
				Users assigned on <?php print "$proj_title ($client_name)"; ?>
			</td>
			</tr>
		</table>

		<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
			<tr>
			<td>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body">
				<tr class="inner_table_head">
					<td class="inner_table_column_heading"><i>&nbsp;</i></td>
					<td class="inner_table_column_heading">&nbsp;User</td>
					<td class="inner_table_column_heading">Billing Rate (per hour)</td>
				</tr>
<?php

list($qh,$num) = dbQuery("SELECT rate_id, bill_rate FROM  ".tbl::getRateTable()." ORDER BY bill_rate");

$count = 0;
$rate_array = array();
while ($data = dbResult($qh)) {
	$rate_array[$count] = $data["rate_id"] . ':' . $data["bill_rate"];
	$count++;
}

list($qh,$num) = dbQuery("SELECT u.username, r.rate_id FROM  ".tbl::getUserTable()."  u,  ".tbl::getAssignmentsTable()."  a,  ".tbl::getAssignmentsTable()." r ".
							"WHERE a.proj_id=$proj_id " .
							"AND a.username = u.username " .
							"AND a.rate_id = r.rate_id " .
							"ORDER BY u.username");

$count = 0;
$urate_map = array();
while ($data = dbResult($qh)) {
	$urate_map[$data["username"]] = $data["rate_id"];
	$count++;
}


$len = count($username_array);
$idx = 0;
while ($idx < $len) {
	$count = $idx + 1;
	$user = "username_" . strval($count);
	$rate = "rateid_" . strval($count);
	if (array_key_exists($username_array[$idx], $urate_map)) {
		$rateid = $urate_map[$username_array[$idx]];
	} else {
		$rateid = "";
	}

	print "<tr><td align=\"center\" class=\"calendar_cell_middle\">$count.</td><td class=\"calendar_cell_middle\">&nbsp;$firstname_array[$idx] $lastname_array[$idx] ($username_array[$idx])</td><td class=\"calendar_cell_middle\">" . Common::build_uni_select($rate, $rate_array, $rateid) . "</td></tr>\n";
	print "<input type=\"hidden\" name=\"$user\" value=\"$username_array[$idx]\" />\n";

	$idx++;
}
?>
				<tr>
					<td colspan=3 align="center">
					<input type="button" name="update" value="Update Rates" onclick="javascript:updateRate()" class="bottom_panel_button" />
					<input type="button" name="back" value="Cancel" onclick="javascript:goBack()" class="bottom_panel_button" />
					</td>
				</tr>
				</table>
			</td>
			</tr>
		</table>

		</td>

	</tr>
	</table>
	<input type="hidden" name="usercount" value="<?php print $len; ?>" />
</form>