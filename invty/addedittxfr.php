<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed 
$allowed=array(703,704,705,706,707,6931,6933,6934,70311); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=true; include_once('../switchboard/contents.php');
include_once('../backendphp/functions/editok.php');

if(isset($_REQUEST['TxnID'])){
	include_once('../backendphp/layout/showencodedbybutton.php');
	$txnid=intval($_REQUEST['TxnID']);
}
$showbranches=false;
$method='POST';




 //to make alternating rows have different colors
        $colorcount=0;
        $color1="DBDBFF"; $color2="FFFFFF";
        // remove this when all are direct edits into cell
        $rcolor[0]="DBDBFF";
        $rcolor[1]="FFFFFF";
        
$whichqry=$_GET['w'];


if (in_array($whichqry,array('Transfers','LookupInventoryInTransit','EditDateIn'))){
	$sqlmain='SELECT t.*, b1.Branch as FROMBranch,  b2.Branch as TOBranch,  e1.Nickname as FromEncodedBy,  e2.Nickname as ToEncodedBy, format(sum(s.UnitPrice*s.QtySent),2) as AmountSent, format(sum(s.UnitCost*s.QtyReceived),2) as AmountReceived, t.FROMTimeStamp, t.TOTimeStamp FROM invty_2transfer as t 
	join `1branches` as b1 on b1.BranchNo=t.BranchNo
	join `1branches` as b2 on b2.BranchNo=t.ToBranchNo
	left join `1employees` as e1 on e1.IDNo=t.FromEncodedByNo
	left join `1employees` as e2 on e2.IDNo=t.ToEncodedByNo
	left join invty_2transfersub as s on t.TxnID=s.TxnID ';

	if($whichqry=='LookupInventoryInTransit'){
		$addfield='DateOut,b1.Branch as FROMBranch,QtySent AS Quantity,';
	} else {
		$addfield='';
	}
	$sqlsub='Select s.*,'.$addfield.' c.Category, i.ItemDesc, i.Unit, s.UnitPrice*s.QtySent as AmountSent, s.UnitCost*s.QtyReceived as AmountReceived,e1.Nickname as FromEncodedBy,e2.Nickname as ToEncodedBy, if(s.Defective=1,"Defective",if(s.Defective=2,"ForCheckup","Good Item")) as Defective from invty_2transfersub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e1 on s.FromEncodedByNo=e1.IDNo
    left join `1employees` as e2 on s.ToEncodedByNo=e2.IDNo';

}


