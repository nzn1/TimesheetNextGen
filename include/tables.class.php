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

private static $ASSIGNMENTS_TABLE = "tsx_assignments";
private static $CLIENT_TABLE = "tsx_client";
private static $CONFIG_TABLE = "tsx_config";
private static $PROJECT_TABLE = "tsx_project";
private static $TASK_TABLE = "tsx_task";
private static $TASK_ASSIGNMENTS_TABLE = "tsx_task_assignments";
private static $TIMES_TABLE = "tsx_times";
private static $USER_TABLE = "tsx_user";
private static $RATE_TABLE = "tsx_billrate";
private static $ABSENCE_TABLE = "tsx_absences";
private static $ALLOWANCE_TABLE = "tsx_allowances";

    public static function getAssignmentsTable(){
    	return self::$ASSIGNMENTS_TABLE;
    }
	public static function getClientTable(){
    	return self::$CLIENT_TABLE;
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
	public static function getTimeTables(){
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