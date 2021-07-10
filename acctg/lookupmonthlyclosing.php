<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(562,563,564,565,566,567,568); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=false; include_once('../switchboard/contents.php');

 
include ('../backendphp/layout/standardprintsettings.php');



$whichqry=$_GET['w'];

switch ($whichqry){

case 'NetGovt':
    if (!allowedToOpen(568,'1rtc')) { echo 'No permission'; exit; }
$title='Net Govt Payables';

$sql0='Create temporary table netgovt (
Month smallint(2) not null,
BranchNo smallint(6) not null,
NetPerMonth double null,
AccountID smallint(6) not null
)
Select Month(m.Date) as `Month`, s.BranchNo,truncate(sum(s.Amount),2) as NetPerMonth, DebitAccountID as AccountID from acctg_2cvmain m join acctg_2cvsub s on m.CVNo=s.CVNo where DebitAccountID>=501 and  DebitAccountID<=510 and DebitAccountID NOT IN (506,509)
group by Month(m.Date), s.BranchNo, s.DebitAccountID 
union
Select 1 as `Month`, BranchNo, truncate(BegBalance,2) as NetPerMonth, AccountID from acctg_1begbal where AccountID>=501 and  AccountID<=510 and  AccountID NOT IN (506,509)';
$stmt=$link->prepare($sql0);
$stmt->execute();

$columnmonths=array(1,2,3,4,5,6,7,8,9,10,11,12);
    $sql='';
    
$columnnames=array('Account', 'Branch', 'EndBalance');
   foreach ($columnmonths as $month){
      $sql=$sql.'round(Sum(Case when Month='.$month.' then NetPerMonth end),2) as \''.$month.'\', ';
      $columnnames[]=$month;
   }
   $sql='Select c.ShortAcctID as Account, b.Branch, '. $sql.' round(Sum(NetPerMonth),2) as EndBalance from netgovt g
   join `1branches` b on b.BranchNo=g.BranchNo
   join acctg_1chartofaccounts c on c.AccountID=g.AccountID 
   group by Account, Branch having EndBalance<-0.01 or EndBalance>0.01';
//echo $sql; break;

    include('../backendphp/layout/displayastable.php');

break;

case 'FridayCash':
if (!allowedToOpen(566,'1rtc')) { echo 'No permission'; exit; }
?><br>
   <form method="post" action="lookupacctgAP.php?w=AutoVch" enctype="multipart/form-data">
      Payments due on :  <input type="date" name="PayAsOf" value="<?php echo date('Y-m-d',strtotime("this Friday")); ?>"></input>
      Start of temporary check number <input type="text" name="CheckNo" size=1 value="0"></input>
      <input type="submit" name="lookup" value="Auto Enter Check Vouchers for Suppliers"> </form>
   <br><br>
   <?php
 
$title='Cash Needed Per Friday';
include_once '../banktxns/sqlphp/dataforclearchecks.php';
$sql0='Create temporary table amountdue (
DateDue date not null,
IssuedChecks double null,
Purchases double null
)
SELECT (`DateDue` + interval mod(6-DAYOFWEEK(`DateDue`)+7,7) day) AS `DateDue`,  0 AS IssuedChecks, sum(bal.PayBalance) AS Purchases
FROM acctg_23balperinv bal left join `1suppliers` s on s.SupplierNo=bal.SupplierNo where bal.PayBalance<>0
GROUP BY  (`DateDue` + interval mod(6-DAYOFWEEK(`DateDue`)+7,7) day)
UNION ALL SELECT Date_Add(`DateofCheck`, INTERVAL mod(6-DAYOFWEEK(`DateofCheck`)+7,7) DAY) DateofCheck, Sum(AmountofCheck) AS IssuedChecks, 0 AS Purchases
FROM `banktxns_22unclearedcheckamts` group by DateofCheck ;';
$stmt=$link->prepare($sql0);
$stmt->execute();

