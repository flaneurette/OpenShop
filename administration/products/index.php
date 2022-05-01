<?php
	error_reporting(E_ALL); 
	session_start(); 
	session_regenerate_id();
	include("../../resources/PHP/Class.Shop.php");
	include("../../core/Cryptography.php");
	
	$shop  		  = new Shop;
	$cryptography = new Cryptography();
	
	if(!isset($_SESSION['admin-uuid']) || empty($_SESSION['admin-uuid'])) {
		exit;
	}
	
	// create a new admin token
	if(!isset($_SESSION['uuid'])) {
		$token  = $cryptography->uniqueID();
		$token .= $cryptography->uniqueID();
		$token .= $cryptography->uniqueID();
		$token .= $cryptography->uniqueID();
		$_SESSION['uuid'] = $token;		
	} else {
		$token = $_SESSION['uuid'];
	}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="../resources/admin.css">
</head>
<body>
<nav>
	<div><h2><a href="../">Administration</a></h2> <h2><a href="./">Products</a></h2> <h2><a href="../upload-csv/">Upload CSV</a></h2>  <h2><a href="../upload-json/">Upload JSON</a></h2> <h2><a href="../downloads/">Downloads</a></h2> <h2><a href="../upload-images/">Upload Images</a></h2> <h2><a href="../lightbox/">Lightbox</a></h2> <h2><a href="../logs/">Logs</a></div>
</nav>
<hr />
<h1>Inventory</h1>
<?php

	$products = $shop->getproducts('rows','all',false,false,false,$_SESSION['token']);
	
	if(count($products) >=1) { 
	
		$products  = array_reverse($products);
	
		$string = '<table width="100%" cellspacing="0" style="border-style:table;">';
		$string .= '<tr>';
		$string .= '<td>#</td>';
		$string .= '<td>Id</td>';
		$string .= '<td>Title</td>';
		$string .= '<td>Description</td>';
		$string .= '<td>Category</td>';
		$string .= '<td>Price</td>';
		$string .= '<td>Stock</td>';
		$string .= '</tr>';
	
		for($i=0;$i<count($products);$i++) {
			$string .= '<tr>';
			for($k=0;$k<count($products[$i]);$k++) {
			$string .= '<td style="border-bottom:1px solid grey;border-style:table;">'.$products[$i][$k].'</td>';
			}
			$string .= '</tr>';
		}	
		$string .= '</table>';
		
	} else {
		 $string = "There are not enough product to show. Please download shop.csv and add products.";
	}
	echo $string;
?>
</div>
</body>
</html>
