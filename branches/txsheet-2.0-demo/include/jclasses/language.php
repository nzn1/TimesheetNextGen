<?php
/**
 * The classes within were borrowed from and inspired by a collection of Joomla 1.6 classes
 */

// No direct access.
//defined('JPATH_BASE') or die;

/**
 * Allows for quoting in language .ini files.
 */
define('_QQ_', '"');


/**
 * Languages/translation handler class
 */
class JLanguage
{
	protected static $languages = array();
	/**
	 * Debug language, If true, highlights if string isn't found
	 *
	 * @var		boolean
	 */
	protected $debug = false;

	protected $junk = '';

	/**
	 * The default language
	 *
	 * The default language is used when a language file in the requested language does not exist.
	 *
	 * @var		string
	 */
	protected $default	= 'en-GB';

	/**
	 * An array of orphaned text
	 *
	 * @var		array
	 */
	protected $orphans = array();

	/**
	 * Array holding the language metadata
	 *
	 * @var		array
	 */
	protected $metadata = null;

	/**
	 * Array|boolean holding the language locale
	 *
	 * @var		array|boolean
	 */
	protected $locale = null;

	/**
	 * The language to load
	 *
	 * @var		string
	 */
	protected $lang = null;

	/**
	 * List of language files that have been loaded
	 *
	 * @var		array of arrays
	 */
	protected $paths = array();

	/**
	 * An array of errors
	 *
	 * @var		array of error messages or JExceptions objects.
	 */
	protected $_errors = array();

	/**
	 * List of language files that are in error state
	 *
	 * @var		array of string
	 */
	protected $errorfiles = array();

	/**
	 * Translations
	 *
	 * @var		array
	 */
	protected $strings = null;

	/**
	 * An array of used text, used during debugging
	 *
	 * @var		array
	 */
	protected $used = array();

	/**
	 * Counter for number of loads
	 *
	 * @var		integer
	 */
	protected $counter = 0;

	/**
	 * Name of the transliterator function for this language
	 *
	 * @var		string
	 */
	protected $transliterator = null;

	/**
	 * Name of the pluralSufficesCallback function for this language
	 *
	 * @var		string
	 */
	protected $pluralSufficesCallback = null;

	/**
	 * Constructor activating the default information of the language
	 */
	public function __construct($lang = null, $debug = false)
	{
		$this->strings = array ();

		if ($lang == null) {
			$lang = $this->default;
		}

		$this->junk = dirname(__FILE__);

		$this->setLanguage($lang);
		$this->setDebug($debug);

		// Look for a language specific localise class
		$class = str_replace('-', '_', $lang . 'Localise');
		if (class_exists($class)) {
			/* Class exists. Try to find
			 * -a transliterate method,
			 * -a getPluralSuffixes method,
			 */
			if (method_exists($class, 'transliterate')) {
				$this->transliterator = array($class, 'transliterate');
			}
			if (method_exists($class, 'getPluralSuffixes')) {
				$this->pluralSufficesCallback = array($class, 'getPluralSuffixes');
			}
		}

		$this->load();
	}

	/**
	 * Returns a language object
	 *
	 * @param	string $lang  The language to use.
	 * @param	boolean	$debug	The debug mode
	 * @return	JLanguage  The Language object.
	 */
	public static function getInstance($lang, $debug=false)
	{
		if (!isset(self::$languages[$lang.$debug])) {
			self::$languages[$lang.$debug] = new JLanguage($lang, $debug);
		}
		return self::$languages[$lang.$debug];
	}

	/**
	 * Translate function, mimics the php gettext (alias _) function
	 *
	 * @param	string		$string	The string to translate
	 * @param	boolean	$jsSafe		Make the result javascript safe
	 * @param	boolean	$interpreteBackslashes		Interprete \t and \n
	 * @return	string	The translation of the string
	 * @note	The function check if $jsSafe is true then if $interpreteBackslashes is true
	 */
	public function _($string, $jsSafe = false, $interpreteBackSlashes = true)
	{
		$key = strtoupper($string);
		if (isset ($this->strings[$key])) {
			$string = $this->debug ? '**'.$this->strings[$key].'**' : $this->strings[$key];

			// Store debug information
			if ($this->debug) {
				$caller = $this->getCallerInfo();

				if (! array_key_exists($key, $this->used)) {
					$this->used[$key] = array();
				}

				$this->used[$key][] = $caller;
			}
		} else {
			if ($this->debug) {
				$caller = $this->getCallerInfo();
				$caller['string'] = $string;

				if (! array_key_exists($key, $this->orphans)) {
					$this->orphans[$key] = array();
				}

				$this->orphans[$key][] = $caller;

				$string = '??'.$string.'??';
			}
		}

		if ($jsSafe) {
			// javascript filter
			$string = addslashes($string);
		} elseif ($interpreteBackSlashes) {
			// interprete \n and \t characters
			$string = str_replace(array('\\\\','\t','\n'),array("\\", "\t","\n"),$string);
		}

		//help find where JText is missing
		$string="j".$string;
		return $string;
	}

