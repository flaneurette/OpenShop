<?php

	include("../resources/PHP/Header.inc.php");
	include("../resources/PHP/Class.Session.php");
	include("../resources/PHP/Class.Shop.php");
	include("../core/Cryptography.php");
	
	$shop  		  = new Shop();
	$cryptography = new Cryptography();
	$session 	  = new Session();
	
	$session->sessioncheck();
	
	if(isset($_SESSION['token'])) { 
		$token = $_SESSION['token'];
		} else {
		$token = $cryptography->getToken();
		$_SESSION['token'] = $token;
	}

	$host_path = $shop->getbase(true);
	
	/* Get the currency of site.json
	 * To change the default currency, edit site.json which has a numeric value that corresponds to the values inside currencies.json.
	 * DO NOT edit currencies.json, unless adding a new currency, as this file is used throughout OpenShop and might break functionality.
	*/
	
	$sitecurrency = $shop->getsitecurrency('../server/config/site.conf.json','../server/config/currencies.conf.json');
?>
<!DOCTYPE html>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=0.73">
	<?php
	echo $shop->getmeta();				
	?>
	</head>
<body>

<?php
include("../resources/PHP/Header.php");
?>
<div id="ts-shop-result-message" onclick="OpenShop.togglecartmsg('close');" onmouseover="OpenShop.togglecartmsg('close');"></div>
<div id="bio-wrapper">

<div id="ts-shop-cart-form">
<h1>Shopping Cart</h1>

	<?php 
		
		if(isset($_SESSION['cart']) && count($_SESSION['cart']) >= 1) {
		$c = count($_SESSION['cart']);
		
		if(($c > 0) && ($c < 9999) ) {
			
	?>
		<form name="ts_cart" method="post" action="<?php echo $host;?>cart/checkout/" id="ts-shop-cart-form-data" onSubmit="javascript:return OpenShop.checkform();">
		<input type="hidden" name="token" value="<?php echo $token;?>">
		<input type="hidden" name="checkout-post" value="1">
		<hr />
		
		<div class="ts-shop-ul-set">
		<div class="ts-shop-ul">
			<li class="ts-shop-ul-li-item-product">Product Name</li>
			<li class="ts-shop-ul-li-item-description">Description</li>
			<li class="ts-shop-ul-li-item">Variants</li>
			<li class="ts-shop-ul-li-item">Price</li>
			<li class="ts-shop-ul-li-item-qty">Qty</li>
			<li class="ts-shop-ul-li-item-update"></li>
			<li class="ts-shop-ul-li-item-total">Total</li>			
			<li class="ts-shop-ul-li-item-delete"></li>
		</div>

	<?php
			
		$products = $shop->getproductlist('../inventory/shop.json');
			
		// var_dump($products);
		
		$fixedpriceshipping = 0;
		$productsum_total 	= 0;
		
		for($i=0; $i < $c; $i++) {
			
			if($_SESSION['cart'][$i]) {
				$product = (int) $_SESSION['cart'][$i]['product.id'];
				if($_SESSION['cart'][$i]['product.qty'] == 0) {
					$_SESSION['cart'][$i]['product.qty'] = 1;
				}
				$productqty = $_SESSION['cart'][$i]['product.qty'];
			}
			
			$j = 0;

			if(isset($products)) {
			
				foreach($products as $key => $value) {
					
					if(isset($products[$j]) && $products[$j][0][1] == $product ) {
						
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
						
						if($productshipping != null || $productshipping != 0 ) {
							$fixedpriceshipping += ($productshipping * (int)$productqty);
						}
								
						$productsum = round(($productprice * (int)$productqty),2);
						$productsum_total = ($productsum_total + $productsum);
						
						$qtyid = 'tscart-'.$j.$product;

		?>
		<div class="ts-shop-ul">
			<li class="ts-shop-ul-li-item-product"><?php echo $producttitle;?><!-- title --></li>
			<li class="ts-shop-ul-li-item-description"><?php echo $productdesc;?><!-- desc --></li>
			
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
			<li class="ts-shop-ul-li-item-price"><?php echo $sitecurrency;?> <?php echo $productprice;?><!-- price --></li>
			<li class="ts-shop-ul-li-item-qty"><input type="number" name="qty" id="<?php echo $qtyid;?>" size="1" min="1" max="9999" value="<?php echo $productqty;?>"></li>
			<li class="ts-shop-ul-li-item-update"><a href="#" onclick="OpenShop.updatecart('<?php echo $product;?>','<?php echo $qtyid;?>','<?php echo $token;?>','<?php echo $host_path;?>');">&#x21bb;</a></li>
			<li class="ts-shop-ul-li-item-total"><?php echo $sitecurrency;?> <?php echo $productsum;?><!-- sum --></li>
			<li class="ts-shop-ul-li-item-delete" id="ts-shop-delete"><a href="#" onclick="OpenShop.deletefromcart('<?php echo $product;?>','<?php echo $token;?>','<?php echo $host_path;?>');">&#x2716;</a>
			</li>
		</div>
		<?php
					}
					$j++;
					}
				}
			}
			
			$cobox = false;
			$siteconf1 = $shop->load_json("../server/config/shipping.conf.json");
			$siteconf2 = $shop->load_json("../server/config/site.conf.json");
			
			$carbonoffsetting = $shop->getasetting($siteconf2,'site.carbonoffset');

			if($carbonoffsetting["site.carbonoffset"] == '1') {
				
				$coprices = $carbonoffsetting["site.carbonoffset.prices"];
				
				if(stristr($coprices,',')) {
					
					$coboxdisplay  = '<div id="carbon-offset-box" style="float:left;"><img src="../resources/images/icons/carbon-offset.png" style="margin:0px;padding:0px;vertical-align:bottom;" /> ';
					$coboxdisplay .= 'Carbon offset option: ';
					$coboxdisplay .= '<select name="cooffset" id="carbon-offset-option">';
					$coboxdisplay .= '<option value="">Choose carbon offset...</option>';
					
					$copieces = explode(',',$coprices);
					
					for($co=0;$co<count($copieces);$co++) {
						$coboxdisplay .= '<option value="'.$copieces[$co].'">Offset CO2 value: '.$sitecurrency.trim($copieces[$co]).'</option>';
					}
					
					$coboxdisplay .= '</select></div>';
					$cobox = true;
					
				} else {
					$cobox = false;
				}
			}
			
			
		?>
		</div>
		<hr />
		<h1>Checkout</h1>
		<hr />
			<span>e-mailaddress:</span>
			<div><input type="text" name="email" id="email-prepayment" size="25" value="" style="width:250px;" /></div>
			<br /><br /><hr />
			
			<?php
			if($cobox == true) {
				echo $coboxdisplay . '<br /><hr />';
			}
			?>
			<select name="shipping_country" id="ts-form-cart-shipping-country-select">
			
			<option value="">Select shipping country...</option>
			<?php
				// dynamically generate payment gateways from site.json
				echo $shop->shippinglist($siteconf1,true);
			?>
			</select>
			<br />
			<select name="payment_gateway" id="ts-form-cart-payment-gateway-select">
			<option value="">Select payment method...</option>
			<?php
				// dynamically generate payment gateways from site.json
				$keys = 'site.payment.gateways';
				echo $shop->gatewaylist($siteconf2,$keys);
			?>
			</select>
		<div class="ts-shop-form-field">
			<input type="submit" name="submit" value="Checkout">
		</div>
		</form>
		
		<?php
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
