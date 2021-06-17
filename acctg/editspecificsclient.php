<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(999,5971,5151,5153,6003,5991,5992,5952);$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { header("Location:".$_SERVER['HTTP_REFERER']."?denied=true"); }
allowed:
// end of check
$showbranches=false; include_once('../switchboard/contents.php');

 

$txnid=intval($_REQUEST['TxnID']);
//$txntype=(isset($_REQUEST['txntype'])?$_REQUEST['txntype']:0);
$title='Edit Acctg Txn';
    

$processblank='';
$processlabelblank='';
$whichqry=$_REQUEST['w'];
switch ($whichqry){

case 'SaleMainEdit':
    if($_SESSION['bnum']==999){ $allowed=999;} else { $allowed=5971;}
    if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit; }
$columnnames=array('Date','BranchNo','Remarks','TeamLeader');
$columnstoedit=array('Date','BranchNo','Remarks','TeamLeader');
	
$columnslist=array('BranchNo');
$listsname=array('BranchNo'=>'branches');
$liststoshow=array('branches');
$listcondition='';

$method='POST';
$action='preditclientside.php?w=SaleMainEdit&TxnID='.$txnid;

$sql='Select m.* from `acctg_2salemain` m where TxnID='.$txnid;

include('../backendphp/layout/rendersubform.php');
		break;

case 'SaleSubEdit':
    if($_SESSION['bnum']==999){ $allowed=999;} else { $allowed=5971;}
    if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit; }
$txnsubid=$_REQUEST['TxnSubId'];
$columnnames=array('Particulars','ClientName','DebitAccount','CreditAccount','Amount','RCompany');
$columnstoedit=array('Particulars','ClientName','DebitAccount','CreditAccount','Amount','RCompany');
	
$columnslist=array('DebitAccount','CreditAccount','ClientName');
$listsname=array('DebitAccount'=>'accounts','CreditAccount'=>'accounts','ClientName'=>'clients');
$liststoshow=array('clients','companies');
$listcondition='';
    $whichotherlist='acctg';
    $otherlist=array('accounts');
$method='POST';
$action='preditclientside.php?w=SaleSubEdit&TxnSubId='.$txnsubid.'&TxnID='.$txnid;

$sql='Select s.*,ca.ShortAcctID as DebitAccount,ca1.ShortAcctID as CreditAccount, cl.ClientName from `acctg_2salesub` as s join acctg_2salemain m on m.TxnID=s.TxnID 
join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID
 join acctg_1chartofaccounts ca1 on ca1.AccountID=s.CreditAccountID 
 join `acctg_0uniclientsalesperson` cl on cl.ClientNo=s.ClientNo AND cl.BranchNo=m.BranchNo
 where TxnSubId='.$txnsubid;
//echo $sql;
include('../backendphp/layout/rendersubform.php');
		break;



case 'CollectMainEdit':
        if (!allowedToOpen(5151,'1rtc')) { echo 'No permission'; exit; }
        collectmainedit:
  
        $columnnames=array('Date','Client','CheckBank','CheckNo','CheckBRSTN','ClientCheckBankAccountNo','DateofCheck','DebitAccount','Remarks','ReceivedBy','ReceivedByName');
        $columnstoedit=array_diff($columnnames,array('ReceivedByName'));

            
$table='acctg_2collectmain'; 
array_unshift($columnnames,'CollectNo'); $columnnames[]='Type'; array_unshift($columnstoedit,'CollectNo');

$columnslist=array('DebitAccount','Client');
$listsname=array('DebitAccount'=>'accounts','Client'=>'clients');
$liststoshow=array('clients');
$listcondition='';
$whichotherlist='acctg';
$otherlist=array('accounts');
if (allowedToOpen(6003,'1rtc')) { 
    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
    echo comboBox($link, 'SELECT * FROM acctg_1collecttype WHERE CollectTypeID<>6 ORDER BY CollectTypeID', 'CollectTypeDesc', 'CollectTypeID', 'type'); 
    $columnnames[]='BranchSeriesNo'; $columnstoedit[]='BranchSeriesNo'; $columnstoedit[]='Type'; 	
        array_unshift($columnslist,'Type'); 
        array_unshift($listsname,array('Type'=>'type'));         
}
$method='POST';
$action='preditclientside.php?w='.$whichqry.'&TxnID='.$txnid;

