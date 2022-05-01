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
	<div><h2><a href="../">Administration</a></h2> <h2><a href="../products/">Products</a></h2> <h2><a href="./">Upload CSV</a></h2>  <h2><a href="../upload-json/">Upload JSON</a></h2> <h2><a href="../downloads/">Downloads</a></h2> <h2><a href="../upload-images/">Upload Images</a></h2> <h2><a href="../lightbox/">Lightbox</a></h2> <h2><a href="../logs/">Logs</a></div>
</nav>
<hr />
<h1>Upload CSV files, and convert to JSON</h1>
<div>Select a CSV file to process...</div>
<hr />
<form name="" action="../" method="post" enctype="multipart/form-data">
	<input type="hidden" name="upload_csv" value="1">
	<input type="hidden" name="token" value="<?php echo $token;?>">
	<input type="file" name="csv_file[]" style="float:left;margin-right:10px;" multiple>
	<input type="submit" name="submit" value="Upload & Convert CSV">
</form>
<hr />
<small>N.B. your current PHP configuration allows only: <?=ini_get('max_file_uploads');?> simultaneous files to uploaded. To change it, edit PHP.ini max_file_uploads = number.</small>
</div>
</body>
</html>
