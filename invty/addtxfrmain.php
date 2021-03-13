<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed 
$allowed=array(703,704,705,706,707); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=true; include_once('../switchboard/contents.php');
 ;


    $method='POST';
$txntype=$_GET['w'];
$whichqry=$_GET['w'];

  

$user=$_SESSION['(ak0)'];
switch ($whichqry){
    case 'Out':
        $title='Add Transfer OUT';
        $columnnames=array(
                    array('field'=>'DateOut', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d'),'readonly'=>'readonly'),
                    array('field'=>'TransferNo', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'ToBranch', 'caption'=>'To Branch:','type'=>'text','size'=>10,'required'=>true,'list'=>'branches'),
                    array('field'=>'ForRequestNo', 'type'=>'text','size'=>20, 'required'=>true,'list'=>'undeliveredrequestsOUT'),
                    array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false),
                    array('field'=>'txntype', 'type'=>'hidden', 'size'=>0,'value'=>'4')
                    );
        $liststoshow=array('branches','undeliveredrequestsOUT');
          break;
    case 'In':
        $title='Add Transfer IN';
        $columnnames=array(
                    array('field'=>'DateIn', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'DateOut', 'type'=>'hidden','size'=>0,'value'=>date('Y-m-d')),
                    array('field'=>'TransferNo', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'BranchNo','caption'=>'From Branch','type'=>'text','size'=>10,'required'=>true,'list'=>'branches'),
                    array('field'=>'ForRequestNo', 'type'=>'text','size'=>20, 'required'=>true,'list'=>'undeliveredrequestsIN'),
                    array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false),
                    array('field'=>'txntype', 'type'=>'hidden', 'size'=>0,'value'=>'4')
                           );
        $liststoshow=array('branches','undeliveredrequestsIN');
          break;
    case 'AcceptIn':
	$title='Accept Transfer';
        $sql1='SELECT concat(\'<a href="praddtxfr.php?w=AcceptIn&action_token='.$_SESSION['action_token'].'&txntype=AcceptIn&TransferNo=\',TransferNo,\'">Set Date IN as Today</a>\') as AccepTransfer,TxnID,CONCAT("DateOut: ",DateOut) as DateOut,CONCAT("DateIN: ",ifnull(DateIN,"")) as DateIN,
		CONCAT("TransferNo: ",TransferNo) as TransferNo,CONCAT("ForRequestNo: ",ForRequestNo) as ForRequestNo,CONCAT("FROMBranch: ",b1.Branch) as FROMBranch, CONCAT("ToBranch: ",b2.Branch) as TOBranch, CONCAT("Remarks: ",Remarks) as Remarks, CONCAT("Waybill: ",ifnull(Waybill,"")) as Waybill,CONCAT("Posted: ",Posted) as Posted,CONCAT("PostedIn: ",PostedIn) as PostedIn FROM invty_2transfer t
		join `1branches` as b1 on b1.BranchNo=t.BranchNo
		join `1branches` as b2 on b2.BranchNo=t.ToBranchNo
		where ToBranchNo='.$_SESSION['bnum'].' and DateIN is null;';
		
		$sql2='Select ts.ItemCode,Category,ItemDesc,Unit,QtySent,UnitPrice,UnitPrice*QtySent as AmountSent,QtyReceived,UnitCost,UnitCost*QtyReceived as AmountReceived,SerialNo,
		if(ts.Defective=1,"Defective",if(ts.Defective=2,"ForCheckup","Good Item")) as Defective
		from invty_2transfersub ts left join invty_1items i on i.ItemCode=ts.ItemCode left join invty_1category c on c.CatNo=i.CatNo';
	
	$groupby='TxnID';
    $orderby='';
    $columnnames1=array('DateOut','DateIN','TransferNo','ForRequestNo','FROMBranch','TOBranch','Remarks','Waybill','Posted','PostedIn','AccepTransfer');
    $columnnames2=array('ItemCode','Category','ItemDesc','Unit','QtySent','UnitPrice','AmountSent','QtyReceived','UnitCost','AmountReceived','SerialNo','Defective');
	
	include('../backendphp/layout/displayastablewithsub.php');
	exit();
          break;
    case 'Request':
        $title='Add Request';
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        echo comboBox($link,'SELECT BranchNo,Branch from `1branches` where Active=1 AND Pseudobranch<>1 ORDER BY Branch','BranchNo','Branch','allbranches');
        $columnnames=array(
                    array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    //array('field'=>'RequestNo', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'SupplierBranchNo','type'=>'text','size'=>10,'required'=>true,'list'=>'allbranches'),
                    array('field'=>'DateReq', 'caption'=>'Date Required', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false),
                    array('field'=>'Defective', 'caption'=>'Check if items are : GOOD ', 'type'=>'radio','size'=>10, 'value'=>0, 'checked'=>TRUE,'required'=>FALSE),
                        array('field'=>'Defective', 'caption'=>'DEFECTIVE ', 'type'=>'radio','size'=>10, 'value'=>1,'required'=>FALSE),					
					array('field'=>'Defective', 'caption'=>'FOR CHECKING ', 'type'=>'radio','size'=>10, 'value'=>2,'required'=>FALSE)
                           );
        $liststoshow=array();
          break;
        
    case 'Repack':
        $title='Encode Repacked Items';
        $columnnames=array(
                    array('field'=>'DateofRepack', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'txntype', 'type'=>'hidden', 'size'=>0,'value'=>'12')
                            );
        $liststoshow=array();
          break;
   
}
         
    $action='praddtxfr.php?w='.$whichqry.'&txntype='.$txntype;
    include('../backendphp/layout/inputmainform.php');
      $link=null; $stmt=null;
    ?>