$sql='Select m.*, ca.ShortAcctID as DebitAccount, c.ClientName as Client, e1.Nickname as ReceivedByName, Type from `'.$table.'` m 
 join `1clients` c on c.ClientNo=m.ClientNo
 join acctg_1chartofaccounts ca on ca.AccountID=m.DebitAccountID
left join `1employees` as e1 on e1.IDNo=m.ReceivedBy
where TxnID='.$txnid;

include('../backendphp/layout/rendersubform.php');
		break;
    
case 'CollectSubEdit':
case 'CollectDeductSubEdit':
    if (!allowedToOpen(5151,'1rtc')) {   echo 'No permission'; exit;}
$whichotherlist='acctg'; 
$otherlist=array('accounts');
if($whichqry=='CollectDeductSubEdit'){
		$acct='DebitAccount'; $table='deduct'; $columnnames=array('DeductDetails','Amount');		
		} else {
		$acct='CreditAccount'; $table=''; $columnnames=array('OtherORDetails','Amount');
		}
$columnslist=array($acct); $listsname=array($acct=>'accounts');
$txnsubid=$_REQUEST['TxnSubId'];

if (allowedToOpen(6003,'1rtc')){ $columnnames[]='BranchNo'; } 
if (allowedToOpen(5992,'1rtc')){ $columnnames[]=$acct; } 
$columnstoedit=$columnnames;
$listcondition='';
$liststoshow=array();
$method='POST';
$action='preditclientside.php?w='.$whichqry.'&TxnSubId='.$txnsubid.'&TxnID='.$txnid;

$sql='Select s.*,ca.ShortAcctID as `'.$acct.'` from `acctg_2collectsub'.$table.'` as s join acctg_1chartofaccounts ca on ca.AccountID=s.'.$acct.'ID where TxnSubId='.$txnsubid;
//echo $sql;
include('../backendphp/layout/rendersubform.php');
		break;

case 'DepMainEdit':
    if (!allowedToOpen(5991,'1rtc')) { echo 'No permission'; exit; }  
$columnnames=array('DepositNo','Date','DebitAccount','Remarks');
$columnstoedit=array('DepositNo','Date','DebitAccount','Remarks');
	
$columnslist=array('DebitAccount');
$listsname=array('DebitAccount'=>'accounts');
$liststoshow=array();
$listcondition='';
$whichotherlist='acctg';
$otherlist=array('accounts');
$method='POST';
$action='preditclientside.php?w=DepMainEdit&TxnID='.$txnid;

$sql='Select m.*, ca.ShortAcctID as DebitAccount, e1.Nickname as EncodedBy from `acctg_2depositmain` m
 join acctg_1chartofaccounts ca on ca.AccountID=m.DebitAccountID
left join `1employees` as e1 on e1.IDNo=m.EncodedByNo
where TxnID='.$txnid;

include('../backendphp/layout/rendersubform.php');
		break;

case 'DepSubEdit':
    if (!allowedToOpen(5991,'1rtc')) { echo 'No permission'; exit; }  
