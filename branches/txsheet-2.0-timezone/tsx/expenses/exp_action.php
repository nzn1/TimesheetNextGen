<?php
if(!class_exists('Site'))die('Restricted Access');

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;


//load local vars from request/post/get
$action = $_REQUEST['action'];
if ($action == "add") {

	$client_id = $_REQUEST['client_id'];
	$proj_id = $_REQUEST['proj_id'];
	$category = $_REQUEST['category'];
	$exp_day = $_REQUEST['exp_day'];
	$exp_month = $_REQUEST['exp_month'];
	$exp_year = $_REQUEST['exp_year'];
	$billable = $_REQUEST['billable'];
	$amount = $_REQUEST['amount'];
	$description = $_REQUEST['description'];
	
}
//load local vars from request/post/get
if (isset($_REQUEST['uid']))
	$user_id = $_REQUEST['uid'];
else
	$user_id = gbl::getContextUser();

if (!isset($action)) {
//	Header("Location: $HTTP_REFERER");
	Common::errorPage("ERROR: No action has been passed.  Please fix.\n");
}
elseif ($action == "add") {
	// Do add type things in here, then send back to expense list.
	// No error checking for now.
	switch($billable) {
		case  0;
			$bill_code = "Billable";
			break;
		case  1;
			$bill_code = "Internal";
			break;
		case  2;
			$bill_code = "Personal";
	}
	$eDate =  mktime(0, 0, 0, $exp_month, $exp_day, $exp_year);
	$exp_date = date("Y-m-d H:i:s",$eDate);
	$qinsert = "INSERT INTO  ".tbl::getExpenseTable()."  (eid, cat_id, proj_id, user_id, client_id, billable, amount, description, date, status) VALUES ".
				"(NULL,'$category','$proj_id','$user_id', '$client_id','$bill_code','$amount','$description', '$exp_date', 'Open')";

	LogFile::write("\n\nexp_action\nDB Insert query = ". $qinsert ."\n");
	list($qh, $num) = dbQuery($qinsert);

	gotoLocation(Config::getRelativeRoot()."/expenses/exp_list?client_id=$client_id");
}

// vim:ai:ts=4:sw=4
?>
