<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6926,'1rtc')) {   echo 'No permission'; exit;} 

$txntype=$_REQUEST['txntype'];
if($txntype=='InvtyChargesDistri'){
	$showbranches=false; }
 else {
	 $showbranches=true; 
 } include_once('../switchboard/contents.php');
 
include_once('../backendphp/functions/editok.php');

if($txntype=='InvtyChargesDistri'){
	goto bypass;
}

	
	// condition for cash sales
			$sqlc='select ClientNo,Remarks,TxnID,txntype from invty_2sale where TxnID=\''.$_GET['TxnID'].'\'';
			$stmtc=$link->query($sqlc); $resultc=$stmtc->fetch();	
		
		if(isset($_GET['c'])){
			if($resultc['ClientNo']==10000){
				echo'<form method="post" action="praddsale.php?w=UpdateRemarks&txntype='.$resultc['txntype'].'&TxnID='.$resultc['TxnID'].'">
						Client Name: <input type="text" name="Remarks[]" required>
						Telephone No.: <input type="text" name="Remarks[]" size="10" required>
						<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
						<input type="submit" name="submit">
					</form>';
				exit();
			}
		}
		//end	


include_once('../backendphp/layout/showencodedbybutton.php');


$method='POST';

$txnid=intval($_REQUEST['TxnID']);



$user=$_SESSION['(ak0)'];
$w='SaleMainEdit';
$wdel='SaleMainDel';
$fieldsinrow=5;

bypass:


