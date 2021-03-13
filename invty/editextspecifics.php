<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';




// check if allowed
$allowed=array(694,6920,6921);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=$allow+1; } else { $allow=$allow; }}
if ($allow==0) { header("Location:".$_SERVER['HTTP_REFERER']."?denied=true");}
// end of check

$txnid=intval($_REQUEST['TxnID']);
$txntype=(isset($_REQUEST['txntype'])?$_REQUEST['txntype']:0);
$title='Edit Invty Txn';
    $minorswitch=$_SERVER['HTTP_REFERER'];
    $minorswitchname='Back';

$processblank='';
$processlabelblank='';
switch ($_REQUEST['w']){

case 'RequestMainEdit':
$columnnames=array('Date','RequestNo','Remarks','DateReq','TxnID','EncodedByNo','TimeStamp');
$columnstoedit=array('Date','RequestNo','Remarks','DateReq');
	
$columnslist=array();
$listsname=array();
$liststoshow=array();

$method='POST';
$action='praddext.php?w=RequestMainEdit&txntype=Request&TxnID='.$txnid;

$sql='Select rm.*, e1.Nickname as EncodedBy from `invty_3extrequest` rm 
left join `1employees` as e1 on e1.IDNo=rm.EncodedByNo
where TxnID='.$txnid;

include('../backendphp/layout/rendersubform.php');
		break;

case 'RequestSubEdit':
$txnsubid=$_REQUEST['TxnSubId'];
$columnnames=array('ItemCode','Category', 'ItemDesc','Unit','Qty');
$columnstoedit=array('ItemCode','Qty');
	
$columnslist=array('ItemCode');
$listsname=array('ItemCode'=>'items');
$liststoshow=array('items');

$method='POST';
$action='praddext.php?w=RequestSubEdit&TxnSubId='.$txnsubid.'&TxnID='.$txnid;

$sql='Select s.*, c.Category, i.ItemDesc, i.Unit from `invty_3extrequestsub` as s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo where TxnSubId='.$txnsubid;
//echo $sql;
include('../backendphp/layout/rendersubform.php');
		break;
case 'SendforDistriEdit':
    if (!allowedToOpen(692,'1rtc')) { echo 'No permission'; exit;}
$txnsubid=$_REQUEST['TxnSubId'];
$columnnames=array('RequestNo','ItemCode','Category','Description','Unit','Qty','DateReq','UnitCost','PriceLevel1','PriceLevel2','PriceLevel3','PriceLevel4','PriceLevel5','RequestingWH','SupplierName','Company');
$columnstoedit=array('RequestNo','ItemCode','Qty','DateReq','UnitCost','PriceLevel1','PriceLevel2','PriceLevel3','PriceLevel4','PriceLevel5','BranchNo','SupplierName','Company');
$columnslist=array('ItemCode','SupplierName','BranchNo','Company');
$listsname=array('ItemCode'=>'items','SupplierName'=>'suppliers','BranchNo'=>'branches','Company'=>'companies');
$liststoshow=array('items','suppliers','branches','companies');
$method='POST';
$action='praddext.php?w=SendforDistriEdit&TxnSubId='.$txnsubid.'&TxnID='.$txnid;
$sql='SELECT d.*, c.Category, i.ItemDesc as Description, i.Unit, b.Branch as RequestingWH, s.SupplierName, ca.Company FROM invty_3distributeorders d 
join invty_1items i on i.ItemCode=d.ItemCode join invty_1category c on c.CatNo=i.CatNo
join `1branches` as b on d.BranchNo=b.BranchNo
left join `1companies` as ca on d.CompanyNo=ca.CompanyNo
left join `1suppliers` as s on d.SupplierNo=s.SupplierNo where TxnSubId='.$txnsubid;
include('../backendphp/layout/rendersubform.php');
		break;

case 'OrderMainEdit':
    if (!allowedToOpen(6920,'1rtc')) { echo 'No permission'; exit;}
$columnnames=array('Date','PONo','Remarks','DateReq','Company','TxnID','EncodedByNo','TimeStamp');
$columnstoedit=array('Date','PONo','SupplierNo','RequestNo','BranchNo','Remarks','DateReq','CompanyNo');
	
$columnslist=array('RequestNo');
$listsname=array('RequestNo'=>'externalreqno');
$liststoshow=array('companies');
$listcondition='';
$whichotherlist='invty';
$otherlist=array('externalreqno');
$method='POST';
$action='praddext.php?w=OrderMainEdit&txntype=Order&TxnID='.$txnid;

$sql='Select rm.*, e1.Nickname as EncodedBy from `invty_3order` rm 
left join `1employees` as e1 on e1.IDNo=rm.EncodedByNo
where TxnID='.$txnid;

include('../backendphp/layout/rendersubform.php');
		break;

case 'OrderSubEdit':
    if (!allowedToOpen(6920,'1rtc')) { echo 'No permission'; exit;}
$txnsubid=$_REQUEST['TxnSubId'];
if (allowedToOpen(6921,'1rtc')){
$columnnames=array('ItemCode','Category', 'ItemDesc','Unit','Qty','UnitCost','PriceLevel1','PriceLevel2','PriceLevel3','PriceLevel4','PriceLevel5');
$columnstoedit=array('ItemCode','Qty','UnitCost','PriceLevel1','PriceLevel2','PriceLevel3','PriceLevel4','PriceLevel5');
} else {
	$columnnames=array('ItemCode','Category', 'ItemDesc','Unit','Qty');
	$columnstoedit=array('ItemCode','Qty');
}

$columnslist=array('ItemCode');
$listsname=array('ItemCode'=>'externalitems');
$liststoshow=array();
$listcondition='';
$whichotherlist='invty';
$otherlist=array('externalitems');
$method='POST';
$action='praddext.php?w=OrderSubEdit&TxnSubId='.$txnsubid.'&TxnID='.$txnid;

$sql='Select s.*, c.Category, i.ItemDesc, i.Unit from `invty_3ordersub` as s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo where TxnSubId='.$txnsubid;
//echo $sql;
include('../backendphp/layout/rendersubform.php');
		break;
}

  $link=null; $stmt=null;
?>
