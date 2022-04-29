<?php

	include("../../resources/PHP/Header.inc.php");
	include("../../resources/PHP/Class.Session.php");
	include("../../resources/PHP/Class.SecureMail.php");
	include("../../resources/PHP/Class.Shop.php");
	
	$shop = new Shop();
	$hostaddr = $shop->getbase();

	$session = new Session();
	
	$session->sessioncheck();
	
	if(isset($_SESSION['token'])) {
		
		$token = $_SESSION['token'];
		
			if($token != $_GET['token']) {
				$shop->message('Transaction completed, however token is incorrect. Please contact the shop owner if issues arrive through either e-mail or the contact form. N.B. The shopowner has not been notified of this error.');
				$shop->showmessage();
				exit;
			}
	
		} else {
			
		$shop->message('Transaction completed, however token is incomplete. Please contact the shop owner if issues arrive through either e-mail or the contact form. N.B. The shopowner has not been notified of this error.');
		$shop->showmessage();
		exit;
	}

	/*
		$item_name = $_POST['item_name'];
		$item_number = $_POST['item_number'];
		$payment_status = $_POST['payment_status'];
		$payment_amount = $_POST['mc_gross'];
		$payment_currency = $_POST['mc_currency'];
		$txn_id = $_POST['txn_id'];
		$receiver_email = $_POST['receiver_email'];
		$payer_email = $_POST['payer_email'];
	*/

	if(isset($_REQUEST['invoice'])) {
		$paypalinvoice = (int)$_REQUEST['invoice'];
		} else {
		$paypalinvoice = null;
	}
	
	$dir = 	'../../server/config/orders.conf.json';
	
	$invoiceid = $shop->invoiceid($dir,'get');
	
	if($paypalinvoice != $invoiceid) {

		// different invoice ID, check for race condition.
				// probable race condition.
				$invoicediff = ($invoiceid - $_SESSION['invoiceid']);

				if($invoicediff == 1) {
					$shop->invoiceid($dir,'set',$invoiceid+1);
					} elseif($invoicediff > 1) {
					// certainly race condition.
					// mail shop owner here
					} else {
					$shop->invoiceid($dir,'set',$invoiceid+1);
				}
	
	} else {

		$shop->invoiceid('set',$invoiceid+1);
	}

	$sitecurrency = $shop->getsitecurrency('../../server/config/site.conf.json','../../server/config/currencies.conf.json');
	$shippingcountry = $shop->sanitize($_SESSION['shipping_country'],'encode');
	$siteconf = $shop->load_json("../../server/config/shipping.conf.json");
	$countryprice = $shop->getcountryprice($siteconf,$shippingcountry);
	
	if($countryprice != false) {
		$country_price = (int)$countryprice;
		} else {
		$country_price = 10; // default shipping fee.
	}
	
	// mail to shopowner.

	$setup = new \security\forms\SecureMail();

	$siteconf = $shop->load_json("../../server/config/site.conf.json");
	$result = $shop->getasetting($siteconf,'site.email');

	if($result["site.email"] != '') {
		if(strlen($result["site.email"]) > 64) {
			$email = $shop->decrypt($result["site.email"]);
			} else {
			$email = $shop->sanitize($result["site.email"],'email');
		}
	}
	
	$siteconf = $shop->load_json("../../server/config/site.conf.json");
	$result = $shop->getasetting($siteconf,'site.title');
	
	if($result["site.title"] != '') {
		if(strlen($result["site.title"]) > 10) {
			$shopname = $shop->sanitize($result["site.title"],'unicode');
			} else {
			$shopname = 'Webshop owner';
		}
	}

		$body  = "Today, a new order was placed in the webshop and paid. Below are the details of the order.".PHP_EOL . PHP_EOL;
		$body .= "### ORDER ###".PHP_EOL;
		
		$body .= '<html>';
		$body .= '<head>';
		// Might not load 3rd resources, may have to include static CSS.
		$body .= '<link rel="stylesheet" type="text/css" href="'.$hostaddr.'resources/style/css.css">';
		$body .= '<link rel="stylesheet" type="text/css" href="'.$hostaddr.'resources/style/style.css">';
		$body .= '</head>';
		$body .= '<body>';
		$body .= '<hr />';
	
		if(isset($_SESSION['cart']) && count($_SESSION['cart']) >= 1) {
			
			$products = $shop->getproductlist("../../inventory/shop.json");
			$productsum_total = 0;
			$productsum = 0;
			
			$c = count($_SESSION['cart']);
			
			for($i=0; $i < $c; $i++) {
				
				if($_SESSION['cart'][$i]) {
					$product = (int) $_SESSION['cart'][$i]['product.id'];
					if($_SESSION['cart'][$i]['product.qty'] == 0) {
						$_SESSION['cart'][$i]['product.qty'] = 1;
					}
					$productqty = $_SESSION['cart'][$i]['product.qty'];
				}
				
				$j = 0;

				if(isset($product)) {
					
					$variants = $_SESSION['cart'][$i]['product.variants'];
					
					$vc = count($variants);
					
					if(isset($variants)) {
						
						$variant = '';
						
						if($vc >=1) {
							for($j=0;$j<$vc;$j++) {
								$variant .= $variants[$j];
								if($j < ($vc-1)) {
								$variant .= ', ';
								}
							}
						} 
					}	
				
					foreach($products as $key => $value) {
						
						
						if(isset($products[$j]) && $products[$j][0][1] == $product) {
							
						for($k=0;$k<count($value); $k++) {
							if($value[$k][0] == 'shipping.price') {
								$productshipping = $value[$k][1];	
							} else {
							$productshipping = 0;
							}

							if($value[$k][0] == 'product.price') {
								$productprice = (int) $value[$k][1];	
							}
							
							if($value[$k][0] == 'variant.option1') {
								
								if(stristr($value[$k][1],',')) {
									$arr = explode(',',$value[$k][1]);
									// TODO: if multiple variants, check the first box with a price value and display it as some variants may not have a price value (as it is unwise to do so).
									if(isset($_SESSION['cart'][$i]['product.variants'][0])) {
										$key = array_search($_SESSION['cart'][$i]['product.variants'][0],$arr);
									} else { $key =0; }
								}
							}

							if($key >=0) {
									if($value[$k][0] == 'variant.price1') {
										if(stristr($value[$k][1],',')) {
											$values = explode(',',$value[$k][1]);
											$productprice = trim($values[$key]);		
										}
									}
							}
								
							if($value[$k][0] == 'product.description') {
								$productdesc = $shop->sanitize($value[$k][1],'encode');	
							}

							if($value[$k][0] == 'product.title') {
								$producttitle = $shop->sanitize($value[$k][1],'encode');	
							}

						}
						
						if($productprice == null || $productprice == 0 ) {
							$productprice = 1;
						}
						
						if($productqty == null || $productqty == 0 ) {
							$productqty = 1;
						}				
									
							$productsum = round(($productprice * (int)$productqty),2);
							
							$qtyid = 'tscart-'.$j.$product;

							$body .= '<div class="ts-shop-ul">';
							$body .= '<li class="ts-shop-ul-li-item-product">'.$producttitle.'</li>';
							$body .= '<li class="ts-shop-ul-li-item-description">'.$productdesc.'</li>';
							$body .= '<li class="ts-shop-ul-li-item-qty">'.$variant.'</li>';
							$body .= '<li class="ts-shop-ul-li-item-price">'.$sitecurrency .' '.$productprice.'</li>';
							$body .= '<li class="ts-shop-ul-li-item-qty">'.$productqty.'</li>';
							$body .= '<li class="ts-shop-ul-li-item-total">'.$sitecurrency .' '. $productsum.'</li>';
							$body .= '</div>';
						}
						
					$j++;
				}
			}
			
				$body .= '<div class="ts-shop-ul-set">';
				$body .= '<div class="ts-shop-ul">';
				$body .= '<li class="ts-shop-ul-li-item" width="10%"></li>';
				$body .= '<li class="ts-shop-ul-li-item" width="10%">Country</li>';
				$body .= '<li class="ts-shop-ul-li-item" width="30%">Subtotal</li>';
				$body .= '<li class="ts-shop-ul-li-item" width="35%">Shipping &amp; handling</li>';
				$body .= '<li class="ts-shop-ul-li-item" width="2%">Carbon offset</li>';
				$body .= '<li class="ts-shop-ul-li-item" width="15%">Total</li>';
				$body .= '</div>';
						
				$body .= '<li class="ts-shop-ul-li-item">';
				$body .= '</li>';
								
				$body .= '<li class="ts-shop-ul-li-item">';
				$body .= str_replace('shipping.','',$shippingcountry);
				$body .= '</li>';
				$body .= '<li class="ts-shop-ul-li-item">';
				$body .= $sitecurrency .' '. (int) $_SESSION['subtotal'];
				$body .= '</li>';
				$body .= '<li class="ts-shop-ul-li-item">';
				$body .=  $sitecurrency .' '. (int) $_SESSION['shipping'];
				$body .= '</li>';
				$body .= '<li class="ts-shop-ul-li-item">';
				if(isset($_SESSION['carbonoffset'])) {
				$body .=  $sitecurrency .' '. (float)$_SESSION['carbonoffset'];
				} else {
					$body .= '0';
				}
				$body .= '</li>';				
				$body .= '<li class="ts-shop-ul-li-item">';
				$body .= $sitecurrency .' '. (int) $_SESSION['totalprice'];
				$body .= '</li>';
				$body .= '</div>';
		}
	
	$body .= '<hr />';
	$body .= '</body>';
	$body .= '</html>';
	
	}
	
	$parameters = array( 
		'to' => $email,
		'name' => $shopname,
		'email' => $email,				
		'subject' => "A new order was placed in the shop today.",
		'body' => $body
	);

	$ordermail = new \security\forms\SecureMail($parameters);
	$ordermail->sendmail();

	// destroy cart session.
	/*
		$_SESSION['cart']  = array();
		$_SESSION['token'] = null;
		$_SESSION['messages'] = array();
		session_destroy();
	*/
?>
