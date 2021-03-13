<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
if (!allowedToOpen(7611,'1rtc')){echo 'No permission'; exit;}



$which=!isset($_GET['w'])?'List':$_GET['w'];

switch ($which){

    case 'AddWaybill':
	if (!allowedToOpen(7611,'1rtc')){echo 'No permission'; exit;}
	$txnid=intval($_REQUEST['TxnID']);
	$sql0='SELECT  DateOUT from `invty_2transfer` WHERE TxnID='.$txnid; $stmt=$link->query($sql0); 	$result=$stmt->fetch(); 
	if((addslashes($result['DateOUT']))<$_SESSION['nb4'] or date('Y', strtotime($result['DateOUT']))<>$currentyr){ header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); }
	else {
		$sql='UPDATE `invty_2transfer` SET Waybill="'.$_POST['Waybill'].'" WHERE TxnID='.$txnid; $stmt=$link->prepare($sql); $stmt->execute();
		header("Location:".$_SERVER['HTTP_REFERER']);}
	break;
    
}
 $link=null; $stmt=null;
?>