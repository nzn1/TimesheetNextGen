<?php
/**
 * *****************************************************************************
 * Name:                    tables.class.php
 * Recommended Location:    /include
 * Last Updated:            July 2010
 * Author:                  Mark Wrightson
 * Contact:                 mark@voltnet.co.uk
 *
 * Description:
 * Database Table Constants - these constants
 * hold the names of all the database tables used
 * in the script.
 *
 * Copyright:
 * This script may not be used by any other person without the express
 * permission of the author.
 ******************************************************************************/

class tbl{

private static $ASSIGNMENTS_TABLE;
private static $CLIENT_TABLE;
private static $CONFIG_TABLE;
private static $PROJECT_TABLE;
private static $TASK_TABLE;
private static $TASK_ASSIGNMENTS_TABLE;
private static $TIMES_TABLE;
private static $USER_TABLE;
private static $RATE_TABLE;
private static $ABSENCE_TABLE;
private static $ALLOWANCE_TABLE;

    public static function initialise(){
      require("table_names.inc");
      tbl::setAssignmentsTable($ASSIGNMENTS_TABLE);
      tbl::setClientTable($CLIENT_TABLE);
      tbl::setConfigTable($CONFIG_TABLE);
      tbl::setProjectTable($PROJECT_TABLE);
      tbl::setTaskTable($TASK_TABLE);
      tbl::setTaskAssignmentsTable($TASK_ASSIGNMENTS_TABLE);
      tbl::setTimesTable($TIMES_TABLE);
      tbl::setUserTable($USER_TABLE);
      tbl::setRateTable($RATE_TABLE);		
      tbl::setAbscenceTable($ABSENCE_TABLE);
      tbl::setAllowanceTable($ALLOWANCE_TABLE);
    }
    public static function getAssignmentsTable(){
    	return self::$ASSIGNMENTS_TABLE;
    }
    public static function setAssignmentsTable($str){
    	self::$ASSIGNMENTS_TABLE = $str;
    }    
	public static function getClientTable(){
    	return self::$CLIENT_TABLE;
    }
	public static function setClientTable($str){
    	self::$CLIENT_TABLE = $str;
    }
	public static function getConfigTable(){
    	return self::$CONFIG_TABLE;
    }
	public static function setConfigTable($str){
    	self::$CONFIG_TABLE = $str;
    }    
	public static function getProjectTable(){
    	return self::$PROJECT_TABLE;
    }
	public static function setProjectTable($str){
    	self::$PROJECT_TABLE = $str;
    }    
	public static function getTaskTable(){
    	return self::$TASK_TABLE;
    }
	public static function setTaskTable($str){
    	self::$TASK_TABLE = $str;
    }
	public static function getTaskAssignmentsTable(){
    	return self::$TASK_ASSIGNMENTS_TABLE;
    }
	public static function setTaskAssignmentsTable($str){
    	self::$TASK_ASSIGNMENTS_TABLE = $str;
    }    
	public static function getTimesTable(){
    	return self::$TIMES_TABLE;
    }
	public static function setTimesTable($str){
    	self::$TIMES_TABLE = $str;
    }    
	public static function getUserTable(){
    	return self::$USER_TABLE;
    }
	public static function setUserTable($str){
    	self::$USER_TABLE = $str;
    }    
	public static function getRateTable(){
    	return self::$RATE_TABLE;
    }
	public static function setRateTable($str){
    	self::$RATE_TABLE = $str;
    }    
	public static function getAbsenceTable(){
    	return self::$ABSENCE_TABLE;
    }
	public static function setAbscenceTable($str){
    	self::$ABSENCE_TABLE = $str;
    }    
	public static function getAllowanceTable(){
    	return self::$ALLOWANCE_TABLE;
    }
	public static function setAllowanceTable($str){
    	self::$ALLOWANCE_TABLE = $str;
    }    
}