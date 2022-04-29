<?php

	include("../resources/PHP/Header.inc.php");
	include("../resources/PHP/Class.Shop.php");
	include("../core/Cryptography.php");
	
	$shop  		  = new Shop();
	$cryptography = new Cryptography();
	
	if(isset($_SESSION['token'])) {
		$token = $_SESSION['token'];
	} else {
		$token = $cryptography->getToken();
		$_SESSION['token'] = $token;
	}
	
	if(isset($_GET['cat'])) {
		$cat   = str_replace('-',' ',$shop->sanitize($_GET['cat'],'cat'));
		$catid = $shop->getcatId($cat,$subcat=false);
	}
	
	if(isset($_GET['subcat'])) {
		$subcat   = str_replace('-',' ',$shop->sanitize($_GET['subcat'],'cat'));
		$subcatid = $shop->getcatId($cat,$subcat);
	}
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
<div id="cart-contents"><a href="<?php echo $host;?>cart/">View Cart</a></div>
<div id="wrapper">
<h2>Store</h2>
	<div id="ts-shop-result-message" onclick="OpenShop.togglecartmsg('close');" onmouseover="OpenShop.togglecartmsg('close');"></div>
		
		<div id="shop">
			
		<div id="ts-shop-nav-left">
			<?php
			$selected = [];
				if(isset($subcat) != false) {
					array_push($selected,$subcat);
				} 
				echo $shop->categories($selected,'left');
			
				if($optionbar != false) { 
				
					$options = $shop->getoptionbar($cat,$subcat);
					if($options != false) {
						?>
						<form name="optionbar" method="POST" action="<?php echo $host;?>refine/">
						<div id="optionbar">
						<div class="optionbar-item">OPTIONS</div>
								<div>
									
									<?php 
										echo $options;
									?>
									
								</div>
							</div>
							<input type="submit" value="refine">
						<?php
					}
				}
				?>
		</div>
			
		<div id="ts-shop-nav">
				<?php
				
				if(isset($_GET['page'])) {
					if($_GET['page'] != '' || $_GET['page'] != null) {
						$paginate = (int) $_GET['page'];
						} else {
						$paginate = false;
					}
				} else {
					$paginate = false;
				}
					
				if(isset($subcat)) {
					$products = $shop->getproducts('list',$subcat,false,false,$paginate,$_SESSION['token']);				
					echo $products[1];
				} elseif(isset($cat)) {
					$products = $shop->getproducts('list',$cat,false,false,$paginate,$_SESSION['token']);				
					echo $products[1];
				} else { }	

				?>
		</div>
	</div>
</div>

<?php
include("../resources/PHP/Footer.php");
?>
<script>

function categoryEvents() {
	OpenShop.toggle('<?php echo $catid;?>','<?php echo $shop->maxcats;?>');
}

OpenShop.tinyEvents('categories');

</script>
</body>
</html>
