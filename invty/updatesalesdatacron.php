<?php
	$path=$_SERVER['DOCUMENT_ROOT']; 
	include_once $path.'/acrossyrs/dbinit/userinit.php';
        
        $currentyr=!isset($currentyr)?2021:$currentyr;
        
	$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
	date_default_timezone_set('Asia/Manila');
	
	if (!isset($_SESSION['(ak0)'])){
		$time = date("H:i",strtotime(date("Y-m-d H:i:s")." +0 minutes"));
		if($time<'21:55'){
			echo 'Invalid Update.';
			goto noupdate;
		}
	}
	
	$txndate=(strlen(date('m'))<>2?'0'.date('m'):date('m'));
	$sqldel='DELETE FROM acctg_6targetscores WHERE MonthNo='.$txndate.' AND DisplayType=5';
	$stmt=$link->prepare($sqldel); $stmt->execute();
	
	require ('calctargets.php');
	require ('insert6targets.php');
	noupdate:
?>