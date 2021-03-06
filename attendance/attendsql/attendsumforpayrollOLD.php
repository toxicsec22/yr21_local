<?php
$sql0='DROP TEMPORARY TABLE IF EXISTS `443semimonthlyempnoattendance';
$stmt0=$link->prepare($sql0); $stmt0->execute();

$sql0='CREATE TEMPORARY TABLE `443semimonthlyempnoattendance` AS
SELECT 
    `d`.`PayrollID`, `a`.`IDNo`, `rc`.`RegDayCount`, COUNT(`a`.`DateToday`) AS `ActualAttendDates`,
    `rc`.`RegDayCount` - COUNT(`a`.`DateToday`) AS `DaysNotWithUs`
FROM
    (((`attend_2attendancedates` `d`
    JOIN `attend_2attendance` `a` ON (`d`.`DateToday` = `a`.`DateToday`))
    JOIN (SELECT  `d`.`PayrollID`,  COUNT(`d`.`DateToday`) AS `RegDayCount` FROM `attend_2attendancedates` `d`  WHERE
    DAYOFWEEK(`d`.`DateToday`) <> 1 AND `d`.`TypeOfDayNo` = 0 AND `d`.`PayrollID`='. $_POST['payrollid'].') `rc` ON (`rc`.`PayrollID` = `d`.`PayrollID`))
    JOIN `attend_30latestpositionsinclresigned` `e` ON (`e`.`IDNo` = `a`.`IDNo`))
WHERE
    DAYOFWEEK(`d`.`DateToday`) <> 1
        AND `d`.`TypeOfDayNo` = 0
        AND `e`.`LatestDorM` = 1 AND `d`.`PayrollID`='. $_POST['payrollid'].'
GROUP BY `d`.`PayrollID` , `a`.`IDNo`
HAVING `rc`.`RegDayCount` <> `ActualAttendDates`';
$stmt0=$link->prepare($sql0); $stmt0->execute();

$sql0='DROP TEMPORARY TABLE IF EXISTS `attend_44sumforpayroll';
$stmt0=$link->prepare($sql0); $stmt0->execute();

