<?php

require_once("Sanitize.php");

class Logging {
	
	CONST LOGGINGDIR  = "server/logging/";
	CONST LOGFILE	  = "log.log";
	CONST MAXLOGSIZE  = 3000000; // 3Mb
	
	public function __construct($params = array()) 
	{ 
		$this->init($params);
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
	
	public function logging($dir)  {
		
		$storing  = 1;
		$logfile  = self::LOGGINGDIR;
		$logfile .= $this->sanitizer->sanitize($dir,'alphanum') . '/'. self::LOGFILE;		
		
		$remoteaddr	 	= $this->sanitizer->sanitize($_SERVER['REMOTE_ADDR'],'log',50);
		$useragent 		= $this->sanitizer->sanitize($_SERVER['HTTP_USER_AGENT'],'log',250);
		$scriptname 	= $this->sanitizer->sanitize($_SERVER['SCRIPT_NAME'],'log',255);
		$querystring 	= $this->sanitizer->sanitize($_SERVER['QUERY_STRING'],'log',500);
		
		if(isset($this->referer)) {
			$referer  = $this->sanitizer->sanitize($this->referer,'log',500);	
			} else {
			$referer  = '';
		}
		
		if($remoteaddr == false) {
			$storing += 1;
		}
		if($useragent == false) {
			$storing += 1;
		}		
		if($scriptname == false) {
			$storing += 1;
		}
		if($querystring == false) {
			// $storing += 1;
		}
		if($referer == false) {
			// $storing += 1;
		}	

		if($storing == 1) {
			if(file_exists($logfile)) {
				if(filesize($logfile) > self::MAXLOGSIZE) {
					// empty log
					@file_put_contents($logfile, "");
					} else {
						if(isset($this->referer)) {
							$refer = $referer;
							} else {
							$refer = 'no-referer';
						}
					$log = date("F j, Y, g:i a") . ' - '. $remoteaddr.' - '.$useragent.' - '. $refer.' - '.$scriptname. ' - '.$querystring. PHP_EOL;
					@file_put_contents($logfile, $this->sanitizer->sanitize($log,'log'), FILE_APPEND);
				}
			}
		}
	}
	
}

?>
