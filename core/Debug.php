<?php

class Debug {
	
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
	
	public function debug($rawdata) 
	{
		$string  = "<pre>";
		$string .= print_r($rawdata);
		$string .= "</pre>";
		return $string;
	}
}

?>