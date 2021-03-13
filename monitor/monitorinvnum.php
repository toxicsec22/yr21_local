<?php
	if (isset($result['TxnID'])){
		$txnid=$result['TxnID'];
	}

	$sqltotal = 'SELECT COUNT(TxnSubId) AS TotalNumberAll FROM monitor_2fromsuppliersub WHERE TxnID='.$txnid.'';
	$stmttotal=$link->query($sqltotal);
	$rowtotal = $stmttotal->fetch();
	
	$sqltransferred = 'SELECT COUNT(TxnSubId) AS TotalNumberTransferred FROM monitor_2fromsuppliersub WHERE TxnID='.$txnid.' AND TransferredToCentralWarehouse=1;';
	$stmttransferred=$link->query($sqltransferred);
	$rowtransferred = $stmttransferred->fetch();
	
	$sqlnotransfered = 'SELECT COUNT(TxnSubId) AS TotalNumberNotTransferred FROM monitor_2fromsuppliersub WHERE TxnID='.$txnid.' AND TransferredToCentralWarehouse=0;';
	$stmtnotransfered=$link->query($sqlnotransfered);
	$rownotransfered = $stmtnotransfered->fetch();
	
	$sqlissued = 'SELECT COUNT(TxnSubId) AS TotalIssued FROM monitor_2fromsuppliersub WHERE TxnID='.$txnid.' AND TransferredToCentralWarehouse=1 AND DateIssued IS NOT NULL;';
	$stmtissued=$link->query($sqlissued);
	$rowissued = $stmtissued->fetch();
	
	$sqlaccepted = 'SELECT COUNT(TxnSubId) AS TotalAccepted FROM monitor_2fromsuppliersub WHERE TxnID='.$txnid.' AND TransferredToCentralWarehouse=1 AND DateAccepted IS NOT NULL;';
	$stmtaccepted=$link->query($sqlaccepted);
	$rowaccepted = $stmtaccepted->fetch();
	
	echo '<div style="margin-left:65%">Total Number of Booklets: <b>'.$rowtotal['TotalNumberAll'].'</b><br/>Transferred To Central Warehouse: <b>'.$rowtransferred['TotalNumberTransferred'].'</b><br/>Not Transferred: <b>'.$rownotransfered['TotalNumberNotTransferred'].'</b><br/><br/></div>';
	echo '<div style="margin-left:65%">Number of Issued Booklet By Central Warehouse: <b>'.$rowissued['TotalIssued'].'</b><br/>Number of Booklet Accepted: <b>'.$rowaccepted['TotalAccepted'].'</b><br/></div>';
	
	
	?>