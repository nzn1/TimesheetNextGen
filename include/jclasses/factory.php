<?php
/**
 * The classes within were borrowed from and inspired by a collection of Joomla 1.6 classes
 */

abstract class JFactory
{
	public static $language = null;

	/**
	 * Get a language object
	 *
	 * Returns the global {@link JLanguage} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return JLanguage object
	 */
	public static function getLanguage()
	{
		if (!self::$language) {
			self::$language = self::_createLanguage();
		}

		return self::$language;
	}

	/**
	 * Reads a XML file.
	 *
	 * @param string  $fileORdata	Full path and file name, or xml data string.
	 * @param boolean $isFile 	true to load a file | false to load a string.
	 *
	 * @return mixed JXMLElement on success | false on error.
	 * @todo This may go in a separate class - error reporting may be improved.
	 */
	public static function getXML($fileORdata, $isFile = true)
	{
		// Disable libxml errors and allow to fetch error information as needed
		libxml_use_internal_errors(true);

		if ($isFile) {
			// Try to load the xml file
			$xml = simplexml_load_file($fileORdata);
		} else {
			// Try to load the xml string
			$xml = simplexml_load_string($fileORdata);
		}

		if (empty($xml)) {
			// There was an error
			//JError::raiseWarning(100, JText::_('JLIB_UTIL_ERROR_XML_LOAD'));

			if ($isFile) {
			//	JError::raiseWarning(100, $fileORdata);
			}

			foreach (libxml_get_errors() as $error) {
			//	JError::raiseWarning(100, 'XML: '.$error->message);
			}
		}

		return $xml ;
	}

	/**
	 * Create a language object
	 *
	 * @return JLanguage object
	 */
	private static function _createLanguage()
	{
		jimport('jclasses.language');

		$locale	= Site::Config()->get('locale');
		$debug	= 0;
		$lang	= JLanguage::getInstance($locale, $debug);

		return $lang;
	}
}
