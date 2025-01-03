<?php

require_once(__DIR__."/../../core/Logging.php");

if(isset($shop)) {
	$host = $shop->host();
	} else {
	require("class.Shop.php");
	$shop  = new Shop;
	$host = $shop->host();
}

$logger = new Logging;

$logging 		= false;
$logginglocations 	= false;
$path 			= '';
$site 			= $shop->json->load_json("server/config/site.conf.json");
$title 			= $shop->sanitizer->cleaninput($site[0]['site.title']);
$logging 		= $shop->sanitizer->cleaninput($site[0]['site.logging']);
$searchbar  	= $shop->sanitizer->cleaninput($site[0]['site.searchbar']);
$pricebar  		= $shop->sanitizer->cleaninput($site[0]['site.pricebar']);
$optionbar  	= $shop->sanitizer->cleaninput($site[0]['site.optionbar']);


if(!$searchbar) {
	$searchbar	= false;
}

if(!$pricebar) {
	$pricebar	= false;
}

if(!$optionbar) {
	$optionbar	= false;
} 

if(!$pricebar) {
	$pricebar	= false;
} else {
	$pricebarvalues  = $shop->sanitizer->cleaninput($site[0]['site.pricebar.values']);
}

if(isset($logging)) {
	
	if(trim($logging) == '1' || strtolower(trim($logging)) == 'yes') {
		
		$logginglocations = $shop->sanitizer->cleaninput($site[0]['site.logging.locations']);
		
		$uri = $shop->sanitizer->sanitize($_SERVER['REQUEST_URI'],'dir');

		if(strlen($uri) <= 100) {
			
			if(stristr($uri,'/')) {
				
				$uripieces = explode('/',$uri);
				$curi = count($uripieces);

				for($t=0;$t<$curi;$t++) {

				switch($uripieces[$t]) {
					case 'checkout':
					$path = 'checkout';
					break;
					case 'payment':
					$path = 'payment';
					break;
					case 'cart':
					$path = 'cart';
					break;
					case 'blog':
					$path = 'blog';
					break;
					case 'pages':
					$path = 'pages';
					break;					
					case 'articles':
					$path = 'articles';
					break;				
					}
				}
				
			}
		} else {
			$path = 'shop';
		}
		
		if(strlen($path) <=3) {
			$path = 'shop';
		}

		if(stristr($logginglocations,',')) {
			$locations = explode(',',$logginglocations);
			if(in_array($path,$locations)) {
				$logger->logging($path);
			}
		} else {
			if(strlen($logginglocations) >=4) {
				$logger->logging($path);
			}
		}
	}
}

if(!$title) {
	$title = 'Webshop Name';
} 

?>
<header>
<h1 id="logo"><span id="logo-left"><?php echo $title;?></span></h1>
<?php
echo $shop->navigation($host);
?>
</header>

<?php 
if($searchbar == '1') {
?>
<div id="searchbar">
	<form name="tiny-search" action="<?php echo $host;?>search/" method="post">
	<input type="search" name="search" value="" placeholder="I am looking for..." autocomplete="false" history="true" id="searchbar-input" alt="search for a product" title="search for a product" /><input type="submit" name="search-button" id="search-button" value="search" />
	</form>
</div>
<?php
}
?>
