<?php
$sql0='drop temporary table if exists cibbalances';  $stmt0=$link->prepare($sql0);  $stmt0->execute();
$sql='Create temporary table cibbalances SELECT u.AccountID, u.BranchNo, Round(Sum(`Amount`),2) as TotalCIB, Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\') as AsOf, RCompanyUse, OwnedByCompany, CompanyNo FROM `'.$currentyr.'_static`.`acctg_unialltxns` u
JOIN `acctg_1chartofaccounts` ca on ca.AccountID=u.AccountID LEFT JOIN `banktxns_1maintaining` m ON m.AccountID=ca.AccountID 
JOIN `1branches` b ON b.BranchNo=u.BranchNo
'.$condition.' GROUP BY u.`AccountID`, u.`BranchNo`  HAVING TotalCIB<>0;'; //if ($_SESSION['(ak0)']==1002) { echo $sql; break;}
$stmt=$link->prepare($sql); $stmt->execute();
?>