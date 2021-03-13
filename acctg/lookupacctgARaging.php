<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(551,552,553,554,5511,5512,5513,5514,5531);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=TRUE; include_once('../switchboard/contents.php');


//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="f6ebf9";
        $rcolor[1]="FFFFFF";


$whichqry=$_GET['w'];

switch ($whichqry){
case 'ARAging':
    if (!allowedToOpen(551,'1rtc')) { echo 'No permission'; exit; }
$title='Aging of Receivables';
$formdesc='Special note: This report counts PDC\'s as cleared.  This is for COLLECTION PURPOSES ONLY.<br><br>';
if (allowedToOpen(5511,'1rtc')) {
   $show=!isset($_POST['show'])?0:$_POST['show'];
?><form style="display:inline" method="post" action="#">
   <input type=hidden name="show" value="<?php echo ($show==0?1:0); ?>">
    <input type="submit" name="submit" value="<?php echo ($show==0?'Show All Branches':'Per Branch'); ?>">
</form><br><br>
<?php
}  else { // branches
   $show=0;
}

$condition=($show==1)?'':' where bal.ClientNo in (Select ClientNo from `acctg_1clientsperbranch` cb where cb.BranchNo='.$_SESSION['bnum'].') ';
$table='acctg_34allarforaging'; $fromdate='Now()'; include('sqlphp/aragingtemptable.php');

$columnnames=array('ClientName','Company','Branch','Before_'.$twoyrsago,'Yr_'.$twoyrsago,'Yr_'.$lastyr,'Beyond3MosOverdue','2to3MosOverdue','1MonthOverdue','2wksOverdue','DueNow','DueNextWk','DueNext2Wks','DueNext3Wks','DueBeyond3Wks','Total');
$colstosum=array('Before_'.$twoyrsago,'Yr_'.$twoyrsago,'Yr_'.$lastyr,'Beyond3MosOverdue','2to3MosOverdue','1MonthOverdue','2wksOverdue','DueNow','DueNextWk','DueNext2Wks','DueNext3Wks','DueBeyond3Wks','Total');
$sql=''; foreach ($colstosum as $tosum){ $sql=$sql.'FORMAT(`'.$tosum.'`,2) AS `'.$tosum.'`,'; }
$sql='SELECT ClientName,Company,Branch,'.rtrim($sql,",").' FROM araging;';
    if (allowedToOpen(5512,'1rtc')) {
         $sqlsum=''; $totalstext=''; $totalstexthead=''; $totalstextpercent='';
         foreach ($colstosum as $tosum){ $sqlsum=$sqlsum.'SUM(`'.$tosum.'`) AS `'.$tosum.'`,'; $totalstexthead=$totalstexthead.'<td>'.$tosum.'</td>';}
         $sqlsum='SELECT '.rtrim($sqlsum,",").' FROM araging;';
         $stmtsum=$link->query($sqlsum); $resultsum=$stmtsum->fetch(); $grandtotal=$resultsum['Total'];
         foreach ($colstosum as $tosum){
            $totalstext=$totalstext.'<td>'.number_format($resultsum[$tosum],0).'</td>';
            $totalstextpercent=$totalstextpercent.'<td>'.number_format(($resultsum[$tosum]/$grandtotal)*100,2).'%</td>';
            }
         $totalstext='<table><tr>'.$totalstexthead.'</tr><tr>'.$totalstext.'</tr><tr>'.$totalstextpercent.'</tr></table>';
         }
    //$showtotals=false; $showgrandtotal=false; $runtotal=false;
    include('../backendphp/layout/displayastable.php');

break;

case 'ActualARAging':
    if (!allowedToOpen(5514,'1rtc')) { echo 'No permission'; exit; }
$title='Actual Aging of Receivables';
    if(!isset($_POST['submit'])) {$show=0;} else { $show=($_POST['submit']=='All Branches' and (allowedToOpen(5514,'1rtc')))?1:0;}
   $defaultdate=!isset($_REQUEST['AsOfDate'])?date('Y-m-d'):$_REQUEST['AsOfDate'];
   $formdesc='Special note: This report recognizes CLEARED PAYMENTS only.<br><br></i><h5>'.($show==0?$_SESSION['@brn']:'All Branches').' as of '.$defaultdate.'</h5><i><br><br>';
?><form style="display:inline" method="post" action="#">Accounts Receivable as of <input type="date" name="AsOfDate" value="<?php echo $defaultdate; ?>"></input>
   &nbsp; &nbsp;   <input type="submit" name="submit" value="Per Branch">
   <?php if (allowedToOpen(5514,'1rtc')) {  ?>   &nbsp; &nbsp;    <input type="submit" name="submit" value='All Branches'>   <?php }  ?>
</form><br><br>

<?php
$condition=($show==1)?'':' where bal.BranchNo='.$_SESSION['bnum'].' ';
include('sqlphp/aragingtemptableforactual.php');
$table='34'; $fromdate='\''.$defaultdate.'\''; include('sqlphp/aragingtemptable.php');

$columnnames=array('ClientName','Company','Branch','Before_'.$twoyrsago,'Yr_'.$twoyrsago,'Yr_'.$lastyr,'Beyond3MosOverdue','2to3MosOverdue','1MonthOverdue','2wksOverdue','DueNow','DueNextWk','DueNext2Wks','DueNext3Wks','DueBeyond3Wks','Total');
$colstosum=array('Before_'.$twoyrsago,'Yr_'.$twoyrsago,'Yr_'.$lastyr,'Beyond3MosOverdue','2to3MosOverdue','1MonthOverdue','2wksOverdue','DueNow','DueNextWk','DueNext2Wks','DueNext3Wks','DueBeyond3Wks','Total');
$sql=''; foreach ($colstosum as $tosum){ $sql=$sql.'FORMAT(`'.$tosum.'`,2) AS `'.$tosum.'`,'; }
$sql='SELECT ClientName,Company,Branch,'.rtrim($sql,",").' FROM araging;';
    if (allowedToOpen(5512,'1rtc')) {
         $sqlsum=''; $totalstext=''; $totalstexthead=''; $totalstextpercent='';
         foreach ($colstosum as $tosum){ $sqlsum=$sqlsum.'SUM(`'.$tosum.'`) AS `'.$tosum.'`,'; $totalstexthead=$totalstexthead.'<td>'.$tosum.'</td>';}
         $sqlsum='SELECT '.rtrim($sqlsum,",").' FROM araging;';
         $stmtsum=$link->query($sqlsum); $resultsum=$stmtsum->fetch(); $grandtotal=$resultsum['Total'];
         foreach ($colstosum as $tosum){
            $totalstext=$totalstext.'<td>'.number_format($resultsum[$tosum],0).'</td>';
            $totalstextpercent=$totalstextpercent.'<td>'.number_format(($resultsum[$tosum]/$grandtotal)*100,2).'%</td>';
            }
         $totalstext='<table><tr>'.$totalstexthead.'</tr><tr>'.$totalstext.'</tr><tr>'.$totalstextpercent.'</tr></table>';
         }
    //$showtotals=false; $showgrandtotal=false; $runtotal=false;
    include('../backendphp/layout/displayastable.php');

break;

case 'ARThisWk':
    if (!allowedToOpen(552,'1rtc')) { echo 'No permission'; exit; }
$title='Collection List This Week'; $formdesc='All receivables due until this Friday';
$method='GET';
if (allowedToOpen(5511,'1rtc')) {
   include('../backendphp/layout/showallbranchesbutton.php');
}  else { // branches
   $show=0;
}
$condition=($show==1)?'':' where bal.ClientNo in (Select ClientNo from `acctg_1clientsperbranch` cb where cb.BranchNo='.$_SESSION['bnum'].') ';
$orderby=($show==1)?' order by b.Branch, c.ClientName ':' order by c.ClientName, b.Branch ';
$sql='select bal.ClientNo, c.ClientName, bal.BranchNo, b.Branch,
sum(case when (`Due` <= (now() + interval (((6 - dayofweek(now())) + 7) % 7) day)) then ARAmount end) as `DueThisFriVal`,
format(sum(case when (`Due` <= (now() + interval (((6 - dayofweek(now())) + 7) % 7) day)) then ARAmount end),2) as `DueThisFri`
from acctg_34allarforaging bal 
JOIN `1clients` c ON (bal.ClientNo =c.ClientNo)
join `1branches` b on b.BranchNo=bal.BranchNo 
'.$condition.' group by bal.ClientNo, bal.BranchNo having `DueThisFri` is not null '.$orderby;
// echo $sql;break;
$coltototal='DueThisFriVal';$showgrandtotal=true; $width='60%';
    $columnnames=array('Branch','ClientName','DueThisFri');
    include('../backendphp/layout/displayastable.php');

break;

case 'ARByBranch':
    if (!allowedToOpen(5512,'1rtc')) { echo 'No permission'; exit;} 
$condition=''; $defaultdate=date('Y-m-d');
include('sqlphp/aragingtemptableforactual.php');
$table='34'; $fromdate='\''.$defaultdate.'\''; include('sqlphp/aragingtemptable.php');
//$tablestyle='  border=1';
$title='AR Aging - Branch Totals';
$formdesc='Special note: This report recognizes CLEARED PAYMENTS only.<br><br>';
$columnnames=array('Branch','Before_'.$twoyrsago,'Yr_'.$twoyrsago,'Yr_'.$lastyr,'Beyond3MosOverdue','2to3MosOverdue','1MonthOverdue','2wksOverdue','DueNow','DueNextWk','DueNext2Wks','DueNext3Wks','DueBeyond3Wks','Total');
$colstosum=array('Before_'.$twoyrsago,'Yr_'.$twoyrsago,'Yr_'.$lastyr,'Beyond3MosOverdue','2to3MosOverdue','1MonthOverdue','2wksOverdue','DueNow','DueNextWk','DueNext2Wks','DueNext3Wks','DueBeyond3Wks','Total');
$sql=''; foreach ($colstosum as $tosum){ $sql=$sql.'FORMAT(SUM(IFNULL(`'.$tosum.'`,0)),0) AS `'.$tosum.'`,'; }
$sql='SELECT Branch,'.rtrim($sql,",").' FROM araging GROUP BY BranchNo ORDER BY Branch;';/* $stmt=$link->query($sql); $result=$stmt->fetchAll(); //echo $sql0.$sql;
$colhead=''; foreach ($columnnames as $col){$colhead=$colhead.'<th>'.$col.'</th>';}
$display=''; foreach ($result as $res){
                $coldisplay='';
                foreach ($columnnames as $col){ $coldisplay=$coldisplay.'<td>'.$res[$col].'</td>'; }
                $display=$display.'<tr>'.$coldisplay.'</tr>';
    }
   */
         $sqlsum=''; $totalstext=''; $totalstextpercent=''; $totalstexthead='';
         foreach ($colstosum as $tosum){ $sqlsum=$sqlsum.'SUM(`'.$tosum.'`) AS `'.$tosum.'`,'; $totalstexthead=$totalstexthead.'<td>'.$tosum.'</td>';}
         $sqlsum='SELECT '.rtrim($sqlsum,",").' FROM araging;';
         $stmtsum=$link->query($sqlsum); $resultsum=$stmtsum->fetch(); $grandtotal=$resultsum['Total'];
         foreach ($colstosum as $tosum){
            $totalstext=$totalstext.'<td>'.number_format($resultsum[$tosum],0).'</td>';
            $totalstextpercent=$totalstextpercent.'<td>'.number_format(($resultsum[$tosum]/$grandtotal)*100,2).'%</td>';
            }
         $totalstext='<table  border=1><tr>'.$totalstexthead.'</tr><tr>'.$totalstext.'</tr>
            <tr>'.$totalstextpercent.'</tr></table>';
/*
$msg='<table  border=1><tr>'.$colhead.'</tr>'.$display.'</table><br><br>'.$totalstext;  */
include('../backendphp/layout/displayastable.php'); 
break;    

case 'PurchandPay':
if (!allowedToOpen(554,'1rtc')) { echo 'No permission'; exit;}  

$title='Client Purchases and Payments';
$fieldname='Client';
$showbranches=false;

include_once('../generalinfo/lists.inc'); 
renderlist('clientsemployees');
   ?>
<form method="post" action="lookupacctgARaging.php?w=PurchandPay" enctype="multipart/form-data">
For Client:  <input type="text" name="<?php echo $fieldname; ?>" list="clientsemployees"></input>&nbsp &nbsp &nbsp
<input type="submit" name="lookup" value="Lookup"> </form>
<?php
if (!isset($_REQUEST[$fieldname])){
include('../backendphp/layout/clickontabletoedithead.php');
goto noform;
} else {
include('../backendphp/functions/getnumber.php');
$clientno=getNumber('ClientEmployee',addslashes($_POST[$fieldname]));
$title='Purchases and Payments - '.$_REQUEST[$fieldname].' ('.$clientno.')';
include 'clientaraddlformdesc.php';

$formdesc='</i>'.$addlformdesc.'<i><br><br> Only CLEARED Collections are counted<br><br>';
$sql0='CREATE TEMPORARY TABLE clientpurchandpay AS 
SELECT clp.ClientNo, 0 AS MonthPurchPay, "Purch" as Col, Sum(clp.Balance) AS ClientPurchPay
FROM `acctg_3unpdclientinvlastperiod` clp WHERE ClientNo='.$clientno.'
GROUP BY clp.ClientNo
union all
SELECT ss.ClientNo, Month(`Date`) AS MonthPurchPay, "Purch" as Col, Sum(ss.Amount) AS ClientPurchPay
FROM acctg_2salemain sm JOIN acctg_2salesub ss ON sm.TxnId = ss.TxnId
WHERE (((ss.DebitAccountID)=200) and ClientNo='.$clientno.')
GROUP BY ss.ClientNo, Month(`Date`)
union all
SELECT ds.ClientNo, Month(`Date`) as MonthPurchPay, "Pay" as Col, Sum(ds.Amount) AS Payments
FROM acctg_2depositmain dm INNER JOIN acctg_2depositsub ds ON dm.TxnID = ds.TxnID
WHERE (ds.CreditAccountID=200) and (ClientNo='.$clientno.') AND (ds.ForChargeInvNo IS NOT NULL)
GROUP BY ds.ClientNo, Month(`Date`)
union all
SELECT cm.ClientNo, Month(`m`.`Cleared`) as MonthPurchPay, "Pay" as Col, Sum(cs.Amount) AS Payments
FROM acctg_2collectmain cm INNER JOIN acctg_2collectsub cs ON (cm.TxnID = cs.TxnID)
JOIN `acctg_2depositsub` `s` ON (((`s`.`CRNo` = CONCAT("C-",cm.BranchSeriesNo,"-",`cm`.`CollectNo`))
            AND (`cm`.`CheckBank` = `s`.`CheckDraweeBank`)
            AND (`s`.`CheckNo` = `cm`.`CheckNo`)
            AND (`s`.`BranchNo` = `cs`.`BranchNo`)
            AND (`cm`.`ClientNo` = `s`.`ClientNo`)))
        JOIN `acctg_2depositmain` `m` ON `m`.`TxnID` = `s`.`TxnID`
    WHERE
        (`cs`.`ForChargeInvNo` IS NOT NULL)
            AND (`m`.`Cleared` IS NOT NULL)
AND (((cs.CreditAccountID)>=200 And (cs.CreditAccountID)<=204 Or (cs.CreditAccountID)=705)) AND cm.ClientNo='.$clientno.'
GROUP BY cm.ClientNo, Month(`m`.`Cleared`)';
$stmt=$link->prepare($sql0);
$stmt->execute();
}
$sql='select cpp.ClientNo,  cpp.MonthPurchPay as Month, sum(case when Col="Purch" then ClientPurchPay end) as Purchases, sum(case when Col="Pay" then ClientPurchPay end) as Payments, ifnull(sum(case when Col="Purch" then ClientPurchPay end),0)-ifnull(sum(case when Col="Pay" then ClientPurchPay end),0) as MonthBal  from clientpurchandpay cpp  group by cpp.ClientNo, cpp.MonthPurchPay';

$coltototal='MonthBal';
   
    $columnnames=array('Month','Purchases','Payments','MonthBal');
    $showtotals=true; $showgrandtotal=true; $runtotal=true; $width='50%';
    include('../backendphp/layout/displayastablenosort.php');
   break;

case 'ChargeInvPerSeries':
    if (!allowedToOpen(553,'1rtc')) { echo 'No permission'; exit;} 
$title='Lookup Charge Invoices Per Series';
$method='GET';
if (allowedToOpen(5531,'1rtc')) {
   $show=!isset($_POST['show'])?0:$_POST['show'];
?><form style="display:inline" method="post" action="#">
   <input type=hidden name="show" value="<?php echo ($show==0?1:0); ?>">
    <input type="submit" name="submit" value="<?php echo ($show==0?'Show All Branches':'Per Branch'); ?>">
</form>&nbsp &nbsp

<?php
}  else { // branches
   $show=0;
}
if (!isset($_POST['fromseries'])){
   $from=0; $to=0;
} else {
   $from=$_POST['fromseries'];$to=$_POST['toseries'];
}
$formdesc='<form method="post" action="#">
   Lookup Series from <input type=text name="fromseries" autofill=off>&nbsp to &nbsp <input type=text name="toseries" autofill=off>
    <input type="submit" name="submitseries" value="Lookup">
</form>&nbsp &nbsp';
$condition=($show==1)?'':' where bal.BranchNo='.$_SESSION['bnum'].' ';
$orderby=($show==1)?' order by `Particulars` ':' order by b.Branch, `Particulars` ';
$sql='SELECT bal.ClientNo, `Particulars` AS Inv, bal.Particulars, date_format(bal.Date,\'%Y-%m-%d\') as Date, format(bal.SaleAmount,2) as SaleAmount, format(bal.RcdAmount,2) as RcdAmount, format(bal.InvBalance,2) as InvBalance, bal.BranchNo, b.Branch, c.ClientName
FROM `acctg_33qrybalperrecpt` bal
JOIN `1clients` c ON (bal.ClientNo =c.ClientNo)
join `1branches` b on b.BranchNo=bal.BranchNo 
'.$condition.'
HAVING ((((`Inv`))>='.$from.' And ((`Inv`))<='.$to.') AND ((InvBalance)<>0)) OR (((`Inv`)>='.$from.' And (`Inv`)<='.$to.') AND ((InvBalance)<>0)) '.$orderby ;

// echo $sql;break;
    $columnnames=array('Branch','Inv','Particulars','ClientName','Date','SaleAmount','RcdAmount','InvBalance');
    include('../backendphp/layout/displayastable.php');
}
noform:
      $link=null; $stmt=null;
?>