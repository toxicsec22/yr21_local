<?php

// called in banktxns_33tocleardeposits
$sql1='CREATE TEMPORARY TABLE `banktxns_31banktxnsuncleareddep` AS SELECT `AccountID`, `TxnDate`, `BankBranch`, `DepositAmt`, `Cleared`, `Remarks`, CONCAT(`Particulars`," ",`BankBranch`," ",`CheckNo`," ",`BankTransCode`) AS `Details`, `TxnNo` FROM `banktxns_banktxns` WHERE `DepositAmt` <> 0 and `Cleared` = 0 ORDER BY `AccountID`,`TxnDate`, `DepositAmt` DESC;';

$stmt=$link->prepare($sql1); $stmt->execute();

// called in lookupgenacctg, lookupbankdata
$sql1='CREATE TEMPORARY TABLE `banktxns_32uniunclearedfordeptotals` AS SELECT `ds`.`TxnID` AS `TxnID`,SUM(`ds`.`Amount`) AS `SumOfAmount`,MIN(`ds`.`Type`) AS `FirstOfType`,`ds`.`BranchNo` AS `BranchNo` FROM (`acctg_2depositsub` `ds` JOIN `acctg_2depositmain` `dm` ON (`dm`.`TxnID` = `ds`.`TxnID`)) WHERE `dm`.`Cleared` IS NULL GROUP BY `ds`.`TxnID` UNION ALL SELECT `de`.`TxnID` AS `TxnID`,sum(`de`.`Amount`) * -1 AS `SumOfAmount`,0 AS `FirstOfType`,`de`.`BranchNo` AS `BranchNo` FROM (`acctg_2depencashsub` `de` join `acctg_2depositmain` `dm` on(`dm`.`TxnID` = `de`.`TxnID`)) WHERE `dm`.`Cleared` IS NULL GROUP BY `de`.`TxnID`;';

$stmt=$link->prepare($sql1); $stmt->execute();

// called in cleardeposits, banktxns_33tocleardeposits
$sql1='CREATE TEMPORARY TABLE `banktxns_32uncleareddepamt` AS select `dm`.`TxnID` AS `TxnID`,`dm`.`DebitAccountID` AS `AccountID`,`ca`.`ShortAcctID` AS `Bank`,`dm`.`DepositNo` AS `DepositNo`,`dm`.`Date` AS `Date`,SUM(`dt`.`SumOfAmount`) AS `OurDepAmt`,`dt`.`BranchNo` AS `OurBranchNo`,`dt`.`FirstOfType` AS `Type` FROM ((`acctg_2depositmain` `dm` JOIN `banktxns_32uniunclearedfordeptotals` `dt` ON (`dm`.`TxnID` = `dt`.`TxnID`)) JOIN `acctg_1chartofaccounts` `ca` ON (`dm`.`DebitAccountID` = `ca`.`AccountID`)) WHERE `dm`.`Cleared` IS NULL AND `dm`.`Posted` <> 0 GROUP BY `dm`.`TxnID`,`dm`.`DebitAccountID`,`ca`.`ShortAcctID`,`dm`.`DepositNo`,`dm`.`Date`,`dt`.`FirstOfType` ORDER BY `ca`.`ShortAcctID`,`dm`.`Date`;';

$stmt=$link->prepare($sql1); $stmt->execute();

// called in lookupgenacctg, lookupbankdata, cleardeposits
$sql1='CREATE TEMPORARY TABLE `banktxns_33tocleardeposits` AS SELECT `ca`.`ShortAcctID` AS `ShortAcctID`,`ud`.`AccountID` AS `AccountID`,`ud`.`TxnDate` AS `TxnDate`,`ud`.`BankBranch` AS `BankBranch`,`ud`.`Remarks` AS `Remarks`,`ud`.`Details` AS `Details`,`ud`.`DepositAmt` AS `DepositAmt`,`ua`.`OurDepAmt` AS `OurDepAmt`,`ua`.`OurBranchNo` AS `OurBranchNo`,`ud`.`TxnNo` AS `TxnNo`,`ua`.`DepositNo` AS `DepositNo` FROM ((`acctg_1chartofaccounts` `ca` JOIN `banktxns_31banktxnsuncleareddep` `ud` on(`ca`.`AccountID` = `ud`.`AccountID`)) LEFT JOIN `banktxns_32uncleareddepamt` `ua` ON (`ud`.`AccountID` = `ua`.`AccountID` AND `ud`.`DepositAmt` = `ua`.`OurDepAmt`));';

$stmt=$link->prepare($sql1); $stmt->execute();