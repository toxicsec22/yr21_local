<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(8114,'1rtc')) { echo 'No permission'; exit();}
$showbranches=false;
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT CompanyNo, CompanyName FROM 1companies where CompanyNo NOT IN(92,93,94) Order By CompanyName','CompanyNo','CompanyName','companylist');
$which=(!isset($_GET['w'])?'Export':$_GET['w']);
switch ($which){
	case'Export':
	echo'<title>Export 2316</title></br><h3>Export 2316</h3></br>
	<form method="post" action="exportbirinfo.php">
	Company: &nbsp;<input type="text" name="Company" list="companylist">
	<input type="submit" name="Lookup" value="Lookup">
	</form>';
	if(isset($_REQUEST['Lookup'])){
$companyno=companyandbranchValue($link,'1companies','CompanyName',addslashes($_REQUEST['Company']),'CompanyNo');
// echo $companyno; exit();
include('../payroll/tempdata/yrtotalssql.php');	
$filename=''.$companyno.'85485600000123120201604C.DAT';
	$sql='Select *, Concat(ii.SurName,\' | \',ii.FirstName,\' | \',ii.MiddleName) as `LastName FirstName MiddleName`,upper(ii.SurName) as LastName,upper(ii.FirstName) as FirstName,upper(ii.MiddleName) as MiddleName,CompanyName,b.Branch,
	
	case 
		when locate("IV A",RegionMinWageArea) then "IV-A"
		when locate("IV-A",RegionMinWageArea) then "IV-A"
		when locate("X-",RegionMinWageArea) then "X"
		when locate("VII ",RegionMinWageArea) then "VII"	
		else RegionMinWageArea
	end as RegionMinWageArea
	
	,replace(ii.TIN,"-","") AS TIN,CONCAT(ii.DateHired,\' | \', ifnull(ii.DateResigned,"")) as `EmploymentFrom EmploymentTo`, if(ii.DateHired<\''.$currentyr.'-01-01\',"01/01/'.$currentyr.'",DATE_FORMAT(ii.DateHired, \'%m/%d/%Y\')) as EmploymentFrom,if(ii.DateResigned is null, \'12/31/'.$currentyr.'\',DATE_FORMAT(ii.DateResigned, \'%m/%d/%Y\')) as EmploymentTo, \'12/31/'.$currentyr.'\' as DateToday,if(Resigned=1,damr.BasicDaily,dam.BasicDaily) as BasicDaily,if(Resigned=1,damr.BasicMonthly,dam.BasicMonthly) as BasicMonthly,(TaxSalaries+NonTaxSalaries+if(Resigned=1,ifnull(13thBasicCalc,0),ifnull(`13th`,0))) as `GROSS COMPENSATION INCOME`,if(Resigned=1,ifnull(13thBasicCalc,0),ifnull(`13th`,0)) as `13th`,DeM as `DeMinimisRate`,(TotalGovtDeduct) as `TOTAL SSS, PHIC & PAGIBIG`,((TaxSalaries+NonTaxSalaries+
	if(Resigned=1,ifnull(13thBasicCalc,0),ifnull(`13th`,0)))-
	if(Resigned=1,ifnull(13thBasicCalc,0),ifnull(`13th`,0))-DeM-(TotalGovtDeduct)) as `SALARIES & OTHER FORMS OF COMPENSATION`,TaxWithheld,if(Nationality=0,"FILIPINO","") as Nationality,case when EmpStatus=0 then "P" when EmpStatus=1 then "R" end as EmpStatus,Resigned 
	FROM yrgross yg left join 1employees e on e.IDNo=yg.IDNo left join 1_gamit.0idinfo ii on ii.IDNo=yg.IDNo left join 1companies c on c.CompanyNo=e.RCompanyNo left join attend_30currentpositions cp on cp.IDNo=yg.IDNo left join 1branches b on b.BranchNo=cp.BranchNo left join 1_gamit.payroll_0regionsminwageareas rmwa on rmwa.MinWageAreaID=b.EffectiveMinWageAreaID left join payroll_21dailyandmonthlyofresigned damr on damr.IDNo=yg.IDNo left join payroll_26yrtotaland13thmonthcalc ytt on ytt.IDNo=yg.IDNo left join payroll_21dailyandmonthly dam on dam.IDNo=yg.IDNo where if(Resigned=1, c.CompanyNo=\''.$companyno.'\', c.CompanyNo=\''.$companyno.'\' and cp.PositionID not in (select deptheadpositionid from 1departments Group By deptheadpositionid)) Order By LastName';	 
	$stmt=$link->query($sql);
	$result=$stmt->fetchAll();
	// echo $sql; exit();
	$date='12/31/'.$currentyr.'';
	$exportdata='H1604C,8548560,0,'.$date.',N,0'. PHP_EOL; //remove <br> when downloading
	$export='';
	$c1=1;
	$c2=1;
	foreach($result as $row){
	
		if($row['SALARIES & OTHER FORMS OF COMPENSATION']<250000){
//schedule 2			
	$export=$export.'"D2","1604C","8548560","0","'.$row['DateToday'].'","'.$c2.'","'.$row['TIN'].'","0","'.$row['LastName'].'","'.$row['FirstName'].'","'.$row['MiddleName'].'","'.$row['RegionMinWageArea'].'","0","0","0","0","0","0","0","0","0","0","0","0","0","0","'.$row['EmploymentFrom'].'","'.$row['EmploymentTo'].'","'.$row['GROSS COMPENSATION INCOME'].'","'.$row['BasicDaily'].'","'.$row['BasicMonthly'].'","'.($row['BasicMonthly']*12).'","313","0","0","0","0","'.$row['13th'].'","'.$row['DeMinimisRate'].'","'.$row['TOTAL SSS, PHIC & PAGIBIG'].'","'.$row['SALARIES & OTHER FORMS OF COMPENSATION'].'","'.$row['GROSS COMPENSATION INCOME'].'","0","0","0","0","0","0","0","0","0","0","0","'.$row['Nationality'].'","'.$row['EmpStatus'].'"'. PHP_EOL;
   $c2++;
		}else{
//schedule 1			
	$taxdue=($row['SALARIES & OTHER FORMS OF COMPENSATION']-250000)*.2;
				
   $export=$export.'"D1","1604C","8548560","0","'.$row['DateToday'].'","'.$c1.'","'.$row['TIN'].'","0","'.$row['LastName'].'","'.$row['FirstName'].'","'.$row['MiddleName'].'","'.$row['RegionMinWageArea'].'","0","0","0","0","0","0","0","0","0","0","0","'.$row['EmploymentFrom'].'","'.$row['EmploymentTo'].'","0","0","'.$row['13th'].'","'.$row['DeMinimisRate'].'","'.$row['TOTAL SSS, PHIC & PAGIBIG'].'","0","0","'.$row['SALARIES & OTHER FORMS OF COMPENSATION'].'","0","0","'.$row['SALARIES & OTHER FORMS OF COMPENSATION'].'","'.$row['SALARIES & OTHER FORMS OF COMPENSATION'].'","'.$row['SALARIES & OTHER FORMS OF COMPENSATION'].'","'.$taxdue.'","0","0","'.$taxdue.'","0","'.$taxdue.'","'.$row['Nationality'].'","'.$row['EmpStatus'].'"'. PHP_EOL;
   $c1++;
	   
		}
	}
	$export=$exportdata.$export;
?>	
	</br><form style="display: inline" action='../invty/downloadinvfilecsv.php' method='post'>
		<input type='submit' name='download' value='Export'>
		<input type='hidden' name='csvfile' value='<?php echo $export; ?>'>
		<input type='hidden' name='filename' value='<?php echo $filename; ?>'>
	</form>
<?php
echo' <b>'.$_REQUEST['Company'].'</b></br></br>';
}
break;
}
?>