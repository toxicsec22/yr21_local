<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;
include_once('../switchboard/contents.php');

if (!allowedToOpen(8176,'1rtc')) { header('Location:../index.php?denied=true'); }
  
$title='Payroll Errors';

include('payrolllayout/addentryhead.php');
include('payrolllayout/setpayidsession.php');

?><br>

<form method='post' action='#' enctype='multipart/form-data'>
    Payroll ID<input type='text' name='payrollid' value='<?php echo (isset($_SESSION['payrollidses'])?$_SESSION['payrollidses']:((date('m')*2)+(date('d')<15?-1:0)));?>' list='payperiods' autocomplete='off' size=5>&nbsp;
<input type='submit' name='submit' value='Lookup' >
      <br><br><hr><br><br>
<?php
$payrollid=$_SESSION['payrollidses'];
 // no atm
$subtitle='No ATM';
$sql='SELECT *, IF(Resigned<>0,"Resigned","") AS `Resigned?` FROM 1employees WHERE (ISNULL(UBPATM) OR UBPATM=0) AND Resigned=0 AND IDNo IN (SELECT IDNo FROM payroll_25payroll WHERE PayrollID='.$payrollid.')';
$columnnames=array('IDNo','FirstName','SurName','UBPATM','Resigned?');
$width='50%';
include('../backendphp/layout/displayastablenosort.php'); 
echo '<br><hr><br>';
// sss basis, show only if PayrollID%2=0
// echo $payrollid; exit();
if ($payrollid%2==0){
$subtitle='Different Actual SSS from Calculated';
$sql0='CREATE TEMPORARY TABLE sssbasis AS
SELECT e.IDNo, FirstName, SurName, IF(e.Resigned<>0,"Resigned","") AS `Resigned?`, SUM(Basic+OT-UndertimeBasic-AbsenceBasic)+(SELECT IFNULL(SUM(Basic+OT-UndertimeBasic-AbsenceBasic),0) FROM `payroll_25payroll` pp WHERE pp.IDNo=p.IDNo AND pp.PayrollID='.($payrollid-1).') AS Basis FROM `payroll_25payroll` p '
        . ' JOIN 1employees e ON e.IDNo=p.IDNo'
        . '  WHERE PayrollID='.$payrollid.' GROUP BY e.IDNo';
$stmt0=$link->prepare($sql0); $stmt0->execute();

$sql='SELECT s.*, `SSS-EE` AS `PayrollSSS-EE`, getContriEE(Basis,"sss") AS CalculatedSSS FROM sssbasis s JOIN payroll_25payroll p ON p.IDNo=s.IDNo WHERE p.PayrollID='.$payrollid.' HAVING (`SSS-EE`-CalculatedSSS)<>0';
$columnnames=array('IDNo','FirstName','SurName','Resigned?','PayrollSSS-EE','CalculatedSSS');
include('../backendphp/layout/displayastableonlynoheaders.php'); 
echo '<br><hr><br>';

$subtitle='Different Philhealth from Latest Rate (may differ after salary increases)';
$sql='SELECT s.*, p.`Philhealth-EE` AS `PayrollPHIC-EE`, r.`Philhealth-EE` AS RecordedRate FROM sssbasis s JOIN payroll_25payroll p ON p.IDNo=s.IDNo JOIN payroll_20latestrates r ON r.IDNo=s.IDNo WHERE p.PayrollID='.$payrollid.' HAVING (p.`Philhealth-EE`-r.`Philhealth-EE`)<>0';
$columnnames=array('IDNo','FirstName','SurName','Resigned?','PayrollPHIC-EE','RecordedRate');
include('../backendphp/layout/displayastableonlynoheaders.php'); 
echo '<br><hr><br>';

$subtitle='Different PagIbig from Usual Rate';
$sql='SELECT s.*, `PagIbig-EE` AS `PayrollPagIbig-EE`, 100 AS StandardRate FROM sssbasis s JOIN payroll_25payroll p ON p.IDNo=s.IDNo WHERE p.PayrollID='.$payrollid.' HAVING (`PagIbig-EE`)<>100';
$columnnames=array('IDNo','FirstName','SurName','Resigned?','PayrollPagIbig-EE','StandardRate');
include('../backendphp/layout/displayastableonlynoheaders.php'); 
echo '<br><hr><br>';
}
$subtitle='Different Attendance vs Basis for Payroll';
$cols=array('RegDaysPresent', 'LWOPDays', 'LegalDays', 'SpecDays', 'SLDays','VLDays', 'RestDays', 'LWPDays', 'QDays', 'RegDaysActual', 'LegalHrsOT', 'SpecHrsOT', 'RestHrsOT', 'PaidLegalDays', 'RegOTHrs');
$sql=''; $sqlhaving='';
$columnnames=array('IDNo','FirstName','SurName','Resigned?','Remarks');
foreach ($cols as $col){
    $sql.=', a.'.$col.'-IFNULL(pa.'.$col.',0) AS Diff'.$col.' ';
    $sqlhaving.=' (ABS(ROUND(Diff'.$col.',1))<>0) '.((end($cols)==$col)?'':' OR ');
    $columnnames[]='Diff'.$col;
}
include_once '../attendance/attendsql/attendsumforpayroll.php';
$sql='SELECT a.IDNo, Nickname, FirstName, SurName, IF(e.Resigned<>0,"Resigned","") AS `Resigned?`, p.Remarks '.$sql.' FROM `attend_44sumforpayroll` a 
JOIN payroll_20fromattendance pa ON a.IDNo=pa.IDNo AND a.PayrollID=pa.PayrollID
LEFT JOIN payroll_25payroll p ON a.IDNo=p.IDNo AND a.PayrollID=p.PayrollID
JOIN `1employees` e ON `a`.IDNo=`e`.IDNo WHERE a.PayrollID='.$payrollid.' HAVING '.$sqlhaving.'';

