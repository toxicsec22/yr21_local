<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if ((!allowedToOpen(6455,'1rtc')) AND (!allowedToOpen(6457,'1rtc'))) { echo 'No permission'; exit; } 
 
$link1=connect_db("".$currentyr."_1rtc",1);

//check if weightedavecosts is updated
$sql0='Select sum(`'.str_pad($reportmonth,2,'0',STR_PAD_LEFT).'`) as SumAveCost from `' . $currentyr . '_static`.`invty_weightedavecost`';
$stmt0=$link1->query($sql0); $res0=$stmt0->fetch();

if ($res0['SumAveCost']==0){header("Location:/".$url_folder."/acctg/closedataautoadj.php?which=acctg_endinvvalues&done=3"); exit();}
// end check

// GOOD ITEMS
 $sql0='drop table if exists acctg_invtyendvalues'; $stmt=$link1->prepare($sql0); $stmt->execute();
    $sql0='CREATE  TABLE acctg_invtyendvalues as
    SELECT  `a`.`BranchNo` AS `BranchNo` , round(sum(case when  MONTH(`a`.`Date`)<='.$reportmonth.' then (`a`.`Qty` * `wac`.`'.str_pad($reportmonth,2,'0',STR_PAD_LEFT).'`) end),0) as `InvtyEndInv` FROM (`invty_20uniallposted` `a` JOIN `' . $currentyr . '_static`.`invty_weightedavecost` `wac` ON ((`a`.`ItemCode` = `wac`.`ItemCode`)))
    WHERE (`a`.`Date` IS NOT NULL) AND Defective=0 GROUP BY `a`.`BranchNo`;';
 
    $stmt0=$link1->prepare($sql0); $stmt0->execute();
echo 'endinvper branch done';

 $sql0='drop table if exists acctg_acctgendvalues'; $stmt=$link1->prepare($sql0); $stmt->execute();
$sql1='CREATE  TABLE  acctg_acctgendvalues as
SELECT `AccountID`, `BranchNo`, Sum(`Amount`) as CurMonth FROM `acctg_0unialltxns` where MONTH(`Date`)<='.$reportmonth.' and `AccountID` IN (300) group by BranchNo;';
// echo $sql1; break;
$stmt=$link1->prepare($sql1); $stmt->execute();
echo 'invty acct current done<br>';
$sql0='drop table if exists acctg_endvalues'; $stmt=$link1->prepare($sql0); $stmt->execute();
$sql1='create table acctg_endvalues (
BranchNo	smallint(6)	NOT NULL,
AcctgEndInv	double	NOT NULL,
InvtyEndInv	double	NOT NULL
)
SELECT a.BranchNo, round(CurMonth,0) as AcctgEndInv, round(InvtyEndInv,0) as InvtyEndInv from `1branches` b
join acctg_acctgendvalues a on b.BranchNo=a.BranchNo join acctg_invtyendvalues i on b.BranchNo=i.BranchNo;';
$stmt=$link1->prepare($sql1);
$stmt->execute();
//echo $sql0.'<br>'.$sql1; break;
echo 'acctg end values done<br>';

// DEFECTIVE ITEMS

$sql0='drop table if exists acctg_definvtyendvalues'; $stmt=$link1->prepare($sql0); $stmt->execute();
    $sql0='CREATE  TABLE acctg_definvtyendvalues as
    SELECT  `a`.`BranchNo` AS `BranchNo` , round(sum(case when  MONTH(`a`.`Date`)<='.$reportmonth.' then (`a`.`Qty` * `wac`.`'.str_pad($reportmonth,2,'0',STR_PAD_LEFT).'`) end),0) as `InvtyEndInv` FROM (`invty_20uniallposted` `a` JOIN `' . $currentyr . '_static`.`invty_weightedavecost` `wac` ON ((`a`.`ItemCode` = `wac`.`ItemCode`)))
    WHERE (`a`.`Date` IS NOT NULL) AND Defective<>0 GROUP BY `a`.`BranchNo`;';
 
    $stmt0=$link1->prepare($sql0); $stmt0->execute();

 $sql0='drop table if exists acctg_defacctgendvalues'; $stmt=$link1->prepare($sql0); $stmt->execute();
$sql1='CREATE  TABLE  acctg_defacctgendvalues as
SELECT `AccountID`, `BranchNo`, Sum(`Amount`) as CurMonth FROM `acctg_0unialltxns` where MONTH(`Date`)<='.$reportmonth.' and `AccountID`=331 group by BranchNo;';
// echo $sql1; break;
$stmt=$link1->prepare($sql1); $stmt->execute();
echo 'invty acct current done<br>';
$sql0='drop table if exists acctg_defendvalues'; $stmt=$link1->prepare($sql0); $stmt->execute();
$sql1='create table acctg_defendvalues (
BranchNo	smallint(6)	NOT NULL,
AcctgEndInv	double	NOT NULL,
InvtyEndInv	double	NOT NULL
)
SELECT a.BranchNo, round(CurMonth,0) as AcctgEndInv, round(InvtyEndInv,0) as InvtyEndInv from `1branches` b
join acctg_defacctgendvalues a on b.BranchNo=a.BranchNo join acctg_definvtyendvalues i on b.BranchNo=i.BranchNo;';
$stmt=$link1->prepare($sql1);
$stmt->execute();
//echo $sql0.'<br>'.$sql1; break;
echo 'acctg end values done<br>';
?>