<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(5173,'1rtc')) { echo 'No permission'; exit();}
$showbranches=false;
include_once('../switchboard/contents.php');
include_once('../backendphp/layout/linkstyle.php');
$which=!isset($_GET['w'])?'BudgetPlanning':$_GET['w'];
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
//	positioncheker
	$sqlpck='select IDNo,PositionID,deptheadpositionid from attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].' ';
	// echo $sqlpck; exit();
	$stmtpck = $link->query($sqlpck); $resultpck = $stmtpck->fetch();
	
	if($resultpck['PositionID']!=$resultpck['deptheadpositionid'] and $resultpck['IDNo']!=1002){
		$join='join 1departments d on d.deptheadpositionid=cp.deptheadpositionid ';
	}else{
		$join='join 1departments d on d.deptheadpositionid=cp.PositionID';
	}
	
//
$sqldepartments='select GROUP_CONCAT(d.deptid) as deptid,PositionID,cp.deptheadpositionid from attend_30currentpositions cp '.$join.' WHERE IDNo='.$_SESSION['(ak0)'].'';
// echo $sqldepartments;
$stmtdepartments = $link->query($sqldepartments); $resultdepartments = $stmtdepartments->fetch();	

if(!isset($_REQUEST['department'])){
$sqld='select department,deptid,deptheadpositionid,PositionID from attend_30currentpositions where IDNo=\''.$_SESSION['(ak0)'].'\'';
}else{
$sqld='select department,deptid,deptheadpositionid from 1departments where deptid='.$_REQUEST['department'].'';	
}
$stmtd=$link->query($sqld); $resultd=$stmtd->fetch();

 if (allowedToOpen(5174,'1rtc')) {
	 $sqlt='create temporary table NewBranchWH as select \'New Branch/WH\' as department, \'-1\' as deptid, \'150\'  as deptheadpositionid ';
	 $stmtt=$link->prepare($sqlt); $stmtt->execute();
	$fcondition='';
	$unionbranchwh='UNION select * from NewBranchWH';
 }else{
	 $fcondition='where deptid in ('.$resultdepartments['deptid'].')';
	 $unionbranchwh='';
 }	 
 $acondition='where d.deptid in ('.$resultdepartments['deptid'].') and (Posted is null or Posted=0)';

echo comboBox($link,'SELECT  department, deptid, deptheadpositionid FROM `1departments` '.$fcondition.' '.$unionbranchwh.' ORDER BY department','department','deptid','departments');
echo comboBox($link,'SELECT department,d.deptid as deptid,deptheadpositionid FROM `1departments` d left join  budget_2budgetplanning bp on bp.deptid=d.deptid '.$acondition.' Group By d.deptid '.$unionbranchwh.' ORDER BY department','department','deptid','adepartments');  
echo comboBox($link,'SELECT ShortAcctID, AccountID FROM `acctg_1chartofaccounts` where Budgeted=1 ORDER BY ShortAcctID','AccountID','ShortAcctID','Budgeted');
echo comboBox($link,'SELECT PositionID, Position FROM `attend_0positions` ORDER BY Position','PositionID','Position','positions'); 
if (in_array($which,array('Add','EditProcess'))){ 
$accountid=comboBoxValue($link, 'acctg_1chartofaccounts', 'ShortAcctID', $_REQUEST['Account'], 'AccountID');	
$positionid=comboBoxValue($link, 'attend_0positions', 'Position', $_REQUEST['Position'], 'PositionID');	
}

