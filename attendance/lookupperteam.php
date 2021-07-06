<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false; include_once('../switchboard/contents.php');
// check if allowed
$allowed=array(623,622,608,611,618,619,628,6281,6110,6121);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit; }
allowed:
// end of check

 
 

include_once('lookupperteamlinks.php');
$which=(!isset($_GET['w'])?((allowedToOpen(623,'1rtc'))?'PerDay':(allowedToOpen(608,'1rtc')?'PerPerson':'PerMonth')):$_GET['w']);
$deptcondition='(((IF('.$_SESSION['&pos'].' IN (99,100),supervisorpositionid,IF( '.$_SESSION['&pos'].' IN (SELECT deptheadpositionid FROM `1departments`), deptheadpositionid, supervisorpositionid)))='.$_SESSION['&pos'].' OR p.LatestSupervisorIDNo='.$_SESSION['(ak0)'].') '.((allowedToOpen(618,'1rtc'))?' OR (p.IDNo IN (SELECT IDNo FROM attend_30currentpositions cp JOIN attend_1branchgroups bg ON cp.BranchNo=bg.BranchNo WHERE '.$_SESSION['(ak0)'].' IN (FieldSpecialist,BranchSupport,BranchCoordinator) OR OpsManager='.$_SESSION['(ak0)'].')) OR (p.IDNo IN (SELECT IDNo FROM attend_30currentpositions cp JOIN attend_1branchgroups bg ON cp.BranchNo=bg.BranchNo WHERE SAM='.$_SESSION['(ak0)'].')) OR (p.IDNo IN (SELECT IDNo FROM attend_30currentpositions cp JOIN attend_1branchgroups bg ON cp.BranchNo=bg.BranchNo WHERE TeamLeader='.$_SESSION['(ak0)'].')) OR (p.IDNo IN (SELECT IDNo FROM attend_30currentpositions cp JOIN attend_1branchgroups bg ON cp.BranchNo=bg.BranchNo WHERE CNC='.$_SESSION['(ak0)'].'))':'').')';

if (allowedToOpen(6110,'1rtc')){ 
           $stmt0=$link->query('SELECT deptid FROM `attend_30currentpositions` WHERE IDNo='.$_SESSION['(ak0)']);
           $res0=$stmt0->fetch();
		   
		    $deptcondition='p.deptid IN ('.(($res0['deptid']==70)?'70,10':$res0['deptid']).')';
			
			if (allowedToOpen(629,'1rtc')){
			
				$deptcondition='(p.IDNo IN (SELECT TeamLeader FROM attend_1branchgroups WHERE SAM='.$_SESSION['(ak0)'].' GROUP BY TeamLeader
								UNION ALL
								SELECT CNC FROM attend_1branchgroups WHERE SAM='.$_SESSION['(ak0)'].' GROUP BY CNC))';
			}
}





