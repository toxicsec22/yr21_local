<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';


// check if allowed
$allowed=array(614,615,616,617,6615,6616);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=false; include_once('../switchboard/contents.php');

 $txnid='TxnID';

$whichqry=$_GET['w'];
$_SESSION['Date'] = date('Y-m-d'); //$_SESSION['Date'] = date('Y-m-d',strtotime('tomorrow')); allowed today
$attenddate1=$_SESSION['Date'];

$sqlforselect = 'SELECT * FROM attend_0leavetype';
$stmtselect = $link->query($sqlforselect);
$options='';

while($rowselect = $stmtselect->fetch())
{
	$options .= '<option value="'.$rowselect['LeaveNo'].'">'.$rowselect['LeaveNo'].' - '.$rowselect['LeaveName'].'</option>';
}

if (isset($_REQUEST['AttendDate'])){
		$_SESSION['AttendDate']=$_REQUEST['AttendDate'];
        } else { 
            $_SESSION['AttendDate']= !isset($_SESSION['AttendDate'])?date('Y-m-d',time()):$_SESSION['AttendDate'];
        }

$attenddate=$_SESSION['AttendDate'];



if(in_array($whichqry,array('SetRestday','addremarks','RemarksOfHR','RemarksOfDept','SetShiftsPerBranch'))){
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
}

if(in_array($whichqry,array('SetRestday','RemarksOfHR'))){

	if (allowedToOpen(2133,'1rtc')){
		$deptincondition=' AND BranchNo IN (SELECT BranchNo FROM attend_1branchgroups WHERE OpsSpecialist='.$_SESSION['(ak0)'].')';
	} else {
		$deptincondition='';
	}

	$sqllist='SELECT CONCAT(`e`.`Nickname`," - ",`e`.`FirstName`," ",`e`.`SurName`) AS FullName, e.IDNo FROM `1employees` e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo WHERE e.Resigned=0 '.$deptincondition.'';
	echo comboBox($link,$sqllist,'FullName','IDNo','names');	
}

