<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(100,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');
$title='Vaccine Registration';
// case 'Results':
?>
<div width='100%'><div width='35%'  style='float: left; margin-left: 3%'>
<?php
$hidecount=true;

$subtitle='Total Responses';
    $sql='SELECT COUNT(ba.IDNo) AS Responses, COUNT(cp.IDNo) AS Employees, CONCAT(TRUNCATE((COUNT(ba.IDNo)/COUNT(cp.IDNo))*100,2),"%") AS ResponseRate FROM event_1bakuna ba RIGHT JOIN attend_30currentpositions cp ON ba.IDNo=cp.IDNo;';
    $columnnames=array('Responses', 'Employees','ResponseRate');
    $width='25%';
    include ('../backendphp/layout/displayastablenosort.php');

echo '<br><br><br>';
    $subtitle='Vaccines To Buy';
    $sql='SELECT COUNT(CASE WHEN DECISION=1 THEN DECISION END ) AS Agree, COUNT(CASE WHEN DECISION=2 THEN DECISION END ) AS Disagree, SUM(TotalBakuna) AS VaccinesToBuy FROM event_1bakuna ;';
    $columnnames=array('Agree', 'Disagree','VaccinesToBuy');
    include ('../backendphp/layout/displayastableonlynoheaders.php');

?></div><div width='50%' style='float: right; margin-left: 40%'>
<?php
    $subtitle='Registration Results'; $color1='e0ebeb';
    $sql='SELECT `BranchorDept`, COUNT(CASE WHEN DECISION=1 THEN DECISION END ) AS Agree, COUNT(CASE WHEN DECISION=2 THEN DECISION END ) AS Disagree,  COUNT(ba.IDNo) AS Responses, COUNT(cp.IDNo) AS Employees FROM event_1bakuna ba RIGHT JOIN attend_30currentpositions cp ON ba.IDNo=cp.IDNo GROUP BY `BranchOrDept`;';
    $columnnames=array('BranchorDept', 'Agree', 'Disagree', 'Responses','Employees');
    
    include ('../backendphp/layout/displayastableonlynoheaders.php');
?>
</div></div>
    
