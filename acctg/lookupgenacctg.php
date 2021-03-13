<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(558,559,5591,560,561,5581,5582,5583,5584,5611,5692); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
 
$showbranches=true; include_once('../switchboard/contents.php');
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link; 


//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="FFE9E7";
        $rcolor[1]="FFFFFF";
        
$whichqry=$_GET['w'];

switch ($whichqry){
    
case 'AcctSched':
    if (!allowedToOpen(558,'1rtc')) { echo 'No permission'; exit; }
$title='Account Schedule';
$fieldname='Account';

   include('../backendphp/layout/clickontabletoedithead.php');
   include_once('acctglists.inc'); $otherlist='accounts';
   $listcondition1=(!isset($_REQUEST['submit']) OR $_REQUEST['submit']=='Per Branch')?(' AND AccountID IN (SELECT AccountID FROM `acctg_1begbal` WHERE BranchNo='.$_SESSION['bnum'].') '):'';
   if (allowedToOpen(5581,'1rtc')) { $listcondition=' WHERE 1=1 '; } elseif (allowedToOpen(5583,'1rtc')) { $listcondition=' where AccountID in (160,200,201,205,405,705) '; $show=0;}
   elseif (allowedToOpen(5584,'1rtc')) { $listcondition=' WHERE AccountType IN (150,200,201,210,220,230,240) AND AccountID NOT IN (822, 821, 978, 823, 902, 903, 901, 505, 906) '; $show=0;}
   else { $listcondition=' where Hide=0 '; $show=0;}

renderotherlist($otherlist,$listcondition.$listcondition1);
$account=(isset($_REQUEST['Account'])?$_REQUEST['Account']:'CashOnHand');
$month1=(isset($_REQUEST['Month1'])?$_REQUEST['Month1']:date('m'));
$month2=(isset($_REQUEST['Month2'])?$_REQUEST['Month2']:date('m'));
$show=!isset($_REQUEST['submit'])?'Per Branch':$_REQUEST['submit'];
$download=!isset($_REQUEST['download'])?'':$show='Per Company';
$action='lookupgenacctg.php?w=AcctSched&Account='.$account.'&Month1='.$month1.'&Month2='.$month2;
   ?>
<form style="display:inline;width: 300px; height: 100px; padding: 15px; border: 2px solid black;" method="get" action="<?php echo $action; ?>" enctype="multipart/form-data">
<input type="hidden" name="w" value="AcctSched">
Choose Account:  <input type="text" name="<?php echo $fieldname; ?>" list="accounts"  value="<?php echo $account; ?>"></input>&nbsp &nbsp &nbsp
From Month (1 - 12):  <input type="text" size=5 name="Month1" value="<?php echo $month1; ?>"></input>&nbsp
To Month (1 - 12):  <input type="text" size=5 name="Month2" value="<?php echo $month2; ?>"></input>&nbsp &nbsp &nbsp 

<input type="submit" name="submit" value="Per Branch"> &nbsp &nbsp &nbsp <input type="submit" name="submit" value="Per Company"> &nbsp &nbsp &nbsp 
<?php echo  (allowedToOpen(5581,'1rtc')?'<input type="submit" name="submit" value="ALL">':''); ?>&nbsp &nbsp &nbsp 
</form>
<?php 

$showprint=true;
include('../backendphp/functions/getnumber.php');
$accts=array(getNumber('Account',addslashes($account)));
$acctid='('; $counter=0;$countof=count($accts);
$acctidarray=array();
foreach ($accts as $acct){
   $counter++;
   $acctid=$acctid.$acct.($counter==$countof?'':', ');
   $acctidarray[]=$acct;
}
$acctid=$acctid.')';

$formdesc='<br><b>'.$account.' '.$acctid.' <i>for the months '.strtoupper(date('F',strtotime(''.$currentyr.'-'.$month1.'-1'))).'&nbsp to '.strtoupper(date('F',strtotime(''.$currentyr.'-'.$month2.'-1'))).str_repeat('&nbsp',3).'</i>(';
$monthfrom=$month1; $monthto=$month2;


// REPLACED THIS WITH STATIC DATA: include('../acctg/sqlphp/sqlalltxnsperaccountpermonth.php');
include('../acctg/sqlphp/createacctsched.php');
include('../acctg/sqlphp/createacctbegbal.php');
//}

$columnsub=array('Date', 'ControlNo', 'Supplier/Customer/Branch', 'Particulars','Debit','Credit');

if ($show==='Per Company') { 
   $formdesc=$formdesc.$_SESSION['*cname'];
   $condition=' WHERE a.BranchNo IN (SELECT BranchNo FROM `1branches` WHERE CompanyNo='.$_SESSION['*cnum'].') ';
   $columnsub[]='FromBudgetOf'; $columnsub[]='Branch'; 
   $sqlsum='Select (Select (Sum(SumofAmount)) from acctbegbal a '.$condition.' ) as Beginning , Sum(Case when (Entry="DR" and (Amount)>=0) or (Entry="CR" and (Amount)>=0)  then (IFNULL(Amount,0)) else 0 end) as TotalDebit,abs(Sum(Case when (Entry="CR" and (Amount)<0) or (Entry="DR" and (Amount)<0)  then ((IFNULL(Amount,0))) else 0 end)) as TotalCredit from  `acctsched` a
   '.$condition.' group by a.AccountID';
} elseif ($show==='ALL') { 
   $formdesc=$formdesc.' ALL';
   $condition=''; $columnsub[]='FromBudgetOf'; $columnsub[]='Branch'; //$addlgroupby='';
   $sqlsum='Select (Select (Sum(SumofAmount)) from
   acctbegbal a '.$condition.' ) as Beginning , Sum(Case when (Entry="DR" and (Amount)>=0) or (Entry="CR" and (Amount)>=0)  then (IFNULL(Amount,0)) else 0 end) as TotalDebit,abs(Sum(Case when (Entry="CR" and (Amount)<0) or (Entry="DR" and (Amount)<0) then ((IFNULL(Amount,0))*-1) else 0 end)) as TotalCredit from  `acctsched` a
   join `acctg_1chartofaccounts` ca on ca.AccountID=a.AccountID '.$condition.' group by a.AccountID';
} else { // per branch
   $formdesc=$formdesc.$_SESSION['@brn'];
   $condition=' where a.BranchNo='.$_SESSION['bnum'];  $columnsub[]='FromBudgetOf';
   $sqlsum='Select (Select (Sum(IFNULL(SumofAmount,0))) from
   acctbegbal a '.$condition.' ) as Beginning , Sum(Case when (Entry="DR" and (IFNULL(Amount,0))>=0) or (Entry="CR" and (Amount)>=0)  then (IFNULL(Amount,0)) else 0 end) as TotalDebit,abs(Sum(Case when (Entry="CR" and (Amount)<0) or (Entry="DR" and (Amount)<0)  then ((IFNULL(Amount,0))) else 0 end)) as TotalCredit from  `acctsched` a
   '.$condition.' group by a.BranchNo, a.AccountID';
}  

