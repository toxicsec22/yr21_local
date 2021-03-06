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
			$txnidname='TxnID';
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
            $columnnames=array('TxnID','DateToday', 'IDNo', 'FullName','TimeIn','TimeOut','RemarksDept','RemarksHR','OTApproval','OTTypeNo','LeaveName', 'Branch');
            include('../backendphp/layout/displayastablewithdatecondition.php');
            break;
        case 'time_out_before_5':
            if (!allowedToOpen(637,'1rtc')){ echo 'No permission'; exit;}
            $title='Time Out Before 5';
            $pagetouse='tocheckattendance.php?calledfrom=7&qry=time_out_before_5';
            $sql='SELECT `a`.*, CONCAT(FirstName," ",SurName) as `FullName` FROM `attend_45lookupattend` a JOIN `1employees` e ON `e`.IDNo=`a`.IDNo WHERE  ((STR_TO_DATE(`TimeOut`,\'%l:%i %p\'))<(IF(DAYOFWEEK(DateToday)=7,IF(WithSat=2,\'17:00:00\',\'12:00:00\'),\'17:00:00\')))';
            $orderby='DateToday, FullName';    
            $columnnames=array('TxnID','DateToday', 'IDNo', 'FullName','TimeIn','TimeOut','RemarksDept','RemarksHR','OTApproval','OTTypeNo','LeaveName', 'Branch');
            include('../backendphp/layout/displayastablewithdatecondition.php');
            break;
        case 'check_overtime': 
            if (!allowedToOpen(632,'1rtc')){ echo 'No permission'; exit;}
            $title='Approved Overtime';
            $pagetouse='tocheckattendance.php?calledfrom=7&qry=check_overtime';
            $sql='SELECT `a`.*, concat(FirstName," ",SurName) as `FullName` FROM `attend_45lookupattend` a JOIN `1employees` e ON `e`.IDNo=`a`.IDNo WHERE 
 `OTApproval`<>0';
            $orderby='DateToday, FullName';    
            $columnnames=array('TxnID','DateToday', 'IDNo', 'FullName','TimeIn','TimeOut','RemarksDept','RemarksHR','OTApproval','OTTypeNo','LeaveName', 'Branch');
            include('../backendphp/layout/displayastablewithdatecondition.php');
            break;
        case 'attendance_per_payperiod':
            if (!allowedToOpen(630,'1rtc')){ echo 'No permission'; exit;}
            $title='Attendance Per Payroll Period';
            $pagetouse='tocheckattendance.php?calledfrom=7&qry=attendance_per_payperiod';
            $sql='SELECT `attend_45lookupattend`.*, concat(FirstName,\' \',SurName) as `FullName` FROM attend_45lookupattend inner join `1employees` on `1employees`.IDNo=`attend_45lookupattend`.IDNo WHERE ((`attend_45lookupattend`.Posted)<>\'0\')';
            $orderby='DateToday, FullName';    
            $columnnames=array('TxnID','DateToday', 'IDNo', 'FullName','TimeIn','TimeOut','RemarksDept','RemarksHR','OTApproval','OTTypeNo','LeaveName', 'Branch');
            include('../backendphp/layout/displayastablewithdatecondition.php');
            break;
        case 'summary_for_payrollOLD':
            if (!allowedToOpen(635,'1rtc')){ echo 'No permission'; exit;}
				
				$title='Summary For Payroll OLD';
				echo '<title>'.$title.'</title>';
				echo '<br><h3>'.$title.'</h3>';
				
				echo '<form action="tocheckattendance.php?calledfrom=7&qry=summary_for_payrollOLD" method="POST">payperiod: <input type="text" size="5" name="payrollid" list="payperiods"><input type="submit" name="btnSubmit" value="Lookup"/></form>';
			
				
				$title='';
				$formdesc='</i>payperiod: '.$_POST['payrollid'].'<i>';
				$pagetouse='tocheckattendance.php?calledfrom=7&qry=summary_for_payrollOLD';
                include_once '../attendance/attendsql/attendsumforpayrollOLD.php';
				$sql='SELECT `attend_44sumforpayroll`.*, Nickname, FirstName, SurName from `attend_44sumforpayroll` join `1employees` on `attend_44sumforpayroll`.IDNo=`1employees`.IDNo WHERE PayrollID='.$_POST['payrollid'].'';
			 $columnnames=array('IDNo', 'Nickname','FirstName','SurName','RegDaysPresent','LWOPDays','LegalDays','SpecDays','SLDays','VLDays','LWPDays','QDays','RestDays','RegDaysActual','LegalHrsOT','SpecHrsOT','RestHrsOT','ExcessRestHrsOT','PaidLegalDays','RegOTHrs');
				$orderby='IDNo';
				
				include('../backendphp/layout/displayastable.php');
			
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
                 $columnnames=array('IDNo', 'Nickname','FirstName','SurName','RegDaysPresent','LWOPDays','LegalDays','SpecDays','SLDays','VLDays','LWPDays','QDays','RWSDays','RestDays','RegDaysActual','LegalShiftHrsOT','LegalExShiftHrsOT','SpecShiftHrsOT','SpecExShiftHrsOT','RestShiftHrsOT','RestExShiftHrsOT','PaidLegalDays','RegExShiftHrsOT');
                    $orderby='IDNo';
                    
                    include('../backendphp/layout/displayastable.php');
                
                break;

	   case 'PerCompanyList':
            include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
            $title='Employee List From '.companyandbranchValue($link,'1companies','CompanyNo', $_GET['RCompanyNo'],'Company') . ' Company';
            // $sql='SELECT e.IDNo,MobileNo, CONCAT(e.FirstName, " ", e.MiddleName, " ", e.SurName) AS EmployeeName,e.DateHired, BranchorDept,id.SSSNo,id.PHICNo,id.PagIbigNo,id.TIN FROM 1employees e JOIN 1_gamit.0idinfo id ON e.IDNo=id.IDNo
            // JOIN 1companies c ON e.RCompanyNo = c.CompanyNo JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo WHERE e.RCompanyNo= '.intval($_GET['RCompanyNo']).' AND Resigned=0 AND DirectOrAgency=0 ORDER BY BranchorDept';
            // $columnnames=array('IDNo','EmployeeName','BranchorDept','DateHired','MobileNo','SSSNo','PHICNo','PagIbigNo','TIN'); 
            // if (allowedToOpen(62411,'1rtc')){ 
            //     $columnnames=array('IDNo','EmpName','BranchorDept','Position','DateHired','MobileNo','SSSNo','PHICNo','PagIbigNo','TIN','MonthlyBasic'); 
            //     $orderby=' ORDER BY e.SurName,e.FirstName,e.MiddleName';
            // } else {
                $columnnames=array('IDNo','EmployeeName','BranchorDept'); 
                $orderby=' ORDER BY BranchorDept';
            // }

            $sql='SELECT e.IDNo, CONCAT(e.FirstName, " ", e.MiddleName, " ", e.SurName) AS EmployeeName, BranchorDept FROM 1employees e JOIN 1_gamit.0idinfo id ON e.IDNo=id.IDNo
            JOIN 1companies c ON e.RCompanyNo = c.CompanyNo JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo WHERE e.RCompanyNo= '.intval($_GET['RCompanyNo']).' AND Resigned=0 AND e.DirectOrAgency=0 '.$orderby;

            
            //$width='40%'; 
            ?>
            <div width='100%'><div width='50%'  style='float: left; margin-left: 3%'>
            <?php
            include_once('../backendphp/layout/displayastablenosort.php');
            ?>
            </div><div width='30%' style='float: left;margin-left:5%;' >
            <?php
            $title='';$subtitle='Per Company Per Branch';
            $sql='SELECT COUNT(e.IDNo) AS `EmployeeCount`, IF(`b`.`PseudoBranch` <> 1, `b`.`Branch`, "Head Office") AS BranchorHO FROM 1employees e 
            JOIN `attend_1defaultbranchassign` `db` ON (`e`.`IDNo` = `db`.`IDNo`) 
            JOIN `1branches` `b` ON (`db`.`DefaultBranchAssignNo` = `b`.`BranchNo`)
            WHERE e.RCompanyNo= '.intval($_GET['RCompanyNo']).' AND Resigned=0 AND DirectOrAgency=0 GROUP BY BranchorHO';
            
            $columnnames=array('BranchorHO','EmployeeCount'); 
            //$width='15%'; 
            include_once('../backendphp/layout/displayastableonlynoheaders.php');
            ?>
            </div></div>
            <?php
    break;

