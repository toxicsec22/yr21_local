<?php 
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(655,6242,6243,6244,6245,6247,6248,6249,6252,6253,62461);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
  
$showbranches=false; include_once('../switchboard/contents.php');


    $method='POST';
$whichqry=$_GET['w'];


if (in_array($whichqry,array('CompareDailyTotals','CompareActualDeposits'))){
	if(isset($_POST['bank'])){
	include('../backendphp/functions/getnumber.php');
	$acctid=getNumber('Account',$_POST['bank']);
	// replacement of the view banktxns_45datacibtxns2

$sql0='CREATE TEMPORARY TABLE banktxns_45datacibtxns2 AS '
        . 'SELECT `jvs`.`Date` AS `Date`, CONCAT("JVNo", `jvm`.`JVNo`) AS `ControlNo`, `jvs`.`Particulars` AS `Supplier/Customer`,
        SUM(`jvs`.`Amount`) AS `DEBIT`, 0 AS `CREDIT`, `jvs`.`DebitAccountID` AS `AccountID`, `jvm`.`Remarks` AS `Remarks`, 1 AS `Cleared`
    FROM
        (`acctg_2jvmain` `jvm` JOIN `acctg_2jvsub` `jvs` ON ((`jvm`.`JVNo` = `jvs`.`JVNo`)))
    WHERE
        (`jvs`.`CreditAccountID` <> 500) AND `jvs`.`DebitAccountID`='.$acctid.'
    GROUP BY `jvm`.`JVNo` 
    UNION ALL SELECT 
        `jvs`.`Date` AS `Date`, CONCAT("JVNo", `jvm`.`JVNo`) AS `ControlNo`,
        `jvs`.`Particulars` AS `Particulars`, 0 AS `DEBIT`, SUM(`jvs`.`Amount`) AS `CREDIT`, `jvs`.`CreditAccountID`, `jvm`.`Remarks` AS `Remarks`, 1 AS `Cleared`
    FROM
        (`acctg_2jvmain` `jvm` JOIN `acctg_2jvsub` `jvs` ON ((`jvm`.`JVNo` = `jvs`.`JVNo`)))
    WHERE
        (`jvs`.`DebitAccountID` <> 500) AND `jvs`.`CreditAccountID`='.$acctid.'
    GROUP BY `jvm`.`JVNo` 
    
    UNION ALL SELECT 
        `depm`.`Cleared` AS `Date`, CONCAT("Dep#", `depm`.`DepositNo`) AS `ControlNo`, 
        CONCAT(`c`.`ClientName`," ",CONVERT( `deps`.`DepDetails` USING UTF8)) AS `Expr2`,
        SUM(`deps`.`Amount`) AS `DEBIT`,0 AS `CREDIT`, `depm`.`DebitAccountID`, `depm`.`Remarks` AS `Remarks`, IF(ISNULL(`depm`.`Cleared`), 0, 1) AS `Cleared`
    FROM
        (`acctg_2depositmain` `depm` JOIN (`acctg_2depositsub` `deps`
        LEFT JOIN `1clients` `c` ON ((`deps`.`ClientNo` = `c`.`ClientNo`))) ON ((`depm`.`TxnID` = `deps`.`TxnID`)))
        WHERE `depm`.`DebitAccountID`='.$acctid.'
    GROUP BY `depm`.`TxnID` 
    UNION ALL SELECT 
        `depm`.`Cleared` AS `Date`, CONCAT("Dep#", `depm`.`DepositNo`) AS `ControlNo`,
        CONCAT(`depe`.`EncashDetails`,
                CONVERT( IF(ISNULL(`depe`.`ApprovalNo`),
                    "",
                    CONCAT(" approval ", `depe`.`ApprovalNo`)) USING LATIN1)) AS `Expr2`,
        SUM(`depe`.`Amount`) AS `DEBIT`, 0 AS `CREDIT`, `depe`.`DebitAccountID`, `depm`.`Remarks` AS `Remarks`, IF(ISNULL(`depm`.`Cleared`), 0, 1) AS `Cleared`
    FROM
        (`acctg_2depositmain` `depm`
        JOIN `acctg_2depencashsub` `depe` ON ((`depm`.`TxnID` = `depe`.`TxnID`)))
    WHERE
        (`depm`.`Cleared` <= NOW()) AND `depe`.`DebitAccountID`='.$acctid.'
    GROUP BY `depm`.`TxnID` 
    UNION ALL SELECT 
        `depm`.`Cleared` AS `Date`,  CONCAT("Dep#", `depm`.`DepositNo`) AS `ControlNo`,
        CONCAT(`c`.`ClientName`,
                " ",
                CONVERT( `deps`.`DepDetails` USING UTF8)) AS `Expr2`,
        0 AS `DEBIT`, SUM(`deps`.`Amount`) AS `CREDIT`, `deps`.`CreditAccountID`, `depm`.`Remarks`, IF(ISNULL(`depm`.`Cleared`), 0, 1) AS `Cleared`
    FROM
        (`acctg_2depositmain` `depm` JOIN (`acctg_2depositsub` `deps`
        LEFT JOIN `1clients` `c` ON ((`deps`.`ClientNo` = `c`.`ClientNo`))) ON ((`depm`.`TxnID` = `deps`.`TxnID`)))
        WHERE `deps`.`CreditAccountID`='.$acctid.'
    GROUP BY `depm`.`TxnID` 
    UNION ALL SELECT 
        `depm`.`Cleared` AS `Date`, CONCAT("Dep#", `depm`.`DepositNo`) AS `ControlNo`,
        CONCAT(`depe`.`EncashDetails`,
                CONVERT( IF(ISNULL(`depe`.`ApprovalNo`),
                    "",
                    CONCAT(" approval ", `depe`.`ApprovalNo`)) USING LATIN1)) AS `Expr2`,
        SUM((`depe`.`Amount` * -(1))) AS `DEBIT`, 0 AS `CREDIT`, `depm`.`DebitAccountID`, `depm`.`Remarks` AS `Remarks`, IF(ISNULL(`depm`.`Cleared`), 0, 1) AS `Cleared`
    FROM
        (`acctg_2depositmain` `depm`
        JOIN `acctg_2depencashsub` `depe` ON ((`depm`.`TxnID` = `depe`.`TxnID`)))
        WHERE `depm`.`DebitAccountID`='.$acctid.'
    GROUP BY `depm`.`TxnID` 
    UNION ALL SELECT 
        `vchm`.`Cleared` AS `Date`, CONCAT("CVNo", `vchm`.`CVNo`) AS `ControlNo`, `vchm`.`Payee`,
        0 AS `DEBIT`, SUM(`vchs`.`Amount`) AS `CREDIT`, `vchm`.`CreditAccountID`, `vchm`.`Remarks`, IF(ISNULL(`vchm`.`Cleared`), 0, 1) AS `Cleared`
    FROM
        (`acctg_2cvmain` `vchm` JOIN `acctg_2cvsub` `vchs` ON ((`vchm`.`CVNo` = `vchs`.`CVNo`)))
    WHERE
        (`vchm`.`Cleared` IS NOT NULL) AND `vchm`.`CreditAccountID`='.$acctid.'
    GROUP BY `vchm`.`CVNo` 
    UNION ALL SELECT `vchm`.`Cleared` AS `Date`, CONCAT("CVNo", `vchm`.`CVNo`) AS `ControlNo`, `vchm`.`Payee`,
        SUM(`vchs`.`Amount`) AS `DEBIT`, 0 AS `CREDIT`, `vchs`.`DebitAccountID`, `vchm`.`Remarks`, IF(ISNULL(`vchm`.`Cleared`), 0, 1) AS `Cleared`
    FROM
        (`acctg_2cvmain` `vchm` JOIN `acctg_2cvsub` `vchs` ON ((`vchm`.`CVNo` = `vchs`.`CVNo`)))
    WHERE
        (`vchm`.`Cleared` IS NOT NULL) AND `vchs`.`DebitAccountID`='.$acctid.'
    GROUP BY `vchm`.`CVNo` 
    UNION ALL SELECT  `lp`.`Cleared` AS `Date`, CONCAT("CVNo", `lp`.`CVNo`) AS `ControlNo`, `lp`.`Payee` AS `Payee`,
        0 AS `DEBIT`, SUM(`lp`.`AmountofCheck`) AS `CREDIT`, `lp`.`FromAccount`, "Last Year" AS `Last Year`,
        IF(ISNULL(`lp`.`Cleared`), 0, 1) AS `Cleared`
    FROM
        `acctg_3unclearedchecksfromlastperiod` `lp` 
    WHERE
        (ISNULL(`lp`.`Cleared`) = 0) AND `FromAccount`='.$acctid.'
    GROUP BY `lp`.`CVNo`
    
    UNION ALL SELECT  `lpb`.`DateBounced` AS `Date`, CONCAT("Bounced#CR",CRNo,"_",PDCNo) AS `ControlNo`, ClientName,
        0 AS `DEBIT`, SUM(`lp`.`AmountofPDC`) AS `CREDIT`, CreditAccountID, "Bounced from Collection Last Year" AS `Last Year`,
        1 AS `Cleared`
    FROM
        `acctg_3undepositedpdcfromlastperiod` `lp`  JOIN `acctg_3undepositedpdcfromlastperiodbounced` lpb ON lp.UndepPDCId=lpb.UndepPDCId
        JOIN 1clients c ON c.ClientNo=lp.ClientNo
    WHERE
        CreditAccountID='.$acctid.'
    GROUP BY `lp`.`UndepPDCId`
    
    UNION ALL SELECT 
        `DateBounced` AS `Date`, CONCAT("Bounced#CR",bm.CollectNo,"_",bm.CheckNo) AS `ControlNo`, `c`.`ClientName`,
        SUM(`bs`.`Amount`) AS `DEBIT`, 0 AS `CREDIT`, 200 AS `DebitAccountID`,
        `bm`.`Remarks` AS `Remarks`, 1 AS `Cleared`
    FROM
        ((`acctg_2collectmain` `bm`
        JOIN `1clients` `c` ON ((`bm`.`ClientNo` = `c`.`ClientNo`)))
        JOIN `acctg_2collectsub` `bs` ON ((`bm`.`TxnID` = `bs`.`TxnID`))
         JOIN `acctg_2collectsubbounced` `bsb` ON ((`bm`.`TxnID` = `bsb`.`TxnID`)))
         WHERE 200='.$acctid.'
    GROUP BY `bm`.`TxnID` 
    UNION ALL 
    
    SELECT 
        `DateBounced` AS `Date`, CONCAT("Bounced#CR",bm.CollectNo,"_",bm.CheckNo) AS `ControlNo`, `c`.`ClientName`, 0 AS `DEBIT`, SUM(`bs`.`Amount`) AS `CREDIT`,
        `bsb`.`CreditAccountID`, `bm`.`Remarks` AS `Remarks`, 1 AS `Cleared`
    FROM
        ((`acctg_2collectmain` `bm`
        JOIN `1clients` `c` ON ((`bm`.`ClientNo` = `c`.`ClientNo`)))
        JOIN `acctg_2collectsub` `bs` ON ((`bm`.`TxnID` = `bs`.`TxnID`))
         JOIN `acctg_2collectsubbounced` `bsb` ON ((`bm`.`TxnID` = `bsb`.`TxnID`)))
         WHERE `bsb`.`CreditAccountID`='.$acctid.'
    GROUP BY `bm`.`TxnID`
    ORDER BY `Date`';
$stmt=$link->prepare($sql0); $stmt->execute();
	}
// end banktxns_45datacibtxns2
	
}

