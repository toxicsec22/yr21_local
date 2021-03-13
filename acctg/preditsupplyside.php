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
	
case 'PurchaseEdit':
    if($_SESSION['bnum']==999){ $allowed=999;} else { $allowed=5962;}
		if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit;} 
		$txnid=intval($_GET['TxnID']);
	//to check if editable
        $table='acctg_2purchasemain';
	if (editOk($table,$txnid,$link,$whichqry)){
        recordtrail($txnid,$table,$link,0);
	$suppno=getNumber('Supplier',addslashes($_POST['SupplierName']));
	$regsuppno=empty($_POST['RegisteredSupplier'])?'':' RegisteredSupplierNo='.getNumber('Supplier',addslashes($_POST['RegisteredSupplier'])).',';
	$crid=getNumber('Account',addslashes($_POST['CreditAccount']));
	if (!isset($_POST['RCompany']) or empty($_POST['RCompany'])){$co='';}else{$co='RCompany='.getNumber('Company',addslashes($_POST['RCompany'])).', ';}
	$sqlupdate='UPDATE `acctg_2purchasemain` SET  SupplierNo='.$suppno.', CreditAccountID='.$crid.', '.$regsuppno.$co; 
        $sql='';
        $columnstoedit=array('Date','SupplierInv','DateofInv','MRRNo','Terms','BranchNo','Remarks');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() where Posted=0 and TxnID='.$txnid; 
	//echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	} 
	header("Location:addeditsupplyside.php?w=Purchase&TxnID=".$txnid);
        break;

case 'PurchaseSubEdit':
    if($_SESSION['bnum']==999){ $allowed=999;} else { $allowed=5962;}
		if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit;} 
		$txnid=intval($_GET['TxnID']);
	//to check if editable
	if (editOk('acctg_2purchasemain',$txnid,$link,$whichqry)){
	$txnsubid=$_GET['TxnSubId']; recordtrail($txnsubid,'acctg_2purchasesub',$link,0);
	$drid=getNumber('Account',addslashes($_POST['DebitAccount']));
	// echo $frombudgetof;
	// exit();
	$sqlupdate='UPDATE `acctg_2purchasesub` SET  TxnID='.$txnid.', DebitAccountID='.$drid.', ';
        $sql='';
        $columnstoedit=array('Amount');
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' FromBudgetOf='.$frombudgetof.',EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() where TxnSubId='.$txnsubid; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	}
	header("Location:addeditsupplyside.php?w=Purchase&TxnID=".$txnid);
        break;

case 'FutureCVMainEdit':
case 'CVMainEdit':
		if (!allowedToOpen(5401,'1rtc')) { echo 'No permission'; exit;} 
		$txnid=intval($_GET['CVNo']); 
	if ($whichqry=='FutureCVMainEdit'){$w='FutureCV'; $table='4future';} else { $w='CV'; $table='2';}
	//to check if editable
	if (editOk('acctg_'.$table.'cvmain',$txnid,$link,$whichqry)){
         recordtrail($txnid,'acctg_'.$table.'cvmain',$link,0);
	// to get client no
	$suppno=getNumber('Supplier',addslashes($_POST['Payee']));
	$crid=getNumber('Account',addslashes($_POST['CreditAccount']));
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	$pmid=comboBoxValue($link,'acctg_0paymentmodes','PaymentMode',addslashes($_POST['PaymentMode']),'PaymentModeID');
	$sqlupdate='UPDATE `acctg_'.$table.'cvmain` SET PaymentModeID='.$pmid.','.(!is_null($suppno)?'PayeeNo='.$suppno.', ':'PayeeNo=null, ').' Payee=\''.$_POST['Payee'].'\', CreditAccountID='.$crid.', ';
        $sql='';
        $columnstoedit=array('Date','DueDate','CVNo','CheckNo','DateofCheck','Remarks');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() where Posted=0 and CVNo='.$txnid; 
	if($_SESSION['(ak0)']==1002){ echo $sql;}
        $stmt=$link->prepare($sql);
	$stmt->execute();
	} 
	header("Location:addeditsupplyside.php?w=".$w."&CVNo=".intval($_POST['CVNo']));
        break;

case 'CVSubEdit':

