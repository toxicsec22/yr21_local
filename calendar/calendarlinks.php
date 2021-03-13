<?php
include_once('../backendphp/layout/linkstyle.php');

	echo '<br><br>';
	if (allowedToOpen(5630,'1rtc')) {
		echo '<a id=\'link\' href="calendarsettings.php?w=DeptColorSettings">Department Color</a> ';
	}
	if (allowedToOpen(5631,'1rtc')) {
		echo '<a id=\'link\' href="calendarsettings.php?w=ColorSettings">Color Settings</a> ';
	}
	if (allowedToOpen(5632,'1rtc')) {
		echo '<a id=\'link\' href="calendarsettings.php?w=Schedule">Schedule A Visit</a> ';
	}
	echo '<a id=\'link\' href="calendarschedview.php">Calendar</a> ';
	if (allowedToOpen(5631,'1rtc')) {
		echo '<a id=\'link\' href="calendarsettings.php?w=SchedSummary">Schedule Summary</a> ';
	}
	echo '<br><br>';


?>