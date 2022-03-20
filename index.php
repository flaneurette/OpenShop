<?php
	include("resources/php/header.inc.php");
	include("resources/php/class.Shop.php");
	include("instance/init.php");
?>
<!DOCTYPE html>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=0.5, maximum-scale=5.0, minimum-scale=0.5">
	<?php
	echo $shop->getmeta();				
	?>
	</head>
	<body>
	<?php
	include("resources/php/header.php");
	?>
		<div id="cart-contents"><a href="<?php echo $host;?>cart/">View Cart</a></div>
			<div id="wrapper">
			<h2>Store</h2>
				<div id="ts-shop-result-message" onclick="OpenShop.togglecartmsg('close');" onmouseover="OpenShop.togglecartmsg('close');"></div>
					<div id="shop">
						
						<div id="ts-shop-nav-left">
						<?php
						echo $shop->categories($selected=false,'left');
						if($pricebar != false) { 
						    include("instance/priceapp.php");
						}
						?>
						</div>
						
						<div id="ts-shop-nav">
						<?php
						$products = $shop->getproducts('list','index',false,false,false,$token);
						echo $products[1];	
						?>
					</div>
				</div>
			</div>
		<?php
		// Initialize logging for webshop traffic.
		$shop->logging('shop');
		include("resources/php/footer.php");
		?>
		<script language="JavaScript">
		<!-- Initialize the menu selection -->
		function categoryEvents() {
			OpenShop.toggle(<?php echo $catid;?>,'8');
		}
		// Register events
		OpenShop.tinyEvents('categories');
		</script>
	</body>
</html>
