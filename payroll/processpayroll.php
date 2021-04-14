<?php
$path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
        $showbranches=false;
include_once('../switchboard/contents.php');



if (!allowedToOpen(826,'1rtc')) { echo 'No permission'; exit;}

$title='Process Payroll';
include('payrolllayout/addentryhead.php');
include('payrolllayout/setpayidsession.php');
if (!isset($_POST['submitIDNoforrecalc'])) { 
?><br>

<form method='post' action='#' enctype='multipart/form-data'>
    Process Payroll ID<input type='text' name='payrollid' value='<?php echo (isset($_SESSION['payrollidses'])?$_SESSION['payrollidses']:((date('m')*2)+(date('d')<15?-1:0)));?>' list='payperiods' autocomplete='off' size=5>&nbsp;
	
    <input type='submit' name='submit2' value='PREVIEW Payroll'> &nbsp; 
    <input type='submit' name='submit' value='PROCESS This Payroll' onClick="return confirm('Process Payroll? or Preview Payroll first.')">
      <br><br><hr><br><br>
<?php

$sql='SELECT lr.IDNo, Nickname, SurName, p.Position, IF(`LatestDorM`=0,"Daily","Monthly") AS RecordedRate, IF(`PreferredRateType`=0, "Daily","Monthly") AS PreferredForPosition FROM payroll_20latestrates lr JOIN `1employees` e ON e.IDNo=lr.IDNo 
JOIN `attend_30currentpositions` cp ON cp.IDNo=lr.IDNo
JOIN attend_0positions p ON p.PositionID=cp.PositionID WHERE e.`Resigned`=0 AND `LatestDorM`<>`PreferredRateType` AND lr.IDNo NOT IN (1525,1526);';
$columnnames=array('IDNo','Nickname', 'SurName','Position','RecordedRate','PreferredForPosition');
$subtitle='Recorded Rate Type Different from Preferred for Position';
include('../backendphp/layout/displayastableonlynoheaders.php');
?>      
      <br><br><hr><br><br>
      Payroll Formulas for DAILY<br><br>
      <?php echo str_repeat('&nbsp',10)?>Total Days = Regular Days Present + Paid Legal Days + SL Days + VL Days + Birthday + LWP Days<br>
<?php echo str_repeat('&nbsp',15)?>Basic = Daily Basic Rate x Total Days<br>
<!--<?php //echo str_repeat('&nbsp',15)?>Cola = Daily Cola Rate x Total Days<br>-->
<?php echo str_repeat('&nbsp',15)?>De Minimis = Daily De Minimis Rate x Total Days<br>
<?php echo str_repeat('&nbsp',15)?>Tax Shield = Daily Tax Shield Rate x Total Days<br><br>
<?php echo str_repeat('&nbsp',10)?>Hourly Rate = (Daily Basic + De Minimis + Tax Shield)/8<br><br><hr><br><br>
      Payroll Formulas for MONTHLY<br><br>
      <?php echo str_repeat('&nbsp',10)?><i>Notes:  1. Calculations start as if perfect attendance, before absences are deducted.<br>
      <?php echo str_repeat('&nbsp',20)?>2. The first payroll of a NEW employee will be counted as daily paid.  Remote Working Saturdays and Special Holidays are added to the counted regular days so these will be paid as well.<br>
      <?php echo str_repeat('&nbsp',20)?>3. The following calculations assume that Saturday <u>whole days</u> are paid for monthly employees.</i><br><br>
      <?php echo str_repeat('&nbsp',15)?>Days Per Year = Total Days Per Year less Sundays:  365-(365/7) = 313<br>
      <?php echo str_repeat('&nbsp',15)?>Average Days Per Month = Days Per Year divided by 12 = 26.08<br>
      <?php echo str_repeat('&nbsp',15)?>Daily Rate = (Monthly Basic + De Minimis + Tax Shield) / 26.08<br>
      <?php echo str_repeat('&nbsp',15)?>Hourly Rate = (Monthly Basic + De Minimis + Tax Shield) / 26.08 / 8<br><br>      
<?php echo str_repeat('&nbsp',15)?>Basic = (Monthly/2) Basic Rate<br>
<!--<?php //echo str_repeat('&nbsp',15)?>Cola = (Monthly/2) Cola Rate<br>-->
<?php echo str_repeat('&nbsp',15)?>De Minimis = (Monthly/2) De Minimis Rate<br>
<?php echo str_repeat('&nbsp',15)?>Tax Shield = (Monthly/2) Tax Shield Rate<br><br>
<?php echo str_repeat('&nbsp',15)?>Absence (Basic/Tax Shield) = Daily Rate x LWOP<br><br>
<hr><br><br>
Undertime = Daily Rate x (Regular Days Present - Regular Days Actual as calculated by attendance)
<br><br>

<hr><br><br>Hourly Rate Multiplier for Overtime<br><br>
<style>
.ottable td { text-align: center;}
.ottable table, p { margin-left: 10%; }
</style>
<div class='ottable'>
<table>
<thead><th>Type of Day</th><th>Regular Shift Hours</th><th>Beyond Shift Hours</th></thead>
<tr><td>Regular Workday</td><td>1</td><td>1.25</td></tr>
<tr><td>Restday</td><td>1.3</td><td>1.3 x 1.3</td></tr>
<tr><td>Special Holiday</td><td>1.3</td><td>1.3 x 1.3</td></tr>
<tr><td>Regular Holiday</td><td>2</td><td>2 x 1.3</td></tr>
<tr><td>Restday & Special Holiday<sup>*</sup></td><td>1.5</td><td>1.5 x 1.3</td></tr>
<tr><td>Restday & Regular Holiday<sup>*</sup></td><td>2.3</td><td>2 x 1.3 x 1.3</td></tr>
</table><br>
<p><i><sup>*</sup>For simplicity, the overtime HOURS for the holiday is multiplied by the restday factor, and the overtime pay is shown in the Restday column.</i></p>
</div>
<?php
} // end of NOT recalc
include_once '../generalinfo/lists.inc';
renderlist('payperiods');    
if ((!isset($_POST['submit'])) AND (!isset($_POST['submit2']))){
    goto end;
}
if (isset($_POST['submit']) AND (!isset($_POST['submit2'])) AND (!isset($_POST['sortfield']))){
	$temp=''; 
} else if (isset($_POST['submit2']) OR (isset($_POST['sortfield']))) {
	$sql = "CREATE TEMPORARY TABLE `payroll_25payrolltemp` (
	  `PayrollID` tinyint(3) unsigned NOT NULL,
	  `IDNo` smallint(6) NOT NULL,
	  `BranchNo` smallint(6) NOT NULL COMMENT 'AssignedBranchNo',
	  `DorSM` tinyint(3) unsigned DEFAULT '0' COMMENT 'DailyorBiWeekly',
	  `Basic` double DEFAULT '0' COMMENT 'BasicRate',
	  `DeM` double DEFAULT '0' COMMENT 'DeMinimisRate',
	  `TaxSh` double DEFAULT '0' COMMENT 'Tax ShieldRate',
	  `OT` double DEFAULT '0' COMMENT 'OTActual',
	  `Remarks` varchar(255) DEFAULT '0',
	  `AbsenceBasic` double DEFAULT '0' COMMENT 'LessAbsenceValueBasic',
	  `UndertimeBasic` double DEFAULT '0' COMMENT 'LessUndertimeValueBasic',
	  `AbsenceTaxSh` double DEFAULT '0' COMMENT 'LessAbsenceValueAllow',
	  `UndertimeTaxSh` double DEFAULT '0' COMMENT 'LessUndertimeValueAllow',
	  `SSS-EE` double DEFAULT '0',
	  `PhilHealth-EE` double DEFAULT '0',
	  `PagIbig-EE` double DEFAULT '0',
	  `WTax` double DEFAULT '0',
	  `SSS-ER` double DEFAULT '0',
	  `PhilHealth-ER` double DEFAULT '0',
	  `PagIbig-ER` double DEFAULT '0',
	  `EncodedByNo` smallint(6) DEFAULT NULL,
	  `TimeStamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  `DisburseVia` tinyint(1) NOT NULL DEFAULT '1', 
	  `TxnID` int(11) NOT NULL AUTO_INCREMENT,
	  `RecordInBranchNo` smallint(6) NOT NULL COMMENT 'To record into the correct company',
	  PRIMARY KEY (`TxnID`),
	  UNIQUE KEY `PayrollID_UNIQUE` (`PayrollID`,`IDNo`),
	  KEY `AssignedBranchNo` (`BranchNo`),
	  KEY `IDNo` (`IDNo`),
	  KEY `PayrollID` (`PayrollID`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
	";
	$stmt=$link->prepare($sql); $stmt->execute();
	
	$sql2="CREATE TEMPORARY TABLE `payroll_21paydayadjustmentstemp` (
  `PayrollID` tinyint(3) unsigned NOT NULL,
  `IDNo` smallint(6) NOT NULL,
  `AdjustTypeNo` tinyint(3) unsigned NOT NULL,
  `AdjustAmt` double DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  `EncodedByNo` smallint(6) DEFAULT NULL,
  `TimeStamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `AdjID` int(11) NOT NULL AUTO_INCREMENT,
  `BranchNo` smallint(6) NOT NULL COMMENT 'To record into the correct company',
  PRIMARY KEY (`AdjID`),
  UNIQUE KEY `PayrollIDAdjUnique` (`PayrollID`,`IDNo`,`AdjustTypeNo`),
  KEY `PayrollID` (`PayrollID`),
  KEY `IDNo` (`IDNo`),
  KEY `fk_adjust_acct_idx` (`AdjustTypeNo`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
$stmt2=$link->prepare($sql2); $stmt2->execute(); //echo $sql2;
$temp = 'temp';
}

// Divisor of SemiMonthly is the same as Daily @ 26.07/month -- Saturdays are paid as if worked full day
$payrollid=$_SESSION['payrollidses'];


// First get who are the new employees with monthly rate
$sql0='SELECT IFNULL(GROUP_CONCAT(e.IDNo),0) AS NewMonthlyEmployees FROM 1employees e JOIN payroll_20latestrates r ON e.IDNo=r.IDNo WHERE 
LatestDorM=1 AND
DateHired BETWEEN (SELECT FromDate FROM payroll_1paydates WHERE PayrollID='.$payrollid.') AND (SELECT ToDate FROM payroll_1paydates WHERE PayrollID='.$payrollid.');';
$stmt0=$link->query($sql0); $res0=$stmt0->fetch();
$newmonthlyemp='('.$res0['NewMonthlyEmployees'].')';

// Get employees with no attendance for the whole payroll period
$sql0='SELECT IFNULL(GROUP_CONCAT(IDNo),0) AS ZeroAttend FROM 2021_1rtc.payroll_20fromattendance WHERE (`SLDays` + `VLDays` + `LWPDays` + `QDays` + `RegDaysPresent`)=0 AND PayrollID='.$payrollid;
$stmt0=$link->query($sql0); $res0=$stmt0->fetch();
$zeroattend='('.$res0['ZeroAttend'].')';

// Overtime is now calculated based on Basic only 2020-07-21

$sql='INSERT INTO `payroll_25payroll'.$temp.'`
(`PayrollID`,`IDNo`,`BranchNo`,`RecordInBranchNo`,`DorSM`,`Basic`,`DeM`,`TaxSh`,`OT`,`AbsenceBasic`,`UndertimeBasic`,`AbsenceTaxSh`,`UndertimeTaxSh`,`SSS-EE`,`PhilHealth-EE`,`PagIbig-EE`,`WTax`,`SSS-ER`,`PhilHealth-ER`,`PagIbig-ER`,`DisburseVia`, `EncodedByNo`,`TimeStamp`)
SELECT a.PayrollID, a.IDNo, a.LatestAssignedBranchNo as `BranchNo`,
IF(a.`LatestAssignedBranchNo` IN (SELECT BranchNo FROM `1branches` WHERE CompanyNo=e.RCompanyNo),a.`LatestAssignedBranchNo`,(SELECT BranchNo FROM `1branches` WHERE PseudoBranch=1 AND BranchNo<>95 AND CompanyNo=e.RCompanyNo)) AS RecordInBranchNo,
 r.LatestDorM as `DorSM`,
truncate(if(r.LatestDorM=0,LatestBasicRate*(RegDaysPresent+PaidLegalDays+SLDays+VLDays+LWPDays),
(IF(a.IDNo IN '.$newmonthlyemp.', (LatestBasicRate/13.04)*(RegDaysPresent+PaidLegalDays+SLDays+VLDays+LWPDays+SpecDays) ,LatestBasicRate))),2) as `Basic`,
    
truncate(if(r.LatestDorM=0,LatestDeMinimisRate*(RegDaysPresent+PaidLegalDays+SLDays+VLDays+LWPDays),
(IF(a.IDNo IN '.$newmonthlyemp.', (LatestDeMinimisRate/13.04)*(RegDaysPresent+PaidLegalDays+SLDays+VLDays+LWPDays+SpecDays) ,LatestDeMinimisRate))),2) as `DeM`,
    
truncate(if(r.LatestDorM=0,LatestTaxShield*(RegDaysPresent+PaidLegalDays+SLDays+VLDays+LWPDays),
(IF(a.IDNo IN '.$newmonthlyemp.', (LatestTaxShield/13.04)*(RegDaysPresent+PaidLegalDays+SLDays+VLDays+LWPDays+SpecDays) ,LatestTaxShield))),2) as `TaxSh`,

truncate(if(r.LatestDorM=0,(LatestBasicRate)/8,(LatestBasicRate)/ 13.04/8)*((RegExShiftHrsOT*1.25)+
(RestShiftHrsOT*1.3)+
(SpecShiftHrsOT*1.3)+
LegalShiftHrsOT+
(RestExShiftHrsOT*1.3*1.3)+
(SpecExShiftHrsOT*1.3*1.3)+
(LegalExShiftHrsOT*2*1.3)),2) AS OT,

truncate(if(r.LatestDorM=0 OR a.IDNo IN '.$newmonthlyemp.',0,(LatestBasicRate+LatestDeMinimisRate)/ 13.04)*(LWOPDays+QDays),2) as `AbsenceBasic`,

truncate(if(r.LatestDorM=0,(LatestBasicRate+LatestDeMinimisRate),(LatestBasicRate+LatestDeMinimisRate)/ 13.04)*(RegDaysPresent-RegDaysActual),2) as `UndertimeBasic`,

truncate(if(r.LatestDorM=0 OR a.IDNo IN '.$newmonthlyemp.',0,(LatestTaxShield)/ 13.04)*(LWOPDays+QDays),2) as `AbsenceTaxSh`,
truncate(if(r.LatestDorM=0,(LatestTaxShield),(LatestTaxShield)/ 13.04)*(RegDaysPresent-RegDaysActual),2) as `UndertimeTaxSh`,';

$sqlfrom=' FROM `payroll_20fromattendance` as a INNER JOIN `payroll_20latestrates` as r ON a.IDNo=r.IDNo JOIN `1employees` as e ON a.IDNo = e.IDNo WHERE (a.PayrollID='.$payrollid.' AND r.DirectOrAgency=0) ';

if ($payrollid%2==0 AND $payrollid<=24){ //SSS 
        
    $payrollwithsss=1;
    $sql.=' 0 AS `SSS-EE`, `Philhealth-EE`, `PagIbig-EE`,0 as `WTax`, 0 AS `SSS-ER`, `Philhealth-ER`, 100 AS `PagIbig-ER`, IF(e.Resigned=1,0,IF((UBPATM IS NOT NULL AND UBPATM<>0),3,0)) AS `DisburseVia`, \''.$_SESSION['(ak0)'].'\' AS `EncodedByNo`,Now() as `TimeStamp` '.$sqlfrom.' UNION ALL SELECT '.$payrollid.', r.IDNo, DefaultBranchAssignNo as BranchNo, 
IF(a.`DefaultBranchAssignNo` IN (SELECT BranchNo FROM `1branches` WHERE CompanyNo=r.RCompanyNo),a.`DefaultBranchAssignNo`,(SELECT BranchNo FROM `1branches` WHERE PseudoBranch=1 AND BranchNo<>95 AND CompanyNo=r.RCompanyNo)) AS RecordInBranchNo, 1 as `DorSM`, LatestBasicRate  as `Basic`,LatestDeMinimisRate as `DeM`,LatestTaxShield as `TaxSh`,0 as OT,0 as `AbsenceBasic`,0 as `UndertimeBasic`,0 as `AbsenceTaxSh`,0 as `UndertimeTaxSh`, `SSS-EE`,`Philhealth-EE`,`PagIbig-EE`,0 as `WTax`,`SSS-ER`,`Philhealth-ER`,if(`SSS-ER`<>0,100,0) as `PagIbig-ER`, IF((UBPATM IS NOT NULL AND UBPATM<>0),3,0) as `DisburseVia`, \''.$_SESSION['(ak0)'].'\' as `EncodedByNo`,Now() as `TimeStamp` FROM `payroll_20latestrates` r JOIN `attend_1defaultbranchassign` a ON a.IDNo=r.IDNo JOIN 1employees e ON r.IDNo=e.IDNo WHERE r.IDNo IN (1001,1002)';
// IF ($_SESSION['(ak0)']==1002){  echo $sql;  exit();}
$stmt=$link->prepare($sql); $stmt->execute(); 

include_once 'sssbasistemptable.php';

$sql='UPDATE `payroll_25payroll'.$temp.'` p JOIN sssbasis ss ON p.IDNo=ss.IDNo JOIN `1employees` as e ON e.IDNo = p.IDNo '
        . ' SET `SSS-EE`=getContriEE(Basis,"sss"), `SSS-ER`=getContriEE(Basis,"sser") WHERE p.PayrollID='.$payrollid . '  AND p.IDNo NOT IN  '.$zeroattend; //AND e.Resigned=0; 
//IF ($_SESSION['(ak0)']==1002){  echo $sql;  exit();}
$stmt=$link->prepare($sql); $stmt->execute();



//moved to wtax
 

} else { //WTax
    $payrollwithsss=0;
    $sql.=' 0 as `SSS-EE`,0 as `Philhealth-EE`,0 as `PagIbig-EE`, 0 as `WTax`,0 as `SSS-ER`,0 as `Philhealth-ER`,0 as `PagIbig-ER`, IF(e.Resigned=1,0,IF((UBPATM IS NOT NULL AND UBPATM<>0),3,0)) as `DisburseVia`, \''.$_SESSION['(ak0)'].'\' as `EncodedByNo`,Now() as `TimeStamp` '.$sqlfrom.' UNION ALL SELECT '.$payrollid.', r.IDNo, DefaultBranchAssignNo as BranchNo, 
IF(a.`DefaultBranchAssignNo` IN (SELECT BranchNo FROM `1branches` WHERE CompanyNo=r.RCompanyNo),a.`DefaultBranchAssignNo`,(SELECT BranchNo FROM `1branches` WHERE PseudoBranch=1 AND BranchNo<>95 AND CompanyNo=r.RCompanyNo)) AS RecordInBranchNo,  1 as `DorSM`, LatestBasicRate,LatestDeMinimisRate as `DeM`,LatestTaxShield as `TaxSh`,0,0,0,0,0, 0 as `SSS-EE`,0 as `Philhealth-EE`,0 as `PagIbig-EE`,`WTax`,0 as `SSS-ER`,0 as `Philhealth-ER`,0 as `PagIbig-ER`,IF((UBPATM IS NOT NULL AND UBPATM<>0),3,0) as `DisburseVia`, \''.$_SESSION['(ak0)'].'\' as `EncodedByNo`,Now() as `TimeStamp` FROM `payroll_20latestrates` r JOIN `attend_1defaultbranchassign` a ON a.IDNo=r.IDNo JOIN 1employees e ON r.IDNo=e.IDNo WHERE r.IDNo IN (1001,1002)'; 
  // IF ($_SESSION['(ak0)']==1002){  echo $sql;  exit();}
$stmt=$link->prepare($sql); $stmt->execute();

$sql='UPDATE `payroll_25payroll'.$temp.'` p JOIN `1employees` as e ON e.IDNo = p.IDNo SET WTax=CalcTax(p.IDNo,'.$payrollid.') WHERE PayrollID='.$payrollid . '  and CalcTax(p.IDNo,'.$payrollid.')>0 AND e.Resigned=0 AND p.IDNo NOT IN  '.$zeroattend; //echo $sql;
$stmt=$link->prepare($sql); $stmt->execute();


	//loans module
	$sqlloans='SELECT lm.TxnID,LoanTypeID FROM payroll_31loansmain lm JOIN payroll_32loanssub ls ON lm.TxnID=ls.TxnID WHERE PayrollID IS NULL AND LoanTypeID IN (30,31,32,33) AND IDNo IN (SELECT IDNo FROM payroll_25payroll WHERE PayrollID='.$payrollid.') AND StartDeductDate<=CURDATE() AND IDNo NOT IN  '.$zeroattend.' GROUP BY TxnID;';
	$stmtloans=$link->query($sqlloans); $resloans=$stmtloans->fetchAll();

	foreach($resloans AS $resloan){
		$sqlinsertloan='INSERT INTO `payroll_21paydayadjustments'.$temp.'` ( PayrollID, IDNo, AdjustTypeNo, AdjustAmt,BranchNo, Remarks,EncodedByNo)
				SELECT '.$payrollid.', IDNo, LoanTypeID, Amount*-1,(SELECT DefaultBranchAssignNo FROM attend_1defaultbranchassign WHERE IDNo=lm.IDNo), CONCAT(InstallmentNo,"/",Installments," ",AdjustType), \''.$_SESSION['(ak0)'].'\' AS EncodedByNo
				FROM payroll_31loansmain lm JOIN payroll_32loanssub ls ON lm.TxnID=ls.TxnID JOIN payroll_0acctid ad ON lm.LoanTypeID=ad.AdjustTypeNo WHERE lm.TxnID='.$resloan['TxnID'].' AND PayrollID IS NULL ORDER BY InstallmentNo LIMIT 1';
				
		$stmtinsertloan=$link->prepare($sqlinsertloan); $stmtinsertloan->execute();
		
		
		if (isset($_POST['submit'])){ //process
			$sqlupdateloan='UPDATE payroll_32loanssub SET PayrollID='.$payrollid.',YearD='.$currentyr.' WHERE TxnID='.$resloan['TxnID'].' AND PayrollID IS NULL ORDER BY InstallmentNo LIMIT 1';
			$stmtupdateloan=$link->prepare($sqlupdateloan); $stmtupdateloan->execute();
		}
		
	}

	//end loans module


}

// Set fields to zero for zero attendance employees
$sql='UPDATE `payroll_25payroll'.$temp.'` p SET Basic=0, DeM=0, TaxSh=0, OT=0, AbsenceBasic=0, UndertimeBasic=0, AbsenceTaxSh=0, UndertimeTaxSh=0, `SSS-EE`=0, `PhilHealth-EE`=0, `PagIbig-EE`=0, WTax=0, `SSS-ER`=0, `PhilHealth-ER`=0, `PagIbig-ER`=0, DisburseVia=0, Remarks="No attendance for payroll period." WHERE PayrollID='.$payrollid . ' AND p.IDNo IN  '.$zeroattend; //echo $sql;
$stmt=$link->prepare($sql); $stmt->execute();

 
//All Adjustment Except OIC allowance,and loans
$sqladj='INSERT INTO `payroll_21paydayadjustments'.$temp.'` ( PayrollID, IDNo, AdjustTypeNo, AdjustAmt,BranchNo, Remarks,EncodedByNo)
SELECT s.PayrollID, s.IDNo, s.AdjustTypeNo, s.AdjustAmt,BranchNo, s.Remarks, \''.$_SESSION['(ak0)'].'\' AS EncodedByNo
FROM `payroll_21scheduledpaydayadjustments` as s

 WHERE AdjustTypeNo NOT IN (30,31,32,33,41) AND s.PayrollID='.$payrollid;
// WHERE AdjustTypeNo<>41 AND s.PayrollID='.$payrollid;
// WHERE AdjustTypeNo NOT IN (30,31,32,33) AND s.PayrollID='.$payrollid;
$stmtadj=$link->prepare($sqladj); $stmtadj->execute();

//End Adjustments

//Oic Allowance
$sqladj2='INSERT INTO `payroll_21paydayadjustments'.$temp.'` ( PayrollID, IDNo, AdjustTypeNo, AdjustAmt,BranchNo, Remarks,EncodedByNo)
SELECT s.PayrollID, s.IDNo, s.AdjustTypeNo, IF(((SELECT COUNT(ad.DateToday) FROM attend_2attendance a JOIN attend_2attendancedates ad ON a.DateToday=ad.DateToday WHERE IDNo=s.IDNo AND PayrollID='.$payrollid.' AND (TimeIn IS NOT NULL OR LeaveNo IN (12,13,15,22)) AND ad.DateToday>=(SELECT `Date` FROM payroll_2requestoicallowance WHERE IDNo=s.IDNo ORDER BY `Date` DESC LIMIT 1))>=(SELECT COUNT(DISTINCT(Date)) FROM acctg_2salemain sm JOIN attend_2attendancedates ad ON sm.Date=ad.DateToday WHERE PayrollID='.$payrollid.' AND BranchNo=s.BranchNo AND ad.DateToday>=(SELECT `Date` FROM payroll_2requestoicallowance WHERE IDNo=s.IDNo ORDER BY `Date` DESC LIMIT 1))),s.AdjustAmt,(s.AdjustAmt-((s.AdjustAmt/(SELECT COUNT(ad.DateToday) FROM attend_2attendance a JOIN attend_2attendancedates ad ON a.DateToday=ad.DateToday WHERE IDNo=s.IDNo AND PayrollID='.$payrollid.'))*(SELECT COUNT(ad.DateToday) FROM attend_2attendance a JOIN attend_2attendancedates ad ON a.DateToday=ad.DateToday WHERE IDNo=s.IDNo AND PayrollID='.$payrollid.' AND LeaveNo IN (10,16,17,18,19) AND ad.DateToday>=(SELECT `Date` FROM payroll_2requestoicallowance WHERE IDNo=s.IDNo ORDER BY `Date` DESC LIMIT 1))))) AS AdjustAmt,BranchNo, s.Remarks, \''.$_SESSION['(ak0)'].'\' AS EncodedByNo
FROM `payroll_21scheduledpaydayadjustments` as s
WHERE AdjustTypeNo=41 AND s.PayrollID='.$payrollid.'  AND s.IDNo NOT IN  '.$zeroattend;

$stmtadj2=$link->prepare($sqladj2); $stmtadj2->execute();
//Oic Allowance Adjustment
 


// insert payment for quarantine days
$sql0='SELECT SUM(QDays) AS Q FROM `payroll_20fromattendance` WHERE PayrollID='.$payrollid;
$stmt0=$link->query($sql0); $res0=$stmt0->fetch();


$sql1='CREATE TEMPORARY TABLE given13th AS SELECT IDNo,SUM(AdjustAmt) AS Given13th, AdjustTypeNo FROM `payroll_21paydayadjustments` WHERE AdjustTypeNo IN (21,22) GROUP BY IDNo, AdjustTypeNo';
$stmt1=$link->prepare($sql1); $stmt1->execute();

if($res0['Q']>0){
    
    $sqlins1='INSERT INTO `payroll_21paydayadjustments'.$temp.'` ( PayrollID, IDNo, AdjustTypeNo, AdjustAmt,BranchNo, Remarks,EncodedByNo) 
    SELECT '.$payrollid.', fa.IDNo, '; 
    
    $sqlins2=' AS AdjustTypeNo, IF(LatestDorM=0,';
    
    $sqlins3='/13.04)*IFNULL(QDays,0) AS AdjustAmt, BranchNo, "Quarantine Days'.($temp=='temp'?' - temp calc':'').'", \''.$_SESSION['(ak0)'].'\' AS EncodedByNo FROM `payroll_20latestrates` lr JOIN (SELECT PayrollID, IDNo, QDays FROM `payroll_20fromattendance` WHERE PayrollID='.$payrollid.' AND QDays<>0) fa ON fa.IDNo=lr.IDNo JOIN `payroll_25payroll'.$temp.'` p ON p.IDNo=lr.IDNo LEFT JOIN given13th g ON fa.IDNo=g.IDNo';
    
    $sqlins4=' AND AdjustTypeNo=';
    
    $sqlins5=' WHERE p.PayrollID='.$payrollid;
    
    // QL will be paid from EARNED 13th month
    $sqlupdate1='UPDATE `payroll_21paydayadjustments'.$temp.'` pa LEFT JOIN `payroll_26yrtotaland13thmonthcalc` tmc ON pa.IDNo=tmc.IDNo LEFT JOIN given13th g ON pa.IDNo=g.IDNo AND pa.AdjustTypeNo=g.AdjustTypeNo SET AdjustAmt=IF(IFNULL(';    
    $sqlupdate2=',0)-IFNULL( g.Given13th,0)-AdjustAmt>=0,AdjustAmt,IF(IFNULL(';    
    $sqlupdate3=',0)-IFNULL( g.Given13th,0)>0,IFNULL(';    
    $sqlupdate4=',0)-IFNULL( g.Given13th,0),0)) WHERE pa.AdjustTypeNo=';    
    $sqlupdate5=' AND pa.PayrollID='.$payrollid; //.' AND g.Given13th>0';
    
    
    // Basic
    $adjtypeno='21'; $rate='LatestBasicRate'; $thirteenth='13thBasicCalc';
   
    $sqlins=$sqlins1.' '.$adjtypeno.' '.$sqlins2.$rate.','.$rate.$sqlins3.$sqlins4.$adjtypeno.$sqlins5;
    // if($_SESSION['(ak0)']==1002) { echo $sqlins; exit();}
    $stmtadjq=$link->prepare($sqlins); $stmtadjq->execute();
    
    $sqlupdate=$sqlupdate1.$thirteenth.$sqlupdate2.$thirteenth.$sqlupdate3.$thirteenth.$sqlupdate4.$adjtypeno.$sqlupdate5;
    //if($_SESSION['(ak0)']==1002) { echo $sqlins.'<br><br>'.$sqlupdate; exit();}
    $stmtadjq=$link->prepare($sqlupdate); $stmtadjq->execute();
	
    if($_SESSION['(ak0)']==1002) { echo $sqlupdate;}
    // DeMinimis and TaxShield
    $adjtypeno='22'; $rate='(LatestDeMinimisRate+LatestTaxShield)'; $thirteenth='13thTaxShCalc';
    $sqlins=$sqlins1.' '.$adjtypeno.' '.$sqlins2.$rate.','.$rate.$sqlins3.$sqlins4.$adjtypeno.$sqlins5.' AND (LatestDeMinimisRate+LatestTaxShield)<>0';
    $stmtadjq=$link->prepare($sqlins); $stmtadjq->execute();
    
    $sqlupdate=$sqlupdate1.$thirteenth.$sqlupdate2.$thirteenth.$sqlupdate3.$thirteenth.$sqlupdate4.$adjtypeno.$sqlupdate5;
    $stmtadjq=$link->prepare($sqlupdate); $stmtadjq->execute();    

}
// end for quarantine




if (isset($_POST['submit']) AND (!isset($_POST['sortfield']))){
	
	
	header('Location: lookupwithedit.php?w=PayrollPerPayID&PayrollID='.$payrollid.'&edit=0');
} else {
	if (!allowedToOpen(817,'1rtc')) { echo 'No permission'; exit;}
	
	
	$sql2="CREATE TEMPORARY TABLE `payroll_25payrolldatalookuptemp` AS select `p`.`PayrollID` AS `PayrollID`,`p`.`IDNo` AS `IDNo`,`p`.`RecordInBranchNo` AS `RecordInBranchNo`,`p`.`BranchNo` AS `BranchNo`,`p`.`DorSM` AS `DorSM`,`p`.`Basic` AS `Basic`,`p`.`DeM` AS `DeM`,`p`.`TaxSh` AS `TaxSh`,`p`.`OT` AS `OT`,`a`.`Remarks` AS `Remarks`,`p`.`AbsenceBasic` AS `AbsenceBasic`,`p`.`UndertimeBasic` AS `UndertimeBasic`,`p`.`AbsenceTaxSh` AS `AbsenceTaxSh`,`p`.`UndertimeTaxSh` AS `UndertimeTaxSh`"
                . ($payrollwithsss==1? ", FORMAT(sb.Basis,0) AS SSSBasis,ss.SSECCredit,`p`.`SSS-EE` AS `SSS-EE`,`p`.`PhilHealth-EE` AS `PhilHealth-EE`,`p`.`PagIbig-EE` AS `PagIbig-EE`,`p`.`SSS-ER` AS `SSS-ER`,`p`.`PhilHealth-ER` AS `PhilHealth-ER`,`p`.`PagIbig-ER` AS `PagIbig-ER`":"")
                .",`p`.`WTax` AS `WTax`,`p`.`EncodedByNo` AS `EncodedByNo`,`p`.`DisburseVia` AS `DisburseVia`, TRUNCATE((Basic-AbsenceBasic-UndertimeBasic)/(SELECT IF(LatestDorM=0,LatestBasicRate,LatestBasicRate/13.04) FROM `payroll_20latestrates` lr WHERE lr.IDNo=p.IDNo ),2) AS `DaysPaid Calculated`,`p`.`TxnID` AS `TxnID`,concat(`e`.`FirstName`,' ',`e`.`SurName`) AS `FullName`,ifnull(sum(`a`.`AdjustAmt`),0) AS `TotalAdj`,truncate((((((((((((((ifnull(`p`.`Basic`,0)) + ifnull(`p`.`DeM`,0)) + ifnull(`p`.`TaxSh`,0)) + ifnull(`p`.`OT`,0)) - ifnull(`p`.`AbsenceBasic`,0)) - ifnull(`p`.`AbsenceTaxSh`,0)) - ifnull(`p`.`UndertimeBasic`,0)) - ifnull(`p`.`UndertimeTaxSh`,0)) - ifnull(`p`.`SSS-EE`,0)) - ifnull(`p`.`PhilHealth-EE`,0)) - ifnull(`p`.`PagIbig-EE`,0)) - ifnull(`p`.`WTax`,0)) + ifnull(sum(`a`.`AdjustAmt`),0)),2) AS `NetPay` from ((`payroll_25payrolltemp` `p` left join `payroll_21paydayadjustmentstemp` `a` on(((`p`.`PayrollID` = `a`.`PayrollID`) and (`p`.`IDNo` = `a`.`IDNo`)))) join `1employees` `e` on((`p`.`IDNo` = `e`.`IDNo`))) "
                . ($payrollwithsss==1? "JOIN sssbasis sb ON sb.IDNo=p.IDNo JOIN payroll_0ssstable ss ON `p`.`SSS-EE`=(SSEE+ECEE+MPFEE) ":"")." 
				group by `p`.`PayrollID`,`p`.`IDNo`;";
				// echo $sql2; exit();
	$stmt2=$link->prepare($sql2); $stmt2->execute();
	
	/* 
	// placed here bec of payroll_datalookuptemp
	$stmtca=$link->prepare($sqlca); $stmtca->execute();
	
	
	$stmtinsca=$link->prepare($sqlinsca); $stmtinsca->execute();
	
	
	$sqlupdateplookup='UPDATE payroll_25payrolldatalookuptemp pdlt INNER JOIN (
 SELECT IDNo, IFNULL(SUM(AdjustAmt),0) as total
 FROM payroll_21paydayadjustmentstemp pdat WHERE PayrollID='.$payrollid.' AND AdjustTypeNo IN (21,22)
 GROUP BY IDNo
 ) temp ON pdlt.IDNo=temp.IDNo SET pdlt.TotalAdj=(pdlt.TotalAdj+(SELECT IFNULL(AdjustAmt,0) FROM payroll_21paydayadjustmentstemp WHERE IDNo=pdlt.IDNo AND PayrollID='.$payrollid.' AND AdjustTypeNo=11)),pdlt.NetPay=(pdlt.NetPay+(SELECT IFNULL(AdjustAmt,0) FROM payroll_21paydayadjustmentstemp WHERE IDNo=pdlt.IDNo AND PayrollID='.$payrollid.' AND AdjustTypeNo=11)) WHERE pdlt.PayrollID='.$payrollid.'';
	$stmtupdateplookup=$link->prepare($sqlupdateplookup); $stmtupdateplookup->execute();
 */

	$_SESSION['payperiod1']=$payrollid;
            $payrollid=(isset($payrollid)?$payrollid:((date('m')*2)+(date('d')<15?-1:0)));
            
            if (!isset($_POST['submitIDNoforrecalc'])) { 
            $sql0='SELECT SUM(NetPay) AS NetPay FROM `payroll_25payrolldatalookuptemp` WHERE PayrollID='.$payrollid; $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
            $formdesc='';
            if (allowedToOpen(8171,'1rtc')){ $formdesc='<br><br></i>Total cash needed: '.number_format($res0['NetPay'], 2).'<i>';}
            $title='Payroll Data - '.$payrollid;
            $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'`BranchNo`,`FirstName`');
	    include('../backendphp/layout/clickontabletoedithead.php');
            } // end of NOT recalc
	    ?>
	   
	    </form>
	    <?php
	    $columnnames=$payrollwithsss==1?
                    array('RecordInBranch','Branch','FullName','IDNo','Basic','DeM','TaxSh','OT','Remarks','AbsenceBasic','UndertimeBasic','AbsenceTaxSh','UndertimeTaxSh','SSSBasis','SSECCredit','SSS-EE','PhilHealth-EE','PagIbig-EE','WTax','TotalAdj','NetPay','DisburseVia','DaysPaid Calculated'):
                array('RecordInBranch','Branch','FullName','IDNo','Basic','DeM','TaxSh','OT','Remarks','AbsenceBasic','UndertimeBasic','AbsenceTaxSh','UndertimeTaxSh','WTax','TotalAdj','NetPay','DisburseVia','DaysPaid Calculated');
            $columnsub=$columnnames; 
            
            if (!isset($_POST['submitIDNoforrecalc'])) { 
            if (allowedToOpen(8171,'1rtc')){ $coltototal='NetPay'; $showgrandtotal=true; }
            
	    if (allowedToOpen(8172,'1rtc')) {
	       $sqlco='SELECT CompanyNo, CompanyName, Company FROM 1companies WHERE Active<>0';
	    } 
        $stmtco=$link->query($sqlco); $resultco=$stmtco->fetchAll();
	    
	    foreach ($resultco as $co){
             
	    $sql='SELECT b1.Branch AS RecordInBranch, b.Branch, p.* FROM `payroll_25payrolldatalookuptemp` p JOIN `1employees` `e` ON `p`.`IDNo` = `e`.`IDNo`
	    JOIN `1branches` b ON b.BranchNo=p.BranchNo
	    JOIN `1branches` b1 ON b1.BranchNo=p.RecordInBranchNo 
	    WHERE PayrollID='.$payrollid.' AND e.RCompanyNo='.$co['CompanyNo']
	    .((!allowedToOpen(8173,'1rtc'))?' AND p.IDNo>1002':'')
	    .' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
            $txnid='TxnID'; 
	       $subtitle='<font color="darkblue">'.$co['CompanyName'].'</font>';
	       include('../backendphp/layout/displayastableonlynoheaders.php');
	       echo '<br><br>';
	    }
      unset($coltototal,$showgrandtotal);
      $subtitle='Payroll Adjustments';
      $columnnames=array('AdjustType','IDNo','FirstName','Nickname','SurName','AdjustAmt','Remarks');
      $sql='SELECT AdjID,a.IDNo, a.PayrollID, FirstName, Nickname, e.SurName, a.AdjustTypeNo, ac.AdjustType, FORMAT(a.AdjustAmt,2) AS AdjustAmt, Remarks,BranchNo, a.EncodedByNo FROM `1employees` as e JOIN (`payroll_21paydayadjustments'.$temp.'` as a JOIN payroll_0acctid ac ON a.AdjustTypeNo = ac.AdjustTypeNo ) ON e.IDNo = a.IDNo ORDER BY a.AdjustTypeNo,a.IDNo';
      include('../backendphp/layout/displayastableonlynoheaders.php');

            } // end NOT for recalc
            
}
end:
     if (!isset($_POST['submitIDNoforrecalc'])) {  $link=null; $stmt=null;}
?>
</form>
</body>
</html>