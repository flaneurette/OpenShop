<?php

class Parser {
	
	CONST TEMPLATE_START 	= '{{';
	CONST TEMPLATE_END 		= '}}';
	
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
	
	/**
	* Parses html templates.
	* @return string html code.
	*/
	public function template($template,$parameters) {	
		$html = '';
		if(file_exists($template)) {
			$html = file_get_contents($template);
			if(is_array($parameters) && is_string($html)) {
				foreach ($parameters as $key => $value) {
					$html = str_ireplace(self::TEMPLATE_START.$key.self::TEMPLATE_END, $value, $html);
				}
			}
		} else { 
			return false; 	
		}
		
		return $html;
	}

}

?>