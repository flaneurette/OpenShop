<?php


//phpinfo();

    // mail('admin@localhost','test','test','from:<test@localhost>');
	
	// exit;
	
	include("../../resources/PHP/Header.inc.php");
	include("../../resources/PHP/Class.Session.php");
	include("../../resources/PHP/Class.SecureMail.php");
	include("../../Class.Shop.php");
	
	$shop = new Shop;

	$session = new Session;
	
	$session->sessioncheck();
	
	$parameters = array( 
		'to' => 'admin@localhost',
		'name' => 'shop',
		'email' => 'shop@localhost',				
		'subject' => "A new order was placed in the shop today.",
		'body' => 'body text'
	);

	$ordermail = new \security\forms\SecureMail($parameters);
	$ordermail->sendmail();

	// destroy cart session.
	/*
		$_SESSION['cart']  = array();
		$_SESSION['token'] = null;
		$_SESSION['messages'] = array();
		session_destroy();
	*/
?>
test