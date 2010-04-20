<?php
// Define Timesheet NG error constant
define('E_TIMESHEET', 42);

// Define 404 error constant
define('E_PAGE_NOT_FOUND', 43);

// Define database error constant
define('E_DATABASE_ERROR', 44);

final class TSNG {
	// The singleton instance of the controller
	public static $instance;
	
	/**
	 * @var  array to hold any internal caching, for config and language values etc
	 */
	public static $internal_cache;
	
	/**
	 * TSNG::config()
	 * Returns a config value based on key passed
	 * The key should be of the format:
	 *  module::array_key
	 * @param $key 
	 * @return unknown_type
	 */
	public final static function config($key) {
		// key is split into Module:array_key
		$parts = explode('.', $key, 3);
		$module = $parts[0];
		if(isset($parts[2])) {
			$file = $parts[1];
			$array_key = $parts[2];
		}
		else {
			$file = 'config';
			$array_key = $parts[1];
		}
		
		// will hold the config values
		$values = array();
		
		// check to see if we have cached this config file already
		if ( ! isset(self::$internal_cache['config'][$module][$file])) {
			// load in the file, which should contain a $lang array
			include(MODULES.$module.DS.'config'.DS.$file.EXT);
			if ( ! empty($config) && is_array($config)) {
				foreach ($config as $k => $v) {
					$values[$k] = $v;
				}
				self::$internal_cache['config'][$module][$file] = $values;
			}
		}
		// if __ALL__ has been specified, simply return the array
		if($array_key == '__ALL__') {
			return self::$internal_cache['config'][$module][$file];
		}
		// check to see if the array_key we're looking for exists
		elseif(!isset(self::$internal_cache['config'][$module][$file][$array_key])) {
			// do something here - log??
			return $array_key;
		}
		else {
			return self::$internal_cache['config'][$module][$file][$array_key];
		}
	}
	
	/**
	 * TSNG::lang()
	 * Returns a language string for current local based on key passed
	 * The key should be of the format:
	 *  module::array_key
	 * NOTE: Multiple files for module languages, eg:
	 * 	- error.php
	 *  - view_translations.php 
	 *  ?? 
	 * @param $key
	 * @return language string
	 */
	public final static function lang($key) {
		// key is split into Module:array_key
		list($module, $array_key) = explode('.', $key, 2);

		// get the current local
		$locales = unserialize(self::config('core.language'));
		
		// will hold the language messages
		$messages = array();
		// check to see if we have cached this language file already
		if ( ! isset(self::$internal_cache['language'][$locales[0]][$module])) {
			// load in the file, which should contain a $lang array
			$lang_file_exists = false;
			foreach($locales AS $locale) {
				if(file_exists(MODULES.strtolower($module).DS.'i18n'.DS.$locale.EXT)) {
					include(MODULES.strtolower($module).DS.'i18n'.DS.$locale.EXT);
					$lang_file_exists = true;
				}
			}
			// no language file found
			if(!$lang_file_exists) {
				echo "key = ".$key."<BR>";
				echo "@TODO throw execption, no language file found<BR>";
				exit;
			}
			// load in language
			if ( ! empty($lang) && is_array($lang)) {
				foreach ($lang as $k => $v) {
					$messages[$k] = $v;
				}
				self::$internal_cache['language'][$locales[0]][$module] = $messages;
			}
		}
		
		// check to see if the array_key we're looking for exists
		if(!isset(self::$internal_cache['language'][$locales[0]][$module][$array_key])) {
			// do something here - log??
			$line = $array_key;
		}
		else {
			$line = self::$internal_cache['language'][$locales[0]][$module][$array_key];
			if (is_string($line) AND func_num_args() > 1) {
				$args = array_slice(func_get_args(), 1);
				// Add the arguments into the line
				$line = vsprintf($line, is_array($args[0]) ? $args[0] : $args);
			}
		}
		return $line;
	}
	
}