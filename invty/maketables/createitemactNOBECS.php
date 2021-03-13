<?php
if(isset($_GET['NotSessionBranch'])){
	$branchno=intval($_GET['BranchNo']);
} else {
	$branchno=$_SESSION['bnum'];
}

$sql0='CREATE TEMPORARY TABLE ItemAct AS SELECT `Date`, `From`, `MRRNo` AS Number, `SupplierNo`,`ItemCode`,`Qty`,`UnitCost`,`UnitPrice`,`SerialNo`,`BranchNo`,`ActRemarks`,`txntype`,`Defective`
    FROM `'.$currentyr.'_static`.`invty_unialltxns` `uni` WHERE (`Date` IS NOT NULL) AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
    UNION ALL 
    SELECT `mm`.`Date`, "MRR" AS `From`, `MRRNo`,`SupplierNo`,`ItemCode`,`Qty`,`UnitCost`,"" AS `UnitPrice`, `SerialNo`, `BranchNo`, 
        `mm`.`Remarks` AS `ActRemarks`, `txntype`, `Defective`
    FROM (`invty_2mrr` `mm` JOIN `invty_2mrrsub` `ms` ON ((`mm`.`TxnID` = `ms`.`TxnID`)))
    WHERE ((`mm`.`txntype` NOT IN (8,9)) AND (MONTH(`mm`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
    UNION ALL 
    SELECT `mm`.`Date`, "PR" AS `From`, `PRNo`,`SupplierNo`,`ItemCode`,`ms`.`Qty`,`UnitCost`,"" AS `UnitPrice`, ms.`SerialNo`, `BranchNo`, 
        IF(ISNULL(DecisionNo),mm.Remarks,CONCAT(ifnull(mm.Remarks,""), " Decision: ", GROUP_CONCAT(CASE WHEN DecisionNo=1 THEN "Credit Memo" WHEN DecisionNo=2 THEN "Rejected" WHEN DecisionNo=3 THEN "Replaced" ELSE "Pending" END ))) AS `ActRemarks`, `txntype`, `Defective`
    FROM (`invty_2pr` `mm` JOIN `invty_2prsub` `ms` ON ((`mm`.`TxnID` = `ms`.`TxnID`))) 
    WHERE ((`mm`.`txntype` =8) AND (MONTH(`mm`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'  GROUP BY ms.TxnSubId   
    UNION ALL 
    SELECT DATE(`ms`.`DecisionTS`) AS `Date`,IF(DecisionNo=3,"ReplacedFromPR","RejectedFromPR") AS `From`, CONCAT(`mm`.`PRNo`," Replacement") AS `PRNo`, `mm`.`SupplierNo` AS `SupplierNo`, `ms`.`ItemCode` AS `ItemCode`,
        `ms`.`Qty`*-1 AS `Qty`,`ms`.`UnitCost` AS `UnitCost`,"" AS `UnitPrice`, `ms`.`SerialNo` AS `SerialNo`,
        `mm`.`BranchNo` AS `BranchNo`, CONCAT(IFNULL(CONCAT(`ms`.`DecisionRefNo`, " "), ""), IFNULL(`ms`.`DecisionRemarks`, "")) AS `ActRemarks`,
        `mm`.`txntype` AS `txntype`, IF(DecisionNo=3,0,1) AS `Defective`
    FROM
        `invty_2pr` `mm`
        JOIN `invty_2prsub` `ms` ON `mm`.`TxnID` = `ms`.`TxnID`
    WHERE `mm`.`txntype` = 8
            AND (MONTH(`ms`.`DecisionTS`) > '.$asofmonth.') AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
            AND (`ms`.`DecisionNo` IN (2,3)) 
    UNION ALL SELECT `Date`, "StoreUsed" AS `From`, `MRRNo`, `SupplierNo`, `ItemCode`, `Qty`, `UnitCost`,
        "" AS `UnitPrice`, `SerialNo`, `BranchNo`, `m`.`Remarks` AS `ActRemarks`, `txntype`,`Defective`
    FROM (`invty_2mrr` `m` JOIN `invty_2mrrsub` `ms` ON ((`m`.`TxnID` = `ms`.`TxnID`)))
    WHERE ((`m`.`txntype` = 9) AND (MONTH(`m`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
    UNION ALL SELECT `DateIN`, "Transfer-In" AS `From`, `TransferNo`, `BranchNo`, `ItemCode`, `QtyReceived`, `UnitCost`, `UnitPrice`, `SerialNo`,
        `ToBranchNo`, `tm`.`Remarks` AS `Remarks`, 7 AS `txntype`, `Defective`
    FROM (`invty_2transfer` `tm`  JOIN `invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
    WHERE ((YEAR(`tm`.`DateIN`) = '.$currentyr.') AND (MONTH(`tm`.`DateIN`) > '.$asofmonth.')) AND ToBranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
    UNION ALL SELECT `Date`,"Sale" AS `From`, `SaleNo`, `ClientNo`, `ItemCode`, (`ss`.`Qty` * -(1)) AS `Qty`,
        `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective`
    FROM (`invty_2sale` `sm` JOIN `invty_2salesub` `ss` ON ((`sm`.`TxnID` = `ss`.`TxnID`)))
    WHERE (MONTH(`sm`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
    
	UNION ALL SELECT `Date`,"Add-on" AS `From`, `SaleNo`, `ClientNo`, `ItemCode`, (`ao`.`Qty` * -(1)) AS `Qty`,
        \'\' as `UnitCost`, \'\' as `UnitPrice`, \'\' as `SerialNo`, `BranchNo`, case when Approved=1 then "Approved Add-on" when Approved=0 then "For approval Add-on" when Approved=2 then "Rejected Add-on" end as `Remarks`, `txntype`, \'\' as `Defective`
    FROM (`invty_2salesubaddons` `ao` JOIN `invty_2sale` `s` ON ((`s`.`TxnID` = `ao`.`TxnID`)))
	WHERE (MONTH(`s`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
	
	UNION ALL SELECT `DateOUT`, "Transfer-Out" AS `From`, `TransferNo`, `ToBranchNo`, `ItemCode`, (`ts`.`QtySent` * -(1)) AS `Expr1`,
        `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective`
    FROM (`invty_2transfer` `tm` JOIN `invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
    WHERE (YEAR(`tm`.`DateOUT`) = '.$currentyr.') AND (MONTH(`tm`.`DateOUT`) > '.$asofmonth.') AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
    UNION ALL SELECT  `Date`, "Adj" AS `From`, `AdjNo`, `am`.`BranchNo` AS `AdjBranchNo`, `ItemCode`, `Qty`,
        0 AS `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `adjs`.`Remarks` AS `Remarks`,
        20 AS `txntype`, `Defective`
    FROM (`invty_4adjust` `am` JOIN `invty_4adjustsub` `adjs` ON ((`am`.`TxnID` = `adjs`.`TxnID`)))
    WHERE (MONTH(`am`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
    ORDER BY `Date`, `From`, `Number`';
//if($_SESSION['(ak0)']==1002) { echo $sql0;}  
$stmt0=$link->prepare($sql0); $stmt0->execute();