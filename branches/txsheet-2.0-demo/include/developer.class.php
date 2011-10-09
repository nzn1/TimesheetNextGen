<?php
class Developer{
  
  public static function checkForDeveloperPrerequisites(){
  
    self::timesTable();
    
    
    
  
  }
  
  
  public static function timesTable(){
  //ALTER TABLE __TABLE_PREFIX__times CHANGE `uid` `username` varchar(32) DEFAULT '' NOT NULL
    $q = "SHOW COLUMNS FROM ".tbl::getTimesTable().";";    
    $data = Database::getInstance()->sql($q,Database::TYPE_OBJECT);
    
    foreach($data as $obj){
      if($obj->Field == 'uid'){
      
        $msg = "The column uid has been modified to username.<br />"
            ."Look in the upgrade sql.in file for the line below and update your db:<br />"
            ."ALTER TABLE times .... `uid` to `username`  (or similar)";
        $title = "Database Update Required";        
        Errorhandler::fatalError($msg.ppr($obj,'SQL Problem Column',true),$title,$title);
      }
    }
  
  }


}

?>