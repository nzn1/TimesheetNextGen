<?php
if(!class_exists('Site'))die('Restricted Access');

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclECategories'))return;

//load local vars from request/post/get
$action = $_REQUEST['action'];
if ($action == "add") {
	$cat_name = addslashes($_REQUEST['name']);
	$description = addslashes($_REQUEST['description']);
	$cat_id = $_REQUEST['cat_id'];
}

if ($action == "add") {
	// Do add type things in here, then send back to cat_list.php.
	// No error checking for now.

	
		list($qh, $num) = dbQuery("UPDATE  ".tbl::getExpenseCategoryTable()." SET cat_name= '".
				$cat_name . "', cat_description= '".
				$description . "' WHERE cat_id = '". $cat_id. "'");
		
	}
	gotoLocation(Config::getRelativeRoot()."/expenses/cat_list");

?>
