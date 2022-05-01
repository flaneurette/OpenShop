<?php

class Backup {
	
	CONST BACKUPS	= "inventory/backups/";
	CONST BACKUPEXT	= ".bak"; 
	
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
	
	public function backup($url,$dir=false) 
	{	
		if($dir != false) {
			$find 	 = ['../inventory/','../inventory/csv/','../','../../'];
			$replace = ['','','',''];
			$copy 	 = $dir.str_ireplace($find,$replace,$url).self::BACKUPEXT;
			} else {
			$copy 	= $url.self::BACKUPEXT;
		}
		// TODO: find out scope, for better security.
		@copy($url, $copy);
	}
}

?>