	/**
	 * Transliterate function
	 *
	 * This method processes a string and replaces all accented UTF-8 characters by unaccented
	 * ASCII-7 "equivalents"
	 *
	 * @param	string	$string	The string to transliterate
	 * @return	string	The transliteration of the string
	 */
	public function transliterate($string)
	{
		include_once dirname(__FILE__) . '/latin_transliterate.php';

		if ($this->transliterator !== null) {
			return call_user_func($this->transliterator, $string);
		}

		$string = JLanguageTransliterate::utf8_latin_to_ascii($string);
		$string = JString::strtolower($string);

		return $string;
	}

	/**
	 * Getter for transliteration function
	 *
	 * @return	string|function Function name or the actual function for PHP 5.3
	 */
	public function getTransliterator()
	{
		return $this->transliterator;
	}

	/**
	 * Set the transliteration function
	 *
	 * @return	string|function Function name or the actual function for PHP 5.3
	 */
	public function setTransliterator($function)
	{
		$previous = $this->transliterator;
		$this->transliterator = $function;
		return $previous;
	}

	/**
	 * pluralSuffices function
	 *
	 * This method return an array of suffices for plural rules
	 *
	 * @param	int	$count	The count number
	 * @return	array	The array of suffices
	 */
	public function getPluralSuffixes($count) {
		if ($this->pluralSufficesCallback !== null) {
			return call_user_func($this->pluralSufficesCallback, $count);
		} else {
			return array((string)$count);
		}
	}

	/**
	 * Getter for pluralSufficesCallback function
	 *
	 * @return      string|function Function name or the actual function for PHP 5.3
	 */
	public function getPluralSuffixesCallback() {
		return $this->pluralSufficesCallback;
	}

	/**
	 * Set the pluralSuffices function
	 *
	 * @return      string|function Function name or the actual function for PHP 5.3
	 */
	public function setPluralSufficesCallback($function) {
		$previous = $this->pluralSufficesCallback;
		$this->pluralSufficesCallback = $function;
		return $previous;
	}

	/**                                             
	* Function to strip additional / or \ in a path name
	*                              
	* @param       string  The path to clean
	* @param       string  Directory separator (optional)
	* @return      string  The cleaned path
	*/                     
	public static function pathClean($path, $ds = DIRECTORY_SEPARATOR) {                       
		$path = trim($path);

		if (empty($path)) {
			$path = JPATH_ROOT;
		} else {
			// Remove double slashes and backslahses and convert all slashes and backslashes to DS
			$path = preg_replace('#[/\\\\]+#', $ds, $path);
		}

		return $path;
	}

	/**
	 * Check if a language exists
	 *
	 * This is a simple, quick check for the directory that should contain language files for the given user.
	 *
	 * @param	string $lang Language to check
	 * @param	string $basePath Optional path to check
	 * @return	boolean True if the language exists
	 */
	public static function exists($lang, $basePath = JPATH_BASE)
	{
		static	$paths	= array();

		// Return false if no language was specified
		if (! $lang) {
			return false;
		}

		$path	= "$basePath/language/$lang";

		// Return previous check results if it exists
		if (isset($paths[$path]))
		{
			return $paths[$path];
		}

		$paths[$path]	= is_dir($this->pathClean($path));

		return $paths[$path];
	}

