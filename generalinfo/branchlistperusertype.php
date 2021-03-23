<?php
$group=$_SESSION['&pos'];
$userid=$_SESSION['(ak0)'];
$branchno=$_SESSION['@brn'];
switch ($group) {
    case in_array($group,array($execom,$controller,$hrmgr,$hrspecialist,$hrasst,$salesmgr,$grouphead,$scmgr, $procurement,$techtrainor,$adminhead,$acctgstaff,$operations, $operationshead)): 
        $branchlist='SELECT `1branches`.BranchNo, `1branches`.Branch from 1branches';
        break;
    /* case $grouphead : 
        $branchlist='SELECT `1branches`.BranchNo, `1branches`.Branch FROM 1branches INNER JOIN `attend_1branchgroups` ON `1branches`.BranchNo = `attend_1branchgroups`.BranchNo where GroupHead=$userid';
        break;*/
    case $branchhead :
    case $branchoic:
        $branchlist='SELECT `1branches`.BranchNo, `1branches`.Branch FROM 1branches where BranchNo=$branchno)';
        break;
    default:
       $branchlist='';
        
}
?>