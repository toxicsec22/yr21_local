<?php


$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
include_once($path.'/acrossyrs/dbinit/userinit.php');
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
// check if allowed
$allowed=array(615,625,617,616);
if (!allowedToOpen($allowed,'1rtc')){ echo 'No permission'; exit;}
// end of check
$user=$_SESSION['(ak0)'];
$calledfrom=$_REQUEST['calledfrom'];
switch ($calledfrom) {
case 0: //post from encodeattend.php
    if (!allowedToOpen(615,'1rtc')){ echo 'No permission'; exit;}
	$attenddate=$_REQUEST['attenddate'];
	$sql='Update `attend_2attendancedates` set Posted=1, PostedEncby='.$_SESSION['(ak0)'].', PostedTS=Now() where DateToday=\''.$attenddate.'\'';
	break;
case 1: //post from attendance switchboard
    if (!allowedToOpen(625,'1rtc')){ echo 'No permission'; exit;}
	$payrollid=$_REQUEST['payrollid'];
	$sql='Update `attend_2attendancedates` set Posted=1, PostedEncby='.$_SESSION['(ak0)'].', PostedTS=Now() where PayrollID=\''.$payrollid.'\'';
	break;
case 2: //unpost from attendance switchboard
    if (!allowedToOpen(617,'1rtc')){ echo 'No permission'; exit;}
	$attenddate=$_REQUEST['attenddate'];
	$sql='UPDATE `attend_2attendancedates` set Posted=0 where DateToday=\''.$attenddate.'\'';
	break;
case 4: //set restday
    if (!allowedToOpen(616,'1rtc')){ echo 'No permission'; exit;}
        $attenddate=$_REQUEST['attenddate']; echo $_POST['set'];
        // set restday in employees table
        $sql='UPDATE  `1employees` SET RestDay='.$_POST['Restday'].', TimeStamp=Now(), EncodedByNo='.$_SESSION['(ak0)'].'  WHERE Resigned=0 AND IDNo='.$_POST['IDNo'];
        $stmt=$link->prepare($sql); $stmt->execute(); 

    $condifuture=' AND DateToday>=CURDATE() ';

        // reset future attendance 
	$sql='UPDATE `attend_2attendance` SET LeaveNo=18, HRTS=Now(), HREncby='.$_SESSION['(ak0)'].' where DateToday>=\''.$attenddate.'\' '.$condifuture.' AND IDNo='.$_POST['IDNo'];
        $stmt=$link->prepare($sql); $stmt->execute(); 
        // set restdays
       if($_POST['set']==' Set Sat AND Sun as Restdays/RWS '){ 
           $sql0='SELECT WithSat FROM `1employees` WHERE IDNo='.$_POST['IDNo'].' AND WithSat=0'; //choose which have 2 restdays
           $stmt0=$link->query($sql0); 
           if($stmt0->rowCount()>0){ 
           $sql='UPDATE `attend_2attendance` SET LeaveNo=15, HRTS=Now(), HREncby='.$_SESSION['(ak0)'].' where DateToday>=\''.$attenddate.'\' '.$condifuture.' AND IDNo='.$_POST['IDNo'].' AND (DAYOFWEEK(DateToday) IN (1,7)) AND '.$_POST['IDNo'].' IN (SELECT IDNo FROM `1employees` WHERE WithSat=0)';    }
           else { $sql='UPDATE `attend_2attendance` SET LeaveNo=15, HRTS=Now(), HREncby='.$_SESSION['(ak0)'].' where DateToday>=\''.$attenddate.'\' '.$condifuture.' AND IDNo='.$_POST['IDNo'].' AND DAYOFWEEK(DateToday)=1';}
        } else { 
        $sql='UPDATE `attend_2attendance` SET LeaveNo=15, HRTS=Now(), HREncby='.$_SESSION['(ak0)'].' where DateToday>=\''.$attenddate.'\' '.$condifuture.' AND IDNo='.$_POST['IDNo'].' AND DAYOFWEEK(DateToday)='. $_POST['Restday'];
        }
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:encodeattend.php?w=SetRestday&IDNo=".$_POST['IDNo']); exit;
	break;
case 5: //set shift
    if (!allowedToOpen(616,'1rtc')){ echo 'No permission'; exit;}
    $attenddate=$_REQUEST['attenddate']; 
    $shift=in_array($_POST['Shift'],array(7,8,9))?$_POST['Shift']:8;

    $sql='UPDATE `attend_2attendance` a JOIN attend_2attendancedates ad ON `a`.DateToday = `ad`.DateToday JOIN attend_30currentpositions p ON a.IDNo=p.IDNo SET a.Shift='.$shift.', HRTS=Now(), HREncby='.$_SESSION['(ak0)'].' WHERE a.DateToday>=\''.$attenddate.'\' AND `ad`.Posted=0 AND a.IDNo='.$_POST['IDNo'].' AND FIND_IN_SET(PositionID,(SELECT AllowedPos FROM permissions_2allprocesses WHERE ProcessID=6361))'; 
    $stmt=$link->prepare($sql); $stmt->execute();
    header("Location:encodeattend.php?w=SetRestday&IDNo=".$_POST['IDNo']); exit;
    break;

default:
	goto goback;
	break;
}
$stmt=$link->prepare($sql); $stmt->execute();
goback:
header("Location:".$_SERVER['HTTP_REFERER']);
exitpage:
     $link=null; $stmt=null;
?>