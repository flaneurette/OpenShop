<?php

	include("../resources/PHP/Header.inc.php");
	include("../resources/PHP/Class.Shop.php");
	include_once("../core/Cryptography.php");
	include_once("../core/Logging.php");
	include_once("../core/Meta.php");
	
	$shop  		  = new Shop;
	$cryptography = new Cryptography;
	$sanitizer 	= new Sanitizer;
	$metafactory  = new Meta;
	
	$token = $cryptography->getToken();
	$_SESSION['token'] = $token;
	
	$cat   = $sanitizer->sanitize('index','cat');
	$catid = $shop->getcatId($cat,$subcat=false);
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
<div id="cart-contents"><a href="<?php echo $host;?>cart/">View Cart</a></div>
	<div id="wrapper">
	<h2>Store</h2>
		<div id="ts-shop-result-message" onclick="OpenShop.togglecartmsg('close');" onmouseover="OpenShop.togglecartmsg('close');"></div>
				<div id="shop">
					<div id="ts-shop-nav-left">
					<?php

							// categories
							$categories = "../inventory/categories.json";
							
							// subcategories
							$subcategories = "../inventory/subcategories.json";
							
							$selected = [];
							
							if(isset($cat) != false) {
								array_push($selected,$cat);
							} 
							
							if(isset($subcat) != false) {
								array_push($selected,$subcat);
							} 
							
							$cats = $shop->categories($categories,$subcategories,$selected,'left');
							
							echo $cats;
					?>
					</div>
				
					<div id="ts-shop-nav">
						<?php
						$products = $shop->getproducts('refine','index',false,false,false,$_SESSION['token']);
						echo $products[1];	
						?>
					</div>
			</div>
	</div>

	<?php
	$logging = new Logging;
	$logging ->logging('Refine');
	include("../instance/Shopfloor.php");
	include("../resources/PHP/Footer.php");
	?>
	<script>
		function categoryEvents() {
			OpenShop.toggle(<?php echo $catid;?>,'8');
		}

		OpenShop.tinyEvents('categories');
	</script>
</body>
</html>

