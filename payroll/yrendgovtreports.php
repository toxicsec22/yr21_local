<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(8113,'1rtc')) { echo 'No permission'; exit; }
// if ($_GET['w']=='AddMain'){$showbranches=false;}else{
// $showbranches=true; }
$showbranches=false;
include_once('../switchboard/contents.php');
$which=!isset($_GET['w'])?'lists':$_GET['w'];
switch ($which){
	case 'lists':
	// $title='2316 FORM';
	 
  
	// $sql1='SELECT CompanyNo,`CompanyName` FROM 1companies;';
	// $sql2='select IDNo,CONCAT(SurName,\' \',FirstName,\' \',MiddleName) as FullName from 1employees e left join 1companies c on c.CompanyNo=e.RCompanyNo';
     // $groupby='CompanyNo'; $orderby=' order by SurName';
    // $columnnames1=array('CompanyName');
    // $columnnames2=array('IDNo','FullName');
	 // $txnid='IDNo';
	 // $addprocess='yrendgovtreports.php?w=lookup&IDNo=';
	 // $addprocesslabel='lookup';
    
    // include('../backendphp/layout/displayastablewithsub.php');
	
	?>
			<title>2316 Form</title>
			<style>
table {
  border-collapse: collapse;
  width: auto;
  font-size:10pt;
  border:1px solid black;
  background-color:white;
}

th, td {
  text-align: left;
  padding: 3px;
}

tr:nth-child(even) {background-color: #f2f2f2;}
			</style>
			
			<?php
						
	$sql='SELECT CompanyNo,`CompanyName` FROM 1companies where CompanyNo NOT IN(92,93,94)';
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	foreach($result as $res){
		echo'<table><tr><td>'.$res['CompanyName'].'</td></tr></table>';
		echo'<h4>RESIGNED</h4>';
		echo'<table><tr><td>IDNo</td><td>FullName</td><td></td></tr>';
		$sql1='select e.IDNo,CONCAT(SurName,\' \',FirstName,\' \',MiddleName) as FullName from 1employees e join 1companies c on c.CompanyNo=e.RCompanyNo where CompanyNo=\''.$res['CompanyNo'].'\' AND Resigned=1 Order By SurName ';
		// echo $sql1; exit();
		$stmt=$link->query($sql1); $result1=$stmt->fetchAll();
		$count1=0;
		foreach($result1 as $res1){
			echo'<tr><td>'.$res1['IDNo'].'</td><td>'.$res1['FullName'].'</td><td><a href="yrendgovtreports.php?w=lookup&IDNo='.$res1['IDNo'].'">lookup</a></td>';
		$count1++;	
			
		}
		echo'</tr></table>Number of Employees: <b>'.$count1.'</b></br>';
		
		echo'<h4>Min Wage Earner</h4>';
		echo'<table><tr><td>IDNo</td><td>FullName</td><td></td></tr>';
		$sql2='select e.IDNo,CONCAT(SurName,\' \',FirstName,\' \',MiddleName) as FullName from 1employees e join 1companies c on c.CompanyNo=e.RCompanyNo join payroll_25payroll  p on p.IDno=e.IDNo where CompanyNo=\''.$res['CompanyNo'].'\' AND e.Resigned=0 and WTax<=0 Group By p.IDNo Order By SurName';
		// echo $sql2; exit();
		$stmt=$link->query($sql2); $result2=$stmt->fetchAll();
		$count2=0;
		foreach($result2 as $res2){
			echo'<tr><td>'.$res2['IDNo'].'</td><td>'.$res2['FullName'].'</td><td><a href="yrendgovtreports.php?w=lookup&IDNo='.$res2['IDNo'].'">lookup</a></td>';
		$count2++;		
			
		}
		echo'</tr></table>Number of Employees: <b>'.$count2.'</b></br>';
		
		echo'<h4>Taxable</h4>';
		echo'<table><tr><td>IDNo</td><td>FullName</td><td></td></tr>';
		$sql3='select e.IDNo,CONCAT(SurName,\' \',FirstName,\' \',MiddleName) as FullName from 1employees e join payroll_25payroll  p on p.IDno=e.IDNo join 1companies c on c.CompanyNo=e.RCompanyNo where CompanyNo=\''.$res['CompanyNo'].'\' and WTax>0 Group By p.IDNo Order By SurName ';
		// echo $sql3; exit();
		$stmt=$link->query($sql3); $result3=$stmt->fetchAll();
		$count3=0;
		foreach($result3 as $res3){
			echo'<tr><td>'.$res3['IDNo'].'</td><td>'.$res3['FullName'].'</td><td><a href="yrendgovtreports.php?w=lookup&IDNo='.$res3['IDNo'].'">lookup</a></td>';
		$count3++;		
			
		}
		echo'</tr></table>Number of Employees: <b>'.$count3.'</b></br></br>';
				
	}
	
	
	break;
	
	case 'lookup':
	?>
			<style>
			table {
			  width: 17%;
			  font-size:9pt;
			  border:1px solid black;
			  background-color:white;
			}

			th, td {
			white-space: nowrap;
			  text-align: left;
			  padding: 3px;
			}

			tr:nth-child(even) {background-color: #f2f2f2;}
			</style>
			<?php
	$title='';
	$idno=intval($_GET['IDNo']);
	$sqlc='select Resigned from 1employees where IDNo=\''.$_GET['IDNo'].'\'';
	$stmt=$link->query($sqlc); $resultc=$stmt->fetch();
	$sql2='Select *, Concat(ii.SurName,\' | \',ii.FirstName,\' | \',ii.MiddleName) as `LastName FirstName MiddleName`,ii.SurName as LastName,CompanyName,Branch,ii.TIN AS TIN,CONCAT(ii.DateHired,\' | \', ifnull(ii.DateResigned,"")) as `EmploymentFrom EmploymentTo`,BasicDaily,BasicMonthly,format((TaxSalaries+'.(($resultc['Resigned']==1)?'ifnull(13thBasicCalc,0)':'ifnull(`13th`,0)').'),2) as `GROSS COMPENSATION INCOME`,'.(($resultc['Resigned']==1)?'ifnull(format(13thBasicCalc,2),0) as `13th`':'ifnull(`13th`,0) as `13th`').',format(DeM,2) as `DeMinimisRate`,format((TotalGovtDeduct),2) as `TOTAL SSS, PHIC & PAGIBIG`,format(((TaxSalaries+'.(($resultc['Resigned']==1)?'ifnull(13thBasicCalc,0)':'ifnull(`13th`,0)').')-'.(($resultc['Resigned']==1)?'ifnull(13thBasicCalc,0)':'ifnull(`13th`,0)').'-DeM-(TotalGovtDeduct)),2) as `SALARIES & OTHER FORMS OF COMPENSATION`,format(TaxWithheld,2) from yrgross yg left join 1employees e on e.IDNo=yg.IDNo left join 1_gamit.0idinfo ii on ii.IDNo=yg.IDNo left join 1companies c on c.CompanyNo=e.RCompanyNo left join attend_30currentpositions cp on cp.IDNo=yg.IDNo '.(($resultc['Resigned']==1)?'left join payroll_21dailyandmonthlyofresigned dam on dam.IDNo=yg.IDNo left join payroll_26yrtotaland13thmonthcalc ytt on ytt.IDNo=yg.IDNo ':' left join payroll_21dailyandmonthly dam on dam.IDNo=yg.IDNo ').'';

	include('tempdata/yrtotalssql.php');
	$sql2.=' where yg.IDNo='.$idno.''; 
	
	// echo $sql2; exit();
	$stmt=$link->query($sql2); $result=$stmt->fetch();
	?>
	<title><?php echo $result['LastName']; ?></title>
		<div style="background-color: #e6e6e6;
                        width: 750px;
                        border: 2px solid grey;
                        padding: 25px;
                        margin: 25px;">
						<b>FORMULA:</b></br>
						GROSS COMPENSATION INCOME = (Basic+OT+Diminimis-AbsenceBasic-UndertimeBasic+13thmonthbasic)</br>
						SALARIES & OTHER COMPENSATION = (Gross-13thmonthbasic-Diminimis-(SSS/PHIC/Pagibig))</br>
						</div>
	<?php
	$arrayinfo=array("LastName FirstName MiddleName","CompanyName","Branch","TIN","EmploymentFrom EmploymentTo","BasicDaily","BasicMonthly","GROSS COMPENSATION INCOME","13th","DeMinimisRate","TOTAL SSS, PHIC & PAGIBIG","SALARIES & OTHER FORMS OF COMPENSATION","TaxWithheld");
	foreach($arrayinfo as $info){
		echo '</br><table>
			<tr><th>'.$info.'</th></tr>
			<tr><td>'.$result[$info].'</td></tr>
			</table>';	
	}

	break;
	
}
?>