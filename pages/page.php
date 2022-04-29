<?php

	include("../resources/PHP/Header.inc.php");
	include("../resources/PHP/Class.Shop.php");
	
	include("../core/Cryptography.php");
	
	$shop  		  = new Shop();
	$cryptography = new Cryptography();
	
	$token = $cryptography->getToken();
	
	$_SESSION['token'] = $token;
	
	if(isset($_GET['cat'])) {
		$cat = (int) $_GET['cat'];
	}

	if(isset($_GET['pageid'])) {
		$pageid = (int) $_GET['pageid'];
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
	echo $shop->getmeta("../server/config/site.conf.json");				
?>

<link rel="stylesheet" type="text/css" href="<?php echo $baseurl;?>resources/style/pages.css">

</head>

<body>

<?php
include("../resources/PHP/Header.php");
?>

<div id="wrapper">

	<h1>Pages</h1>
		
	<div id="ts-shop-page"></div>
<?php
	$json = "../inventory/pages.json";
	$pagelist = $shop->getpagelist($json,'pages');
				
	$num = count($pagelist);
	
	if($num >=1) {
		
	foreach($pagelist as $row)	{

		if(isset($pageid) >=1) {
			
			if($row['page.id'] == $pageid) {
				?>
					<div class="ts-shop-page-item">
						<div class="ts-shop-page-item-header-focus">
						<?php
						if(strlen($row['page.image.header']) > 30) {
							echo '<img src="'.str_replace('../','',$baseurl.$shop->cleanInput($row['page.image.header'])).'" width="" height="" />';
						}
						?>
						</div>
						<div class="ts-shop-page-item-main">
							<div class="ts-shop-page-item-title">
								<h1><?php echo $shop->cleanInput($row['page.title']);?></h1>
							</div>
							<div class="ts-shop-page-item-titles">
								<?php 
										
									if($row['page.handle'] != '') {
										echo '<span class="ts-shop-page-item-author">By '.$shop->cleanInput($row['page.handle']).'</span>';
										} elseif($row['page.author'] != '') { 
										echo '<span class="ts-shop-page-item-author">By '.$shop->cleanInput($row['page.author']).'</span>';
									} else {}	
								?>
								<span class="ts-shop-page-item-date"><?php echo $shop->cleanInput($row['page.published']);?></span>
							</div>
							<div class="ts-shop-page-item-textbox"><?php echo $shop->cleanpageoutput($row['page.long.text']);?></div>
							</div>
							<div class="ts-shop-page-item-main-footer">
								<span class="ts-shop-page-item-tags"><?php echo $shop->cleanInput($row['page.tags']);?></span>
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
				if(strlen($row['page.image.header']) > 30) {
					echo '<img src="'.$shop->cleanInput($row['page.image.header']).'" />';
				}
				?>
				</div>
				<div class="ts-shop-page-item-main">
					<div class="ts-shop-page-item-title">
					<h1><?php echo $shop->cleanInput($row['page.title']);?></h1>
					</div>
					<div class="ts-shop-page-item-titles">
						<?php 
								
							if($row['page.handle'] != '') {
								echo '<span class="ts-shop-page-item-author">By '.$shop->cleanInput($row['page.handle']).'</span>';
								} elseif($row['page.author'] != '') { 
								echo '<span class="ts-shop-page-item-author">By '.$shop->cleanInput($row['page.author']).'</span>';
							} else {}	
						?>
						<span class="ts-shop-page-item-date"><?php echo $shop->cleanInput($row['page.published']);?></span>
					</div>
					<div class="ts-shop-page-item-textbox"><?php echo $shop->cleanInput($row['page.short.text']);?></div>
					</div>
					<div class="ts-shop-page-item-main-footer">
						<span class="ts-shop-page-item-rm"><a href="<?php echo (int)$shop->cleanInput($row['page.id']);?>/<?php echo $shop->seoUrl($shop->cleanInput($row['page.title']));?>/">read more &raquo;</a></span> 
						<span class="ts-shop-page-item-tags"><?php echo $shop->cleanInput($row['page.tags']);?></span>
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