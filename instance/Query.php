<?php

include("../resources/PHP/Header.inc.php");
include("../resources/PHP/Class.Session.php");
include("../core/Sanitize.php");

$session 	= new Session;
$sanitizer 	= new Sanitizer;

if(isset($shop)) {
	$host = $shop->gethost("../server/config/site.conf.json");
	} else {
	require("../resources/PHP/Class.Shop.php");
	$shop  = new Shop;
	$host 		= $shop->gethost("../server/config/site.conf.json");
	$host_path 	= $shop->gethost("../server/config/site.conf.json",true);
}

if(isset($_POST['token']))  {
	// A token was provided through $_POST data. Check if it is the same as our session token.
	if($_POST['token'] == $_SESSION['token']) {
		// token is correct.
		} else {
			echo 'token is incorrect';
		exit;
	}
} else {
	echo 'A token was not given, aborting code execution.';
	exit;
}

if(isset($_POST['qty'])) {
	if(is_int($_POST['qty'])) {
		if($_POST['qty'] > $session::MAXQTY) {
			$qty = 1;
		}
	} else {
		$qty = (int)$_POST['qty'];
	}
}
			
$default = null;

if(isset($_GET['action'])) {
	
	$action = $_GET['action'];
	
	if(preg_match("/[a-zA-Z]/i",$action)) {
			
		$action = $sanitizer->sanitize($action,'alpha');
		
		switch($action) {
			
				case 'prepayment':
				
				if(!isset($_SESSION['email'])) {
					$_SESSION['email'] = $sanitizer->sanitize(base64_decode($_POST['email']),'email');
				}
				
				echo "1";
				
				break;

				case 'cancel':
				
				echo "Checkout has been cancelled.";
				
				$siteconf_admin = $shop->load_json("../server/config/site.conf.json");
				$result_admin = $shop->getasetting($siteconf_admin,'site.email');
				$result_title = $shop->getasetting($siteconf_admin,'site.title');
				$result_domain = $shop->getasetting($siteconf_admin,'site.domain');
			
				$shopname 	= $sanitizer->sanitize($result_title["site.title"],'unicode');
				$shopdomain = $result_domain["site.domain"];
				
				// site.email is also used as 'from' e-mail address, unless you change it...

				if($result_admin["site.email"] != '') {
					if(strlen($result_admin["site.email"]) > 64) {
						$email_from = $shop->decrypt($result_admin["site.email"]);
						} else {
						$email_from = $sanitizer->sanitize($result_admin["site.email"],'email');
					}
				}

				// check if there needs to be a notification.
				$siteconf 	= $shop->load_json("../server/config/payment.conf.json");
				$result 	= $shop->getasetting($siteconf,'payment.email.oncancel');
				$oncancel 	= (int) $result["payment.email.oncancel"];

				if($oncancel == 1 || strtolower($oncancel) =='yes') {
					// email cancellation message.
					$siteconf 	= $shop->load_json("../server/config/payment.conf.json");
					$result 	= $shop->getasetting($siteconf,'payment.cancel.text');
					$oncanceltext 	= $result["payment.cancel.text"];
					// send e-mail.

					if(isset($_SESSION['email'])) {
						
						$email = $sanitizer->sanitize($_SESSION['email'],'email');
						
							require("../resources/PHP/Class.SecureMail.php");
							
							$parameters = array( 
								'to' => $email,
								'name' => $shopname,
								'email' => $email_from,
								'from' => $email_from,
								'domain' => $shopdomain,								
								'subject' => "Your order was cancelled.",
								'body' => $oncanceltext
							);
							
							$ordermail = new \security\forms\SecureMail($parameters);
							$ordermail->sendmail();
					}
				}
				
				// redirect to cart
				header('Location: '.$host, true, 302);
				exit;
				break;
				
				case 'payed':
				case 'paid':
				
				// update stock here.
				header('Location: '.$host.'/payment/paid/index.php?token='.$sanitizer->sanitize($_SESSION['token'],'alphanum'), true, 302);
				exit;
				
				break;	
				
				case 'ipn':
				echo "Checkout process has been notified.";
				break;
		}
	}	
}