switch ($whichqry){
    CASE 'Transfers':
$title='Add/Edit Interbranch Transfers';
$showbranches=true;

    $sqlmain.=' WHERE t.TxnID='.$txnid;

    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    $reqno=$result['ForRequestNo']; $reqtxnid=$result['ReqTxnID'];
    if ($result['BranchNo']==$result['ToBranchNo']) {
        $txntype='Repack'; $editsubtable='TxfrSubEdit'; $editprocesslabel='Enter';
        $postedfield='Posted'; $datefield='DateOUT';
        $deloreditmain='<a href=..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=invty_2transfer&l=invty OnClick="return confirm(\'Really delete this?\');">Delete</a>';
        } elseif ($result['BranchNo']==$_SESSION['bnum']) {
        $txntype='Out'; $editsubtable='TxfrSubEdit'; $editprocesslabel='Enter';
        $postedfield='Posted'; $datefield='DateOUT';
        $deloreditmain='<a href=..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=invty_2transfer&l=invty OnClick="return confirm(\'Really delete this?\');">Delete</a>';
        
    } elseif ($result['ToBranchNo']==$_SESSION['bnum']) {
         $txntype='In';$editsubtable='TxfrSubAccept'; $editprocesslabel='Accept';
         $datefield='DateIN';
         $postedfield='PostedIn';
         $deloreditmain='';
         
         
    } else {
         $txntype='';
         $postedfield='';
         $deloreditmain='';
    }
    $main='';
    $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'s.FROMTimeStamp');
    $delsubtable='TxfrSubDel';
    
    /*if ($result[$postedfield]==0){
        $columnnamesmain=array('DateOUT','DateIN','TransferNo','ForRequestNo','FROMBranch','TOBranch','Remarks','Waybill');
        $columnsub=array('ItemCode','Category','ItemDesc','Unit','QtySent','UnitPrice', 'AmountSent','QtyReceived','UnitCost', 'AmountReceived','SerialNo','Defective');
    } else {*/
    $columnnamesmain=array('DateOUT','DateIN','TransferNo','ForRequestNo','FROMBranch','TOBranch','Remarks','Waybill','Posted','PostedIn');
    $columnsub=array('ItemCode','Category','ItemDesc','Unit','QtySent','UnitPrice', 'AmountSent','QtyReceived','UnitCost', 'AmountReceived','SerialNo','Defective');
   // }
    if (!allowedToOpen(705,'1rtc')) {   $editok=FALSE; } else { $editok=editOk('invty_2transfer',$txnid,$link,$txntype); }
    
    if ($editok){
        $editmain='<td><a href="editinvspecifics.php?edit=2&w=TxfrMainEdit&txntype='.$txntype.'&TxnID='.$txnid.'">Edit</a>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp'.$deloreditmain.'</td><td><a href="addedittxfr.php?edit=2&w=ScanItems&ReqTxnID='.$reqtxnid.'&txntype='.$txntype.'&TxnID='.$txnid.'">Enter Items with Scanner</a></td>';
        $editsub=true;
        $addlprocess='praddtxfr.php?w=TxfrSubEdit&ReqTxnID='.$reqtxnid.'&txntype='.$txntype.'&TxnID='.$txnid.'&TxnSubId='; $addlprocesslabel='EditQty';
    } else {
        $editmain='<td><a href="printtxfr.php?w=Transfers&TxnID='.$txnid.'">Print Preview</a></td>';
        $editsub=false;
    }
    
    if ($showenc==1) { array_push($columnnamesmain,'FromEncodedBy','ToEncodedBy','PostedByNo','FROMTimeStamp','TOTimeStamp','PostedInByNo'); array_push($columnsub,'FromEncodedBy','FROMTimeStamp','ToEncodedBy','TOTimeStamp');}
      else {$columnnamesmain=$columnnamesmain; $columnsub=$columnsub;}
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'<tr></table>';
    
    $sqlsub.=' WHERE TxnID='.$txnid.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
    //$stmt=$link->query($sqlsub);
    //$result=$stmt->fetchAll();
    
    $sqlsum='Select count(ItemCode) as LineItems, sum(s.UnitPrice*s.QtySent) as TotalSent, sum(s.UnitCost*s.QtyReceived) as TotalReceived from invty_2transfersub s where TxnID='.$txnid;
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();    
    $addlinfo='Total Sent:  '.number_format($result['TotalSent'],2).str_repeat('&nbsp',7).'  Line Items: '.$result['LineItems'].($result['LineItems']>15?'<font color="red">'.str_repeat('&nbsp',6).' ONLY 15 LINE ITEMS CAN FIT IN ONE TRANSFER FORM.</font>':'').'<br>Total Received:  '.number_format($result['TotalReceived'],2).'&nbsp &nbsp &nbsp &nbsp<a href="addtxfrmain.php?w='.($txntype=='In'?'AcceptIn':$txntype).'">Add New Transfer</a><br>';
    
    $listcondition=$reqno;
    
    if ($txntype=='Out'){
    $columnnames=array(
                    array('field'=>'ItemCode', 'type'=>'text','size'=>20,'required'=>true,'list'=>'internalout','autofocus'=>true),
                    array('field'=>'QtySent', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                   // array('field'=>'UnitPrice', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'SerialNo', 'type'=>'text','size'=>10, 'required'=>false),
                    array('field'=>'UnitCost', 'type'=>'hidden','size'=>0,'value'=>0),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
    $otherlist=array('internalout');
    $liststoshow=array();
    $columnstoedit=$editok?array('ItemCode','QtySent','SerialNo'):array();
    } elseif ($txntype=='In'){
    $columnnames=array(
                    array('field'=>'ItemCode', 'type'=>'text','size'=>20,'required'=>true,'list'=>'internalin','autofocus'=>true),
                    array('field'=>'QtyReceived', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'UnitCost', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'SerialNo', 'type'=>'text','size'=>10, 'required'=>false),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid),
                    array('field'=>'RequestNo', 'type'=>'hidden', 'size'=>0,'value'=>$reqno)
                    );
    $otherlist=array('internalin');
    $liststoshow=array();
    $columnstoedit=$editok?array('QtyReceived'):array();
    } elseif ($txntype=='Repack'){
    $columnnames=array(
                    array('field'=>'BulkItemCode', 'caption'=>'Bulk Item Code','type'=>'text','size'=>10,'required'=>true,'list'=>'bulkitems','autofocus'=>true),
                    
                    // Repack must be encoded with ONE unit of Bulk Item for every repack entry. 
                    // Exemption only if in Central warehouse for oil items. 
                    array('field'=>'QtySent', 'caption'=>'Bulk Qty','type'=>'hidden','size'=>0, 'value'=>1),
                    // array('field'=>'QtyReceived', 'caption'=>'Repacked Qty','type'=>'hidden','size'=>0, 'required'=>true, 'value'=>0),
                    array('field'=>'SerialNo', 'type'=>'text','size'=>10, 'required'=>false),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid),
                    array('field'=>'RequestNo', 'type'=>'hidden', 'size'=>0,'value'=>$reqno)
                    );
    $liststoshow=array('bulkitems');
    $columnstoedit=$editok?array('ItemCode','QtySent','QtyReceived','SerialNo'):array();
    } elseif ($txntype==21 or $txntype='Vacuum'){
    $columnnames=array(
                    array('field'=>'ItemCode', 'type'=>'text','size'=>20,'required'=>true,'list'=>'vacuumitems','autofocus'=>true),
                    array('field'=>'QtySent', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'QtyReceived', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'UnitCost', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'SerialNo', 'type'=>'text','size'=>10, 'required'=>false),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid),
                    array('field'=>'RequestNo', 'type'=>'hidden', 'size'=>0,'value'=>$reqno)
                    );
    $liststoshow=array('vacuumitems');
    } else {
    $columnnames=array();
    $columnstoedit=$editok?array('ItemCode','QtySent','QtyReceived','SerialNo'):array();
    }
    
    $action='praddtxfr.php?w=TxfrSubAdd&ReqTxnID='.$reqtxnid.'&TxnID='.$txnid.'&txntype='.$txntype;
        
    // info for posting:
    $postvalue='1';
    $table='invty_2transfer';
    
    
    
      break;
  

