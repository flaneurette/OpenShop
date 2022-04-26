<?php

class Shop {

	CONST SHOPVERSION 			= "?cache-control=3.1"; // increment if major changes are made to the shop database.
	CONST INVENTORY_PATH 			= "";
	CONST SITECONF				= "server/config/site.conf.json";
	CONST CURRENCIES			= "server/config/currencies.conf.json";
	CONST SHOPCONF				= "server/config/shop.conf.json";
	CONST SHIPPING 				= "server/config/shipping.conf.json";
	CONST INVENTORY				= "inventory/shop.json";
	CONST CATEGORIES			= "inventory/categories.json";
	CONST SUBCATEGORIES			= "inventory/subcategories.json";
	CONST NAVIGATION 			= "inventory/navigation.json";
	CONST BLOG				= "inventory/blog.json";
	CONST ARTICLES				= "inventory/articles.json";
	CONST PAGES				= "inventory/pages.json";
	CONST CSV				= "inventory/csv/";
	CONST SERVERCSV				= "server/config/csv/";
	CONST LOGGINGDIR 			= "server/logging/";
	CONST BACKUPS				= "inventory/backups/";
	CONST BACKUPEXT				= ".bak"; 
	CONST FILE_ENC				= "UTF-8";
	CONST FILE_OS				= "WINDOWS-1252"; // only for JSON and CSV, not the server architecture.
	CONST MAXINT  				= 9999999999;
	CONST DEPTH				= 10024;
	CONST MAXTITLE				= 255; // Max length of title.
	CONST MAXDESCRIPTION			= 500; // Max length of description.

	CONST PHPENCODING 			= 'UTF-8';		// Characterset of PHP functions: (htmlspecialchars, htmlentities) 
	CONST MINHASHBYTES			= 32; 			// Min. of bytes for secure hash.
	CONST MAXHASHBYTES			= 64; 			// Max. of bytes for secure hash, more increases cost. Max. recommended: 256 bytes.
	CONST MINMERSENNE			= 0xff; 		// Min. value of the Mersenne twister.
	CONST MAXMERSENNE			= 0xffffffff; 	// Max. value of the Mersenne twister.
	
	CONST GATEWAYS 	= ["ACH","Alipay","Apple Pay","Bancontact","BenefitPay","Boleto Bancario","Citrus Pay","EPS","Fawry","Giropay","Google Pay","PayPal","KNET","Klarna","Mada","Multibanco","OXXO","Pago FÃƒÂ¡cil","Poli","Przelewy24","QPAY","Rapipago","SEPA Direct Debit","Sofort","Stripe","Via Baloto","iDEAL"];
	
	public function __construct() {
		
		$incomplete = false;
		$this->maxcats = 0;
		
		$host = $this->getbase();
		
		isset($_SERVER['REMOTE_ADDR']) 		?  	$this->remoteaddr	= $_SERVER['REMOTE_ADDR'] : false;
		isset($_SERVER['HTTP_USER_AGENT']) 	? 	$this->useragent 	= $_SERVER['HTTP_USER_AGENT'] : false;
		isset($_SERVER['SCRIPT_NAME'])	 	? 	$this->scriptname 	= $_SERVER['SCRIPT_NAME'] : false;
		isset($_SERVER['QUERY_STRING']) 	?	$this->querystring 	= $_SERVER['QUERY_STRING'] : false;
		isset($_SERVER['HTTP_REFERER'])		?  	$this->referer 		= $_SERVER['HTTP_REFERER'] : false;
		isset($_SERVER["SCRIPT_URL"])		?   	$this->scripturl 	= $_SERVER['SCRIPT_URL'] : false;
		isset($_SERVER["REQUEST_URI"])		?   	$this->requesturi 	= $_SERVER['REQUEST_URI'] : false;
		isset($_SESSION['token'])		?   	$this->token 		= $_SESSION['token'] : false;
		isset($_SESSION['messages'])		?   	$this->messages 	= $_SESSION['messages'] : array();
		
		isset($_POST['optionbar']) 		? 	$this->refinekey 	= $_POST['optionbar'] : false;
		isset($_POST['search']) 		? 	$this->searchkey 	= $_POST['search'] : false;
		isset($_GET['maxprice']) 		? 	$this->maxprice 	= $_GET['maxprice'] : false;
		isset($_GET['minprice']) 		? 	$this->minprice 	= $_GET['minprice'] : false;
		isset($_GET['page']) 			? 	$this->page 		= $_GET['page'] : false;
		isset($_GET['page_id']) 		? 	$this->pageid 		= $_GET['pageid'] : false;
		isset($_GET['cat']) 			? 	$this->cat 		= $_GET['cat'] : false;
		isset($_GET['subcat']) 			? 	$this->subcat 		= $_GET['subcat'] : false;
		
		$this->serverconfig_csv = [
			'currencies.conf.csv',
			'messages.conf.csv',
			'orders.conf.csv',
			'shipping.conf.csv',
			'shop.conf.csv',
			'site.conf.csv',
			'tax.conf.csv',
			'payment.conf.csv',
			'paypal.csv'];
		
		$this->serverconfig_json = [
			'currencies.conf.json',
			'messages.conf.json',
			'orders.conf.json',
			'shipping.conf.json',
			'shop.conf.json',
			'site.conf.json',
			'tax.conf.json',
			'payment.conf.json',
			'paypal.json'];
	}
	
	// Password to encrypt JSON
	private static function PWD() {
		return "thepasswordisnow";
	}

	public function host() {
		return $this->getbase();
	}
	
	public function getbase($path=false,$nav=false) 
	{	
	
		$host 		= $this->gethost(self::INVENTORY_PATH . self::SITECONF,true);
		$siteconf 	= $this->load_json(self::INVENTORY_PATH . self::SITECONF);
		$url  		= $this->getasetting($siteconf,'site.url');
		$canonical  = $this->getasetting($siteconf,'site.canonical');

		if($nav == true) {
			return $url['site.url'];
		}
		
		if($path == true) {
			return $canonical['site.canonical'];
		}
		
		$find 	 = ['http://','https://','http//','https//','www.','www','/'];
		$replace = ['','','','','','',''];

		// build paths dynamically
		$home  = 'https://';
		$home .= str_replace($find,$replace,$url['site.url']);
		$home .= '/';
		$home .= $canonical['site.canonical'];
		$home .= '/';
		
		return $home;
	}

	/**
	* Encodes JSON object
	* @param shop
	* @return void
	*/
	
	public function encode($json) 
	{
		if($json === false || $json === NULL) {
			$this->message("Error: Could not load JSON. JSON data is either false or NULL.");
			exit;
			} else {
			return json_encode($json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);	
		}
	}
	
	/**
	* Loads and decodes JSON object
	* @return mixed object/array
	*/
	
	public function decode($url=false) 
	{
		$url = self::INVENTORY; 
		
		$url  = $this->sanitize($url,'json');
		$url .= '.json';
		
		$file  = $this->traverse($url);
		
		if(strlen($file) < 1) { 
			$this->message("Error: JSON file could not be loaded, reason: file is empty.");
			exit;	
		}
		
		$json = json_decode($file, true, self::DEPTH, JSON_BIGINT_AS_STRING);
		
		if($json !== NULL || $json !== false) {
			return $json;
			} else {
				$this->message("Error: JSON file could not be loaded.");
			exit;
		} 
		
	}

	public function load_json($url) 
	{
		if(!$url) {
			$url = self::INVENTORY_PATH . self::SITECONF;
		}
		
		$url = str_ireplace('.json','',$url);
		$url .= '.json';
		
		$file  = $this->traverse($url,'traverse');

		$json = json_decode($file, true, self::DEPTH, JSON_BIGINT_AS_STRING);
		
		if($json !== NULL || $json != false) {
			return $json;
			} else {
				$this->message("Error: JSON file could not be loaded.");
			exit;
		} 
	}

	/**
	* Store data used in admin and install
	* @return boolean, true for success, false for failure.
	*/
	public function storedata($url,$data,$method='json',$backup=false) 
	{
		// TODO: check $url and contents.
		if($method == 'json') {
			
			$json = mb_convert_encoding($this->encode($data), self::FILE_ENC, self::FILE_OS);
			
			if(!is_writable($url)) {
				chmod($url,0777);
			}
			
			if($backup != false) {
				$this->backup($url,$backup);
			}

			file_put_contents($url,$json, LOCK_EX);
			} else {
			file_put_contents($url,$data, LOCK_EX);					
		}
		
		chmod($url,0755);
		
	return true;
	}

