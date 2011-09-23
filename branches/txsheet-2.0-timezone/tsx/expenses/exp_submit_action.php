<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate


if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclExpenses'))return;

if (isset($_REQUEST['Submit'])) { // if submission of times
	
	$action = $_REQUEST["Submit"];
	
	if (isset($action)) {
	
		if (isset($_REQUEST['sub'])) {
			//var_dump ($_REQUEST['sub']);
			$transids = "";
			foreach ($_REQUEST['sub'] as $transId) {
				if($transids == "")
					$transids = $transId;
				else 
					$transids = $transids . ", " . $transId;
			}
			list($qh, $num) = dbQuery("UPDATE ".tbl::getExpenseTable()." SET status = \"Submitted\"" .
					" WHERE eid IN ( $transids )");
			
			}
		}
	}
	else 
	{
		// a date, client or user redirection
		// get new date for the url
		$date1 = $_POST["date1"];
		$newdate = explode("-", $date1);
		$Location = Config::getRelativeRoot()."/expenses/exp_list?uid=".$_REQUEST["uid"]."&amp;orderby=".$_REQUEST["orderby"]."&amp;client_id=".gbl::getClientId()."&amp;mode=".gbl::getMode()."&amp;year=".$newdate[2]."&amp;month=".$newdate[1]."&amp;day=".$newdate[0];
		LogFile::write("No changed data, redirect path is $Location\n");
		gotoLocation($Location);
		exit;
	}
	// we're done so redirect to the submission page
	$Location = Config::getRelativeRoot()."/expenses/exp_list?uid=".$_REQUEST["uid"]."&amp;orderby=".$_REQUEST["orderby"]."&amp;client_id=".gbl::getClientId()."&amp;mode=".gbl::getMode()."&amp;year=".gbl::getYear()."&amp;month=".gbl::getMonth()."&amp;day=".gbl::getDay();
	LogFile::write("Changed data, redirect path is $Location\n");
	gotoLocation($Location);
	exit;

?>




