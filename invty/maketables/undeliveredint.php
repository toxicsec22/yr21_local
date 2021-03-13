<?php
$reqtxnid=$_REQUEST['ReqTxnID'];
$sql0='CREATE TEMPORARY TABLE unitransferred AS SELECT `ReqTxnID`,`ItemCode`, `QtySent`, `QtyReceived`
    FROM `invty_2transfer` `tm` JOIN `invty_2transfersub` `ts` ON `tm`.`TxnID` = `ts`.`TxnID`
    WHERE (MONTH(`tm`.`DateOUT`) > '.$asofmonth.') AND (YEAR(`tm`.`DateOUT`) = '.$currentyr.') AND tm.ReqTxnID='.$reqtxnid.' 
    UNION ALL SELECT `ReqTxnID`, `ItemCode`, `Sent`, `Recvd`
    FROM `'.$currentyr.'_static`.`invty_2transferredasofclosed` `tc` WHERE ReqTxnID='.$reqtxnid;
$stmt0=$link->prepare($sql0); $stmt0->execute();

$sql0='CREATE TEMPORARY TABLE `transferred` AS
    SELECT `ReqTxnID`, `ItemCode`, SUM(`QtySent`) AS `Sent`, SUM(`QtyReceived`) AS `Recvd`
    FROM `unitransferred` GROUP BY `ItemCode`;';
$stmt0=$link->prepare($sql0); $stmt0->execute();

$sql0='CREATE TEMPORARY TABLE `undeliveredreq` AS
    SELECT `m`.`TxnID` AS `ReqTxnID`, m.RequestNo,
        `s`.`ItemCode` AS `ItemCode`,
        `s`.`RequestQty` AS `RequestQty`,
        (`s`.`RequestQty` - IFNULL(`r`.`Recvd`, 0)) AS `RcvBal`,
        (`s`.`RequestQty` - IFNULL(`r`.`Sent`, 0)) AS `SendBal`
    FROM
        `invty_3branchrequest` `m` JOIN .`invty_3branchrequestsub` `s` ON `m`.`TxnID` = `s`.`TxnID`
        LEFT JOIN `transferred` `r` ON `s`.`ItemCode` = `r`.`ItemCode` AND (`m`.`TxnID` = `r`.`ReqTxnID`)
    GROUP BY `m`.`TxnID` , `s`.`ItemCode`;';
$stmt0=$link->prepare($sql0); $stmt0->execute();
?>