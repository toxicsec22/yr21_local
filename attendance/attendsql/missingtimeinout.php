<?php
$sql0='DROP TEMPORARY TABLE IF EXISTS `attend_41missingtimeinout';
$stmt0=$link->prepare($sql0); $stmt0->execute();

$sql0='CREATE TEMPORARY TABLE `attend_41missingtimeinout` AS 
SELECT 
        `a`.`TxnID` AS `TxnID`,
        `a`.`DateToday` AS `DateToday`,
        `a`.`IDNo` AS `IDNo`,
        `a`.`TimeIn` AS `TimeIn`,
        `a`.`TimeOut` AS `TimeOut`,
        `a`.`RemarksHR` AS `RemarksHR`,
        `a`.`RemarksDept` AS `RemarksDept`,
        `l`.`LeaveName` AS `LeaveName`,
        `a`.`BranchNo` AS `BranchNo`,
        `b`.`Branch` AS `Branch`
    FROM
        (((`attend_2attendancedates` `ad`
        JOIN `attend_2attendance` `a` ON (`ad`.`DateToday` = `a`.`DateToday`))
        JOIN `1branches` `b` ON (`a`.`BranchNo` = `b`.`BranchNo`))
        JOIN `attend_0leavetype` `l` ON (`a`.`LeaveNo` = `l`.`LeaveNo`))
    WHERE
    `ad`.`Posted` = 0
            AND ((`a`.`TimeIn` IS NULL
            AND `a`.`TimeOut` IS NOT NULL) 
            OR (`a`.`TimeOut` IS NULL
            AND `a`.`TimeIn` IS NOT NULL
            AND IF(CURTIME() < STR_TO_DATE(Shift+9, "%T"),
            `a`.`DateToday` < CURDATE(),
            `a`.`DateToday` <= CURDATE())));
';
$stmt0=$link->prepare($sql0); $stmt0->execute();

