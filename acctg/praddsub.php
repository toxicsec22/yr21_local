<?php	
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
        // check if allowed
        $allowed=array(515,999,5971,515,5153,514,5404,5931,5951,5962,5401,5921,5991);$allow=0;
        foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
        if ($allow==0) { echo 'No permission'; exit;}
        allowed:
        // end of check
        
	 
        include('../backendphp/functions/getnumber.php');
	include_once('../backendphp/functions/editok.php');
         $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
         
	
        $whichqry=$_GET['w'];
		if (in_array($whichqry,array('DepSubAdd','CVSubAdd','JVSubAdd','DepEncashAdd','FutureSubAdd','PurchaseSubAdd'))){
		include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
		if($whichqry<>'DepSubAdd'){
			$frombudgetof=companyandbranchValue($link, 'acctg_1budgetentities', 'Entity', $_POST['FromBudgetOf'], 'EntityID');
		}
	}

		if (!in_array($whichqry,array('CVSubAdd','JVSubAdd','FutureSubAdd'))){
		$txnid=intval($_GET['TxnID']);
		}
			
switch ($whichqry){
case 'SaleSubAdd':
    if($_SESSION['bnum']==999){ $allowed=999;} else { $allowed=5971;}
		if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit;}
		
	//to check if editable
	if (editOk('acctg_2salemain',$txnid,$link,$whichqry)){
	// to get client no
	$clientno=getNumber('ClientEmployee',addslashes($_POST['ClientName']));	
	$acctdrid=getNumber('Account',addslashes($_POST['DebitAccount'])); $acctcrid=getNumber('Account',addslashes($_POST['CreditAccount']));
	$sqlinsert='INSERT INTO `acctg_2salesub` SET `TxnID`=\''.$txnid.'\', ClientNo='.$clientno.', DebitAccountID='.$acctdrid.', CreditAccountID='.$acctcrid.', ';
        $sql='';
        $columnstoadd=array('Particulars', 'Amount');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
	// echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
		} 
			header("Location:addeditclientside.php?w=Sales&TxnID=".$txnid);
		   
	break;


