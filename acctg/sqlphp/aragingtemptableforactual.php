<?php
// get cleared payments
$sql0='CREATE TEMPORARY TABLE `31` AS
    SELECT 
        `cm`.`ClientNo` AS `ClientNo`, 
        `cs`.`ForChargeInvNo` AS `ForChargeInvNo`,
        SUM(`cs`.`Amount`) AS `Amount`,
        `cs`.`BranchNo` AS `BranchNo`
    FROM
        `acctg_2collectmain` `cm` JOIN `acctg_2collectsub` `cs` ON `cm`.`TxnID` = `cs`.`TxnID` 
        JOIN `acctg_2depositsub` `s`  ON `s`.`CRNo`=CONCAT("C-",cm.BranchSeriesNo,"-",`cm`.`CollectNo`) AND s.BranchNo=`cs`.`BranchNo` AND `s`.`ClientNo`=`cm`.`ClientNo`
        JOIN `acctg_2depositmain` `m` ON `m`.`TxnID` = `s`.`TxnID`
    WHERE
        (`cs`.`ForChargeInvNo` IS NOT NULL) AND (`cs`.`ForChargeInvNo` NOT LIKE "") AND (`cs`.`CreditAccountID`=200)  AND (m.Cleared IS NOT NULL) AND (m.Cleared<=\''.$defaultdate.'\') AND (cm.`ClientNo` <> 10000)
    GROUP BY `cm`.`ClientNo` , `cs`.`ForChargeInvNo` , `cs`.`BranchNo` 
    
    UNION ALL SELECT 
        `ds`.`ClientNo` AS `ClientNo`,
        `ds`.`ForChargeInvNo` AS `ForChargeInvNo`,
        SUM(`ds`.`Amount`) AS `SumOfAmount`,
        `ds`.`BranchNo` AS `BranchNo`
    FROM
        (`acctg_2depositmain` `dm`
        JOIN `acctg_2depositsub` `ds` ON ((`dm`.`TxnID` = `ds`.`TxnID`)))
    WHERE
        (`ds`.`ForChargeInvNo` IS NOT NULL) AND (`ds`.`CreditAccountID`=200) AND (`ds`.`Amount` > 0) AND (dm.Cleared IS NOT NULL) AND (dm.Cleared<=\''.$defaultdate.'\') AND (ds.`ClientNo` <> 10000)
         GROUP BY `ds`.`ClientNo` , `ds`.`ForChargeInvNo` , `ds`.`BranchNo` 
    ORDER BY `ClientNo`;';
$stmt=$link->prepare($sql0); $stmt->execute();
// get total paid per invoice
$sql0='CREATE TEMPORARY TABLE `32` AS
    SELECT 
        `arp`.`ClientNo` AS `ClientNo`,
        `arp`.`ForChargeInvNo` AS `ForChargeInvNo`,
        SUM(`arp`.`Amount`) AS `RcdAmount`,
        `arp`.`BranchNo` AS `BranchNo`
    FROM
        `31` `arp`
    GROUP BY `arp`.`ClientNo` , `arp`.`ForChargeInvNo` , `arp`.`BranchNo`;';
$stmt=$link->prepare($sql0); $stmt->execute();
// get unpaid invoices
$sql0='CREATE TEMPORARY TABLE `33` AS
    SELECT 
        `ar`.`ClientNo` AS `ClientNo`, `ForChargeInvNo`,
        `ar`.`Date` AS `Date`, 
        SUM(`ar`.`SaleAmt` - IFNULL(`r`.`RcdAmount`, 0)) AS `InvBalance`,
        `ar`.`BranchNo` AS `BranchNo`
    FROM
        (`acctg_30uniar` `ar`
        LEFT JOIN `32` `r` ON (((`ar`.`Particulars` = `r`.`ForChargeInvNo`)
            AND (`ar`.`BranchNo` = `r`.`BranchNo`))))
    WHERE ((`ar`.`ClientNo` <> 10000) AND (`ar`.`ClientNo` IS NOT NULL) AND (`ar`.`Date`<=\''.$defaultdate.'\'))
    GROUP BY `ar`.`ClientNo` , `ar`.`BranchNo`, `ar`.`Particulars` HAVING `InvBalance`<>0;'; 
$stmt=$link->prepare($sql0); $stmt->execute();
// get due dates per unpaid invoice
$sql0='CREATE TEMPORARY TABLE `34` AS
    SELECT 
        `bal`.`ClientNo` AS `ClientNo`, `Date`,
        (`bal`.`Date` + INTERVAL IFNULL(`c`.`Terms`, 0) DAY) AS `Due`,
        TRUNCATE(SUM(`bal`.`InvBalance`), 2) AS `ARAmount`,
        `bal`.`BranchNo` AS `BranchNo`
    FROM
        (`33` `bal`
        LEFT JOIN `1clients` `c` ON ((`bal`.`ClientNo` = `c`.`ClientNo`)))
    WHERE
        (`bal`.`InvBalance` <> 0)
    GROUP BY `bal`.`ClientNo` , (`bal`.`Date` + INTERVAL IFNULL(`c`.`Terms`, 0) DAY) , `bal`.`BranchNo`
    HAVING ((`ARAmount` > 1) OR (`ARAmount` < -(1)));';
$stmt=$link->prepare($sql0); $stmt->execute();

?>