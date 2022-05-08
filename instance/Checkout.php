<?php

	include("../resources/PHP/Header.inc.php");
	include("../resources/PHP/Class.Session.php");
	include("../resources/PHP/Class.Shop.php");
	include("../core/Cryptography.php");
	include_once("../core/Meta.php");
	
	$cryptography = new Cryptography();
	
	$shop 			= new Shop;
	$session 		= new Session;
	$sanitizer 	  	= new Sanitizer;
	$metafactory    = new Meta;
	
	$session->sessioncheck();
	$addedvalue = false;
	
	if(!empty($_GET)) {	
		$messages->message('Checkout page cannot be accessed this way.');
		$messages->showmessage();
		exit;
	}
	
	if(isset($_SESSION['token'])) {
		
		$token = $_SESSION['token'];
		
			if($token != $_POST['token']) {
				$messages->message('Token is incorrect.');
				$messages->showmessage();
				exit;
			}
	
		} else {
			
		$messages->message('Token is incorrect or not set.');
		$messages->showmessage();
		exit;
	}
		
	if(!isset($_POST['checkout-post'])) {	
		$messages->message('Checkout page could not be loaded from resource and cannot be accessed this way.');
		$messages->showmessage();
		exit;
	}
	
	/* Get the currency of site.json
	*  To change the default currency, edit site.json which has a numeric value that corresponds to the values inside currencies.json.
	*  DO NOT edit currencies.json, unless adding a new currency, as this file is used throughout OpenShop and might break functionality.
	*/
	
	$sitecurrency = $shop->getsitecurrency('../server/config/site.conf.json','../server/config/currencies.conf.json');
	
	// echo $shop->debug($_POST);
	
	if(isset($_POST['payment_gateway'])) {
		$payment_gateway = $sanitizer->sanitize($_POST['payment_gateway'],'encode');
		} else {
		$payment_gateway = 'PayPal';
		$messages->message('Payment Gateway not selected, assuming and defaulting to PayPal');
		$messages->showmessage();
	}
	
	if(isset($_POST['shipping_country'])) {
		$shippingcountry = $sanitizer->sanitize($_POST['shipping_country'],'encode');
		} else {
		$messages->message('Country not selected, cannot continue to checkout!');
		$messages->showmessage();
		exit;
	}
	
	if(isset($_POST['cooffset'])) {
		$addedvalue = true;
		$carbonvalue = (float)$_POST['cooffset'];
		$_SESSION['carbonoffset'] = $carbonvalue;
	}

	$gateway = $sanitizer->sanitize($payment_gateway,'alphanum');
				
?>
<!DOCTYPE html>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=0.73">
	<?php
	echo $metafactory->getmeta();				
	?>
	</head>
<body>

<?php
include("../resources/PHP/Header.php");
?>

<div id="wrapper">

		<div id="ts-shop-result-message" onclick="OpenShop.togglecartmsg('close');"  onmouseover="OpenShop.togglecartmsg('close');"></div>
		<div id="ts-shop-cart-form">