$formdesc=$formdesc.')</b>';
$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Date,ControlNo');

$sql='SELECT Last_Day(\''.($month1<>1?(''.$currentyr.'-'.($month1-1).'-1'):($currentyr-1).'-12-30').'\') as Date, "-" as ControlNo, "Beginning Balance" as `Supplier/Customer/Branch`, "" as Particulars, Branch,Entity as FromBudgetOf, if(Sum(SumofAmount)>0,format(Sum(SumofAmount),2),0) as Debit, if(Sum(SumofAmount)<0,format(abs(Sum(SumofAmount)),2),0) as Credit, Sum(SumofAmount) as AmtToTotal, "AcctSched" as w, 0 as TxnID, ca.NormBal
from  acctbegbal a join `1branches` b on b.BranchNo=a.BranchNo left join acctg_1budgetentities be on be.EntityID=a.FromBudgetOf
join `acctg_1chartofaccounts` ca on ca.AccountID=a.AccountID '.$condition.' 
UNION ALL
SELECT Date, ControlNo, `Supplier/Customer/Branch`, Particulars, Branch,Entity as FromBudgetOf, (Case when (Entry="DR" and (Amount)>=0) OR (Entry="CR" and (Amount)>=0) then format((Amount),2) else "" end) as Debit,(Case when ((Entry="CR" and (Amount)<0) or (Entry="DR" and (Amount)<0)) then format(abs((Amount)),2) else 0 end) as Credit,(Amount) as AmtToTotal, w, TxnID, ca.NormBal from `1branches` b join acctsched a on a.BranchNo=b.BranchNo left join acctg_1budgetentities be on be.EntityID=a.FromBudgetOf join `acctg_1chartofaccounts` ca on ca.AccountID=a.AccountID  '.$condition.'  order by '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
// echo $sql;break;
    
