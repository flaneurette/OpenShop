<?php

	error_reporting(0);
	
	include("../resources/PHP/Header.inc.php");
	include("../resources/PHP/Class.Shop.php");
	include("../core/Cryptography.php");
	include("../core/Formatter.php");
	include_once("../core/Meta.php");
	
	$shop  		  = new Shop;
	$cryptography = new Cryptography;
	$formatter 	  = new Formatter;
	$sanitizer	  = new Sanitizer;
	$metafactory  = new Meta;
	
	$token = $cryptography->getToken();
	$_SESSION['token'] = $token;
	$_SESSION['streamtoken'] = $cryptography->pseudoNonce().$cryptography->pseudoNonce();

	$productid = false;
	
	if(isset($_REQUEST['cat'])) {
		$cat 		= $sanitizer->sanitize($_REQUEST['cat'],'cat');
		$product 	= $sanitizer->sanitize($_REQUEST['product'],'cat');
		$productid	= (int)$sanitizer->sanitize($_REQUEST['productid'],'num');
		$page 		= $sanitizer->sanitize($_REQUEST['page'],'num');	
		
		if(isset($_REQUEST['subcat'])) {
			$scat 		= $sanitizer->sanitize($_REQUEST['subcat'],'cat');
			$subcat  	= $shop->getcatId($cat,$scat);
		}
		
	}
	
	// get host
	if(isset($shop)) {
		$hostaddr = $shop->getbase();
		} else {
		echo "Could not load Shop.class.php";
		exit;
	}
	
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=0.73">
<?php
echo $metafactory->getmeta(false,$productid);				
?>
</head>

<body>

<?php
include("../resources/PHP/Header.php");
?>

