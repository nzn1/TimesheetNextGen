<?php

/**
 * *****************************************************************************
 * Name:                    authorisation.class.php
 * Recommended Location:    /include
 * Last Updated:            August 2010
 * Author:                  Mark Wrightson
 * Contact:                 mark@voltnet.co.uk
 *
 * Description:
 * The Authorisation Class is responsible for retreiving privilege data
 * from the database and determining whether the current user has the
 * required privileges to perform a particular action.
 *
 * Copyright:
 * This script may not be used by any other person without the express
 * permission of the author.
 ******************************************************************************/

/**
 *
 * The Authorisation Class is responsible for retreiving privilege data
 * from the database and determining whether the current user has the
 * required privileges to perform a particular action.
 *
 * @author Mark
 *
 */

 /**
  * NB THIS IS A PLACEHOLDER CLASS TILL I UPDATE THE AUTH MECHANISMS
  */   
class Auth
{
	const ACCESS_GRANTED = 1;
	const ACCESS_DENIED = 2;
	const ACCESS_UNKNOWN = 3;
	

  public static function requestAuth($privilegeGroup,$privilegeName){
  
  if(PageElements::getPageAuth()->authGroup =='Open'){
			return Auth::ACCESS_GRANTED;
		}
		$auth = Site::getAuthenticationManager()->hasAccess(PageElements::getPageAuth()->authGroup);
		
		if($auth == 0){
				return Auth::ACCESS_DENIED;
			}
		else if($auth != 1){
			$msg = 'authorisation error. Contact Webmaster';
			ErrorHandler::fatalError($msg);
		}
		else return Auth::ACCESS_GRANTED;
  }
}

?>