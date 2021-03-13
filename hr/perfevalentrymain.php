<title><?php echo $title;?></title>
<center><h3><?php echo $title;?></h3></center><br><br>
<style>
    body {font-family:sans-serif;}
    #scores { font-family: sans-serif; align-content: center; font-size:14pt; font-weight: 400;}
    #values { font-family: sans-serif; align-content: center; font-size:14pt; font-weight: 400; color: blue;}
    #textboxid { height:30px; width:60px; font-size:14pt; text-align: center; }
    #wrap {  width:80%; margin: 0 20 0 20;}
    #left {   float:left;   width:55%; overflow: auto;}
    #right {   float:right;   width:45%; overflow: auto;}
    table,th,td {border:1px solid black;border-collapse:collapse; padding: 5px !important; font-size: small !important; overflow:auto;}
    #legendcell { font-size: 8pt;}
    #main {  width:100%;  margin:0 auto; clear: both;}
    #comments { width:55%; height:auto; font-size: 11pt; color:green; padding: 2px; display: inline;}
    #headings { font-size: 12pt; font-weight: bold; display: inline;}
	
</style>
<?php
$txnid=intval($_REQUEST['TxnID']);  
$sql='SELECT pf.*, e.Nickname, e.FirstName, LEFT(e.MiddleName,1) AS MI, e.SurName, DATE_FORMAT(EvalDueDate,\'%Y %M %d\') AS EvalDue, '
        . ' DATE_FORMAT(e.DateHired,\'%Y %M %d\') AS DateHired, Position, Branch, Department, '
        . ' CONCAT(e1.Nickname, " ",e1.SurName) AS EvaluatedBy, FORMAT((TO_DAYS(EvalDueDate) - TO_DAYS(`e`.`DateHired`)) / 365,2) AS `InYears(as of EvalDueDate)`,'
        . ' IF(EvalAfterDays=YEAR(EvalDueDate),"Annual Evaluation",CONCAT("Evaluation after ",FORMAT(EvalAfterDays/30,0)," months")) AS Reason '
        . ' FROM `hr_2perfevalmain` pf '
        . ' JOIN `1employees` e ON pf.IDNo=e.IDNo '
        . ' JOIN attend_0positions p ON p.PositionID=pf.CurrentPositionID JOIN `1branches` b ON b.BranchNo=pf.CurrentBranchNo '
        . ' JOIN `1departments` d ON d.deptid=p.deptid LEFT JOIN `1employees` e1 ON pf.SupervisorIDNo=e1.IDNo WHERE pf.TxnID='.$txnid.$printcondition;
$stmt=$link->query($sql); $result=$stmt->fetch(); 
// echo $sql;
$sql0='SELECT DATE_FORMAT(MAX(SuperCompletedTS),\'%Y %M %d\') AS LastEval FROM `hr_2perfevalmain` WHERE IDNo='.$result['IDNo'];  $stmt0=$link->query($sql0); $result0=$stmt0->fetch();
$startofform=''; $endofform=''; 
?>
<div id="hidethis">
    <table><thead colspan="4"><b><h5>Performance Rating Scales</h5></b></thead>
    <tr><td width="30px" align="center" ><div id="legendcell">5<br>4<br>3<br>2<br>1</div></td>
        <td width="60px" align="center"><div id="legendcell">95-100%<br>85-94%<br>75-84%<br>65-74%<br>< 65%</div></td>
        <td width="300px" ><div id="legendcell">Outstanding / Exceptional<br>Meets Expectations Completely<br>Mediocre; Meets Requirements at Most Basic Level<br>Below Requirements<br>Fail</td>
    </table><br>
 </div>
<div id="wrap"><div>
        
<table bgcolor="white">
    <tr><td>Employee Number<br><h4><?php echo $result['IDNo'];?></h4></td><td>Name<br><h4><?php echo $result['FirstName'].' '.$result['MI'].'. '.$result['SurName'];?></h4></td>
        <td>Position<br><h4><?php echo $result['Position'];?></h4></td><td>Department/Branch<br><h4><?php echo $result['Department'].'/'.$result['Branch'];?></h4></td>
        <td>Evaluated By<br><h4><?php echo $result['EvaluatedBy'];?></h4></td>
    <td><h4><?php echo $result['Reason'];?></h4></td>
    <td>Due Date of Evaluation<br><h4><font color="red"><?php echo $result['EvalDue'];?></font></h4></td>
        <td>Date of Last Evaluation<br><h4><?php echo $result0['LastEval'];?></h4></td>
        <td>Date Hired<br><h4><?php echo $result['DateHired'];?></h4></td><td>How Long With Us <br>(Years, as of Eval Due Date)?<br><h4><?php echo $result['InYears(as of EvalDueDate)'];?></h4></td>
    </tr>
