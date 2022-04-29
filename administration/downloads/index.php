<?php
	error_reporting(E_ALL);
	session_start(); 
	session_regenerate_id();
	include("../../resources/PHP/Class.Shop.php");
	include("../../core/Cryptography.php");
	
	$shop  		  = new Shop();
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
	<div><h2><a href="../">Administration</a></h2> <h2><a href="../products/">Products</a><h2><a href="../upload-csv/">Upload CSV</a></h2>  <h2><a href="../upload-json/">Upload JSON</a></h2> <h2><a href="./">Downloads</a></h2> <h2><a href="../upload-images/">Upload Images</a></h2> <h2><a href="../lightbox/">Lightbox</a></h2> <h2><a href="../logs/">Logs</a></div>
</nav>
<hr />
<h1>Downloads</h1>
<div>Please note that these files may not be renamed when uploading them.</div>
<hr />
<div>Download inventory files.</div>
	<div>
		<ul>
		<li><a href="download.php?file=shop&token=<?php echo $token;?>">shop.csv</a></li>
		<li><a href="download.php?file=categories&token=<?php echo $token;?>">categories.csv</a></li>
		<li><a href="download.php?file=subcategories&token=<?php echo $token;?>">subcategories.csv</a></li>
		</ul>
	</div>
<hr />

<div>Download shop configuration.</div>
	<div>
		<ul>
		<li><a href="download.php?file=conf&token=<?php echo $token;?>">site.conf.csv</a></li>
		<li><a href="download.php?file=navigation&token=<?php echo $token;?>">navigation.csv</a></li>
		<li><a href="download.php?file=tax&token=<?php echo $token;?>">tax.csv</a></li>
		<li><a href="download.php?file=shipping&token=<?php echo $token;?>">shipping.csv</a></li>
		<li><a href="download.php?file=currencies&token=<?php echo $token;?>">currencies.csv</a></li>
		<li><a href="download.php?file=messages&token=<?php echo $token;?>">messages.csv</a></li>
		</ul>
	</div>
<hr />

<div>Download pages</div>
	<div>
		<ul>
		<li><a href="download.php?file=articles&token=<?php echo $token;?>">articles.csv</a></li>
		<li><a href="download.php?file=blog&token=<?php echo $token;?>">blog.csv</a></li>
		<li><a href="download.php?file=pages&token=<?php echo $token;?>">pages.csv</a></li>
		</ul>
	</div>
<hr />

<div>Download gateway configuration</div>
	<div>
		<ul>
		<li><a href="download.php?file=paypal&token=<?php echo $token;?>">paypal.csv</a></li>
		<li><a href="download.php?file=payment&token=<?php echo $token;?>">payment.csv</a></li>
		</ul>
	</div>
<hr />
</div>
</body>
</html>