<?php // used in fsreportcompanies NOT FINISHED

$sqlcompanies='SELECT Company, c.CompanyNo FROM `1companies` c JOIN `1branches` b ON c.CompanyNo=b.CompanyNo JOIN '.$currentyr.'_static.acctg_unialltxns u on b.BranchNo=u.BranchNo '.(in_array($_POST['groupby'],array(2,5))?' AND b.BranchNo<>999':'').' GROUP BY c.CompanyNo;';

    $stmtcompany=$link->query($sqlcompanies);
    $resultcount=$stmtcompany->rowCount();
    $resultbranch=$stmtcompany->fetchAll();
    $companylist=array(); $companynames=array();
foreach ($resultbranch as $company){
    $companylist[]=$company;
    $companynames[]=$company['Company'];
}
$lastrecord=end($companylist);
$keyoflast=$lastrecord['CompanyNo'];

?>