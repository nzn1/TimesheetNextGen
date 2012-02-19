<?php
if(!class_exists('Site'))die('Restricted Access');

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclECategories'))return;

//load local vars from request/post/get
$action = $_REQUEST['action'];
if ($action == "add") {
	
	$description = addslashes($_REQUEST['description']);
	$cat_name = addslashes($_REQUEST['name']);
}

if ($action == "add") {
	// Do add type things in here, then send back to cat_edit.php.
	// No error checking for now.

	list($qn, $num) = dbQuery("select cat_id FROM ". tbl::getExpenseCategoryTable(). " WHERE cat_name = '". $cat_name. "'");
	$data = dbResult($qn);
	if ($num == 0) { // no duplicate in the table, ok to add this new category
	
		list($qh, $num) = dbQuery("INSERT INTO  ".tbl::getExpenseCategoryTable()."  (cat_name, cat_description) VALUES ".
						" ('$cat_name', '$description')");

	}

	gotoLocation(Config::getRelativeRoot()."/expenses/cat_list");
}

?>
