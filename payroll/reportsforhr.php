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
			$sql1='SELECT JobLevelID, CONCAT(JobClassification," Level ", RIGHT(JobLevelID,1)) AS JobLevel FROM `attend_0joblevels` jl JOIN `attend_0jobclass` jc ON jc.JobLevelID=jl.JobLevelID '.(!allowedToOpen(8041,'1rtc')?' WHERE jc.JobLevelID NOT IN (7)':'').' ORDER BY jc.JobLevelID, jl.JobLevelID ';
			$sql2='SELECT cp.JobLevelID, FullName,Position,IF(cp.deptid IN (2,3,4,10),Branch,department) AS BranchOrDepartment,DateHired, FORMAT(TotalMonthly,2) AS MonthlyRate,LatestDateofChange from attend_30currentpositions cp join 1employees e on e.IDNo=cp.IDNo join payroll_21dailyandmonthly dm on dm.IDNo=cp.IDNo  ';
			$groupby='JobLevelID';
			$orderby=' ORDER By Position, BranchOrDepartment, FullName';
			$columnnames1=array('JobLevel'); 
			$columnnames2=array('FullName','Position','BranchOrDepartment', 'DateHired','MonthlyRate','LatestDateofChange'); 
			include('../backendphp/layout/displayastablewithsub.php');
				
			
    break;
	
	}
	
		
		
	