$lefttabletitle='<h3>Due This Friday</h3>';
$sqlleft='Select DateDue, format(Sum(IssuedChecks),2) as IssuedChecks, format(Sum(Purchases),2) as Purchases, Sum(IssuedChecks)+ Sum(Purchases) as IssuedandPurchases from amountdue where DateDue<=DATE_ADD(Now(),INTERVAL mod(6-DAYOFWEEK(Now())+7,7) DAY)  group by DateDue order by DateDue';
//echo $sqlleft; 
$columnnamesleft=array('DateDue','IssuedChecks','Purchases');
$sqltotalleft='Select Sum(IssuedChecks) AS TotalIssuedChecks, Sum(Purchases) AS TotalPurchases from amountdue where DateDue<=DATE_ADD(Now(),INTERVAL mod(6-DAYOFWEEK(Now())+7,7) DAY)';
$stmt=$link->query($sqltotalleft);
$resultleft=$stmt->fetch();
$totalleft='<td>Totals</td><td>'.number_format($resultleft['TotalIssuedChecks'],2).'</td><td>'.number_format($resultleft['TotalPurchases'],2).'</td><td>'.number_format($resultleft['TotalIssuedChecks']+$resultleft['TotalPurchases'],2).'</td>';

$righttabletitle='<h3>Future Fridays</h3>';
$sqlright='Select DateDue, format(Sum(IssuedChecks),2) as IssuedChecks, format(Sum(Purchases),2) as Purchases, Sum(IssuedChecks)+ Sum(Purchases) as IssuedandPurchases from amountdue where DateDue>DATE_ADD(Now(),INTERVAL mod(6-DAYOFWEEK(Now())+7,7) DAY)  group by DateDue order by DateDue';
$columnnamesright=array('DateDue','IssuedChecks','Purchases');
$sqltotalright='Select Sum(IssuedChecks) AS TotalIssuedChecks, Sum(Purchases) AS TotalPurchases from amountdue where DateDue>DATE_ADD(Now(),INTERVAL mod(6-DAYOFWEEK(Now())+7,7)+7 DAY)';
$stmt=$link->query($sqltotalright);
$resultright=$stmt->fetch();
$totalright='<td>Totals</td><td>'.number_format($resultright['TotalIssuedChecks'],2).'</td><td>'.number_format($resultright['TotalPurchases'],2).'</td>';
include('../backendphp/layout/twotablessidebyside.php');

break;

case 'AutoPost':
if (!allowedToOpen(563,'1rtc')) { echo 'No permission'; exit; }

$title='Auto Post as of Date'; 
?><title><?php echo $title; ?></title>
<h2><?php echo $title; ?></h2><br><br>
<form method="post" action="lookupmonthlyclosing.php?w=AutoPost" enctype="multipart/form-data">
Post all as of:  <input type="date" size=5 name="asofdate" value="<?php echo date('Y-m-d'); ?>"></input>&nbsp &nbsp &nbsp 
<input type="submit" name="lookup" value="Lookup"> </form>
<?php
if (!isset($_REQUEST['asofdate'])){    

?>
<div style='background-color: #e6e6e6;
  width: 300px; border: 2px solid grey;
  padding: 25px; margin: 25px; 
  font-size: 14px; font-style: Arial;'>
The following will be posted in accounting data:<br><br>
<ul>
   <li>Deposits</li>
   <li>Collection Receipts</li>
   <li>Purchases</li>
   <li>Sales</li>
   <li>Interbranch Transfers</li>
   <li>Journal Vouchers</li>
   <li>Check Vouchers</li>
   <li>Future Check Vouchers</li>
   <li>Assets and Depreciation</li>
   <li>Prepaid Expenses & Amortization</li>
</ul>
</div>

<?php

   goto noformmonthlyclosing;
} 
else {
include_once ('../backendphp/functions/postperdate.php');
   $date=$_REQUEST['asofdate'];

   $posttables=array('acctg_2depositmain','acctg_2collectmain','acctg_2purchasemain','acctg_2salemain','acctg_2txfrmain');
   foreach ($posttables as $table){ postperdate($link,$table,$date,false);   }
   postperdate($link,'acctg_2cvmain',$date,'CV');
   postperdate($link,'acctg_4futurecvmain',$date,'FCV');
   postperdate($link,'acctg_2jvmain',$date,'JV');
   postperdate($link,'acctg_1assets',$date,'Assets');
   postperdate($link,'acctg_2prepaid',$date,'Prepaid');
    header('Location:/'.$url_folder.'/index.php?done=1');
}

