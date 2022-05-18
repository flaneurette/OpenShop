<?php

include_once("Sanitize.php");
include_once("JSON.Loader.php");

class Meta {
	
	CONST INVENTORY_PATH 	= "";
	CONST SITECONF		= "server/config/site.conf.json";
	CONST INVENTORY		= "inventory/shop.json";
	
	public function __construct($params = array()) 
	{ 
		$this->init($params);
		$this->sanitizer = new Sanitizer;
		$this->json	 = new JSONLoader;
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
	* Product Meta generation
	* @return $string, html.
	*/	
	public function getaproductmeta($productid) 
	{
		$html = '';
		$json = $this->json->load_json(self::INVENTORY);
		if($json !== null) {
			$c = count($json);
			if($c >=1) {
				for($i=0;$i<$c;$i++) {
					if($json[$i]['product.id'] == $productid) {
						$html .= '<title>'.$this->sanitizer->cleaninput($json[$i]['product.title']).'</title>';
						$html .= '<meta name="description" content="'.$this->sanitizer->cleaninput($json[$i]['product.description']).'" />';
						$html .= '<meta name="keyword" content="'.$this->sanitizer->cleaninput($json[$i]['product.tags']).'" />';
						break;	
					}
				}	
			}			
		}
	return $html;
	}
	
	/**
	* Meta generation
	* @return $string, html.
	*/	
	public function getmeta($json=self::INVENTORY_PATH . self::SITECONF,$product=false) {
		
		$html = '';
		
		$site = $this->json->load_json($json);

		foreach($site as $row)
		{
			if($row['site.status'] == 'offline') {
				header('Location: /offline/', true, 302);
				exit;
			}
			
			if($row['site.status'] == 'closed') {
				header('Location: /closed/', true, 301);
				exit;			
			}
			
			if(isset($row['site.cdn'])) {
				$cdn = $this->sanitizer->cleaninput($row['site.cdn']);
			}
			
			if($product !=false) {
				
				if($product >=1) {
						$html .= $this->getaproductmeta($product);
						$html .= '<meta charset="'.$this->sanitizer->cleaninput($row['site.charset']).'">';
						$html .= '<meta name="author" content="OpenShop">';
				}
				
			} else {
  
				$html .= '<title>'.$this->sanitizer->cleaninput($row['site.title']).'</title>';
				$html .= '<meta charset="'.$this->sanitizer->cleaninput($row['site.charset']).'">';
				// $html .= '<meta name="viewport" content="'.$this->sanitizer->cleaninput($row['site.viewport']).'">';
				$html .= '<meta name="description" content="'.$this->sanitizer->cleaninput($row['site.description']).'">';
				$html .= '<meta name="author" content="OpenShop">';
				
				if(!empty($row['site.updated'])) {
					$html .= '<meta http-equiv="last-modified" content="'.$this->sanitizer->cleaninput($row['site.updated']).'">';
				}			

				if(!empty($row['site.meta.name.1'])) {
					$html .= '<meta name="'.$this->sanitizer->cleaninput($row['site.meta.name.1']).'" content="'.$this->sanitizer->cleaninput($row['site.meta.value.1']).'">';
				}

				if(!empty($row['site.meta.name.2'])) {
					$html .= '<meta name="'.$this->sanitizer->cleaninput($row['site.meta.name.2']).'" content="'.$this->sanitizer->cleaninput($row['site.meta.value.2']).'">';
				}

				if(!empty($row['site.meta.name.3'])) {
					$html .= '<meta name="'.$this->sanitizer->cleaninput($row['site.meta.name.3']).'" content="'.$this->sanitizer->cleaninput($row['site.meta.value.3']).'">';
				}

				if(!empty($row['site.meta.name.4'])) {
					$html .= '<meta name="'.$this->sanitizer->cleaninput($row['site.meta.name.4']).'" content="'.$this->sanitizer->cleaninput($row['site.meta.value.4']).'">';
				}

				if(!empty($row['site.google.tags'])) {
					$html .= '<meta name="google-site-verification" content="'.$this->sanitizer->cleaninput($row['site.google.tags']).'">';
				}
			}

			$html .= '<link rel="stylesheet" type="text/css" href="'.$this->sanitizer->cleaninput($row['site.domain']).'/'.$this->sanitizer->cleaninput($row['site.canonical']).'/'.$this->sanitizer->cleaninput($row['site.stylesheet.reset']).'">';
			$html .= '<link rel="stylesheet" type="text/css" href="'.$this->sanitizer->cleaninput($row['site.domain']).'/'.$this->sanitizer->cleaninput($row['site.canonical']).'/'.$this->sanitizer->cleaninput($row['site.stylesheet1']).'">';
			$html .= '<link rel="stylesheet" type="text/css" href="'.$this->sanitizer->cleaninput($row['site.domain']).'/'.$this->sanitizer->cleaninput($row['site.canonical']).'/'.$this->sanitizer->cleaninput($row['site.stylesheet2']).'">';
			
			if(!empty($row['site.stylesheet3'])) {
				$html .= '<link rel="stylesheet" type="text/css" href="'.$this->sanitizer->cleaninput($row['site.domain']).'/'.$this->sanitizer->cleaninput($row['site.canonical']).'/'.$this->sanitizer->cleaninput($row['site.stylesheet3']).'">';
			}
			
			if(!empty($row['site.ext.stylesheet'])) {
				$html .= '<link rel="stylesheet" type="text/css" href="'.$this->sanitizer->cleaninput($row['site.ext.stylesheet']).'">';
			}		
			
			$html .= '<link rel="icon" type="image/ico" href="'.$this->sanitizer->cleaninput($row['site.domain']).'/'.$this->sanitizer->cleaninput($row['site.canonical']).'/'.$this->sanitizer->cleaninput($row['site.icon']).'">';
			$html .= '<script src="'.$this->sanitizer->cleaninput($row['site.domain']).'/'.$this->sanitizer->cleaninput($row['site.canonical']).'/'.$this->sanitizer->cleaninput($row['site.javascript']).'" type="text/javascript"></script>';
			
			if(!empty($row['site.ext.javascript'])) {
				$html .= '<script src="'.$this->sanitizer->cleaninput($row['site.ext.javascript']).'" type="text/javascript"></script>';
			}					
			if(!empty($row['site.logo'])) {
				$html .= '<img src="'.$this->sanitizer->cleaninput($row['site.domain']).'/'.$this->sanitizer->cleaninput($row['site.canonical']).'/'.$this->sanitizer->cleaninput($row['site.logo']).'" id="ts.shop.logo">';
			}
		}
		
		return $html;
	}

}

?>
