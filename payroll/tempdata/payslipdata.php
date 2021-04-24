<?php
$sql1='CREATE TEMPORARY TABLE `payslips` (
    `PayrollID` tinyint(3) unsigned NOT NULL,
    `IDNo` smallint(6) NOT NULL,
    `BranchNo` smallint(3) NOT NULL,
    `DorSM` tinyint(1) NOT NULL,
    `Basic` double DEFAULT 0,
    `DeM` double DEFAULT 0,
    `TaxSh` double DEFAULT 0,
    `OT` double DEFAULT 0,
    `Remarks` varchar(50) DEFAULT NULL,
    `Absence` double DEFAULT 0,
    `Undertime` double DEFAULT 0,
    `SSS-EE` double DEFAULT 0,
    `PhilHealth-EE` double DEFAULT 0,
    `PagIbig-EE` double DEFAULT 0,
    `WTax` double DEFAULT 0,
    `FullName` varchar(50) NOT NULL,
    `Company` varchar(100)  NULL,
    `FromDate` date NOT NULL,
    `ToDate` date NOT NULL,
    `Branch` varchar(20) NOT NULL,
    `GrossPay` double DEFAULT 0,
    `GovtDeduct` double DEFAULT 0,
    `NetPay` double DEFAULT 0,
    `TxnID` int(11) NOT NULL ,
    UNIQUE KEY `PayrollID_UNIQUE` (`PayrollID`,`IDNo`))
      SELECT p.PayrollID,p.IDNo,p.BranchNo,p.DorSM, 
      (RegDayBasic+VLBasic+SLBasic+LWPBasic+RHBasicforDaily) AS `Basic`,
      (RegDayDeM+VLDeM+SLDeM+LWPDeM+RHDeMforDaily) AS `DeM`,
      (RegDayTaxSh+VLTaxSh+SLTaxSh+LWPTaxSh+RHTaxShforDaily) AS `TaxSh`,
      (RegDayOT + RestDayOT + SpecOT + RHOT) AS `OT`,  p.Remarks,  
      (AbsenceBasicforMonthly + AbsenceDeMforMonthly + AbsenceTaxShforMonthly) AS `Absence`,
      (UndertimeBasic + UndertimeDeM + UndertimeTaxSh) AS `Undertime`,
      p.`SSS-EE`, p.`PhilHealth-EE`, p.`PagIbig-EE`, p.WTax, p.FullName,
      c.`CompanyName` as `Company`,d.FromDate, d.ToDate, b.Branch,
      (SELECT `Basic`) + (SELECT `DeM`) + (SELECT `TaxSh`) + (SELECT `OT`) - (SELECT `Absence`)- (SELECT `Undertime`) AS GrossPay,
      (`SSS-EE`+`PhilHealth-EE`+`PagIbig-EE`+`WTax`) as GovtDeduct, p.NetPay FROM `payroll_25payrolldatalookup` as p 
  JOIN `1employees` e on p.IDNo=e.IDNo 
  LEFT JOIN `1companies` c on e.RCompanyNo=c.CompanyNo
  JOIN `1branches` b on p.BranchNo=b.BranchNo
  JOIN `payroll_1paydates` d on p.PayrollID=d.PayrollID ';


if (allowedToOpen(824,'1rtc') and $paysliptype<>'mypayslip'){
  switch ($paysliptype) {
    case 'all':
      $sql1.=' WHERE p.PayrollID=' . $_POST['payrollid'] ; 
      break;

      default: //per person
      $sql1.=' WHERE p.PayrollID BETWEEN '.$_POST['PayrollIDFrom'].' AND '.$_POST['PayrollIDTo'].' AND p.IDNo='. $_POST['IDNo'];
      break;
  }
  
} else { //My Payslip
 $sql1.=' WHERE p.PayrollID=' . $_REQUEST['payrollid'] .' AND d.Posted=1 AND (datediff(CURDATE(),d.PayrollDate)>=0) AND p.IDNo='.$_SESSION['(ak0)'];
 //IF ($_SESSION['(ak0)']==1002) { echo $sql1;}
}     
 $stmt1=$link->prepare($sql1); $stmt1->execute(); 
