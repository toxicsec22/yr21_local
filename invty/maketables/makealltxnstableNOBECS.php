<?php

// makes  table `invty_unialltxns as of closed date
$sql0='drop table if exists `'.$currentyr.'_static`.`invty_unialltxns`';
$stmt=$link->prepare($sql0); $stmt->execute();
// $sql0='CREATE TABLE `'.$currentyr.'_static`.`invty_unialltxns` (
  // `Date` date DEFAULT NULL,
  // `From` varchar(12) CHARACTER SET utf8 NOT NULL DEFAULT "",
  // `MRRNo` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT "",
  // `SupplierNo` smallint(6) NOT NULL DEFAULT "0",
  // `ItemCode` smallint(6) NOT NULL DEFAULT "0",
  // `Qty` double NOT NULL DEFAULT "0",
  // `UnitCost` double DEFAULT NULL,
  // `UnitPrice` varchar(22) CHARACTER SET utf8 DEFAULT NULL,
  // `SerialNo` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  // `BranchNo` smallint(6) NOT NULL DEFAULT "0",
  // `ActRemarks` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  // `txntype` bigint(20) unsigned NOT NULL DEFAULT "0",
  // `Defective` tinyint(1) NOT NULL DEFAULT "0",
  // KEY `mrrnouniidx` (`MRRNo`),
  // KEY `suppuniidx` (`SupplierNo`),
  // KEY `itemuniidx` (`ItemCode`),
  // KEY `branchnouniidx` (`BranchNo`),
  // KEY `txntypeuniidx` (`txntype`)
// )

