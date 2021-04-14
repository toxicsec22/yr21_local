<?php
//first set $monthfrom and $monthto; RESULT is list of months to be included: $$montharrayclosed and $montharray
$montharrayclosed='';$montharray='';
$resdate=$link->query("SELECT YEAR(DataClosedBy) AS DataClosedByYr,MONTH(DataClosedBy) AS DataClosedBy FROM `00dataclosedby` where ForDB=1"); $rowdate=$resdate->fetch();
$closedmonth=((($rowdate['DataClosedByYr'])<>$currentyr))?0:($rowdate['DataClosedBy']);

$activemonthfrom=$monthfrom<=$closedmonth?$monthfrom:$closedmonth;
$listmonthto=($monthto>=$closedmonth)?$closedmonth:$monthto;
while ($activemonthfrom<=$listmonthto) {
   $montharrayclosed=$montharrayclosed.$activemonthfrom.($activemonthfrom==$listmonthto?'':', ');
   $activemonthfrom++;
}

$activemonthto=$monthfrom<=$closedmonth?($closedmonth+1):$monthfrom;

while ($activemonthto<=$monthto) {
   $montharray=$montharray.$activemonthto.($activemonthto==$monthto?'':', ');
   $activemonthto++;
}
//echo 'active month '.$activemonthto.'  month from '.$monthfrom.'  closed month '.$closedmonth; break;
$sqlalltxns='SELECT `Date`,
    `ControlNo`, `BECS`,
    `SuppNo/ClientNo`,
    `Supplier/Customer/Branch`,
    `Particulars`,
    `AccountID`,
    `BranchNo`, 
	`FromBudgetOf`, 
    IFNULL(`Amount`,0) AS `Amount`, IFNULL(`Forex`,1) AS Forex,
    IFNULL(`Amount`*`Forex`,0) AS PHPAmount,
    `Entry`,
    `w`,
    `TxnID`
FROM `'.$currentyr.'_static`.`acctg_unialltxns` WHERE YEAR(Date)='.$currentyr.' AND MONTH(Date) in ('.$montharrayclosed.') and AccountID in '.$acctid;

if (((($rowdate['DataClosedByYr'])==$currentyr)) and $monthfrom==1){$sqllastmonth=''; goto skipJan;}

$sqllastmonth='SELECT 
    `ControlNo`, `BECS`,
    `SuppNo/ClientNo`,
    `AccountID`,
    `BranchNo`,
	`FromBudgetOf`,
    IFNULL(Sum(`Amount`),0) as SumofAmount, IFNULL(Forex,1) AS Forex, IFNULL(Sum(`Amount`*IFNULL(Forex,1)),0) as SumofPHPAmount,
    `Entry`
FROM `'.$currentyr.'_static`.`acctg_unialltxns` WHERE YEAR(Date)='.$currentyr.' AND (MONTH(Date)<='.($monthfrom<=$closedmonth?($monthfrom-1):$closedmonth).' OR `ControlNo` LIKE \'%BegBal\') and AccountID in '.$acctid.' GROUP BY AccountID, BranchNo';
//if($_SESSION['(ak0)']==1002) { echo $sqllastmonth.'<br><br>'; }
$sqllastmonthcompany='SELECT 
    `ControlNo`, `BECS`,
    `SuppNo/ClientNo`,
    `AccountID`,    
    Sum(`Amount`) as SumofAmount, IFNULL(Forex,1) AS Forex, IFNULL(Sum(`Amount`*(IFNULL(Forex,1))),0) as SumofPHPAmount, CompanyNo,
    `Entry`
FROM `'.$currentyr.'_static`.`acctg_unialltxns` WHERE YEAR(Date)='.$currentyr.' AND (MONTH(Date)<='.($monthfrom<=$closedmonth?($monthfrom-1):$closedmonth).' OR `ControlNo` LIKE \'%BegBal\') and AccountID in '.$acctid.' GROUP BY AccountID';
//}
skipJan:
// condition for beginning bal
if (($monthfrom>$closedmonth+1)){  include 'sqlalltxnsnotstaticbeg.php'; } //include 'sqlalltxnsnotstaticbegcompany.php';
// conditions for acct sched
if (($monthfrom<=$closedmonth) and ($monthto<=$closedmonth)){ goto nootherdata; }
if (($monthfrom<=$closedmonth) and ($monthto>$closedmonth)){ $sqlalltxns=$sqlalltxns.' UNION ALL '; include 'sqlalltxnsnotstaticsched.php';}
if (($monthfrom>$closedmonth)){ $sqlalltxns=''; include 'sqlalltxnsnotstaticsched.php';}
nootherdata:
?>