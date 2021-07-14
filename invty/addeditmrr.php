<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$allowed=array(6922,6923);
if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit();}
$showbranches=true; include_once('../switchboard/contents.php');
 
include_once('../backendphp/functions/editok.php');

include_once('../backendphp/layout/showencodedbybutton.php');
$method='POST';
$txnid=intval($_REQUEST['TxnID']);

 //to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="EDDBFF";
        $rcolor[1]="FFFFFF";

$whichqry=$_GET['w'];

//switch ($whichqry){
//    case 'MRR':
      $sql='Select txntype from invty_2mrr where TxnID='.$txnid;
      $stmt=$link->query($sql);
      $result=$stmt->fetch();
      $txntype=$result['txntype'];
      
   if ($txntype==9){ //store used
       if (!allowedToOpen(6922,'1rtc')) { echo 'No permission'; exit;}
   } else {
       if (!allowedToOpen(6923,'1rtc')) { echo 'No permission'; exit;}
   }
    
      $title='Add/Edit '. ($txntype==9?'Store Used':'MRR');//($txntype==8?'Purchase Return<br><i>Qty must be NEGATIVE</i>':
      $formdesc=($txntype==9?'Qty must be NEGATIVE':'');
        
    $sqlmain='select m.*,  CASE WHEN SuppInvNo = "" THEN "Get info from supplier"  WHEN SuppInvNo is null THEN "Get info from supplier" ELSE SuppInvNo END as `SupplierInvoiceNo`,SuppDRNo as `SupplierDeliveryReceiptNo`, s.SupplierName, b.Branch as RequestingBranch, CompanyName, e.Nickname as EncodedBy from invty_2mrr m 
        left join `1suppliers` as s on s.SupplierNo=m.SupplierNo
        left join `1companies` as co on co.CompanyNo=m.RCompany
        join `1branches` as b on m.BranchNo=b.BranchNo left join `1employees` as e on m.EncodedByNo=e.IDNo where (m.txntype=6 or m.txntype=8) and TxnID='.$txnid
        .' union select m.*,ifnull(SuppInvNo,"Get information from supplier") as `SupplierInvoiceNo`,SuppDRNo as `SupplierDeliveryReceiptNo`, b.Branch as SupplierName, b.Branch as RequestingBranch,  CompanyName, e.Nickname as EncodedBy from invty_2mrr m 
        join `1branches` b on b.BranchNo=m.BranchNo left join `1companies` as co on co.CompanyNo=m.RCompany left join `1employees` as e on m.EncodedByNo=e.IDNo where txntype=9 and m.BranchNo='.$_SESSION['bnum'].' and TxnID='.$txnid;
    
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    
    $main='';
    $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Category, ItemDesc');
    $listcondition=$result['ForPONo'];
    if (editOk('invty_2mrr',$txnid,$link,$txntype)){
        if ($txntype<>9){ // not store used
        $editmain='<td><a href="editmrrspecifics.php?edit=2&w=MRRMainEdit&txntype='.$txntype.'&TxnID='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=invty_2mrr&l=invty OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editsub=true;
        switch ($txntype){
            case 8: // purchase return
                $columnstoedit=array('ItemCode','Qty','SerialNo','UnitCost');
                break;
            default: //mrr
                $columnstoedit=array('Qty','SerialNo');
                break;
        }
       // $columnstoedit=$txntype<>8?array('ItemCode','Qty','SerialNo'):array('ItemCode','Qty','SerialNo','UnitCost');
        } else {
        $editmain='<td>'.((allowedToOpen(6924,'1rtc'))?'<a href="editmrrspecifics.php?edit=2&w=MRRMainEdit&txntype='.$txntype.'&TxnID='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=invty_2mrr&l=invty OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'</td>');
        $editsub=(allowedToOpen(6924,'1rtc'))?true:false;
        }
        $editmain=$editmain.($result['Posted']==1?'<td><a href="javascript:window.print()">Print</a></td>':'');
        $columnnamesmain=array('Date','SupplierName','SupplierInvoiceNo','SupplierDeliveryReceiptNo','MRRNo','Remarks','ForPONo','RequestingBranch','CompanyName');
        $columnsub=array('ItemCode','Category','ItemDesc','Qty','Unit','SerialNo');
    } else {
        $editmain='';
        $editsub=false;
        $columnnamesmain=array('Date','SupplierName','SupplierInvoiceNo','SupplierDeliveryReceiptNo','MRRNo','Remarks','ForPONo','RequestingBranch','CompanyName','Posted');
        $columnsub=array('ItemCode','Category','ItemDesc','Qty','Unit','SerialNo');
        $columnstoedit=array();
    }
    //if ($txntype==8){ $columnsub[]='DefectiveOrGoodOrForCheckUp'; $columnsub[]='Decision'; $columnsub[]='DecisionRefNo';}
    $editok=$editsub;
    
    if ($showenc==1) { array_push($columnnamesmain,'TimeStamp','EncodedBy','PostedByNo'); array_push($columnsub,'TimeStamp','EncodedBy');}
      else {$columnnamesmain=$columnnamesmain; $columnsub=$columnsub;}
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $showall=isset($_GET['showall']);
    $main='<table><tr>'.$main.$editmain.'<tr></table>';
    if ($txntype==6){
    $sqllookup='Select ud.*, c.Category, i.ItemDesc, i.Unit from `invty_41supplierundelivered` ud join invty_1items i on i.ItemCode=ud.ItemCode join invty_1category c on c.CatNo=i.CatNo where ud.SupplierUndelivered<>0 and BranchNo='.$_SESSION['bnum'].' and ud.PONo=\''.$listcondition.'\' order by Category, ud.ItemCode';
    $stmt=$link->query($sqllookup);
    $resultlookup=$stmt->fetchAll();
    $columnslookup=array('ItemCode','Category','ItemDesc','Unit','Ordered','Received','SupplierUndelivered');
    $lookup='';$lookupcol='<table><tr>';
    foreach ($columnslookup as $collookup){ //column names
        $lookupcol=$lookupcol.'<td><font face="arial" size="2">'.$collookup.'</font></td>';
    }
    foreach($resultlookup as $rowlookup){
      $colorcount++;
        $lookup=$lookup.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnslookup as $collookup){
            $lookup=$lookup.'<td>'.$rowlookup[$collookup].'</td>';
        }
        $lookup=$lookup.'<td><a href="praddmrr.php?w=MRRAutoEnter&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'&txntype='.$txntype.'&po='.$listcondition.'&ItemCode='.$rowlookup['ItemCode'].'">Accept</a></td></tr>';
    }
    $lookup='<b><font color="blue">Purchase Order: </font></b><br>'.$lookupcol.'</tr>'.$lookup.'</table><br>';
    } else{
      $lookup='';
    }
        
 /*   if($txntype==8){ $sqlsub='Select s.*, s.UnitCost*s.Qty as Amount, c.Category, i.ItemDesc, i.Unit, If(Defective=1,"Defective",If(Defective=2,"ForCheckUp","Good Item")) as DefectiveOrGoodOrForCheckUp,CASE WHEN DecisionNo=1 THEN "Credit Memo" WHEN DecisionNo=2 THEN "Rejected" WHEN DecisionNo=3 THEN "Replaced" ELSE "" END AS Decision, DecisionRefNo, e1.Nickname as EncodedBy from invty_2mrrsub s LEFT JOIN invty_2prdecision pd ON s.TxnSubId=pd.TxnSubId join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e1 on s.EncodedByNo=e1.IDNo join invty_2mrr m on m.TxnID=s.TxnID where m.TxnID='.$txnid.' Order By '.$sortfield;
    } else {*/
    $sqlsub='Select s.*, s.UnitCost*s.Qty as Amount, c.Category, i.ItemDesc, i.Unit, e1.Nickname as EncodedBy from invty_2mrrsub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e1 on s.EncodedByNo=e1.IDNo join invty_2mrr m on m.TxnID=s.TxnID where m.TxnID='.$txnid.' Order By '.$sortfield;
   // }
    
  //  $caption=($txntype==8?'Purchase Return':($txntype==9?'Store Used':'Received Items:'));
  $caption=($txntype==9?'Store Used':'Received Items:');
  //  $sub='<b><font color="blue">'.$caption.'</font></b><br><table><tr>'.$subcol.'<td>Edit?</td></tr><tbody>'.$sub.'</tbody></table>';
    $sqlsum='Select count(s.ItemCode) as LineItems,sum(UnitCost*Qty) as Total from  `invty_2mrrsub` s 
