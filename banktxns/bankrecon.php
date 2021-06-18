<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 
if (!allowedToOpen(6250,'1rtc')) {   echo 'No permission'; exit;}  
   
$showbranches=false;
include_once('../switchboard/contents.php');
$which=!isset($_GET['w'])?'Recon':$_GET['w'];
?>
<br>
<form method="POST" action='bankrecon.php?w=<?php echo $which;?>' enctype="multipart/form-data">
    Choose Month (1 - 12):  <input type="text" size=5 name="Month" value="<?php echo date('m'); ?>"></input>
<?php
if ($which=='Recon'){
    $title='Bank Reconciliation per Month'; 
?>
<input type="text" name="bank" list="banks" size=60 autocomplete="off" required="true">
<?php include('bankslist.php');}
elseif ($which=='ClearedUncleared') { $title='Cleared/Uncleared per Month';}
?>
<input type="submit" name="lookup" value="Lookup"></form><br><br>
<?php
if ($which=='UpdateEndBal') { goto skipquery;}
if (!isset($_REQUEST['Month'])) { include('../backendphp/layout/clickontabletoedithead.php'); goto noform;}
$reportmonth=$_REQUEST['Month'];
$formdesc= '<i>'.strtoupper(date('F',strtotime(''.$currentyr.'-'.$_POST['Month'].'-1'))).'</i>';
$sql0='CREATE TEMPORARY TABLE accruedunion AS
SELECT `Date` AS VchDate, DateofCheck, Cleared, CheckNo, Payee, Sum(vs.Amount) AS AmountofCheck, 0 as txntype, "Uncleared" AS Particulars, vm.CreditAccountID AS DR, 500 AS CR, vm.CVNo, 1 as CurrentYr
FROM acctg_2cvmain vm INNER JOIN acctg_2cvsub vs ON vm.CVNo = vs.CVNo
WHERE (Month(`Cleared`)>'.$reportmonth.' AND Month(`Date`)<='.$reportmonth.') OR 
(Month(`Date`)<='.$reportmonth.' AND ISNULL(`Cleared`))
GROUP BY  vm.CreditAccountID, CheckNo

UNION ALL 
SELECT \''.($currentyr-1).'-12-31\' AS VchDate, DateofCheck, Cleared, CheckNo, Payee, Sum(up.AmountofCheck), 0 as txntype, "Uncleared" AS Particulars, up.FromAccount AS DR, 500 AS CR, `UnclearedCheckId`, 0 as CurrentYr
FROM `acctg_3unclearedchecksfromlastperiod` up
WHERE (Month(`Cleared`)>'.$reportmonth.' OR ISNULL(`Cleared`))
GROUP BY up.FromAccount, CheckNo

UNION ALL  SELECT `Date` AS VchDate, DateofCheck, Cleared, CheckNo, Payee, Sum(vs.Amount) AS AmountofCheck, 1 as txntype, "Cleared This Month" AS Particulars, 500 AS DR, vm.CreditAccountID AS CR, vm.CVNo, 1 as CurrentYr
FROM acctg_2cvmain vm INNER JOIN acctg_2cvsub vs ON vm.CVNo = vs.CVNo
WHERE (Month(`Cleared`)='.$reportmonth.' AND Month(`Date`)<'.$reportmonth.')
GROUP BY vm.CreditAccountID, CheckNo 

UNION ALL 
SELECT \''.($currentyr-1).'-12-31\' AS VchDate, DateofCheck, Cleared, CheckNo, Payee, Sum(up.AmountofCheck), 1 as txntype, "Cleared This Month" AS Particulars, 500 AS DR, up.FromAccount AS CR, `UnclearedCheckId`, 0 as CurrentYr
FROM `acctg_3unclearedchecksfromlastperiod` up
WHERE `cleared`is not null AND YEAR(`Cleared`)='.$currentyr.' AND Month(`Cleared`)='.$reportmonth.'
GROUP BY up.FromAccount, CheckNo;';

$stmt=$link->prepare($sql0);$stmt->execute();

skipquery:

