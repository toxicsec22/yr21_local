<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(609,'1rtc')){ echo 'No permission'; exit;}
$showbranches=false; include_once('../switchboard/contents.php');


$title='Agency Attendance';
$fromdate=(!isset($_REQUEST['FromDate'])?date('Y-m-d'):$_REQUEST['FromDate']); $todate=(!isset($_REQUEST['ToDate'])?date('Y-m-d'):$_REQUEST['ToDate']);
if (!isset($_POST['submit'])){
    include_once('../switchboard/contents.php');
?><br>
<form action="agencyattendance.php?FromDate=<?php echo $fromdate;?>&ToDate=<?php echo $todate;?>" method='post'>
    Show attendance from <input type='date' name='FromDate' value="<?php echo $fromdate; ?>">
    &nbsp to <input type='date' name='ToDate' value="<?php echo $todate; ?>">
    &nbsp &nbsp <input type='submit' name='submit' value='Lookup'>&nbsp &nbsp <!--<input type='submit' name='print' value='Print'>-->
</form>
<?php
}
if (!isset($_POST['submit']) /*AND !isset($_GET['print'])*/){ goto noform;}

$sql='DROP TEMPORARY TABLE IF EXISTS AgencyAttend;'; $stmt=$link->prepare($sql); $stmt->execute();
$sql='CREATE TEMPORARY TABLE AgencyAttend AS Select a.*, IF(LeaveName="Present","8 to 5",LeaveName) as Schedule, Left(DayName(DateToday),3) as `Day`, IF((`a`.`Overtime`=0),"",OTHours) AS OT_Hours, CompanyName as Company
FROM attend_70agencyattendance a 
JOIN `attend_1defaultbranchassign` db ON a.IDNo=db.IDNo
JOIN `1branches` b ON db.`DefaultBranchAssignNo`=b.BranchNo
JOIN `1companies` c ON b.CompanyNo=c.CompanyNo
WHERE DateToday>=\''.$fromdate.'\' AND DateToday<=\''.$todate.'\'
ORDER BY IDNo, DateToday'; 
$stmt=$link->prepare($sql); $stmt->execute(); 
$sql1='Select SurName, FirstName, concat(SurName, ", ", FirstName) AS FullName, IDNo, Company, Position from AgencyAttend GROUP BY IDNo ORDER BY Surname, FirstName';
$sql2='Select * from AgencyAttend ';

// to get name for signature
$sqluser='Select concat(SurName, ", ", FirstName) AS FullName, Position FROM `1employees` e JOIN `attend_30currentpositions` p ON e.IDNo=p.IDNo WHERE e.IDNo='.$_SESSION['(ak0)'];
$stmt=$link->query($sqluser); $result=$stmt->fetch();
$name=$result['FullName']; $position=$result['Position'];
?>
<html>
<head>
<title>Agency Attendance</title>
<?php include ('../backendphp/layout/standardprintsettings.php');?>
<style>

body  
{ 
    /* this affects the margin on the content before sending to printer */ 
    margin: 10px;
    font-size: 10pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
}
table,td {
        border:1px solid;
border-collapse:collapse;
padding: 3px;
    font-size: 9pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
    page-break-inside:avoid;
    }
a:link {
    color: darkblue;
    text-decoration: none;
}
footer {page-break-after: always;}
</style>
</head>
<body>
    <?php
    $stmt1=$link->query($sql1); $res1=$stmt1->fetchAll();
    $textfordisplay='';
    
    foreach ($res1 as $emp){
        $textfordisplay=$textfordisplay.'For the period: '.$fromdate.' to '.$todate.'<br><br>
        <h4>Employee\'s Name:  <a href="javascript:window.print()">'.$emp['SurName'].', '.$emp['FirstName'].'</a><br>Company Assignment: '.$emp['Company']
            .'<br>Position: '.$emp['Position'].'</h4>'
            .'<table><thead><tr><td>Date</td><td>Day</td><td>Schedule</td><td>TimeIn</td><td>TimeOut</td><td>OT_Hours</td></tr></thead><tbody>';
        //$sql2=$sql2
        $stmt2=$link->query($sql2.' WHERE IDNo='.$emp['IDNo'].' ORDER BY DateToday'); $res2=$stmt2->fetchAll();
        foreach ($res2 as $emp2){
            $textfordisplay=$textfordisplay.'<tr><td>'.$emp2['DateToday'].'</td><td>'.$emp2['Day'].'</td><td>'.$emp2['Schedule'].'</td><td>'.$emp2['TimeIn'].'</td><td>'.$emp2['TimeOut'].'</td><td>'.$emp2['OT_Hours'].'</td></tr>';
        }
        $textfordisplay=$textfordisplay.'</tbody></table><br><br>
        <footer><table><tr><td>VERIFIED/APPROVED BY:</td></tr><tr><td><br><br><br>___________________________________<br>
        '.$name.str_repeat('&nbsp',15).' Date</td></tr>
        <tr><td>'.str_repeat('&nbsp',10).$position.'</td></tr></table></footer><br><br>';
    }
    
    echo $textfordisplay.'<br><br><br>';
    
    ?>
</body>
</html>
<?php
noform:
      $link=null; $stmt=null;
?>