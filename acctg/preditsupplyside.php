<?php
	 // global $currentyr;
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
        // check if allowed
        $allowed=array(999,5962,5401,5404,601,5921);$allow=0;
        foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
        if ($allow==0) { echo 'No permission'; exit;}
        allowed:
        // end of check
        
	include_once('../backendphp/functions/editok.php');
	include_once('../backendphp/functions/getnumber.php');
	include_once 'trailacctg.php';

         $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
	
	
        
        $whichqry=$_GET['w'];
		if (in_array($whichqry,array('CVSubEdit','JVSubEdit','FutureCVSubEdit','PurchaseSubEdit'))){
			include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
		$frombudgetof=companyandbranchValue($link, 'acctg_1budgetentities', 'Entity', $_POST['FromBudgetOf'], 'EntityID');}
switch ($whichqry){
	
case 'CVBudget':
	if (!allowedToOpen(601,'1rtc')) { echo 'No permission'; exit;}
	$txnid=intval($_GET['CVNo']);
	//to check if editable
	if (editOk('acctg_2cvmain',$txnid,$link,$whichqry)){
	$sqlupdate='UPDATE `acctg_2cvmain` SET ';
        $sql='';
        $columnstoedit=array('DateofCheck','CheckNo','CreditAccountID','Remarks');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() where Posted=0 and TxnID='.$txnid; 
	//echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	} 
	header("Location:txnsperday.php?perday=1&w=CVBudget&Date=".$_GET['Date']);
        break;
case 'Budget':
	if (!allowedToOpen(601,'1rtc')) { echo 'No permission'; exit;}
	$txnid=intval($_GET['TxnID']);
	$sql='UPDATE `budgetforcalc` SET Budget='.$_POST['Budget'].', TS=Now() where AccountID='.$_REQUEST['AccountID'];
	//echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:txnsperday.php?perday=1&w=CVBudget&Date=".$_GET['Date']);
        break;

 
        }
  $link=null; $stmt=null;
?>