<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6924,'1rtc')) { header("Location:".$_SERVER['HTTP_REFERER']."?denied=true");	}

$txnid=intval($_REQUEST['TxnID']);
$txntype=(isset($_REQUEST['txntype'])?$_REQUEST['txntype']:0);
$title='Edit Invty Txn';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
    

$processblank='';
$processlabelblank='';
switch ($_REQUEST['w']){

case 'MRRMainEdit':
    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
    echo comboBox($link,'Select c.CompanyNo, c.Company from `1companies` c WHERE Active=1','CompanyNo','Company','companies');
$txntype=$_REQUEST['txntype'];
if ($txntype==9){ // Store Used
$columnnames=array('Date','MRRNo','Remarks','EncodedByNo','TimeStamp');
$columnstoedit=array('Date','MRRNo','Remarks');
$columnslist=array();
$listsname=array();
$liststoshow=array();
} elseif ($txntype==8){ //Purchase Return
$columnnames=array('Date','MRRNo','SupplierNo','Remarks','CompanyName','EncodedByNo','TimeStamp');
$columnstoedit=array('Date','MRRNo','SupplierNo','ForPONo','Remarks','CompanyName');
$columnslist=array('SupplierNo','CompanyName');
$listsname=array('SupplierNo'=>'suppliers','CompanyName'=>'companies');
$liststoshow=array('suppliers');
} else { //MRR 
$columnnames=array('Date','MRRNo','SupplierNo','ForPONo','SuppInvNo','SuppDRNo','SuppDRDate','SuppInvDate','Terms','Remarks','CompanyName','EncodedByNo','TimeStamp');
if (!allowedToOpen(6951,'1rtc')){
	$columnstoedit=array('Date','MRRNo','SupplierNo','SuppInvNo','SuppDRNo','SuppDRDate','SuppInvDate','Terms','Remarks','CompanyName');
} else {
	$columnstoedit=array('Date','CompanyName');
}

$columnslist=array('CompanyName');
$listcondition='';
$whichotherlist='invty';
$otherlist=($txntype==8?array():array());
$listsname=array('CompanyName'=>'companies');
$liststoshow=array();
}

$method='POST';
$action='praddmrr.php?w=MRRMainEdit&txntype='.$txntype.'&TxnID='.$txnid;

$sql='Select m.*, e1.Nickname as EncodedBy, Company as CompanyName from `invty_2mrr` m 
left join `1employees` as e1 on e1.IDNo=m.EncodedByNo left join `1companies` as co on co.CompanyNo=m.RCompany
where TxnID='.$txnid;

include('../backendphp/layout/rendersubform.php');
		break;

case 'MRRSubEdit':
$txnsubid=$_REQUEST['TxnSubId'];
$txntype=$_REQUEST['txntype'];
$columnnames=$txntype<>8?array('ItemCode','Category', 'ItemDesc','Unit','Qty','SerialNo'):array('ItemCode','Category', 'ItemDesc','Unit','Qty','SerialNo','UnitCost');
$columnstoedit=$txntype<>8?array('ItemCode','Qty','SerialNo'):array('ItemCode','Qty','SerialNo','UnitCost');
$columnslist=array('ItemCode');
if ($txntype==6){ // MRR
$listsname=array('ItemCode'=>'mrritemsperpo');
$liststoshow=array();
$listcondition=$_GET['ForPONo'];
$whichotherlist='invty';
$otherlist=array('mrritemsperpo');
} else { //Purchase Return or Store Used
$listsname=array('ItemCode'=>'items');
$liststoshow=array('items');
}
$method='POST';
$action='praddmrr.php?txntype='.$txntype.'&w=MRRSubEdit&TxnSubId='.$txnsubid.'&TxnID='.$txnid;

$sql='Select s.*, c.Category, i.ItemDesc, i.Unit from `invty_2mrrsub` as s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo where TxnSubId='.$txnsubid;
//echo $sql;
include('../backendphp/layout/rendersubform.php');
		break;


}

  $link=null; $stmt=null;
?>