switch($which){
case'BudgetPlanning':
$nextyr=$currentyr+1;
echo'<title>Budget Planning Module</title>';


echo'</br><h3>Filtering:</h3><div style="margin-top:0.5%; background-color:1b3d6d; padding:5px; color:white; width:max-content;">
<form method="post" action="budgetplanningmodule.php?w=BudgetPlanning">
Departments: <input type="text" name="department" list="departments">
<input type="submit" name="filter" value="Filter">
</form>
	</div>';

$condition1='and bp.AccountID<>965';

 if(isset($_REQUEST['department'])){
	 $sql='select department,deptid,deptheadpositionid from 1departments where deptid='.$_REQUEST['department'].' 
	 '.$unionbranchwh.' '.(allowedToOpen(5174,'1rtc')?'where deptid='.$_REQUEST['department'].'':'').'';
	 // echo $sql; exit();
	 $stmt=$link->query($sql); $result=$stmt->fetch();
 $entity=$result['department'];
 $condition=$_REQUEST['department'];
 $deptheadpositionid=$result['deptheadpositionid'];
 }else{
 $entity=$resultd['department'];
 $condition=$resultd['deptid'];
 $deptheadpositionid=$resultd['PositionID'];
 }
	$sqlc='select if(Posted=1,"Posted","Not Posted") as Posted,if(Approval=1,"Approved","Pending") as Approval from budget_2budgetplanning bp where deptid=\''.$condition.'\' Limit 1';
// echo $sqlc; exit();
$stmtc=$link->query($sqlc); $resultc=$stmtc->fetch();
	if($stmtc->rowCount()!=0){	
		if($resultc['Approval']=='Pending'){
			$approval='<p style="display:inline; color:red;">'.$resultc['Approval'].' Approval</p>';
		}else{
			$approval='<p style="display:inline; color:green;">'.$resultc['Approval'].'</p>';
		}
		
		$posted=$resultc['Posted'];
		
		if($posted=='Posted'){
			 if (allowedToOpen(5175,'1rtc')) {
		if($resultc['Approval']!='Approved'){
			$buttonapprove='<b><a OnClick="return confirm(\'Are you sure you want to Approve?\');" href="budgetplanningmodule.php?w=Approve&deptid='.$condition.'">APPROVE?</a></b>';
			$unpostedbutton='<b><a OnClick="return confirm(\'Are you sure you want to Unpost?\');" href="budgetplanningmodule.php?w=Unpost&deptid='.$condition.'">Unpost?</a></b>';
		}else{
			$unpostedbutton='';
			$buttonapprove='';
		}
			 }else{
				 $buttonapprove='';
				 $unpostedbutton='';
			}
		}else{
			$buttonapprove='';
			$unpostedbutton='';
		}
		
		 
	if($posted!='Posted' and $deptheadpositionid==$resultdepartments['PositionID']){
				$postedbutton='<b><a OnClick="return confirm(\'Are you sure you want to Post?\');" href="budgetplanningmodule.php?w=Posted&deptid='.$condition.'">POST?</a></b>';
	}else{
				  $postedbutton='';
			 }
		
	}else{
		$buttonapprove='';
		$postedbutton='';
		$approval='<p style="display:inline; color:red;">Pending Approval</p>';
		$posted='Not Posted';
		$unpostedbutton='';
	}

	//download
	$filename='File.csv';
	$sqlc='select * from budget_2budgetplanning bp where deptid=\''.$condition.'\' '.$condition1.'';
	// echo $sqlc; exit();
	$stmtc=$link->query($sqlc); $resultc=$stmtc->fetchAll();
	// echo $sql; exit();
	$exportdata='deptid,AccountID,Details,Q1,Q2,Q3,Q4'. PHP_EOL; //remove <br> when downloading
	$export='';
	foreach($resultc as $row){
   $export=$export.'"'.$row['deptid'].'","'.$row['AccountID'].'","'.$row['Details'].'","'.$row['Q1'].'","'.$row['Q2'].'","'.$row['Q3'].'","'.$row['Q4'].'"'. PHP_EOL;
	}
	$export=$exportdata.$export;	
	//
$sqlc='select Posted from budget_2budgetplanning where deptid=\''.$condition.'\' Limit 1';
// echo $sqlc; exit();
$stmtc=$link->query($sqlc); $resultc=$stmtc->fetch();
	if($stmtc->rowCount()!=0){
		$postedn=$resultc['Posted'];
	}else{
		$postedn=0;
	}
if($postedn==0){
	if($deptheadpositionid==$resultdepartments['PositionID']){
?>
</br><h3>For Accounts Only:</h3>
<div style="margin-top:0.5%; background-color:1b3d6d; padding:5px; color:white; width:max-content;">
	<form style="display: inline" action='../invty/downloadinvfilecsv.php' method='post'>
		<input type='hidden' name='csvfile' value='<?php echo $export; ?>'>
		<input type='hidden' name='filename' value='<?php echo $filename; ?>'>
		<input type='submit' name='download' value='Export'> Budget to update in SpreadSheet.
	</form>
	</br></br>
	<form method="post" action="budgetplanningmodule.php?w=Upload" enctype="multipart/form-data">
            Columns: deptid, AccountID,Details, Q1, Q2, Q3, Q4
		</br></br><input type="file" name="userfile" accept="csv/text" required>
		<input type="hidden" name="action_token" value="<?php echo $_SESSION['action_token']; ?>">
		<input type="hidden" name="deptid" value="<?php echo $condition; ?>">
       <input type="submit" name="upload" value="Upload" OnClick="return confirm('Are you sure you want to upload?');">  
	</form>
</div>
	</br>
<?php
}
}	
//start enablebasedonradio	
if($posted!='Posted' and ($deptheadpositionid==$resultdepartments['PositionID'] or $deptheadpositionid==$resultdepartments['deptheadpositionid'])){
			$radionamefield='radiolist';	
			echo '<h3 style="display:inline;">Encode Budget: </h3><form style="display:inline;" id="form-id">
			Accounts <input type="radio" id="watch-me1" name="'.$radionamefield.'"> '.str_repeat('&nbsp;',5).'
			Employee <input type="radio" id="watch-me2" name="'.$radionamefield.'">
			</form></br>';
$formaccounts='<div style="margin-top:0.5%; background-color:1b3d6d; padding:5px; color:white; width:max-content;"><form method="post" action="budgetplanningmodule.php?w=Add">
					Department: <input type="text" name="deptid" list="adepartments">
					Account: <input type="text" name="Account" list="Budgeted">
					Details: <input type="text" name="Details">
					Q1: <input type="text" name="Q1" size="10">
					Q2: <input type="text" name="Q2" size="10">
					Q3: <input type="text" name="Q3" size="10">
					Q4: <input type="text" name="Q4" size="10">
					<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
					<input type="submit" name="submit">
				</form></div>';
$formee='<div style="margin-top:0.5%; background-color:1b3d6d; padding:5px; color:white; width:max-content;"><form method="post" action="budgetplanningmodule.php?w=Add">
					Department: <input type="text" name="deptid" list="adepartments">
					Account: <input type="text" name="Account" list="accounts" value="Other Expenses" size="12" readonly>
					Position: <input type="text" name="Position" size="10" list="positions">
					Quarter: <input type="number" min="1" max="4" name="Quarter" size="1" >
					<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
					<input type="hidden" name="checker" value="1">
					<input type="submit" name="submit">
				</form></div>';
			
			//perbranch
			echo '<div id="show-me1" style="display:none">	
					'.$formaccounts.'
				</div>';
			
			//per company
			echo '<div id="show-me2" style="display:none">
					'.$formee.'
				</div>';			
			
			include $path.'/acrossyrs/commonfunctions/enablebasedonradio.php';	
}
//end enablebasedonradio

	

if($posted!='Posted' and ($deptheadpositionid==$resultdepartments['PositionID'] or $deptheadpositionid==$resultdepartments['deptheadpositionid'])){	
	$txnidname='TxnID';	
	$delprocess='budgetplanningmodule.php?w=Delete&department='.$condition.'&TxnID=';
	$editprocess='budgetplanningmodule.php?w=Edit&department='.$condition.'&TxnID=';
	$editprocesslabel='Edit';
}


//showhide	
	if(!isset($_POST['showhide'])){
		$showhidevalue=0;
		$showhidelabel='Show Encoded By and Timestamp';
	}
	if(isset($_POST['showhide'])){
		if($_POST['showhidevalue']==0){
			$showhidevalue=1;
			$showhidelabel='Hide Encoded By and Timestamp';
		}else{
			$showhidevalue=0;
			$showhidelabel='Show Encoded By and Timestamp';
			}
	}
				//

echo'</br>
<form method="post" action="budgetplanningmodule.php?w=BudgetPlanning&department='.$condition.'">
	<input type="hidden" name="showhidevalue" value="'.$showhidevalue.'">
	<input type="submit" name="showhide" value="'.$showhidelabel.'">
</form></br><div style="margin-left:15%;"><center><div style="background-color:#f2f2f2; padding:3px; margin-right:10%;"><h3 style="display:inline; float:left;">'.$entity.' Budget for  '.$nextyr.'</h3>'.str_repeat('&nbsp;',100).'  '.$approval.' '.str_repeat('&nbsp;',5).' '.$posted.' '.str_repeat('&nbsp;',5).' '.$unpostedbutton.' '.str_repeat('&nbsp;',5).' '.$buttonapprove.'  '.$postedbutton.'</div></center>';
		$title='';
		$formdesc='</i><b>Accounts</b>';
		
		$columnnames=array('AccountID','Account','Details','Q1','Q2','Q3','Q4','Total');
	if ($showhidevalue==1) {
	array_push($columnnames,'EncodedBy','TimeStamp');		
	}
		$total1='UNION ALL select \'zzz\' as ShortAcctID,bp.TxnID,\'\' as AccountID,format(sum(Q1),0) as Q1, format(sum(Q2),0) as Q2, format(sum(Q3),0) as Q3, format(sum(Q4),0) as Q4,\'\' as Account,\'TOTAL\' as Details,format((sum(Q1)+sum(Q2)+sum(Q3)+sum(Q4)),0) as Total,\'\' as EncodedBy,\'\' as TimeStamp from budget_2budgetplanning bp join acctg_1chartofaccounts ca on ca.AccountID=bp.AccountID where bp.deptid=\''.$condition.'\' '.$condition1.'';
		
		
		$sql='select ShortAcctID,bp.TxnID,bp.AccountID,format(Q1,0) as Q1,format(Q2,0) as Q2,format(Q3,0) as Q3,format(Q4,0) as Q4,ShortAcctID as Account,Details,format((Q1+Q2+Q3+Q4),0) as Total, CONCAT(Nickname,\' \',SurName) as EncodedBy, bp.TimeStamp from budget_2budgetplanning bp join acctg_1chartofaccounts ca on ca.AccountID=bp.AccountID left join 1employees e on e.IDNo=bp.EncodedByNo where bp.deptid=\''.$condition.'\' '.$condition1.'

		'.$total1.' 
		Order By ShortAcctID ';
		
		$hidecount=1;

		include('../backendphp/layout/displayastablenosort.php');
/////////////////////////////////////////////////////////////////Employee/////////////////////////	 

if($posted!='Posted' and ($deptheadpositionid==$resultdepartments['PositionID'] or $deptheadpositionid==$resultdepartments['deptheadpositionid'])){	
	$txnidname='TxnID';	
	$delprocess='budgetplanningmodule.php?w=Delete&department='.$condition.'&checker=1&TxnID=';
	$editprocess='budgetplanningmodule.php?w=Edit&department='.$condition.'&checker=1&TxnID=';
	$editprocesslabel='Edit';
}
 
		// $sqls='select max(DateEffective),TotalMinWage,TimeStamp from `1_gamit`.`payroll_4wageorders` where MinWageAreaID=\'1\' ';
		// $stmts=$link->query($sqls); $results=$stmts->fetch();
		// $minwage=$results['TotalMinWage']; $daysofmonth=26.08; 
//formula
if (allowedToOpen(5174,'1rtc')) {		
			$sqld='select * from budget_2budgetplanning bp  where bp.deptid=\''.$condition.'\' and bp.AccountID=\'965\'';
			$stmtd=$link->query($sqld);
			if($stmtd->rowCount()!=0){
echo'</br><div style="background-color:#f2f2f2; width:710px; border: 1px solid #404040; padding:5px;" >
	<h3>Formula:</h3></br>
	<i>Note: For Existing Employee there is no one time expenses and hiring rate will become monthly salary.</i></br></br>
	<b>Salaries</b> = (Hiring Rate+SSS+PhilHealth+Pag-IBIG) * 3 </br>
	<b>One time expenses</b> = Laptop(25,000)+Mobile(5,000)+HMO(700)</br>
	<b>Plan</b> = (3600 / 4)</br>
	<b>13th</b> = (Hiring Rate / 4)</br>
	<b>SIL</b> = (Daily Salary * 5) / 4</br>
	<b>Performance Bonus</b> = (1 month salary / 4)</br>
	<b>Quarter</b> = Salaries + Plan + 13th + SIL + Performance Bonus </br>
	<b>Quarter that is equal to QuarterHired</b> = Salaries + Plan + 13th + One time expenses + SIL + Performance Bonus
	</div>';
			}
}
		
$plan=3600;
$onetimeexpenses='30700';

//existing employee
$bonussil='';
$quartercomputation='(((sum(TotalMonthly)+sum(`SSS-EE`)+sum(`Philhealth-EE`)+sum(`PagIbig-EE`))*3)+(('.$plan.'*count(IDNo))/4)+(sum(TotalMonthly)/4)+((sum(BasicDaily)*5)/4)+(sum(TotalMonthly)/4))';

//new employee
$quarterhiredcomputation='(((MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100))+(select SSER+ECER+MPFER from payroll_0ssstable where (MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100)) BETWEEN RangeMin AND RangeMax)+(SELECT if(`monthlybasic`<=MinBasic,MinPremium,if(`monthlybasic` < MaxBasic,(`monthlybasic`*PremiumRate/100),MaxPremium))/2 FROM payroll_0phicrate WHERE ApplicableYear='.$currentyr.')+\'100\')*3+('.$plan.'/4)+'.$onetimeexpenses.'+((MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100))/4))
+(((MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100))/26.08)*5)/4+((MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100))/4)';

