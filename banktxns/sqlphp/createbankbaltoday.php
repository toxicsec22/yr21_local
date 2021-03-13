<?php

$mainsql='SELECT `m`.`Order`,`m`.`AccountID`,`m`.`ShortAcctID`,ROUND((SUM(IFNULL(`bt`.`DepositAmt`,0)) - SUM(IFNULL(`bt`.`WithdrawAmt`,0))) + IFNULL(`bal`.`BalPerMonth`,0),2) AS `BankBalToday`,CURDATE() AS `Today`,IFNULL(`bal`.`BalPerMonth`,0) AS `BegBal` , (SELECT Balance FROM banktxns_banktxns bb WHERE bb.AccountID=m.AccountID ORDER BY TxnNo DESC LIMIT 1) AS TrueBalToday
FROM `banktxns_1maintaining` `m` LEFT JOIN 
`banktxns_banktxns` `bt` ON `m`.`AccountID` = `bt`.`AccountID` 
LEFT JOIN `banktxns_bankbalancespermonth` `bal` ON `bal`.`AccountID` = `m`.`AccountID`
WHERE `bt`.`TxnDate` <= CURDATE() AND `bt`.`TxnDate` >`bal`.`DateofBal` AND (`bal`.`DateofBal`=(SELECT Max(DateofBal) FROM banktxns_bankbalancespermonth bm WHERE bm.AccountID=bal.AccountID)) GROUP BY `m`.`AccountID` ORDER BY `m`.`Order`,`m`.`ShortAcctID`';


//replaced view banktxns_431qrybalancetoday
$sql1='Create temporary table banktxns_431qrybalancetoday AS '.$mainsql;
$stmt=$link->prepare($sql1);
$stmt->execute();




?>