$main='';
$columnnames=array();
$sub=''; $downloadsub='';

// echo $acctid.'<br>'.$sql; break;
$stmt=$link->query($sql);   $result=$stmt->fetchAll();
 
   $subcol='';$runtotal=0; $downloadsubcol='';
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';$downloadsubcol=$downloadsubcol.$colsub.',';
    }
    foreach($result as $row){
        $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>'; $downloadsub=$downloadsub.PHP_EOL;
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>'; $downloadsub=$downloadsub.str_replace('#','',str_replace(',','',$row[$colsub])).',';
        }
        $runtotal=$runtotal+($row['AmtToTotal']*$row['NormBal']);
        
        $which=$row['w'];
        
         include('acctglayout/openfromacctsched.php');
         $txnid=!isset($txnid)?'TxnID':$txnid;
        $sub=$sub.'<td>'.number_format($runtotal,2).'</td><td><a href="'.$filetoopen.'.php?w='.$which.'&'.$txnid.'='.$row['TxnID'].'"  target=_blank>Lookup</a></tr>';
        $colorcount++;
        $downloadsub=$downloadsub.$runtotal;
    }
    $sub='<table><tr>'.$subcol.'<td>Running Sum</td><td>Lookup?</td></tr><tbody>'.$sub.'</tbody></table>';
    $downloadsub=$downloadsubcol.'Running_Sum'.PHP_EOL.$downloadsub;
       
    $stmt=$link->query($sqlsum);
    $resultsum=$stmt->fetch();
    $total='Totals:'.str_repeat('&nbsp',4).'<font color="maroon">Beginning:  '.number_format(abs($resultsum['Beginning']),2).str_repeat('&nbsp',7).'<font color="maroon">Debit:  '.number_format($resultsum['TotalDebit'],2).str_repeat('&nbsp',7).'Credit:  '.number_format($resultsum['TotalCredit'],2).str_repeat('&nbsp',7).'Net:  '.number_format(abs($resultsum['Beginning']+$resultsum['TotalDebit']-$resultsum['TotalCredit']),2).'</font><br><br>';
// echo $sql; break;
    
    if (allowedToOpen(5582,'1rtc')){
        $filetype='xls';
    echo '<form style="display: inline" action="downloadacctg.php" method="post">
   <input type="submit" name="download" value="Download Per Company">
   <input type="hidden" name="acctgdata" value="'.$downloadsub.'"><input type="hidden" name="type" value="'.$filetype.'">
   <input type="hidden" name="filename" value="'.$account.'_'.$month1.'_'.$month2.'_'.$_SESSION['*cname'].'.'.$filetype.'"></form>';
    }
    
    if (!isset($_REQUEST['download'])) {    include('../backendphp/layout/lookupreport.php');} 
    else {  }

break;

case 'Expenses':
    if (!allowedToOpen(560,'1rtc')) { echo 'No permission'; exit; }
$title='Details of Expenses per Branch';
include('../backendphp/layout/clickontabletoedithead.php');
$month1=(isset($_REQUEST['Month1'])?$_REQUEST['Month1']:date('m'));
$month2=(isset($_REQUEST['Month2'])?$_REQUEST['Month2']:date('m'));
$action='lookupgenacctg.php?w=Expenses&Month1='.$month1.'&Month2='.$month2;
   ?>
