<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false; include_once('../switchboard/contents.php');

include_once('../switchboard/contents.php');
 
$which=(!isset($_GET['w'])?'RequestOT':$_GET['w']);
include_once('../backendphp/layout/linkstyle.php');
		echo '<br/><br/><div>';
				
					echo '<a id=\'link\' href="otrequest.php?w=RequestOT">Overtime Request</a> ';
				if (allowedToOpen(6215,'1rtc')) {
					echo '<a id=\'link\' href="otrequest.php?w=OTPerPersonPerPayrollID">Overtime Per Person</a> ';
					echo '<a id=\'link\' href="otrequest.php?w=TotalOTReport">Total OT Per PayrollID</a> ';
				}
				if (allowedToOpen(62121,'1rtc')) {
					echo '<a id=\'link\' href="otrequest.php?w=OverrideTimeOut">Override Time Out</a> ';
				}
		echo '</div><br/>';
if (in_array($which,array('RequestOT','Submit','OTPerPersonPerPayrollID','TotalOTReport'))){
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	$listsql='SELECT PayrollID, concat(PayrollID, " : ", FromDate, " - ", ToDate) as PayPeriod FROM payroll_1paydates;';
	$_POST['payrollid']=(isset($_POST['payrollid'])?$_POST['payrollid']:((date('m')*2)+(date('d')<15?-1:0)));
    echo comboBox($link,$listsql,'PayPeriod','PayrollID','payperiods');
	echo comboBox($link,'SELECT "Regular" AS TypeOfOvertime, 0 AS TypeOfOvertimeValue UNION SELECT "RDOT Regular Hrs" AS TypeOfOvertime, 2 AS TypeOfOvertimeValue UNION SELECT "RDOT Beyond 8 Hrs" AS TypeOfOvertime, 1 AS TypeOfOvertimeValue','TypeOfOvertimeValue','TypeOfOvertime','typeofovertimelist');
}

