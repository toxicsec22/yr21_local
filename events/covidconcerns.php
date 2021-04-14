<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(array(2210,2211),'1rtc')) {   echo 'No permission'; exit;}
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
$showbranches=false;
include_once('../switchboard/contents.php');
$w=!isset($_GET['w'])?'Given13th':$_GET['w'];

// include_once('../backendphp/layout/linkstyle.php');
	
// 		echo '<br><div>';
// 				if (allowedToOpen(2210,'1rtc')) {
// 					echo '<a id=\'link\' href="covidconcerns.php?w=DOLECAMP">DOLE CAMP List of Employees</a> ';
// 				}
//                                 if (allowedToOpen(2211,'1rtc')) {
// 					echo '<a id=\'link\' href="covidconcerns.php?w=SubmitFebPayroll">Feb Payroll for CAMP</a> ';
// 				}
// 				if (allowedToOpen(2211,'1rtc')) {
//                                 echo '<a id=\'link\' href="covidconcerns.php?w=Given13th">13th month for Quarantine Leaves</a> ';}
                                
// 		echo '</div><br/>';

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';  


// if(in_array($w, array('DOLECAMP','SubmitFebPayroll'))){
// echo comboBox($link,'SELECT CompanyNo, CompanyName FROM 1companies WHERE CompanyNo<=5 UNION ALL SELECT "9" AS CompanyNo, "ALL" ORDER BY CompanyNo','CompanyNo','CompanyName','companylist');
//          $formdesc='<BR><font color="red"><b>Static Data as of 2020-03-23</b></font><BR><BR>'.'</i><form method="post" action="covidconcerns.php?w='.$w.'">
// 			Choose Company<input type="text" name="Company" list="companylist"></input>
// 			<input type="submit" name="submit"> 
// 			</form></br><i>';
//          if(isset($_POST['submit'])){
//              $formdesc.'<BR><BR>'.$_POST['Company'].'<BR>';
//              if($_POST['Company']=='ALL'){
//                  $condition=' ';
//              } else {
//                  $companyno=comboBoxValue($link, '1companies', 'CompanyName', $_POST['Company'], 'CompanyNo');
//                  $condition=' AND e.RCompanyNo='.$companyno;
//              }
//          }
// }

                
switch ($w){
//         case 'DOLECAMP':
// 	 if (!allowedToOpen(2210,'1rtc')) { echo 'No permission'; exit;}
         
				
				
//             $title='DOLE CAMP List of Employees';
//             $txnid='IDNo';
//             $sql='SELECT e.IDNo, CONCAT(e.Surname,", ",e.FirstName," ",IF(ISNULL(e.MiddleName) OR e.MiddleName LIKE " ","",CONCAT(LEFT(e.MiddleName,1),"."))) AS `Name of Worker`,
// TRUNCATE(((to_days(curdate()) - to_days(e.`Birthdate`)) / 365.25),0) AS `Age`, IF(Gender=1,"M","F") AS Sex,
// IFNULL(CONCAT(ei.Street_Present,", ",bot.BarangayOrTown,", ",cop.CityOrProvince),(StreetAddress)) AS `Home Address`, 
// ei.MobileNo AS `Contact Number`, Position AS `Designation`, IF(((to_days(now()) - to_days(`e`.`DateHired`)) / 365)>=0.5,"Regular","Probationary")`Employment Status`, IF(LatestDorM=0,"Per Day","Per Month") AS `Salary`

// FROM 1employees e JOIN 1_gamit.0idinfo id ON e.IDNo=id.IDNo
// LEFT JOIN 0employeeinfo ei ON ei.IDNo=e.IDNo LEFT JOIN 1_gamit.0cityorprovince cop ON ei.CPID_Present=cop.CPID LEFT JOIN 1_gamit.0barangayortown bot ON ei.BTID_Present=bot.BTID 
// JOIN `attend_30currentpositions` cp ON e.IDNo=cp.IDNo
// JOIN `payroll_20latestrates` lr ON e.IDNo=lr.IDNo
// WHERE e.IDNo IN (SELECT DISTINCT(IDNo) FROM attend_2attendance WHERE LeaveNo=22 AND DateToday<=\'2020-03-23\') AND (e.Resigned=0 OR  DateResigned>\'2020-03-31\')  '.$condition;
//             $columnnames=array('IDNo','Name of Worker','Age','Sex','Home Address','Contact Number','Designation','Employment Status','Salary');
//             include('../backendphp/layout/displayastablenosort.php');
//             break;
            
        case 'Given13th':
            if (!allowedToOpen(2211,'1rtc')) { echo 'No permission'; exit;}
			$columnnames=array('IDNo','FullName','Branch/Dept','MonthlyRate DuringCovid','Earned 13th','QDays Payroll8','Given Apr 25','QDays Payroll9','Given May 10','TotalGiven','Net Due');
			
            $title='13th month for Quarantine Leaves';
			$as='e';
			$formdesc='<br></i><h4>Current Employees</h4><i>';
            // Get salary rate during covid ecq            
            $sql0='CREATE TEMPORARY TABLE rateduringcovid AS SELECT r1.* FROM `payroll_22rates` r1 JOIN (select `IDNo`,max(`DateofChange`) AS `LatestDateofChange` from `payroll_22rates` WHERE ((`ApprovedByNo` is not null) and (`ApprovedByNo` <> 0) and (`DateofChange` <= \'2020-03-17\')) group by `IDNo` ) r2 ON r1.IDNo=r2.IDNo AND r1.DateofChange=r2.LatestDateofChange;'; 
            $stmt0=$link->prepare($sql0); $stmt0->execute();
			$sql1='SELECT pc.IDNo, (SELECT IF(DailyORMonthly=1,BasicRate,BasicRate*26.08) FROM rateduringcovid rc WHERE rc.IDNo=pc.IDNo) AS `MonthlyRateDuringCovid`,';
			$sql2='`FullName`,IF(deptid';
			$sql3=' IN (1,2,3,10),Branch,dept) AS `Branch/Dept`,(13thBasicCalc+13thTaxShCalc) AS `Earned13th`,  (SELECT QDays FROM `payroll_20fromattendance` att WHERE PayrollID=6 AND att.IDNo=';
			$sql4='.IDNo) AS `QDays Payroll8`,IFNULL((SELECT SUM(AdjustAmt) FROM `payroll_21paydayadjustments` pda WHERE AdjustTypeNo IN (21,22) AND pda.IDNo=pc.IDNo AND PayrollID=8),0) AS `Given_Apr25`, (SELECT QDays FROM `payroll_20fromattendance` att WHERE PayrollID=9 AND att.IDNo=';
			$sql5='.IDNo) AS `QDays Payroll9`,IFNULL((SELECT SUM(AdjustAmt) FROM `payroll_21paydayadjustments` pda WHERE AdjustTypeNo IN (21,22) AND pda.IDNo=pc.IDNo AND PayrollID=9),0) AS `Given_May10`,
TRUNCATE((SELECT Earned13th)-(SELECT Given_Apr25)-(SELECT Given_May10),2) AS `NetDue`,
(CASE WHEN (SELECT NetDue)<0 THEN (SELECT NetDue) END) AS Excess13th

FROM `payroll_26yrtotaland13thmonthcalc` pc';
			
            $sql0='CREATE TEMPORARY TABLE currentemp AS '.$sql1.$sql2.$sql3.$as.$sql4.$as.$sql5.' JOIN attend_30currentpositions e ON e.IDNo=pc.IDNo  HAVING  `Given_Apr25`<>0 OR `Given_May10`<>0';
            $stmt0=$link->prepare($sql0); $stmt0->execute();
            $sql10='SELECT *, FORMAT(`MonthlyRateDuringCovid`,2) AS `MonthlyRate DuringCovid`, FORMAT(`Earned13th`,2) AS `Earned 13th`, FORMAT(`Given_Apr25`,2) AS `Given Apr 25`, FORMAT(`Given_May10`,2) AS `Given May 10`,FORMAT(`Given_Apr25`+`Given_May10`,2) AS TotalGiven, FORMAT(`NetDue`,2) AS `Net Due` FROM ';
            $sql=$sql10.' currentemp'; //echo $sql;
            $coltototal='Excess13th'; $showgrandtotal=true; $totalstext='<font color="maroon">Grand total only shows overpayment of 13th month.  This value will decrease as people earn 13th month credits.</font>';
            include('../backendphp/layout/displayastable.php');
			
			 $title='';
			 $formdesc='</i><h4>Resigned Employees<h4><i>';
			 $as='lpir'; $sql2='CONCAT(FirstName," ",SurName) AS `FullName`,IF(dba.deptid';
            $sql0='CREATE TEMPORARY TABLE resignedemp AS '.$sql1.$sql2.$sql3.$as.$sql4.$as.$sql5.' JOIN attend_30latestpositionsinclresigned lpir ON lpir.IDNo=pc.IDNo JOIN attend_1defaultbranchassign dba ON lpir.IDNo=dba.IDNo JOIN 1employees e ON e.IDNo=lpir.IDno JOIN 1branches b ON dba.DefaultBranchAssignNo=b.BranchNo JOIN 1departments d ON dba.deptid=d.deptid WHERE lpir.Resigned=1 HAVING `Given_Apr25`<>0 OR `Given_May10`<>0;';
            $stmt0=$link->prepare($sql0); $stmt0->execute();
            $sql=$sql10.' resignedemp';
            
            include('../backendphp/layout/displayastable.php');
			
            break;
            
        // case 'SubmitFebPayroll':
        //     $title='Feb payroll for CAMP';
        //     $txnid='IDNo';
        //     $sql0='SELECT e.IDNo, CONCAT(e.Surname,", ",e.FirstName," ",IF(ISNULL(e.MiddleName) OR e.MiddleName LIKE " ","",CONCAT(LEFT(e.MiddleName,1),"."))) AS `Name of Worker`, Basic,OT,AbsenceBasic,UndertimeBasic,`SSS-EE`,`PhilHealth-EE`,WTax, (Basic+OT-AbsenceBasic-UndertimeBasic-`SSS-EE`-`PhilHealth-EE`-WTax) AS NetPay  FROM 1employees e JOIN `payroll_25payroll` p ON e.IDNo=p.IDNo JOIN 1_gamit.0idinfo id ON e.IDNo=id.IDNo WHERE e.IDNo IN (SELECT DISTINCT(IDNo) FROM attend_2attendance WHERE LeaveNo=22 AND DateToday<=\'2020-03-23\') AND (e.Resigned=0 OR DateResigned>\'2020-03-31\') ';
        //     $columnnames=array('IDNo','Name of Worker','Basic','OT','AbsenceBasic','UndertimeBasic','SSS-EE','PhilHealth-EE','WTax','NetPay');
            
        //     $subtitle='Feb 10';            
        //     $sql=$sql0.' AND PayrollID=3 '.$condition;
            
        //     include('../backendphp/layout/displayastable.php');
            
        //     $subtitle='Feb 25';            
        //     $sql=$sql0.' AND PayrollID=4 '.$condition;
            
        //     include('../backendphp/layout/displayastable.php');
        //     break;
}