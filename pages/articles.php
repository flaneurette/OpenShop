<?php

	require_once("../resources/PHP/Header.inc.php");
	require_once("../resources/PHP/Class.Shop.php");
	require_once("../core/Cryptography.php");
	require_once("../core/Sanitize.php");
	include_once("../core/Meta.php");
	
	$shop  		  = new Shop;
	$cryptography = new Cryptography;
	$sanitizer 	  = new Sanitizer;
	$metafactory  = new Meta;
	
	$token = $cryptography->getToken();
	$_SESSION['token'] = $token;
	
	if(isset($_GET['cat'])) {
		$cat = (int) $_GET['cat'];
	}

	if(isset($_GET['articleid'])) {
		$articleid = (int) $_GET['articleid'];
	}
	
	if(isset($_GET['page'])) {
		$page = (int) $_GET['page'];
		} else {
		$page = 1;
	}
	
	$baseurl = $shop->getbase();
	
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=0.73">
	
<?php
	echo $metafactory->getmeta("../server/config/site.conf.json");				
?>

<link rel="stylesheet" type="text/css" href="<?php echo $baseurl;?>resources/style/pages.css">

</head>

<body>

<?php
include("../resources/PHP/Header.php");
?>

<div id="wrapper">

<h2>Articles</h2>
		
	<div id="ts-shop-page"></div>
<?php
	$json = "../inventory/articles.json";
	$pagelist = $shop->getpagelist($json,'articles');
				
	$num = count($pagelist);
	
	if($num >=1) {
		
	foreach($pagelist as $row)	{
	
			if(isset($articleid) >=1) {
			
				if($row['article.id'] == $articleid && $row['article.status'] == 1) {
				?>

					<div class="ts-shop-page-item">
						<div class="ts-shop-page-item-header-focus">
						<?php
						if(strlen($row['article.image.header']) > 30) {
							echo '<img src="' . str_replace('../','',$baseurl.$sanitizer->cleaninput($row['article.image.header'])) . '" />';
						}
						?>
						</div>
						<div class="ts-shop-page-item-main">
							<div class="ts-shop-page-item-title">
							<h1><?php echo $sanitizer->cleaninput($row['article.title']);?></h1>
							</div>
							<div class="ts-shop-page-item-titles">
								<?php 
								
								if(isset($row['article.handle']) != '') {
									echo '<span class="ts-shop-page-item-author">By '.$sanitizer->cleaninput($row['article.handle']).'</span>';
									} elseif(isset($row['article.author']) != '') { 
									echo '<span class="ts-shop-page-item-author">By '.$sanitizer->cleaninput($row['article.author']).'</span>';
								} else {}
								
								?>
								<span class="ts-shop-page-item-date"><?php echo $sanitizer->cleaninput($row['article.published']);?></span>
							</div>
							<div class="ts-shop-page-item-textbox"><?php echo $sanitizer->format($row['article.long.text']);?></div>
							
							</div>
							<div class="ts-shop-page-item-main-footer">
								<span class="ts-shop-page-item-tags"><?php echo $sanitizer->cleaninput($row['article.tags']);?></span>
							</div>
						</div>	
					</div>
			
					
				<?php
				}
			
			} else {
	?>
				
			<div class="ts-shop-page-item">
				<div class="ts-shop-page-item-header">
				<?php
				if(strlen($row['article.image.header']) > 30) {
					echo '<img src="'.$sanitizer->cleaninput($row['article.image.header']).'" />';
				}
				?>
				</div>
				<div class="ts-shop-page-item-main">
					<div class="ts-shop-page-item-title">
					<h1><?php echo $sanitizer->cleaninput($row['article.title']);?></h1>
					</div>
					<div class="ts-shop-page-item-titles">
								<?php 
								
								if($row['article.handle'] != '') {
									echo '<span class="ts-shop-page-item-author">By '.$sanitizer->cleaninput($row['article.handle']).'</span>';
									} elseif($row['article.author'] != '') { 
									echo '<span class="ts-shop-page-item-author">By '.$sanitizer->cleaninput($row['article.author']).'</span>';
								} else {}
								
								?>
						<span class="ts-shop-page-item-date"><?php echo $sanitizer->cleaninput($row['article.published']);?></span>
					</div>
					<div class="ts-shop-page-item-textbox"><?php echo $sanitizer->cleaninput($row['article.short.text']);?></div>
					
					</div>
					<div class="ts-shop-page-item-main-footer">
						<span class="ts-shop-page-item-rm"><a href="<?php echo (int)$sanitizer->cleaninput($row['article.id']);?>/<?php echo $shop->seoUrl($sanitizer->cleaninput($row['article.title']));?>/">read more &raquo;</a></span> 
						<span class="ts-shop-page-item-tags"><?php echo $sanitizer->cleaninput($row['article.tags']);?></span>
					</div>
				</div>	
			</div>

		<?php
			}
		}
	} else {
		echo "No articles have been written yet.";
	}
	
	?>
</div>

<?php
include("../resources/PHP/Footer.php");
?>
</body>
</html>