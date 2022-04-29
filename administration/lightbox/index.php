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
	<div><h2><a href="../">Administration</a></h2> <h2><a href="../products/">Products</a></h2> <h2><a href="../upload-csv/">Upload CSV</a></h2>  <h2><a href="../upload-json/">Upload JSON</a></h2> <h2><a href="../downloads/">Downloads</a></h2> <h2><a href="../upload-images/">Upload Images</a></h2> <h2><a href="./">Lightbox</a></h2> <h2><a href="../logs/">Logs</a></h2></div>
</nav>
<hr />
<h1>Product Images</h1>
<div>
<?php 

function scanimages($path) {
	
	if(is_dir($path)) {
		$images = scandir($path,1);
		$c = count($images);
		unset($images[$c]);
		unset($images[$c-1]);
		return $images;
	}
}

$imagelist = scanimages('../../resources/images/products/');

$ignore = ['carbon-offset.png','icons/carbon-offset.png','offset.png','product-screen.png','pricebar.png','placeholder.png','static.png'];

for($i=0;$i<count($imagelist);$i++) {
	
	if(is_dir('../../resources/images/products/'.$imagelist[$i])) {
		
		if($imagelist[$i] !='') {
			
			$listsubdir = scanimages('../../resources/images/products/'.$imagelist[$i]);

			for($j=0;$j<count($listsubdir);$j++) {
				
				if(stristr($listsubdir[$j],'.') || stristr($listsubdir[$j],'..')) {
						$listsubdir[$j] = '';
				} 
				$imgtest = strtolower($listsubdir[$j]);
				if(stristr($imgtest,'.png') || stristr($imgtest,'.jpg') || stristr($imgtest,'.gif')) {
								if(!in_array($imgtest,$ignore)) {
									//echo '<a href="../../resources/images/products/'.$imagelist[$i].$listsubdir[$j].'" target="_blank"><img src="'.'../../resources/images/products/'.$imagelist[$i].$listsubdir[$j].'" width="50" style="float:left; margin:3px; border:1px solid #000;"></a> ';
								}
				}
								
				if($listsubdir[$j] !='') {
					$subdir = '../../resources/images/products/'.$imagelist[$i].'/'.$listsubdir[$j].'/';

					$images = scanimages($subdir);	
					for($k=0;$k<count($images);$k++) {
						$imgtest = strtolower($images[$k]);
						if(stristr($imgtest,'.png') || stristr($imgtest,'.jpg') || stristr($imgtest,'.gif')) {
								if(!in_array($imgtest,$ignore)) {
									//echo '<a href="'.$subdir.$images[$k].'" target="_blank"><img src="'.$subdir.$images[$k].'" width="100" style="float:left; margin:3px; border:1px solid #000;"></a> ';
								}
						} else {

						$subsubdir = '../../'.str_replace(['products/','../','..','//'],'',$subdir.$imgtest.'/');
						$imgm = scanimages($subsubdir);
							if($imgm != NULL) {
							$imgm = array_unique($imgm);
								for($m=0;$m<count($imgm);$m++) {
								$imgtestm = strtolower($imgm[$m]);
									if(stristr($imgtestm,'.png') || stristr($imgtestm,'.jpg') || stristr($imgtestm,'.gif')) {
											if(!in_array($imgtestm,$ignore)) {
												echo '<a href="'.$subsubdir.$imgm[$m].'" target="_blank"><img src="'.$subsubdir.$imgm[$m].'" width="100" style="float:left; margin:3px; border:1px solid #000;"></a> ';
											}
									}
								}
							}
					    }
					}
				}
			}
		}
	} 
}

?>
</div>

</body>
</html>
