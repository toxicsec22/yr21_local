<?php
	$plcase='CASE 
				WHEN
					PriceLevel = 1 THEN lmp.PriceLevel1
				WHEN
					PriceLevel = 2 THEN lmp.PriceLevel2
				WHEN
					PriceLevel = 3 THEN lmp.PriceLevel3
				WHEN
					PriceLevel = 4 THEN lmp.PriceLevel4
				ElSE
					lmp.PriceLevel5
			END';
?>