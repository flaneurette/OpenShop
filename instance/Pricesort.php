<?php

require_once("core/PriceBar.php");
$pricebar = new PriceBar;

?>
<div id="price-sort">
			<ul>
				<?php 
										
					if(isset($pricebarvalues)) {
						echo $pricebar->getpricebar($pricebarvalues);
					}
				?>
			</ul>
</div>
