<?php

include_once('../backendphp/layout/linkstyle.php');
	
		echo '<br><div>';
				if (allowedToOpen(822,'1rtc')) {
					echo '<a id=\'link\' href="lookupwithedit.php?w=TotalTaxAndNonTax">Monthly Salaries Per Company</a> ';
				}
				if (allowedToOpen(800,'1rtc')) {
					echo '<a id=\'link\' href="govtsummaries.php?w=SSS">SSS Summary</a> ';
				}
				if (allowedToOpen(799,'1rtc')) {
					echo '<a id=\'link\' href="govtsummaries.php?w=PHIC">Philhealth Summary</a> ';
				}
				if (allowedToOpen(797,'1rtc')) {
					echo '<a id=\'link\' href="govtsummaries.php?w=PagIbig">PagIbig Summary</a> ';
				}
				if (allowedToOpen(802,'1rtc')) {
					echo '<a id=\'link\' href="govtsummaries.php?w=WTax">Withholding Tax Summary</a> ';
				}
				if (allowedToOpen(801,'1rtc')) {
					echo '<a id=\'link\' href="govtsummaries.php?w=SSSLoans">SSS-Salary Loans Summary</a> ';
				}
				
				if (allowedToOpen(801,'1rtc')) {
					echo '<a id=\'link\' href="govtsummaries.php?w=SSSLoansCalamity">SSS-Calamity Loans Summary</a> ';
				}
				
				if (allowedToOpen(798,'1rtc')) {
					echo '<a id=\'link\' href="govtsummaries.php?w=PagIbigLoans">Pagibig-Salary Loans Summary</a> ';
				}
				if (allowedToOpen(798,'1rtc')) {
					echo '<a id=\'link\' href="govtsummaries.php?w=PagIbigLoansCalamity">Pagibig-Calamity Loans Summary</a> ';
				}
		echo '</div><br/>';
	
?>