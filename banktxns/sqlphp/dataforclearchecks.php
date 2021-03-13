<?php

if (!isset($_REQUEST['bank'])){ 
    $condition=($show==2?' AND cc.AccountID NOT IN (Select AccountID FROM `banktxns_1maintaining`) ':''); 
    $condition2=($show==2?' AND FromAccountID NOT IN (Select AccountID FROM `banktxns_1maintaining`) ':''); }
else { $condition=' AND cc.AccountID='.$bank;  $condition2=' AND FromAccountID="'.$bank.'"';   }

$sql1='CREATE TEMPORARY TABLE banktxns_21banktxnsunclearedcheck
SELECT `AccountID`,`TxnDate`,`BankBranch`,`CheckNo`,`WithdrawAmt`,`Cleared` ,`Remarks`,`TxnNo` FROM  `banktxns_banktxns` cc where `WithdrawAmt` <> 0 and `Cleared` = 0 '.$condition.' order by `AccountID`,`TxnDate`;';

$stmt=$link->prepare($sql1); $stmt->execute();

// called also in lookupbankdata
$sql1='CREATE TEMPORARY TABLE `banktxns_22unclearedcheckamts` AS 
SELECT `ca`.`ShortAcctID` AS `FromAccount`,`vm`.`CreditAccountID` AS `FromAccountID`,`vm`.`DateofCheck` AS `DateofCheck`,`vm`.`CVNo` AS `CVNo`,`vm`.`CheckNo` AS `CheckNo`,`vm`.`Payee` AS `Payee`,round(sum(`vs`.`Amount`),2) AS `AmountofCheck`,`vm`.`ReleaseDate` AS `ReleaseDate`,`vm`.`Cleared` AS `Cleared`,1 AS `CurrentYr`,`vm`.`CVNo` AS `TxnID` from (`acctg_1chartofaccounts` `ca` join (`acctg_2cvmain` `vm` join `acctg_2cvsub` `vs` on(`vm`.`CVNo` = `vs`.`CVNo`)) on(`ca`.`AccountID` = `vm`.`CreditAccountID`)) where `vm`.`Cleared` is null group by `ca`.`ShortAcctID`,`vm`.`DateofCheck`,`vm`.`CVNo`,`vm`.`CheckNo`,`vm`.`Payee`,`vm`.`Cleared` union all select `ca`.`ShortAcctID` AS `ShortAcctID`,`ulp`.`FromAccount` AS `FromAccount`,`ulp`.`DateofCheck` AS `DateofCheck`,`ulp`.`CVNo` AS `CVNo`,`ulp`.`CheckNo` AS `CheckNo`,`ulp`.`Payee` AS `Payee`,round(sum(`ulp`.`AmountofCheck`),2) AS `SumOfAmountofCheck`,`ulp`.`ReleaseDate` AS `ReleaseDate`,`ulp`.`Cleared` AS `Cleared`,0 AS `CurrentYr`,0 AS `TxnID` from (`acctg_3unclearedchecksfromlastperiod` `ulp` join `acctg_1chartofaccounts` `ca` on(`ulp`.`FromAccount` = `ca`.`AccountID`)) group by `ca`.`ShortAcctID`,`ulp`.`DateofCheck`,`ulp`.`CheckNo`,`ulp`.`Payee`,`ulp`.`CVNo`,`ulp`.`Cleared`,`ulp`.`Cleared` is null having `ulp`.`Cleared` is null order by `DateofCheck`,`CheckNo`;';

$stmt=$link->prepare($sql1); $stmt->execute();

$sql1='CREATE TEMPORARY TABLE banktxns_23toclearchecks AS
SELECT `ca`.`ShortAcctID` AS `ShortAcctID`,`bu`.`AccountID` AS `AccountID`,`bu`.`TxnDate` AS `TxnDate`,`bu`.`BankBranch` AS `BankBranch`,`bu`.`Remarks` AS `Remarks`,`bu`.`CheckNo` AS `BankCheckNo`,`bu`.`WithdrawAmt` AS `WithdrawAmt`,`bu`.`Cleared` AS `Cleared`,`uc`.`CheckNo` AS `OurCheckNo`,`uc`.`AmountofCheck` AS `AmountofCheck`,`uc`.`CurrentYr` AS `CurrentYr`,`bu`.`TxnNo` AS `TxnNo`,`uc`.`CVNo` AS `CVNo`,`uc`.`DateofCheck` AS `DateofCheck` from ((`banktxns_21banktxnsunclearedcheck` `bu` left join `banktxns_22unclearedcheckamts` `uc` on(`bu`.`AccountID` = `uc`.`FromAccountID` and `uc`.`DateofCheck` <= curdate() + interval 3 day and `bu`.`WithdrawAmt` = `uc`.`AmountofCheck` and `uc`.`ReleaseDate` is not null)) join `acctg_1chartofaccounts` `ca` on(`bu`.`AccountID` = `ca`.`AccountID`));';

$stmt=$link->prepare($sql1); $stmt->execute();