<?php defined('APPLICATION') OR die('No direct access allowed.');
/**
 * @package  Unit_Test
 *
 * Default paths to scan for tests.
 */
$config['paths'] = array
(
	MODULES.'unit-test'.DS.'tests',
);

/**
 * Set to TRUE if you want to hide passed tests from the report.
 */
$config['hide_passed'] = FALSE;