$quartercomputationne='(((MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100))+(select SSER+ECER+MPFER from payroll_0ssstable where (MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100)) BETWEEN RangeMin AND RangeMax)+(SELECT if(`monthlybasic`<=MinBasic,MinPremium,if(`monthlybasic` < MaxBasic,(`monthlybasic`*PremiumRate/100),MaxPremium))/2 FROM payroll_0phicrate WHERE ApplicableYear='.$currentyr.')+\'100\')*3+('.$plan.'/4)+((MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100))/4))+(((MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100))/26.08)*5)/4+((MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100))/4)';
	
		$title='';
		$formdesc='</i><b>Employee</b>';
if (allowedToOpen(5174,'1rtc')) {
$union='UNION All select Branch,\'\' as TxnID,\'\' as JobLevelNo,\'\' as Account,\'\' as QuarterHired,if(dam.deptid=10,Branch,"Existing Employee") as Position,\'\' as `Hiring Rate`,\'\' as monthlybasic,\'\' as `Pag-IBIG`,\'\' as Laptop,\'\' as Mobile,\'\' as Plan,\'\' as PhilHealth,\'\' as QuarterHired,
'.$quartercomputation.' as Q1, '.$quartercomputation.' as Q2, 
'.$quartercomputation.' as Q3, '.$quartercomputation.' as Q4, \'\' as EncodedBy , \'\' as TimeStamp



from payroll_21dailyandmonthly dam left join attend_0positions p on p.PositionID=dam.PositionID left join 1branches b on b.BranchNo=dam.BranchNo where dam.deptid=\''.$condition.'\' Group by if(dam.deptid=10,dam.BranchNo,"") Order By Branch';
$union2='UNION ALL select TxnID,\'\' as Position,\'\' as JobLevelNo,\'TOTAL\' QuarterHired,format(sum(floor(Q1)),0) as Q1,format(sum(floor(Q2)),0) as Q2,format(sum(floor(Q3)),0) as Q3,format(sum(floor(Q4)),0) as Q4,format((sum(floor(Q1))+sum(floor(Q2))+sum(floor(Q3))+sum(floor(Q4))),0) as Total, \'\' as EncodedBy, \'\' as TimeStamp from EmployeeExpenses';
}
else{
	$union='';
	$union2='';
}		
		$sqlee='Create temporary table EmployeeExpenses select \'\' as Branch,bp.TxnID,p.JobLevelNo,ShortAcctID as Account,substring(Details,LOCATE(\'-\', Details)+1,100) as QuarterHired,Position,(MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100)) as `Hiring Rate` ,(MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100)) as `monthlybasic` ,(select SSER+ECER+MPFER from payroll_0ssstable where `monthlybasic` BETWEEN RangeMin AND RangeMax) as SSS,\'100\' as `Pag-IBIG`,\'25,000\' as Laptop,\'5,000\' as Mobile,\''.$plan.'\' as Plan,(SELECT if(`monthlybasic`<=MinBasic,MinPremium,if(`monthlybasic` < MaxBasic,(`monthlybasic`*PremiumRate/100),MaxPremium))/2 FROM payroll_0phicrate WHERE ApplicableYear='.$currentyr.') as PhilHealth,

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
		
		,"")) as Q4, CONCAT(Nickname,\' \',SurName) as EncodedBy, bp.TimeStamp
		
		
		from budget_2budgetplanning bp join acctg_1chartofaccounts ca on ca.AccountID=bp.AccountID  join attend_0positions p on p.PositionID=SUBSTRING_INDEX(bp.Details,\'-\',\'1\') join attend_1joblevel jl on jl.JobLevelNo=p.JobLevelNo left join 1employees e on e.IDNo=bp.EncodedByNo  where bp.deptid=\''.$condition.'\' and bp.AccountID=\'965\' 
		
		'.$union.'
		';
		// echo $sqlee; exit();
		$stmtee=$link->prepare($sqlee); $stmtee->execute();
		
		$sql='select TxnID,Position,JobLevelNo,QuarterHired,format(floor(Q1),0) as Q1,format(floor(Q2),0) as Q2,format(floor(Q3),0) as Q3,format(floor(Q4),0) as Q4,format((floor(Q1)+floor(Q2)+floor(Q3)+floor(Q4)),0) as Total, EncodedBy, TimeStamp from EmployeeExpenses
		
			'.$union2.'';
		
		// echo $sql; exit();
		if (allowedToOpen(5174,'1rtc')) {
			$columnnames=array('Position','JobLevelNo','QuarterHired','Q1','Q2','Q3','Q4','Total');
	if ($showhidevalue==1) {
	array_push($columnnames,'EncodedBy','TimeStamp');		
	}
		}else{
			$columnnames=array('QuarterHired','Position','JobLevelNo');
		}
		include('../backendphp/layout/displayastablenosort.php');	
		 
break;

case'Add':

	//postedchecker
		$sqlpc='select Posted from budget_2budgetplanning where deptid='.$_POST['deptid'].' limit 1';
		// echo $sqlpc; exit();
		$stmtpc=$link->query($sqlpc); $resultpc=$stmtpc->fetch();
		if($resultpc['Posted']==1){
			echo '<b>The Budget is Posted. Cannot be edited.</b>'; exit();
		}
	//

require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
if(!isset($_POST['checker'])){
	$sql='insert into budget_2budgetplanning set deptid=\''.$_POST['deptid'].'\',AccountID=\''.$accountid.'\',Q1=\''.$_POST['Q1'].'\',Q2=\''.$_POST['Q2'].'\',Q3=\''.$_POST['Q3'].'\',Q4=\''.$_POST['Q4'].'\',Details=\''.$_POST['Details'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
}else{
	$sql='insert into budget_2budgetplanning set deptid=\''.$_POST['deptid'].'\',AccountID=\''.$accountid.'\',Details=\''.$positionid.'-'.$_POST['Quarter'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
}
header('Location:budgetplanningmodule.php?w=BudgetPlanning&department='.$_POST['deptid'].'');
break;

case'Edit':
$txnid=intval($_GET['TxnID']);

if(!isset($_GET['checker'])){
	$sql='select bp.*,ShortAcctID as Account from budget_2budgetplanning bp join acctg_1chartofaccounts ca on ca.AccountID=bp.AccountID where TxnID=\''.$txnid.'\'';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	echo'<h3>Edit?</h3></br><form method="post" action="budgetplanningmodule.php?w=EditProcess&department='.$_GET['department'].'&TxnID='.$txnid.'">
		Account: <input type="text" name="Account" value="'.$result['Account'].'" list="Budgeted">
		Details: <input type="text" name="Details" value="'.$result['Details'].'" >
		Q1: <input type="text" name="Q1" value="'.$result['Q1'].'">
		Q2: <input type="text" name="Q2" value="'.$result['Q2'].'">
		Q3: <input type="text" name="Q3" value="'.$result['Q3'].'">
		Q4: <input type="text" name="Q4" value="'.$result['Q4'].'">
		<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		<input OnClick="return confirm(\'Are you sure you want to submit?\');" type="submit" name="submit">';
}else{
	$sql='select bp.*,p.JobLevelNo,ShortAcctID as Account,substring(Details,LOCATE(\'-\', Details)+1,100) as QuarterHired,Position from budget_2budgetplanning bp join acctg_1chartofaccounts ca on ca.AccountID=bp.AccountID join attend_0positions p on p.PositionID=SUBSTRING_INDEX(bp.Details,\'-\',\'1\') join attend_1joblevel jg on jg.JobLevelNo=p.JobLevelNo where TxnID=\''.$txnid.'\'';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	echo'<h3>Edit?</h3></br><form method="post" action="budgetplanningmodule.php?w=EditProcess&department='.$_GET['department'].'&TxnID='.$txnid.'">
		Account: <input type="text" name="Account" value="'.$result['Account'].'" readonly>
		Position: <input type="text" name="Position" value="'.$result['Position'].'" list="positions">
		Quarter: <input type="number" min="1" max="4" name="Quarter" value="'.$result['QuarterHired'].'" size="1">
		<input type="hidden" name="checker" value="'.$_GET['checker'].'">
		<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		<input OnClick="return confirm(\'Are you sure you want to submit?\');" type="submit" name="submit">';
	
}
break;

case'EditProcess':
require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
$txnid=intval($_GET['TxnID']);
if(!isset($_POST['checker'])){
	$sql='update budget_2budgetplanning set AccountID=\''.$accountid.'\', Q1=\''.$_POST['Q1'].'\',Q2=\''.$_POST['Q2'].'\',Q3=\''.$_POST['Q3'].'\',Q4=\''.$_POST['Q4'].'\',Details=\''.$_POST['Details'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() where TxnID=\''.$txnid.'\' and Posted=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
}else{
	$sql='update budget_2budgetplanning set AccountID=\''.$accountid.'\',Details=\''.$positionid.'-'.$_POST['Quarter'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() where TxnID=\''.$txnid.'\' and Posted=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	
}
header('Location:budgetplanningmodule.php?w=BudgetPlanning&department='.$_GET['department'].'');
break;

case'Delete':
$txnid=intval($_GET['TxnID']);
	$sql='delete from budget_2budgetplanning where TxnID=\''.$txnid.'\' and Posted=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
header('Location:budgetplanningmodule.php?w=BudgetPlanning&department='.$_GET['department'].'');
break;

case'Upload':
if(isset($_POST['upload'])){	
	$sqld='delete from budget_2budgetplanning where deptid=\''.$_POST['deptid'].'\' and AccountID<>965';
	$stmtd=$link->prepare($sqld); $stmtd->execute();
	
        $tblname='budget_2budgetplanning'; $firstcolumnname='deptid'; 
        $DOWNLOAD_DIR="../../uploads/";  $requireencodedby=true; $requiredts=true;

        include '../backendphp/layout/uploaddatanoheader.php';
}
        header('Location:budgetplanningmodule.php?w=BudgetPlanning&department='.$_POST['deptid'].'');
		
break;

case'Approve':
$deptid=intval($_GET['deptid']);
	$sql='update budget_2budgetplanning set Approval=1, ApprovedByNo=\''.$_SESSION['(ak0)'].'\',ApprovedTS=Now() where deptid=\''.$deptid.'\' and Posted=1';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
header("Location:budgetplanningmodule.php?w=BudgetPlanning&filter=1&department=".$deptid."");
break;

case'Posted':
$deptid=intval($_GET['deptid']);
	$sql='update budget_2budgetplanning set Posted=1, PostedByNo=\''.$_SESSION['(ak0)'].'\',PostedTS=Now() where deptid=\''.$deptid.'\' and Posted=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
header('Location:budgetplanningmodule.php?w=BudgetPlanning&department='.$deptid.'');
break;

case'Unpost':
$deptid=intval($_GET['deptid']);
	$sql='update budget_2budgetplanning set Posted=0, PostedByNo=\''.$_SESSION['(ak0)'].'\',PostedTS=Now() where deptid=\''.$deptid.'\'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
header('Location:budgetplanningmodule.php?w=BudgetPlanning&department='.$deptid.'');
break;


}