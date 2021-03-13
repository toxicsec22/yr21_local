<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(824,'1rtc')) { echo 'No permission'; exit;}
$showbranches=false;
echo '<div class="nodisplay">';
include_once('../switchboard/contents.php');
echo '</div>';
?>
<html>
<head>
<title>Payslips</title>
<style>@media print {
			 .nodisplay {
					display: none;    
		}
		}</style>
<?php
if (isset($_REQUEST['MyPayslip'])){
   include_once('../backendphp/layout/autoclose.php');
   if ((time() - $_SESSION['LAST_ACTIVITY'] > 30)){ // 30 seconds viewing
      include('../../logout.php');
   }
   }
   include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once("../backendphp/layout/regulartablestyle.php");
?>
</head>
<body>
<?php
if (!isset($_POST['IDNo'])){
    
	$sqlemp='SELECT IDNo,FullName FROM attend_30currentpositions ORDER BY FullName';
	echo comboBox($link,$sqlemp,'FullName','IDNo','employees');
	
?>
<h3>Payroll Summary for the Year</h3><br>
<form method="POST" action="payslipsfortheyear.php" enctype="multipart/form-data">
        Employee <input type='text' name="IDNo" list='employees' required=true autocomplete=off value='' /> 
		PayrollID From <input type="number" name="PayrollIDFrom" style="width:70px;" list='payperiods' value="<?php echo (isset($_POST['PayrollIDFrom'])?$_POST['PayrollIDFrom']:'1');?>">
		PayrollID To <input type="number" name="PayrollIDTo" style="width:70px;" list='payperiods' value="<?php echo (isset($_POST['PayrollIDTo'])?$_POST['PayrollIDTo']:'24');?>">
        <input type='submit' name='submit' value='Lookup'>
		<?php include_once "../generalinfo/lists.inc"; renderlist('payperiods'); ?>
</form>
<?php
	goto noform;
    } else {
    

	
  // `Cola` double DEFAULT 0, after basic
$sql1='CREATE TEMPORARY TABLE `payslips` (
  `PayrollID` tinyint(3) unsigned NOT NULL,
  `IDNo` smallint(6) NOT NULL,
  `BranchNo` smallint(3) NOT NULL,
  `DorSM` tinyint(1) NOT NULL,
  `Basic` double DEFAULT 0,
  `DeM` double DEFAULT 0,
  `TaxSh` double DEFAULT 0,
  `OT` double DEFAULT 0,
  `Remarks` varchar(50) DEFAULT NULL,
  `AbsenceBasic` double DEFAULT 0,
  `UndertimeBasic` double DEFAULT 0,
  `AbsenceTaxSh` double DEFAULT 0,
  `UndertimeTaxSh` double DEFAULT 0,
  `SSS` double DEFAULT 0,
  `PhilHealth` double DEFAULT 0,
  `PagIbig` double DEFAULT 0,
  `WTax` double DEFAULT 0,
  `SILBAL` double DEFAULT 0,
  `FullName` varchar(50) NOT NULL,
  `Company` varchar(100)  NULL,
  `FromDate` date NOT NULL,
  `ToDate` date NOT NULL,
  `Branch` varchar(20) NOT NULL,
  `GrossPay` double DEFAULT 0,
  `GovtDeduct` double DEFAULT 0,
  `Absence` double DEFAULT 0,
  `Undertime` double DEFAULT 0,
  `NetPay` double DEFAULT 0,
  `TxnID` int(11) NOT NULL ,
  PRIMARY KEY (`TxnID`),
  UNIQUE KEY `PayrollID_UNIQUE` (`PayrollID`,`IDNo`))
    SELECT p.*, c.`CompanyName` as `Company`,d.FromDate, d.ToDate, b.Branch, (`Basic`+`DeM`+`TaxSh`+`OT`-`AbsenceBasic`-`UndertimeBasic`-`AbsenceTaxSh`-`UndertimeTaxSh`) AS GrossPay,
    (`SSS-EE`+`PhilHealth-EE`+`PagIbig-EE`+`WTax`) as GovtDeduct,
    (`AbsenceBasic`+`AbsenceTaxSh`) as `Absence`,
    (`UndertimeBasic`+`UndertimeTaxSh`) as `Undertime` FROM `payroll_25payrolldatalookup` as p 
join `1employees` as e on p.IDNo=e.IDNo 
left join `1companies` as c on e.RCompanyNo=c.CompanyNo
join `1branches` as b on p.BranchNo=b.BranchNo
join `payroll_1paydates` as d on p.PayrollID=d.PayrollID
where p.PayrollID BETWEEN '.$_POST['PayrollIDFrom'].' AND '.$_POST['PayrollIDTo'].' AND p.IDNo=' . $_POST['IDNo'];


$stmt1=$link->prepare($sql1);
$stmt1->execute(); 

$sql2='CREATE TEMPORARY TABLE `payslipssub` (
  `PayrollID` tinyint(3) unsigned NOT NULL,
  `IDNo` smallint(6) NOT NULL,
  `AdjustTypeNo` tinyint(3) NOT NULL,
  `AdjustAmt` double DEFAULT 0,
  `AdjustType` varchar(50) NOT NULL,
  `AdjID` int(11) NOT NULL ,
  PRIMARY KEY (`AdjID`),
  UNIQUE KEY `PayrollIDAdj_UNIQUE` (`PayrollID`,`IDNo`,`AdjustTypeNo`))
