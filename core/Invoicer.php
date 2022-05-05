<?php

include_once("Backup.php");
include_once("JSON.Loader.php");

class Invoicer {
	
	
	CONST FILE_ENC	= "UTF-8";
	CONST FILE_OS	= "WINDOWS-1252"; // only for JSON and CSV, not the server architecture.
	
	public function __construct($params = array()) 
	{ 
		$this->init($params);
		$this->backups = new Backup;
		$this->json    = new JSONLoader;
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

	public function invoiceid($dir,$method,$value=false) 
	{

		if(!isset($method)) {
			return false;
		}

		$shopconf = $this->json->load_json($dir);
		
		if($shopconf == null || $shopconf == '') {
			return false;
		}
		
		$configuration = [];
		
		if($shopconf !== null) {
			foreach($shopconf as $conf) {	
				array_push($configuration,$conf);
			}
		}
		
		if($method == 'get') {
			$invoiceid = $configuration[0]['orders.conf.invoice.id'];
			return $invoiceid;
		} 
		
		if($method == 'set') {
			
			if(isset($value)) {
				$shopconf[0]['orders.conf.invoice.id'] = (int)$value;
				$this->backup($dir);
				$this->storedata($dir,$shopconf);
				return true;
				} else {
				return false;
			}
		} 
	}
}
?>