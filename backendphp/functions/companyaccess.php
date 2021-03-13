<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
include_once('allowedtoopen.php');

function companyaccess($linklist,$table,$givenfield,$givenvalue,$getfield){
	
	$processids='306,307,308,309,311,312,313';
	if(allowedToOpen(array($processids),'1rtc')){
	if(in_array($table,array('1branches','acctg_1budgetentities','1companies'))){
	$sqlc='select 
	group_concat(CASE 
	WHEN ProcessID="306" then "1"
	WHEN ProcessID="307" then "2"
	WHEN ProcessID="308" then "3"
	WHEN ProcessID="309" then "4"
	WHEN ProcessID="311" then "5"
	WHEN ProcessID="312" then "6"
	end) as CompanyNo from permissions_2allprocesses where ProcessID in ('.$processids.') and FIND_IN_SET('.$_SESSION['(ak0)'].',`AllowedPerID`)';
	$stmtc=$linklist->query($sqlc); $resultc=$stmtc->fetch();

	if($table=='acctg_1budgetentities'){
		//check entity if departments
		$sqlent='SELECT '.$givenfield.' FROM '.$table.' WHERE ' . $givenfield . ' Like "' . addslashes($givenvalue) .'" AND '.$getfield.'>=800';
		$stmtent=$linklist->query($sqlent);
		if ($stmtent->rowCount()>0){
			$checking=0;
		} else {
			$checking=1;
		}
	} else {
		$checking=1;
	}
	
		if (allowedToOpen(313,'1rtc')) {
			$addcon='';
		} elseif($checking==0){
			$addcon='';
		} else{
		$addcon='AND CompanyNo in ('.$resultc['CompanyNo'].')';	
		}
	}else{
		$addcon='';
	}
	
	$listsql="SELECT `" . $getfield . "` from " . $table . " WHERE " . $givenfield . " Like '" . addslashes($givenvalue) ."' ".$addcon." Limit 1";

	$stmt=$linklist->query($listsql);
	
	if($stmt->rowCount()==0){
			echo 'You have no permission to this branch.'; exit();
		}
	}
	
}

?>