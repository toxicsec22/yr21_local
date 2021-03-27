<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(100,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');
$title='Vaccine Registration';
// case 'Results':
?>
<div width='100%'><div width='30%'  style='float: left; margin-left: 3%'>
<?php
$hidecount=true;

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
    
