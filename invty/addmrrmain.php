<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

$showbranches=true; include_once('../switchboard/contents.php');
 

$showbranches=true;
    $method='POST';
// $txntype=$_GET['w'];
$whichqry=$_GET['w'];


if ($whichqry=='StoreUsed'){ $allowed=697;  } else { $allowed=695;  }
if(!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit;}

$user=$_SESSION['(ak0)'];
switch ($whichqry){
    case 'MRR':
        $title='Add MRR';
        $columnnames=array(
                    array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'MRRNo', 'type'=>'text','size'=>10, 'required'=>true),
               //     array('field'=>'SupplierNo','type'=>'text','size'=>10,'required'=>true,'list'=>'suppliers'),
                    array('field'=>'ForPONo', 'type'=>'text','size'=>10, 'required'=>true,'list'=>'openpo'),
					array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false),
					array('field'=>'SuppDRNo', 'caption'=>'Delivery Receipt No', 'type'=>'text','size'=>10, 'required'=>false),
					 array('field'=>'SuppDRDate', 'caption'=>'Supplier DR Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'SuppInvNo', 'caption'=>'Supplier Invoice No', 'type'=>'text','size'=>10, 'required'=>false),
                    array('field'=>'SuppInvDate', 'caption'=>'Supplier Inv Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d'))
                    
                           );
        $liststoshow=array();//'suppliers'
        $listcondition='';
        $whichotherlist='invty';
        $otherlist=array('externalitems','openpo');
        $txntype=6;
          break;

         
case 'PurchaseReturn': 
        $title='Add Purchase Return';
        $columnnames=array(
                    array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'SupplierNo','type'=>'text','size'=>10,'required'=>true,'list'=>'suppliers'),
                    array('field'=>'txntype', 'type'=>'hidden', 'size'=>0,'value'=>'8')
                           );
        $liststoshow=array('suppliers');
        $txntype=8;
          break;

case 'StoreUsed': 
        $title='Step 1. Request Approval to Use Inventory Items';
        $liststoshow=array();
        
        $columnnames=array();   
         $columnnames[]=array('field'=>'ItemCode', 'type'=>'text','size'=>20,'required'=>true,'autofocus'=>true);
         $columnnames[]=array('field'=>'Qty', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0);
         $columnnames[]=array('field'=>'Reason', 'type'=>'text','size'=>10, 'required'=>false);
         $columnnames[]=array('field'=>'SerialNo', 'type'=>'text','size'=>10, 'required'=>false);
         $action='praddmrr.php?w=StoreUsedRequestApp&txntype=9&po=StoreUsed';
         
      include('../backendphp/layout/inputmainform.php');
      
      $title='<br><br>Step 2. Wait for Approval from Supply Chain'; $formdesc='Approval is PER ITEM';
      $columnnames=array('ItemCode','Category','ItemDesc','Qty','Reason','EncodedBy','TimeStamp');
        if (allowedToOpen(6971,'1rtc')){
        $condition='';
        $txnidname='ApprovalId';
        $editprocess='prinvapproval.php?w=ApproveSU&action_token='.$_SESSION['action_token'].'&ApprovalId=';
        $editprocesslabel='Approve';
        $columnnames[]='Branch';
     } else {
        $condition=' and su.BranchNo='.$_SESSION['bnum'];
     }
      $sql='SELECT su.*, ItemDesc,Category,Branch, Nickname as EncodedBy FROM approvals_2storeused su join invty_1items i on su.ItemCode=i.ItemCode join invty_1category c on c.CatNo=i.CatNo join `1branches` b on b.BranchNo=su.BranchNo join `1employees` e on e.IDNo=su.EncodedByNo where Approved=0'.$condition;
      $delprocess='prinvapproval.php?w=DeleteSU&action_token='.$_SESSION['action_token'].'&TxnID=';
      $txnidname='ApprovalId';
      include('../backendphp/layout/displayastablewithedit.php');
      
      $title='<br><br>Step 3. Record Store Used in Inventory'; $formdesc='All store used per day will be encoded together in ONE main form.';
      $columnnames=array('ItemCode','Category','ItemDesc','Qty','Reason','EncodedBy','TimeStamp','ApprovedBy','ApprovalTS');
        if (allowedToOpen(6971,'1rtc')){
        $condition='';  $editprocesslabel=null;   $editprocess=null;
        $columnnames[]='Branch';
     } else {
        $condition=' and su.BranchNo='.$_SESSION['bnum'];
        $txnidname='ApprovalId';
        $editprocess='prinvapproval.php?w=RecordSU&action_token='.$_SESSION['action_token'].'&ApprovalId=';
        $editprocesslabel='Record';
     }
      $sql='SELECT su.*, ItemDesc,Category,Branch, e.Nickname as EncodedBy, e1.Nickname as ApprovedBy FROM approvals_2storeused su join invty_1items i on su.ItemCode=i.ItemCode join invty_1category c on c.CatNo=i.CatNo join `1branches` b on b.BranchNo=su.BranchNo join `1employees` e on e.IDNo=su.EncodedByNo join `1employees` e1 on su.ApprovedByNo=e1.IDNo where Approved like \'1%\' '.$condition;
      include('../backendphp/layout/displayastablewithedit.php');
      
      goto StoreUsed;
      break;
}
         
    $action='praddmrr.php?w='.$whichqry.'&txntype='.$txntype;
    include('../backendphp/layout/inputmainform.php');
StoreUsed:
      $link=null; $stmt=null;
    ?>
