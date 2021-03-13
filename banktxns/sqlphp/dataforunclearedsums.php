<?php
$sql1='CREATE TEMPORARY TABLE `banktxns_431ciballforbalances` AS
SELECT 
    `jvs`.`Date`, CONCAT("JVNo", `jvm`.`JVNo`) AS `ControlNo`, `jvs`.`Amount` AS `DEBIT`, 0 AS `CREDIT`, `jvs`.`BranchNo`, `jvs`.`DebitAccountID` AS `AcctID`
FROM
    (`acctg_2jvmain` `jvm` JOIN `acctg_2jvsub` `jvs` ON (`jvm`.`JVNo` = `jvs`.`JVNo`))
WHERE
    `jvs`.`DebitAccountID` IN (SELECT `AccountID`  FROM `banktxns_1maintaining`) 

UNION ALL SELECT `jvs`.`Date` AS `Date`, CONCAT("JVNo", `jvm`.`JVNo`) AS `ControlNo`, 0 AS `Debit`, `jvs`.`Amount`, `jvs`.`BranchNo`, `jvs`.`CreditAccountID`
FROM
    (`acctg_2jvmain` `jvm` JOIN `acctg_2jvsub` `jvs` ON (`jvm`.`JVNo` = `jvs`.`JVNo`))
WHERE
    `jvs`.`CreditAccountID` IN (SELECT `AccountID`  FROM `banktxns_1maintaining`) 

UNION ALL SELECT 
    `dm`.`Cleared`, CONCAT("DepNo", `dm`.`DepositNo`), `ds`.`Amount` AS `Debit`, 0 AS `Credit`, `ds`.`BranchNo`, `dm`.`DebitAccountID`
FROM
    (`acctg_2depositmain` `dm` JOIN `acctg_2depositsub` `ds` ON (`dm`.`TxnID` = `ds`.`TxnID`))
WHERE
    `dm`.`DebitAccountID` IN (SELECT `AccountID`  FROM `banktxns_1maintaining`) 

UNION ALL SELECT `dm`.`Cleared`, CONCAT("DepNo", `dm`.`DepositNo`), 0 AS `Debit`, `ds`.`Amount`, `ds`.`BranchNo`, `ds`.`CreditAccountID`
FROM
    (`acctg_2depositmain` `dm` JOIN `acctg_2depositsub` `ds` ON (`dm`.`TxnID` = `ds`.`TxnID`))
WHERE
    `ds`.`CreditAccountID` IN (SELECT `AccountID`  FROM `banktxns_1maintaining`) 

UNION ALL SELECT  `dm`.`Cleared`, CONCAT("EncashNo", `dm`.`DepositNo`) , 0 AS `Debit`, SUM(`des`.`Amount`) AS `Credit`, `des`.`BranchNo`, `des`.`DebitAccountID`
FROM
    (`acctg_2depositmain` `dm` JOIN `acctg_2depencashsub` `des` ON (`dm`.`TxnID` = `des`.`TxnID`))
WHERE
    `des`.`DebitAccountID` IN (SELECT `AccountID` FROM `banktxns_1maintaining`)
GROUP BY `dm`.`Cleared` , CONCAT("EncashNo", `dm`.`DepositNo`) , `des`.`EncashDetails` , `des`.`BranchNo` , `des`.`DebitAccountID` 
UNION ALL SELECT `dm`.`Cleared`, CONCAT("EncashNo", `dm`.`DepositNo`) , 0 AS `Debit`, SUM(`des`.`Amount`) AS `Credit`, `des`.`BranchNo`, `dm`.`DebitAccountID`
FROM
    (`acctg_2depositmain` `dm` JOIN `acctg_2depencashsub` `des` ON (`dm`.`TxnID` = `des`.`TxnID`))
WHERE
    `dm`.`DebitAccountID` IN (SELECT  `AccountID` FROM `banktxns_1maintaining`)
GROUP BY `dm`.`Cleared` , CONCAT("EncashNo", `dm`.`DepositNo`) , `des`.`EncashDetails` , `des`.`BranchNo` , `dm`.`DebitAccountID` 
UNION ALL SELECT  `vm`.`Cleared`, CONCAT("CVNo", `vm`.`CVNo`) , 0 AS `Debit`, `vs`.`Amount`, `vs`.`BranchNo`, `vm`.`CreditAccountID`
FROM
    (`acctg_2cvmain` `vm` JOIN `acctg_2cvsub` `vs` ON (`vm`.`CVNo` = `vs`.`CVNo`))
WHERE
    `vm`.`Cleared` IS NOT NULL AND `vm`.`CreditAccountID` IN (SELECT `AccountID`  FROM `banktxns_1maintaining`) 
UNION ALL SELECT `vm`.`Cleared`, CONCAT("CVNo", `vm`.`CVNo`) , `vs`.`Amount`, 0 AS `Credit`, `vs`.`BranchNo`, `vs`.`DebitAccountID`
FROM
    (`acctg_2cvmain` `vm` JOIN `acctg_2cvsub` `vs` ON (`vm`.`CVNo` = `vs`.`CVNo`))
WHERE
    `vm`.`Cleared` IS NOT NULL AND `vs`.`DebitAccountID` IN (SELECT `AccountID`  FROM `banktxns_1maintaining`) 
