<?php
if(!class_exists('Site'))die('Restricted Access');

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

//load local vars from request/post/get
$action = $_REQUEST['action'];
if ($action == "add") {
	
	$description = $_REQUEST['description'];
	$cat_id = $_REQUEST['cat_id'];
}

if ($action == "add") {
	// Do add type things in here, then send back to cat_list.php.
	// No error checking for now.

	
		list($qh, $num) = dbQuery("UPDATE  ".tbl::getExpenseCategoryTable()." SET description= '".
				$description . "' WHERE cat_id = '". $cat_id. "'");
		
		$data_cat = dbResult($qh);
	}
	gotoLocation(Config::getRelativeRoot()."/expenses/cat_list");

?>
