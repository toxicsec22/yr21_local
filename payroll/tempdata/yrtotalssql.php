<?php
// if from Payroll Totals
 $condition=!isset($_REQUEST['MonthLookup'])?'': 'WHERE MONTH(pd.PayrollDate)='.$_REQUEST['Month']; //echo $condition;
 
 // if from yrendgovtreports.php
 $perid=(!isset($idno))?'':' WHERE p.IDNo='.$idno;

      $sql0='CREATE TEMPORARY TABLE yrtotals 
      select 
        `p`.`IDNo` AS `IDNo`,  concat(`e`.`FirstName`, \' \', `e`.`SurName`) AS `FullName`, e.RCompanyNo as CompanyNo, `e`.`SurName`,
        ROUND(SUM((RegDayBasic+VLBasic+SLBasic+LWPBasic+RHBasicforDaily)),2) AS `Basic`,
        ROUND(SUM((RegDayDeM+VLDeM+SLDeM+LWPDeM+RHDeMforDaily)),2) AS `DeM`,
        ROUND(SUM((RegDayTaxSh+VLTaxSh+SLTaxSh+LWPTaxSh+RHTaxShforDaily)),2) AS `TaxSh`,
        ROUND(SUM((RegDayOT + RestDayOT + SpecOT + RHOT)),2) AS `OT`,
        ROUND(SUM(AbsenceBasicforMonthly),2) AS `AbsenceBasic`,
        ROUND(SUM(AbsenceDeMforMonthly),2) AS `AbsenceDeM`,
        ROUND(SUM(AbsenceTaxShforMonthly),2) AS `AbsenceTaxSh`,
        ROUND(SUM(UndertimeBasic),2) AS `UndertimeBasic`,
        ROUND(SUM(UndertimeDeM),2) AS `UndertimeDeM`,
        ROUND(SUM(`p`.`UndertimeTaxSh`),2) AS `UndertimeTaxSh`,
        ROUND(SUM(`p`.`SSS-EE`),2) AS `SSS-EE`,
        ROUND(SUM(`p`.`PhilHealth-EE`),2) AS `PhilHealth-EE`,
        ROUND(SUM(`p`.`PagIbig-EE`),2) AS `PagIbig-EE`,
        ROUND(SUM(`p`.`WTax`),2)+(SELECT IFNULL(SUM(`AdjustAmt`),0) FROM `payroll_21paydayadjustments` WHERE `IDNo`=`e`.`IDNo` AND `AdjustTypeNo`=60) AS `WTax`
    from
        `payroll_25payroll` `p`        
        join `1employees` `e` ON ((`p`.`IDNo` = `e`.`IDNo`))
        JOIN `payroll_1paydates` pd ON pd.PayrollID=p.PayrollID '.$condition.$perid.' group by `p`.`IDNo`;';
       // echo $sql0.'<br>';
    $stmt=$link->prepare($sql0);     $stmt->execute();
	
    
    $sql0='CREATE TEMPORARY TABLE yradj
    select IDNo, ifnull(SUM(case when AdjustTypeNo=21 and p.PayrollID=23 then AdjustAmt end),0) as `13th`, ifnull(SUM(case when AdjustTypeNo=25 then AdjustAmt end),0) as `LeaveConversion`
    FROM `payroll_21paydayadjustments` p JOIN `payroll_1paydates` pd ON pd.PayrollID=p.PayrollID '.$condition.$perid.' group by IDNo;';
    $stmt=$link->prepare($sql0);     $stmt->execute();
	// echo $sql0.'<br>';
	
    $sql0='CREATE TEMPORARY TABLE yrgross as
    Select p.*, 
    ROUND(`WTax`,2) as `TaxWithheld`, 
    ROUND((`Basic`+`OT`-`AbsenceBasic`-`UndertimeBasic`-`AbsenceDeM`-UndertimeDeM),2) as TaxSalaries,
    ROUND(`DeM`,2) as NonTaxSalaries,
    `13th`, `LeaveConversion`,
    ROUND((`DeM` +`13th`+`LeaveConversion`),2) as TotalNotTax,
    ROUND((`SSS-EE`+`PhilHealth-EE`+`PagIbig-EE`),2) as TotalGovtDeduct, 
    ((SELECT TaxSalaries)-(SELECT TotalGovtDeduct)) as Taxable from yrtotals p
    left join yradj ya on p.IDNo=ya.IDNo '.$perid;
    $stmt=$link->prepare($sql0);     $stmt->execute();
    
    
    $sql1='SELECT CompanyNo,`CompanyName` FROM 1companies;';
?>