include('../backendphp/layout/displayastableonlynoheaders.php'); 
echo '<br><hr><br>';

$subtitle='Adjusted Attendance in Future Payrolls';

$columnnames=array('IDNo','FirstName','SurName','Resigned?','Remarks','RegDaysActual', 'SpecDays', 'LWPDays', 'LegalHrsOT', 'SpecHrsOT', 'RestHrsOT', 'PaidLegalDays', 'RegOTHrs');
$sql='SELECT a.*, Nickname, FirstName, SurName, IF(Resigned<>0,"Resigned","") AS `Resigned?`  FROM `payroll_50adjattendance` a 
JOIN `1employees` e ON `a`.IDNo=`e`.IDNo WHERE a.LackInPayrollID='.$payrollid;

include('../backendphp/layout/displayastableonlynoheaders.php'); 
echo '<br><hr><br>';

$subtitle='Earning Less Than MinWage (Provincial rates with 10% premium)';

 $sql0='CREATE TEMPORARY TABLE currentrates AS SELECT MAX(DateEffective) AS MaxDate, MinWageAreaID FROM 1_gamit.payroll_4wageorders GROUP BY MinWageAreaID;';
    $stmt0=$link->prepare($sql0); $stmt0->execute();

$sql1='Create temporary table total as select CONCAT(e.FirstName,\' \',e.SurName) as FullName,CONCAT(e1.Nickname) as EncodedBy,wo.TimeStamp,Branch,dam.IDNo,IFNULL(LatestDateofChange,"") AS LatestDateofChange, TRUNCATE(IF(b.EffectiveMinWageAreaID=1,TotalMinWage,(TotalMinWage*1.1)),2) as TotalMinWageShouldBe,BasicDaily,IF(b.EffectiveMinWageAreaID=1,\'1\',\'0\') AS Col from payroll_21dailyandmonthly dam LEFT JOIN 1branches b on b.BranchNo=dam.BranchNo LEFT JOIN 1_gamit.payroll_4wageorders wo on wo.MinWageAreaID=b.EffectiveMinWageAreaID JOIN currentrates cr ON cr.MaxDate=wo.DateEffective AND cr.MinWageAreaID=wo.MinWageAreaID left join 1employees e on e.IDNo=dam.IDNo left join 1employees e1 on e1.IDNo=wo.EncodedByNo
HAVING TotalMinWageShouldBe>BasicDaily AND BasicDaily<(select TotalMinWage from 1_gamit.payroll_4wageorders  where DateEffective=(select max(DateEffective) from 1_gamit.payroll_4wageorders where MinWageAreaID=1));';
$stmt1=$link->prepare($sql1); $stmt1->execute();
// echo $sql1; exit();

$sql='select t.*,BasicDaily as CurrentBasicDaily from total t Order By FullName';

$columnnames=array('LatestDateofChange','IDNo','FullName','Branch','CurrentBasicDaily','TotalMinWageShouldBe','EncodedBy','TimeStamp');
include('../backendphp/layout/displayastableonlynoheaders.php'); 
echo '<br><hr><br>';

echo 'END OF PAGE';