switch ($whichqry){
    case 'UnclearedChecks':
    if (!allowedToOpen(6244,'1rtc')) {   echo 'No permission'; exit;} 
    $asof=isset($_POST['asof'])?$_POST['asof']:date('Y-m-d',strtotime("next Friday"));
    $title='Uncleared Checks up to '.$asof;
    ?>
    
        <form method='POST' action='lookupbankdata.php?w=UnclearedChecks'>
            As of: <input type='date' name='asof' value='<?php echo date('Y-m-d',strtotime("next Friday")); ?>'>
            <input type='submit' name='lookup' value='Lookup'>
        </form>
    <?php
    
    include_once 'sqlphp/dataforclearchecks.php';

    $sql0='CREATE TEMPORARY TABLE unclearedchecks 
    Select FromAccountID, FromAccount, DateofCheck, CheckNo, u.Payee, truncate(AmountofCheck,2) as AmountofCheck, format(AmountofCheck,2) as Amount FROM banktxns_22unclearedcheckamts u where DateofCheck<=Date_Add(\''.$asof.'\', INTERVAL 6 day)';
    
    $stmt=$link->prepare($sql0);
    $stmt->execute();
    $sql1='SELECT FromAccount, format(Sum(AmountofCheck),2) as TotalUncleared, `Order` FROM unclearedchecks u LEFT JOIN banktxns_1maintaining m ON m.AccountID=u.FromAccountID GROUP BY u.FromAccountID ORDER BY `Order`';
    $sql2='Select FromAccount, DateofCheck, CheckNo, Payee, AmountofCheck, Amount FROM unclearedchecks ';
    $showbranches=false;
    $groupby='FromAccount';
    $orderby=' ORDER By FromAccount,DateofCheck,Payee ';
    $columnnames1=array('FromAccount');
    $columnnames2=array('DateofCheck','CheckNo', 'Payee','Amount');
    $coltototal='AmountofCheck'; $runtotal=true;
    $showgrandtotal=true;
    include('../backendphp/layout/displayastablewithsub.php');
        break;
case 'MonthEnd': 
    if (!allowedToOpen(array(6245,6249),'1rtc')) {   echo 'No permission'; exit;}   ?>
<html>
<head>
<title>Month-End Balances</title>
<?php
    $title='Month-End Balances'; $columnnames=array('AccountID','BankAccount','Beginning'); $sql='';
    if (allowedToOpen(6249,'1rtc')) { $formdesc='<form action="lookupbankdata.php?w=SetMonthEnd" method="post">
		Choose Month <input type="number" min="1" max="12" name="asof" value="'.(date('m')==1?1:date('m')-1).'">
		<input type="submit" name="submit" value="Set month-end values">
                </form>';
        }
    
    include '../backendphp/functions/monthsarray.php';
    foreach ($months as $balmonth){
        $monthname=monthName($balmonth);
        $columnnames[]=$monthname;
        $sql=$sql.', FORMAT(SUM(CASE WHEN (YEAR(b.DateofBal)='.$currentyr.' AND MONTH(b.DateofBal)='.$balmonth.') THEN b.BalPerMonth END),2) as `'.$monthname.'`';
    }
    $sql='SELECT c.AccountID, c.ShortAcctID AS BankAccount, FORMAT((CASE WHEN YEAR(b.DateofBal)<'.$currentyr.' THEN b.BalPerMonth END),2) as Beginning '.$sql.' FROM `acctg_1chartofaccounts` c JOIN banktxns_bankbalancespermonth b ON c.AccountID = b.AccountID GROUP BY c.AccountID ORDER BY c.ShortAcctID;';
	
	// echo $sql;
    include('../backendphp/layout/displayastable.php');
break;   

case 'SetMonthEnd':
    if (!allowedToOpen(6249,'1rtc')) {   echo 'No permission'; exit;} 
    $date=$currentyr.'-'.$_POST['asof'].'-'.cal_days_in_month(CAL_GREGORIAN,$_POST['asof'],$currentyr);
    
if ($date<=$_SESSION['nb4A']  or date('Y', strtotime($date))<>$currentyr){
		echo 'Data protected';
		goto noform;
}
$sql0='CREATE TEMPORARY TABLE `setmonthend` AS
    select 
        `m`.`AccountID` AS `AccountID`,
        truncate(((sum(ifnull(`bt`.`DepositAmt`, 0)) - sum(ifnull(`bt`.`WithdrawAmt`, 0)))+ ifnull(`bb`.`BalPerMonth`,0)),
            2) AS `BalPerMonth`,
        \''.$date.'\' AS `AsOfDate`
    from
        ((`banktxns_1maintaining` `m`
        left join `banktxns_banktxns` `bt` ON ((`m`.`AccountID` = `bt`.`AccountID`)))
        left join `banktxns_bankbalancespermonth` `bb` ON ((`bb`.`AccountID` = `m`.`AccountID`)))
    where
        ((`bt`.`TxnDate` <= \''.$date.'\')
            and (month(`bt`.`TxnDate`) = if((year(\''.$date.'\') <> year(`bt`.`TxnDate`)),
            12,
            month(\''.$date.'\')))
            and (month(`bb`.`DateofBal`) = if((month(\''.$date.'\') = 1),
            12,
            (month(\''.$date.'\') - 1))))
    group by `m`.`AccountID`;';
$stmt=$link->prepare($sql0);
$stmt->execute();

$sql0='INSERT INTO `banktxns_bankbalancespermonth` (`AccountID`, `DateofBal`, `BalPerMonth`)
Select s.`AccountID`, \''.$date.'\' as `DateofBal`, s.`BalPerMonth` from `setmonthend` s left join `banktxns_bankbalancespermonth` bb on s.AccountID=bb.AccountID and s.AsOfDate=bb.DateofBal where bb.AccountID is null;';
// echo $sql0; break;
$stmt=$link->prepare($sql0);
$stmt->execute();

$sql0='UPDATE `banktxns_bankbalancespermonth` bb SET `BalPerMonth`=(Select `BalPerMonth` from `setmonthend` s where s.`AccountID`=bb.`AccountID` and bb.`DateofBal`=\''.$date.'\')  where bb.`DateofBal`=\''.$date.'\'';
$stmt=$link->prepare($sql0);
$stmt->execute();

$sql='INSERT INTO `banktxns_banktxns` (`AccountID`, `TxnDate`, `WithdrawAmt`, `DepositAmt`, `Balance`, `Remarks`, `Cleared`)
Select `AccountID`, date_add(\''.($date).'\', interval 1 day) as `TxnDate`, 0 as `WithdrawAmt`, 0 as `DepositAmt`, `BalPerMonth` as `Balance`, "initialize" as `Remarks`, "1" as `Cleared`
from `setmonthend`;';
$stmt=$link->prepare($sql);
$stmt->execute();
header("Location:lookupbankdata.php?w=MonthEnd");
    break;

case 'BankBalances':
if (!allowedToOpen(6248,'1rtc')) {   echo 'No permission'; exit;} 
$link=connect_db(''.$currentyr.'_1rtc',1);
$sessako=$_SESSION['(ak0)'];
$asofthisfri=date('Y-m-d',strtotime("Friday this week")); $asofthisfricol=date('m-d',strtotime($asofthisfri));
$asof=date('Y-m-d',strtotime("Friday next week")); $asofcol=date('m-d',strtotime("Friday next week"));
$asoffri2=date('Y-m-d',strtotime("Friday this week + 2 weeks")); $asoffri2col=date('m-d',strtotime($asoffri2));
$asoffri3=date('Y-m-d',strtotime("Friday this week + 3 weeks")); $asoffri3col=date('m-d',strtotime($asoffri3));
$asoffri4=date('Y-m-d',strtotime("Friday this week + 4 weeks")); $asoffri4col=date('m-d',strtotime($asoffri4));
$title='Bank Balances'; $formdesc='</i><br>New Deposits are recorded deposits in our data, but no corresponding downloaded bank transactions yet.<br>'
        . 'Downloaded and uncleared bank deposits are deducted from the CASH column.<br>'
        . 'Only new CASH deposits are added to the available value.<br><i>';
$hidecount=true; 
$addlmenu='<form action=lookupbankdata.php?w=SetAsZero method=post><input type=submit name=submit value="Set_TempValues_Zero">'
        . '&nbsp; &nbsp; <input type=submit name=submit value="Set_Transfers_Zero">'
        . '&nbsp; &nbsp; <input type=submit name=submit value="Set_Budgets_Zero"></form>';

include_once 'sqlphp/dataforcleardep.php';
include_once 'sqlphp/dataforclearchecks.php';

$sql0='CREATE TEMPORARY TABLE uncleareddep AS SELECT `dm`.`DebitAccountID` AS `AccountID`, (IFNULL(TRUNCATE(SUM(CASE WHEN (dt.FirstofType<>2 AND dm.Date<=(CURDATE() + INTERVAL 3 DAY)) THEN IFNULL(`dt`.`SumOfAmount`,0) END),2),0))-(SELECT IFNULL(SUM(DepositAmt),0) FROM `banktxns_33tocleardeposits` WHERE AccountID=`dm`.`DebitAccountID`) AS `NewCASHDep`, IFNULL(TRUNCATE(SUM(CASE WHEN dt.FirstofType=2 AND dm.Date<=(CURDATE() + INTERVAL 3 DAY) THEN `dt`.`SumOfAmount` END),2),0) AS `NewCHECKDep`
    FROM `acctg_2depositmain` `dm` JOIN `banktxns_32uniunclearedfordeptotals` `dt` ON (`dm`.`TxnID` = `dt`.`TxnID`)
    WHERE isnull(`dm`.`Cleared`) GROUP BY `dm`.`DebitAccountID`'; $stmt=$link->prepare($sql0); $stmt->execute(); 
	
	// echo $sql0.'<br><br>';
$stmt=$link->prepare('DROP TABLE IF EXISTS `bankbalances'.$sessako.'`'); $stmt->execute();



$sqlmaxdate='SELECT MAX(DateOfBal) AS MaxDate,(select curdate() - interval 1 month) AS LastMonth FROM banktxns_bankbalancespermonth;';
$stmtmaxdate = $link->query($sqlmaxdate); $rowmaxdate=$stmtmaxdate->fetch();
$datamonth=(date("m",strtotime($rowmaxdate['MaxDate'])));

IF(($datamonth<>(date("m",strtotime(date('Y-m-d')))-1)) AND substr($rowmaxdate['MaxDate'],0,4)==$currentyr){
	echo '<center><font color="red"><h3>No month-end balances set for '.date("F", mktime(0, 0, 0, (date("m",strtotime($rowmaxdate['LastMonth']))), 10)).'</h3></font></center>';
}
include_once 'sqlphp/createbankbaltoday.php';

 $sql0='CREATE TABLE `bankbalances'.$sessako.'` as
SELECT m.AccountID,m.ShortAcctID,m.AcctNo as AccountNumber, LPAD(m.Order,2,"0") AS `Order`, truncate(Backup,0) as Backup, truncate(m.Maintain,0) as Maintain,
truncate(m.Transfers,0) as Transfers, truncate(m.Budget,0) as Budget,
truncate(IFNULL(bt.BankBalToday,0),0) as BankBalToday,
truncate(sum(case when u.DateofCheck<=DATE_ADD(\''. $asofthisfri .'\',INTERVAL 6 day) then u.AmountofCheck else 0 end),0) as `Uncleared'.$asofthisfricol.'`,
truncate(round(IFNULL(BankBalToday,0)-round(sum(case when u.DateofCheck<=DATE_ADD(\''. $asofthisfri .'\',INTERVAL 6 day) then u.AmountofCheck else 0 end),2)-Backup-Maintain-Transfers-Budget,2),0) as `Available'.$asofthisfricol.'`,
truncate(sum(case when u.DateofCheck<=DATE_ADD(\''. $asof .'\',INTERVAL 6 day) then u.AmountofCheck else 0 end),0) as `Uncleared'.$asofcol.'`, 
truncate(round(IFNULL(BankBalToday,0)-round(sum(case when u.DateofCheck<=DATE_ADD(\''. $asof .'\',INTERVAL 6 day) then IFNULL(u.AmountofCheck,0) else 0 end),2)-Backup-Maintain-Transfers-Budget,2),0) as `Available'.$asofcol.'`,
truncate(sum(case when u.DateofCheck<=DATE_ADD(\''. $asoffri2 .'\',INTERVAL 6 day) then u.AmountofCheck else 0 end),0) as `Uncleared'.$asoffri2col.'`, 
truncate(round(IFNULL(BankBalToday,0)-round(sum(case when u.DateofCheck<=DATE_ADD(\''. $asoffri2 .'\',INTERVAL 6 day) then u.AmountofCheck else 0 end),2)-Backup-Maintain-Transfers-Budget,2),0) as `Available'.$asoffri2col.'`,
truncate(sum(case when u.DateofCheck<=DATE_ADD(\''. $asoffri3 .'\',INTERVAL 6 day) then u.AmountofCheck else 0 end),0) as `Uncleared'.$asoffri3col.'`, 
truncate(round(IFNULL(BankBalToday,0)-round(sum(case when u.DateofCheck<=DATE_ADD(\''. $asoffri3 .'\',INTERVAL 6 day) then u.AmountofCheck else 0 end),2)-Backup-Maintain-Transfers-Budget,2),0) as `Available'.$asoffri3col.'`,
    truncate(sum(case when u.DateofCheck<=DATE_ADD(\''. $asoffri4 .'\',INTERVAL 6 day) then u.AmountofCheck else 0 end),0) as `Uncleared'.$asoffri4col.'`, 
truncate(round(IFNULL(BankBalToday,0)-round(sum(case when u.DateofCheck<=DATE_ADD(\''. $asoffri4 .'\',INTERVAL 6 day) then u.AmountofCheck else 0 end),2)-Backup-Maintain-Transfers-Budget,2),0) as `Available'.$asoffri4col.'`,
m.Remarks, IFNULL(NewCASHDep,0) AS NewCASHDep, IFNULL(NewCHECKDep,0) AS NewCHECKDep   FROM `banktxns_1maintaining` m
LEFT join `banktxns_431qrybalancetoday` bt on bt.AccountID=m.AccountID
left join `banktxns_22unclearedcheckamts` u on bt.AccountID=u.FromAccountID 
LEFT JOIN `uncleareddep` ud ON m.AccountID=ud.AccountID WHERE m.Order<>9999
 group by m.AccountID ORDER BY m.Order';
 // echo $sql0;
// if($sessako==1002){echo $sql0; break;}
$stmt=$link->prepare($sql0); $stmt->execute(); 

$columnnames=array('Order','AccountID','ShortAcctID','BankBalToday','NewCASHDep','NewCHECKDep','Maintain','Uncleared'.$asofthisfricol,'Available'.$asofthisfricol,'Uncleared'.$asofcol,
		   'Available'.$asofcol,'Uncleared'.$asoffri2col,'Available'.$asoffri2col,'Uncleared'.$asoffri3col,'Available'.$asoffri3col,'Uncleared'.$asoffri4col,'Available'.$asoffri4col,
		   'Transfers','Backup','Budget','AccountNumber','Remarks'); 
$columnstoedit=array('Backup','Transfers','Budget','Remarks'); 

//$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'`Order`'); $columnsub=$columnnames;

$sql='Select m.AccountID, m.ShortAcctID, AccountNumber, bb.Remarks, format(BankBalToday,0) as BankBalToday, bb.Order,
format(`Uncleared'.$asofthisfricol.'`,0) as `Uncleared'.$asofthisfricol.'`, format(`Available'.$asofthisfricol.'`+NewCASHDep,0) as `Available'.$asofthisfricol.'`,
format(`Uncleared'.$asofcol.'`-`Uncleared'.$asofthisfricol.'`,0) as `Uncleared'.$asofcol.'`, format(`Available'.$asofcol.'`+NewCASHDep,0) as `Available'.$asofcol.'`,
format(`Uncleared'.$asoffri2col.'`-`Uncleared'.$asofcol.'`,0) as `Uncleared'.$asoffri2col.'`, format(`Available'.$asoffri2col.'`+NewCASHDep,0) as `Available'.$asoffri2col.'`,
format(`Uncleared'.$asoffri3col.'`-`Uncleared'.$asoffri2col.'`,0) as `Uncleared'.$asoffri3col.'`, format(`Available'.$asoffri3col.'`+NewCASHDep,0) as `Available'.$asoffri3col.'`,
format(`Uncleared'.$asoffri4col.'`-`Uncleared'.$asoffri3col.'`,0) as `Uncleared'.$asoffri4col.'`, format(`Available'.$asoffri4col.'`+NewCASHDep,0) as `Available'.$asoffri4col.'`,
format(m.Maintain,0) as Maintain, format(m.Backup,0) as Backup, format(m.Transfers,0) as Transfers, format(m.Budget,0) as Budget, IF(NewCASHDep=0,"",FORMAT(NewCASHDep,0)) AS NewCASHDep, IF(NewCHECKDep=0,"",FORMAT(NewCHECKDep,0)) AS NewCHECKDep
from `bankbalances'.$sessako.'` bb RIGHT JOIN banktxns_1maintaining m ON m.AccountID=bb.AccountID WHERE m.Order<>9999 ';

     
$txnid='AccountID'; //$coltototal='Available'; $showgrandtotal=true;
$editprocess='prbankbalances.php?AccountID='; $editprocesslabel='Enter';
include('../backendphp/layout/displayastableeditcellsnoheaders.php'); 
$sqlsum='Select  "Totals" AS AccountID, "(No Remittance)" AS ShortAcctID,"" AS AccountNumber, "" Remarks, format(sum(BankBalToday),0) as BankBalToday, "" AS `Order`,
format(sum(`Uncleared'.$asofthisfricol.'`),0) as `Uncleared'.$asofthisfricol.'`, format(sum(`Available'.$asofthisfricol.'`+NewCASHDep),0) as `Available'.$asofthisfricol.'`,
format(sum(`Uncleared'.$asofcol.'`)-sum(`Uncleared'.$asofthisfricol.'`),0) as `Uncleared'.$asofcol.'`, format(sum(`Available'.$asofcol.'`+NewCASHDep),0) as `Available'.$asofcol.'`,
format(sum(`Uncleared'.$asoffri2col.'`)-sum(`Uncleared'.$asofcol.'`),0) as `Uncleared'.$asoffri2col.'`, format(sum(`Available'.$asoffri2col.'`+NewCASHDep),0) as `Available'.$asoffri2col.'`,
format(sum(`Uncleared'.$asoffri3col.'`)-sum(`Uncleared'.$asoffri2col.'`),0) as `Uncleared'.$asoffri3col.'`, format(sum(`Available'.$asoffri3col.'`+NewCASHDep),0) as `Available'.$asoffri3col.'`,format(sum(`Uncleared'.$asoffri4col.'`)-sum(`Uncleared'.$asoffri3col.'`),0) as `Uncleared'.$asoffri4col.'`, format(sum(`Available'.$asoffri4col.'`+NewCASHDep),0) as `Available'.$asoffri4col.'`,format(sum(Maintain),0) as Maintain,format(sum(Backup),0) as Backup,"" AS Transfers, format(SUM(Budget),0) as Budget, FORMAT(SUM(NewCASHDep),0) AS NewCASHDep, FORMAT(SUM(NewCHECKDep),0) AS NewCHECKDep from `bankbalances'.$sessako.'` where `Order`<80';
/*$stmtsum=$link->query($sqlsum);
$resultsum=$stmtsum->fetch();*/
$sql=$sqlsum;$txnid='Total';
//$columnnames=array('Total','BankBalToday','Backup','Uncleared'.$asofthisfricol,'Available'.$asofthisfricol,'Uncleared'.$asofcol,'Available'.$asofcol,
	//	   'Uncleared'.$asoffri2col,'Available'.$asoffri2col,'Uncleared'.$asoffri3col,'Available'.$asoffri3col,'TotalBudget');
//if($sessako){echo $sql; break;}
unset($editprocess,$sortfield,$addlmenu); 
include('../backendphp/layout/displayastableonlynoheaders.php');
//echo '<br><br>Bank Bal:  '.$resultsum['TotalBankBalToday'].str_repeat('&nbsp',10).'Total Uncleared '.$asofthisfricol.':  '.$resultsum['TotalUncleared'.$asofthisfricol].str_repeat('&nbsp',20).'Total Backup:  '.$resultsum['TotalBackup'].str_repeat('&nbsp',10).'Net Available '.$asofthisfricol.':  '.$resultsum['TotalPeso'.$asofthisfricol].str_repeat('&nbsp',20).'Total Budget:  '.$resultsum['TotalBudget'];
//echo '<br><br>In backup:<br>'.str_repeat('&nbsp',20).'HSS PDC: 9.5M';
echo '<div style="float:right;"><a href="prbankbalances.php?Send=1">Send budgets</a></div>';
// for budgetting of ap:
if(allowedToOpen(62461,'1rtc')){
    $subtitle='<br><br>Budget Due AP'; 
$stmt=$link->prepare('DROP TABLE IF EXISTS `dueap'.$sessako.'`'); $stmt->execute();
$sql0='CREATE TABLE `dueap'.$sessako.'` as
    SELECT ifnull(ap.RCompany,0) as `RCompany`, 
    truncate(sum(case when (`ap`.`Date` + interval ((((6 - dayofweek((`ap`.`Date` + interval `ap`.`PayTerms` day))) + 7) % 7) + ifnull(`ap`.`PayTerms`, 0)) day)<=\''. $asofthisfri .'\' then  `ap`.`PayBalance` else 0 end),0) AS `Due'.$asofthisfricol.'`,
    truncate(sum(case when (`ap`.`Date` + interval ((((6 - dayofweek((`ap`.`Date` + interval `ap`.`PayTerms` day))) + 7) % 7) + ifnull(`ap`.`PayTerms`, 0)) day)<=\''. $asof .'\' then  `ap`.`PayBalance` else 0 end),0) AS `Due'.$asofcol.'`,
    truncate(sum(case when (`ap`.`Date` + interval ((((6 - dayofweek((`ap`.`Date` + interval `ap`.`PayTerms` day))) + 7) % 7) + ifnull(`ap`.`PayTerms`, 0)) day)<=\''. $asoffri2 .'\' then  `ap`.`PayBalance` else 0 end),0) AS `Due'.$asoffri2col.'`,
    truncate(sum(case when (`ap`.`Date` + interval ((((6 - dayofweek((`ap`.`Date` + interval `ap`.`PayTerms` day))) + 7) % 7) + ifnull(`ap`.`PayTerms`, 0)) day)<=\''. $asoffri3 .'\' then  `ap`.`PayBalance` else 0 end),0) AS `Due'.$asoffri3col.'`, truncate(sum(case when (`ap`.`Date` + interval ((((6 - dayofweek((`ap`.`Date` + interval `ap`.`PayTerms` day))) + 7) % 7) + ifnull(`ap`.`PayTerms`, 0)) day)<=\''. $asoffri4 .'\' then  `ap`.`PayBalance` else 0 end),0) AS `Due'.$asoffri4col.'` FROM `acctg_23balperinv` `ap` group by ap.RCompany';
 
$stmt0=$link->prepare($sql0); $stmt0->execute(); 

$sql='SELECT bb.AccountID,bb.ShortAcctID,FORMAT(`Due'.$asofthisfricol.'`,0) AS `Due'.$asofthisfricol.'`,'
        . ' FORMAT((`Available'.$asofthisfricol.'`-IFNULL(`Due'.$asofthisfricol.'`,0)),0) AS `Available'.$asofthisfricol.'`,'
        . ' FORMAT((IFNULL(`Due'.$asofcol.'`,0)-IFNULL(`Due'.$asofthisfricol.'`,0)),0) AS `Due'.$asofcol.'`,FORMAT((`Available'.$asofcol.'`-`Due'.$asofcol.'`),0) AS `Available'.$asofcol.'`,'
        . ' FORMAT((IFNULL(`Due'.$asoffri2col.'`,0)-IFNULL(`Due'.$asofcol.'`,0)),0) AS `Due'.$asoffri2col.'`,FORMAT((`Available'.$asoffri2col.'`-`Due'.$asoffri2col.'`),0) AS `Available'.$asoffri2col.'`,'
        . ' FORMAT((IFNULL(`Due'.$asoffri3col.'`,0)-IFNULL(`Due'.$asoffri2col.'`,0)),0) AS `Due'.$asoffri3col.'`,FORMAT((`Available'.$asoffri3col.'`-`Due'.$asoffri3col.'`),0) AS `Available'.$asoffri3col.'`, '
        . ' FORMAT((IFNULL(`Due'.$asoffri4col.'`,0)-IFNULL(`Due'.$asoffri3col.'`,0)),0) AS `Due'.$asoffri4col.'`,FORMAT((`Available'.$asoffri4col.'`-`Due'.$asoffri4col.'`),0) AS `Available'.$asoffri4col.'` '
        . ' FROM `bankbalances'.$sessako.'` bb JOIN `banktxns_1maintaining` m ON m.AccountID=bb.AccountID JOIN `dueap'.$sessako.'` d ON m.RCompanyUse=d.RCompany;';
$txnid='AccountID';
$columnnames=array('AccountID','ShortAcctID','Due'.$asofthisfricol,'Available'.$asofthisfricol,'Due'.$asofcol,'Available'.$asofcol,
		   'Due'.$asoffri2col,'Available'.$asoffri2col,'Due'.$asoffri3col,'Available'.$asoffri3col,'Due'.$asoffri4col,'Available'.$asoffri4col);
//if($sessako==1002){echo $sql0; break;}
include('../backendphp/layout/displayastableonlynoheaders.php');

$sql='SELECT "Total" AS `Total`,FORMAT(SUM(IFNULL(`Due'.$asofthisfricol.'`,0)),0) AS `Due'.$asofthisfricol.'`,'
        . ' FORMAT(SUM(`Available'.$asofthisfricol.'`-IFNULL(`Due'.$asofthisfricol.'`,0)),0) AS `Available'.$asofthisfricol.'`,'
        . ' FORMAT(SUM(IFNULL(`Due'.$asofcol.'`,0)-IFNULL(`Due'.$asofthisfricol.'`,0)),0) AS `Due'.$asofcol.'`,FORMAT(SUM(`Available'.$asofcol.'`-IFNULL(`Due'.$asofcol.'`,0)),0) AS `Available'.$asofcol.'`,'
        . ' FORMAT(SUM(IFNULL(`Due'.$asoffri2col.'`,0)-IFNULL(`Due'.$asofcol.'`,0)),0) AS `Due'.$asoffri2col.'`,FORMAT(SUM(`Available'.$asoffri2col.'`-IFNULL(`Due'.$asoffri2col.'`,0)),0) AS `Available'.$asoffri2col.'`,'
        . ' FORMAT(SUM(IFNULL(`Due'.$asoffri3col.'`,0)-IFNULL(`Due'.$asoffri2col.'`,0)),0) AS `Due'.$asoffri3col.'`,FORMAT(SUM(`Available'.$asoffri3col.'`-IFNULL(`Due'.$asoffri3col.'`,0)),0) AS `Available'.$asoffri3col.'`, '
        . ' FORMAT(SUM(IFNULL(`Due'.$asoffri4col.'`,0)-IFNULL(`Due'.$asoffri3col.'`,0)),0) AS `Due'.$asoffri4col.'`,FORMAT(SUM(`Available'.$asoffri4col.'`-IFNULL(`Due'.$asoffri4col.'`,0)),0) AS `Available'.$asoffri4col.'` '
        . ' FROM `bankbalances'.$sessako.'` bb JOIN `banktxns_1maintaining` m ON m.AccountID=bb.AccountID LEFT JOIN `dueap'.$sessako.'` d ON m.RCompanyUse=d.RCompany WHERE bb.Order<80;';
$txnid='Total';
$columnnames=array('Total','Due'.$asofthisfricol,'Available'.$asofthisfricol,'Due'.$asofcol,'Available'.$asofcol,
		   'Due'.$asoffri2col,'Available'.$asoffri2col,'Due'.$asoffri3col,'Available'.$asoffri3col,'Due'.$asoffri4col,'Available'.$asoffri4col);
unset($subtitle,$txnid);
include('../backendphp/layout/displayastableonlynoheaders.php');


$subtitle='Future Checks and Purchases Due';
$stmt=$link->prepare('DROP TABLE IF EXISTS `futureamountdue'.$sessako.'`'); $stmt->execute();
$sql0='CREATE TABLE `futureamountdue'.$sessako.'` ( DateDue date not null, IssuedChecks double null, Purchases double null )
SELECT (`DateDue` + interval mod(6-DAYOFWEEK(`DateDue`)+7,7) day) AS `DateDue`,  0 AS IssuedChecks, sum(bal.PayBalance) AS Purchases
FROM `acctg_23balperinv` bal left join `1suppliers` s on s.SupplierNo=bal.SupplierNo where bal.PayBalance<>0
GROUP BY  (`DateDue` + interval mod(6-DAYOFWEEK(`DateDue`)+7,7) day) HAVING `DateDue`>\''. $asoffri4 .'\'
UNION ALL SELECT Date_Add(`DateofCheck`, INTERVAL mod(6-DAYOFWEEK(`DateofCheck`)+7,7) DAY) AS DateDue, Sum(AmountofCheck) AS IssuedChecks, 0 AS Purchases
FROM `banktxns_22unclearedcheckamts` GROUP BY DateDue  HAVING `DateDue`>\''. $asoffri4 .'\';';
$stmt=$link->prepare($sql0); $stmt->execute(); 

$sql1='SELECT `DateDue` FROM `futureamountdue'.$sessako.'` GROUP BY `DateDue`';
$stmt1=$link->query($sql1); $res1=$stmt1->fetchAll();
$columnnames=array('DateDue', 'IssuedChecks', 'Purchases', 'Total');
$sql='SELECT `DateDue`, FORMAT(SUM(IssuedChecks),0) AS IssuedChecks, FORMAT(SUM(Purchases),0) AS Purchases, FORMAT(SUM(Purchases+IssuedChecks),0) AS Total FROM `futureamountdue'.$sessako.'` GROUP BY DateDue'; 
$width='20%';
include('../backendphp/layout/displayastableonlynoheaders.php');

$stmt=$link->prepare('DROP TABLE IF EXISTS `dueap'.$sessako.'`'); $stmt->execute();
$stmt=$link->prepare('DROP TABLE IF EXISTS `bankbalances'.$sessako.'`'); $stmt->execute();
$stmt=$link->prepare('DROP TABLE IF EXISTS `futureamountdue'.$sessako.'`'); $stmt->execute();
}
break;

case 'SetAsZero':
    if($_POST['submit']=='Set_Transfers_Zero') { $sql='UPDATE `banktxns_1maintaining` SET `Transfers`=0;';}
    elseif($_POST['submit']=='Set_Budgets_Zero'){ $sql='UPDATE `banktxns_1maintaining` SET `Budget`=0;';}
    else { $sql='UPDATE `banktxns_1maintaining` SET `Backup`=0, `Transfers`=0, `Budget`=`DefaultBudget`;';}  
    $link->query($sql); 
    header("Location:".$_SERVER['HTTP_REFERER']);
break;

case 'FundTransfers':
if (!allowedToOpen(6253,'1rtc')) {   echo 'No permission'; exit;}
$title='Fund Transfers and Budgets'; $subtitle='Fund Transfers';
$formdesc='Negative values show you must deposit in that account.';
$columnnames=array('AccountID','ShortAcctID','Transfer','AccountNumber');
$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'`Order`'); $columnsub=$columnnames;
$sql='Select bb.AccountID, ShortAcctID, AcctNo AS AccountNumber, bb.Order, format(Transfers,0) as Transfer'
        . ' FROM `banktxns_1maintaining` bb WHERE (`Order` <80) AND (Transfers<>0)  ORDER BY '
        .$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');

include('../backendphp/layout/displayastablenosort.php');
unset($formdesc);
echo '<hr><br><br>';
$subtitle='Budgets';
$columnnames=array('AccountID','ShortAcctID','Budget', 'Remarks');
$sql='Select bb.AccountID, ShortAcctID, AcctNo AS AccountNumber, bb.Order, format(Budget,0) as Budget, Remarks'
        . ' FROM `banktxns_1maintaining` bb WHERE (AccountID BETWEEN 106 AND 145)  AND (Budget<>0)  ORDER BY '
        .$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
$width='40%';
include('../backendphp/layout/displayastableonlynoheaders.php');

break;
case 'LatestImports':
   if (!allowedToOpen(6243,'1rtc')) {   echo 'No permission'; exit;} 
$title='Latest Imported Data';
$showbranches=false;

$addlmenu='<a href="lookupbankdata.php?w=LatestImportsBal" target=_blank>Lookup Balances</a><br>';
$txnid='TxnNo';
$delprocess='lookupbankdata.php?w=LatestImportsDel&TxnNo=';
$sortfield=(!isset($_POST['sortfield'])?'TxnNo DESC':$_POST['sortfield']).(!isset($_POST['sortarrange'])?' ':' '.$_POST['sortarrange']);
$sql='SELECT bt.*, c.ShortAcctID FROM banktxns_banktxns bt join `acctg_1chartofaccounts` c on c.AccountID=bt.AccountID
WHERE ((bt.TxnDate)>=Date_Add(Now(), interval -7 day)) OR TxnDate=\'0000-00-00\' OR (YEAR(TxnDate)<>\''.substr($currentyr,0,4).'\' AND bt.Remarks NOT LIKE \'initialize\') or TxnNo in (select TxnNo from banktxns_banktxns where month(TxnDate)<=month(curdate()) and TxnNo>(select TxnNo from banktxns_banktxns where month(TxnDate)=month(curdate()) limit 1)) ORDER BY '.$sortfield;;

$columnnames=array('AccountID','ShortAcctID','TxnDate','Particulars','BankBranch','CheckNo','BankTransCode','WithdrawAmt','DepositAmt','Balance','Remarks','Cleared');
$columnsub=$columnnames;

include('../backendphp/layout/displayastablewithedit.php');
break;
case 'LatestImportsDel':
   if (!allowedToOpen(6247,'1rtc')) {
   echo 'No permission'; exit;
} 
    $sql='Delete from banktxns_banktxns where TxnNo='.$_GET['TxnNo'];
    $stmt=$link->prepare($sql);
    $stmt->execute();
    header("Location:".$_SERVER['HTTP_REFERER']);
break;
case 'LatestImportsBal':
   if (!allowedToOpen(6247,'1rtc')) {
   echo 'No permission'; exit;
} 
$title='Check Balances of Imported Data';
include_once 'sqlphp/createbankbaltoday.php';
$sql='SELECT bal.ShortAcctID, bal.Today, format(bal.BankBalToday,2) as BankBalToday, Max(bt.TxnDate) AS MaxOfTxnDate, (Select format(Balance,2) from banktxns_banktxns bt where bt.TxnNo=li.MaxOfTxnNo) AS LastOfBalance, Truncate(BankBalToday-(Select Balance from banktxns_banktxns bt where bt.TxnNo=li.MaxOfTxnNo),2) AS Diff
FROM (banktxns_banktxns bt INNER JOIN `banktxns_431qrybalancetoday` bal ON bt.AccountID = bal.AccountID) INNER JOIN (SELECT 
        `bt`.`AccountID` AS `AccountID`, MAX(`bt`.`TxnNo`) AS `MaxOfTxnNo` FROM  `banktxns_banktxns` `bt`
    GROUP BY `bt`.`AccountID`) li ON (bt.AccountID = li.AccountID) AND (bt.TxnNo = li.MaxOfTxnNo)
GROUP BY bal.ShortAcctID, bal.Today, bal.BankBalToday';

$columnnames=array('ShortAcctID','Today','BankBalToday','MaxOfTxnDate','LastOfBalance','Diff');
include('../backendphp/layout/displayastablenosort.php');
break;

case 'CompareDailyTotals':
   if (!allowedToOpen(6242,'1rtc')) {   echo 'No permission'; exit;} 

$title='Compare Daily Totals';
$showbranches=false;
?>
<form method='POST' action='lookupbankdata.php?w=CompareDailyTotals'>
<?php
include('../backendphp/layout/choosemonth.php');
  ?>
<input type="text" name="bank" list="banks" size=60 autocomplete="off" required="true">
<?php include('bankslist.php'); ?>&nbsp &nbsp &nbsp 
<input type="submit" name="lookup" value="Lookup">
</form>
<?php
    if (!isset($_POST['bank'])){
	goto noform;
    }
// include('../backendphp/functions/getnumber.php');
// $acctid=getNumber('Account',$_POST['bank']);
$formdesc='<font color="blue">'.$_POST['bank'] . ' for the month of ' . $_POST['month'].'</font>';
$sql0='Create temporary table lefttable(
    TxnDate date  null,
    BankWithdraw double null,
    BankDeposit double null,
    Cleared smallint(2) not null
)
SELECT bt.TxnDate, Sum(bt.WithdrawAmt) AS BankWithdraw, Sum(bt.DepositAmt) AS BankDeposit, bt.Cleared FROM banktxns_banktxns bt INNER JOIN banktxns_1maintaining m ON bt.AccountID=m.AccountID WHERE Month(bt.TxnDate)=Month(\''.$_POST['month'].'\')  AND bt.AccountID='.$acctid.' GROUP BY bt.AccountID, bt.TxnDate, bt.Cleared';
$stmt=$link->prepare($sql0);
$stmt->execute();
$sqlleft='Select * from lefttable';
//echo $sqlleft; 
$columnnamesleft=array('TxnDate','BankWithdraw','BankDeposit','Cleared');
$lefttabletitle='Bank<br>';
$sqltotalleft='Select Sum(BankWithdraw) AS TotalBankWithdraw, Sum(BankDeposit) AS TotalBankDeposits, Sum(BankDeposit)-Sum(BankWithdraw) as Net from lefttable';
$stmt=$link->query($sqltotalleft);
$resultleft=$stmt->fetch();
$totalleft='<td>Totals</td><td>'.number_format($resultleft['TotalBankWithdraw'],2).'</td><td>'.number_format($resultleft['TotalBankDeposits'],2).'</td><td>'.number_format($resultleft['Net'],2).'</td>';



$sql1='Create temporary table righttable(
    Date date null,
    DataWithdraw double null,
    DataDeposit double null,
    Cleared smallint(2) not null
)
SELECT dc.Date, Sum(dc.CREDIT) AS DataWithdraw,Sum(dc.DEBIT) AS DataDeposit,  dc.Cleared FROM banktxns_45datacibtxns2 dc INNER JOIN banktxns_1maintaining m ON dc.AccountID=m.AccountID WHERE Month(`Date`)=Month(\''.$_POST['month'].'\') AND dc.AccountID='.$acctid.' GROUP BY dc.AccountID, dc.Date';
//echo $sql1; break;
$stmt=$link->prepare($sql1);
$stmt->execute();
$sqlright='Select * from righttable';
$righttabletitle='Data<br>';
$columnnamesright=array('Date','DataWithdraw','DataDeposit','Cleared');
$sqltotalright='Select Sum(DataWithdraw) AS TotalDataWithdraw, Sum(DataDeposit) AS TotalDataDeposits, Sum(DataDeposit)-Sum(DataWithdraw) as Net from righttable';
$stmt=$link->query($sqltotalright);
$resultright=$stmt->fetch();
$totalright='<td>Totals</td><td>'.number_format($resultright['TotalDataWithdraw'],2).'</td><td>'.number_format($resultright['TotalDataDeposits'],2).'</td><td>'.number_format($resultright['Net'],2).'</td>';
include('../backendphp/layout/twotablessidebyside.php');
echo '<br><br><br class="clearFloat" />';
$subtitle='Differences:';
$sql='Select b.txndate AS Date, FORMAT(BankWithdraw-DataWithdraw,2) AS WithdrawDiff, FORMAT(BankDeposit-DataDeposit,2) AS DepositDiff FROM lefttable b JOIN righttable d ON b.txndate=d.Date'
        . ' WHERE b.Cleared=1 HAVING WithdrawDiff<>0 OR DepositDiff<>0';
$columnnames=array('Date','WithdrawDiff','DepositDiff');
include('../backendphp/layout/displayastableonlynoheaders.php');
break;

case 'CompareActualDeposits':
   if (!allowedToOpen(6252,'1rtc')) {
   echo 'No permission'; exit;
} 

$title='Compare Actual Deposits';
$showbranches=false;
?>
<form method='POST' action='lookupbankdata.php?w=CompareActualDeposits'>
<?php
include('../backendphp/layout/choosemonth.php');
  ?>
<input type="text" name="bank" list="banks" size=60 autocomplete="off" required="true">
<?php include('bankslist.php'); ?>&nbsp &nbsp &nbsp 
<input type="submit" name="lookup" value="Lookup">
</form>
<?php
    if (!isset($_POST['month'])){
	goto noform;
    }
// include('../backendphp/functions/getnumber.php');
// $acctid=getNumber('Account',$_POST['bank']);
$formdesc='<font color="blue">'.$_POST['bank'] . ' for the month of ' . $_POST['month'].'</font>';
$sql0='Create temporary table datatxns(
    Date date not null,
    DataWithdraw double null,
    DataDeposit double null,
    Cleared tinyint(1) not null
)
SELECT dc.Date, Sum(dc.DEBIT) AS SumOfDEBIT, Sum(dc.CREDIT) AS SumOfCREDIT, dc.AccountID, dc.ControlNo, dc.Remarks, dc.Cleared
FROM `banktxns_45datacibtxns2` dc where Month(`Date`)=Month(\''.$_POST['month'].'\') AND AccountID='.$acctid.'
GROUP BY dc.Date, dc.AccountID, dc.ControlNo, dc.Remarks, dc.Cleared ORDER BY dc.Date';
$stmt=$link->prepare($sql0);
$stmt->execute();

$sql='SELECT  bt.TxnDate, bt.Particulars, bt.DepositAmt  AS BankDep, bt.Cleared, dt.ControlNo, dt.SumOfDEBIT AS DataDep, ifnull(bt.DepositAmt,0) - ifnull(dt.SumOfDEBIT,0) as Diff
FROM banktxns_banktxns bt LEFT JOIN datatxns dt ON (bt.DepositAmt = dt.SumOfDEBIT) AND (bt.TxnDate = dt.Date)
WHERE ((bt.DepositAmt) Is Not Null) AND (Month(TxnDate))=Month(\''.$_POST['month'].'\') and bt.AccountID='.$acctid.'
ORDER BY bt.TxnDate,bt.TxnNo';

$columnnames=array('TxnDate','Particulars','BankDep','DataDep','Diff','ControlNo','Cleared');
include('../backendphp/layout/displayastable.php');
break;

case 'BouncedChecks':
   if (!allowedToOpen(655,'1rtc')) {   echo 'No permission'; exit;} 
$title='Bounced Checks';
   $sql='SELECT `TxnDate`,concat(ifnull(`Particulars`,\' \'),\' \',ifnull(`BankBranch`,\' \'),\' \',ifnull(`CheckNo`,\' \'),\' \',ifnull(`BankTransCode`,\'\'),\' \',ifnull(bt.`Remarks`,\'\')) AS Details, format(WithdrawAmt,2) as Amount, if(Cleared=1, "Y","N") as `Cleared?` FROM `banktxns_banktxns` bt join `banktxns_1maintaining` m on bt.AccountID=m.AccountID where Cleared=0 and CheckNo like \'%bounced%\' order by txndate desc;';
   $columnnames=array('TxnDate','Details','Amount','Cleared?');
include('../backendphp/layout/displayastable.php');
   break;
}
noform:
     $link=null; $stmt=null; 
?>