<?php
// error_reporting(E_ALL);
	// ini_set('display_errors', 1);
$path=$_SERVER['DOCUMENT_ROOT']; 
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once($path.'/acrossyrs/dbinit/userinit.php');
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

if (!allowedToOpen(5572,'1rtc')) {   echo 'No permission'; exit;} 
 
$sql='UPDATE `'.$_GET['tbl'].'` SET `'.$_GET['Date'].'`=(IF(YEAR(CURDATE())='.$currentyr.',CURDATE(),\''.$currentyr.'-12-31\')) WHERE '.$_GET['TxnIDName'].'='.$_GET['TxnID'];  //echo $sql; exit();


$stmt=$link->prepare($sql); $stmt->execute();  $link=null; $stmt=null;

// echo $_GET['ToOpen'];

// echo "Location:/'.$url_folder.'/".($_GET['DB']==0?"invty/":"acctg/").$_GET['ToOpen'];
header("Location:/'.$url_folder.'/".($_GET['DB']==0?"invty/":"acctg/").$_GET['ToOpen'].'&'.$_GET['TxnIDName'].'='.$_GET['TxnID']);
?>