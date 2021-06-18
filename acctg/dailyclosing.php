<!--<html>
<head>
<title><?php echo !isset($title)?'Daily Closing':$title; ?></title>-->
<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(528,529,530,2501);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
    $showbranches=false;
include_once('../switchboard/contents.php');

 

include_once('../backendphp/layout/regulartablestyle.php');
?>
</head>
<body>
<?php


 
$showbranches=false;
$whichqry=$_GET['w'];

switch ($whichqry){
    
case 'SetStaticDataToday':
    if (!allowedToOpen(2501,'1rtc')) { echo 'No permission'; exit; }
    $whichdata='withcurrent'; $reportmonth=(date('Y')<>substr($_SESSION['nb4A'],0,4)?12:date('m'));
    require("maketables/makefixedacctgdata.php"); if($_SESSION['(ak0)']==1002) { echo 'error is here';}
    if(isset($_GET['done']) and $_GET['done']==1){ echo 'Static Data created.';}
    header("Location:".$_SERVER['HTTP_REFERER'].(strpos($_SERVER['HTTP_REFERER'],'?')?'&':'?')."done=1");
    break;

case 'AcctgVsInvtySalesTxfr':
    if (!allowedToOpen(528,'1rtc')) { echo 'No permission'; exit; }
$title='Sales - Acctg Vs Invty';
?>

<!-- <div style="float:left"> -->
<?php
//echo 'Sales - Acctg vs. Invty<br><br>'; ADD BACK FREIGHT INCL into Acctg Value
$sql0='Create temporary table dailysales (
BranchNo smallint(6)  null,
Branch varchar(30)  null,
AcctgDate date  null,
InvtyDate date  null,
AcctgCash double null, AcctgCashOP double null,
InvtyCash double null, InvtyCashOP double null,
AcctgCharge double null, AcctgChargeOP double null,
InvtyCharge double null, InvtyChargeOP double null
)

