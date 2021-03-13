<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6217,'1rtc')) { exit();}
$showbranches=false; include_once('../switchboard/contents.php');

include_once('../switchboard/contents.php');
 
$which=(!isset($_GET['w'])?'RequestWFH':$_GET['w']);

include_once('../backendphp/layout/linkstyle.php');
    echo '</br>';
    ?>
<!--buttons -->
    <div>
    <font size=4 face='sans-serif'>
    <?php if (allowedToOpen(622,'1rtc')) {?> 
    <a id="link" href='wfhrequest.php?w=RequestWFH'>Work From Home Request</a><?php echo str_repeat('&nbsp',5)?>
        <?php } ?>
        <?php if (allowedToOpen(6217,'1rtc')) {?> 
    <a id="link" href='wfhrequest.php?w=Guidelines'>Guidelines for WFH</a><?php echo str_repeat('&nbsp',5)?>
        <?php } ?>
        
    </font></div><br>
    <?php

if (in_array($which,array('RequestWFH','Submit','OTPerPersonPerPayrollID','TotalOTReport'))){
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	$listsql='SELECT PayrollID, concat(PayrollID, " : ", FromDate, " - ", ToDate) as PayPeriod FROM payroll_1paydates;';
	$_POST['payrollid']=(isset($_POST['payrollid'])?$_POST['payrollid']:((date('m')*2)+(date('d')<15?-1:0)));
    echo comboBox($link,$listsql,'PayPeriod','PayrollID','payperiods');
}

