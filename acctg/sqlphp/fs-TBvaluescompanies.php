<?php
$allperco=1; include('../backendphp/functions/getcompanylist.php');

if ($showcurrent==0){
    
   $sql0='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_tbvalues` as Select b.CompanyNo,c.Company,ca.AccountID, truncate(sum(Bal),2) as `Balance`, ca.GroupID, ca.NormBal, IF(ca.GroupID<>0,ca.GroupID,ca.AccountID) AS ForTotal from `'.$currentyr.'_static`.`acctg_fsvalues` fs join acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID join `acctg_1accounttype` at on ca.AccountType=at.AccountType JOIN `1branches` b on b.BranchNo=fs.BranchNo JOIN `'. $currentyr .'_1rtc`.`1companies` c ON c.CompanyNo=b.CompanyNo WHERE FSMonth<='.$reportmonth.' group by ca.AccountID, b.CompanyNo ;';
} else {
    
   $whichdata='withcurrent'; $month=$reportmonth;require ('maketables/makefixedacctgdata.php');
  $sql0='CREATE TEMPORARY TABLE `'.$currentyr.'_static`.`acctg_tbvalues` as 
Select b.BranchNo,b.Branch,ca.AccountID,truncate(sum(Amount),2) as `Balance`, ca.GroupID, ca.NormBal from `acctg_0unialltxns` uni join acctg_1chartofaccounts ca on ca.AccountID=uni.AccountID join `acctg_1accounttype` at on ca.AccountType=at.AccountType  JOIN `1branches` b on b.BranchNo=uni.BranchNo WHERE b.CompanyNo='.$co.' and Month(Date)<='.$reportmonth.' group by ca.AccountID, b.BranchNo having Balance<>0';
}
// echo $sql0.'<br>'.$reportmonth.'<br>'.$closedmonth; break;
$stmt=$link->prepare($sql0);
$stmt->execute();

$sqlbranches=''; 
$columnnames=array('AccountID','AccountDescription');
foreach ($resultbranch as $company){
   $columnnames[]=$company['Company'].'-DR';$columnnames[]=$company['Company'].'-CR';
   $sqlbranches=$sqlbranches. 'format(sum(case when ca.NormBal=1 and CompanyNo='.$company['CompanyNo'].' then Balance end),2) as "'.$company['Company'].'-DR", format(sum(case when ca.NormBal<>1  and CompanyNo='.$company['CompanyNo'].' then Balance*-1 end),2) as "'.$company['Company'].'-CR",';
   }

$sql='Select '.($grouped==1?'IF(ca.GroupID<>0,ca.GroupID,ca.AccountID)':'ca.AccountID').' AS AccountID,'.$sqlbranches.' '.($grouped==1?'IF(ca.GroupID<>0,ag.AccountGroup,ca.AccountDescription)':'ca.AccountDescription').' AS AccountDescription from `'.$currentyr.'_static`.`acctg_tbvalues` f join `acctg_1chartofaccounts` ca on ca.AccountID=f.AccountID '
        . 'JOIN `acctg_1accountgroup` ag ON ag.GroupID=ca.GroupID group by '.($grouped==1?'ForTotal':'ca.AccountID').' order by AccountType, '.($grouped==1?'ag.OrderNo,ca.OrderNo':'ca.OrderNo');
//echo $sql;
$sqltotals='Select "" AS AccountID,'.$sqlbranches.' "TOTALS" AS AccountDescription from `'.$currentyr.'_static`.`acctg_tbvalues` f join `acctg_1chartofaccounts` ca on ca.AccountID=f.AccountID' ;
//echo $sqltotals; break;
?>