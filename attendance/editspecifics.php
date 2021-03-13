<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(615,'1rtc')){ echo 'No permission'; exit;}
$showbranches=false; include_once('../switchboard/contents.php');
 
$txnid=intval($_REQUEST['TxnID']);
$method='POST';

$which=$_GET['w'];
switch ($which){
case 'attend':
    if (!allowedToOpen(615,'1rtc')){ echo 'No permission'; exit;}
$attenddate=$_REQUEST['AttendDate'];
$title='Edit Attendance';
   // $minorswitch='attendanceswitch.php';
   // $minorswitchname='Attendance Switchboard';
// $columnnames=array('TxnID','DateToday','IDNo','FullName','TimeIn','TimeOut','RemarksHR','Overtime','Branch','GroupHead','LeaveNo', 'LeaveName');
$columnnames=array('TxnID','DateToday','IDNo','FullName','TimeIn','TimeOut','RemarksHR','Overtime','Branch','LeaveNo', 'LeaveName');
$columnstoedit=array('TimeIn','TimeOut','RemarksHR','Overtime','LeaveNo');
$columnslist=array('LeaveNo'); //not sure which of these is used
$listsname=array('LeaveNo'=>'leaves'); 
$liststoshow=array('leaves');
$action='prattend.php?edit=2&AttendDate='.$attenddate.'&TxnID='.$txnid;
$hiddencolumns=array();

$processblank='prattend.php?edit=3&AttendDate='.$attenddate.'&TxnID='.$txnid;
$processlabelblank='Set as Blank';

$sql='SELECT `attend_45lookupattend`.*, concat(FirstName,\' \',SurName) as `FullName` FROM attend_45lookupattend inner join `1employees` on `1employees`.IDNo=`attend_45lookupattend`.IDNo WHERE (TxnID)='.$txnid;
break;
case 'attend_dates':
    if (!allowedToOpen(631,'1rtc')){ echo 'No permission'; exit;}
$title='Edit Attendance Dates';
$columnnames=array('TxnID','DateToday', 'TypeofDayName','RemarksOnDates','Posted');
$columnstoedit=array('TypeOfDayNo','RemarksOnDates');
$columnslist=array('TypeOfDayNo'); //not sure which of these is used
$listsname=array('TypeOfDayNo'=>'typeofday'); 
$liststoshow=array('typeofday');
$action='preditattend.php?w=attenddates&TxnID='.$txnid;
$hiddencolumns=array();

$processblank='';
$processlabelblank='';

$sql='SELECT d.*,t.TypeofDayName FROM attend_2attendancedates d join `attend_0typeofday` t on t.TypeOfDayNo=d.TypeofDayNo WHERE (Posted=0) and (TxnID)='.$txnid;

break;
default:
break;
}
include('../backendphp/layout/rendersubform.php');
  $link=null; $stmt=null; 
?>
