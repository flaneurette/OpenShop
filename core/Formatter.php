<?php

include_once("Sanitize.php");

class Formatter {
	
	public function __construct($params = array()) 
	{ 
		$this->sanitizer = new Sanitizer;
	}
	
	/**
	* Initializes object.
	* @param array $params
	* @throws Exception
	*/	
	
	public function format($string,$method) {
		
		$returnstring = '';
		
		switch($method) {
			
			case 'product-description':

			$returnstring = $this->sanitizer->sanitize($string,'encode');
			$returnstring = substr($returnstring,0,512);
			
			$find = ['\n','\r','\t'];
			$replace = ['<br />','<br />','&emsp;'];
			$returnstring = str_ireplace($find,$replace,htmlspecialchars($returnstring, ENT_QUOTES, 'UTF-8'));
			return nl2br($returnstring);
		
			break;
			
		}
		
		return $returnstring;
	}
}

?>