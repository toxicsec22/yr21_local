<?php
if(session_id()==''){ session_start(); } 
$path=$_SERVER['DOCUMENT_ROOT']; 
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';	
    if (!allowedToOpen(6238,'1rtc')) { echo 'No permission'; exit;}   
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
        
        $banktxno=$_REQUEST['banktxno'];
        $depno=$_REQUEST['depno'];
        $txndate=$_REQUEST['txndate'];
        if ($_REQUEST['separate']){
            goto separate;
        } else {
        $banktxno=$_REQUEST['banktxno'];
        }
        
        
        $stmt=$link->prepare("UPDATE banktxns_banktxns SET banktxns_banktxns.Cleared = 1, `ClearedByNo`=".$_SESSION['(ak0)'].", `ClearedTS`=Now() WHERE ((banktxns_banktxns.TxnNo)=:TxnNo)"); 
        
	$stmt->bindValue(':TxnNo', $banktxno, PDO::PARAM_STR);
	$stmt->execute();
	
	separate:
	
        $stmt=$link->prepare("UPDATE acctg_2depositmain SET acctg_2depositmain.Cleared = :TxnDate, `ClearedByNo`=".$_SESSION['(ak0)'].", `ClearedTS`=Now() WHERE ((acctg_2depositmain.DepositNo)=:DepNo)");
	$stmt->bindValue(':TxnDate', $txndate, PDO::PARAM_STR);
        $stmt->bindValue(':DepNo', $depno, PDO::PARAM_STR);
	$stmt->execute();
        $bank=!isset($_GET['bank'])?'':'&bank='.$_GET['bank'];
        $defaultdate=!isset($_REQUEST['txndate'])?date('Y-m-d', time() - 60 * 60 * 24):$_REQUEST['txndate'];
        header("Location:cleardeposits.php?txndate=".$defaultdate.$bank);
 $link=null; $stmt=null;
?>