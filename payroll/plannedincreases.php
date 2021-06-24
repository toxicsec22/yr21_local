<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(100,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false; include_once('../switchboard/contents.php');
$w=!isset($_GET['w'])?'List':$_GET['w'];
$deadline=(allowedToOpen(1500,'1rtc'))?date('Y-m-d H:i', strtotime($currentyr.'-03-31 12:00')):date('Y-m-d H:i',strtotime($currentyr.'-03-27 13:00'));
if (allowedToOpen(1500,'1rtc')) { $cond='WHERE 1=1 '; } else {$cond=' WHERE pi.IDNo<>'.$_SESSION['(ak0)'].' AND deptheadpositionid='.$_SESSION['&pos'].' ';}

switch ($w) {
    case 'List':
?><div style='width: 100%; '>
<div style='background-color: #e6e6e6;
  width: 40%; float: left; 
  border: 2px solid grey;
  padding: 25px;
  margin: 25px;'>
  <style>
  table, tr, td { border-collapse: collapse; border: 1px black solid; padding: 8px;} 
  td {text-align: center;}
  </style>
  <h4>Who are covered? </h4>All employees regularized by Feb 28.<br><br>
<h4>Calculated Rate: </h4>
<table>
<tr><td>If performance score is</td><td>increase is </td></tr>
<tr><td><= 70% <br><br>or latest date of increase is after Sep 20 last year</td><td>no increase</td></tr>
<tr><td>>70% to 85%</td><td>3%</td></tr>
<tr><td>>85% to 90%</td><td>5%</td></tr>
<tr><td>>90%</td><td>7.5%</td></tr>
</table> <br><br>
<h4>Notes </h4>
<ul><li>Calculations are based only on the performance evaluation last Dec.</li><br>
<li>Lates, unauthorized absences, merits/demerits, etc are counted from March 1 last year to Feb 28 this year.</li><br>
<li>Effectivity of the new rate is March 21, in time for April 10 payroll.</li>
</ul><br><br>
<h4>Instructions </h4>
If you have a different recommended rate versus the calculated rate, put the value and remarks for your rationale.<br>
If you agree with the calculated rate, you may leave the recommended rate as blank.
</div>
<?php
if(allowedToOpen(1500,'1rtc')){
  include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
  $sql1='SELECT IF(cp.deptid IN (2,10),BranchNo, cp.deptid+800) AS deptid, BranchorDept FROM payroll_61plannedincrease p JOIN attend_30currentpositions cp ON p.IDNo=cp.IDNo 
  WHERE p.IDNo NOT IN (SELECT IDNo FROM payroll_22rates WHERE DateofChange="'.$currentyr.'-03-21" AND EncodedByNo='.$_SESSION['(ak0)'].')
  GROUP BY BranchorDept;';
  echo comboBox($link,$sql1,'BranchorDept','deptid','depts');
  
?>
<div style='background-color: lightyellow; float: right;
  width: 40%; 
  border: 2px solid grey;
  padding: 25px;' >
  
  <form method=post action='plannedincreases.php?w=Setup'><input type='submit' name='Setup' value='Setup Data'> Click to enter relevant data into table payroll_61plannedincrease.
  <input type="hidden" name="action_token" value="<?php echo $_SESSION['action_token']; ?>">
  </form><br><br>
  <form method=post action='plannedincreases.php?w=PerDept' target=_blank>
  Choose department: <input type='text' size=10 name='Dept' list='depts'>
  <input type="hidden" name="action_token" value="<?php echo $_SESSION['action_token']; ?>">
  <input type='submit' name='PerDept' value='Preview Increases'> 
  </form>

  </div>
<?php
}
?>
</div>
<br><br>
<?php
$title='Annual Increase';   



$table='payroll_61plannedincrease'; $txnidname='IDNo'; 
$sql='SELECT pi.*, FullName, Position AS CurrentPosition, cp.deptid, FORMAT(CurrentRate,0) AS CurrentRate, FORMAT(CalculatedRate,0) AS CalculatedRate, FORMAT(RecommendedRate,0) AS RecommendedRate FROM payroll_61plannedincrease pi JOIN attend_30currentpositions cp ON pi.IDNo=cp.IDNo '.$cond;
$columnnames=array('CurrentAssignment','IDNo', 'FullName', 'CurrentPosition', 'CurrentRate','CalculatedRate','RecommendedRate', 'Remarks', 'LatesMarchToFeb', 'AbsencesMarchToFeb', 'MeritsMarchToFeb', 'DemeritsMarchToFeb','NTEMarchToFeb', 'PerfScoreLastYr', 'DateofLastIncrease'); 

$sql1='SELECT IDNo, FullName FROM attend_30currentpositions pi  '.$cond.' ';
if(date('Y-m-d H:i')<=$deadline){
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,$sql1,'FullName','IDNo','names');

$formdesc='</i><form method="post" action="plannedincreases.php?w=SetRemarks&action_token='. $_SESSION['action_token'].'" enctype="multipart/form-data">
Put remarks for IDNo &nbsp; 
<input type="text" name="IDNo" list="names" size=7 autocomplete="off" required="true"> &nbsp; &nbsp;
Recommended rate <input type="text" name="RecommendedRate" size=20 autocomplete="off"> &nbsp; &nbsp;
Remarks <input type="text" name="Remarks" size=20 autocomplete="off"> &nbsp;
<input type="submit" value="Submit"></form> &nbsp; &nbsp;  &nbsp; &nbsp; 
<form method="post" action="plannedincreases.php?w=Reset&action_token='. $_SESSION['action_token'].'" enctype="multipart/form-data">
Reset IDNo &nbsp; 
<input type="text" name="IDNo" list="names" size=7 autocomplete="off" required="true"> &nbsp; &nbsp;
<input type="submit" value="Reset"></form><br><br>
<i>';
}	else {
  $formdesc='</i><form method=post action="plannedincreases.php?w=FinalList"><input type="submit" name="Final" value="Check Final Approved Increases" target=_blank> 
<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
</form><br><br>
<i>';
}
echo'<div style="clear: both; display: block; position: relative;">';
include('../backendphp/layout/displayastable.php');
echo'</div>';
break;

case 'SetRemarks':
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    if(date('Y-m-d H:i')<=$deadline){
    $sql='UPDATE payroll_61plannedincrease SET RecommendedRate=\''.(!is_numeric($_POST['RecommendedRate'])?str_replace(',','',$_POST['RecommendedRate']):$_POST['RecommendedRate']).'\', Remarks=\''.$_POST['Remarks'].'\', EncodedByNo='.$_SESSION['(ak0)'].', TS=Now() WHERE IDNo=\''.$_POST['IDNo'].'\'';
    //echo $sql;
    $stmt=$link->prepare($sql); $stmt->execute();
    }
    header('Location: plannedincreases.php');
    break;

case 'Reset':
      require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
      if(date('Y-m-d H:i')<=$deadline){
      $sql='UPDATE payroll_61plannedincrease SET RecommendedRate=NULL, Remarks=NULL, EncodedByNo='.$_SESSION['(ak0)'].', TS=Now() WHERE IDNo=\''.$_POST['IDNo'].'\'';
      $stmt=$link->prepare($sql); $stmt->execute();
      }
      header('Location: plannedincreases.php');
      break;

case 'Setup':
  if (!allowedToOpen(1500,'1rtc')) {echo 'No permission.'; exit();}
  require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
  $sql='INSERT INTO `payroll_61plannedincrease`(IDNo, PerfScoreLastYr, EncodedByNo, TS)
SELECT e.IDNo, Evaluation, '.$_SESSION['(ak0)'].', Now() FROM 1employees e JOIN `'.$lastyr.'_1rtc`.`payroll_21plannedbonuses` pb ON e.IDNo=pb.IDNo 
WHERE e.Resigned=0 AND DATEDIFF(\''.$currentyr.'-02-28\',DateHired)>180;';
$stmt=$link->prepare($sql); $stmt->execute();

$sql='UPDATE `payroll_61plannedincrease` p SET LatesUndertimeMarchToFeb=
IFNULL((SELECT SUM(LatesPerMonth) FROM '.$currentyr.'_1rtc.attend_62latescount l WHERE ForMonth<=2 AND l.IDNo=p.IDNo),0)
+IFNULL((SELECT SUM(LatesPerMonth) FROM '.$lastyr.'_1rtc.attend_62latescount l WHERE ForMonth>2 AND l.IDNo=p.IDNo),0)
+IFNULL((SELECT SUM(UndertimeCount) FROM '.$currentyr.'_1rtc.attend_62undertime l WHERE `Month`<=2 AND l.IDNo=p.IDNo),0)
+IFNULL((SELECT SUM(UndertimeCount) FROM '.$lastyr.'_1rtc.attend_62undertime l WHERE `Month`>2 AND l.IDNo=p.IDNo),0);';
$stmt=$link->prepare($sql); $stmt->execute();


$sql='UPDATE `payroll_61plannedincrease` p SET AbsencesMarchToFeb=
IFNULL((SELECT SUM(AbsencesPerMonth) FROM '.$currentyr.'_1rtc.attend_62absences l WHERE MonthNum<=2 AND LeaveNo IN (10,18,19,30) AND l.IDNo=p.IDNo),0)
+IFNULL((SELECT SUM(AbsencesPerMonth) FROM '.$lastyr.'_1rtc.attend_62absences l WHERE MonthNum>2 AND LeaveNo IN (10,17,18,19,30) AND l.IDNo=p.IDNo),0);';
$stmt=$link->prepare($sql); $stmt->execute();

$sql='UPDATE `payroll_61plannedincrease` p SET MeritsMarchToFeb=
IFNULL((SELECT SUM(WeightInPoints) FROM '.$currentyr.'_1rtc.hr_72scores s JOIN '.$currentyr.'_1rtc.hr_71scorestmt ss ON s.SSID=ss.SSID JOIN '.$currentyr.'_1rtc.hr_70points p ON ss.PointID=p.PointID WHERE DecisionStatus=3 AND MONTH(DateofIncident)<=2 AND s.ReporteeNo=p.IDNo),0)
+IFNULL((SELECT SUM(WeightInPoints) FROM '.$lastyr.'_1rtc.hr_72scores s JOIN '.$lastyr.'_1rtc.hr_71scorestmt ss ON s.SSID=ss.SSID JOIN '.$lastyr.'_1rtc.hr_70points p ON ss.PointID=p.PointID WHERE DecisionStatus=3 AND MONTH(DateofIncident)>2 AND s.ReporteeNo=p.IDNo),0);';
$stmt=$link->prepare($sql); $stmt->execute();

$sql='UPDATE `payroll_61plannedincrease` p SET DemeritsMarchToFeb=
IFNULL((SELECT SUM(WeightInPoints) AS MeritPoints FROM '.$currentyr.'_1rtc.hr_72scores s JOIN '.$currentyr.'_1rtc.hr_71scorestmt ss ON s.SSID=ss.SSID JOIN '.$currentyr.'_1rtc.hr_70points p ON ss.PointID=p.PointID WHERE DecisionStatus=1 AND MONTH(DateofIncident)<=2 AND s.ReporteeNo=p.IDNo),0)
+IFNULL((SELECT SUM(WeightInPoints) AS MeritPoints FROM '.$lastyr.'_1rtc.hr_72scores s JOIN '.$lastyr.'_1rtc.hr_71scorestmt ss ON s.SSID=ss.SSID JOIN '.$lastyr.'_1rtc.hr_70points p ON ss.PointID=p.PointID WHERE DecisionStatus=1 AND MONTH(DateofIncident)>2 AND s.ReporteeNo=p.IDNo),0);';
$stmt=$link->prepare($sql); $stmt->execute();

$sql='UPDATE `payroll_61plannedincrease` p SET DateofLastIncrease=
(SELECT  MIN(DateofChange) FROM payroll_22rates WHERE IDNo=p.IDNo AND (BasicRate+DeMinimisRate+TaxShield)=(SELECT MAX(IF(LatestDorM=1,TotalMonthly,TotalDaily)) FROM payroll_21dailyandmonthly r WHERE r.IDNo=p.IDNo GROUP BY r.IDNo));';
$stmt=$link->prepare($sql); $stmt->execute();

$sql='UPDATE `payroll_61plannedincrease` p SET CurrentRate=
(SELECT TRUNCATE(MAX(BasicRate+DeMinimisRate+TaxShield)*IF(DailyORMonthly=1,1,26.08),0) FROM payroll_22rates r WHERE r.DateofChange=p.DateofLastIncrease AND r.IDNo=p.IDNo GROUP BY r.IDNo);';
$stmt=$link->prepare($sql); $stmt->execute();

$sql='UPDATE `payroll_61plannedincrease` p SET CurrentPosID=
(SELECT PositionID FROM attend_30currentpositions cp WHERE p.IDNo=cp.IDNo);';
$stmt=$link->prepare($sql); $stmt->execute();

$sql='UPDATE `payroll_61plannedincrease` p SET CurrentAssignment=
(SELECT IF(deptid in (2,10),`Branch`,`Department`) FROM attend_30currentpositions cp WHERE p.IDNo=cp.IDNo);';
$stmt=$link->prepare($sql); $stmt->execute();

$sql='UPDATE `payroll_61plannedincrease` p SET CalculatedRate=0;';  
$stmt=$link->prepare($sql); $stmt->execute();
$sql='UPDATE `payroll_61plannedincrease` p SET CalculatedRate=CurrentRate WHERE PerfScoreLastYr<=70 AND DateofLastIncrease>\''.$lastyr.'-09-21\';';  
$stmt=$link->prepare($sql); $stmt->execute();
$sql='UPDATE `payroll_61plannedincrease` p SET CalculatedRate=CurrentRate*1.03 WHERE PerfScoreLastYr>70 AND PerfScoreLastYr<=85 AND DateofLastIncrease<=\''.$lastyr.'-09-21\';';  
$stmt=$link->prepare($sql); $stmt->execute();
$sql='UPDATE `payroll_61plannedincrease` p SET CalculatedRate=CurrentRate*1.05 WHERE PerfScoreLastYr>85 AND PerfScoreLastYr<=90 AND DateofLastIncrease<=\''.$lastyr.'-09-21\';';  
$stmt=$link->prepare($sql); $stmt->execute();
$sql='UPDATE `payroll_61plannedincrease` p SET CalculatedRate=CurrentRate*1.075 WHERE PerfScoreLastYr>90 AND DateofLastIncrease<=\''.$lastyr.'-09-21\';';  
$stmt=$link->prepare($sql); $stmt->execute();

  break;

case 'PerDept':
  require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
  $title='Per Department';
  $dept=$_REQUEST['Dept'];
  $sql='CREATE TEMPORARY TABLE `increases` AS SELECT m.IDNo, m.Nickname, m.SurName, p.Position, IFNULL(LatestDateofChange,"") AS LatestDateofChange, `How Long` AS Tenure,  IF(m.deptid IN (10,2),Branch,Dept) AS CurrentBranch, FORMAT(IF(LatestDorM=1,pi.CurrentRate,pi.CurrentRate/26.08),2) AS OldBasic,  IF(DeMMonthly<>0,"With DeM","") AS DeM, IF(TaxShieldDaily<>0,"With TaxShield","") AS TaxShield, FORMAT(pi.CurrentRate,2) AS OldMonthlyTotal, jl.Minimum, jl.Median, jl.Maximum, FORMAT(CalculatedRate,2) AS CalculatedRate,RecommendedRate, ROUND(IF(ISNULL(RecommendedRate) OR RecommendedRate=0,CalculatedRate,RecommendedRate),2) AS FinalRate, ROUND(IF(PreferredRateType=1,(SELECT FinalRate),(SELECT FinalRate)/26.08),2) AS BasicRate, getContriEE((SELECT FinalRate),"sss") AS NewSSS, ROUND(getContriEE((SELECT FinalRate),"phic"),2) AS NewPHIC, ROUND(TAXDUE(((SELECT FinalRate)-`SSS-EE`-`PhilHealth-EE`-`PagIbig-EE`)*12)/12,2) AS ApproxTax, pi.Remarks FROM payroll_21dailyandmonthly as m join `attend_1positions` p on p.PositionID=m.PositionID  
  JOIN `1branches` b ON b.BranchNo=m.BranchNo JOIN `1departments` d ON d.deptid=m.deptid
  JOIN payroll_61plannedincrease pi ON pi.IDNo=m.IDNo
  JOIN (SELECT JobLevelID, jc.JobLevelID, FORMAT(MinRate,0) AS `MINIMUM`, FORMAT(MinRate*(1+PercentMintoMed/100),-1) AS MEDIAN, FORMAT(MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100),-1) AS MAXIMUM  FROM attend_0joblevels jl GROUP BY jl.JobLevelID ORDER BY JobLevelID,JobLevelID) jl ON jl.JobLevelID=p.JobLevelID
   WHERE '.($dept>800?'m.deptid='.($dept-800):'m.BranchNo='.$dept);
  $stmt=$link->prepare($sql); $stmt->execute();
  $sql='SELECT * FROM increases';
  $columnnames=array('IDNo', 'Nickname', 'SurName', 'Position', 'LatestDateofChange', 'Tenure', 'CurrentBranch', 'OldBasic', 'DeM', 'TaxShield', 'OldMonthlyTotal',  'Minimum', 'Median', 'Maximum', 'CalculatedRate', 'RecommendedRate', 'FinalRate','Remarks', 'BasicRate', 'NewSSS', 'NewPHIC','ApproxTax');
  $columnstoedit=array('FinalRate');
  $txnidname='IDNo';
  $addlprocesslabel2='Approve Final Rate';  $addlprocess2='plannedincreases.php?w=Approve&Dept='.$dept.'&IDNo=';
  $editprocesslabel='Add New Rate';  $editprocess='../payroll/addentry.php?w=Rates&IDNo=';
  echo'<div style="clear: both; display: block; position: relative;">';
include('../backendphp/layout/displayastablenosort.php');
echo'</div>';

    break;

case 'Approve':
  require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
  
  $sql1='SELECT pi.IDNo, "'.$currentyr.'-03-21" AS DateofChange, ROUND(IF(ISNULL(RecommendedRate) OR RecommendedRate=0,CalculatedRate,RecommendedRate),2) AS FinalRate, ROUND(IF(PreferredRateType=1,(SELECT FinalRate),(SELECT FinalRate)/26.08),2) AS BasicRate, getContriEE((SELECT FinalRate),"sss") AS `SSS-EE`, ROUND(getContriEE((SELECT FinalRate),"phic"),2) AS `Philhealth-EE`,`PagIbig-EE`, ROUND(TAXDUE(((SELECT FinalRate)-`SSS-EE`-`PhilHealth-EE`-`PagIbig-EE`)*12)/12,2) AS WTax, PreferredRateType AS DailyORMonthly, "salary increase" AS Remarks FROM payroll_21dailyandmonthly as m 
  JOIN attend_1positions p ON p.PositionID=m.PositionID
   JOIN payroll_61plannedincrease pi ON pi.IDNo=m.IDNo WHERE pi.IDNo='.$_REQUEST['IDNo'];
   $stmt=$link->query($sql1); $res=$stmt->fetch();
  $columnstoadd=array('IDNo', 'DateofChange', 'BasicRate', 'SSS-EE', 'Philhealth-EE', 'PagIbig-EE', 'WTax', 'Remarks', 'DailyORMonthly');
 $sql='';
foreach ($columnstoadd as $field) {
$sql.=' `' . $field. '`=\''.$res[$field].'\', '; 
}
$sql='INSERT INTO `payroll_22rates` SET '.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now(), ApprovedByNo=\''.$_SESSION['(ak0)'].'\', ApprovalTS=Now();'; 
//echo $sql;
  $stmt=$link->prepare($sql); $stmt->execute();
  header('Location: plannedincreases.php?w=PerDept&Dept='.$_REQUEST['Dept']);
  break;

  case 'FinalList':
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $title='Final Approved Increases';
    $subtitle='WITH Increases';
    $sql='SELECT pi.IDNo, CONCAT(e.Nickname," ",FirstName," ", SurName) AS Name, p.Position, CurrentAssignment, CurrentRate, FORMAT(pi.CurrentRate,2) AS OldMonthlyTotal, PerfScoreLastYr, FORMAT(CalculatedRate,2) AS CalculatedRate,RecommendedRate, ROUND(IF(ISNULL(RecommendedRate) OR RecommendedRate=0,CalculatedRate,RecommendedRate),2) AS FinalReco, IF(DailyORMonthly=1,(BasicRate+DeMinimisRate+TaxShield),(BasicRate+DeMinimisRate+TaxShield)*26.08) AS FinalApprovedValue, FORMAT((SELECT FinalApprovedValue),2) AS FinalApproved, pi.Remarks, p.deptid, deptheadpositionid FROM payroll_61plannedincrease pi JOIN 1employees e ON e.IDNo=pi.IDNo LEFT JOIN `attend_1positions` p on p.PositionID=pi.CurrentPosID LEFT JOIN 1departments d ON p.deptid=d.deptid 
   JOIN payroll_22rates r ON pi.IDNo=r.IDNo AND r.DateofChange="'.$currentyr.'-03-21" '.$cond; 
    $columnnames=array('IDNo','Name','Position','CurrentAssignment','FinalApproved','Remarks','OldMonthlyTotal','CalculatedRate', 'RecommendedRate');
    echo'<div style="clear: both; display: block; position: relative;">';
     include('../backendphp/layout/displayastable.php');
     echo'</div>'; 
     $title='';
    $subtitle='No change';
    $sql='SELECT pi.IDNo, CONCAT(e.Nickname," ",FirstName," ", SurName) AS Name, p.Position,  CurrentAssignment, CurrentRate, FORMAT(pi.CurrentRate,2) AS OldMonthlyTotal, PerfScoreLastYr, FORMAT(CalculatedRate,2) AS CalculatedRate,RecommendedRate, ROUND(IF(ISNULL(RecommendedRate) OR RecommendedRate=0,CalculatedRate,RecommendedRate),2) AS FinalReco, pi.Remarks, p.deptid, deptheadpositionid FROM payroll_61plannedincrease pi JOIN 1employees e ON e.IDNo=pi.IDNo LEFT JOIN `attend_1positions` p on p.PositionID=pi.CurrentPosID LEFT JOIN 1departments d ON p.deptid=d.deptid '.$cond.' AND pi.IDNo NOT IN (SELECT IDNo FROM payroll_22rates WHERE DateofChange="'.$currentyr.'-03-21");'; 
    $columnnames=array('IDNo','Name','Position','CurrentAssignment','OldMonthlyTotal','Remarks');
    $width='85%';
     include('../backendphp/layout/displayastable.php');
     
    break;

  }

?>