$txnsubid=$_REQUEST['TxnSubId'];
$columnnames=array('Client','ClientNo','DepDetails','ForChargeInvNo','Type','CRNo','CheckDraweeBank','CheckNo','CreditAccount','Amount','Forex');
if (allowedToOpen(5992,'1rtc')){
$columnstoedit=array('Branch','Client','ForChargeInvNo','DepDetails','Type','CheckDraweeBank','CheckNo','CreditAccount','Amount','Forex');
$columnslist=array('Branch','CreditAccount','Client');
$listsname=array('Branch'=>'branchnames','CreditAccount'=>'accounts','Client'=>'clients');
$liststoshow=array('clients','branchnames');
$listcondition='';
$whichotherlist='acctg';
$otherlist=array('accounts');
} else {
$columnstoedit=array('DepDetails','Amount');		
$columnslist=array();
$listsname=array();
$liststoshow=array();
}
$method='POST';
$action='preditclientside.php?w=DepSubEdit&TxnSubId='.$txnsubid.'&TxnID='.$txnid;

$sql='Select s.*, ca.ShortAcctID as CreditAccount, c.ClientName as Client, b.Branch from `acctg_2depositsub` as s
join acctg_1chartofaccounts ca on ca.AccountID=s.CreditAccountID JOIN  `1branches` b ON b.BranchNo=s.BranchNo
left join `acctg_01uniclientsalespersonfordep` c on c.ClientNo=s.ClientNo where TxnSubId='.$txnsubid;
// echo $sql;
include('../backendphp/layout/rendersubform.php');
		break;

		
case 'DepEncashSubEdit':
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT TypeID, BudgetDesc FROM acctg_1branchpreapprovedbudgetlist ORDER BY BudgetDesc','TypeID','BudgetDesc','typelist');
echo comboBox($link,'SELECT EntityID,Entity FROM `acctg_1budgetentities` ORDER BY Entity;','EntityID','Entity','entities');
echo comboBox($link,'SELECT BranchNo,Branch from `1branches` where Active=1 ORDER BY Branch','BranchNo','Branch','allbranches');
$branchlist='allbranches';
$entities='entities';
    if (!allowedToOpen(5991,'1rtc')) { echo 'No permission'; exit; }  
$txnsubid=$_REQUEST['TxnSubId'];
$columnnames=array('Branch','FromBudgetOf','BudgetDesc','EncashDetails','ApprovalNo','CompanyName','TIN','DebitAccount','Amount');

$columnstoedit=array('Branch','FromBudgetOf','BudgetDesc','EncashDetails','TIN','DebitAccount','Amount');

if(!allowedToOpen(5994,'1rtc')){
	$columnstoedit=array('Branch','BudgetDesc','EncashDetails','TIN','DebitAccount','Amount');
}
	
$columnslist=array('DebitAccount','Branch','FromBudgetOf','BudgetDesc');
$listsname=array('DebitAccount'=>'accounts','FromBudgetOf'=>'entities','Branch'=>'allbranches','BudgetDesc'=>'typelist');
$liststoshow=array();
$listcondition='';
$whichotherlist='acctg';
$otherlist=array('accounts');
$method='POST';
$action='preditclientside.php?w=DepEncashSubEdit&TxnSubId='.$txnsubid.'&TxnID='.$txnid;

$sql='Select s.*, ca.ShortAcctID as DebitAccount, CONCAT(t.CompanyName, " ",t.Address) as CompanyName,Entity as FromBudgetOf,Branch,BudgetDesc  from `acctg_2depencashsub` as s join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID join `1branches` b on b.BranchNo=s.BranchNo LEFT JOIN `acctg_1budgetentities` be on be.EntityID=s.FromBudgetOf left join `acctg_1branchpreapprovedbudgetlist` l on l.TypeID=s.TypeID LEFT JOIN `gen_info_1tinforexpenses` t on t.TIN=s.TIN where TxnSubId='.$txnsubid;
//echo $sql;
include('../backendphp/layout/rendersubform.php');
		break;


case 'BouncedfromCR':
$fromlast=0; $addlurl=''; goto gohere;
case 'BouncedfromCRLast':
$fromlast=1; $addlurl='&fromlast=1';
gohere:

    if (!allowedToOpen(5992,'1rtc')) { echo 'No permission'; exit; }  
