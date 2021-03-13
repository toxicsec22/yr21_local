<?php
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
        // check if allowed
        $allowed=array(515,523,2051,2052,5181,522,521,5211,523,206,999,5971,515,514,5931,5951,5962,5401,5921,5993,6002);
        $allow=0;
        foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
        if ($allow==0) { echo 'No permission'; exit;}
        allowed:
        // end of check
        
	  include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        include('../backendphp/functions/getnumber.php');
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
        
        $whichqry=$_GET['w'];
switch ($whichqry){

case 'RequestForExpenseApproval': // Unbudgetted Cash Expenses
	if (!allowedToOpen(523,'1rtc')) { echo 'No permission'; exit; }		
	if(isset($_REQUEST['Type'])){
	$type=comboBoxValue($link, 'acctg_1branchpreapprovedbudgetlist', 'BudgetDesc', $_REQUEST['Type'], 'TypeID');	
	$accountid=comboBoxValue($link, 'acctg_1branchpreapprovedbudgetlist', 'BudgetDesc', $_REQUEST['Type'], 'AccountID');	
	
	$monthvalue=date('m',strtotime($_POST['Date']));
		$sqlc='select sum(`'.$monthvalue.'`)-(select sum(Amount) from acctg_2depositmain m left join acctg_2depencashsub s on s.TxnID=m.TxnID where month(Date)=\''.$monthvalue.'\' and TypeID=\''.$type.'\' and BranchNo=\''.$_SESSION['bnum'].'\') as `'.$monthvalue.'` from acctg_5branchpreapprovedbudgetspermonth where TypeID=\''.$type.'\' and BranchNo=\''.$_SESSION['bnum'].'\'';
		// echo $sqlc; exit();

		$stmtc=$link->query($sqlc); $resultc=$stmtc->fetch();
		if($_POST['Amount']<$resultc[$monthvalue]){
			echo'There is enough pre-approved budget to cover this expense.'; exit();
		}

	}else{
		$type='';
	}
	$sqlinsert='INSERT INTO `approvals_2encashedexpenses` Set ';
	$sql='';
        $columnstoadd=array('Date','Particulars','Amount');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	$addsqlfbo='';
	if(isset($_POST['FromBudgetOf'])){
		$frombudgetof=comboBoxValue($link, ' `acctg_1budgetentities` ', 'Entity', $_POST['FromBudgetOf'], 'EntityID');
		$addsqlfbo='FromBudgetOf='.$frombudgetof.',';
	}
	
	$sql=$sqlinsert.$sql.' '.$addsqlfbo.' eeswitch='.$_GET['eeswitch'].',BranchNo='.$_SESSION['bnum'].', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now(),TypeID=\''.$type.'\' '.(isset($_REQUEST['Type'])?',AccountID=\''.$accountid.'\'' :'').' ;'; 
	// echo $sql; exit();
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header('Location:/'.$url_folder.'/index.php?done=1');
	break;

case 'ApproveRequestforExpense': // Approve Unbudgetted Cash Expenses
    if (!allowedToOpen(2051,'1rtc')) { echo 'No permission'; exit; }
		if(isset($_POST['AccountID'])){
	$accountid=getNumber('Account',addslashes($_POST['AccountID']));
        $branchno=getNumber('Branch',addslashes($_POST['Branch']));
		}
	$approvalno=$_POST['Approval'];
	$frombudgetof=comboBoxValue($link, ' `acctg_1budgetentities` ', 'Entity', $_POST['FromBudgetOf'], 'EntityID');
	$sql0='Select Approval from `approvals_2encashedexpenses` where Approval='.$approvalno;
	$stmt=$link->query($sql0);
	if ($stmt->rowCount()>0){
		$approvalno=mt_rand(1000,99999);
		$sql0='Select Approval from `approvals_2encashedexpenses` where Approval='.$approvalno;
		$stmt=$link->query($sql0);
		if ($stmt->rowCount()>0){
		$approvalno=mt_rand(1000,99999);
		$sql0='Select ApprovalNo from acctg_2depencashsub where ApprovalNo='.$approvalno;
		$stmt=$link->query($sql0);
		}
		
	}
		
	$sqlinsert='UPDATE `approvals_2encashedexpenses` Set FromBudgetOf='.$frombudgetof.',Approval='.$approvalno.', '.(isset($_POST['AccountID'])?'AccountID=\''.$accountid.'\', BranchNo=\''.$branchno.'\',':'').' ';
	$sql='';
        $columnstoadd=array('Particulars','Amount');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	$sql=$sqlinsert.$sql.' ApprovedByNo=\''.$_SESSION['(ak0)'].'\', ApprovedTS=Now() where ApprovalID='.$_POST['ApprovalID']; 
	// echo $sql; exit();
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	
	
	$sql='UPDATE invty_2waybills SET Encashed=1 WHERE WaybillNo="'.$_POST['Particulars'].'"';
    $stmt=$link->prepare($sql);
	$stmt->execute();
	
	
	
	header("Location:approval.php?w=Approved&BranchNo=".$branchno."&Approval=".$approvalno);
	break;
        
case 'ConfirmedExpense': // Unbudgetted Cash Expenses, confirmed by Acctg Mgr
    if (!allowedToOpen(2052,'1rtc')) { echo 'No permission'; exit; }
	$sql='UPDATE `approvals_2encashedexpenses` SET ConfirmedByNo=\''.$_SESSION['(ak0)'].'\', ConfirmedTS=Now() WHERE ApprovalId='.$_POST['ApprovalId']; 
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:".$_SERVER['HTTP_REFERER']);
	break;
    
case 'Approval': // Unbudgetted Cash Expenses, approval before the request
    if (!allowedToOpen(5181,'1rtc')) { echo 'No permission'; exit; }
	
	if(isset($_REQUEST['Type'])){
	$type=comboBoxValue($link, 'acctg_1branchpreapprovedbudgetlist', 'BudgetDesc', $_REQUEST['Type'], 'TypeID');	
	$accountid=comboBoxValue($link, 'acctg_1branchpreapprovedbudgetlist', 'BudgetDesc', $_REQUEST['Type'], 'AccountID');	
	}else{
	$accountid=getNumber('Account',addslashes($_POST['AccountID']));
	$type='';
	}
	
	$branchno=getNumber('Branch',addslashes($_POST['Branch']));

	
	$approvalno=$_POST['Approval'];
	$sql0='Select Approval from `approvals_2encashedexpenses` where Approval='.$approvalno;
	$stmt=$link->query($sql0);
	//echo $stmt->rowCount(); echo '<br>'.$approvalno; break;
	while ($stmt->rowCount()>0):
		$approvalno=mt_rand(10000,999999);
		$sql0='Select Approval from approvals_2encashedexpenses where Approval='.$approvalno;
		$stmt=$link->query($sql0);
		endwhile;
		
	$sqlinsert='INSERT INTO `approvals_2encashedexpenses` Set Approval='.$approvalno.', AccountID=\''.$accountid.'\', TypeID=\''.$type.'\', ';
	$sql='';
        $columnstoadd=array('Date','Particulars','Amount');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo='.$branchno.',  ApprovedByNo=\''.$_SESSION['(ak0)'].'\', ApprovedTS=Now(), EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now();'; 
	 // echo $sql; exit();
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:approval.php?w=Approved&Approval=".$approvalno);
	break;

case 'OPRequest':
	if (!allowedToOpen(5200,'1rtc')) {   echo 'No permission'; exit;}
	$sqlinsert='INSERT INTO `invty_7opapproval` Set ';
	$sql='';
        $columnstoadd=array('InvNo','Amount');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo='.$_SESSION['bnum'].', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now();'; 
	// echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:approval.php?w=LookupOP");
	break;

case 'ApproveOP':
	if (!allowedToOpen(522,'1rtc')) {   echo 'No permission'; exit;}
	$approvalno=mt_rand(1000,99999);
	//start of check if approval number has been used
	$sql0='Select Approval from invty_7opapproval where Approval='.$approvalno;
	$stmt=$link->query($sql0);
	while ($stmt->rowCount()>0):
		$approvalno=mt_rand(1000,99999);
		$sql0='Select Approval from invty_7opapproval where Approval='.$approvalno;
		$stmt=$link->query($sql0);
		endwhile;
	// end of check	
        $sql=''; $columnstoedit=array('Amount','OPClientName','OPClientMobile');
        foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	$sql='UPDATE `invty_7opapproval` Set '.$sql.' Approval='.$approvalno.',ApprovedByNo=\''.$_SESSION['(ak0)'].'\', ApprovalTS=Now() WHERE BranchNo='.$_REQUEST['BranchNo'].' AND InvNo='.$_REQUEST['InvNo']; 
	// echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:approval.php?w=OPApproved&Approval=".$approvalno);
	break;
        
case 'NewDD':
	if (allowedToOpen(521,'1rtc')) { 
	$bank=intval($_POST['Bank']);
	$clientno=getNumber('Client',addslashes($_POST['Client']));
        
	$sqlinsert='INSERT INTO approvals_2directdeposits Set Bank='.$bank.', ClientNo='.$clientno.', ';
	$sql='';
        $columnstoadd=array('DirectDepDate','Amount','RequestRemarks');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo='.$_SESSION['bnum'].', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now();'; 
	if($_SESSION['(ak0)']==1002){echo $sql;}
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:approval.php?w=LookupDD");
        } else { echo 'No permission'; exit; }
	break;

case 'DDApproval':
    
        if (allowedToOpen(5211,'1rtc')) { 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        if(!empty($_POST['Remarks'])){ $remarks=', `ApproveRemarks`=\''.addslashes($_POST['Remarks']).'\'';} else {$remarks='';}
        if($_POST['submit']==='Approve'){ $approvalno=mt_rand(1000,59999); $approved=1;} else { $approvalno='NULL'; $approved=-1; goto update;}
        // check if approval number is existing
        $sql0='SELECT Approval FROM `approvals_2directdeposits` WHERE Approval='.$approvalno;
        $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
        while ($stmt0->rowCount()>0){
            $approvalno=(($approved==1)?(mt_rand(1000,59999)):($approvalno+1));
            
        }
        // end of check	
	update:
	$sql='UPDATE approvals_2directdeposits Set Approval='.$approvalno.$remarks.', `Approved?`='.$approved.', ApprovedByNo=\''.$_SESSION['(ak0)'].'\', ApprovalTS=Now() WHERE TxnSubId='.$_REQUEST['TxnSubId']; 
	if($_SESSION['(ak0)']==1002){echo $sql;}
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:".$_SERVER['HTTP_REFERER']);
        } else { echo 'No permission'; exit; }

	break;

case 'RequestForFreightClientApproval': // Freight-Client Expenses
    if (!allowedToOpen(523,'1rtc')) { echo 'No permission'; exit; }
	$sqlinsert='INSERT INTO `approvals_2freightclients` Set ';
	$sql='';
        $columnstoadd=array('Date','Particulars','ForInvoiceNo','txntype','Amount', 'PriceFreightInclusive');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	$sql=$sqlinsert.$sql.' efswitch='.$_GET['efswitch'].',BranchNo='.$_SESSION['bnum'].', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now();'; 
	// echo $sql; 
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header('Location:/'.$url_folder.'/index.php?done=1');
	break;

case 'ApproveFreightClientExpense': // Approve FreightClient Expenses
    if (!allowedToOpen(206,'1rtc')) { echo 'No permission'; exit; }
	$approvalno=$_POST['Approval'];
	$sql0='Select Approval from `approvals_2freightclients` where Approval LIKE \''.$approvalno.'\'';
	$stmt=$link->query($sql0);
	if ($stmt->rowCount()>0){
		$approvalno=mt_rand(1000,99999);
		$sql0='Select Approval from approvals_2freightclients where Approval=concat('.$approvalno.'\'),"FC")';
		$stmt=$link->query($sql0);
	}
	$frombudgetof=comboBoxValue($link, ' `acctg_1budgetentities` ', 'Entity', $_POST['FromBudgetOf'], 'EntityID');	
	$sqlinsert='UPDATE `approvals_2freightclients` Set FromBudgetOf='.$frombudgetof.',Approval="'.$approvalno.'", ';
	$sql='';
        $columnstoadd=array('Amount');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	$sql=$sqlinsert.$sql.' ApprovedByNo=\''.$_SESSION['(ak0)'].'\', ApprovedTS=Now() where ApprovalID='.$_POST['ApprovalID']; 
	// echo $sql; exit();
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:approval.php?w=Approved&Approval=".$approvalno);
	break;

case 'SaleMain':
        if($_SESSION['bnum']==999){ $allowed=999;} else { $allowed=5971;}
        if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit;}
	//to check if editable
	include('../backendphp/functions/checkeditablemainacctg.php');
	//$acctid=700;//INCOME
	$branchno=getNumber('Branch',addslashes($_POST['Branch']));
        $sqlinsert='INSERT INTO `acctg_2salemain` SET BranchNo='.$branchno.', ';
        $sql='';
        $columnstoadd=array('Date','Remarks');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', Posted=0, PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	// echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select TxnID from `acctg_2salemain` where Date=\''.$_POST['Date'].'\' and BranchNo='.$branchno;
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	header("Location:addeditclientside.php?w=Sale&TxnID=".$result['TxnID']);
        break;