switch ($which){
   case 'PerDay':
       if (!allowedToOpen(623,'1rtc')){ echo 'No permission'; exit; }
$title='Attendance Per Dept Per Day';
    
?><form method="POST" action="lookupperteam.php?w=PerDay" enctype="multipart/form-data">
        Choose Date:  <input type="date" width=8 name="DateToday" value="<?php echo date('Y-m-d'); ?>"></input>         
        <input type="submit" name="show" value="Show"></form>
</form>
<?php
if (allowedToOpen(608,'1rtc')){ $deptcondition='1=1'; }
$date=(!isset($_POST['DateToday'])?date('Y-m-d'):$_POST['DateToday']);
$sql='SELECT `a`.*,LEFT(TimeIn,5) AS TimeIn,LEFT(TimeOut,5) AS TimeOut, Position, concat(FirstName," ",SurName) as `FullName`, OTType, IF(OTApproval=2,"Pre-approved",IF(OTApproval=1,"HR Approved","")) AS OT_Approval FROM attend_45lookupattend a JOIN `1employees` on `1employees`.IDNo=`a`.IDNo 
          JOIN `attend_30currentpositions` p ON a.IDNo=p.IDNo LEFT JOIN attend_0ottype ot ON ot.OTTypeNo=a.OTTypeNo
        WHERE '.$deptcondition.' AND DateToday=\''.$date.'\' ORDER BY Branch, FullName, DateToday';
	
        $columnnames=array('DateToday','IDNo','FullName','Position','TimeIn','TimeOut','RemarksHR','RemarksDept','Shift','OT_Approval','OTType','LeaveName', 'Branch');

$width='80%';
     include('../backendphp/layout/displayastable.php');
     break;

   case 'PerMonth':
       if (!allowedToOpen(622,'1rtc')){ echo 'No permission'; exit; }
$title='Attendance Per Dept Per Month';
 if (allowedToOpen(608,'1rtc')){ $deptcondition='1=1'; } 
?><title>Attendance Per Dept Per Month</title>
<br><br>
<form method="post" action="lookupperteam.php?w=PerMonth" enctype="multipart/form-data">
                Choose Month (1 - 12):  <input type="text" name="month" value="<?php echo date('m'); ?>"></input>
<input type="submit" name="lookup" value="Lookup"> </form>

<?php

if (!isset($_REQUEST['month'])){ $month=date('m');} else { $month=$_REQUEST['month'];}    
$width='60%';
echo '<h4>Lates</h4>';
        $sql='SELECT a.`IDNo`, `Nickname`, `SurName`, `LatesPerMonth`, `Month` FROM `attend_62latescount` a JOIN `attend_30currentpositions` p ON a.IDNo=p.IDNo
 WHERE '.$deptcondition.' AND ForMonth='.$month;
                             
        $orderby='Nickname';
        $columnnames=array('IDNo', 'Nickname', 'SurName', 'LatesPerMonth', 'Month');
        
		 include('../backendphp/layout/displayastable.php');
    
echo '<h4>Absences</h4>';
        $sql='SELECT a.`IDNo`, `Nickname`, `SurName`, `AbsencesPerMonth`, `Month`,`LeaveName` FROM attend_62absences a JOIN `attend_30currentpositions` p ON a.IDNo=p.IDNo WHERE '.$deptcondition.' AND MonthNum='.$month; 
        $columnnames=array('IDNo', 'Nickname', 'SurName', 'AbsencesPerMonth', 'Month','LeaveName');
        $orderby='Nickname'; 
        
		 include('../backendphp/layout/displayastable.php'); 
        
		
echo '<h4>Actual Attendance</h4>';
      $sql='SELECT `a`.*,LEFT(TimeIn,5) AS TimeIn,LEFT(TimeOut,5) AS TimeOut, concat(FirstName," ",SurName) as `FullName`, IF(a.OTTypeNo=0,"",OTType) AS OTType, IF(OTApproval=2,"Pre-approved", IF(OTApproval=1,"HR Approved","")) AS OT_Approval   FROM attend_45lookupattend a JOIN `1employees` on `1employees`.IDNo=`a`.IDNo 
          JOIN `attend_30currentpositions` p ON a.IDNo=p.IDNo LEFT JOIN attend_0ottype ot ON ot.OTTypeNo=a.OTTypeNo
        WHERE  '.$deptcondition.' AND Month(DateToday)='.$month; 
        $orderby='FullName';
		$sql .= ' ORDER BY DateToday, Branch';
        
        $columnnames=array('DateToday','IDNo','FullName','TimeIn','TimeOut','RemarksHR','RemarksDept','Shift','OTType','OT_Approval','LeaveName', 'Branch');
        
        include('../backendphp/layout/displayastable.php');
break;



case 'LatesPerMonthAll':
        if (!allowedToOpen(619,'1rtc')){ echo 'No permission'; exit; }
        /* 
        * Rank 6 and up are given until 8:30.
        */
        $title='Lates Per Month';
		$addsql=''; $d=1; $columns=array('IDNo','FullName','Position','Branch','Frequency/Yr');
		$montharr=array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
		foreach ($montharr as $month){
			$addsql.=',IFNULL((SELECT LatesPerMonth FROM `attend_62latescount` lc WHERE ForMonth='.$d.' AND lc.IDNo=cp.IDNo),"") AS `'.$month.'`,IFNULL((SELECT TotalMinutesLate FROM `attend_62latescount` lc WHERE ForMonth='.$d.' AND lc.IDNo=cp.IDNo),"") AS `Tot Min '.$month.'`';
			
			
			$columns[]=$month;
			$columns[]='Tot Min '.$month;
			if (date('m')==$d){
				goto proceedlate;
			}
			$d++;
		}
		proceedlate:
        $sql='SELECT IDNo,FullName,Position,IF(deptid<>10,department,Branch) AS Branch,IFNULL((SELECT SUM(LatesPerMonth) FROM `attend_62latescount` lc WHERE lc.IDNo=cp.IDNo AND ForMonth<='.$d.'),0) AS `Frequency/Yr`'.$addsql.' FROM attend_30currentpositions cp ORDER BY Branch, IDNo;';
		// echo $sql;
        $columnnames=$columns;
		
        include('../backendphp/layout/displayastable.php');
break;

case 'AbsencesPerMonthAll':
        if (!allowedToOpen(619,'1rtc')){ echo 'No permission'; exit; }
        $title='Absences Per Month';
		$addsql=''; $d=1; $columns=array('IDNo','FullName','Position','Branch','Total');
		$montharr=array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
		foreach ($montharr as $month){
			$addsql.=',IFNULL((SELECT SUM(AbsencesPerMonth) FROM `attend_62absences` a WHERE MonthNum='.$d.' AND a.IDNo=cp.IDNo),0) AS `'.$month.'`';
			$columns[]=$month;
			if (date('m')==$d){
				goto proceedabsence;
			}
			$d++;
		}
		proceedabsence:
        $sql='SELECT IDNo,FullName,Position,IF(deptid<>10,department,Branch) AS Branch,IFNULL((SELECT SUM(AbsencesPerMonth) FROM `attend_62absences` a WHERE a.IDNo=cp.IDNo AND MonthNum<='.$d.'),0) AS Total'.$addsql.' FROM attend_30currentpositions cp ORDER BY Branch, IDNo;';
		// echo $sql;
        $columnnames=$columns;
        include('../backendphp/layout/displayastable.php');
break;

   case 'PerPerson':
   if (allowedToOpen(608,'1rtc')){ $deptcondition='1=1'; }
      $sql0='SELECT IDNo, CONCAT(FullName," - ", Branch) AS Name FROM `attend_30currentpositions` p WHERE '.$deptcondition;
       
	   goto step2;
   case 'ResignedAttendance':
   if (allowedToOpen(608,'1rtc')){ $deptcondition='1=1'; }
    $sql0='SELECT IDNo, CONCAT(Nickname," ", Surname) AS Name FROM `1employees` e WHERE Resigned=1';
	  
   step2:
    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
   echo comboBox($link, $sql0, 'Name', 'IDNo', 'emplist');
       if (!allowedToOpen(611,'1rtc')){ echo 'No permission'; exit; }
	   
		
       $title='Attendance Per Person';
       
       echo '<title>'.$title.'</title>';
       
       
       
       ?><form method="post" action="lookupperteam.php?w=<?php echo $which;?>" enctype="multipart/form-data">
                Employee:  <input type="text" name="IDNo" list="emplist" value="<?php echo (isset($_POST['IDNo'])?$_POST['IDNo']:'');?>"></input> &nbsp; &nbsp; Color Coding per: <input type="radio" name="ColorField" value="1" <?php echo (((!isset($_POST['ColorField'])) OR (isset($_POST['ColorField']) AND $_POST['ColorField']==1))?'checked':'');?>> PayrollID <input type="radio" name="ColorField" value="0" <?php echo (((isset($_POST['ColorField']) AND $_POST['ColorField']==0))?'checked':'');?>> Month 
<input type="submit" name="lookup" value="Lookup"> </form>
       
    <?php
    
    if(!isset($_POST['IDNo'])) { goto nodata;}
	if($which=='PerPerson'){
		$name=comboBoxValue($link, 'attend_30currentpositions', 'IDNo', $_POST['IDNo'], 'FullName');
		$dept=comboBoxValue($link, 'attend_30currentpositions', 'IDNo', $_POST['IDNo'], 'department');
		$formdesc=!isset($_POST['IDNo'])?'':'</i><br><br>IDNo '.$_POST['IDNo'].' :  '.$name.' of '.$dept.'<br><i>';
	} else {
		$fname=comboBoxValue($link, '1employees', 'IDNo', $_POST['IDNo'], 'Nickname');
		$lname=comboBoxValue($link, '1employees', 'IDNo', $_POST['IDNo'], 'Surname');
		$formdesc=!isset($_POST['IDNo'])?'':'</i><br><br>IDNo '.$_POST['IDNo'].' :  '.$fname.' of '.$lname.'<br><i>';
	}
    
    $sql='SELECT `a`.*,LEFT(TimeIn,5) AS TimeIn,LEFT(TimeOut,5) AS TimeOut, concat(e.FirstName," ",e.SurName) as `FullName`,concat(e1.FirstName," ",e1.SurName) as `INEncodedBy`,concat(e2.FirstName," ",e2.SurName) as `OUTEncodedBy`, MONTH(DateToday) AS `Month`, IF(a.OTTypeNo=0,"",OTType) AS OTType, IF(OTApproval=2,"Pre-approved", IF(OTApproval=1,"HR Approved","")) AS OT_Approval    FROM attend_45lookupattend a JOIN `1employees` e on `e`.IDNo=`a`.IDNo LEFT JOIN attend_0ottype ot ON ot.OTTypeNo=a.OTTypeNo
          LEFT JOIN `1employees` e1 ON a.`TIEncby`=e1.IDNo LEFT JOIN `1employees` e2 ON a.`TOEncby`=e2.IDNo
		  LEFT JOIN attend_30currentpositions p ON a.IDNo=p.IDNo 
        WHERE  '.$deptcondition.' AND a.IDNo='.$_POST['IDNo'].'  ORDER BY DateToday'; //AND PayrollID='.$_POST['payperiod'].'
// echo $deptcondition;
$columnnames=array('DateToday','TimeIn','TimeOut','RemarksHR','RemarksDept','Shift','OTType','OT_Approval','LeaveName', 'INEncodedBy','OUTEncodedBy','Branch');

if(isset($_POST['ColorField']) AND $_POST['ColorField']==0){
	$changecolorfield='Month';
} else {
	$changecolorfield='PayrollID';
}
     include('../backendphp/layout/displayastablenosort.php');
     
       break;
      
  case 'PerBranch':
       if (!allowedToOpen(618,'1rtc')){ echo 'No permission'; exit; }
       $title='Attendance Per Branch';    
       
       include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
       if (allowedToOpen(608,'1rtc')){ $deptcondition='1=1'; } 
       
       $sql0='SELECT IDNo, CONCAT(FullName," - ", Branch) AS Name FROM `attend_30currentpositions` WHERE '.$deptcondition;
       echo comboBox($link, 'Select BranchNo, Branch FROM `1branches` WHERE Active<>0 AND BranchNo<>95', 'Branch', 'BranchNo', 'branches');
       echo comboBox($link, 'SELECT PayrollID, concat(PayrollID, " : ", FromDate, " - ", ToDate) as PayPeriod FROM payroll_1paydates;', 'PayPeriod', 'PayrollID', 'payperiods');
       ?><form method="post" action="lookupperteam.php?w=PerBranch" enctype="multipart/form-data">
                For Payroll Period:  <input type="text" name="payperiod" value="<?php echo date('m'); ?>" list="payperiods"></input> &nbsp; &nbsp; &nbsp;
                Branch:  <input type="text" name="BranchNo" list="branches"></input>
<input type="submit" name="lookup" value="Lookup"> </form>
       
    <?php
    
    if(!isset($_POST['BranchNo'])) { goto nodata;}
    $name=comboBoxValue($link, '`1branches`', 'BranchNo', $_POST['BranchNo'], 'Branch');
    $formdesc=!isset($_POST['BranchNo'])?'':'</i><br><br>BranchNo '.$_POST['BranchNo'].' :  '.$name.'<i>';
    $sql='SELECT `a`.*,LEFT(TimeIn,5) AS TimeIn,LEFT(TimeOut,5) AS TimeOut, concat(FirstName," ",SurName) as `FullName`, OTType, IF(OTApproval=2,"Pre-approved",IF(OTApproval=1,"HR Approved","")) AS OT_Approval   FROM attend_45lookupattend a JOIN `1employees` e on `e`.IDNo=`a`.IDNo 
          JOIN `attend_30currentpositions` p ON a.IDNo=p.IDNo LEFT JOIN attend_0ottype ot ON ot.OTTypeNo=a.OTTypeNo
        WHERE  '.$deptcondition.' AND a.BranchNo='.$_POST['BranchNo'].' AND PayrollID='.$_POST['payperiod'].' ORDER BY DateToday';

$columnnames=array('DateToday','IDNo','FullName','TimeIn','TimeOut','RemarksHR','RemarksDept','Shift','OTType','OT_Approval','LeaveName', 'Branch');
     include('../backendphp/layout/displayastable.php');
     
       break;     
       
   case 'SLBal':
       if (!allowedToOpen(628,'1rtc')){ echo 'No permission'; exit; }
       $title='Leave Balance Per Person';
    $formdesc=(allowedToOpen(6281,'1rtc'))?'<a href="lookupperteam.php?w=SLBalforConversion">Jan 10: First check if SL balance from Dec cut off is accurate.</a>':'';
    $formdesc='</i>Legend: <br>&nbsp &nbsp &nbsp SLBalDecCutoff = Balance from last year'.  str_repeat('&nbsp;',15).$formdesc.'<br>'
            . '&nbsp &nbsp &nbsp SLThisYr = Total Sick Leaves for the year, maximum of 5 <br>'
            . '&nbsp &nbsp &nbsp AvailableSL = Prorated Sick Leaves (first half until June 20, all unused after that) less used Sick Leaves <br>'
            . '&nbsp &nbsp &nbsp VLfromPosition = Vacation Leaves depending on position <br>'
            . '&nbsp &nbsp &nbsp VLfromTenure = Additional Vacation Leaves per year of service, limited by the maximum for the position of the employee. <br>'
            . '&nbsp &nbsp &nbsp VLThisYr = Total VL for the year (VLfromPosition+VLfromTenure)<br><br>'
            . '&nbsp &nbsp &nbsp AvailableVL = Prorated Vacation Leaves (first half until June 20, all unused after that) less used Vacation Leaves <br><i>';
       $columnnames=array('IDNo', 'Name', 'SLThisYr','SLUsed','AvailableSL', 'VLfromPosition','VLfromTenure','VLThisYr', 'VLUsed', 'AvailableVL', 'BirthdayBal');
       
       $sql='SELECT l.*,CONCAT(l.Nickname," ",l.SurName," (",BranchorDept,")") AS Name, SLBal AS AvailableSL, VLBal AS AvailableVL, (e.VLfromPosition+e.VLfromTenure) AS VLThisYr  from `attend_61leavebal` l JOIN attend_30currentpositions cp ON l.IDNo=cp.IDNo JOIN 1employees e ON cp.IDNo=e.IDNo ORDER BY Name;'; 
     include('../backendphp/layout/displayastable.php');
	 
     $title=''; $subtitle='<br><br>Leave Balance of Resigned This Year'; unset($formdesc);
	
     $columnnames=array('IDNo', 'Name', 'SLThisYr','SLUsed', 'VLfromPosition','VLfromTenure','VLThisYr', 'VLUsed');
    $sql='SELECT l.*,CONCAT(e.Nickname," ",e.SurName) AS Name, (e.VLfromPosition+e.VLfromTenure) AS VLThisYr from `attend_61leavebal` l JOIN 1employees e ON l.IDNo=e.IDNo WHERE l.Resigned<>0';
     include('../backendphp/layout/displayastable.php');
       break;
	   
   case 'SLBalforConversion': //this must be changed for Yr 22 bec view has changed AND condition is regularization
       $title='Balance of Leaves for Conversion';
       $formdesc=((allowedToOpen(6281,'1rtc'))?'<a href="../payroll/praddentry.php?w=LeaveConversion&action_token='.$_SESSION['action_token'].'">Jan 10: Encode leave conversion into payroll future adjustments</a>':'');
       $columnnames=array('IDNo', 'Name', 'DateHired', 'SLBalDecCutoff', 'ActualBalLastYr', 'Diff');
         if($currentyr==2022) { echo 'UPDATE THE REFERRED VIEW';}
    $sql='SELECT e.IDNo, CONCAT(e.Nickname," ",e.SurName) AS Name, e.DateHired, e.SLBalDecCutoff, IF(SILBAL>5,5,SILBAL) AS ActualBalLastYr, 
e.SLBalDecCutoff-(SELECT ActualBalLastYr) AS Diff
FROM 1employees e LEFT JOIN '.$lastyr.'_1rtc.`attend_61silbal` b ON b.IDNo=e.IDNo WHERE e.Resigned=0 AND b.SILBAL<>0 AND YEAR(e.DateHired)<'.$lastyr.' ;';
    $width='50%';
     include('../backendphp/layout/displayastable.php');
       break;
   case 'SLDiscrepancies':
       if (!allowedToOpen(628,'1rtc')){ echo 'No permission'; exit; }
       $title='SL Discrepancies';
       $columnnames=array('IDNo', 'Name','SLDaysFromPayroll','SLDaysFromAttendance','PayrollIDsFromPayroll','PayrollIDsFromAttendDance');
      
		
			$link=connect_db(''.$currentyr.'_1rtc',1);
			$sqldrop='DROP TABLE IF EXISTS TempSlUsed'.$_SESSION['(ak0)'].'';
	$stmtdrop=$link->prepare($sqldrop); $stmtdrop->execute();
	
		$sqlexec='CREATE TABLE TempSlUsed'.$_SESSION['(ak0)'].' SELECT 
        d.PayrollID,`a`.`IDNo` AS `IDNo`, COUNT(`a`.`LeaveNo`) AS `SLUsed`
    FROM
        (`attend_2attendancedates` `d`
        JOIN (`1employees` `e`
        JOIN (`attend_2attendance` `a`
        JOIN `attend_0leavetype` `l` ON (`a`.`LeaveNo` = `l`.`LeaveNo`)) ON (`e`.`IDNo` = `a`.`IDNo`)) ON (`d`.`DateToday` = `a`.`DateToday`)) JOIN payroll_1paydates pd ON d.PayrollID=pd.PayrollID 
    WHERE
        `a`.`TimeIn` IS NULL
            AND `d`.`TypeOfDayNo` <> 2
            AND `a`.`LeaveNo` = 14 AND pd.Posted=1
    GROUP BY `a`.`IDNo`,PayrollID;';
	$stmt=$link->prepare($sqlexec); $stmt->execute();
	
		$sql='SELECT sl.IDNo,CONCAT(Nickname," ",SurName," -(",IF(deptid=10,branch,dept),")") AS Name,IFNULL((SELECT SUM(SLDays) FROM payroll_20fromattendance fa1 JOIN payroll_1paydates pd1 ON fa1.PayrollID=pd1.PayrollID WHERE Posted=1 AND IDNo=sl.IDNo),0) AS `SLDaysFromPayroll`,
            IFNULL((SELECT SUM(SLUsed) FROM TempSlUsed'.$_SESSION['(ak0)'].' WHERE IDNo=sl.IDNo),0) AS `SLDaysFromAttendance`,CONCAT("PayrollID: ",(SELECT GROUP_CONCAT(DISTINCT(fa2.PayrollID)) FROM payroll_20fromattendance fa2 JOIN payroll_1paydates pd2 ON fa2.PayrollID=pd2.PayrollID WHERE SLDays<>0 AND Posted=1 AND IDNo=sl.IDNo)) AS PayrollIDsFromPayroll,CONCAT("PayrollID: ",(SELECT GROUP_CONCAT(PayrollID) FROM TempSlUsed'.$_SESSION['(ak0)'].' WHERE IDNo=sl.IDNo ORDER BY PayrollID)) As PayrollIDsFromAttendDance from `attend_61leavebal` sl JOIN attend_30currentpositions cp ON sl.IDNo=cp.IDNo WHERE  Resigned=0 HAVING SLDaysFromPayroll<>SLDaysFromAttendance ORDER BY Nickname;'; 
     include('../backendphp/layout/displayastablenosort.php');
	 
	 $sqldrop='DROP TABLE IF EXISTS TempSlUsed'.$_SESSION['(ak0)'].'';
	$stmtdrop=$link->prepare($sqldrop); $stmtdrop->execute();
	$link=connect_db(''.$currentyr.'_1rtc',0);
       break;
	   
	   
	   
	   case 'DaysAssigned':
       if(allowedToOpen(612, '1rtc')){
                   if (allowedToOpen(608,'1rtc')){ $deptcondition='1=1'; } else if(allowedToOpen(6121,'1rtc')){ $deptcondition='(PseudoBranch=0 OR PseudoBranch=2)';}
		   
           $title='Days Assigned Per Person';
           $showtitle=(!isset($_POST['submit'])?true:false);
           $pagetouse='lookupperteam.php?w=DaysAssigned';
           include('../backendphp/layout/fromtodate.php'); 
            if (!isset($_POST['submit'])){ goto noform;  }
            
           $formdesc='From '.$fromdate.' To '.$todate;
           $columnnames=array('IDNo', 'Branch', 'FullName','CountOfDate');
           $sql='SELECT b.Branch, e.IDNo, concat(FirstName,\' \',SurName,IF(Resigned<>0," - RESIGNED","")) as `FullName`, Count(DateToday) AS CountOfDate FROM `1branches` as b INNER JOIN (1employees e INNER JOIN `attend_2attendance` as a ON e.IDNo = a.IDNo) ON b.BranchNo = a.BranchNo JOIN attend_30currentpositions p On e.IDNo=p.IDNo WHERE '.$deptcondition.' AND ((TimeIn) Is Not Null) and DateToday>=\''. $_POST['FromDate']  . '\' and DateToday<=\''. $_POST['ToDate']  . '\'  GROUP BY e.IDNo,a.BranchNo ORDER BY Branch, FullName'; //JOIN attend_30currentpositions p ON e.IDNo=p.IDNo  JOIN attend_30currentpositions p ON e.IDNo=p.IDNo 
           // '.(allowedToOpen(6121,'1rtc')?'UNION ALL SELECT b.Branch, e.IDNo, concat(FirstName,\' \',SurName) as `FullName`, Count(DateToday) AS CountOfDate FROM `1branches` as b INNER JOIN (1employees e INNER JOIN `attend_2attendance` as a ON e.IDNo = a.IDNo) ON b.BranchNo = a.BranchNo WHERE ((TimeIn) Is Not Null) and DateToday>=\''. $_POST['FromDate']  . '\' and DateToday<=\''. $_POST['ToDate']  . '\' AND (PseudoBranch=0 OR PseudoBranch=2) GROUP BY e.IDNo ORDER BY Branch, FullName':'
        //    echo $sql;
           $width='30%';
     include('../backendphp/layout/displayastable.php');
       } else { echo 'No permission'; exit; }
       break;
	   
	   
      case 'PerfectAttendanceNoLate':
       if (!allowedToOpen(623,'1rtc')){ echo 'No permission'; exit; }
    if (!isset($_REQUEST['month'])){ $month=date('m');} else { $month=$_REQUEST['month'];}

?><form method="POST" action="lookupperteam.php?w=PerfectAttendanceNoLate" enctype="multipart/form-data">
                  Choose Month (1 - 12):  <input type="text" name="month" value="<?php echo $month; ?>"></input>
<input type="submit" name="lookup" value="Lookup Per Month"> <input type="submit" name="lookupfullyr" value="Lookup Full Year"> </form>

<?php

// print_r($_POST);

if (allowedToOpen(608,'1rtc')){ $deptcondition='1=1'; }
$date=(!isset($_POST['DateToday'])?date('Y-m-d'):$_POST['DateToday']);
if(isset($_POST['lookupfullyr'])){
	$condmon='1=1';
	$condmon2='1=1';
	$condmon3='1=1';
	$title='Perfect Attendance and No Lates (<font color="blue">Full Year</font>)</font>';
	
} else {
	$condmon='MONTH(a.DateToday)='.$month.'';
	$condmon2='ForMonth='.$month.'';
	$condmon3='Month='.$month.'';
	$title='Perfect Attendance and No Lates, MonthNo: <font color="blue">'.$month.'</font>';
}

$sql0='CREATE TEMPORARY TABLE attend_62absencesTEMP AS select `e`.`IDNo` AS `IDNo` from (((`1employees` `e` join `attend_2attendance` `a` on(`e`.`IDNo` = `a`.`IDNo`)) join `attend_0leavetype` `l` on(`a`.`LeaveNo` = `l`.`LeaveNo`)) join `attend_30currentpositions` `p` on(`e`.`IDNo` = `p`.`IDNo`)) where `a`.`DateToday` <= current_timestamp() and `e`.`Resigned` = 0 and `a`.`LeaveNo` in (10,16,18,19,14) and (`a`.`TimeIn` is null or hour(`a`.`TimeIn`) + minute(`a`.`TimeIn`) / 60 > 10) AND '.$condmon.' group by `e`.`IDNo`,`e`.`Nickname`,`e`.`SurName`,monthname(`a`.`DateToday`) order by `e`.`Nickname`,month(`a`.`DateToday`),`l`.`LeaveName`';
$stmt0=$link->prepare($sql0); $stmt0->execute();

$sql='SELECT p.*,IF(deptid IN (2,3,10),Branch,dept) AS Branch FROM `attend_30currentpositions` p WHERE '.$deptcondition.' AND p.IDNo NOT IN (SELECT IDNo FROM attend_62absencesTEMP) AND p.IDNo NOT IN (SELECT IDNo FROM attend_62latescount WHERE '.$condmon2.') AND p.IDNo NOT IN (SELECT IDNo FROM attend_62undertime WHERE '.$condmon3.') ORDER BY Branch, FullName';
		// echo $sql; exit();
		
        $columnnames=array('IDNo','FullName','Position','Branch');

$width='80%';
     include('../backendphp/layout/displayastable.php');
     break;
	 
     case 'SummaryPayroll':

        if (allowedToOpen(608,'1rtc')){ $deptcondition='1=1'; }
        $listsql='SELECT PayrollID, concat(PayrollID, " : ", FromDate, " - ", ToDate) as PayPeriod FROM payroll_1paydates;';
        $listvalue='PayrollID';
        $listlabel='PayPeriod';
    
        
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        echo comboBox($link,$listsql,$listlabel,$listvalue,'payperiods');
        
        
        $_POST['payrollid']=(isset($_POST['payrollid'])?$_POST['payrollid']:((date('m')*2)+(date('d')<15?-1:0)));
        echo '<form action="lookupperteam.php?w='.$which.'" method="POST">payperiod: <input type="text" size="5" name="payrollid" list="payperiods"><input type="submit" name="btnSubmit" value="Lookup"/></form>';
	
        include_once '../attendance/attendsql/attendsumforpayroll.php';
				$title='Summary For Payroll';
				$formdesc='</i>payperiod: '.$_POST['payrollid'].'<i>';
				$sql='SELECT sfp.IDNo,FullName,RegDaysPresent,LWOPDays,LegalDays,SpecDays,SLDays,VLDays,LWPDays,QDays,RestDays,RegDaysActual,LegalHrsOT,SpecHrsOT,RestHrsOT,ExcessRestHrsOT,PaidLegalDays,RegOTHrs,IF(p.deptid IN (2,3,10),Branch,dept) AS Branch from `attend_44sumforpayroll` sfp JOIN attend_30currentpositions p ON sfp.IDNo=p.IDNo WHERE '.$deptcondition.' AND PayrollID='.$_POST['payrollid'].' ORDER BY Branch';
                $columnnames=array('IDNo', 'FullName','RegDaysPresent','LWOPDays','LegalDays','SpecDays','SLDays','VLDays','LWPDays','QDays','RestDays','RegDaysActual','LegalHrsOT','SpecHrsOT','RestHrsOT','PaidLegalDays','RegOTHrs','ExcessRestHrsOT');
				$orderby='IDNo';
				
				include('../backendphp/layout/displayastable.php');
				
        break;
        
        
        case 'AWOLCount':
            if (allowedToOpen(608,'1rtc')){ $deptcondition='1=1'; }
                       
                       $title='AWOL Count';
                       
                       $formdesc='</i><a href="../hr/printawol.php">Print AWOL Letters</a><i>';
                       $formdesc.='<br><br>For the last 30 days only';
                    
                       $sql='SELECT a.IDNo,GROUP_CONCAT(a.DateToday ORDER BY a.DateToday DESC SEPARATOR "<br>") AS DateAWOL,COUNT(a.TxnID) AS NumberOfAWOL,FullName,IF(p.deptid IN (2,3,10),Branch,dept) AS Branch from `attend_2attendance` a JOIN attend_30currentpositions p ON a.IDNo=p.IDNo JOIN attend_2attendancedates ad ON a.DateToday=ad.DateToday WHERE '.$deptcondition.' AND LeaveNo=18 AND (a.DateToday BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE()) AND HOUR(NOW())>=Shift GROUP BY IDNo HAVING NumberOfAWOL>=1 ORDER BY DateAWOL DESC, NumberOfAWOL DESC,Branch';

                    //    echo $sql;
                       $columnnames=array('IDNo', 'FullName','Branch','DateAWOL','NumberOfAWOL');
                       $orderby='IDNo';
                       $width="50%";
                       include('../backendphp/layout/displayastable.php');
                       
               break;
	   
}
nodata:
    $link=null; $stmt=null; 
noform:
?>