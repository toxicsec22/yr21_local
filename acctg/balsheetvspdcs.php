<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(557,'1rtc')) { echo 'No permission'; exit; } 
$showbranches=false; include_once('../switchboard/contents.php');
?>
<html>
<head>
<title>Data Errors</title>
</head>
<body>
<h3>Data Errors</h3><br>
<?php
echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';
$showsubtitlealways=true; 
$subtitle='Bal Sheet Vs Unpaid Invoices';
$link=connect_db("".$currentyr."_1rtc",1); 
// $whichdata='withcurrent'; $reportmonth=((date('Y')<>substr($_SESSION['nb4A'],0,4))?12:date('m')); require ('maketables/makefixedacctgdata.php');


$sql0='drop table if exists `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`'; $stmt=$link->prepare($sql0); $stmt->execute();
$sql0='drop table if exists `acctg_dailyclose_endapar'.$_SESSION['(ak0)'].'`'; $stmt=$link->prepare($sql0); $stmt->execute();
require('sqlphp/sqldailyclose.php');
$stmt=$link->prepare($sql0);$stmt->execute();
$stmt=$link->prepare($sql1);$stmt->execute();

$columnnames=array('Account','Branch','BSAmt', 'InvBalances','Diff');  
$sql='select dc.*, b.Branch, ca.ShortAcctID as Account from acctg_dailyclose_endapar'.$_SESSION['(ak0)'].' dc
join `1branches` b ON dc.BranchNo = b.BranchNo
join acctg_1chartofaccounts ca on ca.AccountID=dc.AccountID   where (Diff<-0.1 or Diff>0.1)   order by Account,Branch'; //or (BSAmt<>0 and InvBalances<>0)
include('../backendphp/layout/displayastableonlynoheaders.php');

 // 
$sql0='drop table if exists `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`'; $stmt=$link->prepare($sql0); $stmt->execute();
$sql0='drop table if exists `acctg_dailyclose_endapar'.$_SESSION['(ak0)'].'`'; $stmt=$link->prepare($sql0); $stmt->execute();

endofreport:
      $stmt=null; $link=null;
?><BR>END OF REPORT
</body>
</html>