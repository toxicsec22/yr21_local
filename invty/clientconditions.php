<?php
// lookup if with conditions
$sql='Select s.ClientNo, s.PaymentType, PORequired, ARClientType, Terms from invty_2sale s join `1clients` c on s.ClientNo=c.ClientNo where s.TxnID='.$txnid;
		$stmt=$link->query($sql);
		$result=$stmt->fetch();
	if ($result['ClientNo']>10001){
		$clientconditions='';
		if ($result['ARClientType']==0 and $result['PaymentType']<>1){$clientconditions=$clientconditions.'No allowed terms.';}
		if ($result['Terms']==1){$clientconditions=$clientconditions.' Dated check only.<br>';}
		if ($result['ARClientType']==2){$clientconditions=$clientconditions.'  PDC required (Terms: '.$result['Terms'].')<br>';}
		if ($result['PORequired']<>0){$clientconditions=$clientconditions.'  PO required.';}
		
	} else {
		$clientconditions='';
	}
?>