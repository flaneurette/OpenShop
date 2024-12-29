<?php


include_once(__DIR__."/../../core/Sanitize.php");
include_once(__DIR__."/../../core/JSON.Loader.php");


class Shop {

	CONST SHOPVERSION 			= "?cache-control=4.1"; // increment if major changes are made to the shop database.
	CONST INVENTORY_PATH 		= "";
	CONST SITECONF				= "server/config/site.conf.json";
	CONST CURRENCIES			= "server/config/currencies.conf.json";
	CONST SHOPCONF				= "server/config/shop.conf.json";
	CONST SHIPPING 				= "server/config/shipping.conf.json";
	CONST INVENTORY				= "inventory/shop.json";
	CONST CATEGORIES			= "inventory/categories.json";
	CONST SUBCATEGORIES			= "inventory/subcategories.json";
	CONST NAVIGATION 			= "inventory/navigation.json";
	CONST BLOG					= "inventory/blog.json";
	CONST ARTICLES				= "inventory/articles.json";
	CONST PAGES					= "inventory/pages.json";
	CONST CSV					= "inventory/csv/";
	CONST SERVERCSV				= "server/config/csv/";
	CONST LOGGINGDIR 			= "server/logging/";
	CONST BACKUPS				= "inventory/backups/";
	CONST BACKUPEXT				= ".bak"; 
	CONST FILE_ENC				= "UTF-8";
	CONST FILE_OS				= "WINDOWS-1252"; // only for JSON and CSV, not the server architecture.
	CONST MAXINT  				= 9999999999;
	CONST DEPTH					= 10024;
	CONST MAXTITLE				= 255; // Max length of title.
	CONST MAXDESCRIPTION		= 500; // Max length of description.

	CONST PHPENCODING 			= 'UTF-8';		// Characterset of PHP functions: (htmlspecialchars, htmlentities) 
	
	CONST GATEWAYS 	= ["ACH","Alipay","Apple Pay","Bancontact","BenefitPay","Boleto Bancario","Citrus Pay","EPS","Fawry","Giropay","Google Pay","PayPal","KNET","Klarna","Mada","Multibanco","OXXO","Pago FÃƒÂ¡cil","Poli","Przelewy24","QPAY","Rapipago","SEPA Direct Debit","Sofort","Stripe","Via Baloto","iDEAL"];
	
	public function __construct() {
		
		$this->sanitizer 	= new Sanitizer;
		$this->json 	 	= new JSONLoader;
		
		$incomplete = false;
		$this->maxcats = 0;
		$this->subcategories = [];
		
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
		
	}

	public function host() {
		return $this->getbase();
	}
	
