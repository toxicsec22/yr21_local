<?php
$branchno=intval($_GET['BranchNo']);
// Note: there is no Add-on transactions bec there is no defective field for add-on.
$sql0='CREATE TEMPORARY TABLE ItemAct'.$currentyr.' AS SELECT `Date`, `From`, `MRRNo` AS Number, `BECS`, `uni`.`SupplierNo` AS BECSNo,`ItemCode`,`Qty`,`UnitCost`,`UnitPrice`,`SerialNo`,`BranchNo`,`ActRemarks`,`txntype`,`Defective`
    FROM `'.$currentyr.'_static`.`invty_unialltxns` `uni`
    JOIN `'.$supplierincluded.'` s ON s.SupplierNo=uni.SupplierNo
    WHERE (`Date` IS NOT NULL) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0
    UNION 
    SELECT `mm`.`Date`, "MRR" AS `From`, `MRRNo`, "S" AS `BECS`, `mm`.`SupplierNo`,`ItemCode`,`Qty`,`UnitCost`,"" AS `UnitPrice`, `SerialNo`, `BranchNo`, 
        `mm`.`Remarks` AS `ActRemarks`, `txntype`, `Defective`
    FROM (`invty_2mrr` `mm` JOIN `invty_2mrrsub` `ms` ON ((`mm`.`TxnID` = `ms`.`TxnID`)))
    JOIN `'.$supplierincluded.'` s ON s.SupplierNo=mm.SupplierNo
    WHERE ((`mm`.`txntype` NOT IN (8,9)) AND (MONTH(`mm`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0
    UNION 
    SELECT `mm`.`Date`, "PR" AS `From`, `PRNo`, "S" AS `BECS`, `mm`.`SupplierNo`,`ItemCode`,`ms`.`Qty`,`UnitCost`,"" AS `UnitPrice`, ms.`SerialNo`, `BranchNo`, 
        IF(ISNULL(DecisionNo),mm.Remarks,CONCAT(mm.Remarks, " Decision: ", GROUP_CONCAT(CASE WHEN DecisionNo=1 THEN "Credit Memo" WHEN DecisionNo=2 THEN "Rejected" WHEN DecisionNo=3 THEN "Replaced" ELSE "Pending" END ))) AS `ActRemarks`, `txntype`, `Defective`
    FROM (`invty_2pr` `mm` JOIN `invty_2prsub` `ms` ON ((`mm`.`TxnID` = `ms`.`TxnID`)))
    JOIN `'.$supplierincluded.'` s ON s.SupplierNo=mm.SupplierNo 
    WHERE ((`mm`.`txntype` =8) AND (MONTH(`mm`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0  GROUP BY ms.TxnSubId   
    UNION 
    SELECT DATE(`ms`.`DecisionTS`) AS `Date`,IF(DecisionNo=3,"ReplacedFromPR","RejectedFromPR") AS `From`, CONCAT(`mm`.`PRNo`," Replacement") AS `MRRNo`, "S" AS `BECS`, `mm`.`SupplierNo`, `ms`.`ItemCode` AS `ItemCode`,
        `ms`.`Qty`*-1 AS `Qty`,`ms`.`UnitCost` AS `UnitCost`,"" AS `UnitPrice`, `ms`.`SerialNo` AS `SerialNo`,
        `mm`.`BranchNo` AS `BranchNo`, CONCAT(IFNULL(CONCAT(`ms`.`DecisionRefNo`, " "), ""), IFNULL(`ms`.`DecisionRemarks`, "")) AS `ActRemarks`,
        `mm`.`txntype` AS `txntype`, IF(DecisionNo=3,0,1) AS `Defective`
    FROM
        `invty_2pr` `mm`
        JOIN `invty_2prsub` `ms` ON `mm`.`TxnID` = `ms`.`TxnID`
        JOIN `'.$supplierincluded.'` s ON s.SupplierNo=mm.SupplierNo
    WHERE `mm`.`txntype` = 8 AND Defective<>0
            AND (MONTH(`ms`.`DecisionTS`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.'
            AND (`ms`.`DecisionNo` IN (2,3)) 
    UNION SELECT `Date`, "StoreUsed" AS `From`, `MRRNo`, "B" AS `BECS`, `m`.`SupplierNo`, `ItemCode`, `Qty`, `UnitCost`,
        "" AS `UnitPrice`, `SerialNo`, `BranchNo`, `m`.`Remarks` AS `ActRemarks`, `txntype`,`Defective`
    FROM (`invty_2mrr` `m` JOIN `invty_2mrrsub` `ms` ON ((`m`.`TxnID` = `ms`.`TxnID`)))
    WHERE ((`m`.`txntype` = 9) AND (MONTH(`m`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0
    UNION SELECT `DateIN`, "Transfer-In" AS `From`, `TransferNo`, "B" AS `BECS`, `BranchNo`, `ItemCode`, `QtyReceived`, `UnitCost`, `UnitPrice`, `SerialNo`,
        `ToBranchNo`, `tm`.`Remarks` AS `Remarks`, 7 AS `txntype`, `Defective`
    FROM (`invty_2transfer` `tm`  JOIN `invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
    WHERE ((YEAR(`tm`.`DateIN`) = '.$currentyr.') AND (MONTH(`tm`.`DateIN`) > '.$asofmonth.')) AND Defective<>0 AND ToBranchNo='.$branchno .' AND ItemCode='.$itemcode.'
    UNION SELECT `Date`,"Sale" AS `From`, `SaleNo`, IF(ClientNo<9999,"E","C") AS `BECS`, `ClientNo`, `ItemCode`, (`ss`.`Qty` * -(1)) AS `Qty`,
        `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective`
    FROM (`invty_2sale` `sm` JOIN `invty_2salesub` `ss` ON ((`sm`.`TxnID` = `ss`.`TxnID`)))
    WHERE (MONTH(`sm`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0

    UNION SELECT `DateOUT`, "Transfer-Out" AS `From`, `TransferNo`, "B" AS `BECS`, `ToBranchNo`, `ItemCode`, (`ts`.`QtySent` * -(1)) AS `Expr1`,
        `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective`
    FROM (`invty_2transfer` `tm` JOIN `invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
    WHERE (YEAR(`tm`.`DateOUT`) = '.$currentyr.') AND (MONTH(`tm`.`DateOUT`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0
    UNION SELECT  `Date`, "Adj" AS `From`, `AdjNo`, "B" AS `BECS`,  `am`.`BranchNo` AS `AdjBranchNo`, `ItemCode`, `Qty`,
        0 AS `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `adjs`.`Remarks` AS `Remarks`,
        20 AS `txntype`, `Defective`
    FROM (`invty_4adjust` `am` JOIN `invty_4adjustsub` `adjs` ON ((`am`.`TxnID` = `adjs`.`TxnID`)))
    WHERE (MONTH(`am`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0
    ORDER BY `Date`, `From`, `Number`';

$stmt0=$link->prepare($sql0); $stmt0->execute();


$sql0='CREATE TEMPORARY TABLE ItemAct'.$lastyr.' AS SELECT `Date`, `From`, `MRRNo` AS Number, `SupplierNo`,`ItemCode`,`Qty`,`UnitCost`,`UnitPrice`,`SerialNo`,`BranchNo`,`ActRemarks`,`txntype`,`Defective`
    FROM `'.$lastyr.'_static`.`invty_unialltxns` `uni` WHERE (`Date` IS NOT NULL) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0
    UNION 
    SELECT `mm`.`Date`, "MRR" AS `From`, `MRRNo`,`SupplierNo`,`ItemCode`,`Qty`,`UnitCost`,"" AS `UnitPrice`, `SerialNo`, `BranchNo`, 
        `mm`.`Remarks` AS `ActRemarks`, `txntype`, `Defective`
    FROM (`'.$lastyr.'_1rtc`.`invty_2mrr` `mm` JOIN `'.$lastyr.'_1rtc`.`invty_2mrrsub` `ms` ON ((`mm`.`TxnID` = `ms`.`TxnID`)))
    WHERE ((`mm`.`txntype` NOT IN (8,9)) AND (MONTH(`mm`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0
    UNION 
    SELECT `mm`.`Date`, "PR" AS `From`, `PRNo`,`SupplierNo`,`ItemCode`,`ms`.`Qty`,`UnitCost`,"" AS `UnitPrice`, ms.`SerialNo`, `BranchNo`, 
        IF(ISNULL(DecisionNo),mm.Remarks,CONCAT(mm.Remarks, " Decision: ", GROUP_CONCAT(CASE WHEN DecisionNo=1 THEN "Credit Memo" WHEN DecisionNo=2 THEN "Rejected" WHEN DecisionNo=3 THEN "Replaced" ELSE "Pending" END ))) AS `ActRemarks`, `txntype`, `Defective`
    FROM (`'.$lastyr.'_1rtc`.`invty_2pr` `mm` JOIN `'.$lastyr.'_1rtc`.`invty_2prsub` `ms` ON ((`mm`.`TxnID` = `ms`.`TxnID`))) 
    WHERE ((`mm`.`txntype` =8) AND (MONTH(`mm`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0  GROUP BY ms.TxnSubId   
    UNION 
    SELECT DATE(`ms`.`DecisionTS`) AS `Date`,IF(DecisionNo=3,"ReplacedFromPR","RejectedFromPR") AS `From`, CONCAT(`mm`.`PRNo`," Replacement") AS `MRRNo`, `mm`.`SupplierNo` AS `SupplierNo`, `ms`.`ItemCode` AS `ItemCode`,
        `ms`.`Qty`*-1 AS `Qty`,`ms`.`UnitCost` AS `UnitCost`,"" AS `UnitPrice`, `ms`.`SerialNo` AS `SerialNo`,
        `mm`.`BranchNo` AS `BranchNo`, CONCAT(IFNULL(CONCAT(`ms`.`DecisionRefNo`, " "), ""), IFNULL(`ms`.`DecisionRemarks`, "")) AS `ActRemarks`,
        `mm`.`txntype` AS `txntype`, IF(DecisionNo=3,0,1) AS `Defective`
    FROM
        `'.$lastyr.'_1rtc`.`invty_2pr` `mm`
        JOIN `'.$lastyr.'_1rtc`.`invty_2prsub` `ms` ON `mm`.`TxnID` = `ms`.`TxnID`
    WHERE `mm`.`txntype` = 8 AND Defective<>0
            AND (MONTH(`ms`.`DecisionTS`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.'
            AND (`ms`.`DecisionNo` IN (2,3)) 
    UNION SELECT `Date`, "StoreUsed" AS `From`, `MRRNo`, `SupplierNo`, `ItemCode`, `Qty`, `UnitCost`,
        "" AS `UnitPrice`, `SerialNo`, `BranchNo`, `m`.`Remarks` AS `ActRemarks`, `txntype`,`Defective`
    FROM (`'.$lastyr.'_1rtc`.`invty_2mrr` `m` JOIN `'.$lastyr.'_1rtc`.`invty_2mrrsub` `ms` ON ((`m`.`TxnID` = `ms`.`TxnID`)))
    WHERE ((`m`.`txntype` = 9) AND (MONTH(`m`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0
    UNION SELECT `DateIN`, "Transfer-In" AS `From`, `TransferNo`, `BranchNo`, `ItemCode`, `QtyReceived`, `UnitCost`, `UnitPrice`, `SerialNo`,
        `ToBranchNo`, `tm`.`Remarks` AS `Remarks`, 7 AS `txntype`, `Defective`
    FROM (`'.$lastyr.'_1rtc`.`invty_2transfer` `tm`  JOIN `'.$lastyr.'_1rtc`.`invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
    WHERE ((YEAR(`tm`.`DateIN`) = '.$lastyr.') AND (MONTH(`tm`.`DateIN`) > '.$asofmonth.')) AND Defective<>0 AND ToBranchNo='.$branchno .' AND ItemCode='.$itemcode.'
    UNION SELECT `Date`,"Sale" AS `From`, `SaleNo`, `ClientNo`, `ItemCode`, (`ss`.`Qty` * -(1)) AS `Qty`,
        `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective`
    FROM (`'.$lastyr.'_1rtc`.`invty_2sale` `sm` JOIN `'.$lastyr.'_1rtc`.`invty_2salesub` `ss` ON ((`sm`.`TxnID` = `ss`.`TxnID`)))
    WHERE (MONTH(`sm`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0
    UNION SELECT `DateOUT`, "Transfer-Out" AS `From`, `TransferNo`, `ToBranchNo`, `ItemCode`, (`ts`.`QtySent` * -(1)) AS `Expr1`,
        `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective`
    FROM (`'.$lastyr.'_1rtc`.`invty_2transfer` `tm` JOIN `'.$lastyr.'_1rtc`.`invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
    WHERE (YEAR(`tm`.`DateOUT`) = '.$lastyr.') AND (MONTH(`tm`.`DateOUT`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0
    UNION SELECT  `Date`, "Adj" AS `From`, `AdjNo`, `am`.`BranchNo` AS `AdjBranchNo`, `ItemCode`, `Qty`,
        0 AS `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `adjs`.`Remarks` AS `Remarks`,
        20 AS `txntype`, `Defective`
    FROM (`'.$lastyr.'_1rtc`.`invty_4adjust` `am` JOIN `'.$lastyr.'_1rtc`.`invty_4adjustsub` `adjs` ON ((`am`.`TxnID` = `adjs`.`TxnID`)))
    WHERE (MONTH(`am`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0
    ORDER BY `Date`, `From`, `Number`';

$stmt0=$link->prepare($sql0); $stmt0->execute();
$last2yrs=$currentyr-2;
$sql0='CREATE TEMPORARY TABLE ItemAct'.$last2yrs.' AS SELECT `Date`, `From`, `MRRNo` AS Number, `SupplierNo`,`ItemCode`,`Qty`,`UnitCost`,`UnitPrice`,`SerialNo`,`BranchNo`,`ActRemarks`,`txntype`,`Defective` FROM `'.$last2yrs.'_static`.`invty_unialltxns` `uni` WHERE (`Date` IS NOT NULL) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `mm`.`Date`, "MRRorPR" AS `From`, `MRRNo`,`SupplierNo`,`ItemCode`,`Qty`,`UnitCost`,"" AS `UnitPrice`, `SerialNo`, `BranchNo`, `mm`.`Remarks` AS `ActRemarks`, `txntype`, `Defective` FROM (`'.$last2yrs.'_1rtc`.`invty_2mrr` `mm` JOIN `'.$last2yrs.'_1rtc`.`invty_2mrrsub` `ms` ON ((`mm`.`TxnID` = `ms`.`TxnID`))) WHERE ((`mm`.`txntype` <> 9) AND (MONTH(`mm`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `Date`, "StoreUsed" AS `From`, `MRRNo`, `SupplierNo`, `ItemCode`, `Qty`, `UnitCost`, "" AS `UnitPrice`, `SerialNo`, `BranchNo`, `m`.`Remarks` AS `ActRemarks`, `txntype`,`Defective` FROM (`'.$last2yrs.'_1rtc`.`invty_2mrr` `m` JOIN `'.$last2yrs.'_1rtc`.`invty_2mrrsub` `ms` ON ((`m`.`TxnID` = `ms`.`TxnID`))) WHERE ((`m`.`txntype` = 9) AND (MONTH(`m`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `DateIN`, "Transfer-In" AS `From`, `TransferNo`, `BranchNo`, `ItemCode`, `QtyReceived`, `UnitCost`, `UnitPrice`, `SerialNo`, `ToBranchNo`, `tm`.`Remarks` AS `Remarks`, 7 AS `txntype`, `Defective` FROM (`'.$last2yrs.'_1rtc`.`invty_2transfer` `tm` JOIN `'.$last2yrs.'_1rtc`.`invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`))) WHERE ((YEAR(`tm`.`DateIN`) = '.$last2yrs.') AND (MONTH(`tm`.`DateIN`) > '.$asofmonth.')) AND ToBranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `Date`,"Sale" AS `From`, `SaleNo`, `ClientNo`, `ItemCode`, (`ss`.`Qty` * -(1)) AS `Qty`, `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective` FROM (`'.$last2yrs.'_1rtc`.`invty_2sale` `sm` JOIN `'.$last2yrs.'_1rtc`.`invty_2salesub` `ss` ON ((`sm`.`TxnID` = `ss`.`TxnID`))) WHERE (MONTH(`sm`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `DateOUT`, "Transfer-Out" AS `From`, `TransferNo`, `ToBranchNo`, `ItemCode`, (`ts`.`QtySent` * -(1)) AS `Expr1`, `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective` FROM (`'.$last2yrs.'_1rtc`.`invty_2transfer` `tm` JOIN `'.$last2yrs.'_1rtc`.`invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`))) WHERE (YEAR(`tm`.`DateOUT`) = '.$last2yrs.') AND (MONTH(`tm`.`DateOUT`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `Date`, "Adj" AS `From`, `AdjNo`, `am`.`BranchNo` AS `AdjBranchNo`, `ItemCode`, `Qty`, 0 AS `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `adjs`.`Remarks` AS `Remarks`, 20 AS `txntype`, `Defective` FROM (`'.$last2yrs.'_1rtc`.`invty_4adjust` `am` JOIN `'.$last2yrs.'_1rtc`.`invty_4adjustsub` `adjs` ON ((`am`.`TxnID` = `adjs`.`TxnID`))) WHERE (MONTH(`am`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 ORDER BY `Date`, `From`, `Number`';

$stmt0=$link->prepare($sql0); $stmt0->execute();

$last3yrs=$currentyr-3;
$sql0='CREATE TEMPORARY TABLE ItemAct'.$last3yrs.' AS SELECT `Date`, `From`, `MRRNo` AS Number, `SupplierNo`,`ItemCode`,`Qty`,`UnitCost`,`MinPrice`,`SerialNo`,`BranchNo`,`ActRemarks`,`txntype`,`Defective` FROM `'.$last3yrs.'_static`.`invty_unialltxns` `uni` WHERE (`Date` IS NOT NULL) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `mm`.`Date`, "MRRorPR" AS `From`, `MRRNo`,`SupplierNo`,`ItemCode`,`Qty`,`UnitCost`,"" AS `MinPrice`, `SerialNo`, `BranchNo`, `mm`.`Remarks` AS `ActRemarks`, `txntype`, `Defective` FROM (`'.$last3yrs.'_1rtc`.`invty_2mrr` `mm` JOIN `'.$last3yrs.'_1rtc`.`invty_2mrrsub` `ms` ON ((`mm`.`TxnID` = `ms`.`TxnID`))) WHERE ((`mm`.`txntype` <> 9) AND (MONTH(`mm`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `Date`, "StoreUsed" AS `From`, `MRRNo`, `SupplierNo`, `ItemCode`, `Qty`, `UnitCost`, "" AS `MinPrice`, `SerialNo`, `BranchNo`, `m`.`Remarks` AS `ActRemarks`, `txntype`,`Defective` FROM (`'.$last3yrs.'_1rtc`.`invty_2mrr` `m` JOIN `'.$last3yrs.'_1rtc`.`invty_2mrrsub` `ms` ON ((`m`.`TxnID` = `ms`.`TxnID`))) WHERE ((`m`.`txntype` = 9) AND (MONTH(`m`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `DateIN`, "Transfer-In" AS `From`, `TransferNo`, `BranchNo`, `ItemCode`, `QtyReceived`, `UnitCost`, `UnitPrice`, `SerialNo`, `ToBranchNo`, `tm`.`Remarks` AS `Remarks`, 7 AS `txntype`, `Defective` FROM (`'.$last3yrs.'_1rtc`.`invty_2transfer` `tm` JOIN `'.$last3yrs.'_1rtc`.`invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`))) WHERE ((YEAR(`tm`.`DateIN`) = '.$last3yrs.') AND (MONTH(`tm`.`DateIN`) > '.$asofmonth.')) AND ToBranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `Date`,"Sale" AS `From`, `SaleNo`, `ClientNo`, `ItemCode`, (`ss`.`Qty` * -(1)) AS `Qty`, `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective` FROM (`'.$last3yrs.'_1rtc`.`invty_2sale` `sm` JOIN `'.$last3yrs.'_1rtc`.`invty_2salesub` `ss` ON ((`sm`.`TxnID` = `ss`.`TxnID`))) WHERE (MONTH(`sm`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `DateOUT`, "Transfer-Out" AS `From`, `TransferNo`, `ToBranchNo`, `ItemCode`, (`ts`.`QtySent` * -(1)) AS `Expr1`, `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective` FROM (`'.$last3yrs.'_1rtc`.`invty_2transfer` `tm` JOIN `'.$last3yrs.'_1rtc`.`invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`))) WHERE (YEAR(`tm`.`DateOUT`) = '.$last3yrs.') AND (MONTH(`tm`.`DateOUT`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `Date`, "Adj" AS `From`, `AdjNo`, `am`.`BranchNo` AS `AdjBranchNo`, `ItemCode`, `Qty`, 0 AS `UnitCost`, `MinPrice`, `SerialNo`, `BranchNo`, `adjs`.`Remarks` AS `Remarks`, 20 AS `txntype`, `Defective` FROM (`'.$last3yrs.'_1rtc`.`invty_4adjust` `am` JOIN `'.$last3yrs.'_1rtc`.`invty_4adjustsub` `adjs` ON ((`am`.`TxnID` = `adjs`.`TxnID`))) WHERE (MONTH(`am`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 ORDER BY `Date`, `From`, `Number`';

$stmt0=$link->prepare($sql0); $stmt0->execute();
$last4yrs=$currentyr-4;
$sql0='CREATE TEMPORARY TABLE ItemAct'.$last4yrs.' AS SELECT `Date`, `From`, `MRRNo` AS Number, `SupplierNo`,`ItemCode`,`Qty`,`UnitCost`,`MinPrice`,`SerialNo`,`BranchNo`,`ActRemarks`,`txntype`,`Defective` FROM `'.$last4yrs.'_static`.`invty_unialltxns` `uni` WHERE (`Date` IS NOT NULL) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `mm`.`Date`, "MRRorPR" AS `From`, `MRRNo`,`SupplierNo`,`ItemCode`,`Qty`,`UnitCost`,"" AS `MinPrice`, `SerialNo`, `BranchNo`, `mm`.`Remarks` AS `ActRemarks`, `txntype`, `Defective` FROM (`'.$last4yrs.'_1rtc`.`invty_2mrr` `mm` JOIN `'.$last4yrs.'_1rtc`.`invty_2mrrsub` `ms` ON ((`mm`.`TxnID` = `ms`.`TxnID`))) WHERE ((`mm`.`txntype` <> 9) AND (MONTH(`mm`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `Date`, "StoreUsed" AS `From`, `MRRNo`, `SupplierNo`, `ItemCode`, `Qty`, `UnitCost`, "" AS `MinPrice`, `SerialNo`, `BranchNo`, `m`.`Remarks` AS `ActRemarks`, `txntype`,`Defective` FROM (`'.$last4yrs.'_1rtc`.`invty_2mrr` `m` JOIN `'.$last4yrs.'_1rtc`.`invty_2mrrsub` `ms` ON ((`m`.`TxnID` = `ms`.`TxnID`))) WHERE ((`m`.`txntype` = 9) AND (MONTH(`m`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `DateIN`, "Transfer-In" AS `From`, `TransferNo`, `BranchNo`, `ItemCode`, `QtyReceived`, `UnitCost`, `UnitPrice`, `SerialNo`, `ToBranchNo`, `tm`.`Remarks` AS `Remarks`, 7 AS `txntype`, `Defective` FROM (`'.$last4yrs.'_1rtc`.`invty_2transfer` `tm` JOIN `'.$last4yrs.'_1rtc`.`invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`))) WHERE ((YEAR(`tm`.`DateIN`) = '.$last4yrs.') AND (MONTH(`tm`.`DateIN`) > '.$asofmonth.')) AND ToBranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `Date`,"Sale" AS `From`, `SaleNo`, `ClientNo`, `ItemCode`, (`ss`.`Qty` * -(1)) AS `Qty`, `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective` FROM (`'.$last4yrs.'_1rtc`.`invty_2sale` `sm` JOIN `'.$last4yrs.'_1rtc`.`invty_2salesub` `ss` ON ((`sm`.`TxnID` = `ss`.`TxnID`))) WHERE (MONTH(`sm`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `DateOUT`, "Transfer-Out" AS `From`, `TransferNo`, `ToBranchNo`, `ItemCode`, (`ts`.`QtySent` * -(1)) AS `Expr1`, `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective` FROM (`'.$last4yrs.'_1rtc`.`invty_2transfer` `tm` JOIN `'.$last4yrs.'_1rtc`.`invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`))) WHERE (YEAR(`tm`.`DateOUT`) = '.$last4yrs.') AND (MONTH(`tm`.`DateOUT`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `Date`, "Adj" AS `From`, `AdjNo`, `am`.`BranchNo` AS `AdjBranchNo`, `ItemCode`, `Qty`, 0 AS `UnitCost`, `MinPrice`, `SerialNo`, `BranchNo`, `adjs`.`Remarks` AS `Remarks`, 20 AS `txntype`, `Defective` FROM (`'.$last4yrs.'_1rtc`.`invty_4adjust` `am` JOIN `'.$last4yrs.'_1rtc`.`invty_4adjustsub` `adjs` ON ((`am`.`TxnID` = `adjs`.`TxnID`))) WHERE (MONTH(`am`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 ORDER BY `Date`, `From`, `Number`';

$stmt0=$link->prepare($sql0); $stmt0->execute();

$last5yrs=$currentyr-5;
$sql0='CREATE TEMPORARY TABLE ItemAct'.$last5yrs.' AS SELECT `Date`, `From`, `MRRNo` AS Number, `SupplierNo`,`ItemCode`,`Qty`,`UnitCost`,`MinPrice`,`SerialNo`,`BranchNo`,`ActRemarks`,`txntype`,`Defective` FROM `'.$last5yrs.'_static`.`invty_unialltxns` `uni` WHERE (`Date` IS NOT NULL) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `mm`.`Date`, "MRRorPR" AS `From`, `MRRNo`,`SupplierNo`,`ItemCode`,`Qty`,`UnitCost`,"" AS `MinPrice`, `SerialNo`, `BranchNo`, `mm`.`Remarks` AS `ActRemarks`, `txntype`, `Defective` FROM (`'.$last5yrs.'_1rtc`.`invty_2mrr` `mm` JOIN `'.$last5yrs.'_1rtc`.`invty_2mrrsub` `ms` ON ((`mm`.`TxnID` = `ms`.`TxnID`))) WHERE ((`mm`.`txntype` <> 9) AND (MONTH(`mm`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `Date`, "StoreUsed" AS `From`, `MRRNo`, `SupplierNo`, `ItemCode`, `Qty`, `UnitCost`, "" AS `MinPrice`, `SerialNo`, `BranchNo`, `m`.`Remarks` AS `ActRemarks`, `txntype`,`Defective` FROM (`'.$last5yrs.'_1rtc`.`invty_2mrr` `m` JOIN `'.$last5yrs.'_1rtc`.`invty_2mrrsub` `ms` ON ((`m`.`TxnID` = `ms`.`TxnID`))) WHERE ((`m`.`txntype` = 9) AND (MONTH(`m`.`Date`) > '.$asofmonth.')) AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `DateIN`, "Transfer-In" AS `From`, `TransferNo`, `BranchNo`, `ItemCode`, `QtyReceived`, `UnitCost`, `UnitPrice`, `SerialNo`, `ToBranchNo`, `tm`.`Remarks` AS `Remarks`, 7 AS `txntype`, `Defective` FROM (`'.$last5yrs.'_1rtc`.`invty_2transfer` `tm` JOIN `'.$last5yrs.'_1rtc`.`invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`))) WHERE ((YEAR(`tm`.`DateIN`) = '.$last5yrs.') AND (MONTH(`tm`.`DateIN`) > '.$asofmonth.')) AND ToBranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `Date`,"Sale" AS `From`, `SaleNo`, `ClientNo`, `ItemCode`, (`ss`.`Qty` * -(1)) AS `Qty`, `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective` FROM (`'.$last5yrs.'_1rtc`.`invty_2sale` `sm` JOIN `'.$last5yrs.'_1rtc`.`invty_2salesub` `ss` ON ((`sm`.`TxnID` = `ss`.`TxnID`))) WHERE (MONTH(`sm`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `DateOUT`, "Transfer-Out" AS `From`, `TransferNo`, `ToBranchNo`, `ItemCode`, (`ts`.`QtySent` * -(1)) AS `Expr1`, `UnitCost`, `UnitPrice`, `SerialNo`, `BranchNo`, `Remarks`, `txntype`, `Defective` FROM (`'.$last5yrs.'_1rtc`.`invty_2transfer` `tm` JOIN `'.$last5yrs.'_1rtc`.`invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`))) WHERE (YEAR(`tm`.`DateOUT`) = '.$last5yrs.') AND (MONTH(`tm`.`DateOUT`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 UNION SELECT `Date`, "Adj" AS `From`, `AdjNo`, `am`.`BranchNo` AS `AdjBranchNo`, `ItemCode`, `Qty`, 0 AS `UnitCost`, `MinPrice`, `SerialNo`, `BranchNo`, `adjs`.`Remarks` AS `Remarks`, 20 AS `txntype`, `Defective` FROM (`'.$last5yrs.'_1rtc`.`invty_4adjust` `am` JOIN `'.$last5yrs.'_1rtc`.`invty_4adjustsub` `adjs` ON ((`am`.`TxnID` = `adjs`.`TxnID`))) WHERE (MONTH(`am`.`Date`) > '.$asofmonth.') AND BranchNo='.$branchno .' AND ItemCode='.$itemcode.' AND Defective<>0 ORDER BY `Date`, `From`, `Number`';

$stmt0=$link->prepare($sql0); $stmt0->execute();