switch ($txntype){
    case '1': //cash
    case '2': //charge
    case '10': //DR
    include_once('clientconditions.php');    
    
    case '3': //Inventory Charges
    $title='Add/Edit '.(($txntype==1 or $txntype==10)?'Cash Sale':($txntype==2?'Charge Sale':'Inv Charges'));
        goto mainform;
    case '32': //GCash
        $title='Add/Edit GCash';
    case '33': //Paymaya
        $title='Add/Edit Paymaya';
    mainform:
    if ($txntype<>3){
    $sqlmain='Select s.*, pt.paytypedesc as PaymentType, c.ClientName, t.txndesc as SaleType, CONCAT(SUBSTR(c.TIN,1,3),"-",SUBSTR(c.TIN,4,3),"-",SUBSTR(c.TIN,7,3),"-",SUBSTR(c.TIN,10,3)) AS TIN, TownOrCity AS Address, concat(e2.`Nickname`," ", e2.`SurName`) AS SoldBy, e.Nickname as EncodedBy, concat(e1.`Nickname`," ",e1.`SurName`) AS `TeamLeader` from `invty_2sale` as s join `1clients` as c on s.ClientNo=c.ClientNo  join `invty_0txntype` as t on t.txntypeid=s.txntype
join invty_0paytype pt on pt.paytypeid=s.PaymentType
left join `1employees` as e on s.EncodedByNo=e.IDNo 
left join `1employees` as e1 on s.TeamLeader=e1.IDNo '
            . ' left join `1employees` as e2 on s.SoldByNo=e2.IDNo where TxnID='.$txnid;
   
    } else { //invty charges
    $sqlmain='Select s.*, pt.paytypedesc as PaymentType, concat(ec.Nickname,\' \',ec.Surname) as ClientName, t.txndesc as SaleType, e.Nickname as EncodedBy, concat(e1.`Nickname`," ",e1.`SurName`) AS `TeamLeader`  from `invty_2sale` as s left join `1employees` ec on ec.IDNo=s.ClientNo  join `invty_0txntype` as t on t.txntypeid=s.txntype join invty_0paytype pt on pt.paytypeid=s.PaymentType left join `1employees` as e on s.EncodedByNo=e.IDNo 
left join `1employees` as e1 on s.TeamLeader=e1.IDNo where TxnID='.$txnid;  
    }
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    $txndate=$result['Date']; $branch=$result['BranchNo']; $saleno=$result['SaleNo']; 
    if ($result['Posted']==0){
       $columnnamesmain=array('Date','SaleNo','ClientName','TIN','Address','Remarks','SoldBy','TeamLeader','SaleType','PaymentType','CheckDetails','DateofCheck','PONo');
    } else {
    $columnnamesmain=array('Date','SaleNo','ClientName','TIN','Address','Remarks','SoldBy','TeamLeader','SaleType','PaymentType','CheckDetails','DateofCheck','PONo','Posted');
    }
    $main='';

  
    if(isset($_GET['EncodeBundle']) AND $_GET['EncodeBundle']==1){
      include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
      echo comboBox($link,'SELECT ItemCode,ItemDesc FROM invty_1items WHERE ItemCode>=30000','ItemDesc','ItemCode','bundlelists');
      $linkitemprbundle='<a href="addeditsale.php?TxnID='.$txnid.'&txntype='.$txntype.'">Encode By ItemCode</a>';

      $columnnames=array(
        array('field'=>'ItemCode','caption'=>'BundleID','list'=>'bundlelists','type'=>'text','size'=>10,'required'=>true),
        array('field'=>'Qty', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
        array('field'=>'UnitPrice', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
        array('field'=>'SerialNo', 'type'=>'text','size'=>10, 'required'=>false),
        array('field'=>'UnitCost', 'type'=>'hidden','size'=>0,'value'=>0),
        array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
        );
    } else {
      $linkitemprbundle='<a href="addeditsale.php?TxnID='.$txnid.'&txntype='.$txntype.'&EncodeBundle=1">Encode By BundleID</a>';
    $columnnames=array(
                    array('field'=>'ItemCode', 'type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'Qty', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'UnitPrice', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'SerialNo', 'type'=>'text','size'=>10, 'required'=>false),
                    array('field'=>'UnitCost', 'type'=>'hidden','size'=>0,'value'=>0),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
          }
    $liststoshow=array();
    
     break;
    case '5':
    $title='Add/Edit Customer Return<font color="maroon"><br>Returned Item - NEGATIVE Qty<br> Replacement - POSITIVE Qty</font>';
 
    include_once '../generalinfo/unionlists/ECList.php';

    $sqlmain='Select s.TxnID,
s.Date,
s.SaleNo as CRSNo,
s.ClientNo,
s.Remarks,
s.PaymentType,
s.CheckDetails as OldInvoice,
s.DateofCheck as OldInvDate,
s.PONo as ApprovalNo,
s.TimeStamp,
s.BranchNo,
s.EncodedByNo,
s.PostedByNo,
s.Posted,
s.txntype as SaleType, c.BECSName AS ClientName, t.txndesc as SaleType, CONCAT(SUBSTR(c.TIN,1,3),"-",SUBSTR(c.TIN,4,3),"-",SUBSTR(c.TIN,7,3),"-",SUBSTR(c.TIN,10,3)) AS TIN, Address, e.Nickname as EncodedBy, concat(e1.`Nickname`," ",e1.`SurName`) AS `TeamLeader` from `invty_2sale` as s join `ECList` c on c.BECSNo=s.ClientNo AND c.BECS=IF(s.`ClientNo`<9999,"E","C")  join `invty_0txntype` as t on t.txntypeid=s.txntype
left join `1employees` as e on s.EncodedByNo=e.IDNo
left join `1employees` as e1 on s.TeamLeader=e1.IDNo
where txntype=5 and TxnID='.$txnid;
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch(); $branch=$result['BranchNo'];
    if ($result['Posted']==0){
       $columnnamesmain=array('Date','CRSNo','ClientName','TIN','Address','Remarks','SaleType','PaymentType','OldInvoice','OldInvDate','TeamLeader','ApprovalNo');
       $columnstoedit=allowedToOpen(210,'1rtc')?array('ItemCode','Qty','UnitPrice','SerialNo'):array();
    } else {
    $columnnamesmain=array('Date','CRSNo','ClientName','TIN','Address','Remarks','SaleType','PaymentType','OldInvoice','OldInvDate','TeamLeader','ApprovalNo','Posted');
    $columnstoedit=array();
    }
    $main=''; 
    $columnnames=array(
                    array('field'=>'ItemCode', 'type'=>'text','size'=>20,'required'=>true),
                    array('field'=>'Qty', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'UnitPrice', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'SerialNo', 'type'=>'text','size'=>10, 'required'=>false),
                    array('field'=>'UnitCost', 'type'=>'hidden','size'=>0,'value'=>0),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
    $liststoshow=array();
    $listcondition=$result['OldInvoice'];
    
      break;
	  
	  
	  case 'InvtyChargesDistri':
	  $title="Inventory Charges Distribution";
	  echo '<title>'.$title.'</title>';
	  echo '<br><h3>'.$title.'</h3>';
	 echo '<br><i>* If no Branch Head, distribution of inventory charges is prorated based on attendance.</i><br>';
	  echo '<br><table style="width:43%;background-color:white;border-collapse:collapse;" border="1px solid black;">';
	  echo '<tr><th></th><th style="padding:3px;width:70px;">BH with 1 BP</th><th style="padding:3px;width:85px;">BH with 2 JBH/BP</th><th style="padding:3px;width:85px;">BH with 3 JBH/BP</th><th style="padding:3px;width:85px;">BH with 4 JBH/BP</th><th style="padding:3px;width:85px;">BH with 5 JBH/BP</th></tr>';
	  echo '<tr style="text-align:center;"><td style="text-align:left;padding:4px;">Branch Head</td><td style="background-color:#d0fffe;padding:4px;">60%</td><td style="background-color:#fffddb;padding:4px;">44%</td><td style="background-color:#e4ffde;padding:4px;">34%</td><td style="background-color:#ffd3fd;padding:4px;">28%</td><td style="background-color:#ffe7d3;padding:4px;">25%</td></tr>';
	  echo '<tr style="text-align:center;"><td width="170px" align="left" style="padding:4px;">Junior Branch Head/<br>Branch Personnel</td><td style="background-color:#d0fffe;padding:4px;">40%<br><br><br><br><br><br>100%</td><td style="background-color:#fffddb;padding:4px;">28%<br>28%<br><br><br><br><br>100%</td><td style="background-color:#e4ffde;padding:4px;">32%<br>32%<br>32%<br><br><br><br>100%</td><td style="background-color:#ffd3fd;padding:4px;">18%<br>18%<br>18%<br>18%<br><br><br>100%</td><td style="background-color:#ffe7d3;padding:4px;">15%<br>15%<br>15%<br>15%<br>15%<br><br>100%</td></tr>';
	  echo '</table>';
	  
	  exit();
	  break;
	  
     }    // end switch
     
     if($txntype==3){ if (!allowedToOpen(6928,'1rtc')) {  $editok=FALSE; } else { $editok=editOk('invty_2sale',$txnid,$link,$txntype); } }
     else { if (!allowedToOpen(6927,'1rtc') and !allowedToOpen(6929,'1rtc')) {   $editok=FALSE; } else { $editok=editOk('invty_2sale',$txnid,$link,$txntype); } }
    
    if ($editok){
      echo $linkitemprbundle;
        $editmain='<td><a href="editinvspecifics.php?edit=2&w='.$w.'&txntype='.$txntype.'&TxnID='.$txnid.'">Edit</a>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp<a href=..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=invty_2Sale&l=invty OnClick="return confirm(\'Really delete this?\');">Delete</a></td><td><a href=addeditsale.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&txntype='.$txntype.'&specprice=1>Request for Special Price</a></td>'.((allowedToOpen(6929,'1rtc'))?'<td><a href=praddsale.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&txntype='.$txntype.'&w=RemoveSP OnClick="return confirm(\'Really delete special prices?\');">Remove Special Prices</a></td>':'');
        $editsub=true;
        $columnsub=array('ItemCode','Category','ItemDesc','Qty','Unit','UnitPrice', 'Amount','Defective?','SerialNo');
        $columnstoedit=array('ItemCode','Qty','UnitPrice','SerialNo');
    } else {
        $editmain=(in_array($txntype,array(2,3,32)))?'<td><a href="printtxfr.php?w='.($txntype==2?'Charge':'InvCharge').'&TxnID='.$txnid.'">Print Preview</a></td>':'';
        $editsub=false;
        $columnsub=array('ItemCode','Category','ItemDesc','Qty','Unit','UnitPrice', 'Amount','Defective?','SerialNo');
        $columnstoedit=array();
    }
    if ($showenc==1) { array_push($columnnamesmain,'TimeStamp','EncodedBy','PostedByNo'); array_push($columnsub,'TimeStamp','EncodedBy');}
      else {$columnnamesmain=$columnnamesmain; $columnsub=$columnsub;}  
          
    $colno=0; $fieldsinrow=(isset($fieldsinrow)?$fieldsinrow:4);
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%$fieldsinrow==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'<tr></table>';
    $main=$main.(isset($clientconditions)?'<br><font color="red">'.$clientconditions.'</font>':'').'<br>'.(isset($_GET['msg'])?'<br><b><font color="maroon">'.strtoupper($_GET['msg']).'</font></b><br><br>':'');
    $withspecprice=false;
    $sqlsub='Select s.*, c.Category,if(s.ItemCode<30000,0,1) AS `SwitchTR`, i.ItemDesc, i.Unit, s.UnitPrice*s.Qty as Amount, if(Defective<>0,"Defective",0) as `Defective?`,e.Nickname as EncodedBy from invty_2salesub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e on s.EncodedByNo=e.IDNo where TxnID='.$txnid;
    $approvespecprice='';
    if ($txntype<>3){
    // $sqlsp='Select sp.TxnID from `invty_7specdisctapproval` sp where sp.TxnID='.$txnid;
    // $stmtsp=$link->query($sqlsp);
    // $resultsp=$stmtsp->fetch();
    if (isset($_GET['specprice'])){
      $withspecprice=true;
      $columnsub[]=('SpecPriceRequest');$columnsub[]=('BranchRemarks');$columnsub[]=('SpecPriceApproved');$columnsub[]=('SCRemarks');$columnsub[]=('ApprovedBy');
      if(allowedToOpen(6929,'1rtc')){
		  if ($result['Posted']==0){
         $columnstoedit[]='SpecPriceApproved'; $columnstoedit[]='SCRemarks';
		  }
         $addlprocess='praddsale.php?TxnID='.$txnid.'&w=ApproveSP&txntype='.$txntype.'&TxnSubId=';$addlprocesslabel='Approve';
      } else {
		   if ($result['Posted']==0){
         $columnstoedit[]='SpecPriceRequest'; $columnstoedit[]='BranchRemarks';
		   }
      }
         
      $sqlsub='Select s.*, c.Category,0 AS `SwitchTR`, i.ItemDesc, i.Unit, s.UnitPrice*s.Qty as Amount, if(Defective<>0,"Defective",0) as `Defective?`, e.Nickname as EncodedBy, a.SpecPriceRequest, a.BranchRemarks, a.SpecPriceApproved, a.SCRemarks, e1.Nickname as ApprovedBy from invty_2salesub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo 
left join `1employees` as e on s.EncodedByNo=e.IDNo 
left join `invty_7specdisctapproval` a on s.TxnID=a.TxnID and s.ItemCode=a.ItemCode
left join `1employees` as e1 on a.ApprovedByNo=e1.IDNo
where s.TxnID='.$txnid;
   $approvespecprice=(allowedToOpen(6929,'1rtc'))?'<td><a href=praddsale.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&txntype='.$txntype.'&w=AppSpecPrice>Approve</a></td>':'';
  
    } else { // no special price
      //$sqlsub=$sqlsubnospecprice;
    }
    } else { //$txntype==3
        if(($editsub==true) and allowedToOpen(6930,'1rtc')){
        $addlprocess='setasdefective.php?TxnID='.$txnid.'&which=SetDefectInSales&TxnSubId='; $addlprocesslabel='Set_as_Defective';}
    }
    // echo $sqlsub;;
    $stmt=$link->query($sqlsub);
    $resultsub=$stmt->fetchAll();
    
    $sqlsum='Select sum(s.UnitPrice*s.Qty) as Total, count(ItemCode) as LineItems from invty_2salesub s where TxnID='.$txnid;
    $stmt=$link->query($sqlsum);
    $resultsum=$stmt->fetch();
    $total='';$totalwithop=0;
    if (in_array($txntype,array(1,2,10))){  include_once('overpricesection.php');}
    
    $totalsale=$total.'Total :  '.number_format($resultsum['Total']+$totalwithop,2).'&nbsp &nbsp &nbsp <a href="addsalemain.php?w=Sale&saletype='.$txntype.'">Add New '.$result['SaleType'].' </a><br>';
    $addlinfo=$totalsale.($txntype<>2?'':($resultsum['LineItems']>15?'<font color="red">'.str_repeat('&nbsp',6).' ONLY 15 LINE ITEMS CAN FIT IN ONE CHARGE INV FORM.</font><br>':''));
    
    $action='praddsale.php?w=SaleSub&TxnID='.$txnid.'&txntype='.$txntype;
    
    // info for posting:
    $postvalue='1';
    $table='invty_2sale';
    
    
    $editprocess='praddsale.php?w=SaleSubEdit&withspecprice='.$withspecprice.'&txntype='.$txntype.'&TxnID='.$txnid.'&TxnSubId='; $editprocesslabel='Enter';
    $delprocess='..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=invty_2salesub&l=invty&TxnSubId=';
    $txnsubid='TxnSubId';
    
    if (in_array($txntype,array(1,2,10))){ //Sales
      $sqlsum='Select sum(s.UnitPrice*s.Qty)+'.+$totalwithop.' as Total, if(VatTypeNo IN (1,2),0,round((sum(s.UnitPrice*s.Qty)+'.+$totalwithop.')/1.12,2)) as Vatable, if(VatTypeNo IN (1,2),0,round((sum(s.UnitPrice*s.Qty)+'.+$totalwithop.')*(0.12/1.12),2)) as Vat from invty_2salesub s join invty_2sale m on m.TxnID=s.TxnID join `1clients` c on c.ClientNo=m.ClientNo where s.TxnID='.$txnid;
    $stmt=$link->query($sqlsum);
    $resultsum=$stmt->fetch();
    $addlinfo='<table width="30%"><tr><td width="10%" align=right>'.(($resultsum['Vat']<>0)?'(Vat Inc Total / 1.12) &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp':'').($totalwithop>0?'Total Sales with OP':'Total Sales').str_repeat('&nbsp',8).(($resultsum['Vat']<>0)?number_format($resultsum['Vatable'],2):number_format($resultsum['Total'],2)).'</td><tr><td  align=right>'.(($resultsum['Vat']<>0)?'(Vat Inc Total *0.12) / 1.12 &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp VAT Amount':'VAT Exempt').str_repeat('&nbsp',8).number_format($resultsum['Vat'],2).'</td>'.'</tr><tr height="20"></tr><tr height="20"><td  align=right>'.($totalwithop>0?'Total with OP':'Total Amount Due').str_repeat('&nbsp',8).number_format($resultsum['Total'],2).'</td></tr></table>';
//addons	
	$sqladdons='select Approved,Branch,ao.ItemCode,CONCAT(Category,\' \',ItemDesc) as ItemDesc,ao.TxnSubId,ao.Qty,
	case when Approved=0 then "For Approval" when Approved=1 then "Approved" when Approved=2 then "Rejected" end as SCstatus,
	case when FApproved=0 then "For Approval" when FApproved=1 then "Approved" when FApproved=2 then "Rejected" end as SAMstatus
	from invty_2salesubaddons ao join invty_2sale s on s.TxnID=ao.TxnID join invty_1items i on i.ItemCode=ao.ItemCode join invty_1category c on c.CatNo=i.CatNo join 1branches b on b.BranchNo=s.BranchNo where ao.TxnID='.$txnid.'';
	$stmtaddons=$link->query($sqladdons); $resultaddons=$stmtaddons->fetchAll();
	
if($stmtaddons->rowCount()!=0){
	$addlinfo.='</br><h3>Add-on</h3><table></br><tr><th>ItemCode</th><th>ItemDesc</th><th>Qty</th><th>SAMstatus</th><th>SCstatus</th></tr>';
	foreach($resultaddons as $resaddons){
		$addlinfo.='<tr>
		<td>'.$resaddons['ItemCode'].'</td><td>'.$resaddons['ItemDesc'].'</td><td>'.$resaddons['Qty'].'</td><td>'.$resaddons['SAMstatus'].'</td><td>'.$resaddons['SCstatus'].'</td>
		</tr>';
	}
	$addlinfo.='</table>';
}
//	
	$addlinfo.='<br><br>&nbsp &nbsp &nbsp <a href="addsalemain.php?w=Sale&saletype='.$txntype.'">Add New '.$result['SaleType'].' </a>
	';
      } else {
         $showgrandtotal=true; $coltototal='Amount';
      }
    include_once('../backendphp/functions/getnumber.php');  
    $formdesc='Branch No. '.$branch.': <b>'.getNumber('BranchName',$branch).'</b>';
    //  $formdesc='Branch No. '.$_SESSION['bnum'].': <b>'.$_SESSION['@brn'].'</b>';
    $left='90%'; $leftmargin='91%'; $right='9%';

    $newtargettxnidsubname="ItemCode"; $troptioneditnoedit=1; $switchtr='SwitchTR';
    $newtargetprocess='bundleditems.php?w=Lookup&BundleID='; $newtargetprocesslabel='Lookup Bundle';
    $withsub=true;include('../backendphp/layout/inputsubform.php');
    if (in_array($txntype,array(5))){include_once('approvedcrssection.php');}
     
     //invty charges - to show sharing
    if ($txntype==3){
      if ($editok){
      ?><br><br>
    <form style='display:inline' method=post action='prauditdistri.php?w=distri<?php echo '&TxnID='.$txnid; ?>'>
    <input type="hidden" name='Date' value='<?php echo $txndate; ?>'>
    <input type="hidden" name='BranchNo' value='<?php echo $branch; ?>'>
    <input type="hidden" name='SaleNo' value='<?php echo $saleno; ?>'>
    <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" /> 
    <input type=submit name=submit value='Distribute Charges'>
</form><br>
<?php
      }
	  
include 'auditdistri.php';

    }
      $link=null; $stmt=null;
?>