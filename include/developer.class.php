<?php
class Developer{
  
  public static function checkForDeveloperPrerequisites($db){
    self::newtables($db);
    self::timesTable($db);
    self::userTable($db);
    self::expenseCategoryTable($db);   
  
  }
  
  public static function userTable($db){
  	//ALTER TABLE __TABLE_PREFIX__user  ADD   employee_type enum('Contractor','Employee') NOT NULL DEFAULT 'Employee';
	//ALTER TABLE __TABLE_PREFIX__user  ADD supervisor int(11) DEFAULT NULL;
    $q = "SHOW COLUMNS FROM ".tbl::getUserTable().";";    
    $data = $db->sql($q,Database::TYPE_OBJECT);
    $supervisor = FALSE;
    $employee_type = FALSE;
    $msg = "";
    $title = "";
    foreach($data as $obj){
      if($obj->Field == 'employee_type'){
      	$employee_type = TRUE;
      }
      if($obj->Field == 'supervisor'){
      	$supervisor = TRUE;
      }
    }
    if ($employee_type == FALSE) {
        $msg .= "A new column employee_type has been added to the user table.<br />"
            ."Look in the upgrade sql.in file for the line below and update your db:<br />"
            ."ALTER TABLE __TABLE_PREFIX__user  ADD   employee_type enum('Contractor','Employee') NOT NULL DEFAULT 'Employee';";
        $title = "Database Update Required";  
    }
    if ($supervisor == FALSE) {
        $msg .= "A new column supervisor has been added to the user table.<br />"
            ."Look in the upgrade sql.in file for the line below and update your db:<br />"
            ."ALTER TABLE __TABLE_PREFIX__user  ADD supervisor int(11) DEFAULT NULL;";
        $title = "Database Update Required";  
    }
    if ($title != "") {
        Errorhandler::fatalError($msg.ppr($obj,'SQL Problem Column',true),$title,$title);
    }
  }

  public static function timesTable($db){
  //ALTER TABLE __TABLE_PREFIX__times CHANGE `uid` `username` varchar(32) DEFAULT '' NOT NULL
    $q = "SHOW COLUMNS FROM ".tbl::getTimesTable().";";    
    $data = $db->sql($q,Database::TYPE_OBJECT);
    $status = FALSE;
    $uid = FALSE;
    $msg = "";
	$title = "";
    foreach($data as $obj){
      if($obj->Field == 'uid'){
      	$uid = TRUE;
        $msg .= "The column uid has been modified to username.<br />"
            ."Look in the upgrade sql.in file for the line below and update your db:<br />"
            ."ALTER TABLE times .... `uid` to `username`  (or similar)";
        $title = "Database Update Required";        
      }
      //ALTER TABLE __TABLE_PREFIX__times  ADD  `status` enum('Open','Submitted','Approved') NOT NULL DEFAULT 'Open';
      if($obj->Field == 'status'){
      	$status = TRUE;
      }
    }
    if ($status == FALSE) {
        $msg .= "A new column status has been added to the times table.<br />"
            ."Look in the upgrade sql.in file for the line below and update your db:<br />"
            ."ALTER TABLE __TABLE_PREFIX__times  ADD  `status` enum('Open','Submitted','Approved') NOT NULL DEFAULT 'Open'";
        $title = "Database Update Required";  
    }
    if ($title != "") {
        Errorhandler::fatalError($msg.ppr($obj,'SQL Problem Column',true),$title,$title);
    }
  }

  public static function expenseCategoryTable($db){
  //ALTER TABLE __TABLE_PREFIX__times CHANGE `uid` `username` varchar(32) DEFAULT '' NOT NULL
    $q = "SHOW COLUMNS FROM ".tbl::getExpenseCategoryTable().";";    
    $data = $db->sql($q,Database::TYPE_OBJECT);
    $status = FALSE;
    $description = FALSE;
    $msg = "";
	$title = "";
    foreach($data as $obj){
      if($obj->Field == 'description'){
      	$description = TRUE;
        $msg .= "The column description has been modified to cat_description.<br />" .
            "Look in the upgrade sql.in file for the line below and update your db:<br />" .
            "ALTER TABLE __TABLE_PREFIX__category CHANGE `description` `cat_description`";
        $title = "Database Update Required";        
      }
      if($obj->Field == 'cat_name'){
      	$status = TRUE;
      }
      
    }
    if ($status == FALSE) {
        $msg .= "A new column status has been added to the category table.<br />"
            ."Look in the upgrade sql.in file for the line below and update your db:<br />"
            ."ALTER TABLE __TABLE_PREFIX__times  ADD `cat_name` varchar(64) NOT NULL";
        $title = "Database Update Required";  
    }
    if ($title != "") {
        Errorhandler::fatalError($msg.ppr($obj,'SQL Problem Column',true),$title,$title);
    }
  }
  
  public static function newtables($db){
    // check that tables exenses, category and std_tasks exist
	$expenses = FALSE;
	$category = FALSE;	
	$std_tasks = FALSE;
	$configuration = FALSE;
	$msg = "";
	$title = "";
	$q = "SHOW TABLES;";    
	$tableobject = "Tables_in_".Config::getDbName();
    $data = $db->sql($q,Database::TYPE_ARRAY);

    $msg .= ppr($data,'',true);
    foreach($data as $obj){
    	LogFile::write("tables: ". $obj->$tableobject);
      if(strstr($obj->$tableobject, "expense")) $expenses = TRUE;
      if(strstr($obj->$tableobject, "category")) $category = TRUE;
      if(strstr($obj->$tableobject, "std_task")) $std_tasks = TRUE;
      if(strstr($obj->$tableobject, "configuration")) $configuration = TRUE;
    }
    if ($expenses == FALSE) {
        $msg .= "A new table called expenses has been added to the database.<br />"
            ."Look in the upgrade sql.in file for the group of lines below and update your db:<br />"
            ."CREATE TABLE IF NOT EXISTS `__TABLE_PREFIX__expense`   (and following lines) <br>";
        $title = "Database Update Required";        
        
    }
    if ($category == FALSE) {
        $msg .= "A new table called category has been added to the database.<br />"
            ."Look in the upgrade sql.in file for the group of lines below and update your db:<br />"
            ."CREATE TABLE IF NOT EXISTS `__TABLE_PREFIX__category`   (and following lines) <br>";
        $title = "Database Update Required";        
        
    }
    if ($std_tasks == FALSE) {
        $msg .= "A new table called std_tasks has been added to the database.<br />"
            ."Look in the upgrade sql.in file for the group of lines below and update your db:<br />"
            ."CREATE TABLE IF NOT EXISTS `__TABLE_PREFIX__std_tasks`   (and following lines) <br>";
        $title = "Database Update Required";        
        
    }
    if ($configuration == FALSE) {
        $msg .= "The old config table has been replaced with a new table called configuration.<br />"
            ."Look in the upgrade sql.in file for the group of lines below and update your db:<br />"
            ."CREATE TABLE IF NOT EXISTS `__TABLE_PREFIX__configuration`   (and following lines) <br>";
        $title = "Database Update Required";        
        
    }
	if ($title != "")
    	Errorhandler::fatalError($msg.ppr($obj,'SQL Definition Problem',true),$title,$title);
  
  }


}

?>