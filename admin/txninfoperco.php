<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(5963); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
 
include_once('../switchboard/contents.php');
  

$which=$_GET['w'];

switch ($which){
        
case 'PurchasePerCo':
if (!allowedToOpen(5963,'1rtc')) { echo 'No permission'; exit;} 
$formdesc='</i><br><br>'
        . '<form method=GET action="txninfoperco.php"  enctype="multipart/form-data">'
        . 'Choose Month (1-12) &nbsp; <input type=text name=Month size=5><input type=hidden name=w value="PurchasePerCo"> &nbsp;<input type=submit name=Lookup>'
        . '</form>'
        . '<i><br><br>';
$month=(!isset($_GET['Month'])?date('m'):$_GET['Month']);
$pagetouse='&Month='.$month;
$title=implode(' ', array_slice(explode(' ', $_SESSION['*cname']), 0, 1));
$title=strtoupper(date('F',strtotime(''.$currentyr.'-'.$month.'-01'))).' Purchases of '.strtoupper($title); 
$columnnames=array('Date','SupplierName','SupplierInv','DateofInv','Amount','MRRNo','Remarks','SenttoAcctg');

$sql='SELECT m.TxnID, DATE_FORMAT(m.Date,"%b %d, %Y") AS Date, s.SupplierName, m.SupplierInv, DATE_FORMAT(m.DateofInv,"%b %d, %Y") AS DateofInv, FORMAT(SUM(ps.Amount),2) AS Amount, SUM(ps.Amount) AS AmountValue, m.MRRNo, m.Remarks, 1 AS SenttoAcctg FROM `acctg_2purchasemain` m JOIN `acctg_2purchasesub` ps ON `m`.TxnID=`ps`.TxnID  JOIN `1suppliers` s on s.SupplierNo=m.SupplierNo WHERE MONTH(m.Date)='.$month .' AND m.RCompany='.$_SESSION['*cnum'].' GROUP BY m.TxnID 
 UNION ALL
 SELECT m.TxnID, DATE_FORMAT(m.Date,"%M %d, %Y") AS Date, s1.SupplierName, m.SuppInvNo, DATE_FORMAT(m.SuppInvDate,"%M %d, %Y") AS DateofInv, FORMAT(SUM(Qty*UnitCost),2) AS Amount,SUM(Qty*UnitCost) AS AmountValue, m.MRRNo, m.Remarks, m.SenttoAcctg FROM `invty_2mrr` m JOIN `invty_2mrrsub` s ON m.TxnID=s.TxnID JOIN `1suppliers` s1 on s1.SupplierNo=m.SupplierNo WHERE MONTH(m.Date)='.$month .' AND m.RCompany='.$_SESSION['*cnum'].' AND SenttoAcctg<>1 AND txntype=6 GROUP BY m.TxnID ';
$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' Date, SupplierName, SupplierInv'); $columnsub=$columnnames;
        $sql=$sql.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');   

//if($_SESSION['(ak0)']==1002){ echo $sql;}
$coltototal='AmountValue';$showgrandtotal=true; 
$editprocess='../acctg/formpurch.php?w=Purchase&TxnID='; $editprocesslabel='Lookup';
include_once('../backendphp/layout/displayastablenosort.php');

    break; 
}

noreport:
     $link=null; $stmt=null; 
?>