<?php

$sql0is=''; $sql0bs='';  $sqlyr='IFNULL(SUM(`00`),0)'; $sqlvalues=', IFNULL(SUM(`00`),0) as `00`'; $sqlvaluesbs=', IFNULL(SUM(`00`),0) as `00`'; 
$columnnames=array('AccountID','AccountDescription','Beginning');

if ($showcurrent==0){
   $whichdata='static'; include '../backendphp/functions/monthsarray.php';
foreach ($months as $fsmonth){
    $book='fsvaluesmonthcol';

   if($fsmonth>$reportmonth){ goto skip;}
   $monthcol=str_pad($fsmonth,2,'0',STR_PAD_LEFT); $monthname=monthName($fsmonth);
   $sqlyr=$sqlyr.' + IFNULL(SUM(`'.$monthcol.'`),0)';
   $sql0is=$sql0is.', FORMAT(IFNULL(SUM(`'.$monthcol.'`)*ca.NormBal,0)*IF(ContraAccountOf<>0,(-1),1),2) as `'.$monthname.'`';
   $sql0bs=$sql0bs.', FORMAT(IFNULL(SUM(`'.$monthcol.'asof`)*ca.NormBal,0)*IF(ContraAccountOf<>0,(-1),1),2) as `'.$monthname.'`';
   $sqlvalues=$sqlvalues.', (IFNULL(SUM(`'.$monthcol.'`),0)) as `'.$monthcol.'`';
   $sqlvaluesbs=$sqlvaluesbs.', (IFNULL(SUM(`'.$monthcol.'asof`),0)) as `'.$monthcol.'`';   
   $columnnames[]=$monthname;
   skip:
}   
   $sql0is='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_isvalues` as SELECT b.BranchNo,b.Branch,'.($grouped==1?'IF(ca.GroupID<>0,ca.GroupID,ca.AccountID)':'ca.AccountID').' AS AccountID, (IFNULL(SUM(`00`),0)) as `BegValue`, FORMAT(IFNULL(SUM(`00`),0)*ca.NormBal*IF(ContraAccountOf<>0,(-1),1),2) as `Beginning`, FORMAT(('.$sqlyr.')*ca.NormBal*IF(ContraAccountOf<>0,(-1),1),2) as `Year`, ('.$sqlyr.') as `YearValue` '.$sql0is.$sqlvalues.',  ca.NormBal, ContraAccountOf, ca.AccountType,'.($grouped==1?'IF(ca.GroupID<>0,ag.AccountGroup,ca.AccountDescription)':'ca.AccountDescription').' AS AccountDescription,'.($grouped==1?'IF(ca.GroupID<>0,ag.OrderNo,ca.OrderNo)':'ca.OrderNo').' AS OrderNo FROM `'.$currentyr.'_static`.`acctg_'.$book.'` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID JOIN `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `1branches` b on b.BranchNo=fs.BranchNo JOIN `acctg_1accountgroup` ag ON ag.GroupID=ca.GroupID where ca.AccountType>=100 '.$sqlgroupby;
   $sql0bs='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_bsvalues` as SELECT b.BranchNo,b.Branch,'.($grouped==1?'IF(ca.GroupID<>0,ca.GroupID,ca.AccountID)':'ca.AccountID').' AS AccountID, (IFNULL(SUM(`00`),0)) as `BegValue`, FORMAT(IFNULL(SUM(`00`),0)*ca.NormBal*IF(ContraAccountOf<>0,(-1),1),2) as `Beginning`, FORMAT(('.$sqlyr.')*ca.NormBal*IF(ContraAccountOf<>0,(-1),1),2) as `Year`, ('.$sqlyr.') as `YearValue` '.$sql0bs.$sqlvaluesbs.',  ca.NormBal, ContraAccountOf, ca.AccountType,'.($grouped==1?'IF(ca.GroupID<>0,ag.AccountGroup,ca.AccountDescription)':'ca.AccountDescription').' AS AccountDescription,'.($grouped==1?'IF(ca.GroupID<>0,ag.OrderNo,ca.OrderNo)':'ca.OrderNo').' AS OrderNo FROM `'.$currentyr.'_static`.`acctg_'.$book.'` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID JOIN `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `1branches` b on b.BranchNo=fs.BranchNo JOIN `acctg_1accountgroup` ag ON ag.GroupID=ca.GroupID where (ca.AccountType<>0) '.$sqlgroupby;
//   echo $sql0is.'<br>'.'<br>';
// echo $sql0bs.'<br>'.$reportmonth.'<br>'.$closedmonth; break;
  $stmt=$link->prepare($sql0is);$stmt->execute();
   $stmt=$link->prepare($sql0bs);$stmt->execute();
 } else {
    $book='';
   $whichdata='withcurrent'; $month=$reportmonth;
   include '../backendphp/functions/monthsarray.php';
   require ('maketables/makefixedacctgdata.php');
   $sqlvalues=''; $sqlvaluesbs=''; 
   $updateis='fs.AccountID=fs.AccountID';
   foreach ($months as $fsmonth){
      $monthcol=str_pad($fsmonth,2,'0',STR_PAD_LEFT); $monthname=monthName($fsmonth);
      $columnnames[]=$monthname;
    $updateis=$updateis.', `'.$monthname.'`=(`'.$monthcol.'`*ca.NormBal)*IF(ca.ContraAccountOf<>0,(-1),1)';
    $sql0bs=$sql0bs.', 0 as `'.$monthname.'`';
    $sqlvalues=$sqlvalues.', (IFNULL(SUM(case when Month(Date)='.$monthcol.' then `Amount` end),0)) as `'.$monthcol.'`';
    $sqlvaluesbs=$sqlvaluesbs.', (IFNULL(SUM(case when Month(Date)<='.$monthcol.' then `Amount` end),0)) as `'.$monthcol.'`';
    }

   $sql0is='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_isvalues` as SELECT '.($grouped==1?'IF(ca.GroupID<>0,ca.GroupID,ca.AccountID)':'ca.AccountID').' AS AccountID, 0 as `BegValue`, 0 as `Beginning`,FORMAT(IFNULL(SUM(`Amount`),0)*ca.NormBal*IF(ca.ContraAccountOf<>0,(-1),1),2) as `Year`, (IFNULL(SUM(`Amount`),0)) as `YearValue` '.$sql0bs.$sqlvalues.', ca.GroupID,  ca.NormBal, ca.ContraAccountOf, IF(ca.GroupID<>0,ca.GroupID,ca.AccountID) AS ForTotal, ca.AccountType,'.($grouped==1?'IF(ca.GroupID<>0,ag.AccountGroup,ca.AccountDescription)':'ca.AccountDescription').' AS AccountDescription,'.($grouped==1?'IF(ca.GroupID<>0,ag.OrderNo,ca.OrderNo)':'ca.OrderNo').' AS OrderNo FROM `'.$currentyr.'_static`.`acctg_0unialltxns` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID JOIN `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `1branches` b on b.BranchNo=fs.BranchNo JOIN `acctg_1accountgroup` ag ON ag.GroupID=ca.GroupID where ca.AccountType>=100 '.$book.$sqlgroupby;
   $sql0bs='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_bsvalues` as SELECT '.($grouped==1?'IF(ca.GroupID<>0,ca.GroupID,ca.AccountID)':'ca.AccountID').' AS AccountID, 0 as `BegValue`, 0 as `Beginning`, FORMAT(IFNULL(SUM(`Amount`),0)*ca.NormBal*IF(ca.ContraAccountOf<>0,(-1),1),2) as `Year`, (IFNULL(SUM(`Amount`),0)) as `YearValue` '.$sql0bs.$sqlvaluesbs.', ca.GroupID,  ca.NormBal, ca.ContraAccountOf, IF(ca.GroupID<>0,ca.GroupID,ca.AccountID) AS ForTotal, ca.AccountType,'.($grouped==1?'IF(ca.GroupID<>0,ag.AccountGroup,ca.AccountDescription)':'ca.AccountDescription').' AS AccountDescription,'.($grouped==1?'IF(ca.GroupID<>0,ag.OrderNo,ca.OrderNo)':'ca.OrderNo').' AS OrderNo FROM `'.$currentyr.'_static`.`acctg_0unialltxns` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID JOIN `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `1branches` b on b.BranchNo=fs.BranchNo JOIN `acctg_1accountgroup` ag ON ag.GroupID=ca.GroupID where (ca.AccountType<>0) '.$book.$sqlgroupby;
   $sql0begbs='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_begbsvalues` as SELECT '.($grouped==1?'IF(ca.GroupID<>0,ca.GroupID,ca.AccountID)':'ca.AccountID').' AS AccountID, SUM(fs.BegBalance) AS `BegValue`, (SUM(fs.BegBalance)*ca.NormBal*IF(ca.ContraAccountOf<>0,(-1),1)) AS `Beginning`, ca.GroupID,  ca.NormBal, IF(ca.GroupID<>0,ca.GroupID,ca.AccountID) AS ForTotal FROM `acctg_1begbal` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID JOIN `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `1branches` b on b.BranchNo=fs.BranchNo where (ca.AccountType<>0) '.$sqlgroupby;
//   echo $sql0is.'<br>'.'<br>';
//echo $sql0bs.'<br>'.$reportmonth.'<br>'.$closedmonth; break;
// print_r ($months); break;
   $stmt=$link->prepare($sql0is);$stmt->execute();
   $stmt=$link->prepare($sql0bs);$stmt->execute();
   $stmt=$link->prepare($sql0begbs);$stmt->execute();
   
   $sqlupdatebs='UPDATE `'.$currentyr.'_static`.`acctg_bsvalues` fs JOIN `'.$currentyr.'_static`.`acctg_begbsvalues` beg ON beg.AccountID=fs.AccountID SET fs.`BegValue`=beg.`BegValue`, fs.`Beginning`=beg.`Beginning`';
   $stmt=$link->prepare($sqlupdatebs);$stmt->execute();
   
   $sql1is='Select AccountID from `'.$currentyr.'_static`.`acctg_isvalues`'; $stmt=$link->query($sql1is);$resultacct=$stmt->fetchAll();
   foreach ($resultacct as $acctid){
      $sqlupdateis='UPDATE `'.$currentyr.'_static`.`acctg_isvalues` fs JOIN acctg_1chartofaccounts ca ON ca.AccountID=fs.AccountID  SET '.$updateis.' WHERE fs.AccountID='.$acctid['AccountID']; 
      //echo $sqlupdateis;break;
      $stmt=$link->prepare($sqlupdateis);$stmt->execute();
   }
   $sql1bs='Select AccountID from `'.$currentyr.'_static`.`acctg_bsvalues`'; $stmt=$link->query($sql1bs);$resultacct=$stmt->fetchAll();
   foreach ($resultacct as $acctid){
      $sqlupdatebs='UPDATE `'.$currentyr.'_static`.`acctg_bsvalues` fs JOIN acctg_1chartofaccounts ca ON ca.AccountID=fs.AccountID SET '.$updateis.' WHERE fs.AccountID='.$acctid['AccountID'];
      $stmt=$link->prepare($sqlupdatebs);$stmt->execute();
     
   }
   
}