break;
case 'AcctgInvtyTotalsMonth':
    if (!allowedToOpen(562,'1rtc')) { echo 'No permission'; exit; }
   ?>
<form action='lookupmonthlyclosing.php?w=AcctgInvtyTotalsMonth' method='POST'>
    For the month (1-12)<input type='text' name='month' autocomplete='off' size='2' style="text-align: center" value="<?php echo (substr($_SESSION['nb4A'],5,2)==12?1:substr($_SESSION['nb4A'],5,2)+1); ?>">
    <input type='submit' name='submit' value='Lookup'>
    </form>
<?php
if (!isset($_POST['month'])){
   goto noformmonthlyclosing;
}
$month=$_POST['month'];	


$title='Totals for Closing for '.strtoupper(date('F',strtotime(''.$currentyr.'-'.$month.'-1')));
if (($month>substr($_SESSION['nb4A'],5,2)) OR (($month==1) AND (date('Y')<>substr($_SESSION['nb4A'],0,4)))){
   $table='`'.$currentyr.'_static`.`acctg_0unialltxns`'; $reportmonth=(date('Y')<>substr($_SESSION['nb4A'],0,4)?13:date('m')); $whichdata='withcurrent'; require('maketables/makefixedacctgdata.php');
} else {$table='`' . $currentyr . '_static`.`acctg_unialltxns`';}

