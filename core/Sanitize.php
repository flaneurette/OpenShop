<?php

class Sanitizer {
	
	
	CONST PHPENCODING 	= 'UTF-8'; // Characterset of PHP functions: (htmlspecialchars, htmlentities)
	CONST MAXINT  		= 9999999999;
	
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

	public function sanitize($string,$method='',$len=false) 
	{
		
		$data = '';
		
		switch($method) {
			
			case 'alpha':
				$this->data =  preg_replace('/[^a-zA-Z]/','', $string);
			break;
			
			case 'trim':
				
				if(isset($string)) {
					
					if(trim($string) != "") {
						$this->data = $string;
						} elseif(strlen($string) > 2) {
						$this->data = $string;
						} else {
						$this->data = false;
					}
					
				} else {
					$this->data = false;
				}
				
			break;		
			
			case 'num':
			
			if($string > self::MAXINT) {
				return false;
				} else {
				$this->data =  preg_replace('/[^0-9]/m','', $string);
			}
				
			break;
			
			case 'dir':
				$this->data =  preg_replace('/[^a-zA-Z-0-9\.\/]/m','', $string);
			break;			

			case 'email':
			$this->data = preg_replace('/[^a-zA-Z-0-9\-\_.@\/]/m','', $string);
			break;

			case 'search':
			$this->data = preg_replace('/[^a-zA-Z-0-9\-\s\/]/m','', $string);
			break;
			
			case 'cat':
			$this->data = preg_replace('/[^a-zA-Z-0-9\-_\/]/m','', $string);
			break;
			
			case 'alphanum':
				$this->data =  preg_replace('/[^a-zA-Z-0-9]/m','', $string);
			break;
			
			case 'field':
				$this->data =  preg_replace('/[^a-zA-Z-0-9\-\_.@\/]/','', $string);
			break;

			case 'option':
				$string =  preg_replace('/[^a-zA-Z-0-9\-\_.]/','', $string);
				$this->data = htmlspecialchars($string,ENT_QUOTES,self::PHPENCODING);
			break;
			
			case 'query':
				$search  = ['`','"','\'',';'];
				$replace = ['','','',''];
				$this->data = str_replace($search,$replace,$string);
			break;
			
			case 'cols':
				// comma is allowed for selecting multiple columns.
				$search  = ['`','"','\'',';'];
				$replace = ['','','',''];
				$this->data = str_replace($search,$replace,$string);
			break;
			
			case 'table':
				$search  = ['`','"',',','\'',';','$','%','>','<'];
				$replace = ['','','','','','','','',''];
				$this->data = str_replace($search,$replace,$string);
			break;
			
			case 'unicode':
				$this->data =  preg_replace("/[^[:alnum:][:space:]]/u", '', $string);
			break;
			
			case 'encode':
				$this->data =  htmlspecialchars($string,ENT_QUOTES,self::PHPENCODING);
			break;
			
			case 'log':
			
				if($len == false) {
					$len = 255;
				}
	
				if(strlen($string) > $len) {
					$this->data = false;
					} else {
					$this->data =  htmlspecialchars($string,ENT_QUOTES,self::PHPENCODING);
				}
				
			break;			
			
			case 'entities':
				$this->data =  htmlentities($string, ENT_QUOTES | ENT_HTML5, self::PHPENCODING);
			break;
			
			case 'url':
				$search  = ['`','"',',','\'',';','$','%','>','<','\/'];
				$replace = ['','','','','','','','','','/'];
				$this->data = stripslashes(str_replace($search,$replace,$string));
			break;
			
			case 'domain':
				$search = ['http://','www.'];
				$replace = ['',''];
				$this->data =  str_ireplace($search,$replace,$string);
			break;
			
			case 'image':
				$search  = ['..','`','"',',','\'',';','%','>','<',];
				$replace = ['','','','','','','','',''];
				$this->data = stripslashes(str_ireplace($search,$replace,$string));
			break;

			case 'json':
				$find = ['.json','./','../','\\','..','?','<','>'];
				$replace = ['','','','','','','',''];
				$this->data = str_ireplace($find,$replace,$string);
			break;
			
			default:
			return $this->data;
			
			}
		return $this->data;
	}
	
}

?>
