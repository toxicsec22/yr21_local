<?php
	$path=$_SERVER['DOCUMENT_ROOT']; 
	include_once $path.'/acrossyrs/dbinit/userinit.php';
	$link=!isset($link)?connect_db('2021_1rtc',0):$link;
	date_default_timezone_set('Asia/Manila');
	
	$time = date("H:i",strtotime(date("Y-m-d H:i:s")." +0 minutes"));
	if($time<'18:00'){
		echo 'Invalid Update.';
		exit();
	}
	
	$sqlupdate='UPDATE approvals_5ot SET Approved=3 WHERE DateToday=CURDATE() AND Approved=0';
	$stmt=$link->prepare($sqlupdate); $stmt->execute();
?>