SELECT a.*, i.AdjustType FROM `payroll_21paydayadjustments` as a join `payroll_0acctid` as i on a.AdjustTypeNo=i.AdjustTypeNo where a.IDNo=' . $_POST['IDNo'];
$stmt2=$link->prepare($sql2);
$stmt2->execute();

$sqlmain='Select * from `payslips` '.((!allowedToOpen(8241,'1rtc'))?' WHERE IDNo>1002':'').' order by PayrollID';
$stmtmain=$link->query($sqlmain);
$resultmain=$stmtmain->fetchAll();
if (allowedToOpen(824,'1rtc') and !isset($_REQUEST['MyPayslip'])) {
?>
<div class="nodisplay">
<br>
<INPUT TYPE="button" onClick="window.print()" value="Print!">
</div>

<div align="center">
 <?php
 
 $sqlw='Select CONCAT(Firstname," ",Surname) AS FullName,CompanyName,Company from `1employees` e LEFT JOIN 1companies c ON e.RCompanyNo=c.CompanyNo WHERE IDNo='.$_POST['IDNo'];
$stmtw=$link->query($sqlw);
$resultw=$stmtw->fetch();
	echo '<center><img src="../generalinfo/logo/'.$resultw['Company'].'.png"></center>';
	echo '<h3 align="left">'.$resultw['FullName'].'</h3><br>';
 
 
}

 $columns=array('Basic','DeM','TaxSh','OT','Absence','Undertime');
 $columnsgovt=array('SSS-EE','PhilHealth-EE','PagIbig-EE','WTax');
 foreach ($resultmain as $rowmain){
$payslip='<table  width="70%" border="0"><tr><td colspan="3">PayrollID: '.$rowmain['PayrollID'].'&nbsp &nbsp &nbsp From '.$rowmain['FromDate'].'&nbsp To '.$rowmain['ToDate'].'</td></tr>
        ';
    $payslipgross='<tr><td>';
        foreach ($columns as $column){
            $payslipgross=$payslipgross.($rowmain[$column]==0?'':$column.':  '.$rowmain[$column].'<br>');
        }
        $payslipgross=$payslipgross.'</td>';
     $payslipgovt='<td>';
        foreach ($columnsgovt as $column){
            $payslipgovt=$payslipgovt.($rowmain[$column]==0?'':$column.':  '.$rowmain[$column].'<br>');
        }   

        $sqlsub='Select IDNo, AdjustAmt, AdjustType from `payslipssub` where PayrollID='.$rowmain['PayrollID'].' AND IDNo='.$rowmain['IDNo'];
        $stmtsub=$link->query($sqlsub);
        $resultsub=$stmtsub->fetchAll();
        $payslipadj='<td>';
        $adjamt=0;
         foreach ($resultsub as $rowsub){
            $payslipadj=$payslipadj.($rowsub['AdjustAmt']==0?'':$rowsub['AdjustType'].':  '.$rowsub['AdjustAmt'].'<br>');
            $adjamt=$adjamt+$rowsub['AdjustAmt'];
         }
        $payslipgross=$payslipgross.$payslipgovt.'</td>'.$payslipadj.'</td></tr><tr><td>Subtotal:  '.number_format($rowmain['GrossPay'],2).'</td><td>Govt Deductions:  '.number_format($rowmain['GovtDeduct'],2).'</td><td>Adjustments: '.number_format($adjamt,2).'</tr>';
        $payslip=$payslip.$payslipgross.'<tr><td colspan="2">'.
        (is_null($rowmain['Remarks'])?'':'Remarks: '.htmlspecialchars($rowmain['Remarks']).'&nbsp &nbsp').
        (($rowmain['SILBAL']==0)?'':'SIL Balance (unused as of payroll CUT-OFF): '.$rowmain['SILBAL']).
        '</td><td>NetPay:  '. number_format($rowmain['NetPay'],2) .'</td></tr></table>';
 echo $payslip.'<br>';
 
	 $sql='SELECT ForChargeInvNo,Amount,Branch from acctg_2depositmain dm JOIN acctg_2depositsub ds ON dm.TxnID=ds.TxnID JOIN 1branches b ON ds.BranchNo=b.BranchNo WHERE DepositNo LIKE "%InvtyCharges-Payroll-'.$rowmain['PayrollID'].'-%" AND ClientNo='.$rowmain['IDNo'].' AND Posted=1 ORDER BY Branch';
		
		// echo $sql;
		$stmt=$link->query($sql); $rows=$stmt->fetchAll();

		if($stmt->rowCount()>0){
			echo 'Summary of Charges<br><table>';
			echo '<tr><td>Branch</td><td>ForChargeInvNo</td><td>Amount</td></tr>';
			foreach($rows AS $row){
				echo '<tr><td>'.$row['Branch'].'</td><td>'.$row['ForChargeInvNo'].'</td><td>'.number_format($row['Amount'],2).'</td></tr>';
			}
			echo '</table><br>';
			
		}
	echo '<hr><br>';
 
 
 }
 echo '</div>';
 
 
 $sqla='SELECT CONCAT(e.FirstName," ",LEFT(MiddleName,1),". ",Surname) AS Approver,`Position` FROM attend_30currentpositions cp JOIN 1employees e ON cp.IDNo=e.IDNo WHERE cp.PositionID =(SELECT AllowedPos FROM permissions_2allprocesses WHERE ProcessID=8242);';
 $stmta=$link->query($sqla); $rowa=$stmta->fetch();
 
 echo '<br><br>Approved by:<br><br><br><br>'.strtoupper($rowa['Approver']).'<br>'.$rowa['Position'];
}
noform:    
     $link=null; $stmt=null;
    ?>
</html>

