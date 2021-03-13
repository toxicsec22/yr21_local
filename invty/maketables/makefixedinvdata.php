<?php

// get dateclosedby for INVTY
	$resdate=$link->query('Select IF(YEAR(DataClosedBy)<>'.$currentyr.',0,Month(DataClosedBy)) as AsofMonth from `00dataclosedby` where ForDB=0'); $rowdate=$resdate->fetch();
	$asofmonth=$rowdate['AsofMonth'];

// TRANSFERRED ITEMS
$sql0='Drop table if exists `' . $currentyr . '_static`.`invty_2transferredasofclosed`;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
	$sql1='CREATE TABLE `' . $currentyr . '_static`.`invty_2transferredasofclosed` (
  `ReqTxnID` int(11) NOT NULL,
  `ItemCode` smallint(6) NOT NULL DEFAULT \'0\',
  `Sent` double DEFAULT NULL,
  `Recvd` double DEFAULT NULL,
  KEY `ItemCodetxfrd` (`ItemCode`),
  KEY `Req` (`ReqTxnID`)
) 
as
SELECT 
        `tm`.`ReqTxnID` AS `ReqTxnID`,
        `ts`.`ItemCode` AS `ItemCode`,
        SUM(`ts`.`QtySent`) AS `Sent`,
        SUM(`ts`.`QtyReceived`) AS `Recvd`
    FROM
        (`invty_2transfer` `tm`
        JOIN `invty_2transfersub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
    where Month(tm.DateOUT)<='.$asofmonth.' AND Year(tm.DateOUT)='.$currentyr.' GROUP BY `tm`.`ForRequestNo` , `ts`.`ItemCode` ;';
	$stmt1=$link->prepare($sql1); $stmt1->execute();
 

?>