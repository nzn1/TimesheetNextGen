<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

// Config::getRelativeRoot()."submit.php?uid=peter&amp;orderby=project&amp;client_id=0&amp;mode=monthly&amp;year=2010&amp;month=8&amp;day=1"
//load local vars from request/post/get

	if (!isset($_REQUEST['Modify'])) {
		// if a modify request, look for changes to individual expenses
			$transids = "";
			foreach ($_REQUEST['approve'] as $transId) {
				if($transids == "")
					$transids = $transId;
				else 
					$transids = $transids . ", " . $transId;
			}
			LogFile::write("\nsSupervisore approved: ". $transids);
			if ($transids != "") // if there are some expenses approved, update them in the db
				list($qh, $num) = dbQuery("UPDATE ".tbl::getTimesTable()." SET status = \"Approved\"" .
					" WHERE eid IN ( $transids )");
			
			foreach ($_REQUEST['reject'] as $transId) {
				if($transids == "")
					$transids = $transId;
				else 
					$transids = $transids . ", " . $transId;
			}
			LogFile::write("\nsSupervisore rejected: ". $transids);
			if ($transids != "") // if there are some expenses rejected, update them in the db
				list($qh, $num) = dbQuery("UPDATE ".tbl::getTimesTable()." SET status = \"Open\"" .
					" WHERE eid IN ( $transids )");

		// now go back to the supervisor expense list		
		
		$Location = Config::getRelativeRoot()."/expenses/supervisore?uid=".gbl::getUid()."&amp;orderby=".$_REQUEST["orderby"]."&amp;client_id=".gbl::getClientId()."&amp;mode=".gbl::getMode()."&amp;year=".$newdate[2]."&amp;month=".$newdate[1]."&amp;day=".$newdate[0];
		gotoLocation($Location);
	}
		
	$action = $_REQUEST["Modify"];
	$proj_id = gbl::getProjId();
	$client_id = gbl::getClientId();
	$orderby = $_REQUEST["orderby"];
	$mode = gbl::getMode();
	$uid = gbl::getUid();
	
	// if the approve tick box is pressed, change the state of all selected times to Approved
	if (isset($_REQUEST['approve'])) {
		$transids = "";
		foreach ($_REQUEST['approve'] as $transId) {
			if($transids == "")
				$transids = $transId;
			else 
				$transids = $transids . ", " . $transId;
			}
		LogFile::write("\nsSupervisore approve a: ". $transids);
		list($qh, $num) = dbQuery("UPDATE ".tbl::getExpenseTable()." SET status = \"Approved\"" .
				" WHERE eid IN ( $transids )");
			//LogFile::->write("update query transids = \" $transids\" qh = \"$qh\"  num=\"".$num. "\"\n");		
		}
		// if the reject tick box is pressed, change the state of all selected times to Open
		if (isset($_REQUEST['reject'])) {
			//var_dump ($_REQUEST['sub']);
			$transids = "";
			foreach ($_REQUEST['reject'] as $transId) {
				if($transids == "")
					$transids = $transId;
				else 
					$transids = $transids . ", " . $transId;
			}
			LogFile::write("\nsSupervisore reject a: ". $transids);
			list($qh, $num) = dbQuery("UPDATE ".tbl::getExpenseTable()." SET status = \"Open\"" .
					" WHERE eid IN ( $transids )");
			//LogFile::->write("update query transids = \" $transids\" qh = \"$qh\"  num=\"".$num. "\"\n");		
		}
		
	
	
	// we're done so redirect to the submission page
	$path = Config::getRelativeRoot()."/expenses/supervisore?uid=".$uid."&amp;orderby=".$_REQUEST["orderby"]."&amp;client_id=".gbl::getClientId()."&amp;mode=".gbl::getMode()."&amp;year=".gbl::getYear()."&amp;month=".gbl::getMonth()."&amp;day=".gbl::getDay();
	gotoLocation($path);

?>




