<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(827,'1rtc')) {   echo 'No permission'; exit;}
$showbranches=false;
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/lastnum.php'; 
 

$title='Send to Acctg'; include('payrolllayout/addentryhead.php'); 
include('payrolllayout/setpayidsession.php');
include_once '../generalinfo/lists.inc'; renderlist('payperiods');
?>
<form style="display:inline; text-align: center" method='post' action='#' enctype='multipart/form-data'>
    Process Payroll ID<input type='text' name='payrollid' list='payperiods' autocomplete='off' size=3 value='<?php echo (isset($_SESSION['payrollidses'])?$_SESSION['payrollidses']:((date('m')*2)+(date('d')<15?-1:0)));?>'>
    <input type='submit' name='submit' value='Lookup'> &nbsp; &nbsp; Send to Acctg Vouchers: 
<?php    
if (!isset($_SESSION['payrollidses'])){ goto end;}
$payrollid=$_SESSION['payrollidses'];
if (allowedToOpen(8271,'1rtc')){
    ?><input type='submit' name='submit' value='Payroll'> &nbsp; 
        <input type='submit' name='submit' value='SSS'> &nbsp; 
        <input type='submit' name='submit' value='Philhealth'> &nbsp; 
        <input type='submit' name='submit' value='PagIbig'> &nbsp; 
        <input type='submit' name='submit' value='SSS Loans (Salary)'> &nbsp; 
        <input type='submit' name='submit' value='SSS Loans (Calamity)'> &nbsp; 
        <input type='submit' name='submit' value='Pag-Ibig Loans (Salary)'> &nbsp;
        <input type='submit' name='submit' value='Pag-Ibig Loans (Calamity)'> &nbsp;
        <input type='submit' name='submit' value='WTax'> &nbsp; &nbsp;
</form> &nbsp; &nbsp;
    
    <form style="display:inline; text-align: center" method='post' action='autoaddinvcharges.php?p=dep' enctype='multipart/form-data'>
        <input type='hidden' name='payrollid' value='<?php echo $payrollid; ?>'>
    <input type='submit' name='submit' value='Apply Invty Charges'></form>
    <br><br>
<?php
    }//close invty charges

// Companies query
$sqlco='SELECT c.CompanyNo, c.Company FROM `1companies` c JOIN `1employees` e ON c.CompanyNo=e.RCompanyNo GROUP BY c.CompanyNo '; $stmtco=$link->query($sqlco); $resultco=$stmtco->fetchAll();


// "FROM" SQL STATEMENTS FOR PAYROLL DATA
    $sqlfromwithdateposition='payroll_25payroll as p join payroll_1paydates d on d.PayrollID=p.PayrollID JOIN `1employees` as e ON p.IDNo = e.IDNo
    JOIN `attend_30latestpositionsinclresigned` cp ON cp.IDNo=e.IDNo 
    JOIN `1branches` b ON b.BranchNo=p.BranchNo
    JOIN `1branches` b1 ON b1.BranchNo=p.RecordInBranchNo
	JOIN attend_0positions ps ON cp.PositionID=ps.PositionID 
	LEFT JOIN acctg_1budgetentities be ON IF(b.PseudoBranch=1,(800 + ps.deptid),b.BranchNo)=be.EntityID 
    WHERE p.PayrollID=' .$payrollid;
	
    $sqlfromwithposition='payroll_25payroll as p  JOIN `1employees` as e ON p.IDNo = e.IDNo
    JOIN `attend_30latestpositionsinclresigned` cp ON cp.IDNo=e.IDNo
    JOIN `1branches` b ON b.BranchNo=p.BranchNo
    JOIN `1branches` b1 ON b1.BranchNo=p.RecordInBranchNo
	JOIN attend_0positions ps ON cp.PositionID=ps.PositionID 
	LEFT JOIN acctg_1budgetentities be ON IF(b.PseudoBranch=1,(800 + ps.deptid),b.BranchNo)=be.EntityID
    WHERE p.PayrollID=' .$payrollid. ' GROUP BY p.PayrollID, b1.CompanyNo, p.BranchNo,EntityID, DebitAccountID, p.DisburseVia HAVING Amount<>0';
	
	
    $sqlfrom='payroll_25payroll as p  JOIN `1employees` as e ON p.IDNo = e.IDNo
	JOIN `attend_30latestpositionsinclresigned` cp ON cp.IDNo=e.IDNo
    JOIN `1branches` b ON b.BranchNo=p.BranchNo
    JOIN `1branches` b1 ON b1.BranchNo=p.RecordInBranchNo
	JOIN attend_0positions ps ON cp.PositionID=ps.PositionID 
	LEFT JOIN acctg_1budgetentities be ON IF(b.PseudoBranch=1,(800 + ps.deptid),b.BranchNo)=be.EntityID
    WHERE p.PayrollID=' .$payrollid. ' GROUP BY p.PayrollID, b1.CompanyNo, p.BranchNo,EntityID, p.DisburseVia HAVING Amount<>0';
	
	
    $sqlfromadj='payroll_21paydayadjustments as a join payroll_0acctid as t on a.AdjustTypeNo=t.AdjustTypeNo JOIN `1employees` as e ON a.IDNo = e.IDNo
	JOIN `attend_30latestpositionsinclresigned` cp ON cp.IDNo=e.IDNo
    JOIN payroll_25payroll as p ON p.PayrollID=a.PayrollID AND p.IDNo=a.IDNo JOIN `1branches` b ON b.BranchNo=p.BranchNo
    JOIN `1branches` b1 ON b1.BranchNo=p.RecordInBranchNo
	JOIN attend_0positions ps ON cp.PositionID=ps.PositionID 
	LEFT JOIN acctg_1budgetentities be ON IF(b.PseudoBranch=1,(800 + ps.deptid),b.BranchNo)=be.EntityID
    WHERE a.PayrollID=' .$payrollid. ' and a.AdjustTypeNo ';
	
	
    $sqlfromadjtype=' NOT IN (10,20,26) GROUP BY a.PayrollID, a.AdjustTypeNo, b1.CompanyNo, p.BranchNo,EntityID, p.DisburseVia HAVING Amount<>0 ';
	
    $sqlfromadjinv='payroll_21paydayadjustments as a join payroll_0acctid as t on a.AdjustTypeNo=t.AdjustTypeNo JOIN `1employees` as e ON a.IDNo = e.IDNo
	JOIN `attend_30latestpositionsinclresigned` cp ON cp.IDNo=e.IDNo
    JOIN payroll_25payroll as p ON p.PayrollID=a.PayrollID AND p.IDNo=a.IDNo JOIN `1branches` b ON b.BranchNo=a.BranchNo
	JOIN attend_0positions ps ON cp.PositionID=ps.PositionID 
	LEFT JOIN acctg_1budgetentities be ON IF(b.PseudoBranch=1,(800 + ps.deptid),b.BranchNo)=be.EntityID
    WHERE a.PayrollID=' .$payrollid. ' and a.AdjustTypeNo  IN (10,20,26) GROUP BY a.PayrollID, a.AdjustTypeNo, b.CompanyNo, a.BranchNo,EntityID, p.DisburseVia HAVING Amount<>0 ';

// Common expressions in the union queries
    $invtypositions='1,2'; //branch and warehouse personnel for cost of sales
    $sqlencby=', \''.$_SESSION['(ak0)'].'\' as EncodedByNo, Now() as `TimeStamp`';
    
    
// CREATE TEMP DATA
$sql0='CREATE TEMPORARY TABLE `payroll_payrolldata`
(
RCompanyNo SMALLINT(6) NOT NULL,
Particulars VARCHAR(60),
FromBudgetOf SMALLINT(6) NOT NULL,
RecordInBranchNo SMALLINT(6) NOT NULL,
BranchNo SMALLINT(6) NOT NULL,
DebitAccountID SMALLINT(6) NOT NULL,
AmountValue DOUBLE NOT NULL DEFAULT 0,
Amount VARCHAR(10) NOT NULL DEFAULT 0,
DisburseVia TINYINT(1) NOT NULL DEFAULT 3,
EncodedByNo SMALLINT(6) NOT NULL,
`TimeStamp` DATETIME NOT NULL
)
 AS