$sql0='Create temporary table acctg_endvalues(
   EndValue double not null,
   Col varchar(20) not null,
   BranchNo smallint(6) not null
)
SELECT round(sum(s.UnitPrice*s.Qty),2) as EndValue, "InvtySales" as Col, m.BranchNo FROM invty_2salesub s join `invty_2sale` m on m.TxnID=s.TxnID where Month(m.Date)='.$month.' and m.txntype<>5 group by Month(m.Date), m.BranchNo
UNION ALL
SELECT SUM(Amount) AS EndValue, "InvtyFreightEx" AS Col, m.BranchNo FROM approvals_2freightclients fc JOIN `invty_2sale` m on fc.txntype=m.txntype AND fc.ForInvoiceNo=m.SaleNo AND fc.BranchNo=m.BranchNo WHERE PriceFreightInclusive=0 AND Month(m.Date)='.$month.' group by Month(m.Date), m.BranchNo
union all
SELECT round(sum(s.UnitPrice*s.Qty*-1),2) as EndValue, "InvtyReturns" as Col, m.BranchNo FROM invty_2salesub s join `invty_2sale` m on m.TxnID=s.TxnID where Month(m.Date)='.$month.' and m.txntype=5 group by Month(m.Date), m.BranchNo
UNION ALL
SELECT ROUND(SUM(Amount)) AS EndValue, "InvtyOPChargeInv" AS Col, m.BranchNo FROM `invty_7opapproval` ap JOIN `invty_2sale` m on ap.TxnID=m.TxnID WHERE Month(m.Date)='.$month.' AND m.txntype=2 group by Month(m.Date), m.BranchNo
union all
SELECT round(sum(s.UnitPrice*s.QtySent),2) as EndValue, "InvtyTxfrOut", m.BranchNo FROM `invty_2transfersub` s join `invty_2transfer` m on m.TxnID=s.TxnID where Month(m.DateOUT)='.$month.' and m.BranchNo<>m.ToBranchNo group by Month(m.DateOUT), m.BranchNo
union all
SELECT round(sum(s.UnitCost*s.QtyReceived),2) as EndValue, "InvtyTxfrIn", m.ToBranchNo FROM `invty_2transfersub` s join `invty_2transfer` m on m.TxnID=s.TxnID where Month(m.DateIN)='.$month.'  and m.BranchNo<>m.ToBranchNo and (m.DateIN is not null) group by Month(m.DateIN), m.ToBranchNo
union all
SELECT round(sum(s.UnitCost*s.Qty),2) as EndValue, "InvtyMRR", m.BranchNo FROM `invty_2mrrsub` s join `invty_2mrr` m on m.TxnID=s.TxnID where Month(m.Date)='.$month.' group by Month(m.Date), m.BranchNo
union all
SELECT round(sum(Amount),2) as EndValue, "AcctgSales" as Col, m.BranchNo FROM `acctg_2salesub` s join `acctg_2salemain` m on m.TxnID=s.TxnID where (s.DebitAccountID NOT IN (509,704) AND s.CreditAccountID NOT IN (509,405,709)) AND s.Particulars NOT LIKE \'%FreightAdjIncl%\' and Month(m.Date)='.$month.' group by Month(m.Date), m.BranchNo
union all
SELECT round(sum(Amount),2) as EndValue, "AcctgOP" as Col, m.BranchNo FROM `acctg_2salesub` s join `acctg_2salemain` m on m.TxnID=s.TxnID where (s.DebitAccountID IN (704)) and Month(m.Date)='.$month.' group by Month(m.Date), m.BranchNo
union all
SELECT round(sum(Amount),2) as EndValue, "AcctgReturns" as Col, m.BranchNo FROM '.$table.' m where Month(m.Date)='.$month.' and AccountID=705 group by Month(m.Date), m.BranchNo
union all
SELECT round(sum(Amount),2) as EndValue, "AcctgTxfrOut" as Col, m.FromBranchNo FROM `acctg_2txfrsub` s join `acctg_2txfrmain` m on m.TxnID=s.TxnID where Month(m.Date)='.$month.' and s.DebitAccountID in (204,805) group by Month(m.Date), m.FromBranchNo
union all
SELECT round(sum(Amount),2) as EndValue, "AcctgTxfrIn" as Col, ClientBranchNo FROM `acctg_2txfrsub` s  where Month(s.DateIn)='.$month.' and s.DebitAccountID=204 and (s.DateIn is not null) group by Month(s.DateIn), s.ClientBranchNo
union all
SELECT round(sum(Amount),2) as EndValue, "AcctgPurchases" as Col, m.BranchNo FROM `acctg_2purchasemain` m join `acctg_2purchasesub` ps on `m`.TxnID=`ps`.TxnID
join 1suppliers s on m.SupplierNo=s.SupplierNo
where (DebitAccountID IN (300,331,510)) and (Month(m.Date)='.$month.')  and (InvtySupplier=1) group by Month(m.Date), m.BranchNo
union all
SELECT round(sum(Amount)*-1,2) as EndValue, "AcctgPurchases" as Col, s.BranchNo FROM `acctg_2jvmain` m join `acctg_2jvsub` s on m.JVNo=s.JVNo where DebitAccountID=919 and (CreditAccountID IN (300)) and Month(s.Date)='.$month.' group by Month(s.Date), s.BranchNo';
$stmt=$link->prepare($sql0);
$stmt->execute();

