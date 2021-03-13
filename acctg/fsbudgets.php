<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(536,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
    
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$which=!isset($_GET['w'])?'lists':$_GET['w'];

if($which!='LookupExpenses'){
include_once('../switchboard/contents.php');
} else {
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
}

echo comboBox($link,'SELECT BranchNo, Branch FROM `1branches` ORDER BY Branch','BranchNo','Branch','branches'); 
echo comboBox($link,'SELECT CompanyNo, Company FROM `1companies` where CompanyNo in (1,2,3,4,5) ORDER BY Company','CompanyNo','Company','companies'); 

//budget planning module vs actual expenses filtering
$sqldepartments='select GROUP_CONCAT(d.deptid) as deptid,PositionID,cp.deptheadpositionid from attend_30currentpositions cp join 1departments d on d.deptheadpositionid=cp.deptheadpositionid  WHERE IDNo='.$_SESSION['(ak0)'].'';
// echo $sqldepartments;
$stmtdepartments = $link->query($sqldepartments); $resultdepartments = $stmtdepartments->fetch();
 if (allowedToOpen(5174,'1rtc')) {
	$fcondition='';

 }else{
	 $fcondition='where deptid in ('.$resultdepartments['deptid'].')';
 }
//

echo comboBox($link,'SELECT  department, deptid, deptheadpositionid FROM `1departments` '.$fcondition.' ORDER BY department','department','deptid','departments'); 

?>
	<style>
#table {
  border-collapse: collapse;
  font-size:10pt;
  width: auto;
  background-color:#cccccc;
}

#table td, #table th {
  border: 1px solid black;
  padding: 3px;
}
#table tr:nth-child(even){background-color:white;}

#table th {
		  text-align:left;
		  background: white;
		  position: sticky;
		  top: 0;
		  box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
		}


</style>
<?php

if (in_array($which,array('LookupExpenses','lists'))){
	// employee
if(isset($_REQUEST['department'])){
// $sqls='select max(DateEffective),TotalMinWage,TimeStamp from `1_gamit`.`payroll_4wageorders` where MinWageAreaID=\'1\' ';
// $stmts=$link->query($sqls); $results=$stmts->fetch();
// $minwage=$results['TotalMinWage']; $daysofmonth=26.08; 
		
$plan=3600;
$onetimeexpenses='30700';

$quartercomputation='(((sum(TotalMonthly)+sum(`SSS-EE`)+sum(`Philhealth-EE`)+sum(`PagIbig-EE`))*3)+(('.$plan.'*count(IDNo))/4)+(sum(TotalMonthly)/4)+((sum(BasicDaily)*5)/4)+(sum(TotalMonthly)/4))';

$quarterhiredcomputation='(((MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100))+(select Employer from '.$lastyr.'_1rtc.payroll_0ssstable where (MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100)) BETWEEN RangeMin AND RangeMax)+(SELECT if(`monthlybasic`<=MinBasic,MinPremium,if(`monthlybasic` < MaxBasic,(`monthlybasic`*PremiumRate/100),MaxPremium))/2 FROM '.$lastyr.'_1rtc.payroll_0phicrate WHERE ApplicableYear='.$currentyr.')+\'100\')*3+('.$plan.'/4)+'.$onetimeexpenses.'+((MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100))/4))
+(((MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100))/26.08)*5)/4+((MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100))/4)';

$quartercomputationne='(((MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100))+(select Employer from '.$lastyr.'_1rtc.payroll_0ssstable where (MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100)) BETWEEN RangeMin AND RangeMax)+(SELECT if(`monthlybasic`<=MinBasic,MinPremium,if(`monthlybasic` < MaxBasic,(`monthlybasic`*PremiumRate/100),MaxPremium))/2 FROM '.$lastyr.'_1rtc.payroll_0phicrate WHERE ApplicableYear='.$currentyr.')+\'100\')*3+('.$plan.'/4)+((MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100))/4))+(((MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100))/26.08)*5)/4+((MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100))/4)';

$union='UNION All select dam.deptid,\'\' as AccountID,\'\' as Account,\'\' as Details,\'\' as monthlybasic,
'.$quartercomputation.' as Q1, '.$quartercomputation.' as Q2, 
'.$quartercomputation.' as Q3, '.$quartercomputation.' as Q4
from '.$lastyr.'_1rtc.payroll_21dailyandmonthly dam left join attend_0positions p on p.PositionID=dam.PositionID left join 1branches b on b.BranchNo=dam.BranchNo where dam.deptid=\''.$_REQUEST['department'].'\' Group by if(dam.deptid=10,dam.BranchNo,"") ';
		