if(isset($_POST['action'])) {
	
	$action = $_POST['action'];
	
	if(preg_match("/[a-zA-Z]/i",$action)) {
		
		$action = $sanitizer->sanitize($action,'alpha');
		
		switch($action) {
			
			case 'addtocart':
			
				$id  = (int)$sanitizer->sanitize($_POST['id'],'num');
				$qty = (int)$sanitizer->sanitize($_POST['qty'],'num');
				
				$variants = [];
				
				if(isset($_POST['vrs1'])) {
					if($_POST['vrs1'] != 'undefined') {
						$vrs1 = $sanitizer->sanitize($_POST['vrs1'],'alphanum');
						array_push($variants,$vrs1);
					} else {}
				}

				if(isset($_POST['vrs2'])) {	
					if($_POST['vrs2'] != 'undefined') {
						$vrs2 = $sanitizer->sanitize($_POST['vrs2'],'alphanum');
						array_push($variants,$vrs2);
					} else {}
				}

				if(isset($_POST['vrs3'])) {
					if($_POST['vrs3'] != 'undefined') {
						$vrs3 = $sanitizer->sanitize($_POST['vrs3'],'alphanum');
						array_push($variants,$vrs3);
					} else {}
				}
				
				$arr = [
						'product.id' => $id,
						'product.qty' => $qty,
						'product.variants' => $variants
				];

				$session->addtocart($arr);

				$_SESSION['cart'] = $session->unique_array($_SESSION['cart'], 'product.id');
				echo "Product added to cart. ";
				echo PHP_EOL;
				echo "X";
			
			break;			
			
			case 'updatecartprice':

				// TODO SEP 2021: copy over this logic into cart, and recalculate.

				if(isset($_POST['id'])) {	
					if($_POST['id'] != 'undefined') {
						$divid  = (int)$sanitizer->sanitize($_POST['id'],'unicode');
					}
				}
				
				if(isset($_POST['productid'])) {	
					if($_POST['productid'] != 'undefined') {
						$id  = (int)$sanitizer->sanitize($_POST['productid'],'num');
					}
				}		
				
				if(isset($_POST['box'])) {	
					if($_POST['box'] != false) {
						$variantselected = strtolower($sanitizer->sanitize($_POST['box'],'alphanum')); 
						} else {
						$variantselected = 'variant1';
					}
				}	else {
					$variantselected = 'variant1';
					$variantvalueselected = 'variant.price1';
				}
				
				switch($variantselected) {
					
					case 'variant1':
					$variantselected = 'variant.option1';
					$variantvalueselected = 'variant.price1';
					break;
					case 'variant2':
					$variantselected = 'variant.option2';
					$variantvalueselected = 'variant.price2';
					break;	
					case 'variant3':
					$variantselected = 'variant.option3';
					$variantvalueselected = 'variant.price3';
					break;		
					default:
					$variantselected = 'variant.option1';
					$variantvalueselected = 'variant.price1';
					break;					
				}

				$product_list = $shop->decode('../inventory/shop.json');

				if($product_list !== null) {
					
					$iv = [];
					$i = 0;
					
					foreach($product_list as $c) {	
					
						array_push($iv,$c);
						
						if($iv[$i]['product.id'] == $id) {

							if($iv[$i]['product.status'] == '1') {
								if($_POST['bid']) { 
									$bid = $sanitizer->sanitize(base64_decode($_POST['bid']),'alphanum');
									if($bid) {
										$key = array_search($bid,explode(',',$iv[$i][$variantselected]));
									}
								}
								
								$currency = $shop->getsitecurrency('../server/config/site.conf.json','../server/config/currencies.conf.json');
								
								if(stristr($iv[$i][$variantvalueselected],',')) {
									$values = explode(',',$iv[$i][$variantvalueselected]);
									echo $currency.' '.trim($values[$key]);	
									} else {
									echo $currency.' '.$iv[$i]['product.price'];
								}
								break;
							}
						}
						$i++;
					}
				}	
			break;
			
			
			case 'deletefromcart':
			
				$id = (int)$sanitizer->sanitize($_POST['id'],'num');
				
				if($id) {
					$_SESSION['cart'] = $session->deletefromcart($id);
				}
				
				$_SESSION['cart'] = array_values($_SESSION['cart']);
				
				echo "Product deleted from cart.";
			
			break;
			
			case 'updatecart':
			
				$id = (int)$sanitizer->sanitize($_POST['id'],'num');
				$qty = (int)$sanitizer->sanitize($_POST['qty'],'num');
				
				if($id) {
					$_SESSION['cart'] = $session->updatecart($id,$qty);
				}

				$_SESSION['cart'] = array_values($_SESSION['cart']);
				echo "Cart has been updated.";
			
			break;			
			
			case 'emptycart':
				
				$cartid = (int)$sanitizer->sanitize($_POST['cartid'],'num');
				$_SESSION['cart'] = [];
				echo "Cart was emptied.";
				
			break;			
			
			case 'voucher':
				$code = $sanitizer->sanitize($_POST['code'],'alpha');
			break;
			
			case 'wishlist':
				$product = $sanitizer->sanitize($_POST['product'],'alpha'); 
				$tr 	 = $sanitizer->sanitize($_POST['tr'],'alpha'); 
			break;	
			
			case 'query':
			
			break;
		}
	
	} else {
	// contains other characters.
	echo $default;
	}
} else {
	echo $default;
}
?>