</table>
<div id="hidethis">
        <br><br>
<table bgcolor="white">
    <tr><td>Self-Evaluation:<br><h4><?php echo ($result['SelfCompleted']==1?'<font color="green">Completed</font>':'<font color="red">Unfinished</font>');?></h4></td>
        <td>Evaluator: <br><h4><?php echo ($result['SuperCompleted']==1?'<font color="green">Completed</font>':'<font color="red">Unfinished</font>');?></h4></td>
        <td>Dept Head: <br><h4><?php echo ($result['DeptHeadConfirm']==1?'<font color="green">Completed</font>':'<font color="red">Unfinished</font>');?></h4></td>
        <td>Employee Response: <br><h4><?php echo ($result['EmpResponse']==1?'Agree':($result['EmpResponse']==-1?'Disagree':'<font color="red">Unfinished</font>'));?></h4></td>
        <td>HR Status<br><h4><?php echo ($result['HRStatus']==1?'<font color="green">Filed</font>':'<font color="red">Unfinished</font>');?></h4></td>
    </tr>
</table>
    </div></div><br/>
	</div>
<?php
// if ((allowedToOpen(683,'1rtc')) or ($result['DeptHeadIDNo']==$_SESSION['(ak0)'])){
    // echo '<br/><form action="perfeval.php?w=AddAnotherEvaluator" method="post">';
	
	// echo comboBox($link,'SELECT IDNo, CONCAT(Nickname, " ", Surname) AS FullName FROM `1employees` WHERE Resigned=0 ORDER BY Nickname','IDNo','FullName','employees');
	// echo 'Add another evaluator (only if weights have been set) : <input type="text" name="AnotherEvaluator" list="employees">';
	// echo '<input type="hidden" name=TxnID value="'.$txnid.'"> <input type="submit" name="btnOtherEvaluator" value="Add"/>';
	// echo '</form><br/><br/>';
// }
?>
<div id="hidethis">
<div style="width: 60%">
    <div style="margin-left: 5%; float: left; ">
<?php
$idno=$result['IDNo'];
include('meritdemeritsummary.php');
$subtitle='<br/>Lates for the Year';
$sql='SELECT `Month`,LatesPerMonth,TotalMinutesLate FROM attend_62latescount WHERE IDNo='.$result['IDNo'];
$columnnames=array('Month','LatesPerMonth','TotalMinutesLate');
$width='25%'; $hidecount=true;
include('../backendphp/layout/displayastableonlynoheaders.php');
echo '</div>';
echo '<div style="margin-left: 55%; ">';
$subtitle='<br/>Unapproved Absences for the Year';
$sql='SELECT `IDNo`, COUNT(`a`.`IDNo`) AS `AWOL`, (MONTHNAME(`a`.`DateToday`)) AS `Month`
    FROM `attend_2attendance` `a` WHERE
        (`a`.`DateToday` <= NOW()) AND (`a`.`LeaveNo`=18) AND a.IDNo='.$result['IDNo'].
    ' GROUP BY `a`.`IDNo`, MONTHNAME(`a`.`DateToday`) ORDER BY MONTH(`a`.`DateToday`)'; //echo $sql;
$columnnames=array('Month','AWOL');
$width='25%'; $hidecount=true;
include('../backendphp/layout/displayastableonlynoheaders.php');
?></div></div><br/>
<?php
$subtitle='<br/>NTE\'s for the Year';
$sql='SELECT om.*, GROUP_CONCAT(OtherInfo) AS Result FROM hr_4offensemain om LEFT JOIN hr_4offenseresult res ON om.TxnID=res.TxnID WHERE IDNo='.$result['IDNo'].' GROUP BY om.TxnID;';
$columnnames=array('DateNTE','FirstInfo','Result');
$width='100%'; $hidecount=true;
include('../backendphp/layout/displayastableonlynoheaders.php');
$txnid=intval($_REQUEST['TxnID']);
?>
</div>
