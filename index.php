<?php
	include("resources/PHP/Header.inc.php");
	include("resources/PHP/Class.Shop.php");
	include("instance/Init.php");
	include_once("core/Logging.php");
	include_once("core/Meta.php");
	
	$metafactory = new Meta;
?>
<!DOCTYPE html>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php
	echo $metafactory->getmeta();				
	?>
	</head>
	<body>
	<?php
	include("resources/PHP/Header.php");
	?>
		<div id="cart-contents"><a href="<?php echo $host;?>cart/">View Cart</a></div>
			<div id="wrapper">
				<div id="ts-shop-result-message" onclick="OpenShop.togglecartmsg('close');" onmouseover="OpenShop.togglecartmsg('close');"></div>
					<div id="shop">
						
						<div id="ts-shop-nav-left">
						<?php
						echo $shop->categories($selected=false,'left');
						if($pricebar != false) { 
						    include("instance/Priceapp.php");
						}
						?>
						</div>
						
						<div id="ts-shop-nav">
						<?php
						$products = $shop->getproducts('list','index',false,false,false,$token);
						if(isset($products)) {
							echo $products[1];
						}						
						?>
					</div>
				</div>
			</div>
		<?php
		// Initialize logging for webshop traffic.
		$logging = new Logging;
		$logging ->logging('shop');
		include("instance/Shopfloor.php");
		include("resources/PHP/Footer.php");
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
