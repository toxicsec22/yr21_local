<?php
if(session_id()==''){
	session_start();
} 
if(!isset($_SESSION['&pos'])){ $_SESSION['&pos']='-1';} //echo $_SESSION['&pos'].' '.$_SESSION['(ak0)'];
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 
$showbranches=false; include_once('../switchboard/contents.php');
 
    
    
    $fieldname='payperiod';
    $listname='payperiods';
    $listsql='SELECT PayrollID, concat(PayrollID, " : ", FromDate, " - ", ToDate) as PayPeriod FROM payroll_1paydates;';
    $listvalue='PayrollID';
    $listlabel='PayPeriod';
    $listcaption='For Payroll Period';
    $fieldname2='';
    $listname2='';
    $listvalue2='';
    $listlabel2='';
    $listcaption2='';
    $calledfrom=7;
 
    if ($_SESSION['(ak0)']==1002){
        error_reporting(E_ALL);
	ini_set('display_errors', 1);
}
     $whichqry=$_GET['qry'];
	 
if (in_array($whichqry,array('summary_for_payroll','my_attendance'))){
			$_POST['payrollid']=(isset($_POST['payrollid'])?$_POST['payrollid']:((date('m')*2)+(date('d')<15?-1:0)));
             include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
             echo comboBox($link,$listsql,$listlabel,$listvalue,'payperiods');
}			 
    

     switch ($whichqry){
      case 'attend_dates':
          if (!allowedToOpen(631,'1rtc')){ echo 'No permission'; exit;}
		  
		  
		  $title='Attendance Dates';
			$sql1='Select CONCAT ("PayrollID: ",PayrollID) AS `PayrollIDColumn`,PayrollID  from attend_2attendancedates Group By PayrollID';
            $sql2='SELECT d.*,t.TypeofDayName FROM attend_2attendancedates d join `attend_0typeofday` t on t.TypeOfDayNo=d.TypeofDayNo';
			$groupby='PayrollID';
			$orderby='order by DateToday';
			$txnid='TxnID';
			$columnnames1=array('PayrollIDColumn');
            $columnnames2=array('DateToday', 'TypeofDayName','RemarksOnDates','Posted');
	    $editprocess='editspecifics.php?w=attend_dates&edit=2&TxnID=';$editprocesslabel='Edit';
		$addlfield='Posted';
		  if (allowedToOpen(617,'1rtc')){ 
		  $addprocess1='tocheckattendance.php?qry=unpost&TxnID=';
		  $addprocesslabel1='Unpost';
		  }
		  if (allowedToOpen(615,'1rtc')){ 
		  $addprocess2='tocheckattendance.php?qry=post&TxnID=';
		  $addprocesslabel2='Post';
		  }
            include('../backendphp/layout/displayastablewithsub.php');
		  
		  
		  
            // $title='Attendance Dates';
            // $sql='SELECT d.*,t.TypeofDayName FROM attend_2attendancedates d join `attend_0typeofday` t on t.TypeOfDayNo=d.TypeofDayNo order by DateToday;';
            // $orderby='';
	    // $txnid='TxnID';
            // $columnnames=array('PayrollID','DateToday', 'TypeofDayName','RemarksOnDates','CheckDateBefore','CheckDateAfter','Posted');
	    // $editprocess='editspecifics.php?w=attend_dates&edit=2&TxnID=';$editprocesslabel='Edit';
            // include('../backendphp/layout/displayastablewithedit.php');
            break;
			
		case'unpost':
		$txnid=intval($_GET['TxnID']);
		$sql='update attend_2attendancedates set Posted=0,PostedEncBy=\''.$_SESSION['(ak0)'].'\',PostedTS=Now() where TxnID=\''.$txnid.'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:tocheckattendance.php?qry=attend_dates");

		break;
		
		case'post':
		$txnid=intval($_GET['TxnID']);
		$sql='update attend_2attendancedates set Posted=1,PostedEncBy=\''.$_SESSION['(ak0)'].'\',PostedTS=Now() where TxnID=\''.$txnid.'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:tocheckattendance.php?qry=attend_dates");

		break;
			
       case 'time_in_after_8':
            if (!allowedToOpen(636,'1rtc')){ echo 'No permission'; exit;}
            $title='Time In After 8';
            $pagetouse='tocheckattendance.php?calledfrom=7&qry=time_in_after_8';
            //$pagetouse='../backendphp/layout/displayastablewithdatecondition.php?calledfrom=7';
            $sql='SELECT `a`.*, concat(FirstName," ",SurName) as `FullName` FROM `attend_45lookupattend` a JOIN `1employees` e ON `e`.IDNo=`a`.IDNo WHERE ((STR_TO_DATE(`TimeIn`,\'%l:%i %p\'))>\'08:00:59\') ';
            $orderby='DateToday, FullName';    
            $columnnames=array('TxnID','DateToday', 'IDNo', 'FullName','TimeIn','TimeOut','RemarksDept','RemarksHR','Overtime','LeaveName', 'Branch');
            include('../backendphp/layout/displayastablewithdatecondition.php');
            break;
        case 'time_out_before_5':
            if (!allowedToOpen(637,'1rtc')){ echo 'No permission'; exit;}
            $title='Time Out Before 5';
            $pagetouse='tocheckattendance.php?calledfrom=7&qry=time_out_before_5';
            $sql='SELECT `a`.*, CONCAT(FirstName," ",SurName) as `FullName` FROM `attend_45lookupattend` a JOIN `1employees` e ON `e`.IDNo=`a`.IDNo WHERE  ((STR_TO_DATE(`TimeOut`,\'%l:%i %p\'))<(IF(DAYOFWEEK(DateToday)=7,IF(WithSat=2,\'17:00:00\',\'12:00:00\'),\'17:00:00\')))';
            $orderby='DateToday, FullName';    
            $columnnames=array('TxnID','DateToday', 'IDNo', 'FullName','TimeIn','TimeOut','RemarksDept','RemarksHR','Overtime','LeaveName', 'Branch');
            include('../backendphp/layout/displayastablewithdatecondition.php');
            break;
        case 'check_overtime': 
            if (!allowedToOpen(632,'1rtc')){ echo 'No permission'; exit;}
            $title='Approved Overtime';
            $pagetouse='tocheckattendance.php?calledfrom=7&qry=check_overtime';
            $sql='SELECT `a`.*, concat(FirstName," ",SurName) as `FullName` FROM `attend_45lookupattend` a JOIN `1employees` e ON `e`.IDNo=`a`.IDNo WHERE 
 `Overtime`<>0';
            $orderby='DateToday, FullName';    
            $columnnames=array('TxnID','DateToday', 'IDNo', 'FullName','TimeIn','TimeOut','RemarksDept','RemarksHR','Overtime','LeaveName', 'Branch');
            include('../backendphp/layout/displayastablewithdatecondition.php');
            break;
        case 'attendance_per_payperiod':
            if (!allowedToOpen(630,'1rtc')){ echo 'No permission'; exit;}
            $title='Attendance Per Payroll Period';
            $pagetouse='tocheckattendance.php?calledfrom=7&qry=attendance_per_payperiod';
            $sql='SELECT `attend_45lookupattend`.*, concat(FirstName,\' \',SurName) as `FullName` FROM attend_45lookupattend inner join `1employees` on `1employees`.IDNo=`attend_45lookupattend`.IDNo WHERE ((`attend_45lookupattend`.Posted)<>\'0\')';
            $orderby='DateToday, FullName';    
            $columnnames=array('TxnID','DateToday', 'IDNo', 'FullName','TimeIn','TimeOut','RemarksDept','RemarksHR','Overtime','LeaveName', 'Branch');
            include('../backendphp/layout/displayastablewithdatecondition.php');
            break;
        case 'summary_for_payroll':
            if (!allowedToOpen(635,'1rtc')){ echo 'No permission'; exit;}
				
				$title='Summary For Payroll';
				echo '<title>'.$title.'</title>';
				echo '<br><h3>'.$title.'</h3>';
				
				echo '<form action="tocheckattendance.php?calledfrom=7&qry=summary_for_payroll" method="POST">payperiod: <input type="text" size="5" name="payrollid" list="payperiods"><input type="submit" name="btnSubmit" value="Lookup"/></form>';
			
				
				$title='';
				$formdesc='</i>payperiod: '.$_POST['payrollid'].'<i>';
				$pagetouse='tocheckattendance.php?calledfrom=7&qry=summary_for_payroll';
                include_once '../attendance/attendsql/attendsumforpayroll.php';
				$sql='SELECT `attend_44sumforpayroll`.*, Nickname, FirstName, SurName from `attend_44sumforpayroll` join `1employees` on `attend_44sumforpayroll`.IDNo=`1employees`.IDNo WHERE PayrollID='.$_POST['payrollid'].'';
			 $columnnames=array('IDNo', 'Nickname','FirstName','SurName','RegDaysPresent','LWOPDays','LegalDays','SpecDays','SLDays','VLDays','LWPDays','QDays','RestDays','RegDaysActual','LegalHrsOT','SpecHrsOT','RestHrsOT','PaidLegalDays','RegOTHrs');
				$orderby='IDNo';
				
				include('../backendphp/layout/displayastable.php');
			
            break;
	   case 'PerCompanyList':
            include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
            $title='Employee List From '.companyandbranchValue($link,'1companies','CompanyNo', $_GET['RCompanyNo'],'Company') . ' Company';
            $sql='SELECT e.IDNo, CONCAT(e.FirstName, " ", e.MiddleName, " ", e.SurName) as EmployeeName from 1employees e
                join 1companies c on e.RCompanyNo = c.CompanyNo where e.RCompanyNo= '.intval($_GET['RCompanyNo']).' AND Resigned=0 AND DirectOrAgency=0';
            $columnnames=array('IDNo','EmployeeName'); 
            $width='30%'; 
            include_once('../backendphp/layout/displayastable.php');
    break;

    case 'PerDeptList':
            include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
            $title='Employee List From '.comboBoxValue($link,'attend_30currentpositions','deptid', $_GET['deptid'],'department');
            $sql='SELECT IDNo, FullName as EmployeeName FROM attend_30currentpositions WHERE deptid = ' .intval($_GET['deptid']);
            $columnnames=array('IDNo','EmployeeName'); 
            $width='30%'; 
            include_once('../backendphp/layout/displayastable.php');
    break;

    case 'PerAreaList':
            include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
            $title='Employee List From ' .comboBoxValue($link, '0area', 'AreaNo', $_GET['AreaNo'], 'Area');
            $sql='SELECT IDNo, FullName as EmployeeName FROM attend_30currentpositions e 
            JOIN 1branches b ON b.BranchNo=e.BranchNo JOIN `0area` a ON a.AreaNo=b.AreaNo WHERE a.AreaNo = '.intval($_GET['AreaNo']);
            $columnnames=array('IDNo','EmployeeName'); 
            $width='30%'; 
            include_once('../backendphp/layout/displayastable.php');
    break;


    case 'PerGenderList':
            include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
            $title=intval($_GET['gender'])==0?'Female Employee List':'Male Employee List';
            $sql='SELECT IDNo, CONCAT(FirstName, " ", MiddleName, " ", SurName) as EmployeeName FROM `1employees` WHERE Resigned = 0 AND Gender='.$_GET['gender'];
            $columnnames=array('IDNo','EmployeeName'); 
            $width='30%'; 
            include_once('../backendphp/layout/displayastable.php');
    break;


    case 'PerBranchList':
            include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
            $title='Employee List From ' .companyandbranchValue($link, '1branches', 'BranchNo', $_GET['BranchNo'], 'Branch');
            $sql='SELECT d.IDNo, CONCAT(e.FirstName, " ", e.MiddleName," ", e.Surname) AS EmployeeName FROM 1employees e
            JOIN attend_1defaultbranchassign d ON d.IDNo=e.IDNo JOIN 1branches b ON b.BranchNo=d.DefaultBranchAssignNo WHERE Resigned = 0 AND d.DefaultBranchAssignNo = '.$_GET['BranchNo'];
            $columnnames=array('IDNo','EmployeeName'); 
            $width='30%'; 
            include_once('../backendphp/layout/displayastable.php');
    break;
    case 'InPositionList':
            include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
            $title='Employee List From ' .comboBoxValue($link, 'attend_0positions', 'PositionID', $_GET['PositionID'], 'Position');
            $sql='SELECT IDNo, FullName FROM attend_30currentpositions WHERE PositionID='.intval($_GET['PositionID']);
            $columnnames=array('IDNo','FullName'); 
            $width='30%'; 
            include_once('../backendphp/layout/displayastable.php');
    break;

    case 'PerYrsOfSrvcList':
            include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
            $title='Employee List With ' . $_GET['Years'] . ' Years of Service';
            $sql='SELECT IDNo, FullName as EmployeeName FROM attend_howlongwithus WHERE IDNo>1000 AND FLOOR(InYears)='.$_GET['Years'];
            $columnnames=array('IDNo','EmployeeName'); 
            $width='30%'; 
            include_once('../backendphp/layout/displayastable.php');
    break;


    case 'PerCompany': 
             if (!allowedToOpen(634,'1rtc')){ echo 'No permission'; exit;}
            $title='Employees Per Company';
            $editprocess='tocheckattendance.php?qry=PerCompanyList&RCompanyNo=';
            $editprocesslabel='Lookup';
            $sql='SELECT e.RCompanyNo AS TxnID, e.RCompanyNo,`CompanyName`, count(e.RCompanyNo) as EmployeeCount from `1employees` as e join `1companies` as c on e.RCompanyNo=c.CompanyNo WHERE Resigned=0 AND DirectOrAgency=0 group by RCompanyNo UNION SELECT 0,0,"Agency",COUNT(IDNo) FROM `1employees` WHERE Resigned=0 AND DirectOrAgency=1';
            $orderby='RCompanyNo';  $coltototal='EmployeeCount';   $showgrandtotal=true;
            $columnnames=array('RCompanyNo','CompanyName','EmployeeCount'); 
            $width='60%';
            include_once('../backendphp/layout/displayastable.php');

            echo '<br><br><H3>Per Department</H3>'; $title='';
            $editprocess='tocheckattendance.php?qry=PerDeptList&deptid=';
            $editprocesslabel='Lookup';
            $sql='SELECT deptid as TxnID, department AS Department,count(IDNo) AS EmployeeCount FROM `attend_30currentpositions` AS e GROUP BY deptid ORDER BY deptid';
            $columnnames=array('Department','EmployeeCount'); $width='35%'; 
            include('../backendphp/layout/displayastable.php');

            echo '<br><br><H3>Per Area</H3>';
            $editprocess='tocheckattendance.php?qry=PerAreaList&AreaNo=';
            $editprocesslabel='Lookup';
            $sql='SELECT a.AreaNo as TxnID, Area, count(IDNo) AS EmployeeCount FROM `attend_30currentpositions` AS e JOIN `1branches` b ON b.BranchNo=e.BranchNo JOIN `0area` a ON a.AreaNo=b.AreaNo GROUP BY a.AreaNo ORDER BY a.AreaNo';  
            $columnnames=array('Area', 'EmployeeCount'); 
            include('../backendphp/layout/displayastable.php');

            echo '<br><br><H3>Per Gender</H3>';
            $editprocess='tocheckattendance.php?qry=PerGenderList&gender=';
            $editprocesslabel='Lookup';
            $sql='SELECT Gender as TxnID, IF(Gender=0,"Female","Male") AS `Gender`, COUNT(IDNo) AS `EmployeeCount` FROM `1employees` AS e WHERE Resigned=0 GROUP BY Gender;';
            $columnnames=array('Gender','EmployeeCount'); 
            include('../backendphp/layout/displayastable.php');

            echo '<br><br><H3>Per Branch</H3>';
            $editprocess='tocheckattendance.php?qry=PerBranchList&BranchNo=';
            $editprocesslabel='Lookup';
            $sql='SELECT BranchNo as TxnID, BranchNo, Branch, Count(DefaultBranchAssignNo) as EmployeeCount FROM `attend_1defaultbranchassign` as d JOIN `1employees` e ON d.IDNo=e.IDNo '
                    . 'join `1branches` as b on b.BranchNo=d.DefaultBranchAssignNo WHERE Resigned=0 group by DefaultBranchAssignNo order by Branch';
            $columnnames=array('BranchNo', 'Branch', 'EmployeeCount'); 
            include('../backendphp/layout/displayastable.php');
            
            echo '<br><br><H3>In Years of Service</H3>';
            $editprocess='tocheckattendance.php?qry=PerYrsOfSrvcList&Years=';
            $editprocesslabel='Lookup';
            $sql='SELECT FLOOR(InYears) as TxnID, FLOOR(InYears) AS YrsInService, COUNT(*) AS EmployeeCount FROM attend_howlongwithus WHERE IDNo>1000 GROUP BY FLOOR(InYears);';
            $columnnames=array('YrsInService', 'EmployeeCount'); 
            include('../backendphp/layout/displayastable.php');
			
			// unset($editprocess,$editprocesslabel);
			echo '<br><br><H3>In Positions</H3>';
			$editprocess='tocheckattendance.php?qry=InPositionList&PositionID=';
            $editprocesslabel='Lookup';
           $sql='SELECT PositionID AS TxnID,Position, COUNT(*) AS EmployeeCount FROM `attend_30currentpositions` GROUP BY PositionID ORDER BY EmployeeCount DESC, JLID;';  
           $columnnames=array('Position', 'EmployeeCount');
           include('../backendphp/layout/displayastable.php');
   break;
        
            
    break;
	 
	 case 'my_attendance':
		/* $_POST['payrollid']=(isset($_POST['payrollid'])?$_POST['payrollid']:((date('m')*2)+(date('d')<15?-1:0)));
             include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
             echo comboBox($link,$listsql,$listlabel,$listvalue,'payperiods'); */
             ?><br><br>
        <form method="POST" action="tocheckattendance.php?calledfrom=7&qry=my_attendance&logout=1" enctype="multipart/form-data">
        For Payroll ID <input type='text' name='payrollid' list='payperiods' value="<?php echo $_POST['payrollid'];?>"></input> 
	<input type="submit" name="lookup" value="Lookup">
<?php
            include_once '../attendance/attendsql/attendsumforpayroll.php';
             
            $title='Attendance Summary';
 
            $sql='SELECT `RegDaysActual` AS  `Regular Days`,`LWOPDays` AS `Leave WITHOUT Pay`,`PaidLegalDays` AS `Legal Holidays`,`SpecDays` AS `Special Holidays`,`SLDays` AS `Sick Leaves`,`VLDays` AS `Vacation Leaves`,`RestDays` AS `Restdays`,`LWPDays` AS `Leave WITH Pay`,`LegalHrsOT` AS `Overtime (Legal Holiday in Hrs)`,`SpecHrsOT` AS `Overtime (Special Holiday in Hrs)`,`RestHrsOT` AS `Overtime (Restday in Hrs)`,`RegOTHrs` AS `Overtime (Regular Workday in Hrs)` FROM `attend_44sumforpayroll` WHERE IDNo='.$_SESSION['(ak0)'].' AND PayrollID='.$_POST['payrollid'];
            $stmt=$link->query($sql);$row=$stmt->fetch();
            $columnnames=array('Regular Days','Legal Holidays','Special Holidays','Sick Leaves','Vacation Leaves','Restdays','Leave WITH Pay','Leave WITHOUT Pay','Overtime (Legal Holiday in Hrs)','Overtime (Special Holiday in Hrs)','Overtime (Restday in Hrs)','Overtime (Regular Workday in Hrs)');
            $attend='';
            foreach($columnnames as $col){
                $attend.=($row[$col]<>0?'<tr><td>'.$col.'</td><td>'.number_format($row[$col],2).'</td></tr>':'');
            }

            echo '<br><br><h3>Totals:</h3><div style="margin-left: 50px;"><table bgcolor="lightyellow">'.$attend.'</table></div><br><br>';
             
            $title='My Attendance Per Payroll Period'; 
            $sql='SELECT *, DateToday AS `Date` FROM attend_45lookupattend WHERE (IDNo='.$_SESSION['(ak0)'].') AND PayrollID='.$_POST['payrollid'].' ORDER BY DateToday';
            $columnnames=array('TxnID','Date', 'IDNo', 'TimeIn','TimeOut','RemarksHR','Overtime','LeaveName', 'Branch');
            $width='60%';
            include('../backendphp/layout/displayastable.php');
            break;
        default:
            break;
     }
  $link=null; $stmt=null;    
?>