<div id="cart-contents"><a href="<?php echo $host;?>cart/">View Cart</a></div>
<div id="wrapper">
	<div id="ts-shop-result-message" onclick="OpenShop.togglecartmsg('close');" onmouseover="OpenShop.togglecartmsg('close');"></div>
		<!-- <h1>Shop product list</h1> -->
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
			
			if(isset($_REQUEST['productid'])) {
				$id = (int) $_REQUEST['productid'];
			}
			
			$product_list = $shop->json->decode('../inventory/shop.json');
			
			$base_url = $shop->getbase();

			if($product_list !== null) {

				$shoplist = $product_list;
				
				$iv = array();
				
				$i = 0;
				
					foreach($product_list as $c) {	
					
						array_push($iv,$c);
						
						if($iv[$i]['product.id'] == $id) {

							if($iv[$i]['product.status'] == '1') {


									$sanitizer->sanitize($iv[$i]["product.id"],'trim') ? $product_id = $sanitizer->cleaninput($iv[$i]["product.id"]) : $product_id = false; 
									$sanitizer->sanitize($iv[$i]["product.title"],'trim') ? $product_title = $sanitizer->cleaninput($iv[$i]["product.title"]) : $product_title = false; 
									$sanitizer->sanitize($iv[$i]["product.description"],'trim') ? $product_description = $sanitizer->cleaninput($iv[$i]["product.description"]) : $product_description = false; 
									$sanitizer->sanitize($iv[$i]["product.category"],'trim') ? $product_category = $sanitizer->cleaninput($iv[$i]["product.category"]) : $product_category = false; 
									$sanitizer->sanitize($iv[$i]["product.stock"],'trim') ? $product_stock= $sanitizer->cleaninput($iv[$i]["product.stock"]) : $product_stock = false; 
									$sanitizer->sanitize($iv[$i]["product.price"],'trim') ? $product_price = $sanitizer->cleaninput($iv[$i]["product.price"]) : $product_price = false; 
									$sanitizer->sanitize($iv[$i]["product.image"],'trim') ? $product_image = $sanitizer->cleaninput($iv[$i]["product.image"]) : $product_image = false; 
									$sanitizer->sanitize($iv[$i]["product.catno"],'trim') ? $product_catno = $sanitizer->cleaninput($iv[$i]["product.catno"]) : $product_catno = false; 
									$sanitizer->sanitize($iv[$i]["product.stock"],'trim') ? $product_stock = $sanitizer->cleaninput($iv[$i]["product.stock"]) : $product_stock = false; 
									$sanitizer->sanitize($iv[$i]["product.quantity"],'trim') ? $product_quantity = $sanitizer->cleaninput($iv[$i]["product.quantity"]) : $product_quantity = false; 
									$sanitizer->sanitize($iv[$i]["product.format"],'trim') ? $product_format = $sanitizer->cleaninput($iv[$i]["product.format"]) : $product_format = false; 
									$sanitizer->sanitize($iv[$i]["product.type"],'trim') ? $product_type = $sanitizer->cleaninput($iv[$i]["product.type"]) : $product_type = false; 
									$sanitizer->sanitize($iv[$i]["product.weight"],'trim') ? $product_weight = $sanitizer->cleaninput($iv[$i]["product.weight"]) : $product_weight = false; 
									$sanitizer->sanitize($iv[$i]["product.condition"],'trim') ? $product_condition = $sanitizer->cleaninput($iv[$i]["product.condition"]) : $product_condition = false; 
									$sanitizer->sanitize($iv[$i]["product.ean"],'trim') ? $product_ean = $sanitizer->cleaninput($iv[$i]["product.ean"]) : $product_ean = false; 
									$sanitizer->sanitize($iv[$i]["product.sku"],'trim') ? $product_sku = $sanitizer->cleaninput($iv[$i]["product.sku"]) : $product_sku = false; 
									$sanitizer->sanitize($iv[$i]["product.vendor"],'trim') ? $product_vendor = $sanitizer->cleaninput($iv[$i]["product.vendor"]) : $product_vendor = false; 
									$sanitizer->sanitize($iv[$i]["product.price_min"],'trim') ? $product_price_min= $sanitizer->cleaninput($iv[$i]["product.price_min"]) : $product_price_min = false; 
									$sanitizer->sanitize($iv[$i]["product.price_max"],'trim') ? $product_price_max = $sanitizer->cleaninput($iv[$i]["product.price_max"]) : $product_price_max = false; 
									$sanitizer->sanitize($iv[$i]["product.price_varies"],'trim') ? $product_price_varies = $sanitizer->cleaninput($iv[$i]["product.price_varies"]) : $product_price_varies = false; 
									$sanitizer->sanitize($iv[$i]["product.date"],'trim') ? $product_date = $sanitizer->cleaninput($iv[$i]["product.date"]) : $product_date = false; 
									$sanitizer->sanitize($iv[$i]["product.url"],'trim') ? $product_url = $sanitizer->cleaninput($iv[$i]["product.url"]) : $product_url = false; 
									$sanitizer->sanitize($iv[$i]["product.tags"],'trim') ? $product_tags = $sanitizer->cleaninput($iv[$i]["product.tags"]) : $product_tags = false; 
									$sanitizer->sanitize($iv[$i]["product.images"],'trim') ? $product_images = $sanitizer->cleaninput($iv[$i]["product.images"]) : $product_images = false; 
									$sanitizer->sanitize($iv[$i]["product.featured"],'trim') ? $product_featured = $sanitizer->cleaninput($iv[$i]["product.featured"]) : $product_featured = false; 
									$sanitizer->sanitize($iv[$i]["product.featured_location"],'trim') ? $product_featured_location = $sanitizer->cleaninput($iv[$i]["product.featured_location"]) : $product_featured_location = false; 
									$sanitizer->sanitize($iv[$i]["product.featured_carousel"],'trim') ? $product_featured_carousel = $sanitizer->cleaninput($iv[$i]["product.featured_carousel"]) : $product_featured_carousel = false; 
									$sanitizer->sanitize($iv[$i]["product.featured_image"],'trim') ? $product_featured_image = $sanitizer->cleaninput($iv[$i]["product.featured_image"]) : $product_featured_image = false; 
									$sanitizer->sanitize($iv[$i]["product.content"],'trim') ? $product_content = $sanitizer->cleaninput($iv[$i]["product.content"]) : $product_content = false; 
									$sanitizer->sanitize($iv[$i]["product.variants"],'trim') ? $product_variants = $sanitizer->cleaninput($iv[$i]["product.variants"]) : $product_variants = false; 
									$sanitizer->sanitize($iv[$i]["product.available"],'trim') ? $product_available = $sanitizer->cleaninput($iv[$i]["product.available"]) : $product_available = false; 
									$sanitizer->sanitize($iv[$i]["product.selected_variant"],'trim') ? $product_selected_variant = $sanitizer->cleaninput($iv[$i]["product.selected_variant"]) : $product_selected_variant = false; 
									$sanitizer->sanitize($iv[$i]["product.collections"],'trim') ? $product_collections = $sanitizer->cleaninput($iv[$i]["product.collections"]) : $product_collections = false; 
									$sanitizer->sanitize($iv[$i]["product.options"],'trim') ? $product_options = $sanitizer->cleaninput($iv[$i]["product.options"]) : $product_options = false; 
									$sanitizer->sanitize($iv[$i]["variant.title1"],'trim') ? $variant_title_1 = $sanitizer->cleaninput($iv[$i]["variant.title1"]) : $variant_title_1 = false; 
									$sanitizer->sanitize($iv[$i]["variant.title2"],'trim') ? $variant_title_2 = $sanitizer->cleaninput($iv[$i]["variant.title2"]) : $variant_title_2 = false; 
									$sanitizer->sanitize($iv[$i]["variant.title3"],'trim') ? $variant_title_3 = $sanitizer->cleaninput($iv[$i]["variant.title3"]) : $variant_title_3 = false; 	
									$sanitizer->sanitize($iv[$i]["variant.image1"],'trim') ? $variant_image_1 = $sanitizer->cleaninput($iv[$i]["variant.image1"]) : $variant_image_1 = false; 
									$sanitizer->sanitize($iv[$i]["variant.image2"],'trim') ? $variant_image_2 = $sanitizer->cleaninput($iv[$i]["variant.image2"]) : $variant_image_2 = false; 
									$sanitizer->sanitize($iv[$i]["variant.image3"],'trim') ? $variant_image_3 = $sanitizer->cleaninput($iv[$i]["variant.image3"]) : $variant_image_3 = false;
									$sanitizer->sanitize($iv[$i]["variant.option1"],'trim') ? $variant_option_1 = $sanitizer->cleaninput($iv[$i]["variant.option1"]) : $variant_option_1 = false;
									$sanitizer->sanitize($iv[$i]["variant.option2"],'trim') ? $variant_option_2 = $sanitizer->cleaninput($iv[$i]["variant.option2"]) : $variant_option_2 = false;
									$sanitizer->sanitize($iv[$i]["variant.option3"],'trim') ? $variant_option_3 = $sanitizer->cleaninput($iv[$i]["variant.option3"]) : $variant_option_3 = false;
									$sanitizer->sanitize($iv[$i]["variant.price1"],'trim') ? $variant_price_1 = $sanitizer->cleaninput($iv[$i]["variant.price1"]) : $variant_price_1 = false;
									$sanitizer->sanitize($iv[$i]["variant.price2"],'trim') ? $variant_price_2 = $sanitizer->cleaninput($iv[$i]["variant.price2"]) : $variant_price_2 = false;
									$sanitizer->sanitize($iv[$i]["variant.price3"],'trim') ? $variant_price_3 = $sanitizer->cleaninput($iv[$i]["variant.price3"]) : $variant_price_3 = false;

									$sanitizer->sanitize($iv[$i]["shipping.price"],'trim') ? $shipping_fixed_price = $sanitizer->cleaninput($iv[$i]["shipping.price"]) : $shipping_fixed_price = false;
									$sanitizer->sanitize($iv[$i]["shipping.flatfee"],'trim') ? $shipping_flat_fee = $sanitizer->cleaninput($iv[$i]["shipping.flatfee"]) : $shipping_flat_fee = false;
									$sanitizer->sanitize($iv[$i]["shipping.locations"],'trim') ? $shipping_locations = $sanitizer->cleaninput($iv[$i]["shipping.locations"]) : $shipping_locations = false;

								if(trim($iv[$i]["variant.title1"]) != "") {
									$variant_title1			= $sanitizer->cleaninput($iv[$i]["variant.title1"]);
									$variant_image1 		= $sanitizer->cleaninput($iv[$i]["variant.image1"]);
									$variant_option1 		= $sanitizer->cleaninput($iv[$i]["variant.option1"]);
									$variant_price1 		= $sanitizer->cleaninput($iv[$i]["variant.price1"]);
								}
								
								if(trim($iv[$i]["variant.title2"]) != "") {
									$variant_title2			= $sanitizer->cleaninput($iv[$i]["variant.title2"]);
									$variant_image2 		= $sanitizer->cleaninput($iv[$i]["variant.image2"]);
									$variant_option2 		= $sanitizer->cleaninput($iv[$i]["variant.option2"]);
									$variant_price2 		= $sanitizer->cleaninput($iv[$i]["variant.price2"]);
								}
								
								if(trim($iv[$i]["variant.title3"]) != "") {
									$variant_title3			= $sanitizer->cleaninput($iv[$i]["variant.title3"]);
									$variant_image3 		= $sanitizer->cleaninput($iv[$i]["variant.image3"]);
									$variant_option3 		= $sanitizer->cleaninput($iv[$i]["variant.option3"]);
									$variant_price3 		= $sanitizer->cleaninput($iv[$i]["variant.price3"]);
								}									
						
								$variantprices1 = false;
								$variantprices2 = false;
								$variantprices3 = false;
								
								$optionbox1 = false;
								$optionbox2 = false;
								$optionbox3 = false;
								
								if(trim($iv[$i]["variant.option1"]) != "") {
									$variantprices1 = trim($iv[$i]["variant.price1"]);
									$optionbox1 = $shop->getoptionbox('variant1',$iv[$i]["variant.title1"],$iv[$i]["variant.option1"],$variantprices1,$product_id);
								}
								
								if(trim($iv[$i]["variant.option2"]) != "") {
									$variantprices2 = trim($iv[$i]["variant.price2"]);
									$optionbox2 = $shop->getoptionbox('variant2',$iv[$i]["variant.title2"],$iv[$i]["variant.option2"],$variantprices2,$product_id);
								}	
								
								if(trim($iv[$i]["variant.option3"]) != "") {
									$variantprices3 = trim($iv[$i]["variant.price3"]);
									$optionbox3 = $shop->getoptionbox('variant3',$iv[$i]["variant.title3"],$iv[$i]["variant.option3"],$variantprices3,$product_id);
								}		

							$string_button = "<div><input type='number' name='qty' size='1' value='1' min='1' max='9999' id='ts-group-cart-qty-".$i.'-'.$product_id."'><input type='button' onclick='OpenShop.addtocart(\"".$product_id."\",\"ts-group-cart-qty-".$i.'-'.$product_id."\",\"".$token."\",\"".$hostaddr."\");' class='ts-list-cart-button' name='add_cart' value='Add to Cart' /></div>";
							
							echo '<div class="product-box">
									<div class="product-title"><h2>'.$product_title.'</h2></div>
										<div class="product-subbox">
											
											<div class="product-details">
											<div class="product-image">
												<img src="'.$base_url.$product_image.'" onmouseup="mouse(\'11\');" onmousedown="mouse(\'11\');" onrightclick="mouse(\'11\');"/>
											</div>
												<div class="product-description">'.$formatter->format($product_description,'product-description').'</div>
												<span class="product-price" id="price-update">'.$shop->getsitecurrency('server/config/site.conf.json','server/config/currencies.conf.json').' '.$product_price.'</span>'; 

												if($optionbox1 != false) { 
													echo '<div class="product-option">Variant: '. $optionbox1 .'</div>'; 
												} 
												
												if($optionbox2 != false) { 
													echo '<div class="product-option">Variant: '. $optionbox2 .'</div>'; 
												} 
												
												if($optionbox3 != false) { 
													echo '<div class="product-option">Variant: '. $optionbox3 .'</div>'; 
												} 

												echo '<br /><br /><div class="product-buynow">'.$string_button.'</div>';
										
												$video 	= $sanitizer->cleaninput($iv[$i]['product.video']);
												$audio 	= $sanitizer->cleaninput($iv[$i]['product.audio']);
												
												if(isset($video) && $video != "") {
													echo "<br /><div class=\"product-video\"><video width=\"50%\" controls><source src=\"".$hostaddr."instance/Stream.php?id=".(int)$product_id."&type=video&streamtoken=".$_SESSION['streamtoken']."\" type=\"video/mp4\"></video></div>";
												}
												
												if(isset($audio) && $audio != "") {
													echo "<br /><div class=\"product-audio\"><audio controls><source src=\"".$hostaddr."instance/Stream.php?id=".(int)$product_id."&type=audio&streamtoken=".$_SESSION['streamtoken']."\" type=\"audio/mpeg\"></audio></div>";
												}
												
							// closing div below.
							
							$find = strstr($product_images,',');
						
							if(is_array($product_images) || $find == true ) {
								
								if($find == true) {
									$product_images =  explode(",",$product_images); 
								}

								$count = count($product_images);
								
								if($count >=1) {
									
									echo '<div class="product-images">';
									
										for($img = 0; $img < $count; $img++) {
											echo '<div class="product-images-item"><a href="'.$base_url.trim($product_images[$img]).'" target="_blank"><img src="'.$base_url.trim($product_images[$img]).'" /></a></div>';
										}
									
									echo '</div>';
								}
							}
							echo '<br /><br />';
							echo '<div id="product-info-box">';
									if($product_id  != false) { echo '<div id="product-info-box-item">Product ID: '.$sanitizer->cleaninput($product_id).'</div>'; }
									if($product_title != false) { echo '<div id="product-info-box-item">Product title: '.$sanitizer->cleaninput($product_title).'</div>'; } 
									if($product_category != false) { echo '<div id="product-info-box-item">Category: '.$sanitizer->cleaninput($product_category).'</div>'; } 
									if($product_stock != false) { echo '<div id="product-info-box-item">In stock: '.$sanitizer->cleaninput($product_stock).'</div>'; } 
									if($product_catno != false) { echo '<div id="product-info-box-item">Catno: '.$sanitizer->cleaninput($product_catno).'</div>'; } 
									if($product_quantity != false) { echo '<div id="product-info-box-item">Quantity: '.$sanitizer->cleaninput($product_quantity).'</div>'; } 
									if($product_format != false) { echo '<div id="product-info-box-item">Format: '.$sanitizer->cleaninput($product_format).'</div>'; } 
									if($product_type != false) { echo '<div id="product-info-box-item">Type: '.$sanitizer->cleaninput($product_type).'</div>'; } 
									if($product_weight != false) { echo '<div id="product-info-box-item">Weight: '.$sanitizer->cleaninput($product_weight).'</div>'; } 
									if($product_condition != false) { echo '<div id="product-info-box-item">Condition: '.$sanitizer->cleaninput($product_condition).'</div>'; } 
									if($product_ean != false) { echo '<div id="product-info-box-item">EAN: '.$sanitizer->cleaninput($product_ean).'</div>'; } 
									if($product_sku != false) { echo '<div id="product-info-box-item">SKU: '.$sanitizer->cleaninput($product_sku).'</div>'; } 
									if($product_vendor != false) { echo '<div id="product-info-box-item">Vendor: '.$sanitizer->cleaninput($product_vendor).'</div>'; } 
									
									if($product_date != false) { echo '<div id="product-info-box-item">Date: '.$sanitizer->cleaninput($product_date).'</div>'; } 
									if($product_url != false) { echo '<div id="product-info-box-item">URL: '.$sanitizer->cleaninput($product_url).'</div>'; } 
									if($product_tags != false) { echo '<div id="product-info-box-item">Tags: '.$sanitizer->cleaninput($product_tags).'</div>'; } 

									// if($product_price_min != false) { echo '<div id="product-info-box-item">'..'</div>'; } 
									// if($product_price_max != false) { echo '<div id="product-info-box-item">'..'</div>'; } 
									// if($product_price_varies != false) { echo '<div id="product-info-box-item">'..'</div>'; } 		
									// if($product_images != false) { echo '<div id="product-info-box-item">'..'</div>'; } 
									// if($product_featured != false) { echo '<div id="product-info-box-item">'..'</div>'; } 
									// if($product_featured_location != false) { echo '<div id="product-info-box-item">'..'</div>'; } 
									// if($product_featured_carousel != false) { echo '<div id="product-info-box-item">'..'</div>'; } 
									// if($product_featured_image != false) { echo '<div id="product-info-box-item">'..'</div>'; } 
									// if($product_content != false) { echo '<div id="product-info-box-item">'..'</div>'; } 
									// if($product_variants != false) { echo '<div id="product-info-box-item">'..'</div>'; } 
									// if($product_available != false) { echo '<div id="product-info-box-item">'..'</div>'; } 
									// if($product_selected_variant != false) { echo '<div id="product-info-box-item">'..'</div>'; } 
									// if($product_collections != false) { echo '<div id="product-info-box-item">'..'</div>'; } 
									// if($product_options != false) { echo '<div id="product-info-box-item">'..'</div>'; } 		
									
									if($shipping_fixed_price != false) { echo '<div id="product-info-box-item">Shipping fixed price: '.$sanitizer->cleaninput($shipping_fixed_price).'</div>'; }
									if($shipping_flat_fee != false) { echo '<div id="product-info-box-item">Flat fee: '.$sanitizer->cleaninput($shipping_flat_fee).'</div>'; } 
									if($shipping_locations != false) { echo '<div id="product-info-box-item">Shipping locations: '.$sanitizer->cleaninput($shipping_locations).'</div>'; } 

							echo '</div>';	

							echo '<div id="product-social">';
									if($socialmedia_option1 != false) { echo '<div id="product-social-box-item"><a href="'.$sanitizer->cleaninput($socialmedia_option1).'" target="_blank">'.$sanitizer->cleaninput($socialmedia_option1).'</a></div>'; } 
									if($socialmedia_option2 != false) { echo '<div id="product-social-box-item"><a href="'.$sanitizer->cleaninput($socialmedia_option2).'" target="_blank">'.$sanitizer->cleaninput($socialmedia_option2).'</a></div>'; } 
									if($socialmedia_option3 != false) { echo '<div id="product-social-box-item"><a href="'.$sanitizer->cleaninput($socialmedia_option3).'" target="_blank">'.$sanitizer->cleaninput($socialmedia_option3).'</a></div>'; }
							echo '</div>';

				echo '</div></div></div>'; // closing div
							
							echo '<div id="product-footer"></div>';
							
							} else {
								echo "Product cannot be shown.";
							}
	 
							break;
						}
						
						$i++;
					}

			} else {
				echo "<p class='book'><em>Shop database is empty... edit the JSON database through shop.csv, and add products.</em></p>";
			}
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
	OpenShop.toggle(<?php echo $catid;?>,'8');
}

OpenShop.tinyEvents('categories');

</script>
</body>
</html>
