<?php
if (!allowedToOpen(250,'1rtc')) {
	include('../backendphp/functions/checkeditablemain.php');
	} else {
		include('../backendphp/functions/checkeditablemainacctg.php');
	}
?>