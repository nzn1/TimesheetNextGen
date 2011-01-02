<?
/**
 ******************************************************************************
 * Name:                    common_functions.php
 * Recommended Location:    /include
 * Last Updated:            August 2010
 * Author:                  Mark Wrightson
 * Contact:                 mark@voltnet.co.uk
 *
 * Description:
 * This is a set of commonly used functions throughout the web framework
 *
 *
 * Copyright:
 * This script may not be used by any other person without the express
 * permission of the author.
 ******************************************************************************/

/**
 *
 * pre_print_r - print out an object into a neat array list
 * surrounded by pre tags
 * @param $var - the object to be printed
 * @param $info - a reference name for the object to be printed
 * @param $flag - true returns a string.  false echo's the string
 * @param $showTrace - print out a mini debugtrace to determine
 * where the ppr command is being called from
 */
function ppr($var,$info = '',$flag=false,$showTrace=false){
	$i = pre_print_r($var,$info,$flag,$showTrace);
	return $i;
}

/**
 *
 * pre_print_r - print out an object into a neat array list
 * surrounded by pre tags
 * @param $var - the object to be printed
 * @param $info - a reference name for the object to be printed
 * @param $flag - true returns a string.  false echo's the string
 * @param $showTrace - print out a mini debugtrace to determine
 * where the ppr command is being called from
 */

function pre_print_r($var,$info = '',$flag=false,$showTrace=false){
	if(debug::getPprTrace()==1)$showTrace=true;
	$i = null;
	$i .= "<pre>$info: ";
	$i .= print_r($var,true);
	if($showTrace==true)$i .= "ppr Trace: ".getShortDebugTrace();
	$i .= "</pre>";

	if($flag==true)return $i;
	else echo $i;
}

/**
 * getShortDebugTrace() -
 * @param $level - number of trace levels to display.
 * @return $output - the returned trace log
 */
function getShortDebugTrace($level = null){

	if($level == null){
		$output = null;
		$traces = debug_backtrace();

		if (isset($traces[2])){
			$arr = array(
            'file'=>$traces[2]['file'],
            'line'=>$traces[2]['line'],
            'function'=>$traces[2]['function']        
			);
			$output = print_r($arr,true);
		}
		return $output;
	}

	else{
		$output = null;
		$traces = debug_backtrace();

		for ($i=0;$i<=$level;$i++){
			if (isset($traces[$i])){
				$arr = array(
              'file'=>$traces[$i]['file'],
              'line'=>$traces[$i]['line'],
              'function'=>$traces[$i]['function']        
				);
				$output .= ppr($arr,$i,true);
			}
		}
		return $output;
	}
}

/**
 *
 * get the time in micro seconds
 */
function getmicrotime(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

/**
 *
 * replaces spaces with %20 in a string
 *
 * @param $var - the string to be encoded
 */

function space_encode($var){
	$var = str_replace  (  ' '  ,  '%20'  ,  $var );
	return $var;
}

/**
 *
 * recursive_mysql_real_escape_string($data)
 * recursively cycles through $data to any depth array and escapes everything
 *
 * @param $data - the string or array to be escaped
 *
 */
function recursive_mysql_real_escape_string($data){
	if(is_array($data)){
		$data = array_map('recursive_mysql_real_escape_string', $data);
	}
	else{
		$data = mysql_real_escape_string($data);
	}
	return $data;
}

/**
 *
 * determines whether a given array is associative or indexed
 *
 * @param $var - an array
 *
 **/
function is_assoc($var){
	return is_array($var) && array_diff_key($var,array_keys(array_keys($var)));
}

/**
 * generateRandID - Generates a string made up of randomized
 * letters (lower and upper case) and digits and returns
 * the md5 hash of it to be used as a userid.
 */
function generateRandID(){
	return md5(generateRandStr(16));
}
 
/**
 * generateRandStr - Generates a string made up of randomized
 * letters (lower and upper case) and digits, the length
 * is a specified parameter.
 *
 * @param $length - length of the rand string
 */
function generateRandStr($length){
	$randstr = "";
	for($i=0; $i<$length; $i++){
		$randnum = mt_rand(0,61);
		if($randnum < 10){
			$randstr .= chr($randnum+48);
		}else if($randnum < 36){
			$randstr .= chr($randnum+55);
		}else{
			$randstr .= chr($randnum+61);
		}
	}
	return $randstr;
}
 
/**
 * if get_magic_quotes_gpc() is on then all cookie, post and get data will contain
 * characters that have been escaped by a backslash.  This function will recursively
 * go through and remove all of these slashes
 *
 * the string or array to remove the slashes
 */
 
function stripslashes_deep($value){
	$value = is_array($value) ? array_map('stripslashes_deep', $value):stripslashes($value);
	return $value;
}

function encodeEmail($e){
	$output = null;
	for ($i = 0; $i < strlen($e); $i++) { $output .= '&#'.ord($e[$i]).';'; }
	return $output;
}
?>