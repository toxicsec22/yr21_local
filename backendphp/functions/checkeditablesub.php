<?php
$date=isset($date)?$date:'Date';
$sql='Select ' . $date . ',Posted from `'.$table.'` where `'.$pk.'`='.$txnid;
$stmt=$link->query($sql);
$result=$stmt->fetch();

//require('../backendphp/logincodes/varpositions.php');
$closedate=$_SESSION['nb4'];
    if(in_array($table,array('acctg_2salemain','acctg_2collectmain','acctg_2txfrmain','acctg_2depositmain','2purchases','acctg_2cvmain','acctg_2jvmain','acctg_2jvmainforex'))){
    $closedate=(!allowedToOpen(300,'1rtc'))?$_SESSION['nb4']:$_SESSION['nb4A'];    
    }

if($result[$date]<$closedate or date('Y', strtotime($result[$date]))<>$currentyr){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed');  exit; 
	}
if($result['Posted']<>0){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Posted'); exit;
	}
?>