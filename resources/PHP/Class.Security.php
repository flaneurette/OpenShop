<?php

class Security {

	/**
	* Security class
	*/

	CONST MAXINT  			= 9999999999;	
	CONST PHPENCODING 		= 'UTF-8';		// Characterset of PHP functions: (htmlspecialchars, htmlentities) 
	CONST MINHASHBYTES		= 32; 			// Min. of bytes for secure hash.
	CONST MAXHASHBYTES		= 64; 			// Max. of bytes for secure hash, more increases cost. Max. recommended: 256 bytes.
	CONST MINMERSENNE		= 0xff; 		// Min. value of the Mersenne twister.
	CONST MAXMERSENNE		= 0xffffffff; 	// Max. value of the Mersenne twister.

	public function __construct() {
		$securing = true;
	}

	/**
	* Sanitizes user-input
	* @param string
	* @return string
	*/
	public function cleaninput($string) 
	{
		if(is_array($string)) {
			return @array_map("htmlspecialchars", $string, array(ENT_QUOTES, self::FILE_ENC));
			} else {
			return htmlspecialchars($string, ENT_QUOTES, self::FILE_ENC);
		}
	}
	
	/**
	* Sanitizes user-input
	* @param string
	* @return string
	*/
	
	public function sanitize($string,$method='') 
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
			$this->data = preg_replace('/[^a-zA-Z-0-9\-_\/]/m','', $string);
			break;
				
			case 'cat':
			$this->data = preg_replace('/[^a-zA-Z-0-9\-_\/]/m','', $string);
			break;
			
			case 'alphanum':
				$this->data =  preg_replace('/[^a-zA-Z-0-9]/m','', $string);
			break;
			
			case 'field':
				$this->data =  preg_replace('/[^a-zA-Z-0-9-_.@/]/','', $string);
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
			
			case 'entities':
				$this->data =  htmlentities($string, ENT_QUOTES | ENT_HTML5, self::PHPENCODING);
			break;
			
			case 'url':
				$search  = ['`','"',',','\'',';','$','%','>','<','\/'];
				$replace = ['','','','','','','','','','/'];
				$this->data = stripslashes(str_replace($search,$replace,$string));
			break;
			
			case 'domain':
				$this->data =  str_ireplace(array('http://','www.'),array('',''),$string);
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

	}
	
	/**
	* Allocates a pseudo random token to prevent CSRF.
	* @return mixed boolean, void.
	*/
	public function getToken()
	{
		
		$bytes = 0;
		
		$_SESSION['token'] = '';
		
		if (function_exists('random_bytes')) {
			$len   = mt_rand(self::MINHASHBYTES,self::MAXHASHBYTES);
        		$bytes .= bin2hex(random_bytes($len));
    		}
		if (function_exists('openssl_random_pseudo_bytes')) {
			$len   = mt_rand(self::MINHASHBYTES,self::MAXHASHBYTES);
        		$bytes .= bin2hex(openssl_random_pseudo_bytes($len));
    		}
		
		if(strlen($bytes) < 128) {
			$bytes .= mt_rand(self::MINMERSENNE,self::MAXMERSENNE) . mt_rand(self::MINMERSENNE,self::MAXMERSENNE) . mt_rand(self::MINMERSENNE,self::MAXMERSENNE)
				. mt_rand(self::MINMERSENNE,self::MAXMERSENNE) . mt_rand(self::MINMERSENNE,self::MAXMERSENNE) . mt_rand(self::MINMERSENNE,self::MAXMERSENNE) 
				. mt_rand(self::MINMERSENNE,self::MAXMERSENNE) . mt_rand(self::MINMERSENNE,self::MAXMERSENNE) . mt_rand(self::MINMERSENNE,self::MAXMERSENNE) 
				. mt_rand(self::MINMERSENNE,self::MAXMERSENNE) . mt_rand(self::MINMERSENNE,self::MAXMERSENNE) . mt_rand(self::MINMERSENNE,self::MAXMERSENNE); 
		}
		
		$token = hash('sha512',$bytes);
		
		if(isset($_SESSION['token']) && $_SESSION['token'] != false) 
		{ 
			if(strlen($_SESSION['token']) < 128) {
				// $this->sessionmessage('Issue found: session token is too short.'); 
				} else {
				return $this->sanitize($_SESSION['token'],'alphanum'); 
			}
		} else { 
		return $token;
		} 
	} 
	
 	/**
	* Destroys the previously set token.
	* @return mixed string.
	*/
	public function destroyToken()
	{
		try {
			if(isset($_SESSION['token'])) {
				$_SESSION['token'] = '';
			}
		} catch(Exception $e) {
			// $this->sessionmessage('Issue: session could not be destroyed, '.$e->getMessage());
			return false;
		}
		return true;
	}
	
	
}

?>
