<?php

$co=!isset($_POST['company'])?0:getNumber('Company',$_REQUEST['company']);
if (!isset($allperco) and $allperco<>1){
    $sqlbranches='SELECT BranchNo, Branch from `1branches` where Active=1 AND BranchNo>=0 and Company='.$co.' order by Branch';    } 
else { $sqlbranches='SELECT u.BranchNo, Branch FROM `1branches` b join '.$currentyr.'_static.acctg_unialltxns u on b.BranchNo=u.BranchNo where Active=1 AND b.BranchNo>=0 and b.CompanyNo='.$co.' group by u.BranchNo order by Branch;';
}

if (isset($allactive) and $allactive=1) { $sqlbranches='SELECT BranchNo, Branch from `1branches` where Active=1 AND BranchNo>=0 order by Branch';}

    $stmtbranch=$link->query($sqlbranches);
    $resultcount=$stmtbranch->rowCount();
    $resultbranch=$stmtbranch->fetchAll();
    $branchlist=array(); $branchnames=array();
foreach ($resultbranch as $branch){
    $branchlist[]=$branch;
    $branchnames[]=$branch['Branch'];
}
$lastrecord=end($branchlist);
$keyoflast=$lastrecord['BranchNo'];

?>