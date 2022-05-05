<?php

require_once("Backup.php");
require_once("Message.php");
require_once("Sanitize.php");

class JSONLoader {
	
	CONST INVENTORY_PATH 		= "";
	CONST SITECONF				= "server/config/site.conf.json";
	CONST INVENTORY				= "inventory/shop.json";
	CONST DEPTH					= 10024;
		
	public function __construct($params = array()) 
	{ 
		$this->init($params);
		$this->messages = new Message;
		$this->sanitizer = new Sanitizer;
	}
	
	/**
	* Initializes object.
	* @param array $params
	* @throws Exception
	*/	
	
    public function init($params)
    {
		try {
			isset($params['var'])  ? $this->var  = $params['var'] : false; 
			} catch(Exception $e) {}
    }

	public function load_json($url) 
	{
		if(!$url) {
			$url = self::INVENTORY_PATH . self::SITECONF;
		}
		
		$url = str_ireplace('.json','',$url);
		$url .= '.json';
		
		$file  = $this->traverse($url,'traverse');

		$json = json_decode($file, true, self::DEPTH, JSON_BIGINT_AS_STRING);
		
		if($json !== NULL || $json != false) {
			return $json;
			} else {
			exit;
		} 
	}
	
	/**
	* Encodes JSON object
	* @param shop
	* @return void
	*/
	
	public function encode($json) 
	{
		if($json === false || $json === NULL) {
			$this->message("Error: Could not load JSON. JSON data is either false or NULL.");
			exit;
			} else {
			return json_encode($json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);	
		}
	}
	
	/**
	* Loads and decodes JSON object
	* @return mixed object/array
	*/
	
	public function decode($url=false) 
	{
		$url = self::INVENTORY; 
		
		$url  = $this->sanitizer->sanitize($url,'json');
		$url .= '.json';
		
		$file  = $this->traverse($url);
		
		if(strlen($file) < 1) { 
			$this->messages->message("Error: JSON file could not be loaded, reason: file is empty.");
			exit;	
		}
		
		$json = json_decode($file, true, self::DEPTH, JSON_BIGINT_AS_STRING);
		
		if($json !== NULL || $json !== false) {
			return $json;
			} else {
				$this->messages->message("Error: JSON file could not be loaded.");
			exit;
		} 
		
	}
	
	public function traverse($string) {
		
		// prepare string by removing all illegal characters.
		$find = ['../','./','%','#','&'];
		$replace = ['','','','',''];
		$string = str_ireplace($find,'',$string);
		
		// test string before processing
		if(stristr(rawurldecode($string),'..') != false) {
			$this->messages->message("Error: illegal characters found in filename.");
			exit;	
		}		
		// filetype must be either json or csv.	
		if(substr(strtolower($string),-5) == '.json' || substr(strtolower($string),-4) == '.csv') {
			} else {
			$this->messages->message("Error: this is not a supported file.");
			exit;	
		}		
		
		// only allow alphanumeric characters, a period and slash.
		$string  = preg_replace('/[^a-zA-Z-0-9.\/]/','', $string);
		// filetype must be either json or csv, after preg_replace.
		if((substr(strtolower($string),-5) == '.json') || (substr(strtolower($string),-4) == '.csv')) {
			$urlstring = $string;
			} else {
			$this->messages->message("Error: this is not a supported file.");
			exit;								
		}

		// a file path must start with either inventory/ or server/config/
		if(substr($urlstring,0,10) == 'inventory/') 
		{
			$url = $urlstring;
			} elseif(substr($urlstring,0,14) == 'server/config/') {
			$url = $urlstring;
			
			} else {
				$this->messages->message("Error: JSON file could not be loaded due to possible directory traversal.");
				exit;			    
		}	
		
		// try to locate the file, rewind if needed.
		if(file_exists($url)) {
			$file = file_get_contents($url);
				} elseif(file_exists('../'.$url)) {
				$file = file_get_contents('../'.$url);
				} elseif(file_exists('../../'.$url)) {
				$file = file_get_contents('../../'.$url);
				} else { 
			$file = false;
		}
				
		return $file;		
	}
}

?>