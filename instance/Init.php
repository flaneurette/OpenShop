<?php


	include("core/Cryptography.php");
	
	$shop  		  = new Shop();
	$cryptography = new Cryptography();

	// initialize and check session.
	$token = $cryptography->getToken();

	if(isset($_SESSION['token']) && $_SESSION['token'] != false) { 
		
		if(strlen($_SESSION['token']) < 128) {
			$token = $cryptography->getToken();
			} else {
			$token = $shop->sanitize($_SESSION['token'],'alphanum'); 
		}
		
	} 

	// set session
	$_SESSION['token'] = $token;
	
	// initialize category
	$category = $shop->sanitize('index','cat');
	$catid 	  = $shop->getcatId($category,$subcat=false);
?>
