<?php
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	include_once('../backendphp/functions/editok.php');
	include_once "../generalinfo/lists.inc";
        if (!allowedToOpen(6406,'1rtc')) { echo 'No permission'; exit;}  
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
	
        
        $whichqry=$_GET['w'];

switch ($whichqry){
case 'NewAdjust':
	if (!allowedToOpen(6406,'1rtc')) { echo 'No permission'; exit;}  
	//to check if editable
	if(($_POST['Date'])<$_SESSION['nb4']  or date('Y', strtotime($_POST['Date']))<>$currentyr){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 	 break; 
	}	
	$sqlinsert='INSERT INTO `invty_4adjust` SET  ';
        $sql='';
	
        $columnstoadd=array('Date','AdjNo');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo=\''.$_SESSION['bnum'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\', `TimeStamp`=Now()';
        if($_SESSION['(ak0)']==1002){echo $sql;}
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select TxnID from `invty_4adjust` where AdjNo=\''.$_POST['AdjNo'].'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	$txnid=$result['TxnID'];
	header("Location:addeditadj.php?w=Adjust&TxnID=".$txnid);
        break;

case 'AdjustMainEdit':
	if (!allowedToOpen(6406,'1rtc')) { echo 'No permission'; exit;}  
	include('../backendphp/functions/checkeditablemain.php');
	$txnid=intval($_REQUEST['TxnID']);
	if (editOk('invty_4adjust',$txnid,$link,'adjust')){
        include_once '../invty/trailinvty.php'; recordtrail($txnid,'invty_4adjust',$link,0);
	
        $columnstoedit=array('Date','AdjNo');
    } else {
        $columnstoedit=array();
    }
	
	$sqlupdate='UPDATE `invty_4adjust` SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\' where TxnID='.$txnid . ' and Posted=0 and `Date`>'.$_SESSION['nb4']; 
	
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:addeditadj.php?w=Adjust&TxnID=".$txnid);
	break;

case 'AdjustSubAdd':
	if (!allowedToOpen(6406,'1rtc')) { echo 'No permission'; exit;}  
	$txnid=intval($_REQUEST['TxnID']);
	$pk='TxnID';$table='invty_4adjust';$date='Date';
	//to check if editable
	include('../backendphp/functions/checkeditablesub.php');
			
	$sqlinsert='INSERT INTO `invty_4adjustsub` SET TxnID='.$txnid.', ';
        $sql='';
        $columnstoadd=array('ItemCode','Qty','UnitPrice','SerialNo','Remarks');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:addeditadj.php?w=Adjust&TxnID=".$txnid);
        break;

case 'AdjustSubEdit':
	if (!allowedToOpen(6406,'1rtc')) { echo 'No permission'; exit;}  
	$txnid=intval($_REQUEST['TxnID']);
	$txnsubid=$_REQUEST['TxnSubId'];
	if (editOk('invty_4adjust',$txnid,$link,'adjust')){
        include_once '../invty/trailinvty.php'; recordtrail($txnsubid,'invty_4adjustsub',$link,0);
	
        $columnstoedit=array('ItemCode','Qty','SerialNo','Remarks');
    } else {
        $columnstoedit=array();
    }
	
	$sqlupdate='UPDATE `invty_4adjustsub` as s join `invty_4adjust` as m on m.TxnID=s.TxnID SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' s.EncodedByNo=\''.$_SESSION['(ak0)'].'\', s.TimeStamp=Now() where TxnSubId='.$txnsubid . ' and m.Posted=0 and m.`Date`>\''.$_SESSION['nb4'].'\''; 
	//echo $sql;
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:addeditadj.php?w=Adjust&TxnID=".$txnid);
	break;

case 'ChargeSales':
	if (!allowedToOpen(6406,'1rtc')) { echo 'No permission'; exit;}  
	include_once('../backendphp/functions/getnumber.php');
	$txnid=intval($_REQUEST['TxnID']);
	$sql='Select * from invty_4adjust where TxnID='.$txnid;
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	$branchno=$result['BranchNo'];
	$date=$result['Date'];
	
    $txnnoprefixold='c-'.str_pad($branchno,2,'0',STR_PAD_LEFT).'-';
	$txnnoprefix=substr($dbprefix,2,2).'c-'.str_pad($branchno,2,'0',STR_PAD_LEFT).'-';// echo $txnnoprefix; exit();
	
	
		$sqltxnno='SELECT SaleNo FROM invty_2sale where SaleNo LIKE "%'.$txnnoprefixold.'%" order by RIGHT(SaleNo,3) desc Limit 1;';
	    $stmttxnno=$link->query($sqltxnno);
	    $resulttxnno=$stmttxnno->fetch();
		
	    if (is_null($resulttxnno['SaleNo'])){
			$txnno=$txnnoprefix.str_pad('1',3,'0',STR_PAD_LEFT);
	    } else {
			$txnno=$txnnoprefix.str_pad((substr($resulttxnno['SaleNo'],-3)+1),3,'0',STR_PAD_LEFT);
	    }
	
	
	//To get branch head
	$sql='Select IDNo from `attend_30currentpositions` where BranchNo=\''.$branchno.'\' and PositionID in (50,53,32,33,37,81) order by JLID desc limit 1';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	$clientno=$result['IDNo'];
	
	//insert main form
	$sqlinsert='INSERT INTO `invty_2sale` SET `Date`=\''.$date.'\', ClientNo='.$clientno.', SaleNo=\''.$txnno.'\', PaymentType=2, txntype=3, BranchNo=\''.$_SESSION['bnum'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
	
	$stmt=$link->prepare($sqlinsert);
	$stmt->execute();
	
	//get sale txnid
	$sql='Select TxnID from `invty_2sale` where SaleNo=\''.$txnno.'\'';
	
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	$saletxnid=$result['TxnID'];
	//insert sub
	$sqladj='Select *, Qty*-1 as ChargeQty from invty_4adjustsub where TxnID='.$txnid;
	$stmt=$link->query($sqladj);
	$result=$stmt->fetchAll();
	
	foreach($result as $sub){
		$sqlinsert='INSERT INTO `invty_2salesub` SET `TxnID`='.$saletxnid.', `ItemCode`='.$sub['ItemCode'].', `Qty`='.$sub['ChargeQty'].', `UnitPrice`=\''.$sub['UnitPrice'].'\', `SerialNo`=\''.$sub['SerialNo'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
				
	$stmt=$link->prepare($sqlinsert);
	$stmt->execute();
	}
	
	$sqldel='Delete from invty_4adjustsub where TxnID='.$txnid;
	$stmt=$link->prepare($sqldel);
	$stmt->execute();
	header("Location:addeditadj.php?w=Adjust&TxnID=".$txnid);
        break;

        }
 $link=null; $stmt=null;
?>