<form style="display:inline;width: 300px; height: 100px; padding: 15px; border: 2px solid black;" method="get" action="<?php echo $action; ?>" enctype="multipart/form-data">
<input type="hidden" name="w" value="Expenses">
Expenses From Month (1 - 12):  <input type="text"  size=2 name="Month1" value="<?php echo $month1; ?>"></input>&nbsp
To Month (1 - 12):  <input type="text" size=2 name="Month2" value="<?php echo $month2; ?>"></input>&nbsp &nbsp &nbsp 
<input type="submit" name="lookup" value="Lookup">&nbsp &nbsp
</form>
<?php 
$stmt0=$link->query('SELECT AccountID FROM acctg_1chartofaccounts WHERE AccountType BETWEEN 200 AND 240;');
$res0=$stmt0->fetchAll();
$accts=array();

$acctid='('; $counter=0;$countof=$stmt0->rowCount();
$acctidarray=array();
foreach ($res0 as $acct){
   $counter++;
   $acctid=$acctid.$acct['AccountID'].($counter==$countof?'':', ');
   $acctidarray[]=$acct['AccountID'];
}
$acctid=$acctid.')';

$formdesc='<br><b>Expenses <i>for the months '.strtoupper(date('F',strtotime(''.$currentyr.'-'.$month1.'-1'))).'&nbsp to '.strtoupper(date('F',strtotime(''.$currentyr.'-'.$month2.'-1'))).str_repeat('&nbsp',3).'</i>('.$_SESSION['@brn'].')</b>';
$monthfrom=$month1; $monthto=$month2;

include('../acctg/sqlphp/createacctsched.php');
include('../acctg/sqlphp/createacctbegbal.php');

$columnsub=array('Date', 'ControlNo', 'Supplier/Customer/Branch', 'Particulars','Debit','Credit');

   $condition=' where a.BranchNo='.$_SESSION['bnum'];
   $sqlsum='Select (Select (Sum(SumofAmount)) from
   acctbegbal a '.$condition.' ) as Beginning , Sum(Case when (Entry="DR" and (Amount)>=0) or (Entry="CR" and (Amount)>=0)  then (Amount) else 0 end) as TotalDebit,abs(Sum(Case when (Entry="CR" and (Amount)<0) or (Entry="DR" and (Amount)<0)  then ((Amount)) else 0 end)) as TotalCredit from  `acctsched` a
   '.$condition.' group by a.BranchNo, a.AccountID';

$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Date,ControlNo');

$sql='SELECT Last_Day(\''.$currentyr.'-'.($month1-1).'-1\') as Date, "-" as ControlNo, "Beginning Balance" as `Supplier/Customer/Branch`, "" as Particulars, Branch, if(Sum(SumofAmount)>0,format(Sum(SumofAmount),2),0) as Debit, if(Sum(SumofAmount)<0,format(abs(Sum(SumofAmount)),2),0) as Credit, Sum(SumofAmount) as AmtToTotal, "AcctSched" as w, 0 as TxnID, ca.NormBal
from  acctbegbal a join `1branches` b on b.BranchNo=a.BranchNo
join `acctg_1chartofaccounts` ca on ca.AccountID=a.AccountID '.$condition.' HAVING AmtToTotal<>0 
UNION ALL
SELECT Date, ControlNo, `Supplier/Customer/Branch`, Particulars, Branch, (Case when (Entry="DR" and (Amount)>=0) OR (Entry="CR" and (Amount)>=0) then format((Amount),2) else "" end) as Debit,(Case when ((Entry="CR" and (Amount)<0) or (Entry="DR" and (Amount)<0)) then format(abs((Amount)),2) else 0 end) as Credit,(Amount) as AmtToTotal, w, TxnID, ca.NormBal from `1branches` b join acctsched a on a.BranchNo=b.BranchNo join `acctg_1chartofaccounts` ca on ca.AccountID=a.AccountID  '.$condition.' HAVING AmtToTotal<>0  order by '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
// echo $sql;break;
    
$main='';
$columnnames=array();
$sub=''; $downloadsub='';

