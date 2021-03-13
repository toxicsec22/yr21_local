<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(640,'1rtc')) { header("Location:".$_SERVER['HTTP_REFERER']."?denied=true"); }
$showbranches=true; include_once('../switchboard/contents.php');



$title='Edit Audit Txn';
    $minorswitch=$_SERVER['HTTP_REFERER'];
    $minorswitchname='Back';

$processblank='';
$processlabelblank='';
switch ($_REQUEST['w']){

case 'InvCountMainEdit':
$txnid=$_REQUEST['CountID'];
$columnnames=array('Date','BranchNo','Remarks','AuditedBy');
$columnstoedit=array('Date','BranchNo','Remarks');
$columnslist=array('BranchNo');
$listsname=array('BranchNo'=>'branches');
$liststoshow=array('branches');
$method='POST';
$action='praddaudit.php?w=InvCountMainEdit&CountID='.$txnid;
$sql='Select m.*, e1.Nickname as AuditedBy from `audit_2countmain` m 
left join `1employees` as e1 on e1.IDNo=m.AuditedByNo
where CountID='.$txnid;
include('../backendphp/layout/rendersubform.php');
		break;

case 'InvCountSubEdit':
$txnid=$_REQUEST['CountID'];
$txnsubid=$_REQUEST['CountSubID'];
$columnnames=array('ItemCode','Category', 'ItemDesc','Unit','Count','Remarks');
$columnstoedit=array('ItemCode','Count','Remarks');
$columnslist=array();
$listsname=array(); // removed this so faster loading: 'ItemCode'=>'items'); 
$liststoshow=array();

$method='POST';
$action='praddaudit.php?w=InvCountSubEdit&CountSubID='.$txnsubid.'&CountID='.$txnid;

$sql='Select s.*, c.Category, i.ItemDesc, i.Unit from `audit_2countsub` as s join `invty_1items` i on i.ItemCode=s.ItemCode join `invty_1category` c on c.CatNo=i.CatNo where CountSubID='.$txnsubid;
//echo $sql;
include('../backendphp/layout/rendersubform.php');
		break;


case 'CashCountMainEdit':
$txnid=$_REQUEST['CashCountID'];
$columnnames=array('DateCounted','BranchNo','NoOfUsedReceipts','Remarks','1000','500','200','100','50','20','10','5','1','025','010','005');
$columnstoedit=array('DateCounted','BranchNo','NoOfUsedReceipts','Remarks','1000','500','200','100','50','20','10','5','1','025','010','005');
$columnslist=array('BranchNo');
$listsname=array('BranchNo'=>'branches');
$liststoshow=array('branches');
$method='POST';
$action='prcashtools.php?w=CashCountMainEdit&CashCountID='.$txnid;
$sql='Select m.*, e1.Nickname as AuditedBy from `audit_2countcash` m 
left join `1employees` as e1 on e1.IDNo=m.EncodedByNo
where CashCountID='.$txnid;
include('../backendphp/layout/rendersubform.php');
		break;

case 'CashCountSubEdit':
$txnid=$_REQUEST['CashCountID'];
$txnsubid=$_REQUEST['CashCountSubID'];
$columnnames=array('InvandPRCollectNo','Amount');
$columnstoedit=array('InvandPRCollectNo','Amount');
$columnslist=array('');
$listsname=array('');
$liststoshow=array('');
$method='POST';
$action='prcashtools.php?w=CashCountSubEdit&CashCountSubID='.$txnsubid.'&CashCountID='.$txnid;
$sql='Select s.* from `audit_2countcashsub` s where CashCountSubID='.$txnsubid;
include('../backendphp/layout/rendersubform.php');
		break;

case 'ToolsCountMainEdit':
$txnid=$_REQUEST['CountID'];
$columnnames=array('Date','BranchNo','Remarks');
$columnstoedit=array('Date','BranchNo','Remarks');
$columnslist=array('BranchNo');
$listsname=array('BranchNo'=>'branches');
$liststoshow=array('branches');
$method='POST';
$action='prcashtools.php?w=ToolsCountMainEdit&CountID='.$txnid;
$sql='Select m.*, e1.Nickname as AuditedBy from `audit_2toolscountmain` m 
left join `1employees` as e1 on e1.IDNo=m.AuditedByNo
where CountID='.$txnid;
include('../backendphp/layout/rendersubform.php');
		break;

case 'ToolsCountSubEdit':
$txnid=$_REQUEST['CountID'];
$txnsubid=$_REQUEST['CountSubID'];
$columnnames=array('ToolID','ToolDesc','Count','Remarks');
$columnstoedit=array('ToolID','Count','Remarks');
$columnslist=array('ToolID');
$listsname=array('ToolID'=>'tools');
$liststoshow=array('tools');
$method='POST';
$action='prcashtools.php?w=ToolsCountSubEdit&CountSubID='.$txnsubid.'&CountID='.$txnid;
$sql='Select s.*, t.ToolDesc from `audit_2toolscountsub` s join `audit_1tools` t on t.ToolID=s.ToolID where CountSubID='.$txnsubid;
include('../backendphp/layout/rendersubform.php');
		break;

case 'VacuumEdit':
$txnid=$_REQUEST['CountID'];
$columnnames=array('Date','SerialNo','Vacuum','VacuumedBy','TotalSoldPerTank');
$columnstoedit=array('Date','SerialNo','Vacuum','VacuumedBy','TotalSoldPerTank');
$columnslist=array('VacuumedBy');
$listsname=array('VacuumedBy'=>'employeeid');
$liststoshow=array('employeeid');
$method='POST';
$action='prvacuum.php?w=VacuumEdit&CountID='.$txnid;
$sql='Select v.* from `audit_3vacuum` v where CountID='.$txnid;
include('../backendphp/layout/rendersubform.php');
		break;

case 'AdjustMainEdit':

$txnid=intval($_REQUEST['TxnID']);
$columnnames=array('Date','AdjNo');
$columnstoedit=array('Date','AdjNo');
$columnslist=array('');
$listsname=array('');
$liststoshow=array('');
$method='POST';
$action='pradjust.php?w=AdjustMainEdit&TxnID='.$txnid;
$sql='Select m.* from `invty_4adjust` m where TxnID='.$txnid;
include('../backendphp/layout/rendersubform.php');
		break;

case 'AdjustSubEdit':

$txnid=intval($_REQUEST['TxnID']);
$txnsubid=$_REQUEST['TxnSubId'];
$columnnames=array('ItemCode','Category','ItemDesc','Unit','UnitPrice','Qty','SerialNo','Remarks');
$columnstoedit=array('ItemCode','Qty','SerialNo','Remarks');
$columnslist=array('ItemCode');
$listsname=array('ItemCode'=>'items');
$liststoshow=array('items');
$method='POST';
$action='pradjust.php?w=AdjustSubEdit&TxnSubId='.$txnsubid.'&TxnID='.$txnid;
$sql='Select s.*, c.Category, i.ItemDesc, i.Unit from `invty_4adjustsub` s join `invty_1items` i on i.ItemCode=s.ItemCode join `invty_1category` c on c.CatNo=i.CatNo where TxnSubID='.$txnsubid;
include('../backendphp/layout/rendersubform.php');
		break;
		
}

  $link=null; $stmt=null;
?>
