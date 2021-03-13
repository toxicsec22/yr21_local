<?php

$sql0is=''; $sqlyr=''; $sqlvalues=''; 
$columnnames=array('AccountDescription');
$link1=connect_db("".$currentyr."_1rtc",1);
$sql0='DROP TABLE IF EXISTS `'.$currentyr.'_static`.`acctg_deptvalues`;'; $stmt=$link1->prepare($sql0);$stmt->execute();

   $whichdata='withcurrent'; $month=$reportmonth;
   include '../backendphp/functions/monthsarray.php';
   $sqlvalues=''; 
  
   foreach ($months as $fsmonth){
      $monthcol=str_pad($fsmonth,2,'0',STR_PAD_LEFT); $monthname=monthName($fsmonth);
      $columnnames[]=$monthname;
    $sql0is=$sql0is.', FORMAT(IFNULL(SUM(case when Month(Date)='.$monthcol.' then `Amount` end)*ca.NormBal,0)*IF(ContraAccountOf<>0,(-1),1),0) as `'.$monthname.'`';
   $sqlvalues=$sqlvalues.', (IFNULL(SUM(case when Month(Date)='.$monthcol.' then `Amount` end),0)) as `'.$monthcol.'`';
   }
   $sql0is='CREATE TABLE `'.$currentyr.'_static`.`acctg_deptvalues` as SELECT d.department AS Department, ca.DeptID, ca.AccountID, FORMAT(IFNULL(SUM(`Amount`),0)*ca.NormBal*IF(ContraAccountOf<>0,(-1),1),0) as `Year`, (IFNULL(SUM(`Amount`),0)) as `YearValue` '.$sql0is.$sqlvalues.',  ca.NormBal FROM `acctg_0unialltxns` fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID JOIN  `1departments` d ON d.deptid=ca.DeptID where ca.AccountType BETWEEN 200 AND 249 '.$deptcondition.' GROUP BY fs.AccountID, ca.DeptID;';
      
//echo $sql0is.'<br>'.$reportmonth.'<br>'.$closedmonth; break;
// print_r ($months); break;
   $stmt=$link1->prepare($sql0is);$stmt->execute();
   

function getSqlPerAcct($dept,$addlcondition,$fsmonths,$normbal,$reportmonth){
	global $currentyr;
   $sqldetail='SELECT AccountDescription,fs.* FROM `'.$currentyr.'_static`.`acctg_deptvalues` fs JOIN `acctg_1chartofaccounts` ca on ca.AccountID=fs.AccountID   where fs.DeptID in ('.$dept.') '.$addlcondition.' GROUP BY ca.AccountID  ';
   $sqltotal='SELECT CONCAT("<b>",Department, " Total</b>") as AccountDescription,"" AS Department, "" AS DeptID,"" as AccountID, CONCAT("<b>",FORMAT(IFNULL(SUM(`YearValue`),0)*'.$normbal.'*IF(ContraAccountOf<>0,(-1),1),0),"</b>") as `Year`,TRUNCATE(IFNULL(SUM(`YearValue`),0)*'.$normbal.'*IF(ContraAccountOf<>0,(-1),1),0) as `YearValue`';
   
   foreach ($fsmonths as $fsmonth){
         if($fsmonth>$reportmonth){ goto skip;}
         $sqltotal.=', CONCAT("<b>",FORMAT(IFNULL(SUM(`'.str_pad($fsmonth,2,'0',STR_PAD_LEFT).'`),0)*'.$normbal.',0),"</b>") as `'.monthName($fsmonth).'`';
         skip:
      }
      
    foreach ($fsmonths as $fsmonth){
         if($fsmonth>$reportmonth){ goto skip2;}
         $sqltotal.=', TRUNCATE(IFNULL(SUM(`'.str_pad($fsmonth,2,'0',STR_PAD_LEFT).'`),0)*'.$normbal.',0) as `'.($fsmonth).'`';
         skip2:
      }  
      
   $sqltotal.=', 1 AS NormBal FROM `'.$currentyr.'_static`.`acctg_deptvalues` ds JOIN `acctg_1chartofaccounts` ca on ca.AccountID=ds.AccountID where ds.DeptID in ('.$dept.')  '.$addlcondition;
   $sql=$sqldetail.' UNION '.$sqltotal;
  // echo $sql; break;
return $sql;
}

?>