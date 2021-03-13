<?php
if(session_id()==''){
	session_start();
} 
if(!isset($_SESSION['&pos'])){ $_SESSION['&pos']='-1';} //echo $_SESSION['&pos'].' '.$_SESSION['(ak0)'];
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 
$showbranches=false; include_once('../switchboard/contents.php');

 
?>
<html>
<head>
<title>My Payslip</title>
<?php
if (isset($_REQUEST['submit'])){
   include_once('../backendphp/layout/autoclose.php');
   if ((time() - $_SESSION['LAST_ACTIVITY'] > 30)){ // 30 seconds viewing
      include('../../logout.php');
   }
   }

include_once("../backendphp/layout/regulartablestyle.php");
?>
</head>
<body><br>
<?php
if (!isset($_POST['payrollid'])){
?>
<form method="POST" action="mypayslip.php" enctype="multipart/form-data">
        For Payroll ID<input type='text' name='payrollid' list='payperiods' required=true autocomplete=off></input>
        <input type='submit' name='submit' value='Lookup'>
<?php include_once "../generalinfo/lists.inc"; renderlist('payperiods'); ?>
</form>
<?php
	goto noform;
    } 

$sqlmain='SELECT p.*, c.`CompanyName` as `Company`,d.FromDate, d.ToDate, b.Branch, (`Basic`+`DeM`+`TaxSh`+`OT`-`AbsenceBasic`-`UndertimeBasic`-`AbsenceTaxSh`-`UndertimeTaxSh`) AS GrossPay,
    (`SSS-EE`+`PhilHealth-EE`+`PagIbig-EE`+`WTax`) as GovtDeduct,
    (`AbsenceBasic`+`AbsenceTaxSh`) as `Absence`,
    (`UndertimeBasic`+`UndertimeTaxSh`) as `Undertime` FROM `payroll_25payrolldatalookup` as p 
join `1employees` as e on p.IDNo=e.IDNo 
left join `1companies` as c on e.RCompanyNo=c.CompanyNo
join `1branches` as b on p.BranchNo=b.BranchNo
join `payroll_1paydates` as d on p.PayrollID=d.PayrollID
where p.PayrollID=' . $_POST['payrollid'] .' AND d.Posted=1 AND (datediff(CURDATE(),d.PayrollDate)>=0) AND p.IDNo='.$_SESSION['(ak0)'];
$stmtmain=$link->query($sqlmain);$rowmain=$stmtmain->fetch();

$sqlsub='SELECT a.*, i.AdjustType FROM `payroll_21paydayadjustments` as a join `payroll_0acctid` as i on a.AdjustTypeNo=i.AdjustTypeNo join `payroll_1paydates` as d on a.PayrollID=d.PayrollID '
        . '  AND (datediff(CURDATE(),d.PayrollDate)>=0) WHERE a.PayrollID=' . $_POST['payrollid'].'  AND a.IDNo='.$_SESSION['(ak0)'];
$stmtsub=$link->query($sqlsub); $resultsub=$stmtsub->fetchAll();


 $columns=array('Basic','DeM','TaxSh','OT','Absence','Undertime');
 $columnsgovt=array('SSS-EE','PhilHealth-EE','PagIbig-EE','WTax');
 
$payslip='<table  width="50%" border="0"><tr><td colspan="3">'.$rowmain['Company'].'&nbsp &nbsp &nbsp From '.$rowmain['FromDate'].'&nbsp To '.$rowmain['ToDate'].'</td></tr>
        <tr><td colspan="2">'.$rowmain['IDNo'].': '.$rowmain['FullName'].'</td><td align="right">'.$rowmain['Branch'].'</td></tr>';
    $payslipgross='<tr><td>';
        foreach ($columns as $column){
            $payslipgross=$payslipgross.($rowmain[$column]==0?'':$column.':  '.$rowmain[$column].'<br>');
        }
        $payslipgross=$payslipgross.'</td>';
     $payslipgovt='<td>';
        foreach ($columnsgovt as $column){
            $payslipgovt=$payslipgovt.($rowmain[$column]==0?'':$column.':  '.$rowmain[$column].'<br>');
        }   

        
        
        
        $payslipadj='<td>';
        $adjamt=0;
    
    if ($stmtsub->rowCount()>0){
         foreach ($resultsub as $rowsub){
            $payslipadj=$payslipadj.($rowsub['AdjustAmt']==0?'':$rowsub['AdjustType'].':  '.$rowsub['AdjustAmt'].'<br>');
            $adjamt=$adjamt+$rowsub['AdjustAmt'];
         }
    }
        $payslipgross=$payslipgross.$payslipgovt.'</td>'.$payslipadj.'</td></tr><tr><td>Subtotal:  '.number_format($rowmain['GrossPay'],2).'</td><td>Govt Deductions:  '.number_format($rowmain['GovtDeduct'],2).'</td><td>Adjustments: '.number_format($adjamt,2).'</tr>';
        //$payslipgovt=$payslipgovt.'<tr><td>Deductions:  '.$rowmain['GrossPay'].'</td></tr></table>';
        $payslip=$payslip.$payslipgross.'<tr><td colspan="2">'.
        (is_null($rowmain['Remarks'])?'':'Remarks: '.htmlspecialchars($rowmain['Remarks']).'&nbsp &nbsp').
        '</td><td>NetPay:  '. number_format($rowmain['NetPay'],2) .'</td></tr></table>';
		
		
	echo '<br><form action="summaryofcharges.php" method="POST"><input type="submit" value="Summary of Charges for the Year" name="btnLookup"></form><br>';
	
 echo $payslip;
 
	$sql='SELECT ForChargeInvNo,Amount,Branch from acctg_2depositmain dm JOIN acctg_2depositsub ds ON dm.TxnID=ds.TxnID JOIN 1branches b ON ds.BranchNo=b.BranchNo WHERE DepositNo LIKE "%InvtyCharges-Payroll-'.$_POST['payrollid'].'-%" AND ClientNo='.$_SESSION['(ak0)'].' AND Posted=1 ORDER BY Branch';
	
	// echo $sql;
	$stmt=$link->query($sql); $rows=$stmt->fetchAll();

	if($stmt->rowCount()>0){
		echo '<br>Summary of Charges<br><table>';
		echo '<tr><td>Branch</td><td>ForChargeInvNo</td><td>Amount</td></tr>';
		foreach($rows AS $row){
			echo '<tr><td>'.$row['Branch'].'</td><td>'.$row['ForChargeInvNo'].'</td><td>'.number_format($row['Amount'],2).'</td></tr>';
		}
		echo '</table>';
		
	}
	
 
 echo '<br>Attendance Summary<br><br>';
 
    $sql='SELECT * FROM `payroll_20fromattendance` WHERE IDNo='.$_SESSION['(ak0)'].' AND PayrollID='.$_POST['payrollid'];
    $stmt=$link->query($sql);$row=$stmt->fetch();
    $columnnames=array('RegDaysActual','LWOPDays','PaidLegalDays','SpecDays','SLDays','VLDays','RestDays','LWPDays','QDays','LegalHrsOT','SpecHrsOT','RestHrsOT','RegOTHrs');
    $attend='';
    foreach($columnnames as $col){
        $attend.=($row[$col]<>0?'<tr><td>'.$col.'</td><td>'.number_format($row[$col],2).'</td></tr>':'');
    }
 
    echo '<table>'.$attend.'</table>';
	
	
noform:    
     $link=null; $stmt=null;
    ?>
</body>
</html>