Select sm.BranchNo, b.Branch, sm.Date as AcctgDate, sv.Date as InvtyDate, sum(case when ss.DebitAccountID=100 AND ss.Particulars NOT LIKE "exchanged%" then ss.Amount end) as AcctgCash, 
IFNULL(sum(case when ss.DebitAccountID=704 AND ss.ClientNo=10000 then ss.Amount end),0) AS AcctgCashOP, sv.InvtyCash, 
(SELECT SUM(Amount) FROM `invty_7opapproval` a JOIN `invty_2sale` sm2 ON sm2.TxnID=a.TxnID WHERE sm2.BranchNo=sm.BranchNo AND `Date`=sm.Date AND txntype=1) AS InvtyCashOP,
sum(case when ss.DebitAccountID in (200,721,925) and (ss.Particulars NOT LIKE \'%FreightAdjIncl%\') AND ss.CreditAccountID<>925 then ss.Amount end) as AcctgCharge, IFNULL(sum(case when ss.DebitAccountID=704 AND ss.ClientNo<>10000 then ss.Amount end),0) AS AcctgChargeOP, sv.InvtyCharge, (SELECT SUM(Amount) FROM `invty_7opapproval` a JOIN `invty_2sale` sm2 ON sm2.TxnID=a.TxnID WHERE sm2.BranchNo=sm.BranchNo AND `Date`=sm.Date AND txntype=2) AS InvtyChargeOP  
from acctg_2salemain sm 
join acctg_2salesub ss on sm.TxnID=ss.TxnID 
join `1branches` b on b.BranchNo=sm.BranchNo 
right join `invty_901salevalues` sv on sv.BranchNo=sm.BranchNo and sv.Date=sm.Date
 group by sm.BranchNo, sm.Date
union
Select sm.BranchNo, b.Branch, sm.Date as AcctgDate, sv.Date as InvtyDate, sum(case when ss.DebitAccountID=100 AND ss.Particulars NOT LIKE "exchanged%" then ss.Amount end) as AcctgCash, 0 AS AcctgCashOP, sv.InvtyCash, 0  AS InvtyCashOP, 
sum(case when ss.DebitAccountID in (200,721,925) then ss.Amount end) as AcctgCharge, 0 AS AcctgChargeOP, sv.InvtyCharge, 0  AS InvtyChargeOP 
from acctg_2salemain sm 
join acctg_2salesub ss on sm.TxnID=ss.TxnID 
join `1branches` b on b.BranchNo=sm.BranchNo 
left join `invty_901salevalues` sv on sv.BranchNo=sm.BranchNo and sv.Date=sm.Date
where  (sv.Date is null) group by sm.BranchNo, sm.Date';
/*UNION
Select ism.BranchNo, b.Branch, ism.Date as AcctgDate, ism.Date as InvtyDate, sum(case when ism.PaymentType=1 then fc.Amount end) as AcctgCash, 0 AS AcctgCashOP, 0 AS InvtyCash,  0 as AcctgCharge, 0 AS AcctgChargeOP, 0 AS InvtyCharge 
from `invty_2sale` ism 
join `1branches` b on b.BranchNo=ism.BranchNo 
join `approvals_2freightclients` fc on ism.BranchNo=fc.BranchNo and ism.Date=fc.Date AND fc.ForInvoiceNo=ism.SaleNo AND fc.txntype=ism.txntype
where  (PriceFreightInclusive=1) group by ism.BranchNo, ism.Date;
';*/
// REMOVED sum(case when ism.PaymentType<>1 then fc.Amount end) FOR ACCTG CHARGE
//echo $sql0; break; Month(sm.Date)='.$_REQUEST[$fieldname].' and 
$stmt=$link->prepare($sql0);
$stmt->execute();

//$sql='SELECT `BranchNo`,`Branch`,`AcctgDate`, `InvtyDate`, format(`AcctgCash`,2) as `AcctgCash`,format(`InvtyCash`,2) as `InvtyCash`,format(ifnull(`AcctgCash`,0)-ifnull(`InvtyCash`,0),2) as `DiffCash`,format(`AcctgCharge`,2) as `AcctgCharge`,format(`InvtyCharge`,2) as `InvtyCharge`,format(ifnull(`AcctgCharge`,0)-ifnull(`InvtyCharge`,0),2) as `DiffCharge` FROM dailysales having `DiffCash`<-0.05 Or `DiffCash`>0.05 or `DiffCharge`<-0.05 Or `DiffCharge`>0.05 order by Branch, AcctgDate';

$columnnames=array('Branch','AcctgDate','AcctgCash','InvtyCash','DiffCashSales','AcctgCashOP','InvtyCashOP','DiffCashOP','AcctgCharge','InvtyCharge','DiffChargeSales','AcctgChargeOP','InvtyChargeOP','DiffChargeOP','InvtyDate');
    $columnsub=$columnnames;
    $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Branch, AcctgDate');

$sql='SELECT `BranchNo`,`Branch`,`AcctgDate`, `InvtyDate`, format(SUM(`AcctgCash`),2) as `AcctgCash`, format(SUM(`InvtyCash`),2) as `InvtyCash`,
format(ifnull(SUM(`AcctgCash`),0)-ifnull(SUM(`InvtyCash`),0)-ifnull(SUM(`InvtyCashOP`),0),2) as `DiffCashSales`,
format(SUM(`AcctgCashOP`),2) as `AcctgCashOP`,format(SUM(`InvtyCashOP`),2) as `InvtyCashOP`,
format(ifnull(SUM(`AcctgCashOP`),0)-ifnull(SUM(`InvtyCashOP`),0),2) as `DiffCashOP`,
format(SUM(`AcctgCharge`),2) as `AcctgCharge`, format(SUM(`InvtyCharge`),2) as `InvtyCharge`,
format(ifnull(SUM(`AcctgCharge`),0)-ifnull(SUM(`InvtyCharge`),0)-ifnull(SUM(`InvtyChargeOP`),0),2) as `DiffChargeSales`,
format(SUM(`AcctgChargeOP`),2) as `AcctgChargeOP`, format(SUM(`InvtyChargeOP`),2) as `InvtyChargeOP`,
format(ifnull(SUM(`AcctgChargeOP`),0)-ifnull(SUM(`InvtyChargeOP`),0),2) as `DiffChargeOP` FROM dailysales
GROUP BY `BranchNo`,`AcctgDate`, `InvtyDate`
HAVING `DiffCashSales`<-0.05 Or `DiffCashSales`>0.05 or `DiffChargeSales`<-0.05 Or `DiffChargeSales`>0.05 or 
`DiffCashOP`<-0.05 Or `DiffCashOP`>0.05 or `DiffChargeOP`<-0.05 Or `DiffChargeOP`>0.05 order by '.$sortfield;

    
    // $showtotals=false; $showgrandtotal=true; $runtotal=true;
    include('../backendphp/layout/displayastable.php');
?>
<!-- </div>
<div style="float:right"> -->
<?php
$title='Interbranch Transfers Sales - Acctg Vs Invty';

$sql0='Create temporary table dailytransfers (
BranchNo smallint(6) null,
Branch varchar(30) null,
Date date  null,
AcctgTxfr double  null,
AcctgWriteOff double null,
InvtyTxfr double null,
InvtyDate date null,
InvtyBranch varchar(30) null
)
Select tm.FromBranchNo as BranchNo, b.Branch, tm.Date, sum(case when ts.DebitAccountID=204 then ts.Amount end) as AcctgTxfr, sum(case when ts.DebitAccountID=805 then ts.Amount end) as AcctgWriteOff,tv.InvtyTxfr, tv.Date as InvtyDate,b1.Branch as InvtyBranch
from acctg_2txfrmain tm
join acctg_2txfrsub ts on tm.TxnID=ts.TxnID 
join `1branches` b on b.BranchNo=tm.FromBranchNo 
right join `invty_902txfrvalues` tv on tv.BranchNo=tm.FromBranchNo and tv.Date=tm.Date
join `1branches` b1 on b1.BranchNo=tv.BranchNo 
where Year(tv.`Date`)='.$currentyr.' group by tm.FromBranchNo, tm.Date, tv.Date,tv.BranchNo
union
Select tm.FromBranchNo as BranchNo, b.Branch, tm.Date, sum(case when ts.DebitAccountID=204 then ts.Amount end) as AcctgTxfr, sum(case when ts.DebitAccountID=805 then ts.Amount end) as AcctgWriteOff, 0 as InvtyTxfr, null as InvtyDate, null as InvtyBranch
from acctg_2txfrmain tm
join acctg_2txfrsub ts on tm.TxnID=ts.TxnID 
join `1branches` b on b.BranchNo=tm.FromBranchNo 
left join `invty_2transfer` tv on tv.TransferNo=ts.Particulars
where (tv.TransferNo is null) group by tm.FromBranchNo, tm.Date, tv.TransferNo';
//echo $sql0.'<br>'; break;
$stmt=$link->prepare($sql0);
$stmt->execute();
unset ($sortfield);
$columnnames=array('Branch','Date','AcctgTxfr','AcctgWriteOff','InvtyTxfr','InvtyDate','InvtyBranch','Diff');
    $columnsub=$columnnames;
    $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Branch, Date, InvtyBranch,InvtyDate');

$sql='SELECT `BranchNo`,`Branch`,`Date`,truncate(`AcctgTxfr`,2) as `AcctgTxfr`,truncate(`AcctgWriteOff`,2) as `AcctgWriteOff`,truncate(`InvtyTxfr`,2) as `InvtyTxfr`,ifnull(`AcctgTxfr`,0)+ifnull(`AcctgWriteOff`,0)-ifnull(`InvtyTxfr`,0) as `Diff`, InvtyBranch,InvtyDate FROM dailytransfers having Diff<-0.05 Or Diff>0.05 order by '.$sortfield;
    
    include('../backendphp/layout/displayastable.php');
?>
<!-- </div> -->
<?php
//}
break;

case 'YrTotalsAcctgVsInvty':
    if (!allowedToOpen(530,'1rtc')) { echo 'No permission'; exit; }

$title='Discrepancies for the Year'; $formdesc='Both tables are based on same data. Discrepancy on one side DOES NOT CANCEL the discrepancy on the other.';
$showbranches=false;
$whichdata='withcurrent'; $reportmonth=(date('Y')<>substr($_SESSION['nb4A'],0,4)?12:date('m')); require('maketables/makefixedacctgdata.php');
$link1=connect_db("".$currentyr."_1rtc",1);
$sql0='drop table if exists `acctg_endinvvalues`'; $stmt=$link1->prepare($sql0); $stmt->execute();
   $sql0='Create table acctg_endinvvalues as
SELECT Month(m.Date) as `Month`, round(sum(s.UnitPrice*s.Qty),2) as EndValue, "InvtySales" as Col, m.BranchNo, 4 as Compare FROM invty_2salesub s join `invty_2sale` m on m.TxnID=s.TxnID where m.txntype<>5 group by Month(m.Date), m.BranchNo
union all
SELECT Month(m.Date) as `Month`, round(sum(s.UnitPrice*s.Qty*-1),2) as EndValue, "InvtyReturns" as Col, m.BranchNo, 5 as Compare FROM invty_2salesub s join `invty_2sale` m on m.TxnID=s.TxnID where m.txntype=5 group by Month(m.Date), m.BranchNo
union all
SELECT Month(m.DateOUT) as `Month`, round(sum(s.UnitPrice*s.QtySent),2) as EndValue, "InvtyTxfrOut", m.BranchNo, 2 as Compare FROM `invty_2transfersub` s join `invty_2transfer` m on m.TxnID=s.TxnID where m.BranchNo<>m.ToBranchNo  and (m.DateOUT>=\''.$currentyr.'-1-1\') group by Month(m.DateOUT), m.BranchNo
union all
SELECT Month(m.DateIN) as `Month`, round(sum(s.UnitCost*s.QtyReceived),2) as EndValue, "InvtyTxfrIn", m.ToBranchNo, 3 as Compare FROM `invty_2transfersub` s join `invty_2transfer` m on m.TxnID=s.TxnID where m.BranchNo<>m.ToBranchNo and (m.DateIN is not null) and (UnitPrice<>0 and QtySent<>0)  group by Month(m.DateIN), m.ToBranchNo
union all
SELECT Month(m.DateIN) as `Month`, round(sum(s.UnitCost*s.QtyReceived),2) as EndValue, "InvtyTxfrInFromLastYr", m.ToBranchNo, 8 as Compare FROM `invty_2transfersub` s join `invty_2transfer` m on m.TxnID=s.TxnID where m.BranchNo<>m.ToBranchNo and (m.DateIN is not null) and (s.UnitPrice=0 and s.QtySent=0)  group by Month(m.DateIN), m.ToBranchNo
union all
SELECT Month(m.Date) as `Month`, round(sum(s.UnitCost*s.Qty),2) as EndValue, "InvtyMRR", m.BranchNo, 1 as Compare FROM `invty_2mrrsub` s join `invty_2mrr` m on m.TxnID=s.TxnID where txntype<>9 and txntype<>8 group by Month(m.Date), m.BranchNo
union all
SELECT Month(m.Date) as `Month`, round(sum(s.UnitCost*s.Qty),2) as EndValue, "InvtyStoreUsed", m.BranchNo, 6 as Compare FROM `invty_2mrrsub` s join `invty_2mrr` m on m.TxnID=s.TxnID where txntype=9 group by Month(m.Date), m.BranchNo
union all
SELECT Month(m.Date) as `Month`, round(sum(s.UnitCost*s.Qty),2) as EndValue, "InvtyPurchReturn", m.BranchNo, 7 as Compare FROM `invty_2mrrsub` s join `invty_2mrr` m on m.TxnID=s.TxnID where txntype=8 group by Month(m.Date), m.BranchNo';
//echo $sql0; 
$stmt=$link1->prepare($sql0); $stmt->execute();
$sql0='drop table if exists `acctg_endacctgvalues`'; $stmt=$link1->prepare($sql0); $stmt->execute();
$sql1='Create table acctg_endacctgvalues as
SELECT Month(m.Date) as `Month`, round(sum(Amount),2) as EndValue, "AcctgSales" as Col, m.BranchNo, 4 as Compare FROM `acctg_2salesub` s join `acctg_2salemain` m on m.TxnID=s.TxnID where CreditAccountID BETWEEN 700 AND 703 group by Month(m.Date), m.BranchNo 
union all
SELECT Month(m.Date) as `Month`, round(sum(Amount),2) as EndValue, "AcctgReturns" as Col, m.BranchNo, 5 as Compare FROM `'.$currentyr.'_static`.`acctg_0unialltxns` m where AccountID=705 group by Month(m.Date), m.BranchNo
union all
SELECT Month(m.Date) as `Month`, round(sum(Amount),2) as EndValue, "AcctgTxfrOut" as Col, m.FromBranchNo, 2 as Compare FROM `acctg_2txfrsub` s join `acctg_2txfrmain` m on m.TxnID=s.TxnID where s.DebitAccountID in (204,805) group by Month(m.Date), m.FromBranchNo
union all
SELECT Month(s.DateIn) as `Month`, round(sum(Amount),2) as EndValue, "AcctgTxfrIn" as Col, ClientBranchNo, 3 as Compare FROM `acctg_2txfrsub` s  where s.DebitAccountID=204 and (s.DateIn is not null) group by Month(s.DateIn), s.ClientBranchNo
union all
SELECT Month(s.DateIn) as `Month`, round(sum(Balance),2) as EndValue, "AcctgTxfrInFromLastYr" as Col, ToBranchNo, 8 as Compare FROM `acctg_3unpdinterbranchlastperiod` s  where s.ARAccount=204 and (s.DateIn>=\''.$currentyr.'-1-1\') group by Month(s.DateIn), s.ToBranchNo
union all
SELECT Month(m.Date) as `Month`, round(sum(Amount),2) as EndValue, "AcctgPurchases" as Col, m.BranchNo, 1 as Compare FROM `acctg_2purchasemain` m join `acctg_2purchasesub` s on `m`.TxnID=`s`.TxnID where m.TxnID in (Select TxnID from acctg_2purchasesub where DebitAccountID IN (300)) and Amount>=0 group by Month(m.Date), m.BranchNo
union all
SELECT Month(m.Date) as `Month`, round(sum(Amount),2) as EndValue, "AcctgPurchReturn" as Col, m.BranchNo, 7 as Compare FROM `acctg_2purchasemain` m join `acctg_2purchasesub` s on `m`.TxnID=`s`.TxnID where m.TxnID in (Select TxnID from acctg_2purchasesub where DebitAccountID IN (300,331)) and Amount<0 group by Month(m.Date), m.BranchNo
union all
SELECT Month(m.Date) as `Month`, round(sum(Amount)*-1,2) as EndValue, "AcctgStoreUsed" as Col, s.BranchNo, 6 as Compare FROM `acctg_2jvmain` m join `acctg_2jvsub` s on m.TxnID=s.TxnID where DebitAccountID in (919,302) and CreditAccountID IN (300) group by Month(m.Date), s.BranchNo';
$stmt=$link1->prepare($sql1);
$stmt->execute();

$sqlleft='select i.Month, Branch, a.Col as WhereInAcctg, i.Col as WhereInInvty,format(sum(ifnull(i.EndValue,0)),2) as InvtyValue, format(sum(ifnull(a.EndValue,0)),2) as AcctgValue, format((sum(ifnull(i.EndValue,0))-sum(ifnull(a.EndValue,0))),2) as Diff from acctg_endinvvalues i
left join acctg_endacctgvalues a on (i.BranchNo=a.BranchNo and i.`Month`=a.`Month` and i.Compare=a.Compare)
join `1branches` b ON i.BranchNo = b.BranchNo
 group by i.`Month`, i.BranchNo, i.Compare having Diff>1 or Diff<-1
order by Branch, Month';

$columnnamesleft=array('Month','Branch','WhereInAcctg','AcctgValue','WhereInInvty','InvtyValue','Diff');
$lefttabletitle='Invty has data, Acctg may not have data';
//include('../backendphp/layout/displayastable.php');
$sqlright='select a.Month, Branch, a.Col as WhereInAcctg, i.Col as WhereInInvty,format(sum(ifnull(i.EndValue,0)),2) as InvtyValue, format(sum(ifnull(a.EndValue,0)),2) as AcctgValue,format((sum(ifnull(i.EndValue,0))-sum(ifnull(a.EndValue,0))),2) as Diff from acctg_endinvvalues i
right join acctg_endacctgvalues a on (i.BranchNo=a.BranchNo and i.`Month`=a.`Month` and i.Compare=a.Compare)
join `1branches` b ON a.BranchNo = b.BranchNo
 group by a.`Month`, a.BranchNo, a.Compare having Diff>1 or Diff<-1 
order by Branch, Month';
$columnnamesright=array('Month','Branch','WhereInAcctg','AcctgValue','WhereInInvty','InvtyValue','Diff');
$righttabletitle='Acctg has data, Invty may not have data';
//}
include('../backendphp/layout/twotablessidebyside.php');
   break;

case 'CreditableWTax': //THIS IS WRONG!  MUST SEPARATE PER INVOICE. THERE IS CURRENT PROBLEM WITH OR STRUCTURE
    if (!allowedToOpen(529,'1rtc')) { echo 'No permission'; exit; }
   $title='Creditable WTax In Collections';
   ?>THIS IS WRONG! <BR>
<form method="post" action="dailyclosing.php?w=CreditableWTax" enctype="multipart/form-data">
Choose Month (1 - 12):  <input type="text" size=5 name="Month" value="<?php echo date('m'); ?>"></input>&nbsp &nbsp &nbsp 
<input type="submit" name="lookup" value="Lookup"> </form>
<?php
if (!isset($_REQUEST['Month'])){
   include('../backendphp/layout/clickontabletoedithead.php');
   goto noform;
} else {
   //$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Branch, CollectNo');
   $sql='SELECT Date,CollectNo, Branch, ClientName, FORMAT(SUM(os.Amount),2) AS Collected, FORMAT(SUM(osd.Amount),2) AS EWT, ROUND((SUM(osd.Amount)/SUM(os.Amount))*100,2) AS `Rate%`  FROM `acctg_2collectmain` om JOIN `acctg_2collectsub` os ON om.TxnID=os.TxnID JOIN acctg_2collectsubdeduct osd ON om.TxnID=osd.TxnID JOIN `1branches` b ON b.BranchNo=om.BranchSeriesNo JOIN `1clients` c ON c.ClientNo=om.ClientNo WHERE MONTH(`Date`)='.$_REQUEST['Month'].' GROUP BY om.TxnID
ORDER BY Branch, CollectNo';
   $columnnames=array('Date','CollectNo', 'Branch', 'ClientName','Collected','EWT','Rate%'); //$columnsub=$columnnames;
   include_once('../backendphp/layout/displayastable.php');
}
   break;
}
noform:
      $stmt=null; $link=null;
?>
</body>
</html>