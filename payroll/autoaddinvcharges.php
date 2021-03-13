<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;
include_once('../switchboard/contents.php');
 
if (!allowedToOpen(793,'1rtc')) {   echo 'No permission'; exit;}
 

switch ($_REQUEST['p']){
    
case 'deduct':
if (!allowedToOpen(7931,'1rtc')) {   echo 'No permission'; exit;}
$title='Auto Add Invty Charges';
  //  include('payrolllayout/addentryhead.php');
    
include_once '../generalinfo/lists.inc';
renderlist('payperiods');
?>
<html>
<head>
<title><?php echo $title; ?></title>
<div style='margin-left: 40%;'>
<h3><?php echo $title; ?></h3>
<BR>
<form style="display:inline; text-align: center" method='post' action='autoaddinvcharges.php?p=deduct' enctype='multipart/form-data'>
    Process Payroll ID<input type='text' name='payperiod' list='payperiods' autocomplete='off' size=3 >
    <input type='submit' name='submit' value='Add'></form> <br><br>
    <p style="color: blue;">Only UNPOSTED payroll will be affected.</p></div>
<?php    
if (!isset($_POST['submit'])){ goto end;   } else {$payrollid=$_POST['payperiod'];}

    /* $sqlinsert='INSERT INTO `payroll_21paydayadjustments` (`PayrollID`,`IDNo`,`AdjustTypeNo`,`AdjustAmt`,`EncodedByNo`,`TimeStamp`,`BranchNo`)
SELECT '.$payrollid.', `ClientNo` AS `IDNo`, 10, IF(SUM(`InvBalance`)>NetPay*.3,NetPay*.3,SUM(`InvBalance`))*-1 AS `AdjustAmt`,'.$_SESSION['(ak0)'].',Now(),bal.`BranchNo`
FROM `acctg_33qrybalperrecpt` bal JOIN `1employees` e ON e.IDNo=bal.`ClientNo` '
            . ' JOIN `payroll_25payroll` p ON p.`PayrollID`='.$payrollid.' AND p.IDNo=bal.ClientNo '
            . ' JOIN payroll_1paydates pd ON pd.PayrollID=p.PayrollID AND pd.Posted=0 JOIN payroll_25payrolldatalookup pdl ON e.IDNo=pdl.IDNo '
            . ' WHERE `InvBalance`>0.05 AND `Resigned`=0 AND `DirectOrAgency`=0 GROUP BY `ClientNo`;'; */
    $sqlinsert='INSERT INTO `payroll_21paydayadjustments` (`PayrollID`,`IDNo`,`AdjustTypeNo`,`AdjustAmt`,`EncodedByNo`,`TimeStamp`,`BranchNo`)
SELECT '.$payrollid.', `ClientNo` AS `IDNo`, 10, IF(NetPay>0,IF(SUM(`InvBalance`)>NetPay*.3,NetPay*.3,SUM(`InvBalance`))*-1,0) AS `AdjustAmt`,'.$_SESSION['(ak0)'].',Now(),bal.`BranchNo`
FROM `acctg_33qrybalperrecpt` bal JOIN `1employees` e ON e.IDNo=bal.`ClientNo` 
             JOIN payroll_25payrolldatalookup pdl ON e.IDNo=pdl.IDNo 
             and pdl.IDNo=bal.ClientNo
            JOIN payroll_1paydates pd ON pd.PayrollID=pdl.PayrollID AND pd.Posted=0  WHERE `InvBalance`>0.05 AND `Resigned`=0 AND `DirectOrAgency`=0 AND pdl.PayrollID='.$payrollid.' GROUP BY `ClientNo`';
			// echo $sqlinsert; exit();
    if($_SESSION['(ak0)']==1002){ echo $sqlinsert;}    
    $stmt=$link->prepare($sqlinsert); $stmt->execute();
      
    header("Location:lookupwithedit.php?w=AdjPerPayID&edit=0");
    break;

case 'dep':
    if (!allowedToOpen(7932,'1rtc')) {   echo 'No permission'; exit;}
    // record invty discrepancy payments
    $payrollid=$_POST['payrollid'];
    $sql0='CREATE TEMPORARY TABLE deducted AS SELECT ClientNo, Particulars, InvBalance, DebitAccountID, bal.BranchNo, b.CompanyNo FROM acctg_33qrybalperrecpt bal JOIN `1employees` e on bal.ClientNo=e.IDNo JOIN `payroll_25payroll` p on p.IDNo=bal.ClientNo JOIN `1branches` b ON b.BranchNo=bal.BranchNo WHERE p.PayrollID='.$payrollid.' AND Resigned=0 AND InvBalance>0.1';
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    
    $sql0='SELECT CompanyNo FROM deducted GROUP BY CompanyNo'; $stmt0=$link->query($sql0); $resultco=$stmt0->fetchAll(); 
    if ($stmt0->rowCount()==0){ echo 'There is no unpaid inventory. Please recheck deductions.'; goto end;}
    
    foreach($resultco AS $co){
        $sqlinvtycharge='INSERT INTO `acctg_2depositmain` (`DepositNo`,`Date`,`DebitAccountID`,`TimeStamp`,`EncodedByNo`,`PostedByNo`,`Posted`,`ClearedByNo`,`ClearedTS`) SELECT "InvtyCharges-Payroll-'.$payrollid.'-'.$co['CompanyNo'].'",(SELECT PayrollDate FROM ' . $currentyr . '_1rtc.`payroll_1paydates` where PayrollID='.$payrollid.'), 205,Now(), \''.$_SESSION['(ak0)'].'\', \''.$_SESSION['(ak0)'].'\',0, \''.$_SESSION['(ak0)'].'\', Now()'; //echo $sqlinvtycharge;
    $stmt=$link->prepare($sqlinvtycharge);  $stmt->execute();
    
    $sqldep='SELECT TxnID FROM `acctg_2depositmain` WHERE `DepositNo`="InvtyCharges-Payroll-'.$payrollid.'-'.$co['CompanyNo'].'"';
    $stmt=$link->query($sqldep); $resultdep=$stmt->fetch();

    $txnid=$resultdep['TxnID'];

    $sql0='SELECT * FROM deducted WHERE CompanyNo='.$co['CompanyNo'];
    $stmt0=$link->query($sql0); $result=$stmt0->fetchAll(); 
    
    foreach ($result as $row){
        $sqlinsert='INSERT INTO `acctg_2depositsub` SET TxnID='.$txnid.', BranchNo='.$row['BranchNo'].', ClientNo='.$row['ClientNo']
            .', ForChargeInvNo=\''.$row['Particulars'].'\', Type=0, CreditAccountID='.$row['DebitAccountID'].', Amount='.$row['InvBalance']
            .', TimeStamp=Now(), EncodedByNo='.$_SESSION['(ak0)'];
    //echo $sqlinsert; 
        $stmt=$link->prepare($sqlinsert);
    	$stmt->execute();
        }
        
    } //end $resultco
    header("Location:../acctg/txnsperday.php?perday=1&w=Deposits");
    break;
default:  
   }
end:
     $link=null; $stmt=null;
?>