<?php

class Formatter {
	
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
	
	public function store($string,$method) {
		
		$returnstring = '';
		
		switch($method) {
			
			case 'product-description':

			$returnstring = $this->sanitize($string,'encode');
			$returnstring = substr($returnstring,0,512);
			
			$find = ['\n','\r','\t'];
			$replace = ['<br />','<br />','&emsp;'];
			$returnstring = str_ireplace($find,$replace,htmlspecialchars($returnstring, ENT_QUOTES, self::PHPENCODING));
			return nl2br($returnstring);
		
			break;
			
		}
		
		return $returnstring;
	}
}

?>