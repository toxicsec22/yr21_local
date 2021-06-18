<?php
$whichdata='static'; 
$sql0is=''; $sql0bs='';  $sqlyr=''; $sqlvalues=''; $sqlvaluesbs='';  $sqlbudget=''; $sqlbudgetcolumns='';
$columnnames=array('AccountID','AccountDescription');

if ($showcurrent==0){
   include '../backendphp/functions/monthsarray.php';
foreach ($months as $fsmonth){
   if($fsmonth>$reportmonth){ goto skip;}
   $monthcol=str_pad($fsmonth,2,'0',STR_PAD_LEFT); $monthname=monthName($fsmonth);
   $sqlyr=$sqlyr.' + IFNULL(sum(`'.$monthcol.'`),0)';
   $sql0is=$sql0is.', FORMAT(IFNULL(SUM(`'.$monthcol.'`)*ca.NormBal,0),0) as `'.$monthname.' Actual`';
   $sql0bs=$sql0bs.', FORMAT(IFNULL(SUM(`'.$monthcol.'asof`)*ca.NormBal,0),0) as `'.$monthname.' Actual`';
   $sqlvalues=$sqlvalues.', (IFNULL(SUM(`'.$monthcol.'`),0)) as `'.$monthcol.'`';
   $sqlvaluesbs=$sqlvaluesbs.', (IFNULL(SUM(`'.$monthcol.'asof`),0)) as `'.$monthcol.'`';
   $sqlbudget=$sqlbudget.', SUM(CASE WHEN `Month`='.$fsmonth.' THEN `Budget` END) AS `'.$monthname.' Budget`';
   $sqlbudgetcolumns=$sqlbudgetcolumns.', `'.$monthname.' Budget`';
   $columnnames[]=$monthname.' Budget'; $columnnames[]=$monthname.' Actual';
   skip:
}  

$sqlbudget='CREATE TEMPORARY TABLE `monthlybudget` AS SELECT `EntityID`, `AccountID`'.$sqlbudget.', SUM(`Budget`) AS `BudgetTotal`  FROM `budget_1budgets` GROUP BY `EntityID`, `AccountID`;';
// echo $sqlbudget; exit();
$stmt=$link->prepare($sqlbudget);$stmt->execute();

   $sql0is='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_isvalues` as SELECT b.EntityID,b.Entity,ca.AccountID, (IFNULL(SUM(`00`),0)) as `BegValue`, FORMAT(IFNULL(SUM(`00`),0)*ca.NormBal,0) as `Beginning`, FORMAT(('.$sqlyr.')*ca.NormBal,0) as `Year`, ('.$sqlyr.') as `YearValue` '.$sql0is.$sqlvalues.$sqlbudgetcolumns.',  ca.NormBal FROM `'.$currentyr.'_static`.`acctg_fsvaluesmonthcol` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID JOIN `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `acctg_1budgetentities` b ON b.EntityID=fs.BranchNo LEFT JOIN `monthlybudget` mb ON mb.AccountID=fs.AccountID AND mb.EntityID=fs.BranchNo WHERE ca.AccountType>11 '.$sqlgroupby;
   $sql0bs='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_bsvalues` as SELECT b.EntityID,b.Entity,ca.AccountID, (IFNULL(SUM(`00`),0)) as `BegValue`, FORMAT(IFNULL(SUM(`00`),0)*ca.NormBal,0) as `Beginning`, FORMAT(('.$sqlyr.')*ca.NormBal,0) as `Year`, ('.$sqlyr.') as `YearValue` '.$sql0bs.$sqlvaluesbs.$sqlbudgetcolumns.',  ca.NormBal FROM `'.$currentyr.'_static`.`acctg_fsvaluesmonthcol` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID JOIN `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `acctg_1budgetentities` b ON b.EntityID=fs.BranchNo JOIN `monthlybudget` mb ON mb.AccountID=fs.AccountID AND mb.EntityID=fs.BranchNo WHERE (ca.AccountType<>0) '.$sqlgroupby;
  // echo $sqlbudget.'<br>'.'<br>' .$sql0is.'<br>'.'<br>' .$sql0bs.'<br>'.$reportmonth.'<br>'.$closedmonth; break;
   $stmt=$link->prepare($sql0is);$stmt->execute();
   $stmt=$link->prepare($sql0bs);$stmt->execute();

} else {
   $whichdata='withcurrent'; $month=$reportmonth;
   include '../backendphp/functions/monthsarray.php';
   require ('maketables/makefixedacctgdata.php');
   $sqlvalues=''; $sqlvaluesbs=''; 
   $updateis='fs.AccountID=fs.AccountID';
   foreach ($months as $fsmonth){
      $monthcol=str_pad($fsmonth,2,'0',STR_PAD_LEFT); $monthname=monthName($fsmonth);
      $columnnames[]=$monthname;
    $updateis=$updateis.', `'.$monthname.'`=(`'.$monthcol.' Actual`*ca.NormBal)';
    $sql0bs=$sql0bs.', 0 as `'.$monthname.'`';
    $sqlvalues=$sqlvalues.', (IFNULL(SUM(case when Month(Date)='.$monthcol.' then `Amount` end),0)) as `'.$monthcol.'`';
    $sqlvaluesbs=$sqlvaluesbs.', (IFNULL(SUM(case when Month(Date)<='.$monthcol.' then `Amount` end),0)) as `'.$monthcol.'`';
    }

   $sql0is='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_isvalues` as SELECT ca.AccountID, 0 as `BegValue`, 0 as `Beginning`,FORMAT(IFNULL(SUM(`Amount`),0)*ca.NormBal,0) as `Year`, (IFNULL(SUM(`Amount`),0)) as `YearValue` '.$sql0bs.$sqlvalues.',  ca.NormBal FROM `'.$currentyr.'_static`.`acctg_0unialltxns` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID JOIN `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `acctg_1budgetentities` b ON b.EntityID=fs.BranchNo where ca.AccountType>11 '.$sqlgroupby;
   $sql0bs='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_bsvalues` as SELECT ca.AccountID, 0 as `BegValue`, 0 as `Beginning`, FORMAT(IFNULL(SUM(`Amount`),0)*ca.NormBal,0) as `Year`, (IFNULL(SUM(`Amount`),0)) as `YearValue` '.$sql0bs.$sqlvaluesbs.',  ca.NormBal FROM `'.$currentyr.'_static`.`acctg_0unialltxns` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID JOIN `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `acctg_1budgetentities` b ON b.EntityID=fs.BranchNo where (ca.AccountType<>0) '.$sqlgroupby;
//   echo $sql0is.'<br>'.'<br>';
//echo $sql0bs.'<br>'.$reportmonth.'<br>'.$closedmonth; break;
// print_r ($months); break;
   $stmt=$link->prepare($sql0is);$stmt->execute();
   $stmt=$link->prepare($sql0bs);$stmt->execute();

   $sql1is='Select AccountID from `'.$currentyr.'_static`.`acctg_isvalues`'; $stmt=$link->query($sql1is);$resultacct=$stmt->fetchAll();
   foreach ($resultacct as $acctid){
      $sqlupdateis='UPDATE `'.$currentyr.'_static`.`acctg_isvalues` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID  SET '.$updateis.' where fs.AccountID='.$acctid['AccountID']; 
      //echo $sqlupdateis;break;
      $stmt=$link->prepare($sqlupdateis);$stmt->execute();
   }
   $sql1bs='Select AccountID from `'.$currentyr.'_static`.`acctg_bsvalues`'; $stmt=$link->query($sql1bs);$resultacct=$stmt->fetchAll();
   foreach ($resultacct as $acctid){
      $sqlupdatebs='UPDATE `'.$currentyr.'_static`.`acctg_bsvalues` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID SET '.$updateis.' where fs.AccountID='.$acctid['AccountID'];      $stmt=$link->prepare($sqlupdatebs);$stmt->execute();
      $sqlupdatebs='UPDATE `'.$currentyr.'_static`.`acctg_bsvalues` fs JOIN `acctg_1begbal` beg on beg.AccountID=fs.AccountID SET `BegValue`=beg.BegBalance, `Beginning`=(beg.BegBalance*NormBal) where fs.AccountID='.$acctid['AccountID'];
      $stmt=$link->prepare($sqlupdatebs);$stmt->execute();
      $stmt=$link->prepare($sqlupdateis);$stmt->execute();
   }
   
}


