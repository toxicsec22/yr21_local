<?php
$isonly=' and ca.AccountType>11 ';
$allperco=1;
include('../backendphp/functions/getcompanylist.php');
$sql0is=''; $sql0bs='';  $sqlyr='0'; $sqlvalues=''; $branchnolist='-1';
$columnnames=array('AccountID','AccountDescription');

if ($showcurrent==0){
   foreach ($resultbranch as $company){
       $columnnames[]=$company['Company']; 
    $companyname=$company['Company']; $companyno=$company['CompanyNo']; 
    $sqlyr=$sqlyr.' + sum(IFNULL(`'.$companyno.'`,0))';
    $sql0is=$sql0is.', FORMAT(SUM(IFNULL(`'.$companyno.'`,0))*ca.NormBal*IF(ca.ContraAccountOf<>0,(-1),1),2) as `'.$companyname.'`';
    $sql0bs=$sql0bs.', FORMAT(SUM(IFNULL(`'.$companyno.'`,0))*ca.NormBal*IF(ca.ContraAccountOf<>0,(-1),1),2) as `'.$companyname.'`';
    $sqlvalues=$sqlvalues.', (SUM(IFNULL(`'.$companyno.'`,0))) as `'.$companyno.'`';
}

   $sql0is='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_isvalues` as SELECT '.($grouped==1?'IF(ca.GroupID<>0,ca.GroupID,ca.AccountID)':'ca.AccountID').' AS AccountID, FORMAT(('.$sqlyr.')*ca.NormBal*IF(ContraAccountOf<>0,(-1),1),2) as `Total`, ('.$sqlyr.') as `TotalValue` '.$sql0is.$sqlvalues.',  ca.GroupID,  ca.NormBal, ContraAccountOf, IF(ca.GroupID<>0,ca.GroupID,ca.AccountID) AS ForTotal, ca.AccountType,'.($grouped==1?'IF(ca.GroupID<>0,ag.AccountGroup,ca.AccountDescription)':'ca.AccountDescription').' AS AccountDescription,'.($grouped==1?'IF(ca.GroupID<>0,ag.OrderNo,ca.OrderNo)':'ca.OrderNo').' AS OrderNo FROM `'.$currentyr.'_static`.`acctg_fsvaluescompanycol` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID JOIN `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `acctg_1accountgroup` ag ON ag.GroupID=ca.GroupID WHERE '.$sqlgroupby.' and (ca.AccountType<>0) '.$isonly.' GROUP BY ca.AccountID;';
   $sql0bs='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_bsvalues` as SELECT '.($grouped==1?'IF(ca.GroupID<>0,ca.GroupID,ca.AccountID)':'ca.AccountID').' AS AccountID, FORMAT(('.$sqlyr.')*ca.NormBal*IF(ContraAccountOf<>0,(-1),1),2) as `Total`, ('.$sqlyr.') as `TotalValue` '.$sql0bs.$sqlvalues.',  ca.GroupID,  ca.NormBal, ContraAccountOf, IF(ca.GroupID<>0,ca.GroupID,ca.AccountID) AS ForTotal, ca.AccountType,'.($grouped==1?'IF(ca.GroupID<>0,ag.AccountGroup,ca.AccountDescription)':'ca.AccountDescription').' AS AccountDescription,'.($grouped==1?'IF(ca.GroupID<>0,ag.OrderNo,ca.OrderNo)':'ca.OrderNo').' AS OrderNo FROM `'.$currentyr.'_static`.`acctg_fsvaluescompanycol` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID JOIN `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `acctg_1accountgroup` ag ON ag.GroupID=ca.GroupID where `FSMonth`<='.$reportmonth.' and (ca.AccountType<>0) GROUP BY ca.AccountID;';
/*echo $sql0is.'<br>'.'<br>';
 echo $sql0bs.'<br>'.$reportmonth.'<br>'.$closedmonth; break;*/

   $stmt=$link->prepare($sql0is);$stmt->execute();
   $stmt=$link->prepare($sql0bs);$stmt->execute();

   
} else {
    
   $whichdata='withcurrent'; $month=$reportmonth;require ('maketables/makefixedacctgdata.php');
   $updateis='fs.AccountID=fs.AccountID';
   foreach ($resultbranch as $company){
       
      $companyname=$company['Company']; $companyno=$company['CompanyNo']; $branchnolist=$branchnolist.','.$companyno;
    $columnnames[]=$company['Company'];
    $updateis=$updateis.', `'.$companyname.'`=(`'.$company['CompanyNo'].'`*ca.NormBal*IF(ca.ContraAccountOf<>0,(-1),1))';
    $sql0bs=$sql0bs.', 0 as `'.$companyname.'`';
       $sqlvalues=$sqlvalues.', (IFNULL(SUM(case when BranchNo in (SELECT BranchNo FROM `1branches` WHERE CompanyNo='.$company['CompanyNo'].') then `Amount` end),0)) as `'.$companyno.'`';
}
   
   $sql0is='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_isvalues` as SELECT '.($grouped==1?'IF(ca.GroupID<>0,ca.GroupID,ca.AccountID)':'ca.AccountID').' AS AccountID, FORMAT(IFNULL(SUM(`Amount`),0)*ca.NormBal*IF(ContraAccountOf<>0,(-1),1),2) as `Total`, (IFNULL(SUM(`Amount`),0)) as `TotalValue` '.$sql0bs.$sqlvalues.',  ca.GroupID,  ca.NormBal, ContraAccountOf, IF(ca.GroupID<>0,ca.GroupID,ca.AccountID) AS ForTotal, ca.AccountType,'.($grouped==1?'IF(ca.GroupID<>0,ag.AccountGroup,ca.AccountDescription)':'ca.AccountDescription').' AS AccountDescription,'.($grouped==1?'IF(ca.GroupID<>0,ag.OrderNo,ca.OrderNo)':'ca.OrderNo').' AS OrderNo FROM `acctg_0unialltxns` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID JOIN `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `acctg_1accountgroup` ag ON ag.GroupID=ca.GroupID where Month(Date)'.$sqlcurrgroupby.$isonly.' GROUP BY ca.AccountID;';
   $sql0bs='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_bsvalues` as SELECT '.($grouped==1?'IF(ca.GroupID<>0,ca.GroupID,ca.AccountID)':'ca.AccountID').' AS AccountID, FORMAT(IFNULL(SUM(`Amount`),0)*ca.NormBal*IF(ContraAccountOf<>0,(-1),1),2) as `Total`, (IFNULL(SUM(`Amount`),0)) as `TotalValue` '.$sql0bs.$sqlvalues.',  ca.GroupID,  ca.NormBal, ContraAccountOf, IF(ca.GroupID<>0,ca.GroupID,ca.AccountID) AS ForTotal, ca.AccountType,'.($grouped==1?'IF(ca.GroupID<>0,ag.AccountGroup,ca.AccountDescription)':'ca.AccountDescription').' AS AccountDescription,'.($grouped==1?'IF(ca.GroupID<>0,ag.OrderNo,ca.OrderNo)':'ca.OrderNo').' AS OrderNo FROM `acctg_0unialltxns` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID JOIN `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `acctg_1accountgroup` ag ON ag.GroupID=ca.GroupID where Month(Date)<='.$reportmonth.' and (ca.AccountType<>0) GROUP BY ca.AccountID;';
 //  echo $sql0is.'<br>'.$sql0bs; break;
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
      $sqlupdatebs='UPDATE `'.$currentyr.'_static`.`acctg_bsvalues` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID SET '.$updateis.' where fs.AccountID='.$acctid['AccountID']; $stmt=$link->prepare($sqlupdatebs);$stmt->execute();
      $stmt=$link->prepare($sqlupdateis);$stmt->execute();
   }
   
}
//ECHO $updateis; BREAK;