// echo $acctid.'<br>'.$sql; break;
$stmt=$link->query($sql);
   $result=$stmt->fetchAll();
 
   $subcol='';$runtotal=0; $downloadsubcol='';
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';$downloadsubcol=$downloadsubcol.$colsub.',';
    }
    foreach($result as $row){
        $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>'; $downloadsub=$downloadsub.PHP_EOL;
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>'; $downloadsub=$downloadsub.str_replace('#','',str_replace(',','',$row[$colsub])).',';
        }
        $runtotal=$runtotal+($row['AmtToTotal']*$row['NormBal']);
        
        $which=$row['w'];
        
        include('acctglayout/openfromacctsched.php');
        $txnid=!isset($txnid)?'TxnID':$txnid;
        $sub=$sub.'<td>'.number_format($runtotal,2).'</td><td><a href="'.$filetoopen.'.php?w='.$which.'&'.$txnid.'='.$row['TxnID'].'"  target=_blank>Lookup</a></tr>';
        $colorcount++;
      
    }
    $sub='<table><tr>'.$subcol.'<td>Running Sum</td><td>Lookup?</td></tr><tbody>'.$sub.'</tbody></table>';
    
       
    $stmt=$link->query($sqlsum);
    $resultsum=$stmt->fetch();
    $total='Totals:'.str_repeat('&nbsp',4).'<font color="maroon">Beginning:  '.number_format(abs($resultsum['Beginning']),2).str_repeat('&nbsp',7).'<font color="maroon">Debit:  '.number_format($resultsum['TotalDebit'],2).str_repeat('&nbsp',7).'Credit:  '.number_format($resultsum['TotalCredit'],2).str_repeat('&nbsp',7).'Net:  '.number_format(abs($resultsum['Beginning']+$resultsum['TotalDebit']-$resultsum['TotalCredit']),2).'</font><br><br>';
// echo $sql; break;
    
    include('../backendphp/layout/lookupreport.php');

break;

case 'Encashments':
if (!allowedToOpen(559,'1rtc')) { echo 'No permission'; exit; }
$show=!isset($_REQUEST['show'])?1:$_REQUEST['show'];
?>
<form method="post" action="lookupgenacctg.php?w=Encashments&show=0" style="display:inline">
<input type=hidden name="show" value=0>
<input type="submit" name="lookup" value="Lookup Per Branch">&nbsp &nbsp
</form>
<form method="post" action="lookupgenacctg.php?w=Encashments&show=1" style="display:inline">
<input type=hidden name="show" value=1>
<input type="submit" name="submit" value="Show All">&nbsp &nbsp
</form><BR><BR>
<?php
if ((allowedToOpen(303,'1rtc')) or (allowedToOpen(5591,'1rtc'))) { $showperco='';} else { $showperco=' JOIN `banktxns_1maintaining` m ON m.AccountID=dm.DebitAccountID  ';}
if ($show<>1){	$condition=' AND de.BranchNo like \''.$_SESSION['bnum'].'\' ';  } else {  $condition=''; }
$txnid='TxnID';
$title='Clear Encashments';
$columnnames=array('DepositNo','EncashDetails','Amount', 'DebitAccount','Date','Branch');  
$sql='SELECT dm.TxnID,dm.DepositNo, b.Branch, de.EncashDetails, de.Amount, ca.ShortAcctID as DebitAccount, dm.Date, dm.ClearedEncash
FROM acctg_2depositmain dm JOIN (acctg_1chartofaccounts ca INNER JOIN (acctg_2depencashsub de INNER JOIN `1branches` b ON de.BranchNo = b.BranchNo) ON ca.AccountID = de.DebitAccountID) ON dm.TxnID = de.TxnID '.$showperco.'
WHERE dm.ClearedEncash=0 '.$condition.'
ORDER BY dm.DepositNo, dm.Date;';
$autoprocess='praddmain.php?w='.$whichqry.'&TxnID='; $editprocess='addeditdep.php?w=Deposit&TxnID='; $editprocesslabel='Lookup';
$autoprocesslabel='Clear';
include_once('../backendphp/layout/displayastablewithedit.php');
   break;

