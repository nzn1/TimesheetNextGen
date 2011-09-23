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

/**
 * A File handling class
 */
class JFile
{
	/**
	 * Gets the extension of a file name
	 *
	 * @param	string	$file	The file name
	 *
	 * @return	string	The file extension
	 */
	public static function getExt($file)
	{
		$dot = strrpos($file, '.') + 1;
		return substr($file, $dot);
	}

	/**
	 * Strips the last extension off a file name
	 *
	 * @param	string	$file The file name
	 *
	 * @return	string	The file name without the extension
	 */
	public static function stripExt($file)
	{
		return preg_replace('#\.[^.]*$#', '', $file);
	}

	/**
	 * Makes file name safe to use
	 *
	 * @param	string	$file	The name of the file [not full path]
	 *
	 * @return	string	The sanitised string
	 */
	public static function makeSafe($file)
	{
		$regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#');
		return preg_replace($regex, '', $file);
	}

	/**
	 * Wrapper for the standard file_exists function
	 *
	 * @param	string	$file File path
	 *
	 * @return	boolean	True if path is a file
	 */
	public static function exists($file)
	{
		return is_file(JPath::clean($file));
	}

	/**
	 * Returns the name, without any path.
	 *
	 * @param	string	$file	File path
	 * @return	string	filename
	 */
	public static function getName($file)
	{
		$slash = strrpos($file, DS);
		if ($slash !== false) {
			return substr($file, $slash + 1);
		} else {
			return $file;
		}
	}
}

/**
 * A Folder handling class
 */
abstract class JFolder
{
	/**
	 * Wrapper for the standard file_exists function
	 *
	 * @param string Folder name relative to installation dir
	 * @return boolean True if path is a folder
	 */
	public static function exists($path)
	{
		return is_dir(JPath::clean($path));
	}

	/**
	 * Utility function to read the files in a folder.
	 *
	 * @param	string	The path of the folder to read.
	 * @param	string	A filter for file names.
	 * @param	mixed	True to recursively search into sub-folders, or an
	 * integer to specify the maximum depth.
	 * @param	boolean	True to return the full path to the file.
	 * @param	array	Array with names of files which should not be shown in
	 * the result.
	 * @param	array	Array of filter to exclude
	 * @return	array	Files in the given folder.
	 */
	public static function files($path, $filter = '.', $recurse = false, $full = false, $exclude = array('.svn', 'CVS','.DS_Store','__MACOSX'), $excludefilter = array('^\..*','.*~'))
	{
		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Is the path a folder?
		if (!is_dir($path))
		{
			//JError::raiseWarning(21, JText::sprintf('JLIB_FILESYSTEM_ERROR_PATH_IS_NOT_A_FOLDER_FILES', $path));
			return false;
		}

		// Compute the excludefilter string
		if(count($excludefilter)) {
			$excludefilter_string = '/('. implode('|', $excludefilter) .')/';
		}
		else {
			$excludefilter_string = '';
		}

		// Get the files
		$arr = self::_items($path, $filter, $recurse, $full, $exclude, $excludefilter_string, true);

		// Sort the files
		asort($arr);
		return array_values($arr);
	}

	/**
	 * Utility function to read the folders in a folder.
	 *
	 * @param	string	The path of the folder to read.
	 * @param	string	A filter for folder names.
	 * @param	mixed	True to recursively search into sub-folders, or an
	 * integer to specify the maximum depth.
	 * @param	boolean	True to return the full path to the folders.
	 * @param	array	Array with names of folders which should not be shown in
	 * the result.
	 * @param	array	Array with regular expressions matching folders which
	 * should not be shown in the result.
	 * @return	array	Folders in the given folder.
	 */
	public static function folders($path, $filter = '.', $recurse = false, $full = false, $exclude = array('.svn', 'CVS','.DS_Store','__MACOSX'), $excludefilter = array('^\..*'))
	{
		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Is the path a folder?
		if (!is_dir($path))
		{
			//JError::raiseWarning(21, JText::sprintf('JLIB_FILESYSTEM_ERROR_PATH_IS_NOT_A_FOLDER_FOLDER', $path));
			return false;
		}

		// Compute the excludefilter string
		if(count($excludefilter)){
			$excludefilter_string = '/('. implode('|', $excludefilter) .')/';
		}
		else {
			$excludefilter_string = '';
		}

		// Get the folders
		$arr = self::_items($path, $filter, $recurse, $full, $exclude, $excludefilter_string, false);

		// Sort the folders
		asort($arr);
		return array_values($arr);
	}

	/**
	 * Function to read the files/folders in a folder.
	 *
	 * @param	string	The path of the folder to read.
	 * @param	string	A filter for file names.
	 * @param	mixed	True to recursively search into sub-folders, or an
	 * integer to specify the maximum depth.
	 * @param	boolean	True to return the full path to the file.
	 * @param	array	Array with names of files which should not be shown in
	 * the result.
	 * @param	string	Regexp of files to exclude
	 * @param	boolean	true to read the files, false to read the folders
	 * @return	array	Files.
	 */
	private static function _items($path, $filter, $recurse, $full, $exclude, $excludefilter_string, $findfiles)
	{
		// Initialise variables.
		$arr = array();

		// read the source directory
		$handle = opendir($path);
		while (($file = readdir($handle)) !== false)
		{
			if ($file != '.' && $file != '..' && !in_array($file, $exclude) && (empty($excludefilter_string) || !preg_match($excludefilter_string, $file)))
			{
				// Compute the fullpath
				$fullpath = $path . DS . $file;

				// Compute the isDir flag
				$isDir = is_dir($fullpath);

				if (($isDir xor $findfiles) && preg_match("/$filter/", $file))
				{
					// (fullpath is dir and folders are searched or fullpath is not dir and files are searched) and file matches the filter
					if ($full) {
						// full path is requested
						$arr[] = $fullpath;
					}
					else {
						// filename is requested
						$arr[] = $file;
					}
				}
				if ($isDir && $recurse)
				{
					// Search recursively
					if (is_integer($recurse)) {
						// Until depth 0 is reached
						$arr = array_merge($arr, self::_items($fullpath, $filter, $recurse - 1, $full, $exclude, $excludefilter_string, $findfiles));
					}
					else {
						$arr = array_merge($arr, self::_items($fullpath, $filter, $recurse, $full, $exclude, $excludefilter_string, $findfiles));
					}
				}
			}
		}
		closedir($handle);
		return $arr;
	}

	/**
	 * Makes path name safe to use.
	 *
	 * @access	public
	 * @param	string The full path to sanitise.
	 * @return	string The sanitised string.
	 * @since	1.5
	 */
	public static function makeSafe($path)
	{
		//$ds = (DS == '\\') ? '\\' . DS : DS;
		$regex = array('#[^A-Za-z0-9:_\\\/-]#');
		return preg_replace($regex, '', $path);
	}
}