// SELECT 
        // CAST(\''.$currentyr.'-01-01\' AS DATE) AS `Date`,
        // "BegInv" AS `From`,
        // "" AS `MRRNo`,
        // `BranchNo` AS `SupplierNo`,
        // `ItemCode` AS `ItemCode`,
        // `BegInv` AS `Qty`,
        // `BegCost` AS `UnitCost`,
        // `BegPriceLevel3` AS `UnitPrice`,
        // "BegInv" AS `SerialNo`,
        // `BranchNo` AS `BranchNo`,
        // "" AS `ActRemarks`,
        // 0 AS `txntype`, 0 AS `Defective`
    // FROM
        // `invty_1beginv`';
		$sql0='CREATE TABLE `'.$currentyr.'_static`.`invty_unialltxns` AS 
		SELECT 
        CAST(\''.$currentyr.'-01-01\' AS DATE) AS `Date`,
        "BegInv" AS `From`,
        "" AS `MRRNo`,
        `BranchNo` AS `SupplierNo`,
        `ItemCode` AS `ItemCode`,
        `BegInv` AS `Qty`,
        `BegCost` AS `UnitCost`,
        `BegPriceLevel3` AS `UnitPrice`,
        "BegInv" AS `SerialNo`,
        `BranchNo` AS `BranchNo`,
        "" AS `ActRemarks`,
        0 AS `txntype`, 0 AS `Defective`
    FROM
        `invty_1beginv`';
if ($reportmonth==0) {goto BeginningOnly;}
$sql0=$sql0.'UNION ALL 
SELECT 
        `Date` AS `Date`,
        "MRR" AS `From`,
        `MRRNo` AS `MRRNo`,
        `SupplierNo` AS `SupplierNo`,
        `ItemCode` AS `ItemCode`,
        `Qty` AS `Qty`,
        `UnitCost` AS `UnitCost`,
        "" AS `UnitPrice`,
        `SerialNo` AS `SerialNo`,
        `BranchNo` AS `BranchNo`,
        `Remarks` AS `ActRemarks`,
        `txntype` AS `txntype`, `Defective` AS `Defective`
    FROM
        (`invty_2mrr` mm
        JOIN `invty_2mrrsub` ms ON ((mm.`TxnID` = ms.`TxnID`)))
    WHERE
        (`txntype` NOT IN (8,9)) and Month(`Date`)<='.$asofmonth.'
    UNION ALL SELECT 
        `Date` AS `Date`,
        "PR" AS `From`,
        `PRNo` AS `PRNo`,
        `SupplierNo` AS `SupplierNo`,
        `ItemCode` AS `ItemCode`,
        ms.`Qty` AS `Qty`,
        `UnitCost` AS `UnitCost`,
        "" AS `UnitPrice`,
        ms.`SerialNo` AS `SerialNo`,
        `BranchNo` AS `BranchNo`,
        IF(ISNULL(DecisionNo),mm.Remarks,CONCAT(mm.Remarks, " Decision: ", GROUP_CONCAT(CASE WHEN DecisionNo=1 THEN "Credit Memo" WHEN DecisionNo=2 THEN "Rejected" WHEN DecisionNo=3 THEN "Replaced" ELSE "Pending" END ))) AS `ActRemarks`,
        `txntype` AS `txntype`, `Defective` AS `Defective`
    FROM
        (`invty_2pr` mm
        JOIN `invty_2prsub` ms ON ((mm.`TxnID` = ms.`TxnID`)))
    WHERE
        (`txntype`=8) and Month(`Date`)<='.$asofmonth.' GROUP BY ms.TxnSubId
    UNION ALL SELECT 
        DATE(`ms`.`DecisionTS`) AS `Date`,
        IF(DecisionNo=3,"ReplacedFromPR","RejectedFromPR") AS `From`,
        `PRNo` AS `PRNo`,
        `SupplierNo` AS `SupplierNo`,
        `ItemCode` AS `ItemCode`,
        ms.`Qty`*-1 AS `Qty`,
        `UnitCost` AS `UnitCost`,
        "" AS `UnitPrice`,
        ms.`SerialNo` AS `SerialNo`,
        `BranchNo` AS `BranchNo`,
        IF(ISNULL(DecisionNo),mm.Remarks,CONCAT(mm.Remarks, " Decision: ", GROUP_CONCAT(CASE WHEN DecisionNo=1 THEN "Credit Memo" WHEN DecisionNo=2 THEN "Rejected" WHEN DecisionNo=3 THEN "Replaced" ELSE "Pending" END ))) AS `ActRemarks`,
        `txntype` AS `txntype`, IF(DecisionNo=3,0,1) AS `Defective`
    FROM
        (`invty_2pr` mm
        JOIN `invty_2prsub` ms ON ((mm.`TxnID` = ms.`TxnID`)))
    WHERE
        (`txntype`=8) AND (`ms`.`DecisionNo` IN (2,3)) and Month(`ms`.`DecisionTS`)<='.$asofmonth.' GROUP BY ms.TxnSubId
    UNION ALL SELECT 
        `Date` AS `Date`,
        "StoreUsed" AS `From`,
        `MRRNo` AS `MRRNo`,
        `SupplierNo` AS `SupplierNo`,
        `ItemCode` AS `ItemCode`,
        `Qty` AS `Qty`,
        `UnitCost` AS `UnitCost`,
        "" AS `UnitPrice`,
        `SerialNo` AS `SerialNo`,
        `BranchNo` AS `BranchNo`,
        `Remarks` AS `ActRemarks`,
        `txntype` AS `txntype`, `Defective` AS `Defective`
    FROM
        (`invty_2mrr` m
        JOIN `invty_2mrrsub` ms ON ((m.`TxnID` = ms.`TxnID`)))
    WHERE
        (`txntype` = 9) and Month(`Date`)<='.$asofmonth.'
    UNION ALL SELECT 
        `DateIN` AS `DateIN`,
        "Transfer-In" AS `From`,
        `TransferNo` AS `TransferNo`,
        `BranchNo` AS `BranchNo`,
        `ItemCode` AS `ItemCode`,
        `QtyReceived` AS `QtyReceived`,
        `UnitCost` AS `UnitCost`,
        `UnitPrice` AS `UnitPrice`,
        `SerialNo` AS `SerialNo`,
        `ToBranchNo` AS `ToBranchNo`,
        `Remarks` AS `Remarks`,
        7 AS `txntype`, `Defective` AS `Defective`
    FROM
        (`invty_2transfer` tm
        JOIN `invty_2transfersub` ts ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
    WHERE
        (YEAR(`DateIN`) = '.$currentyr.') and Month(`DateIN`)<='.$asofmonth.'
    UNION ALL SELECT 
        `Date` AS `Date`,
        "Sale" AS `From`,
        `SaleNo` AS `SaleNo`,
        `ClientNo` AS `ClientNo`,
        `ItemCode` AS `ItemCode`,
        (`Qty` * -(1)) AS `Qty`,
        `UnitCost` AS `UnitCost`,
        `UnitPrice` AS `UnitPrice`,
        `SerialNo` AS `SerialNo`,
        `BranchNo` AS `BranchNo`,
        `Remarks` AS `Remarks`,
        `txntype` AS `txntype`, `Defective` AS `Defective`
    FROM
        (`invty_2sale` sm
        JOIN `invty_2salesub` ss ON ((`sm`.`TxnID` = `ss`.`TxnID`)))
    WHERE Month(`Date`)<='.$asofmonth.'
    UNION ALL SELECT 
        `DateOUT` AS `DateOUT`,
        "Transfer-Out" AS `From`,
        `TransferNo` AS `TransferNo`,
        `ToBranchNo` AS `ToBranchNo`,
        `ItemCode` AS `ItemCode`,
        (`QtySent` * -(1)) AS `Expr1`,
        `UnitCost` AS `UnitCost`,
        `UnitPrice` AS `UnitPrice`,
        `SerialNo` AS `SerialNo`,
        `BranchNo` AS `BranchNo`,
        `Remarks` AS `Remarks`,
        `txntype` AS `txntype`, `Defective` AS `Defective`
    FROM
        (`invty_2transfer` tm
        JOIN `invty_2transfersub` ts ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
    WHERE
        (YEAR(`DateOUT`) = '.$currentyr.') and Month(`DateOUT`)<='.$asofmonth.'
    UNION ALL SELECT 
        `Date` AS `Date`,
        "Adj" AS `From`,
        `AdjNo` AS `AdjNo`,
        `BranchNo` AS `AdjBranchNo`,
        `ItemCode` AS `ItemCode`,
        `Qty` AS `Qty`,
        0 AS `UnitCost`,
        `UnitPrice` AS `UnitPrice`,
        `SerialNo` AS `SerialNo`,
        `BranchNo` AS `BranchNo`,
        `Remarks` AS `Remarks`,
        20 AS `txntype`, `Defective` AS `Defective`
    FROM
        (`invty_4adjust` am
        JOIN `invty_4adjustsub` adjs ON ((`am`.`TxnID` = `adjs`.`TxnID`)))
    WHERE Month(`Date`)<='.$asofmonth.'
    
    ORDER BY `DATE`;';
// echo $sql0; break;
BeginningOnly:
    $stmt=$link->prepare($sql0);
    $stmt->execute();

?>