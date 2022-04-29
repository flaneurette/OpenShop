<?php

	include("../../resources/PHP/Header.inc.php");
	include("../../resources/PHP/Class.Session.php");
	include("../../resources/PHP/Class.Shop.php");
	
	$session = new Session();
	$session->sessioncheck();
	
	$shop  	= new Shop();
	
	$shopconf = $shop->load_json("../../server/config/paypal.json");

	if(strtolower($_SERVER['REQUEST_METHOD']) != 'post') {
		$shop->message('Checkout page cannot be accessed this way.');
		$shop->showmessage();
		exit;
	}

	if(!empty($_GET)) {	
		$shop->message('Checkout page cannot be accessed this way.');
		$shop->showmessage();
		exit;
	}

	if(empty($_SESSION)) {	
		$shop->message('Checkout page cannot be accessed this way.');
		$shop->showmessage();
		exit;
	}
	
	if(!isset($_SESSION['token'])) {
		$shop->message('Token is not set.');
		$shop->showmessage();
		exit;	
	}
	
	if(!isset($_SESSION['cartid'])) {
		$shop->message('Cart ID is not set.');
		$shop->showmessage();
		exit;
	}	
	
	if(isset($_SESSION['token'])) {
		
		$token = $_SESSION['token'];
		
			if($token != $_POST['token']) {
				$shop->message('Token is incorrect.');
				$shop->showmessage();
				exit;
			}
	
		} else {
			
		$shop->message('Token is incorrect or not set.');
		$shop->showmessage();
		exit;
	}
	
	if(!isset($_POST['checkout-post-gateway'])) {	
		$shop->message('Gateway page could not be loaded from resource and cannot be accessed this way.');
		$shop->showmessage();
		exit;
	}
	
	$cartid 			= $shop->sanitize($_SESSION['cartid'],'alphanum');
	
	$productsum_total 	= (int) $_SESSION['subtotal'];
	$country_price 		= (int) $_SESSION['shipping'];
	$total_price 		= (int) $_SESSION['totalprice'];
	$shipping_country	= (int) $_SESSION['shipping_country'];
	$tax 				= $_SESSION['tax'];
	$idtax 				= false;
	
	$dir = 	'../../server/config/orders.conf.json';
	
	$invoiceid = $shop->invoiceid($dir,'get');
	
	if($invoiceid > 0) {
		$invoiceid = ($invoiceid +1);
		$_SESSION['invoiceid'] = $invoiceid;
		} else {
		$invoiceid = 1;
		$_SESSION['invoiceid'] = $invoiceid;
	}
	
	// echo $_SESSION['invoiceid'];
	
	/* No need to edit this below. 
	*  Start of PayPal code 
	*/
		
	// Price of the product.
	$item_price = $productsum_total;
	// Handling price.
	$handling_price = 0;
	// Shipping price.
	$shipping_price = $country_price;
	
	// PayPal variables: only edit this in paypal.json!
	$paypal_domain 			= $shop->cleanInput($shopconf[0]['paypal.domain']);
	$paypal_cancel_page 		= $shop->cleanInput($shopconf[0]['paypal.cancel.page']);
	$paypal_return_page 		= $shop->cleanInput($shopconf[0]['paypal.return.page']);
	$paypal_email 			= $shop->cleanInput($shopconf[0]['paypal.email']);
	$paypal_notify_url 		= $shop->cleanInput($shopconf[0]['paypal.notify.url']);
	$paypal_currency_code 		= $shop->cleanInput($shopconf[0]['paypal.currency.code']);
	$paypal_invoice_number 		= $invoiceid;
	
	if(empty($paypal_invoice_number)) {
		// should not be empty.
		$paypal_invoice_number 	= 1;
	}
	
	$paypal_image_url 			= $shop->cleanInput($shopconf[0]['paypal.image.url']);
	
	if(empty($paypal_image_url)) {
		$paypal_image_url 		= 'http://www.paypal.com/en_US/i/btn/x-click-but01.gif';
	}
	
	$paypal_no_note 			= $shop->cleanInput($shopconf[0]['paypal.no.note']);
	$paypal_no_shipping 		= $shop->cleanInput($shopconf[0]['paypal.no.shipping']);
	$paypal_on0 				= $shop->cleanInput($shopconf[0]['paypal.on0']);
	$paypal_on1 				= $shop->cleanInput($shopconf[0]['paypal.on1']);
	$paypal_os0 				= $shop->cleanInput($shopconf[0]['paypal.os0']);
	$paypal_os1 				= $shop->cleanInput($shopconf[0]['paypal.os1']);
	$paypal_show_user_details 	= $shop->cleanInput($shopconf[0]['paypal.show.user.details']);
	$paypal_store_user_details 	= $shop->cleanInput($shopconf[0]['paypal.store.user.details']);
	
	/*
	* doc: https://developer.paypal.com/docs/paypal-payments-standard/integration-guide/Appx-websitestandard-htmlvariables/#individual-items-variables
	*/
	
