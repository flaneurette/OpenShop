<div id="pricebar">
	<div class="pricebar-item">PRICEBAR</div>
				<div>
						<ul>
								<?php 
										if(isset($pricebarvalues)) {
											echo $shop->getpricebar($pricebarvalues);
										}
								?>
						</ul>
			</div>
</div>