Select b1.CompanyNo AS RCompanyNo, CONCAT("Payroll ", SUBSTR(d.FromDate,6), " to ", SUBSTR(d.ToDate,6), " - ",Entity) AS Particulars,IF(b.PseudoBranch=1,(800 + deptid),b.BranchNo) AS FromBudgetOf, p.RecordInBranchNo, p.BranchNo, 
IF(ps.deptid IN ('.$invtypositions.'),821,901) as DebitAccountID, 
ROUND(IFNULL(SUM(`RegDayBasic` + `p`.`VLBasic` + `p`.`SLBasic` + `p`.`LWPBasic` + `p`.`RHBasicforDaily` - `p`.`AbsenceBasicforMonthly` - `p`.`UndertimeBasic`+
`RegDayDeM`+`VLDeM`+`SLDeM`+`LWPDeM`-`AbsenceDeMforMonthly`-`UndertimeDeM`),0),2) AS AmountValue,
FORMAT(IFNULL(SUM(`RegDayBasic` + `p`.`VLBasic` + `p`.`SLBasic` + `p`.`LWPBasic` + `p`.`RHBasicforDaily` - `p`.`AbsenceBasicforMonthly` - `p`.`UndertimeBasic`+
`RegDayDeM`+`VLDeM`+`SLDeM`+`LWPDeM`-`AbsenceDeMforMonthly`-`UndertimeDeM`),0),2)  AS Amount, p.DisburseVia 
'.$sqlencby.' FROM '.$sqlfromwithdateposition.' GROUP BY p.PayrollID, b1.CompanyNo, p.BranchNo,EntityID,DebitAccountID, p.DisburseVia

 
UNION ALL
Select b1.CompanyNo AS RCompanyNo, CONCAT("Payroll (tax shield) ", SUBSTR(d.FromDate,6), " to ", SUBSTR(d.ToDate,6), " - ",Entity) AS Particulars,IF(b.PseudoBranch=1,(800 + deptid),b.BranchNo) AS FromBudgetOf, p.RecordInBranchNo, p.BranchNo, 914 as DebitAccountID, 
ROUND(IFNULL(SUM(`RegDayTaxSh`+`VLTaxSh`+`SLTaxSh`+`LWPTaxSh`-`AbsenceTaxShforMonthly`-`UndertimeTaxSh`),0),2) AS AmountValue,
FORMAT(IFNULL(SUM(`RegDayTaxSh`+`VLTaxSh`+`SLTaxSh`+`LWPTaxSh`-`AbsenceTaxShforMonthly`-`UndertimeTaxSh`),0),2) AS Amount, p.DisburseVia 
'.$sqlencby.' FROM '.$sqlfromwithdateposition.' GROUP BY p.PayrollID, b1.CompanyNo, p.BranchNo,EntityID, DebitAccountID, p.DisburseVia HAVING AmountValue<>0


UNION ALL
Select b1.CompanyNo AS RCompanyNo, CONCAT("Overtime - ",Entity) AS Particulars,IF(b.PseudoBranch=1,(800 + deptid),b.BranchNo) AS FromBudgetOf, p.RecordInBranchNo, p.BranchNo, IF(ps.deptid IN ('.$invtypositions.'),823,903) AS DebitAccountID, 
ROUND(IFNULL(SUM(`RegDayOT`+`RestDayOT`+`SpecOT`+`RHOT`),0),2) AS AmountValue, 
FORMAT(IFNULL(SUM(`RegDayOT`+`RestDayOT`+`SpecOT`+`RHOT`),0),2) AS Amount, p.DisburseVia'.$sqlencby.'
FROM '.$sqlfromwithposition.'
 
 
 
UNION ALL
Select b1.CompanyNo AS RCompanyNo, CONCAT("SSS-EE - ",Entity) AS Particulars,IF(b.PseudoBranch=1,(800 + deptid),b.BranchNo) AS FromBudgetOf, p.RecordInBranchNo, p.BranchNo, 503 AS DebitAccountID, ROUND(SUM(`SSS-EE`)*-1,2) AS AmountValue, FORMAT(SUM(`SSS-EE`)*-1,2) AS Amount, p.DisburseVia'.$sqlencby.'
FROM '.$sqlfrom.'

UNION ALL
Select b1.CompanyNo AS RCompanyNo, CONCAT("PhilHealth-EE - ",Entity) as Particulars,IF(b.PseudoBranch=1,(800 + deptid),b.BranchNo) AS FromBudgetOf, p.RecordInBranchNo, p.BranchNo, 502 as DebitAccountID, ROUND(Sum(`PhilHealth-EE`)*-1,2) AS AmountValue, FORMAT(Sum(`PhilHealth-EE`)*-1,2) AS Amount, p.DisburseVia'.$sqlencby.'
FROM '.$sqlfrom.' 




UNION ALL
Select b1.CompanyNo AS RCompanyNo, CONCAT("PagIbig-EE - ",Entity) as Particulars,IF(b.PseudoBranch=1,(800 + deptid),b.BranchNo) AS FromBudgetOf, p.RecordInBranchNo, p.BranchNo, 501 as DebitAccountID, ROUND(Sum(`PagIbig-EE`)*-1,2) AS AmountValue, FORMAT(Sum(`PagIbig-EE`)*-1,2) AS Amount, p.DisburseVia'.$sqlencby.' FROM '.$sqlfrom.'

UNION ALL
Select b1.CompanyNo AS RCompanyNo, CONCAT("WTax - ",Entity) as Particulars,IF(b.PseudoBranch=1,(800 + deptid),b.BranchNo) AS FromBudgetOf, p.RecordInBranchNo, p.BranchNo, 505 as DebitAccountID, ROUND(Sum(`WTax`)*-1,2) AS AmountValue, FORMAT(Sum(`WTax`)*-1,2) AS Amount, p.DisburseVia'.$sqlencby.'
FROM '.$sqlfrom.'


UNION ALL
Select b1.CompanyNo AS RCompanyNo, CONCAT(t.AdjustType, " ",Entity) as Particulars,IF(b.PseudoBranch=1,(800 + deptid),b.BranchNo) AS FromBudgetOf, p.RecordInBranchNo, p.BranchNo, t.AccountID as DebitAccountID, ROUND(Sum(a.AdjustAmt),2) AS AmountValue, FORMAT(Sum(a.AdjustAmt),2) AS Amount, p.DisburseVia'.$sqlencby.' FROM '.$sqlfromadj.$sqlfromadjtype.'
AND a.AdjustTypeNo NOT IN (21,22,23,24)


UNION ALL
Select b1.CompanyNo AS RCompanyNo, CONCAT(t.AdjustType, " ",Entity) as Particulars,IF(b.PseudoBranch=1,(800 + deptid),b.BranchNo) AS FromBudgetOf, p.RecordInBranchNo, p.BranchNo, t.AccountID as DebitAccountID, ROUND(Sum(a.AdjustAmt),2) AS AmountValue, FORMAT(Sum(a.AdjustAmt),2) AS Amount, p.DisburseVia'.$sqlencby.' FROM '.$sqlfromadj.' IN (21,22,23,24) GROUP BY p.RecordInBranchNo,EntityID,a.AdjustTypeNo, p.DisburseVia


UNION ALL
Select b.CompanyNo AS RCompanyNo, CONCAT(t.AdjustType, " ",Entity) as Particulars, IF(b.PseudoBranch=1,(800 + deptid),b.BranchNo) AS FromBudgetOf, a.BranchNo AS RecordInBranchNo, a.BranchNo, t.AccountID as DebitAccountID, ROUND(Sum(a.AdjustAmt),2) AS AmountValue, FORMAT(Sum(a.AdjustAmt),2) AS Amount, p.DisburseVia'.$sqlencby.' FROM '.$sqlfromadjinv.' 


UNION ALL
Select b1.CompanyNo AS RCompanyNo, CONCAT("cash payroll - ",Entity) as Particulars,IF(b.PseudoBranch=1,(800 + deptid),b.BranchNo) AS FromBudgetOf, p.RecordInBranchNo, p.BranchNo, 100 as DebitAccountID, ROUND(Sum(p.NetPay)*-1,2) AS AmountValue, FORMAT(Sum(p.NetPay)*-1,2) AS Amount, p.DisburseVia'.$sqlencby.'
FROM `payroll_25payrolldatalookup` as p JOIN `1employees` as e ON p.IDNo = e.IDNo 
JOIN `attend_30latestpositionsinclresigned` cp ON cp.IDNo=e.IDNo
JOIN `1branches` b ON b.BranchNo=p.BranchNo JOIN `1branches` b1 ON b1.BranchNo=p.RecordInBranchNo
JOIN attend_0positions ps ON cp.PositionID=ps.PositionID 
	LEFT JOIN acctg_1budgetentities be ON IF(b.PseudoBranch=1,(800 + ps.deptid),b.BranchNo)=be.EntityID
WHERE p.PayrollID=' .$payrollid. ' and p.DisburseVia=0 GROUP BY p.PayrollID, b1.CompanyNo, p.BranchNo,EntityID, p.DisburseVia HAVING Amount<>0';

