<html>
<head>
<title>Search</title>
</head>
<body>
<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6461,'1rtc')) { echo 'No permission'; exit; }
$showbranches=true; include_once('../switchboard/contents.php'); 
 
include_once('../backendphp/layout/regulartablestyle.php');


?><br>
<h3>Search Data</h3><br><p style="font-size:small"><i> Pls inform me if you want other searches - JYE</i></p><br><br>
&nbsp &nbsp <form style="display:inline" action='#' method='POST'>Search for check deposits:&nbsp &nbsp 
    <input type='text' name='stringsearch' autocomplete='off' size='10' >&nbsp &nbsp <input type='submit' name='submit' value='Check Deposits'></form><br><br>
&nbsp &nbsp <form style="display:inline" action='#' method='POST'>Search for invoices paid to supplier:
    <input type='text' name='stringsearch' autocomplete='off' size='10' >&nbsp &nbsp <input type='submit' name='submit' value='Paid to Suppliers'>  </form><br><br>
&nbsp &nbsp <form style="display:inline" action='#' method='POST'>Search for payees in check vouchers:
    <input type='text' name='stringsearch' autocomplete='off' size='10' >&nbsp &nbsp <input type='submit' name='submit' value='Payees'>  </form><br><br>
&nbsp &nbsp <form style="display:inline" action='#' method='POST'>Search for client invoices paid in collection receipts or deposits:
    <input type='text' name='stringsearch' autocomplete='off' size='10' >&nbsp &nbsp <input type='submit' name='submit' value='Client Invoices Paid'>  </form><br><br>
&nbsp &nbsp <form style="display:inline" action='#' method='POST'>Search for amount in protected static data (no comma):
    <input type='text' name='stringsearch' autocomplete='off' size='10' >&nbsp &nbsp <input type='submit' name='submit' value='Amount'>  </form><br><br>
&nbsp &nbsp <form style="display:inline" action='#' method='POST'>Search for text in particulars in protected static data:
    <input type='text' name='stringsearch' autocomplete='off' size='10' >&nbsp &nbsp <input type='submit' name='submit' value='Particulars'>  </form><br><br>
&nbsp &nbsp <form style="display:inline" action='#' method='POST'>Search for client:
    <input type='text' name='stringsearch' autocomplete='off' size='10' >&nbsp &nbsp <input type='submit' name='submit' value='Clients'>  </form><br><br>
<?php
if (!isset($_POST['submit'])){    goto noform;}