case 'UnclearedDep':
if (!allowedToOpen(561,'1rtc')) { echo 'No permission'; exit; }
$title='Uncleared Deposits';
$showbranches=false;
$txnid='TxnID';
$lefttabletitle='Actual Bank Transactions';
$righttabletitle='Uncleared in our data (unposted)';

$show=!isset($_REQUEST['show'])?1:$_REQUEST['show'];
if ($show<>1){
	$condition=' where ShortAcctID like \''.$_REQUEST['bank'].'\' ';
	$conditiondata=' and `ca`.ShortAcctID like \''.$_REQUEST['bank'].'\'  AND `dm`.`Date`<=DATE_ADD(CURDATE(), INTERVAL 7 DAY)';
        $formdesc='Per bank, until 7 days from today';
    } elseif (isset($_REQUEST['hide']) and$_REQUEST['hide']==1) {
        $condition=''; $conditiondata=' AND `dm`.`Date`<CURDATE()'; $formdesc='Until yesterday';
    } elseif (isset($_REQUEST['future']) and$_REQUEST['future']==1) {
        $condition=''; $conditiondata=' AND `dm`.`Date`>CURDATE()';
        $formdesc='Future only';
    } else {
        $condition=''; $conditiondata=''; $formdesc='ALL';
    }

$columnnamesleft=array('Bank','TxnDate','BankBranch', 'Remarks','Details','Amount');
$columnnamesright=array('Bank','DepositNo','Date','DepType', 'Total','Posted');

include_once '../banktxns/sqlphp/dataforcleardep.php';
$sqlleft='SELECT *, FORMAT(DepositAmt,2) AS Amount, ShortAcctID as Bank FROM `banktxns_33tocleardeposits` '.$condition.' order by ShortAcctID, TxnDate,DepositAmt';
if (allowedToOpen(5611,'1rtc')){
$sql0='SELECT TRUNCATE(SUM(`DepositAmt`),2) AS `TotalUnclearedBank` FROM `banktxns_33tocleardeposits` '.$condition;
$stmt0=$link->query($sql0); $res0=$stmt0->fetch();
$totalleft='<td colspan=4>Total Uncleared Bank</td><td colspan=2>'.number_format($res0['TotalUnclearedBank'],2).'</td>';}
$sqlright='SELECT 
        `dm`.`TxnID` AS `TxnID`,
        `ca`.`ShortAcctID` AS `Bank`,
        `dm`.`DepositNo` AS `DepositNo`,
        `dm`.`Date` AS `Date`,
        format(sum(`dt`.`SumOfAmount`),2) AS `Total`, TRUNCATE(SUM(`dt`.`SumOfAmount`),2) AS `TotalValue`,
        `dm`.`Posted`, DepType
    from
        ((`acctg_2depositmain` `dm`
        join `banktxns_32uniunclearedfordeptotals` `dt` ON ((`dm`.`TxnID` = `dt`.`TxnID`)))
        join `acctg_1chartofaccounts` `ca` ON ((`dm`.`DebitAccountID` = `ca`.`AccountID`)))
        LEFT JOIN acctg_1deptype dy ON dy.DepTypeID=dt.FirstofType
    where
        isnull(`dm`.`Cleared`) '.$conditiondata.' AND dm.Posted=0
    GROUP BY `dm`.`TxnID` 
    ORDER BY `dm`.`Date`, dt.FirstofType, `ca`.`ShortAcctID`';

$righteditprocess='addeditdep.php?w=Deposit&TxnID=';
$righteditprocesslabel='Lookup';
if (allowedToOpen(5611,'1rtc')){
$sql0='SELECT TRUNCATE(SUM(`dt`.`SumOfAmount`),2) AS `TotalUnclearedUnposted` FROM `acctg_2depositmain` `dm`
        join `banktxns_32uniunclearedfordeptotals` `dt` ON `dm`.`TxnID` = `dt`.`TxnID`  
            join `acctg_1chartofaccounts` `ca` ON `dm`.`DebitAccountID` = `ca`.`AccountID`
    where isnull(`dm`.`Cleared`) '.$conditiondata.' and dm.Posted=0';
$stmt0=$link->query($sql0); $res0=$stmt0->fetch();
$totalright='<td colspan=4>Total Uncleared Unposted</td><td colspan=2>'.number_format($res0['TotalUnclearedUnposted'],2).'</td>';
}
?>
<form method="post" action="#" style="display:inline">
	<input type="text" name="bank" list="banks" size=20 autocomplete="off" required="true">