function getSqlPerAcct($accttype,$addlcondition,$fs){
	global $currentyr;
   $sql='SELECT fs.*,AccountDescription FROM `'.$currentyr.'_static`.`acctg_'.$fs.'svalues` fs JOIN `acctg_1chartofaccounts` ca on ca.AccountID=fs.AccountID  ';
   $sql=$sql.' where AccountType in ('.$accttype.') '.$addlcondition.' GROUP BY ca.AccountID order by AccountType, OrderNo';
//   echo $sql; break;
return $sql;
}


function getSqlSumPerTypeIS($accttype,$addlcondition, $totallabel,$fsmonths,$normbal,$reportmonth){
	global $currentyr;
   $sql='SELECT "'.$totallabel.'" as AccountDescription, FORMAT(IFNULL(SUM(`YearValue`),0)*'.$normbal.',0) as `Year`';
   foreach ($fsmonths as $fsmonth){
         if($fsmonth>$reportmonth){ goto skip;}
         $sql=$sql.', FORMAT(IFNULL(SUM(`'.str_pad($fsmonth,2,'0',STR_PAD_LEFT).'`),0)*'.$normbal.',0) as `'.monthName($fsmonth).' Actual`'
                 . ', FORMAT(SUM(IFNULL(`'.monthName($fsmonth).' Budget`,0)),0) as `'.monthName($fsmonth).' Budget`';
         skip:
      }
   $sql=$sql.' FROM `'.$currentyr.'_static`.`acctg_isvalues` fs JOIN `acctg_1chartofaccounts` ca on ca.AccountID=fs.AccountID where AccountType in ('.$accttype.')  '.$addlcondition;
//echo $sql; break;
return $sql;
}

/*
function getSqlSumPerTypeBS($accttype,$addlcondition, $totallabel,$fsmonths,$normbal,$reportmonth){
	global $currentyr;
   $sql='SELECT "'.$totallabel.'" as AccountDescription, FORMAT(IFNULL(SUM(`YearValue`),0)*'.$normbal.',0) as `Year`';
   foreach ($fsmonths as $fsmonth){
         if($fsmonth>$reportmonth){ goto skip;}
         $sql=$sql.', FORMAT(IFNULL(SUM(`'.str_pad($fsmonth,2,'0',STR_PAD_LEFT).'`),0)*'.$normbal.',0) as `'.monthName($fsmonth).'`';
         skip:
      }
   $sql=$sql.' FROM `'.$currentyr.'_static`.`acctg_bsvalues` fs JOIN `acctg_1chartofaccounts` ca on ca.AccountID=fs.AccountID where AccountType in ('.$accttype.')  '.$addlcondition;
// echo $sql; break;
return $sql;
}*/
?>