<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(557,'1rtc')) { echo 'No permission'; exit; } 
$showbranches=false; include_once('../switchboard/contents.php');
?>
<html>
<head>
<title>BS vs Unpaid Inv</title>
</head>
<body>
<h3>Data Errors</h3><br>
<?php
echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';
$showsubtitlealways=true; 
/* //$whichdata='withcurrent'; $reportmonth=(date('Y')<>substr($_SESSION['nb4A'],0,4)?12:date('m'));
//require("maketables/makefixedacctgdata.php");

$subtitle='Bal Sheet Vs Unpaid Invoices';
$link=connect_db("".$currentyr."_1rtc",1); 


$sql0='drop table if exists `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`'; $stmt=$link->prepare($sql0); $stmt->execute();
$sql0='drop table if exists `acctg_dailyclose_endapar'.$_SESSION['(ak0)'].'`'; $stmt=$link->prepare($sql0); $stmt->execute();

$sql0='CREATE TABLE `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'` AS
    SELECT 
        `uni`.`BranchNo` AS `BranchNo`,
        IF(((`uni`.`AccountID` >= 200)
                AND (`uni`.`AccountID` <= 202)),
            SUM(`uni`.`Amount`),
            (SUM(`uni`.`Amount`) * -(1))) AS `ARAPEnd`,
        `uni`.`AccountID` AS `AccountID`,
        "EndBal" AS `DataFrom`
    FROM
        `'.$currentyr.'_static.acctg_0unialltxns` `uni`
    WHERE
        (((`uni`.`AccountID` >= 200)
            AND (`uni`.`AccountID` <= 202))
            OR ((`uni`.`AccountID` >= 400)
            AND (`uni`.`AccountID` <= 403)))
    GROUP BY `uni`.`BranchNo` , `uni`.`AccountID` 
    UNION ALL SELECT 
        `acctg_23balperinv`.`BranchNo` AS `BranchNo`,
        SUM(`acctg_23balperinv`.`PayBalance`) AS `NetPayables`,
        `acctg_23balperinv`.`CreditAccountID` AS `CreditAccountID`,
        "InvBalances" AS `DataFrom`
    FROM
        `acctg_23balperinv`
    GROUP BY `acctg_23balperinv`.`BranchNo` , `acctg_23balperinv`.`CreditAccountID` 
    UNION ALL SELECT 
        `acctg_33qrybalperrecpt`.`BranchNo` AS `BranchNo`,
        SUM(`acctg_33qrybalperrecpt`.`InvBalance`) AS `NetReceivables`,
        `acctg_33qrybalperrecpt`.`DebitAccountID` AS `DebitAccountID`,
        "InvBalances" AS `DataFrom`
    FROM
        `acctg_33qrybalperrecpt`
    GROUP BY `acctg_33qrybalperrecpt`.`BranchNo` , `acctg_33qrybalperrecpt`.`DebitAccountID` 
	UNION ALL SELECT 
        `acctg_38undepositedclientpdcs`.`BranchNo` AS `BranchNo`,
        SUM(`acctg_38undepositedclientpdcs`.`PDC`) AS `NetPDC`,
        201 AS `201`,
        "InvBalances" AS `DataFrom`
    FROM
        `acctg_38undepositedclientpdcs`
    GROUP BY `acctg_38undepositedclientpdcs`.`BranchNo`
    ';

$sql1='CREATE TABLE `acctg_dailyclose_endapar'.$_SESSION['(ak0)'].'` AS
    SELECT 
        `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`BranchNo` AS `BranchNo`,
        `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`AccountID` AS `AccountID`,
        FORMAT(IFNULL(SUM((CASE
                        WHEN (`acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`DataFrom` = "EndBal") THEN `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`ARAPEnd`
                    END)),
                    0),
            2) AS `BSAmt`,
        FORMAT(IFNULL(SUM((CASE
                        WHEN (`acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`DataFrom` = "InvBalances") THEN `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`ARAPEnd`
                    END)),
                    0),
            2) AS `InvBalances`,
        FORMAT((IFNULL(SUM((CASE
                        WHEN (`acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`DataFrom` = "EndBal") THEN `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`ARAPEnd`
                    END)),
                    0) - IFNULL(SUM((CASE
                        WHEN (`acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`DataFrom` = "InvBalances") THEN `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`ARAPEnd`
                    END)),
                    0)),
            2) AS `Diff`
    FROM
        `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`
    GROUP BY `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`BranchNo` , `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`AccountID`';


$stmt=$link->prepare($sql0);$stmt->execute();
$stmt=$link->prepare($sql1);$stmt->execute();

$columnnames=array('Account','Branch','BSAmt', 'InvBalances','Diff');  
$sql='select dc.*, b.Branch, ca.ShortAcctID as Account from acctg_dailyclose_endapar'.$_SESSION['(ak0)'].' dc
join `1branches` b ON dc.BranchNo = b.BranchNo
join acctg_1chartofaccounts ca on ca.AccountID=dc.AccountID   where (Diff<-0.1 or Diff>0.1)   order by Account,Branch';

include('../backendphp/layout/displayastableonlynoheaders.php');

$sql0='drop table if exists `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`'; $stmt=$link->prepare($sql0); $stmt->execute();
$sql0='drop table if exists `acctg_dailyclose_endapar'.$_SESSION['(ak0)'].'`'; $stmt=$link->prepare($sql0); $stmt->execute();
    
 */
$subtitle='Bal Sheet Vs Unpaid Invoices';
$link=connect_db("".$currentyr."_1rtc",1); 
// $whichdata='withcurrent'; $reportmonth=((date('Y')<>substr($_SESSION['nb4A'],0,4))?12:date('m')); require ('maketables/makefixedacctgdata.php');


$sql0='drop table if exists `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`'; $stmt=$link->prepare($sql0); $stmt->execute();
$sql0='drop table if exists `acctg_dailyclose_endapar'.$_SESSION['(ak0)'].'`'; $stmt=$link->prepare($sql0); $stmt->execute();
require('sqlphp/sqldailyclose.php');
$stmt=$link->prepare($sql0);$stmt->execute();
$stmt=$link->prepare($sql1);$stmt->execute();

$columnnames=array('Account','Branch','BSAmt', 'InvBalances','Diff');  
$sql='select dc.*, b.Branch, ca.ShortAcctID as Account from acctg_dailyclose_endapar'.$_SESSION['(ak0)'].' dc
join `1branches` b ON dc.BranchNo = b.BranchNo
join acctg_1chartofaccounts ca on ca.AccountID=dc.AccountID   where (Diff<-0.1 or Diff>0.1)   order by Account,Branch'; //or (BSAmt<>0 and InvBalances<>0)
include('../backendphp/layout/displayastableonlynoheaders.php');

 // 
$sql0='drop table if exists `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`'; $stmt=$link->prepare($sql0); $stmt->execute();
$sql0='drop table if exists `acctg_dailyclose_endapar'.$_SESSION['(ak0)'].'`'; $stmt=$link->prepare($sql0); $stmt->execute();

endofreport:
      $stmt=null; $link=null;
?><BR>END OF REPORT
</body>
</html>