UNION ALL SELECT 
    `cbs`.`DateBounced` AS `Date`, CONCAT("BouncedNoCR", `cm`.`CollectNo`,  "_", `cm`.`CheckNo`), SUM(`cs`.`Amount`) AS `Debit`, 0 AS `Credit`, `cs`.`BranchNo`, 200 AS `DebitAccountID`
FROM
    ((`acctg_2collectmain` `cm` JOIN `acctg_2collectsub` `cs` ON (`cm`.`TxnID` = `cs`.`TxnID`)) JOIN `acctg_2collectsubbounced` `cbs` ON (`cm`.`TxnID` = `cbs`.`TxnID`))
GROUP BY `cm`.`TxnID` 
UNION ALL SELECT  `cbs`.`DateBounced` AS `Date`, CONCAT("BouncedNoCR", `cm`.`CollectNo`, "_", `cm`.`CheckNo`), 0 AS `Debit`, SUM(`cs`.`Amount`), `cs`.`BranchNo`, `cbs`.`CreditAccountID`
FROM
    ((`acctg_2collectmain` `cm` JOIN `acctg_2collectsub` `cs` ON (`cm`.`TxnID` = `cs`.`TxnID`)) JOIN `acctg_2collectsubbounced` `cbs` ON (`cm`.`TxnID` = `cbs`.`TxnID`))
GROUP BY `cm`.`TxnID` 
UNION ALL SELECT  `cbs`.`DateBounced` AS `Date`, CONCAT("BouncedNoCR", `cm`.`CollectNo`, "_", `cm`.`CheckNo`), SUM(`cs`.`Amount`) AS `Amount`, 0 AS `Credit`, `cs`.`BranchNo`, `cbs`.`CreditAccountID`
FROM
    ((`acctg_2collectmain` `cm` JOIN `acctg_2collectsubdeduct` `cs` ON (`cm`.`TxnID` = `cs`.`TxnID`)) JOIN `acctg_2collectsubbounced` `cbs` ON (`cm`.`TxnID` = `cbs`.`TxnID`))
GROUP BY `cm`.`TxnID` 
UNION ALL SELECT `cbs`.`DateBounced` AS `Date`, CONCAT("BouncedNoCR", `cm`.`CollectNo`, "_", `cm`.`CheckNo`), 0 AS `Debit`, SUM(`cs`.`Amount`) , `cs`.`BranchNo`, `cs`.`DebitAccountID`
FROM
    ((`acctg_2collectmain` `cm` JOIN `acctg_2collectsubdeduct` `cs` ON (`cm`.`TxnID` = `cs`.`TxnID`)) JOIN `acctg_2collectsubbounced` `cbs` ON (`cm`.`TxnID` = `cbs`.`TxnID`))
GROUP BY `cm`.`TxnID` 
UNION ALL SELECT  `bs`.`DateBounced` AS `Date`, CONCAT("BouncedPDCNo ", `bm`.`PDCNo`) AS `Particulars`, 0 AS `DEBIT`, `bm`.`AmountofPDC` AS `CREDIT`, `bm`.`BranchNo`, `bs`.`CreditAccountID`
FROM
    (`acctg_3undepositedpdcfromlastperiod` `bm` JOIN `acctg_3undepositedpdcfromlastperiodbounced` `bs` ON (`bm`.`UndepPDCId` = `bs`.`UndepPDCId`))
ORDER BY `Date`;';

$stmt=$link->prepare($sql1); $stmt->execute();

$sql1='CREATE TEMPORARY TABLE `banktxns_431cibbegbalforbalances` AS select `ca`.`AccountID` AS `AccountID`,`ca`.`ShortAcctID` AS `ShortAcctID`,sum(`b`.`BegBalance`) AS `SumOfBegBalance` from (`acctg_1chartofaccounts` `ca` join `acctg_1begbal` `b` on(`ca`.`AccountID` = `b`.`AccountID`)) where `b`.`AccountID`  IN (SELECT `AccountID`  FROM `banktxns_1maintaining`) group by `ca`.`AccountID`,`ca`.`ShortAcctID` ;';

$stmt=$link->prepare($sql1); $stmt->execute();

$sql1='CREATE TEMPORARY TABLE `banktxns_431qrysumofuncleareddeposits` AS select `AccountID`,sum(`DepositAmt`) AS `UnclearedSum` from `banktxns_banktxns` group by `AccountID`, `Cleared` having `Cleared` = 0;';

$stmt=$link->prepare($sql1); $stmt->execute();

$sql1='CREATE TEMPORARY TABLE `banktxns_432cibbalancestodate` AS select `m`.`AccountID`,`m`.`ShortAcctID`,`b`.`SumOfBegBalance` AS `MonthBegBal`,sum(ifnull(`c`.`DEBIT`,0)) - sum(ifnull(`c`.`CREDIT`,0)) AS `DRLessCR` from ((`banktxns_1maintaining` `m` left join `banktxns_431ciballforbalances` `c` on(`m`.`AccountID` = `c`.`AcctID` and `c`.`Date` is not null)) left join `banktxns_431cibbegbalforbalances` `b` on(`m`.`AccountID` = `b`.`AccountID`)) group by `m`.`AccountID`;';

$stmt=$link->prepare($sql1); $stmt->execute();
?>