case 'CollectSubAdd':
    if ((!allowedToOpen(515,'1rtc')) AND (!allowedToOpen(5153,'1rtc'))) { echo 'No permission'; exit; }
         $table='acctg_2collectmain'; $tablesub='acctg_2collectsub';
        //to check if editable
	if (editOk($table,$txnid,$link,$whichqry)){
	
        $sqlunpd='Select `BranchNo`, `DebitAccountID` as `CreditAccountID`, InvBalance as `Amount` from acctg_unpaidinv where Particulars Like \''.$_POST['ForChargeInvNo'].'\' and ClientNo=\''.$_REQUEST['ClientNo'].'\'';
	// echo $sqlunpd;break;
	$stmtunpd=$link->query($sqlunpd);
	$resultunpd=$stmtunpd->fetch();
	$sqlinsert='INSERT INTO `'.$tablesub.'` SET `TxnID`=\''.$txnid.'\', ';
        $sql='';
        $columnstoadd=array('ForChargeInvNo');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	
	$columnstoadd=array('BranchNo', 'CreditAccountID','Amount');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$resultunpd[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
	
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
		} 
			header("Location:addeditclientside.php?w=".substr($whichqry,0,-6)."&TxnID=".$txnid);
		   
	break;

case 'CollectSubAutoAdd':
    if ((!allowedToOpen(515,'1rtc')) AND (!allowedToOpen(5153,'1rtc'))) { echo 'No permission'; exit; }
        $table='acctg_2collectmain'; $tablesub='acctg_2collectsub';
        //to check if editable
	if (editOk($table,$txnid,$link,$whichqry)){
		
        $sqlunpd='Select Particulars as ForChargeInvNo, InvBalance as Amount, c.BranchNo, DebitAccountID as CreditAccountID from acctg_unpaidinvunion c where c.ClientNo=\''.$_REQUEST['ClientNo'].'\' and Particulars Like \''.$_REQUEST['Inv'].'\' and c.BranchNo='.$_REQUEST['BranchNo'];
        //if($_SESSION['(ak0)']==1002){ echo $sqlunpd; exit;}
	
	$columnstoadd=array('BranchNo', 'ForChargeInvNo', 'CreditAccountID','Amount');
       
	if($_SESSION['(ak0)']==1002){echo $sqlunpd.'<br><br>'; }
	// if($_SESSION['(ak0)']==2031){echo $sqlunpd.'<br><br>'; }
        $stmtunpd=$link->query($sqlunpd); 	$resultunpd=$stmtunpd->fetch();
	$sqlinsert='INSERT INTO `'.$tablesub.'` SET `TxnID`=\''.$txnid.'\', ';
        $sql='';
        foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$resultunpd[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
        if($_SESSION['(ak0)']==1002){echo $sql; }
        // if($_SESSION['(ak0)']==2031){echo $sql; }
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
		
        } 
			header("Location:addeditclientside.php?w=".substr($whichqry,0,-10)."&TxnID=".$txnid);
		   
	break;
        
case 'CollectSubCanvass':
    if ((!allowedToOpen(515,'1rtc')) AND (!allowedToOpen(5153,'1rtc'))) { echo 'No permission'; exit; }
        $table='acctg_2collectmain'; $tablesub='acctg_2collectsub';

        //to check if editable
	if (editOk($table,$txnid,$link,$whichqry)){
		
	$sqlunpd='Select CONCAT("CanvassID ", CanvassID) AS OtherORDetails, QuotedPrice as Amount, BranchNo, 405 as CreditAccountID from `quotations_2canvass` c where c.CanvassID='.$_REQUEST['CanvassID'];
	 if($_SESSION['(ak0)']==1002){echo $sqlunpd; }
	$stmtunpd=$link->query($sqlunpd);
	$resultunpd=$stmtunpd->fetch();
	$sqlinsert='INSERT INTO `'.$tablesub.'` SET `TxnID`=\''.$txnid.'\', ForChargeInvNo=0, ';
        if($_SESSION['(ak0)']==1002){echo $sqlinsert; }
        $sql='';
	$columnstoadd=array('BranchNo', 'OtherORDetails', 'CreditAccountID','Amount');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$resultunpd[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
        if($_SESSION['(ak0)']==1002){echo $sql; }
        $stmt=$link->prepare($sql); $stmt->execute();
        
        $sqlupdate='UPDATE `quotations_2canvass` SET Downpayment='.$resultunpd['Amount'].', DPAmtByNo='.$_SESSION['(ak0)'].', DPAmtTimeStamp=Now() WHERE CanvassID='.$_REQUEST['CanvassID'];
	$link->query($sqlupdate);
		} 
			header("Location:addeditclientside.php?w=Collect&TxnID=".$txnid);
		   
	break;

case 'CollectDeduct':
    if ((!allowedToOpen(515,'1rtc')) AND (!allowedToOpen(5153,'1rtc'))) { echo 'No permission'; exit; }
       $table='acctg_2collectmain'; $tablesub='acctg_2collectsubdeduct';
	//to check if editable
	if (editOk($table,$txnid,$link,$whichqry)){
	switch ($_POST['DeductType']){
			case 'CredWTax2306':
			$accountid=161; $desc='CredWTax2306'; break;
		case 'CredWTax2307':
			$accountid=160; $desc='CredWTax2307'; break;	
		default: $accountid=100; $desc=''; break;
	}
	$sql='INSERT INTO `'.$tablesub.'` SET `TxnID`=\''.$txnid.'\', `BranchNo`='.$_SESSION['bnum'].', `DeductDetails`=\''.$desc.(!isset($_POST['inv'])?'':' for inv '.($_POST['inv'])).'\',`DebitAccountID`='.$accountid.', `Amount`=\''.($_POST['Amount']).'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
	$stmt=$link->prepare($sql); if($_SESSION['(ak0)']==1002){echo $sql; }
	$stmt->execute();	
		} 
			header("Location:addeditclientside.php?w=".substr($whichqry,0,-6)."&TxnID=".$txnid);
	break;

case 'CollectAddOther':
    if ((!allowedToOpen(515,'1rtc')) AND (!allowedToOpen(5153,'1rtc'))) { echo 'No permission'; exit; }
        $table='acctg_2collectmain'; $tablesub='acctg_2collectsub'; $detailsfield='OtherORDetails';
        //to check if editable
	if (editOk($table,$txnid,$link,$whichqry)){
	switch ($_POST['AddType']){
		case 'Freight':
			$accountid=921; break;
		case 'Delivery':
			$accountid=921; break;
		case 'Bank Charge':
			$accountid=930; break;
		default: $accountid=100; break;
	}
	$sql='INSERT INTO `'.$tablesub.'` SET `TxnID`=\''.$txnid.'\', `BranchNo`='.$_SESSION['bnum'].', `ForChargeInvNo`=0, `'.$detailsfield.'`=\''.$_POST['AddType'].(!isset($_POST['inv'])?'':' for inv '.($_POST['inv'])).'\',`CreditAccountID`='.$accountid.', `Amount`=\''.($_POST['Amount']).'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
	$stmt=$link->prepare($sql); if($_SESSION['(ak0)']==1002){echo $sql; }
	$stmt->execute();	
		} 
			header("Location:addeditclientside.php?w=".substr($whichqry,0,-8)."&TxnID=".$txnid);
	break;

case 'DepSubAdd':
    if (!allowedToOpen(514,'1rtc')) { echo 'No permission'; exit; }
	//to check if editable
	$pk='TxnID'; $table='acctg_2depositmain'; 
	include('../backendphp/functions/checkeditablesub.php');
	$branchno=getNumber('Branch',addslashes($_POST['Branch']));
    $accountid=getNumber('Account',addslashes($_POST['CreditAccountID']));
	$type=comboBoxValue ($link,'acctg_1deptype','DepType',$_POST['Type'],'DepTypeID');
	$sqlinsert='INSERT INTO `acctg_2depositsub` SET `TxnID`=\''.$txnid.'\', CreditAccountID='.$accountid.', ';
        $sql='';
        $columnstoadd=array('ClientNo','DepDetails','CheckNo','Amount');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' Type=\''.$type.'\',  BranchNo=\''.$branchno.'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()'; 
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:addeditdep.php?w=Deposit&TxnID=".$txnid);
        break;

case 'DepSubAutoAdd':
    if (!allowedToOpen(514,'1rtc')) { echo 'No permission'; exit; }
	//to check if editable
	$pk='TxnID'; $table='acctg_2depositmain'; 
	include('../backendphp/functions/checkeditablesub.php');
	$sqlunpaid='SELECT BranchNo,ClientNo, CRNo,PDCBank AS CheckDraweeBank,PDCNo as CheckNo, if(Cash<>0,1,2) as Type, (Cash+PDC) as Amount,'
                . 'IF(PDCBank LIKE "Downpayment",100,DebitAccountID) as CreditAccountID  FROM acctg_undepositedclientpdcs up where up.PDCID like \''. $_REQUEST['PDCID'].'\'';
	$stmt=$link->query($sqlunpaid);
	$result=$stmt->fetchAll();
	
	foreach ($result as $row){
	$sqlinsert='INSERT INTO `acctg_2depositsub` SET `TxnID`=\''.$txnid.'\', ';
        $sql='';
        $columnstoadd=array('BranchNo','ClientNo','Type','CRNo','CheckDraweeBank','CheckNo','CreditAccountID','Amount');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$row[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()'; 
	if ($_SESSION['(ak0)']==1002){ echo $sql.'<br>';}
        $stmt=$link->prepare($sql); $stmt->execute();
        }
        
        
	header("Location:addeditdep.php?w=Deposit&TxnID=".$txnid);
        break;

case 'DepSubAutoAddCash':
    if (!allowedToOpen(514,'1rtc')) { echo 'No permission'; exit; }
	
    if (allowedToOpen(2201,'1rtc')){
        error_reporting(E_ALL);
	ini_set('display_errors', 1);
}
    
	//to check if editable
	$pk='TxnID'; $table='acctg_2depositmain'; 
	include('../backendphp/functions/checkeditablesub.php');
	// deposit cash sales
        // get recorded op first
        $sql0='SELECT SUM(IFNULL(a.Amount,0)) as OPAmount FROM `invty_7opapproval` a join `invty_2sale` m ON m.TxnID=a.TxnID
WHERE ((m.Date=\''.$_POST['CashSales'].'\') AND ((m.BranchNo)='.$_SESSION['bnum'].')) AND m.PaymentType=1 Group by m.Date, m.BranchNo;';
        $stmt=$link->query($sql0);	$result0=$stmt->fetch(); 
        
	$sql='SELECT round((sum(s.UnitPrice*s.Qty)+'.(is_null($result0['OPAmount'])?0:$result0['OPAmount']).'),2) as Amount FROM invty_2sale m join invty_2salesub as s on m.TxnID=s.TxnID '
                . 'LEFT JOIN `invty_7opapproval` a ON m.TxnID=a.TxnID '
                . 'WHERE ((m.Date=\''.$_POST['CashSales'].'\') AND ((m.BranchNo)='.$_SESSION['bnum'].')) AND m.PaymentType=1 Group by m.Date, m.BranchNo';
        if($_SESSION['(ak0)']==1002){echo $sql; }
	$stmt=$link->query($sql); 	$result=$stmt->fetch();
	$sqlinsert='INSERT INTO `acctg_2depositsub` SET `TxnID`=\''.$txnid.'\', `ClientNo`=10000, `DepDetails`=concat("income ",\''.date_format(date_create($_POST['CashSales']),"m/d/y").'\'), `Type`=0, `CreditAccountID`=100, `Amount`=\''.$result['Amount'].'\',  BranchNo=\''.$_SESSION['bnum'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()'; 
	if($_SESSION['(ak0)']==1002){echo $sqlinsert; }
        $stmt=$link->prepare($sqlinsert); 	$stmt->execute();
     
	//get sum of returns
	$sqltxn5='SELECT round((sum(s.UnitPrice*s.Qty)),2) as Amount,m.SaleNo, ClientNo,m.BranchNo FROM invty_2sale m join invty_2salesub as s on m.TxnID=s.TxnID '
                . 'WHERE ((m.Date=\''.$_POST['CashSales'].'\') AND ((m.BranchNo)='.$_SESSION['bnum'].')) AND m.PaymentType=5 Group by m.TxnID HAVING Amount>0';
	$stmttxn5=$link->query($sqltxn5);	$resulttxn5=$stmttxn5->fetchAll(); 
	// echo $sqltxn5;
	
	if($stmttxn5->rowCount()>0){
            foreach($resulttxn5 as $txn5){
			$sqlinsert='INSERT INTO `acctg_2depositsub` SET `TxnID`=\''.$txnid.'\', `ClientNo`=\''.$txn5['ClientNo'].'\', `DepDetails`="sales returns with exchange '.$txn5['SaleNo'].' '.date_format(date_create($_POST['CashSales']),"m/d/y").'", `Type`=0, `CreditAccountID`=100, `Amount`=\''.$txn5['Amount'].'\', BranchNo=\''.$txn5['BranchNo'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();';
			if($_SESSION['(ak0)']==1002){echo $sqlinsert; }
                        $stmt=$link->prepare($sqlinsert); 	$stmt->execute();
            }
	}
		
	// exit();
	// deposit cash collections for freight-clients
	$sqlfc='SELECT sum(fc.Amount) as FreightSum FROM `approvals_2freightclients` fc JOIN `invty_2sale` m ON m.SaleNo=fc.ForInvoiceNo AND m.BranchNo=fc.BranchNo AND m.txntype=fc.txntype  WHERE ((m.Date=\''.$_POST['CashSales'].'\') AND ((m.BranchNo)='.$_SESSION['bnum'].')) AND m.PaymentType=1 AND fc.PriceFreightInclusive=0 Group by m.Date, m.BranchNo'; 
   $stmtfc=$link->query($sqlfc); $resultfc=$stmtfc->fetch();
   if (!is_null($resultfc['FreightSum'])){
	$sqlinsert='INSERT INTO `acctg_2depositsub` SET `TxnID`=\''.$txnid.'\', `ClientNo`=10000, `DepDetails`=concat("collection for freight ",\''.date_format(date_create($_POST['CashSales']),"m/d/y").'\'), `Type`=0, `CreditAccountID`=925, `Amount`=\''.$resultfc['FreightSum'].'\',  BranchNo=\''.$_SESSION['bnum'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()'; 
	if($_SESSION['(ak0)']==1002){echo $sqlinsert; }
        $stmt=$link->prepare($sqlinsert); 	$stmt->execute();
	}
        
                
        // Encashment for overprice from cash invoices
        $sqlop='SELECT round((SUM(Amount)),2)-round((SUM(Amount)*(0.12)),0) as OP FROM `invty_2sale` ism '
                 . 'join `invty_7opapproval` isb on ism.TxnID=isb.TxnID WHERE ism.Date=\''.$_POST['CashSales'].'\' AND (ism.BranchNo)='.$_SESSION['bnum']
                .'  and ism.txntype=1 ';
        $stmtop=$link->query($sqlop); $resultop=$stmtop->fetch(); 
        if ($resultop['OP']<>0){
        $sqlinsert='INSERT INTO `acctg_2depencashsub` SET `TxnID`=\''.$txnid.'\', DebitAccountID=405, `EncashDetails`=concat("overprice ",\''.date_format(date_create($_POST['CashSales']),"m/d/y").'\'), `Amount`=\''.$resultop['OP'].'\', BranchNo='.$_SESSION['bnum'].', EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
        $stmt=$link->prepare($sqlinsert); 	$stmt->execute(); }
	header("Location:addeditdep.php?w=Deposit&TxnID=".$txnid);
        break;

case 'DepSubAutoAddInv':
    if (!allowedToOpen(514,'1rtc')) { echo 'No permission'; exit; }
	//to check if editable
	$pk='TxnID'; $table='acctg_2depositmain'; 
	include('../backendphp/functions/checkeditablesub.php');
	$sql='SELECT *, Particulars as ForChargeInvNo, DebitAccountID as CreditAccountID, InvBalance as Amount FROM acctg_unpaidinv up where up.UnpdInvID='.$_REQUEST['UnpdInvID'];
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	$sqlinsert='INSERT INTO `acctg_2depositsub` SET `TxnID`=\''.$txnid.'\', `Type`=0, '; 
	$sql='';
        $columnstoadd=array('BranchNo','ClientNo','ForChargeInvNo','CreditAccountID','Amount');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$result[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
	// echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:addeditdep.php?w=Deposit&TxnID=".$txnid);
        break;

case 'SaveCashCount':
    if (!allowedToOpen(514,'1rtc')) { echo 'No permission'; exit; }
	//to check if editable
	$pk='TxnID'; $table='acctg_2depositmain'; 
	include('../backendphp/functions/checkeditablesub.php');
	if ($_GET['exist']==0){
	$sqlinsert='INSERT INTO `acctg_2depcashcountsub` SET `TxnID`=\''.$txnid.'\', ';
	$sqlafter='';
	} else {
	$sqlinsert='UPDATE `acctg_2depcashcountsub` SET ';
	$sqlafter=' where `TxnID`=\''.$txnid.'\'';
	}
        $sql='';
        $columnstoadd=array('1000','500','200','100','50','20','10','5','1','025','010','005');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() '.$sqlafter; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:addeditdep.php?w=Deposit&TxnID=".$txnid);
        break;

        
case 'TxfrSubAdd':
    if (!allowedToOpen(5951,'1rtc')) { echo 'No permission'; exit; }
	//to check if editable
	if (editOk('acctg_2txfrmain',$txnid,$link,$whichqry)){
	$sqlinsert='INSERT INTO `acctg_2txfrsub` SET `TxnID`=\''.$txnid.'\', DebitAccountID=204, ';
        $sql='';
        	
	$columnstoadd=array('ClientBranchNo', 'Particulars','Amount');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' OUTEncodedByNo=\''.$_SESSION['(ak0)'].'\', OUTTimeStamp=Now()'; 
	// echo $sql;break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
		} 
			header("Location:addeditclientside.php?w=Interbranch&TxnID=".$txnid);
		   
	break;

case 'PurchaseSubAdd':
        if($_SESSION['bnum']==999){ $allowed=999;} else { $allowed=5962;}
        if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit;} 
        //to check if editable
	if (editOk('acctg_2purchasemain',$txnid,$link,$whichqry)){
	$drid=getNumber('Account',$_POST['DebitAccount']);//,	
	$sqlinsert='INSERT INTO `acctg_2purchasesub` SET FromBudgetOf='.$frombudgetof.',TxnID='.$txnid.', DebitAccountID='.$drid.', ';
	$sql='';
        $columnstoadd=array('Amount');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now();'; 
	// echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	}
	header("Location:addeditsupplyside.php?w=Purchase&TxnID=".$txnid);
        break;
	
case 'CVSubAdd':
case 'FutureSubAdd':
		if (!allowedToOpen(5401,'1rtc')) { echo 'No permission'; exit;} 
		$txnid=intval($_REQUEST['CVNo']);
	if ($whichqry=='FutureSubAdd'){ $w='FutureCV'; $table='4future'; } else { $w='CV'; $table='2'; }
	//to check if editable
	if (editOk('acctg_'.$table.'cvmain',$txnid,$link,$whichqry)){
	$drid=getNumber('Account',addslashes($_POST['DebitAccount']));
	$branchno=getNumber('Branch',addslashes($_POST['Branch']));
	// echo $_POST['Entity']; exit();
	
	if (isset($_POST['TIN']) AND !empty($_POST['TIN'])){$tin=' TIN=\''.str_replace("-","",$_POST['TIN']).'\', ';} else { $tin='';}
	$sqlinsert='INSERT INTO `acctg_'.$table.'cvsub` SET `CVNo`=\''.$txnid.'\', DebitAccountID='.$drid.', BranchNo='.$branchno.', FromBudgetOf='.$frombudgetof.', '.$tin;
        $sql='';
        	
	$columnstoadd=array('Particulars', 'Amount');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
	if ($_SESSION['(ak0)']==1002){ echo $sql;}
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
		}  
			header("Location:addeditsupplyside.php?w=".$w."&CVNo=".$txnid);
		   
	break;


case 'CVSubAutoAdd':
        if (!allowedToOpen(5401,'1rtc')) { echo 'No permission'; exit;}
	//to check if editable
	if (editOk('acctg_2cvmain',$txnid,$link,$whichqry)){
		$sql0='Select SupplierInv, PayBalance, BranchNo, CreditAccountID from acctg_23balperinv i  where SupplierInv like \''.$_REQUEST['SupplierInv'].'\' and i.SupplierNo='.$_REQUEST['SupplierNo'];
		$stmt=$link->query($sql0);
		$result=$stmt->fetch();
	$sql='INSERT INTO `acctg_2cvsub` SET `CVNo`=\''.$txnid.'\', DebitAccountID='.$result['CreditAccountID'].', ForInvoiceNo=\''.$result['SupplierInv'].'\', Amount='.$result['PayBalance'].', BranchNo='.$result['BranchNo'].', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
	// echo $sql;break;
        $stmt=$link->prepare($sql);
	$stmt->execute();	
		} 
			header("Location:addeditsupplyside.php?w=CV&CVNo=".$txnid);
	break;

case 'JVSubAdd':
        if (!allowedToOpen(5921,'1rtc')) { echo 'No permission'; exit;}
        $table='acctg_2jvmain'; $subtable='acctg_2jvsub'; 
		$txnid=intval($_GET['JVNo']);
    
    	//to check if editable
	if (editOk($table,$txnid,$link,$whichqry)){
	$branchno=getNumber('Branch',addslashes($_POST['Branch']));
	$drid=getNumber('Account',addslashes($_POST['DebitAccount']));
	$crid=getNumber('Account',addslashes($_POST['CreditAccount']));
	$sqlinsert='INSERT INTO `'.$subtable.'` SET `JVNo`=\''.$txnid.'\', BranchNo='. $branchno .', FromBudgetOf='. $frombudgetof .', DebitAccountID='.$drid.', CreditAccountID='.$crid.', ';
        $sql='';
        
	$columnstoadd=array('Date','Particulars','Amount');
	       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sql.($whichqry=='ForexSubAdd'?'`PhpAmount`='.$_POST['$Amount']*$_POST['Forex'].', ':'');
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
	if ($_SESSION['(ak0)']==1002){ echo $sql;}
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
		} 
			header("Location:addeditsupplyside.php?w=".substr($whichqry,0,-6)."&JVNo=".$txnid);
		   
	break;

case 'DepEncashAdd':
    if (!allowedToOpen(5991,'1rtc')) { echo 'No permission'; exit;}
	//to check if editable
	$pk='TxnID'; $table='acctg_2depositmain'; 
	include_once('../backendphp/functions/checkeditablesub.php'); 
	$typeidsql='';
	$details=(empty($_POST['EncashDetails']))?(($_POST['TypeID']=='Gas')?'KR: '.$_POST['KMperReading'].' Inv: ' .$_POST['InvoiceNo']:$_POST['EncashDetails']):$_POST['EncashDetails'];
	
	$amt=$_POST['Amount'];
	
	$acctid=(empty($_POST['DebitAccount']) or !isset($_POST['DebitAccount']))?100:(getNumber('Account',($_POST['DebitAccount']))); 
	//echo $details.'<br>'.$acctid.'<br>post: '.$_POST['DebitAccount'];
	$branchno=$_SESSION['bnum']; $approvalno=0; //echo $branchno;
	include ('sqlphp/checkapprovedamtandbudget.php');
	
	if ($encode==true){ goto encode;} else {goto skip;}
	encode:
	
	//check frombudget of approval
	$sqlfbo='SELECT FromBudgetOf FROM approvals_2encashedexpenses WHERE Approval = "'.$approval.'" UNION SELECT FromBudgetOf FROM approvals_2freightclients WHERE Approval = "'.$approval.'" ';
	$stmtfbo=$link->query($sqlfbo); $resfbo=$stmtfbo->fetch();
	
	if($stmtfbo->rowCount()>0 AND $resfbo['FromBudgetOf']<>''){
		$frombudgetof=$resfbo['FromBudgetOf'];
	}
	
	
	if (isset($_POST['TIN']) AND !empty($_POST['TIN'])){$tin=' TIN=\''.str_replace("-","",$_POST['TIN']).'\', ';} else { $tin='';}
        $type=comboBoxValue($link, 'acctg_1branchpreapprovedbudgetlist', 'BudgetDesc', $_REQUEST['TypeID'], 'TypeID');
		$sql='INSERT INTO `acctg_2depencashsub` SET `TxnID`=\''.$txnid.'\', DebitAccountID='.$acctid.', '.$tin.' `EncashDetails`="'.addslashes($details).'", `ApprovalNo`="'.$approval.'",'.(!empty($_REQUEST['TypeID'])?'TypeID=\''.$type.'\',':'').'  `Amount`='.$amt.', 
	 BranchNo='.$branchno.',FromBudgetOf='. $frombudgetof .', EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
if($_SESSION['(ak0)']==1002){echo $sql; }
// echo $sql; exit();
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	if ($_POST['TypeID']=='Gas'){
		$sql2='INSERT INTO `admin_2fuelconsumption` SET `Date`=\''.$_POST['Date'].'\', VehicleID='.$_POST['VehicleID'].', `KmReading`="'.$_POST['KMperReading'].'", `Liter`="'.$_POST['Liter'].'", PriceperLiter='.$_POST['PriceperLiter'].', InvoiceNo='.$_POST['InvoiceNo'].', Remarks="'.$_POST['Remarks'].'", EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
                if($_SESSION['(ak0)']==1002){echo $sql2; }
		$stmt2=$link->prepare($sql2);
		$stmt2->execute();
	}
	skip:
	header("Location:addeditdep.php?w=Deposit&TxnID=".$txnid.(isset($msg)?"&msg=".$msg:""));
        break;
    
        } // end switch
$stmt=null; $link=null;
?>