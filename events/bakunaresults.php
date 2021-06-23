<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(100,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');
$hidecount=true;
$w=!isset($_GET['w'])?'V':$_GET['w'];
?>
<br><br><a href='bakunaresults.php?w=V'>Vaccinated</a> &nbsp; <a href='bakunaresults.php?w=R'>Registered with 1Rotary</a><br><br>
<?php

switch ($w){
    case 'V':
?>
<div width='100%'><div width='35%'  style='float: left; margin-left: 3%'>
<?php
$title='Vaccinated';

$sql0='CREATE TEMPORARY TABLE vaccinated AS
SELECT BranchorDept, Pseudobranch, SUM(CASE WHEN LGU IS NOT NULL THEN 1 ELSE 0 END) AS Registered,
SUM(CASE WHEN DateVac1 IS NOT NULL AND DateVac1<=CURDATE() THEN 1 ELSE 0 END) AS Dose1,
SUM(CASE WHEN DateVac2 IS NOT NULL AND DateVac2<=CURDATE() THEN 1 ELSE 0 END) AS Dose2,
COUNT(cp.IDNo) AS NoofEmployees, IF(`b`.`PseudoBranch` <> 1, cp.BranchNo, cp.deptid+800) AS BranchOrDeptNo
FROM event_1bakuna ba RIGHT JOIN attend_30currentpositions cp ON ba.IDNo=cp.IDNo
JOIN 1branches b ON b.BranchNo=cp.BranchNo   
GROUP BY BranchOrDeptNo;';
$stmt0=$link->prepare($sql0); $stmt0->execute();

$subtitle='Total';
$columnnames=array('Dose1','Dose2','TotalNoofEmployees','PercentVaxDose1','PercentVaxDose2');
$sql='SELECT SUM(Dose1) AS Dose1, SUM(Dose2) AS Dose2, SUM(NoofEmployees) AS TotalNoofEmployees, TRUNCATE(SUM(Dose1)/SUM(NoofEmployees)*100,2) AS PercentVaxDose1, TRUNCATE(SUM(Dose2)/SUM(NoofEmployees)*100,2) AS PercentVaxDose2 FROM vaccinated ;';
include ('../backendphp/layout/displayastablenosort.php');

echo '<br><br>';

$sql1='SELECT BranchorDept, IF(Dose1=0,"",Dose1) AS Dose1, IF(Dose2=0,"",Dose2) AS Dose2, NoofEmployees, TRUNCATE((Dose1/NoofEmployees)*100,2) AS PercentVaxDose1, TRUNCATE((Dose2/NoofEmployees)*100,2) AS PercentVaxDose2 FROM vaccinated WHERE ';
$sql2=' ORDER BY PercentVaxDose1 DESC, BranchORDept ASC;';
$columnnames=array('BranchorDept', 'Dose1','Dose2','NoofEmployees','PercentVaxDose1','PercentVaxDose2');
$title='';
$subtitle='Offices';
$sql=$sql1.'BranchOrDeptNo >= 800'.$sql2;
include ('../backendphp/layout/displayastablenosort.php');

$subtitle='Warehouses';
$sql=$sql1.'Pseudobranch=2'.$sql2;
include ('../backendphp/layout/displayastablenosort.php');

?></div>

<div width='50%' style='float: left;margin-left:10%; '>
<?php
$subtitle='Stores & Warehouses';
$sql=$sql1.' Pseudobranch=0 '.$sql2;
include ('../backendphp/layout/displayastable.php');

?>
</div></div>
<?php
break;
case 'R':
?>
<div width='100%'><div width='30%'  style='float: left; margin-left: 3%'>
<?php

$title='Vaccine Registration';
$subtitle='Total Responses';
    $sql='SELECT COUNT(ba.IDNo) AS Responses, COUNT(cp.IDNo) AS Employees, CONCAT(TRUNCATE((COUNT(ba.IDNo)/COUNT(cp.IDNo))*100,2),"%") AS ResponseRate FROM event_1bakuna ba RIGHT JOIN attend_30currentpositions cp ON ba.IDNo=cp.IDNo;';
    $columnnames=array('Responses', 'Employees','ResponseRate');
    $width='25%';
    include ('../backendphp/layout/displayastablenosort.php');

echo '<br><br>';
    $subtitle='Vaccines To Buy';
    $sql='SELECT COUNT(CASE WHEN DECISION=1 THEN DECISION END ) AS Agree, COUNT(CASE WHEN DECISION=2 THEN DECISION END ) AS Disagree, SUM(TotalBakuna) AS VaccinesToBuy,CONCAT(TRUNCATE(((SUM(CASE
	WHEN Decision=1 THEN 1
    ELSE 0
END)/COUNT(IDNo))*100),2),"%") AS `AgreeRate` FROM event_1bakuna ;';
    $columnnames=array('Agree', 'Disagree','VaccinesToBuy','AgreeRate');
    include ('../backendphp/layout/displayastableonlynoheaders.php');

    echo '<br><br>';
    $subtitle='Vaccines Per Area';
    $sql='SELECT Area, SUM(TotalBakuna) AS ToBeVaccinated FROM event_1bakuna ba JOIN attend_30currentpositions cp ON ba.IDNo=cp.IDNo JOIN 1branches b ON b.BranchNo=cp.BranchNo JOIN 0area a ON a.AreaNo=b.AreaNo GROUP BY a.AreaNo;';
    $columnnames=array('Area', 'ToBeVaccinated');
    include ('../backendphp/layout/displayastableonlynoheaders.php');

    
    echo '<br><br>';
    $subtitle='<font color="red">Agreed But Zero Total Vaccine</font>';
    $sql='SELECT FullName,IF(deptid IN (2,10),Branch,dept) AS `Branch/Dept` FROM event_1bakuna b JOIN attend_30currentpositions cp ON b.IDNo=cp.IDNo WHERE TotalBakuna=0 AND Decision=1;';
    $columnnames=array('FullName', 'Branch/Dept');

    include ('../backendphp/layout/displayastableonlynoheaders.php');

    

?></div>

<div width='40%' style='float: left;margin-left:10%; '>
<?php
    $subtitle='Registration Results'; $color1='e0ebeb';
    $sql='SELECT `BranchorDept`, COUNT(CASE WHEN DECISION=1 THEN DECISION END ) AS Agree, COUNT(CASE WHEN DECISION=2 THEN DECISION END ) AS Disagree,  COUNT(ba.IDNo) AS Responses, COUNT(cp.IDNo) AS Employees FROM event_1bakuna ba RIGHT JOIN attend_30currentpositions cp ON ba.IDNo=cp.IDNo GROUP BY `BranchOrDept`;';
    $columnnames=array('BranchorDept', 'Agree', 'Disagree', 'Responses','Employees');
    
    include ('../backendphp/layout/displayastableonlynoheaders.php')

    
?>
</div>


<div width='40%' style='float: right; '>
<?php
    
$title='';
$subtitle='<font color="red">Not Yet Registered</font>';
$sql='SELECT FullName,IF(deptid IN (2,10),Branch,dept) AS `Branch/Dept` FROM attend_30currentpositions WHERE IDNo NOT IN (SELECT IDNo from event_1bakuna) ORDER BY `Branch/Dept`;';
$columnnames=array('FullName', 'Branch/Dept');

include ('../backendphp/layout/displayastablenosort.php');

    
?>
</div>

</div>
    
<?php
}