$columnnames=array('DateBounced','ClientName','CheckNo','CheckBank','Remarks','CreditAccountID','Amount','DateofFirstInv','EncodedBy'); 
$columnstoedit=array('DateBounced','CreditAccountID','Remarks','DateofFirstInv');
	
$columnslist=array('CreditAccountID');
$listsname=array('CreditAccountID'=>'banks');
$liststoshow=array('allbanks');
$listcondition='';
$whichotherlist='acctg';
$otherlist=array();

$method='POST';
$action='preditclientside.php?w=BouncedfromCREdit'.$addlurl.'&TxnID='.$txnid;
if($fromlast==0){
$sql='SELECT sb.*, m.CheckNo, m.CheckBank, c.ClientName, FORMAT(SUM(s.Amount),2)  AS Amount, e.Nickname AS EncodedBy, sb.CreditAccountID FROM acctg_2collectmain m JOIN acctg_2collectsub s ON m.TxnID=s.TxnID JOIN acctg_2collectsubbounced sb ON m.TxnID=sb.TxnID
JOIN `1clients` c ON c.ClientNo=m.ClientNo
JOIN `1employees` e ON e.IDNo=sb.EncodedByNo WHERE m.TxnID='.$txnid;
} else {
$sql='SELECT sb.*,PDCNo AS CheckNo, PDCBank AS CheckBank, c.ClientName, AmountOfPDC AS Amount, e.Nickname AS EncodedBy, sb.CreditAccountID FROM acctg_3undepositedpdcfromlastperiod m JOIN acctg_3undepositedpdcfromlastperiodbounced sb ON m.UndepPDCId=sb.UndepPDCId JOIN `1clients` c ON c.ClientNo=m.ClientNo JOIN `1employees` e ON e.IDNo=sb.EncodedByNo JOIN acctg_1chartofaccounts ca ON ca.AccountID=sb.CreditAccountID WHERE m.UndepPDCId='.$txnid;	
}
include('../backendphp/layout/rendersubform.php');
    break;
		
case 'TxfrMainEdit':
    if (!allowedToOpen(5952,'1rtc')) { echo 'No permission'; exit; }  
$columnnames=array('Date','FromBranchNo','CreditAccount','Remarks');
$columnstoedit=array('Date','FromBranchNo','CreditAccount','Remarks');
	
$columnslist=array('CreditAccount');
$listsname=array('CreditAccount'=>'accounts');
$liststoshow=array();
$listcondition='';
$whichotherlist='acctg';
$otherlist=array('accounts');

$method='POST';
$action='preditclientside.php?w=TxfrMainEdit&TxnID='.$txnid;

$sql='Select m.*, ca.ShortAcctID as CreditAccount from `acctg_2txfrmain` m join acctg_1chartofaccounts ca on ca.AccountID=m.CreditAccountID where TxnID='.$txnid;

include('../backendphp/layout/rendersubform.php');
		break;

case 'TxfrSubEdit':
    if (!allowedToOpen(5952,'1rtc')) { echo 'No permission'; exit; }  
$txnsubid=$_REQUEST['TxnSubId'];
$columnnames=array('ClientBranchNo','Particulars','DebitAccount','DateIN','Amount','Remarks');
$columnstoedit=array('ClientBranchNo','Particulars','DebitAccount','DateIN','Amount','Remarks');
	
$columnslist=array('DebitAccount');
$listsname=array('DebitAccount'=>'accounts');
$liststoshow=array();
$listcondition='';
$whichotherlist='acctg';
$otherlist=array('accounts');
$method='POST';
$action='preditclientside.php?w=TxfrSubEdit&TxnSubId='.$txnsubid.'&TxnID='.$txnid;

$sql='Select s.*, ca.ShortAcctID as DebitAccount from `acctg_2txfrsub` as s  join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID where TxnSubId='.$txnsubid;
//echo $sql;
include('../backendphp/layout/rendersubform.php');
		break;
		
}

  $link=null; $stmt=null;
?>
