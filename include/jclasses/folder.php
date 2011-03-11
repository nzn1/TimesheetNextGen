<?php
/**
 * The classes within were borrowed from and inspired by a collection of Joomla 1.6 classes
 */

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