<?php include('../banktxns/bankslist.php'); ?>
<input type=hidden name="show" value=0>
<input type="submit" name="lookup" value="Lookup Per Bank">&nbsp &nbsp
</form>
<form method="post" action="#" style="display:inline">
<input type=hidden name="hide" value=1>
<input type="submit" name="submit" value="Hide Today">&nbsp &nbsp
</form>
<form method="post" action="#" style="display:inline">
<input type=hidden name="future" value=1>
<input type="submit" name="submit" value="Show Future">&nbsp &nbsp
</form>
<form method="post" action="#" style="display:inline">
<input type=hidden name="show" value=1>
<input type="submit" name="submit" value="Show All">&nbsp &nbsp
</form><BR><BR>
<?php
include_once('../backendphp/layout/twotablessidebyside.php');
$columnnames=array('Bank','DepositNo','Date','DepType', 'Total','Posted');
$sql='SELECT 
        `dm`.`TxnID` AS `TxnID`,
        `ca`.`ShortAcctID` AS `Bank`,
        `dm`.`DepositNo` AS `DepositNo`,
        `dm`.`Date` AS `Date`,
        format(sum(`dt`.`SumOfAmount`),2) AS `Total`,
        `dm`.`Posted`, DepType
    from
        ((`acctg_2depositmain` `dm`
        join `banktxns_32uniunclearedfordeptotals` `dt` ON ((`dm`.`TxnID` = `dt`.`TxnID`)))
        join `acctg_1chartofaccounts` `ca` ON ((`dm`.`DebitAccountID` = `ca`.`AccountID`)))
        LEFT JOIN acctg_1deptype dy ON dy.DepTypeID=dt.FirstofType
    where
        isnull(`dm`.`Cleared`) '.$conditiondata.' and dm.Posted=1
    group by `dm`.`TxnID` 
    order by `dm`.`Date`,dt.FirstofType, `ca`.`ShortAcctID`';

$editprocess='addeditdep.php?w=Deposit&TxnID=';
$editprocesslabel='Lookup'; 
$subtitle='<h3>Uncleared in our data (posted)</h3>'; $hidecount=1;
if (allowedToOpen(5611,'1rtc')){
$sql0='SELECT TRUNCATE(SUM(`dt`.`SumOfAmount`),2) AS `TotalUnclearedPosted` FROM `acctg_2depositmain` `dm`
        join `banktxns_32uniunclearedfordeptotals` `dt` ON `dm`.`TxnID` = `dt`.`TxnID`     
            join `acctg_1chartofaccounts` `ca` ON `dm`.`DebitAccountID` = `ca`.`AccountID`
    where isnull(`dm`.`Cleared`) '.$conditiondata.' and dm.Posted=1';
$stmt0=$link->query($sql0); $res0=$stmt0->fetch();
$totaltable='<td colspan=4>Total Uncleared Posted</td><td colspan=2>'.number_format($res0['TotalUnclearedPosted'],2).'</td>';}
echo '<br><br>'; $width='45%';
include_once('../backendphp/layout/displayastableonlynoheaders.php');
    break; 
        
    
case 'SendCurrtoClosing':
    if (!allowedToOpen(5692,'1rtc')) { echo 'No permission'; exit; }
    $currmonth=((((date('m',strtotime($_SESSION['nb4A'])))==12)?0:(date('m',strtotime($_SESSION['nb4A']))))+1); 
    $whichdata='withcurrent'; $month=$currmonth; $reportmonth=$currmonth; require ('maketables/makefixedacctgdata.php');
  $sql0='CREATE TEMPORARY TABLE `acctg_endvalues` as 
