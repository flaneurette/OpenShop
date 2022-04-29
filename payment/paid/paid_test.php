<?php

	include("../../resources/PHP/Header.inc.php");
	include("../../resources/PHP/Class.Session.php");
	include("../../resources/PHP/Class.SecureMail.php");
	include("../../Class.Shop.php");
	
	$shop = new Shop();
	
	$hostaddr = $shop->getbase();
	
		$item_name = 'Diamond ring';
		$item_number = '1234567890';
		$payment_status = 'Paid';
		$payment_amount = '2837';
		$payment_currency = 'EUR';
		$txn_id = '823005681045308HGSX2389NSHGD';

		// edit this to test the mail function:

		$receiver_email = 'you@domain.tld';
		$payer_email = 'buyer@gmail.com';
		$email = 'admin@yourdomain.tld';

	$paypalinvoice = 1223;
	
	$dir = 	'../../server/config/orders.conf.json';
	
	$invoiceid = $shop->invoiceid($dir,'get');
	
	$shop->invoiceid('set',$invoiceid+1);

	$sitecurrency = $shop->getsitecurrency('../../server/config/site.conf.json','../../server/config/currencies.conf.json');
	$shippingcountry = $shop->sanitize('Germany','encode');
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

	$body  = "Today, a new order was placed in the webshop and paid. Below are the details of the order." . PHP_EOL . PHP_EOL;
	$body .= "### ORDER ###" . PHP_EOL;
	
	if(isset($email)) {
		
		$products = $shop->getproductlist("../../inventory/shop.json");
		$productsum_total = 0;
		$productsum = 0;
		
		$c = 1;
		
		$product = 1000010;
		$productqty = 3;
		
		$body .= '<html>';
		$body .= '<head>';
		$body .= '<link rel="stylesheet" type="text/css" href="'.$hostaddr.'resources/style/css.css">';
		$body .= '<link rel="stylesheet" type="text/css" href="'.$hostaddr.'resources/style/style.css">';
		$body .= '</head>';
		$body .= '<body>';
		$body .= '<hr />';
		
		for($i=0; $i < $c; $i++) {
			
			$j = 0;

			if(isset($product)) {
			
				foreach($products as $key => $value) {
					
					if($products[$j][0][1] == $product) {
						
						$producttitle = $products[$j][2][1];
						$productdesc  = $products[$j][3][1];
						$productprice = $products[$j][18][1];
						
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
		$body .= '<li class="ts-shop-ul-li-item" width="15%">Total</li>';
		$body .= '</div>';
				
		$body .= '<li class="ts-shop-ul-li-item">';
		$body .= '</li>';
						
		$body .= '<li class="ts-shop-ul-li-item">';
		$body .= str_replace('shipping.','',$shippingcountry);
		$body .= '</li>';
		$body .= '<li class="ts-shop-ul-li-item">';
		$body .= $sitecurrency .' 100 ';
		$body .= '</li>';
		$body .= '<li class="ts-shop-ul-li-item">';
		$body .=  $sitecurrency .' 10';
		$body .= '</li>';
		$body .= '<li class="ts-shop-ul-li-item">';
		$body .= $sitecurrency .' 110';
		$body .= '</li>';
		$body .= '</div>';
	}
	
	
	}
	
	$body .= '<hr />';
	$body .= '</body>';
	$body .= '</html>';
	
	$parameters = array( 
		'html_mail' => true, 
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