?>
<!DOCTYPE html>
<html>
	<head>
	<?php
	echo $shop->getmeta("../../server/config/site.conf.json");				
	?>
	</head>
<body>

<?php
include("../../resources/PHP/Header.php");
?>

<div id="wrapper">

		<div id="ts-shop-result-message"></div>
		<div id="ts-shop-cart-form">
		
<form action="https://www.paypal.com/us/cgi-bin/webscr" method="post" onSubmit="javascript:return OpenShop.checkPayPalform('<?php echo $token;?>');" autocomplete="false">	

<?php

		$c = count($_SESSION['cart']);
		
		$shipping_item = number_format(($shipping_price / $c),2); 
		
		$products = $shop->getproductlist('../../inventory/shop.json');
		$productsum_total = 0;
		$productsum = 0;
		
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
							if($value[$k][0] == 'shipping.price') {
								$productshipping = $value[$k][1];	
							} else {
							// $productshipping = 0;
							}

							if($value[$k][0] == 'product.price') {
								$productprice = $value[$k][1];	
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
							
							$productsum = round(($productprice * (int)$productqty),2);
							$productsum_total = ($productsum_total + $productsum);
							$qtyid = 'tscart-'.$j.$product;

					//if($tax == 'payableperunit' || $tax == 'payable per unit'){
						
						if($producttax >=1 ) {
							$taxitem = $producttax;
							echo '<input type="hidden" name="tax" value="'.$taxitem.'">';
						}

						//$tax_item = 0;
						if($producttax < 1)  {
							$producttax = 0;
						}
						
						$tax_price = ($productprice / 100);
						$product_tax_calc = round(($tax_price * $producttax),2);
						$total_products_price = round(($productprice + $product_tax_calc),2);
						//$taxitem = $product_tax_calc;
  
					// }
?>
							<input type="hidden" name="item_name_<?php echo ($i+1);?>" maxlength="127" size="20" value="<?php echo $producttitle;?>" title="cart item, 127 chars">
							<input type="hidden" name="item_number_<?php echo ($i+1);?>" maxlength="127" size="20" value="<?php echo $product;?>" title="track payments, 127 chars">
							<input type="hidden" name="item_price_<?php echo ($i+1);?>" maxlength="127" size="20" id="item_price" value="<?php echo $total_products_price;?>" title="">
							<!-- required -->
							<input type="hidden" name="amount_<?php echo ($i+1);?>" maxlength="127" size="20" id="item_price" value="<?php echo $total_products_price;?>" title=""> 
							<input type="hidden" name="quantity_<?php echo ($i+1);?>" value="<?php echo $productqty;?>">
							<input type="hidden" name="shipping_<?php echo ($i+1);?>" maxlength="127" size="20" id="shipping_x" value="<?php echo $shipping_item;?>" title="">
<?php
					}
					$j++;
				}
			}
			
		}

		if($tax != 'payableperunit'){ 
		  echo '<input type="hidden" name="tax" title="totaltax" value="'.(int)$tax.'">';
		}

