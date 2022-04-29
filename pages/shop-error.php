<?php

	include("../resources/PHP/Header.inc.php");
	include("../resources/PHP/Class.Shop.php");
		
	$shop  = new Shop();
		
	$reason = (int)$_REQUEST['reason'];

	if($reason) {

		switch($reason) {
			case 1:
			$message = "Webshop is with vacation.";
			break;
			case 2:
			$message = "Webshop is offline.";
			break;
			case 3:
			$message = "Webshop is closed.";
			break;
			default:
			$message = "Unknown error.";
		}

	} else  {
		$message = "Unknown error.";
	}
?>
<!DOCTYPE html>
<html>
	<head>
	<?php
	echo $shop->getmeta("../server/config/site.conf.json");				
	?>
	</head>
	<body>
		<h1>Shop Message</h1>
			<div id="ts.shop.error">
				<?php
					echo $shop->cleanInput($message);
				?>
			</div>
	</body>
</html>