case 'LookupInventoryInTransit':
if (!allowedToOpen(70311,'1rtc')) {echo 'No Permission'; exit(); }
        $title='Lookup Inventory in Transit';
		echo '<title>'.$title.'</title>';
		echo '<h3>'.$title.'</h3>';
	
		$columnnames=array('DateOut','FROMBranch','ItemCode','Category','ItemDesc','Unit','Quantity','UnitPrice');
		$title='';
		$sql=$sqlsub.' JOIN invty_2transfer m ON s.TxnID=m.TxnID JOIN 1branches b1 ON b1.BranchNo=m.BranchNo WHERE ToBranchNo='.$_SESSION['bnum'].' AND DateIN IS NULL ORDER BY DateOut ASC,Category,ItemCode';
		
		include_once('../backendphp/layout/displayastable.php');

	 exit();
	 break;
	 

case 'EditDateIn':
if (!allowedToOpen(6781,'1rtc')) { echo 'No Permission'; exit(); }
$title='Edit Date IN';
		$sql='SELECT TransferNo, Branch FROM invty_2transfer t join `1branches` b on b.BranchNo=t.BranchNo where ToBranchNo='.$_SESSION['bnum'].' and DateIN is NOT null AND DATE(TOTimeStamp)>\''.$_SESSION['nb4'].'\' ORDER BY Branch;'; $stmt=$link->query($sql);
		$res=$stmt->fetchAll();
		
	 echo '<title>'.$title.'</title>';
	 echo '<h3>'.$title.'</h3><br>';
	 if($stmt->rowCount()>0){
			
		 	 $select='<form action="addedittxfr.php?w=EditDateIn" method="POST">TransferNo: <select name="TransferNo">';
			 foreach($res as $row){
				 $select.='<option value="'.$row['TransferNo'].'" '.(((isset($_POST['TransferNo'])) AND $_POST['TransferNo']==$row['TransferNo'])?'selected':'').'>'.$row['Branch'].' - '.$row['TransferNo'].'</option>';
			 }
			$select.=' &nbsp; <input type="submit" name="btnLookup" value="Lookup"></form>';
			echo $select;
			
	 } else {
		 echo 'No records.';
	 }
	 