function getSqlPerAcctBranch($accttype,$addlcondition,$fs){
	global $currentyr;
   $sql='SELECT fs.*,AccountDescription FROM `'.$currentyr.'_static`.`acctg_'.$fs.'svalues` fs   ';
   $sql=$sql.' where AccountType in ('.$accttype.') '.$addlcondition.' GROUP BY AccountID order by AccountType, OrderNo';
return $sql;
}

function getSqlSumPerGroupBranch($accttype,$addlcondition, $fs,$branches,$normbal){
	global $currentyr;
   $sql='SELECT AccountID,AccountDescription, FORMAT(IFNULL(SUM(`TotalValue`),0)*'.$normbal.',2) as `Total`';
   foreach ($branches as $branch){
         $sql=$sql.', FORMAT(IFNULL(SUM(`'.$branch['CompanyNo'].'`),0)*'.$normbal.',2) as `'.$branch['Company'].'`';
      }
   $sql=$sql.' FROM `'.$currentyr.'_static`.`acctg_'.$fs.'svalues` fs where AccountType in ('.$accttype.')  '.$addlcondition.' GROUP BY AccountID ORDER BY OrderNo';
//echo $sql; break;
return $sql;
}

function getSqlSumPerTypeISBranch($accttype,$addlcondition, $totallabel,$branches,$normbal){
	global $currentyr;
   $sql='SELECT "" AS AccountID, "'.$totallabel.'" as AccountDescription, FORMAT(IFNULL(SUM(`TotalValue`),0)*'.$normbal.',2) as `Total`';
   foreach ($branches as $branch){
         $sql=$sql.', FORMAT(IFNULL(SUM(`'.$branch['CompanyNo'].'`),0)*'.$normbal.',2) as `'.$branch['Company'].'`';
      }
   $sql=$sql.' FROM `'.$currentyr.'_static`.`acctg_isvalues` fs where AccountType in ('.$accttype.')  '.$addlcondition;
//echo $sql; break;
return $sql;
}


function getSqlSumPerTypeBSBranch($accttype,$addlcondition, $totallabel,$branches,$normbal){
	global $currentyr;
   $sql='SELECT "" AS AccountID, "'.$totallabel.'" as AccountDescription, FORMAT(IFNULL(SUM(`TotalValue`),0)*'.$normbal.',2) as `Total`';
   foreach ($branches as $branch){
         $sql=$sql.', FORMAT(IFNULL(SUM(`'.$branch['CompanyNo'].'`),0)*'.$normbal.',2) as `'.$branch['Company'].'`';
      }
   $sql=$sql.' FROM `'.$currentyr.'_static`.`acctg_bsvalues` fs where AccountType in ('.$accttype.')  '.$addlcondition;
//echo $sql; break;
return $sql;
}


?>