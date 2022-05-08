<?php

require_once("Sanitize.php");

class Sorter {
	
	public function __construct($params = array()) 
	{ 
		$this->init($params);
		$this->sanitizer = new Sanitizer;
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
	
	public function sorting($uri,$method,$optionnames,$optionvalues) {
		
		
		$optionnames  = explode(',',$optionnames);
		$optionvalues = explode(',',$optionvalues);
		
		switch($method) {
			
			case 'price':
			
				if(count($optionvalues) >1) {
					
					$optionbar = "<select name=\"sort\" id=\"sort\" onChange=\"OpenShop.redirect('".$uri."'+this.value);\">";
					
					for($i=1;$i<=count($optionvalues);$i++) { 
						$optionbar .= '<option value="'.$this->sanitizer->sanitize($optionvalues[$i-1],'search').'">'.ucfirst($this->sanitizer->sanitize($optionnames[$i-1],'search')).'</option>';
					} 
					$optionbar .= "</select>";
				} 
			break;
			
			case '':
			break;
			
			default:
			return false;
			break;
			
		}
		
		if(isset($optionbar)) { 
			return $optionbar;
			} else {
			return false;
		}
	}
}

?>