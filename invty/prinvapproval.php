<?php
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	include_once $path.'/acrossyrs/dbinit/userinit.php';
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
        // check if allowed
$allowed=array(6937,6936,697,6971);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
        
        include_once('../backendphp/functions/editok.php');
	include_once "../generalinfo/lists.inc";
        
        
	 
        $which=$_GET['w'];
        
switch ($which){
    
    case 'NewCRSApproval':
	if (!allowedToOpen(6937,'1rtc')) { echo 'No permission'; exit;}
        if(($_POST['Defective'])==''){ echo 'Please choose GOOD, DEFECTIVE, or FOR CHECKING.'; exit();}
	if (isset($_POST['Year']) AND $_POST['Year']==1) { $dbsaleprefix=$lastyr; $txnidprefix=''.substr($lastyr,-2).'00'; $slastyr=1; } else {$dbsaleprefix=$currentyr; $txnidprefix=''; $slastyr=0;}
        
	$sql0='Select concat("'.$txnidprefix.'",sm.TxnID) as TxnID, ss.UnitPrice, ss.Qty from `'.$dbsaleprefix.'_1rtc`.`invty_2sale` sm join `'.$dbsaleprefix.'_1rtc`.`invty_2salesub` ss on sm.TxnID=ss.TxnID where SaleNo=\''.$_POST['SaleNo'].'\' and txntype=(Select txntypeid from invty_0txntype where txndesc=\''.$_REQUEST['txntype'].'\') and (sm.BranchNo='.$_SESSION['bnum'].'  OR sm.BranchNo=(SELECT MovedBranch FROM `1branches` WHERE BranchNo='.$_SESSION['bnum'].')) and ss.ItemCode='.$_POST['ItemCode'].($_REQUEST['txntype']=='Customer Return'?' AND ss.Qty>0':''); //echo $sql0; break; }
        
        $stmt=$link->query($sql0);
	$result0=$stmt->fetch();
        $qty=$_POST['Qty']<=$result0['Qty']?$_POST['Qty']:$result0['Qty'];
	$sqlinsert='INSERT INTO `approvals_2salesreturns` SET TxnID='.$result0['TxnID'].', Qty='.$qty.', AmountofReturn='.(($qty)*($result0['UnitPrice'])).', LastYr='.$slastyr.',';
        $sql='';
        $columnstoadd=array('ItemCode','Reason', 'Defective');
	foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; }
	$sql=$sqlinsert.$sql.' BranchNo=\''.$_SESSION['bnum'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';	
	if ($_SESSION['(ak0)']==1002) { echo $sql; }
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:".$_SERVER['HTTP_REFERER']);
        break;
case 'DeleteCRS':
	if (!allowedToOpen(6937,'1rtc')) { echo 'No permission'; exit;}
		
	$sql='DELETE from `approvals_2salesreturns` where ApprovalId='.$_REQUEST['ApprovalId'];

	if ($_SESSION['(ak0)']==1002) { echo $sql; }
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:".$_SERVER['HTTP_REFERER']);
        break;
case 'ApproveCRS':
	if (!allowedToOpen(6936,'1rtc')) { echo 'No permission'; exit;}
	$txnid=$_REQUEST['ApprovalId'];
        $approvalno=mt_rand(100000,999999);
	//start of check if approval number has been used
	$sql0='Select Approval from `approvals_2salesreturns` where Approval='.$approvalno;
	$stmt=$link->query($sql0);
	while ($stmt->rowCount()>0):
		$approvalno=mt_rand(100000,999999);
		$sql0='Select Approval from `approvals_2salesreturns` where Approval='.$approvalno;
		$stmt=$link->query($sql0);
		endwhile;
	// end of check	
               
	$sql0='Select TxnID from `approvals_2salesreturns` where ApprovalId='.$txnid;
	$stmt=$link->query($sql0); $result=$stmt->fetch();
	$sql='UPDATE `approvals_2salesreturns` SET  Approval='.$approvalno.', ApprovedByNo=\''.$_SESSION['(ak0)'].'\', ApprovalTS=Now() where Approval IS NULL AND TxnID='.$result['TxnID']; 
      //  $sql='UPDATE `approvals_2salesreturns` SET  Approval='.$approvalno.', ApprovedByNo=\''.$_SESSION['(ak0)'].'\', ApprovalTS=Now() WHERE ApprovalId='.$txnid;
        if ($_SESSION['(ak0)']==1002) { echo $sql; }
        $stmt=$link->prepare($sql);
	$stmt->execute();
	$approvalno=null;
	header("Location:".$_SERVER['HTTP_REFERER']);
        break;
case 'RecordCRS':
	if (!allowedToOpen(6937,'1rtc')) { echo 'No permission'; exit;}
	$appid=$_REQUEST['ApprovalId'];
        $sql='SELECT TxnID,LastYr,Approval FROM `approvals_2salesreturns` WHERE ApprovalId='.$appid;
        if ($_SESSION['(ak0)']==1002) { echo $sql; }
        $stmt=$link->query($sql); $res=$stmt->fetch();
        $txnid=$res['TxnID']; $approvalno=$res['Approval'];
	//insert main form
	if($res['LastYr']<>0){
		$date2digits=date('y')-$res['LastYr'];
		$sql0='Select sm.*, sr.Approval from `20'.$date2digits.'_1rtc`.`invty_2sale` sm join `approvals_2salesreturns` sr ON (Select CONCAT("'.$date2digits.'00",sm.TxnID))=sr.TxnID where sm.TxnID='.str_replace(''.$date2digits.'00','',$txnid).' AND ApprovalId='.$appid.' group by sm.TxnID';
	} else {
		$sql0='Select sm.*, sr.Approval from `invty_2sale` sm join `approvals_2salesreturns` sr on sm.TxnID=sr.TxnID where sm.TxnID='.$txnid.' AND ApprovalId='.$appid.' group by sm.TxnID';
	}
	$stmt=$link->query($sql0); $result0=$stmt->fetch();
	$columnstoadd=array('ClientNo');
	$sqlinsert='Insert into `invty_2sale` SET `Date`=Now(), SaleNo=\''.$_REQUEST['CRSNo'].'\', PaymentType=5, CheckDetails=\''.$result0['SaleNo'].'\', DateofCheck=\''.$result0['Date'].'\', PONo='.$result0['Approval'].', txntype=5, TeamLeader='.(is_null($result0['TeamLeader'])?0:$result0['TeamLeader']).', ';
	$sql='';
	foreach ($columnstoadd as $field) {
		$sql=$sql.' ' . $field. '=\''.$result0[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo='.$_SESSION['bnum'].', EncodedByNo=\''.$_SESSION['(ak0)'].'\' , PostedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
	// echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	// end of main form
	
	// insert sub
	$sql0='Select sr.ItemCode, (sr.Qty*-1) as Qty, (AmountofReturn/(ifnull(Qty,1))) as UnitPrice, sr.Defective, (Select SerialNo from invty_2salesub ss where ss.ItemCode=sr.ItemCode and ss.TxnID=sr.TxnID) as SerialNo from  `approvals_2salesreturns` sr where Approval='.$approvalno.' AND sr.TxnID='.$txnid; //echo $sql0;
	$stmt=$link->query($sql0); $result=$stmt->fetchAll();
	$sql0='Select TxnID from invty_2sale where BranchNo='.$_SESSION['bnum'].' and SaleNo=\''.$_REQUEST['CRSNo'].'\' and txntype=5';
	$stmt=$link->query($sql0); $result0=$stmt->fetch();
	$txnid=$result0['TxnID'];
	foreach ($result as $row){
	$columnstoadd=array('ItemCode', 'Qty', 'UnitPrice', 'SerialNo', 'Defective');
	$sqlinsert='Insert into `invty_2salesub` SET TxnID='.$txnid.', QtySign=-1, ';
	$sql='';
	foreach ($columnstoadd as $field) {
		$sql=$sql.' ' . $field. '=\''.$row[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
	// echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	}
	// end of sub
	
	
	header("Location:addeditsale.php?txntype=5&TxnID=".$txnid);
	break;

case 'DeleteSU':
	if (!allowedToOpen(697,'1rtc')) { echo 'No permission'; exit;}
		
	$sql='DELETE from `approvals_2storeused` where ApprovalId='.$_REQUEST['TxnID'];

	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:".$_SERVER['HTTP_REFERER']);
        break;
	
case 'ApproveSU':
	if (!allowedToOpen(6971,'1rtc')) { echo 'No permission'; exit;}
	$sql='UPDATE `approvals_2storeused` SET  Approved=1, ApprovedByNo='.$_SESSION['(ak0)'].', ApprovalTS=Now() where ApprovalId='.$_REQUEST['ApprovalId'];
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:".$_SERVER['HTTP_REFERER']);
        break;

case 'RecordSU':
	if (!allowedToOpen(697,'1rtc')) { echo 'No permission'; exit;}
	// check if there is store used for the day; if none, add to main
	$sql0='Select TxnID from invty_2mrr where txntype=9 and BranchNo='.$_SESSION['bnum'].' and Date=curdate()';
	$stmt=$link->query($sql0);
	$result=$stmt->fetch();
	if ($stmt->rowCount()==0){
	//To get Store Used Number 
	include_once '../backendphp/functions/getnumber.php';
	$txnnoprefix='su-'.str_pad($_SESSION['bnum'],2,'0',STR_PAD_LEFT).'-';
	// $txnno=getAutoTxnNo($txnnoprefix,6,'MRRNo','invty_2mrr',$link);

	$charssu=6;
	if($_SESSION['bnum']>=100){
		$charssu=7;
	}
	$txnno=getAutoTxnNo($txnnoprefix,$charssu,'MRRNo','invty_2mrr',$link);
	
	$sql='INSERT INTO `invty_2mrr` SET `Date`=curdate(), MRRNo=\''.$txnno.'\', SupplierNo=\''.$_SESSION['bnum'].'\', txntype=9, ForPONo=\'StoreUsed\', SuppInvNo=0, BranchNo=\''.$_SESSION['bnum'].'\',  EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
	//echo $sql; break;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select TxnID, txntype from `invty_2mrr` where BranchNo=\''.$_SESSION['bnum'].'\' and MRRNo=\''.$txnno.'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	}
	$txnid=$result['TxnID']; 
	// end of main
	// insert sub
	include_once('../backendphp/functions/editok.php');
	
	if (editOk('invty_2mrr',$txnid,$link,9)){
		$sql0='SELECT su.* FROM approvals_2storeused su where Approved=1 and su.BranchNo='.$_SESSION['bnum'];
		$stmt0=$link->query($sql0); $result=$stmt0->fetchAll(); // if ($_SESSION['(ak0)']==1002) { echo $sql0; break;}
		foreach ($result as $row){
		$sql='INSERT INTO `invty_2mrrsub` (`TxnID`, `ItemCode`, `Qty`, `SerialNo`, `TimeStamp`, `EncodedByNo`, `UnitCost`)
Select '.$txnid.' as `TxnID`, \''.$row['ItemCode'].'\' as `ItemCode`, \''.(abs($row['Qty'])*-1).'\' as `Qty`,  concat(\''.$row['Reason'].'\',\' \',\''.$row['ApprovedByNo'].'\',\' \',\''.$row['SerialNo'].'\') as `SerialNo`, Now() as `TimeStamp`, \''.$_SESSION['(ak0)'].'\' as `EncodedByNo`, c.UnitCost from `invty_52latestcost` c where c.ItemCode=\''.$row['ItemCode'].'\'';
                if ($_SESSION['(ak0)']==1002) { echo $sql; }
		$stmt=$link->prepare($sql); $stmt->execute();
		}
	} 
	// end of sub
	//echo $txnid.'<br>'.$sql0.'<br>'.$sql.'<br>'.(editOk('invty_2mrr',$txnid,$link,9)); break;
	//Delete records from approvals.2storeused
	$stmt=$link->prepare('Delete from approvals_2storeused where Approved=1 and BranchNo='.$_SESSION['bnum'].' and ItemCode in (Select ItemCode from invty_2mrr m join invty_2mrrsub ms on m.TxnID=ms.TxnID where m.Date=curdate() and txntype=9 and BranchNo='.$_SESSION['bnum'].')');
	$stmt->execute();
	// done
	header("Location:addeditmrr.php?w=StoreUsed&txntype=9&TxnID=".$txnid);
	break;
	
	case 'AddCancelSRS':
	
	if (!allowedToOpen(6935,'1rtc')) { echo 'No permission'; exit; }
	$sqlinsert='INSERT INTO `approvals_2salesreturns` SET Reason="Cancelled SRS-'.$_POST['Reason'].'",ItemCode=0,TxnID=1, Qty=0, Approval="'.$_POST['SRSNo'].'", AmountofReturn=0, BranchNo=\''.$_SESSION['bnum'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	$stmt=$link->prepare($sqlinsert);
    $stmt->execute();
	header("Location:addsalemain.php?w=Cancel&saletype=cancel5");	
	
	break;
	
	
	case 'ApproveCancelSRS':
	
	if (!allowedToOpen(6936,'1rtc')) { echo 'No permission'; exit; }
	$sql0='SELECT * FROM approvals_2salesreturns WHERE ApprovalId='.intval($_GET['ApprovalId']).'';
	$stmt=$link->query($sql0);
	$result0=$stmt->fetch();
	
	$sqlinsert='INSERT INTO `invty_2sale` SET Date=CURDATE(),ClientNo=10001,txntype=5,Posted=1,Remarks="'.$result0['Reason'].'", SaleNo="SRS'.$result0['Approval'].'", BranchNo=\''.$result0['BranchNo'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';

	$stmt=$link->prepare($sqlinsert);
    $stmt->execute();
	
	
	$sqlupdate='UPDATE approvals_2salesreturns SET ApprovedByNo='.$_SESSION['(ak0)'].',ApprovalTS=NOW() WHERE ApprovalId='.intval($_GET['ApprovalId']).'';

	$stmtupdate=$link->prepare($sqlupdate);
    $stmtupdate->execute();
	
	
		
	$sql='SELECT TxnID FROM invty_2sale WHERE BranchNo=\''.$result0['BranchNo'].'\' AND SaleNo="SRS'.$result0['Approval'].'"';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	
	header("Location:addeditsale.php?txntype=5&TxnID=".$result['TxnID']."");
	
	break;
	
	case 'DeleteCancelSRS':
	if(allowedToOpen(7003,'1rtc')){
		$condi='';
	} else {
		$condi=' EncodedByNo='.$_SESSION['(ak0)'].' AND ';
	}
	$sql='DELETE from `approvals_2salesreturns` where '.$condi.' ApprovalId='.$_REQUEST['ApprovalId'];

    $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addsalemain.php?w=Cancel&saletype=cancel5");
	
	break;
	
	
}
 $link=null; $stmt=null;
?>