case 'CollectMain':
    if (!allowedToOpen(515,'1rtc')) { echo 'No permission'; exit; }
	//to check if editable
	include ('acctglayout/checkifacctgeditable.php');
        
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        $type=comboBoxValue($link, 'acctg_1collecttype', 'CollectTypeDesc', $_POST['Type'], 'CollectTypeID');
         //    echo $_POST['Type'].'<br>'.$type; break;   
	//direct deposit
        if($type==7){
			if($_POST['DateofCheck']>date('Y-m-d')){
				echo 'PDC\'s not allowed.';
				exit();
			}
			
		}
	if ($type==3){
		$sqldd='Select Approval, ClientNo, DirectDepDate, Amount, concat("DDApp ",Approval," - P",Amount) as Remarks, EncodedByNo from `approvals_2directdeposits` where Approval='.$_POST['CheckNo'].' AND `Approved?`=1 AND Approval IS NOT NULL';
		$stmt=$link->query($sqldd);
		$resultdd=$stmt->fetch();
		$clientno=$resultdd['ClientNo'];
		$dateofcheck=$resultdd['DirectDepDate'];
		$remarks=$resultdd['Remarks'];
		$recvdby=$resultdd['EncodedByNo'];
	} else {
       // if(!in_array($type,array(1,2,3,4,5))){$type=1;} 
	$clientno=getNumber('Client',addslashes($_POST['Client']));
        $sql0='SELECT Terms FROM `1clients` WHERE ClientNo='.$clientno;
        $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
        $addldays=($res0['Terms']<=1?0:7);
        if(($_POST['DateofCheck']=='') OR ($stmt0->rowCount()==0)){ $dateofcheck=date('Y-m-d'); } 
        elseif ($_POST['DateofCheck']>date('Y-m-d', strtotime(date('Y-m-d'). ' + '.($res0['Terms']+$addldays).' days'))){  // Terms plus 7 days
            echo '<title>Error!</title><h4><font color=red>The date of check is beyond terms.</font></h4></head>'
    . '<a href='.$_SERVER['HTTP_REFERER'].'>Go back</a>'; exit; }
        else { $dateofcheck=$_POST['DateofCheck'];}
	if($type==7){
		$remarks='';
		$counter=0;
		foreach($_POST['Remarks'] as $remark){
			if($counter==1){
				$remark=wordwrap($remark, 3, '-', true);
			}
			$remarks.=''.$remark.',';
			$counter++;
			
		};
		// exit();
		$remarks=substr($remarks, 0, -1);
		// echo $remarks; exit();
		
	}else{
	$remarks=$_POST['Remarks'];}
	$recvdby=$_POST['ReceivedBy'];
	}
	// end of direct deposit
         
	// to confirm if govt
	if($type==4){
            $sqlclient='Select VatTypeNo FROM `1clients` WHERE ClientNo='.$clientno;
            $stmt=$link->query($sqlclient); $resultclient=$stmt->fetch();
            if($resultclient['VatTypeNo']<>3) { header("Location:".$_SERVER['HTTP_REFERER']);}
        }
        
        
	$drid=(in_array($type,array(2,4,6))?'201':'100');
	
	$sqlinsert='INSERT INTO `acctg_2collectmain` SET ClientNo='.$clientno.', BranchSeriesNo='.$_SESSION['bnum'].', `DebitAccountID`=\''.$drid.'\', `Posted`=0, `DateofCheck`=\''.$dateofcheck.'\', Remarks="'.$remarks.'", ReceivedBy='.$recvdby.', Type='.$type.', ';
	// echo $sqlinsert; exit();
        if($_SESSION['(ak0)']==1002){echo $sqlinsert;}
	$sql='';
        $columnstoadd=array('CollectNo','Date','CheckBank','CheckNo','CheckBRSTN', 'ClientCheckBankAccountNo');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	if($_SESSION['(ak0)']==1002){echo $sql;}
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select TxnID from `acctg_2collectmain` where CollectNo=\''.$_POST['CollectNo'].'\' and BranchSeriesNo='.$_SESSION['bnum'];
        if($_SESSION['(ak0)']==1002){echo $sql;}
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
        
	if ($type==3){
	//Record into DD approval table
		$sql='UPDATE approvals_2directdeposits SET `TxnID` = '.$result['TxnID'].' WHERE `Approval` ='.$resultdd['Approval'];
		$stmt=$link->prepare($sql);
		$stmt->execute();
		//end record DD
	} 
	header("Location:addeditclientside.php?w=Collect&TxnID=".$result['TxnID']);
        break;

