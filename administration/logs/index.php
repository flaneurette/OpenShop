<?php
	error_reporting(E_ALL);
	session_start(); 
	session_regenerate_id();
	include("../../resources/php/class.Shop.php");
	$shop  = new Shop();
	
	if(!isset($_SESSION['admin-uuid']) || empty($_SESSION['admin-uuid'])) {
		exit;
	}
	
	// create a new admin token
	if(!isset($_SESSION['uuid'])) {
		$token  = $shop->uniqueID();
		$token .= $shop->uniqueID();
		$token .= $shop->uniqueID();
		$token .= $shop->uniqueID();
		$_SESSION['uuid'] = $token;		
	} else {
		$token = $_SESSION['uuid'];
	}

function logs($filename) {

		$tmp = '';
		if(file_exists($filename)) {
			
			if(filesize($filename) >0) {
				
				$file = fopen($filename,'rt');
				$logfile = fread($file,filesize($filename));
				
				if(strlen($logfile)>1) {	
				
					$logfile = htmlspecialchars($logfile,ENT_QUOTES,'UTF-8');
					echo '<div>Last 200 unique visitors</div>';
					$log = explode("\n",$logfile);
					$log = array_unique($log);
					$c = count($log);

					if($c > 200) {
						$c = 200;
					}
					for($i=0;$i<$c;$i++) {
						if(!empty($log[$i])) {
							$tmp .= ''. htmlspecialchars($log[$i],ENT_QUOTES,'UTF-8').'<br />'.PHP_EOL;
						}
					}
					
				} else {
					$tmp .= 'Logfile is empty. No-one visisted, start promoting it!';
				}
			
			} else {
				$tmp .= 'Logfile is empty. No-one visisted, start promoting it!';
			}
	}
return $tmp;
}

?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="../resources/admin.css">
<script>
function toggle(id, counter) {
		
	for (i = 1; i <= counter; i++) {
			
		try {
			document.getElementById('toggle' + i).style.display = 'none';
			} catch (e) {
			continue;
		}
	}
		
	document.getElementById('toggle' + id).style.display = 'block'; 
}

</script>
</head>
<body>
<nav>
	<div><h2><a href="../">Administration</a></h2> <h2><a href="../products/">Products</a><h2><a href="../upload-csv/">Upload CSV</a></h2>  <h2><a href="../upload-json/">Upload JSON</a></h2> <h2><a href="../downloads/">Downloads</a></h2> <h2><a href="../upload-images/">Upload Images</a></h2> <h2><a href="../lightbox/">Lightbox</a></h2> <h2><a href="./">Logs</a></h2></div>
</nav>
<hr />
<h1>Shop log.</h1>
<ul>
<li><a href="#" onclick="toggle(1,7);">Shop</a></li>
<li><a href="#" onclick="toggle(7,7);">Cart</a></li>
<li><a href="#" onclick="toggle(2,7);">Payment</a></li>
<li><a href="#" onclick="toggle(3,7);">Checkout</a></li>
<li><a href="#" onclick="toggle(4,7);">Articles</a></li>
<li><a href="#" onclick="toggle(5,7);">Pages</a></li>
<li><a href="#" onclick="toggle(6,7);">Blogs</a></li>
</ul>


<div style="font-size:10px;font-family:monospace;display:none;padding-top:25px;" id="toggle1">
<?php

	$log = logs('../../server/logging/shop/log.log');
	$list = explode('\n',$log);
	 
	if(count($list) > 1) {
		 
		 for($i=0;$i<count();$i++) {
			 
		 }
		 
	} else {
		echo $log;
	}
	 
?>
</div>

<div style="font-size:10px;font-family:monospace;display:none;padding-top:25px;" id="toggle2">
<?php
 echo logs('../../server/logging/payment/log.log');
?>
</div>

<div style="font-size:10px;font-family:monospace;display:none;padding-top:25px;" id="toggle3">
<?php
 echo logs('../../server/logging/checkout/log.log');
?>
</div>

<div style="font-size:10px;font-family:monospace;display:none;padding-top:25px;" id="toggle4">
<?php
 echo logs('../../server/logging/articles/log.log');
?>
</div>

<div style="font-size:10px;font-family:monospace;display:none;padding-top:25px;" id="toggle5">
<?php
 echo logs('../../server/logging/pages/log.log');
?>
</div>

<div style="font-size:10px;font-family:monospace;display:none;padding-top:25px;" id="toggle6">
<?php
 echo logs('../../server/logging/blog/log.log');
?>
</div>
<div style="font-size:10px;font-family:monospace;display:none;padding-top:25px;" id="toggle7">
<?php
 echo logs('../../server/logging/cart/log.log');
?>
</div>



</body>
</html>
