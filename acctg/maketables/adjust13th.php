<?php
$sql0='CREATE TEMPORARY TABLE calc13th AS
SELECT `p`.`IDNo` AS `IDNo`,
        TRUNCATE((((IFNULL(SUM(`p`.`Basic`), 0) - IFNULL(SUM(`p`.`AbsenceBasic`), 0)) - IFNULL(SUM(`p`.`UndertimeBasic`), 0)) / 12), 2) AS `13thBasicCalc`,
        TRUNCATE((((((IFNULL(SUM(`p`.`DeM`), 0)) + IFNULL(SUM(`p`.`TaxSh`), 0)) - IFNULL(SUM(`p`.`AbsenceTaxSh`), 0)) - IFNULL(SUM(`p`.`UndertimeTaxSh`), 0)) / 12), 2) AS `13thTaxShCalc`
    FROM
        `'.$currentyr.'_1rtc`.`payroll_25payroll` `p` WHERE (`p`.`PayrollID` = 26) GROUP BY `p`.`IDNo`  
UNION ALL 
    SELECT `p`.`IDNo` AS `IDNo`,
        TRUNCATE((((IFNULL(SUM(`p`.`Basic`), 0) - IFNULL(SUM(`p`.`AbsenceBasic`), 0)) - IFNULL(SUM(`p`.`UndertimeBasic`), 0)) / 12), 2) AS `13thBasicCalc`,
        TRUNCATE((((((IFNULL(SUM(`p`.`DeM`), 0)) + IFNULL(SUM(`p`.`TaxSh`), 0)) - IFNULL(SUM(`p`.`AbsenceTaxSh`), 0)) - IFNULL(SUM(`p`.`UndertimeTaxSh`), 0)) / 12), 2) AS `13thTaxShCalc`
    FROM `payroll_25payroll` `p` WHERE (`p`.`PayrollID` <='.($month*2).') GROUP BY `p`.`IDNo`;'; //echo $sql0; break;
$stmt0=$link->prepare($sql0); $stmt0->execute();

$sql0='CREATE TEMPORARY TABLE totalcalculated AS
SELECT SUM(`13thBasicCalc`) AS `13thBasicAsOf`, SUM(`13thTaxShCalc`) AS `13thAllowAsOf`,  IF(a.`DefaultBranchAssignNo` IN (SELECT BranchNo FROM `1branches` WHERE CompanyNo=e.RCompanyNo),a.`DefaultBranchAssignNo`,
(SELECT BranchNo FROM `1branches` WHERE PseudoBranch=1 AND BranchNo<>95 AND CompanyNo=e.RCompanyNo)) AS RecordInBranchNo,
IF(PositionID IN (1,2,32,33,37,81,38,50,51,52,55),967,966) AS AccountID
FROM `calc13th` yt JOIN `attend_1defaultbranchassign` a ON a.IDNo=yt.IDNo
JOIN `1employees` e ON e.IDNo=yt.IDNo JOIN `attend_30latestpositionsinclresigned` lpr ON yt.IDNo=lpr.IDNo GROUP BY RecordInBranchNo,AccountID;';
$stmt0=$link->prepare($sql0); $stmt0->execute();

$sql0='CREATE TEMPORARY TABLE paidorrecorded AS
SELECT BranchNo, AccountID, SUM(Amount) AS TotalRecorded FROM `acctg_0unialltxns` uni WHERE AccountID IN (966,967) GROUP BY BranchNo,AccountID;';
$stmt0=$link->prepare($sql0); $stmt0->execute();
?>