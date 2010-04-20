<?php
/**
 * Timesheet Exceptions
 * Modiefied version of the Kohanaphp framework
 * http://www.kohanaphp.com
 */
/**
 * Creates a generic i18n exception.
 */
class Core_Exception extends Exception {

	// Template file
	protected $template = 'kohana_error_page';

	// Header
	protected $header = FALSE;

	// Error code
	protected $code = E_TIMESHEET;

	/**
	 * Set exception message.
	 *
	 * @param  string  i18n language key for the message
	 * @param  array   addition line parameters
	 */
	public function __construct($error)
	{
		$args = array_slice(func_get_args(), 1);

		// Fetch the error message
		$message = TSNG::lang($error, $args);

		if ($message === $error OR empty($message))
		{
			// Unable to locate the message for the error
			$message = 'Unknown Exception: '.$error;
		}

		// Sets $this->message the proper way
		parent::__construct($message);
	}

	/**
	 * Magic method for converting an object to a string.
	 *
	 * @return  string  i18n message
	 */
	public function __toString()
	{
		return (string) $this->message;
	}

	/**
	 * Fetch the template name.
	 *
	 * @return  string
	 */
	public function getTemplate()
	{
		return $this->template;
	}

	/**
	 * Sends an Internal Server Error header.
	 *
	 * @return  void
	 */
	public function sendHeaders()
	{
		// Send the 500 header
		header('HTTP/1.1 500 Internal Server Error');
	}

} // End Kohana Exception

/**
 * Creates a custom exception.
 */
class Core_User_Exception extends Core_Exception {

	/**
	 * Set exception title and message.
	 *
	 * @param   string  exception title string
	 * @param   string  exception message string
	 * @param   string  custom error template
	 */
	public function __construct($title, $message, $template = FALSE)
	{
		Exception::__construct($message);

		$this->code = $title;

		if ($template !== FALSE)
		{
			$this->template = $template;
		}
	}

} // End Kohana PHP Exception

/**
 * Creates a Page Not Found exception.
 */
class Core_404_Exception extends Core_Exception {

	protected $code = E_PAGE_NOT_FOUND;

	/**
	 * Set internal properties.
	 *
	 * @param  string  URL of page
	 * @param  string  custom error template
	 */
	public function __construct($page = FALSE, $template = FALSE)
	{
		if ($page === FALSE)
		{
			// Construct the page URI using Router properties
			//$page = Router::$current_uri.Router::$url_suffix.Router::$query_string;
		}

		Exception::__construct(TSNG::lang('core.page_not_found', $page));

		$this->template = $template;
	}

	/**
	 * Sends "File Not Found" headers, to emulate server behavior.
	 *
	 * @return void
	 */
	public function sendHeaders()
	{
		// Send the 404 header
		header('HTTP/1.1 404 File Not Found');
	}

} // End Kohana 404 Exception