//temporary only
    case 'PerCompanyListResigned':
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        $title='SEPARATED Employee List '.(isset($_POST['btnLookup'])?'From '.companyandbranchValue($link,'1companies','CompanyNo', $_POST['CompanyNo'],'Company') . ' Company':'').'';

            $columnnames=array('IDNo','EmployeeName','PHICNo','Birthdate','DirectHire','Gender','DateResigned','MonthlyBasic'); 
            $orderby=' ORDER BY e.SurName,e.FirstName,e.MiddleName';
       

            $companylist='';
            $sql='SELECT CompanyNo,Company FROM 1companies WHERE CompanyNo<=6';
            $stmt=$link->query($sql);$rows=$stmt->fetchAll();
            foreach($rows AS $row){
                $companylist.='<option value="'.$row['CompanyNo'].'" '.((isset($_POST['CompanyNo']) AND $_POST['CompanyNo']==$row['CompanyNo'])?'selected':'').'>'.$row['Company'].'</option>';
            }

$title="SEPARATED Employees";
echo '<title>'.$title.'</title>';
echo '<br><br><h3>'.$title.'</h3>';
            echo '<form action="#" method="POST">
            Year: <select name="Yr">
                <option value="2021" '.((isset($_POST['Yr']) AND $_POST['Yr']==2021)?'selected':'').'>2021</option>
                <option value="2020" '.((isset($_POST['Yr']) AND $_POST['Yr']==2020)?'selected':'').'>2020</option>
                <option value="2019" '.((isset($_POST['Yr']) AND $_POST['Yr']==2019)?'selected':'').'>2019</option>
                <option value="2018" '.((isset($_POST['Yr']) AND $_POST['Yr']==2018)?'selected':'').'>2018</option>
                <option value="2017" '.((isset($_POST['Yr']) AND $_POST['Yr']==2017)?'selected':'').'>2017</option>
                <option value="2016" '.((isset($_POST['Yr']) AND $_POST['Yr']==2016)?'selected':'').'>2016</option>
                <option value="2015" '.((isset($_POST['Yr']) AND $_POST['Yr']==2015)?'selected':'').'>2015</option>
                <option value="2014" '.((isset($_POST['Yr']) AND $_POST['Yr']==2014)?'selected':'').'>2014</option>
                <option value="2013" '.((isset($_POST['Yr']) AND $_POST['Yr']==2013)?'selected':'').'><=2013</option>
            </select> Company: <select name="CompanyNo">'.$companylist.'</select><input type="submit" value="Lookup" name="btnLookup"></form>';

