<?php
// DID NOT USE THIS ANYMORE BEC THERE IS STATIC DATA NOW.
 
 // Cash Sales and Returns - Branch and overall
$sql0='CREATE TEMPORARY TABLE `targets0cashsalesreturns` as
        SELECT Round((Sum(`Qty` * `UnitPrice`)-SUM(IF(ISNULL(fc.Amount),0,fc.Amount))),0) as CashSalesLessReturns, sm.BranchNo
    from
        (`invty_2sale` sm
        join `invty_2salesub` ss ON ((`sm`.`TxnID` = `ss`.`TxnID`)))  
        LEFT JOIN `'.$thisyr.'_1rtc`.`approvals_2freightclients` fc ON (fc.ForInvoiceNo=sm.SaleNo AND fc.BranchNo=sm.BranchNo AND fc.txntype=sm.txntype AND PriceFreightInclusive=1)
where  (PaymentType<>2 and sm.txntype<>3 and YEAR(sm.`Date`)='.$thisyr.') AND sm.BranchNo<>999 group by sm.BranchNo;
';
$stmt=$link->prepare($sql0);$stmt->execute();

// Cash Sales and Returns - STL (no unknown client)
$sql0='CREATE TEMPORARY TABLE `targets0cashsalesreturnstl` as
        SELECT if((isnull(sm.`TeamLeader`) or sm.`TeamLeader`=0),asm.TeamLeader,sm.`TeamLeader`) as `TeamLeader`,
        Round((Sum(`Qty` * `UnitPrice`)-SUM(IF(ISNULL(fc.Amount),0,fc.Amount))),0) as CashSalesLessReturns, sm.BranchNo
    from
        (`invty_2sale` sm
        join `invty_2salesub` ss ON ((`sm`.`TxnID` = `ss`.`TxnID`)))  
        JOIN `acctg_2salemain` asm ON (sm.`Date`=asm.`Date` AND sm.`BranchNo`=asm.`BranchNo`)
        LEFT JOIN `'.$thisyr.'_1rtc`.`approvals_2freightclients` fc ON (fc.ForInvoiceNo=sm.SaleNo AND fc.BranchNo=sm.BranchNo AND fc.txntype=sm.txntype AND PriceFreightInclusive=1)
where  (ClientNo NOT IN (10000,10004) AND PaymentType<>2 and sm.txntype<>3 AND sm.BranchNo<>999) group by TeamLeader, sm.BranchNo;
';
$stmt=$link->prepare($sql0);$stmt->execute();

//Adjust sales that crossed companies
$sql0='CREATE TEMPORARY TABLE `AcrossCompaniesPaidItems` AS
    SELECT `ItemsFromBranchNo` AS BranchNo, sm.TeamLeader, 
    IFNULL(SUM(`cs`.`Amount`), 0) AS `ARCollected` 
    FROM `invty_4salesacrosscompanies` ac
        LEFT JOIN `invty_2sale` sm ON sm.SaleNo=ac.SaleNo AND sm.BranchNo=ac.InvoiceFromBranchNo
        JOIN `acctg_41clearedpaymts` `cs` ON cs.ForChargeInvNo=ac.SaleNo AND cs.ClientNo=sm.ClientNo 
        WHERE sm.txntype=2 GROUP BY `ItemsFromBranchNo`;';
$stmt=$link->prepare($sql0); $stmt->execute();

$sql0='CREATE TEMPORARY TABLE `AcrossCompaniesPaidInvoice` AS
    SELECT `InvoiceFromBranchNo` AS BranchNo,  sm.TeamLeader, 
    IFNULL(SUM(`cs`.`Amount`*-1), 0) AS `ARCollected`
    FROM `invty_4salesacrosscompanies` ac
        LEFT JOIN `invty_2sale` sm ON sm.SaleNo=ac.SaleNo AND sm.BranchNo=ac.InvoiceFromBranchNo
        JOIN `acctg_41clearedpaymts` cs ON cs.ForChargeInvNo=ac.SaleNo AND cs.ClientNo=sm.ClientNo 
        WHERE sm.txntype=2 GROUP BY `ItemsFromBranchNo`;';
$stmt=$link->prepare($sql0); $stmt->execute();

// Distribute cleared deposits & collections per branch, team leader
// NO BOUNCED DATA
$sql0='CREATE TEMPORARY TABLE `targets0cleareddeposits1step1` AS
SELECT cs.BranchNo,
        `aa`.`TeamLeader` AS `TeamLeader`,
        IFNULL(SUM(`cs`.`Amount`), 0) AS `ARCollected`
    FROM `acctg_41clearedpaymts` `cs` 
	JOIN `acctg_30uniar` aa on (aa.`Particulars`=cs.ForChargeInvNo AND aa.`BranchNo`=cs.BranchNo AND aa.ClientNo=`cs`.`ClientNo`) 
    LEFT JOIN `acctg_1clientsperbranch` `cb` ON `cs`.`BranchNo` = `cb`.`BranchNo` AND (`aa`.`ClientNo` = `cb`.`ClientNo`)
    WHERE aa.ClientNo>10000 AND aa.ClientNo<>10004  AND YEAR(`aa`.`Date`)='.$thisyr.'
    GROUP BY `aa`.`TeamLeader`,cs.BranchNo
    UNION ALL SELECT BranchNo, `TeamLeader`, `ARCollected` FROM `AcrossCompaniesPaidItems`
    UNION ALL SELECT BranchNo, `TeamLeader`, `ARCollected` FROM `AcrossCompaniesPaidInvoice`
    UNION ALL SELECT sm.BranchNo, `sm`.`TeamLeader`, Sum(Amount)*-1 AS OP 
    FROM `invty_2salesub` ss JOIN `acctg_2salemain` sm ON sm.TxnID=ss.TxnID WHERE DebitAccountID IN (405) GROUP BY `TeamLeader`, sm.BranchNo
    UNION ALL SELECT ord.BranchNo, sm.TeamLeader AS `TeamLeader`, Sum(Amount)*-1 AS ORDeduct 
    FROM `acctg_2collectsubdeduct` ord JOIN `acctg_2collectmain` orm ON orm.TxnID=ord.TxnID JOIN `acctg_2salemain` sm ON sm.`Date`=orm.`Date` AND sm.BranchNo=ord.BranchNo 
    WHERE ord.DebitAccountID<>160 GROUP BY `TeamLeader`, ord.BranchNo
    
    ;
    ';    