$sql1='create temporary table endvaluessummary (
   BranchNo smallint(6) not null,
   InvtyMRR double not null,
   AcctgPurchases double not null,
   InvtyTxfrOut double not null,
   AcctgTxfrOut double not null,
   InvtyTxfrIn double not null,
   AcctgTxfrIn double not null,
   InvtySales double not null,InvtyOPChargeInv double default 0,InvtyFreightEx double default 0,
   AcctgSales double not null, AcctgOP double not null,
   InvtyReturns double not null,
   AcctgReturns double not null
   
)
select ev.BranchNo, ifnull(sum(case when Col="InvtyMRR" then EndValue end),0) as "InvtyMRR",
ifnull(sum(case when Col="AcctgPurchases" then EndValue end),0) as "AcctgPurchases",
ifnull(sum(case when Col="InvtyTxfrOut" then EndValue end),0) as "InvtyTxfrOut",
ifnull(sum(case when Col="AcctgTxfrOut" then EndValue end),0) as "AcctgTxfrOut",
ifnull(sum(case when Col="InvtyTxfrIn" then EndValue end),0) as "InvtyTxfrIn",
ifnull(sum(case when Col="AcctgTxfrIn" then EndValue end),0) as "AcctgTxfrIn", 
ifnull(sum(case when Col="InvtySales" then EndValue end),0) as "InvtySales",
ifnull(sum(case when Col="InvtyOPChargeInv" then EndValue end),0) as "InvtyOPChargeInv",
ifnull(sum(case when Col="InvtyFreightEx" then EndValue end),0) as "InvtyFreightEx",
ifnull(sum(case when Col="AcctgSales" then EndValue end),0) as "AcctgSales", ifnull(sum(case when Col="AcctgOP" then EndValue end),0) as "AcctgOP",
ifnull(sum(case when Col="InvtyReturns" then EndValue end),0) as "InvtyReturns",
ifnull(sum(case when Col="AcctgReturns" then EndValue end),0) as "AcctgReturns"
from acctg_endvalues ev group by ev.BranchNo ;';
$stmt=$link->prepare($sql1);
$stmt->execute();

$sql='Select b.Branch,
format(InvtyMRR,2) as InvtyMRR, format(AcctgPurchases,2) as AcctgPurchases, format(InvtyMRR-AcctgPurchases,2) as DiffPurchases,
format(InvtyTxfrOut,2) as InvtyTxfrOut, format(AcctgTxfrOut,2) as AcctgTxfrOut, format(InvtyTxfrOut-AcctgTxfrOut,2) as DiffTxfrOut,
format(InvtyTxfrIn,2) as InvtyTxfrIn, format(AcctgTxfrIn,2) as AcctgTxfrIn, format(InvtyTxfrIn-AcctgTxfrIn,2) as DiffTxfrIn,
format(InvtySales,2) as InvtySales, format(InvtyOPChargeInv,2) AS InvtyOPChargeInv,format(InvtyFreightEx,2) as InvtyFreightEx, format(AcctgSales,2) as AcctgSales, format(AcctgOP,2) as AcctgOP, format((InvtySales+InvtyFreightEx)-(AcctgSales-AcctgOP),2) as DiffSales,
format(InvtyReturns,2) as InvtyReturns, format(AcctgReturns,2) as AcctgReturns, format(InvtyReturns-AcctgReturns,2) as DiffReturns
from endvaluessummary ev join `1branches` b ON ev.BranchNo = b.BranchNo order by b.Branch';
$columnnames=array('Branch','InvtyMRR','AcctgPurchases','DiffPurchases','InvtyTxfrOut','AcctgTxfrOut','DiffTxfrOut','InvtyTxfrIn','AcctgTxfrIn','DiffTxfrIn','InvtySales','InvtyFreightEx','InvtyOPChargeInv','AcctgSales','AcctgOP','DiffSales','InvtyReturns','AcctgReturns','DiffReturns');
    
    include('../backendphp/layout/displayastable.php');
?>
<!--<ul>Formulas</ul><br><br>

PURCHASES:&nbsp &nbsp &nbsp SUM(MRR)-SUM(Purchases)<br><br>-->
  
<?php    

   break;

case 'InTransit':
    if (!allowedToOpen(567,'1rtc')) { echo 'No permission'; exit; }
   
 $title='Inventory In Transit'; 
