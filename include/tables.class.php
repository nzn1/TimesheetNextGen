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
private static $NEW_CONFIG_TABLE;
private static $PROJECT_TABLE;
private static $TASK_TABLE;
private static $TASK_ASSIGNMENTS_TABLE;
private static $TIMES_TABLE;
private static $USER_TABLE;
private static $RATE_TABLE;
private static $ABSENCE_TABLE;
private static $ALLOWANCE_TABLE;

    public static function initialise(){
      if(!file_exists('table_names.inc')){
      	trigger_error('table_names.inc could not be found');
      	return;	
      }
    	require("table_names.inc");
      self::$ASSIGNMENTS_TABLE = $ASSIGNMENTS_TABLE;
      self::$CLIENT_TABLE = $CLIENT_TABLE;
      self::$CONFIG_TABLE = $CONFIG_TABLE;
      self::$PROJECT_TABLE = $PROJECT_TABLE;
      self::$TASK_TABLE = $TASK_TABLE;
      self::$TASK_ASSIGNMENTS_TABLE = $TASK_ASSIGNMENTS_TABLE;
      self::$TIMES_TABLE = $TIMES_TABLE;
      self::$USER_TABLE = $USER_TABLE;
      self::$RATE_TABLE = $RATE_TABLE;		
      self::$ABSENCE_TABLE = $ABSENCE_TABLE;
      self::$ALLOWANCE_TABLE = $ALLOWANCE_TABLE;
      self::$NEW_CONFIG_TABLE = $NEW_CONFIG_TABLE;
    }
    public static function getAssignmentsTable(){
    	return self::$ASSIGNMENTS_TABLE;
    }   
	public static function getClientTable(){
    	return self::$CLIENT_TABLE;
    }
    public static function getNewConfigTable(){
    	return self::$NEW_CONFIG_TABLE;
    }
	public static function getConfigTable(){
    	return self::$CONFIG_TABLE;
    }
    
	public static function getProjectTable(){
    	return self::$PROJECT_TABLE;
    }
   
	public static function getTaskTable(){
    	return self::$TASK_TABLE;
    }
	public static function getTaskAssignmentsTable(){
    	return self::$TASK_ASSIGNMENTS_TABLE;
    }
    
	public static function getTimesTable(){
    	return self::$TIMES_TABLE;
    }
	public static function getUserTable(){
    	return self::$USER_TABLE;
    }
	public static function getRateTable(){
    	return self::$RATE_TABLE;
    }    
	public static function getAbsenceTable(){
    	return self::$ABSENCE_TABLE;
    }    
	public static function getAllowanceTable(){
    	return self::$ALLOWANCE_TABLE;
    }    
}