switch($which){
case 'ClearedUncleared':
if (!allowedToOpen(6250,'1rtc')) {   echo 'No permission'; exit;} 
$txnidname='CVNo'; $editprocesslabel='Lookup'; $editprocess='../acctg/addeditsupplyside.php?w=CV&CVNo='; $txnidname='CVNo';
$sql='SELECT acc.*, FORMAT(SUM(AmountofCheck),2) as Amount, ca.ShortAcctID as DebitAccount, ca1.ShortAcctID as CreditAccount, if(CurrentYr=1,"True","False") as CurrentYr FROM accruedunion acc JOIN `acctg_1chartofaccounts` ca on ca.AccountID=acc.DR JOIN `acctg_1chartofaccounts` ca1 on ca1.AccountID=acc.CR GROUP BY CheckNo, Payee ORDER BY Particulars, Amount';
$columnnames=array('VchDate','DateofCheck', 'Cleared', 'CheckNo', 'Payee','Particulars','Amount', 'DebitAccount','CreditAccount','CurrentYr');
$showgrandtotal=true; $coltototal='AmountofCheck';
// include('../backendphp/layout/displayastablewithedit.php');
include('../backendphp/layout/displayastable.php');
break;

case 'Recon':
if (!allowedToOpen(6251,'1rtc')) {   echo 'No permission'; exit;} 
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$acctid=comboBoxValue($link,'`acctg_1chartofaccounts`','ShortAcctID',$_POST['bank'],'AccountID');
$sql0='SELECT DateofBal, BalPerMonth FROM `banktxns_bankbalancespermonth` WHERE AccountID='.$acctid.' AND YEAR(DateofBal)='.$currentyr.' AND MONTH(DateofBal)='.$reportmonth;
$stmt0=$link->query($sql0); $res0=$stmt0->fetch(); $bankbal=$res0['BalPerMonth'];
echo '<h3>'.$title.'</h3><i>To update data, open Data Errors first.</i><br><br>'; unset($formdesc); $title='Bank Recon';
?>
<p style='font-size: large'>Bank Balance ending <?php echo str_repeat('&nbsp', 8).$_POST['bank']. str_repeat('&nbsp', 8).$res0['DateofBal'].  str_repeat('&nbsp', 20). number_format($bankbal,2); ?></p>
<?php
$sqlbal='SELECT AccountID,(IFNULL(SUM(`Amount`),0)) as `EndBal` FROM `'.$currentyr.'_static`.`acctg_0unialltxns` uni WHERE AccountID='.$acctid.' AND '.($reportmonth==1?'w="BegBal"':'MONTH(`Date`)<='.$reportmonth);
$stmtbal=$link->query($sqlbal); $resbal=$stmtbal->fetch(); 
$sql='SELECT (SUM(CASE WHEN DR='.$acctid.' THEN AmountofCheck END)) as Debit, '
        . '(SUM(CASE WHEN CR='.$acctid.' THEN AmountofCheck END)) as Credit FROM accruedunion acc ';
$stmt=$link->query($sql); $res=$stmt->fetch(); 

$calcbookbal=$resbal['EndBal']+$res['Debit'];//-$res['Credit'];
echo '<br>Book Balance '.number_format($resbal['EndBal'],2);
echo '<br>Add Total Uncleared '.number_format($res['Debit'],2);
// echo '<br>Less Total Cleared '.number_format($res['Credit'],2);
echo '<br><br><b>Net Book Balance '.number_format($calcbookbal,2).'</b>';
echo '<br><br>Difference: '.number_format(($bankbal-$calcbookbal),2).'<br><br>';
$txnidname='CVNo'; $editprocesslabel='Lookup'; $editprocess='../acctg/addeditsupplyside.php?w=Vouchers&TxnID=';

$righttabletitle='Cleared This Month From Previous Months Total: '.number_format($res['Credit'],2);
$sqlright='SELECT acc.*, FORMAT(SUM(AmountofCheck),2) as Amount, if(CurrentYr=1,"True","False") as CurrentYr FROM accruedunion acc JOIN `acctg_1chartofaccounts` ca1 on ca1.AccountID=acc.CR WHERE txntype=1 AND CR='.$acctid.' GROUP BY CheckNo, Payee ORDER BY  CheckNo';
$columnnamesright=array('CheckNo','VchDate','DateofCheck', 'Cleared', 'Payee','Amount');

$lefttabletitle='Uncleared Checks: '.number_format($res['Debit'],2);
$sqlleft='SELECT acc.*, FORMAT(SUM(AmountofCheck),2) as Amount, if(CurrentYr=1,"True","False") as CurrentYr FROM accruedunion acc JOIN `acctg_1chartofaccounts` ca1 on ca1.AccountID=acc.DR WHERE txntype=0 AND DR='.$acctid.' GROUP BY CheckNo, Payee ORDER BY  CheckNo'; 
$columnnamesleft=array('CheckNo','VchDate','DateofCheck', 'Payee','Amount');
$showgrandtotal=true; $coltototal='AmountofCheck';
include('../backendphp/layout/twotablessidebyside.php');
break;

}
noform:
      $link=null; $stmt=null;
?>