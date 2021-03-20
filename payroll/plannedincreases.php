<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(8115,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false; include_once('../switchboard/contents.php');
$w=!isset($_GET['w'])?'List':$_GET['w'];

switch ($w) {
    case 'List':
?>
<div style='background-color: #e6e6e6;
  width: 500px;
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
</ul>
</div>

<?php


$title='Set Annual Increase';   

if (allowedToOpen(1500,'1rtc')) { $cond=''; } else {$cond=' WHERE p.IDNo<>'.$_SESSION['(ak0)'].' AND deptheadpositionid='.$_SESSION['&pos'].' ';}

$table='payroll_61plannedincrease'; $txnid='IDNo'; $txnidname='IDNo'; 
$sql='SELECT p.*, FirstName, SurName, Position AS CurrentPosition, cp.deptid, FORMAT(CurrentRate,0) AS CurrentRate, FORMAT(CalculatedRate,0) AS CalculatedRate FROM payroll_61plannedincrease p JOIN 1employees e ON e.IDNo=p.IDNo JOIN attend_0positions cp ON p.CurrentPosID=cp.PositionID JOIN 1departments d ON d.deptid=cp.deptid '.$cond;
$columnnames=array('CurrentAssignment','IDNo', 'FirstName', 'SurName', 'CurrentPosition', 'CurrentRate','CalculatedRate', 'Remarks', 'LatesMarchToFeb', 'AbsencesMarchToFeb', 'MeritsMarchToFeb', 'DemeritsMarchToFeb','NTEMarchToFeb', 'PerfScoreLastYr', 'DateofLastIncrease'); 

$sql1='SELECT IDNo, FullName FROM attend_30currentpositions p  '.$cond.' ';
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,$sql1,'FullName','IDNo','names');

$formdesc='</i><form method="post" action="plannedincreases.php?w=SetRemarks&action_token='. $_SESSION['action_token'].'" enctype="multipart/form-data">
Put remarks for IDNo &nbsp; 
<input type="text" name="IDNo" list="names" size=7 autocomplete="off" required="true"> &nbsp;
<input type="text" name="Remarks" size=20 autocomplete="off"> &nbsp;
<input type="submit" value="Set Remarks"><br><br>
<i>';
	
include('../backendphp/layout/displayastable.php');
break;

case 'SetRemarks':
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $sql='UPDATE payroll_61plannedincrease SET Remarks=\''.$_POST['Remarks'].'\' WHERE IDNo=\''.$_POST['IDNo'].'\'';
    echo $sql;
    $stmt=$link->prepare($sql); $stmt->execute();
    header('Location: plannedincreases.php');
    break;
}

?>
