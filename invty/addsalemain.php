<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6926,'1rtc')) {   echo 'No permission'; exit;}  
$showbranches=true; include_once('../switchboard/contents.php');

$method='POST';
$saletype=$_GET['saletype'];
$whichqry=$_GET['w'];

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT e.`IDNo`, concat(e.`Nickname`," ", e.`SurName`) AS SoldBy from (`1employees` e JOIN `attend_30currentpositions` p ON (e.`IDNo` = p.`IDNo`)) where p.`PositionID` IN (32,33,37,38,81) AND p.BranchNo='.$_SESSION['bnum'].' UNION SELECT e.`IDNo`, concat(e.`Nickname`," ", e.`SurName`) AS SoldBy from (`1employees` e JOIN `attend_30currentpositions` p ON (e.`IDNo` = p.`IDNo`)) JOIN attend_2attendance a ON e.IDNo=a.IDNo where p.`PositionID` IN (32,33,37,38,81) AND a.BranchNo='.$_SESSION['bnum'].' AND DateToday=CURDATE()','IDNo','SoldBy','branchpersonnel');

$sqltl='Select TeamLeader FROM attend_1branchgroups where BranchNo='.$_SESSION['bnum'];
    $stmttl=$link->query($sqltl);
    $resulttl=$stmttl->fetch();
    
switch ($saletype){
    case '1': //Cash
		$addltitle='Cash';
		goto here;
    case '32': //GCash
		$addltitle='GCash';
		$txntype=1;
		goto here;
    case '33': //Paymaya
		$addltitle='Paymaya';
		$txntype=1;
		goto here;
    case '2': //Charge
		$addltitle='Charge';
		goto here;
    case '3': //Invty Charges
		$addltitle='Invty Charges';
		goto here;
    case '10': //DR
		$addltitle='Delivery Receipt';
		here:
	
        $clientlist=($saletype==2?'arclients':($saletype==3?'employeesforlist':'clientsnodatedcheck'));
        
        
        if($saletype==3){ $allowed=6928; } else { $allowed=6927; }
        if($_SESSION['bnum']==999){ $allowed=999;}    
        if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit;}    
	
    $title='Add Sale ('.$addltitle.')';
    
    $columnnames=array(
                    array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'SaleNo', 'caption'=>'InvNo','type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'Client','caption'=>'Client','type'=>'text','size'=>10,'required'=>true,'list'=>$clientlist),
                    array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false),
                    array('field'=>'PaymentType', 'type'=>'hidden','size'=>0, 'value'=>($saletype<>10?$saletype:1),'list'=>'paytype'),
                    array('caption'=>'GCash/Paymaya or CheckDetails','field'=>'CheckDetails', 'type'=>'text','size'=>20, 'required'=>false),
                    array('field'=>'DateofCheck', 'type'=>'date','size'=>20,'value'=>date('Y-m-d'),'required'=>false),
                    array('field'=>'PONo', 'type'=>'text','size'=>20, 'required'=>false),
                    array('field'=>'SoldBy', 'type'=>'text','size'=>20, 'required'=>true, 'list'=>'branchpersonnel'),
                    array('field'=>'TeamLeader', 'type'=>'hidden','size'=>0, 'required'=>false, 'value'=>$resulttl['TeamLeader'], 'list'=>'teamleaders'),
                    array('field'=>'txntype', 'type'=>'hidden', 'size'=>0,'value'=>(($saletype==32 OR $saletype==33)?$txntype:$saletype))
                    );
    
    $action='praddsale.php?w=SaleMain';
    $liststoshow=array('paytype',$clientlist);
    
    include('../backendphp/layout/inputmainform.php');
    
     break;
    case '5': //Return 
        
