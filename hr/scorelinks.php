<?php
include_once('../backendphp/layout/linkstyle.php');
		echo '<br><div>';
		if (allowedToOpen(65072,'1rtc')){	//JYE / RCE	/ HR Head
			echo '<a id=\'link\' href="scoresettings.php?w=PointSettings">Points Settings</a> ';
		}
		// if (allowedToOpen(65071,'1rtc')){ //Dept Heads
		if (allowedToOpen(array(65071,65076),'1rtc')){ //Dept Heads //Others
			echo '<a id=\'link\' href="scores.php?w=MeritsList">Statement List (Merits)</a> ';
			echo '<a id=\'link\' href="scores.php">Statement List (Demerits)</a> ';
			echo str_repeat('&nbsp;',10);
		}
		if (allowedToOpen(6507,'1rtc')){ //Reporters
			if (!allowedToOpen(65073,'1rtc')){
				echo '<a id=\'link\' href="scores.php?w=ScoreMerits">Encode Merits</a> ';
				echo '<a id=\'link\' href="scores.php?w=ScoreDemerits">Encode Demerits</a> ';
				echo str_repeat('&nbsp;',10);
			}
			
			// echo '<a id=\'link\' href="scores.php?w=ScoreMeritsMonth">Merits Per Month</a> ';
			// echo '<a id=\'link\' href="scores.php?w=ScoreDemeritsMonth">Demerits Per Month</a> ';
			echo '<a id=\'link\' href="scores.php?w=ScoreMeritsMonth">Status of Merits/Demerits</a> ';
		}
		// if (allowedToOpen(65077,'1rtc')){ 
		//ScoresOfMyTeam
			echo '<a id=\'link\' href="scores.php?w=ScoresOfMyTeam">Scores of My Team</a> ';
		// }
		
		// if (allowedToOpen(65071,'1rtc')){ //Report
		//if (allowedToOpen(array(65071,65077),'1rtc')){ //Report
			echo '<br><br>';
			if (allowedToOpen(65071,'1rtc')){
				echo '<a id=\'link\' href="scores.php?w=ScoreSummary">Scores Of My Department</a> ';
				echo '<a id=\'link\' href="scores.php?w=ScoreSummaryOthers">Scores By My Department</a> ';
				echo '<a id=\'link\' href="scores.php?w=LookupScorePerPersonPerMonth">Lookup Score Per Person</a> ';
			}
			if (allowedToOpen(217,'1rtc')){
				echo '<a id=\'link\' href="scores.php?w=Offense">Offense</a> ';
			}
			echo str_repeat('&nbsp;',10);
			// }
			if (allowedToOpen(65072,'1rtc')){ //JYE/RCE/HR
				echo '<a id=\'link\' href="scores.php?w=ScoreSummaryPending">All Pending Reports</a> ';
				echo '<a id=\'link\' href="scores.php?w=ScoreSummaryAll">All Department Score Summary</a> ';
				
			}
		if (allowedToOpen(65071,'1rtc')){
			echo '<a id=\'link\' href="scores.php?w=NotCountedReports">List of Uncounted Reports</a> ';
		}
		echo '</div><br/>';
?>