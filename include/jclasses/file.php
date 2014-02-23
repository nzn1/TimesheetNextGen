<?php
/**
 * The classes within were borrowed from and inspired by a collection of Joomla 1.6 classes
 */

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
