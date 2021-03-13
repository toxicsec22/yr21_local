<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(759,6930,7591,7592);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
include_once('../switchboard/contents.php');    
 


$which=$_GET['w'];
switch ($which){
    case 'RequestSetDefective':
	
        $columnnames=array(
                    array('field'=>'ItemCode', 'type'=>'text','size'=>6, 'required'=>true),
                    array('field'=>'Qty', 'type'=>'text','size'=>6, 'required'=>true),
                    array('field'=>'Remarks', 'type'=>'text','size'=>80, 'required'=>true),
                    array('field'=>'SerialNo', 'type'=>'text','size'=>6, 'required'=>false)
                    );
    $action='setasdefective.php?w=NewAdjForApproval'; $method='POST';
    $title='Step 1. Report Defective Item'; $liststoshow=array();
     include('../backendphp/layout/inputmainform.php');
     echo '<br><br>';
     $title='Step 2. Wait for Approval from Supply Chain';
     $columnnames=array('ItemCode','Qty','Value','SerialNo','Remarks','EncodedBy','TimeStamp');
     if (allowedToOpen(7591,'1rtc')){
        $condition='';
        $editprocess='setasdefective.php?w=ApproveDefective&action_token='.$_SESSION['action_token'].'&TxnID=';
        $editprocesslabel='Approve';
        $columnnames[]='Branch';
     } else {
        $condition='and sd.BranchNo='.$_SESSION['bnum'];
     }
     
        $sql='Select b.Branch,sd.*, format(UnitPrice*Qty,2) as Value,e.Nickname as EncodedBy from `approvals_2setasdefective` sd 
        join `1employees` e on sd.EncodedByNo=e.IDNo
        join `1branches` b on b.BranchNo=sd.BranchNo
        where sd.Approval is null '.$condition;
        $delprocess='setasdefective.php?w=DeleteAdjForApproval&action_token='.$_SESSION['action_token'].'&TxnID=';
        $txnid='ApprovalId'; 
        include('../backendphp/layout/displayastablewithedit.php');
        echo '<br><br>';
    
    case 'NewAdjForApproval':
        if (!allowedToOpen(759,'1rtc')) {   echo 'No permission'; exit;}
        include_once('invlayout/pricelevelcase.php');
      require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
      $columnstoadd=array('ItemCode', 'Qty', 'SerialNo', 'Remarks'); $sql='';
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	
	$sql='INSERT INTO `approvals_2setasdefective` SET '.$sql.' UnitPrice= 
(SELECT 
(SELECT 
						'.$plcase.'
					FROM `1branches` b1 where b1.BranchNo='.$_SESSION['bnum'].'
				)
				
		FROM `invty_5latestminprice` lmp WHERE lmp.ItemCode='.$_POST['ItemCode'].') , BranchNo=\''.$_SESSION['bnum'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
      $stmt=$link->prepare($sql); $stmt->execute();
      header("Location:setasdefective.php?w=RequestSetDefective");
      break;
   
   case 'ApproveDefective':
      require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
      if (!allowedToOpen(7591,'1rtc')) { echo 'No permission'; exit;}
      if($_SESSION['nb4']>=''.$currentyr.'-12-31') { header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 	 break;}
	$txnid=intval($_REQUEST['TxnID']);
        $approvalno=mt_rand(100000,999999);
	//start of check if approval number has been used
	$sql0='Select Approval from `approvals_2setasdefective` where Approval='.$approvalno;
	$stmt=$link->query($sql0); $res=$stmt->fetch();        
	while ($stmt->rowCount()>0):
		$approvalno=mt_rand(100000,999999);
		$sql0='Select Approval from `approvals_2setasdefective` where Approval='.$approvalno;
		$stmt=$link->query($sql0);
		endwhile;
	// end of check
        // approve!
	$sql='UPDATE `approvals_2setasdefective` SET  Approval='.$approvalno.', ApprovedByNo=\''.$_SESSION['(ak0)'].'\', ApprovalTS=Now() where ApprovalId='.$txnid;
	//echo $sql; break;
        $stmt=$link->prepare($sql); $stmt->execute();
        // end of approval
        // make adjustment -- MAIN
        $sql1='SELECT * FROM `approvals_2setasdefective` WHERE Approval='.$approvalno;
        $stmt1=$link->query($sql1); $result1=$stmt1->fetch() ;
	$branchno=$result1['BranchNo'];
        //ADJUST TO MAKE DEFECTIVE
	$sql='INSERT INTO `invty_4adjust` SET Date=Now(), AdjNo=\''.$approvalno.'D\', AdjType=1,  BranchNo=\''.$branchno.'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\', `TimeStamp`=Now()'; $stmt=$link->prepare($sql); $stmt->execute();
	//get txnID
	$sql='Select TxnID from `invty_4adjust` where AdjNo=\''.$approvalno.'D\''; $stmt=$link->query($sql); $result=$stmt->fetch(); $txnid=$result['TxnID'];
	// make adjustment -- SUB
        $columnstoadd=array('ItemCode','Qty','UnitPrice','SerialNo','Remarks'); $sql='';
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($result1[$field]).'\', '; 
	}
	$sqlinsert='INSERT INTO `invty_4adjustsub` SET TxnID='.$txnid.', '.$sql.' Defective=1, EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';// echo $sql;break;
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
        //ADJUST TO REMOVE GOOD ITEM
        $sql='INSERT INTO `invty_4adjust` SET Date=Now(), AdjNo=\''.$approvalno.'G\', AdjType=1,  BranchNo=\''.$branchno.'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\', `TimeStamp`=Now()'; $stmt=$link->prepare($sql); $stmt->execute();
	//get txnID
	$sql='Select TxnID from `invty_4adjust` where AdjNo=\''.$approvalno.'G\''; $stmt=$link->query($sql); $result=$stmt->fetch(); $txnid=$result['TxnID'];
	// make adjustment -- SUB
        $columnstoadd=array('ItemCode','UnitPrice','SerialNo','Remarks'); $sql='';
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($result1[$field]).'\', '; 
	}
	$sqlinsert='INSERT INTO `invty_4adjustsub` SET TxnID='.$txnid.', '.$sql.'Qty='.($result1['Qty']*-1).', Defective=0, EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
	//GET AMOUNT OF DEFECTIVE
	$sql2='SELECT SUM(lc.UnitCost*Qty)*-1 AS AdjAmt FROM invty_4adjustsub adjs JOIN invty_52latestcost lc ON adjs.ItemCode=lc.ItemCode WHERE TxnID='.$txnid.' GROUP BY TxnID';
	$stmt2=$link->query($sql2); $result2=$stmt2->fetch(); $amt=$result2['AdjAmt'];
	//ACCTG ADJUSTMENT TO MOVE FROM INVTY TO DEFECTIVE INVTY -- REMOVED IN YR2021
	/*
	$sql='INSERT INTO `acctg_2jvmain` SET Date=Now(), AdjustNo=\''.$approvalno.'D\', Remarks="Defective", EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\', `TimeStamp`=Now()'; $stmt=$link->prepare($sql); $stmt->execute();
	//get txnID
	$sql='Select TxnID from `acctg_2jvmain` where AdjustNo=\''.$approvalno.'D\''; $stmt=$link->query($sql); $result=$stmt->fetch(); $txnid=$result['TxnID'];
	// make adjustment -- SUB
	$sqlinsert='INSERT INTO `acctg_2jvsub` SET TxnID='.$txnid.', BranchNo='. $branchno .', DebitAccountID=331, CreditAccountID=300, Amount='.$amt.', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
		$stmt=$link->prepare($sqlinsert); $stmt->execute();
		*/
	$approvalno=null;
	//end of Acctg Adjustment
	header("Location:setasdefective.php?w=RequestSetDefective&done=1");
      break;
    
   case 'DeleteAdjForApproval':
      require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
      $txnid=intval($_REQUEST['TxnID']);
	$sql='Delete from `approvals_2setasdefective` where ApprovalId='.$txnid.' and EncodedByNo=\''.$_SESSION['(ak0)'].'\' AND (Approval IS NULL)';
	echo "<font color='red'>Deletions can be done by the person who entered it.</font>";
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:setasdefective.php?w=RequestSetDefective");
      break;
    
    case 'ReverseDefective':
	if(!allowedToOpen(7592,'1rtc')) { echo 'No permission'; exit;}    
        $columnnames=array(
                    array('field'=>'FromApprovalNo', 'type'=>'text','size'=>6, 'required'=>true),
		    array('field'=>'ItemCode', 'type'=>'text','size'=>6, 'required'=>true),
                    array('field'=>'Qty', 'type'=>'text','size'=>6, 'required'=>true),
                    array('field'=>'Remarks', 'type'=>'text','size'=>80, 'required'=>true),
                    array('field'=>'SerialNo', 'type'=>'text','size'=>6, 'required'=>false)
                    );
    $action='setasdefective.php?w=Reverse'; $method='POST';
    $title='Reverse Defective Item'; $liststoshow=array();
     include('../backendphp/layout/inputmainform.php');
     break;
    
    case 'Reverse':
      require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
      if(!allowedToOpen(7592,'1rtc')) { echo 'No permission'; exit;}
	$approvalno=$_POST['FromApprovalNo'];
	$sql1='SELECT s.ItemCode, s.Qty, s.UnitPrice FROM `invty_4adjust` m JOIN `invty_4adjustsub` s ON m.TxnID=s.TxnID WHERE AdjNo=\''.$approvalno.'D\' AND BranchNo='.$_SESSION['bnum'].' AND ItemCode='.$_POST['ItemCode'];
	$stmt1=$link->query($sql1); $result1=$stmt1->fetch() ;
	if ($stmt1->rowCount()>0){
	    
	    // make adjustment -- MAIN reverse defective
	    $sql='INSERT INTO `invty_4adjust` SET Date=Now(), AdjNo=\''.$approvalno.'RD\', AdjType=1,  BranchNo=\''.$_SESSION['bnum'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\', `TimeStamp`=Now()'; $stmt=$link->prepare($sql); $stmt->execute();
	    //get txnID
	    $sql='Select TxnID from `invty_4adjust` where AdjNo=\''.$approvalno.'RD\''; $stmt=$link->query($sql); $result=$stmt->fetch(); $txnid=$result['TxnID'];
	    // make adjustment -- SUB reverse defective
	    $columnstoadd=array('ItemCode','UnitPrice'); $sql='';
	    foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($result1[$field]).'\', '; }
	    $columnstoadd=array('SerialNo','Remarks'); $sql2='';
	    foreach ($columnstoadd as $field) { $sql2=$sql2.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
	    $sqlinsert='INSERT INTO `invty_4adjustsub` SET TxnID='.$txnid.', '.$sql.$sql2.'Qty='.($_POST['Qty']*-1).', Defective=1, EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; //echo $sqlinsert;break;
	    $stmt=$link->prepare($sqlinsert); $stmt->execute();
	    
        //ADJUST TO add back to good item
	    $sql='INSERT INTO `invty_4adjust` SET Date=Now(), AdjNo=\''.$approvalno.'RG\', AdjType=1,  BranchNo=\''.$_SESSION['bnum'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\', `TimeStamp`=Now()'; $stmt=$link->prepare($sql); $stmt->execute();
	    //get txnID
	    $sql='Select TxnID from `invty_4adjust` where AdjNo=\''.$approvalno.'RG\''; $stmt=$link->query($sql); $result=$stmt->fetch(); $txnid=$result['TxnID'];
	    // make adjustment -- SUB back to good item
	    $columnstoadd=array('ItemCode','UnitPrice'); $sql='';
	    foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($result1[$field]).'\', '; }
	    $columnstoadd=array('Qty','SerialNo','Remarks'); $sql2='';
	    foreach ($columnstoadd as $field) { $sql2=$sql2.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
	    $sqlinsert='INSERT INTO `invty_4adjustsub` SET TxnID='.$txnid.', '.$sql.$sql2.' Defective=0, EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';// echo $sql;break;
	    $stmt=$link->prepare($sqlinsert); $stmt->execute();
	
	//REVERSE IN ACCTG -- REMOVED IN YR2021
	/*
	$sql2='SELECT * FROM `acctg_2jvmain` adjm JOIN `acctg_2jvsub` adjs ON adjm.TxnID=adjs.TxnID WHERE AdjustNo=\''.$approvalno.'D\'';
	$stmt2=$link->query($sql2); $result2=$stmt2->fetch() ;
	$amt=($result2['Amount']/$result1['Qty'])*$_POST['Qty'];
	
	//ACCTG ADJUSTMENT TO MOVE FROM DEFECTIVE INVTY TO INVTY
	$sql='INSERT INTO `acctg_2jvmain` SET Date=Now(), AdjustNo=\''.$approvalno.'RD\', Remarks="Reverse Defective", EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\', `TimeStamp`=Now()'; $stmt=$link->prepare($sql); $stmt->execute();
	//get txnID
	$sql='Select TxnID from `acctg_2jvmain` where AdjustNo=\''.$approvalno.'RD\''; $stmt=$link->query($sql); $result=$stmt->fetch(); $txnid=$result['TxnID'];
	// make adjustment -- SUB
	$sqlinsert='INSERT INTO `acctg_2jvsub` SET TxnID='.$txnid.', BranchNo='. $_SESSION['bnum'] .', DebitAccountID=300, CreditAccountID=331, Amount='.$amt.', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
		$stmt=$link->prepare($sqlinsert); $stmt->execute();
		*/
	}
	$approvalno=null;
	//end of Acctg Adjustment
	header("Location:setasdefective.php?w=ReverseDefective&done=1");
      break;
      
case 'SetDefectInAdj':
      require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
      if(!allowedToOpen(6930,'1rtc')) { echo 'No permission'; exit;}
      $txnsubid=$_REQUEST['TxnSubId'];
      $sql='UPDATE `invty_4adjustsub` SET  Defective='.($_GET['Defect']==1?0:1).' WHERE TxnSubId='.$txnsubid; $stmt=$link->prepare($sql); $stmt->execute();
      header("Location:".$_SERVER['HTTP_REFERER']);
      
case 'SetDefectInSales':
      require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
      if(!allowedToOpen(6930,'1rtc')) { echo 'No permission'; exit;}
      $txnsubid=$_REQUEST['TxnSubId'];
      $sql0='SELECT Defective FROM `invty_2salesub` WHERE TxnSubId='.$txnsubid; $stmt=$link->query($sql0); $res0=$stmt->fetch();
      $sql='UPDATE `invty_2salesub` s JOIN `invty_2sale` m ON m.TxnID=s.TxnID SET  Defective='.($res0['Defective']==1?0:1).' WHERE txntype=3 AND s.TxnID='.$_REQUEST['TxnID'].' AND TxnSubId='.$txnsubid; $stmt=$link->prepare($sql); $stmt->execute();
      header("Location:".$_SERVER['HTTP_REFERER']);
}
      $link=null; $stmt=null;
?>