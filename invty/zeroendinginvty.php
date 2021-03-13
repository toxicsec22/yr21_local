<?php
error_reporting(E_ALL);
	ini_set('display_errors', 1);
	$path=$_SERVER['DOCUMENT_ROOT']; 
	include_once $path.'/acrossyrs/dbinit/userinit.php';
	$currentyr=!isset($currentyr)?date('Y'):$currentyr;
	$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',1):$link;
	date_default_timezone_set('Asia/Manila');

        $sql='CREATE TEMPORARY TABLE ZeroEndingInvty AS SELECT BranchNo, a.ItemCode,SUM(Qty) AS EndInvZero FROM invty_20uniallposted a JOIN invty_1items i ON i.ItemCode=a.ItemCode  WHERE Date<=CURDATE() AND i.MoveType=0 GROUP BY BranchNo,ItemCode HAVING EndInvZero BETWEEN -0.1 AND 0.1;';
	$stmt=$link->prepare($sql); $stmt->execute();
        $sqli='UPDATE invty_1beginv i JOIN ZeroEndingInvty e ON i.ItemCode=e.ItemCode AND i.BranchNo=e.BranchNo SET DaysStockOut=DaysStockOut+1 '.((date('N')==7)?' WHERE i.BranchNo IN (SELECT BranchNo FROM 1branches WHERE WithSunday=1) ':'');
	$stmti=$link->prepare($sqli); $stmti->execute();
	
exit();		
?>