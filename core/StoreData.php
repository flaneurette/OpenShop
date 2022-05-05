<?php

require_once("Backup.php");

class StoreData {
	
	
	CONST FILE_ENC	= "UTF-8";
	CONST FILE_OS	= "WINDOWS-1252"; // only for JSON and CSV, not the server architecture.
	
	public function __construct($params = array()) 
	{ 
		$this->init($params);
		$this->backups = new Backup;
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
	* Store data used in admin and install
	* @return boolean, true for success, false for failure.
	*/
	public function storedata($url,$data,$method='json',$backup=false) 
	{
		// TODO: check $url and contents.
		if($method == 'json') {
			
			$json = mb_convert_encoding($this->encode($data), self::FILE_ENC, self::FILE_OS);
			
			if(!is_writable($url)) {
				chmod($url,0777);
			}
			
			if($backup != false) {
				$this->backups->backup($url,$backup);
			}

			file_put_contents($url,$json, LOCK_EX);
			} else {
			file_put_contents($url,$data, LOCK_EX);					
		}
		
		chmod($url,0755);
		
	return true;
	}
}

?>