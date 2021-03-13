<?php
$whichdata=$showcurrent==0?'static':'withcurrent'; 
$sql0is=''; $sql0bs='';  $sqlyr='IFNULL(SUM(`00`),0)'; $sqlvalues=', IFNULL(SUM(`00`),0) as `00`'; 
$columnnames=array('AccountID','AccountType','AccountDescription','Beginning');

include '../backendphp/functions/monthsarray.php';
if ($showcurrent==0){ //static
foreach ($months as $fsmonth){
   if($fsmonth>$reportmonth){ goto skip;}
   $monthcol=str_pad($fsmonth,2,'0',STR_PAD_LEFT); $monthname=monthName($fsmonth);
   $sqlyr=$sqlyr.' + IFNULL(SUM(`'.$monthcol.'`),0)';
   $sql0is=$sql0is.', FORMAT(IFNULL(SUM(`'.$monthcol.'`),0),2) as `'.$monthname.'`';
   $sqlvalues=$sqlvalues.', (IFNULL(SUM(`'.$monthcol.'`),0)) as `'.$monthcol.'`'; 
   $columnnames[]=$monthname;
   skip:
} 
    $book='fsvaluesmonthcol';
   $sql0is='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_wsvalues` as SELECT b.BranchNo,b.Branch,ca.AccountID, (IFNULL(SUM(`00`),0)) as `BegValue`, FORMAT(IFNULL(SUM(`00`),0),2) as `Beginning`, FORMAT(('.$sqlyr.'),2) as `Year`, ('.$sqlyr.') as `YearValue` '.$sql0is.$sqlvalues.',  ca.NormBal FROM `'.$currentyr.'_static`.`acctg_'.$book.'` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID JOIN `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `1branches` b on b.BranchNo=fs.BranchNo '.$sqlgroupby;
   
} else { //current
    $book=''; $sqlvalues='';
    foreach ($months as $fsmonth){
      $monthcol=str_pad($fsmonth,2,'0',STR_PAD_LEFT); $monthname=monthName($fsmonth);
      $columnnames[]=$monthname;
      $sql0is=$sql0is.', FORMAT(IFNULL(SUM(case when Month(Date)='.$monthcol.' then `Amount` end),0),2) as `'.$monthname.'`';
      $sqlvalues=$sqlvalues.', (IFNULL(SUM(case when Month(Date)='.$monthcol.' then `Amount` end),0)) as `'.$monthcol.'`';
    }
   require ('maketables/makefixedacctgdata.php');
   $sql0is='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_wsvalues` as SELECT b.BranchNo,b.Branch,ca.AccountID, 0 as `BegValue`, 0 as `Beginning`, FORMAT(IFNULL(SUM(`Amount`),0),2) as `Year`, (IFNULL(SUM(`Amount`),0)) as `YearValue` '.$sql0is.$sqlvalues.',  ca.NormBal FROM `acctg_0unialltxns` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID JOIN `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `1branches` b on b.BranchNo=fs.BranchNo '.$book.$sqlgroupby;
      
} //echo $sql0is;break;
$stmt=$link->prepare($sql0is);$stmt->execute();

function getSqlPerAcct($accttype,$addlcondition){
	global $currentyr;
   $sql='SELECT fs.*,AccountDescription, at.AcctTypeDescription AS AccountType FROM `'.$currentyr.'_static`.`acctg_wsvalues` fs JOIN `acctg_1chartofaccounts` ca on ca.AccountID=fs.AccountID JOIN `acctg_1accounttype` at ON at.AccountType=ca.AccountType ';
   $sql=$sql.' where ca.AccountType in ('.$accttype.') '.$addlcondition.' GROUP BY ca.AccountID order by ca.AccountType, OrderNo';
   //echo $sql; break;
return $sql;
}


function getSqlSumPerTypeWS($accttype,$addlcondition, $totallabel,$fsmonths,$reportmonth){
	global $currentyr;
   $sql='SELECT "" AS AccountID, "'.$totallabel.'" as AccountDescription, "" AS AccountType, FORMAT(IFNULL(SUM(`BegValue`),0),2) as `Beginning`, FORMAT(IFNULL(SUM(`YearValue`),0),2) as `Year`';
   foreach ($fsmonths as $fsmonth){
         if($fsmonth>$reportmonth){ goto skip;}
         $sql=$sql.', FORMAT(IFNULL(SUM(`'.str_pad($fsmonth,2,'0',STR_PAD_LEFT).'`),0),2) as `'.monthName($fsmonth).'`';
         skip:
      }
   $sql=$sql.' FROM `'.$currentyr.'_static`.`acctg_wsvalues` fs JOIN `acctg_1chartofaccounts` ca on ca.AccountID=fs.AccountID where AccountType in ('.$accttype.')  '.$addlcondition;
//echo $sql; break;
return $sql;
}

?>