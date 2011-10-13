<?php
class Developer{
  
  public static function checkForDeveloperPrerequisites(){
  
    self::timesTable();
    self::timesTable();
    self::userTable();
    self::configTable();    
    
    
  
  }
  
  public static function userTable(){
  	//ALTER TABLE __TABLE_PREFIX__user  ADD   employee_type enum('Contractor','Employee') NOT NULL DEFAULT 'Employee';
	//ALTER TABLE __TABLE_PREFIX__user  ADD supervisor int(11) DEFAULT NULL;
    $q = "SHOW COLUMNS FROM ".tbl::getUserTable().";";    
    $data = Database::getInstance()->sql($q,Database::TYPE_OBJECT);
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

  public static function configTable(){

    $q = "SHOW COLUMNS FROM ".tbl::getTimesTable().";";    
    $data = Database::getInstance()->sql($q,Database::TYPE_OBJECT);
    $aclExpenses = FALSE;
    $aclECategories = FALSE;
    $aclTApproval = FALSE;
    $msg = "";
	$title = "";
    foreach($data as $obj){
          if($obj->Field == 'aclExpenses'){
      	$aclExpenses = TRUE;
      }
      if($obj->Field == 'aclECategories'){
      	$aclECategories = TRUE;
      }
      if($obj->Field == '$aclTApproval'){
      	$aclTApproval = TRUE;
      }
    }
    if ($aclExpenses == FALSE) {
        $msg .= "A new column aclExpenses has been added to the config table.<br />"
            ."Look in the upgrade sql.in file for the line below and update your db:<br />"
            ."ALTER TABLE __TABLE_PREFIX__config  ADD  aclExpenses enum('Admin','Mgr','Basic','None') NOT NULL DEFAULT 'Basic';";
        $title = "Database Update Required";  
    }
    if ($aclECategories == FALSE) {
        $msg .= "A new column aclECategories has been added to the config table.<br />"
            ."Look in the upgrade sql.in file for the line below and update your db:<br />"
            ."ALTER TABLE __TABLE_PREFIX__config  ADD  aclECategories enum('Admin','Mgr','Basic','None') NOT NULL DEFAULT 'Basic';";
        $title = "Database Update Required";  
    }
    if ($aclTApproval == FALSE) {
        $msg .= "A new column aclTApproval has been added to the config table.<br />"
            ."Look in the upgrade sql.in file for the line below and update your db:<br />"
            ."ALTER TABLE __TABLE_PREFIX__config  ADD  aclECategories enum('Admin','Mgr','Basic','None') NOT NULL DEFAULT 'Basic';";
        $title = "Database Update Required";  
    }
    if ($title != "") {
        Errorhandler::fatalError($msg.ppr($obj,'SQL Problem Column',true),$title,$title);
    }
  }
  
  public static function timesTable(){
  //ALTER TABLE __TABLE_PREFIX__times CHANGE `uid` `username` varchar(32) DEFAULT '' NOT NULL
    $q = "SHOW COLUMNS FROM ".tbl::getTimesTable().";";    
    $data = Database::getInstance()->sql($q,Database::TYPE_OBJECT);
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

  public static function newtables(){
    // check that tables exenses, category and std_tasks exist
	$expenses = FALSE;
	$category = FALSE;	
	$std_tasks = FALSE;
	$configuration = FALSE;
	$msg = "";
	$title = "";
	$q = "SHOW TABLES;";    
    $data = Database::getInstance()->sql($q,Database::TYPE_OBJECT);
    
    foreach($data as $obj){
      if($obj->Field == 'expenses') $expenses = TRUE;
      if($obj->Field == 'category') $category = TRUE;
      if($obj->Field == 'std_tasks') $std_tasks = TRUE;
      if($obj->Field == 'configuration') $configuration = TRUE;
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