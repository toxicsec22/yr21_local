<?php
$sql='CREATE TEMPORARY TABLE sssbasis AS '
        . 'SELECT EncodedByNo,TimeStamp,IDNo, SUM(RegDayBasic+VLBasic+SLBasic+LWPBasic+RHBasicforDaily+RegDayOT+RestDayOT+SpecOT+RHOT-AbsenceBasicforMonthly-UndertimeBasic)+(SELECT IFNULL(SUM(RegDayBasic+VLBasic+SLBasic+LWPBasic+RHBasicforDaily+RegDayOT+RestDayOT+SpecOT+RHOT-AbsenceBasicforMonthly-UndertimeBasic),0) FROM `payroll_25payroll` pp WHERE pp.IDNo=p.IDNo AND pp.PayrollID='.($payrollid-1) . ') AS Basis FROM `payroll_25payroll'.(isset($temp)?$temp:'').'` p WHERE PayrollID='.$payrollid .(isset($perid)?$perid:''). ' GROUP BY IDNo;';


$stmt=$link->prepare($sql); $stmt->execute(); 