?>
			<input type="hidden" name="no_note" maxlength="1" min="0" max="1" value="1" title="0">
			<!-- <input type="hidden" name="no_shipping" maxlength="1" min="0" max="1" value="1" title="0 or 1. 0 = to add shipping address"> -->
			<input type="hidden" name="shipping" id="shipping" size="5" title="The item's shipping cost" value="<?php echo $shipping_price;?>">
			
 

			<input type="hidden" name="handling" id="handling" size="5" title="handling cost" value="<?php echo $handling_price;?>">
			<input type="hidden" name="amount" size="5" id="total_amount" title="total amount" value="<?php echo $total_price;?>">	

			<div id="ts-shop-form">
			
				<div class="ts-shop-form-section">	
					<input type="hidden" name="image_url" value="<?php echo $paypal_image_url;?>">
					<input type="hidden" name="currency_code" value="<?php echo $paypal_currency_code;?>">		
					<input type="hidden" name="business" value="<?php echo $paypal_email;?>">
					<input type="hidden" name="cancel_return" value="<?php echo $paypal_domain.''.$paypal_cancel_page;?>">
					<input type="hidden" name="custom" value="<?php echo $paypal_currency_code;?>">
					<input type="hidden" name="invoice" value="<?php echo $paypal_invoice_number;?>">
					<input type="hidden" name="notify_url" value="<?php echo $paypal_domain.''.$paypal_notify_url;?>">
					<?php
					if($paypal_on0) {
					?>
						<input type="hidden" name="on0" maxlength="64" value="<?php echo $paypal_on0;?>">
						<input type="hidden" name="on1" maxlength="64" value="<?php echo $paypal_on1;?>">
					<?php
					}
					if($paypal_os0) {
					?>
						<input type="hidden" name="os0" maxlength="64" value="<?php echo $paypal_os0;?>">
						<input type="hidden" name="os1" maxlength="64" value="<?php echo $paypal_os1;?>">
					<?php
					}
					?>
					<input type="hidden" name="return" value="<?php echo $paypal_domain.''.$paypal_return_page;?>">
					
					<!-- optional -->			
					<!-- <input type="hidden" name="cmd" value="_ext-enter"> -->
					<!-- <input type="hidden" name="redirect_cmd" value="_xclick"> -->
					
					<input type="hidden" name="cmd" value="_cart">
					<input type="hidden" name="upload" value="1">
					<input type="hidden" name="rm" value="2">
					<label for="first_name">First name</label>
					<input type="text" name="first_name" id="first_name" size="15" maxlength="32" value="" title="The customer's first name (32-alphanumeric character limit).">
					<label for="last_name">Last name</label>
					<input type="text" name="last_name" id="last_name" size="15" maxlength="64" value="" title="The customer's last name (64-alphanumeric character limit).">
					<label for="address1">Address</label>
					<input type="text" name="address1" id="address1" maxlength="100" value="" title="The first line of the customer's address (100-alphanumeric character limit).">
					<label for="city">City</label>
					<input type="text" name="city" id="city" maxlength="100" value="" title="The city noted in the customer's address (100-alphanumeric character limit).">
					<label for="day_phone_a">Area code</label>
					<input type="text" name="day_phone_a" id="day_phone_a" size="5" value="">
					
				</div>
				<div class="ts-shop-form-section">

					<label for="state">State</label>
					<input type="text" name="state" id="state" size="3" maxlength="3" value="" title="The state noted in the customer's address (the official two-letter abbreviation).">
					<label for="zip">Zip</label>
					<input type="text" name="zip" id="zip" size="5"  maxlength="32" value="" title="The postal code noted in the customer's address.">
					<label for="email">E-mail</label>
					<input type="text" name="email" id="email" size="15" value="" title="The customer's email address.">
					<label for="day_phone_b">Phone</label>
					<input type="text" name="day_phone_b" id="day_phone_b" size="7" value="" value="">
					<label for="zip">Country</label>			
					<input type="text" name="country" value="">

					<!-- 
						input type="text" name="night_phone_a" id="night_phone_a" value="The area code of the customer's evening telephone number.">
						<input type="text" name="night_phone_b" id="night_phone_b" value="The first three digits of the customer's evening telephone number.">
					-->
					<input type="submit" title="Make payments with PayPal - it's fast, free and secure!" value="Pay with PayPal" onSubmit="javascript:return OpenShop.checkPayPalform('<?php echo $token;?>')" />
				</div>
			</div>
			</form>
			<hr />
</div>

<?php
include("../../resources/PHP/Footer.php");
?>
</body>
</html>
