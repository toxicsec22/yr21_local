<?php
$sql='CREATE TEMPORARY TABLE sssbasis AS '
        . 'SELECT EncodedByNo,TimeStamp,IDNo, SUM(RegDayBasic+VLBasic+SLBasic+LWPBasic+RHBasicforDaily+RegDayOT+RestDayOT+SpecOT+RHOT-AbsenceBasicforMonthly-UndertimeBasic)+(SELECT IFNULL(SUM(RegDayBasic+VLBasic+SLBasic+LWPBasic+RHBasicforDaily+RegDayOT+RestDayOT+SpecOT+RHOT-AbsenceBasicforMonthly-UndertimeBasic),0) FROM `payroll_25payroll` pp WHERE pp.IDNo=p.IDNo AND pp.PayrollID='.($payrollid-1) . ') AS Basis FROM `payroll_25payroll'.$temp.'` p WHERE PayrollID='.$payrollid . ' GROUP BY IDNo;';

// originally
// $sql='CREATE TEMPORARY TABLE sssbasis AS '
//         . 'SELECT EncodedByNo,TimeStamp,IDNo, SUM(Basic+OT-UndertimeBasic-AbsenceBasic)+(SELECT IFNULL(SUM(Basic+OT-UndertimeBasic-AbsenceBasic),0) FROM `payroll_25payroll` pp WHERE pp.IDNo=p.IDNo AND pp.PayrollID='.($payrollid-1) . ') AS Basis FROM `payroll_25payroll'.$temp.'` p WHERE PayrollID='.$payrollid . ' GROUP BY IDNo;';
$stmt=$link->prepare($sql); $stmt->execute(); 