<?php
	   $sqlinsert='INSERT INTO `acctg_6targetscores` (`MonthNo`,`BranchNo`,`CashSales`,`ClearedCollections`,`OverdueAR`,`UndepPDC`,`Net`,`Score`,`Units`,`DisplayType`,`EncodedByNo`,`TimeStamp`) SELECT '.$txndate.' AS MonthNo, `BranchNo`, CashSalesValue AS CashSales,ClearedCollectionsValue AS ClearedCollections,OverdueARValue AS OverdueAR, UndepPDCValue AS UndepPDC, TRUNCATE(`NetforBranchValue`,2) AS Net, `TargetReached` AS `Score`,Units, IF('.$txndate.'='.date('m').',5,1) AS `DisplayType`,'.(isset($_SESSION['(ak0)'])?$_SESSION['(ak0)']:'0').' AS `EncodedByNo`, NOW() AS `TimeStamp` FROM targetresults UNION SELECT '.$txndate.' AS MonthNo, 9999 AS `BranchNo`, "" AS CashSales,"" AS ClearedCollections, "" AS OverdueAR, "" AS UndepPDC, (SELECT TRUNCATE(SUM(s.Qty*s.UnitPrice),2) FROM `invty_2sale` m JOIN `invty_2salesub` s ON m.`TxnID`=s.`TxnID` WHERE txntype IN (1,2,5,10) AND (ClientNo NOT BETWEEN 1000 AND 9999) AND (ClientNo NOT BETWEEN 15001 AND 15005) AND Month(`Date`)='.$txndate.') AS Net, "" AS `Score`,"" AS Units, IF('.$txndate.'='.date('m').',5,1) AS `DisplayType`,'.(isset($_SESSION['(ak0)'])?$_SESSION['(ak0)']:'0').' AS `EncodedByNo`, NOW() AS `TimeStamp`';

		$stmt=$link->prepare($sqlinsert);$stmt->execute();


		$sqlinsert='CREATE TEMPORARY TABLE peruse AS
		SELECT MONTH(`Date`) AS MonthNo, BranchNo, SUM(CASE WHEN (Auto+Ref+Aircon)>1 THEN (UnitPrice*Qty) END) AS Multi, 
		SUM(CASE WHEN (Auto+Ref+Aircon)=1 AND Auto=1 THEN (UnitPrice*Qty) END) AS Auto, SUM(CASE WHEN (Auto+Ref+Aircon)=1 AND Aircon=1 THEN (UnitPrice*Qty) END) AS Aircon, 
		SUM(CASE WHEN (Auto+Ref+Aircon)=1 AND Ref=1 THEN (UnitPrice*Qty) END) AS Ref 
		FROM invty_2sale m JOIN invty_2salesub s ON m.TxnID=s.TxnID 
		JOIN invty_1items i ON i.ItemCode=s.ItemCode WHERE MONTH(`Date`)='.$txndate.' AND txntype IN (1,2,5) GROUP BY BranchNo, MONTH(`Date`);';

		$stmt=$link->prepare($sqlinsert);$stmt->execute();


		$sqlinsert='UPDATE `acctg_6targetscores` ts JOIN peruse p ON ts.BranchNo=p.BranchNo AND ts.MonthNo=p.MonthNo SET ts.Auto=TRUNCATE(p.Auto,2), ts.Aircon=TRUNCATE(p.Aircon,2), ts.Ref=TRUNCATE(p.Ref,2), ts.Multi=TRUNCATE(p.Multi,2);';

		$stmt=$link->prepare($sqlinsert);$stmt->execute();
?>