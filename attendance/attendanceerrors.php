<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!isset($_SESSION['(ak0)'])) {    header ("Location:/index.php?nologin=2");} 
if (!allowedToOpen(array(633,6331,638),'1rtc')){ echo 'No permission'; exit; }
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;

    $showbranches=false;
   

$which=!isset($_GET['w'])?'AttendanceErrors':$_GET['w'];
switch ($which){
    case 'AttendanceErrors':
	$title='Attendance Errors';
	$formdesc='<br></i><h3>No Time In Today</h3><i>';
       
        $sqlmain='SELECT a.*,concat(FirstName,\' \',SurName) as `FullName`, OTType, IF(OTApproval=2,"Pre-approved",IF(OTApproval=1,"HR Approved","")) AS OT_Approval FROM attend_45lookupattend a JOIN `1employees` e ON e.IDNo=a.IDNo LEFT JOIN attend_0ottype ot ON ot.OTTypeNo=a.OTTypeNo ';
		
		$sql=$sqlmain.' WHERE (TimeIn IS NULL) AND DateToday=CURDATE() AND LeaveNo NOT IN (10,14,15,16,19,22,31,32) ORDER BY Branch, FullName ASC';
        $columnnames=array('DateToday', 'IDNo', 'FullName','RemarksDept','RemarksHR', 'Branch'); $width='60%';
        include('../backendphp/layout/displayastable.php'); 
		
		$formdesc='';
		$title='Missing Time In/Out';
        include_once 'attendsql/missingtimeinout.php';
            $sql='SELECT `a`.*, concat(e.FirstName,\' \',e.SurName) as `FullName` FROM attend_41missingtimeinout a JOIN `1employees` e ON `e`.IDNo=`a`.IDNo 
            ORDER BY DateToday, FullName';
            $columnnames=array('TxnID','DateToday', 'IDNo', 'FullName','TimeIn','TimeOut','RemarksDept','RemarksHR','OTType','OT_Approval','LeaveName', 'Branch');$width='100%';
	    include('../backendphp/layout/displayastable.php');
		
		$title='WFH No Response';
            $sql='SELECT wfh.IDNo,Position,IF( p.deptid IN (1,2,3,10),Branch,dept) AS `Branch/Dept`,CONCAT(e.Nickname," ",e.SurName) AS FullName, wfh.DateToday AS WFHDate,WFHTimeIn AS TimeIn,WFHTimeOut AS TimeOut FROM approvals_5wfh wfh JOIN `1employees` e ON wfh.IDNo=e.IDNo JOIN attend_1defaultbranchassign dba ON wfh.IDNo=dba.IDNo JOIN 1branches b ON dba.DefaultBranchAssignNo=b.BranchNo JOIN 1employees e2 ON wfh.RequestedByNo=e2.IDNo LEFT JOIN 1employees e3 ON wfh.ApprovedByNo=e3.IDNo JOIN attend_2attendancedates ad ON wfh.DateToday=ad.DateToday JOIN attend_30latestpositionsinclresigned lpir ON wfh.IDNo=lpir.IDNo JOIN attend_0positions p ON lpir.PositionID=p.PositionID JOIN 1departments d ON p.deptid=d.deptid WHERE Approved=0 AND wfh.DateToday<CURDATE() ORDER BY wfh.DateToday;';
            $columnnames=array('IDNo','DateToday','FullName','Position','TimeIn','TimeOut','Branch/Dept');$width='70%';
	    include('../backendphp/layout/displayastable.php');
		
		$title='With Attendance on Restday'; $width='60%';
		$sql=$sqlmain.' WHERE (OTApproval=0 OR a.OTTypeNo=0) AND LeaveNo=15 AND TimeIn IS NOT NULL AND TimeOut IS NOT NULL ORDER BY DateToday DESC, Branch, FullName ASC';
        $columnnames=array('DateToday', 'IDNo', 'FullName','RemarksDept','RemarksHR', 'Branch'); 
        include('../backendphp/layout/displayastable.php'); 
		
		
		$title='Wrong Time Out'; ;
		$sql=$sqlmain.' WHERE (a.OTTypeNo<>13 AND TimeOut<"10:00") OR (a.OTTypeNo=13 AND TimeOut>"08:00") ORDER BY DateToday DESC, Branch, FullName ASC';
        $columnnames=array('DateToday', 'IDNo', 'FullName','TimeIn','TimeOut','RemarksDept','RemarksHR', 'Branch'); $width='70%';
        include('../backendphp/layout/displayastable.php'); 
		
		
		$title='Wrong Time In'; ;
		$sql=$sqlmain.' WHERE TimeIn>="17:00" ORDER BY DateToday DESC, Branch, FullName ASC';
		// echo $sql;
        $columnnames=array('DateToday', 'IDNo', 'FullName','TimeIn','TimeOut','RemarksDept','RemarksHR', 'Branch'); $width='70%';
        include('../backendphp/layout/displayastable.php'); 
		
		
      break;
    default:

}
 $link=null; $stmt=null; $stmt0=null;
?>