$sql0='CREATE TEMPORARY TABLE `attend_44sumforpayroll` AS
SELECT 
    `d`.`PayrollID`, `a`.`IDNo`,
    COUNT(IF(`a`.`LeaveNo` IN (11 , 20, 21, 30), 1, NULL)) AS `RegDaysPresent`,
    COUNT(IF(`a`.`LeaveNo` IN (10 , 17, 18, 19), 1, NULL)) + IFNULL(`sm`.`DaysNotWithUs`, 0) AS `LWOPDays`,
    COUNT(IF(`a`.`LeaveNo` = 12, 1, NULL)) AS `LegalDays`,
    COUNT(IF(`a`.`LeaveNo` = 13, 1, NULL)) AS `SpecDays`,
    COUNT(IF(`a`.`TimeIn` IS NULL AND `d`.`TypeOfDayNo` <> 2 AND `a`.`LeaveNo` = 14, 1, NULL)) AS `SLDays`,
    COUNT(IF(`a`.`TimeIn` IS NULL AND `d`.`TypeOfDayNo` <> 2 AND `a`.`LeaveNo` = 31, 1, NULL)) AS `VLDays`,
    COUNT(IF(`a`.`LeaveNo` = 15, 1, NULL)) AS `RestDays`,
    COUNT(IF(`a`.`LeaveNo` IN (16 , 32), 1, NULL)) AS `LWPDays`,
    COUNT(IF(`a`.`LeaveNo` = 22, 1, NULL)) AS `QDays`,
    IFNULL(SUM(CASE
                WHEN
                    (`a`.`LeaveNo` IN (11 , 20, 21, 30)
                        AND DAYOFWEEK(`a`.`DateToday`) = 7 -- Saturdays for existing monthly employees 
                        AND (`e`.`WithSat` = 1 OR `e`.`WithSat` = 0 ) AND sm.IDNo IS NULL)
                THEN
                    IF(REGHOURS(`a`.`TimeIn`,
                                `a`.`TimeOut`,
                                `e`.`JobLevelID`,
                                `a`.`Shift`) / 4 > 1,
                        1,
                        REGHOURS(`a`.`TimeIn`,
                                `a`.`TimeOut`,
                                `e`.`JobLevelID`,
                                `a`.`Shift`) / 4)
                WHEN (`a`.`LeaveNo` IN (15)
                        AND DAYOFWEEK(`a`.`DateToday`) = 7 -- Saturdays for new monthly employees
                        AND (`e`.`WithSat` = 0 ) AND sm.IDNo IS NOT NULL)
                THEN 1
            END),
            0) + IFNULL(SUM(CASE
                WHEN
                    (`a`.`LeaveNo` IN (11 , 20, 21, 30)
                        AND DAYOFWEEK(`a`.`DateToday`) <> 7
                        AND (`e`.`WithSat` = 1 OR `e`.`WithSat` = 0))
                THEN
                    REGHOURS(`a`.`TimeIn`,
                            `a`.`TimeOut`,
                            `e`.`JobLevelID`,
                            `a`.`Shift`) / 8
            END),
            0) + IFNULL(SUM(CASE
                WHEN
                    (`a`.`LeaveNo` IN (11 , 20, 21, 30)
                        AND `e`.`WithSat` = 2)
                THEN
                    REGHOURS(`a`.`TimeIn`,
                            `a`.`TimeOut`,
                            `e`.`JobLevelID`,
                            `a`.`Shift`) / 8
            END),
            0) + COUNT(IF(`a`.`OvertimeREMOVE` = 3, 1, NULL)) AS `RegDaysActual`,
    SUM(IF(`a`.`LeaveNo` = 12
            AND `a`.`OvertimeREMOVE` <> 0,
        IF(`a`.`OvertimeREMOVE` = 4,
            RDOTHOURS(`a`.`TimeIn`,
                    `a`.`TimeOut`,
                    `e`.`JobLevelID`,
                    `a`.`Shift`),
            REGHOURS(`a`.`TimeIn`,
                    `a`.`TimeOut`,
                    `e`.`JobLevelID`,
                    `a`.`Shift`)),
        0)) AS `LegalHrsOT`,
    SUM(IF(`a`.`LeaveNo` = 13
            AND `a`.`OvertimeREMOVE` <> 0,
        IF(`a`.`OvertimeREMOVE` = 4,
            RDOTHOURS(`a`.`TimeIn`,
                    `a`.`TimeOut`,
                    `e`.`JobLevelID`,
                    `a`.`Shift`),
            REGHOURS(`a`.`TimeIn`,
                    `a`.`TimeOut`,
                    `e`.`JobLevelID`,
                    `a`.`Shift`)),
        0)) AS `SpecHrsOT`,
    SUM(IF(`a`.`LeaveNo` = 15
            AND `a`.`OvertimeREMOVE` NOT IN (0 ),
        REGHOURS(`a`.`TimeIn`,
                `a`.`TimeOut`,
                `e`.`JobLevelID`,
                `a`.`Shift`),
        0)) AS `RestHrsOT`,
    SUM(IF(`a`.`LeaveNo` = 15
            AND `a`.`OvertimeREMOVE` = 5,
        RDOTHOURS(TIME(CONCAT(a.Shift+9,":00")),
                IF(`ot`.`EndOfOT` IS NULL,
                    `a`.`TimeOut`,
                    IF(`ot`.`EndOfOT` < `a`.`TimeOut`,
                        `ot`.`EndOfOT`,
                        `a`.`TimeOut`)),
                `e`.`JobLevelID`,
                `a`.`Shift`),
        0)) AS `ExcessRestHrsOT`,
    SUM(IFNULL(`l`.`PaidLegal`, 0)) AS `PaidLegalDays`,
    SUM(IF(`a`.`OvertimeREMOVE` <> 0
            AND `a`.`LeaveNo` IN (11 , 20, 21, 30),
        TRUNCATE(TIMESTAMPDIFF(MINUTE,
                CONCAT(`a`.`DateToday`, " ", `a`.`Shift` + 9),
                CONCAT(IF(`a`.`OvertimeREMOVE` = 3,
                            `a`.`DateToday` + INTERVAL 1 DAY,
                            `a`.`DateToday`),
                        " ",
                        IF(`a`.`OvertimeREMOVE` = 2,
                            IF(`ot`.`EndOfOT` IS NULL,
                                `a`.`TimeOut`,
                                IF(`ot`.`EndOfOT` < `a`.`TimeOut`,
                                    `ot`.`EndOfOT`,
                                    `a`.`TimeOut`)),
                            `a`.`TimeOut`))) / 60,
            2),
        0)) AS `RegOTHrs`,
    `e`.`Resigned` AS `Resigned`
FROM
    `attend_2attendance` `a` JOIN `attend_2attendancedates` `d` ON (`d`.`DateToday` = `a`.`DateToday`)
    LEFT JOIN `attend_30latestpositionsinclresigned` `e` ON (`e`.`IDNo` = `a`.`IDNo`)
    LEFT JOIN `approvals_5ot` `ot` ON (`a`.`IDNo` = `ot`.`IDNo` AND `a`.`DateToday` = `ot`.`DateToday`)
    LEFT JOIN `attend_441legaldays` `l` ON (`l`.`IDNo` = `a`.`IDNo` AND `l`.`LegalHoliday` = `a`.`DateToday`)
    LEFT JOIN `443semimonthlyempnoattendance` `sm` ON (`sm`.`PayrollID` = `d`.`PayrollID` AND `sm`.`IDNo` = `a`.`IDNo`) 
    WHERE `d`.`PayrollID`='. $_POST['payrollid'].'
GROUP BY `d`.`PayrollID` , `a`.`IDNo` 
HAVING IF(`e`.`Resigned` <> 0, `SLDays` + `VLDays` + `LWPDays` + `QDays` + `RegDaysPresent` <> 0, 1)';
$stmt0=$link->prepare($sql0); $stmt0->execute();