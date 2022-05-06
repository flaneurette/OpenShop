<?php

class PriceBar {
	
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
	
	public function getpricebar($pricebarvalues) {
		
		$bars = "";
		if(stristr($pricebarvalues,',')) {
			$barvalues = explode(',',$pricebarvalues);
			for($i=1;$i<=6;$i++) { 
				$bars .= '<li id="pb-'.$i.'"><a href="bargain/'.str_replace('-',':',$barvalues[$i-1]).'">'.$barvalues[$i-1].'</a></li>';
			} 
		} 
		return $bars;
	}
}

?>