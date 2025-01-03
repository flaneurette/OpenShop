<?php

	include("../resources/PHP/Header.inc.php");
	include("../resources/PHP/Class.Shop.php");
	include("../core/Cryptography.php");
	include_once("../core/Meta.php");
	include_once("../core/Sorter.php");
	
	$shop  		  = new Shop;
	$cryptography = new Cryptography;
	$sanitizer    = new Sanitizer;
	$metafactory  = new Meta;
	$sorter 	  = new Sorter;
	
	$sorting = false;
	$sortvalue = false;
	
	if(isset($_SESSION['token'])) {
		$token = $_SESSION['token'];
	} else {
		$token = $cryptography->getToken();
		$_SESSION['token'] = $token;
	}
	
	if(isset($_GET['cat'])) {
		$cat   = str_replace('-',' ',$sanitizer->sanitize($_GET['cat'],'cat'));
		$catid = $shop->getcatId($cat,$subcat=false);
	}
	
	if(isset($_GET['subcat'])) {
		$subcat   = str_replace('-',' ',$sanitizer->sanitize($_GET['subcat'],'cat'));
		$subcatid = $shop->getcatId($cat,$subcat);
	}

	if(isset($_GET['sorting'])) {
		if($_GET['sorting'] != '' || $_GET['sorting'] != null) {
			$sorting = true;
			$sortvalue = $sanitizer->sanitize($_GET['sorting'],'search');
		}
	}
	
	$uriparameters = $shop->getbase()."category";
	
	if(isset($cat)) {
		$uriparameters .= '/'.$cat;
	}
	if(isset($subcat)) {
		$uriparameters .= '/'.$subcat;
	}	
	
	if(isset($_GET['page'])) {
		if($_GET['page'] != '' || $_GET['page'] != null) {
				$paginate = (int) $_GET['page'];
				} else {
				$paginate = false;
			}
		} else {
		$paginate = false;
	}
				
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
						<h2>OPTIONS</h2>
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
				
				 if(isset($subcat) && $cat != 'index') {
					echo $sorter->sorting($uriparameters.'sort/','price','Sorting...,Price ascending,Price descending,Title acending,Title descending','Sorting...,Price:ascending,Price:descending,Title:ascending,Title:descending');
				} elseif(isset($cat) && $cat != 'index') {

				echo $sorter->sorting($uriparameters.'sort/','price','Sorting...,Price ascending,Price descending,Title acending,Title descending','Sorting...,Price:ascending,Price:descending,Title:ascending,Title:descending');

				} else {}
				
				if(isset($subcat)) {
					$products = $shop->getproducts('list',$subcat,false,false,$paginate,$sorting,$sortvalue,$_SESSION['token']);				
					$productlist = $products[1];
				} elseif(isset($cat)) {
					$products = $shop->getproducts('list',$cat,false,false,$paginate,$sorting,$sortvalue,$_SESSION['token']);				
					$productlist = $products[1];
				} else { }	

				echo $productlist;

				?>
		</div>
	</div>
</div>

<?php
include("../instance/Shopfloor.php");
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