	/**
	 * Loads a single language file and appends the results to the existing strings
	 *
	 * @param	string	$basePath	The basepath to use
	 * @param	string	$lang		The language to load, default null for the current language
	 * @param	boolean	$default	Flag that force the default language to be loaded if the current does not exist
	 * @return	boolean	True, if the file has successfully loaded.
	 */
	public function load($basePath = JPATH_BASE, $lang = null, $default = true)
	{
		if (! $lang) {
			$lang = $this->lang;
		}

		$path = self::getLanguagePath($basePath, $lang);

		$filename = "$path/$lang.ini";

		$result = false;
		if (isset($this->paths[$filename])) {
			// Strings for this file have already been loaded
			$result = true;
		} else {
			// Load the language file
			$result = $this->loadLanguage($filename);

			// Check if there was a problem with loading the file
			if ($result === false && $default) {
				// No strings, so either file doesn't exist or the file is invalid
				$oldFilename = $filename;

				// Check the standard file name
				$path		= self::getLanguagePath($basePath, $this->default);
				$filename 	= $this->default;
				$filename	= "$path/$filename.ini";

				// If the one we tried is different than the new name, try again
				if ($oldFilename != $filename) {
					$result = $this->loadLanguage($filename);
				}
			}
		}
		return $result;
	}

	/**
	 * Loads a language file
	 *
	 * This method will not note the successful loading of a file - use load() instead
	 *
	 * @param	string The name of the file
	 * @return	boolean True if new strings have been added to the language
	 * @see		JLanguage::load()
	 */
	protected function loadLanguage($filename)
	{

		$this->counter++;

		$result	= false;

		$strings = false;
		if (file_exists($filename)) {
			$strings = $this->parse($filename);
		}

		if ($strings) {
			if (is_array($strings)) {
				$this->strings = array_merge($this->strings, $strings);
				$result = true;
			}
		}

		$this->paths[$filename] = $result;

		return $result;
	}

	/**
	 * Parses a language file
	 *
	 * @param	string	$filename	The name of the file.
	 *
	 * @return	array	The array of parsed strings.
	 */
	protected function parse($filename)
	{
		$version = phpversion();
		$strings = array();

		// Capture hidden PHP errors from the parsing.
		$php_errormsg	= null;
		$track_errors	= ini_get('track_errors');
		ini_set('track_errors', true);

		if ($version >= '5.3.0') {  //parse_ini_string introduced in 5.3.0
			$contents = file_get_contents($filename);
			$contents = str_replace('_QQ_','"\""',$contents);
			$strings = @parse_ini_string($contents);
		} else {
			$strings = @parse_ini_file($filename);
			if ($version >= '4.3.0' && is_array($strings)) {
				foreach($strings as $key => $string) {
					$strings[$key]=str_replace('_QQ_','"',$string);
				}
			}
		}

		// Restore error tracking to what it was before.
		ini_set('track_errors',$track_errors);

		if ($this->debug) {
			// Initialise variables for manually parsing the file for common errors.
			$blacklist	= array('YES','NO','NULL','FALSE','ON','OFF','NONE','TRUE');
			$regex		= '/^(|(\[[^\]]*\])|([A-Z][A-Z0-9_\-]*\s*=(\s*(("[^"]*")|(_QQ_)))+))\s*(;.*)?$/';
			$this->debug = false;
			$errors		= array();
			$lineNumber	= 0;

			$fh = fopen($filename, 'r');

			while (!feof($fh)) {
				$line = fgets($fh);
				$lineNumber++;

				// Check that the key is not in the blacklist and that the line format passes the regex.
				$key = strtoupper(trim(substr($line, 0, strpos($line, '='))));
				if (!preg_match($regex, $line) || in_array($key, $blacklist)) {
					$errors[] = $lineNumber;
				}
			}

			if ($php_errormsg) {
				$this->setError($php_errormsg);
			}

			$res = fclose($fh);
			if (!$res) {
				$this->setError($php_errormsg);
			}

			// Check if we encountered any errors.
			if (count($errors)) {
				if (basename($filename) != $this->lang.'.ini') {
					$this->errorfiles[$filename] = $filename.JText::sprintf('JERROR_PARSING_LANGUAGE_FILE', implode(', ', $errors));
				} else {
					$this->errorfiles[$filename] = $filename . '&#160;: error(s) in line(s) ' . implode(', ', $errors);
				}
			} else if ($php_errormsg) {
				// We didn't find any errors but there's probably a parse notice.
				$this->errorfiles['PHP'.$filename] = 'PHP parser errors :'.$php_errormsg;
			}

			$this->debug = true;
		}

		return $strings;
	}

	/**
	 * Get a matadata language property
	 *
	 * @param	string $property	The name of the property
	 * @param	mixed  $default	The default value
	 * @return	mixed The value of the property
	 */
	public function get($property, $default = null)
	{
		if (isset ($this->metadata[$property])) {
			return $this->metadata[$property];
		}
		return $default;
	}