switch ($which){
   case 'RequestWFH':
   $title='Work From Home Request';
   echo '<title>'.$title.'</title>';
   	
		if(isset($_POST['MonthNo'])){ $txndate=$_POST['MonthNo']; } else {
			$txndate=date('m');
		}
		if(isset($_POST['PayrollID'])){ $payrollid=$_POST['PayrollID']; } else {
			$payrollid=$_POST['payrollid'];
		}
		
		$morp='MONTH(wfh.DateToday)='.$txndate.'';
		$addlformdesc='Filtered By MonthNo: '.$txndate.'';
		
		if(isset($_POST['PayrollID'])){
			$morp='PayrollID='.$payrollid.''; 
			$addlformdesc='Filtered By PayrollID: '.$payrollid.'';
		}
		
		$withbranchesselect=''; $withbranchestable='';
		if(allowedToOpen(6222,'1rtc')){
			$withbranchesselect=' OR deptid=10';
			$withbranchestable=' OR b.Pseudobranch=0';
		}
		$posin='';
		if(allowedToOpen(6219,'1rtc')){
			$stmtposids=$link->query('SELECT GROUP_CONCAT(DISTINCT(PositionID)) AS PositionIDs FROM attend_30currentpositions WHERE deptheadpositionid='.$_SESSION['&pos'].''); $resposids=$stmtposids->fetch();
			$posin=' OR PositionID IN ('.$resposids['PositionIDs'].')';
			
			// $maincon='IF(lpir.PositionID IN ('.$resposids['PositionIDs'].') AND wfh.DateToday>=CURDATE(),1,0)';
			$maincon='IF(lpir.PositionID IN ('.$resposids['PositionIDs'].') AND wfh.RequestedByNo<>'.$_SESSION['(ak0)'].' AND (SELECT Posted FROM attend_2attendancedates WHERE DateToday=wfh.DateToday)=0,1,0)';
		}
		
		$formdesc='</i><br><div><div style="float:left;"><form method="POST" action="#">MonthNo: <input type="text" size="5" name="MonthNo" value="'.$txndate.'"> <input type="submit" value="Lookup"></form></div><div style="margin-left:25%"><form method="POST" action="#">PayrollID: <input type="text" size="5" name="PayrollID" list="payperiods" value="'.$_POST['payrollid'].'"> <input type="submit" value="Lookup"></form></div></div><br><b>'.$addlformdesc.'</b><i>';
		// if (allowedToOpen(6218,'1rtc')){
		echo comboBox($link,'SELECT FullName,IDNo FROM `attend_30currentpositions` WHERE deptid = (SELECT deptid FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].') '.$posin.' '.$withbranchesselect.' ORDER BY FullName;','FullName','IDNo','employees');
		 echo '<br><h3>'.$title.'</h3>';
        ?>
        <form method='post' action='wfhrequest.php?w=Submit'>
            Date Today <input type='date' name='DateToday' value='<?php echo date('Y-m-d'); ?>'>&nbsp &nbsp &nbsp
            IDNo <input type='text' name='IDNo' value='' list='employees'>&nbsp &nbsp &nbsp
            TimeIn <input type='time' name='WFHTimeIn' value='08:00'> &nbsp &nbsp &nbsp
            TimeOut <input type='time' name='WFHTimeOut' value='<?php if (date('D')<>'Sat'){ echo '17:00'; } else { echo '12:00'; } ?>'> &nbsp &nbsp &nbsp <br>
            Reason <input type='text' name='Reason' size=70> &nbsp &nbsp &nbsp
            <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>" />
            <input type='submit' name='Submit' value='Submit'>
        </form><br>
        <?php
		if (allowedToOpen(6219,'1rtc') AND (!allowedToOpen(6221,'1rtc'))){
			$addlcondi=' lpir.PositionID IN ('.$resposids['PositionIDs'].') AND ';
		} else if (allowedToOpen(6221,'1rtc') AND (!allowedToOpen(6219,'1rtc'))){
			$addlcondi=''; $showprocesslabel='';
		} else if ((allowedToOpen(6219,'1rtc'))){
			$addlcondi='';
		} else {
			$addlcondi='(wfh.RequestedByNo='.$_SESSION['(ak0)'].' OR wfh.IDNo='.$_SESSION['(ak0)'].' '.$withbranchestable.') AND ';
			$showprocesslabel='';
		}
		
		if (allowedToOpen(6219,'1rtc')){
			$showprocesslabel=','.$maincon.' AS showeditprocess,'.$maincon.' AS showaddlprocess';
		}
		if (allowedToOpen(6220,'1rtc')){
			$showprocesslabel=',1 AS showeditprocess,1 AS showaddlprocess';
		}
		
        $sqlmain='SELECT wfh.TxnID,Position,IF( p.deptid IN (1,2,3,10),Branch,dept) AS `Branch/Dept`,ApprovedTS,PayrollID,IF((SELECT Posted FROM attend_2attendancedates WHERE DateToday=wfh.DateToday)=1,"To Unpost by HR","") AS `NoResponse?`,CONCAT(e.Nickname," ",e.SurName) AS FullName,CONCAT(e3.Nickname," ",e3.SurName) AS ApprovedBy,CONCAT(e3.Nickname," ",e3.SurName) AS DeniedBy,ApprovedTS AS DeniedTS,CONCAT(e2.Nickname," ",e2.SurName) AS RequestedBy,RequestedTS, Branch, wfh.DateToday AS WFHDate,WFHTimeIn AS TimeIn,WFHTimeOut AS TimeOut, Reason'.$showprocesslabel.' FROM approvals_5wfh wfh JOIN `1employees` e ON wfh.IDNo=e.IDNo JOIN attend_1defaultbranchassign dba ON wfh.IDNo=dba.IDNo JOIN 1branches b ON dba.DefaultBranchAssignNo=b.BranchNo JOIN 1employees e2 ON wfh.RequestedByNo=e2.IDNo LEFT JOIN 1employees e3 ON wfh.ApprovedByNo=e3.IDNo JOIN attend_2attendancedates ad ON wfh.DateToday=ad.DateToday JOIN attend_30latestpositionsinclresigned lpir ON wfh.IDNo=lpir.IDNo JOIN attend_0positions p ON lpir.PositionID=p.PositionID JOIN 1departments d ON p.deptid=d.deptid WHERE '.$addlcondi.' '.$morp.' AND ';
		
        $title='Pending WFH Request'; $columnnames=array('FullName','Position','Branch/Dept','WFHDate','TimeIn','TimeOut','Reason','RequestedBy','RequestedTS','NoResponse?');
		// if (allowedToOpen(6218,'1rtc')){
			$delprocess='wfhrequest.php?w=DeleteRequest&TxnID=';
		// }
		if (allowedToOpen(6219,'1rtc')){
			$editprocess='wfhrequest.php?w=Approve&TxnID='; $editprocesslabel='Approve';
			$addlprocess='wfhrequest.php?w=Deny&TxnID='; $addlprocesslabel='Deny';
		}
		$orderbydesc=' ORDER BY WFHDate,`Branch/Dept`,FullName';
		$sql=$sqlmain.'Approved=0 '.$orderbydesc.'';
		// echo $sql;
        include('../backendphp/layout/displayastable.php');
		
		unset($formdesc,$editprocess,$addlprocess,$delprocess);

		if(allowedToOpen(6219,'1rtc')){
			$editprocess='wfhrequest.php?w=Reset&TxnID='; $editprocesslabel='Reset';
			$showprocesslabel=',IF(wfh.DateToday>=CURDATE(),1,0) AS showeditprocess';
		}
		
		
		$sqlmain='SELECT wfh.TxnID,Position,IF( p.deptid IN (1,2,3,10),Branch,dept) AS `Branch/Dept`,ApprovedTS,PayrollID,CONCAT(e.Nickname," ",e.SurName) AS FullName,CONCAT(e3.Nickname," ",e3.SurName) AS ApprovedBy,CONCAT(e3.Nickname," ",e3.SurName) AS DeniedBy,ApprovedTS AS DeniedTS,CONCAT(e2.Nickname," ",e2.SurName) AS RequestedBy,RequestedTS, Branch, wfh.DateToday AS WFHDate,WFHTimeIn AS TimeIn,WFHTimeOut AS TimeOut, Reason'.$showprocesslabel.' FROM approvals_5wfh wfh JOIN `1employees` e ON wfh.IDNo=e.IDNo JOIN attend_1defaultbranchassign dba ON wfh.IDNo=dba.IDNo JOIN 1branches b ON dba.DefaultBranchAssignNo=b.BranchNo JOIN 1employees e2 ON wfh.RequestedByNo=e2.IDNo LEFT JOIN 1employees e3 ON wfh.ApprovedByNo=e3.IDNo JOIN attend_2attendancedates ad ON wfh.DateToday=ad.DateToday JOIN attend_30latestpositionsinclresigned lpir ON wfh.IDNo=lpir.IDNo JOIN attend_0positions p ON lpir.PositionID=p.PositionID JOIN 1departments d ON p.deptid=d.deptid WHERE '.$addlcondi.' '.$morp.' AND ';
		$orderbydesc=' ORDER BY WFHDate DESC,`Branch/Dept`';
        $sql=$sqlmain.'Approved=1 '.$orderbydesc.'';
        $title='Approved WFH Request'; 
		$columnnames=array('FullName','Position','Branch/Dept','WFHDate','TimeIn','TimeOut','Reason','RequestedBy','RequestedTS','ApprovedBy','ApprovedTS');
        include('../backendphp/layout/displayastable.php');
		
		$sql=$sqlmain.'Approved=2 '.$orderbydesc.'';
        $title='Denied WFH Request'; 
		$columnnames=array('FullName','Position','Branch/Dept','WFHDate','TimeIn','TimeOut','Reason','RequestedBy','RequestedTS','DeniedBy','DeniedTS');
        include('../backendphp/layout/displayastable.php');
		/* 
		unset($editprocess);
		$sql=str_replace($showprocesslabel,'',$sqlmain).'Approved=3';
        $title='"No Response" WFH Request'; 
		$columnnames=array('FullName','Position','Branch','WFHDate','TimeIn','TimeOut','Reason','RequestedBy','RequestedTS');
        include('../backendphp/layout/displayastable.php'); */
		
    break;
		
	
    case 'Submit':
	if (!allowedToOpen(array(6218,6219),'1rtc')){ echo 'No Permission'; exit(); }
		
		
		// if(''.$_POST['DateToday'].''<''.date('Y-m-d').''){ echo 'Date should be Date Today or Future Date.'; exit();}
		if(''.$_POST['DateToday'].''<>''.date('Y-m-d').''){ echo 'Error! Date Today Only.'; exit();}
		// if(''.date("H:i").''>'17:10'){ echo 'Can request until 17:00 [05:00 PM]'; exit(); }
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $columnstoadd=array('IDNo','DateToday','WFHTimeIn','WFHTimeOut','Reason'); $sql='';
		
		
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `approvals_5wfh` SET RequestedByNo='.$_SESSION['(ak0)'].','.$sql.' RequestedTS=NOW()';
		
		if ((allowedToOpen(6219,'1rtc')) AND ($_SESSION['(ak0)']<>$_POST['IDNo'])){
			$sql.=',ApprovedByNo='.$_SESSION['(ak0)'].',Approved=1,ApprovedTS=NOW()';
		
		$stmt=$link->prepare($sql); $stmt->execute();
		
		$sql='UPDATE `attend_2attendance` SET TimeIn="'.$_POST['WFHTimeIn'].'", TimeOut="'.$_POST['WFHTimeOut'].'",LeaveNo=21,TIEncby="'.$_SESSION['(ak0)'].'",TInTS=NOW(),TOEncby="'.$_SESSION['(ak0)'].'",TOTS=NOW(),HREncby="'.$_SESSION['(ak0)'].'",HRTS=NOW() WHERE IDNo="'.$_POST['IDNo'].'" AND DateToday="'.$_POST['DateToday'].'"';
		}
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		
        header('Location:wfhrequest.php?w=RequestWFH');
    break;
	
		
    case 'Approve':
	case 'Deny':
        if (!allowedToOpen(6219,'1rtc')) {   echo 'No permission'; exit; }
        // $sql='UPDATE `approvals_5wfh` SET ApprovedByNo='.$_SESSION['(ak0)'].', Approved='.($which=='Approve'?1:2).', ApprovedTS=Now() WHERE RequestedByNo<>'.$_SESSION['(ak0)'].' AND TxnID='.$_GET['TxnID'];
        $sql='UPDATE `approvals_5wfh` SET ApprovedByNo='.$_SESSION['(ak0)'].', Approved='.($which=='Approve'?1:2).', ApprovedTS=Now() WHERE '.((allowedToOpen(6220,'1rtc'))?'':'RequestedByNo<>'.$_SESSION['(ak0)'].' AND ').' TxnID='.$_GET['TxnID'];
        $stmt=$link->prepare($sql); $stmt->execute();
		
		if($which=='Approve'){
			$stmt0=$link->query('SELECT IDNo,DateToday,WFHTimeIn,WFHTimeOut,DateToday FROM approvals_5wfh WHERE RequestedByNo<>'.$_SESSION['(ak0)'].' AND TxnID='.$_GET['TxnID'].'');
			$res0=$stmt0->fetch();
			if($stmt0->rowCount()>0){
				$sql='UPDATE `attend_2attendance` SET TimeIn="'.$res0['WFHTimeIn'].'", TimeOut="'.$res0['WFHTimeOut'].'",LeaveNo=21,TIEncby="'.$_SESSION['(ak0)'].'",TInTS=NOW(),TOEncby="'.$_SESSION['(ak0)'].'",TOTS=NOW(),HREncby="'.$_SESSION['(ak0)'].'",HRTS=NOW() WHERE IDNo="'.$res0['IDNo'].'" AND DateToday="'.$res0['DateToday'].'"';
			}
			$stmt=$link->prepare($sql); $stmt->execute();
		}
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;
	
    case 'Reset':
        if (!allowedToOpen(6219,'1rtc')) {   echo 'No permission'; exit; }
        $sql='UPDATE `approvals_5wfh` SET ApprovedByNo=NULL, Approved=0, ApprovedTS=NULL WHERE TxnID='.$_GET['TxnID'];
        $stmt=$link->prepare($sql); $stmt->execute();
		
		$stmt0=$link->query('SELECT IDNo,DateToday FROM approvals_5wfh WHERE TxnID='.$_GET['TxnID'].'');
		$res0=$stmt0->fetch();
	
		$sql='UPDATE `attend_2attendance` SET TimeIn=NULL, TimeOut=NULL,LeaveNo=18,TIEncby=NULL,TInTS=NOW(),TOEncby=NULL,TOTS=NOW() WHERE IDNo='.$res0['IDNo'].' AND DateToday="'.$res0['DateToday'].'"';
		$stmt=$link->prepare($sql); $stmt->execute();
		
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;
	
case 'DeleteRequest':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='DELETE FROM `approvals_5wfh` WHERE TxnID='.$_GET['TxnID'].' AND RequestedByNo='.$_SESSION['(ak0)'].' AND Approved=0'; $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;

case 'Guidelines':
    ?>
        <title>Guidelines for WFH</title>
        <style>
    li { padding-bottom: 5px;}
    ol { margin-left: 30px; padding-bottom: 1px;}
    ol li { padding-bottom: 1px;}
</style>
    <div style='background-color: #e6e6e6;
  width: 1100px;
  border: 2px solid grey;
  padding: 25px;
  margin: 25px;'>
        <h3>Guidelines for Work from Home (WFH)</h3><br>
        <h4>Who are allowed?</h4><br>
        Allowed are usually office personnel who can accomplish their regular work with at least 90% completeness while at their respective homes. This is only for unusual circumstances where safety of the employees may be compromised if they go to their place of work.<br><br><br>
        <h4>What are the requirements?</h4><br>
        For an employee to avail, he/she must have access to a computer and to the internet.  These may or may not be provided by the company.<br><br><br>
        <h4>What to do to have effective WFH?</h4><br>
        <ol>
            <li>Create a space in your home that will be your workplace.  Treat this as your office.</li>
            <li>Everyday, do your regular rituals as if going to the office.</li>
            <li>Be presentable.  Tops must be decent.  Minimum of collared shirt for men; blouse and light makeup for women.   Be prepared to go on a teleconference call at any time. Looking decent during work hours is respect for your work and for your colleagues.</li>
            <li>On or before 8:00 a.m, take a selfie with your workspace, and send to your boss with your assignment for the day.  Any late message will be recorded as late.</li>
            <li>At 5:00 p.m., inform your boss of your accomplishments for the day.  Your boss may request regular progress reports during the day.</li>
            <li>If you do not intend to work for the day, or opt to have halfday or undertime work, inform your boss so he/she can indicate it on the remarks of your attendance for HR to record accurately.</li>
            <li>WFH does NOT mean flexitime. Remember, we are still supporting the stores, so we will keep store hours.</li>
        </ol>
</div>
        <?php
       break;
	
}
 $link=null; $stmt=null; 
?>
</body></html>