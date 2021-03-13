<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;
include_once('../switchboard/contents.php');

if (!allowedToOpen(1500,'1rtc')) { header('Location:../index.php?denied=true'); }
  
$title='Check Rate Errors';


?><br>

<?php
echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3><br>';

$sql1='CREATE TEMPORARY TABLE checkrates AS 
SELECT * FROM '.$lastyr.'_1rtc.payroll_22rates UNION
SELECT * FROM '.$currentyr.'_1rtc.payroll_22rates ORDER BY IDNo,DateofChange';
$stmt=$link->prepare($sql1); $stmt->execute();

$sql1='CREATE TEMPORARY TABLE AllIn AS SELECT IDNo from checkrates GROUP BY IDNo,DateofChange HAVING COUNT(IDNo)>1;';
$stmt=$link->prepare($sql1); $stmt->execute();


$sql='SELECT IDNo from AllIn WHERE IDNo>1002;';
$stmt=$link->query($sql); $row=$stmt->fetchAll();

foreach($row AS $res){
	$sql='SELECT * FROM checkrates WHERE IDNo='.$res['IDNo'].' ORDER BY DateofChange DESC';
	$stmt=$link->query($sql); $rowall=$stmt->fetchAll();
	echo '<div style="border:1px solid black;">';
	echo '<b>'.$res['IDNo'].'</b><br>';
	foreach($rowall AS $resdate){
		echo $resdate['DateofChange'].' '.$resdate['BasicRate'].'<br>';
	}
	echo '</div>';
	echo '<br>';
}
				
echo '<br><br>';
echo 'END OF PAGE';

