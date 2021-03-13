<?php
include('../backendphp/functions/getbranchlist.php');

if ($showcurrent==0){
    
   $sql0='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_tbvalues` as Select b.BranchNo,b.Branch,ca.AccountID, truncate(sum(Bal),2) as `Balance`, ca.GroupID, ca.NormBal, IF(ca.GroupID<>0,ca.GroupID,ca.AccountID) AS ForTotal from `'.$currentyr.'_static`.`acctg_fsvalues` fs join acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID join `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `1branches` b on b.BranchNo=fs.BranchNo where FSMonth<='.$reportmonth.' and (ca.AccountType<>0) and b.CompanyNo='.$co.' GROUP BY ca.AccountID, b.BranchNo HAVING `Balance`<>0;';
} else {
    
   $whichdata='withcurrent'; $month=$reportmonth;require ('maketables/makefixedacctgdata.php');
  $sql0='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_tbvalues` as 
Select b.BranchNo,b.Branch, ca.AccountID,truncate(sum(Amount),2) as `Balance`, ca.GroupID, ca.NormBal from `acctg_0unialltxns` uni join acctg_1chartofaccounts ca on ca.AccountID=uni.AccountID join `acctg_1accounttype` at on ca.AccountType=at.AccountType  JOIN `1branches` b on b.BranchNo=uni.BranchNo WHERE b.CompanyNo='.$co.' and Month(Date)<='.$reportmonth.' GROUP BY ca.AccountID, b.BranchNo HAVING Balance<>0';
}
// echo $sql0.'<br>'.$reportmonth.'<br>'.$closedmonth; break;
$stmt=$link->prepare($sql0);
$stmt->execute();

$sqlbranches=''; 
$columnnames=array('AccountID','AccountDescription');
foreach ($resultbranch as $branch){
   $columnnames[]=$branch['Branch'].'-DR';$columnnames[]=$branch['Branch'].'-CR';
   $sqlbranches=$sqlbranches. 'format(sum(case when ca.NormBal=1 and BranchNo='.$branch['BranchNo'].' then Balance end)*IF(ContraAccountOf<>0,(-1),1),2) as "'.$branch['Branch'].'-DR", format(sum(case when ca.NormBal<>1  and BranchNo='.$branch['BranchNo'].' then Balance*-1 end)*IF(ContraAccountOf<>0,(-1),1),2) as "'.$branch['Branch'].'-CR",';
   }

$sql='Select '.($grouped==1?'IF(ca.GroupID<>0,ca.GroupID,ca.AccountID)':'ca.AccountID').' AS AccountID,'.$sqlbranches.' '.($grouped==1?'IF(ca.GroupID<>0,ag.AccountGroup,ca.AccountDescription)':'ca.AccountDescription').' AS AccountDescription from `'.$currentyr.'_static`.`acctg_tbvalues` f join `acctg_1chartofaccounts` ca on ca.AccountID=f.AccountID '
        . ' JOIN `acctg_1accountgroup` ag ON ag.GroupID=ca.GroupID GROUP BY '.($grouped==1?'ForTotal':'ca.AccountID').' ORDER BY AccountType, '.($grouped==1?'ag.OrderNo,ca.OrderNo':'ca.OrderNo');
//echo $sql;
$sqltotals='Select "" AS AccountID,'.$sqlbranches.' "TOTALS" AS AccountDescription from `'.$currentyr.'_static`.`acctg_tbvalues` f join `acctg_1chartofaccounts` ca on ca.AccountID=f.AccountID' ;
//echo $sqltotals; break;
?>