case 'FutureCVSubEdit':
		if (!allowedToOpen(5401,'1rtc')) { echo 'No permission'; exit;} 
		$txnid=intval($_REQUEST['CVNo']);
	$columnstoedit=array();//('','Amt');
	// if ($whichqry=='FutureCVSubEdit'){$w='FutureCV'; $table='4future';} else { $w='CV'; $table='2'; $columnstoedit[]='ForInvoiceNo';}
	if ($whichqry=='FutureCVSubEdit'){$w='FutureCV'; $table='4future';} else { $w='CV'; $table='2'; $columnstoedit[]='ForInvoiceNo';}
	//to check if editable
	if (editOk('acctg_'.$table.'cvmain',$txnid,$link,$whichqry)){
	$txnsubid=$_GET['TxnSubId']; recordtrail($txnsubid,'acctg_'.$table.'cvsub',$link,0);
	$drid=getNumber('Account',addslashes($_POST['DebitAccount']));
	$branchno=getNumber('Branch',addslashes($_POST['Branch']));
	$sql=empty($_POST['Particulars'])?'':'Particulars="'.$_POST['Particulars'].'", ';
	$amt=str_replace(',','',$_POST['Amount']);
	if (isset($_POST['TIN']) AND !empty($_POST['TIN'])){$tin=' TIN=\''.str_replace("-","",$_POST['TIN']).'\', ';} else { $tin='';}
	$sql='UPDATE `acctg_'.$table.'cvsub` SET  CVNo='.$txnid.', DebitAccountID='.$drid.', BranchNo='.$branchno.', FromBudgetOf='.$frombudgetof.', Amount='.$amt.', '.$tin.$sql.'  EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() where TxnSubId='.$txnsubid; 
	// echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	}
	header("Location:addeditsupplyside.php?w=".$w."&CVNo=".$txnid);
        break;
 
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
case 'JVMainEdit':
		if (!allowedToOpen(5921,'1rtc')) { echo 'No permission'; exit;}
		$txnid=intval($_GET['JVNo']);
        $title='Add/Edit JV'; $table='acctg_2jvmain'; 
    
	//to check if editable
	if (editOk($table,$txnid,$link,$whichqry)){
	recordtrail($txnid,$table,$link,0);
	$sqlupdate='UPDATE `'.$table.'` SET  ';
        $sql='';
        $columnstoedit=array('JVDate','JVNo','Remarks');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() where Posted=0 and JVNo='.$txnid; 
	//echo $sql; exit();
        $stmt=$link->prepare($sql);	$stmt->execute();
	} 
	header("Location:addeditsupplyside.php?w=".substr($whichqry,0,-8)."&JVNo=".$_POST['JVNo']);
        break;

case 'JVSubEdit':
    
        if (!allowedToOpen(5921,'1rtc')) { echo 'No permission'; exit;}
        $table='acctg_2jvmain'; $subtable='acctg_2jvsub'; $columnstoedit=array('Date','Particulars','Amount'); 
		$txnid=intval($_GET['JVNo']);
	//to check if editable
	if (editOk($table,$txnid,$link,$whichqry)){
	$txnsubid=$_GET['TxnSubId']; recordtrail($txnsubid,$subtable,$link,0);
	$stmt=$link->query('Select DebitAccountID, CreditAccountID from `'.$subtable.'` where TxnSubId='.$txnsubid);
	$result=$stmt->fetch();
	$branchno=getNumber('Branch',addslashes($_POST['Branch']));
	$drid=isset($_POST['DebitAccount'])?getNumber('Account',addslashes($_POST['DebitAccount'])):$result['DebitAccountID'];
	$crid=isset($_POST['CreditAccount'])?getNumber('Account',addslashes($_POST['CreditAccount'])):$result['CreditAccountID'];
	
	$sqlupdate='UPDATE `'.$subtable.'` SET  JVNo='.$txnid.', BranchNo='. $branchno .',FromBudgetOf='.$frombudgetof.', DebitAccountID='.$drid.', CreditAccountID='.$crid.', ';
        $sql='';
        
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() where TxnSubId='.$txnsubid; 
	if($_SESSION['(ak0)']==1002){ echo $sql;}
        $stmt=$link->prepare($sql);
	$stmt->execute();
	}
	header("Location:addeditsupplyside.php?w=".substr($whichqry,0,-7)."&JVNo=".$txnid);
        break;
 
        }
  $link=null; $stmt=null;
?>