switch ($whichqry){
		
case 'EncodeAttend':
include_once('../backendphp/layout/showencodedbybutton.php');
    if (!allowedToOpen(615,'1rtc')){ echo 'No permission'; exit;}
	
$title='Encode Attendance';
$pagetouse="prattend.php?calledfrom=6";
$calledfrom=6;
$fieldname='AttendDate';


$txnid='TxnID';


$columnnames=array('DateToday','IDNo','FullName','TimeIn','TimeOut','RemarksHR','OTApproval','OT_Approval','OTTypeNo','OTType','RemarksDept','Branch','Shift','LeaveNo');

if ($showenc==1) { array_push($columnnames,'TIEncby','TInTS','TOEncby','TOTS','HREncby','HRTS'); }

		
$method='GET';

include_once('../backendphp/layout/clickontabletoedithead.php');

?>

    <form method="post" action="encodeattend.php?w=EncodeAttend" enctype="multipart/form-data">
    Choose Date:  <input type="date" name="AttendDate" value=<?php echo $attenddate?>></input> 
    <input type="submit" name="lookup" value="Lookup"> <!--Unposted Data Only--><?php echo str_repeat('&nbsp',20); ?>
    <div style="float:right; width:20%;"><font size="1">Grace Period for Office Personnel:<br>Casimiro, Imus, Molino, Noveleta, AbadSantos - 30 min<br>
Dasmarinas, Fairview, QCMIndanaoAve, Binangonan, Roosevelt, Valenzuela, Zabarte - 1 hr</font></div>

    <div style="float:right; width:12%;"><font size="1">Overtime Approval<br>0 - No Overtime<br>1 - HR Approved OT<br>2 - Pre-Approved OT<br></font></div>

	<div style="float:right; width:15%;"><font size="1">Overtime Types<br>0 - No Overtime<br>10 - Full Shift<br>11 - Pre Shift<br>12 - Post Shift<br>13 - After Midnight<br>23 - Pre and Post Shift<br>24 - Pre and Post Shift after Midnight<br><br></font></div>

    <table style="display: inline-block; border: 1px solid; float: left; ">
<?php

if (allowedToOpen(615,'1rtc')) { ?> <br><br>
	<a href='postattend.php?calledfrom=0&action_token=<?php echo $_SESSION['action_token'];?>&attenddate=<?php echo $attenddate?>'>Post Today's Attendance</a>&nbsp &nbsp &nbsp &nbsp &nbsp
	<a href='encodeattend.php?w=WrongLeaveType&AttendDate=<?php echo $attenddate?>'>Lookup Incorrect Leave Type</a>
	<?php } 
	 /*end if */

$columnstoedit=array('TimeIn','TimeOut');
$columnstoedit2=array('RemarksHR','OTApproval','OTTypeNo');

$columnstoeditselect=array('LeaveNo');

$sqlforselect = 'SELECT * FROM attend_0leavetype';
$stmtselect = $link->query($sqlforselect);
$options='';

while($rowselect = $stmtselect->fetch())
{
	$options .= '<option value="'.$rowselect['LeaveNo'].'">'.$rowselect['LeaveNo'].' - '.$rowselect['LeaveName'].'</option>';
}



$sqlpost = 'SELECT Posted FROM attend_2attendancedates WHERE DateToday="'.$attenddate.'"';
$stmtpost = $link->query($sqlpost);
$rowpost = $stmtpost->fetch();
if ($rowpost['Posted']<>1){
	$showlabel=1;
} else {
	$showlabel=0;
}

if($showlabel==1){
	$addlprocess='prattend.php?edit=1&AttendDate='.$attenddate.'&TxnID='; $addlprocesslabel='8_to_5';
	$editprocess2='prattend.php?edit=4&AttendDate='.$attenddate.'&TxnID=';
	$delprocess='prattend.php?edit=3&AttendDate='.$attenddate.'&TxnID='; $delprocesslabel='Reset';
	$editprocess='prattend.php?edit=2&AttendDate='.$attenddate.'&TxnID='; $editprocesslabel='Enter';
}

$columnsub=$columnnames; 
$tdform=true; // .($showlabel==0?'IF(OTApproval=2,"Pre-approved",IF(OTApproval=1,"HR Approved","")) AS OT_Approval,':'').
$sortfield=(!isset($_POST['sortfield']) OR empty($_POST['sortfield']))?'Branch, FirstName':$_POST['sortfield'];
    $sql='SELECT a.*, IF(OTApproval=2,"Pre-approved",IF(OTApproval=1,"HR Approved","")) AS OT_Approval, TIME_FORMAT(a.TimeIn, "%H:%i") AS TimeIn,TIME_FORMAT(a.TimeOut, "%H:%i") AS TimeOut, concat(FirstName,\' \',SurName) as `FullName`, concat(lt.LeaveNo,\' - \',lt.LeaveName) as `LeaveNo`, IF(a.OTTypeNo<>0,OTType,"") AS OTType FROM attend_45lookupattend a JOIN `1employees` e ON e.IDNo=a.IDNo LEFT JOIN `attend_0leavetype` lt ON a.LeaveNo=lt.LeaveNo 
	JOIN attend_0ottype ot ON ot.OTTypeNo=a.OTTypeNo
	WHERE (((`DateToday`)=\''.$attenddate.'\') and (a.IDNo<>\''.$_SESSION['(ak0)'].'\')) AND deptID NOT IN (SELECT deptID FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].') ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
$title='';
	echo '<div>';
		if($showlabel==1){
			include_once('../backendphp/layout/displayastableeditcellspercolumn.php');
		} else {
			$columnnames=array_diff($columnnames,array('Posted'));
			include_once('../backendphp/layout/displayastable.php');
		}
		
break;

case 'WrongLeaveType':
    if (!allowedToOpen(615,'1rtc')){ echo 'No permission'; exit;}
$title='Wrong Leave Type';	
    $minorswitch='attendanceswitch.php';
    $minorswitchname='Attendance Switchboard';
$attenddate=$_REQUEST['AttendDate'];
$editprocess="editspecifics.php?w=attend&edit=2&AttendDate=".$attenddate."&TxnID=";$editprocesslabel='Edit';
$calledfrom=6;
$fieldname='TxnID';
$method='GET';
include_once('../backendphp/layout/clickontabletoedithead.php');


$txnid='TxnID';
$columnnames=array('TxnID','DateToday','IDNo','TimeIn','TimeOut','RemarksHR','Overtime','LeaveNo');		
$sql='SELECT * FROM attend_2attendance where LeaveNo not in (Select LeaveNo from attend_0leavetype);';
		
include_once('../backendphp/layout/displayastablewithedit.php');
break;

case 'UnpostAttend':
    if (!allowedToOpen(617,'1rtc')){ echo 'No permission'; exit;}
$title='Unpost Attendance';
include_once('../backendphp/layout/clickontabletoedithead.php');
?>
<br><br>
    <form method="post" action="postattend.php?calledfrom=2&action_token=<?php echo $_SESSION['action_token'];?>" enctype="multipart/form-data">
    Choose Date to UNPOST:  <input type="date" name="attenddate" value=<?php echo date('Y-m-d',time()); ?>></input> 
    <input type="submit" name="unpost" value="Unpost">
<?php
break;

case 'SetRestday':
    if (!allowedToOpen(616,'1rtc')){ echo 'No permission'; exit;}
	$addtitle='';
	if (!allowedToOpen(2133,'1rtc')){
		$addtitle='/Shift';
	}
echo '<title>Set Restday'.$addtitle.'</title>'; $title='Updated Attendance';	 
// $sqllist='SELECT CONCAT(`e`.`Nickname`," - ",`e`.`FirstName`," ",`e`.`SurName`) AS FullName, IDNo FROM `1employees` e WHERE e.Resigned=0 ';
// 	echo comboBox($link,$sqllist,'FullName','IDNo','names');
$columnnames=array('DateToday','RemarksDept','RemarksHR','Shift','LeaveName', 'Branch');
?>
<div style='margin-left: 20%;'>
<br><br>
<h4><br>Set Restday</h4>
<br>
    <form method="post" action="postattend.php?calledfrom=4&action_token=<?php echo $_SESSION['action_token'];?>" enctype="multipart/form-data">
    IDNo:<input type="text" name="IDNo" list="names" size=7 autocomplete="off" required="true"> &nbsp;
	Start Date:  <input type="date" name="attenddate" value=<?php echo date('Y-m-d',time()); ?>></input> <br><br>
    <label>Restday</label><br>
	<br><input type=radio name="Restday"  value=1> &nbsp; Sunday
	<br><input type=radio name="Restday"  value=2> &nbsp; Monday
	<br><input type=radio name="Restday"  value=3> &nbsp; Tuesday
	<br><input type=radio name="Restday"  value=4> &nbsp; Wednesday
	<br><input type=radio name="Restday"  value=5> &nbsp; Thursday
	<br><input type=radio name="Restday"  value=6> &nbsp; Friday
	<br><input type=radio name="Restday"  value=7> &nbsp; Saturday
	<br><br>
    <input type="submit" name="set" value=" Set Restday "> <?php 
if (!allowedToOpen(2133,'1rtc')){
?> &nbsp; &nbsp; &nbsp; <input type="submit" name="set" value=" Set Sat as RWS AND Sun as Restdays ">
<?php } ?>
</form>
<?php 
if (!allowedToOpen(2133,'1rtc')){
?>
	<br><br><hr><br><br>
<h4><br>Set Shift</h4>
	<br>
    <form method="post" action="postattend.php?calledfrom=5&action_token=<?php echo $_SESSION['action_token'];?>" enctype="multipart/form-data">
    IDNo:<input type="text" name="IDNo" list="names" size=7 autocomplete="off" required="true"> &nbsp;
	Start Date:  <input type="date" name="attenddate" value=<?php echo date('Y-m-d',time()); ?>></input> <br><br>
    <label>Shift</label><br>
	<!-- <br><input type=radio name="Shift"  value=7> &nbsp; 7:00 am to 4:00 pm -->
	<br><input type=radio name="Shift"  value=8> &nbsp; 8:00 am to 5:00 pm
	<br><input type=radio name="Shift"  value=9> &nbsp; 9:00 am to 6:00 pm
	&nbsp;  &nbsp; 
    <input type="submit" name="set" value=" Set Shift "></form>
<?php } ?>
</div>
<?php
if (isset($_GET['IDNo'])){
$sql='SELECT DateToday, RemarksDept, RemarksHR, Shift, LeaveName, Branch FROM attend_45lookupattend WHERE IDNo='.$_GET['IDNo'].' AND DateToday>=\''.date('Y-m-d',time()).'\' ORDER BY DateToday';
include('../backendphp/layout/displayastable.php');
}

break;

case "AddAttendRecords":
    if (!allowedToOpen(614,'1rtc')){ echo 'No permission'; exit;}
$title='Add/Delete Attendance Records'; $formdesc='Use this only for cases where attendance was not automatically created in <b>New Employee</b>';	

$txnid='TxnID';
$columnnames=array('DateToday','IDNo','TimeIn','TimeOut','RemarksHR','Overtime','Branch','Posted','LeaveNo','LeaveName');
$method='POST';
include_once('../backendphp/layout/clickontabletoedithead.php');
?>

    <form method="post" action="praddemployee.php?calledfrom=2&manual=1" enctype="multipart/form-data">
	ID No<input type='text' name='IDNo' list='employeeid' autocomplete='off'>
	Start Date<input type='date' name='DateHired' value=<?php echo date('Y-m-d',time()); ?>>
	Branch Number<input type='text' name='BranchNo' list='branches' autocomplete='off'>
	Restday (0-Mon, 1-Tue, 2-Wed, 3-Thu, 4-Fri, 5-Sat, 6-Sun)<input type='text' name='RestDay' autocomplete='off'>
	<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" /> 
        <input type='submit' name='submit' value='Submit'>
   </form>
   <br>
   <hr>
   <br>
  
   <h3>Manually change branch of IDNo in attendance records</h3><br> <?php
	if(isset($_GET['msg'])){
		echo '<b><font color="green">'.$_GET['msg'].'</font></b><br>';
	}
   ?>
    <form method="post" action="encodeattend.php?w=ChangeBranchInAttendance" enctype="multipart/form-data">
	ID No<input type='text' name='IDNo' list='employeeid' autocomplete='off' required>
	Start Date<input type='date' name='StartDate' value=<?php echo date('Y-m-d',time()); ?>>
	Branch Number<input type='text' name='BranchNo' list='branches' autocomplete='off' required>
	<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" /> 
        <input type='submit' name='submit' value='Submit' onClick="return confirm('Are You Sure?');">
   </form>
   
   
   <br>
   <hr>
   <br>
  
   <h3>Delete attendance records</h3><br> 
   * Update DateHired in Current Employees and ID Information then delete here.<br><br>
    <form method="post" action="encodeattend.php?w=AddAttendRecords" enctype="multipart/form-data">
	ID No<input type='text' name='IDNo' list='employeeid' autocomplete='off' required>
	StartDate<input type='date' name='StartDate' value=<?php echo date('Y-m-d',time()); ?>>
	<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>" /> 
        <input type='submit' name='btnDelAttendance' value='Submit'>
   </form>
   
   
<?php 
$liststoshow=array('employeeid','branches');
include_once "../generalinfo/lists.inc";


foreach ($liststoshow as $list){renderlist($list);   }

if(isset($_REQUEST['btnDelAttendance'])){
	$sqlmain='SELECT * FROM attend_45lookupattend WHERE IDNo='.$_REQUEST['IDNo'].' AND DateToday';
	$sql=$sqlmain.'<"'.$_REQUEST['StartDate'].'" ORDER BY DateToday';
	$title='';
	
	if(isset($_GET['msg2'])){
		echo '<br><b><font color="green">'.$_GET['msg2'].'</font></b><br>';
	}
	$formdesc='</i><form action="encodeattend.php?w=DeleteAttendance&IDNo='.$_REQUEST['IDNo'].'&StartDate='.$_REQUEST['StartDate'].'" method="POST"><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'"><input type="submit" value="Delete attendance" name="btnDelete"  onClick="return confirm(\'Are You Sure?\');"></form><i>';

	echo '<br><div style="background-color:red;padding:5px; ">';
	include('../backendphp/layout/displayastablenosort.php');
	echo '</div>';
	$sql=$sqlmain.'>="'.$_REQUEST['StartDate'].'" ORDER BY DateToday LIMIT 7 ';
	$title=''; $formdesc='<br>Attendance 7 days after.';
	include('../backendphp/layout/displayastablenosort.php');
	
	exit();
}
	 
if (!isset($_GET['IDNo'])) { goto noform;}
$sql='SELECT * FROM attend_45lookupattend WHERE IDNo='.$_GET['IDNo'].' ORDER BY DateToday';
include_once('../backendphp/layout/displayastable.php');

break;




case 'DeleteAttendance':

	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sqlc='SELECT COUNT(TimeIn) AS cnttimein,COUNT(TimeOut) AS cnttimeout FROM `attend_2attendance` WHERE IDNo='.$_GET['IDNo'].' AND DateToday<"'.$_GET['StartDate'].'"';
	// echo $sqlc;
	$stmtc=$link->query($sqlc);
	$resc=$stmtc->fetch();
	
	if($resc['cnttimein']>0 OR $resc['cnttimeout']>0){
		echo 'Error. Pls check attendance data.';
		exit();
	}
							   
	$sql='DELETE FROM `attend_2attendance` WHERE IDNo='.$_GET['IDNo'].' AND DateToday<"'.$_GET['StartDate'].'"';
	
	// exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location: encodeattend.php?w=AddAttendRecords&msg2=Deleted&btnDelAttendance=1&IDNo='.$_GET['IDNo'].'&StartDate='.$_GET['StartDate'].'');
break;


case 'ChangeBranchInAttendance':

	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
	$sql='UPDATE `attend_2attendance` SET BranchNo='.$_POST['BranchNo'].'
	WHERE IDNo='.$_POST['IDNo'].' AND DateToday>="'.$_POST['StartDate'].'"';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location: encodeattend.php?w=AddAttendRecords&msg=Done');
break;

case 'RemarksOfDept':
	
    if (!allowedToOpen(6615,'1rtc')){ echo 'No permission'; exit;}
    
    $title='Remarks Of Dept';	
 $columnnames=array('DateToday','IDNo','FullName','Shift','IsReliever?','RemarksDept','DEPTEncby','DEPTTS');   
$method='GET';

include_once('../backendphp/layout/clickontabletoedithead.php');
					
					if (isset($_POST['btnFilter']) OR isset($_REQUEST['btnSubmit'])){
						

						if ($_REQUEST['IDNo']=="All"){
						$fullnamesql = '';
						$ae = 'All';
						}else {
						$fullnamesql = ' AND a.IDNo='.$_REQUEST['IDNo'] ;
						}	
				$condi = ' AND DateToday>="'.$_REQUEST['sDate'].'" AND DateToday<="'.$_REQUEST['eDate'].'"   '.$fullnamesql.' ';
					}else {
						$condi='';
					}
					// <----->		
					
					if (allowedToOpen(6110,'1rtc')){ 
							   $stmtdeptin=$link->query('SELECT deptid FROM `attend_30currentpositions` WHERE IDNo='.$_SESSION['(ak0)']);
							   $resdeptin=$stmtdeptin->fetch();
							   $deptincondition='OR deptid IN ('.(($resdeptin['deptid']==70)?'70,10':$resdeptin['deptid']).')';
					} elseif (allowedToOpen(2133,'1rtc')){
						$deptincondition='OR BranchNo IN (SELECT BranchNo FROM attend_1branchgroups WHERE OpsSpecialist='.$_SESSION['(ak0)'].')';
					} else {
						$deptincondition='';
					}
					// echo $deptincondition;
					// $columnstoadd=array('DateToday','RemarksDept'); 

					$sql1='SELECT IDNo, FullName FROM attend_30currentpositions WHERE deptid IN (SELECT deptID FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].') '.$deptincondition.' OR deptid IN (SELECT deptid FROM 1departments WHERE deptheadpositionid='.$_SESSION['&pos'].') UNION SELECT "All", "All"';
					echo comboBox($link,$sql1,'FullName','IDNo','names');
					
					
					echo '<table>'; echo'<td style=" border: 1px solid black;">';
					echo '<BR>'; echo '<BR>';
					echo '<h3>For Searching</h3>';echo '<BR>'; echo '<BR>';
					
					echo '<form method="POST" action="encodeattend.php?w=RemarksOfDept">
					IDNo:<input type="text" name="IDNo" list="names" size=7 autocomplete="off" required="true"> &nbsp;
					Date From: <input type="date" name="sDate" value="'.$attenddate1.'"/>
					Date To: <input type="date" name="eDate" value="'.$attenddate1.'">';
					
					echo ' <input type="submit" name="btnFilter" value="Filter">';
					echo '</form>'; 
					echo '</table>';
					
					echo '</BR>';

					echo '<table>'; echo'<td style=" border: 1px solid black;">';
					echo '<BR>'; echo '<BR>';
					echo '<h3>Set Shift';
					if (allowedToOpen(array(2133,6362),'1rtc')){
						
						if(allowedToOpen(2133,'1rtc')){
							echo ' &nbsp; <a href="encodeattend.php?w=SetShiftsPerBranch">Set Shifts Per Branch</a>';
						}
							echo ' &nbsp; <a href="../calendar/shifting.php" target="_blank">Shifting Report</a>';
					}
					echo '</h3>';
					echo 'Only <u>future</u> dates may be set.<BR>'; echo '<BR>';
					$sql1='SELECT IDNo, FullName FROM attend_30currentpositions WHERE deptid IN (SELECT deptID FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].') '.$deptincondition.' OR deptid IN (SELECT deptid FROM 1departments WHERE deptheadpositionid='.$_SESSION['&pos'].') ';
					echo comboBox($link,$sql1,'FullName','IDNo','namesnoall');

					$attendshift=date('Y-m-d',strtotime('tomorrow'));
					echo '<form method="POST" action="encodeattend.php?w=SetShiftByDept">
					IDNo:<input type="text" name="IDNo" list="namesnoall" size=7 autocomplete="off" required="true"> &nbsp;
					Date From: <input type="date" name="sDate" value="'.$attendshift.'"/>
					Date To: <input type="date" name="eDate" value="'.$attendshift.'">
					
					&nbsp; &nbsp; <input type=radio name="Shift"  value=8>  8:00 am to 5:00 pm
					&nbsp; &nbsp; <input type=radio name="Shift"  value=9>  9:00 am to 6:00 pm
					<input type="hidden" name="editby" value="d">
					<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
					&nbsp;  &nbsp; ';
					// removed: &nbsp; &nbsp; Shift &nbsp; <input type=radio name="Shift"  value=7>  7:00 am to 4:00 pm
					echo ' <input type="submit" name="btnSubmit" value="Set Shift">';
					echo '</form>'; 
					echo '</table>';
					
					echo '</BR>';

					echo '<table>'; echo'<td style=" border: 1px solid black;">';
					echo '<BR>'; echo '<BR>';
					echo '<h3>For Remarks</h3>';echo '<BR>'; echo '<BR>';
					
					$sqllist='SELECT BranchNo,Branch FROM 1branches WHERE Active=1 AND BranchNo>0 AND PseudoBranch=0 ';

					echo comboBox($link,$sqllist,'BranchNo','Branch','branches');
					echo '<form method="POST" action="encodeattend.php?w=addremarks">
					IDNo:<input type="text" name="IDNo" list="namesnoall" size=7 autocomplete="off" required="true"> &nbsp;
					Date From: <input type="date" name="sDate" value="'.$attenddate1.'"/>
					Date To: <input type="date" name="eDate" value="'.$attenddate1.'"/>
					
					RemarksDept: <input type="text" name="RemarksDept" placeholder="if reliever, add remarks" required/>';


					if (allowedToOpen(61101,'1rtc')){

					echo '
					<label for="IsReliever">Reliever? </label> 
					<input type="checkbox" id="IsReliever" name="IsReliever" onclick="RelieverCB()">
					<span id="text" style="display:none"><input type="text" name="Branch" list="branches" size="14" placeholder="Choose Branch"></span>';
					}


					echo '
					<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"> <input type="submit" name="btnSubmit" value="add new">';
					echo '</form>'; 
					echo '</table>';
					if (allowedToOpen(61101,'1rtc')){
					?>
					<script>
					function RelieverCB() {
					var checkBox = document.getElementById("IsReliever");
					var text = document.getElementById("text");
					if (checkBox.checked == true){
						text.style.display = "inline";
					} else {
						text.style.display = "none";
					}
					}
					</script>

					<?php
					}
					$title='';
