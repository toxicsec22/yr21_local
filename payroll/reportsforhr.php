<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(67704,'1rtc')) { echo 'No permission'; exit();}
include_once $path.'/acrossyrs/dbinit/userinit.php';
$showbranches=false;
include_once('../switchboard/contents.php');
?>
	
    <?php
	
	$which=(!isset($_GET['w'])?'PerJobLevel':$_GET['w']);
		
	switch ($which){
		case 'PerJobLevel':
		$title='Salaries per Job Level';
			$sql1='SELECT JobLevelNo, CONCAT(JobClassification," Level ", RIGHT(JobLevelNo,1)) AS JobLevel FROM `attend_1joblevel` jl JOIN `attend_0jobclass` jc ON jc.JobClassNo=jl.JobClassNo '.(!allowedToOpen(8041,'1rtc')?' WHERE jc.JobClassNo NOT IN (7)':'').' ORDER BY jc.JobClassNo, jl.JobLevelNo ';
			$sql2='SELECT cp.JobLevelNo, FullName,Position,IF(cp.deptid IN (2,3,4,10),Branch,department) AS BranchOrDepartment,DateHired, FORMAT(TotalMonthly,2) AS MonthlyRate,LatestDateofChange from attend_30currentpositions cp join 1employees e on e.IDNo=cp.IDNo join payroll_21dailyandmonthly dm on dm.IDNo=cp.IDNo  ';
			$groupby='JobLevelNo';
			$orderby=' ORDER By Position, BranchOrDepartment, FullName';
			$columnnames1=array('JobLevel'); 
			$columnnames2=array('FullName','Position','BranchOrDepartment', 'DateHired','MonthlyRate','LatestDateofChange'); 
			include('../backendphp/layout/displayastablewithsub.php');
				
			
    break;
	
	}
	
		
		
	