switch ($which){
   case 'RequestOT':
   $title='Paid Overtime Request';
   echo '<title>'.$title.'</title>';
       echo '
	   <div style="background-color:#cccccc; width:60%; border: 1px solid black; padding:10px;" >
		<b>Overtime Conditions (Present/Fieldwork/RestDay):</b><br>
&nbsp; &nbsp; &nbsp; &nbsp; 1. Less than 17:30 [05:30 PM] is not considered as OT.<br>
&nbsp; &nbsp; &nbsp; &nbsp; 2. Request for OT must be for CURRENT/FUTURE date and time.<br>
&nbsp; &nbsp; &nbsp; &nbsp; 3. For approved OT beyond the request, ONLY HR can override.<br>
&nbsp; &nbsp; &nbsp; &nbsp; 4. No need to request if extended OT (>00:00 [12:00 AM]), ask HR to encode extended OT.<br>
&nbsp; &nbsp; &nbsp; &nbsp; 5. If OT was filed on current date,<br>
	<font style="font-size:9pt;">
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; for REQUESTER: can request until 17:00 [05:00 PM].<br>
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; for APPROVER: can approve until 18:00 [06:00 PM] (Otherwise, request status will tag automatically as "No Response").
	</font><br>
&nbsp; &nbsp; &nbsp; &nbsp; 6. There is a possible demerit if OT has not been requested.<br>
&nbsp; &nbsp; &nbsp; &nbsp; 7. ONLY HR can override all OT on rare occasions.</div><br>';

		
		
		if(isset($_POST['MonthNo'])){ $txndate=$_POST['MonthNo']; } else {
			$txndate=date('m');
		}
		if(isset($_POST['PayrollID'])){ $payrollid=$_POST['PayrollID']; } else {
			$payrollid=$_POST['payrollid'];
		}
		
		$morp='MONTH(ot.DateToday)='.$txndate.'';
		$addlformdesc='Filtered By MonthNo: '.$txndate.'';
		
		if(isset($_POST['PayrollID'])){
			$morp='PayrollID='.$payrollid.''; 
			$addlformdesc='Filtered By PayrollID: '.$payrollid.'';
		}
		
		$withbranchesselect=''; $withbranchestable='';
		if(allowedToOpen(6216,'1rtc')){
			$withbranchesselect=' OR deptid=10';
			$withbranchestable=' OR b.Pseudobranch=0';
		}
		
		$formdesc='</i><br><div><div style="float:left;"><form method="POST" action="#">MonthNo: <input type="text" size="5" name="MonthNo" value="'.$txndate.'"> <input type="submit" value="Lookup"></form></div><div style="margin-left:25%"><form method="POST" action="#">PayrollID: <input type="text" size="5" name="PayrollID" list="payperiods" value="'.$_POST['payrollid'].'"> <input type="submit" value="Lookup"></form></div></div><br><b>'.$addlformdesc.'</b><i>';
		if (allowedToOpen(6212,'1rtc')){
		echo comboBox($link,'SELECT FullName,IDNo FROM `attend_30currentpositions` WHERE deptid = (SELECT deptid FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].') '.$withbranchesselect.' ORDER BY FullName;','FullName','IDNo','employees');
		 echo '<br><h3>'.$title.'</h3>';
        ?>
        <form method='post' action='otrequest.php?w=Submit'>
            Date Today/Future Date <input type='date' name='DateToday' value='<?php echo date('Y-m-d'); ?>'>&nbsp &nbsp &nbsp
            IDNo <input type='text' name='IDNo' value='' list='employees' size=7>&nbsp &nbsp &nbsp
			Type Of Overtime: <input type='text' name='TypeOfOvertime' list="typeofovertimelist" size=9 required> &nbsp &nbsp &nbsp
            EndOfOT <input type='time' name='EndOfOT' value='19:00'> &nbsp &nbsp &nbsp<br>
            Reason <input type='text' name='Reason' size=50> &nbsp &nbsp &nbsp
            <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>" />
            <input type='submit' name='Submit' value='Submit'>
        </form><br>
        <?php
		}
		if(allowedToOpen(6213,'1rtc')){
			$stmtposids=$link->query('SELECT GROUP_CONCAT(DISTINCT(PositionID)) AS PositionIDs FROM attend_30currentpositions WHERE deptheadpositionid='.$_SESSION['&pos'].''); $resposids=$stmtposids->fetch();
			$maincon='IF(lpir.PositionID IN ('.$resposids['PositionIDs'].') AND ot.DateToday>=CURDATE(),1,0)';
		}
		if (allowedToOpen(6213,'1rtc') AND (!allowedToOpen(6215,'1rtc'))){
			$addlcondi=' lpir.PositionID IN ('.$resposids['PositionIDs'].') AND ';
		} else if (allowedToOpen(6215,'1rtc') AND (!allowedToOpen(6213,'1rtc'))){
			$addlcondi=''; $showprocesslabel='';
		} else if ((allowedToOpen(6213,'1rtc'))){
			$addlcondi='';
			
		} else {
			$addlcondi='(ot.RequestedByNo='.$_SESSION['(ak0)'].' OR ot.IDNo='.$_SESSION['(ak0)'].' '.$withbranchestable.') AND ';
			$showprocesslabel='';
		}
		
		if (allowedToOpen(6213,'1rtc')){
			$showprocesslabel=','.$maincon.' AS showeditprocess,'.$maincon.' AS showaddlprocess';
		}
		
        $sqlmain='SELECT if(TypeOfOvertime=0,"Regular",IF(TypeOfOvertime=1,"RDOT Beyond 8 Hrs","RDOT Regular Hrs")) as TypeOfOvertime,ot.TxnID,Position,ApprovedTS,PayrollID,CONCAT(e.Nickname," ",e.SurName) AS FullName,CONCAT(e3.Nickname," ",e3.SurName) AS ApprovedBy,CONCAT(e3.Nickname," ",e3.SurName) AS DeniedBy,ApprovedTS AS DeniedTS,CONCAT(e2.Nickname," ",e2.SurName) AS RequestedBy,RequestedTS, Branch, ot.DateToday AS DateOfOT,EndOfOT, Reason'.$showprocesslabel.' FROM approvals_5ot ot JOIN `1employees` e ON ot.IDNo=e.IDNo JOIN attend_1defaultbranchassign dba ON ot.IDNo=dba.IDNo JOIN 1branches b ON dba.DefaultBranchAssignNo=b.BranchNo JOIN 1employees e2 ON ot.RequestedByNo=e2.IDNo LEFT JOIN 1employees e3 ON ot.ApprovedByNo=e3.IDNo JOIN attend_2attendancedates ad ON ot.DateToday=ad.DateToday JOIN attend_30latestpositionsinclresigned lpir ON ot.IDNo=lpir.IDNo JOIN attend_0positions p ON lpir.PositionID=p.PositionID WHERE '.$addlcondi.' '.$morp.' AND ';
		
        $title='Pending OT Request'; 
		$columnnames=array('FullName','Position','Branch','TypeOfOvertime','DateOfOT','PayrollID','EndOfOT','Reason','RequestedBy','RequestedTS');
		if (allowedToOpen(6212,'1rtc')){
			$delprocess='otrequest.php?w=DeleteRequest&TxnID=';
		}
		if (allowedToOpen(6213,'1rtc')){
			$editprocess='otrequest.php?w=Approve&TxnID='; $editprocesslabel='Approve';
			$addlprocess='otrequest.php?w=Deny&TxnID='; $addlprocesslabel='Deny';
		}
		$sql=$sqlmain.'Approved=0';
		// echo $sql;
        include('../backendphp/layout/displayastable.php');
		
		unset($formdesc,$editprocess,$addlprocess,$delprocess);
		
		$editprocess='otrequest.php?w=Reset&TxnID='; $editprocesslabel='Reset';
		
		$showprocesslabel=',IF(ot.DateToday>=CURDATE(),1,0) AS showeditprocess';
		
		$sqlmain='SELECT if(TypeOfOvertime=0,"Regular",IF(TypeOfOvertime=1,"RDOT Beyond 8 Hrs","RDOT Regular Hrs")) as TypeOfOvertime,ot.TxnID,Position,ApprovedTS,PayrollID,CONCAT(e.Nickname," ",e.SurName) AS FullName,CONCAT(e3.Nickname," ",e3.SurName) AS ApprovedBy,CONCAT(e3.Nickname," ",e3.SurName) AS DeniedBy,ApprovedTS AS DeniedTS,CONCAT(e2.Nickname," ",e2.SurName) AS RequestedBy,RequestedTS, Branch, ot.DateToday AS DateOfOT,EndOfOT, Reason'.$showprocesslabel.' FROM approvals_5ot ot JOIN `1employees` e ON ot.IDNo=e.IDNo JOIN attend_1defaultbranchassign dba ON ot.IDNo=dba.IDNo JOIN 1branches b ON dba.DefaultBranchAssignNo=b.BranchNo JOIN 1employees e2 ON ot.RequestedByNo=e2.IDNo LEFT JOIN 1employees e3 ON ot.ApprovedByNo=e3.IDNo JOIN attend_2attendancedates ad ON ot.DateToday=ad.DateToday JOIN attend_30latestpositionsinclresigned lpir ON ot.IDNo=lpir.IDNo JOIN attend_0positions p ON lpir.PositionID=p.PositionID WHERE '.$addlcondi.' '.$morp.' AND ';
       
        $sql=$sqlmain.'Approved=1';
        $title='Approved OT Request'; 
		$columnnames=array('FullName','Position','Branch','TypeOfOvertime','DateOfOT','EndOfOT','Reason','RequestedBy','RequestedTS','ApprovedBy','ApprovedTS');
        include('../backendphp/layout/displayastable.php');
		
		// unset($editprocess);
		// $sql=str_replace($showprocesslabel,'',$sqlmain).'Approved=2';
		$sql=$sqlmain.'Approved=2';
        $title='Denied OT Request'; 
		$columnnames=array('FullName','Position','Branch','TypeOfOvertime','DateOfOT','EndOfOT','Reason','RequestedBy','RequestedTS','DeniedBy','DeniedTS');
        include('../backendphp/layout/displayastable.php');
		
		unset($editprocess);
		$sql=str_replace($showprocesslabel,'',$sqlmain).'Approved=3';
        $title='"No Response" OT Request'; 
		$columnnames=array('FullName','Position','Branch','TypeOfOvertime','DateOfOT','EndOfOT','Reason','RequestedBy','RequestedTS');
        include('../backendphp/layout/displayastable.php');
		
    break;
		
	
    case 'Submit':
	if (!allowedToOpen(6212,'1rtc')){ echo 'No Permission'; exit(); }
		
		if(''.$_POST['DateToday'].''<''.date('Y-m-d').''){ echo 'Date should be Date Today or Future Date.'; exit();}
		if(''.date("H:i").''>'17:10'){ echo 'Can request until 17:00 [05:00 PM]'; exit(); }
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $columnstoadd=array('IDNo','DateToday','EndOfOT','Reason'); $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		
		if($_POST['TypeOfOvertime']=='Regular'){
			$typeofovertime=0;
		}elseif($_POST['TypeOfOvertime']=='RDOT Regular Hrs'){
			$typeofovertime=2;
		}else{
			$typeofovertime=1;
		}
		// echo $typeofovertime; exit();
        $sql='INSERT INTO `approvals_5ot` SET RequestedByNo='.$_SESSION['(ak0)'].','.$sql.' RequestedTS=NOW(),TypeOfOvertime=\''.$typeofovertime.'\'';
		$stmt=$link->prepare($sql); $stmt->execute();
        header('Location:otrequest.php?w=RequestOT');
    break;
	
	case 'OTPerPersonPerPayrollID':
	$title='Overtime Per Person (Present/Fieldwork/RestDay)'; 
	
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3>';
	$sqlm='SELECT e.IDNo,CONCAT(e.Nickname," ",e.SurName," (",Branch,"/",Position,")") AS FullName FROM `1employees` e JOIN attend_1defaultbranchassign dba ON e.IDNo=dba.IDNo JOIN 1branches b ON dba.DefaultBranchAssignNo=b.BranchNo JOIN attend_30latestpositionsinclresigned lpir ON e.IDNo=lpir.IDNo JOIN attend_0positions p ON lpir.PositionID=p.PositionID';
	$sqle=$sqlm.' WHERE e.IDNo>1002 ORDER BY FullName ASC;';
	
	 echo comboBox($link,$sqle,'FullName','IDNo','employees');
	 
	echo '<br></i><form action="#" method="POST">Employee: <input type="text" name="IDNo" value="" list="employees"> <input type="submit" value="Lookup"></form><i>';
	  if (isset($_POST['IDNo'])){
		$sqlf=$sqlm.' WHERE e.IDNo='.$_POST['IDNo'];  
		$stmt0=$link->query($sqlf); $res0=$stmt0->fetch();
		echo '<br>'.$res0['IDNo'].' : '.$res0['FullName'];
		
            /* TRUNCATE((TIMESTAMPDIFF(MINUTE,(SELECT CONCAT(a.DateToday," 17:00:00")),(SELECT CONCAT(IF(`Overtime`=3,a.DateToday + INTERVAL 1 DAY,a.DateToday)," ",AcceptedOT)))/60),2) AS OTHoursToday, */
			/* Replaced: TRUNCATE(OTHOURSwithDate(CONCAT(a.DateToday," ",(a.Shift+9)),(SELECT CONCAT(IF(`Overtime`=3,a.DateToday + INTERVAL 1 DAY,a.DateToday)," ",AcceptedOT))),2) AS OTHoursToday,*/
	  $sql='SELECT 
        `d`.`PayrollID` AS `PayrollID`,
        `a`.`IDNo` AS `IDNo`,
            `d`.`DateToday`,
            `TimeOut`,
            `ot`.`EndofOT`,
			d.PayrollID,
            IF(`Overtime` IN (2,5),IF(`ot`.`EndofOT` IS NULL,`a`.`TimeOut`,IF(`ot`.`EndofOT`<`a`.`TimeOut`,`ot`.`EndofOT`,`a`.`TimeOut`)), `TimeOut`) AS AcceptedOT,
			TRUNCATE((TIMESTAMPDIFF(MINUTE,CONCAT(a.DateToday," ",(a.Shift+9)),(SELECT CONCAT(IF(`Overtime`=3,a.DateToday + INTERVAL 1 DAY,a.DateToday)," ",AcceptedOT))))/60,2) AS OTHoursToday,
			
			(
	CASE
		WHEN Overtime=0 THEN "No Overtime"
		WHEN Overtime=1 THEN "HR Approved OT"
		WHEN Overtime=2 THEN "Pre-Approved OT"
		WHEN Overtime=4 THEN "Holiday OT"
		WHEN Overtime=5 THEN "RDOT Beyond 8 Hrs"
		ELSE "Extended OT"
	END
	) AS OTDesc
    FROM
        `attend_2attendance` `a`
        JOIN `attend_2attendancedates` `d` ON `d`.`DateToday` = `a`.`DateToday`
        LEFT JOIN `attend_30latestpositionsinclresigned` `e` ON `e`.`IDNo` = `a`.`IDNo`
        LEFT JOIN `approvals_5ot` `ot` ON `a`.`IDNo` = `ot`.`IDNo` AND `a`.`DateToday`=`ot`.`DateToday`
        LEFT JOIN `attend_441legaldays` `l` ON `l`.`IDNo` = `a`.`IDNo`
            AND `l`.`LegalHoliday` = `a`.`DateToday`
        WHERE `a`.`Overtime` <> 0 AND LeaveNo IN (11,20) AND a.IDNo='.$_POST['IDNo'].' GROUP BY a.DateToday ORDER BY a.DateToday;';
		// REMOVED: LEFT JOIN `attend_443semimonthlyempnoattendance` `sm` ON `sm`.`PayrollID` = `d`.`PayrollID` AND `sm`.`IDNo` = `a`.`IDNo` 	
			$title='';
        $columnnames=array('PayrollID','DateToday','OTDesc','TimeOut','EndofOT','AcceptedOT','OTHoursToday');;
		
        include('../backendphp/layout/displayastablenosort.php');
	  }
	break;
	
	
	case 'TotalOTReport':
	
	include_once '../attendance/attendsql/attendsumforpayroll.php';	 
	$title="Total OT Per PayrollID (Present/Fieldwork/RestDays)";
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3>';
	echo '<form action="#" method="POST">PayrollID: <input type="text" size="5" name="payrollid" list="payperiods" value="'.$_POST['payrollid'].'"> <input type="submit" value="Lookup"></form>';
	  $sql='SELECT e.IDNo,CONCAT(e.Nickname," ",e.SurName) AS FullName,Branch,Position,RegOTHrs,ExcessRestHrsOT FROM `1employees` e JOIN attend_1defaultbranchassign dba ON e.IDNo=dba.IDNo JOIN 1branches b ON dba.DefaultBranchAssignNo=b.BranchNo JOIN attend_30latestpositionsinclresigned lpir ON e.IDNo=lpir.IDNo JOIN attend_0positions p ON lpir.PositionID=p.PositionID JOIN attend_44sumforpayroll sp ON e.IDNo=sp.IDNo WHERE PayrollID='.$_POST['payrollid'].' HAVING RegOTHrs<>0 OR ExcessRestHrsOT<>0';
      $title=''; $columnnames=array('IDNo','FullName','Branch','Position','RegOTHrs','ExcessRestHrsOT');
      include('../backendphp/layout/displayastablenosort.php');
	break;
	
    case 'DeleteRequest':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='DELETE FROM `approvals_5ot` WHERE TxnID='.$_GET['TxnID'].' AND RequestedByNo='.$_SESSION['(ak0)'].' AND Approved=0'; $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;
		
    case 'Approve':
	case 'Deny':
        if (!allowedToOpen(6213,'1rtc')) {   echo 'No permission'; exit; }
        $sql='UPDATE `approvals_5ot` SET ApprovedByNo='.$_SESSION['(ak0)'].', Approved='.($which=='Approve'?1:2).', ApprovedTS=Now() WHERE TxnID='.$_GET['TxnID'];
        $stmt=$link->prepare($sql); $stmt->execute();
		
		if($which=='Approve'){
			$stmt0=$link->query('SELECT IDNo,DateToday,TypeOfOvertime FROM approvals_5ot WHERE TxnID='.$_GET['TxnID'].'');
			$res0=$stmt0->fetch();
		
			$sql='UPDATE `attend_2attendance` SET Overtime=IF('.$res0['TypeOfOvertime'].'=0,2,IF('.$res0['TypeOfOvertime'].'=2,1,5)), HREncby='.$_SESSION['(ak0)'].', HRTS=Now() WHERE IDNo='.$res0['IDNo'].' AND DateToday="'.$res0['DateToday'].'"';
			$stmt=$link->prepare($sql); $stmt->execute();
		}
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;
	
    case 'Reset':
        if (!allowedToOpen(6213,'1rtc')) {   echo 'No permission'; exit; }
        $sql='UPDATE `approvals_5ot` SET ApprovedByNo=NULL, Approved=0, ApprovedTS=NULL WHERE TxnID='.$_GET['TxnID'];
        $stmt=$link->prepare($sql); $stmt->execute();
		
		$stmt0=$link->query('SELECT IDNo,DateToday FROM approvals_5ot WHERE TxnID='.$_GET['TxnID'].'');
		$res0=$stmt0->fetch();
	
		$sql='UPDATE `attend_2attendance` SET Overtime=0, HREncby='.$_SESSION['(ak0)'].', HRTS=Now() WHERE IDNo='.$res0['IDNo'].' AND DateToday="'.$res0['DateToday'].'"';
		$stmt=$link->prepare($sql); $stmt->execute();
		
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;
	
	
	case 'OverrideTimeOut':
	

	
$title='Override Time Out';

$ydate=date('Y-m-d',(strtotime ( '-1 day' , strtotime ( date('Y-m-d')) ) ));
if (isset($_REQUEST['AttendDate'])){
		$_SESSION['AttendDate']=$_REQUEST['AttendDate'];
        } else { 
            $_SESSION['AttendDate']= !isset($_SESSION['AttendDate'])? $ydate:$_SESSION['AttendDate'];
        }

$attenddate=$_SESSION['AttendDate'];

$txnid='TxnID';


$columnnames=array('DateToday','IDNo','FullName','TimeIn','TimeOut','RemarksDept','Branch','LeaveNo');


?>

    <form method="post" action="otrequest.php?w=OverrideTimeOut" enctype="multipart/form-data">
    Choose Date:  <input type="date" name="AttendDate" value=<?php echo $attenddate?>></input> 
    <input type="submit" name="lookup" value="Lookup"></form>
   
<?php

$columnstoedit=array('TimeOut','RemarksDept');

if (($attenddate==date('Y-m-d') AND date('H:s')>'20:00') OR ($attenddate==((date('w')==1)?date('Y-m-d',(strtotime ( '-1 day' , strtotime ($ydate) ) )):$ydate) AND date('H:s')<'09:00')){ //!!!
	$showlabel=1;
} else {
	$showlabel=0;
}


$columnsub=$columnnames; 
$formdesc='<br>Notes: <br>&nbsp; &nbsp; &nbsp; WH Supervisor can override time out of the previous workday until 9 am today.<br>&nbsp; &nbsp; &nbsp; Pls ask HR to override if Extended OT.<br>&nbsp; &nbsp; &nbsp; Time Out should be military time (22:00).<br>';

    $sql='SELECT a.*,TIME_FORMAT(a.TimeIn, "%H:%i") AS TimeIn,TIME_FORMAT(a.TimeOut, "%H:%i") AS TimeOut,`FullName`, concat(lt.LeaveNo,\' - \',lt.LeaveName) as `LeaveNo` FROM attend_45lookupattend a JOIN `attend_30currentpositions` cp ON cp.IDNo=a.IDNo JOIN `attend_0leavetype` lt ON a.LeaveNo=lt.LeaveNo JOIN approvals_5ot ot ON (a.IDNo=ot.IDNo AND a.DateToday=ot.DateToday)WHERE (((a.`DateToday`)=\''.$attenddate.'\') and (a.IDNo<>\''.$_SESSION['(ak0)'].'\')) AND LatestSupervisorIDNo='.$_SESSION['(ak0)'].' AND a.LeaveNo IN (11,20) AND a.TimeOut IS NULL AND a.IDNo<>'.$_SESSION['(ak0)'].' AND a.DateToday<=CURDATE() ORDER BY Branch, FullName;';
	
	echo '<div>';
		if($showlabel==1){
			$editprocess='otrequest.php?w=OverrideProcess&AttendDate='.$attenddate.'&TxnID='; $editprocesslabel='Enter';
			include_once('../backendphp/layout/displayastableeditcells.php');
		} else {
			$width='70%';
			include_once('../backendphp/layout/displayastable.php');
		}
	
	break;
	
	
	case 'OverrideProcess':
	if (!allowedToOpen(62121,'1rtc')) { echo 'No Permission'; exit(); }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$txnid=intval($_REQUEST['TxnID']);
			
			$sql='UPDATE attend_2attendance a JOIN attend_2attendancedates ad ON `a`.DateToday = `ad`.DateToday
			SET 
				`a`.Overtime = 1,
				`a`.TimeOut = \''.$_POST['TimeOut'].'\',
				`a`.RemarksDept = \''.addslashes($_POST['RemarksDept']).'\',
				`a`.TOEncby = ' . $_SESSION['(ak0)'] .',
				`a`.TOTS=Now(),
				`a`.DEPTEncby = ' . $_SESSION['(ak0)'] .',
				`a`.DEPTTS=Now(),
				`a`.HREncby = ' . $_SESSION['(ak0)'] .',
				`a`.HRTS=Now()
				WHERE `a`.TxnID='.$txnid.' AND `ad`.Posted=0;';
			
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:otrequest.php?AttendDate=".$_GET['AttendDate']."&w=OverrideTimeOut");
	break;
	
	
}
 $link=null; $stmt=null; 
?>
</body></html>