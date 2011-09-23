<?php
/**
 * The classes within were borrowed from and inspired by a collection of Joomla 1.6 classes
 */

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

spl_autoload_register(array('JLoader','load'));

/**
 * Load and automatically register J* classes
 */
abstract class JLoader
{
	private static $paths = array();

	private static $classes = array();

	/**
	 * Loads a class from specified directories.
	 *
	 * @param string	The class name to look for (dot notation).
	 * @param string	Search this directory for the class.
	 * @param string	String used as a prefix to denote the full path of the file (dot notation).
	 */
	public static function import($filePath, $base = null, $key = '') {
		$keyPath = $key ? $key . $filePath : $filePath;

		if (!isset(JLoader::$paths[$keyPath])) {
			if (!$base) {
				$base = dirname(__FILE__);
			}

			$parts = explode('.', $filePath);

			$className = array_pop($parts);
			switch ($className)
			{
				case 'helper' :
					$className = ucfirst(array_pop($parts)).ucfirst($className);
					break;

				default :
					$className = ucfirst($className);
					break;
			}

			$path = str_replace('.', DS, $filePath);

			// Prepend the classname with a capital J.
			$className = 'J'.$className;
			$classes = JLoader::register($className, $base.DS.$path.'.php');

			$rs = isset($classes[strtolower($className)]);

			JLoader::$paths[$keyPath] = $rs;
		}

		return JLoader::$paths[$keyPath];
	}

	/**
	 * Add a class to autoload.
	 *
	 * @param	string			The class name
	 * @param	string			Full path to the file that holds the class
	 * @return	array|boolean	Array of classes
	 */
	public static function &register($class = null, $file = null) {
		if ($class && is_file($file)) {
			// Force to lower case.
			$class = strtolower($class);
			JLoader::$classes[$class] = $file;
		}

		return JLoader::$classes;
	}

	/**
	 * Load the file for a class
	 *
	 * @param   string	The class that will be loaded
	 * @return  boolean True on success
	 */
	public static function load($class) {
		$class = strtolower($class); //force to lower case

		if (class_exists($class)) {
			return true;
		}

		if (array_key_exists($class, JLoader::$classes)) {
			include_once JLoader::$classes[$class];
			return true;
		}
		return false;
	}

	public static function getClasses() {
		return JLoader::$classes;
	}
}

/**
 * Global application exit.
 *
 * This function provides a single exit point for the framework.
 *
 * @param	mixed	Exit code or string. Defaults to zero.
 */
function jexit($message = 0) {
	exit($message);
}

/**
 * Intelligent file importer
 *
 * @param	string	A dot syntax path.
 */
function jimport($path) {
	return JLoader::import($path);
}
