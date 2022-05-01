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
	<div><h2><a href="../">Administration</a></h2> <h2><a href="../products/">Products</a><h2><a href="../upload-csv/">Upload CSV</a></h2>  <h2><a href="../upload-json/">Upload JSON</a></h2> <h2><a href="../downloads/">Downloads</a></h2> <h2><a href="./">Upload Images</a></h2> <h2><a href="../lightbox/">Lightbox</a></h2> <h2><a href="../logs/">Logs</a></div>
</nav>
<hr />
<h1>Upload images.</h1>

<div>Select an image to process...</div>
<hr />
	<form name="" action="../" method="post" enctype="multipart/form-data">
	<input type="hidden" name="token" value="<?php echo $token;?>">
		<select name="destination" style="float:left;margin-right:10px;">
			<option value="">Place in category...</option>
			<option value="articles">articles</option>
			<option value="blog">blog</option>
			<option value="pages">pages</option>
			<?php
				$category    = $shop->load_json("../../inventory/categories.json");
				$subcategory = $shop->load_json("../../inventory/subcategories.json");
				echo $shop->categorylist('all',$category, $subcategory);
			?>
		</select> 
		<input type="hidden" name="upload" value="1" /> 
		
		<input type="file" name="files[]" style="float:left;" multiple> 
		
		<input type="submit" name="submit" value="Upload Image" style="float:left;"/> 
		<hr />
		<small>N.B. If there are no categories, please create categories through uploading the categories.csv and subcategories.csv first, as this list is dynamically created from these files.</small>
		<hr />
	</form>
</div>
</body>
</html>