if((isset($_POST['btnLookup'])) OR (isset($_REQUEST['TransferNo']))){
		$sql='Select TxnID from `invty_2transfer` where ToBranchNo=\''.$_SESSION['bnum'].'\' and TransferNo=\''.$_REQUEST['TransferNo'].'\'';
	
		$stmt=$link->query($sql);
		$result=$stmt->fetch();
		$txnid=$result['TxnID'];
		
		 $sql=$sqlmain.' WHERE t.TxnID='.$txnid;
		
		$stmt=$link->query($sql);
		$result=$stmt->fetch();
		$strrep=str_repeat('&nbsp;',20);
		
		
		echo '<br><table style="border:1px solid black;";>';
		echo '<tr><td>DateOUT: '.$result['DateOUT'].'</td><td>'.$strrep.'TransferNo: '.$result['TransferNo'].'</td><td>'.$strrep.'ForRequestNo: '.$result['ForRequestNo'].'</td></tr>';
		echo '<tr><td>FROMBranch: '.$result['FROMBranch'].'</td><td>'.$strrep.'TOBranch: '.$result['TOBranch'].'</td><td>'.$strrep.'Remarks: '.$result['Remarks'].'</td></tr>';
		echo '<tr><td><br><b>AcceptedBy: '.$result['ToEncodedBy'].'</b></td><td><br>'.$strrep.'<b>DateIN: '.$result['DateIN'].'</b></td><td><br>'.$strrep.'<b>AcceptedTS: '.$result['TOTimeStamp'].'</b></td></tr>';
		echo '</table>';
		
		$columnnames=array('ItemCode','Category','ItemDesc','Unit','QtySent','UnitPrice', 'AmountSent','QtyReceived','UnitCost', 'AmountReceived','SerialNo','Defective');
		$title='';
		$sql=$sqlsub.' where TxnID='.$txnid.'';
		include_once('../backendphp/layout/displayastablenosort.php');
		$hiddentxnid='<input type="hidden" name="TxnID" value="'.$result['TxnID'].'">';
		echo '<br><br>';
		echo '<div>';
			echo '<div style="float:left;">';
				echo '<form action="addedittxfr.php?w=EditDateInProcess" method="POST">'.$hiddentxnid.'<input style="background-color:green;color:white;padding:4px;" type="submit" value="Remove DateIN?" onclick="return confirm(\'Really Remove Date In?\');"></form>';
			echo '</div>';
			echo '<div style="margin-left:150px;">';		
				echo '<form action="addedittxfr.php?w=ResetQTYProcess&TransferNo='.$result['TransferNo'].'" method="POST">'.$hiddentxnid.'<input style="background-color:yellow;color:black;padding:4px;" type="submit" value="Reset QTY Received?" onclick="return confirm(\'Reset QTY Received?\');"></form>';
			echo '</div>';
		echo '</div>';
	}
	 exit();
break;

case 'EditDateInProcess':
if (!allowedToOpen(6781,'1rtc')) { echo 'No Permission'; exit(); }

$txnid=$_POST['TxnID'];

$sql='Update `invty_2transfer` Set `DateIN`=NULL,PostedIn=0,PostedInByNo='.$_SESSION['(ak0)'].', TOEncodedByNo=\''.$_SESSION['(ak0)'].'\', TOTimeStamp=Now() where (`DateIN` IS NOT NULL) AND DATE(TOTimeStamp)>\''.$_SESSION['nb4'].'\' AND TxnID='.$txnid;

	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addedittxfr.php?w=EditDateIn");
	
break;


case 'ResetQTYProcess':
if (!allowedToOpen(6781,'1rtc')) { echo 'No Permission'; exit(); }

$txnid=$_POST['TxnID'];

$sql='UPDATE `invty_2transfersub` as s join `invty_2transfer` as m on m.TxnID=s.TxnID SET m.PostedIn=0,m.PostedInByNo='.$_SESSION['(ak0)'].',s.QtyReceived=0, s.TOEncodedByNo=\''.$_SESSION['(ak0)'].'\',s.TOTimeStamp=Now() where s.TxnID='.$txnid . ' and DATE(m.`TOTimeStamp`)>\''.$_SESSION['nb4'].'\''; 

      $stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:addedittxfr.php?w=EditDateIn&TransferNo=".$_GET['TransferNo']);
break;


	 
CASE 'Request':
		include_once('invlayout/pricelevelcase.php');
        $title='Add/Edit Request';
        $showbranches=true;
        $txntype='Request';
    $sqlmain='select rm.*, if(rm.SupplierBranchNo=0,"Supplier",b1.Branch) as SupplierBranch, if(rm.BranchNo=0,"All",b2.Branch) as RequestingBranch, e.Nickname as EncodedBy,b2.BranchNo from invty_3branchrequest as rm
