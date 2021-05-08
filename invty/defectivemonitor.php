<?php 
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(715,'1rtc')) {    echo 'No permission'; exit;}    
$showbranches=true; include_once('../switchboard/contents.php');
 

$which=!isset($_GET['w'])?'List':$_GET['w'];
switch ($which){
    case 'List':
        $title='Defective Items from Returns'; 
	$sDate=(isset($_REQUEST['sDate'])?$_REQUEST['sDate']:date('Y-m-d',strtotime("last month")));
	$eDate=(isset($_REQUEST['eDate'])?$_REQUEST['eDate']:date('Y-m-d'));
	?>
	<form method="post" action="defectivemonitor.php" enctype="multipart/form-data">
	From Date <input type="date" name="sDate" size=5 value="<?php echo $sDate; ?>"></input>
	To Date <input type="date" name="eDate" size=5 value="<?php echo $eDate; ?>"></input>
	<input type="submit" name="lookup" value="Lookup"> </form>
	<?php
	
	
	if (allowedToOpen(7151,'1rtc')){ $condition=''; } else { $condition=' AND sd.BranchNo='.$_SESSION['bnum'];}
	
	$sql='SELECT `Date` AS DateofSale, SaleNo, sd.TxnID, Branch, ItemCode, Qty, Reason, Approval, e.Nickname AS ApprovedBy, sd.TimeStamp, RequestNo,TransferNo, ReqTxfrTS, if(Defective=1,"Defective","ForCheckup") as DefectiveorForCheckUp
	FROM approvals_2salesreturns sd 
	LEFT JOIN `1branches` b ON b.BranchNo=sd.BranchNo
	LEFT JOIN `1employees` e ON e.IDNo=sd.ApprovedByNo
	JOIN `'.$lastyr.'_1rtc`.`invty_2sale` sm ON (Select CONCAT("'.substr($lastyr, -2).'00",sm.TxnID))=sd.TxnID WHERE Defective<>0 AND sd.TimeStamp between \''.$sDate.'\' AND \''.$eDate.'\' '.$condition.
        ' UNION ALL 
                SELECT `Date` AS DateofSale, SaleNo, sd.TxnID, Branch, ItemCode, Qty, Reason, Approval, e.Nickname AS ApprovedBy, sd.TimeStamp, RequestNo,TransferNo, ReqTxfrTS, if(Defective=1,"Defective","ForCheckup") as DefectiveorForCheckUp
	FROM approvals_2salesreturns sd
	JOIN `1branches` b ON b.BranchNo=sd.BranchNo
	JOIN `1employees` e ON e.IDNo=sd.ApprovedByNo
	JOIN `invty_2sale` sm ON sm.TxnID=sd.TxnID WHERE Defective<>0 AND sd.TimeStamp between \''.$sDate.'\' AND \''.$eDate.'\' '.$condition;
	$columnnames=array('DateofSale','SaleNo','Branch','ItemCode','Qty','Reason','Approval','ApprovedBy','TimeStamp','RequestNo','TransferNo','DefectiveorForCheckUp');
	// echo $sql; exit();
	if (allowedToOpen(7152,'1rtc')){
	    /*$columnnames[]='RequestNo'; $columnnames[]='TransferNo'; */ $columnnames[]='ReqTxfrTS';
	    $columnstoedit=array('RequestNo','TransferNo');
	    $txnidname='TxnID';
	    $editprocess='defectivemonitor.php?w=EditReturn&TxnID=';$editprocesslabel='Enter';
	    include('../backendphp/layout/displayastableeditcells.php');
	    }
	    else {include('../backendphp/layout/displayastable.php');}
	
	 echo '<br><br>';
	$title='Defective Items from Stock';
        $sql='SELECT ApprovalID, Branch, ItemCode, Qty, Remarks as Reason, Approval, e.Nickname AS ApprovedBy, sd.TimeStamp, RequestNo,TransferNo, ReqTxfrTS
	FROM approvals_2setasdefective sd
	JOIN `1branches` b ON b.BranchNo=sd.BranchNo
	JOIN `1employees` e ON e.IDNo=sd.ApprovedByNo WHERE sd.TimeStamp between \''.$sDate.'\' AND \''.$eDate.'\' '.$condition;
        $columnnames=array('Branch','ItemCode','Qty','Reason','Approval','ApprovedBy','TimeStamp','RequestNo','TransferNo');
	
        if (allowedToOpen(7152,'1rtc')){
	    /*$columnnames[]='RequestNo'; $columnnames[]='TransferNo';*/ $columnnames[]='ReqTxfrTS';
	    $columnstoedit=array('RequestNo','TransferNo');
	    $txnidname='ApprovalID';
	    $editprocess='defectivemonitor.php?w=EditStock&ApprovalID=';$editprocesslabel='Enter';
	    include('../backendphp/layout/displayastableeditcells.php');
	    }
	    else {include('../backendphp/layout/displayastable.php');}
       
	break;
    case 'EditReturn':
    case 'EditStock':
	if (!allowedToOpen(7152,'1rtc')){ header('Location:defectivemonitor.php');}
      require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
      if ($which=='EditReturn'){ $table='approvals_2salesreturns'; $txnidname='TxnID'; } else { $table='approvals_2setasdefective'; $txnidname='ApprovalID'; }
      $columnstoadd=array('RequestNo','TransferNo'); $sql='';
	foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 	}
      $sql='UPDATE '.$table.' SET '.$sql.' ReqTxfrTS=Now() WHERE '.$txnidname.'='.$_GET[$txnidname];
      $stmt=$link->prepare($sql); $stmt->execute();
      header('Location:defectivemonitor.php');
      break;
}
  $link=null; $stmt=null;    
?>