$sqlee='Create temporary table EmployeeExpenses select bp.deptid, bp.AccountID, \'Employee\' as Account,bp.Details,(MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100)) as `monthlybasic`,

		if(substring(Details,LOCATE(\'-\', Details)+1,100)=1,
		'.$quarterhiredcomputation.'	
		,"") as Q1,
		
		if(substring(Details,LOCATE(\'-\', Details)+1,100)=2,
		'.$quarterhiredcomputation.'
			
		,if(substring(Details,LOCATE(\'-\', Details)+1,100)<2,
		'.$quartercomputationne.'
			
		,"")) as Q2,
		
		if(substring(Details,LOCATE(\'-\', Details)+1,100)=3,
		'.$quarterhiredcomputation.'
			
		,if(substring(Details,LOCATE(\'-\', Details)+1,100)<3,
		'.$quartercomputationne.'
			
		,"")) as Q3,
		
		if(substring(Details,LOCATE(\'-\', Details)+1,100)=4,
		'.$quarterhiredcomputation.'
			
		,if(substring(Details,LOCATE(\'-\', Details)+1,100)<4,
		'.$quartercomputationne.'
		
		,"")) as Q4
		
		
		from '.$lastyr.'_1rtc.budget_2budgetplanning bp join acctg_1chartofaccounts ca on ca.AccountID=bp.AccountID  join attend_0positions p on p.PositionID=SUBSTRING_INDEX(bp.Details,\'-\',\'1\') join attend_1joblevel jl on jl.JobLevelNo=p.JobLevelNo  where bp.deptid=\''.$_REQUEST['department'].'\' and bp.AccountID=\'965\' 
		
		'.$union.'
		';
		// echo $sqlee; exit();
		$stmtee=$link->prepare($sqlee); $stmtee->execute();
}
//
}

switch($which){
	case'Edit':

//select department and accounts
$sqld='select dept from 1departments where deptid=\''.$_GET['deptid'].'\'';
$stmtd=$link->query($sqld); $resultd=$stmtd->fetch();

$sqla='select ShortAcctID from acctg_1chartofaccounts where AccountID=\''.$_GET['AccountID'].'\'';
$stmta=$link->query($sqla); $resulta=$stmta->fetch();
//	

	$sql='select Q1, Q2, Q3, Q4 , b.AccountID, Details from '.$lastyr.'_1rtc.budget_2budgetplanning b left join acctg_1chartofaccounts ca on ca.AccountID=b.AccountID where b.deptid=\''.$_GET['deptid'].'\' and b.AccountID=\''.$_GET['AccountID'].'\'';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	if ($stmt->rowCount()==0){
		echo'<title>Add?</title><h3>Add?</h3>Department: <b>'.$resultd['dept'].'</b> '.str_repeat('&nbsp;',5).' Account: <b>'.$resulta['ShortAcctID'].'</b></br>';
		echo'</br><form method="post" action="fsbudgets.php?w=AddEditProcess&add=1&action_token='.$_SESSION['action_token'].'&deptid='.$_GET['deptid'].'&AccountID='.$_GET['AccountID'].'">
				Details: <input type="text" name="Details">
				Q1: <input type="text" name="Q1" size="10">
				Q2: <input type="text" name="Q2" size="10">
				Q3: <input type="text" name="Q3" size="10">
				Q4: <input type="text" name="Q4" size="10">
				<input type="submit" name="submit" value="Add">
			</form>';
	}else{
		echo'<title>Edit?</title><h3>Edit?</h3>Department: <b>'.$resultd['dept'].'</b> '.str_repeat('&nbsp;',5).' Account: <b>'.$resulta['ShortAcctID'].'</b></br>';
		echo'</br><form method="post" action="fsbudgets.php?w=AddEditProcess&edit=1&action_token='.$_SESSION['action_token'].'&deptid='.$_GET['deptid'].'&AccountID='.$_GET['AccountID'].'">
				Details: <input type="text" name="Details" value="'.$result['Details'].'">
				Q1: <input type="text" name="Q1" size="10" value="'.$result['Q1'].'">
				Q2: <input type="text" name="Q2" size="10" value="'.$result['Q2'].'">
				Q3: <input type="text" name="Q3" size="10" value="'.$result['Q3'].'">
				Q4: <input type="text" name="Q4" size="10" value="'.$result['Q4'].'">
				<input type="submit" name="submit" value="Edit">
			</form>';
	}
	
	break;
	
	case'AddEditProcess':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	if(isset($_REQUEST['add'])){
		$sql='insert into '.$lastyr.'_1rtc.budget_2budgetplanning set deptid=\''.$_REQUEST['deptid'].'\',AccountID=\''.$_REQUEST['AccountID'].'\',Q1=\''.$_REQUEST['Q1'].'\',Q2=\''.$_REQUEST['Q2'].'\',Q3=\''.$_REQUEST['Q3'].'\',Q4=\''.$_REQUEST['Q4'].'\',Details=\''.$_REQUEST['Details'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
	}else{
		$sql='UPDATE '.$lastyr.'_1rtc.budget_2budgetplanning set Q1=\''.$_REQUEST['Q1'].'\',Q2=\''.$_REQUEST['Q2'].'\',Q3=\''.$_REQUEST['Q3'].'\',Q4=\''.$_REQUEST['Q4'].'\',Details=\''.$_REQUEST['Details'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() where deptid=\''.$_REQUEST['deptid'].'\' and AccountID=\''.$_REQUEST['AccountID'].'\'';
		
	}
	// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:fsbudgets.php?w=lists&departmentlookup=1&department='.$_REQUEST['deptid'].'');
	
	break;
	
	case'LookupExpenses':
	if($_GET['AccountID']==-1){
		
		$sqlb='select format(sum(floor(Q1)+floor(Q2)+floor(Q3)+floor(Q4)),0) as Budget from EmployeeExpenses';
		// echo $sqlb; exit();
		$stmt=$link->query($sqlb); $resultb=$stmt->fetch();
	
	 $sql='select format(floor(@running_total:=@running_total - Actual),0) AS RemainingBudget,format(floor(Actual),0) as Actual,Date,ControlNo,`Supplier/Customer/Branch`,Particulars,FromBudgetOf from 
		(select ControlNo,`Supplier/Customer/Branch`,Particulars,Amount as Actual,Date,Branch as FromBudgetOf from acctg_0unialltxns ut left join 1branches b on b.BranchNo=ut.FromBudgetOf left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID where ut.FromBudgetOf=\''.($_GET['department']+800).'\' AND ut.AccountID in (901,301,966,905,974,501,502,503,908,909,910) and ControlNo not like \'%BegBal\') Actual JOIN 
		(SELECT @running_total:=sum(Q1+Q2+Q3+Q4) from EmployeeExpenses) Budget Order By Date';
		// echo $sql; exit();
		$stmt=$link->query($sql); 
		$result=$stmt->fetchAll();
		
	}else{
	$sqlb='select format(sum(Q1+Q2+Q3+Q4),0) as Budget from '.$lastyr.'_1rtc.budget_2budgetplanning b left join acctg_1chartofaccounts ca on ca.AccountID=b.AccountID where b.deptid=\''.$_GET['department'].'\' AND b.AccountID=\''.$_GET['AccountID'].'\' ';
		// echo $sqlb; exit();
		$stmt=$link->query($sqlb); $resultb=$stmt->fetch();
	
	 $sql='select format(@running_total:=@running_total - Actual,0) AS RemainingBudget,format(Actual,0) as Actual,Date,ControlNo,`Supplier/Customer/Branch`,Particulars,FromBudgetOf from 
		(select ControlNo,`Supplier/Customer/Branch`,Particulars,Amount as Actual,Date,Branch as FromBudgetOf from acctg_0unialltxns ut left join 1branches b on b.BranchNo=ut.FromBudgetOf left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID where ut.FromBudgetOf=\''.($_GET['department']+800).'\' AND ut.AccountID=\''.$_GET['AccountID'].'\' and ControlNo not like \'%BegBal\') Actual JOIN 
		(SELECT @running_total:=sum(Q1+Q2+Q3+Q4) from '.$lastyr.'_1rtc.budget_2budgetplanning b where b.deptid=\''.$_GET['department'].'\' AND b.AccountID=\''.$_GET['AccountID'].'\') Budget Order By Date';
		// echo $sql; exit();
		$stmt=$link->query($sql); 
		$result=$stmt->fetchAll();
	}
		
		
		echo'</br><table id="table">
		<tr><th>Date</th><th>ControlNo</th><th>Supplier/Customer/Branch</th><th>Particulars</th><th>FromBudgetOf</th><th>Actual</th><th>RemainingBudget</th></tr>
		<tr><td>Budget for the year:</td><td></td><td></td><td></td><td></td><td></td><td>'.($resultb['Budget']).'</td></tr>';

		foreach($result as $res){
			echo'<tr><td>'.$res['Date'].'</td><td>'.$res['ControlNo'].'</td><td>'.$res['Supplier/Customer/Branch'].'</td><td>'.$res['Particulars'].'</td>
			<td>'.$res['FromBudgetOf'].'</td><td>'.$res['Actual'].'</td><td>'.$res['RemainingBudget'].'</td></tr>';		
		}
	
	break;
	case'lists':
	echo'<title>Budget vs Actual</title>';
	echo'<h3>Budget vs Actual</h3>';
	if(!isset($_POST['submit'])){
		$_POST['branch']='';
		$_POST['company']='';
		
	}
	
	$reportmonth=date('m');
	echo'</br>';
	if (allowedToOpen(5362,'1rtc')) {  
			echo'<div style="float:left; margin-left: 20px; width:300px; padding: 2px; border: 2px solid black;">
				<h5 style="text-align:center; color: darkblue;">MONTH Columns</h5>
				<form style="display:inline;" method="post" action="fsbudgets.php">
				<br>'.str_repeat('&nbsp;',4).'<input type=radio name="groupby" value=0>Per Branch 
				<input type=text size=8 name="branch" list="branches" size="10" autocomplete="off" value="'.$_POST['branch'].'">
				<br>'.str_repeat('&nbsp;',4).'<input type=radio name="groupby" value=1  checked=true>
				Per Company <input type="text" name="company" list="companies" size=10 autocomplete="off" value="'.$_POST['company'].'">
				<br>'.str_repeat('&nbsp;',4).'<input type="radio" name="groupby" value="10"> Show All (no Rodlink)
				<br><br>'.str_repeat('&nbsp;',4).' Choose Month (0 - 12):  <input type="text" name="reportmonth" value="'.$reportmonth.'" size="2">
				<br><br>'.str_repeat('&nbsp;',30).'<input type=submit name="submit" value="Lookup"  size=100px>
				</form></div>';
	}
	if (allowedToOpen(5173,'1rtc')) {  			
			echo'<div style="float:left; margin-left: 20px; width:300px; padding: 2px; border: 2px solid black;">
				<h5 style="text-align:center; color: darkblue;">QUARTER Columns</h5>
				<form style="display:inline;" method="post" action="fsbudgets.php">
				<br>'.str_repeat('&nbsp;',4).'Department:
				<input type="text" name="department" list="departments"autocomplete="off" size="10">
				<input type="submit" name="departmentlookup" value="Lookup">
				</form></div>';
	}
	if (allowedToOpen(5362,'1rtc')) {  
	echo'</br></br></br></br></br></br></br></br></br></br>';
	}else{
		echo'</br></br></br></br>';
	}
	
	if(isset($_REQUEST['departmentlookup'])){
				
		$frombudgetof=$_REQUEST['department']+800;
		$sqlt='Create temporary table Actual select FromBudgetOf, ut.AccountID, month(Date) as Month, sum(Amount) as Amount from acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID where ControlNo not like \'%BegBal\' Group By ut.AccountID,month(Date),FromBudgetOf';
		// echo $sqlt; exit();
		$stmtt=$link->prepare($sqlt); $stmtt->execute();
		$sqla='Create Temporary table QuarterActual as select AccountID, FromBudgetOf,
				sum(CASE WHEN Month=1 or Month=2 or Month=3 THEN IFNULL(`Amount`,0) END) AS Q1Actual,
				sum(CASE WHEN Month=4 or Month=5 or Month=6 THEN IFNULL(`Amount`,0) END) AS Q2Actual,
				sum(CASE WHEN Month=7 or Month=8 or Month=9 THEN IFNULL(`Amount`,0) END) AS Q3Actual,
				sum(CASE WHEN Month=10 or Month=11 or Month=12 THEN IFNULL(`Amount`,0) END) AS Q4Actual
				from Actual where FromBudgetOf=\''.$frombudgetof.'\' Group By AccountID,FromBudgetOf';
		// echo $sqla; exit();
		$stmta=$link->prepare($sqla); $stmta->execute();
//TOTAL Table

$sqlt='create temporary table TOTAL as select b.AccountID,ShortAcctID as Account, Details, 
			format(Q1,0) as Q1Budget, format(Q1Actual,0) as Q1Actual,
			format(Q2,0) as Q2Budget, format(Q2Actual,0) as Q2Actual,
			format(Q3,0) as Q3Budget, format(Q3Actual,0) as Q3Actual,
			format(Q4,0) as Q4Budget, format(Q4Actual,0) as Q4Actual,
			format((Q1+Q2+Q3+Q4),0) AS TotalBudget,format((ifnull(Q1Actual,0)+ifnull(Q2Actual,0)+ifnull(Q3Actual,0)+ifnull(Q4Actual,0)),0) as TotalActual,
			
			Q1 as Q1BudgetValue, Q1Actual as Q1ActualValue,
			Q2 as Q2BudgetValue, Q2Actual as Q2ActualValue,
			Q3 as Q3BudgetValue, Q3Actual as Q3ActualValue,
			Q4 as Q4BudgetValue, Q4Actual as Q4ActualValue,
			(Q1+Q2+Q3+Q4) AS TotalBudgetValue,(ifnull(Q1Actual,0)+ifnull(Q2Actual,0)+ifnull(Q3Actual,0)+ifnull(Q4Actual,0)) as TotalActualValue
			
			
			from '.$lastyr.'_1rtc.budget_2budgetplanning b left join QuarterActual a on a.AccountID=b.AccountID and a.FromBudgetOf=b.deptid+800 left join acctg_1chartofaccounts ca on ca.AccountID=b.AccountID left join 1departments d on d.deptid=b.deptid where b.deptid=\''.$_REQUEST['department'].'\' and b.AccountID<>965 Group By b.AccountID
			
			UNION ALL select \'-1\' as AccountID,\'Employee\' as Account,\'\' as Details,
			format(sum(floor(Q1)),0) as Q1Budget,
			(select format(sum(floor(Q1Actual)),0) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as Q1Actual,
			format(sum(floor(Q2)),0) as Q2Budget,
			(select format(sum(floor(Q2Actual)),0) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as Q2Actual,
			format(sum(floor(Q3)),0) as Q3Budget,
			(select format(sum(floor(Q3Actual)),0) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as Q3Actual,
			format(sum(floor(Q4)),0) as Q4Budget,
			(select format(sum(floor(Q4Actual)),0) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as Q4Actual,
			format((sum(floor(Q1))+sum(floor(Q2))+sum(floor(Q3))+sum(floor(Q4))),0) as TotalBudget,
			(select format(sum(floor(ifnull(Q1Actual,0))+floor(ifnull(Q2Actual,0))+floor(ifnull(Q3Actual,0))+floor(ifnull(Q4Actual,0))),0) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as TotalActual,
			
			sum(floor(Q1)) as Q1BudgetValue,
			(select sum(floor(Q1Actual)) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as Q1ActualValue,
			sum(floor(Q2)) as Q2BudgetValue,
			(select sum(floor(Q2Actual)) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as Q2ActualValue,
			sum(floor(Q3)) as Q3BudgetValue,
			(select sum(floor(Q3Actual)) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as Q3ActualValue,
			sum(floor(Q4)) as Q4BudgetValue,
			(select sum(floor(Q4Actual)) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as Q4ActualValue,
			(sum(floor(Q1))+sum(floor(Q2))+sum(floor(Q3))+sum(floor(Q4))) as TotalBudgetValue,
			(select sum(floor(ifnull(Q1Actual,0))+floor(ifnull(Q2Actual,0))+floor(ifnull(Q3Actual,0))+floor(ifnull(Q4Actual,0))) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as TotalActualValue

			from EmployeeExpenses 
			
			UNION ALL
			
			select 
			qa.AccountID, \'\' AS Account, \'\' as Details,
			\'0\' Q1Budget, 
			format(sum(floor(Q1Actual)),0) as Q1Actual,
			\'0\' as Q2Budget,
			format(sum(floor(Q2Actual)),0) as Q2Actual,
			\'0\' as Q3Budget, 
			format(sum(floor(Q3Actual)),0) as Q3Actual,
			\'0\' as Q4Budget,
			format(sum(floor(Q4Actual)),0) as Q4Actual,
			\'0\' as TotalBudget,
format(sum(floor(ifnull(Q1Actual,0))+floor(ifnull(Q2Actual,0))+floor(ifnull(Q3Actual,0))+floor(ifnull(Q4Actual,0))),0) as TotalActual,

			\'0\' Q1BudgetValue, 
			sum(floor(Q1Actual)) as Q1ActualValue,
			\'0\' as Q2BudgetValue,
			sum(floor(Q2Actual)) as Q2ActualValue,
			\'0\' as Q3BudgetValue, 
			sum(floor(Q3Actual)) as Q3ActualValue,
			\'0\' as Q4BudgetValue,
			sum(floor(Q4Actual)) as Q4ActualValue,
			\'0\' as TotalBudgetValue,
sum(floor(ifnull(Q1Actual,0))+floor(ifnull(Q2Actual,0))+floor(ifnull(Q3Actual,0))+floor(ifnull(Q4Actual,0))) as TotalActualValue from QuarterActual qa left join acctg_1chartofaccounts ca on ca.AccountID=qa.AccountID where qa.AccountID not in (select AccountID from '.$lastyr.'_1rtc.budget_2budgetplanning where deptid=\''.$_REQUEST['department'].'\' Group by AccountID) and Budgeted=1';
$stmtt=$link->prepare($sqlt); $stmtt->execute();			

//
			$sqlm='select b.AccountID,ShortAcctID,ShortAcctID as Account, Details, 
			format(Q1,0) as Q1Budget, format(Q1Actual,0) as Q1Actual,
			format(Q2,0) as Q2Budget, format(Q2Actual,0) as Q2Actual,
			format(Q3,0) as Q3Budget, format(Q3Actual,0) as Q3Actual,
			format(Q4,0) as Q4Budget, format(Q4Actual,0) as Q4Actual,
			format((Q1+Q2+Q3+Q4),0) AS TotalBudget,format((ifnull(Q1Actual,0)+ifnull(Q2Actual,0)+ifnull(Q3Actual,0)+ifnull(Q4Actual,0)),0) as TotalActual,
			
			Q1 as Q1BudgetValue, Q1Actual as Q1ActualValue,
			Q2 as Q2BudgetValue, Q2Actual as Q2ActualValue,
			Q3 as Q3BudgetValue, Q3Actual as Q3ActualValue,
			Q4 as Q4BudgetValue, Q4Actual as Q4ActualValue,
			(Q1+Q2+Q3+Q4) AS TotalBudgetValue,(ifnull(Q1Actual,0)+ifnull(Q2Actual,0)+ifnull(Q3Actual,0)+ifnull(Q4Actual,0)) as TotalActualValue
			
			
			from '.$lastyr.'_1rtc.budget_2budgetplanning b left join QuarterActual a on a.AccountID=b.AccountID and a.FromBudgetOf=b.deptid+800 left join acctg_1chartofaccounts ca on ca.AccountID=b.AccountID left join 1departments d on d.deptid=b.deptid where b.deptid=\''.$_REQUEST['department'].'\' and b.AccountID<>965 Group By b.AccountID
			
			UNION ALL select \'-1\' as AccountID,Account as ShortAcctID,\'Employee\' as Account,\'\' as Details,
			format(sum(floor(Q1)),0) as Q1Budget,
			(select format(sum(floor(Q1Actual)),0) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as Q1Actual,
			format(sum(floor(Q2)),0) as Q2Budget,
			(select format(sum(floor(Q2Actual)),0) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as Q2Actual,
			format(sum(floor(Q3)),0) as Q3Budget,
			(select format(sum(floor(Q3Actual)),0) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as Q3Actual,
			format(sum(floor(Q4)),0) as Q4Budget,
			(select format(sum(floor(Q4Actual)),0) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as Q4Actual,
			format((sum(floor(Q1))+sum(floor(Q2))+sum(floor(Q3))+sum(floor(Q4))),0) as TotalBudget,
			(select format(sum(floor(ifnull(Q1Actual,0))+floor(ifnull(Q2Actual,0))+floor(ifnull(Q3Actual,0))+floor(ifnull(Q4Actual,0))),0) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as TotalActual,
			
			sum(floor(Q1)) as Q1BudgetValue,
			(select sum(floor(Q1Actual)) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as Q1ActualValue,
			sum(floor(Q2)) as Q2BudgetValue,
			(select sum(floor(Q2Actual)) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as Q2ActualValue,
			sum(floor(Q3)) as Q3BudgetValue,
			(select sum(floor(Q3Actual)) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as Q3ActualValue,
			sum(floor(Q4)) as Q4BudgetValue,
			(select sum(floor(Q4Actual)) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as Q4ActualValue,
			(sum(floor(Q1))+sum(floor(Q2))+sum(floor(Q3))+sum(floor(Q4))) as TotalBudgetValue,
			(select sum(floor(ifnull(Q1Actual,0))+floor(ifnull(Q2Actual,0))+floor(ifnull(Q3Actual,0))+floor(ifnull(Q4Actual,0))) from QuarterActual where AccountID in (901,301,966,905,974,501,502,503,908,909,910)) as TotalActualValue from EmployeeExpenses 
			
			UNION ALL select \'\' as AccountID,\'z\' AS ShortAcctID,\'\' AS Account,\'TOTAL\' as Details,
			format(sum(floor(Q1BudgetValue)),0) as Q1Budget, 
			format(sum(floor(Q1ActualValue)),0) as Q1Actual,
			format(sum(floor(Q2BudgetValue)),0) as Q2Budget,
			format(sum(floor(Q2ActualValue)),0) as Q2Actual,
			format(sum(floor(Q3BudgetValue)),0) as Q3Budget, 
			format(sum(floor(Q3ActualValue)),0) as Q3Actual,
			format(sum(floor(Q4BudgetValue)),0) as Q4Budget,
			format(sum(floor(Q4ActualValue)),0) as Q4Actual,
format((sum(floor(TotalBudgetValue))),0) as TotalBudget,
format((sum(floor(TotalActualValue))),0) as TotalActual,
			sum(floor(Q1BudgetValue)) as Q1BudgetValue, 
			sum(floor(Q1ActualValue)) as Q1ActualValue,
			sum(floor(Q2BudgetValue)) as Q2BudgetValue,
			sum(floor(Q2ActualValue)) as Q2ActualValue,
			sum(floor(Q3BudgetValue)) as Q3BudgetValue, 
			sum(floor(Q3ActualValue)) as Q3ActualValue,
			sum(floor(Q4BudgetValue)) as Q4BudgetValue,
			sum(floor(Q4ActualValue)) as Q4ActualValue,
			(sum(floor(TotalBudgetValue))) as TotalBudgetValue,
			(sum(floor(TotalActualValue))) as TotalActualValue from TOTAL
			
			UNION ALL
			
			select 
			qa.AccountID, ShortAcctID, ShortAcctID AS Account, \'\' as Details,
			\'\' Q1Budget, 
			format((floor(Q1Actual)),0) as Q1Actual,
			\'\' as Q2Budget,
			format((floor(Q2Actual)),0) as Q2Actual,
			\'\' as Q3Budget, 
			format((floor(Q3Actual)),0) as Q3Actual,
			\'\' as Q4Budget,
			format((floor(Q4Actual)),0) as Q4Actual,
			\'\' as TotalBudget,
format((floor(ifnull(Q1Actual,0))+floor(ifnull(Q2Actual,0))+floor(ifnull(Q3Actual,0))+floor(ifnull(Q4Actual,0))),0) as TotalActual,

			\'0\' Q1BudgetValue, 
			(floor(Q1Actual)) as Q1ActualValue,
			\'0\' as Q2BudgetValue,
			(floor(Q2Actual)) as Q2ActualValue,
			\'0\' as Q3BudgetValue, 
			(floor(Q3Actual)) as Q3ActualValue,
			\'0\' as Q4BudgetValue,
			(floor(Q4Actual)) as Q4ActualValue,
			\'0\' as TotalBudgetValue,
(floor(ifnull(Q1Actual,0))+floor(ifnull(Q2Actual,0))+floor(ifnull(Q3Actual,0))+floor(ifnull(Q4Actual,0))) as TotalActualValue from QuarterActual qa left join acctg_1chartofaccounts ca on ca.AccountID=qa.AccountID where qa.AccountID not in (select AccountID from '.$lastyr.'_1rtc.budget_2budgetplanning where deptid=\''.$_REQUEST['department'].'\' Group by AccountID) and Budgeted=1 Order By ShortAcctID';
			
			$stmtm=$link->query($sqlm); $resultm=$stmtm->fetchAll();
			
			$sqld='select department from 1departments where deptid=\''.$_REQUEST['department'].'\'';
			$stmtd=$link->query($sqld); $resultd=$stmtd->fetch();
			$sqlac='select group_concat(concat(AccountID,\'-\',ShortAcctID) separator \', \') as Accounts from acctg_1chartofaccounts where AccountID in (901,301,966,905,974,501,502,503,908,909,910)';
			$stmtac=$link->query($sqlac); $resultac=$stmtac->fetch();
			echo'<div style="background-color:#f2f2f2; padding:5px; width:1080px;"></i><b>NOTE:</b></br>
				If cell is color red, the Expenses reached the 80% of the Budget.</br>
				<b>EmployeeExpenses</b> = Accounts:&nbsp; '.$resultac['Accounts'].'
				</div></br>';
			echo'<b>Department:&nbsp; '.$resultd['department'].'</b><table id="table"><tr>
			<th>Account</th><th>Details</th>
				<th>Q1Budget</th><th>Q1Actual</th><th>Q2Budget</th><th>Q2Actual</th>
				<th>Q3Budget</th><th>Q3Actual</th><th>Q4Budget</th><th>Q4Actual</th>
			<th>TotalBudget</th><th>TotalActual</th>	
			</tr>';
			foreach($resultm as $resm){
				$percentq1actual=($resm['Q1ActualValue']/$resm['Q1BudgetValue'])*100;
				$percentq2actual=($resm['Q2ActualValue']/$resm['Q2BudgetValue'])*100;
				$percentq3actual=($resm['Q3ActualValue']/$resm['Q3BudgetValue'])*100;
				$percentq4actual=($resm['Q4ActualValue']/$resm['Q4BudgetValue'])*100;
				$percenttotalactual=($resm['TotalActualValue']/$resm['TotalBudgetValue'])*100;
				
				if($percentq1actual>=80){
					$bgcolor1='bgcolor="red"';
				}else{
					$bgcolor1='';		
				}
				
				if($percentq2actual>=80){
					$bgcolor2='bgcolor="red"';
				}else{
					$bgcolor2='';		
				}
				
				if($percentq3actual>=80){
					$bgcolor3='bgcolor="red"';
				}else{
					$bgcolor3='';		
				}
				
				if($percentq4actual>=80){
					$bgcolor4='bgcolor="red"';
				}else{
					$bgcolor4='';		
				}
				
				if($percenttotalactual>=80){
					$bgcolor5='bgcolor="red"';
				}else{
					$bgcolor5='';		
				}
if (allowedToOpen(5174,'1rtc')) {
	$edit='<td><a href="fsbudgets.php?w=Edit&deptid='.$_REQUEST['department'].'&AccountID='.$resm['AccountID'].'">Edit</a></td>';

 }else{
	 $edit='';

 }				
				echo'<tr>
			<td>'.$resm['Account'].'</td><td>'.$resm['Details'].'</td>
				<td>'.$resm['Q1Budget'].'</td><td '.$bgcolor1.'>'.$resm['Q1Actual'].'</td><td>'.$resm['Q2Budget'].'</td><td '.$bgcolor2.'>'.$resm['Q2Actual'].'</td>
				<td>'.$resm['Q3Budget'].'</td><td '.$bgcolor3.'>'.$resm['Q3Actual'].'</td><td>'.$resm['Q4Budget'].'</td><td '.$bgcolor4.'>'.$resm['Q4Actual'].'</td>
			<td>'.$resm['TotalBudget'].'</td><td '.$bgcolor5.'>'.$resm['TotalActual'].'</td><td><a href=""  onclick="window.open(\'fsbudgets.php?w=LookupExpenses&department='.$_REQUEST['department'].'&AccountID='.$resm['AccountID'].'\', \'newwindow\',\'width=1000,height=500\');return false;">Lookup</a></td>'.$edit.'
			</tr>';
				
			}
		exit();
	}
		if(isset($_POST['submit'])){
				switch($_POST['groupby']){
					case'0':  //Branch
							$branchno=companyandbranchValue($link, '1branches', 'Branch', $_POST['branch'], 'BranchNo');
							$yearsales=',(`01`+`02`+`03`+`04`+`05`+`06`+`07`+`08`+`09`+`10`+`11`+`12`) as YearBudget,(select sum(Amount)*NormBal from acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID where AccountType=\'100\' and BranchNo=\''.$branchno.'\') as YearActual';
							$yearcogst=',(select sum(Amount) from acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID left join 1branches b on b.BranchNo=ut.BranchNo where ut.BranchNo=\''.$branchno.'\' and AccountType=\'101\') as YearActual,(select sum(`01`+`02`+`03`+`04`+`05`+`06`+`07`+`08`+`09`+`10`+`11`+`12`)*.75 from acctg_1yearsalestargets ut left join 1branches b on b.BranchNo=ut.BranchNo where ut.BranchNo=\''.$branchno.'\') as YearBudget';
							$yearcogs=',(select format(sum(Amount),2) from acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID left join 1branches b on b.BranchNo=ut.BranchNo where ut.BranchNo=\''.$branchno.'\' and AccountType=\'101\') as YearActual,(select format(sum(`01`+`02`+`03`+`04`+`05`+`06`+`07`+`08`+`09`+`10`+`11`+`12`)*.75,2) from acctg_1yearsalestargets ut left join 1branches b on b.BranchNo=ut.BranchNo where ut.BranchNo=\''.$branchno.'\') as YearBudget';
							$condi='where b.BranchNo=\''.$branchno.'\' and month(Date) between \'1\' and \''.$_POST['reportmonth'].'\' and AccountType=\'100\'';
							$cogscondi='where b.BranchNo=\''.$branchno.'\' and';
							$acctidcondi='where BranchNo=\''.$branchno.'\' and Month between \'1\' and \''.$_POST['reportmonth'].'\'';
							$acctidactualcondi='where b.BranchNo=\''.$branchno.'\' and month(Date) between \'1\' and \''.$_POST['reportmonth'].'\' and AccountType in (200,201,210,220,230,240)';
							$acctidgroupby='Group By Month, AccountID';
							$yearacctt=',YearBudget,YearActual';
							$yearacct=',format(YearBudget,2) as YearBudget,format(YearActual,2) as YearActual';
							$acctidactualgroupby='Group By Month, ut.AccountID';
							$groupby='Group By month(Date)';
							$fgroupby='Group by sa.BranchNo';
							$cogsgroupby='where b.BranchNo=\''.$branchno.'\'';
							$newcondi='where b.BranchNo=\''.$branchno.'\'';
							$yearcondia='where b.BranchNo=\''.$branchno.'\' and AccountType in (200,201,210,220,230,240)';
							$yearcondib='where b.BranchNo=\''.$branchno.'\'';
							$filter=''.$_POST['branch'].'';
					break;
					case'1':  //Company
							$companyno=companyandbranchValue($link, '1companies', 'Company', $_POST['company'], 'CompanyNo');
							$yearsales=',(sum(`01`)+sum(`02`)+sum(`03`)+sum(`04`)+sum(`05`)+sum(`06`)+sum(`07`)+sum(`08`)+sum(`09`)+sum(`10`)+sum(`11`)+sum(`12`)) as YearBudget,(select sum(Amount)*NormBal from acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID left join 1branches b on b.BranchNo=ut.BranchNo where AccountType=\'100\' and b.CompanyNo=\''.$companyno.'\') as YearActual';
							$yearcogst=',(select sum(Amount) from acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID left join 1branches b on b.BranchNo=ut.BranchNo where CompanyNo=\''.$companyno.'\' and AccountType=\'101\') as YearActual,(select sum(`01`+`02`+`03`+`04`+`05`+`06`+`07`+`08`+`09`+`10`+`11`+`12`)*.75 from acctg_1yearsalestargets ut left join 1branches b on b.BranchNo=ut.BranchNo where CompanyNo=\''.$companyno.'\') as YearBudget';
							$yearcogs=',(select format(sum(Amount),2) from acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID left join 1branches b on b.BranchNo=ut.BranchNo where CompanyNo=\''.$companyno.'\' and AccountType=\'101\') as YearActual,(select format(sum(`01`+`02`+`03`+`04`+`05`+`06`+`07`+`08`+`09`+`10`+`11`+`12`)*.75,2) from acctg_1yearsalestargets ut left join 1branches b on b.BranchNo=ut.BranchNo where CompanyNo=\''.$companyno.'\') as YearBudget';
							$condi='where CompanyNo=\''.$companyno.'\' and month(Date) between \'1\' and \''.$_POST['reportmonth'].'\' and AccountType=\'100\'';
							$cogscondi='where CompanyNo=\''.$companyno.'\' and';
							$acctidcondi='where CompanyNo=\''.$companyno.'\' and Month between \'1\' and \''.$_POST['reportmonth'].'\'';
							$acctidactualcondi='where CompanyNo=\''.$companyno.'\' and month(Date) between \'1\' and \''.$_POST['reportmonth'].'\' and AccountType in (200,201,210,220,230,240)';
							$acctidactualgroupby='Group By Month, ut.AccountID';
							$acctidgroupby='Group By Month, AccountID';
							$yearacctt=',YearBudget,YearActual';
							$yearacct=',format(YearBudget,2) as YearBudget,format(YearActual,2) as YearActual';
							$groupby='Group By month(Date)';
							$fgroupby='Group by sa.CompanyNo';
							$cogsgroupby='where b.CompanyNo=\''.$companyno.'\'';
							$newcondi='where b.CompanyNo=\''.$companyno.'\'';
							$yearcondia='where CompanyNo=\''.$companyno.'\' and AccountType in (200,201,210,220,230,240)';
							$yearcondib='where b.CompanyNo=\''.$companyno.'\'';
							$filter=''.$_POST['company'].'';
					break;
					case'10':  //All
							$yearsales=',sum(`01`+`02`+`03`+`04`+`05`+`06`+`07`+`08`+`09`+`10`+`11`+`12`) as YearBudget,(select sum(Amount)*NormBal from acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID where AccountType=\'100\' ) as YearActual';
							$yearcogst=',(select sum(Amount) from acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID left join 1branches b on b.BranchNo=ut.BranchNo where AccountType=\'101\') as YearActual,(select sum(`01`+`02`+`03`+`04`+`05`+`06`+`07`+`08`+`09`+`10`+`11`+`12`)*.75 from acctg_1yearsalestargets ) as YearBudget';
							$yearcogs=',(select format(sum(Amount),2) from acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID left join 1branches b on b.BranchNo=ut.BranchNo where AccountType=\'101\') as YearActual,(select format(sum(`01`+`02`+`03`+`04`+`05`+`06`+`07`+`08`+`09`+`10`+`11`+`12`)*.75,2) from acctg_1yearsalestargets ) as YearBudget';
							$condi='where AccountType=\'100\'';
							$cogscondi='where ';
							$acctidcondi='where Month between \'1\' and \''.$_POST['reportmonth'].'\'';
							$acctidactualcondi='where month(Date) between \'1\' and \''.$_POST['reportmonth'].'\' and AccountType in (200,201,210,220,230,240)';
							$acctidactualgroupby='Group By Month, ut.AccountID';
							$acctidgroupby='Group By Month, AccountID';
							$yearacctt=',YearBudget,YearActual';
							$yearacct=',format(YearBudget,2) as YearBudget,format(YearActual,2) as YearActual';
							$groupby='Group By month(Date)';
							$fgroupby='';
							$cogsgroupby='';
							$yearcondia='where AccountType in (200,201,210,220,230,240)';
							$yearcondib='';
							$filter='';
					break;
					
				}
		}
if(isset($_POST['submit'])){
					//Temporary table for SalesActual
					$sql1='create temporary table SalesActual as select sum(Amount)*-1 as Actual,ut.BranchNo as BranchNo,CompanyNo,month(Date) as Month from acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID left join 1branches b on b.BranchNo=ut.BranchNo '.$condi.' '.$groupby.'';
					// echo $sql1; exit();
					$stmt1=$link->prepare($sql1); $stmt1->execute();
					
					//Temporary table acctid
					$sql1='create temporary table AcctBudget as select AccountID,sum(Budget) as Budget,BranchNo,CompanyNo, Month from budget_1budgets bu left join 1branches b on b.BranchNo=bu.EntityID '.$acctidcondi.' '.$acctidgroupby.'';
					// echo $sql1; exit();
					$stmt1=$link->prepare($sql1); $stmt1->execute();
					$sql1='create temporary table AcctActual as select ut.AccountID as AccountID,sum(Amount)*-1 as Actual,ut.BranchNo as BranchNo,CompanyNo, month(Date) as Month from acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID left join 1branches b on b.BranchNo=ut.BranchNo '.$acctidactualcondi.' '.$acctidactualgroupby.'';
					// echo $sql1; exit();
					$stmt1=$link->prepare($sql1); $stmt1->execute();
						//yearbudget and yearactyual
						$sql1='create temporary table AcctYearBudget as select AccountID,sum(Budget) as YearBudget from budget_1budgets bu left join 1branches b on b.BranchNo=bu.EntityID '.$yearcondib.' Group By bu.AccountID';
						// echo $sql1; exit();
						$stmt1=$link->prepare($sql1); $stmt1->execute();
						$sql1='create temporary table AcctYearActual as select ut.AccountID as AccountID,sum(Amount)*-1 as YearActual from acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID left join 1branches b on b.BranchNo=ut.BranchNo '.$yearcondia.' Group By ut.AccountID';
						// echo $sql1; exit();
						$stmt1=$link->prepare($sql1); $stmt1->execute();
						
					$sqlcs='';
					$sqlcogs='';
					$sqlcogst='';
					$COGSActual='';
					$COGSActualt='';
					$acctid='';
					$acctidt='';
					$totalacctid='';
					$totalacctidt='';
					$grossincome='';
					$grossincomet='';
					$netincome='';
					$columnnames=array('AccountID','AccountDescription');
					$c=1;
		while($c<=$_POST['reportmonth']){
					if(strlen($c)==1){
						$col=str_pad($c,2,"0",STR_PAD_LEFT);						
					}else{
						$col=$c;
					}
					
						switch($c){
							case'1':$colnameB='JanBudget'; $colnameA='JanActual'; break; case'2':$colnameB='FebBudget'; $colnameA='FebActual';break; case'3':$colnameB='MarBudget'; $colnameA='MarActual';break;
							case'4':$colnameB='AprBudget'; $colnameA='AprActual';break; case'5':$colnameB='MayBudget'; $colnameA='MayActual';break; case'6':$colnameB='JunBudget'; $colnameA='JunActual';break;
							case'7':$colnameB='JulBudget'; $colnameA='JulActual';break; case'8':$colnameB='AugBudget'; $colnameA='AugActual';break; case'9':$colnameB='SepBudget'; $colnameA='SepActual';break;
							case'10':$colnameB='OctBudget'; $colnameA='OctActual';break; case'11':$colnameB='NovBudget'; $colnameA='NovActual';break; case'12':$colnameB='DecBudget'; $colnameA='DecActual';break;
							
						}
						if($_POST['groupby']==10){
						$sqlcs.='case when \''.$c.'\'=\''.$c.'\' then sum(`'.$col.'`) end as '.$colnameB.',sum(CASE WHEN Month=\''.$c.'\' then Actual end) as '.$colnameA.', ';
						}elseif($_POST['groupby']==1){
						$sqlcs.='case when \''.$c.'\'=\''.$c.'\' then sum(`'.$col.'`) end as '.$colnameB.',sum(CASE WHEN Month=\''.$c.'\' then Actual end) as '.$colnameA.', ';}else{
							$sqlcs.='case when \''.$c.'\'=\''.$c.'\' then `'.$col.'` end as '.$colnameB.',sum(CASE WHEN Month=\''.$c.'\' then Actual end) as '.$colnameA.', ';
							
						}
						$sqlcogst.='case when \''.$c.'\'=\''.$c.'\' then sum(`'.$col.'`)*.75 end as '.$colnameB.', ';
						$sqlcogs.='case when \''.$c.'\'=\''.$c.'\' then format(sum(`'.$col.'`)*.75,2) end as '.$colnameB.', ';
						$datem=date("m",strtotime($_SESSION['nb4A']));
						// echo $datem; exit();
						if($c<$datem){
						$COGSActualt.='(select sum(Amount) from acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID left join 1branches b on b.BranchNo=ut.BranchNo '.$cogscondi.' month(Date)=\''.$c.'\' and AccountType=\'101\') as  '.$colnameA.',';
						// echo $COGSActualt; exit();
						$COGSActual.='(select format(sum(Amount),2) from acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID left join 1branches b on b.BranchNo=ut.BranchNo '.$cogscondi.' month(Date)=\''.$c.'\' and AccountType=\'101\') as  '.$colnameA.',';
						$acctidt.='avg(case when bu.Month=\''.$c.'\' then Budget end) as '.$colnameB.',avg(case when ut.Month=\''.$c.'\' then Actual end) as '.$colnameA.',';		
						}else{
						$COGSActualt.='(select sum(Qty*`'.$col.'`) FROM invty_2sale s JOIN invty_2salesub ss ON ss.TxnID=s.TxnID JOIN `'.$currentyr.'_static`.`invty_weightedavecost` wac ON wac.ItemCode=ss.ItemCode left join 1branches b on b.BranchNo=s.BranchNo '.$cogscondi.' month(Date)=\''.$c.'\') as  '.$colnameA.',';
						$COGSActual.='(select format(sum(Qty*`'.$col.'`),2) FROM invty_2sale s JOIN invty_2salesub ss ON ss.TxnID=s.TxnID JOIN `'.$currentyr.'_static`.`invty_weightedavecost` wac ON wac.ItemCode=ss.ItemCode left join 1branches b on b.BranchNo=s.BranchNo '.$cogscondi.' month(Date)=\''.$c.'\') as  '.$colnameA.',';
						$acctidt.='avg(case when bu.Month=\''.$c.'\' then Budget end) as '.$colnameB.',avg(case when ut.Month=\''.$c.'\' then Actual end) as '.$colnameA.',';
						}
						$acctid.='format(avg(case when bu.Month=\''.$c.'\' then Budget end),2) as '.$colnameB.',format(avg(case when ut.Month=\''.$c.'\' then Actual end),2) as '.$colnameA.',';
						$totalacctidt.='sum('.$colnameB.') as '.$colnameB.',sum('.$colnameA.') as '.$colnameA.',';
						$totalacctid.='format(sum('.$colnameB.'),2) as '.$colnameB.',format(sum('.$colnameA.'),2) as '.$colnameA.',';
						$grossincomet.=''.'st.'.$colnameB.'-ct.'.$colnameB.' as '.$colnameB.','.'st.'.$colnameA.'-ct.'.$colnameA.' as '.$colnameA.',';
						$grossincome.=''.'format(st.'.$colnameB.'-ct.'.$colnameB.',2) as '.$colnameB.','.'format(st.'.$colnameA.'-ct.'.$colnameA.',2) as '.$colnameA.',';
						$netincome.=''.'format(gi.'.$colnameB.'-te.'.$colnameB.',2) as '.$colnameB.','.'format(gi.'.$colnameA.'-te.'.$colnameA.',2) as '.$colnameA.',';
						// echo $acctid; exit();
						$columnnames[]=$colnameB;
						$columnnames[]=$colnameA;
						
						$c++;
		}
		
					
				
					$title='';
					$formdesc='';
					if($_POST['groupby']==10){
					$sql1='Create Temporary table SalesTotal as select \'1\' as Con,'.$sqlcs.' \'\' as AccountID,\'Sales\' as AccountDescription '.$yearsales.' from SalesActual sa left join 1branches b on b.BranchNo=sa.branchno right join acctg_1yearsalestargets yst on yst.BranchNo=b.BranchNo '.$fgroupby.'';
					$stmt=$link->prepare($sql1); $stmt->execute();
					$sql='select '.$sqlcs.' \'\' as AccountID,\'Sales\' as AccountDescription '.$yearsales.' from SalesActual sa left join 1branches b on b.BranchNo=sa.branchno right join acctg_1yearsalestargets yst on yst.BranchNo=b.BranchNo '.$fgroupby.'';
					// echo $sql; exit();
					$stmt=$link->query($sql); $result=$stmt->fetch();
					}else{
						$sql1='Create Temporary table SalesTotal as select \'1\' as Con,'.$sqlcs.' \'\' as AccountID,\'Sales\' as AccountDescription '.$yearsales.' from acctg_1yearsalestargets sa left join 1branches b on b.BranchNo=sa.branchno left join SalesActual yst on yst.BranchNo=b.BranchNo '.$newcondi.'';
					$stmt=$link->prepare($sql1); $stmt->execute();
					$sql='select '.$sqlcs.' \'\' as AccountID,\'Sales\' as AccountDescription '.$yearsales.' from acctg_1yearsalestargets sa left join 1branches b on b.BranchNo=sa.branchno left join SalesActual yst on yst.BranchNo=b.BranchNo '.$newcondi.'';
					// echo $sql; exit();
					$stmt=$link->query($sql); $result=$stmt->fetch();
						
					}

					$columnnames[]='YearBudget'; $columnnames[]='YearActual';
					// echo $sql; exit();
					echo '<b>'.$filter.'</b>';
					echo '<table id="table">';
					//header
					echo'<tr>';
					foreach($columnnames as $col){
						echo'<th>'.$col.'</th>';	
					}
					echo'</tr>';
					//table data for sales
					echo '<tr>';
					foreach($columnnames as $col){
						if($result[$col]=='' OR $result[$col]=='Sales'){
							$result[$col]=$result[$col];
							
						}else{
							$result[$col]=number_format($result[$col],2);
						}
						echo'<td>'.$result[$col].'</td>';
					}
					echo'</tr>';
					$fields=$columnnames;
					
					//COGS
					$sql1='Create Temporary table COGStotal as select \'1\' as Con,'.$sqlcogst.' '.$COGSActualt.' \'\' as AccountID,\'COGS Budgeted at 75%\' as AccountDescription '.$yearcogst	.' from acctg_1yearsalestargets sa left join 1branches b on b.BranchNo=sa.branchno '.$cogsgroupby.'';
					// echo $sql1; exit();
					$stmt=$link->prepare($sql1); $stmt->execute();
					$sql='select '.$sqlcogs.' '.$COGSActual.' \'\' as AccountID,\'COGS Budgeted at 75%\' as AccountDescription '.$yearcogs	.' from acctg_1yearsalestargets sa left join 1branches b on b.BranchNo=sa.branchno '.$cogsgroupby.'';
					// echo $sql; exit();
					include('../backendphp/layout/displayassubtable.php');
					
					//expenses per AccountID
					$sql='select '.$acctid.' ut.AccountID,ShortAcctID as AccountDescription '.$yearacct.' from AcctActual ut left join 1branches b on b.BranchNo=ut.branchno left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID left join AcctBudget bu on bu.AccountID=ut.AccountID left join AcctYearActual ya on ya.AccountID=ut.AccountID left join AcctYearBudget yb on yb.AccountID=bu.AccountID Group By AccountID';
					// echo $sql; exit();
					include('../backendphp/layout/displayassubtable.php');
					
					//total expenses
					$sql1='Create Temporary table total as select '.$acctidt.' ut.AccountID,ShortAcctID as AccountDescription '.$yearacctt.' from AcctActual ut left join 1branches b on b.BranchNo=ut.branchno left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID left join AcctBudget bu on bu.AccountID=ut.AccountID left join AcctYearActual ya on ya.AccountID=ut.AccountID left join AcctYearBudget yb on yb.AccountID=bu.AccountID Group By AccountID';
					$stmt=$link->prepare($sql1); $stmt->execute();
					$sql2='Create Temporary table TotalExpenses as select \'1\' as Con,\'\' as AccountID,\'Total Expenses\' as AccountDescription, '.$totalacctidt.' sum(YearBudget) as YearBudget,sum(YearActual) as YearActual from total';
					$stmt=$link->prepare($sql2); $stmt->execute();
					$sql='select \'\' as AccountID,\'Total Expenses\' as AccountDescription, '.$totalacctid.' format(sum(YearBudget),2) as YearBudget,format(sum(YearActual),2) as YearActual from total';
					// echo $sql; exit();
					include('../backendphp/layout/displayassubtable.php');
					
					//Gross Income
					$sql1='Create Temporary table GrossIncomeTotal as select \'1\' as Con,\'\' as AccountID,\'Gross Income\' as AccountDescription, '.$grossincomet.' st.YearBudget-ct.YearBudget as YearBudget,st.YearActual-ct.YearActual as YearActual from SalesTotal st left join COGStotal ct on ct.Con=st.Con';
					$stmt=$link->prepare($sql1); $stmt->execute();
					$sql='select \'\' as AccountID,\'Gross Income\' as AccountDescription, '.$grossincome.' format(st.YearBudget-ct.YearBudget,2) as YearBudget,format(st.YearActual-ct.YearActual,2) as YearActual from SalesTotal st left join COGStotal ct on ct.Con=st.Con';
					// echo $sql; exit();
					include('../backendphp/layout/displayassubtable.php');
					
					//Net Income
					$sql='select \'\' as AccountID,\'Net Income\' as AccountDescription, '.$netincome.' format(gi.YearBudget-te.YearBudget,2) as YearBudget,format(gi.YearActual-te.YearActual,2) as YearActual from TotalExpenses te left join GrossIncomeTotal gi on gi.Con=te.Con';
					// echo $sql; exit();
					include('../backendphp/layout/displayassubtable.php');
					
	
}
	
	break;
}


?>
