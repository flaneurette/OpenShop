<?php

	echo '<div id="shop-floor">';
	
	if(isset($shop)) { 
		$shopfloor = $shop->subcategories;
	}
	
	if(isset($shopfloor) && count($shopfloor) > 0) { 
	
		foreach($shopfloor as $floor => $item) {
		
				echo '<div class="shop-floor-item">'.$item.'</div>';
		}
		
		echo '</div>';
	}
?>