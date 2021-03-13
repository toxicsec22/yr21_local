<?php

echo '<br> &nbsp; ';
if ((!allowedToOpen(7134,'1rtc')) AND (!allowedToOpen(7135,'1rtc')) AND (!allowedToOpen(7138,'1rtc'))) {
	echo '<a id=\'link\' href="allreports.php?w=AggregateReports">Aggregate Reports</a> ';
}
// if ((!allowedToOpen(7136,'1rtc')) AND (!allowedToOpen(7138,'1rtc'))){
	// echo '<a id=\'link\' href="allreports.php?w=PerSTLReports">Per STL Report</a> ';
// }
if ((!allowedToOpen(7134,'1rtc')) AND (!allowedToOpen(7135,'1rtc')) AND (!allowedToOpen(7138,'1rtc'))){
	echo '<a id=\'link\' href="allreports.php?w=SalesHistory">Sales History</a> ';
}
if(!allowedToOpen(7138,'1rtc')){
echo '<a id=\'link\' href="allreports.php?w=BranchComparisons">Comparison of Branch Sales</a> ';
}
/* if ((allowedToOpen(7137,'1rtc')) OR (allowedToOpen(7136,'1rtc'))){
	echo '<a id=\'link\' href="allreports.php?w=TaggedUntaggedPrev">Tagged and UnTagged Sales for the Year</a> ';
}
if ((!allowedToOpen(7135,'1rtc')) AND (!allowedToOpen(7138,'1rtc'))){
	echo '<a id=\'link\' href="allreports.php?w=STLANDTargets">STL Sales and Targets for the Year</a> ';
	if (allowedToOpen(7134,'1rtc')){ echo '<a id=\'link\' href="allreports.php?w=MySTLs">MySTL Individual Scores</a> '; }
} */
if ((allowedToOpen(7137,'1rtc')) OR (allowedToOpen(7136,'1rtc')) OR(allowedToOpen(7138,'1rtc'))){
	
	echo '<a id=\'link\' href="allreports.php?w=NumberOfTransactions">Number Of Transactions</a> ';
	if((!allowedToOpen(7138,'1rtc'))){
		echo '<a id=\'link\' href="allreports.php?w=TransactionsPerBranch">Number Of Transactions Per Branch</a> ';
	}
}
if(!allowedToOpen(7138,'1rtc')){
	echo '<br><br>';
	echo ' &nbsp; <a id=\'link\' href="allreports.php?w=NoOfSales">Number Of Clients with Sales</a> ';
}
if ((allowedToOpen(7137,'1rtc')) OR (allowedToOpen(7136,'1rtc'))){
	echo '<a id=\'link\' href="allreports.php?w=LeadsAndWins">Leads And Wins</a> ';
}
if ((allowedToOpen(7131,'1rtc'))){
	echo '<a id=\'link\' href="marketsharegraphs.php">Sales By Client Type</a> ';
	echo '<a id=\'link\' href="marketsharegraphs.php?w=MarketShare">Market Share of In-house Brands</a> ';
}

?>