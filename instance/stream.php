<?php

include("../resources/php/class.Shop.php");
	
$shop  = new Shop();

if(isset($_GET['streamtoken']))  {
	if($_GET['streamtoken'] == $_SESSION['streamtoken']) {
		//token is correct.
		} else {
			echo 'Token is incorrect, stream cannot be initated.';
		exit;
	}
} else {
	echo 'A token was not given, aborting code execution.';
	exit;
}

if(isset($_GET['id'])) {
	$id = (int)$_GET['id'];
	} else {
	echo 'A stream id was not given, aborting code execution.';
	exit;
}

$product_list = $shop->decode('../inventory/shop.json');

$iv = array();
				
$i = 0;
				
foreach($product_list as $c) {	
					
		array_push($iv,$c);
						
		if($iv[$i]['product.id'] == $id) {

			if($iv[$i]['product.status'] == '1') {
				
					$video 	= $shop->cleaninput($iv[$i]['product.video']);
					$audio 	= $shop->cleaninput($iv[$i]['product.audio']);
												
					if(isset($video) && ($video != "") && ($_GET['type'] == 'video')) {
						$file = $video;
					}
												
					if(isset($audio) && ($audio != "") && ($_GET['type'] == 'audio')) {
						$file = $audio;
					}
			}
			
			break;
		}
		
		$i++;
}


if(isset($_GET['type'])) {
		switch($_GET['type']) {
			case 'audio':
			header('Content-type: audio/mpeg');
			header('Content-length: ' . filesize($file));
			header('Cache-Control: no-cache');
			header("Content-Transfer-Encoding: binary"); 
			header("Content-Type: audio/mpeg");
			break;
			case 'video':
			header('Content-type: video/mp4');
			header('Content-length: ' . filesize($file));
			header('Cache-Control: no-cache');
			header("Content-Transfer-Encoding: binary"); 
			header("Content-Type: video/mp4");
			break;
			default:
			exit;
		}
	} else {
	echo 'A stream id was not given, aborting code execution.';
	exit;
}

readfile($file);

unset($_SESSION['streamtoken']);
$_SESSION['streamtoken'] = null;
?>