$fieldname='Month'; $show=!isset($_POST['show'])?0:$_POST['show'];
$showbranches=FALSE;
//include('../backendphp/layout/showchoosebranch.php');
//?>
<!--<form method="post" action="lookupmonthlyclosing.php?w=InTransit" enctype="multipart/form-data">
Choose Month (1 - 12):  <input type="text" name="//<?php echo $fieldname; ?>" value="<?php echo (!isset($_REQUEST[$fieldname])?date('m'):$_REQUEST[$fieldname]); ?>"></input>
<input type="submit" name="lookup" value="Lookup"><input type=hidden name="show" value="//<?php echo ($show==0?1:0); ?>">
    <input type="submit" name="submit" value="//<?php echo($show==0?'Show All Branches':'Per Branch'); ?>"> </form>-->
<?php
//
//if (!isset($_REQUEST[$fieldname])){
//   include('../backendphp/layout/clickontabletoedithead.php');
//goto noformmonthlyclosing;
//} else {
//$branchcondition=$show==0?' AND (ts.ClientBranchNo='.$_SESSION['bnum'].')':'';
$branchcondition='';
$sql='SELECT  tm.TxnID, Date as DateOUT,`btm`.`Branch` AS `FromBranch`, bts.Branch AS ClientBranch,`ts`.`Particulars` AS `Particulars`,SUM(ts.Amount) AS Amount, DateIN FROM
        (`'.$lastyr.'_1rtc`.`acctg_2txfrmain` `tm` JOIN `'.$lastyr.'_1rtc`.`acctg_2txfrsub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
        JOIN `'.$lastyr.'_1rtc`.`1branches` btm ON btm.BranchNo=tm.FromBranchNo
        JOIN `'.$lastyr.'_1rtc`.`1branches` bts ON bts.BranchNo=ts.ClientBranchNo
    WHERE YEAR(tm.Date)='.$currentyr.' AND  ((`ts`.`DateIN` IS  NULL)  OR ((`ts`.`DateIN` IS NOT NULL)  AND (MONTH(ts.DateIN)<>MONTH(tm.Date)))) '.$branchcondition.' GROUP BY `ts`.`Particulars`;';
//MONTH(tm.Date)='.$_REQUEST[$fieldname].' AND

$columnnames=array('DateOUT','FromBranch','ClientBranch','Particulars','Amount','DateIN');
$coltototal='Amount'; $txnid='TxnID';$showgrandtotal=true;
$filetoopen='addeditclientside'; $w='Interbranch';
$editprocess=$filetoopen.'.php?w='.$w.'&TxnID=';$editprocesslabel='Lookup';   
    include('../backendphp/layout/displayastable.php');
    unset($editprocess,$columnnames);
    echo '<br><br>';
    $subtitle='Total Interbranch Transfers from Warehouses with No Date IN (should be equal to end amount of Sales-Interbranch of warehouses)';
    $sql='SELECT MONTHNAME(`Date`) AS `DateOUTMonth`, MONTHNAME(DateIN) AS `DateINMonth`, Branch AS Warehouse, FORMAT(SUM(Amount),2) AS Amt_No_Date_In, SUM(Amount) AS Amt_No_Date_In_Value FROM acctg_2txfrsub s JOIN `acctg_2txfrmain` m ON m.TxnID=s.TxnID 
JOIN `1branches` b ON b.BranchNo=m.FromBranchNo
WHERE year(Date)='.$currentyr.' AND (MONTH(DateIN)>MONTH(`Date`) OR ISNULL(DateIN)) AND PseudoBranch=2 GROUP BY m.FromBranchNo, MONTH(`Date`),MONTH(DateIN);';
    $columnnames=array('DateOUTMonth','DateINMonth','Warehouse','Amt_No_Date_In');
    $coltototal='Amt_No_Date_In_Value';
    $width='50%';
    include('../backendphp/layout/displayastablenosort.php');
//}
   break;

}
noformmonthlyclosing:
      $link=null; $stmt=null;
?>