$stmt=$link->prepare($sql0); $stmt->execute();

$sql0='CREATE TEMPORARY TABLE `targets0cleareddeposits` AS
SELECT `step1`.`TeamLeader` AS `TeamLeader`, `BranchNo`, 
        ROUND(sum(`step1`.`ARCollected`),0) AS `ClearedAR`
    from
        `targets0cleareddeposits1step1` step1
    group by `step1`.`TeamLeader`, `BranchNo`;
';
$stmt=$link->prepare($sql0); $stmt->execute();


// Sum of monthly targets
$sql0='CREATE TEMPORARY TABLE `YrTargets` AS
    SELECT yt.BranchNo, (`yt`.`01`+`yt`.`02`+`yt`.`03`+`yt`.`04`+`yt`.`05`+`yt`.`06`+`yt`.`07`+`yt`.`08`+`yt`.`09`+`yt`.`10`+`yt`.`11`+`yt`.`12`) AS YrTarget 
    FROM `acctg_1yearsalestargets` yt;';
$stmt=$link->prepare($sql0);$stmt->execute();

// GET PRORATED TARGETS
$sql0='CREATE TEMPORARY TABLE tlratiostep1 AS
SELECT `TeamLeader`,`BranchNo`,COUNT(`TeamLeader`) AS TLCount, (SELECT COUNT(`BranchNo`) FROM `acctg_2salemain` bsm WHERE bsm.BranchNo=asm.BranchNo GROUP BY `BranchNo`) AS BranchCount FROM `acctg_2salemain` asm  GROUP BY `BranchNo`,`TeamLeader`;';
$stmt=$link->prepare($sql0);$stmt->execute();
$sql0='CREATE TEMPORARY TABLE targettl AS
SELECT `TeamLeader`,TRUNCATE(SUM((`TLCount`/`BranchCount`)*(`YrTarget`)),0) AS ProratedTarget FROM tlratiostep1 tl
JOIN `YrTargets` yt ON tl.BranchNo=yt.BranchNo GROUP BY TeamLeader;';
$stmt=$link->prepare($sql0);$stmt->execute();
//, IFNULL(`UndepPDC`,0) AS `UndepPDC`
$sql0='CREATE TEMPORARY TABLE `targetsforcalctl` AS
SELECT `b`.`TeamLeader`, IFNULL(`CashSales`,0) AS `CashSales`, IFNULL(`ClearedDeposits`,0) AS `ClearedDeposits`
FROM
        (SELECT TeamLeader FROM acctg_2salemain GROUP BY TeamLeader) b
LEFT JOIN (SELECT `TeamLeader`, Sum(`CashSalesLessReturns`) AS `CashSales`FROM `targets0cashsalesreturnstl` group by `TeamLeader`) `tcr` ON `tcr`.`TeamLeader`=`b`.`TeamLeader`
LEFT JOIN (SELECT `TeamLeader`, Sum(`ClearedAR`) AS `ClearedDeposits` FROM `targets0cleareddeposits`  group by `TeamLeader`) `tcd` ON `tcd`.`TeamLeader`=`b`.`TeamLeader` 
 
GROUP BY  `b`.`TeamLeader`'; 
$stmt=$link->prepare($sql0); $stmt->execute();

$sql0='CREATE TEMPORARY TABLE `NetValuesTL` AS
SELECT `TeamLeader`,`CashSales`+`ClearedDeposits` AS `NetforBranch` FROM `targetsforcalctl`;';
$stmt=$link->prepare($sql0); $stmt->execute();

$sql0='CREATE TEMPORARY TABLE `targetsforcalc` AS
SELECT `b`.`Branch` AS `Branch`, b.`BranchNo` AS `BranchNo`, IFNULL(`CashSales`,0) AS `CashSales`, IFNULL(`ClearedDeposits`,0) AS `ClearedDeposits`
FROM
        `1branches` `b`
LEFT JOIN (SELECT `BranchNo`, Sum(`CashSalesLessReturns`) AS `CashSales`FROM `targets0cashsalesreturns` GROUP BY `BranchNo`) `tcr` ON `tcr`.`BranchNo`=`b`.`BranchNo`
LEFT JOIN (SELECT `BranchNo`, Sum(`ClearedAR`) AS `ClearedDeposits` FROM `targets0cleareddeposits`  GROUP BY `BranchNo`) `tcd` ON `tcd`.`BranchNo`=`b`.`BranchNo` 
WHERE `b`.`Active`<>0 AND b.PseudoBranch=0;'; 

$stmt=$link->prepare($sql0);$stmt->execute();

$sql0='CREATE TEMPORARY TABLE `NetValues` AS
SELECT `BranchNo`,`CashSales`+`ClearedDeposits` AS `NetforBranch` FROM targetsforcalc;';
$stmt=$link->prepare($sql0);$stmt->execute();
?>