case 'DepMain':
    if (!allowedToOpen(514,'1rtc')) { echo 'No permission'; exit; }
	//to check if editableif ()
	include ('acctglayout/checkifacctgeditable.php');
	//to get dep number
		$depnoprefix=date('y').'-'.($_SESSION['bnum']==999?999:str_pad($_SESSION['bnum'],2,'0',STR_PAD_LEFT)).'-'.date('md').'-';

		// $sql='SELECT DepositNo FROM acctg_2depositmain where Left(DepositNo,'.($_SESSION['bnum']==999?'12':'11').')=\''.$depnoprefix.'\' order by DepositNo desc Limit 1;';
		$sql='SELECT DepositNo FROM acctg_2depositmain where Left(DepositNo,'.($_SESSION['bnum']>=100?'12':'11').')=\''.$depnoprefix.'\' order by DepositNo desc Limit 1;';

	    $stmt=$link->query($sql);
	    $result=$stmt->fetch();
	    if (is_null($result['DepositNo'])){
		$depno=$depnoprefix.'01';
	    } else {
		$depno=$depnoprefix.str_pad((substr($result['DepositNo'],-2)+1),2,'0',STR_PAD_LEFT);
	    }
	$sqlinsert='INSERT INTO `acctg_2depositmain` SET `DepositNo`=\''.$depno.'\', `Posted`=0, ';
        $sql='';
        $columnstoadd=array('Date','DebitAccountID','Remarks');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
        if ($_SESSION['(ak0)']==1002){ echo $sql; }
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select TxnID from `acctg_2depositmain` where DepositNo=\''.$depno.'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	header("Location:addeditdep.php?TxnID=".$result['TxnID']);
        break;

        
