<?php
$path=$_SERVER['DOCUMENT_ROOT']; 
include_once $path.'/acrossyrs/dbinit/userinit.php';
$currentyr=date('Y');
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
$day=date("w");
$sql1='SELECT TypeOfDayNo FROM `attend_2attendancedates` WHERE DateToday<CURDATE()';
$stmt = $link->query($sql1);
$row= $stmt->fetch();

if (($day!=0) || (($row['TypeOfDayNo'])!=0)){
	$sql='Update `attend_2attendancedates` SET Posted=1, PostedEncby=0, PostedTS=Now() WHERE Posted = 0 AND DateToday<CURDATE()';
}

$stmt=$link->prepare($sql); $stmt->execute();
?>

