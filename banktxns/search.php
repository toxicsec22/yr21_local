<html>
<head>
<title>Search</title>
</head>
<body>
<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6254,'1rtc')) {   echo 'No permission'; exit;} 
include_once('../switchboard/contents.php'); 
 include_once('../backendphp/layout/regulartablestyle.php');
 
?><br>
<h3>Search Data</h3><br><p style="font-size:small"><i> Pls inform me if you want other searches - JYE</i></p><br><br>
&nbsp &nbsp <form style="display:inline" action='#' method='POST'>Search amount in deposits:&nbsp &nbsp 
    <input type='text' name='stringsearch' autocomplete='off' size='10' >&nbsp &nbsp <input type='submit' name='submit' value='Deposits'></form><br><br>
&nbsp &nbsp <form style="display:inline" action='#' method='POST'>Search amount in withdrawals:
    <input type='text' name='stringsearch' autocomplete='off' size='10' >&nbsp &nbsp <input type='submit' name='submit' value='Withdrawals'>  </form><br><br>
&nbsp &nbsp <form style="display:inline" action='#' method='POST'>Search in other fields:
    <input type='text' name='stringsearch' autocomplete='off' size='10' >&nbsp &nbsp <input type='submit' name='submit' value='Others'>  </form><br><br>
<?php
if (!isset($_POST['submit'])){    goto noform;}

$sqlbank='SELECT b.*, ShortAcctID AS Account FROM `banktxns_banktxns` b JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=b.AccountID ';
$txnidname='TxnNo';
$columnnames=array('TxnNo','Account','TxnDate','Particulars','BankBranch','CheckNo','BankTransCode','WithdrawAmt','DepositAmt','Remarks','Cleared','ClearedByNo','ClearedTS');

switch ($_POST['submit']){

case 'Deposits':
   $sqlcondition=' WHERE (ABS(DepositAmt-'.str_replace(',', '', $_POST['stringsearch']).')<=0.5) AND WithdrawAmt=0 ';
   $sql=$sqlbank.$sqlcondition; 
   break;

case 'Withdrawals':
   $sqlcondition=' WHERE (ABS(WithdrawAmt-'.str_replace(',', '', $_POST['stringsearch']).')<=0.5) AND DepositAmt=0 ';
   $sql=$sqlbank.$sqlcondition;
   break;
   
case 'Others':
   $colstosearch=array('TxnDate','Particulars','BankBranch','CheckNo','BankTransCode');
   $sql0='';
   foreach ($colstosearch as $col){
       $sql0=$sql0.' OR `'.$col.'` LIKE  \'%'.$_POST['stringsearch'].'%\'';
   }
   $sql=$sqlbank.' WHERE b.`Remarks` LIKE  \'%'.$_POST['stringsearch'].'%\' OR b.`AccountID` LIKE  \'%'.$_POST['stringsearch'].'%\'  '.$sql0; 
   break;

} 
if($_SESSION['(ak0)']==1002){ echo $sql;}
$subtitle='<br><br>Results for: '.$_POST['submit'];
include('../backendphp/layout/displayastableonlynoheaders.php');
noform:
      $link=null; $stmt=null;
?>