// echo $sql0; exit();
$stmt0=$link->prepare('DROP TEMPORARY TABLE IF EXISTS payroll_payrolldata'); $stmt0->execute();
$stmt0=$link->prepare($sql0); $stmt0->execute();

$stmt0=$link->prepare('DROP TEMPORARY TABLE IF EXISTS payroll_payrolldatacompanies'); $stmt0->execute();
$sql0='CREATE TEMPORARY TABLE `payroll_payrolldatacompanies` AS Select 1, CONCAT("payroll via ",IF(p.DisburseVia=2,"GCash","BPI")," - ",c.Company) as Particulars, 0 AS FromBudgetOf, 0 AS RecordInBranchNo, b1.BranchNo, (210+RCompanyNo) as DebitAccountID, ROUND(Sum(p.AmountValue),2) AS AmountValue, FORMAT(Sum(p.AmountValue),2) AS Amount, p.DisburseVia'.$sqlencby.'
FROM `payroll_payrolldata` p JOIN `1branches` b1 ON b1.CompanyNo=p.RCompanyNo AND b1.PseudoBranch=1
JOIN `1companies` c ON c.CompanyNo=b1.CompanyNo
WHERE b1.CompanyNo<>1 GROUP BY b1.CompanyNo, p.DisburseVia HAVING ROUND(AmountValue)<>0 '; 

$stmt0=$link->prepare($sql0); $stmt0->execute();

$sql0='INSERT INTO payroll_payrolldata Select * FROM payroll_payrolldatacompanies';
$stmt0=$link->prepare($sql0); $stmt0->execute();


// methods of disbursement
$sqld='SELECT pd.DisburseVia, Disburse_Via, AccountID FROM payroll_payrolldata pd JOIN payroll_0disbursevia dv ON dv.DisburseVia=pd.DisburseVia WHERE pd.DisburseVia<>0 GROUP BY pd.DisburseVia '; $stmtd=$link->query($sqld); $resultd=$stmtd->fetchAll();