	public function getbase($path=false,$nav=false) 
	{	
	
		$host 		= $this->gethost(self::INVENTORY_PATH . self::SITECONF,true);
		$siteconf 	= $this->json->load_json(self::INVENTORY_PATH . self::SITECONF);
		$url  		= $this->getasetting($siteconf,'site.url');
		$canonical  = $this->getasetting($siteconf,'site.canonical');

		if($nav == true) {
			if(isset($url) != null) {
			return $url;
			}
		}
		
		if($path == true) {
			return $canonical;
		}
		
		$find 	 = ['http://','https://','http//','https//','www.','www','/'];
		$replace = ['','','','','','',''];

		// build paths dynamically
		$home  = 'https://';
		if(isset($url) != null) {
			$home .= str_replace($find,$replace,$url);
		}
		$home .= '/';
		if(isset($canonical) != null) {
			$home .= $canonical;
		}
		$home .= '/';
		
		return $home;
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
	* Navigation function
	* @param int $page
	* @return $string, html, false for failure.
	*/	
	
	public function navigation($host) { 
	
		$navigate = $this->json->load_json(self::INVENTORY_PATH . self::NAVIGATION);
	
		$hostaddr = $this->getbase(false,true);
	
		if(isset($this->scripturl)) {
			$script_url 	= $this->sanitizer->sanitize($this->scripturl,'alpha');
		}
		if(isset($this->requesturi)) {
			$request_uri 	= $this->sanitizer->sanitize($this->requesturi,'alpha');
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
				if(strtolower($this->sanitizer->cleaninput($n['nav.title'])) == 'index' ) {
					$nav .= '<a href="'.$hostaddr.$shopfolder.'" target="_self">'.$this->sanitizer->cleaninput($n['nav.title']).'</a>' .PHP_EOL;
					} else {
					$nav .= '<a href="'.$hostaddr.$shopfolder.'/'.$this->sanitizer->cleaninput($n['nav.url']).'" target="_self">'.$this->sanitizer->cleaninput($n['nav.title']).'</a>' .PHP_EOL;
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
			
			$category = $this->json->load_json($categories);
			foreach($category as $c) {	
				if($this->revSeo($c['category.title']) == $this->revSeo($cat)) {
					$catno = (int)($c['category.id']-1);
					break;
				}
			}
			
			// subcategories
			$subcategory = $this->json->load_json($subcategories);
			
			foreach($subcategory as $sc) {	
			
				if(($this->revSeo($sc['sub.category.title']) == $this->revSeo($subcat)) && ($sc['sub.category.cat.id'] == $catno)) {
					return (int)($sc['sub.category.cat.id'] -1);
					break;
				}
			}
			
		} elseif(isset($cat)) {
			
			// categories
			$category = $this->json->load_json($categories);
				
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

	public function getoptionbar($cat=false,$subcat=false) {
		
		$options 		= false;
		$products 		= $this->json->load_json("inventory/shop.json");
		$alloptions 	= [];
		
		$i=0;
		
		foreach($products as $opt) {	
		
			$variant1  		= $this->sanitizer->cleaninput($opt['variant.title1']);
			$variant2  		= $this->sanitizer->cleaninput($opt['variant.title2']);
			$variant3  		= $this->sanitizer->cleaninput($opt['variant.title3']);
			$productoptions = $this->sanitizer->cleaninput($opt['product.options']);
			$category		= $this->sanitizer->cleaninput($opt['product.category']);
			$subcategory 	= $this->sanitizer->cleaninput($opt['product.category.sub']);
			
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
	
	public function load_json($url) 
	{
		$url = str_ireplace('.json','',$url);
		$url .= '.json';
		
		$file  = $this->traverse($url,'traverse');

		$json = json_decode($file, true, 9999, JSON_BIGINT_AS_STRING);
		
		if($json !== NULL || $json != false) {
			return $json;
			} else {
			exit;
		} 
	}
	
	public function traverse($string) {
		
		// prepare string by removing all illegal characters.
		$find = ['../','./','%','#','&'];
		$replace = ['','','','',''];
		$string = str_ireplace($find,'',$string);
		
		// test string before processing
		if(stristr(rawurldecode($string),'..') != false) {
			$this->messages->message("Error: illegal characters found in filename.");
			exit;	
		}		
		// filetype must be either json or csv.	
		if(substr(strtolower($string),-5) == '.json' || substr(strtolower($string),-4) == '.csv') {
			} else {
			$this->messages->message("Error: this is not a supported file.");
			exit;	
		}		
		
		// only allow alphanumeric characters, a period and slash.
		$string  = preg_replace('/[^a-zA-Z-0-9.\/]/','', $string);
		// filetype must be either json or csv, after preg_replace.
		if((substr(strtolower($string),-5) == '.json') || (substr(strtolower($string),-4) == '.csv')) {
			$urlstring = $string;
			} else {
			$this->messages->message("Error: this is not a supported file.");
			exit;								
		}

		// a file path must start with either inventory/ or server/config/
		if(substr($urlstring,0,10) == 'inventory/') 
		{
			$url = $urlstring;
			} elseif(substr($urlstring,0,14) == 'server/config/') {
			$url = $urlstring;
			
			} else {
				$this->messages->message("Error: JSON file could not be loaded due to possible directory traversal.");
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
						$subcatselected = $this->sanitizer->sanitize($selected[0],'cat');
					} else {
						$subcatselected = false;
					}						
				}
			}
		
			// get host
			$hostaddr = $this->getbase();
		
			// categories
			$categories = $this->json->load_json($categories);
			
			// subcategories
			$subcategories = $this->json->load_json($subcategories);	
			
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
						$html .= '<li class="ts-shop-'.$cssdirection.'-navigation-cat-selected" onclick="OpenShop.toggle(\''.($c['category.id']-1).'\',\''.$totalcats.'\');" id="cat'.($c['category.id']-1).'"><a href="'.$hostaddr.'category/'.$this->seoUrl($c['category.title']).'/">'.ucfirst($c['category.title']).'</a></li>'.PHP_EOL;
						} else {
						$html .= '<li class="ts-shop-'.$cssdirection.'-navigation-cat" onclick="OpenShop.toggle(\''.($c['category.id']-1).'\',\''.$totalcats.'\');" id="cat'.($c['category.id']-1).'"><a href="'.$hostaddr.'category/'.$this->seoUrl($c['category.title']).'/">'.ucfirst($c['category.title']).'</a></li>'.PHP_EOL;
					}
					
					$catid = (int)$c['category.id'];
					
					$j = 0;
					
					if($totalsubcats >=1) {
						foreach($subcategories as $sc) {	
							if($catid == $sc['sub.category.cat.id']) {
								
								$subcat = '<a href="'.$hostaddr.'subcategory/'.$this->seoUrl($c['category.title']).'/'.$this->seoUrl($sc['sub.category.title']).'/">'.ucfirst($sc['sub.category.title']).'</a>';

								array_push($this->subcategories,$subcat);
								
								if($j == 0) {
									$html .= '<ul class="ts-shop-'.$cssdirection.'-navigation-subcat" id="toggle'.($c['category.id']-1).'">'.PHP_EOL;
								}
								
								if(isset($subcatselected) == isset($sc['sub.category.title'])) {
								$html .= '<li class="ts-shop-'.$cssdirection.'-navigation-subcat-item-selected">'.$subcat.'</li>'.PHP_EOL;
									} else {
								$html .= '<li class="ts-shop-'.$cssdirection.'-navigation-subcat-item">'.$subcat.'</li>'.PHP_EOL;	
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
	public function getproducts($method,$category,$string=false,$limit=false,$page=false,$sorting=false,$sortvalue=false,$token=false) 
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
		
		if(isset($_SESSION['token'])) {
			$token = $_SESSION['token'];
		} else {
			$token = $cryptography->getToken();
			$_SESSION['token'] = $token;
		}
	
		$hostaddr = $this->getbase();
		
		// Loading the shop configuration.
		$shopconf = $this->json->load_json(self::INVENTORY_PATH . self::SHOPCONF);
		$configuration = [];
		
		if($shopconf !== null) {
			foreach($shopconf as $conf) {	
				array_push($configuration,$conf);
			}
		}
		
		// Logic for pagination on products.
		if($limit == false) {
			$siteconf 	= $this->json->load_json(self::INVENTORY_PATH . self::SITECONF);
			$result 	= $this->getasetting($siteconf,'site.maxproducts.visible.in.cat');
			$limit 		= (int) $result;
			$limit_products = $limit;
			} else {
			$limit_products = $limit;
		}
		
		if($page != false) {
			$page_products = $page;
			} else {
			$page_products = 1;
		}
		
		$productlist = $this->json->decode();	

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
					array_push($key,$this->sanitizer->maxstring($this->sanitizer->cleaninput($productlist[$i]['product.id']),10,false));
					array_push($key,$this->sanitizer->maxstring($this->sanitizer->cleaninput($productlist[$i]['product.title']),10,false));
					array_push($key,$this->sanitizer->maxstring($this->sanitizer->cleaninput($productlist[$i]['product.description']),30,true));
					array_push($key,$this->sanitizer->cleaninput($productlist[$i]['product.category']));
					array_push($key,$this->getsitecurrency(self::INVENTORY_PATH . self::SITECONF,self::INVENTORY_PATH . self::CURRENCIES).' '.$this->sanitizer->cleaninput($productlist[$i]['product.price']));
					array_push($key,$this->sanitizer->cleaninput($productlist[$i]['product.stock']));
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
					
					$var1 = $this->sanitizer->cleaninput($c['variant.title1']); 
					$var2  = $this->sanitizer->cleaninput($c['variant.title2']);
					$var3  = $this->sanitizer->cleaninput($c['variant.title3']);
					$find  = $this->sanitizer->sanitize($query,'search');
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
					
					$title = $this->sanitizer->cleaninput($c['product.title']); 
					$desc  = $this->sanitizer->cleaninput($c['product.description']);
					$tags  = $this->sanitizer->cleaninput($c['product.tags']);
					$find  = $this->sanitizer->sanitize($query,'search');
			
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
				$minprice = $this->sanitizer->sanitize((int)$this->minprice,'num');
			}
			
			for($k = $min; $k < count($productlist); $k++) {	
			
				$c = $productlist[$k];
				$productprice = $this->sanitizer->cleaninput($c['product.price']); 
	
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
			if($limit_products  > $amount_products) {
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
		$string_pag .= 'Page '.$page_products.' of '.$pages; 
		
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
					$this->textstring .= '<h3>'.$this->sanitizer->cleaninput($c['product.title']).'</h3>'; 
					$this->textstring .= '<div>'.$this->sanitizer->cleaninput($c['product.description']).'</div>'; 
					$this->textstring .= '<a href="'.$this->sanitizer->cleaninput($c['product.url']).'"><img src="'.$hostaddr.'/'.$this->sanitizer->cleaninput($c['product.featured.image']).'" class="ts-product-image"/></a></div>';
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
				
					$this->sanitizer->cleaninput($c['product.title']);
				if($postsearch != false) {
						array_push($ts,$c);
				}
			}
		
			// flip array order, as most products are added sequentially...
			$ts = array_reverse($ts);	
			
			// sorting of products.
			if($sorting == true) {
				// sort array
				if(isset($sortvalue)) {

					$pieces = explode(':',$sortvalue,2);
					
					if(isset($pieces)) {
						
						switch(strtolower(trim($pieces[0]))) {
							case 'price':
							$sort  = array_column($ts, 'product.price');
							break;
							case 'title':
							$sort  = array_column($ts, 'product.title');
							break;
						}
						
						if(isset($sort)) {
							
							switch(strtolower(trim($pieces[1]))) {
								
								case 'ascending':
								array_multisort($sort, SORT_DESC, $ts);
								break;
								case 'descending':
								case 'decending':
								array_multisort($sort, SORT_ASC, $ts);
								break;
							}
						}
						
					} else {}
				}
			}
			
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
						$status = ''; // low stock
						} else {
						$status = '';
					}
					
					if(isset($ts[$i]['product.image']) != "") {
						$productimage = '<div class="ts-product-image-div"><img src="'.$hostaddr.$this->sanitizer->cleaninput($ts[$i]['product.image']).'" class="ts-product-image"/></div>';
						} else {
						$productimage = '<div class="ts-product-image-icon">&#128722;</div>';
					}				
					
					switch($method) {
						
						case 'list':

						if(isset($ts[$i]['product.description'])) {

							$this->textstring .= "<div class=\"ts-product-list\">";
							$this->textstring .= $productimage;
							$this->textstring .= "<div class=\"ts-list-product-link\"><a href=\"".$this->getbase()."category/".$this->seoUrl($this->sanitizer->cleaninput($ts[$i]['product.category']))."/item/".$this->seoUrl($this->sanitizer->cleaninput($ts[$i]['product.category'])).'/'.$this->seoUrl($this->sanitizer->cleaninput($ts[$i]['product.title'])).'/'.$this->sanitizer->cleaninput($ts[$i]['product.id'])."/".(int)$this->page_id."/\">".$this->sanitizer->maxstring($this->sanitizer->cleaninput($ts[$i]['product.title']),10,false)."</a> </div>";
							$this->textstring .= "<div class=\"ts-list-product-desc\">".$this->sanitizer->maxstring($this->sanitizer->cleaninput($ts[$i]['product.description']),30,true)."</div>";
							
							// $this->textstring .= "<div class=\"ts-list-product-cat\">".$this->sanitizer->cleaninput($ts[$i]['product.category'])."</div>";
							$this->textstring .= "<div class=\"ts-list-product-price\">".$this->getsitecurrency(self::INVENTORY_PATH . self::SITECONF,self::INVENTORY_PATH . self::CURRENCIES).' '.$this->sanitizer->cleaninput($ts[$i]['product.price'])."</div>";
							$this->textstring .= "<div class=\"ts-list-product-status\">".$this->sanitizer->cleaninput($ts[$i]['product.stock'])." left in stock.</div>";
							
							if(isset($configuration[0]['products.quick.cart']) == 'yes') {
								$this->textstring .= "<div><input type='number' name='qty' size='1' value='1' min='1' max='9999' id='ts-group-cart-qty-".($i+1).'-'.(int)$ts[$i]['product.id']."'><input type='button' onclick='OpenShop.addtocart(\"".(int)$ts[$i]['product.id']."\",\"ts-group-cart-qty-".($i+1).'-'.(int)$ts[$i]['product.id']."\",\"".$token."\",\"".$hostaddr."\");' class='ts-list-cart-button' name='add_cart' value='".$this->sanitizer->cleaninput($configuration[0]['products.cart.button'])."' /></div>";
								} else {
								$this->textstring .= "<div class='ts-list-view-link'><a href=\"product/".$this->sanitizer->cleaninput($ts[$i]['product.id'])."/\">view</a></div>";
							}
							
							$this->textstring .= "</div>";
						} 
		
						break;

						case 'group':		
						
						$this->textstring .= "<div class=\"ts-product-group\">";
						$this->textstring .= $productimage;
						$this->textstring .= "<div class=\"ts-group-product-link\"><a href=\"item/".$this->seoUrl($this->sanitizer->cleaninput($ts[$i]['product.category'])).'/'.$this->seoUrl($this->sanitizer->cleaninput($ts[$i]['product.title'])).'/'.$this->sanitizer->cleaninput($ts[$i]['product.id'])."/\">".$this->sanitizer->cleaninput($ts[$i]['product.title'])."</a> </div>";
						$this->textstring .= "<div class=\"ts-group-product-desc\">".$this->sanitizer->cleaninput($ts[$i]['product.description'])."</div>";
						$this->textstring .= "<div class=\"ts-group-product-price\">".$this->getsitecurrency(self::INVENTORY_PATH . self::SITECONF,self::INVENTORY_PATH . self::CURRENCIES).' '.$this->sanitizer->cleaninput($ts[$i]['product.price'])."</div>";
						// $this->textstring .= "<div class=\"ts-group-product-cat\">".$this->sanitizer->cleaninput($ts[$i]['product.category'])."</div>";
						$this->textstring .= "<div class=\"ts-group-product-status\">".$this->sanitizer->cleaninput($ts[$i]['product.stock'])." left in stock.</div>";
						
						if(isset($configuration[0]['products.quick.cart']) == 'yes') {
							
							$this->textstring .= "<div><input type='number' name='qty' size='1' min='1' max='9999' value='1' id='ts-group-cart-qty-".($i+1).'-'.(int)$ts[$i]['product.id']."'><input type='button' onclick='OpenShop.addtocart(\"".(int)$ts[$i]['product.id']."\",\"ts-group-cart-qty-".($i+1).'-'.(int)$ts[$i]['product.id']."\",\"".$token."\",\"".$host."\");' class='ts-group-cart-button' name='add_cart' value='".$this->sanitizer->cleaninput($configuration[0]['products.cart.button'])."' /></div>";
							} else {
							$this->textstring .= "<div class='ts-group-view-link'><a href=\"product/".$this->sanitizer->cleaninput($ts[$i]['product.id'])."/\">view</a></div>";
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
		
		$cart = $this->json->load_json($json);
		
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
			
			$cryptography = new Cryptography();
			
			if(stristr($variant,',')) {
								
					if(stristr($variant,',')) {
									
						//$optionbox = '<select name="'.$this->sanitizer->sanitize($box,'option').'" id="'.$this->sanitizer->sanitize($box,'option').'" onchange="javascript:OpenShop.updateprices(\''.$productId.'\',\'price-update\',\''.$cryptography->getToken().'\',\''.$this->getbase().'cart/addtocart/\',\''.$this->sanitizer->sanitize($box,'option').'\',\''.$box.'\');">';
						$optionbox = '<select name="'.$this->sanitizer->sanitize($box,'option').'" id="'.$this->sanitizer->sanitize($box,'option').'">';
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
									$optionbox .= '<option value="'.$this->sanitizer->sanitize($opts[$i],'option').'">'.$this->sanitizer->sanitize($opts[$i],'option').' -> price: '.$this->sanitizer->sanitize($optsprices[$i],'num').'</option>';
									} else {
									$optionbox .= '<option value="'.$this->sanitizer->sanitize($opts[$i],'option').'">'.$this->sanitizer->sanitize($opts[$i],'option').'</option>';
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
		
		$shopconf = $this->json->load_json($json);

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
								$html .= "<option value=\"".$this->sanitizer->sanitize($value,'option')."\">".$this->sanitizer->sanitize($value,'unicode')."</option>";
									foreach($subcategory as $subrow)
									{
										$maincat = $row['category.id'];

										if($subrow['sub.category.cat.id'] == $maincat) {
												
											if($subrow['sub.category.title'] !='' || $subrow['sub.category.title'] != null) {

												$html .= "<option value=\"".$this->sanitizer->sanitize($value,'option').'/'.$this->sanitizer->sanitize($subrow['sub.category.title'],'option')."\"> - ".$this->sanitizer->sanitize($subrow['sub.category.title'],'unicode')."</option>";
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
								$html .= "<option value=\"".$this->sanitizer->sanitize($value,'option')."\">".$this->sanitizer->sanitize($value,'option')."</option>";
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
								$html .= "<option value=\"".$this->sanitizer->sanitize($value,'option')."\">".$this->sanitizer->sanitize($value,'option')."</option>";
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
		$siteconf 		= $this->json->load_json($json);
		
		$result 		= $this->getasetting($siteconf,'site.url');
		$result_path 	= $this->getasetting($siteconf,'site.canonical');   

		$find 		= ['http://','https://','www.','/'];
		$replace 	= ['','','',''];
		
		$home  		= 'https://';
		if(isset($result['site.url']) != null) {
			$home 	   .= str_replace($find,$replace,$result['site.url']);
		}
		
		if($shoppath == true) {
			if(isset($result_path['site.canonical']) != null) {
				$home  .= '/' . $result_path['site.canonical'] . '/';
			}
		}
		
		return $home;
	}

	public function getasetting($json,$akey) 

	{
		foreach($json[0] as $key => $value)
		{
			if(!is_array($value)) {
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
					$html.= "<option value=\"".$this->sanitizer->sanitize($value,'option')."\">".$this->sanitizer->sanitize($value,'option')."</option>";			
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
						$html.= "<option value=\"".$this->sanitizer->sanitize($key,'option')."\" disabled>".str_replace('shipping.','',$this->sanitizer->cleaninput($key))."</option>";
						} else {
						$html.= "<option value=\"".$this->sanitizer->sanitize($key,'option')."\">".str_replace('shipping.','',$this->sanitizer->cleaninput($key))."</option>";
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
		$currencies = $this->json->load_json(self::CURRENCIES);
		
			if($currencies !== null) {
				$i=0;
				foreach($currencies[0] as $key => $value)
				{
					if($disallowed != false && (strtolower($value) == strtolower($disallowed))) {
						$html .= "<option value=\"".$this->sanitizer->sanitize($key,'num')."\" disabled>".$this->sanitizer->cleaninput($currencies[0][$i]['sign'])."</option>";
						} else {
						$html .= "<option value=\"".$this->sanitizer->sanitize($key,'num')."\">".$this->sanitizer->cleaninput($currencies[0][$i]['sign'])."</option>";
					}
					$i++;
				}		
			}
			
		return $html;
	}
	
	public function getcountries() 
	{
		$html = "";
		
		$shipping = $this->json->load_json(self::INVENTORY_PATH . self::SHIPPING);
		
			if($shipping !== null) {
				$i=0;
				foreach($shipping[0] as $key => $value)
				{
					$html .= '<div class=\"ts-country-list-option\">' . $this->sanitizer->cleaninput($key) .": <input type=\"text\" name=\"".$key."\" value=\"".$value."\" size=\"20\" /></div>";
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
			$siteconf = $this->json->load_json(self::INVENTORY_PATH . self::SITECONF);
			} else {
			$siteconf = $this->json->load_json($conf);
		}
		
		if(!isset($currency)) {
			$currencies = $this->json->load_json(self::INVENTORY_PATH . self::CURRENCIES);
			} else {
			$currencies = $this->json->load_json($currency);
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
}

?>