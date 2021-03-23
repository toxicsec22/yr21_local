<?php
if (allowedToOpen(array(7481,8282),'1rtc')) { $sql0='SELECT `SupplierName` AS `BECSName`, `SupplierNo` AS `BECSNo`, "S" AS `BECS` FROM `1suppliers` ';}
 else {   $sql0='SELECT "Supplier" AS `BECSName`, "-1" AS `BECSNo`, "S" AS `BECS` ';} // this does not show since SupplierNo will never equal
$sql0='CREATE TEMPORARY TABLE BECSList AS '.$sql0.' UNION SELECT  `ClientName`, `ClientNo`, "C" AS `BECS` FROM `1clients` 
    UNION SELECT `Branch`, `BranchNo`, "B" AS `BECS` FROM `1branches`
    UNION SELECT CONCAT(`id`.`FirstName`, \' \', `id`.`SurName`) AS `EmployeeName`, `id`.`IDNo` AS `IDNo`, "E" AS `BECS` 
    FROM `1_gamit`.`0idinfo` `id` WHERE `id`.`Resigned?` = 0 AND `id`.`IDNo` <> 0
    ORDER BY `BECSName`';
$stmt0=$link->prepare($sql0); $stmt0->execute();
    