	/**
	 * Determine who called JLanguage or JText
	 *
	 * @return	array Caller information
	 */
	protected function getCallerInfo()
	{
		// Try to determine the source if none was provided
		if (!function_exists('debug_backtrace')) {
			return null;
		}

		$backtrace	= debug_backtrace();
		$info		= array();

		// Search through the backtrace to our caller
		$continue = true;
		while ($continue && next($backtrace)) {
			$step	= current($backtrace);
			$class	= @ $step['class'];

			// We're looking for something outside of language.php
			if ($class != 'JLanguage' && $class != 'JText') {
				$info['function']	= @ $step['function'];
				$info['class']		= $class;
				$info['step']		= prev($backtrace);

				// Determine the file and name of the file
				$info['file']		= @ $step['file'];
				$info['line']		= @ $step['line'];

				$continue = false;
			}
		}

		return $info;
	}

	/**
	 * Getter for Name
	 *
	 * @return	string Official name element of the language
	 */
	public function getName() {
		return $this->metadata['name'];
	}

	/**
	 * Get a list of language files that have been loaded
	 *
	 * @return	array
	 */
	public function getPaths()
	{
		return $this->paths;
	}

	/**
	 * Get a list of language files that are in error state
	 *
	 * @return	array
	 */
	public function getErrorFiles()
	{
		return $this->errorfiles;
	}

	/**
	 * Get for the language tag (as defined in RFC 3066)
	 *
	 * @return	string The language tag
	 */
	public function getTag() {
		return $this->metadata['tag'];
	}

	/**
	 * Get the RTL property
	 *
	 * @return	boolean True is it an RTL language
	 */
	public function isRTL()
	{
		return $this->metadata['rtl'];
	}

	/**
	 * Set the Debug property
	 *
	 * @return	boolean Previous value
	 */
	public function setDebug($debug)
	{
		$previous	= $this->debug;
		$this->debug = $debug;
		return $previous;
	}

	/**
	 * Get the Debug property
	 *
	 * @return	boolean True is in debug mode
	 */
	public function getDebug()
	{
		return $this->debug;
	}

	/**
	 * Get the default language code
	 *
	 * @return	string Language code
	 */
	public function getDefault()
	{
		return $this->default;
	}

	/**
	 * Set the default language code
	 *
	 * @return	string Previous value
	 */
	public function setDefault($lang)
	{
		$previous	= $this->default;
		$this->default	= $lang;
		return $previous;
	}

	/**
	 * Get the list of orphaned strings if being tracked
	 *
	 * @return	array Orphaned text
	 */
	public function getOrphans()
	{
		return $this->orphans;
	}

	/**
	 * Get the list of used strings
	 *
	 * Used strings are those strings requested and found either as a string or a constant
	 *
	 * @return	array	Used strings
	 */
	public function getUsed()
	{
		return $this->used;
	}

	/**
	 * Determines if a key exists
	 *
	 * @param	key $key	The key to check
	 * @return	boolean True, if the key exists
	 */
	function hasKey($string)
	{
		$key = strtoupper($string);
		return isset ($this->strings[$key]);
	}

	/**
	 * Returns a associative array holding the metadata
	 *
	 * @param	string	The name of the language
	 * @return	mixed	If $lang exists return key/value pair with the language metadata,
	 *				otherwise return NULL
	 */
	public static function getMetadata($lang)
	{
		$path = self::getLanguagePath(JPATH_BASE, $lang);
		$file = "$lang.xml";

		$result = null;
		if (is_file("$path/$file")) {
			$result = self::parseXMLLanguageFile("$path/$file");
		}

		return $result;
	}

	/**
	 * Returns a list of known languages for an area
	 *
	 * @param	string	$basePath	The basepath to use
	 * @return	array	key/value pair with the language file and real name
	 */
	public static function getKnownLanguages($basePath = JPATH_BASE)
	{
		$dir = self::getLanguagePath($basePath);
		$knownLanguages = self::parseLanguageFiles($dir);

		return $knownLanguages;
	}

	/**
	 * Get the path to a language
	 *
	 * @param	string $basePath  The basepath to use
	 * @param	string $language	The language tag
	 * @return	string	language related path or null
	 */
	public static function getLanguagePath($basePath = JPATH_BASE, $language = null)
	{
		$dir = "$basePath/language";
		if (!empty($language)) {
			$dir .= "/$language";
		}
		return $dir;
	}

