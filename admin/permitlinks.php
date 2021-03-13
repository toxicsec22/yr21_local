<?php
if(isset($_GET['w']) AND $_GET['w']<>"Print"){
	if (allowedToOpen(8291,'1rtc')) {
		echo '<br><br>';
		if (allowedToOpen(8294,'1rtc')) {
			echo '<a id=\'link\' href="permitsettings.php?w=PermitSettings">Permit Settings</a> ';
			echo '<a id=\'link\' href="permits.php?w=AutoEncode">Generate Data</a> ';
			echo '<a id=\'link\' href="permits.php?w=GenerateMonthlyFrequency">Generate Data (Monthly Frequency)</a> ';
		}
		if (allowedToOpen(8292,'1rtc')) {
			echo '<a id=\'link\' href="permits.php?w=SubmittedButNotReceived">List of Submitted Permits But Not Received</a> ';
			echo '<a id=\'link\' href="permits.php?w=DueDates">Permit Due Dates</a> ';
		}
		echo '<a id=\'link\' href="permits.php?w=List">Permits</a> ';
		echo '<br><br>';
	}
}
?>