$tdform=true;
$editprocess='prattend.php?edit=5&AttendDate='.$attenddate1.'&TxnID='; $editprocesslabel='Enter';
$columnstoedit2=array('RemarksDept');
    $sql='SELECT a.TxnID,a.DateToday, a.IDNo, RemarksDept, IF(Shift="",8,Shift) AS Shift, a.DEPTTS,IF(a.BranchNo<>(SELECT BranchNo FROM attend_30currentpositions WHERE IDNo=e.IDNo),CONCAT((SELECT Branch FROM attend_30currentpositions WHERE IDNo=e.IDNo)," > ",a.Branch),"") AS `IsReliever?`, concat(e.FirstName,\' \',e.SurName) as `FullName`, concat (e2.Nickname,"",e2.SurName) as DEPTEncby FROM attend_45lookupattend a JOIN `1employees` e ON e.IDNo=a.IDNo LEFT JOIN `1employees` e2 ON a.DEPTEncby=e2.IDNo WHERE (`Posted`=\'0\') AND (deptID IN (SELECT deptID FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].') '.$deptincondition.' OR deptid IN (SELECT deptid FROM 1departments WHERE deptheadpositionid='.$_SESSION['&pos'].') ) '.$condi.' '.((!isset($_POST['btnFilter']) AND !isset($_REQUEST['btnSubmit']))?' AND DateToday="'.$attenddate1.'"':'').'';
	
	echo '<div>';
			include_once('../backendphp/layout/displayastableeditcellspercolumn.php');
    break;
	
