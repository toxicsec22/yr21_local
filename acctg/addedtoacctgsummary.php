<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(513,'1rtc')) { echo 'No permission'; exit; }
$showbranches=true; include_once('../switchboard/contents.php');
 


$title='Added to Accounting';

$date=(!isset($_REQUEST['Date'])?date('Y-m-d',strtotime("-1 days")):$_REQUEST['Date']);
$formdesc='Summary of retrieved data for '.$date;
?>
<title><?php echo $title; ?></title>
<div style="width: 100%;">
<form method="post" action="addedtoacctgsummary.php" enctype="multipart/form-data">
    Choose Date:  <input type="date" name="Date" value="<?php echo $date; ?>"></input> &nbsp; &nbsp; 
<input type="submit" name="submit" value="Lookup"></form> <br><br>
<div style="margin-left: 5%; width: 40%; float: left;">
    <h3><?php echo $title; ?></h3><h5><?php echo $formdesc; ?></h5>
<?php

$sql0='CREATE TEMPORARY TABLE totals AS 
SELECT m.BranchNo, DebitAccountID AS AccountID,SUM(Amount) AS Subtotal 
FROM `acctg_2salemain` m JOIN `acctg_2salesub` s ON m.TxnID=s.TxnID WHERE m.Date=\''.$date.'\' GROUP BY m.BranchNo, DebitAccountID
UNION ALL 
SELECT m.BranchNo, CreditAccountID AS AccountID,SUM(Amount)*-1 AS Subtotal 
FROM `acctg_2salemain` m JOIN `acctg_2salesub` s ON m.TxnID=s.TxnID WHERE m.Date=\''.$date.'\' GROUP BY m.BranchNo, CreditAccountID
;';
    $stmt0=$link->prepare($sql0);    $stmt0->execute();
    
$sql0='CREATE TEMPORARY TABLE invsales
SELECT BranchNo, PaymentType FROM `invty_2sale` WHERE `Date`=\''.$date.'\' GROUP BY BranchNo,PaymentType;';
    $stmt0=$link->prepare($sql0);    $stmt0->execute();

$subtitle='<BR>Cash Sales Encoded';
$sql='SELECT s.BranchNo, Branch, AccountID,FORMAT(SUM(Subtotal),2) AS Total  FROM `totals` s
JOIN `1branches` b ON b.BranchNo=s.BranchNo WHERE AccountID=100 GROUP BY s.BranchNo, AccountID;';
$columnnames=array('BranchNo', 'Branch','Total');

include('../backendphp/layout/displayastableonlynoheaders.php');


$subtitle='<BR>Charge Sales and Audit Charges Encoded';
$sql='SELECT s.BranchNo, Branch, AccountID,FORMAT(SUM(Subtotal),2) AS Total  FROM `totals` s
JOIN `1branches` b ON b.BranchNo=s.BranchNo WHERE AccountID=200 GROUP BY s.BranchNo, AccountID;';
$columnnames=array('BranchNo', 'Branch','Total');
include('../backendphp/layout/displayastableonlynoheaders.php');


$subtitle='<BR>Transfer OUT Encoded';
$sql='SELECT m.FromBranchNo, Branch,FORMAT(SUM(Amount),2) AS Total 
FROM `acctg_2txfrmain` m JOIN `acctg_2txfrsub` s ON m.TxnID=s.TxnID 
JOIN `1branches` b ON b.BranchNo=m.FromBranchNo WHERE m.Date=\''.$date.'\' GROUP BY m.FromBranchNo';
$columnnames=array('FromBranchNo','Branch','Total');
include('../backendphp/layout/displayastableonlynoheaders.php');

$subtitle='<BR>Transfer IN Encoded';
$sql='SELECT s.ClientBranchNo AS ToBranchNo, Branch,FORMAT(SUM(Amount),2) AS Total 
FROM `acctg_2txfrmain` m JOIN `acctg_2txfrsub` s ON m.TxnID=s.TxnID 
JOIN `1branches` b ON b.BranchNo=s.ClientBranchNo WHERE s.DateIN=\''.$date.'\' GROUP BY s.ClientBranchNo';
$columnnames=array('ToBranchNo','Branch','Total');
include('../backendphp/layout/displayastableonlynoheaders.php');
?></div><div style="margin-left: 0%; width: 45%; float: right;">
<?php

$subtitle='<BR>Cash Sales - Missing Data in Acctg';
$sql='SELECT inv.BranchNo, Branch,PaymentType  FROM invsales inv LEFT JOIN `totals` s ON inv.BranchNo=s.BranchNo
JOIN `1branches` b ON b.BranchNo=inv.BranchNo WHERE s.BranchNo IS NULL AND PaymentType=1;';
$columnnames=array('BranchNo', 'Branch');
include('../backendphp/layout/displayastableonlynoheaders.php');


$subtitle='<BR>Charge Sales - Missing Data in Acctg';
$sql='SELECT inv.BranchNo, Branch,PaymentType  FROM invsales inv LEFT JOIN `totals` s ON inv.BranchNo=s.BranchNo
JOIN `1branches` b ON b.BranchNo=inv.BranchNo WHERE s.BranchNo IS NULL AND PaymentType=2;';
$columnnames=array('BranchNo', 'Branch');
include('../backendphp/layout/displayastableonlynoheaders.php');

$subtitle='<BR>Transfer OUT - Missing Data in Acctg';
$sql='SELECT inv.BranchNo AS FromBranchNo, Branch  FROM `invty_2transfer` inv LEFT JOIN `acctg_2txfrmain` tm ON inv.BranchNo=tm.FromBranchNo AND inv.DateOUT=tm.Date
JOIN `1branches` b ON b.BranchNo=inv.BranchNo WHERE inv.BranchNo<>inv.ToBranchNo AND inv.DateOUT=\''.$date.'\' AND tm.FromBranchNo IS NULL GROUP BY inv.BranchNo ;';
$columnnames=array('FromBranchNo','Branch');
include('../backendphp/layout/displayastableonlynoheaders.php');

$subtitle='<BR>Transfer IN - Missing Data in Acctg';
$sql='SELECT inv.ToBranchNo, Branch  FROM `invty_2transfer` inv LEFT JOIN `acctg_2txfrsub` ts ON inv.ToBranchNo=ts.ClientBranchNo AND inv.DateIN=ts.DateIN
JOIN `1branches` b ON b.BranchNo=inv.ToBranchNo WHERE inv.BranchNo<>inv.ToBranchNo AND inv.DateIN=\''.$date.'\' AND ts.ClientBranchNo IS NULL GROUP BY inv.ToBranchNo ;';
$columnnames=array('ToBranchNo','Branch');
include('../backendphp/layout/displayastableonlynoheaders.php');
  $link=null; $stmt=null;
?>
</div>
</div>