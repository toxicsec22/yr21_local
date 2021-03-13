<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

        // check if allowed
        $allowed=array(999,5962,5401,601,5921);$allow=0;
        foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
        if ($allow==0) { echo 'No permission'; exit;}
        allowed:
        // end of check
$showbranches=false; include_once('../switchboard/contents.php');

$title='Edit Acctg Txn';
    $minorswitch=$_SERVER['HTTP_REFERER'];
    $minorswitchname='Back';

$processblank='';
$processlabelblank='';
$whichqry=$_REQUEST['w'];
switch ($whichqry){

case 'PurchaseEdit':
    $txnid=intval($_REQUEST['TxnID']);
$columnnames=array('Date','SupplierName','SupplierInv','DateofInv','MRRNo','Terms','CreditAccount','BranchNo','Remarks','RCompany', 'RegisteredSupplier');
$columnstoedit=array('Date','SupplierName','SupplierInv','DateofInv','MRRNo','Terms','CreditAccount','BranchNo','Remarks','RCompany', 'RegisteredSupplier');
	
$columnslist=array('CreditAccount','SupplierName','BranchNo','RCompany', 'RegisteredSupplier');
$listsname=array('CreditAccount'=>'accounts','SupplierName'=>'suppliers','BranchNo'=>'branches','RCompany'=>'companies','RegisteredSupplier'=>'suppliers');
$liststoshow=array('branches','suppliers','companies');
$listcondition='';
$whichotherlist='acctg';
$otherlist=array('accounts');

$method='POST';
$action='preditsupplyside.php?w=PurchaseEdit&TxnID='.$txnid;

$sql='Select m.*, c.Company as RCompany,ca.ShortAcctID as CreditAccount, s.SupplierName, s1.SupplierName AS RegisteredSupplier from `acctg_2purchasemain` m 
 join acctg_1chartofaccounts ca on ca.AccountID=m.CreditAccountID  join `1branches` as b on b.BranchNo=m.BranchNo
left join `1companies` as c on c.CompanyNo=m.RCompany
 join `1suppliers` s on s.SupplierNo=m.SupplierNo
 LEFT join `1suppliers` s1 on s1.SupplierNo=m.RegisteredSupplierNo
 where `m`.TxnID='.$txnid;

include('../backendphp/layout/rendersubform.php');
		break;

case 'FutureCVMainEdit';
case 'CVMainEdit':
    $txnid=intval($_REQUEST['CVNo']);
if ($whichqry=='FutureCVMainEdit'){$table='4future';} else { $table='2';}
$columnnames=array('Date','DueDate','PaymentMode','CVNo','CheckNo','DateofCheck','Payee','CreditAccount','Remarks');
$columnstoedit=array('Date','DueDate','PaymentMode','CVNo','CheckNo','DateofCheck','Payee','CreditAccount','Remarks');

	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT PaymentModeID,PaymentMode FROM `acctg_0paymentmodes` ORDER BY PaymentModeID;','PaymentModeID','PaymentMode','pmlist');

$columnslist=array('CreditAccount','Payee','PaymentMode');
$listsname=array('CreditAccount'=>'accounts','Payee'=>'suppliers','PaymentMode'=>'pmlist');
$liststoshow=array('suppliers');
$listcondition='';
$whichotherlist='acctg';
$otherlist=array('accounts');

$method='POST';
$action='preditsupplyside.php?w='.$whichqry.'&CVNo='.$txnid;

$sql='Select m.*,PaymentMode, ca.ShortAcctID as CreditAccount, Payee from `acctg_'.$table.'cvmain` m 
JOIN acctg_0paymentmodes pm ON m.PaymentModeID=pm.PaymentModeID
 join acctg_1chartofaccounts ca on ca.AccountID=m.CreditAccountID where CVNo='.$txnid;

include('../backendphp/layout/rendersubform.php');
		break;

case 'CVSubEdit':
    $txnid=intval($_REQUEST['CVNo']);
$txnsubid=$_REQUEST['TxnSubId'];
$columnnames=array('Particulars','BranchNo','ForInvoiceNo','DebitAccount','Amount');
$columnstoedit=array('Particulars','BranchNo','ForInvoiceNo','DebitAccount','Amount');
	
$columnslist=array('DebitAccount');
$listsname=array('DebitAccount'=>'accounts');
$liststoshow=array('branches');
$listcondition='';
    $whichotherlist='acctg';
    $otherlist=array('unpaidsuppinvoices');
$method='POST';
$action='preditsupplyside.php?w=CVSubEdit&TxnSubId='.$txnsubid.'&CVNo='.$txnid;

$sql='Select s.*,ca.ShortAcctID as DebitAccount from `acctg_2cvsub` as s join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID where TxnSubId='.$txnsubid;
//echo $sql;
include('../backendphp/layout/rendersubform.php');
		break;

case 'JVMainEdit':
    $txnid=intval($_REQUEST['JVNo']);
        $title='Add/Edit JV'; $table='acctg_2jvmain'; 
    
$columnnames=array('JVDate','JVNo','Remarks');
$columnstoedit=array('JVDate','JVNo','Remarks');
	
$columnslist=array();
$listsname=array();
$liststoshow=array();
$method='POST';
$action='preditsupplyside.php?w='.$whichqry.'&JVNo='.$txnid;

$sql='Select m.* from `'.$table.'` m where JVNo='.$txnid;

include('../backendphp/layout/rendersubform.php');
		break;

case 'JVSubEdit':
    $txnid=intval($_REQUEST['JVNo']);
$txnsubid=$_REQUEST['TxnSubId'];

$table='acctg_2jvsub';
$columnnames=array('Date','Particulars','BranchNo','DebitAccount','CreditAccount','Amount');
$columnstoedit=array('Date','Particulars','BranchNo','DebitAccount','CreditAccount','Amount');

	
$columnslist=array('DebitAccount','CreditAccount','BranchNo');
$listsname=array('DebitAccount'=>'accounts','CreditAccount'=>'accounts','BranchNo'=>'branches');
$liststoshow=array('branches');
$listcondition='';
    $whichotherlist='acctg';
    $otherlist=array('accounts');
$method='POST';
$action='preditsupplyside.php?w='.$whichqry.'&TxnSubId='.$txnsubid.'&JVNo='.$txnid;

$sql='Select s.*,ca.ShortAcctID as DebitAccount,ca1.ShortAcctID as CreditAccount from `'.$table.'` as s join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID
join acctg_1chartofaccounts ca1 on ca1.AccountID=s.CreditAccountID where TxnSubId='.$txnsubid;
//echo $sql;
include('../backendphp/layout/rendersubform.php');
		break;
		
}

  $link=null; $stmt=null;
?>
