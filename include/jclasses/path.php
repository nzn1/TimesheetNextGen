<?php
/**
 * The classes within were borrowed from and inspired by a collection of Joomla 1.6 classes
 */

// no direct access
//defined('JPATH_BASE') or die;

if (!defined('DS')) {
	/** string Shortcut for the DIRECTORY_SEPARATOR define */
	define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JPATH_ROOT')) {
	/** string The root directory of the file system in native format */
	define('JPATH_ROOT', JPath::clean(JPATH_SITE));
}

/**
 * A Path handling class
 */
class JPath
{
	/**
	 * Checks for snooping outside of the file system root
	 *
	 * @param	string	A file system path to check
	 * @param	string	Directory separator (optional)
	 * @return	string	A cleaned version of the path
	 */
	public static function check($path, $ds = DIRECTORY_SEPARATOR)
	{
		if (strpos($path, '..') !== false) {
			//JError::raiseError(20, 'JPath::check Use of relative paths not permitted'); // don't translate
			jexit();
		}

		$path = JPath::clean($path);
		if (strpos($path, JPath::clean(JPATH_ROOT)) !== 0) {
			//JError::raiseError(20, 'JPath::check Snooping out of bounds @ '.$path); // don't translate
			jexit();
		}

		return $path;
	}

	/**
	 * Function to strip additional / or \ in a path name
	 *
	 * @param	string	The path to clean
	 * @param	string	Directory separator (optional)
	 * @return	string	The cleaned path
	 */
	public static function clean($path, $ds = DIRECTORY_SEPARATOR)
	{
		$path = trim($path);

		if (empty($path)) {
			$path = JPATH_ROOT;
		} else {
			// Remove double slashes and backslahses and convert all slashes and backslashes to DS
			$path = preg_replace('#[/\\\\]+#', $ds, $path);
		}

		return $path;
	}

}
