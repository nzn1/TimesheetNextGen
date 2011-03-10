<?php
/*******************************************************************************
 * Name:                    index.php
 * Recommended Location:    /
 * Last Updated:            April 2010
 * Author:                  Mark Wrightson
 * Contact:                 mark@voltnet.co.uk
 *
 * Description:
 *
 *
 * Copyright:
 * This script may not be used by any other person without the express
 * permission of the author.
 ******************************************************************************/
if (defined('SESSION_INCLUDED')){
	ErrorHandler::fatalError("index.php was called from index.php<br />".
  "Recursive relationships are not allowed<br />
  <p>Return to <a href=\"".Config::getRelativeRoot()."/\">Home Page</a></p>");
}

require_once('include/site/site.class.php');
$site = new Site();
$site->load();


?>