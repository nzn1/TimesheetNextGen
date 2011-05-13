<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate


if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

// Config::getRelativeRoot()."submit.php?uid=peter&amp;orderby=project&amp;client_id=0&amp;mode=monthly&amp;year=2010&amp;month=8&amp;day=1"
	
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
			list($qh, $num) = dbQuery("UPDATE ".tbl::getTimesTable()." SET status = \"Submitted\"" .
					" WHERE trans_num IN ( $transids )");
			
			}
		}
	}
	else 
	{
		// a date, client or user redirection
		// get new date for the url
		$date1 = $_POST["date1"];
		$newdate = explode("-", $date1);
		$Location = Config::getRelativeRoot()."/submit?uid=".$_REQUEST["uid"]."&amp;orderby=".$_REQUEST["orderby"]."&amp;client_id=".gbl::getClientId()."&amp;mode=".gbl::getMode()."&amp;year=".$newdate[2]."&amp;month=".$newdate[1]."&amp;day=".$newdate[0];
		LogFile::write("No changed data, redirect path is $Location\n");
		gotoLocation($Location);
		exit;
	}
	// we're done so redirect to the submission page
	$Location = Config::getRelativeRoot()."/submit?uid=".$_REQUEST["uid"]."&amp;orderby=".$_REQUEST["orderby"]."&amp;client_id=".gbl::getClientId()."&amp;mode=".gbl::getMode()."&amp;year=".gbl::getYear()."&amp;month=".gbl::getMonth()."&amp;day=".gbl::getDay();
	LogFile::write("Changed data, redirect path is $Location\n");
	gotoLocation($Location);
	exit;

?>




