<?php
	$path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
		include_once $path.'/acrossyrs/dbinit/userinit.php';
		$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
	if (!allowedToOpen(816,'1rtc')) {
		echo 'No permission'; exit;}
$whichqry=$_GET['w'];
switch ($whichqry){
    case 'PayDates':
            $txnid=$_REQUEST['PayrollID'];
	
	if (allowedToOpen(8161,'1rtc')){
            $columnstoedit=array('PayrollDate','WorkDays','LegalHolidays','SpecHolidays','Remarks','Posted');
	    $condition='';
            } elseif (allowedToOpen(8162,'1rtc')){
            $columnstoedit=array('PayrollDate','WorkDays','LegalHolidays','SpecHolidays','Remarks');
            } else {
                $columnstoedit=array(); $condition='  and Posted=0 ';
	    }
	$sqlupdate='UPDATE payroll_1paydates SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
		
	}
	$sql=$sqlupdate.$sql.' PostedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() WHERE PayrollID=\''.$txnid . '\''.$condition; 
	if($_SESSION['(ak0)']==1002) { echo $sql;}
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:lookupwithedit.php?w=PayDates&edit=0&done=1");
        break;
    case 'FutureAdj':
        $txnid=$_REQUEST['AdjID'];
	
	if (allowedToOpen(816,'1rtc')){ $columnstoedit=array('PayrollID','IDNo','BranchNo','AdjustTypeNo','Remarks');} else {$columnstoedit=array(); }
	$sqlupdate='UPDATE `payroll_21scheduledpaydayadjustments` SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'] . '\',AdjustAmt=\''.str_replace(',','',$_POST['AdjustAmt']).'\' WHERE AdjID=\''.$txnid . '\';'; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:lookupwithedit.php?w=FutureAdj&edit=0&done=1");
        break;
    
    case 'AttendPerPayID':
            $txnid=intval($_REQUEST['TxnID']);
            //echo $txnid;
            if (allowedToOpen(8163,'1rtc')){
            $columnstoedit=array('LWOPDays','LegalDays','SpecDays','SLDays','VLDays','RestDays','RWSDays','LWPDays','QDays','RegDaysActual','PaidLegalDays','RegExShiftHrsOT','RestShiftHrsOT','SpecShiftHrsOT','LegalShiftHrsOT','RestExShiftHrsOT','SpecExShiftHrsOT','LegalExShiftHrsOT');
            } else {
                $columnstoedit=array();
	    }
            $sqlupdate='UPDATE `payroll_20fromattendance` as a join payroll_1paydates as p on a.PayrollID=p.PayrollID SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' a.' . $field. '=\''.$_POST[$field].'\', ';
	}
	$sql=$sqlupdate.$sql.' a.EncodedByNo=\''.$_SESSION['(ak0)'] . '\' WHERE  p.Posted=0  AND (p.PayrollID NOT IN (Select PayrollID from payroll_26approval WHERE Approved=1)) and a.TxnID=\''.$txnid . '\';'; 
        if($_SESSION['(ak0)']==1002) { echo $sql;}
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:lookupwithedit.php?w=AttendPerPayID&edit=0&done=1");
            break;
    case 'PayrollPerPayID':
            $txnid=intval($_REQUEST['TxnID']);
            //echo $txnid;
            if (allowedToOpen(816,'1rtc')){
           // $columnstoedit=array('BranchNo','Basic','DeM','TaxSh','OT','Remarks','AbsenceBasic','UndertimeBasic','AbsenceTaxSh','UndertimeTaxSh','SSS-EE','SSS-ER','PhilHealth-EE','PhilHealth-ER','PagIbig-EE','PagIbig-ER','WTax','DisburseVia');
		   $columnstoedit=array('BranchNo','RegDayBasic','RegDayDeM','RegDayTaxSh','VLBasic','VLDeM','VLTaxSh','SLBasic','SLDeM','SLTaxSh',
        'LWPBasic','LWPDeM','LWPTaxSh','RHBasicforDaily','RHDeMforDaily','RHTaxShforDaily',
        'AbsenceBasicforMonthly','AbsenceDeMforMonthly','AbsenceTaxShforMonthly',
        'UndertimeBasic','UndertimeDeM','UndertimeTaxSh','RegDayOT','RestDayOT','SpecOT','RHOT','Remarks','SSS-EE','SSS-ER','PhilHealth-EE','PhilHealth-ER','PagIbig-EE','PagIbig-ER','WTax','DisburseVia');
   	    
	    $sqlbranchno='SELECT IF('.$_POST['BranchNo'].' IN (SELECT BranchNo FROM `1branches` WHERE CompanyNo=RCompanyNo),'.$_POST['BranchNo'].',(SELECT BranchNo FROM `1branches` WHERE PseudoBranch=1 AND BranchNo<>95 AND CompanyNo=RCompanyNo)) AS RecordInBranchNo FROM `1employees` e JOIN `payroll_25payroll` p ON e.IDNo=p.IDNo WHERE TxnID='.$txnid; //echo $sqlbranchno;
	    $stmt=$link->query($sqlbranchno);	$resultbranch=$stmt->fetch();
            } else {
                $columnstoedit=array();
	    }
            $sqlupdate='UPDATE `payroll_25payroll` as a join payroll_1paydates as p on a.PayrollID=p.PayrollID SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' a.`' . $field. '`=\''.$_POST[$field].'\', ';
	}
	$sql=$sqlupdate.$sql.' RecordInBranchNo='.$resultbranch['RecordInBranchNo'].', a.EncodedByNo=\''.$_SESSION['(ak0)'] . '\' WHERE  p.Posted=0  AND (p.PayrollID NOT IN (Select PayrollID from payroll_26approval WHERE Approved=1)) and a.TxnID=\''.$txnid . '\';'; 
	// echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
        
	header("Location:lookupwithedit.php?w=PayrollPerPayID&edit=0&done=1");
            break;
    case 'AdjPerPayID':
        $txnid=$_REQUEST['AdjID'];
	
	if (allowedToOpen(816,'1rtc')){
            $columnstoedit=array('IDNo','BranchNo','AdjustTypeNo');
            } else {
                $columnstoedit=array();
	    }
	$sqlupdate='UPDATE `payroll_21paydayadjustments` as a right join `payroll_1paydates` as p on a.PayrollID=p.PayrollID SET a.PayrollID=\''.$_POST['PayrollID'].'\',';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'] . '\',a.Remarks=\''.$_POST['Remarks'].'\',a.AdjustAmt=\''.str_replace(',','',$_POST['AdjustAmt']).'\' WHERE p.Posted=0  AND (p.PayrollID NOT IN (Select PayrollID from payroll_26approval WHERE Approved=1)) and a.AdjID=\''.$txnid . '\';'; 
	// echo $sql; exit();
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:lookupwithedit.php?w=AdjPerPayID&edit=0&done=1");
        break;
   case 'ApprovePayroll';
   case 'ApprovePayrollAll';
	if (!allowedToOpen(8164,'1rtc')) { echo 'No permission'; exit;}
	$payrollid=$_GET['PayrollID']; $co=($whichqry=='ApprovePayrollAll'?'':' AND CompanyNo='.$_GET['Company']);
        $sql='SELECT ap.TxnID FROM payroll_55adjpayroll ap JOIN payroll_50adjattendance a ON a.TxnID=ap.TxnID WHERE SentToPayroll=0 AND AdjInPayrollID='.$payrollid;
        $stmt=$link->query($sql);
        if($stmt->rowCount()>0){ echo '<h3>There are unprocessed payroll adjustments for this payroll ID.</h3>' ; exit();}
        
        $sql='UPDATE payroll_26approval set Approved=1, ApprovedByNo=\''.$_SESSION['(ak0)'] . '\', TimeStamp=NOW()
	WHERE PayrollID='.$payrollid.$co;
        $stmt=$link->prepare($sql); $stmt->execute();
	header("Location:lookupwithedit.php?w=PayrollPerPayID&PayrollID=".$payrollid."&edit=0&done=1");
	break;