// echo '<br><br><h3>'.$title.'</h3>';
//             echo '<form action="#" method="POST">
//             Year: <select name="Yr">
//                 <option value="2021" '.((isset($_POST['Yr']) AND $_POST['Yr']==2021)?'selected':'').'>2021</option>
//                 <option value="2020" '.((isset($_POST['Yr']) AND $_POST['Yr']==2020)?'selected':'').'>2020</option>
//                 <option value="2019" '.((isset($_POST['Yr']) AND $_POST['Yr']==2019)?'selected':'').'>2019</option>
//                 <option value="2018" '.((isset($_POST['Yr']) AND $_POST['Yr']==2018)?'selected':'').'>2018</option>
//             </select> Company: <select name="CompanyNo">'.$companylist.'</select><input type="submit" value="Lookup" name="btnLookup"></form>';

if(isset($_POST['btnLookup'])){
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

        $tabledmr='payroll_21dailyandmonthlyofresigned';
        if($_POST['Yr']==2017){
            $sql0='
            CREATE TEMPORARY TABLE `'.$_POST['Yr'].'_1rtc`.payroll_21dailyandmonthlyofresignedprev AS
            select `e`.`IDNo` AS `IDNo`,if(`lr`.`LatestDorM` = 0,ifnull(`lr`.`LatestBasicRate` * 13,0),truncate(ifnull(`lr`.`LatestBasicRate`,0) * 2,2)) AS `BasicMonthly` from ((`'.$_POST['Yr'].'_1rtc`.`1employees` `e` join `'.$_POST['Yr'].'_1rtc`.`payroll_20latestrates` `lr` on(`e`.`IDNo` = `lr`.`IDNo`)) join `'.$_POST['Yr'].'_1rtc`.`gen_info_0idinfo` `i` on(`i`.`IDNo` = `lr`.`IDNo`)) where `e`.`Resigned` <> 0 group by `e`.`IDNo`;';
            $stmt0=$link->prepare($sql0); $stmt0->execute();
            // echo $sql0.'<br><br>';
            $tabledmr='payroll_21dailyandmonthlyofresignedprev'; 
        }
        if($_POST['Yr']>=2014 AND $_POST['Yr']<2017){
            $tabledmr='payroll_21DailyandMonthlyofResigned'; 
        }
        if($_POST['Yr']>=2014){
            $sql='SELECT e.IDNo, CONCAT(e.SurName, ", ",e.FirstName, " ", e.MiddleName) AS EmployeeName,id.Birthdate,id.PHICNo,IF(e.DirectOrAgency<>0,"No","") AS DirectHire,IF(e.Gender=1,"M","F") AS Gender,DateResigned,FORMAT(BasicMonthly,2) AS MonthlyBasic FROM '.$_POST['Yr'].'_1rtc.1employees e JOIN 1_gamit.0idinfo id ON e.IDNo=id.IDNo
            JOIN '.$_POST['Yr'].'_1rtc.1companies c ON e.RCompanyNo = c.CompanyNo JOIN '.$_POST['Yr'].'_1rtc.'.$tabledmr.' dmr ON id.IDNo=dmr.IDNo WHERE e.RCompanyNo= '.intval($_POST['CompanyNo']).' AND e.Resigned=1 '.$orderby;

            $formdesc='</i><b>'.$_POST['Yr'].' '.comboBoxValue($link,'1companies','CompanyNo', $_POST['CompanyNo'],'Company').'</b><i>';
        } else {
            $sql='SELECT id.IDNo, CONCAT(id.SurName, ", ",id.FirstName, " ", id.MiddleName) AS EmployeeName,id.Birthdate,id.PHICNo,"" AS DirectHire,"" AS Gender,DateResigned,"" AS MonthlyBasic FROM 1_gamit.0idinfo id WHERE YEAR(DateResigned)<=2013 ORDER BY DateResigned DESC;';

             $formdesc='</i><b>Resigned Employees <= 2013 (ALL Companies)</b><i>';
        }
        // echo $sql;
        $title='';
        include_once('../backendphp/layout/displayastablenosort.php');
    }  


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
            $title='Employee List From ' .comboBoxValue($link, 'attend_1positions', 'PositionID', $_GET['PositionID'], 'Position');
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
           $sql='SELECT PositionID AS TxnID,Position, COUNT(*) AS EmployeeCount FROM `attend_30currentpositions` GROUP BY PositionID ORDER BY EmployeeCount DESC, JobLevelID;';  
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
 
            $sql='SELECT `RegDaysActual` AS  `Regular Days`,`LWOPDays` AS `Leave WITHOUT Pay`,`PaidLegalDays` AS `Legal Holidays`,`SpecDays` AS `Special Holidays`,`SLDays` AS `Sick Leaves`,`VLDays` AS `Vacation Leaves`,`RWSDays`,`RestDays` AS `Restdays`,`LWPDays` AS `Leave WITH Pay`,`LegalHrsOT` AS `Overtime (Legal Holiday in Hrs)`,`SpecHrsOT` AS `Overtime (Special Holiday in Hrs)`,`RestHrsOT` AS `Overtime (Restday in Hrs)`,`RegOTHrs` AS `Overtime (Regular Workday in Hrs)` FROM `attend_44sumforpayroll` WHERE IDNo='.$_SESSION['(ak0)'].' AND PayrollID='.$_POST['payrollid'];
            $stmt=$link->query($sql);$row=$stmt->fetch();
            $columnnames=array('Regular Days','Legal Holidays','Special Holidays','Sick Leaves','Vacation Leaves','RWSDays','Restdays','Leave WITH Pay','Leave WITHOUT Pay','Overtime (Legal Holiday in Hrs)','Overtime (Special Holiday in Hrs)','Overtime (Restday in Hrs)','Overtime (Regular Workday in Hrs)');
            $attend='';
            foreach($columnnames as $col){
                $attend.=($row[$col]<>0?'<tr><td>'.$col.'</td><td>'.number_format($row[$col],2).'</td></tr>':'');
            }

            echo '<br><br><h3>Totals:</h3><div style="margin-left: 50px;"><table bgcolor="lightyellow">'.$attend.'</table></div><br><br>';
             
            $title='My Attendance Per Payroll Period'; 
            $sql='SELECT *, DateToday AS `Date` FROM attend_45lookupattend WHERE (IDNo='.$_SESSION['(ak0)'].') AND PayrollID='.$_POST['payrollid'].' ORDER BY DateToday';
            $columnnames=array('TxnID','Date', 'IDNo', 'TimeIn','TimeOut','RemarksHR','OTApproval','OTTypeNo','LeaveName', 'Branch');
            $width='60%';
            include('../backendphp/layout/displayastable.php');
            break;
        default:
            break;
     }
  $link=null; $stmt=null;    
?>