	/**
	 * Set the language attributes to the given language
	 *
	 * Once called, the language still needs to be loaded using JLanguage::load()
	 *
	 * @param	string	$lang	Language code
	 * @return	string	Previous value
	 */
	public function setLanguage($lang)
	{
		$previous	= $this->lang;
		$this->lang	= $lang;
		$this->metadata	= $this->getMetadata($this->lang);

		return $previous;
	}

	/**
	 * Get the language locale based on current language
	 *
	 * @return	array|false	The locale according to the language
	 */
	public function getLocale()
	{
		if (!isset($this->locale))
		{
			$locale = str_replace(' ', '', isset($this->metadata['locale']) ? $this->metadata['locale'] : '');
			if ($locale) {
				$this->locale = explode(',', $locale);
			} else {
				$this->locale = false;
			}
		}
		return $this->locale;
	}

	/**
	 * Get the first day of the week for this language
	 *
	 * @return	int	The first day of the week according to the language
	 */
	public function getFirstDay()
	{
		return (int) (isset($this->metadata['firstDay']) ? $this->metadata['firstDay'] : 0);
	}

	/**
	 * Searches for language directories within a certain base dir
	 *
	 * @param	string	$dir	directory of files
	 * @return	array	Array holding the found languages as filename => real name pairs
	 */
	public static function parseLanguageFiles($dir = null)
	{
		//jimport('jclasses.filesystem');

		$languages = array ();

		$subdirs = JFolder::folders($dir);
		foreach ($subdirs as $path) {
			$langs = self::parseXMLLanguageFiles("$dir/$path");
			$languages = array_merge($languages, $langs);
		}

		return $languages;
	}

	/**
	 * Parses XML files for language information
	 *
	 * @param	string	$dir	Directory of files
	 * @return	array	Array holding the found languages as filename => metadata array
	 */
	public static function parseXMLLanguageFiles($dir = null)
	{
		if ($dir == null) {
			return null;
		}

		$languages = array ();
		//jimport('jclasses.filesystem');
		$files = JFolder::files($dir, '^([-_A-Za-z]*)\.xml$');
		foreach ($files as $file) {
			if ($content = file_get_contents("$dir/$file")) {
				if ($metadata = self::parseXMLLanguageFile("$dir/$file")) {
					$lang = str_replace('.xml', '', $file);
					$languages[$lang] = $metadata;
				}
			}
		}
		return $languages;
	}

	/**
	 * Parse XML file for language information.
	 *
	 * @param	string	$path	Path to the xml files
	 * @return	array	Array holding the found metadata as a key => value pair
	 */
	public static function parseXMLLanguageFile($path)
	{
		// Try to load the file
		if (!$xml = JFactory::getXML($path)) {
			return null;
		}

		// Check that it's a metadata file
		if ((string)$xml->getName() != 'metafile') {
			return null;
		}

		$metadata = array();

		foreach ($xml->metadata->children() as $child) {
			$metadata[$child->getName()] = (string) $child;
		}

		return $metadata;
	}

	/**
	 * Get the most recent error message.
	 *
	 * @param	integer	$i			Option error index.
	 * @param	boolean	$toString	Indicates if JError objects should return their error message.
	 * @return	string	Error message
	 */
	public function getError($i = null, $toString = true)
	{
		// Find the error
		if ($i === null) {
			// Default, return the last message
			$error = end($this->_errors);
		} else if (!array_key_exists($i, $this->_errors)) {
			// If $i has been specified but does not exist, return false
			return false;
		} else {
			$error	= $this->_errors[$i];
		}

		// Check if only the string is requested
		if (($error instanceof Exception) && $toString) {
			return (string)$error;
		}

		return $error;
	}

	/**
	 * Return all errors, if any.
	 *
	 * @return	array	Array of error messages or JErrors.
	 */
	public function getErrors()
	{
		return $this->_errors;
	}

	/**
	 * Add an error message.
	 *
	 * @param	string $error	Error message.
	 */
	public function setError($error)
	{
		array_push($this->_errors, $error);
	}

	/**
	 * Magic method to convert the object to a string gracefully.
	 *
	 * @return	string	The classname.
	 */
	public function __toString()
	{
		return get_class($this);
	}

	/**
	 * @see __toString()
	 */
	function toString()
	{
		return __toString();
	}
}
