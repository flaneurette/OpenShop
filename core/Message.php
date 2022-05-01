<?php

include_once("Debug.php");

class Message {
	
	public function __construct($params = array()) 
	{ 
		$this->init($params);
		$this->debug = new Debug;
		isset($_SESSION['messages']) ? $this->messages = $_SESSION['messages'] : array();
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

	public function showmessage($messages) 
	{ 
		if(isset($messages)) { 
			echo "<pre>"; 
			echo "<strong>Message:</strong>\r\n"; 
			foreach($messages as $message) { 
				echo $message . "\r\n" ; 
			} echo "</pre>"; 
		} 
		$messages = array();
	} 

}

?>