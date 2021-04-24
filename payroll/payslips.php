<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(824,'1rtc')) { echo 'No permission'; exit;}
$showbranches=false;
include_once('../switchboard/contents.php');


?>
<html>
<head>
<title>Payslips</title>
<?php
if (isset($_REQUEST['MyPayslip'])){
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
    $payrollid=(isset($_SESSION['payrollidses'])?$_SESSION['payrollidses']:((date('m')*2)+(date('d')<15?-1:0)));
?>
<form method="POST" action="payslips.php" enctype="multipart/form-data">
        For Payroll ID<input type='text' name="payrollid" list='payperiods' required=true autocomplete=off value='<?php echo $payrollid?>'></input>
        <input type='submit' name='submit' value='Lookup'> <a href="payslipsfortheyear.php">Payslips of employee for the year</a>
<?php include_once "../generalinfo/lists.inc"; renderlist('payperiods'); ?>
</form>
<?php
	goto noform;
    } else {
      if (!allowedToOpen(824,'1rtc')) {
   $eachorall=' AND p.IDNo='.$_SESSION['(ak0)'];
} else {
   $eachorall=(isset($_REQUEST['MyPayslip']))? ' AND d.Posted=1 AND (datediff(CURDATE(),d.PayrollDate)>=0) AND p.IDNo='.$_SESSION['(ak0)']: '';
   
}

$paysliptype='all';
include_once 'tempdata/payslipdata.php';


$sql2='CREATE TEMPORARY TABLE `payslipssub` (
  `PayrollID` tinyint(3) unsigned NOT NULL,
  `IDNo` smallint(6) NOT NULL,
  `AdjustTypeNo` tinyint(3) NOT NULL,
  `AdjustAmt` double DEFAULT 0,
  `AdjustType` varchar(50) NOT NULL,
  `AdjID` int(11) NOT NULL ,
  PRIMARY KEY (`AdjID`),
  UNIQUE KEY `PayrollIDAdj_UNIQUE` (`PayrollID`,`IDNo`,`AdjustTypeNo`))
SELECT a.*, i.AdjustType FROM `payroll_21paydayadjustments` as a join `payroll_0acctid` as i on a.AdjustTypeNo=i.AdjustTypeNo where a.PayrollID=' . $_POST['payrollid'];
$stmt2=$link->prepare($sql2);
$stmt2->execute();

$sqlmain='Select * from `payslips` '.((!allowedToOpen(8241,'1rtc'))?' WHERE IDNo>1002':'').' order by Branch';
$stmtmain=$link->query($sqlmain);
$resultmain=$stmtmain->fetchAll();
if (allowedToOpen(824,'1rtc') and !isset($_REQUEST['MyPayslip'])) {
?>
<FORM>
<INPUT TYPE="button" onClick="window.print()" value="Print!">
</FORM>
 <?php
}
 $columns=array('Basic','DeM','TaxSh','OT','Absence','Undertime');
 $columnsgovt=array('SSS-EE','PhilHealth-EE','PagIbig-EE','WTax');
 foreach ($resultmain as $rowmain){
   include 'tempdata/payslipchargesdata.php';
$payslip='<table  width="50%" border="0"><tr><td colspan="3">'.htmlspecialchars($rowmain['Company']).'&nbsp &nbsp &nbsp From '.$rowmain['FromDate'].'&nbsp To '.$rowmain['ToDate'].'</td></tr>
        <tr><td colspan="2">'.$rowmain['IDNo'].': '.htmlspecialchars($rowmain['FullName']).'</td><td align="right">'.$rowmain['Branch'].'</td></tr>';
    $payslipgross='<tr><td>';
        foreach ($columns as $column){
            $payslipgross=$payslipgross.($rowmain[$column]==0?'':$column.':  '.$rowmain[$column].'<br>');
        }
        $payslipgross=$payslipgross.'</td>';
     $payslipgovt='<td>';
        foreach ($columnsgovt as $column){
            $payslipgovt=$payslipgovt.($rowmain[$column]==0?'':$column.':  '.$rowmain[$column].'<br>');
        }   

        $sqlsub='Select IDNo, AdjustAmt, AdjustType from `payslipssub` where IDNo='.$rowmain['IDNo'];
        $stmtsub=$link->query($sqlsub);
        $resultsub=$stmtsub->fetchAll();
        $payslipadj='<td>';
        $adjamt=0;
         foreach ($resultsub as $rowsub){
            $payslipadj=$payslipadj.($rowsub['AdjustAmt']==0?'':$rowsub['AdjustType'].':  '.$rowsub['AdjustAmt'].'<br>');
            $adjamt=$adjamt+$rowsub['AdjustAmt'];
         }
        $payslipgross=$payslipgross.$payslipgovt.'</td>'.$payslipadj.'</td></tr><tr><td>Subtotal:  '.number_format($rowmain['GrossPay'],2).'</td><td>Govt Deductions:  '.number_format($rowmain['GovtDeduct'],2).'</td><td>Adjustments: '.number_format($adjamt,2).'</tr>';
        //$payslipgovt=$payslipgovt.'<tr><td>Deductions:  '.$rowmain['GrossPay'].'</td></tr></table>';
        $payslip=$payslip.$payslipgross.'<tr><td colspan="2">'.
        (is_null($rowmain['Remarks'])?'':'Remarks: '.htmlspecialchars($rowmain['Remarks']).'&nbsp &nbsp')
        .
        '</td><td>NetPay:  '. number_format($rowmain['NetPay'],2) .'</td></tr></table>';
 echo $payslip.'<br>';
 
	echo $charges;
	echo '<hr><br>';
 
 
 }
}
noform:    
     $link=null; $stmt=null;
    ?>
</html>