function getSqlPerAcct($accttype,$addlcondition,$fs){
	global $currentyr;
   $sql='SELECT fs.* FROM `'.$currentyr.'_static`.`acctg_'.$fs.'svalues` fs  ';
   $sql=$sql.' where fs.AccountType in ('.$accttype.') '.$addlcondition.' GROUP BY AccountID order by fs.AccountType, OrderNo';
 //  echo $sql; break;
return $sql;
}

function getSqlPerGroup($accttype,$addlcondition, $fs,$fsmonths,$normbal,$reportmonth){
	global $currentyr;
   $sql='SELECT AccountID, AccountDescription, FORMAT(IFNULL(SUM(`BegValue`),0)*'.$normbal.',2) as `Beginning`, FORMAT(IFNULL(SUM(`YearValue`),0)*'.$normbal.'*IF(ContraAccountOf<>0,(-1),1),2) as `Year`';
   
   foreach ($fsmonths as $fsmonth){
         if($fsmonth>$reportmonth){ goto skip;}
         $sql=$sql.', FORMAT(IFNULL(SUM(`'.str_pad($fsmonth,2,'0',STR_PAD_LEFT).'`),0)*'.$normbal.',2) as `'.monthName($fsmonth).'`';
         skip:
      }
   $sql=$sql.' FROM `'.$currentyr.'_static`.`acctg_'.$fs.'svalues` fs where fs.AccountType in ('.$accttype.')  '.$addlcondition.' GROUP BY AccountID ORDER BY OrderNo';
 //  echo $sql; break;
return $sql;
}
function getSqlSumPerTypeIS($accttype,$addlcondition, $totallabel,$fsmonths,$normbal,$reportmonth){
	global $currentyr;
   $sql='SELECT "" as AccountID,"'.$totallabel.'" as AccountDescription, FORMAT(IFNULL(SUM(`BegValue`),0)*'.$normbal.',2) as `Beginning`, FORMAT(IFNULL(SUM(`YearValue`),0)*'.$normbal.'*IF(ContraAccountOf<>0,(-1),1),2) as `Year`';
   //$sqltotalvalues=''; FORMAT(IFNULL(SUM(`00`),0),0) as `Beginning`, 
   foreach ($fsmonths as $fsmonth){
         if($fsmonth>$reportmonth){ goto skip;}
         $sql=$sql.', FORMAT(IFNULL(SUM(`'.str_pad($fsmonth,2,'0',STR_PAD_LEFT).'`),0)*'.$normbal.',2) as `'.monthName($fsmonth).'`';
         skip:
      }
   $sql=$sql.' FROM `'.$currentyr.'_static`.`acctg_isvalues` fs where fs.AccountType in ('.$accttype.')  '.$addlcondition;
//echo $sql; break;
return $sql;
}


function getSqlSumPerTypeBS($accttype,$addlcondition, $totallabel,$fsmonths,$normbal,$reportmonth){
	global $currentyr;
   $sql='SELECT "" as AccountID,"'.$totallabel.'" as AccountDescription, FORMAT(IFNULL(SUM(`BegValue`),0)*'.$normbal.',2) as `Beginning`, FORMAT(IFNULL(SUM(`YearValue`),0)*'.$normbal.',2) as `Year`';
   //$sqltotalvalues='; FORMAT(IFNULL(SUM(`00`),0)*'.$normbal.',0) as `Beginning`, 
   foreach ($fsmonths as $fsmonth){
         if($fsmonth>$reportmonth){ goto skip;}
         $sql=$sql.', FORMAT(IFNULL(SUM(`'.str_pad($fsmonth,2,'0',STR_PAD_LEFT).'`),0)*'.$normbal.',2) as `'.monthName($fsmonth).'`';
         skip:
      }
   $sql=$sql.' FROM `'.$currentyr.'_static`.`acctg_bsvalues` fs  where AccountType in ('.$accttype.')  '.$addlcondition;
// echo $sql; break;
return $sql;
}
?>