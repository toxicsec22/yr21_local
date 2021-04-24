<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 include_once($path.'/acrossyrs/dbinit/userinit.php'); $link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
if (!allowedToOpen(627,'1rtc')){ echo 'No permission'; exit;}
 
// if there are pending leave requests as of cut off, this won't run

?>
<form action='#' method='POST'>
    <input type='text' name='payrollid'  autocomplete='off' size='2'>
    <input type='submit' name='submit' value='Send this to Payroll'>
    <?php
    if($_SESSION['(ak0)']==1002) { echo "<input type='submit' name='submit' value='Skip check and send this to Payroll'>";}
    ?>
</form>
<?php
$payrollid=(!isset($_POST['payrollid'])?((date('m')*2)+(date('d')<15?-1:0)):$_POST['payrollid']);
if(($_SESSION['(ak0)']==1002) and $_POST['submit']==='Skip check and send this to Payroll' ){ goto skipcheck;}
$sqlcheck='SELECT COUNT(*) AS Pending FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo  WHERE  (Approved=0 OR Acknowledged=0 or SupervisorApproved=0 OR (IF(Approved=1,HRVerifiedByNo IS NULL, FALSE))) AND FromDate<=(SELECT ToDate FROM payroll_1paydates WHERE PayrollID='.$payrollid.')';

$stmtcheck=$link->prepare($sqlcheck); $stmtcheck->execute(); $rescheck=$stmtcheck->fetch();

$sqlcheck='SELECT COUNT(a.TxnID) AS Incomplete FROM
        `attend_2attendancedates` `ad`
        JOIN `attend_2attendance` `a` ON `ad`.`DateToday` = `a`.`DateToday`
    WHERE
        ((ISNULL(`a`.`TimeIn`) AND (`a`.`TimeOut` IS NOT NULL)) OR
        (ISNULL(`a`.`TimeOut`) AND (`a`.`TimeIn` IS NOT NULL)) )
		AND PayrollID='.$payrollid;

$stmtcheck2=$link->prepare($sqlcheck); $stmtcheck2->execute(); $rescheck2=$stmtcheck2->fetch();


$sqlcheck='SELECT COUNT(wfh.TxnID) AS NoResponse FROM approvals_5wfh wfh JOIN `attend_2attendancedates` ad ON wfh.DateToday=ad.DateToday WHERE  Approved=0 AND wfh.DateToday<=(SELECT ToDate FROM payroll_1paydates WHERE PayrollID='.$payrollid.')';

$stmtcheck3=$link->prepare($sqlcheck); $stmtcheck3->execute(); $rescheck3=$stmtcheck3->fetch();

if (($rescheck['Pending']>0) or ($rescheck2['Incomplete']>0) or ($rescheck3['NoResponse']>0)) { 
    if ($rescheck['Pending']>0) {echo '<h4 style="color:red;">There are '.$rescheck['Pending'].' pending leave requests as of cut-off.  Sending of attendance data will not push through.</h4>';}
    if ($rescheck2['Incomplete']>0) { echo '<h4 style="color:red;">There are '.$rescheck2['Incomplete'].' no time in/out.</h4>';}
	
    if ($rescheck3['NoResponse']>0) { echo '<h4 style="color:red;">There are '.$rescheck3['NoResponse'].' pending WFH requests as of cut-off.</h4>';}
goto nopermission;
}

if (!isset($_POST['submit'])){
    goto nopermission;
}

skipcheck:
$dbrecipient=$link;

include_once '../attendance/attendsql/attendsumforpayroll.php';
$sql0='CREATE TEMPORARY TABLE FromAttendance  
Select PayrollID, p.IDNo,RegDaysPresent,LWOPDays,LegalDays,SpecDays,p.SLDays,p.VLDays,RWSDays,RestDays,LWPDays,QDays,RegDaysActual,PaidLegalDays,RegExShiftHrsOT,RestShiftHrsOT,SpecShiftHrsOT,LegalShiftHrsOT,RestExShiftHrsOT,SpecExShiftHrsOT,LegalExShiftHrsOT,`DefaultBranchAssignNo` as `LatestAssignedBranchNo` from `attend_44sumforpayroll` as p join `attend_1defaultbranchassign` on `attend_1defaultbranchassign`.IDNo=p.IDNo 
JOIN `1employees` e ON e.IDNo=p.IDNo
WHERE e.`DirectOrAgency`=0 AND PayrollID='.$_POST['payrollid'];
//echo $sql0;
$stmt=$link->prepare($sql0);
	$stmt->execute();

$stmt2=$link->prepare('Select * from FromAttendance');
    $stmt2->execute();
    $result=$stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row){
$sqlinsert='INSERT INTO `payroll_20fromattendance` SET ';
        $sql='';
        $columnstoadd=array('PayrollID','IDNo','RegDaysPresent','LWOPDays','QDays','LegalDays','SpecDays','SLDays','VLDays','RWSDays','RestDays','LWPDays','RegDaysActual','PaidLegalDays','RegExShiftHrsOT','RestShiftHrsOT','SpecShiftHrsOT','LegalShiftHrsOT','RestExShiftHrsOT','SpecExShiftHrsOT','LegalExShiftHrsOT','LatestAssignedBranchNo');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$row[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' `EncodedByNo`=\''.$_SESSION['(ak0)'].'\', `PostedByNo`=\''.$_SESSION['(ak0)'].'\';';
	// echo $sql.'<br>';break;
	$stmt3=$dbrecipient->prepare($sql);
	$stmt3->execute();
}


header ("Location:/yr21/index.php?done=1");
  $link=null; $dbrecipient=null;  $stmt=null;
nopermission:
?>