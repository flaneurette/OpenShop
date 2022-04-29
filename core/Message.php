<?php

class Message {
	
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
	
	public function message($value) 
	{
		if(isset($this->messages)) { 
			if(count($this->messages) > 10) {
				$this->messages = array(); 
			}
			array_push($this->messages,$value);  
			} else { 
			$this->messages = array(); 
		} 	
	}

	public function showmessage() 
	{ 
		if(isset($this->messages)) { 
			echo "<pre>"; 
			echo "<strong>Message:</strong>\r\n"; 
			foreach($this->messages as $message) { 
				echo $message . "\r\n" ; 
			} echo "</pre>"; 
		} 
		$this->messages = array();
	} 
}

?>