if(!isset($_POST['submit'])){ $_POST['submit']='Lookup';}
if ($_POST['submit']=='Lookup'){  
/* //QUERIES FOR GOVT PAYMENTS
$sqlsss='SELECT PayrollID, Company, g.CompanyNo, RecordInBranchNo,IF(b.PseudoBranch<>1 OR ISNULL(cp.IDNo),b.BranchNo,(800 + ps.deptid)) AS FromBudgetOf, be.Entity AS From_Budget_Of, FORMAT(Sum(`SSS-EE`),2) as `SSS-EE`, FORMAT(Sum(`SSS-ERTotal`),2) as `SSS-ERTotal`, ROUND(Sum(`SSSTotal`),2) as SSSTotalValue, FORMAT(Sum(`SSSTotal`),2) as SSSTotal  FROM payroll_40sss g JOIN `1branches` b ON b.BranchNo=g.ActualBranchNo JOIN 1companies c ON c.CompanyNo=g.CompanyNo LEFT JOIN attend_30currentpositions cp ON g.IDNo=cp.IDNo 
JOIN attend_0positions ps ON cp.PositionID=ps.PositionID 
	LEFT JOIN acctg_1budgetentities be ON IF(b.PseudoBranch=1,(800 + ps.deptid),b.BranchNo)=be.EntityID
where PayrollID=' .$payrollid. ' Group By PayrollID, FromBudgetOf, CompanyNo; '; */
//QUERIES FOR GOVT PAYMENTS
$sqlsss='SELECT PayrollID, Company, g.CompanyNo, RecordInBranchNo,IF(b.PseudoBranch<>1 OR ISNULL(cp.IDNo),b.BranchNo,(800 + ps.deptid)) AS FromBudgetOf, be.Entity AS From_Budget_Of, FORMAT(Sum(`SSS-EE`),2) as `SSS-EE`, FORMAT(Sum(`SSS-ERTotal`),2) as `SSS-ERTotal`, ROUND(Sum(`SSSTotal`),2) as SSSTotalValue, FORMAT(Sum(`SSSTotal`),2) as SSSTotal  FROM payroll_40sss g JOIN `1branches` b ON b.BranchNo=g.ActualBranchNo JOIN 1companies c ON c.CompanyNo=g.CompanyNo JOIN attend_30latestpositionsinclresigned cp ON g.IDNo=cp.IDNo 
JOIN attend_0positions ps ON cp.PositionID=ps.PositionID JOIN 1departments d ON ps.deptid=d.deptid
	LEFT JOIN acctg_1budgetentities be ON IF(b.PseudoBranch=1,(800 + ps.deptid),b.BranchNo)=be.EntityID
where PayrollID=' .$payrollid. ' Group By PayrollID, FromBudgetOf, CompanyNo; ';
$stmt=$link->query($sqlsss); $resultsss=$stmt->fetchAll();


$sqlphic='SELECT PayrollID, Company, g.CompanyNo, RecordInBranchNo, IF(b.PseudoBranch<>1 OR ISNULL(cp.IDNo),b.BranchNo,(800 + ps.deptid)) AS FromBudgetOf, be.Entity AS From_Budget_Of, FORMAT(Sum(`PHIC-EE`),2) as `PHIC-EE`, FORMAT(Sum(`PHIC-ER`),2) as `PHIC-ER`, ROUND(Sum(`PHICTotal`),2) as PHICTotalValue, FORMAT(Sum(`PHICTotal`),2) as PHICTotal  FROM payroll_41phic g JOIN `1branches` b ON b.BranchNo=g.ActualBranchNo JOIN attend_30latestpositionsinclresigned cp ON g.IDNo=cp.IDNo 
JOIN attend_0positions ps ON cp.PositionID=ps.PositionID JOIN 1departments d ON ps.deptid=d.deptid
	LEFT JOIN acctg_1budgetentities be ON IF(b.PseudoBranch=1,(800 + ps.deptid),b.BranchNo)=be.EntityID where PayrollID=' .$payrollid. ' Group By PayrollID, FromBudgetOf, CompanyNo;';
$stmt=$link->query($sqlphic); $resultphic=$stmt->fetchAll();

$sqlpagibig='SELECT PayrollID, Company, g.CompanyNo, RecordInBranchNo, IF(b.PseudoBranch<>1 OR ISNULL(cp.IDNo),b.BranchNo,(800 + ps.deptid)) AS FromBudgetOf, be.Entity AS From_Budget_Of, FORMAT(Sum(`PagIbig-EE`),2) as `PagIbig-EE`,  FORMAT(Sum(`PagIbig-ER`),2) as `PagIbig-ER`, ROUND(Sum(`PagIbigTotal`),2) as PagIbigTotalValue, FORMAT(Sum(`PagIbigTotal`),2) as PagIbigTotal  FROM payroll_42pagibig g JOIN `1branches` b ON b.BranchNo=g.ActualBranchNo JOIN attend_30latestpositionsinclresigned cp ON g.IDNo=cp.IDNo 
JOIN attend_0positions ps ON cp.PositionID=ps.PositionID JOIN 1departments d ON ps.deptid=d.deptid 
	LEFT JOIN acctg_1budgetentities be ON IF(b.PseudoBranch=1,(800 + ps.deptid),b.BranchNo)=be.EntityID
 where PayrollID=' .$payrollid. ' Group By PayrollID, FromBudgetOf, CompanyNo;';
$stmt=$link->query($sqlpagibig); $resultpagibig=$stmt->fetchAll();

$sqlwtax='SELECT PayrollID, Company, g.CompanyNo, RecordInBranchNo, IF(b.PseudoBranch<>1 OR ISNULL(cp.IDNo),b.BranchNo,(800 + ps.deptid)) AS FromBudgetOf, be.Entity AS From_Budget_Of, ROUND(Sum(`WTax`),2) as `WTaxValue`, FORMAT(Sum(`WTax`),2) as `WTax`  FROM payroll_43wtax g JOIN `1branches` b ON b.BranchNo=g.ActualBranchNo JOIN attend_30latestpositionsinclresigned cp ON g.IDNo=cp.IDNo 
JOIN attend_0positions ps ON cp.PositionID=ps.PositionID JOIN 1departments d ON ps.deptid=d.deptid 
	LEFT JOIN acctg_1budgetentities be ON IF(b.PseudoBranch=1,(800 + ps.deptid),b.BranchNo)=be.EntityID where PayrollID=' .$payrollid. ' Group By PayrollID, FromBudgetOf, CompanyNo;';
$stmt=$link->query($sqlwtax); $resultwtax=$stmt->fetchAll();

?>
<div style="width:100%; margin:0 auto;">
    <div style="float:left; width:40%; overflow: auto;">
<?php
    $columnnames=array('Particulars', 'RecordInBranch','From_Budget_Of', 'DebitAccountID', 'Amount');
    $coltototal='AmountValue'; $showgrandtotal=true; 
    
    foreach ($resultd as $d){
    foreach ($resultco as $co){
        echo '<b>'.$co['Company'].' - '.$d['Disburse_Via'].'</b><br>';
        $sql='SELECT p.*, c.Company, b.Branch AS RecordInBranch, Entity AS From_Budget_Of FROM `payroll_payrolldata` p JOIN `1branches` b ON b.BranchNo=p.RecordInBranchNo LEFT JOIN `1companies` c on p.`RCompanyNo`=c.CompanyNo LEFT JOIN acctg_1budgetentities ab ON ab.EntityID=p.FromBudgetOf WHERE p.RCompanyNo='.$co['CompanyNo'].' ';
        $sql.=' AND p.DisburseVia IN (0,'.$d['DisburseVia'].')';
        include('../backendphp/layout/displayastableonlynoheaders.php');
        echo '<br><br>';
    }
    }
?>
</div><div style="float:left; margin: 0 0 40%; width:30%; overflow: auto;">
<?php
    $stmt=$link->prepare('CREATE TEMPORARY TABLE ssscontri AS '.$sqlsss); $stmt->execute();
    foreach ($resultco as $co){
        echo '<b>'.$co['Company'].' - SSS Contributions</b><br>';
        $sql='SELECT g.*, Company, b1.Branch AS RecordInBranch FROM ssscontri g
        JOIN `1branches` b1 ON b1.BranchNo=g.RecordInBranchNo WHERE g.CompanyNo='.$co['CompanyNo']
        .' ORDER BY RecordInBranch,From_Budget_Of';
        $columnnames=array('RecordInBranch','From_Budget_Of', 'SSS-EE', 'SSS-ERTotal', 'SSSTotal');
        $coltototal='SSSTotalValue'; $showgrandtotal=true;
        include('../backendphp/layout/displayastableonlynoheaders.php');
        echo '<br><br>';
    }
    
    $stmt=$link->prepare('CREATE TEMPORARY TABLE sssloan AS SELECT g.CompanyNo, Company, concat(FirstName, " ", SurName) as FullName, RecordInBranchNo, ActualBranchNo,IF(b.PseudoBranch=1,(800 + ps.deptid),b.BranchNo) AS FromBudgetOf, ROUND(Sum(`SSSLoan`),2) as `SSSLoanValue`, FORMAT(Sum(`SSSLoan`),2) as `SSSLoan`  FROM payroll_44sssloan g JOIN 1branches b ON b.BranchNo=g.ActualBranchNo'
            . '  JOIN attend_30latestpositionsinclresigned cp ON g.IDNo=cp.IDNo 
JOIN attend_0positions ps ON cp.PositionID=ps.PositionID JOIN 1departments d ON ps.deptid=d.deptid where PayrollID=' .$payrollid. ' Group By g.IDNo, g.CompanyNo, RecordInBranchNo,FromBudgetOf;'); $stmt->execute();
    foreach ($resultco as $co){
        echo '<b>'.$co['Company'].' - SSS Loans (Salary)</b><br>';
        $sql='SELECT g.*, Company, b1.Branch AS RecordInBranch, Entity AS From_Budget_Of FROM sssloan g
        JOIN `acctg_1budgetentities` b ON b.EntityID=g.FromBudgetOf
        JOIN `1branches` b1 ON b1.BranchNo=g.RecordInBranchNo  WHERE g.CompanyNo='.$co['CompanyNo']
        .' ORDER BY RecordInBranch,From_Budget_Of';
        $columnnames=array('FullName','RecordInBranch','From_Budget_Of', 'SSSLoan');
        $coltototal='SSSLoanValue'; $showgrandtotal=true;
        include('../backendphp/layout/displayastableonlynoheaders.php');
        echo '<br><br>';
    }

    $stmt=$link->prepare('CREATE TEMPORARY TABLE sssloancalamity AS SELECT g.CompanyNo, Company, concat(FirstName, " ", SurName) as FullName, RecordInBranchNo, ActualBranchNo,IF(b.PseudoBranch=1,(800 + ps.deptid),b.BranchNo) AS FromBudgetOf, ROUND(Sum(`SSSLoan`),2) as `SSSLoanValue`, FORMAT(Sum(`SSSLoan`),2) as `SSSLoan` FROM payroll_44sssloancalamity g JOIN 1branches b ON b.BranchNo=g.ActualBranchNo'
            . '  JOIN attend_30latestpositionsinclresigned cp ON g.IDNo=cp.IDNo 
JOIN attend_0positions ps ON cp.PositionID=ps.PositionID JOIN 1departments d ON ps.deptid=d.deptid where PayrollID=' .$payrollid. ' Group By g.IDNo, g.CompanyNo, RecordInBranchNo,FromBudgetOf;'); $stmt->execute();
    foreach ($resultco as $co){
        echo '<b>'.$co['Company'].' - SSS Loans (Calamity)</b><br>';
        $sql='SELECT g.*, Company, b1.Branch AS RecordInBranch, Entity AS From_Budget_Of FROM sssloan g
        JOIN `acctg_1budgetentities` b ON b.EntityID=g.FromBudgetOf
        JOIN `1branches` b1 ON b1.BranchNo=g.RecordInBranchNo  WHERE g.CompanyNo='.$co['CompanyNo']
        .' ORDER BY RecordInBranch,From_Budget_Of';
        $columnnames=array('FullName','RecordInBranch','From_Budget_Of', 'SSSLoan');
        $coltototal='SSSLoanValue'; $showgrandtotal=true;
        include('../backendphp/layout/displayastableonlynoheaders.php');
        echo '<br><br>';
    }

    $stmt=$link->prepare('CREATE TEMPORARY TABLE phiccontri AS '.$sqlphic); $stmt->execute();
    foreach ($resultco as $co){
        echo '<b>'.$co['Company'].' - PHIC Contributions</b><br>';
        $sql='SELECT g.*, Company, b1.Branch AS RecordInBranch FROM phiccontri g
        JOIN `1branches` b1 ON b1.BranchNo=g.RecordInBranchNo  WHERE g.CompanyNo='.$co['CompanyNo']
        .' ORDER BY RecordInBranch,From_Budget_Of';
        $columnnames=array('RecordInBranch','From_Budget_Of', 'PHIC-EE', 'PHIC-ER', 'PHICTotal');
        $coltototal='PHICTotalValue'; $showgrandtotal=true;
        include('../backendphp/layout/displayastableonlynoheaders.php');
        echo '<br><br>';
    }
?>
</div><div style="float:right; width:30%; overflow: auto;">
<?php
    $stmt=$link->prepare('CREATE TEMPORARY TABLE pagcontri AS '.$sqlpagibig); $stmt->execute();
    foreach ($resultco as $co){
        echo '<b>'.$co['Company'].' - PagIbig Contributions</b><br>';
        $sql='SELECT g.*, Company, b1.Branch AS RecordInBranch  FROM pagcontri g
        JOIN `1branches` b1 ON b1.BranchNo=g.RecordInBranchNo WHERE g.CompanyNo='.$co['CompanyNo']
        .' ORDER BY RecordInBranch,From_Budget_Of';
        $columnnames=array('RecordInBranch','From_Budget_Of', 'PagIbig-EE', 'PagIbig-ER', 'PagIbigTotal');
        $coltototal='PagIbigTotalValue'; $showgrandtotal=true;
        include('../backendphp/layout/displayastableonlynoheaders.php');
        echo '<br><br>';
    }
    $stmt=$link->prepare('CREATE TEMPORARY TABLE pagloan AS SELECT g.CompanyNo, Company, concat(FirstName, " ", SurName) as FullName, RecordInBranchNo, ActualBranchNo, IF(b.PseudoBranch=1,(800 + ps.deptid),b.BranchNo) AS FromBudgetOf,ROUND(Sum(`PagibigLoan`),2) as `PagibigLoanValue`, FORMAT(Sum(`PagibigLoan`),2) as `PagibigLoan`  FROM payroll_45pagibigloan g JOIN 1branches b ON b.BranchNo=g.ActualBranchNo  JOIN attend_30latestpositionsinclresigned cp ON g.IDNo=cp.IDNo 
JOIN attend_0positions ps ON cp.PositionID=ps.PositionID JOIN 1departments d ON ps.deptid=d.deptid 
         where PayrollID=' .$payrollid. ' Group By g.IDNo, g.CompanyNo, RecordInBranchNo,FromBudgetOf;'); $stmt->execute();
    foreach ($resultco as $co){
        echo '<b>'.$co['Company'].' - PagIbig Loans (Salary)</b><br>';
        $sql='SELECT g.*, Company, b1.Branch AS RecordInBranch, Entity AS From_Budget_Of  FROM pagloan g
        JOIN `acctg_1budgetentities` b ON b.EntityID=g.FromBudgetOf
        JOIN `1branches` b1 ON b1.BranchNo=g.RecordInBranchNo WHERE g.CompanyNo='.$co['CompanyNo']
        .' ORDER BY RecordInBranch,From_Budget_Of';
        $columnnames=array('FullName','RecordInBranch','From_Budget_Of', 'PagibigLoan');
        $coltototal='PagibigLoanValue'; $showgrandtotal=true;
        include('../backendphp/layout/displayastableonlynoheaders.php');
        echo '<br><br>';
    }
	
	
    $stmt=$link->prepare('CREATE TEMPORARY TABLE pagloancalamity AS SELECT g.CompanyNo, Company, concat(FirstName, " ", SurName) as FullName, RecordInBranchNo, ActualBranchNo, IF(b.PseudoBranch=1,(800 + ps.deptid),b.BranchNo) AS FromBudgetOf,ROUND(Sum(`PagibigLoan`),2) as `PagibigLoanValue`, FORMAT(Sum(`PagibigLoan`),2) as `PagibigLoan`  FROM payroll_45pagibigloancalamity g JOIN 1branches b ON b.BranchNo=g.ActualBranchNo  JOIN attend_30latestpositionsinclresigned cp ON g.IDNo=cp.IDNo 
JOIN attend_0positions ps ON cp.PositionID=ps.PositionID JOIN 1departments d ON ps.deptid=d.deptid 
         where PayrollID=' .$payrollid. ' Group By g.IDNo, g.CompanyNo, RecordInBranchNo,FromBudgetOf;'); $stmt->execute();
    foreach ($resultco as $co){
        echo '<b>'.$co['Company'].' - PagIbig Loans (Calamity)</b><br>';
        $sql='SELECT g.*, Company, b1.Branch AS RecordInBranch, Entity AS From_Budget_Of  FROM pagloan g
        JOIN `acctg_1budgetentities` b ON b.EntityID=g.FromBudgetOf
        JOIN `1branches` b1 ON b1.BranchNo=g.RecordInBranchNo WHERE g.CompanyNo='.$co['CompanyNo']
        .' ORDER BY RecordInBranch,From_Budget_Of';
        $columnnames=array('FullName','RecordInBranch','From_Budget_Of', 'PagibigLoan');
        $coltototal='PagibigLoanValue'; $showgrandtotal=true;
        include('../backendphp/layout/displayastableonlynoheaders.php');
        echo '<br><br>';
    }
	
	
    $stmt=$link->prepare('CREATE TEMPORARY TABLE wtax AS '.$sqlwtax); $stmt->execute();
    foreach ($resultco as $co){
        echo '<b>'.$co['Company'].' - WTax</b><br>';
        $sql='SELECT g.*, Company, RecordInBranchNo, b1.Branch AS RecordInBranch  FROM wtax g
        JOIN `1branches` b1 ON b1.BranchNo=g.RecordInBranchNo WHERE g.CompanyNo='.$co['CompanyNo']
        .' ORDER BY RecordInBranch,From_Budget_Of';
        $columnnames=array('RecordInBranch','From_Budget_Of', 'WTax');
        $coltototal='WTaxValue'; $showgrandtotal=true;
        include('../backendphp/layout/displayastableonlynoheaders.php');
        echo '<br><br>';
    }
?>
</div>
</div>
<?php
} else {//send to acctg vouchers
    
    if (!allowedToOpen(8271,'1rtc')) {  header("Location:sendtoacctg.php?denied=true");}
   
  /* function getVchTxnID($cvno,$payrollprefix){
	   global $currentyr;
	   $path=$_SERVER['DOCUMENT_ROOT']; 
      include_once $path.'/acrossyrs/dbinit/userinit.php';
		$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;

      $sql='Select TxnID from `'.$payrollprefix.'1rtc`.`acctg_2cvmain` where VoucherNo=\''.$cvno.'\'';
	   $stmt=$link->query($sql); $result=$stmt->fetch();  
        return $result['TxnID'];
   }
   
   */
   
   //GET PAYROLL DATE AND PAYROLLCODE
   $sql='SELECT PayrollDate, PayrollCode FROM `payroll_1paydates` where PayrollID='.$payrollid; //echo $sql;break;
   $stmt=$link->query($sql);
   $resultpaydate=$stmt->fetch();
   $paydate=$resultpaydate['PayrollDate'];
   $payrollyr=substr($paydate,0,4);$payrollprefix=$payrollyr.'_';
   
   //GET LAST VOUCHER NUMBER
   include_once $path.'/acrossyrs/commonfunctions/lastnum.php'; 
    $cvno=lastNum('CVNo',''.$payrollprefix.'1rtc`.`acctg_2cvmain',((date('Y',$payrollyr))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',$payrollyr)).',2)')+1;
   $checkno=0;


if (in_array($_POST['submit'],array('SSS','Philhealth','PagIbig','WTax'))){
    /* $sqlfromgovt1=' JOIN `1branches` b ON b.BranchNo=g.RecordInBranchNo LEFT JOIN attend_30currentpositions cp ON g.IDNo=cp.IDNo JOIN 1companies c ON c.CompanyNo=g.CompanyNo '
        . ' WHERE PayrollID=' .$payrollid. ' AND g.CompanyNo='; */
    $sqlfromgovt1=' JOIN `1branches` b ON b.BranchNo=g.RecordInBranchNo JOIN attend_30latestpositionsinclresigned cp ON g.IDNo=cp.IDNo JOIN attend_0positions p ON cp.PositionID=p.PositionID JOIN 1departments d ON p.deptid=d.deptid JOIN 1companies c ON c.CompanyNo=g.CompanyNo '
        . ' WHERE PayrollID=' .$payrollid. ' AND g.CompanyNo=';
$sqlfromgovt2=' GROUP BY PayrollID, FromBudgetOf, g.CompanyNo';

}
   
switch ($_POST['submit']){
    case 'Payroll':
    // ENTER A PAYROLL VOUCHER FOR EACH COMPANY
   foreach ($resultd as $d){
   foreach ($resultco as $co){
    $cvno=$cvno+1;
    $crid=($co['CompanyNo']==1)?$d['AccountID']:(411);
    // insert voucher main - payroll
    $cvno=lastNum('CVNo','acctg_2cvmain',((date('Y',strtotime($paydate)))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',strtotime($paydate))).',2)')+1;
    $sqlinsert='INSERT INTO `'.$payrollprefix.'1rtc`.`acctg_2cvmain` SET PayeeNo=998, Payee="Payroll", `Posted`=0, CreditAccountID='.$crid.',
        Date=\''.$paydate.'\', CVNo='.$cvno.', DateofCheck=\''.$paydate.'\',
        CheckNo=CONCAT(\'Payroll-'.str_pad($payrollid,2,'0',STR_PAD_LEFT).'\',\'-\',\''.$co['CompanyNo'].substr($d['Disburse_Via'], 0, 1).'\'), Remarks=\''.$co['Company'].' '.$d['Disburse_Via'].'\',
	EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; // echo $sqlinsert;
    
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
    
    
    // insert voucher sub - payroll
   $sqlinsert='Insert into `'.$payrollprefix.'1rtc`.`acctg_2cvsub` (CVNo, Particulars,FromBudgetOf, BranchNo, DebitAccountID, Amount, EncodedByNo, `TimeStamp`)
Select '.$cvno.', Particulars, FromBudgetOf, p.RecordInBranchNo, DebitAccountID, AmountValue'.$sqlencby.' FROM payroll_payrolldata p WHERE p.RCompanyNo='.$co['CompanyNo'];
   $sqlinsert.=($d['DisburseVia']<>3?' AND p.DisburseVia IN ('.$d['DisburseVia'].')':' AND p.DisburseVia IN (0,'.$d['DisburseVia'].')');
   
        $stmt=$link->prepare($sqlinsert); $stmt->execute();

   }
   }
    break;

    case 'SSS':
// PAYMENTS TO SSS

$sqlsssmain='SELECT PayrollID, Company, g.CompanyNo, bm.AccountID FROM payroll_40sss g JOIN `banktxns_1maintaining` bm ON bm.RCompanyUse=g.CompanyNo JOIN 1companies c ON c.CompanyNo=g.CompanyNo WHERE PayrollID=' .$payrollid. ' Group By PayrollID, g.CompanyNo;';
$stmt=$link->query($sqlsssmain); $resultsssmain=$stmt->fetchAll();
$cvno=lastNum('CVNo','acctg_2cvmain',((date('Y',strtotime($paydate)))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',strtotime($paydate))).',2)');
foreach ($resultsssmain as $row){
   $cvno=$cvno+1; $checkno=$checkno+1; //CheckNo='.$checkno.'
   $sqlinsert='INSERT INTO `acctg_2cvmain` SET PayeeNo=800, Payee="Social Security System", `Posted`=0, CreditAccountID='.$row['AccountID'].', Date=LAST_DAY(\''.date("Y-m-d").'\'), CVNo='.$cvno.', DateofCheck=LAST_DAY(\''.date("Y-m-d").'\'), CheckNo=CONCAT("SSS-",LEFT(MONTHNAME(\''.$paydate.'\'),3),"-'.$row['Company'].'"), Remarks=\''.$row['Company'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	// echo $sqlinsert; break;
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
    
    $sqlsss='SELECT PayrollID, Company, g.CompanyNo, RecordInBranchNo, IF(b.PseudoBranch=1,(800 + d.deptid),b.BranchNo) AS FromBudgetOf, IF(b.PseudoBranch=1,dept,b.Branch) AS From_Budget_Of,
        ROUND(Sum(`SSS-EE`),2) as `SSS-EE`,ROUND(Sum(`SSS-ERTotal`),2) as `SSS-ERTotal`  FROM payroll_40sss g '.$sqlfromgovt1.$row['CompanyNo'].$sqlfromgovt2;
    $stmt=$link->query($sqlsss); $resultsss=$stmt->fetchAll();

    foreach ($resultsss as $rowsub){
   $sqlinsert='Insert into `'.$payrollprefix.'1rtc`.`acctg_2cvsub` (CVNo, Particulars,FromBudgetOf, BranchNo, DebitAccountID, Amount, EncodedByNo, `TimeStamp`)
   Select '.$cvno.', CONCAT("SSS-EE - ",\''.$rowsub['From_Budget_Of']. '\') as Particulars,'.$rowsub['FromBudgetOf'].', '.$rowsub['RecordInBranchNo']. ' as BranchNo, 503 as DebitAccountID, '.$rowsub['SSS-EE'].' as Amount, \''.$_SESSION['(ak0)'].'\' as EncodedByNo, Now() as TimeStamp
   union all Select '.$cvno.', CONCAT("SSS-ERTotal - ",\''.$rowsub['From_Budget_Of']. '\') as Particulars,'.$rowsub['FromBudgetOf'].', '.$rowsub['RecordInBranchNo']. ' as BranchNo, 909 as DebitAccountID, '.$rowsub['SSS-ERTotal'].' as Amount, \''.$_SESSION['(ak0)'].'\' as EncodedByNo, Now() as TimeStamp';
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
    }
}
    break;

    case 'Philhealth':
// PAYMENTS TO PHIC

$sqlphicmain='SELECT PayrollID, c.Company, g.CompanyNo, bm.AccountID FROM payroll_41phic g JOIN `banktxns_1maintaining` bm ON bm.RCompanyUse=g.CompanyNo JOIN 1companies c ON c.CompanyNo=g.CompanyNo WHERE PayrollID=' .$payrollid. ' Group By PayrollID, g.CompanyNo;';
$stmt=$link->query($sqlphicmain); $resultphicmain=$stmt->fetchAll();
$cvno=lastNum('CVNo','acctg_2cvmain',((date('Y',strtotime($paydate)))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',strtotime($paydate))).',2)');
foreach ($resultphicmain as $row){
   $cvno=$cvno+1; $checkno=$checkno+1;
   $sqlinsert='INSERT INTO `acctg_2cvmain` SET PayeeNo=801, Payee="Phil Health", `Posted`=0, CreditAccountID='.$row['AccountID'].', Date=LAST_DAY(\''.date("Y-m-d").'\'), CVNo='.$cvno.', DateofCheck=LAST_DAY(\''.date("Y-m-d").'\'), CheckNo=CONCAT("PHIC-",LEFT(MONTHNAME(\''.$paydate.'\'),3),"-'.$row['Company'].'"), Remarks=\''.$row['Company'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	// echo $sqlinsert; break;
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
     
    $sqlphic='SELECT PayrollID, c.Company, g.CompanyNo, RecordInBranchNo, IF(b.PseudoBranch=1,(800 + d.deptid),b.BranchNo) AS FromBudgetOf, IF(b.PseudoBranch=1,dept,b.Branch) AS From_Budget_Of,
    ROUND(Sum(`PHIC-EE`),2) as `PHIC-EE`, ROUND(Sum(`PHIC-ER`),2) as `PHIC-ER`  FROM payroll_41phic g '.$sqlfromgovt1.$row['CompanyNo'].$sqlfromgovt2;
    $stmt=$link->query($sqlphic); $resultphic=$stmt->fetchAll();
    
   foreach ($resultphic as $rowsub){
   $sqlinsert='Insert into `'.$payrollprefix.'1rtc`.`acctg_2cvsub` (CVNo, Particulars,FromBudgetOf, BranchNo, DebitAccountID, Amount, EncodedByNo, `TimeStamp`)
   Select '.$cvno.', CONCAT("PHIC-EE - ",\''.$rowsub['From_Budget_Of']. '\') as Particulars,'.$rowsub['FromBudgetOf'].' , '.$rowsub['RecordInBranchNo']. ' as BranchNo, 502 as DebitAccountID, '.$rowsub['PHIC-EE'].' as Amount, \''.$_SESSION['(ak0)'].'\' as EncodedByNo, Now() as TimeStamp
   union all Select '.$cvno.', CONCAT("PHIC-ER - ",\''.$rowsub['From_Budget_Of']. '\') as Particulars,'.$rowsub['FromBudgetOf'].','.$rowsub['RecordInBranchNo']. ' as BranchNo, 910 as DebitAccountID, '.$rowsub['PHIC-ER'].' as Amount, \''.$_SESSION['(ak0)'].'\' as EncodedByNo, Now() as TimeStamp';
         $stmt=$link->prepare($sqlinsert); $stmt->execute();
   }
}
    break;
    
    case 'PagIbig':
// PAYMENTS TO PagIbig

$sqlpagibigmain='SELECT PayrollID, c.Company, g.CompanyNo, bm.AccountID FROM payroll_42pagibig g JOIN `banktxns_1maintaining` bm ON bm.RCompanyUse=g.CompanyNo JOIN 1companies c ON c.CompanyNo=g.CompanyNo WHERE PayrollID=' .$payrollid. ' Group By PayrollID, g.CompanyNo;';
$stmt=$link->query($sqlpagibigmain); $resultpagibigmain=$stmt->fetchAll();
$cvno=lastNum('CVNo','acctg_2cvmain',((date('Y',strtotime($paydate)))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',strtotime($paydate))).',2)');
foreach ($resultpagibigmain as $row){
   $cvno=$cvno+1; $checkno=$checkno+1; //CheckNo='.$checkno.'
   $sqlinsert='INSERT INTO `acctg_2cvmain` SET PayeeNo=802, Payee="Pag-Ibig Fund", `Posted`=0, CreditAccountID='.$row['AccountID'].', Date=LAST_DAY(\''.date("Y-m-d").'\'), CVNo='.$cvno.', DateofCheck=LAST_DAY(\''.date("Y-m-d").'\'), CheckNo=CONCAT("Pagibig-",LEFT(MONTHNAME(\''.$paydate.'\'),3),"-'.$row['Company'].'"), Remarks=\''.$row['Company'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	// echo $sqlinsert; break;
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
        
    $sqlpagibig='SELECT PayrollID, c.Company, g.CompanyNo, RecordInBranchNo, IF(b.PseudoBranch=1,(800 + d.deptid),b.BranchNo) AS FromBudgetOf, IF(b.PseudoBranch=1,dept,b.Branch) AS From_Budget_Of,
    ROUND(Sum(`PagIbig-EE`),2) as `PagIbig-EE`,ROUND(Sum(`PagIbig-ER`),2) as `PagIbig-ER` FROM payroll_42pagibig g '.$sqlfromgovt1.$row['CompanyNo'].$sqlfromgovt2;
    $stmt=$link->query($sqlpagibig); $resultpagibig=$stmt->fetchAll();
    
    foreach ($resultpagibig as $rowsub){
   $sqlinsert='Insert into `'.$payrollprefix.'1rtc`.`acctg_2cvsub` (CVNo, Particulars,FromBudgetOf, BranchNo, DebitAccountID, Amount, EncodedByNo, `TimeStamp`)
   Select '.$cvno.', CONCAT("PagIbig-EE - ",\''.$rowsub['From_Budget_Of']. '\') as Particulars, '.$rowsub['FromBudgetOf'].', '.$rowsub['RecordInBranchNo']. ' as BranchNo, 501 as DebitAccountID, '.$rowsub['PagIbig-EE'].' as Amount, \''.$_SESSION['(ak0)'].'\' as EncodedByNo, Now() as TimeStamp
   union all Select '.$cvno.', CONCAT("PagIbig-ER - ",\''.$rowsub['From_Budget_Of']. '\') as Particulars,'.$rowsub['FromBudgetOf'].', '.$rowsub['RecordInBranchNo']. ' as BranchNo, 908 as DebitAccountID, '.$rowsub['PagIbig-ER'].' as Amount, \''.$_SESSION['(ak0)'].'\' as EncodedByNo, Now() as TimeStamp';
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
    }
}
    break;
    
    case 'WTax':
// PAYMENTS TO BIR 

$sqlwtaxmain='SELECT PayrollID, c.Company, g.CompanyNo, bm.AccountID FROM payroll_43wtax g JOIN `banktxns_1maintaining` bm ON bm.RCompanyUse=g.CompanyNo JOIN 1companies c ON c.CompanyNo=g.CompanyNo WHERE PayrollID=' .$payrollid. ' Group By PayrollID, CompanyNo;';
$stmt=$link->query($sqlwtaxmain); $resultwtaxmain=$stmt->fetchAll();
$cvno=lastNum('CVNo','acctg_2cvmain',((date('Y',strtotime($paydate)))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',strtotime($paydate))).',2)');

foreach ($resultwtaxmain as $row){
   $cvno=$cvno+1; $checkno=$checkno+1; //CheckNo='.$checkno.'
   $sqlinsert='INSERT INTO `acctg_2cvmain` SET PayeeNo=309, Payee="Bureau of Internal Revenue", `Posted`=0, CreditAccountID='.$row['AccountID'].', Date=LAST_DAY(\''.date("Y-m-d").'\'), CVNo='.$cvno.', DateofCheck=LAST_DAY(\''.date("Y-m-d").'\'), CheckNo=CONCAT("WTax-",LEFT(MONTHNAME(\''.$paydate.'\'),3),"-'.$row['Company'].'"), Remarks=\''.$row['Company'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	// echo $sqlinsert; break;
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
        
    $sqlwtax='SELECT PayrollID, c.Company, g.CompanyNo, RecordInBranchNo,IF(b.PseudoBranch=1,(800 + d.deptid),b.BranchNo) AS FromBudgetOf, IF(b.PseudoBranch=1,dept,b.Branch) AS From_Budget_Of, ROUND(Sum(`WTax`),2) as `WTax`
    FROM payroll_43wtax g '.$sqlfromgovt1.$row['CompanyNo'].$sqlfromgovt2;
    $stmt=$link->query($sqlwtax); $resultwtax=$stmt->fetchAll();
    foreach ($resultwtax as $rowsub){    
   $sqlinsert='Insert into `'.$payrollprefix.'1rtc`.`acctg_2cvsub` (CVNo, Particulars,FromBudgetOf, BranchNo, DebitAccountID, Amount, EncodedByNo, `TimeStamp`)
   Select '.$cvno.', CONCAT("WTax - ",\''.$rowsub['From_Budget_Of']. '\') as Particulars,'.$rowsub['FromBudgetOf']. ','.$rowsub['RecordInBranchNo']. ' as BranchNo, 505 as DebitAccountID, '.$rowsub['WTax']. ' as Amount, \''.$_SESSION['(ak0)'].'\' as EncodedByNo, Now() as TimeStamp'; //ECHO $sqlinsert;
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
    }
}
    break;
    
    case 'SSS Loans (Salary)':
//SSS LOANS
$sqlsssloanmain='SELECT c.Company, g.CompanyNo, bm.AccountID  FROM payroll_44sssloan g JOIN `banktxns_1maintaining` bm ON bm.RCompanyUse=g.CompanyNo JOIN 1companies c ON c.CompanyNo=g.CompanyNo WHERE PayrollID=' .$payrollid. ' Group By PayrollID, g.CompanyNo;';
$stmtmain=$link->query($sqlsssloanmain); $resultsssloanmain=$stmtmain->fetchAll();
$cvno=lastNum('CVNo','acctg_2cvmain',((date('Y',strtotime($paydate)))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',strtotime($paydate))).',2)');

foreach($resultsssloanmain as $mainrow){
   $cvno=$cvno+1; 
   $sqlinsert='INSERT INTO `acctg_2cvmain` SET PayeeNo=800, Payee="Social Security System", `Posted`=0, CreditAccountID='.$mainrow['AccountID'].', Date=LAST_DAY(\''.date("Y-m-d").'\'), CVNo='.$cvno.', DateofCheck=LAST_DAY(\''.date("Y-m-d").'\'), CheckNo=CONCAT("SSSLoansSalary-",LEFT(MONTHNAME(\''.$paydate.'\'),3),"-'.$mainrow['Company'].'"), Remarks=\''.$mainrow['Company'].'  - Loans\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 	
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
   $sqlsssloan='SELECT g.CompanyNo,concat(FirstName, " ", SurName) as FullName, RecordInBranchNo, IF(b.PseudoBranch=1,(800 + d.deptid),b.BranchNo) AS FromBudgetOf, IF(b.PseudoBranch=1,dept,b.Branch) AS From_Budget_Of, ROUND(Sum(`SSSLoan`),2) as `SSSLoan`  FROM payroll_44sssloan g JOIN `1branches` b ON b.BranchNo=g.RecordInBranchNo  JOIN attend_30latestpositionsinclresigned cp ON g.IDNo=cp.IDNo 
JOIN attend_0positions ps ON cp.PositionID=ps.PositionID JOIN 1departments d ON ps.deptid=d.deptid  where PayrollID=' .$payrollid. ' AND g.CompanyNo='.$mainrow['CompanyNo'].' Group By g.IDNo;';
   $stmt=$link->query($sqlsssloan); $resultsssloan=$stmt->fetchAll();
   
   foreach ($resultsssloan as $row){
   $sqlinsert='Insert into `'.$payrollprefix.'1rtc`.`acctg_2cvsub` (CVNo, Particulars,FromBudgetOf, BranchNo, DebitAccountID, Amount, EncodedByNo, `TimeStamp`)
   Select '.$cvno.', concat("SSSLoan (Salary) - ", "'.$row['FullName']. ' ",\'('.$row['From_Budget_Of']. ')\') as Particulars,'.$row['FromBudgetOf']. ', '.$row['RecordInBranchNo']. ' as BranchNo, 507 as DebitAccountID, "'.$row['SSSLoan'].'" as Amount, \''.$_SESSION['(ak0)'].'\' as EncodedByNo, Now() as TimeStamp';
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
}   
}
// END OF SSS LOANS
    break;
	
	
    case 'SSS Loans (Calamity)':
	
//SSS LOANS
$sqlsssloanmain='SELECT c.Company, g.CompanyNo, bm.AccountID  FROM payroll_44sssloancalamity g JOIN `banktxns_1maintaining` bm ON bm.RCompanyUse=g.CompanyNo JOIN 1companies c ON c.CompanyNo=g.CompanyNo WHERE PayrollID=' .$payrollid. ' Group By PayrollID, g.CompanyNo;';
$stmtmain=$link->query($sqlsssloanmain); $resultsssloanmain=$stmtmain->fetchAll();
$cvno=lastNum('CVNo','acctg_2cvmain',((date('Y',strtotime($paydate)))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',strtotime($paydate))).',2)');

foreach($resultsssloanmain as $mainrow){
   $cvno=$cvno+1; 
   $sqlinsert='INSERT INTO `acctg_2cvmain` SET PayeeNo=800, Payee="Social Security System", `Posted`=0, CreditAccountID='.$mainrow['AccountID'].', Date=LAST_DAY(\''.date("Y-m-d").'\'), CVNo='.$cvno.', DateofCheck=LAST_DAY(\''.date("Y-m-d").'\'), CheckNo=CONCAT("SSSLoansCalamity-",LEFT(MONTHNAME(\''.$paydate.'\'),3),"-'.$mainrow['Company'].'"), Remarks=\''.$mainrow['Company'].'  - Loans\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 	
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
   $sqlsssloan='SELECT g.CompanyNo,concat(FirstName, " ", SurName) as FullName, RecordInBranchNo, IF(b.PseudoBranch=1,(800 + d.deptid),b.BranchNo) AS FromBudgetOf, IF(b.PseudoBranch=1,dept,b.Branch) AS From_Budget_Of, ROUND(Sum(`SSSLoan`),2) as `SSSLoan`  FROM payroll_44sssloancalamity g JOIN `1branches` b ON b.BranchNo=g.RecordInBranchNo  JOIN attend_30latestpositionsinclresigned cp ON g.IDNo=cp.IDNo JOIN attend_0positions ps ON cp.PositionID=ps.PositionID JOIN 1departments d ON ps.deptid=d.deptid  where PayrollID=' .$payrollid. ' AND g.CompanyNo='.$mainrow['CompanyNo'].' Group By g.IDNo;';
   $stmt=$link->query($sqlsssloan); $resultsssloan=$stmt->fetchAll();
   
   foreach ($resultsssloan as $row){
   $sqlinsert='Insert into `'.$payrollprefix.'1rtc`.`acctg_2cvsub` (CVNo, Particulars,FromBudgetOf, BranchNo, DebitAccountID, Amount, EncodedByNo, `TimeStamp`)
   Select '.$cvno.', concat("SSSLoan (Calamity) - ", "'.$row['FullName']. ' ",\'('.$row['From_Budget_Of']. ')\') as Particulars,'.$row['FromBudgetOf']. ', '.$row['RecordInBranchNo']. ' as BranchNo, 5071 as DebitAccountID, "'.$row['SSSLoan'].'" as Amount, \''.$_SESSION['(ak0)'].'\' as EncodedByNo, Now() as TimeStamp';
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
}   
}
// END OF SSS LOANS
    break;
    
    case 'Pag-Ibig Loans (Salary)':

// PAGIBIG LOANS 

$sqlpagibigloanmain='SELECT c.Company, g.CompanyNo, bm.AccountID FROM payroll_45pagibigloan g JOIN `banktxns_1maintaining` bm ON bm.RCompanyUse=g.CompanyNo JOIN 1companies c ON c.CompanyNo=g.CompanyNo where PayrollID=' .$payrollid. ' Group By PayrollID, CompanyNo;';
$stmtmain=$link->query($sqlpagibigloanmain);$resultpagibigloanmain=$stmtmain->fetchAll();
$cvno=lastNum('CVNo','acctg_2cvmain',((date('Y',strtotime($paydate)))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',strtotime($paydate))).',2)');

foreach ($resultpagibigloanmain as $mainrow){
   $cvno=$cvno+1; 
   $sqlinsert='INSERT INTO `acctg_2cvmain` SET PayeeNo=802, Payee="Pag-Ibig Fund", `Posted`=0, CreditAccountID='.$mainrow['AccountID'].', Date=LAST_DAY(\''.date("Y-m-d").'\'), CVNo='.$cvno.', DateofCheck=LAST_DAY(\''.date("Y-m-d").'\'), CheckNo=CONCAT("PagIbigLoansSalary-",LEFT(MONTHNAME(\''.$paydate.'\'),3),"-'.$mainrow['Company'].'"), Remarks=\''.$mainrow['Company'].'  - Loans\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	// echo $sqlinsert; break;
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
$sqlpagibigloan='SELECT g.CompanyNo,concat(FirstName, " ", SurName) as FullName, RecordInBranchNo, IF(b.PseudoBranch=1,(800 + d.deptid),b.BranchNo) AS FromBudgetOf, IF(b.PseudoBranch=1,dept,b.Branch) AS From_Budget_Of, ROUND(Sum(`PagibigLoan`),2) as `PagibigLoan`  FROM payroll_45pagibigloan g JOIN `1branches` b ON b.BranchNo=g.RecordInBranchNo  JOIN attend_30latestpositionsinclresigned cp ON g.IDNo=cp.IDNo 
JOIN attend_0positions ps ON cp.PositionID=ps.PositionID JOIN 1departments d ON ps.deptid=d.deptid where PayrollID=' .$payrollid. ' AND g.CompanyNo='.$mainrow['CompanyNo'].' Group By g.IDNo;';
   $stmt=$link->query($sqlpagibigloan); $resultpagibigloan=$stmt->fetchAll();

foreach ($resultpagibigloan as $row){
   
   $sqlinsert='Insert into `'.$payrollprefix.'1rtc`.`acctg_2cvsub` (CVNo, Particulars,FromBudgetOf, BranchNo, DebitAccountID, Amount, EncodedByNo, `TimeStamp`)
   Select '.$cvno.', concat("PagibigLoan (Salary) - ", "'.$row['FullName']. ' ","('.$row['From_Budget_Of']. ')") as Particulars,'.$row['FromBudgetOf']. ', '.$row['RecordInBranchNo']. ' as BranchNo, 508 as DebitAccountID, "'.$row['PagibigLoan'].'" as Amount, \''.$_SESSION['(ak0)'].'\' as EncodedByNo, Now() as TimeStamp'; 
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
}
}
    break;   
	
	case 'Pag-Ibig Loans (Calamity)':

// PAGIBIG LOANS 

$sqlpagibigloanmain='SELECT c.Company, g.CompanyNo, bm.AccountID FROM payroll_45pagibigloancalamity g JOIN `banktxns_1maintaining` bm ON bm.RCompanyUse=g.CompanyNo JOIN 1companies c ON c.CompanyNo=g.CompanyNo where PayrollID=' .$payrollid. ' Group By PayrollID, CompanyNo;';
$stmtmain=$link->query($sqlpagibigloanmain);$resultpagibigloanmain=$stmtmain->fetchAll();
$cvno=lastNum('CVNo','acctg_2cvmain',((date('Y',strtotime($paydate)))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',strtotime($paydate))).',2)');

foreach ($resultpagibigloanmain as $mainrow){
   $cvno=$cvno+1; 
   $sqlinsert='INSERT INTO `acctg_2cvmain` SET PayeeNo=802, Payee="Pag-Ibig Fund", `Posted`=0, CreditAccountID='.$mainrow['AccountID'].', Date=LAST_DAY(\''.date("Y-m-d").'\'), CVNo='.$cvno.', DateofCheck=LAST_DAY(\''.date("Y-m-d").'\'), CheckNo=CONCAT("PagIbigLoansCalamity-",LEFT(MONTHNAME(\''.$paydate.'\'),3),"-'.$mainrow['Company'].'"), Remarks=\''.$mainrow['Company'].'  - Loans\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	// echo $sqlinsert; break;
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
$sqlpagibigloan='SELECT g.CompanyNo,concat(FirstName, " ", SurName) as FullName, RecordInBranchNo, IF(b.PseudoBranch=1,(800 + d.deptid),b.BranchNo) AS FromBudgetOf, IF(b.PseudoBranch=1,dept,b.Branch) AS From_Budget_Of, ROUND(Sum(`PagibigLoan`),2) as `PagibigLoan`  FROM payroll_45pagibigloancalamity g JOIN `1branches` b ON b.BranchNo=g.RecordInBranchNo  JOIN attend_30latestpositionsinclresigned cp ON g.IDNo=cp.IDNo 
JOIN attend_0positions ps ON cp.PositionID=ps.PositionID JOIN 1departments d ON ps.deptid=d.deptid where PayrollID=' .$payrollid. ' AND g.CompanyNo='.$mainrow['CompanyNo'].' Group By g.IDNo;';
   $stmt=$link->query($sqlpagibigloan); $resultpagibigloan=$stmt->fetchAll();

foreach ($resultpagibigloan as $row){
   
   $sqlinsert='Insert into `'.$payrollprefix.'1rtc`.`acctg_2cvsub` (CVNo, Particulars,FromBudgetOf, BranchNo, DebitAccountID, Amount, EncodedByNo, `TimeStamp`)
   Select '.$cvno.', concat("PagibigLoan (Calamity) - ", "'.$row['FullName']. ' ","('.$row['From_Budget_Of']. ')") as Particulars,'.$row['FromBudgetOf']. ', '.$row['RecordInBranchNo']. ' as BranchNo, 508 as DebitAccountID, "'.$row['PagibigLoan'].'" as Amount, \''.$_SESSION['(ak0)'].'\' as EncodedByNo, Now() as TimeStamp'; 
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
}
}
    break;   
	
	
	
}
// header("Location:../acctg/txnsperday.php?perday=0&w=Voucher");   
echo $_POST['submit'].' vouchers encoded.'; exit();
}
end:
      $link=null; $stmt=null;
?>
</body></html>