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
JOIN attend_1positions p ON p.PositionID=cp.PositionID WHERE e.`Resigned`=0 AND `LatestDorM`<>`PreferredRateType` AND lr.IDNo NOT IN (1525,1526);';
$columnnames=array('IDNo','Nickname', 'SurName','Position','RecordedRate','PreferredForPosition');
$subtitle='Recorded Rate Type Different from Preferred for Position';
include('../backendphp/layout/displayastableonlynoheaders.php');
include_once 'payrolllayout/dailyformula.php';
include_once 'payrolllayout/semimonthlyformula.php';
include_once 'payrolllayout/overtimeandundertime.php';

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
  `TxnID` int(11) NOT NULL AUTO_INCREMENT,
  `PayrollID` tinyint(3) unsigned NOT NULL,
  `IDNo` smallint(6) NOT NULL,
  `BranchNo` smallint(6) NOT NULL COMMENT 'AssignedBranchNo',
  `RecordInBranchNo` smallint(6) NOT NULL COMMENT 'To record into the correct company',
  `Remarks` varchar(255) DEFAULT NULL,
  `BasicRate` double DEFAULT 0 COMMENT 'BasicRate',
  `DeMRate` double DEFAULT 0 COMMENT 'DeMinimisRate',
  `TaxShRate` double DEFAULT 0 COMMENT 'TaxShield',
  `DorSM` tinyint(1) unsigned DEFAULT 0 COMMENT 'DailyorBiWeekly',
  `RegDayBasic` double DEFAULT 0,
  `RegDayDeM` double DEFAULT 0,
  `RegDayTaxSh` double DEFAULT 0,
  `VLBasic` double DEFAULT 0,
  `VLDeM` double DEFAULT 0,
  `VLTaxSh` double DEFAULT 0,
  `SLBasic` double DEFAULT 0,
  `SLDeM` double DEFAULT 0,
  `SLTaxSh` double DEFAULT 0,
  `LWPBasic` double DEFAULT 0,
  `LWPDeM` double DEFAULT 0,
  `LWPTaxSh` double DEFAULT 0,
  `RHBasicforDaily` double DEFAULT 0,
  `RHDeMforDaily` double DEFAULT 0,
  `RHTaxShforDaily` double DEFAULT 0,
  `AbsenceBasicforMonthly` double DEFAULT 0,
  `AbsenceDeMforMonthly` double DEFAULT 0,
  `AbsenceTaxShforMonthly` double DEFAULT 0,
  `UndertimeBasic` double DEFAULT 0,
  `UndertimeDeM` double DEFAULT 0,
  `UndertimeTaxSh` double DEFAULT 0,
  `RegDayOT` double DEFAULT 0,
  `RestDayOT` double DEFAULT 0,
  `SpecOT` double DEFAULT 0,
  `RHOT` double DEFAULT 0,
  `SSS-EE` double DEFAULT 0,
  `PhilHealth-EE` double DEFAULT 0,
  `PagIbig-EE` double DEFAULT 0,
  `WTax` double DEFAULT 0,
  `SSS-ER` double DEFAULT 0,
  `PhilHealth-ER` double DEFAULT 0,
  `PagIbig-ER` double DEFAULT 0,
  `EncodedByNo` smallint(6) DEFAULT NULL,
  `TimeStamp` timestamp NULL DEFAULT NULL,
  `DisburseVia` tinyint(1) DEFAULT 3 COMMENT '0 Via Cash \n1 Via BPI \n2 Via GCash \n3 Via UBP',
  PRIMARY KEY (`TxnID`),
  UNIQUE KEY `PayrollID_UNIQUE` (`PayrollID`,`IDNo`),
  KEY `AssignedBranchNo` (`BranchNo`),
  KEY `IDNo` (`IDNo`),
  KEY `PayrollID` (`PayrollID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
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
  `BranchNo` smallint(6) NOT NULL,
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
DateHired > (SELECT FromDate FROM payroll_1paydates WHERE PayrollID='.$payrollid.') AND 
DateHired <= (SELECT ToDate FROM payroll_1paydates WHERE PayrollID='.$payrollid.');';
$stmt0=$link->query($sql0); $res0=$stmt0->fetch();
$newmonthlyemp=$res0['NewMonthlyEmployees'];

// Get employees with no attendance for the whole payroll period
$sql0='SELECT IFNULL(GROUP_CONCAT(IDNo),0) AS ZeroAttend FROM payroll_20fromattendance WHERE (`SLDays` + `VLDays` + `LWPDays` + `QDays` + `RegDaysPresent`)=0 AND PayrollID='.$payrollid;
$stmt0=$link->query($sql0); $res0=$stmt0->fetch();
$zeroattend='('.$res0['ZeroAttend'].')';

// Overtime is now calculated based on Basic only 2020-07-21

$sql='INSERT INTO `payroll_25payroll'.$temp.'`
(PayrollID, IDNo, BranchNo, RecordInBranchNo, BasicRate,DeMRate,TaxShRate,DorSM,DisburseVia, EncodedByNo, `TimeStamp`)
SELECT a.PayrollID, a.IDNo, a.LatestAssignedBranchNo as `BranchNo`, IF(a.`LatestAssignedBranchNo` IN (SELECT BranchNo FROM `1branches` WHERE CompanyNo=e.RCompanyNo),a.`LatestAssignedBranchNo`,(SELECT BranchNo FROM `1branches` WHERE PseudoBranch=1 AND BranchNo<>95 AND CompanyNo=e.RCompanyNo)) AS RecordInBranchNo, LatestBasicRate, LatestDeMinimisRate, LatestTaxShield, LatestDorM, IF(e.Resigned=1,0,IF((UBPATM IS NOT NULL AND UBPATM<>0),3,0)) AS DisburseVia, \''.$_SESSION['(ak0)'].'\' as `EncodedByNo`,Now() as `TimeStamp`
FROM `payroll_20fromattendance` a JOIN `payroll_20latestrates` r ON a.IDNo=r.IDNo JOIN `1employees` e ON a.IDNo = e.IDNo WHERE (a.PayrollID='.$payrollid.' AND r.DirectOrAgency=0)
UNION 
SELECT '.$payrollid.', r.IDNo, DefaultBranchAssignNo as BranchNo, 
IF(a.`DefaultBranchAssignNo` IN (SELECT BranchNo FROM `1branches` WHERE CompanyNo=r.RCompanyNo),a.`DefaultBranchAssignNo`,(SELECT BranchNo FROM `1branches` WHERE PseudoBranch=1 AND BranchNo<>95 AND CompanyNo=r.RCompanyNo)) AS RecordInBranchNo, LatestBasicRate, LatestDeMinimisRate, LatestTaxShield, 1 as `DorSM`, IF((UBPATM IS NOT NULL AND UBPATM<>0),3,0) as `DisburseVia`, \''.$_SESSION['(ak0)'].'\' as `EncodedByNo`,Now() as `TimeStamp` FROM `payroll_20latestrates` r JOIN `attend_1defaultbranchassign` a ON a.IDNo=r.IDNo JOIN 1employees e ON r.IDNo=e.IDNo WHERE r.IDNo IN (1001,1002);';

$stmt=$link->prepare($sql); $stmt->execute(); 

$sql='UPDATE `payroll_25payroll'.$temp.'` p JOIN payroll_20fromattendance a ON p.IDNo=a.IDNo AND p.PayrollID=a.PayrollID
SET 
RegDayBasic=ROUND(IF(DorSM=0,BasicRate*RegDaysPresent,(IF(a.IDNo IN ('.$newmonthlyemp.'), (BasicRate/13.04)*(RegDaysPresent+RWSDays+LWPDays+SpecDays+PaidLegalDays),BasicRate))),2),
RegDayDeM=ROUND(IF(DorSM=0,DeMRate*RegDaysPresent,(IF(a.IDNo IN ('.$newmonthlyemp.'), (DeMRate/13.04)*(RegDaysPresent+RWSDays+LWPDays+SpecDays+PaidLegalDays),DeMRate))),2),
RegDayTaxSh=ROUND(IF(DorSM=0,TaxShRate*RegDaysPresent,(IF(a.IDNo IN ('.$newmonthlyemp.'), (TaxShRate/13.04)*(RegDaysPresent+RWSDays+LWPDays+SpecDays+PaidLegalDays),TaxShRate))),2),
VLBasic=ROUND((IF(DorSM=0,BasicRate,0)*VLDays),2),
VLDeM=ROUND((IF(DorSM=0,DeMRate,0)*VLDays),2),
VLTaxSh=ROUND((IF(DorSM=0,TaxShRate,0)*VLDays),2),

SLBasic=ROUND((IF(DorSM=0,BasicRate,0)*SLDays),2),
SLDeM=ROUND((IF(DorSM=0,DeMRate,0)*SLDays),2),
SLTaxSh=ROUND((IF(DorSM=0,TaxShRate,0)*SLDays),2),

LWPBasic=ROUND((IF(DorSM=0,BasicRate,0)*LWPDays),2),
LWPDeM=ROUND((IF(DorSM=0,DeMRate,0)*LWPDays),2),
LWPTaxSh=ROUND((IF(DorSM=0,TaxShRate,0)*LWPDays),2),

RHBasicforDaily=ROUND(IF(DorSM=0,BasicRate,0)*PaidLegalDays,2),
RHDeMforDaily=ROUND(IF(DorSM=0,DeMRate,0)*PaidLegalDays,2),
RHTaxShforDaily=ROUND(IF(DorSM=0,TaxShRate,0)*PaidLegalDays,2),

AbsenceBasicforMonthly=ROUND(IF(DorSM=0 OR (a.IDNo IN ('.$newmonthlyemp.')),0,(BasicRate/13.04)*(LWOPDays+QDays)),2),
AbsenceDeMforMonthly=ROUND(IF(DorSM=0 OR (a.IDNo IN ('.$newmonthlyemp.')),0,(DeMRate/13.04)*(LWOPDays+QDays)),2),
AbsenceTaxShforMonthly=ROUND(IF(DorSM=0 OR (a.IDNo IN ('.$newmonthlyemp.')),0,(TaxShRate/13.04)*(LWOPDays+QDays)),2),

UndertimeBasic=ROUND(IF(DorSM=0,BasicRate,(BasicRate/13.04))*(RegDaysPresent-RegDaysActual),2),
UndertimeDeM=ROUND(IF(DorSM=0,DeMRate,(DeMRate/13.04))*(RegDaysPresent-RegDaysActual),2),
UndertimeTaxSh=ROUND(IF(DorSM=0,TaxShRate,(TaxShRate/13.04))*(RegDaysPresent-RegDaysActual),2),
RegDayOT=ROUND(IF(DorSM=0,BasicRate/8,BasicRate/ 13.04/8)*(RegExShiftHrsOT*1.25),2),

RestDayOT=ROUND(IF(DorSM=0,BasicRate/8,BasicRate/ 13.04/8)*((RestShiftHrsOT*1.3)+(RestExShiftHrsOT*1.3*1.3)),2),
SpecOT=ROUND(IF(DorSM=0,BasicRate/8,BasicRate/ 13.04/8)*((SpecShiftHrsOT*1.3)+(SpecExShiftHrsOT*1.3*1.3)),2),
RHOT=ROUND(IF(DorSM=0,BasicRate/8,BasicRate/ 13.04/8)*((LegalShiftHrsOT)+(LegalExShiftHrsOT*2*1.3)),2)
WHERE p.PayrollID='.$payrollid.' AND p.IDNo NOT IN  '.$zeroattend;
//if($_SESSION['(ak0)']==1002){ echo $sql;}
$stmt=$link->prepare($sql); $stmt->execute(); 

$sql='UPDATE `payroll_25payroll'.$temp.'` SET RegDayBasic=BasicRate,RegDayDeM=DeMRate, RegDayTaxSh=TaxShRate WHERE IDNo IN (1001,1002) AND PayrollID='.$payrollid;
$stmt=$link->prepare($sql); $stmt->execute(); 

$sqlfrom=' FROM `payroll_20fromattendance` as a INNER JOIN `payroll_20latestrates` as r ON a.IDNo=r.IDNo JOIN `1employees` as e ON a.IDNo = e.IDNo WHERE (a.PayrollID='.$payrollid.' AND r.DirectOrAgency=0) ';

if ($payrollid%2==0 AND $payrollid<=24){ //SSS 
        
    $payrollwithsss=1;
    

include_once 'sssbasistemptable.php';

$sql='UPDATE `payroll_25payroll'.$temp.'` p JOIN sssbasis ss ON p.IDNo=ss.IDNo JOIN `1employees` as e ON e.IDNo = p.IDNo '
        . ' SET `SSS-EE`=getContriEE(Basis,"sss"), `SSS-ER`=getContriEE(Basis,"sser") WHERE p.PayrollID='.$payrollid . '  AND p.IDNo NOT IN  '.$zeroattend.' AND p.IDNo IN (SELECT IDNo FROM `payroll_25payroll` WHERE PayrollID='.($payrollid-1).')';
//IF ($_SESSION['(ak0)']==1002){  echo $sql;  exit();}
$stmt=$link->prepare($sql); $stmt->execute();

$sql='UPDATE `payroll_25payroll'.$temp.'` p JOIN payroll_20latestrates r ON p.IDNo=r.IDNo 
    SET p.`PhilHealth-EE`=r.`PhilHealth-EE`, p.`PhilHealth-ER`=r.`PhilHealth-ER`, p.`PagIbig-EE`=r.`PagIbig-EE`, p.`PagIbig-ER`=r.`PagIbig-EE` 
    WHERE p.PayrollID='.$payrollid.' AND p.IDNo NOT IN  '.$zeroattend.' AND p.IDNo IN (SELECT IDNo FROM `payroll_25payroll` WHERE PayrollID='.($payrollid-1).')';
 //IF ($_SESSION['(ak0)']==1002){  echo $sql;  exit();}
    $stmt=$link->prepare($sql); $stmt->execute(); 


} else { //WTax
    $payrollwithsss=0;

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
// $sql='UPDATE `payroll_25payroll'.$temp.'` p SET Basic=0, DeM=0, TaxSh=0, OT=0, AbsenceBasic=0, UndertimeBasic=0, AbsenceTaxSh=0, UndertimeTaxSh=0, `SSS-EE`=0, `PhilHealth-EE`=0, `PagIbig-EE`=0, WTax=0, `SSS-ER`=0, `PhilHealth-ER`=0, `PagIbig-ER`=0, DisburseVia=0, Remarks="No attendance for payroll period." WHERE PayrollID='.$payrollid . ' AND p.IDNo IN  '.$zeroattend; //echo $sql;
// $stmt=$link->prepare($sql); $stmt->execute();

 
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
	
    //if($_SESSION['(ak0)']==1002) { echo $sqlupdate;}
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
	
	

$sql2='CREATE TEMPORARY TABLE `payroll_25payrolldatalookuptemp` AS SELECT p.`TxnID`,
    p.`PayrollID`,p.`IDNo`,p.`BranchNo`,`RecordInBranchNo`,p.`Remarks`,
    `BasicRate`,`DeMRate`,`TaxShRate`,`DorSM`,
    `RegDayBasic`,`RegDayDeM`,`RegDayTaxSh`,`VLBasic`,`VLDeM`,`VLTaxSh`,`SLBasic`,`SLDeM`,`SLTaxSh`,
    `LWPBasic`,`LWPDeM`,`LWPTaxSh`,`RHBasicforDaily`,`RHDeMforDaily`,`RHTaxShforDaily`,
    `AbsenceBasicforMonthly`,`AbsenceDeMforMonthly`,`AbsenceTaxShforMonthly`,
    `UndertimeBasic`,`UndertimeDeM`,`UndertimeTaxSh`,`RegDayOT`,`RestDayOT`,`SpecOT`,`RHOT`
    '. ($payrollwithsss==1? ', FORMAT(sb.Basis,0) AS SSSBasis,ss.SSECCredit, `SSS-EE`,`PhilHealth-EE`,`PagIbig-EE`,`SSS-ER`,`PhilHealth-ER`,`PagIbig-ER`':'')
    .',`WTax`,`DisburseVia`,
    TRUNCATE((`p`.`RegDayBasic` + `p`.`VLBasic` + `p`.`SLBasic` + `p`.`LWPBasic` + `p`.`RHBasicforDaily` - `p`.`AbsenceBasicforMonthly` - `p`.`UndertimeBasic`)/(SELECT IF(LatestDorM=0,LatestBasicRate,LatestBasicRate/13.04) FROM `payroll_20latestrates` lr WHERE lr.IDNo=p.IDNo ),2) AS `DaysPaid Calculated`,concat(`e`.`FirstName`," ",`e`.`SurName`) AS `FullName`,ifnull(sum(`a`.`AdjustAmt`),0) AS `TotalAdj`,
    TRUNCATE((`RegDayBasic`+`RegDayDeM`+`RegDayTaxSh`+`VLBasic`+`VLDeM`+`VLTaxSh`+`SLBasic`+`SLDeM`+`SLTaxSh`+
    `LWPBasic`+`LWPDeM`+`LWPTaxSh`+`RHBasicforDaily`+`RHDeMforDaily`+`RHTaxShforDaily`-
    `AbsenceBasicforMonthly`-`AbsenceDeMforMonthly`-`AbsenceTaxShforMonthly`-
    `UndertimeBasic`-`UndertimeDeM`-`UndertimeTaxSh`+`RegDayOT`+`RestDayOT`+`SpecOT`+`RHOT`-
    `SSS-EE`-`PhilHealth-EE`-`PagIbig-EE`-`WTax`),2) AS NetPay 
FROM `payroll_25payrolltemp` p left join `payroll_21paydayadjustmentstemp` `a` ON `p`.`PayrollID` = `a`.`PayrollID` and `p`.`IDNo` = `a`.`IDNo` join `1employees` `e` on `p`.`IDNo` = `e`.`IDNo` '
                . ($payrollwithsss==1? 'LEFT JOIN sssbasis sb ON sb.IDNo=p.IDNo LEFT JOIN payroll_0ssstable ss ON `p`.`SSS-EE`=(SSEE+ECEE+MPFEE) ':'').' group by `p`.`PayrollID`,`p`.`IDNo`;';

	$stmt2=$link->prepare($sql2); $stmt2->execute();
	
	

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
                    array('RecordInBranch','Branch','FullName','IDNo','RegDayBasic','RegDayDeM','RegDayTaxSh','VLBasic','VLDeM','VLTaxSh','SLBasic','SLDeM','SLTaxSh',
                    'LWPBasic','LWPDeM','LWPTaxSh','RHBasicforDaily','RHDeMforDaily','RHTaxShforDaily',
                    'AbsenceBasicforMonthly','AbsenceDeMforMonthly','AbsenceTaxShforMonthly',
                    'UndertimeBasic','UndertimeDeM','UndertimeTaxSh','RegDayOT','RestDayOT','SpecOT','RHOT','Remarks','SSSBasis','SSECCredit','SSS-EE','PhilHealth-EE','PagIbig-EE','WTax','TotalAdj','NetPay','DisburseVia','DaysPaid Calculated'):
                array('RecordInBranch','Branch','FullName','IDNo','RegDayBasic','RegDayDeM','RegDayTaxSh','VLBasic','VLDeM','VLTaxSh','SLBasic','SLDeM','SLTaxSh',
                'LWPBasic','LWPDeM','LWPTaxSh','RHBasicforDaily','RHDeMforDaily','RHTaxShforDaily',
                'AbsenceBasicforMonthly','AbsenceDeMforMonthly','AbsenceTaxShforMonthly',
                'UndertimeBasic','UndertimeDeM','UndertimeTaxSh','RegDayOT','RestDayOT','SpecOT','RHOT','Remarks','WTax','TotalAdj','NetPay','DisburseVia','DaysPaid Calculated');
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
            $txnidname='TxnID'; 
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