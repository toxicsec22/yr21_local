<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6240,'1rtc')) {   echo 'No permission'; exit;} 
$showbranches=false; include_once('../switchboard/contents.php');
 

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
if (!isset($_REQUEST['bankname'])){ 
  if (!isset($_GET['bank'])){ 
    $acctid=0;
  } else { 
    $acctid=$_GET['bank'];
  } 
}
else { $acctid=comboBoxValue($link,'acctg_1chartofaccounts','ShortAcctID',$_REQUEST['bankname'],'AccountID'); }
?>
<br>
<form method="POST" action="bankvsdatatxns.php" enctype="multipart/form-data">
<?php
include('../backendphp/layout/choosemonth.php');
  ?>
<input type="text" name="bankname" list="banks" size=60 autocomplete="off" required="true" value="<?php echo (!isset($_REQUEST['bankname'])?'':$_REQUEST['bankname']); ?>">
<?php  include('bankslist.php'); ?>
<input type="submit" name="lookup" value="Lookup"></form><br><br>
<?php
if (!isset($_POST['month'])){	goto noform;    }
$title=$_POST['bankname'] . ' as of ' . $_POST['month'];
$month=date('m',strtotime($_POST['month']));
$sqldate=$_POST['month']; //   echo $sqldate.'<br>';
$dates=explode('-',$sqldate);
$datelastmonth='Last_Day(\''.(($dates[1]==1)?($dates[0]-1):$dates[0]).'-'.(($dates[1]==1)?12:(($dates[1])-1)).'-1\')';//datelastmonth($sqldate);
    //echo $datelastmonth;