join `invty_2mrr` m on m.TxnID=s.TxnID
join `1branches` b on b.BranchNo=m.BranchNo
Where m.TxnID='.$txnid.' Group By m.MRRNo';
   
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='Line Items:  '.number_format($result['LineItems'],0);
    if (allowedToOpen(6925,'1rtc')) {
   $total=$total.'&nbsp &nbsp &nbsp &nbsp Total Value:  '.number_format($result['Total'],2);
} 
    $total=$total.'&nbsp &nbsp &nbsp &nbsp<a href="addmrrmain.php?w='. ($txntype==9?'StoreUsed':'MRR').'">Add New '. ($txntype==9?'Store Used':'MRR').'</a><br>';
    if ($txntype==6){ // MRR
$itemslist='mrritemsperpo';
$liststoshow=array();
$whichotherlist='invty';
$otherlist=array('mrritemsperpo');
/*} elseif ($txntype==8) { //Purchase Return
$itemslist='';
$liststoshow=array();*/
} else {// Store Used
   $columnnames=array(); $liststoshow=array();
   goto info_for_posting;
}
   
   $columnnames=array();   
   $columnnames[]=array('field'=>'ItemCode', 'type'=>'text','size'=>20,'required'=>true,'list'=>$itemslist,'autofocus'=>true);
   $columnnames[]=array('field'=>'Qty', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0);
   $columnnames[]=array('field'=>'SerialNo', 'type'=>'text','size'=>10, 'required'=>false);
   /*if($txntype==8){$columnnames[]=array('field'=>'UnitCost', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0);
                  $columnnames[]=array('field'=>'Defective', 'caption'=>'DEFECTIVE ', 'type'=>'radio','size'=>10, 'value'=>1,'required'=>FALSE);
                  $columnnames[]=array('field'=>'Defective', 'caption'=>'GOOD ITEM ', 'type'=>'radio','size'=>10, 'value'=>0,'required'=>FALSE);
				   $columnnames[]=array('field'=>'Defective', 'caption'=>'ForCheckUp ', 'type'=>'radio','size'=>10, 'value'=>2,'required'=>FALSE);}*/
   $columnnames[]=array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid);
    
        
    $action='praddmrr.php?w=MRRSubAdd&TxnID='.$txnid.'&txntype='.$txntype.'&po='.$listcondition;


   info_for_posting:
    $postvalue='1';
    $table='invty_2mrr';
    
//        break;
//}
    
     
 //   if ($editok and (allowedToOpen(69251,'1rtc'))){$columnstoedit[]='UnitCost';$columnsub[]='UnitCost';};
     $editprocess='praddmrr.php?txntype='.$txntype.'&w=MRRSubEdit&TxnID='.$txnid.'&TxnSubId='; $editprocesslabel='Enter';
    $delprocess='..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=invty_2mrrSub&l=invty&TxnSubId=';
    $txnsubid='TxnSubId'; $showgrandtotal=true; $coltototal='Amount';
    $left='90%'; $leftmargin='91%'; $right='9%';
    $withsub=true;include('../backendphp/layout/inputsubform.php');
      $link=null; $stmt=null;
?>