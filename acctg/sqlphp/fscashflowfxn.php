<?php
function getSqlPerGroupCF($acctgroup,$addlcondition, $fs,$fsmonths,$addorless,$reportmonth){
	global $currentyr;
   $sql='SELECT ca.GroupID AS AccountID, IF("'.$addorless.'" LIKE "Add:%" OR "'.$addorless.'" LIKE "Less:%", CONCAT("'.$addorless.'",AccountGroup),"'.$addorless.'") AS AccountDescription, FORMAT(IFNULL(SUM(`BegValue`),0)*ca.NormBal*IF(ca.ContraAccountOf<>0,(-1),1),2) as `Beginning`, FORMAT(IFNULL(SUM(`YearValue`),0)*ca.NormBal*IF(ca.ContraAccountOf<>0,(-1),1),2) as `Year`';
   
   foreach ($fsmonths as $fsmonth){
         if($fsmonth>$reportmonth){ goto skip;}
         $sql=$sql.', FORMAT(IFNULL(SUM(`'.str_pad($fsmonth,2,'0',STR_PAD_LEFT).'`),0)*ca.NormBal*IF(ca.ContraAccountOf<>0,(-1),1),2) as `'.monthName($fsmonth).'`';
         skip:
      }
   $sql=$sql.' FROM `'.$currentyr.'_static`.`acctg_'.$fs.'svalues` fs JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=fs.AccountID JOIN `acctg_1accountgroup` ag ON ag.GroupID=ca.GroupID WHERE ca.GroupID in ('.$acctgroup.')  '.$addlcondition.' ';
 //  echo $sql; break;
return $sql;
}

function getSqlSumPerGroupCF($acctgroup,$addlcondition, $fs,$normbal,$fsmonths,$addorless,$reportmonth){
	global $currentyr;
   $sql='SELECT ca.GroupID AS AccountID, "'.$addorless.'" AS AccountDescription, FORMAT(IFNULL(SUM(`BegValue`),0)*'.$normbal.'*IF(ca.ContraAccountOf<>0,(-1),1),2) as `Beginning`, FORMAT(IFNULL(SUM(`YearValue`),0)*'.$normbal.'*IF(ca.ContraAccountOf<>0,(-1),1),2) as `Year`';
   
   foreach ($fsmonths as $fsmonth){
         if($fsmonth>$reportmonth){ goto skip;}
         $sql=$sql.', FORMAT(IFNULL(SUM(`'.str_pad($fsmonth,2,'0',STR_PAD_LEFT).'`),0)*'.$normbal.'*IF(ca.ContraAccountOf<>0,(-1),1),2) as `'.monthName($fsmonth).'`';
         skip:
      }
   $sql=$sql.' FROM `'.$currentyr.'_static`.`acctg_'.$fs.'svalues` fs JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=fs.AccountID WHERE ca.GroupID in ('.$acctgroup.')  '.$addlcondition.' ';
  
return $sql;
}

function getSqlBSChangeCF($condition,$effectoncash,$fsmonths,$grouplabel,$reportmonth){ // Beginning not needed to be displayed
global $currentyr;
   $sql='SELECT ca.GroupID AS AccountID, "'.$grouplabel.'" AS AccountDescription, 0 AS Beginning, FORMAT((IFNULL(SUM(`YearValue`),0)-IFNULL(SUM(`00`),0))*ca.NormBal*'.$effectoncash.'*IF(ca.ContraAccountOf<>0,(-1),1),2) as `Year`';
   
   foreach ($fsmonths as $fsmonth){
         if($fsmonth>$reportmonth){ goto skip;}
         $sql=$sql.', FORMAT(((IFNULL(SUM(`'.str_pad($fsmonth,2,'0',STR_PAD_LEFT).'`),0)-IFNULL(SUM(`'.str_pad(($fsmonth-1),2,'0',STR_PAD_LEFT).'`),0))*ca.NormBal*'.$effectoncash.'*IF(ca.ContraAccountOf<>0,(-1),1)),2) as `'.monthName($fsmonth).'`';
         skip:
      }
   $sql=$sql.' FROM `'.$currentyr.'_static`.`acctg_bsvalues` fs JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=fs.AccountID JOIN `acctg_1accountgroup` ag ON ag.GroupID=ca.GroupID WHERE '.$condition.' ';
  // echo $sql; 
return $sql;
}