<?php
if (allowedToOpen(824,'1rtc') and $paysliptype<>'mypayslip'){
    switch ($paysliptype) {
      case 'all':
        $payrollid=$_POST['payrollid']; $idno=$rowmain['IDNo'];
        break;
  
        default: //per person
        $payrollid=$rowmain['PayrollID']; $idno=$rowmain['IDNo'];
        break;
    }
    
  } else { //My Payslip
    $payrollid=$_REQUEST['payrollid']; $idno=$_SESSION['(ak0)'];
   //IF ($_SESSION['(ak0)']==1002) { echo $sql1;}
  } 

 if(in_array($paysliptype,array('all','perperson'))){
 $sql='SELECT ForChargeInvNo,Amount,Branch from acctg_2depositmain dm JOIN acctg_2depositsub ds ON dm.TxnID=ds.TxnID JOIN 1branches b ON ds.BranchNo=b.BranchNo WHERE DepositNo LIKE "%InvtyCharges-Payroll-'.$payrollid.'-%" AND ClientNo='.$idno.' AND Posted=1 ORDER BY Branch';
		
		// echo $sql;
		$stmt=$link->query($sql); $rows=$stmt->fetchAll();
    $charges='';

		if($stmt->rowCount()>0){
			$charges.='Summary of Charges<br><table>';
			$charges.='<tr><td>Branch</td><td>ForChargeInvNo</td><td>Amount</td></tr>';
			foreach($rows AS $row){
				$charges.='<tr><td>'.$row['Branch'].'</td><td>'.$row['ForChargeInvNo'].'</td><td>'.number_format($row['Amount'],2).'</td></tr>';
			}
			$charges.='</table><br>';
			
		}
  }