case 'BouncedfromCR': //working on adding DAteofinv
     if (!allowedToOpen(5931,'1rtc')) { echo 'No permission'; exit; }
	$txnid=$_REQUEST['TxnID'];
	if(isset($_GET['FromLastYr'])){
		$tableinsert='acctg_3undepositedpdcfromlastperiodbounced';
		$getlast='&fromlast=1'; $idn='UndepPDCId';
		// $sql0='SELECT GROUP_CONCAT(`Particulars`) AS Invoices, IFNULL(s.`SaleDate`,\'0000-00-00\') AS DateofFirstInv
        // FROM `acctg_2collectsub` cs LEFT JOIN `invty_2sale` s ON cs.ForChargeInvNo=s.SaleNo AND cs.BranchNo=s.BranchNo AND s.txntype=2 WHERE cs.TxnID='.$txnid;
		$sql0='SELECT `Particulars` AS Invoices, IFNULL(`SaleDate`,\'0000-00-00\') AS DateofFirstInv
        FROM `acctg_3undepositedpdcfromlastperiod` WHERE UndepPDCId='.$txnid;
	} else {
		$tableinsert='acctg_2collectsubbounced';
		$getlast=''; $idn='TxnID';
		$sql0='SELECT GROUP_CONCAT(`ForChargeInvNo`) AS Invoices, IFNULL(s.`Date`,\'0000-00-00\') AS DateofFirstInv
        FROM `acctg_2collectsub` cs LEFT JOIN `invty_2sale` s ON cs.ForChargeInvNo=s.SaleNo AND cs.BranchNo=s.BranchNo AND s.txntype=2 WHERE cs.TxnID='.$txnid;
	}
        
        $stmt=$link->prepare($sql0); $stmt->execute(); $res0=$stmt->fetch();
        if($stmt->rowCount()>0) { $remarks=$res0['Invoices']; $dateofinv=$res0['DateofFirstInv'];} else { $remarks='from last yr - pls change date of inv'; $dateofinv=''.$currentyr.'-01-01';}
		
	$sql='INSERT INTO `'.$tableinsert.'` (`'.$idn.'`,`DateBounced`,`CreditAccountID`,`Remarks`,DateofFirstInv,`TimeStamp`,`EncodedByNo`,`PostedByNo`,`Posted`)