if (!allowedToOpen(6935,'1rtc')) {   echo 'No permission'; exit;}  
      
    include_once $path.'/acrossyrs/commonfunctions/renderspeciallist.php';
     $listname='teamleaders'; $listvalue='IDNo'; $listlabel='TeamLeader';
     $listsql='select e.`IDNo`, concat(e.`Nickname`," ", e.`SurName`) AS TeamLeader from (`1employees` e join `attend_30currentpositions` p ON (e.`IDNo` = p.`IDNo`)) where (p.`PositionID` = 36)';
    // include_once('../backendphp/functions/renderspeciallist.php');
     genericList($listsql,$link,$listname,$listvalue,$listlabel);
         $fieldsinrow=3;
    $clientlist=(isset($_GET['auditreturn'])?'employees':'clients');
    if (isset($_GET['auditreturn'])){
    $title='New Employee Return';
    $columnnames=array(
                    array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'SaleNo', 'caption'=>'CRSNo','type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'Client','caption'=>'Client','type'=>'text','size'=>10,'required'=>true,'list'=>$clientlist),
                    array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false),
                    array('field'=>'PaymentType', 'type'=>'hidden','size'=>10, 'value'=>3),
                    array('field'=>'CheckDetails', 'caption'=>'OldInvoiceNo','type'=>'text','size'=>20, 'required'=>true),
                    array('field'=>'DateofCheck', 'caption'=>'OldInvoiceDate','type'=>'date','size'=>20,'value'=>date('Y-m-d'),'required'=>true),
                    array('field'=>'PONo', 'type'=>'text','caption'=>'ApprovalNo','size'=>10, 'required'=>(isset($_GET['auditreturn'])?false:true)),
                    array('field'=>'TeamLeader', 'type'=>'hidden','size'=>20, 'required'=>false, 'value'=>$resulttl['TeamLeader'], 'list'=>'teamleaders'),
                    array('field'=>'txntype', 'type'=>'hidden', 'size'=>0,'value'=>3)
                    );
    $action='praddsale.php?w=SaleMain'.(isset($_GET['auditreturn'])?'&auditreturn=true':'');
    $liststoshow=array($clientlist);
     include('../backendphp/layout/inputmainform.php');
    } else { //customer returns
        $columnnames=array(
                    array('field'=>'SaleNo', 'caption'=>'Old Invoice No','type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'txntype', 'caption'=>'Invoice Type', 'type'=>'text','size'=>6, 'required'=>true,  'list'=>'txntype'),
                    array('field'=>'ItemCode', 'type'=>'text','size'=>6, 'required'=>true),
                    array('field'=>'Qty', 'type'=>'text','size'=>6, 'required'=>true),
                    array('field'=>'Year', 'caption'=>'Check if from last year  ', 'type'=>'checkbox','size'=>10, 'value'=>1,'required'=>FALSE),
                    array('field'=>'Reason', 'type'=>'text','size'=>60, 'required'=>true),
                    array('field'=>'Defective', 'caption'=>'DEFECTIVE ', 'type'=>'radio','size'=>10, 'value'=>1,'required'=>FALSE),
					array('field'=>'Defective', 'caption'=>'ForCheckUp ', 'type'=>'radio','size'=>10, 'value'=>2,'required'=>FALSE),
                    array('field'=>'Defective', 'caption'=>'GOOD ITEM ', 'type'=>'radio','size'=>10, 'value'=>0,'required'=>FALSE)
                    );
    $action='prinvapproval.php?w=NewCRSApproval';
    $liststoshow=array();
    $title='Step 1. Request for Approval for Customer Return '.str_repeat('&nbsp;',15).' OR '.str_repeat('&nbsp;',15).' <a href="addsalemain.php?w=Cancel&saletype=cancel5">Click here for cancelled SRS.</a></h3><i>Please separate Defective Items from Good Items.</i><h3>';
    ; $listname='txntype'; $listvalue='txndesc'; $listlabel='txntypeid';
     $listsql='select txntypeid, txndesc from invty_0txntype where txntypeid in (1,2,10,5)';
     genericList($listsql,$link,$listname,$listvalue,$listlabel);
    
    
     include('../backendphp/layout/inputmainform.php');
     echo '<br><br>';
     $title='Step 2. Wait for Approval from Supply Chain';
     $columnnames=array('SaleNo','ItemCode','Qty','UnitPrice','AmountofReturn','Reason','DefectiveOrGoodorForCheckUp','EncodedBy','TimeStamp');
     if (allowedToOpen(6936,'1rtc')){
        $formdesc='If you allow ONE item in a receipt, you accept ALL for those in the receipt.<br>';
        $condition='';
        $editprocess='prinvapproval.php?w=ApproveCRS&action_token='.$_SESSION['action_token'].'&ApprovalId=';
        $editprocesslabel='Approve';
        $columnnames[]='Branch';
     } else {
        $condition='and sr.BranchNo='.$_SESSION['bnum'];
     }
     
        $sql='Select b.Branch,sm.SaleNo,sr.*, (Select UnitPrice from invty_2salesub ss where ss.TxnID=sm.TxnID and ss.ItemCode=sr.ItemCode limit 1) as UnitPrice, sr.AmountofReturn,e.Nickname as EncodedBy, if(Defective=1,"Defective",if(Defective=2,"ForCheckup","Good Item")) as DefectiveOrGoodorForCheckUp from `approvals_2salesreturns` sr join invty_2sale sm on sm.TxnID=sr.TxnID 
        left join `1employees` e on sr.EncodedByNo=e.IDNo
        join `1branches` b on b.BranchNo=sr.BranchNo
        where sr.Approval is null '.$condition;
        
        $sql=$sql.' UNION ALL Select b.Branch,sm.SaleNo,sr.*, (Select UnitPrice from `'.$lastyr.'_1rtc`.`invty_2salesub` ss where ss.TxnID=sm.TxnID and ss.ItemCode=sr.ItemCode) as UnitPrice, sr.AmountofReturn,e.Nickname as EncodedBy, if(Defective=1,"Defective",if(Defective=2,"ForCheckup","Good Item")) as DefectiveOrGoodorForCheckUp from `approvals_2salesreturns` sr join `'.$lastyr.'_1rtc`.`invty_2sale` sm ON (Select CONCAT("'.substr($lastyr,-2).'00",sm.TxnID))=sr.TxnID
        left join `1employees` e on sr.EncodedByNo=e.IDNo
        join `1branches` b on b.BranchNo=sr.BranchNo
        where sr.Approval is null AND sr.LastYr=1 '.$condition;
        $txnid='ApprovalId'; $txnidname='ApprovalId';
        $delprocess='prinvapproval.php?w=DeleteCRS&action_token='.$_SESSION['action_token'].'&ApprovalId=';
        
        include('../backendphp/layout/displayastablewithedit.php');
        echo '<br><br>';
     $title='Step 3. Record Approved Customer Returns';
     $formdesc='Cash returns must go through the encashment process.  Approval number is DIFFERENT.<br>';
		$sql='Select sm.SaleNo, sr.*, (Select UnitPrice from invty_2salesub ss where ss.TxnID=sm.TxnID and ss.ItemCode=sr.ItemCode) as UnitPrice, if(Defective=1,"Defective",if(Defective=2,"ForCheckup","Good Item")) as DefectiveOrGoodorForCheckUp, e.Nickname as EncodedBy, e1.Nickname as ApprovedBy from `approvals_2salesreturns` sr join invty_2sale sm on sm.TxnID=sr.TxnID join `1employees` e on sr.EncodedByNo=e.IDNo join `1employees` e1 on sr.ApprovedByNo=e1.IDNo where (sr.Approval is not null) and sr.TxnID<>1 AND (sr.Approval not in (Select PONo from invty_2sale where txntype=5 AND PONo IS NOT NULL)) '.$condition;
        // $sql='Select sm.SaleNo, sr.*, (Select UnitPrice from invty_2salesub ss where ss.TxnID=sm.TxnID and ss.ItemCode=sr.ItemCode) as UnitPrice, if(Defective=1,"Defective",if(Defective=2,"ForCheckup","Good Item")) as DefectiveOrGoodorForCheckUp, e.Nickname as EncodedBy, e1.Nickname as ApprovedBy from `approvals_2salesreturns` sr join invty_2sale sm on sm.TxnID=sr.TxnID join `1employees` e on sr.EncodedByNo=e.IDNo join `1employees` e1 on sr.ApprovedByNo=e1.IDNo where (sr.Approval is not null) and (sr.Approval not in (Select PONo from invty_2sale where txntype=5)) '.$condition;// and sr.BranchNo='.$_SESSION['bnum'];
       
        $sql=$sql.' UNION ALL Select sm.SaleNo, sr.*, (Select UnitPrice from `'.$lastyr.'_1rtc`.`invty_2salesub` ss where ss.TxnID=sm.TxnID and ss.ItemCode=sr.ItemCode) as UnitPrice, if(Defective=1,"Defective",if(Defective=2,"ForCheckup","Good Item")) as DefectiveOrGoodorForCheckUp, e.Nickname as EncodedBy, e1.Nickname as ApprovedBy from `approvals_2salesreturns` sr join `'.$lastyr.'_1rtc`.`invty_2sale` sm ON (Select CONCAT("'.substr($lastyr,-2).'00",sm.TxnID))=sr.TxnID join `1employees` e on sr.EncodedByNo=e.IDNo join `1employees` e1 on sr.ApprovedByNo=e1.IDNo where (sr.Approval is not null  AND sr.LastYr=1) and (sr.Approval not in (Select PONo from invty_2sale where txntype=5 AND PONo IS NOT NULL)) '.$condition;
        //echo $sql;break;
        
        $sql=$sql.' UNION ALL Select sm.SaleNo, sr.*, (Select UnitPrice from `'.$last2yrs.'_1rtc`.`invty_2salesub` ss where ss.TxnID=sm.TxnID and ss.ItemCode=sr.ItemCode) as UnitPrice, if(Defective=1,"Defective",if(Defective=2,"ForCheckup","Good Item")) as DefectiveOrGoodorForCheckUp, e.Nickname as EncodedBy, e1.Nickname as ApprovedBy from `approvals_2salesreturns` sr join `'.$last2yrs.'_1rtc`.`invty_2sale` sm ON (Select CONCAT("'.substr($last2yrs,-2).'00",sm.TxnID))=sr.TxnID join `1employees` e on sr.EncodedByNo=e.IDNo join `1employees` e1 on sr.ApprovedByNo=e1.IDNo where (sr.Approval is not null  AND sr.LastYr=2) and (sr.Approval not in (Select PONo from invty_2sale where txntype=5)) '.$condition;
        //echo $sql;break;
        $columnnames=array('SaleNo','ItemCode','Qty','UnitPrice','AmountofReturn','Reason','DefectiveOrGoodorForCheckUp','Approval','EncodedBy','TimeStamp','ApprovedBy','ApprovalTS');
        if (allowedToOpen(6937,'1rtc')){
            $editprocess=null;$editprocesslabel=null;
            $formprocess='prinvapproval.php'; $submitlabel='Record this CRS';
            $formprocessfields='<input type=text name=CRSNo size=6 required=true>
                    <input type=hidden name=w value="RecordCRS"><input type=hidden name=action_token value="'.$_SESSION['action_token'].'">';
        }
        $txnidname='ApprovalId';
        include('../backendphp/layout/displayastable.php');
    }
    
      break;
	  
	  
	  case 'cancel5':
	  $title='Cancellation of SRS';
		echo '<title>'.$title.'</title>';
		echo '<h3>1. '.$title.''.str_repeat('&nbsp;',15).' OR '.str_repeat('&nbsp;',15).' <a href="addsalemain.php?w=Return&saletype=5">New Customer Return.</a></h3><br>';
		
		echo '<form action="prinvapproval.php?w=AddCancelSRS" method="POST"><b>SRSNo:</b> <input type="text" value="" name="SRSNo" autocomplete="off"> <b>Reason:</b> <i>Cancelled SRS-</i><input type="text" value="" name="Reason" autocomplete="off" size="50" placeholder="reason here"><input type=hidden name=action_token value="'.$_SESSION['action_token'].'"> <input type="submit" name="btnSubmit" value="Add Request"></form>';
		
		$columnnames=array('SRSNo','Reason','EncodedBy','TimeStamp');
		if (allowedToOpen(6936,'1rtc')){
			$condition='';
			$editprocess='prinvapproval.php?w=ApproveCancelSRS&action_token='.$_SESSION['action_token'].'&ApprovalId=';
			$editprocesslabel='Approve';
			$columnnames[]='Branch';
		 } else {
			$condition='and sr.BranchNo='.$_SESSION['bnum'];
		 }
		 
		  $sql='Select ApprovalId,Reason,sr.TimeStamp,b.Branch,e.Nickname as EncodedBy,Approval AS SRSNo from `approvals_2salesreturns` sr
			join `1employees` e on sr.EncodedByNo=e.IDNo
			join `1branches` b on b.BranchNo=sr.BranchNo
			where ItemCode=0 AND ApprovedByNo IS NULL '.$condition;
			
			echo '<br><br><h3>2. SRS Request Lists</h3><i>Only Requester can delete.</i>';
			$title='';
			
			$txnid='ApprovalId'; $txnidname='ApprovalId';
			$delprocess='prinvapproval.php?w=DeleteCancelSRS&ApprovalId=';
			include('../backendphp/layout/displayastablewithedit.php');
		
	  
	  break;
            
     }
     
      $link=null; $stmt=null;
    ?>
