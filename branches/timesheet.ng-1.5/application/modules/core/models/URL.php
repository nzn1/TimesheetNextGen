<?php

class Core_URL extends TSNG_Base {
	const default_method = 'index';

	public $controller;
	public $method;
	public $args;
	
	public $raw_url;	
	public $parts;
	
	public function __construct($raw_url = false) {
		if(!$raw_url) {
			$this->raw_url = $_SERVER['REQUEST_URI'];
		}
		else { $this->raw_url = $raw_url; }	
	}
	
	public function translate_url($raw_url = false) {
		if($raw_url) { $this->raw_url = $raw_url; }
		
		// remove any start and end slashes
		$url = substr($this->raw_url, 1); 
		if(strrpos($url, '/') === (strlen($url) - 1)) {
			$url = substr($url, 0, -1);	
		}

		// if there is still a url, get the parts
		if($url != '') {	
			// split url 
			$this->parts = split('/', $url);
		
			// the first url part is the controller, so convert too the controller
			// style naming convention
			if($this->parts[0]) {
				$this->parts[0] = str_replace(' ', '', ucwords(str_replace('-', ' ', $this->parts[0])));
				// names the controller based on the first part of the url
				$this->controller = 'Controller_'.$this->parts[0];
			}
			else {
				$this->controller = 'Controller_'.TSNG::config('core.default_controller');
			}

			// if there is a method, run that
			if(isset($this->parts[1]) && $this->parts[1] != '') {
				$this->method = $this->parts[1];
			}
			// else default to the index method
			else {
				$this->method = self::default_method;
				// no method, don't bother trying to get args
				return;
			}
			
			// the remaining urls parts should be the arguments
			if(count($this->parts) > 2) {
				$this->args = array_slice($this->parts, 2);
			}
		}
		else {
			$this->controller = 'Controller_'.TSNG::config('core.default_controller');
			$this->method = self::default_method;
		}
	}
	

}