<?php

class Logging {
	
	CONST LOGGINGDIR  = "server/logging/";
	
	public function __construct($params = array()) 
	{ 
		$this->init($params);
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
	
	public function log($dir)  {
		
		$storing  = 1;
		$logfile  = self::LOGGINGDIR;
		$logfile .= $this->sanitize($dir,'alphanum') . '/log.log';		
		
		$remoteaddr	 = $this->sanitize($this->remoteaddr,'log',50);
		$useragent 	= $this->sanitize($this->useragent,'log',250);
		$scriptname 	= $this->sanitize($this->scriptname,'log',255);
		$querystring 	= $this->sanitize($this->querystring,'log',500);
		
		if(isset($this->referer)) {
			$referer  = $this->sanitize($this->referer,'log',500);	
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
			//$storing += 1;
		}
		if($referer == false) {
			//$storing += 1;
		}	

		if($storing == 1) {
			if(file_exists($logfile)) {
				if(filesize($logfile) > 3000000) {
					//empty log
					@file_put_contents($logfile, "");
					} else {
						if(isset($this->referer)) {
							$refer = $referer;
							} else {
							$refer = 'no-referer';
						}
					$log = date("F j, Y, g:i a") . ' - '. $remoteaddr.' - '.$useragent.' - '. $refer.' - '.$scriptname. ' - '.$querystring. PHP_EOL;
					@file_put_contents($logfile, $this->sanitize($log,'log'), FILE_APPEND);
				}
			}
		}
	}
	
}

?>