	public function sortshop($products,$method) {

		$produce =  [];

			for($i=0;$i<count($products);$i++) { 
			if(isset($products[$i])) {
				if(isset($products[$i][$method])) { 
					if($products[$i][$method] !='') { 
						$produce[$products[$i][$method]] = $products[$i];
					}	
				} else {
					// backwards compatibility
					$produce[$i] = $products[$i];
				}
			}
			}

		asort($produce);
		return $produce;
	}

	/**
	* Product Meta generation
	* @return $string, html.
	*/	
	public function getaproductmeta($productid) 
	{
		$html = '';
		$json = $this->load_json(self::INVENTORY);
		if($json !== null) {
			$c = count($json);
			if($c >=1) {
				for($i=0;$i<$c;$i++) {
					if($json[$i]['product.id'] == $productid) {
						$html .= '<title>'.$this->cleaninput($json[$i]['product.title']).'</title>';
						$html .= '<meta name="description" content="'.$this->cleaninput($json[$i]['product.description']).'" />';
						$html .= '<meta name="keyword" content="'.$this->cleaninput($json[$i]['product.tags']).'" />';
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
		
		$site = $this->load_json($json);

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
				$cdn = $this->cleaninput($row['site.cdn']);
			}
			
			if($product !=false) {
				
				if($product >=1) {
						$html .= $this->getaproductmeta($product);
						$html .= '<meta charset="'.$this->cleaninput($row['site.charset']).'">';
						$html .= '<meta name="author" content="OpenShop">';
				}
				
			} else {
  
				$html .= '<title>'.$this->cleaninput($row['site.title']).'</title>';
				$html .= '<meta charset="'.$this->cleaninput($row['site.charset']).'">';
				// $html .= '<meta name="viewport" content="'.$this->cleaninput($row['site.viewport']).'">';
				$html .= '<meta name="description" content="'.$this->cleaninput($row['site.description']).'">';
				$html .= '<meta name="author" content="OpenShop">';
				
				if(!empty($row['site.updated'])) {
					$html .= '<meta http-equiv="last-modified" content="'.$this->cleaninput($row['site.updated']).'">';
				}			

				if(!empty($row['site.meta.name.1'])) {
					$html .= '<meta name="'.$this->cleaninput($row['site.meta.name.1']).'" content="'.$this->cleaninput($row['site.meta.value.1']).'">';
				}

				if(!empty($row['site.meta.name.2'])) {
					$html .= '<meta name="'.$this->cleaninput($row['site.meta.name.2']).'" content="'.$this->cleaninput($row['site.meta.value.2']).'">';
				}

				if(!empty($row['site.meta.name.3'])) {
					$html .= '<meta name="'.$this->cleaninput($row['site.meta.name.3']).'" content="'.$this->cleaninput($row['site.meta.value.3']).'">';
				}

				if(!empty($row['site.meta.name.4'])) {
					$html .= '<meta name="'.$this->cleaninput($row['site.meta.name.4']).'" content="'.$this->cleaninput($row['site.meta.value.4']).'">';
				}

				if(!empty($row['site.google.tags'])) {
					$html .= '<meta name="google-site-verification" content="'.$this->cleaninput($row['site.google.tags']).'">';
				}
			}

			$html .= '<link rel="stylesheet" type="text/css" href="'.$this->cleaninput($row['site.domain']).'/'.$this->cleaninput($row['site.canonical']).'/'.$this->cleaninput($row['site.stylesheet.reset']).'">';
			$html .= '<link rel="stylesheet" type="text/css" href="'.$this->cleaninput($row['site.domain']).'/'.$this->cleaninput($row['site.canonical']).'/'.$this->cleaninput($row['site.stylesheet1']).'">';
			$html .= '<link rel="stylesheet" type="text/css" href="'.$this->cleaninput($row['site.domain']).'/'.$this->cleaninput($row['site.canonical']).'/'.$this->cleaninput($row['site.stylesheet2']).'">';
			
			if(!empty($row['site.stylesheet3'])) {
				$html .= '<link rel="stylesheet" type="text/css" href="'.$this->cleaninput($row['site.domain']).'/'.$this->cleaninput($row['site.canonical']).'/'.$this->cleaninput($row['site.stylesheet3']).'">';
			}
			
			if(!empty($row['site.ext.stylesheet'])) {
				$html .= '<link rel="stylesheet" type="text/css" href="'.$this->cleaninput($row['site.ext.stylesheet']).'">';
			}		
			
			$html .= '<link rel="icon" type="image/ico" href="'.$this->cleaninput($row['site.domain']).'/'.$this->cleaninput($row['site.canonical']).'/'.$this->cleaninput($row['site.icon']).'">';
			$html .= '<script src="'.$this->cleaninput($row['site.domain']).'/'.$this->cleaninput($row['site.canonical']).'/'.$this->cleaninput($row['site.javascript']).'" type="text/javascript"></script>';
			
			if(!empty($row['site.ext.javascript'])) {
				$html .= '<script src="'.$this->cleaninput($row['site.ext.javascript']).'" type="text/javascript"></script>';
			}					
			if(!empty($row['site.logo'])) {
				$html .= '<img src="'.$this->cleaninput($row['site.domain']).'/'.$this->cleaninput($row['site.canonical']).'/'.$this->cleaninput($row['site.logo']).'" id="ts.shop.logo">';
			}
		}
		
		return $html;
	}

	/**
	* Paginate function
	* @param int $page
	* @return $string, html, false for failure.
	*/	

	public function invoiceid($dir,$method,$value=false) 
	{

		if(!isset($method)) {
			return false;
		}

		$shopconf = $this->load_json($dir);
		
		if($shopconf == null || $shopconf == '') {
			return false;
		}
		
		$configuration = [];
		
		if($shopconf !== null) {
			foreach($shopconf as $conf) {	
				array_push($configuration,$conf);
			}
		}
		
		if($method == 'get') {
			$invoiceid = $configuration[0]['orders.conf.invoice.id'];
			return $invoiceid;
		} 
		
		if($method == 'set') {
			
			if(isset($value)) {
				$shopconf[0]['orders.conf.invoice.id'] = (int)$value;
				$this->backup($dir);
				$this->storedata($dir,$shopconf);
				return true;
				} else {
				return false;
			}
		} 
	}
	
	public function navigation($host) { 
	
		$navigate = $this->load_json(self::INVENTORY_PATH . self::NAVIGATION);
	
		$hostaddr = $this->getbase(false,true);
	
		if(isset($this->scripturl)) {
			$script_url 	= $this->sanitize($this->scripturl,'alpha');
		}
		if(isset($this->requesturi)) {
			$request_uri 	= $this->sanitize($this->requesturi,'alpha');
		}

		if(strstr($request_uri,'category')) {
			$hostaddr = $this->getbase(false,true) .'/'; 
		} elseif(strstr($request_uri,'subcategory')) {
			$hostaddr = $this->getbase(false,true) .'/';
		} elseif(strstr($request_uri,'cart')) {
			$hostaddr = $this->getbase(false,true) .'/';
		} elseif(strstr($request_uri,$this->getbase(true,false))) {
			$hostaddr = $this->getbase(false,true) .'/';
		} else {
			$hostaddr = $this->getbase();
		}
	
		$nav = '<nav>';
		
		$total = count($navigate);
		
		foreach($navigate as $n) {	
				
			$shopfolder = $this->getbase(true,false);

			if($n['nav.status'] =='1') {
				if(strtolower($this->cleaninput($n['nav.title'])) == 'index' ) {
					$nav .= '<a href="'.$hostaddr.$shopfolder.'" target="_self">'.$this->cleaninput($n['nav.title']).'</a>' .PHP_EOL;
					} else {
					$nav .= '<a href="'.$hostaddr.$shopfolder.'/'.$this->cleaninput($n['nav.url']).'" target="_self">'.$this->cleaninput($n['nav.title']).'</a>' .PHP_EOL;
				}
			}
		}

		$nav .= '</nav>';
		
		return $nav;
	}	
	
	public function getcatId($cat,$subcat) {
		
		// categories JSON
		$categories = self::INVENTORY_PATH . self::CATEGORIES;
		
		// subcategories JSON
		$subcategories = self::INVENTORY_PATH . self::SUBCATEGORIES;

		if(isset($cat) && ($subcat !=false)) {
			
			$category = $this->load_json($categories);
			foreach($category as $c) {	
				if($this->revSeo($c['category.title']) == $this->revSeo($cat)) {
					$catno = (int)($c['category.id']-1);
					break;
				}
			}
			
			// subcategories
			$subcategory = $this->load_json($subcategories);
			
			foreach($subcategory as $sc) {	
			
				if(($this->revSeo($sc['sub.category.title']) == $this->revSeo($subcat)) && ($sc['sub.category.cat.id'] == $catno)) {
					return (int)($sc['sub.category.cat.id'] -1);
					break;
				}
			}
			
		} elseif(isset($cat)) {
			
			// categories
			$category = $this->load_json($categories);
				
			foreach($category as $c) {	

				if($this->revSeo($c['category.title']) == $this->revSeo($cat)) {

					return (int)($c['category.id'] -1);
					break;
				}
			}
			
		} else {
		return false;
		}
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

	public function getoptionbar($cat=false,$subcat=false) {
		
		$options 		= false;
		$products 		= $this->load_json("inventory/shop.json");
		$alloptions 	= [];
		
		$i=0;
		
		foreach($products as $opt) {	
		
			$variant1  		= $this->cleaninput($opt['variant.title1']);
			$variant2  		= $this->cleaninput($opt['variant.title2']);
			$variant3  		= $this->cleaninput($opt['variant.title3']);
			$productoptions = $this->cleaninput($opt['product.options']);
			$category		= $this->cleaninput($opt['product.category']);
			$subcategory 	= $this->cleaninput($opt['product.category.sub']);
			
			if(strtolower($cat) == strtolower($category) || strtolower($subcat) == strtolower($subcategory))  { 
				if(isset($variant1) && $variant1 !='') {
					array_push($alloptions,$variant1);
				}
				if(isset($variant2)  && $variant2 !='') {
					array_push($alloptions,$variant2);
				}			
				if(isset($variant3)  && $variant3 !='') {
					array_push($alloptions,$variant3);
				}
				if(isset($productoptions) && $productoptions !='') {
					array_push($alloptions,$productoptions);
				}	
			}
			
			$i++;
		}

		
		if(!empty($alloptions)) {
			$alloptions = array_unique($alloptions);
			$alloptions = array_values($alloptions);
			$options = "";
			for($j=0;$j<count($alloptions);$j++) { 
					if(stristr($alloptions[$j],',')) {
							$arr = explode(',',$alloptions[$j]);
							$arr = array_unique($arr);
							for($k=0;$k<count($arr);$k++) {
								if(trim($arr[$k]) !='') {
									$options .= '<br />';
									$options .= '<input type="checkbox" name="optionbar[]" value="'.$arr[$k].'">' . $arr[$k];
								}
							}
						
						} else {
						$options .= '<br />';
						$options .= '<input type="checkbox" name="optionbar[]" value="'.$alloptions[$j].'">' . $alloptions[$j];
					}
			} 
		} 
		
		return $options;
	}
	
	public function categories($selected=false,$direction='left') { 

			$categories 	= '../'.self::CATEGORIES;
			$subcategories  = '../'. self::SUBCATEGORIES;

			if(!isset($categories)) {
				return false;
			}
			
			if(!isset($subcategories)) {
				return false;
			}			
			
			if($selected != false) {
			
				if(is_array($selected)) {
					
					$c = count($selected);
					
					if($selected[0] != false) {
						$subcatselected = $this->sanitize($selected[0],'cat');
					} else {
						$subcatselected = false;
					}						
				}
			}
		
			// get host
			$hostaddr = $this->getbase();
		
			// categories
			$categories = $this->load_json($categories);
			
			// subcategories
			$subcategories = $this->load_json($subcategories);	
			
			if($direction == 'left') {
				$cssdirection = 'left';
			} elseif($direction == 'right') {
				$cssdirection = 'right';
			} elseif($direction == 'top') {
				$cssdirection = 'top';
			} else {
				$cssdirection = 'left';
			}
			
			$categories = $this->sortshop($categories,'category.order');
			$subcategories = $this->sortshop($subcategories,'sub.category.order');
			
			$html = '<ul id="ts-shop-'.$cssdirection.'-navigation">';
			
			if($categories !== null) {
				
				$i = 0;
				$totalcats = count($categories);
				$this->maxcats = $totalcats;
				$totalsubcats = count($subcategories);
				foreach($categories as $c) {	
				
				if($c['category.title'] !='') {
					
					if(isset($catselected) == isset($c['category.title'])) {
						$html .= '<li class="ts-shop-'.$cssdirection.'-navigation-cat-selected" onclick="OpenShop.toggle(\''.($c['category.id']-1).'\',\''.$totalcats.'\');" id="cat'.($c['category.id']-1).'"><a href="'.$hostaddr.'category/'.$this->seoUrl($c['category.title']).'/">'.$c['category.title'].'</a></li>'.PHP_EOL;
						} else {
						$html .= '<li class="ts-shop-'.$cssdirection.'-navigation-cat" onclick="OpenShop.toggle(\''.($c['category.id']-1).'\',\''.$totalcats.'\');" id="cat'.($c['category.id']-1).'"><a href="'.$hostaddr.'category/'.$this->seoUrl($c['category.title']).'/">'.$c['category.title'].'</a></li>'.PHP_EOL;
					}
					
					$catid = (int)$c['category.id'];
					
					$j = 0;
					
					if($totalsubcats >=1) {
						foreach($subcategories as $sc) {	
							if($catid == $sc['sub.category.cat.id']) {
								if($j == 0) {
									$html .= '<ul class="ts-shop-'.$cssdirection.'-navigation-subcat" id="toggle'.($c['category.id']-1).'">'.PHP_EOL;
								}
								
								if(isset($subcatselected) == isset($sc['sub.category.title'])) {
								$html .= '<li class="ts-shop-'.$cssdirection.'-navigation-subcat-item-selected"><a href="'.$hostaddr.'subcategory/'.$this->seoUrl($c['category.title']).'/'.$this->seoUrl($sc['sub.category.title']).'/">'.$sc['sub.category.title'].'</a></li>'.PHP_EOL;
									} else {
								$html .= '<li class="ts-shop-'.$cssdirection.'-navigation-subcat-item"><a href="'.$hostaddr.'subcategory/'.$this->seoUrl($c['category.title']).'/'.$this->seoUrl($sc['sub.category.title']).'/">'.$sc['sub.category.title'].'</a></li>'.PHP_EOL;	
								}
								$j++;
							}
						}
					}
					
					if($j > 0) {
						$html .= '</ul>'.PHP_EOL;
					}
				}
				$i++;
				}
			}
			$html .= '</ul>';
			
			return $html;
	}	
		
	/**
	* Returns a product list, by reading shop.json.
	* @param method: list|group.	
	* @param string: custom html can be added.
	* @param category: select shop category, if none is given it will list all products.
	* @return $string, html or array (if method is requested.)
	*/		
	public function getproducts($method,$category,$string=false,$limit=false,$page=false,$token=false) 
	{
	
		$min 	= 0;
		$max 	= 0;
		$pages  = 0;
		
		$postsearch 	= false;
		$searchresults 	= [];
		$pricebars 		= [];
	
		isset($string) ? $this->textstring = $string : $this->textstring = false;
		isset($category) ? $this->category = $this->revSeo($category) : $this->category = false;
		isset($this->pageid) ? $this->page_id = (int)$this->pageid : $this->page_id = 1;	
		isset($this->cat) ? $this->product_cat = $this->revSeo($this->cat) : $this->product_cat = $this->category;
		isset($this->subcat) ? $this->product_subcat = $this->revSeo($this->subcat) : $this->product_subcat = false;	
				
		$hostaddr = $this->getbase();
		
		// Loading the shop configuration.
		$shopconf = $this->load_json(self::INVENTORY_PATH . self::SHOPCONF);
		$configuration = [];
		
		if($shopconf !== null) {
			foreach($shopconf as $conf) {	
				array_push($configuration,$conf);
			}
		}
		
		// Logic for pagination on products.
		if($limit == false) {
			$siteconf 	= $this->load_json(self::INVENTORY_PATH . self::SITECONF);
			$result 	= $this->getasetting($siteconf,'site.maxproducts.visible.in.cat');
			$limit 		= (int) $result["site.maxproducts.visible.in.cat"];
			$limit_products = $limit;
			} else {
			$limit_products = $limit;
		}
		
		if($page != false) {
			$page_products = $page;
			} else {
			$page_products = 1;
		}
		
		$productlist = $this->decode();	

		$activelist = [];

		for($i = 0; $i < count($productlist); $i++) {	
			if($productlist[$i]['product.status'] == 1) {
				array_push($activelist,$productlist[$i]);
			}
		}

		$productlist = array_reverse($activelist);

		// rows function
		
		if($method == 'rows') {
			
			$products = array();
			
			for($i = 0; $i < count($productlist); $i++) {	
			
				$ts = $productlist;
				
					$key = [];
					array_push($key,($i+1));
					array_push($key,$this->maxstring($this->cleaninput($productlist[$i]['product.id']),10,false));
					array_push($key,$this->maxstring($this->cleaninput($productlist[$i]['product.title']),10,false));
					array_push($key,$this->maxstring($this->cleaninput($productlist[$i]['product.description']),30,true));
					array_push($key,$this->cleaninput($productlist[$i]['product.category']));
					array_push($key,$this->getsitecurrency(self::INVENTORY_PATH . self::SITECONF,self::INVENTORY_PATH . self::CURRENCIES).' '.$this->cleaninput($productlist[$i]['product.price']));
					array_push($key,$this->cleaninput($productlist[$i]['product.stock']));
					array_push($products,$key);
			}
			
			return $products;
		}

		// refine function.
		
		if($method == 'refine') {
			
			if(!isset($this->refinekey)) {
				$query = 'tag';
				} else {
				$query = $this->refinekey;
			}
			
			for($k = $min; $k < count($productlist); $k++) {	
			
				$c = $productlist[$k];
					
					$var1 = $this->cleaninput($c['variant.title1']); 
					$var2  = $this->cleaninput($c['variant.title2']);
					$var3  = $this->cleaninput($c['variant.title3']);
					$find  = $this->sanitize($query,'search');
					if(is_array($find)) {
						$find 	= implode(',',$find);
					}
					if(strlen($find) >=3) { 
						if(stristr($var1,$find)) {
							if($c['product.id'] != "") {
								if(!in_array($c,$searchresults)) {
									array_push($searchresults,$c);
								}		
							}
						} elseif(stristr($var2,$find)) {
							if($c['product.id'] != "") {
								if(!in_array($c,$searchresults)) {
									array_push($searchresults,$c);
								}	
							}
						} elseif(stristr($var3,$find)) {
							if($c['product.id'] != "") {
								if(!in_array($c,$searchresults)) {
									array_push($searchresults,$c);
								}	
							}
						}  else {}	
					}
			}
			
			$productlist = $searchresults;
			$method = 'list';
			$postsearch = true;
		}
		
		// end refine
		
		// search function.
		
		if($method == 'search') {
			
			if(!isset($this->searchkey)) {
				$query = 'tag';
				} else {
				$query = $this->searchkey;
			}
			
			for($k = $min; $k < count($productlist); $k++) {	
			
				$c = $productlist[$k];
					
					$title = $this->cleaninput($c['product.title']); 
					$desc  = $this->cleaninput($c['product.description']);
					$tags  = $this->cleaninput($c['product.tags']);
					$find  = $this->sanitize($query,'search');
			
					if(strlen($find) >=3) { 
						if(stristr($title,$find)) {
							if($c['product.id'] != "") {
								if(!in_array($c,$searchresults)) {
									array_push($searchresults,$c);
								}		
							}
						} elseif(stristr($desc,$find)) {
							if($c['product.id'] != "") {
								if(!in_array($c,$searchresults)) {
									array_push($searchresults,$c);
								}	
							}
						} elseif(stristr($tags,$find)) {
							if($c['product.id'] != "") {
								if(!in_array($c,$searchresults)) {
									array_push($searchresults,$c);
								}	
							}
						}  else {}	
					}
			}
			
			$productlist = $searchresults;
			$method = 'list';
			$postsearch = true;
		}
		
		// end search
		
		// pricebar
		if($method == 'pricebar') {
			
			if(!isset($this->maxprice)) {
				$maxprice = self::MAXINT;
				} else {
				$maxprice = (int)$this->maxprice;
			}
			
			if(!isset($this->minprice)) {
				$minprice = 1;
				} else {
				$minprice = $this->sanitize((int)$this->minprice,'num');
			}
			
			for($k = $min; $k < count($productlist); $k++) {	
			
				$c = $productlist[$k];
				$productprice = $this->cleaninput($c['product.price']); 
	
				if($c['product.price'] != "") {
					if(($productprice >= $minprice) && ($productprice <= $maxprice)) {
						if(!in_array($c,$pricebars)) {
							array_push($pricebars,$c);
						}		
					}
				} 	
			}
			
			$productlist = $pricebars;
			$method = 'list';	
			$postsearch = true;
		}
		// end pricebar
		
		if($productlist !== null) {
			$amount_products = count($productlist);
			} else {
			$amount_products = 0;
		}

		if($amount_products < 1) {
				echo 'There are not enough products to view.';
		}
			
		// build pagination for product page.
		if($amount_products >= 1) {

			$pagination = true;
			
			if(isset($this->page)) {
				$page_products   = (int)$this->page;
				} else {
				$page_products   = 1;
			}
		 
			if($amount_products < 1) {
				echo 'There are not enough products to view.';
				// exit;
			}

			if($limit_products >= 500) {
				echo 'There are too many products to view. Please edit the appropiate max product value setting in site.json.';
				exit;
			}

			if($limit_products <= 1) {
				$limit_products = 10;
			}

			if($page_products < 1) {
				$page_products = 1;
			}
			
			// todo: fix bug on limit ~ amount
			if($limit_products > $amount_products) {
				$limit_products = $amount_products;
			}
			
			$pages = round($amount_products / $limit_products);
			
			if($page_products == 1) {
				$min = 0;
				$max = $limit_products;
			}
			
			if($page_products > 1) {
				$min = (($page_products -1) * $limit_products);
				$max = ($page_products * $limit_products);		
			}
			
			if($max > $amount_products) {
				$min = ($amount_products - $limit_products); 
				$max = $amount_products; 
			}
			
		} else {
			$pagination = false;
		}
		
		// top paginate links
		$string_pag = '<div id="ts-paginate">';
		$string_pag .= '<div id="ts-paginate-left">';
		$string_pag .= 'Showing product ';
		
		if($min == 0) {
			$string_pag .= $min+1;
			} else {
			$string_pag .= $min;
		}
	
		$string_pag .= ' to ';
		$string_pag .= $max;
		$string_pag .= '</div>';
		$string_pag .= '<div id="ts-paginate-right">';
		$string_pag .= 'Page '.$page_products.' of '. $pages; 
		
		if($page != $pages) {
		   $string_pag .= '&nbsp;<span id="ts-paginate-arrow"><a href="'.($page_products+1).'/">&rarr;</a></span>';
		} 
		
		$string_pag .= '</div>';
		$string_pag .= '</div>';
		
		// carousel selection.
		if($configuration[0]['products.carousel'] == 1 && $this->category == 'index') {
			$carousel = true;
		}

		$this->textstring .= "<div id=\"ts-product\">";
		
		if($productlist !== null) {
			
			$ts 	  = array();
			$shoplist = $productlist;
		
			if($pagination == false) {
				$min  = 0;
				$max  = count($productlist);
			} 
			
			for($k = $min; $k < count($productlist); $k++) {	
			
				$c = $productlist[$k];
				
				if(($c['product.featured'] == '1') && ($c['product.featured.location'] == $this->product_cat)) {
					$this->textstring .= '<div class="ts-product-image-div-featured">';
					$this->textstring .= '<h3>'.$this->cleaninput($c['product.title']).'</h3>'; 
					$this->textstring .= '<div>'.$this->cleaninput($c['product.description']).'</div>'; 
					$this->textstring .= '<a href="'.$this->cleaninput($c['product.url']).'"><img src="'.$hostaddr.'/'.$this->cleaninput($c['product.featured.image']).'" class="ts-product-image"/></a></div>';
				}
					if($this->product_subcat != false) {
						// category and subcategory

						if($this->revSeo($c['product.category.sub']) == $this->revSeo($this->product_subcat) && ($this->revSeo($c['product.category']) == $this->revSeo($this->product_cat)) && ($c['product.status'] == '1')) {
								array_push($ts,$c);
						}	
					} elseif($this->product_cat != false) {
						// only category
						if($this->revSeo($c['product.category']) == $this->revSeo($this->product_cat)) {
								array_push($ts,$c);
						}	
					} else {
						// no cat nor subcat, might be search.
						if($postsearch != false) {
							array_push($ts,$c);
						}
					}
				
					$this->cleaninput($c['product.title']);
				if($postsearch != false) {
						array_push($ts,$c);
				}
			}
		
			// flip array order, as most products are added sequentially...
			$ts = array_reverse($ts);	
			
			// pagination count correction.
			$ts_pag = count($ts);

			if($ts_pag > $limit) {
				$this->textstring .= $string_pag;
				} else {
				
			}
			
			if($k <= 0) {
				return '<div id="ts-products-noproducts">There are no products in this category.</div>';
			}
			
			if($method == 'array') {
				return $ts;
				exit;
			}

			if($pagination == false) {
				$i = count($ts);
				} else {
				$i = $max;
			}

			if($i >= 0) { 
			
				while($i >= 0) {
					
					if(isset($ts[$i]['product.stock'])) {
						$stock = (int) $ts[$i]['product.stock'];
					} else {
						$stock = 0;
					}
					
					if($stock <= 5) {
						$status = 'ts-product-status-red'; // low stock
						} else {
						$status = 'ts-product-status-green';
					}
					
					if(isset($ts[$i]['product.image']) != "") {
						$productimage = '<div class="ts-product-image-div"><img src="'.$hostaddr.$this->cleaninput($ts[$i]['product.image']).'" class="ts-product-image"/></div>';
						} else {
						$productimage = '<div class="ts-product-image-icon">&#128722;</div>';
					}				
					
					switch($method) {
						
						case 'list':

						if(isset($ts[$i]['product.description'])) {

							$this->textstring .= "<div class=\"ts-product-list\">";
							$this->textstring .= $productimage;
							$this->textstring .= "<div class=\"ts-list-product-link\"><a href=\"".$this->getbase()."category/".$this->seoUrl($this->cleaninput($ts[$i]['product.category']))."/item/".$this->seoUrl($this->cleaninput($ts[$i]['product.category'])).'/'.$this->seoUrl($this->cleaninput($ts[$i]['product.title'])).'/'.$this->cleaninput($ts[$i]['product.id'])."/".(int)$this->page_id."/\">".$this->maxstring($this->cleaninput($ts[$i]['product.title']),10,false)."</a> </div>";
							$this->textstring .= "<div class=\"ts-list-product-desc\">".$this->maxstring($this->cleaninput($ts[$i]['product.description']),30,true)."</div>";
							
							// $this->textstring .= "<div class=\"ts-list-product-cat\">".$this->cleaninput($ts[$i]['product.category'])."</div>";
							$this->textstring .= "<div class=\"ts-list-product-price\">".$this->getsitecurrency(self::INVENTORY_PATH . self::SITECONF,self::INVENTORY_PATH . self::CURRENCIES).' '.$this->cleaninput($ts[$i]['product.price'])."</div>";
							$this->textstring .= "<div class=\"ts-list-product-status\">left in stock.<div class=\"".$status."\">".$this->cleaninput($ts[$i]['product.stock'])."</div></div>";
							
							if(isset($configuration[0]['products.quick.cart']) == 'yes') {
								$this->textstring .= "<div><input type='number' name='qty' size='1' value='1' min='1' max='9999' id='ts-group-cart-qty-".($i+1).'-'.(int)$ts[$i]['product.id']."'><input type='button' onclick='OpenShop.addtocart(\"".(int)$ts[$i]['product.id']."\",\"ts-group-cart-qty-".($i+1).'-'.(int)$ts[$i]['product.id']."\",\"".$token."\",\"".$hostaddr."\");' class='ts-list-cart-button' name='add_cart' value='".$this->cleaninput($configuration[0]['products.cart.button'])."' /></div>";
								} else {
								$this->textstring .= "<div class='ts-list-view-link'><a href=\"product/".$this->cleaninput($ts[$i]['product.id'])."/\">view</a></div>";
							}
							
							$this->textstring .= "</div>";
						} 
		
						break;

						case 'group':		
						
						$this->textstring .= "<div class=\"ts-product-group\">";
						$this->textstring .= $productimage;
						$this->textstring .= "<div class=\"ts-group-product-link\"><a href=\"item/".$this->seoUrl($this->cleaninput($ts[$i]['product.category'])).'/'.$this->seoUrl($this->cleaninput($ts[$i]['product.title'])).'/'.$this->cleaninput($ts[$i]['product.id'])."/\">".$this->cleaninput($ts[$i]['product.title'])."</a> </div>";
						$this->textstring .= "<div class=\"ts-group-product-desc\">".$this->cleaninput($ts[$i]['product.description'])."</div>";
						$this->textstring .= "<div class=\"ts-group-product-price\">".$this->getsitecurrency(self::INVENTORY_PATH . self::SITECONF,self::INVENTORY_PATH . self::CURRENCIES).' '.$this->cleaninput($ts[$i]['product.price'])."</div>";
						// $this->textstring .= "<div class=\"ts-group-product-cat\">".$this->cleaninput($ts[$i]['product.category'])."</div>";
						$this->textstring .= "<div class=\"ts-group-product-status\">left in stock.<div class=\"".$status."\">".$this->cleaninput($ts[$i]['product.stock'])."</div></div>";
						
						if(isset($configuration[0]['products.quick.cart']) == 'yes') {
							
							$this->textstring .= "<div><input type='number' name='qty' size='1' min='1' max='9999' value='1' id='ts-group-cart-qty-".($i+1).'-'.(int)$ts[$i]['product.id']."'><input type='button' onclick='OpenShop.addtocart(\"".(int)$ts[$i]['product.id']."\",\"ts-group-cart-qty-".($i+1).'-'.(int)$ts[$i]['product.id']."\",\"".$token."\",\"".$host."\");' class='ts-group-cart-button' name='add_cart' value='".$this->cleaninput($configuration[0]['products.cart.button'])."' /></div>";
							} else {
							$this->textstring .= "<div class='ts-group-view-link'><a href=\"product/".$this->cleaninput($ts[$i]['product.id'])."/\">view</a></div>";
						}
						
						$this->textstring .= "</div>";
						break;
					}
				$i--;
				}
			}
					
		}

		$this->textstring .= "</div>";		
		
		return array($k,$this->textstring);
	}
	
	public function getproductlist($json) {
		
		if(!isset($json)) {
			$json = self::INVENTORY_PATH . self::INVENTORY;
		} 
		
		$cart = $this->load_json($json);
		
		$products = [];
		
		// TODO: we could do an additional security check on JSON values.
		
		$i=0;
			foreach($cart as $item)
			{	
				$products[$i] = [];
				
			foreach($item as $product => $value)
			{
				array_push($products[$i],[$product,$value]);
			}
			$i++;
		}
			
		return $products;
	}

	public function getoptionbox($box,$variant,$option,$prices=false,$productId=false) {
			
			$optionbox = false;
			
			if(stristr($variant,',')) {
								
					if(stristr($variant,',')) {
									
						$optionbox = '<select name="'.$this->sanitize($box,'option').'" id="'.$this->sanitize($box,'option').'" onchange="javascript:OpenShop.updateprices(\''.$productId.'\',\'price-update\',\''.$this->getToken().'\',\''.$this->getbase().'cart/addtocart/\',\''.$this->sanitize($box,'option').'\',\''.$box.'\');">';
		
						$opts 	= explode(',',$option);
						$n 		=  count($opts);
					
						if($prices != false) {
							if(stristr($prices,',')) {
									$optsprices	= explode(',',$prices);
									$np 		=  count($optsprices);	
							}	
						}
								
						if($n >=1 ) {
							for($i = 0; $i < $n; $i++) {

								if($np > 0) {
									$optionbox .= '<option value="'.$this->sanitize($opts[$i],'option').'">'.$this->sanitize($opts[$i],'option').' -> price: '.$this->sanitize($optsprices[$i],'num').'</option>';
									} else {
									$optionbox .= '<option value="'.$this->sanitize($opts[$i],'option').'">'.$this->sanitize($opts[$i],'option').'</option>';
								}
							}
						}
									
					}
								
				$optionbox .= '</select>';
			}
			
		return $optionbox;
	}
	
	public function getpagelist($json,$method) {

		$html = '';
		
		switch($method) {
			
			case 'blog':
			if(!isset($json)) {
				$json = self::INVENTORY_PATH . self::BLOG;
			}
			$css = 'blog';
			break;
			
			case 'articles':
			if(!isset($json)) {
				$json = self::INVENTORY_PATH . self::ARTICLES;
			}
			$css = 'articles';
			break;

			case 'pages':
			if(!isset($json)) {
				$json = self::INVENTORY_PATH . self::PAGES;
			}
			$css = 'pages';
			break;			
		}
		
		$shopconf = $this->load_json($json);

		return $shopconf;
	}
	
	public function categorylist($method,$category=null,$subcategory=null) 
	{
		
		$html = "";
		$igoreset = [];
		
		switch($method) {
			
			case 'all':
		
				if($category !== null) {

					$i = 0;
					$html = "";
					
					foreach($category as $row)
					{
						foreach($row as $key => $value)
						{
							if($key == 'category.id') {
								$subcatid = $value;
							}
							

							if($key == 'category.title') {
								
								if($value !='' || $value != null) {
								$html .= "<option value=\"".$this->sanitize($value,'option')."\">".$this->sanitize($value,'unicode')."</option>";
									foreach($subcategory as $subrow)
									{
										
										$maincat = $row['category.id'];

										if($subrow['sub.category.cat.id'] == $maincat) {
												
											if($subrow['sub.category.title'] !='' || $subrow['sub.category.title'] != null) {

												$html .= "<option value=\"".$this->sanitize($value,'option').'/'.$this->sanitize($subrow['sub.category.title'],'option')."\"> - ".$this->sanitize($subrow['sub.category.title'],'unicode')."</option>";
											}
										}
									}
								}
							}
						}
					}		
				}		
			
			break;
			
			case 'category':
			
				if($category !== null) {

					$i = 0;
					$html = "";
					
					foreach($category as $row)
					{
						foreach($row as $key => $value)
						{
							if($key == 'category.title') {
								$html .= "<option value=\"".$this->sanitize($value,'option')."\">".$this->sanitize($value,'option')."</option>";
							}
						}
					}		
				}	
			
			break;
			
			case 'subcategory':
			
				if($category !== null) {

					$i = 0;
					$html = "";
					
					foreach($category as $row)
					{
						foreach($row as $key => $value)
						{
							if($key == 'sub.category.title') {
								$html .= "<option value=\"".$this->sanitize($value,'option')."\">".$this->sanitize($value,'option')."</option>";
							}
						}
					}		
				}		
			
			break;	
		}			
		
		return $html;
	}

	public function gethost($json,$shoppath=false)   
	{
		$siteconf 		= $this->load_json($json);
		$result 		= $this->getasetting($siteconf,'site.url');
		$result_path 	= $this->getasetting($siteconf,'site.canonical');   
		
		$find 		= ['http://','https://','www.','/'];
		$replace 	= ['','','',''];
		
		$home  		= 'https://';
		$home 	   .= str_replace($find,$replace,$result['site.url']);
		
		if($shoppath==true) {
			$home  .= '/' . $result_path['site.canonical'] . '/';
		}
		
		return $home;
	}

	public function getasetting($json,$akey) 
	{
			if($json !== null) {
				foreach($json as $key => $value)
				{
					if($key == $akey) {
						return $value;	
					}		
				}		
			}
	}
	
	public function gatewaylist($json,$keys) 
	{
		$html = "";
			if($json !== null) {
				foreach($json[0][$keys] as $key => $value)
				{
					$html.= "<option value=\"".$this->sanitize($value,'option')."\">".$this->sanitize($value,'option')."</option>";			
				}		
			}
		return $html;
	}

	public function shippinglist($json,$freeshipping=false) 
	{
		$html = "";
		$igoreset = ['shipping.Flat.Fee','shipping.Flat.Fee.International'];
		
			if($json !== null) {
				foreach($json[0] as $key => $value)
				{
					if(!in_array($key,$igoreset)) {
						if($value == 0) {
						$html.= "<option value=\"".$this->sanitize($key,'option')."\" disabled>".str_replace('shipping.','',$this->cleaninput($key))."</option>";
						} else {
						$html.= "<option value=\"".$this->sanitize($key,'option')."\">".str_replace('shipping.','',$this->cleaninput($key))."</option>";
							if($freeshipping == false) {
								$html.= "<option disabled>-> shipping price: ".(float)$value."</option>";
							}
						}
					}

				}		
			}
		return $html;
	}
	
	public function currencylist($disallowed=false) 
	{
		$html = "";
		
		echo $disallowed;
		$currencies = $this->load_json(self::CURRENCIES);
		
			if($currencies !== null) {
				$i=0;
				foreach($currencies[0] as $key => $value)
				{
					if($disallowed != false && (strtolower($value) == strtolower($disallowed))) {
						$html .= "<option value=\"".$this->sanitize($key,'num')."\" disabled>".$this->cleaninput($currencies[0][$i]['sign'])."</option>";
						} else {
						$html .= "<option value=\"".$this->sanitize($key,'num')."\">".$this->cleaninput($currencies[0][$i]['sign'])."</option>";
					}
					$i++;
				}		
			}
			
		return $html;
	}
	
	public function getcountries() 
	{
		$html = "";
		
		$shipping = $this->load_json(self::INVENTORY_PATH . self::SHIPPING);
		
			if($shipping !== null) {
				$i=0;
				foreach($shipping[0] as $key => $value)
				{
					$html .= '<div class=\"ts-country-list-option\">' . $this->cleaninput($key) .": <input type=\"text\" name=\"".$key."\" value=\"".$value."\" size=\"20\" /></div>";
					$i++;
				}		
			}
			
		return $html;
	}	
	
	public function getcountryprice($json,$country) {
		
		$countryprice = 0;
			if($json !== null) {
				foreach($json[0] as $key => $value)
				{
					if($key == $country) {
						$countryprice = $value;
					}
				}		
			}
			
		if($countryprice > 0) {
			return $countryprice;
			} else {
			return false;
		}
	}
	
	/* Get the currency of site.json
	*  To change the default currency, edit site.json which has a numeric value that corresponds to the values inside currencies.json.
	*  DO NOT edit currencies.json, unless adding a new currency, as this file is used throughout OpenShop and might break functionality.
	*/
	public function getsitecurrency($conf=false,$currency=false) 
	{
		
		if(!isset($conf)) {
			$siteconf = $this->load_json(self::INVENTORY_PATH . self::SITECONF);
			} else {
			$siteconf = $this->load_json($conf);
		}
		
		if(!isset($currency)) {
			$currencies = $this->load_json(self::INVENTORY_PATH . self::CURRENCIES);
			} else {
			$currencies = $this->load_json($currency);
		}
		
		
		if($siteconf !== null || $siteconf !== false) {
				
			if($siteconf[0]['site.currency'] >=0) {
				return $currencies[0][$siteconf[0]['site.currency']]['symbol'];
			}
		} else {
			return 'Price:';
		}
	}

	public function generatecart($json,$split,$ignore) 
	{
			if($json !== null) {

				$i = 0;
				$html = "";
				
				foreach($json as $row)
				{
					foreach($row as $key => $value)
					{
						if(!in_array($key,$ignore)) {
							
							$key = str_replace(['.','customer'],['',''],$key);
							$keycss = str_replace('.','-',$key);
							
							if($key == 'newsletter') {
								$html .= "<label>".ucfirst($key)."</label>";
								$html .= "<input type=\"checkbox\" id=\"".$keycss."\" name=\"".$key."\">";	
								} else {
								$html .= "<label>".ucfirst($key)."</label>";
								$html .= "<input type=\"text\" id=\"".$keycss."\" name=\"".$key."\">";
							}
							
							$i++;
						}
						
						if($i == $split) {
							$html .= "</div>";
							$html .= "<div class=\"ts-shop-form-field\">";
						}
					}
				}
			}
			return $html;
	}

	/**
	* Parsing CSV values
	* @param string $values
	* @return string
	*/	
	public function csvstring($values) {

		$data = '';
		$cv = count($values);
	
		if($cv >=1) {
					
				for($i=0; $i < $cv; $i++) {
							
						if(is_array($values[$i])) {

							$tmpvalue = '"';
							$c = count($values[$i]);
									
							for($j=0;$j<$c;$j++) {
								$tmpvalue .= $values[$i][$j];
									if($c >=1 && $j < ($c-1)) {
										$tmpvalue .= ',';
									} 
							}
							
							if($cv >=1) {
								if($i == ($cv-1)) {
									$data .= $tmpvalue .= '"';
									} else {
									$data .= $tmpvalue .= '",';
								}
							}
									
						} else {			
							if(stristr($values[$i],',')) {
							$data .= '"'.$values[$i].'",';
							} else {
							$data .= $values[$i].',';
						}
					}
				}
		}			
		return $data;
	}
				
	/**
	* Converter for data, types and strings.
	* @param string $string
	* @return array
	*/	
	public function convert($file,$method,$name=false,$backup=false){
		
	$data = [];
	
		switch($method) {
			
			case 'csv_to_json':
			
				if(!isset($file)) {
					$this->message('Please choose a CSV file to convert.');
					break;
				}

				if(in_array($name,$this->serverconfig_csv)) {
					$server_path = '../server/config/';
					} else {
					$server_path = '../inventory/';
				}
				
				// Back-up CSV before processing.
				$this->backup($server_path.'/csv/'.$name,'../'.self::BACKUPS); 
				
				$csv1 = explode("\r\n", $file);
				$c1 = count($csv1);
				
				$csv2 = explode("\n", $file);
				$c2 = count($csv2);
				
				if($c1 <= $c2) {
					$counter = $c1;
					$csv = $csv1;
					} else {
					$counter = $c2;
					$csv = $csv2;
				}
				
				$buildcsv = "";

				$find = ["\r\n","\n\r","\n","\r","\t"];
				$replace = ["\\n","\\n","\\n","","\\t"];
							
				for($i=0;$i<$counter;$i++) {
					if(strlen($csv[$i]) <= 4) { 
						if($i < ($counter-1) ) {
							$buildcsv .= "\\n" . str_ireplace($find,$replace,$csv[$i]);
						} 
					} else {
						$csv[$i] = str_ireplace($find,$replace,$csv[$i]);
						$buildcsv .= $csv[$i].PHP_EOL;
					}
				}

				$find 		= ["\n\r\n", "\r\n\\n", "\r\n\\n", "\r\n\\n", "\r\n\\n", "\n\n\\n", "\r\\n",  "\r\s\n"];
				$replace 	= ["\\n\\n", "\\n\\n",  "\\n\\n",  "\\n\\n",  "\\n\\n",  "\\n\\n",  "\\n\\n", "\\n\\n"];

				$buildcsv = str_ireplace($find,$replace,$buildcsv);

					$data = array_map("str_getcsv", explode("\n", $buildcsv));
					$columns = $data[0];
					
							foreach ($data as $row_index => $row_data) {
								if($row_index === 0) continue;
								$data[$row_index] = [];
								foreach ($row_data as $column_index => $column_value) {
									$label = $columns[$column_index];
									$data[$row_index][$label] = $column_value;       
								}
								unset($data[0]);
							}
							
							$c = count($data);
							if($c > 1) {
								unset($data[$c]);
							}
							
				$data = array_values($data);
				
			break;

			case 'json_to_csv_admin':
				
				if($name) {
					if(in_array($name,$this->serverconfig_json)) {
						$server_path = '../'.self::SERVERCSV;
						} else {
						$server_path = '../'.self::CSV;
					}
				}
					
				// Back-up JSON before processing.
				$this->backup($server_path.$name,'../'.self::BACKUPS); 
				
				$json_data =  json_decode($file, true, self::DEPTH, JSON_BIGINT_AS_STRING);
				
				array_keys($json_data);

				$csv 	= '';
				$header = [];
				$data 	= [];
				
				$csv_keys = array_keys($json_data[0]);

				for($i=0; $i < count($csv_keys); $i++) {
					$csv .= $csv_keys[$i] . ',';
				}	
	
				$csv .= PHP_EOL;

				$csv_vals = array_values($json_data);
				
				for($i=0; $i < count($csv_vals); $i++) {

					$csv .=  $this->csvstring(array_values($csv_vals[$i])) . PHP_EOL;
				}

				$find 	 = ['.json','../','./','\\'];
				$replace = ['.csv','','',''];
				
				$csv_file_upload = $server_path . str_ireplace($find,$replace,$name);
				
				$pointer = fopen($csv_file_upload,'w');
				fwrite($pointer,$csv);

				$data = $csv;
				
			break;
			
			case 'json_to_csv':

				if(!defined(self::SHOP)) {
					$this->message('Conversion failed: JSON file not found.');
					break;
				}
				
				if(!defined(self::CSV)) {
					$this->message('Conversion failed: CSV file not found.');
					break;
				}
				
				$json_data = $this->convert(self::SHOP,'json_decode');
				$csv_file = fopen(self::CSV, 'w');
				
				$header = false;
				
				foreach ($json_data as $line){

					if (empty($header)) {
						$header = array_keys($line);
						fputcsv($f, $header);
						$header = array_flip($header);
					}
					
					$data = array($line['type']);
					foreach ($line as $value) {
						array_push($data,$value);
					}
					
					array_push($data,$line['stream_type']);
					fputcsv($csv_file, $data);
				}
				
			break;
			
			case 'json_decode':
			$data = json_decode(file_get_contents($file), true, self::DEPTH, JSON_BIGINT_AS_STRING);
			break;

			case 'json_encode':
			$data = json_encode($shop, JSON_PRETTY_PRINT);
			break;
		}
		
		return $data;
	}

	public function traverse($string) {
		
		
		// prepare string by removing all illegal characters.
		$find = ['../','./','%','#','&'];
		$replace = ['','','','',''];
		$string = str_ireplace($find,'',$string);
		
		// test string before processing
		if(stristr(rawurldecode($string),'..') != false) {
			$this->message("Error: illegal characters found in filename.");
			exit;	
		}		
		// filetype must be either json or csv.	
		if(substr(strtolower($string),-5) == '.json' || substr(strtolower($string),-4) == '.csv') {
			} else {
			$this->message("Error: this is not a supported file.");
			exit;	
		}		
		
		// only allow alphanumeric characters, a period and slash.
		$string  = preg_replace('/[^a-zA-Z-0-9.\/]/','', $string);
		// filetype must be either json or csv, after preg_replace.
		if((substr(strtolower($string),-5) == '.json') || (substr(strtolower($string),-4) == '.csv')) {
			$urlstring = $string;
			} else {
			$this->message("Error: this is not a supported file.");
			exit;								
		}

		// a file path must start with either inventory/ or server/config/
		if(substr($urlstring,0,10) == 'inventory/') {
			$url = $urlstring;
			} elseif(substr($urlstring,0,14) == 'server/config/') {
			$url = $urlstring;
			} else {
				echo $this->debug($urlstring);
			$this->message("Error: JSON file could not be loaded due to possible directory traversal.");
			exit;			    
		}	
		
		// try to locate the file, rewind if needed.
		if(file_exists($url)) {
			$file = file_get_contents($url);
				} elseif(file_exists('../'.$url)) {
				$file = file_get_contents('../'.$url);
				} elseif(file_exists('../../'.$url)) {
				$file = file_get_contents('../../'.$url);
				} else { 
			$file = false;
		}
				
		return $file;		
	}
	
	public function logging($dir)  {
		
		$storing  = 1;
		$logfile  = self::LOGGINGDIR;
		$logfile .= $this->sanitize($dir,'alphanum') . '/log.log';		
		
		$remoteaddr	 	= $this->sanitize($this->remoteaddr,'log',50);
		$useragent 		= $this->sanitize($this->useragent,'log',250);
		$scriptname 	= $this->sanitize($this->scriptname,'log',255);
		$querystring 	= $this->sanitize($this->querystring,'log',500);
		
		if(isset($this->referer)) {
			$referer  = $this->sanitize($this->referer,'log',500);	
			} else {
			$referer  = '';
		}
		
		if($remoteaddr == false) {
			$storing += 1;
		}
		if($useragent == false) {
			$storing += 1;
		}		
		if($scriptname == false) {
			$storing += 1;
		}
		if($querystring == false) {
			//$storing += 1;
		}
		if($referer == false) {
			//$storing += 1;
		}	

		if($storing == 1) {
			if(file_exists($logfile)) {
				if(filesize($logfile) > 3000000) {
					//empty log
					@file_put_contents($logfile, "");
					} else {
						if(isset($this->referer)) {
							$refer = $referer;
							} else {
							$refer = 'no-referer';
						}
					$log = date("F j, Y, g:i a") . ' - '. $remoteaddr.' - '.$useragent.' - '. $refer.' - '.$scriptname. ' - '.$querystring. PHP_EOL;
					@file_put_contents($logfile, $this->sanitize($log,'log'), FILE_APPEND);
				}
			}
		}
	}
	
	public function backup($url,$dir=false) 
	{	
		if($dir != false) {
			$find 	 = ['../inventory/','../inventory/csv/','../','../../'];
			$replace = ['','','',''];
			$copy 	 = $dir.str_ireplace($find,$replace,$url).self::BACKUPEXT;
			} else {
			$copy 	= $url.self::BACKUPEXT;
		}
		// TODO: find out scope, for better security.
		@copy($url, $copy);
	}

	/**
	* Sanitizes user-input
	* @param string
	* @return string
	*/
	public function cleaninput($string) 
	{
		if(is_array($string)) {
			return @array_map("htmlspecialchars", $string, array(ENT_QUOTES, self::PHPENCODING));
			} else {
			return htmlspecialchars($string, ENT_QUOTES, self::PHPENCODING);
		}
	}
	
	/**
	* Sanitizes page output
	* @param string
	* @return string
	*/
	public function cleanpageoutput($string) 
	{
		$find = ['\n','\r','\t'];
		$replace = ['<br />','<br />','&emsp;'];
		$str = str_ireplace($find,$replace,htmlspecialchars($string, ENT_QUOTES, self::PHPENCODING));
		return nl2br($str);
	}	

	/**
	* Max string of user-input
	* @param string, length and dots.
	* @return string
	*/
	
	public function maxstring($string,$len,$dots) 
	{
		$wordarray = explode(' ',$string);
		
		$returnstring = '';
		
		$c = count($wordarray);
		
		for($i = 0; $i < $c; $i++) {
			
			if(strlen($returnstring) >= $len) {
				break;
			} else {
				$returnstring .= $wordarray[$i] . ' ';
			}
		}
		
		if($dots == true) {
			$returnstring .= '...';
		}
		
		return $returnstring;
	}	
	

	/**
	* Sanitizes user-input
	* @param string
	* @return string
	*/
	
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
	
	public function formatter($string,$method) {
		
		$returnstring = '';
		
		switch($method) {
			
			case 'product-description':

			$returnstring = $this->sanitize($string,'encode');
			$returnstring = substr($returnstring,0,512);
			
			$find = ['\n','\r','\t'];
			$replace = ['<br />','<br />','&emsp;'];
			$returnstring = str_ireplace($find,$replace,htmlspecialchars($returnstring, ENT_QUOTES, self::PHPENCODING));
			return nl2br($returnstring);
		
			break;
			
		}
		
		return $returnstring;
	}

	/**
	* SEO-ing URL.
	* @param string
	* @return string
	*/
	public function seoUrl($string) 
	{
		$find 		= [' ','_','=','+','&','.'];
		$replace 	= ['-','-','-','-','-','-'];
		$string 	= str_replace($find,$replace,strtolower($string));
		return htmlspecialchars($string, ENT_QUOTES, self::PHPENCODING);
	}	
	
	/**
	* Reverse SEO URL.
	* @param string
	* @return string
	*/
	public function revSeo($string) 
	{
		if(strlen($string) > 150) {
			$this->message("Error: string length in category is too large.");
			return false;
			exit;
			} else {
			$string = preg_replace('/[^a-zA-Z-0-9\-]/','', $string);
			return str_replace('-',' ',strtolower($string));
		}
	}

	public function uniqueID() {
		
		$len_id 	= 0;
		$bytes_id 	= 0;
		
		if (function_exists('random_bytes')) {
			$len   = mt_rand(self::MINHASHBYTES,self::MAXHASHBYTES);
        		$bytes_id .= bin2hex(random_bytes($len));
    		}
		if (function_exists('openssl_random_pseudo_bytes')) {
			$len   = mt_rand(self::MINHASHBYTES,self::MAXHASHBYTES);
        		$bytes_id .= bin2hex(openssl_random_pseudo_bytes($len));
    		}
		
		if(strlen($bytes_id) < 128) {
			$bytes_id .= mt_rand(self::MINMERSENNE,self::MAXMERSENNE) . mt_rand(self::MINMERSENNE,self::MAXMERSENNE) . mt_rand(self::MINMERSENNE,self::MAXMERSENNE)
				. mt_rand(self::MINMERSENNE,self::MAXMERSENNE) . mt_rand(self::MINMERSENNE,self::MAXMERSENNE) . mt_rand(self::MINMERSENNE,self::MAXMERSENNE) 
				. mt_rand(self::MINMERSENNE,self::MAXMERSENNE) . mt_rand(self::MINMERSENNE,self::MAXMERSENNE) . mt_rand(self::MINMERSENNE,self::MAXMERSENNE) 
				. mt_rand(self::MINMERSENNE,self::MAXMERSENNE) . mt_rand(self::MINMERSENNE,self::MAXMERSENNE) . mt_rand(self::MINMERSENNE,self::MAXMERSENNE); 
		}
		
		$token_id 	= hash('sha512',$bytes_id);
		$uniqueid  	= substr($token_id,0,12);
		
		return $uniqueid;
	}

	public function pseudoNonce($max=0xffffffff) {
		$tmp_nonce = uniqid().mt_rand(0,$max).mt_rand(0,$max).mt_rand(0,$max).mt_rand(0,$max);
		return $tmp_nonce;
	}
	
	public function getToken() {
		
		$bytes = 0;
		
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
		
		if(isset($this->token) && $this->token != false) 
		{ 
			if(strlen($this->token) < 128) {
				// $this->sessionmessage('Issue found: session token is too short.'); 
				} else {
				return $this->sanitize($this->token,'alphanum'); 
			}
		} else { 
		return $token;
		} 
	} 
	
	/**
	* Encryption function (requires OpenSSL)
	* @param string $plaintext
	* @return $ciphertext
	*/	
	
	// We don't use this, but you could call it to encrypt the JSON data.
	public function encrypt($plaintext) {

		if (!function_exists('openssl_encrypt')) {
			$this->message('Encryption failed: OpenSSL is not supported or enabled on this PHP instance.');
			return false;
    	}
		
		$key = self::PWD(); // Password is set above at the Constants
		$ivlen = openssl_cipher_iv_length($cipher="AES-256-CTR");
		$iv = openssl_random_pseudo_bytes($ivlen);
		$ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
		$hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
		$ciphertext = base64_encode($iv.$hmac.$ciphertext_raw );
		return bin2hex($ciphertext);
	}
	
	/**
	* Decryption function (requires OpenSSL)
	* @param string $ciphertext
	* @return $plaintext or false if there is no support for OpenSSL.
	*/		
	
	// We don't use this, but you could call it to decrypt the JSON data.
	public function decrypt($ciphertext) {
		
		if (!function_exists('openssl_decrypt')) {
			$this->message('Decryption failed: OpenSSL is not supported or enabled on this PHP instance.');
			return false;
    	}
		
		$key = self::PWD(); // Password is set above at the Constants
		$ciphertext = hex2bin($ciphertext);
		$c = base64_decode($ciphertext);
		$ivlen = openssl_cipher_iv_length($cipher="AES-256-CTR");
		$iv = substr($c, 0, $ivlen);
		$hmac = substr($c, $ivlen, $sha2len=32);
		$ciphertext_raw = substr($c, $ivlen+$sha2len);
		$original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
		$calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
		
		if (hash_equals($hmac, $calcmac)) { //PHP 5.6+ timing attack safe comparison
			return $original_plaintext;
		}
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
	
	public function debug($rawdata) 
	{
		$string  = "<pre>";
		$string .= print_r($rawdata);
		$string .= "</pre>";
		return $string;
	}
}

?>
