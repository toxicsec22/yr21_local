<?php
	$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
// check if allowed
$allowed=array(631,6741,6721);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
 $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

$which=$_GET['w'];
switch ($which){
case 'assigned':
	$txnid=intval($_REQUEST['IDNo']);
    if (!allowedToOpen(6721,'1rtc')){
                $columnstoedit=array();
		$edit=0;
	   } 
	header("Location:../generalinfo/employeeinfo.php?calledfrom=4");
	break;
case 'attenddates':
    if (!allowedToOpen(631,'1rtc')){ echo 'No permission'; exit;}
	$txnid=intval($_REQUEST['TxnID']);
	$columnstoedit=array('TypeOfDayNo');
	
	$sqlupdate='Update `attend_2attendancedates` Set ';
	
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	
	$sql=$sqlupdate.$sql.' `RemarksOnDates`=\''.$_POST['RemarksOnDates'].'\'  where TxnID='.$txnid . ' and Posted=0 and `DateToday`>\''.$_SESSION['nb4'].'\''; 
	// echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	if (in_array($_POST['TypeOfDayNo'],array(2,3))){ //legal & spec holidays
		$sql0='Select DateToday from `attend_2attendancedates` where TxnID='.$txnid;
		$stmt=$link->query($sql0);
		$result=$stmt->fetch();
			
                $sql='UPDATE attend_2attendance Set LeaveNo='.($_POST['TypeOfDayNo']+10).' where DateToday=\''.$result['DateToday'].'\'';
		$stmt=$link->prepare($sql);
		$stmt->execute();
	}
	header("Location:tocheckattendance.php?qry=attend_dates");
break;

case 'delentry': //delete team leader change entry
	$sql='DELETE FROM `attend_2changebranchgroup` WHERE `TxnID`='.$_REQUEST['TxnID'].' AND `EncodedByNo`='.$_SESSION['(ak0)'].' AND MONTH(`DateofChange`)=MONTH(CURDATE())'; 
		$stmt=$link->prepare($sql); 		$stmt->execute();
	   
	header("Location:../generalinfo/employeeinfo.php?calledfrom=7");
	break;
}
 $link=null; $stmt=null;
?>