Select BranchNo,AccountID,truncate(sum(Amount),2) as `Balance` from `acctg_0unialltxns` uni WHERE `AccountID` IN (100,135,205,206,330,405,501,502,503,504,505,507,508,512) AND Month(Date)<='.$currmonth.' GROUP BY AccountID, BranchNo ';
    $stmt=$link->prepare($sql0); $stmt->execute();
    // first update existing data
        $stmt=$link->prepare('UPDATE `closing_2closemain` cm 
SET cm.`EndBal` = (SELECT SUM(`Balance`)*`NormBal` FROM `acctg_endvalues` fs JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=fs.AccountID WHERE BranchNo=cm.BranchNo AND fs.AccountID=cm.AccountID),`EncodedByNo` = \''.$_SESSION['(ak0)'].'\',`TimeStamp`=Now() WHERE cm.Month='.$currmonth); $stmt->execute();
        // insert new data
        $stmt=$link->prepare('INSERT INTO `closing_2closemain` (`Month`,`AccountID`,`BranchNo`,`EndBal`,`EncodedByNo`,`TimeStamp`)
SELECT '.$currmonth.',fs.`AccountID`,`BranchNo`, SUM(`Balance`)*`NormBal` AS `EndBal`, \''.$_SESSION['(ak0)'].'\', Now() FROM `acctg_endvalues` fs JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=fs.AccountID 
WHERE (CONCAT(fs.AccountID,":",fs.BranchNo,":",'.$currmonth.') NOT IN (SELECT CONCAT(AccountID,":",BranchNo,":",Month) FROM `closing_2closemain`)) GROUP BY BranchNo, fs.AccountID;'); $stmt->execute();
    header('Location:../closing/list.php');
    $sql0='DROP TEMPORARY TABLE `acctg_endvalues`'; $stmt0=$link->prepare($sql0); $stmt0->execute();
break;  

case 'BegBal':
    if (!allowedToOpen(526,'1rtc')) { echo 'No permission'; exit; }
   $title='Beginning Balances for Yr '.$currentyr.' - '.$_SESSION['*cname'];
   $sql1='SELECT BranchNo, Branch FROM 1branches WHERE Active<>0 AND BranchNo>=0 AND CompanyNo='.$_SESSION['*cnum'].' ORDER BY Branch'; $stmt=$link->query($sql1); $res1=$stmt->fetchAll();
   $columnnames=array('Account','Total');
   $sql='';
   foreach($res1 as $branch){
       $sql.='FORMAT(SUM(CASE WHEN bb.BranchNo='.$branch['BranchNo'].' THEN IFNULL(BegBalance*IFNULL(Forex,1),0) END),2) AS `'.$branch['Branch'].'`, ';
       $columnnames[]=$branch['Branch'];
   }
 
   $sql='SELECT ShortAcctID AS Account, '.$sql.' FORMAT(SUM(BegBalance*IFNULL(Forex,1)),2) AS Total, SUM(BegBalance*IFNULL(Forex,1)) AS TotalValue FROM acctg_1begbal bb JOIN 1branches b ON b.BranchNo=bb.BranchNo JOIN acctg_1chartofaccounts ca ON ca.AccountID=bb.AccountID WHERE Active<>0 AND CompanyNo='.$_SESSION['*cnum'].' GROUP BY bb.AccountID HAVING TotalValue<>0 UNION SELECT "TotalPerBranch",'.$sql.' FORMAT(SUM(BegBalance*IFNULL(Forex,1)),2) AS Total, SUM(BegBalance*IFNULL(Forex,1)) AS TotalValue FROM acctg_1begbal bb JOIN 1branches b ON b.BranchNo=bb.BranchNo WHERE Active<>0 AND CompanyNo='.$_SESSION['*cnum'].' HAVING TotalValue<>0;';
   //if($_SESSION['(ak0)']) {echo $sql;}
   
   include '../backendphp/layout/displayastablenosort.php';
    break;
}
noform:
      $link=null; $stmt=null;
?>