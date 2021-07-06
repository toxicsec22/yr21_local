<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!isset($_SESSION['(ak0)'])) {    header ("Location:/index.php?nologin=2");} 

include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;

    $showbranches=false;
    
$which=!isset($_GET['w'])?'MyAttendToday':$_GET['w'];
echo (!isset($_GET['nologout'])?'':'<h1><br><br><br>NO ATTENDANCE RECORDED. Please correct all <i><a href="/yr21/acctg/lookupclosingalldates.php">Data Errors</a></i> before leaving.</h1>');
echo (!isset($_GET['msg'])?'':'<h1><br><br><br>UNAUTHORIZED ABSENCE! NO ATTENDANCE RECORDED. You must accomplish a <a href="/yr21/attendance/leaverequest.php?w=RequestLeave">Leave Request</a> before your attendance today will be recorded.  Please call HR for manual Time In AFTER making the leave request.</h1>');
switch ($which){
   /*  case 'NoTimeIn':
        $title='No Time In Today'; $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Branch, FullName');
        $sql='SELECT a.*,concat(FirstName,\' \',SurName) as `FullName` FROM attend_45lookupattend a JOIN `1employees` e ON e.IDNo=a.IDNo WHERE (TimeIn IS NULL) AND DateToday=CURDATE() AND LeaveNo NOT IN (10,14,15,16,19) ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
        $columnnames=array('DateToday', 'IDNo', 'FullName','RemarksDept','RemarksHR', 'Branch'); $columnsub=$columnnames; $width='60%';
        include('../backendphp/layout/displayastable.php');  
      break; */
    default:
    $hidecontents=1;
    $title='My Attendance Today'; $formdesc='';
	    
    $sql='SELECT * FROM attend_45lookupattend WHERE (IDNo='.$_SESSION['(ak0)'].') AND DateToday=CURDATE()';
    $columnnames=array('DateToday', 'IDNo', 'TimeIn','TimeOut','LeaveName', 'Branch');
    echo '<a href="/logout.php">Exit</a>'; $width='50%';
    include('../backendphp/layout/displayastable.php');//include_once('../backendphp/layout/autoclose.php');
	
    $sql0='SELECT GROUP_CONCAT(CurrentlyAssignedIDNo) AS AllAssigned FROM `admin_2vehicleassign`';
    $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
    $vehicleassigned=explode(',', $res0['AllAssigned']);
    
	if (allowedToOpen(8288,'1rtc') OR in_array($_SESSION['(ak0)'], $vehicleassigned)){
		if (allowedToOpen(8285,'1rtc')){ $addlcondi = ' OR 1=1'; goto esc;}//GenAdmin/AdminAssistant
		if (allowedToOpen(8289,'1rtc')){ $addlcondi = ' OR Pseudobranch=0'; goto esc;}//OpsHead
		if (allowedToOpen(8286,'1rtc')){ 
                    $addlcondi = ' OR va.BranchNo IN (SELECT BranchNo FROM attend_1branchgroups WHERE '.$_SESSION['(ak0)'].' IN (FieldSpecialist,BranchSupport,BranchCoordinator))'; 
                    goto esc;}//BranchCoordinator
		if (allowedToOpen(8287,'1rtc')){ $addlcondi = ' OR va.BranchNo='.$_SESSION['bnum']; goto esc;}//BranchHead and OIC
			
		esc:
                

                
        $sql='SELECT CONCAT(Brand,"-", Series) AS Model, VTID, PlateNo, Branch, CONCAT(Nickname," ",Surname) AS AssignedTo FROM admin_1vehiclelist vl
LEFT JOIN admin_1vehiclelistsub vls ON vl.TxnID=vls.TxnID LEFT JOIN admin_2vehicleassign va ON vl.TxnID=va.VehicleID LEFT JOIN
1_gamit.0idinfo id ON va.CurrentlyAssignedIDNo=id.IDNo LEFT JOIN `1branches` b ON va.BranchNo=b.BranchNo WHERE
(RIGHT(PlateNo,1)='.(date('m')==10?0:date('m')).'  AND ((IF(VTID=1,YEAR(ORDate)+1,YEAR(ORDate)+3)<=YEAR(CURDATE())) OR ORPic=0 OR vls.TxnID IS NULL)) AND (va.CurrentlyAssignedIDNo='.$_SESSION['(ak0)'].$addlcondi.') ';
        
		$stmt = $link->query($sql);
                
                if($stmt->rowCount()>0){
                    
                echo '<div align="center" ><font style="font-weight:bold; font-size:large; color:red;"><i><marquee width="50%">Vehicles due to be registered this month.</marquee></i></font>';
		//echo '<style>} </style>';
		echo '<table style="padding: 3px; border-collapse: collapse; border: 2px brown solid; background-color: yellow; "><th align="left">Model</th><th align="left">PlateNo</th><th>Branch</th><th align="left">AssignedTo</th>';
		while($row=$stmt->fetch()) {
			echo '<tr style="padding: 3px; border-collapse: collapse; border: 1px brown solid; "><td>'.$row['Model']. str_repeat('&nbsp;', 5).'</td><td>'.$row['PlateNo'].str_repeat('&nbsp;', 5).'</td><td>'.$row['Branch'].str_repeat('&nbsp;', 5).'</td><td>'.$row['AssignedTo'].'</td></tr>';
		}
		echo '</table></div><br/>';
              //  echo '<style>table, td, th {padding: 3px; border-collapse: collapse; border: 1px black solid; background-color: white;} </style>';
	}
        }
		
		
		// LEAVE REQUESTS -- ACKNOWLEDGEMENT OF REQUESTER
                
		$stmtlr=$link->query('SELECT lr.*, FullName, Branch, LeaveName as LeaveType, CONCAT("SL: ",IFNULL(SLBal,0),"VL: ",IFNULL(VLBal,0),"BirthdayBal: ",IFNULL(BirthdayBal,0)) AS LeaveBalBeforeThisLeave, IF(SupervisorApproved=1,"Approved","Denied") AS SupervisorResponse, IF(SupervisorApproved=1,"Approved","Denied") AS SupervisorResponse, IF(Approved=1,"Approved","Denied") AS DeptHeadResponse FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo JOIN `attend_0leavetype` lt ON lt.LeaveNo=lr.LeaveNo LEFT JOIN `attend_61leavebal` lb ON lb.IDNo=lr.IDNo WHERE SupervisorApproved<>0 AND Approved<>0 AND MarkasReadByDeptHead<>0 AND Acknowledged=0 AND lr.IDNo='.$_SESSION['(ak0)']);

		$datatoshowlr=$stmtlr->fetchAll();
			if ($stmtlr->rowCount()>0){
				$colstoshow=array('FullName', 'Branch', 'FromDate', 'ToDate', 'LeaveType', 'Reason', 'LeaveBalBeforeThisLeave', 'SupervisorComment', 'SupervisorResponse', 'ApproveComment', 'DeptHeadResponse'); $coltitle='';
				foreach ($colstoshow as $field) {$coltitle=$coltitle.'<td>' . $field.'</td>'; }
				include('../backendphp/layout/blink.php');
				$msglr='<br><div id="blink" style="font-weight:bold; font-size:large;"><br><blink>Login to acknowledge so HR can finalized.</blink><table bgcolor="lightgreen"><tr>'.$coltitle.'</tr><tr>';
			foreach($datatoshowlr as $rows){
				$cols='';
				foreach ($colstoshow as $field) {$cols=$cols.'<td>' . htmlcharwithbr($fromBRtoN,$rows[$field]).'</td>'; }
				$msglr=$msglr.'<form method="post" action="/yr21/attendance/leaverequest.php?w=Acknowledge&TxnID='.$rows['TxnID'].'">'.$cols
				.'<td></form></tr><tr>';
		   }
		   echo $msglr.'<br></tr></table><br><br></div>';
		}	
	
    if (in_array(date('d'),array(6,21))){ //first day after cut-off
        echo '<div style="font-weight:bold; font-size:large; color:maroon;"><br><b>Payroll processing will start today.<br><br> Check <a href="tocheckattendance.php?qry=my_attendance&logout=1">your attendance</a> for the payroll period. <br><br> For any change, you must inform HR by 10 am TODAY. After this time, your attendance is <u>final</u>, and you acknowledge that this is the actual basis for your salary.</div><br><br>';
    }
    
    $sql0='SELECT deptid, DATEDIFF(CURDATE(),`DateHired`)/365 AS Tenure, `DateHired` FROM attend_30currentpositions cp JOIN 1employees e ON e.IDNo=cp.IDNo WHERE e.IDNo='.$_SESSION['(ak0)'];
    $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
    
    if($res0['deptid']==10){
    echo '<br><br><h4><font color="darkgreen">All branch employees must <u>call</u> their team leader for Time In AND Time Out while at the branch.</font></h4>'; }
    
    if((date('d', strtotime($res0['DateHired']))==date('d')) and (date('m', strtotime($res0['DateHired']))==date('m'))){
    
    if($res0['Tenure']==1){
        echo '<br><br><h4><font color="red">CONGRATULATIONS on your 1st year WORK ANNIVERSARY!!!</font> <br><br>'
                . '<font color="blue">You are now entitled to Service Incentive Leaves and HMO benefits.</font><br><br></h4>'; 
    } 
    if ($res0['Tenure']>1 AND $res0['Tenure']%5==0){
        $tenure=round($res0['Tenure']);
        function addOrdinalNumberSuffix($num) {
    if (!in_array(($num % 100),array(11,12,13))){
      switch ($num % 10) {
        // Handle 1st, 2nd, 3rd
        case 1:  return $num.'st';
        case 2:  return $num.'nd';
        case 3:  return $num.'rd';
      }
    }
    return $num.'th';
  }
    echo '<br><br><h4><font color="red">CONGRATULATIONS on your '.addOrdinalNumberSuffix($tenure).' year WORK ANNIVERSARY!!!</font> <br><br>'
            . '<font color="blue">You will be among the recipients of Service Awards to be given on December.  Keep up the good work, and continue to be an inspiration to your peers!</font></h4>'; 
        
    }
    }

}
 $link=null; $stmt=null; $stmt0=null;
?>