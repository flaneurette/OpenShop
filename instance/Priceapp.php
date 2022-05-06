<?php

require_once("core/PriceBar.php");
$pricebar = new PriceBar;

?>
<div id="pricebar">
	<h2>PRICEBAR</h2>
				<div>
						<ul>
								<?php 
										
										if(isset($pricebarvalues)) {
											echo $pricebar->getpricebar($pricebarvalues);
										}
								?>
						</ul>
			</div>
</div>
