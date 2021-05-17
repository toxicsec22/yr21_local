<?php
if(isset($_SESSION['(ak0)'])){
 if (allowedToOpen(213,'1rtc')) {
	if (allowedToOpen(2131,'1rtc')) { //Rce/jye
		$cond=' AND LatestSupervisorIDNo IN (1001,1002) ';
	}
	else if (allowedToOpen(2133,'1rtc'))  { //Stores Branch Heads
		$stmt0=$link->query('SELECT IFNULL(GROUP_CONCAT(BranchNo),0) AS BranchNo FROM attend_1branchgroups WHERE OpsSpecialist='.$_SESSION['(ak0)'].'');
		$res0=$stmt0->fetch();
		$cond=' AND (p.BranchNo IN ('.$res0['BranchNo'].'))';
	} else if (allowedToOpen(6110,'1rtc')){ //assistants
           $stmt0=$link->query('SELECT deptid FROM attend_0positions WHERE PositionID='.$_SESSION['&pos']);
           $res0=$stmt0->fetch();
           $cond=' AND p.deptid IN ('.$res0['deptid'].')'; }
	else { //LatestSupervisorIDNo
		$cond=' AND p.LatestSupervisorIDNo='.$_SESSION['(ak0)'];
	}
	//First Approval
    $stmtsuper1=$link->query('SELECT lr.*, FullName,LatestSupervisorIDNo, Branch, LeaveName as LeaveType, CONCAT("SLBal: ", IFNULL(SLBal,0)," VLBal: ",IFNULL(VLBal,0)," BirthdayBal: ",IFNULL(BirthdayBal,0)) AS LeaveBalBeforeThisLeave, lr.TimeStamp as RequestTS 
        FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo LEFT JOIN `attend_61leavebal` lb ON lb.IDNo=lr.IDNo
        JOIN `attend_0leavetype` lt ON lt.LeaveNo=lr.LeaveNo WHERE SupervisorApproved=0 '.$cond);
    $datatoshowsuper=$stmtsuper1->fetchAll();
    if ($stmtsuper1->rowCount()>0){
        $colstoshow=array('FullName', 'Branch', 'FromDate', 'ToDate', 'LeaveType', 'Reason', 'LeaveBalBeforeThisLeave','RequestTS'); $coltitle='';
        foreach ($colstoshow as $field) {$coltitle=$coltitle.'<td>' . $field.'</td>'; }
        $msgsuper='<br><div><br>Leave Requests<table bgcolor="FFFFF"><tr>'.$coltitle.'</tr><tr>';
    
		foreach($datatoshowsuper as $rows){
			$cols='';
			foreach ($colstoshow as $field) {$cols=$cols.'<td>' . htmlcharwithbr($fromBRtoN,$rows[$field]).'</td>'; }
				$msgsuper.='<form method="post" action="../attendance/leaverequest.php?w=SupervisorApprove&TxnID='.$rows['TxnID'].'">'.$cols
				.((($rows['LatestSupervisorIDNo']==$_SESSION['(ak0)']) OR (allowedToOpen(2131,'1rtc')))?'<td><input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'" />
				Comments, if any: <input type="text" size=15 name="SupervisorComment" placeholder="blank if no comment"></td>
				<td>&nbsp &nbsp &nbsp<input type="submit" name="submit" value="Approve">&nbsp &nbsp &nbsp<input type="submit" name="submit" value="Deny"></td>':'').'</form>';
			$msgsuper.='</tr><tr>';
			}
			
		echo $msgsuper.'<br></tr></table><br><br></div>';
	}
 
	
	$sqlall='SELECT lr.*,lr.TimeStamp AS RequestTS, FullName, Branch, LeaveName as LeaveType, CONCAT("SLBal: ", IFNULL(SLBal,0)," VLBal: ",IFNULL(VLBal,0)," BirthdayBal: ",IFNULL(BirthdayBal,0)) AS LeaveBalBeforeThisLeave, IF(SupervisorApproved=1,"Approved","Denied") AS SupervisorResponse';
	
	//Final approval 
	// leave requests status before dept head response
	if ((allowedToOpen(2135,'1rtc')) OR (allowedToOpen(2134,'1rtc'))){
			if (allowedToOpen(2135,'1rtc')){ //Ops Manager
				$cond=' AND p.deptid=10';
			}
			else if (allowedToOpen(2134,'1rtc')) { //Acctg Associate
				$cond=' AND p.PositionID IN (13,131,132)';
			}
			$stmtsuper1=$link->query(''.$sqlall.' FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo LEFT JOIN `attend_61leavebal` lb ON lb.IDNo=lr.IDNo JOIN `attend_0leavetype` lt ON lt.LeaveNo=lr.LeaveNo WHERE SupervisorApproved<>0 AND Approved=0 AND MarkasReadByDeptHead=0 AND (Acknowledged=0 OR (HRVerifiedByNo IS NULL)) '.$cond);
			
			$datatoshowsuper=$stmtsuper1->fetchAll();
			if ($stmtsuper1->rowCount()>0){
				$colstoshow=array('FullName', 'Branch', 'FromDate', 'ToDate','RequestTS', 'LeaveType', 'Reason','LeaveBalBeforeThisLeave','SupervisorComment','SupervisorResponse','SupervisorTS'); $coltitle='';
				foreach ($colstoshow as $field) {$coltitle=$coltitle.'<td>' . $field.'</td>'; }
				$msgsuper2='<br><div><br>Leave Requests<table bgcolor="FFFFF"><tr>'.$coltitle.'</tr><tr>';
			foreach($datatoshowsuper as $rows){
				$cols='';
				foreach ($colstoshow as $field) {$cols=$cols.'<td>' . htmlcharwithbr($fromBRtoN,$rows[$field]).'</td>'; }
				$msgsuper2=$msgsuper2.'<form method="post" action="/yr21/attendance/leaverequest.php?w=Approve&TxnID='.$rows['TxnID'].'">'.$cols
		.'<td><input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'" />
		Comments, if any: <input type="text" size=15 name="ApproveComment" placeholder="blank if no comment"></td>
		<td>&nbsp &nbsp &nbsp<input type="submit" name="submit" value="Approve">&nbsp &nbsp &nbsp<input type="submit" name="submit" value="Deny"></td></form></tr><tr>';
				}
			echo $msgsuper2.'<br></tr></table><br><br></div>';
			
		}
	}
	else { 
	$stmtsuper1=$link->query(''.$sqlall.', IF(p.PositionID IN (13,131,132),"Waiting for final approval","'.(!allowedToOpen(2133,'1rtc')?"Waiting for Dept Head response":"Waiting for Manager response").'") AS Next_Action_Must_Be FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo LEFT JOIN `attend_61leavebal` lb ON lb.IDNo=lr.IDNo JOIN `attend_0leavetype` lt ON lt.LeaveNo=lr.LeaveNo WHERE SupervisorApproved<>0 AND Approved=0 AND MarkasReadByDeptHead=0 AND (Acknowledged=0 OR (HRVerifiedByNo IS NULL)) '.$cond);
		$datatoshowsuper=$stmtsuper1->fetchAll();
		if ($stmtsuper1->rowCount()>0){
			$colstoshow=array('FullName', 'Branch', 'FromDate', 'ToDate','RequestTS', 'LeaveType', 'Reason','LeaveBalBeforeThisLeave','SupervisorComment','SupervisorResponse','SupervisorTS','Next_Action_Must_Be'); $coltitle='';
			foreach ($colstoshow as $field) {$coltitle=$coltitle.'<td>' . $field.'</td>'; }
			$msgsuper2='<br><div><br>Status of Leave Requests (for dept head response)<table bgcolor="FFFFF"><tr>'.$coltitle.'</tr><tr>';
		foreach($datatoshowsuper as $rows){
			$cols='';
			foreach ($colstoshow as $field) {$cols=$cols.'<td>' . htmlcharwithbr($fromBRtoN,$rows[$field]).'</td>'; }
			$msgsuper2=$msgsuper2.$cols.'</tr><tr>';
			}
		echo $msgsuper2.'<br></tr></table><br><br></div>';
	   }
	}
}

  
// LEAVE REQUESTS -- DEPT HEAD
if (allowedToOpen(214,'1rtc')) {
	$stmtsuper2=$link->query('SELECT lr.*, FullName, Branch, LeaveName as LeaveType, CONCAT("SLBal: ", IFNULL(SLBal,0)," VLBal: ",IFNULL(VLBal,0)," BirthdayBal: ",IFNULL(BirthdayBal,0)) AS LeaveBalBeforeThisLeave, IF(SupervisorApproved=1,"Approved",IF(SupervisorApproved=2,"Denied","")) AS SupervisorResponse,IF(Approved=1,"Approved",IF(Approved=2,"Denied","")) AS FinalResponse, e1.Nickname AS FinalApprover, e.Nickname AS Supervisor, lr.TimeStamp as RequestTS FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo
		JOIN `attend_0leavetype` lt ON lt.LeaveNo=lr.LeaveNo JOIN `1employees` e on e.IDNo=lr.SupervisorByNo
		LEFT JOIN `attend_61leavebal` lb ON lb.IDNo=lr.IDNo LEFT JOIN `1employees` e1 on e1.IDNo=lr.ApprovedByNo
		WHERE SupervisorApproved<>0 AND IF(p.PositionID IN (13,131,132,32,37,50,81,33,38),Approved<>0,Approved<>1) AND MarkasReadByDeptHead=0 AND '.((allowedToOpen(2131,'1rtc'))?'deptheadpositionid IN (99,100)':'deptheadpositionid='.$_SESSION['&pos'].'').'');
		
		
	$datatoshowsuper=$stmtsuper2->fetchAll();
	if ($stmtsuper2->rowCount()>0){
		$colstoshow2=array('FullName', 'Branch', 'FromDate', 'ToDate', 'LeaveType', 'Reason','RequestTS', 'LeaveBalBeforeThisLeave', 'SupervisorComment', 'SupervisorResponse', 'Supervisor','SupervisorTS','FinalResponse','FinalApprover','ApproveTS'); $coltitle2='';
		foreach ($colstoshow2 as $field) {$coltitle2=$coltitle2.'<td>' . $field.'</td>'; }
		$msgsuper2='<br><div><br>Leave Requests<table bgcolor="FFFFF"><tr>'.$coltitle2.'</tr><tr>';
	foreach($datatoshowsuper as $rows){
		$cols2='';
		foreach ($colstoshow2 as $field) {$cols2=$cols2.'<td>' . htmlcharwithbr($fromBRtoN,$rows[$field]).'</td>'; }
		// $msgsuper2=$msgsuper2.'<form method="post" action="/yr21/attendance/leaverequest.php?w=Approve&TxnID='.$rows['TxnID'].'">'.$cols2
		$msgsuper2=$msgsuper2.'<form method="post" action="/yr21/attendance/leaverequest.php?w='.($rows['Approved']<>0?'MarkasReadByDeptHead':'Approve').'&TxnID='.$rows['TxnID'].'">'.$cols2
		.'<td><input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'" />'.($rows['Approved']==0?'Comments, if any: <input type="text" size=15 name="ApproveComment" placeholder="blank if no comment">':'').'
		</td>
		<td>&nbsp &nbsp &nbsp<input type="submit" name="submit" value="'.($rows['Approved']<>0?'MarkasReadByDeptHead':'Approve').'">&nbsp &nbsp &nbsp<input type="submit" name="submit" value="'.($rows['Approved']<>0?'Set As Incomplete':'Deny').'"></td></form></tr><tr>';
   }
   echo $msgsuper2.'<br></tr></table><br><br></div>';
   }
   $stmtsuper2=null;
}

// LEAVE REQUESTS -- HR 
if (allowedToOpen(215,'1rtc')) {
    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
    echo comboBox($link,'SELECT * FROM `attend_0leavetype` WHERE LeaveNo NOT IN (11,12,13,15) ORDER BY LeaveName;','LeaveNo','LeaveName','leavetype');
    
    $stmthr=$link->query('SELECT lr.*, FullName, `BranchorDept`, LeaveName as LeaveType, CONCAT("SLBal: ", IFNULL(SLBal,0)," VLBal: ",IFNULL(VLBal,0)," BirthdayBal: ",IFNULL(BirthdayBal,0)) AS LeaveBalBeforeThisLeave, IF(SupervisorApproved=1,"Approved","Denied") AS SupervisorResponse, IF(Approved=1,"Approved","Denied") AS DeptHeadResponse FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo JOIN `attend_0leavetype` lt ON lt.LeaveNo=lr.LeaveNo LEFT JOIN `attend_61leavebal` lb ON lb.IDNo=lr.IDNo WHERE MarkasReadByDeptHead=1 AND HRVerifiedByNo IS NULL');// Acknowledged=1 AND
    
$datatoshowhr=$stmthr->fetchAll();
    if ($stmthr->rowCount()>0){
        $colorcount=0;
        $rcolor[0]="FFFFCC";
        $rcolor[1]="FFFFFF";
        
        $colstoshow=array('FullName', 'BranchorDept', 'FromDate', 'ToDate', 'LeaveType', 'Reason', 'LeaveBalBeforeThisLeave', 'SupervisorComment', 'SupervisorResponse', 'ApproveComment', 'DeptHeadResponse'); $coltitle='';
        foreach ($colstoshow as $field) {$coltitle=$coltitle.'<td style="border: 1px solid black;">' . $field.'</td>'; }
        $msghr='<br><div id="table-wrapper" style="width:89%">Verification of HR<table style="border-collapse: collapse; border: 1px solid black;"><tr>'.$coltitle.'</tr><tr bgcolor='. $rcolor[$colorcount%2].'>';
		// $countrec=0;
    foreach($datatoshowhr as $rows){
        $cols=''; $colorcount++; 
        foreach ($colstoshow as $field) {$cols=$cols.'<td style="border: 1px solid black; font-size: small;">' . htmlcharwithbr($fromBRtoN,$rows[$field]).'</td>'; }
        $msghr.='<form method="post" action="../attendance/leaverequest.php?w=HRVerified&TxnID='.$rows['TxnID'].'">'.$cols.'<td style="padding: 2px; border-bottom: 1px solid black; font-size: small;">';
        if ($rows['Acknowledged']<>0) { $msghr.='<input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'" />
        Revise Leave Type, if needed <input type="text" name="LeaveType" list="leavetype" size=8></td>
        <td style="padding: 2px; font-size: small;">Comments, if any: <input type="text" size=15 name="HRComment" placeholder="blank if no comment"></td>
        <td style="padding: 2px;">&nbsp &nbsp &nbsp<input type="submit" name="submit" value="Verified & Recorded">';
        } else {$msghr.='Requester must acknowledge';}
        $msghr.='</td></form></tr><tr bgcolor='. $rcolor[$colorcount%2].'>';
        // $countrec++;
   }
   $switchboard = $switchboard . $msghr.'<br><b style="color:red;">'.$colorcount.' unfinished leave'.($colorcount>1?'s':'').'.</b><br></tr></table></div>';
    }
    $cntres = $cntres + $stmthr->rowCount();
    $stmthr=null;
}
}
	