$sql0='Create temporary table 0banktxns 
  SELECT 0 as TxnNo, DateofBal as TxnDate, 0 as Withdraw, BalPerMonth as Deposit, BalPerMonth as Balance, 1 as Cleared, "Balance" as Details,"" AS ClearedBy, "" AS ClearedTS FROM `banktxns_1maintaining` m LEFT JOIN `banktxns_bankbalancespermonth` b ON m.AccountID = b.AccountID WHERE ((DateofBal)='.$datelastmonth.' AND m.AccountID=\''.$acctid.'\')
  UNION ALL
  SELECT b.TxnNo, b.TxnDate, ifnull(`WithdrawAmt`,0) AS Withdraw, ifnull(`DepositAmt`,0) AS Deposit, b.Balance, b.Cleared, concat(ifnull(`Particulars`,\' \'),\' \',ifnull(`BankBranch`,\' \'),\' \',ifnull(`CheckNo`,\' \'),\' \',ifnull(`BankTransCode`,\'\'),\' \',ifnull(b.`Remarks`,\'\')) AS Details, e.Nickname AS ClearedBy, ClearedTS FROM `banktxns_1maintaining` m INNER JOIN `banktxns_banktxns` b ON m.AccountID = b.AccountID 
LEFT JOIN  `1employees` e ON e.IDNo=b.ClearedByNo
WHERE (((b.TxnDate)>'.$datelastmonth.' And (b.TxnDate)<=\''.$sqldate.'\') AND ((Month(`TxnDate`))=Month(\''.$sqldate.'\'))  AND m.AccountID=\''.$acctid.'\') ORDER BY  `TxnNo`';
// if($_SESSION['(ak0)']==1002){ echo $sql0; exit();  }
  $stmt=$link->prepare($sql0);
  $stmt->execute();

  $lefttabletitle='Bank Transactions';
  $sqlleft='Select TxnNo,TxnDate,format(Withdraw,2) as Withdraw,format(Deposit,2) as Deposit,format(Balance,2) as Balance,Cleared,Details,ClearedBy,ClearedTS from 0banktxns ORDER BY `TxnNo`';
  $columnnamesleft=array('TxnDate','Withdraw','Deposit','Balance','Cleared','Details','ClearedBy','ClearedTS');
  

$sql0='create temporary table datatxns AS
SELECT DateofBal as Date, "Beginning" as ControlNo, "" as Particulars, BalPerMonth as Debit, 0 as Credit, '.$acctid.' as DebitAccountID, "Balance" as Remarks, "" AS ClearedByNo, "" AS ClearedTS FROM `banktxns_bankbalancespermonth` WHERE (Month(DateofBal)='.($month==1?12:($month-1)).' AND AccountID='.$acctid.')
union all
SELECT `jvs`.Date, concat("JVNo ",`jvm`.`JVNo`) AS `ControlNo`, `jvs`.Particulars AS Particulars, Sum(`jvs`.Amount) AS DEBIT, 0 AS CREDIT, `jvs`.DebitAccountID, `jvm`.`Remarks`, `jvm`.EncodedByNo AS ClearedByNo, `jvm`.`TimeStamp` AS ClearedTS
FROM `acctg_2jvmain` `jvm` INNER JOIN `acctg_2jvsub` `jvs` ON `jvm`.`JVNo` = `jvs`.`JVNo`
WHERE (((`jvs`.DebitAccountID)='.$acctid.') AND ((`jvs`.CreditAccountID)<>500) and Month(`Date`)='.$month.')
GROUP BY `jvm`.`JVNo`


UNION ALL

SELECT `jvs`.Date, concat("JVNo ",`jvm`.`JVNo`) AS ControlNo, `jvs`.Particulars, 0 AS Expr2, Sum(`jvs`.Amount) AS SumOfAmount, `jvs`.`CreditAccountID`, `jvm`.`Remarks`, `jvm`.EncodedByNo AS ClearedByNo, `jvm`.`TimeStamp` AS ClearedTS
FROM `acctg_2jvmain` `jvm` INNER JOIN `acctg_2jvsub` `jvs` ON `jvm`.`JVNo` = `jvs`.`JVNo`
WHERE (((`jvs`.DebitAccountID)<>500) AND ((`jvs`.`CreditAccountID`)='.$acctid.') and Month(`Date`)='.$month.')
GROUP BY `jvm`.`JVNo`

UNION ALL

SELECT `dm`.`Cleared`, concat("Dep ",`dm`.DepositNo) AS ControlNo, "" AS Expr2, Sum(`ds`.Amount) AS SumOfAmount, 0 AS Expr3, `dm`.DebitAccountID, CONCAT(`dm`.`Remarks`," ",IF(Type=0,"Cash","Check")) AS `Remarks`, ClearedByNo, ClearedTS
FROM `acctg_2depositmain` dm INNER JOIN (`acctg_2depositsub` ds ) ON `dm`.TxnID = `ds`.TxnID
WHERE (((`dm`.DebitAccountID)='.$acctid.') and Month(`Cleared`)='.$month.')
GROUP BY `dm`.TxnID

UNION ALL

SELECT `dm`.`Cleared`, concat("Dep ",`dm`.DepositNo) AS ControlNo, concat(`EncashDetails`," app:",IfNull(`ApprovalNo`,"")) AS Expr2, Sum(`de`.Amount) AS SumOfAmount, 0 AS Expr3, `de`.DebitAccountID, CONCAT(`dm`.`Remarks`," ","Cash") AS `Remarks`, ClearedByNo, ClearedTS
FROM `acctg_2depositmain` dm INNER JOIN `acctg_2depencashsub` de ON `dm`.TxnID = `de`.TxnID
WHERE (((`de`.DebitAccountID)='.$acctid.') and Month(`Cleared`)='.$month.')
GROUP BY `dm`.`TxnID`

UNION ALL

SELECT `dm`.`Cleared`, concat("Dep ",`dm`.DepositNo) AS ControlNo, concat(`ClientName`," ",`DepDetails`) AS Expr2, 0 AS Expr3, Sum(`ds`.Amount) AS SumOfAmount, `ds`.CreditAccountID, CONCAT(`dm`.`Remarks`," ",IF(Type=0,"Cash","Check")) AS `Remarks`, ClearedByNo, ClearedTS
FROM `acctg_2depositmain` dm INNER JOIN (`acctg_2depositsub` ds INNER JOIN `acctg_0uniclientsalesperson` ON (`ds`.ClientNo = `acctg_0uniclientsalesperson`.ClientNo) AND (`ds`.BranchNo = `acctg_0uniclientsalesperson`.BranchNo)) ON `dm`.TxnID = `ds`.TxnID
WHERE (((`ds`.CreditAccountID)='.$acctid.') and Month(`Cleared`)='.$month.')
GROUP BY `dm`.`TxnID`


UNION ALL

SELECT `dm`.`Cleared`, concat("Dep ",`dm`.DepositNo) AS ControlNo, concat(`EncashDetails`," app:",IfNull(`ApprovalNo`,"")) AS Expr2, Sum(`Amount`*-1) AS Expr4, 0 AS Expr3, `dm`.DebitAccountID AS CreditAcctID, CONCAT(`dm`.`Remarks`," ","Cash") AS `Remarks`, ClearedByNo, ClearedTS
FROM `acctg_2depositmain` dm INNER JOIN `acctg_2depencashsub` de ON `dm`.TxnID = `de`.TxnID
WHERE (((`dm`.DebitAccountID)='.$acctid.') and Month(`Cleared`)='.$month.')
GROUP BY `dm`.`TxnID`


UNION ALL

SELECT `vm`.Cleared, concat("CVNo ",`vm`.CVNo) AS ControlNo, `vm`.Payee, 0 AS Expr2, Sum(`vs`.Amount) AS SumOfAmount, `vm`.CreditAccountID, `vm`.`Remarks`, ClearedByNo, ClearedTS
FROM `acctg_2cvmain` vm INNER JOIN `acctg_2cvsub` vs ON `vm`.CVNo = `vs`.CVNo
WHERE (((`vm`.Cleared) Is Not Null) AND ((`vm`.CreditAccountID)='.$acctid.') and Month(`Cleared`)='.$month.')
GROUP BY `vm`.CVNo

UNION ALL

SELECT `vm`.Cleared, concat("CVNo ",`vm`.CVNo) AS ControlNo, `vm`.Payee, Sum(`vs`.Amount) AS SumOfAmount,  0 AS Expr2, `vs`.DebitAccountID, `vm`.`Remarks`, ClearedByNo, ClearedTS
FROM `acctg_2cvmain` vm INNER JOIN `acctg_2cvsub` vs ON `vm`.CVNo = `vs`.CVNo
WHERE (((`vm`.Cleared) Is Not Null) AND ((`vs`.DebitAccountID)='.$acctid.') and Month(`Cleared`)='.$month.')
GROUP BY `vm`.CVNo


UNION ALL
 SELECT clp.Cleared, concat("CVNo ",clp.CVNo) AS ControlNo, clp.Payee, 0 AS Expr2, Sum(clp.AmountofCheck) AS SumOfAmountofCheck, clp.FromAccount, "Last Year", ClearedByNo, ClearedTS
FROM `acctg_3unclearedchecksfromlastperiod` clp
WHERE (((`cleared`) is not null) AND ((clp.FromAccount)='.$acctid.') and Month(`Cleared`)='.$month.')
GROUP BY clp.UnclearedCheckId

UNION ALL
 SELECT DateBounced, CONCAT("Bounced#CR",CRNo,"_",PDCNo) AS ControlNo, ClientName, 0 AS Expr2, Sum(clp.AmountofPDC), CreditAccountID, "Bounced Collection from Last Year", clpb.EncodedByNo, clpb.TimeStamp
FROM `acctg_3undepositedpdcfromlastperiod` clp JOIN `acctg_3undepositedpdcfromlastperiodbounced` clpb ON clp.UndepPDCId=clpb.UndepPDCId
JOIN `1clients` c ON `clp`.ClientNo = `c`.ClientNo
WHERE (((CreditAccountID)='.$acctid.') and Month(`DateBounced`)='.$month.')
GROUP BY clp.UndepPDCId

UNION ALL

SELECT DateBounced, concat("Bounced ",`bm`.`CheckNo`) AS ControlNo, `1clients`.ClientName, Sum(`bs`.Amount) AS SumOfAmount, 0 AS Expr3, 200 AS DebitAccountID, `bsb`.`Remarks`, `bsb`.EncodedByNo AS ClearedByNo, `bsb`.`TimeStamp` AS ClearedTS
FROM (`acctg_2collectmain` bm INNER JOIN `1clients` ON `bm`.ClientNo = `1clients`.ClientNo) INNER JOIN `acctg_2collectsub` bs ON `bm`.TxnID = `bs`.TxnID
INNER JOIN `acctg_2collectsubbounced` bsb ON `bm`.TxnID = `bsb`.TxnID
WHERE ((200='.$acctid.') and Month(`DateBounced`)='.$month.')
GROUP BY `bm`.TxnID

UNION ALL SELECT DateBounced, concat("Bounced ",`bm`.`CheckNo`) AS ControlNo, `1clients`.ClientName, 0 AS Expr3, Sum(`bs`.Amount) AS SumOfAmount, `bsb`.CreditAccountID, `bsb`.`Remarks`, `bsb`.EncodedByNo AS ClearedByNo, `bsb`.`TimeStamp` AS ClearedTS
FROM (`acctg_2collectmain` bm INNER JOIN `1clients` ON `bm`.ClientNo = `1clients`.ClientNo) INNER JOIN `acctg_2collectsub` bs ON `bm`.TxnID = `bs`.TxnID
INNER JOIN `acctg_2collectsubbounced` bsb ON `bm`.TxnID = `bsb`.TxnID
WHERE (((`bsb`.CreditAccountID)='.$acctid.') and Month(`DateBounced`)='.$month.')
GROUP BY `bm`.TxnID
ORDER BY `DATE`';
//WHERE (((`bsb`.CreditAccountID)='.$acctid.') and Month(`Date`)='.$month.')
//if($_SESSION['(ak0)']==1002){ echo $sql0; }
$stmt=$link->prepare($sql0); $stmt->execute();
$righttabletitle='Cleared in our Data';
$sqlright='Select Date, ControlNo, FORMAT(SUM(DEBIT),2) AS DEBIT, FORMAT(SUM(CREDIT),2) AS CREDIT, (ifnull(SUM(Debit),0)-ifnull(SUM(Credit),0)) as Balance, DebitAccountID, ifnull(Remarks,"") as Remarks, e.Nickname AS ClearedBy, ClearedTS from datatxns d'
        . ' LEFT JOIN  `1employees` e ON e.IDNo=d.ClearedByNo'
        . ' GROUP BY ControlNo ORDER BY `Date`';
$columnnamesright=array('Date', 'ControlNo', 'Remarks', 'DEBIT', 'CREDIT','ClearedBy','ClearedTS');
$coltototal='Balance'; $runtotalrightcol='Balance';  
  
include '../backendphp/layout/twotablessidebyside.php';
    
noform:		
      $link=null; $stmt=null;
    ?>