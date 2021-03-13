<?php
if(session_id()==''){ session_start(); } 
        $path=$_SERVER['DOCUMENT_ROOT']; 
		include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
		include_once $path.'/acrossyrs/dbinit/userinit.php';	
    if (!allowedToOpen(6237,'1rtc')) { echo 'No permission'; exit;}
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
        // added clearedby fields 
        $vchno=$_REQUEST['vchno'];
        $txndate=(($_REQUEST['txndate']));
        
         if ($_REQUEST['separate']==1){
            goto separate;
        } else {
        $banktxno=$_REQUEST['banktxno'];
        }
  
        $stmt=$link->prepare("UPDATE banktxns_banktxns SET Cleared = 1, `ClearedByNo`=".$_SESSION['(ak0)'].", `ClearedTS`=Now() WHERE ((TxnNo)=:TxnNo)");
	$stmt->bindValue(':TxnNo', $banktxno, PDO::PARAM_STR);
	$stmt->execute();
	
        separate: //REMOVED CONDITION, SO DAPAT NO DUPLICATION OF VOUCHERS!
        //if ($currentyr==0){
	$sql='SELECT * FROM acctg_3unclearedchecksfromlastperiod WHERE (CVNo like \''.$vchno.'\')';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	if ($stmt->rowCount()==0){
        $stmt=$link->prepare('UPDATE acctg_2cvmain SET Cleared = \''.$txndate.'\', `ClearedByNo`='.$_SESSION['(ak0)'].', `ClearedTS`=Now() WHERE ((CVNo) like \''.$vchno.'\')');

	$stmt->execute();
        $stmt=$link->prepare('UPDATE acctg_2cvmain SET Posted=1, PostedByNo='.$_SESSION['(ak0)'].' WHERE ((CVNo) like \''.$vchno.'\') AND Posted=0');
        $stmt->execute();
        } else {
        $stmt=$link->prepare('UPDATE acctg_3unclearedchecksfromlastperiod SET Cleared = \''.$txndate.'\', `ClearedByNo`='.$_SESSION['(ak0)'].', `ClearedTS`=Now() WHERE ((acctg_3unclearedchecksfromlastperiod.CVNo) like \''.$vchno.'\')'); 
	$stmt->execute();
        }
 $link=null; $stmt=null;
$defaultdate=!isset($_REQUEST['txndate'])?date('Y-m-d', time() - 60 * 60 * 24):$_REQUEST['txndate'];
header("Location:clearchecks.php?txndate=".$defaultdate);
?>