<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
//no checking because allowed lahat sa case Status of Leaves
$showbranches=false; include_once('../switchboard/contents.php');

include_once('../switchboard/contents.php');
 
$which=(!isset($_GET['w'])?'RequestLeave':$_GET['w']);
   include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
if (in_array($which,array('RequestLeave','RequestLeaveSuperOrDeptHead'))){
	echo comboBox($link,'SELECT * FROM `attend_0leavetype` WHERE LeaveNo IN (10,14,16,22,30,31,32) ORDER BY LeaveName;','LeaveNo','LeaveName','leavetype');
	
}
switch ($which){
    case 'SupervisorApprove':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        if (allowedToOpen(214,'1rtc')) { // dept heads will have approval from immediate superior only
            $sql='ApprovedByNo='.$_SESSION['(ak0)'].', Approved='.($_POST['submit']=='Approve'?1:2).', ApproveTS=Now(), ReadByNo='.$_SESSION['(ak0)'].', MarkasReadByDeptHead='.($_POST['submit']=='Approve'?1:2).', ReadTS=Now(), ';
        } else {
			if($_POST['submit']<>'Approve'){
				$sql='Approved=2, ApproveTS=Now(), ApprovedByNo='.$_SESSION['(ak0)'].',';
			} else {
				$sql='';
			}
        }
        $columnstoadd=array('SupervisorComment'); foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='UPDATE `attend_3leaverequest` SET SupervisorByNo='.$_SESSION['(ak0)'].', SupervisorApproved='.($_POST['submit']=='Approve'?1:2).', '.$sql.' SupervisorTS=Now() WHERE TxnID='.$_GET['TxnID']; //echo $sql; exit();
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Approve':
        if ((!allowedToOpen(214,'1rtc')) AND (!allowedToOpen(2135,'1rtc')) AND (!allowedToOpen(2134,'1rtc'))) {   echo 'No permission'; exit;}
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $columnstoadd=array('ApproveComment'); $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		if((allowedToOpen(214,'1rtc')) AND ($_POST['submit']=='Approve')){
			$stmt0=$link->query('SELECT FromPreApproval FROM attend_3leaverequest WHERE TxnID='.$_GET['TxnID'].'');
			$res0=$stmt0->fetch();
			if($res0['FromPreApproval']==1){
				$autoack=',Acknowledged=1,AckTimeStamp=NOW()';
			} else {
				$autoack='';
			}
		} else {
			$autoack='';
		}
        $sql='UPDATE `attend_3leaverequest` SET ApprovedByNo='.$_SESSION['(ak0)'].', Approved='.($_POST['submit']=='Approve'?1:2).', '.((allowedToOpen(214,'1rtc'))?'ReadByNo='.$_SESSION['(ak0)'].', MarkasReadByDeptHead='.($_POST['submit']=='Approve'?1:2).',ReadTS=Now(),':'').' '.$sql.' ApproveTS=Now()'.$autoack.' WHERE TxnID='.$_GET['TxnID']; //echo $sql; exit();
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
	 case 'MarkasReadByDeptHead':
		if (!allowedToOpen(214,'1rtc')) { echo 'No permission'; exit; }
		$stmt0=$link->query('SELECT FromPreApproval FROM attend_3leaverequest WHERE TxnID='.$_GET['TxnID'].'');
		$res0=$stmt0->fetch();
		if($res0['FromPreApproval']==1){
			$autoack=',Acknowledged=1,AckTimeStamp=NOW()';
		} else {
			$autoack='';
		}
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		if ($_POST['submit']=='MarkasReadByDeptHead'){
			$sql='UPDATE `attend_3leaverequest` SET ReadByNo='.$_SESSION['(ak0)'].', MarkasReadByDeptHead=1, ReadTS=Now() '.$autoack.' WHERE Approved<>0 AND TxnID='.$_GET['TxnID'];
		} else {
			$sql='UPDATE `attend_3leaverequest` SET Approved=0, ApprovedByNo=NULL, ApproveTS=NULL WHERE Approved<>0 AND TxnID='.$_GET['TxnID'];
		} //echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:".$_SERVER['HTTP_REFERER']);
	break;
   
    case 'HRVerified':
        // include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        $updateleave=(empty($_POST['LeaveType']))?'':' LeaveNo='.comboBoxValue($link,'attend_0leavetype','LeaveName',addslashes($_POST['LeaveType']),'LeaveNo').', ';
        // EDITING OF ATTENDANCE TO SHOW APPROVED LEAVE MUST BE DONE MANUALLY
        $columnstoadd=array('HRComment'); $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='UPDATE `attend_3leaverequest` SET HRVerifiedByNo='.$_SESSION['(ak0)'].', '.$sql.$updateleave.' HRTS=Now() WHERE TxnID='.$_GET['TxnID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'ApprovedLeaves'://per month per department
        $title='Status of Leaves'; $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'FromDate, Branch, FullName');
        $subtitle='Approved Leaves';$formdesc='';
		
		if (allowedToOpen(214,'1rtc')){
			$formdesc= '<br></i><a href="leaverequest.php?w=DirectApproveLeaves">Approve/Deny Leaves Directly By Dept Head</a><i><br>';
		}
		if (allowedToOpen(array(214,2141,5634),'1rtc')){
			$formdesc.= '<br></i><a href="leaverequest.php?w=RequestLeaveSuperOrDeptHead">Add Pre-Approved Leave</a><i><br>';
		}
        //$formdesc='Only approved leaves that have been verified by HR appear here.<br>';
        $month=(isset($_REQUEST['Month'])?$_REQUEST['Month']:date('m'));
        $action='leaverequest.php?w=ApprovedLeaves&Month='.$month;
        
        if (allowedToOpen(6202,'1rtc')) { $condition=''; } 
        elseif (allowedToOpen(214,'1rtc')) { $condition=' AND p.deptheadpositionid='.$_SESSION['&pos']; }
		else if (allowedToOpen(2133,'1rtc'))  {
			$stmt0=$link->query('SELECT GROUP_CONCAT(BranchNo) AS BranchNo FROM attend_1branchgroups WHERE OpsSpecialist='.$_SESSION['(ak0)'].'');
			$res0=$stmt0->fetch();
			$condition=' AND (p.BranchNo IN ('.$res0['BranchNo'].'))';
		}
        elseif (allowedToOpen(5634,'1rtc')) { $condition=' AND p.deptid IN (10,70)'; }
		
        // elseif (allowedToOpen(213,'1rtc')) { $condition=' AND supervisorpositionid='.$_SESSION['&pos']; }
		
        elseif (allowedToOpen(213,'1rtc')) { $condition=' AND (p.LatestSupervisorIDNo='.$_SESSION['(ak0)'].' OR p.BranchNo IN (SELECT BranchNo FROM attend_1branchgroups WHERE OpsManager='.$_SESSION['(ak0)'].'))'; }
        elseif (allowedToOpen(6201,'1rtc')) {  
        $stmt0=$link->query('SELECT IDNo FROM `attend_30currentpositions` WHERE deptid IN (2,3,4,10) AND PositionID IN (32,37,81,50) AND BranchNo='.$_SESSION['bnum'].' GROUP BY PositionID ORDER BY JobLevelID DESC LIMIT 1;');
           $res0=$stmt0->fetch();
        $cond.=' AND '.$_SESSION['(ak0)'].'='.$res0['IDNo'].' AND p.BranchNo='.$_SESSION['bnum'];}
        else { $condition=' AND lr.IDNo='.$_SESSION['(ak0)'];}
        ?>
            <form method="POST" action="<?php echo $action; ?>" enctype="multipart/form-data">
            For the month (1 - 12):  <input type="text" size=5 name="Month" value="<?php echo $month; ?>"></input>&nbsp
            <input type="submit" name="lookup" value="Lookup"> </form>
        <?php
        $sql='SELECT lr.*, p.FullName,cp2.FullName AS RequestedBy, p.Branch, LeaveName as LeaveType, IF(SupervisorApproved=1,"Approved","Denied") AS SupervisorResponse, IF(Approved=1,"Approved","Denied") AS Decision,IF(FromPreApproval=1,"Yes","") AS PreApproved,IF(MarkasReadByDeptHead<>0,"Yes","") AS ReadByDHead, lr.TimeStamp as RequestTS FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo JOIN `attend_0leavetype` lt ON lt.LeaveNo=lr.LeaveNo LEFT JOIN attend_30currentpositions cp2 ON lr.PARequestedByNo=cp2.IDNo WHERE (HRVerifiedByNo IS NOT NULL) AND Acknowledged<>0 AND Approved=1 AND (MONTH(FromDate)='.$month.' OR MONTH(ToDate)='.$month.') '.$condition. ' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
		// echo $sql;
        $columnnames=array('FromDate', 'ToDate', 'FullName', 'Branch', 'LeaveType','Reason','RequestTS','SupervisorComment','SupervisorResponse','SupervisorTS','ApproveComment','ApproveTS','Decision','ReadByDHead','PreApproved','RequestedBy','HRComment','HRTS');
        $columnsub=$columnnames;
        include('../backendphp/layout/displayastable.php');
        $title='';
        $subtitle='<br><br>Denied Leaves'; unset($formdesc); 
        $sql='SELECT lr.*, p.FullName,cp2.FullName AS RequestedBy, p.Branch, LeaveName as LeaveType, IF(SupervisorApproved=1,"Approved","Denied") AS SupervisorResponse, IF(Approved=1,"Approved","Denied") AS Decision,IF(FromPreApproval=1,"Yes","") AS PreApproved,IF(MarkasReadByDeptHead<>0,"Yes","") AS ReadByDHead, lr.TimeStamp as RequestTS FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo JOIN `attend_0leavetype` lt ON lt.LeaveNo=lr.LeaveNo LEFT JOIN attend_30currentpositions cp2 ON lr.PARequestedByNo=cp2.IDNo WHERE  Acknowledged<>0 AND Approved=2 AND (MONTH(FromDate)='.$month.' OR MONTH(ToDate)='.$month.') '.$condition. ' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
        $columnnames=array('FromDate', 'ToDate', 'FullName', 'Branch', 'LeaveType','Reason','RequestTS','SupervisorComment','SupervisorResponse','SupervisorTS','ApproveComment','ApproveTS','Decision','ReadByDHead','PreApproved','RequestedBy','HRComment','HRTS');
       // unset($sortfield);
        include('../backendphp/layout/displayastable.php');
        $subtitle='<br><br>Unfinished Requests'; $formdesc='Resigned employees not included'; 
        $sql='SELECT lr.*,cp2.FullName AS RequestedBy, p.FullName, p.Branch, LeaveName as LeaveType, IF(SupervisorApproved=0,"Pending",IF(SupervisorApproved=1,"Approved","Denied")) AS SupervisorResponse, IF(Approved=0,"Pending",IF(Approved=1,"Approved","Denied")) AS Decision,IF(FromPreApproval=1,"Yes","") AS PreApproved,IF(MarkasReadByDeptHead<>0,"Yes","") AS ReadByDHead, lr.TimeStamp as RequestTS, IF(Acknowledged=0 AND Approved<>0,"No acknowledgment","") AS RequesterAck, IF(ISNULL(HRVerifiedByNo),"Pending",HRComment) AS HRComment FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo JOIN `attend_0leavetype` lt ON lt.LeaveNo=lr.LeaveNo LEFT JOIN attend_30currentpositions cp2 ON lr.PARequestedByNo=cp2.IDNo WHERE (Approved=0 OR Acknowledged=0 or SupervisorApproved=0 OR (IF(Approved=1,HRVerifiedByNo IS NULL, FALSE))) AND (MONTH(FromDate)='.$month.' OR MONTH(ToDate)='.$month.') '.$condition. ' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC'); 
    //    if($_SESSION['(ak0)']==1002){ echo $sql;}
        $columnnames=array('FromDate', 'ToDate', 'FullName', 'Branch', 'LeaveType','Reason','RequestTS','SupervisorComment','SupervisorResponse','SupervisorTS','ApproveComment','ApproveTS','Decision','ReadByDHead','PreApproved','RequestedBy','RequesterAck','HRComment','HRTS');
     //   unset($sortfield);
        include('../backendphp/layout/displayastable.php');
        break;
		
		case 'DirectApproveLeaves':
		if (!allowedToOpen(214,'1rtc')) { echo 'No Permission'; exit(); }
		$title='Approve/Deny Leaves Directly By Dept Head';
		$editprocess='leaverequest.php?w=SubmitDirectApproveLeaves&TxnID=';
		$editprocesslabel='Approve';
		$addlprocess='leaverequest.php?w=SubmitDirectDenyLeaves&TxnID=';
		$addlprocesslabel='Deny';
		$columnnames=array('FromDate', 'ToDate', 'FullName', 'Branch', 'LeaveType','Reason','RequestTS');
		$sql='SELECT lr.*, FullName,LatestSupervisorIDNo, Branch, LeaveName as LeaveType, lr.TimeStamp as RequestTS FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo LEFT JOIN `attend_61leavebal` sl ON sl.IDNo=lr.IDNo JOIN `attend_0leavetype` lt ON lt.LeaveNo=lr.LeaveNo WHERE (SupervisorApproved=0 OR Approved=0) AND deptheadpositionid='.$_SESSION['&pos'].'';
		include('../backendphp/layout/displayastable.php');
		break;
		
		case 'SubmitDirectApproveLeaves':
		if (!allowedToOpen(214,'1rtc')) { echo 'No Permission'; exit(); }
		$sql='UPDATE `attend_3leaverequest` SET SupervisorApproved=1, SupervisorByNo='.$_SESSION['(ak0)'].', SupervisorTS=NOW(), Approved=1,ApprovedByNo='.$_SESSION['(ak0)'].',ApproveTS=NOW(),MarkasReadByDeptHead=1,ReadByNo='.$_SESSION['(ak0)'].',ReadTS=NOW() WHERE TxnID='.$_GET['TxnID'];
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:".$_SERVER['HTTP_REFERER']);
		break;
		
		case 'SubmitDirectDenyLeaves':
		if (!allowedToOpen(214,'1rtc')) { echo 'No Permission'; exit(); }
		$sql='UPDATE `attend_3leaverequest` SET SupervisorApproved=2, SupervisorByNo='.$_SESSION['(ak0)'].', SupervisorTS=NOW(), Approved=2,ApprovedByNo='.$_SESSION['(ak0)'].',ApproveTS=NOW(),MarkasReadByDeptHead=2,ReadByNo='.$_SESSION['(ak0)'].',ReadTS=NOW() WHERE TxnID='.$_GET['TxnID'];
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:".$_SERVER['HTTP_REFERER']);
		break;
		
		case 'RequestLeaveSuperOrDeptHead':

	if (!allowedToOpen(array(214,2141,5634),'1rtc')){ echo 'No Permission'; exit(); }
            ?>
        <html><head><title>Add Pre-Approved Leave</title></head>
        <body><h4>Add Pre-Approved Leave</h4><br><br>
            
        <?php
        // include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
		
	
		if (allowedToOpen(2131,'1rtc')) { $condition=''; } 
        elseif (allowedToOpen(214,'1rtc')) { $condition=' WHERE deptheadpositionid='.$_SESSION['&pos']; }
		else if (allowedToOpen(2133,'1rtc'))  {
			$stmt0=$link->query('SELECT GROUP_CONCAT(BranchNo) AS BranchNo FROM attend_1branchgroups WHERE OpsSpecialist='.$_SESSION['(ak0)'].'');
			$res0=$stmt0->fetch();
			$condition=' WHERE (BranchNo IN ('.$res0['BranchNo'].'))';
		}
        else { $condition=' WHERE supervisorpositionid='.$_SESSION['&pos']; }
		
          echo comboBox($link,'SELECT CONCAT(SUBSTRING_INDEX(FullName, "-", -1)," (SLBal:",IFNULL(SLBAL,0)," VLBal:",IFNULL(VLBal,0)," BirthdayBal:",IFNULL(BirthdayBal,0),")") AS FullName,cp.IDNo FROM `attend_30currentpositions` cp LEFT JOIN attend_61leavebal lb ON cp.IDNo=lb.IDNo '.$condition.' ORDER BY FullName;','FullName','IDNo','employees');
        ?>
        <form method='post' action='leaverequest.php?w=SubmitSuperOrDeptHead'>
			IDNo <input type='text' name='IDNo' value='' list='employees'>
            From Date <input type='date' name='FromDate' value='<?php echo date('Y-m-d'); ?>'>&nbsp &nbsp &nbsp
            To Date <input type='date' name='ToDate' value='<?php echo date('Y-m-d'); ?>'>&nbsp &nbsp &nbsp
            Reason <input type='text' name='Reason' size=25>&nbsp &nbsp
            Leave Type <input type='text' name='LeaveType' required=true list='leavetype' size=8>&nbsp &nbsp
            <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>" />
            <input type='submit' name='Submit' value='Submit'>
        </form><br><br>
        <?php
        // echo comboBox($link,'SELECT * FROM `attend_0leavetype` WHERE LeaveNo IN (10,14,16,30,31,32) ORDER BY LeaveName;','LeaveNo','LeaveName','leavetype');
		
        $sql='SELECT TxnID, FullName, Branch, FromDate, ToDate, Reason, LeaveName as LeaveType, lr.TimeStamp as RequestTS,CONCAT("SLBal: ",SLBAL," VLBal: ",VLBal," BirthdayBal: ",BirthdayBal) AS LeaveBalBeforeThisLeave FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo
        JOIN `attend_0leavetype` lt ON lt.LeaveNo=lr.LeaveNo
        JOIN attend_61leavebal lb ON lr.IDNo=lb.IDNo
        WHERE lr.FromPreApproval=1 AND HRVerifiedByNo IS NULL AND PARequestedByNo='.$_SESSION['(ak0)'].'';
		
        $title='Pending Verification'; $columnnames=array('FullName', 'Branch', 'FromDate', 'ToDate', 'LeaveType','LeaveBalBeforeThisLeave','Reason');
        $delprocess='leaverequest.php?w=DeleteSuperOrDeptHead&TxnID=';
        include('../backendphp/layout/displayastable.php');
     break;
     
	 case 'SubmitSuperOrDeptHead':
	 if (!allowedToOpen(array(214,2141,5634),'1rtc')){ echo 'No Permission'; exit(); }
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        // include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        $leaveno=comboBoxValue($link,'attend_0leavetype','LeaveName',addslashes($_POST['LeaveType']),'LeaveNo');
        $columnstoadd=array('IDNo','FromDate','ToDate','Reason'); $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		// if head addsql
		 if (allowedToOpen(214,'1rtc')){
			$sql='INSERT INTO `attend_3leaverequest` SET '.$sql.' LeaveNo='.$leaveno.', PARequestedByNo='.$_SESSION['(ak0)'].', ReadTS=NOW(), Acknowledged=1,AckTimeStamp=NOW(),FromPreApproval=1,MarkasReadByDeptHead=1, ReadByNo='.$_SESSION['(ak0)'].',SupervisorByNo='.$_SESSION['(ak0)'].', SupervisorTS=NOW(), SupervisorApproved=1, ApprovedByNo='.$_SESSION['(ak0)'].', ApproveTS=NOW(), Approved=1, TimeStamp=Now()';
		 } else {
			 $sql='INSERT INTO `attend_3leaverequest` SET '.$sql.' LeaveNo='.$leaveno.', PARequestedByNo='.$_SESSION['(ak0)'].', FromPreApproval=1,SupervisorByNo='.$_SESSION['(ak0)'].', SupervisorTS=NOW(), SupervisorApproved=1, TimeStamp=Now()';
		 }
		$stmt=$link->prepare($sql); $stmt->execute();
		
        header('Location:leaverequest.php?w=RequestLeaveSuperOrDeptHead');
    break;
	
	case 'DeleteSuperOrDeptHead':
	if (!allowedToOpen(array(214,2141,5634),'1rtc')){ echo 'No Permission'; exit(); }
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='DELETE FROM `attend_3leaverequest` WHERE TxnID='.$_GET['TxnID'].' AND PARequestedByNo='.$_SESSION['(ak0)'].' AND HRVerifiedByNo IS NULL'; $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;
	
}
 $link=null; $stmt=null; 
?>
</body></html>