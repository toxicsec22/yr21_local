<?php
// if from Payroll Totals
 $condition=!isset($_REQUEST['MonthLookup'])?'': 'WHERE MONTH(pd.PayrollDate)='.$_REQUEST['Month']; //echo $condition;
 
 // if from yrendgovtreports.php
 $perid=(!isset($idno))?'':' WHERE p.IDNo='.$idno;
 
      $sql0='create temporary table yrtotals 
      select 
        `p`.`IDNo` AS `IDNo`,  concat(`e`.`FirstName`, \' \', `e`.`SurName`) AS `FullName`, e.RCompanyNo as CompanyNo, `e`.`SurName`,
        truncate(sum(`p`.`Basic`),2) AS `Basic`,
        truncate(sum(`p`.`DeM`),2) AS `DeM`,
        truncate(sum(`p`.`TaxSh`),2) AS `TaxSh`,
        truncate(sum(`p`.`OT`),2) AS `OT`,
        truncate(sum(`p`.`AbsenceBasic`),2) AS `AbsenceBasic`,
        truncate(sum(`p`.`AbsenceTaxSh`),2) AS `AbsenceTaxSh`,
        truncate(sum(`p`.`UndertimeBasic`),2) AS `UndertimeBasic`,truncate(sum(`p`.`UndertimeTaxSh`),2) AS `UndertimeTaxSh`,
        truncate(sum(`p`.`SSS-EE`),2) AS `SSS-EE`,
        truncate(sum(`p`.`PhilHealth-EE`),2) AS `PhilHealth-EE`,
        truncate(sum(`p`.`PagIbig-EE`),2) AS `PagIbig-EE`,
        truncate(sum(`p`.`WTax`),2)+(SELECT IFNULL(SUM(`AdjustAmt`),0) FROM `payroll_21paydayadjustments` WHERE `IDNo`=`e`.`IDNo` AND `AdjustTypeNo`=60) AS `WTax`
    from
        `payroll_25payroll` `p`        
        join `1employees` `e` ON ((`p`.`IDNo` = `e`.`IDNo`))
        JOIN `payroll_1paydates` pd ON pd.PayrollID=p.PayrollID '.$condition.$perid.' group by `p`.`IDNo`;';
       // echo $sql0.'<br>';
    $stmt=$link->prepare($sql0);     $stmt->execute();
	
    
    $sql0='create temporary table yradj
    select IDNo, ifnull(sum(case when AdjustTypeNo=21 and p.PayrollID=23 then AdjustAmt end),0) as `13th`, ifnull(sum(case when AdjustTypeNo=25 then AdjustAmt end),0) as `LeaveConversion`
    FROM `payroll_21paydayadjustments` p JOIN `payroll_1paydates` pd ON pd.PayrollID=p.PayrollID '.$condition.$perid.' group by IDNo;';
    $stmt=$link->prepare($sql0);     $stmt->execute();
	// echo $sql0.'<br>';
	
    $sql0='create temporary table yrgross as
    Select p.IDNo, CompanyNo, FullName, SurName, truncate(`Basic`,2) as `Basic`,truncate(`DeM`,2) as `DeM`,truncate(`TaxSh`,2) as `TaxSh`,truncate(`OT`,2) as `OT`,
        truncate(`AbsenceBasic`,2) as `AbsenceBasic`,truncate(`UndertimeBasic`,2) as `UndertimeBasic`,
    truncate(`AbsenceTaxSh`,2) as `AbsenceTaxSh`,truncate(`UndertimeTaxSh`,2) as `UndertimeTaxSh`,
    truncate(`SSS-EE`,2) as `SSS-EE`,truncate(`PhilHealth-EE`,2) as `PhilHealth-EE`,truncate(`PagIbig-EE`,2) as `PagIbig-EE`,truncate(`WTax`,2) as `WTax`,truncate(`WTax`,2) as `TaxWithheld`, 
    truncate((`Basic`+`OT`-`AbsenceBasic`-`UndertimeBasic`),2) as TaxSalaries,
    truncate((`DeM`),2) as NonTaxSalaries,
    `13th`, `LeaveConversion`,
    truncate((`DeM` +`13th`+`LeaveConversion`),2) as TotalNotTax,
    ROUND((`SSS-EE`+`PhilHealth-EE`+`PagIbig-EE`),2) as TotalGovtDeduct, ((`Basic`+`OT`-`AbsenceBasic`-`UndertimeBasic`)-(`SSS-EE`+`PhilHealth-EE`+`PagIbig-EE`))  as Taxable from yrtotals p
    left join yradj ya on p.IDNo=ya.IDNo '.$perid;
    $stmt=$link->prepare($sql0);     $stmt->execute();
	// echo $sql0.'<br>'; exit();
    
    
    $sql1='SELECT CompanyNo,`CompanyName` FROM 1companies;';
?>