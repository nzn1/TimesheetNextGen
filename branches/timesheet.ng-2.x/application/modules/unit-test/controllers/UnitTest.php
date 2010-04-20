<?php defined('APPLICATION') OR die('No direct access allowed.');
/**
 * Unit_Test controller.
 *
 * $Id: unit_test.php 3769 2008-12-15 00:48:56Z zombor $
 *
 * @package    Unit_Test
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Controller_UnitTest extends Controller_Core {

	const ALLOW_PRODUCTION = FALSE;

	public function index()
	{
		// Run tests and show results!
		try {
			echo new UnitTest;
		}
		catch(Exception $e) {
			echo "Exception:<pre>"; print_r($e); echo "</pre>";
		}
	}

}