switch ($_POST['submit']){
case 'Check Deposits':
   $txnidname='TxnID';
   $editprocess='addeditdep.php?w=Deposit&TxnID=';
   $sql='SELECT dm.TxnID, dm.Date, dm.DepositNo, ds.CheckNo, Sum(ds.Amount) AS AmtofCheck, dm.Cleared, `ca`.`ShortAcctID` AS `Bank`, dm.Posted
FROM acctg_2depositmain dm INNER JOIN acctg_2depositsub ds ON dm.TxnID=ds.TxnID
join `acctg_1chartofaccounts` `ca` ON ((`dm`.`DebitAccountID` = `ca`.`AccountID`))
where ds.CheckNo like \'%'.$_POST['stringsearch'].'%\'
GROUP BY dm.DepositNo, ds.CheckNo;';
$columnnames=array('Date','DepositNo','CheckNo','AmtofCheck','Cleared','Bank');
   break;

case 'Paid to Suppliers':
   $txnidname='TxnID';
   $editprocess='formcv.php?w=CV&TxnID=';
   $sql='Select vm.CVNo, vm.Date,vm.PayeeNo, vm.Payee, vm.Cleared as PaymentClearedOn, ca.ShortAcctID as BankCheck, vs.ForInvoiceNo,SUM(vs.Amount) as Amount from acctg_2cvmain vm join acctg_2cvsub vs on vm.CVNo=vs.CVNo join acctg_1chartofaccounts ca on ca.AccountID=vm.CreditAccountID where vs.ForInvoiceNo like  \'%'.$_POST['stringsearch'].'%\' GROUP BY vs.ForInvoiceNo';
$columnnames=array('Date','Payee','ForInvoiceNo','Amount','PaymentClearedOn','BankCheck');
   break;

case 'Payees':
   $txnidname='CVNo';
   $editprocess='formcv.php?w=CV&CVNo=';
   $sql='Select vm.CVNo, vm.Date,vm.PayeeNo, vm.Payee, vm.ReleaseDate, vm.Cleared as PaymentClearedOn, ca.ShortAcctID as BankCheck, SUM(vs.Amount) AS AmountofCheck from acctg_2cvmain vm join acctg_2cvsub vs on vm.CVNo=vs.CVNo join acctg_1chartofaccounts ca on ca.AccountID=vm.CreditAccountID where vm.Payee like  \'%'.$_POST['stringsearch'].'%\' GROUP BY CVNo';
$columnnames=array('Date','Payee','AmountofCheck','ReleaseDate','PaymentClearedOn','BankCheck');
   break;

case 'Client Invoices Paid':
   $txnidname='TxnID';
   $editprocess='addeditdep.php?w=Deposit&TxnID=';
   include_once '../generalinfo/unionlists/ECList.php';
   $sql='Select m.TxnID, m.Date, m.`DepositNo`, Branch, s.`ForChargeInvNo`, c.BECSName as ClientName, m.Cleared as DepositClearedOn, s.Amount from acctg_2depositmain m join acctg_2depositsub s on m.TxnID=s.TxnID JOIN `ECList` c ON c.BECSNo=s.`ClientNo` AND c.BECS=IF(s.`ClientNo`<9999,"E","C") JOIN `1branches` b ON b.BranchNo=s.BranchNo WHERE s.`ForChargeInvNo` LIKE \'%'.$_POST['stringsearch'].'%\' GROUP BY TxnID;';
$columnnames=array('Date','DepositNo','Branch','ClientName','ForChargeInvNo','Amount','PaymentClearedOn');
$subtitle='<br><br>Results for: '.$_POST['submit'].' - Deposits';
include('../backendphp/layout/displayastableonlynoheaders.php');
   $editprocess='addeditclientside.php?w=Collect&TxnID=';
   $sql='Select m.TxnID, m.Date, m.`CollectNo`, Branch, s.`ForChargeInvNo`, c.BECSName as ClientName,s.Amount from acctg_2collectmain m join acctg_2collectsub s on m.TxnID=s.TxnID JOIN `ECList` c ON c.BECSNo=m.`ClientNo` AND c.BECS=IF(m.`ClientNo`<9999,"E","C") JOIN `1branches` b ON b.BranchNo=s.BranchNo WHERE s.`ForChargeInvNo` LIKE \'%'.$_POST['stringsearch'].'%\' GROUP BY TxnID;';
$columnnames=array('Date','CollectNo','Branch','ClientName','ForChargeInvNo','Amount');
   break;
   
case 'Amount':
   $txnidname='TxnID';
   $sql='SELECT u.*, Branch, ShortAcctID AS Account FROM '.$currentyr.'_static.acctg_unialltxns u JOIN `1branches` b ON b.BranchNo=u.BranchNo '
           . ' JOIN acctg_1chartofaccounts ca on ca.AccountID=u.AccountID '
           . ' WHERE Amount='.$_POST['stringsearch'].' OR Amount='.($_POST['stringsearch']*-1).' OR Amount='.($_POST['stringsearch']+.1).' OR Amount='.(($_POST['stringsearch']+.1)*-1);
$columnnames=array('Date','ControlNo','Supplier/Customer/Branch','Particulars','Account','Branch','Amount','Entry','w','TxnID');
   break;

case 'Particulars':
   $txnidname='TxnID';
   $sql='SELECT u.*, Branch, ShortAcctID AS Account FROM '.$currentyr.'_static.acctg_unialltxns u JOIN `1branches` b ON b.BranchNo=u.BranchNo '
           . ' JOIN acctg_1chartofaccounts ca on ca.AccountID=u.AccountID '
           . ' WHERE Particulars LIKE \'%'.$_POST['stringsearch'].'%\' OR `Supplier/Customer/Branch` LIKE \'%'.$_POST['stringsearch'].'%\'';
$columnnames=array('Date','ControlNo','Supplier/Customer/Branch','Particulars','Account','Branch','Amount','Entry','w','TxnID');
   break;

case 'Clients':
   $txnidname='ClientNo';
   $colstosearch=array('ClientNo', 'TelNo1', 'TelNo2', 'Mobile','ContactPerson','EmailAddress','ARClientType','Terms','CreditLimit','Remarks','StreetAddress','Barangay','TownOrCity','Province','Inactive','EncodedByNo');
   $sql='';
   foreach ($colstosearch as $col){
       $sql=$sql.' OR `'.$col.'` LIKE  \'%'.$_POST['stringsearch'].'%\'';
   }
   $sql='SELECT * FROM 1clients WHERE ClientName LIKE  \'%'.$_POST['stringsearch'].'%\' '.$sql;
$columnnames=array('ClientNo', 'ClientName', 'TelNo1', 'TelNo2', 'Mobile','ContactPerson','EmailAddress','ARClientType','Terms','TIN','CreditLimit', 'PORequired','Remarks','StreetAddress','Barangay','TownOrCity','Province','Inactive','EncodedByNo','TimeStamp');
   break;
   
case 'Collection Number':

 $txnidname='TxnID';
 
   $editprocess='addeditclientside.php?w=Collect&TxnID=';
   
   $sql='SELECT cm.TxnID, Branch, cm.CollectNo, cm.Date, cm.ClientNo, c.ClientName, cm.CheckNo,Sum(cs.Amount) AS AmtofCheck, `ca`.`ShortAcctID` AS `DebitAccount`, cm.Posted FROM acctg_2collectmain cm LEFT JOIN acctg_2collectsub cs ON cm.TxnID=cs.TxnID LEFT JOIN 1clients c ON cm.ClientNo=c.ClientNo JOIN `acctg_1chartofaccounts` `ca` ON ((`cm`.`DebitAccountID` = `ca`.`AccountID`)) 
JOIN `1branches` b ON b.BranchNo=cm.BranchSeriesNo
WHERE cm.CollectNo like \'%'.$_POST['stringsearch'].'%\' GROUP BY cm.CollectNo ORDER BY Branch, cm.CollectNo;';

$columnnames=array('Branch','Date','CollectNo','ClientName','CheckNo','AmtofCheck','DebitAccount','Posted');

   break;
   
  
   
} 

$subtitle='<br><br>Results for: '.$_POST['submit'];
include('../backendphp/layout/displayastableonlynoheaders.php');
noform:
     $link=null; $stmt=null; 
?>