VALUES ('.$txnid.',\''.$_GET['Bounced'].'\','.$_GET['CRID'].',\''.(empty($_GET['Remarks'])?$res0['Invoices']:$_GET['Remarks'].' '.$res0['Invoices']).'\',\''.$res0['DateofFirstInv'].'\',Now(),'.$_SESSION['(ak0)'].','.$_SESSION['(ak0)'].',1);
'; 
	if ($_SESSION['(ak0)']==1002){ echo $sql; }
        $stmt=$link->prepare($sql); $stmt->execute();
	
	header("Location:addeditclientside.php?w=BouncedfromCR".$getlast."&TxnID=".$txnid);
        break;

case 'Interbranch':
    if (!allowedToOpen(5951,'1rtc')) { echo 'No permission'; exit; }
	//to check if editable
	include('../backendphp/functions/checkeditablemainacctg.php');
	
	$sql='INSERT INTO `acctg_2txfrmain` SET `Date`=\''.$_POST['Date'].'\', `CreditAccountID`=300, `Posted`=0, FromBranchNo=\''.$_SESSION['bnum'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	// echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select TxnID from `acctg_2txfrmain` where `Date`=\''.$_POST['Date'].'\' and FromBranchNo=\''.$_SESSION['bnum'].'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	header("Location:addeditclientside.php?w=Interbranch&TxnID=".$result['TxnID']);
        break;
	
	
case 'PurchaseMain':
    if($_SESSION['bnum']==999){ $allowed=999;} else { $allowed=5962;}
        if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit;}
	//to check if editable
	include('../backendphp/functions/checkeditablemainacctg.php');
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        $branchno=getNumber('Branch',addslashes($_POST['Branch']));
	// to get supp no
	$suppno=getNumber('Supplier',addslashes($_POST['SupplierName']));
	$terms=getValue($link,'1suppliers','`SupplierName`',$_POST['SupplierName'],'Terms');
	if (!isset($_POST['RCompany']) or empty($_POST['RCompany'])){$co='';}
        else{$co='RCompany='.comboBoxValue ($link,'`1companies`','CompanyName',addslashes($_POST['RCompany']),'CompanyNo').', ';}
	//$drid=getNumber('Account',$_POST['DebitAccount']);DebitAccountID='.$drid.','Amount',
	$crid=getNumber('Account',$_POST['CreditAccount']);;
	$sqlinsert='INSERT INTO `acctg_2purchasemain` SET SupplierNo='.$suppno.',  `Posted`=0, Terms='.$terms.',  CreditAccountID='.$crid.', '.$co;
	$sql='';
        $columnstoadd=array('Date','SupplierInv','DateofInv','MRRNo','Remarks');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo='.$branchno.', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	// echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select TxnID from `acctg_2purchasemain` where SupplierInv=\''.$_POST['SupplierInv'].'\' and SupplierNo='.$suppno;
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	header("Location:addeditsupplyside.php?w=Purchase&TxnID=".$result['TxnID']);
        break;


case 'CVMain':
	if (!allowedToOpen(5401,'1rtc')) { echo 'No permission'; exit;} 
        //to check if editable
	include('../backendphp/functions/checkeditablemainacctg.php');
	// to get supp no
	$suppno=getNumber('Supplier',addslashes($_POST['Payee']));
	$suppno=($suppno>1?$suppno:'null');
	$crid=getNumber('Account',$_POST['CreditAccount']);
	$pmid=comboBoxValue($link,'acctg_0paymentmodes','PaymentMode',addslashes($_POST['PaymentMode']),'PaymentModeID');
	
	$sqlinsert='INSERT INTO `acctg_2cvmain` SET PaymentModeID='.$pmid.',PayeeNo='.$suppno.', Payee=\''.$_POST['Payee'].'\', `Posted`=0, CreditAccountID='.$crid.', ';
	$sql='';
        $columnstoadd=array('Date','DueDate','CVNo','CheckNo','DateofCheck','Remarks');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	//adjustments
	if($pmid==2){
		$sql.='ReleaseDate=CURDATE(),ReleaseDateByNo='.$_SESSION['(ak0)'].',ReleaseDateTS=NOW(),';
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	// echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	

	header("Location:addeditsupplyside.php?w=CV&CVNo=".$_POST['CVNo']);
        break;
case 'FutureCV':
        if (!allowedToOpen(5401,'1rtc')) { echo 'No permission'; exit;} 
	// ensure future year
	if(date('Y',  strtotime($_POST['Date']))<=$currentyr){header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); }
	if(isset($_POST['Posted']) and $_POST['Posted']<>0){header('Location:/'.$url_folder.'/forms/errormsg.php?err=Posted');}
	// to get supp no
	$suppno=getNumber('Supplier',addslashes($_POST['Payee']));
	$suppno=($suppno>1?$suppno:'null');
	$crid=getNumber('Account',$_POST['CreditAccount']);
	$pmid=comboBoxValue($link,'acctg_0paymentmodes','PaymentMode',addslashes($_POST['PaymentMode']),'PaymentModeID');
	$sqlinsert='INSERT INTO `acctg_4futurecvmain` SET PaymentModeID='.$pmid.',PayeeNo='.$suppno.', Payee=\''.$_POST['Payee'].'\', `Posted`=0, CreditAccountID='.$crid.', ';
	$sql='';
        $columnstoadd=array('Date','DueDate','CVNo','CheckNo','DateofCheck','Remarks');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	//adjustments
	if($pmid==2){
		$sql.='ReleaseDate=CURDATE(),ReleaseDateByNo='.$_SESSION['(ak0)'].',ReleaseDateTS=NOW(),';
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	// echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select CVNo from `acctg_4futurecvmain` where CVNo=\''.$_POST['CVNo'].'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	header("Location:addeditsupplyside.php?w=FutureCV&CVNo=".$result['CVNo']);
        break;

case 'JV':

    if (!allowedToOpen(5921,'1rtc')) { echo 'No permission'; exit;}
	
        $table='acctg_2jvmain'; 
				//to check if editable
				$date='JVDate';
                include('../backendphp/functions/checkeditablemainacctg.php');
	$sqlinsert='INSERT INTO `'.$table.'` SET  Posted=0, ';
	$sql='';
        $columnstoadd=array('JVDate','JVNo','Remarks');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	// echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	

	header("Location:addeditsupplyside.php?w=$whichqry&JVNo=".$_POST['JVNo']);
        break;

case 'Encashments':
        if (!allowedToOpen(5993,'1rtc')) { echo 'No permission'; exit; }
	$txnid=$_REQUEST['TxnID'];
	
	$sqlupdate='UPDATE `acctg_2depositmain` m join `acctg_2depencashsub` s on m.TxnID=s.TxnID Set ClearedEncash=1 where m.TxnID='.$txnid;
	
        $stmt=$link->prepare($sqlupdate);	$stmt->execute();
	header("Location:".$_SERVER['HTTP_REFERER']);
	
	break;

case 'DirectDeposit':
    if (!allowedToOpen(6002,'1rtc')) {   echo 'No permission'; exit;}
	$txnid=$_REQUEST['TxnID'];
	//check amounts
		$sqldd='Select Bank, Amount from `approvals_2directdeposits` where TxnID='.$txnid;
		$stmt=$link->query($sqldd);
		$resultdd=$stmt->fetch();		
	
	$sql='SELECT om.`Date`, om.CollectNo, CONCAT("C-",`om`.`BranchSeriesNo`,"-",`om`.`CollectNo`) AS `CRNo`, om.ClientNo, om.DebitAccountID as CreditAccountID, '.$resultdd['Bank'].' as DebitAccountID, os.BranchNo, sum(os.Amount)-IFNULL(sum(ord.Amount),0) as Amount, om.Remarks FROM `acctg_2collectmain` om join `acctg_2collectsub` os on om.TxnID=os.TxnID LEFT join `acctg_2collectsubdeduct` ord on om.TxnID=ord.TxnID where om.TxnID='.$txnid.' group by om.TxnID, os.BranchNo;'; if ($_SESSION['(ak0)']==1002){ echo $sql; }
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	
	if ($resultdd['Amount']<>$result['Amount']){
		//ECHO $resultdd['Amount'].'<br>'.$result['Amount'];break;	
		header("Location:addeditclientside.php?w=Collect&TxnID=".$txnid."&error=INCORRECTTOTAL"); break;
	} 
	//to check if editableif ()
	if (!allowedToOpen(5992,'1rtc')) {
	$datecondition=$_SESSION['nb4'];
	} else {
		$datecondition=$_SESSION['nb4A'];
	}
	if((addslashes($result['Date']))<$datecondition or date('Y',  strtotime($result['Date']))<>$currentyr){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 
		break;
	}
	//to get dep number  SAME AS DepMain!
		$depnoprefix=date('y').'-'.str_pad($_SESSION['bnum'],2,'0',STR_PAD_LEFT).'-'.date('md').'-';
		$sqldep='SELECT DepositNo FROM acctg_2depositmain where Left(DepositNo,11)=\''.$depnoprefix.'\' order by DepositNo desc Limit 1;';
	    $stmtdep=$link->query($sqldep);
	    $resultdep=$stmtdep->fetch();
	    if (is_null($resultdep['DepositNo'])){
		$depno=$depnoprefix.'01';
	    } else {
		$depno=$depnoprefix.str_pad((substr($resultdep['DepositNo'],-2)+1),2,'0',STR_PAD_LEFT);
	    }
	$sqlinsert='INSERT INTO `acctg_2depositmain` SET `DepositNo`=\''.$depno.'\', `Posted`=0, '; 
        $sql='';
        $columnstoadd=array('Date','DebitAccountID','Remarks');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$result[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	if ($_SESSION['(ak0)']==1002){ echo $sql; }
        $stmt=$link->prepare($sql);
	$stmt->execute();
	// end of add main
	$sqltxn='Select TxnID from `acctg_2depositmain` where DepositNo=\''.$depno.'\'';
	$stmttxn=$link->query($sqltxn);
	$resulttxn=$stmttxn->fetch();
	$txnid=$resulttxn['TxnID'];
	// start add sub SAME AS DepSubAdd  
	$sqlinsert='INSERT INTO `acctg_2depositsub` SET `TxnID`=\''.$txnid.'\', `Type`=1, `CRNo`=\''.$result['CRNo'].'\', ';
        $sql='';
        $columnstoadd=array('BranchNo','ClientNo','CreditAccountID','Amount');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$result[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()'; 
	//echo $sql;break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	// end of add sub
	header("Location:addeditdep.php?TxnID=".$txnid);
        
	break;
        
Case 'AutoVchBudget':
        $sql0='SELECT bl.*, DATE_FORMAT(bl.`TimeStamp`, "%Y/%m/%d") AS DateofRequest, CONCAT(e.FirstName," ",e.SurName) AS Requester, SUM(Amount) AS Amount, RCompanyNo, DATE_SUB(DateNeeded, INTERVAL 2 DAY) AS DateofCheck
FROM `approvals_3budgetandliq` bl LEFT JOIN `1employees` e ON e.IDNo=bl.EncodedByNo 
JOIN `approvals_3budgetrequestsub` brs ON bl.TxnID=brs.TxnID
JOIN `1branches` b ON b.BranchNo=bl.BranchNo
WHERE FundsAccepted=0 AND Approved=1 AND CashorCard=0 AND bl.TxnID='.$_REQUEST['TxnID'];
 $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
 $vchdate=strtotime($res0['DateNeeded']);
 include_once $path.'/acrossyrs/commonfunctions/lastnum.php'; 
 $vchno=lastNum('CVNo','acctg_2cvmain',((date('Y',$vchdate))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',$vchdate)).',2)')+1;
 $sqlinsert='INSERT INTO acctg_2cvmain SET CVNo='.$vchno.', CheckNo=CONCAT(\''.$res0['Requester'].'\',\''.$res0['DateNeeded'].'\'), Date=\''.date('Y-m-d').'\', DateofCheck=\''.(strtotime($res0['DateofCheck'])<strtotime($res0['DateNeeded'])?$res0['DateofCheck']:$res0['DateNeeded']).'\', PayeeNo='.$res0['EncodedByNo'].', Payee=\''.$res0['Requester'].'\', CreditAccountID=403, '
         . ' Remarks="Budget Request", TimeStamp=Now(), EncodedByNo='.$_SESSION['(ak0)'].', PostedByNo='.$_SESSION['(ak0)'];
 if($_SESSION['(ak0)']==1002){ echo $sqlinsert; }
      $stmt=$link->prepare($sqlinsert); $stmt->execute();      
   
   $sql='SELECT CVNo FROM acctg_2cvmain where CVNo='.$vchno;   $stmt=$link->query($sql);   $result=$stmt->fetch();   $txnid=$result['CVNo'];
   
   $sqlinsert='Insert into acctg_2cvsub SET CVNo='.$txnid.', DebitAccountID=206, Amount='.$res0['Amount'].', BranchNo='.$res0['BranchNo'].', TimeStamp=Now(), EncodedByNo='.$_SESSION['(ak0)']; 
      $stmt=$link->prepare($sqlinsert);       $stmt->execute();
      
     // Set as released
      $sql='UPDATE `approvals_3budgetandliq` SET FundsReleased=1, ReleasedByNo='.$_SESSION['(ak0)'].', ReleasedTS=Now() WHERE TxnID='.$_REQUEST['TxnID'];
      $stmt=$link->prepare($sql);	$stmt->execute();
      header('Location:/'.$url_folder.'/acctg/addeditsupplyside.php?w=CV&TxnID='.$txnid);
            break;
        
Case 'AutoVchLiq':
        $sql0='SELECT bl.*, CONCAT(e.FirstName," ",e.SurName) AS Requester, Nickname
FROM `approvals_3budgetandliq` bl LEFT JOIN `1employees` e ON e.IDNo=bl.EncodedByNo 
JOIN `approvals_3budgetliquidatesub` brs ON bl.TxnID=brs.TxnID
JOIN `1branches` b ON b.BranchNo=bl.BranchNo
WHERE ForLiqSubmission=1 AND bl.TxnID='.$_REQUEST['TxnID'];
 $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
 $vchdate=strtotime(''.$currentyr.'-12-31'); 
 $chkdate=($currentyr<>date('Y')?$currentyr.'-12-31':date('Y-m-d'));
 include_once $path.'/acrossyrs/commonfunctions/lastnum.php'; 
 $vchno=lastNum('CVNo','acctg_2cvmain',((date('Y',$vchdate))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',$vchdate)).',2)')+1;
 $sqlinsert='INSERT INTO acctg_2cvmain SET CVNo='.$vchno.', CheckNo=CONCAT(\''.$res0['Nickname'].'\',\''.$res0['DateNeeded'].'\'), Date=\''.$chkdate.'\', DateofCheck=\''.$chkdate.'\', PayeeNo='.$res0['EncodedByNo'].', Payee=\''.$res0['Requester'].'\', CreditAccountID=206, '
         . ' Remarks="Liquidation for Vch ", TimeStamp=Now(), EncodedByNo='.$_SESSION['(ak0)'].', PostedByNo='.$_SESSION['(ak0)'];
 if($_SESSION['(ak0)']==1002){echo $sqlinsert;}
     
      $stmt=$link->prepare($sqlinsert); $stmt->execute();      
   
   $sql='SELECT CVNo FROM acctg_2cvmain where CVNo='.$vchno;   $stmt=$link->query($sql);   $result=$stmt->fetch();   $txnid=$result['CVNo'];
   
   $sqlinsert='INSERT INTO `acctg_2cvsub` (`CVNo`,`Particulars`,`TIN`,`DebitAccountID`,`Amount`,`TimeStamp`,`BranchNo`,`EncodedByNo`) '
           . 'SELECT '.$txnid.', CONCAT(Payee," " ,`Particulars`), TIN, 100, Amount, Now(), BranchNo, '.$_SESSION['(ak0)']
           . ' FROM `approvals_3budgetandliq` bl JOIN `approvals_3budgetliquidatesub` bls ON bl.TxnID=bls.TxnID WHERE CashorCard=0 AND bl.TxnID='.$_REQUEST['TxnID'];
      if($_SESSION['(ak0)']==1002){echo $sqlinsert;}     
      $stmt=$link->prepare($sqlinsert);       $stmt->execute();

      header('Location:/'.$url_folder.'/acctg/addeditsupplyside.php?w=CV&TxnID='.$txnid);
            break;
        
        }
        
     $link=null; $stmt=null;
?>