<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6239,'1rtc')) {   echo 'No permission'; exit;} 
$showbranches=false; include_once('../switchboard/contents.php');
// $create2ndtemptable=1;
include_once 'sqlphp/createbankbaltoday.php';

// replaced views with temp tables
include_once 'sqlphp/dataforunclearedsums.php';


$sql0='CREATE TEMPORARY TABLE banktxns_433qrydifffromactualbalanceallcolumns AS '
        . 'SELECT `bt`.`AccountID`, `bt`.`ShortAcctID`, TRUNCATE(((IFNULL(`bt`.`MonthBegBal`,0) + `bt`.`DRLessCR`) 
    + (SELECT IFNULL(SUM(clp.`AmountofCheck`),0) FROM `acctg_3unclearedchecksfromlastperiod` clp WHERE (ISNULL(clp.`Cleared`) AND (clp.`FromAccount` = `bt`.`AccountID`))))
    - (SELECT IFNULL(SUM(udlp.`AmountofPDC`),0) FROM `acctg_3undepositedpdcfromlastperiod` udlp JOIN `acctg_3undepositedpdcfromlastperiodbounced` udlpb ON udlp.UndepPDCId=udlpb.UndepPDCId WHERE (udlpb.`CreditAccountID` = `bt`.`AccountID`)),
            2) AS `DataClearedBal`,
        TRUNCATE(IFNULL(`ud`.`UnclearedSum`, 0), 2) AS `BankUnclearedDeposits`,
        TRUNCATE(IFNULL(`bb`.`BankBalToday`,0), 2) AS `BankBalToday`,
        TRUNCATE(((((IFNULL(`bb`.`BankBalToday`,0) - (IFNULL(`bt`.`MonthBegBal`,0))) - IFNULL(`bt`.`DRLessCR`, 0)) - IFNULL(`ud`.`UnclearedSum`, 0)) 
        - (SELECT  IFNULL(SUM(clp.`AmountofCheck`), 0)
                FROM `acctg_3unclearedchecksfromlastperiod` clp WHERE (ISNULL(clp.`Cleared`) AND (clp.`FromAccount` = `bt`.`AccountID`)))),
            2) AS `Diff`,
        (SELECT  SUM(`ds`.`Amount`) FROM (`acctg_2depositsub` `ds` JOIN `acctg_2depositmain` `dm` ON ((`dm`.`TxnID` = `ds`.`TxnID`)))
            WHERE (ISNULL(`dm`.`Cleared`) AND (`dm`.`DebitAccountID` = `bt`.`AccountID`)) GROUP BY `dm`.`DebitAccountID`) AS `DataUnclearedDeposits`
    FROM ((`banktxns_432cibbalancestodate` `bt` JOIN `banktxns_431qrybalancetoday` `bb` ON ((`bb`.`AccountID` = `bt`.`AccountID`)))
        LEFT JOIN `banktxns_431qrysumofuncleareddeposits` `ud` ON ((`bt`.`AccountID` = `ud`.`AccountID`)))
    
    GROUP BY `bt`.`AccountID`' ;       
$stmt=$link->prepare($sql0); $stmt->execute();
// end of create temp table

    // NOTE: Dollar forex rate for beginning balance is manually encoded (actual rate used ending in 2014) in `banktxns_431cibbegbalforbalances` since no place to hold data yet.
    $sql='SELECT `AccountID`, `ShortAcctID` as Bank, format(`DataClearedBal`,2) as `DataClearedBal`, format(`BankUnclearedDeposits`,2) as `BankUnclearedDeposits`, format(`BankBalToday`,2) as `BankBalToday`, format(`Diff`,2) as `Diff`, format(`DataUnclearedDeposits`,2) as `DataUnclearedDeposits`
FROM `banktxns_433qrydifffromactualbalanceallcolumns` ORDER BY ABS(`Diff`) DESC;';

$title='Bank Discrepancies & Uncleared Dep Totals';
    if (allowedToOpen(62391,'1rtc')){
	$columnnames=array('AccountID','Bank','BankBalToday','DataClearedBal','BankUnclearedDeposits','Diff','DataUnclearedDeposits');
	} else {
		$columnnames=array('AccountID','Bank','BankUnclearedDeposits','Diff','DataUnclearedDeposits');
	} // end else
        $width='50%';
	include('../backendphp/layout/displayastablenosort.php');

echo '<br><br>';
$subtitle='Date Cleared is BEFORE Date of Check';        
$sql='SELECT CheckNo,Payee,`Date` AS CVDate, DateofCheck,`Cleared`, FORMAT(Sum(vs.Amount),2) AS Amount FROM `acctg_2cvmain` vm 
JOIN `acctg_2cvsub` vs ON vm.CVNo = vs.CVNo WHERE `Cleared`<`DateofCheck` AND MONTH(`Cleared`)<>MONTH(`DateofCheck`) GROUP BY vm.CVNo;';
$columnnames=array('CheckNo', 'Payee','CVDate', 'DateofCheck','Cleared', 'Amount');
include('../backendphp/layout/displayastableonlynoheaders.php');
 $link=null; $stmt=null; 
    ?>