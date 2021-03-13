<?php
	 $path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	if (!allowedToOpen(62481,'1rtc')) {   echo 'No permission'; exit;} 
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
        
if (isset($_REQUEST['Send'])){
	$sql='UPDATE acctg_budgetforcalc c join banktxns_1maintaining m on c.AccountID=m.AccountID Set c.Budget=m.Budget;';
	$stmt=$link->prepare($sql);	$stmt->execute();
	header("Location:../acctg/txnsperday.php?perday=1&w=VoucherBudget");
} else {
	$sql=''; $columnstoedit=array('Transfers','Budget','Remarks');
	foreach ($columnstoedit as $field) { $sql=$sql.' `' . $field. '`=\''.str_replace(",","",$_POST[$field]).'\', '; }
	$sql='UPDATE banktxns_1maintaining SET '.$sql.' Backup='.str_replace(",","",$_POST['Backup']).' WHERE AccountID='.$_GET['AccountID']; //echo $sql; break;
        $stmt=$link->prepare($sql);	$stmt->execute(); 
		$stmt0=$link->query('Select Sum(Transfers)*-1 AS BPI from banktxns_1maintaining as maintain where AccountID<>128');
		$result=$stmt0->fetch();
		$sql='UPDATE banktxns_1maintaining SET Transfers='.$result['BPI'].' WHERE AccountID=128'; 
		$stmt=$link->prepare($sql);	$stmt->execute();
		$sql0='Select Sum(Transfers)*-1 AS BPI from banktxns_1maintaining as maintain where AccountID<>129';
	
		
	//$stmt0=$link->query('Select Sum(Backup) AS NetBackup from banktxns_1maintaining as maintain where AccountID<>128');
	//	$result=$stmt0->fetch();
	//	$sql='UPDATE banktxns_1maintaining SET Backup='.(6000000-$result['NetBackup']).' WHERE AccountID=128';
	//	$stmt=$link->prepare($sql);	$stmt->execute();
		header("Location:lookupbankdata.php?w=BankBalances");
}
 $link=null; $stmt=null; 
?>