case 'addremarks':
		 require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$sql='';
		

		if (allowedToOpen(61101,'1rtc')){
			if(isset($_POST['IsReliever'])){
				$branchno=comboBoxValue($link,'`1branches`','Branch',addslashes($_POST['Branch']),'BranchNo');
				$additionalupdate='BranchNo='.$branchno.',';
				
			}
		}
		
		$remarks2=addslashes($_POST['RemarksDept']);
		
			$sql='UPDATE `attend_2attendance` a JOIN attend_2attendancedates ad ON `a`.DateToday = `ad`.DateToday
			SET '.$additionalupdate.' `a`.RemarksDept = \''.$remarks2.'\',
			`a`.DEPTTS=Now(),
			`a`.DEPTEncby=' . $_SESSION['(ak0)'] .' 
			WHERE `a`.IDNo='.$_POST['IDNo'].' AND (`a`.DateToday BETWEEN \''.$_POST['sDate'].'\' and \''.$_POST['eDate'].'\')  AND `ad`.Posted=0 
			AND `ad`.DateToday>=CURDATE()';
				
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: encodeattend.php?w=RemarksOfDept&sDate='.$_POST['sDate'].'&eDate='.$_POST['eDate'].'&IDNo='.$_POST['IDNo'].'&btnSubmit=1');
	break;
	
	//<---->
	case 'RemarksOfHR':
	if (!allowedToOpen(6616,'1rtc')){ echo 'No permission'; exit;}
	
    $title='Remarks Of HR';	
	
 $columnnames=array('DateToday','IDNo','FullName','RemarksHR','Shift','LeaveNo','HREncby','HRTS');   
$method='GET';

include_once('../backendphp/layout/clickontabletoedithead.php');
					
					if (isset($_POST['btnFilter']) OR isset($_REQUEST['btnSubmit'])){
						

						if (empty($_REQUEST['IDNo'])){ // Set IDNo=0 so all are shown
						$fullnamesql = '';
						$ae = 'All';
						}else {
						$fullnamesql = ' AND a.IDNo='.$_REQUEST['IDNo'] ;
						}	
				$condi = ' AND DateToday>="'.$_REQUEST['sDate'].'" AND DateToday<="'.$_REQUEST['eDate'].'"   '.$fullnamesql.' ';
					}else {
						$condi='';
					}
					// <----->		
					
					$sql1='SELECT LeaveNo, LeaveName FROM attend_0leavetype ';
					$stmt = $link->query($sql1);
					$leaveno=' LeaveNo: <select name="LeaveNo"><option value="All">LeaveNo</option>';
					while($row= $stmt->fetch()) {
					$leaveno.='<option value="'.$row['LeaveNo'].'">'.$row['LeaveNo'].' - '.$row['LeaveName'].'</option>';
					}
					$leaveno.='</select>';
						
					
					
					echo '<table>'; echo'<td style=" border: 1px solid black;">';
					echo '<BR>'; echo '<BR>';
					echo '<h3>For Searching</h3>';echo '<i>To show all, set IDNo as 0.</i><BR><BR>';
					
					echo '<form method="POST" action="encodeattend.php?w=RemarksOfHR">
					IDNo:<input type="text" name="IDNo" list="names" size=7 autocomplete="off" required="true"> &nbsp;
					Date From: <input type="date" name="sDate" value="'.$attenddate1.'"/>
					Date To: <input type="date" name="eDate" value="'.$attenddate1.'">';

					echo ' <input type="submit" name="btnFilter" value="Filter">';
					echo '</form>'; 
					echo '</table>';
					
					echo '</BR>';
					$attendshift=$attenddate1;
					echo '<table>'; echo'<td style=" border: 1px solid black;">';
					echo '<BR>'; echo '<BR>';
					echo '<h3>Set Shift</h3>';echo '<BR>'; echo '<BR>';
					
					echo '<form method="POST" action="encodeattend.php?w=SetShiftByDept">
					IDNo:<input type="text" name="IDNo" list="names" size=7 autocomplete="off" required="true"> &nbsp;
					Date From: <input type="date" name="sDate" value="'.$attendshift.'"/>
					Date To: <input type="date" name="eDate" value="'.$attendshift.'">
					
					&nbsp; &nbsp; <input type=radio name="Shift"  value=8>  8:00 am to 5:00 pm
					&nbsp; &nbsp; <input type=radio name="Shift"  value=9>  9:00 am to 6:00 pm
					<input type="hidden" name="editby" value="h">
					<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
					&nbsp;  &nbsp; ';
					//removed:&nbsp; &nbsp; Shift &nbsp; <input type=radio name="Shift"  value=7>  7:00 am to 4:00 pm

					echo ' <input type="submit" name="btnSubmit" value="Set Shift">';
					echo '</form>'; 
					echo '</table>';
					
					echo '</BR>';

					echo '<table>'; echo'<td style=" border: 1px solid black;">';
					echo '<BR>'; echo '<BR>';
					echo '<h3>For Remarks</h3>';echo '<BR>'; echo '<BR>';
					
					echo '<form method="POST" action="encodeattend.php?w=addremarksHR">
					IDNo:<input type="text" name="IDNo" list="names" size=7 autocomplete="off" required="true"> &nbsp;
					Date From: <input type="date" name="sDate" value="'.$attenddate1.'"/>
					Date To: <input type="date" name="eDate" value="'.$attenddate1.'"/>
					RemarksHR: <input type="text" name="RemarksHR" />
					<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">';
					echo $leaveno;
					echo ' <input type="submit" name="btnSubmit" value="Set Remarks">';
					echo '</form>'; 
					echo '</table>';
					$title='';
$tdform=true;
$editprocess='prattend.php?edit=6&AttendDate='.$attenddate1.'&TxnID='; $editprocesslabel='Enter';
$columnstoedit2=array('RemarksHR');
$columnstoeditselect=array('LeaveNo');


    $sql='SELECT a.TxnID,a.DateToday, a.IDNo, RemarksHR, IF(Shift="",8,Shift) AS Shift, a.HRTS, concat(e.FirstName,\' \',e.SurName) as `FullName` , concat(lt.LeaveNo,\' - \',lt.LeaveName) as `LeaveNo`, concat (e2.Nickname,"",e2.SurName) AS HREncby FROM attend_45lookupattend a JOIN `1employees` e ON e.IDNo=a.IDNo LEFT JOIN `1employees` e2 ON a.HREncby=e2.IDNo LEFT JOIN `attend_0leavetype` lt ON a.LeaveNo=lt.LeaveNo WHERE (`Posted`=\'0\') '.$condi.' '.((!isset($_POST['btnFilter']) AND !isset($_REQUEST['btnSubmit']))?' AND DateToday="'.$attenddate1.'"':'').' ORDER BY FullName ASC';
	
	 // echo $sql; exit();

	echo '<div>';
			include_once('../backendphp/layout/displayastableeditcellspercolumn.php');
    break;
	
case 'addremarksHR':
		 require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $sql0='SELECT LatestDorM FROM `payroll_20latestrates` lr WHERE lr.IDNo='.$_POST['IDNo'];
    $stmt=$link->query($sql0); $res0=$stmt->fetch();
		
		$sql='';
		
		$remarks2=addslashes($_POST['RemarksHR']);
		
			$sql='UPDATE `attend_2attendance` a JOIN attend_2attendancedates ad ON `a`.DateToday = `ad`.DateToday
			SET `a`.RemarksHR = \''.$remarks2.'\',
			`a`.HRTS=Now(),
			`a`.HREncby=' . $_SESSION['(ak0)'] .', 
			`a`.LeaveNo=' . $_POST['LeaveNo'] .' 
			WHERE `a`.IDNo='.$_POST['IDNo'].' AND (`a`.DateToday BETWEEN \''.$_POST['sDate'].'\' and \''.$_POST['eDate'].'\')  AND `ad`.Posted=0 
			AND `ad`.DateToday>CURDATE() AND '.((($res0['LatestDorM']==0) and ($_POST['LeaveNo']==14))?'`a`.LeaveNo NOT IN (12,15)':'`a`.LeaveNo NOT IN (12,13,15)').'';
				
          // echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: encodeattend.php?w=RemarksOfHR&sDate='.$_POST['sDate'].'&eDate='.$_POST['eDate'].'&IDNo='.$_POST['IDNo'].'&btnSubmit=1');
	break;

case 'SetShiftByDept':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$shift=in_array($_POST['Shift'],array(7,8,9))?$_POST['Shift']:8;
		if($_POST['editby']=='h'){ $shiftstart=$_POST['sDate']; $w='RemarksOfHR';}
		else {
			$w='RemarksOfDept';
			 if($_POST['sDate']>date('Y-m-d')) { $shiftstart=$_POST['sDate']; } 
			else { $shiftstart=date('Y-m-d',strtotime('tomorrow')); }
		}

		$sql='UPDATE `attend_2attendance` a JOIN attend_2attendancedates ad ON `a`.DateToday = `ad`.DateToday JOIN attend_30currentpositions p ON a.IDNo=p.IDNo SET a.Shift='.$shift.', HRTS=Now(), HREncby='.$_SESSION['(ak0)'].' WHERE (`a`.DateToday BETWEEN \''.$shiftstart.'\' AND \''.$_POST['eDate'].'\')  AND `ad`.Posted=0  AND a.IDNo='.$_POST['IDNo'].' AND FIND_IN_SET(PositionID,(SELECT AllowedPos FROM permissions_2allprocesses WHERE ProcessID=6361))'; 
		$stmt=$link->prepare($sql); $stmt->execute();	
	   
		if($_POST['editby']=='sreport'){
			header('Location: ../calendar/shifting.php?MonthNo='.$_POST['MonthNo'].'&AreaNo='.$_POST['AreaNo'].'');
			exit();
		}

	   $stmt=$link->prepare($sql); $stmt->execute();
	   header('Location: encodeattend.php?w='.$w.'&sDate='.$_POST['sDate'].'&eDate='.$_POST['eDate'].'&IDNo='.$_POST['IDNo'].'&btnSubmit=1');
   break;



   case 'SetShiftsPerBranch';
	echo '<br>';
	$title='Set Shifts Per Branch';
	echo '<title>'.$title.'</title>';

	echo '<div style="margin-left:25%">';
	echo '<h3>'.$title.'</h3><br>';
	
	$sqllist='SELECT BranchNo,Branch FROM 1branches WHERE Active=1 AND BranchNo>0 AND PseudoBranch=0 AND BranchNo IN (SELECT BranchNo FROM attend_1branchgroups WHERE OpsSpecialist='.$_SESSION['(ak0)'].')';

	echo comboBox($link,$sqllist,'BranchNo','Branch','branches');
   echo '<form action=""><input type="hidden" name="w" value="SetShiftsPerBranch"> Date <b>FROM</b>: <input type="date" name="DateFrom" value="'.(isset($_GET['DateFrom'])?$_GET['DateFrom']:date('Y-m-d',strtotime('tomorrow'))).'"> Date <b>TO</b>: <input type="date" name="DateTo" value="'.(isset($_GET['DateTo'])?$_GET['DateTo']:date('Y-m-d',strtotime('tomorrow'))).'"></span> Branch: <input type="text" name="Branch" list="branches" size="10" value="'.(isset($_GET['Branch'])?$_GET['Branch']:'').'"> <input type="submit" name="btnLookup" value="Lookup"></form>';

   if(isset($_GET['DateFrom'])){
	   $sql='SELECT FullName,Shift,a.IDNo FROM attend_2attendance a JOIN 1branches b ON a.BranchNo=b.BranchNo JOIN attend_30currentpositions cp ON a.IDNo=cp.IDNo WHERE DateToday="'.$_GET['DateFrom'].'" AND b.Branch="'.$_GET['Branch'].'"';
	   $stmt=$link->query($sql);$rows=$stmt->fetchAll();

		if(isset($_GET['DateFrom'])){
			if($_GET['DateFrom']<=date('Y-m-d')){
				echo '<br><font color="red">ERROR! Must be future date.</font>';
				exit();
			}
		}
		echo '<br><br><form action="encodeattend.php?w=SetShiftMultipleProcess&DateFrom='.$_GET['DateFrom'].'&DateTo='.$_GET['DateTo'].'" method="POST">';
		echo '<b><font color="maroon">'.$_GET['Branch'].'</font> From <font color="blue">'.$_GET['DateFrom'].'</font> To <font color="blue">'.$_GET['DateTo'].'</font></b>';
		echo '<table border="1px solid black" style="border-collapse:collapse">';
		echo '<tr style="background-color:white;"><th style="padding:7px;">Employee</th><th style="padding:7px;">Shift 8</th><th style="padding:7px;">Shift 9</th></tr>';

		$padding='style="padding:7px;"';
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
$rcolor[1]="FFFFFF";
$colorcount=0;

		foreach($rows AS $row){
			echo '<tr bgcolor="'. $rcolor[$colorcount%2].'">';
			echo '<td '.$padding.'>'.$row['FullName'].'<input type="hidden" name="IDNo'.$colorcount.'" value="'.$row['IDNo'].'" /></td>';
			// echo '<td '.$padding.'><input type="radio" name="Shift'.$colorcount.'" value="7" '.($row['Shift']==7?'checked':'').'> 7 am - 4 pm</td>';
			echo '<td '.$padding.'><input type="radio" name="Shift'.$colorcount.'" value="8" '.($row['Shift']==8?'checked':'').'> 8 am - 5 pm</td>';
			echo '<td '.$padding.'><input type="radio" name="Shift'.$colorcount.'" value="9" '.($row['Shift']==9?'checked':'').'> 9 am - 6 pm</td>';
			echo '</tr>';
			$colorcount++;
		}
		echo '<input type="hidden" name="shifcnt" value="'.($colorcount).'">';
		echo '<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">';
		echo '<tr><td colspan="3" align="center" '.$padding.'><input style="width:150px;background-color:green;color:white;" type="submit" name="btnSetShift" value="Set Shifts" onclick="return confirm(\'Are you sure?\')"></td></tr>';
		echo '</table>';
		echo '</form>';
		echo '</div>';

   }

   break;

   case 'SetShiftMultipleProcess':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $num2 = 0;
		$num = $_POST['shifcnt'];

        while ($num2 < $num) {
            $shift = $_POST['Shift'.$num2];
			$IDNo = $_POST['IDNo'.$num2];
            $shiftsql="Shift = '".$shift."'";
            $sql = "UPDATE attend_2attendance a JOIN attend_2attendancedates ad ON a.DateToday=ad.DateToday SET ".$shiftsql." WHERE Posted=0 AND a.DateToday>CURDATE() AND a.DateToday BETWEEN \"".$_GET['DateFrom']."\" AND \"".$_GET['DateTo']."\" AND a.IDNo= ".$IDNo."";

			// echo $sql.'<br>';
			$stmt= $link->prepare($sql);
			$stmt->execute();
            $num2++;
        }
    
        header("Location:".$_SERVER['HTTP_REFERER']);

	break;
}
noform:
     $link=null; $stmt=null; 
?>
