<?php

class GetProducts {
	
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
	* Returns a product list, by reading shop.json.
	* @param method: list|group.	
	* @param string: custom html can be added.
	* @param category: select shop category, if none is given it will list all products.
	* @return $string, html or array (if method is requested.)
	*/		
	public function load($method,$category,$string=false,$limit=false,$page=false,$token=false) 
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
}

?>