case 'RemoveApproval';
	if (!allowedToOpen(8164,'1rtc')) { echo 'No permission'; exit;}
	$payrollid=$_GET['PayrollID']; $co=$_GET['Company'];
        $sql='UPDATE payroll_26approval SET Approved=0, ApprovedByNo=null, TimeStamp=NOW() WHERE CompanyNo='.$co.' AND PayrollID='.$payrollid.
                ' AND PayrollID IN (SELECT PayrollID FROM `payroll_1paydates` WHERE Posted=0)';
        $stmt=$link->prepare($sql); $stmt->execute();
	header("Location:lookupwithedit.php?w=PayrollPerPayID&PayrollID=".$payrollid."&edit=0&done=1");
	break;
case 'SpecCredits':
        $txnid=$_REQUEST['idothercredits'];	
	if (!allowedToOpen(8165,'1rtc')){
            $columnstoedit=array('DateofCredit','IDNo','Amount','Remarks');
            } else {
                $columnstoedit=array();
	    }
	$sqlupdate='UPDATE `payroll_30othercredits` SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'] . '\' WHERE DateofCredit>=CurDate() and idothercredits=\''.$txnid . '\';'; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:lookupwithedit.php?w=SpecCredits&edit=0&done=1");
        break;
case 'DeletePayroll':
	if (!allowedToOpen(8166,'1rtc')){ echo 'No permission'; exit;}
        $payrollid=$_GET['PayrollID'];
        $sql='SELECT ap.TxnID FROM payroll_55adjpayroll ap JOIN payroll_50adjattendance a ON a.TxnID=ap.TxnID WHERE SentToPayroll=1 AND AdjInPayrollID='.$payrollid;
        $stmt=$link->query($sql);
        if($stmt->rowCount()>0){ echo '<h3>To delete, first unset all payroll adjustments for this payroll ID.</h3>' ; exit();}
        $sql='Delete p.* from `payroll_25payroll` as p join `payroll_1paydates` as d on d.PayrollID=p.PayrollID where d.Posted=0 AND (p.PayrollID NOT IN (Select PayrollID from payroll_26approval WHERE Approved=1)) and p.PayrollID='.$payrollid;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
       $sql='UPDATE payroll_32loanssub SET PayrollID=NULL,YearD=NULL WHERE PayrollID='.$payrollid.' AND YearD='.$currentyr;
        $stmt=$link->prepare($sql); 
		$stmt->execute();
	
       if($_SESSION['(ak0)']==1002) { echo $sql;}
       
	header("Location:lookupwithedit.php?w=PayrollPerPayID&edit=0&done=1");
    break;
