<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;



if (!allowedToOpen(202,'1rtc')) { header("Location:".$_SERVER['HTTP_REFERER']."?denied=true");	}

$txnid=intval($_REQUEST['TxnID']);
$txntype=$_REQUEST['txntype'];
$title='Lookup Invty Txn';
    $minorswitch=$_SERVER['HTTP_REFERER'];
    $minorswitchname='Back';

$processblank='';
$processlabelblank='';
switch ($_REQUEST['w']){
case 'SaleMainEdit':
$clientlist=($txntype==2?'arclients':($txntype==3?'employeesforlist':'clientsnodatedcheck'));
$columnnames=array('TxnID','Date','SaleNo','ClientName','Remarks','txndesc','PayType','CheckDetails','DateofCheck','PONo','TimeStamp','EncodedBy','Posted','txntype','TeamLeader');
if (allowedToOpen(2021,'1rtc')){
        $columnstoedit=array('Date','SaleNo','ClientName','Remarks','PaymentType','CheckDetails','DateofCheck','PONo','SoldBy','TeamLeader');		
	} else {
		$columnstoedit=array('Date','SaleNo','ClientName','Remarks','CheckDetails','DateofCheck','PONo','SoldBy');
	}
$columnslist=array('ClientName','PaymentType','SoldBy');
$listsname=array('ClientName'=>$clientlist,'PaymentType'=>'paytype','SoldBy'=>'branchpersonnel');

$liststoshow=array($clientlist,'paytype','branchpersonnel');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT e.`IDNo`, concat(e.`Nickname`," ", e.`SurName`) AS SoldBy from (`1employees` e JOIN `attend_30currentpositions` p ON (e.`IDNo` = p.`IDNo`)) where p.`PositionID` IN (32,33,37,81,38) AND p.BranchNo='.$_SESSION['bnum'],'IDNo','SoldBy','branchpersonnel');

$lookupsub='../invty/txnsperdaysub.php';
$method='POST';
$action='praddsale.php?w=SaleMainEdit&txntype='.$txntype.'&TxnID='.$txnid;

$sql='SELECT `invty_2sale`.*, `invty_0txntype`.txndesc, ClientName, PaymentType, if(PaymentType=1,\'Cash\',\'Charge\') as PayType, concat(e2.`Nickname`," ", e2.`SurName`) AS SoldBy, e.NickName as EncodedBy FROM invty_2sale INNER JOIN invty_0txntype ON `invty_2sale`.txntype = `invty_0txntype`.txntypeid join `1clients` as c on c.ClientNo=`invty_2sale`.ClientNo
left join `1employees` as e on e.IDNo=`invty_2sale`.EncodedByNo left join `1employees` as e2 on SoldByNo=e2.IDNo where TxnID='.$txnid;
include('../backendphp/layout/rendersubform.php');
		break;
case 'SaleSubEdit':
$txnsubid=$_REQUEST['TxnSubId'];
$columnnames=array('ItemCode','Category', 'ItemDesc','Qty','Unit','UnitPrice','Amt','SerialNo','TimeStamp','EncodedByNo');
$columnstoedit=array('ItemCode','Qty','UnitPrice','SerialNo');
$columnslist=array('ItemCode');
if($txntype==5){
$itemslist='solditemsperinv';
$liststoshow=array();
$listcondition=$_GET['OldInv'];
$whichotherlist='invty';
$otherlist=array('solditemsperinv');

} else {
	$itemslist='items';
	$liststoshow=array('items');
}

$listsname=array('ItemCode'=>$itemslist);

$method='POST';
$action='praddsale.php?w=SaleSubEdit&txntype='.$txntype.'&TxnSubId='.$txnsubid.'&TxnID='.$txnid;

$sql='Select s.*, c.Category, i.ItemDesc, i.Unit,s.UnitPrice*s.Qty as Amt from invty_2salesub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo where TxnSubId='.$txnsubid;
include('../backendphp/layout/rendersubform.php');
		break;

case 'SaleSubDistriEdit':
$txnsubid=$_REQUEST['TxnSubId'];
$columnnames=array('ChargeTo','ChargeAmount','EncodedBy', 'TimeStamp');
$columnstoedit=array('ChargeTo','ChargeAmount');
$columnslist=array('ChargeTo');
$liststoshow=array('employeesforlist');
$listsname=array('ChargeTo'=>'employeesforlist');

$method='POST';
$action='praddsale.php?w=SaleSubDistriEdit&txntype='.$txntype.'&TxnSubId='.$txnsubid.'&TxnID='.$txnid;

$sql='Select s.*, concat(e1.Nickname, " ", e1.Surname) as ChargeTo, e.Nickname as EncodedBy from invty_2salesubauditdistri s
left join `1employees` as e1 on s.ChargeToIDNo=e1.IDNo
left join `1employees` as e on s.EncodedByNo=e.IDNo where TxnSubId='.$txnsubid;
include('../backendphp/layout/rendersubform.php');
		break;

case 'TxfrMainEdit':
$columnnames=array('DateOUT','FROMBranch','TransferNo','ForRequestNo','DateIN','TOBranch','Remarks','Waybill','Posted','FromEncodedBy','ToEncodedBy','TxnID','PostedByNo','FROMTimeStamp','TOTimeStamp');
if ($txntype=='Out') {
	$columnstoedit=array('DateOUT','TransferNo','ToBranchNo','Remarks');	//'ForRequestNo',
		$listsname=array('ToBranchNo'=>'branches','ForRequestNo'=>'undeliveredrequestsOUT');
		$liststoshow=array('branches','undeliveredrequestsOUT');
		if (allowedToOpen(7611,'1rtc')){$columnstoedit[]='Waybill';}
    } elseif ($txntype=='In') {
	 $columnstoedit=array('DateIN','Remarks'); //'ForRequestNo',
		$listsname=array('ToBranchNo'=>'branches');//,'ForRequestNo'=>'undeliveredrequestsIN'
		$liststoshow=array('branches');//,'undeliveredrequestsIN'
    } elseif( $txntype=='Repack'){
	$columnstoedit=array('DateOUT','TransferNo','Remarks');	
		$listsname=array();
		$liststoshow=array();	
    } else {
	 $columnstoedit=array();
    }
	
$columnslist=array('ToBranchNo'); //,'ForRequestNo'

//$lookupsub='../invty/txnsperdaysub.php';
$method='POST';
$action='praddtxfr.php?w=TxfrMainEdit&txntype='.$txntype.'&TxnID='.$txnid;

$sql='Select tm.*, b1.Branch as FROMBranch, b2.Branch as TOBranch, e1.Nickname as FromEncodedBy, e2.Nickname as ToEncodedBy from `invty_2transfer` tm join `1branches` as b1 on b1.BranchNo=tm.BranchNo
join `1branches` as b2 on b2.BranchNo=tm.TOBranchNo
left join `1employees` as e1 on e1.IDNo=tm.FromEncodedByNo
left join `1employees` as e2 on e2.IDNo=tm.ToEncodedByNo
where TxnID='.$txnid;

include('../backendphp/layout/rendersubform.php');
		break;

case 'TxfrSubEdit':
$txnsubid=$_REQUEST['TxnSubId'];
$columnnames=array('ItemCode','Category', 'ItemDesc','Unit','QtySent','UnitPrice','QtyReceived','UnitCost','SerialNo','FROMEncodedByNo','TOEncodedByNo','FROMTimeStamp','TOTimeStamp');

if ($txntype=='Out') {
	$columnstoedit=array('ItemCode','QtySent','SerialNo');
	$columnslist=array('ItemCode');
$listsname=array('ItemCode'=>'internal');
$liststoshow=array('internal');
    } elseif ($txntype=='In') {
	 $columnstoedit=array('QtyReceived');
    } elseif ($txntype=='Repack') {
	 $columnstoedit=array('ItemCode','QtySent','QtyReceived','SerialNo');
	 $columnslist=array('ItemCode');
$listsname=array('ItemCode'=>'repackitems');
$liststoshow=array('repackitems');
    } else {
	 $columnstoedit=array();
    }
$columnslist =array();
$liststoshow =array();
$method='POST';
$action='praddtxfr.php?w=TxfrSubEdit&txntype='.$txntype.'&TxnSubId='.$txnsubid.'&TxnID='.$txnid;

$sql='Select s.*, c.Category, i.ItemDesc, i.Unit,s.UnitPrice*s.QtySent as AmountSent, s.UnitCost*s.QtyReceived as AmountReceived from invty_2transfersub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo where TxnSubId='.$txnsubid;
include('../backendphp/layout/rendersubform.php');
		break;


case 'RequestMainEdit':
$columnnames=array('Date','SupplierBranchNo','RequestNo','Remarks','DateReq','TxnID','EncodedByNo','TimeStamp');
$columnstoedit=array('Date','SupplierBranchNo','RequestNo','Remarks','DateReq');
	
$columnslist=array('SupplierBranchNo');
$listsname=array('SupplierBranchNo'=>'branches');
$liststoshow=array('branches');

$method='POST';
$action='praddtxfr.php?w=RequestMainEdit&txntype=Request&TxnID='.$txnid;

$sql='Select rm.*, b1.Branch as SupplierBranch, b2.Branch as RequestingBranch, e1.Nickname as EncodedBy from `invty_3branchrequest` rm join `1branches` as b1 on b1.BranchNo=rm.SupplierBranchNo
join `1branches` as b2 on b2.BranchNo=rm.BranchNo
left join `1employees` as e1 on e1.IDNo=rm.EncodedByNo
where TxnID='.$txnid;

include('../backendphp/layout/rendersubform.php');
		break;

case 'RequestSubEdit':
$txnsubid=$_REQUEST['TxnSubId'];
$columnnames=array('ItemCode','Category', 'ItemDesc','Unit','RequestQty','EndInvToday','EncodedByNo','TimeStamp');
$columnstoedit=array('ItemCode','RequestQty');
	
$columnslist=array('ItemCode');
$listsname=array('ItemCode'=>'items');
$liststoshow=array('items');

$method='POST';
$action='praddtxfr.php?w=RequestSubEdit&txntype=Request&TxnSubId='.$txnsubid.'&TxnID='.$txnid;

$sql='Select s.*, c.Category, i.ItemDesc, i.Unit from `invty_3branchrequestsub` as s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo where TxnSubId='.$txnsubid;
//echo $sql;
include('../backendphp/layout/rendersubform.php');
		break;

}

  $link=null; $stmt=null;
?>