<h2>Checkout</h2>

	<?php 
		
		if(isset($_SESSION['cart']) && count($_SESSION['cart']) >= 1) {
		$c = count($_SESSION['cart']);
		
		if(($c > 0) && ($c < 9999) ) {
	?>
		<form name="ts_cart" method="post" action="<?php echo $host;?>payment/paypal/Checkout.php" id="ts-shop-cart-form-data">
		<input type="hidden" name="token" value="<?php echo $token;?>">
		<input type="hidden" name="checkout-post-gateway" value="1">
		<hr />
		<div class="ts-shop-ul-set">
		<div class="ts-shop-ul">
				<li class="ts-shop-ul-li-item-icon" width="2%">&#128722;</li>
				<li class="ts-shop-ul-li-item-product" width="35%">Product Name</li>
				<li class="ts-shop-ul-li-item-description" width="35%">Description</li>
				<li class="ts-shop-ul-li-item" width="5%">Variants</li>
				<li class="ts-shop-ul-li-item-price" width="10%">Price</li>
				<li class="ts-shop-ul-li-item-qty" width="5%">Qty</li>
				<li class="ts-shop-ul-li-item-qty" width="2%">Tax</li>
				<li class="ts-shop-ul-li-item-total" width="16%">Total</li>
		</div>
			
	<?php
			
		$products = $shop->getproductlist('../inventory/shop.json');
		$productsum_total = 0;
		$productsum = 0;
		$fixedpriceshipping = 0;
		$totaltax = 0;
		$totaltax_country = 0;
		$total_products_price = 0;
		$product_tax_calc = 0;

		$productshipping = 0;	
/*
		$productprice = 0;	
		$productdesc = '';	
		$producttitle = '';	
		$producttax = 0;
		$taxitem = 'n/a';
*/
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
			
				foreach($products as $key => $value) {
					
					if($products[$j][0][1] == $product) {

						for($k=0;$k<count($value); $k++) {
							if($value[$k][0] == 'shipping.fixed.price') {
								$productshipping = $value[$k][1];	
							} 

							if($value[$k][0] == 'product.price') {
								$productprice = $value[$k][1];	
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
								$productdesc = $value[$k][1];	
							}

							if($value[$k][0] == 'product.title') {
								$producttitle = $value[$k][1];	
							}
							
							if($value[$k][0] == 'product.tax') {
								$producttax = $value[$k][1];
							} 						

						}
						
						if($productprice == null || $productprice == 0 ) {
							$productprice = 1;
						}
						
						if($productqty == null || $productqty == 0 ) {
							$productqty = 1;
						}					

						if($producttax == null || $producttax == 0 ) {
							$producttax = 0;
							$totaltax_country = 1;
							// get tax for country in total, because there is no tax for a product.
						} else {
							
						}					

						if((int)$productprice >=1) {

								$tax_price = ($productprice / 100);
								$product_tax_calc = round(($tax_price * $producttax),2);
								$total_products_price = round(($productprice + $product_tax_calc),2);
								$taxitem = $product_tax_calc;
								} else {
								$producttax = 0;
								$taxitem = 'n/a';
						}						

						if($productshipping != null || $productshipping != 0 ) {
							$fixedpriceshipping += ($productshipping * (int)$productqty);
						}
						
						$productsum = round(($total_products_price * (int)$productqty),2);
						$productsum_total += $productsum;

						$qtyid = 'tscart-'.$j.$product;

					?>
				<div class="ts-shop-ul">
						<li class="ts-shop-ul-li-item-icon" width="2%">&#128722;</li>
						<li class="ts-shop-ul-li-item-product" width="30%"><?php echo $producttitle;?><!-- title --></li>
						<li class="ts-shop-ul-li-item-description" width="35%"><?php echo $productdesc;?><!-- desc --></li>
						
			<?php
			
					if($_SESSION['cart'][$i]['product.variants']) {
						$variants = $_SESSION['cart'][$i]['product.variants'];
						$vc = count($variants);
					} else {
						$variants = 0;
						$vc = 0;
					}
				
					if(isset($variants)) {
					
					echo '<li class="ts-shop-ul-li-item-qty">';
					if($vc >=1) {
						for($j=0;$j<$vc;$j++) {
							echo $variants[$j];
							if($j < ($vc-1)) {
								echo ', ';
							}
						}
					} else {
						echo 'n/a';
					}
					echo '</li>';
				}			
			?>
			
						<li class="ts-shop-ul-li-item-price" width="9%"><?php echo $sitecurrency;?> <?php echo $productprice;?><!-- price --></li>
						<li class="ts-shop-ul-li-item-qty" width="5%"><?php echo $productqty;?></li>
						<li class="ts-shop-ul-li-item-qty" width="5%"><?php echo $taxitem;?></li>

						<li class="ts-shop-ul-li-item-total" width="15%"><?php echo $sitecurrency;?> <?php echo $productsum;?><!-- sum --></li>
				</div>
			<?php
					}
					$j++;
					}
				}
			}
			
			$siteconf = $shop->json->load_json("../server/config/shipping.conf.json");
			$countryprice = $shop->getcountryprice($siteconf,$shippingcountry);
	
			if($countryprice != false) {
				$country_price = (int)$countryprice;
				} else {
				$country_price = 10; // default shipping fee.
			}
			
			// free shipping logic.
			
			$free = false;
			$siteconf 	= $shop->json->load_json("../server/config/site.conf.json");
			$freeshipping = $shop->getasetting($siteconf,'site.freeshipping');
			$freeshipping = $freeshipping;
			
			if($freeshipping != '' || $freeshipping != null) {
				if((int)$freeshipping >= 1) {
					if(round((int)$productsum_total) >= round((int)$freeshipping)) {
						// shipping is free.
						$free = true;
						$totalshipping = 'free';
						$total = $productsum_total;
					}
				} 
			} 
			
			if($free != true) {

				if($fixedpriceshipping > $country_price) {
					// fixed flatfee shipping, if individual items exceeds flat fee.
					$totalshipping = $country_price;
					$total = ($country_price + $productsum_total);
				
				} elseif($fixedpriceshipping > 0 && ($fixedpriceshipping < $country_price)) {
					
					// TODO: Calculate if there is an item without fixed shipping price. 
					// If so, we default to flat fee again.
					$totalshipping = round($fixedpriceshipping,2);
					$total = ($totalshipping + $productsum_total);
					
				} else {
					// no item fixed price shipping, proceed with default country price.
					$totalshipping = $sitecurrency .' '. $country_price;
					$total = ($country_price + $productsum_total);

				}
			}
			
			if($totaltax_country >=1) {
				
				// there was a product without tax, get country tax.
				$country_tx = str_replace('shipping.','',$shippingcountry);
				$shippingcountry = 'tax.' . strtolower($country_tx);
				$json = $shop->json->load_json("../server/config/tax.conf.json");
							
					if($json !== null) {
						if($json[0][$shippingcountry] != null && $json[0][$shippingcountry] != '') {
							$totaltax = str_replace(['%',' '],['',''],$json[0][$shippingcountry]);
						}
					}
					
					if($totaltax >=1) {
						$pre_tax = ($productsum_total / 100);
						$productsum_total += round(($pre_tax * $totaltax),2);
						$total += round(($pre_tax * $totaltax),2);
					}
			}	else {

					//$productsum_total = $total_products_price;
					//$total = $productsum_total;
					$country_tx = str_replace('shipping.','',$shippingcountry);
			}			
			
			if($totaltax <= 0) {
				$totaltax = 'payable per unit.';
				$totaltax_session = 'payable per unit.'; 
				} else {
				$totaltax = $totaltax .'&percnt;';
				$totaltax_session = $totaltax;
			}	
			
			if($addedvalue == true) { 
				$total = ($total + $carbonvalue);
			} 
			?>
			</div>
			<br />
			<div class="ts-shop-ul-set">
			
			<div class="ts-shop-ul">
					<li class="ts-shop-ul-li-item" width="10%"></li>
					<li class="ts-shop-ul-li-item" width="10%">Country</li>
					<li class="ts-shop-ul-li-item" width="10%">Tax</li>
					<li class="ts-shop-ul-li-item" width="30%">Subtotal</li>
					<li class="ts-shop-ul-li-item" width="35%">Shipping &amp; handling</li>
					<?php if($addedvalue == true) { ?>
					<li class="ts-shop-ul-li-item" width="10%">Carbon offset</li>
					<?php  } ?>
					<li class="ts-shop-ul-li-item" width="15%">Total</li>
			</div>
			
			<li class="ts-shop-ul-li-item">
			</li>
			<li class="ts-shop-ul-li-item">
			<?php echo $country_tx;?>
			</li>
			<li class="ts-shop-ul-li-item">
			<?php echo $totaltax;?>
			</li>			
			<?php if($addedvalue == true) { 
			?>
			<li class="ts-shop-ul-li-item">	
			<!-- subtotal -->
			<?php 			
			echo $sitecurrency.' '.$productsum_total;
			?>
			</li>
			<li class="ts-shop-ul-li-item">
				 <?php echo $totalshipping;?>
			</li>	
			<li class="ts-shop-ul-li-item">
			<img src="../../resources/images/icons/carbon-offset.png" style="margin:0px;padding:0px;vertical-align:bottom;" /> 
			<?php echo $sitecurrency.' '.$carbonvalue . '</li>'; 
			} else {
			?>			
			<li class="ts-shop-ul-li-item">	
			<!-- subtotal -->
			<?php 			
			echo $sitecurrency.' '.$productsum_total;
			?>
			</li>
			<li class="ts-shop-ul-li-item">
				 <?php echo $totalshipping;?>
			</li>	
			<?php 
			}
			?>
			<li class="ts-shop-ul-li-item">
				<?php echo $sitecurrency;?> <?php echo $total;?>	
			</li>
			</div>
			
		<div class="ts-shop-form-field">
		<input type="submit" name="submit" value="Pay with <?php echo $gateway;?>">
		</div>

		</form>
		
		<?php
		
		if($addedvalue == true) { 
				$productsum_total = ($productsum_total + $carbonvalue);
		}

			// Set session data for payment gateway page.
			$uniqueid 	= $cryptography->uniqueID();
			
			$_SESSION['cartid']   	= $sanitizer->sanitize($uniqueid,'alphanum');
			$_SESSION['tax'] 		=  str_replace('percnt','',$sanitizer->sanitize($totaltax_session,'alphanum'));
			$_SESSION['subtotal']   = (int) $productsum_total;
			$_SESSION['shipping']   = (int) $totalshipping;
			$_SESSION['totalprice'] = (int) $total;
			$_SESSION['shipping_country'] = $country_tx;
			
			if(isset($_POST['email'])) {
				// we use this for pre-payment in paypal gateway.
				$_SESSION['email'] = $sanitizer->sanitize($_POST['email'],'email');
			}
		}
		} else {
		echo "<div id='ts-shop-cart-error'>Cart is empty.</div>";
	} 
	
	?>
	</div>
</div>

<?php
include("../resources/PHP/Footer.php");
?>
</body>
</html>
