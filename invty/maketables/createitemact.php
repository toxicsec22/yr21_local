<?php
if(isset($_GET['NotSessionBranch'])){
	$branchno=intval($_GET['BranchNo']);
} else {
	$branchno=$_SESSION['bnum'];
}
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$sql0='CREATE TEMPORARY TABLE ItemAct AS 
SELECT `Date`, IF(ItemCode<30000,`From`,"Sale_BUNDLE") AS `From`, `MRRNo` AS Number, `BECS`, `SupplierNo` AS BECSNo,`ItemCode`,`Qty`,`UnitCost`,`UnitPrice`,`SerialNo`,`BranchNo`,`ActRemarks`,`txntype`,`Defective`
    FROM `'.$currentyr.'_static`.`invty_unialltxns` `uni` WHERE (`Date` IS NOT NULL) AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'

UNION ALL SELECT `Date`,"Sale_BUNDLE" AS `From`, `MRRNo`, `BECS`, `SupplierNo`, bis.`ItemCode`, (`bis`.`Qty`*`uni`.`Qty` * -(1)) AS `Qty`, `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `ActRemarks`, `txntype`, `Defective` FROM `'.$currentyr.'_static`.`invty_unialltxns` uni JOIN invty_1bundleditems_sub bis ON uni.ItemCode=bis.BundleID WHERE `uni`.`ItemCode`>=30000 AND (`Date` IS NOT NULL) AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND bis.ItemCode='.$itemcode.'':'').'


    UNION ALL 
    SELECT `mm`.`Date`, "MRR" AS `From`, `MRRNo`, "S" AS `BECS`, `SupplierNo`,`ItemCode`,`Qty`,`UnitCost`,"" AS `UnitPrice`, `SerialNo`, `BranchNo`, 
        `mm`.`Remarks` AS `ActRemarks`, `txntype`, `Defective`
    FROM (`invty_2mrr` `mm` JOIN `invty_2mrrsub` `ms` ON ((`mm`.`TxnID` = `ms`.`TxnID`)))
    WHERE ((`mm`.`txntype` NOT IN (8,9)) AND (MONTH(`mm`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
    UNION ALL 
    SELECT `mm`.`Date`, "PR" AS `From`, `PRNo`,"S" AS `BECS`, `SupplierNo`,`ItemCode`,`ms`.`Qty`,`UnitCost`,"" AS `UnitPrice`, ms.`SerialNo`, `BranchNo`, 
        IF(ISNULL(DecisionNo),mm.Remarks,CONCAT(ifnull(mm.Remarks,""), " Decision: ", GROUP_CONCAT(CASE WHEN DecisionNo=1 THEN "Credit Memo" WHEN DecisionNo=2 THEN "Rejected" WHEN DecisionNo=3 THEN "Replaced" ELSE "Pending" END ))) AS `ActRemarks`, `txntype`, `Defective`
    FROM (`invty_2pr` `mm` JOIN `invty_2prsub` `ms` ON ((`mm`.`TxnID` = `ms`.`TxnID`))) 
    WHERE ((`mm`.`txntype` =8) AND (MONTH(`mm`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'  GROUP BY ms.TxnSubId   
    UNION ALL 
    SELECT DATE(`ms`.`DecisionTS`) AS `Date`,IF(DecisionNo=3,"ReplacedFromPR","RejectedFromPR") AS `From`, CONCAT(`mm`.`PRNo`," Replacement") AS `PRNo`, "S" AS `BECS`, `mm`.`SupplierNo` AS `SupplierNo`, `ms`.`ItemCode` AS `ItemCode`,
        `ms`.`Qty`*-1 AS `Qty`,`ms`.`UnitCost` AS `UnitCost`,"" AS `UnitPrice`, `ms`.`SerialNo` AS `SerialNo`,
        `mm`.`BranchNo` AS `BranchNo`, CONCAT(IFNULL(CONCAT(`ms`.`DecisionRefNo`, " "), ""), IFNULL(`ms`.`DecisionRemarks`, "")) AS `ActRemarks`,
        `mm`.`txntype` AS `txntype`, IF(DecisionNo=3,0,1) AS `Defective`
    FROM
        `invty_2pr` `mm`
        JOIN `invty_2prsub` `ms` ON `mm`.`TxnID` = `ms`.`TxnID`
    WHERE `mm`.`txntype` = 8
            AND (MONTH(`ms`.`DecisionTS`) > '.$asofmonth.') AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
            AND (`ms`.`DecisionNo` IN (2,3)) 
    UNION ALL SELECT `Date`, "StoreUsed" AS `From`, `MRRNo`, "B" AS `BECS`, `SupplierNo`, `ItemCode`, `Qty`, `UnitCost`,
        "" AS `UnitPrice`, `SerialNo`, `BranchNo`, `m`.`Remarks` AS `ActRemarks`, `txntype`,`Defective`
    FROM (`invty_2mrr` `m` JOIN `invty_2mrrsub` `ms` ON ((`m`.`TxnID` = `ms`.`TxnID`)))
    WHERE ((`m`.`txntype` = 9) AND (MONTH(`m`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
    UNION ALL SELECT `DateIN`, "Transfer-In" AS `From`, `TransferNo`, "B" AS `BECS`, `BranchNo`, `ItemCode`, `QtyReceived`, `UnitCost`, `UnitPrice`, `SerialNo`,
        `ToBranchNo`, `tm`.`Remarks` AS `Remarks`, 7 AS `txntype`, `Defective`
    FROM (`invty_2transfer` `tm`  JOIN `invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
    WHERE ((YEAR(`tm`.`DateIN`) = '.$currentyr.') AND (MONTH(`tm`.`DateIN`) > '.$asofmonth.')) AND ToBranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
    UNION ALL SELECT `Date`,IF(ItemCode<30000,"Sale","Sale_BUNDLE") AS `From`, `SaleNo`, IF(ClientNo<9999,"E","C") AS `BECS`, `ClientNo`, `ItemCode`, (`ss`.`Qty` * -(1)) AS `Qty`,
        `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective`
    FROM (`invty_2sale` `sm` JOIN `invty_2salesub` `ss` ON ((`sm`.`TxnID` = `ss`.`TxnID`)))
    WHERE (MONTH(`sm`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
    UNION ALL SELECT `Date`,"Sale_BUNDLE" AS `From`, `SaleNo`, IF(ClientNo<9999,"E","C") AS `BECS`, `ClientNo`, bis.`ItemCode`, (`bis`.`Qty`*`ss`.`Qty` * -(1)) AS `Qty`, `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective` FROM (`invty_2sale` `sm` JOIN `invty_2salesub` `ss` ON ((`sm`.`TxnID` = `ss`.`TxnID`))) JOIN invty_1bundleditems_sub bis ON ss.ItemCode=bis.BundleID WHERE `ss`.`ItemCode`>=30000 AND (MONTH(`sm`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND bis.ItemCode='.$itemcode.'':'').'
	UNION ALL SELECT `Date`,"Add-on" AS `From`, `SaleNo`, IF(ClientNo<9999,"E","C") AS `BECS`, `ClientNo`, `ItemCode`, (`ao`.`Qty` * -(1)) AS `Qty`,
        \'\' as `UnitCost`, \'\' as `UnitPrice`, \'\' as `SerialNo`, `BranchNo`, case when Approved=1 then "Approved Add-on" when Approved=0 then "For approval Add-on" when Approved=2 then "Rejected Add-on" end as `Remarks`, `txntype`, \'\' as `Defective`
    FROM (`invty_2salesubaddons` `ao` JOIN `invty_2sale` `s` ON ((`s`.`TxnID` = `ao`.`TxnID`)))
	WHERE (MONTH(`s`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
	
	UNION ALL SELECT `DateOUT`, "Transfer-Out" AS `From`, `TransferNo`, "B" AS `BECS`, `ToBranchNo`, `ItemCode`, (`ts`.`QtySent` * -(1)) AS `Expr1`,
        `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective`
    FROM (`invty_2transfer` `tm` JOIN `invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
    WHERE (YEAR(`tm`.`DateOUT`) = '.$currentyr.') AND (MONTH(`tm`.`DateOUT`) > '.$asofmonth.') AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
    UNION ALL SELECT  `Date`, "Adj" AS `From`, `AdjNo`, "B" AS `BECS`, `am`.`BranchNo` AS `AdjBranchNo`, `ItemCode`, `Qty`,
        0 AS `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `adjs`.`Remarks` AS `Remarks`,
        20 AS `txntype`, `Defective`
    FROM (`invty_4adjust` `am` JOIN `invty_4adjustsub` `adjs` ON ((`am`.`TxnID` = `adjs`.`TxnID`)))
    WHERE (MONTH(`am`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' '.(isset($itemcode)?'AND ItemCode='.$itemcode.'':'').'
    ORDER BY `Date`, `From`, `Number`';
//if($_SESSION['(ak0)']==1002) { echo $sql0;}  
$stmt0=$link->prepare($sql0); $stmt0->execute();