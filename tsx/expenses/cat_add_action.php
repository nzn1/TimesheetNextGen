<?php
if(!class_exists('Site'))die('Restricted Access');

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

//load local vars from request/post/get
$action = $_REQUEST['action'];
if ($action == "add") {
	
	$description = $_REQUEST['description'];

}

if ($action == "add") {
	// Do add type things in here, then send back to cat_edit.php.
	// No error checking for now.

	list($qn, $num) = dbQuery("select cat_id, description FROM ". tbl::getExpenseCategoryTable(). " WHERE description = '". $description. "'");
	$data = dbResult($qn);
	if ($num == 0) { // no duplicate in the table, ok to add this new category
	
		list($qh, $num) = dbQuery("INSERT INTO  ".tbl::getExpenseCategoryTable()."  (description) VALUES ".
						"('$description')");
		$data = dbResult($qh);
	}

	gotoLocation(Config::getRelativeRoot()."/expenses/cat_list");
}

?>
