<?php

	session_start();

	include("../../resources/PHP/Class.Shop.php");
	$shop  = new Shop();

	if(!isset($_SESSION['admin-uuid']) || empty($_SESSION['admin-uuid'])) {
		exit;
	}
		
	if($_GET['token'] != $_SESSION['uuid']) {
		echo 'Token is incorrect, cannot download file.';
		exit;
	}

header('Content-Type: text/csv');

$file = false;

switch($_GET['file']) {

	case 'tax':
	$file = '../../server/config/csv/tax.conf.csv';
	$name = 'tax.conf.csv';
	break;

	case 'shipping':
	$file = '../../server/config/csv/shipping.conf.csv';
	$name = 'shipping.conf.csv';
	break;
	
	case 'payment':
	$file = '../../server/config/csv/payment.conf.csv';
	$name = 'payment.conf.csv';
	break;	
	
	case 'messages':
	$file = '../../server/config/csv/messages.conf.csv';
	$name = 'messages.conf.csv';
	break;
	
	case 'currencies':
	$file = '../../server/config/csv/currencies.conf.csv';
	$name = 'currencies.conf.csv';
	break;		
	
	case 'conf':
	$file = '../../server/config/csv/site.conf.csv';
	$name = 'site.conf.csv';
	break;
	
	case 'navigation':
	$file = '../../inventory/csv/navigation.csv';
	$name = 'navigation.csv';
	break;
	
	case 'paypal':
	$file = '../../server/config/csv/paypal.csv';
	$name = 'paypal.csv';
	break;
	
	case 'shop':
	$file = '../../inventory/csv/shop.csv';
	$name = 'shop.csv';
	break;
	
	case 'categories':
	$file = '../../inventory/csv/categories.csv';
	$name = 'categories.csv';
	break;
	
	case 'subcategories':
	$file = '../../inventory/csv/subcategories.csv';
	$name = 'subcategories.csv';
	break;
	
	case 'articles':
	$file = '../../inventory/csv/articles.csv';
	$name = 'articles.csv';
	break;	
	
	case 'blog':
	$file = '../../inventory/csv/blog.csv';
	$name = 'blog.csv';
	break;	
	
	case 'pages':
	$file = '../../inventory/csv/pages.csv';
	$name = 'pages.csv';
	break;	
	
	default:
	$file = false;
	break;
}

if($file != false) {
header('Content-Disposition: attachment; filename="'.$name.'"');
readfile($file);
}
?>