case 'DeleteAttendBasis':
        if (!allowedToOpen(8166,'1rtc')){ echo 'No permission'; exit;}
        $payrollid=$_GET['PayrollID'];
        $sql='Delete a.* from `payroll_20fromattendance` as a join `payroll_1paydates` as d on d.PayrollID=a.PayrollID where d.Posted=0  AND (a.PayrollID NOT IN (Select PayrollID from payroll_26approval WHERE Approved=1)) and a.PayrollID='.$payrollid;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:lookupwithedit.php?w=AttendPerPayID&edit=0&done=1");
    break;
case 'DelFutureAdj':
        if (!allowedToOpen(8166,'1rtc')){ echo 'No permission'; exit;}
        $txnid=$_GET['AdjID'];
        $sql='DELETE from `payroll_21scheduledpaydayadjustments` WHERE AdjID='.$txnid; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:lookupwithedit.php?w=FutureAdj&edit=0&done=1");
    break;
case 'DelAdjPerPayID':
        if (!allowedToOpen(8166,'1rtc')){ echo 'No permission'; exit;}
        $txnid=$_GET['AdjID'];
        $sql='DELETE a.* from `payroll_21paydayadjustments` as a join `payroll_1paydates` as p on a.PayrollID=p.PayrollID WHERE p.Posted=0  AND (p.PayrollID NOT IN (Select PayrollID from payroll_26approval WHERE Approved=1)) and a.AdjID='.$txnid; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:lookupwithedit.php?w=AdjPerPayID&edit=0&done=1");
    break;
case 'DelSpecCredits':
        if (!allowedToOpen(8166,'1rtc')){ echo 'No permission'; exit;}
        $txnid=$_GET['idothercredits'];
        $sql='DELETE from `payroll_30othercredits` WHERE DateofCredit>=CurDate() and idothercredits=\''.$txnid . '\';'; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:lookupwithedit.php?w=SpecCredits&edit=0&done=1");
    break;
case '13th': 
        if (!allowedToOpen(8167,'1rtc')){ echo 'No permission'; exit;}
        $sql0='SELECT c.* FROM `payroll_26yrtotaland13thmonthcalc` c join `1employees` e on e.IDNo=c.IDNo join `attend_30currentpositions` p on p.IDNo=c.IDNo where e.Resigned=0 '; 
	$stmt=$link->query($sql0);
	$result=$stmt->fetchAll();
	foreach ($result as $row){
		$sql='Insert into `payroll_21paydayadjustments` Set PayrollID=23, `IDNo`='.$row['IDNo'].', `AdjustTypeNo`=21, `AdjustAmt`='.$row['13thBasicCalc'].', `EncodedByNo`=\''.$_SESSION['(ak0)'] . '\'';
		$stmt=$link->prepare($sql);
		$stmt->execute();
                $sql='Insert into `payroll_21paydayadjustments` Set PayrollID=23, `IDNo`='.$row['IDNo'].', `AdjustTypeNo`=22, `AdjustAmt`='.$row['13thTaxShCalc'].', `EncodedByNo`=\''.$_SESSION['(ak0)'] . '\'';
		$stmt=$link->prepare($sql);
		$stmt->execute();
                $sql='Insert into `payroll_21paydayadjustments` (PayrollID,IDNo,AdjustTypeNo,AdjustAmt,Remarks,EncodedByNo) '
                        . 'SELECT 23 AS PayrollID, IDNo, AdjustTypeNo,SUM(AdjustAmt)*-1 AS Given13th,"Already given" AS Remarks, \''.$_SESSION['(ak0)'] . '\' AS EncodedByNo FROM `payroll_21paydayadjustments` WHERE AdjustTypeNo IN (21,22) GROUP BY IDNo, AdjustTypeNo';
		$stmt=$link->prepare($sql);
		$stmt->execute();
                
                
	}
               
	header("Location:lookupwithedit.php?w=PayrollPerPayID&edit=0&done=1");
            break;
 
        }
 $link=null; $stmt=null;
?>