join `1branches` as b1 on rm.SupplierBranchNo=b1.BranchNo
join `1branches` as b2 on rm.BranchNo=b2.BranchNo left join `1employees` as e on rm.EncodedByNo=e.IDNo where TxnID='.$txnid;
   
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
//addedcondition	
	   $formdesc='</i><div style="background-color: #e6e6e6; width: 520px; border: 2px solid grey; padding: 5px; "><h3>Formula:</h3></br>
	   <b>Warehouses: </b>RequestQty = Sold + LostSales + TransferOUT - Undelivered</br>
	   <b>Branches: </b>RequestQty = Sold + LostSales - Undelivered</div>';

    
    $columnnamesmain=array('Date','SupplierBranch','RequestNo','Remarks','DateReq','RequestingBranch','Posted');
    $columnsub=array('ItemCode','Category','ItemDesc','Unit','RequestQty','Sold','EndInvToday','Undelivered','LostSales');
    
    $main=''; $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Category, ItemCode');
     $editsubtable='RequestSubEdit'; $delsubtable='RequestSubDel';
    
    if (!allowedToOpen(707,'1rtc')) {   $editok=FALSE; } else { $editok=editOk('invty_3branchrequest',$txnid,$link,$txntype); }
    
    if ((substr($result['RequestNo'],0,1)<>'i') and ($result['Posted']==0) and !allowedToOpen(6933,'1rtc')){ $editok=false; }
    if (((substr($result['RequestNo'],0,1)=='d')OR(substr($result['RequestNo'],0,1)=='c')OR(substr($result['RequestNo'],0,1)=='g')) and ($result['Posted']==0) and allowedToOpen(6934,'1rtc')){ $editok=true; }
    
    if ($editok){
        $editmain='<td><a href="editinvspecifics.php?edit=2&w=RequestMainEdit&txntype='.$txntype.'&TxnID='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=praddtxfr.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=RequestMainDel OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editsub=true; $columnstoedit=array('ItemCode','RequestQty');
    } else {
        $editmain='<td><a href="printtxfr.php?w=Request&TxnID='.$txnid.'">Print Preview</a></td>';
        $editsub=false; $columnstoedit=array();
    }
    
    if ($showenc==1) { array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedByNo'); array_push($columnsub,'EncodedBy','TimeStamp');}
      else {$columnnamesmain=$columnnamesmain; $columnsub=$columnsub;}
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $showall=isset($_GET['showall']);
    $main='<table><tr>'.$main.$editmain.'<td><a href="addedittxfr.php?w=Request'.($showall?'':'&showall=true').'&txntype='.$txntype.'&TxnID='.$txnid.'">'.($showall?'Hide Delivered Items':'Show All Requested Items').'</a></td><tr></table>';
    if ($showall){
        // $sqlsub='Select s.*, c.Category, i.ItemDesc, i.Unit, e1.Nickname as EncodedBy from invty_3branchrequestsub s left join invty_1items i on i.ItemCode=s.ItemCode left join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e1 on s.EncodedByNo=e1.IDNo join invty_3branchrequest m on m.TxnID=s.TxnID
// where m.TxnID='.$txnid.' Order By Category, ItemCode';    
        $sqlsub='Select s.*, c.Category, i.ItemDesc, i.Unit, e1.Nickname as EncodedBy from invty_3branchrequestsub s left join invty_1items i on i.ItemCode=s.ItemCode left join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e1 on s.EncodedByNo=e1.IDNo join invty_3branchrequest m on m.TxnID=s.TxnID
where m.TxnID='.$txnid.' Order By '.$sortfield;    
    } else {
    $sqlsub='Select s.*, c.Category, i.ItemDesc, i.Unit, e1.Nickname as EncodedBy from invty_3branchrequestsub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e1 on s.EncodedByNo=e1.IDNo join invty_3branchrequest m on m.TxnID=s.TxnID
join `invty_44undeliveredreq` ud on s.ItemCode=ud.ItemCode and m.TxnID=ud.ReqTxnID
where m.TxnID='.$txnid.' and ud.SendBal<>0 Order By '.$sortfield;
    }
	// echo $sqlsub;
	// sum((if (b.ProvincialBranch=0, mp.PriceLevel3,mp.PriceLevel4))*SendBal) as ReqValue
    $sqlsum='Select count(ud.ItemCode) as LineItems,

	sum(
	'.$plcase.'
	*SendBal)


		as ReqValue

	from  `invty_44undeliveredreq` ud 
join `invty_3branchrequest` m on m.TxnID=ud.ReqTxnID
left join `invty_5latestminprice` lmp on ud.ItemCode=lmp.ItemCode
join `1branches` b on b.BranchNo=ud.BranchNo
Where m.TxnID='.$txnid.' and ud.SendBal<>0 Group By ud.ReqTxnID';
   
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $addlinfo='Line Items Undelivered:  '.number_format($result['LineItems'],0).'&nbsp &nbsp &nbsp &nbsp Total Value:  '.number_format($result['ReqValue'],2).'&nbsp &nbsp &nbsp &nbsp<a href="addtxfrmain.php?w=Request&txntype=Request">Add New Request</a><br>';
    
    $columnnames=array(
                    array('field'=>'ItemCode', 'type'=>'text','size'=>5,'required'=>true,'autofocus'=>true),
                    array('field'=>'RequestQty', 'type'=>'text','size'=>7, 'required'=>true, 'value'=>0),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
        
    $action='praddtxfr.php?w=RequestSubAdd&TxnID='.$txnid.'&txntype=Request';
    $liststoshow=array();
    // info for posting:
    $postvalue='1';
    $table='invty_3branchrequest';
    
    $editprocesslabel='Enter'; $width='120%';
	
	$filename='File.csv';
	 $sql='Select s.*, c.Category, i.ItemDesc, i.Unit, e1.Nickname as EncodedBy from invty_3branchrequestsub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e1 on s.EncodedByNo=e1.IDNo join invty_3branchrequest m on m.TxnID=s.TxnID
	join `invty_44undeliveredreq` ud on s.ItemCode=ud.ItemCode and m.TxnID=ud.ReqTxnID
	where m.TxnID='.$txnid.' Order By Category,ItemCode ASC';
	$stmt=$link->query($sql);
	$result=$stmt->fetchAll();
	// echo $sql; exit();
	$exportdata='TxnID,Category,ItemCode,RequestQty'. PHP_EOL; //remove <br> when downloading
	$export='';
	foreach($result as $row){
   $export=$export.'"'.$row['TxnID'].'","'.$row['Category'].'","'.$row['ItemCode'].'","'.$row['RequestQty'].'"'. PHP_EOL;
	}
	$export=$exportdata.$export;				
if (allowedToOpen(708,'1rtc')) { 	
?>
</br>
</br>
<div style="background-color: #e6e6e6; width: 400px; border: 2px solid grey; padding: 25px; margin: 25px;">
                        <ol>
						<b>Steps</b>
                        <li><b><form style="display: inline" action='downloadinvfilecsv.php' method='post'>
								<input type='submit' name='download' value='Export'>
								<input type='hidden' name='csvfile' value='<?php echo $export; ?>'>
								<input type='hidden' name='filename' value='<?php echo $filename; ?>'>
								</form></b>the Data to update in SpreadSheet.</li>
                        <li><form action="#" method="post" enctype="multipart/form-data">
								<input type="file" name="userfile" accept="csv/text"> then <input type="submit" name="import" value="Import"></li>
                        <li>Click <a href="addedittxfr.php?w=Update&TxnID=<?php echo $txnid;?>" style="font: bold 12px Arial; text-decoration:none; background-color: #EEEEEE; color: #000;
							padding: 2px 6px 2px 6px;
							border-top: 1px solid #CCCCCC;
							border-right: 1px solid #333333;
							border-bottom: 1px solid #333333;
							border-left: 1px solid #CCCCCC; ">Update</a> To update the data in the system.</li>
                        </ol>
</div>
<?php	
}      // break;
// case 'Upload': 
if(isset($_POST['import'])){
	$sql1='delete from invty_3holdingtable where TxnID='.$txnid.'';
		// echo $sql1; exit();
		$stmt=$link->prepare($sql1);
		$stmt->execute();
$tblname='invty_3holdingtable'; $firstcolumnname='TxnID';
$DOWNLOAD_DIR="../../uploads/"; 
    if (!isset($_FILES['userfile'])) { goto nodata; }
$maxsize = 10004800; //MAX Size 10MB


if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
       
                $csv_file=$_FILES['userfile']['name'];
				
				$ext = pathinfo($csv_file, PATHINFO_EXTENSION);
				if( $ext !== 'csv' ) { echo 'Error! Invalid File Type.'; exit(); }
				if(($_FILES['userfile']['size'] > $maxsize)){ echo 'Error! Invalid File Size (MAX 10MB).'; exit(); }
				
                $file_to_use=$DOWNLOAD_DIR . $csv_file;
                if (file_exists($file_to_use)) {
                    unlink($file_to_use);
                }
                 if (copy($_FILES['userfile']['tmp_name'],$file_to_use)) {
                 $good="Successfully_added_$csv_file";
                 }
                 else {
                 $good="Error: " . $_FILES["userfile"]["error"];
                 }
           } else {
             $good="Did not work  " . "Error: " . $_FILES["userfile"]["error"];            
            echo $csv_file . " is the file name";
            }


$csv = array_map("str_getcsv", file($file_to_use,FILE_SKIP_EMPTY_LINES));
$keys = array_shift($csv);

$numcols = count($keys)-1;
$num=0;
$fieldlist="";
while ($num<$numcols) {
    $fieldlist=$fieldlist . $keys[$num].", ";
    $num=$num+1;
}
$fieldlist=$fieldlist . $keys[$numcols];


$query="";
$row = 1;
if (($handle = fopen($file_to_use,"r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num=0;
    $values=""; //echo $data[0];
if($data[0]!=$firstcolumnname){

        while ($num<=$numcols) {
          $values=$values."'". addslashes($data[$num]) . (($num<$numcols)?"', ":"'");
          $num=$num+1;
        } //end while 

        if(isset($requireencodedby) and $requireencodedby==true) { $fieldlist2=$fieldlist.",EncodedByNo"; $values.=",".$_SESSION['(ak0)']; } else { $fieldlist2=$fieldlist;}
        if(isset($requiredts) and $requiredts==true) { $fieldlist2.=",TimeStamp"; $values.=",Now()"; }
        if(isset($requireencodedby) OR isset($requiredts)) { $fields=$fieldlist2; } else { $fields=$fieldlist; }
$query="Insert into $tblname (" . $fields . ") values (" . $values . ");";
// echo $query;
if($_SESSION['(ak0)']==1002 OR $_SESSION['(ak0)']==1003){ echo $query . "<br>" ; print_r($data). "<br>" ;}
  $row++;
$link->query($query);
} //end if        
  
    }
    fclose($handle);
}

echo ($row-1) . " rows successfully imported to database!!";
}
?>
</form>
<?php

  nodata:
        break;
CASE 'Update':
		$sql='UPDATE invty_3branchrequestsub brs join invty_3holdingtable h ON brs.TxnID=h.TxnID join invty_3branchrequest br on br.TxnID=brs.TxnID SET brs.RequestQty=h.RequestQty where h.ItemCode=brs.ItemCode and Posted<>1 ';
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		
		$sql2='select * from invty_3holdingtable WHERE TxnID='.$txnid.' AND ItemCode NOT IN (select ItemCode from invty_3branchrequestsub WHERE TxnID='.$txnid.'); ';
		// echo $sql2; exit();
		$stmt=$link->query($sql2);
		$result=$stmt->fetchALL();
		
		foreach($result as $row){
		$sql3='INSERT INTO `invty_3branchrequestsub` SET TxnID='.$row['TxnID'].',ItemCode='.$row['ItemCode'].',RequestQty='.$row['RequestQty'].'  ';
		// echo $sql3; exit();
		$stmt=$link->prepare($sql3);
		$stmt->execute();
		}
		
		$sql4='Delete from invty_3branchrequestsub where TxnID='.$txnid.' and RequestQty=0';
		// echo $sql4; exit();
		$stmt=$link->prepare($sql4);
		$stmt->execute();
		
		$sql1='delete from invty_3holdingtable where TxnID='.$txnid.'';
		// echo $sql1; exit();
		$stmt=$link->prepare($sql1);
		$stmt->execute();
		header('Location: '.$_SERVER['HTTP_REFERER']);
    
break;  
CASE 'ScanItems':
$title='Scan Items for Transfer';
    $showbranches=false;
    $sqlmain='SELECT t.*, b1.Branch as FROMBranch,  b2.Branch as TOBranch,  e1.Nickname as FromEncodedBy,  e2.Nickname as ToEncodedBy, format(sum(s.UnitPrice*s.QtySent),2) as AmountSent, format(sum(s.UnitCost*s.QtyReceived),2) as AmountReceived, t.FROMTimeStamp, t.TOTimeStamp FROM invty_2transfer as t 
join `1branches` as b1 on b1.BranchNo=t.BranchNo
join `1branches` as b2 on b2.BranchNo=t.ToBranchNo
join `1employees` as e1 on e1.IDNo=t.FromEncodedByNo
join `1employees` as e2 on e2.IDNo=t.ToEncodedByNo
join invty_2transfersub as s on t.TxnID=s.TxnID WHERE t.TxnID='.$txnid;

    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    $reqno=$result['ForRequestNo']; $reqtxnid=$result['ReqTxnID'];
    if ($result['BranchNo']==$_SESSION['bnum']) {
        $txntype='Out';
    } elseif ($result['ToBranchNo']==$_SESSION['bnum']) {
         $txntype='In';
    } else {
         $txntype='';
    }
    $main=''; $editsubtable='TxfrSubScan'; $delsubtable='TxfrSubScanDel';
    $columnnamesmain=array('DateOUT','DateIN','TransferNo','ForRequestNo','FROMBranch','TOBranch','Remarks');
    if (!allowedToOpen(705,'1rtc')) {   $editok=FALSE; } else { $editok=editOk('invty_2transfer',$txnid,$link,$txntype); }
    if ($editok){    
     $editmain='';
        $editsub=true;
        
    } else {
        $editmain='';
        $editsub=false;
    }
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'<tr></table>';
    $sqlsub='Select s.*, c.Category, i.ItemDesc, i.Unit from invty_2transferbarcodesub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo where TxnID='.$txnid;
    $stmt=$link->query($sqlsub);
    $result=$stmt->fetchAll();
    $columnsub=array('ItemCode','Category','ItemDesc','Unit','QtySent');
    
     $sub='';$subcol='';
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    foreach($result as $row){
        
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        $sub='<tr>'.$sub.(($editsub and $txntype<>'In')?'<td><a href=praddtxfr.php?ReqTxnID='.$reqtxnid.'&TxnID='.$txnid.'&TxnSubId='.$row['TxnSubId'].'&action_token='.$_SESSION['action_token'].'&w=TxfrSubScanDel&txntype='.$txntype.' OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').'</tr>';
    }
    $sub='<table><tr>'.$subcol.'<td>Delete?</td></tr><tbody>'.$sub.'</tbody></table>';
    $sqlsum='Select Count(ItemCode) as CountofItemCode from invty_2transferbarcodesub s where TxnID='.$txnid. ' group by TxnID';
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $addlinfo='Line Items:  '.$result['CountofItemCode'].'<br>';
    
    if ($txntype=='Out'){
    $columnnames=array(
                    array('field'=>'ItemCode', 'type'=>'text','size'=>20,'required'=>true,'list'=>'internalout','autofocus'=>true),
                    array('field'=>'QtySent', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>1),
                    array('field'=>'SerialNo', 'type'=>'text','size'=>10, 'required'=>false),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
    }  else {
    $columnnames=array();
    }
    
    $action='praddtxfr.php?w=TxfrSubScan&ReqTxnID='.$reqtxnid.'&TxnID='.$txnid.'&txntype='.$txntype;
    $liststoshow=array();
    $listcondition=$reqno;
    $whichotherlist='invty';
    $otherlist=array('internalout');
    $addlsubmit='<form method=POST action="praddtxfr.php?w=TxfrSubScanSend&ReqTxnID='.$reqtxnid.'&TxnID='.$txnid.'&txntype='.$txntype.'" enctype="multipart/form-data">
<input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" />'.str_repeat('&nbsp',20).
'<input type="submit" name="send" value="Send Scanned Data to Transfer"> <br><br>';
   $editprocesslabel='Enter';
      break;


}
 
 $editprocess='praddtxfr.php?w='.$editsubtable.'&ReqTxnID='.$reqtxnid.'&txntype='.$txntype.'&TxnID='.$txnid.'&TxnSubId='; 
 if ($txntype<>'In'){
     if($delsubtable=='TxfrSubDel'){
     $delprocess='..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=invty_2transfersub&l=invty&TxnSubId=';}
   else {$delprocess='praddtxfr.php?TxnID='.$txnid.'&w='.$delsubtable.'&txntype='.$txntype.'&TxnSubId=';}
 } else {
   $delprocess=null;
 }
    $txnsubid='TxnSubId'; $showgrandtotal=true;
    $left='100%'; $leftmargin='101%'; $right='9%';
    $withsub=true;include('../backendphp/layout/inputsubform.php');
    
      $link=null; $stmt=null;
?>