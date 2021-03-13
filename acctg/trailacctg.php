<?php
function recordtrail($txnid,$table,$linkinfunction,$editordel){
global $currentyr;
//$currentyr='2020';
//0 edit, 1 delete, 2 record sub for deletions of main
if((strpos($table,'sub') !== false) OR (strpos($table,'fromlastperiodbounced') !== false) OR ($editordel==2)){ goto sub;}
switch($table){
    case 'acctg_2jvmain':
        $ctlno='JVNo'; $suppclientno='""'; $suppclient='""'; $particulars='Remarks'; $acctid='""'; $drcr='1'; $txnidname='JVNo'; $datefield='JVDate';
        break;
    case 'acctg_2collectmain':
        $ctlno='CollectNo'; $suppclientno='ClientNo'; $suppclient='CONCAT("RcvdBy ",`ReceivedBy`)'; 
        $particulars='CONCAT("Check#",CheckNo," dated ",DateofCheck," Series ",`BranchSeriesNo`, " Type ",`Type`," ",Remarks)';
        $acctid='DebitAccountID'; $drcr='1'; $txnidname='TxnID';
        break;
    
    case 'acctg_2depositmain':
        $ctlno='DepositNo'; $suppclientno='""'; $suppclient='CONCAT("Cleared ",`Cleared`)'; $particulars='Remarks';
        $acctid='DebitAccountID'; $drcr='1'; $txnidname='TxnID';
        break;
    case 'acctg_4futurecvmain':
        $ctlno='CVNo'; $suppclientno='PayeeNo'; $suppclient='Payee'; 
        $particulars='CONCAT("Check#",CheckNo," dated ",DateofCheck," ",Remarks)';
        $acctid='CreditAccountID'; $drcr='-1'; $txnidname='CVNo';
        break;
    case 'acctg_2cvmain':
        $ctlno='CVNo'; $suppclientno='PayeeNo'; $suppclient='Payee'; 
        $particulars='CONCAT("Check#",CheckNo," dated ",DateofCheck," ",Remarks," cleared ",Cleared)';
        $acctid='CreditAccountID'; $drcr='-1'; $txnidname='CVNo';
        break;
    case 'acctg_2purchasemain':
        $ctlno='SupplierInv'; $suppclientno='SupplierNo'; $suppclient='CONCAT("MRR ",MRRNo," RCo ",RCompany)'; 
        $particulars='CONCAT("DateofInv",DateofInv," Terms ",Terms," ",Remarks)';
        $acctid='CreditAccountID'; $drcr='-1'; $txnidname='TxnID';
        break;
    case 'acctg_2salemain':
        $ctlno='CONCAT(Date," Branch ",BranchNo)'; $suppclientno='""'; $suppclient='CONCAT("TeamLeader ",TeamLeader)'; 
        $particulars='Remarks'; $acctid='""'; $drcr='1'; $txnidname='TxnID';
        break;
    case 'acctg_2txfrmain':
        $ctlno='CONCAT("FromBranch ",FromBranchNo)'; $suppclientno='""'; $suppclient='""'; 
        $particulars='Remarks'; $acctid='CreditAccountID'; $drcr='1'; $txnidname='TxnID';
        break;
}


$sqltrail='INSERT INTO `'.$currentyr.'_trail`.`acctgtxnsmain` SELECT \''.$table.'\' AS `whichtable`, '.$txnidname.' AS `TxnID`, 
        `'.(!isset($datefield)?'Date':$datefield).'`, '.$ctlno.' AS `ControlNo`, '.$suppclientno.' AS `SuppNo/ClientNo`, '.$suppclient.' AS `Supplier/Customer/Branch`,
        '.$particulars.' AS `Particulars`, '.$acctid.' AS `AccountID`, "'.$drcr.'" AS `DRCR`, EncodedByNo, `TimeStamp`, `PostedByNo`,'
        .$editordel.' AS EditOrDel, '.$_SESSION['(ak0)'].' AS `EditOrDelByNo`, Now() FROM `'.$table.'` WHERE `'.$txnidname.'`='.$txnid;
goto skipsub;

sub: 
switch($table){
    case 'acctg_2jvsub':
        $ctlno='s.JVNo'; $particulars='IFNULL(Particulars,"")'; $txnsubid='s.JVNo'; $txnidname='JVNo';
        break;
    case 'acctg_2collectsub':
        $ctlno='CollectNo'; $particulars='CONCAT(ForChargeInvNo, " ",OtherORDetails)'; $txnidname='TxnID';
        break;
    case 'acctg_2collectsubbounced':
        $ctlno='s.TxnID'; $particulars='CONCAT("Date Bounced",DateBounced, " Bank ",CreditAccountID)'; $txnidname='TxnID';
        $txnsubid='s.TxnID'; $main='acctg_2collectmain'; 
        $amt='""'; $branchno='""';
        break;
    case 'acctg_2collectsubdeduct':
        $ctlno='CollectNo'; $particulars='DeductDetails';  $main='acctg_2collectmain'; $dr='s.DebitAccountID'; $cr=0; $txnidname='TxnID';
        break;
    
    case 'acctg_2depositsub':
        $ctlno='DepositNo'; 
        $particulars='CONCAT(IFNULL(CONCAT("Client ",ClientNo, " "),""), IFNULL(CONCAT(DepDetails," "),""), IFNULL(CONCAT("Inv ",ForChargeInvNo," "),""), '
                . 'IFNULL(CONCAT(" CheckNo ",CheckNo),"")," Type ",Type)'; 
                $txnidname='TxnID';
        break;
    case 'acctg_2depencashsub':
        $ctlno='DepositNo'; $particulars='CONCAT(IFNULL(CONCAT("TypeID ",TypeID, " "),""), IFNULL(CONCAT(EncashDetails," "),""), '
                . 'IFNULL(CONCAT(" App ",ApprovalNo," "),""),IFNULL(CONCAT(" TIN ",TIN),""))'; 
        $main='acctg_2depositmain'; $dr='s.DebitAccountID'; $cr=0; $txnidname='TxnID';
        break;
    case 'acctg_4futurecvsub':
        $ctlno='s.CVNo'; $particulars='CONCAT(IFNULL(CONCAT(Particulars," "),""),IFNULL(CONCAT("Inv ",ForInvoiceNo," "),""), IFNULL(CONCAT(" TIN ",TIN),""))'; 
        $txnsubid='s.TxnSubId'; $txnidname='CVNo';
        break;
    case 'acctg_2cvsub':
        $ctlno='s.CVNo'; $particulars='CONCAT(IFNULL(CONCAT(Particulars," "),""),IFNULL(CONCAT("Inv ",ForInvoiceNo," "),""), IFNULL(CONCAT(" TIN ",TIN),""))'; 
        $txnsubid='s.TxnSubId'; $txnidname='CVNo';
        break;
    case 'acctg_2purchasesub':
        $ctlno='SupplierInv'; $particulars='""'; $txnidname='TxnID';
        break;
    case 'acctg_2salesub':
        $ctlno='CONCAT(Date," Branch ",BranchNo)'; $particulars='CONCAT("Client ",ClientNo, " ", Particulars)'; $txnidname='TxnID';
        break;
    case 'acctg_2txfrsub':
        $ctlno='CONCAT("FromBranch ",FromBranchNo)'; $branchno='ClientBranchNo'; $encby='OUTEncodedByNo'; $ts='OUTTimeStamp'; $txnidname='TxnID';
        $particulars='CONCAT(IFNULL(CONCAT(Particulars," "),""),IFNULL(CONCAT(s.Remarks," "),""),IF(ISNULL(DateIN),"",CONCAT("DateIn ", DateIN, " InBy ",INEncodedByNo, " TS ", INTimeStamp)),IF(ISNULL(DatePaid),"",CONCAT("DatePd ", DatePaid, " via ",PaidViaAcctID)))'; 
        break;
}
$main=!isset($main)?str_replace('sub','main',$table):$main;   $branchno=!isset($branchno)?'BranchNo':$branchno;
$dr=!isset($dr)?'`DebitAccountID`':$dr; $cr=!isset($cr)?'`CreditAccountID`':$cr; $amt=!isset($amt)?'`Amount`':$amt;
$encby=!isset($encby)?'s.EncodedByNo':$encby; $ts=!isset($ts)?'s.`TimeStamp`':$ts;
$txnsubid=!isset($txnsubid)?'TxnSubId':$txnsubid;

if ($editordel==2){ //record sub for deletions of main
    $sqltrail='INSERT INTO `'.$currentyr.'_trail`.`acctgtxnssub` SELECT \''.$table.'\' AS `whichtable`, m.`'.$txnidname.'`,'.$txnsubid.',
        '.$ctlno.' AS `ControlNo`, '.$particulars.' AS `Particulars`, '.$dr.', '.$cr.', '.$amt.', '.$branchno.', '.$encby.', '.$ts.', '
        .$editordel.' AS EditOrDel, '.$_SESSION['(ak0)'].' AS `EditOrDelByNo`, Now() FROM `'.$main.'` m JOIN `'.$table.'` s ON m.`'.$txnidname.'`=s.`'.$txnidname.'` WHERE s.`'.$txnidname.'`='.$txnid;
} else {
    if(!isset($sqltrail)){
		if($table=='acctg_3undepositedpdcfromlastperiodbounced'){
			$sqltrail='INSERT INTO `'.$currentyr.'_trail`.`acctgtxnssub` SELECT "acctg_3undepositedpdcfromlastperiodbounced" AS `whichtable`, m.`UndepPDCId`,s.UndepPDCId,"" AS `ControlNo`, CONCAT("Date Bounced",DateBounced, " Bank ",CreditAccountID) AS `Particulars`,"" AS `DebitAccountID`, `CreditAccountID`, `AmountOfPDC`, BranchNo, s.EncodedByNo, s.`TimeStamp`, 0 AS EditOrDel, 1002 AS `EditOrDelByNo`, Now() FROM `acctg_3undepositedpdcfromlastperiod` m JOIN `acctg_3undepositedpdcfromlastperiodbounced` s ON m.`UndepPDCId`=s.`UndepPDCId` WHERE s.UndepPDCId='.$txnid; 
		} else {
		$sqltrail='INSERT INTO `'.$currentyr.'_trail`.`acctgtxnssub` SELECT \''.$table.'\' AS `whichtable`, m.`'.$txnidname.'`,'.$txnsubid.',
        '.$ctlno.' AS `ControlNo`, '.$particulars.' AS `Particulars`, '.$dr.', '.$cr.', '.$amt.', '.$branchno.', '.$encby.', '.$ts.', '
        .$editordel.' AS EditOrDel, '.$_SESSION['(ak0)'].' AS `EditOrDelByNo`, Now() FROM `'.$main.'` m JOIN `'.$table.'` s ON m.`'.$txnidname.'`=s.`'.$txnidname.'` WHERE '.$txnsubid.'='.$txnid;
		}
    } 
} 

skipsub:
    if($_SESSION['(ak0)']==1002){ echo $sqltrail;}
$stmttrail=$linkinfunction->prepare($sqltrail); $stmttrail->execute(); $stmttrail=null; 
}

function recordtrailallsub($txnid,$table,$linkinfunction){
global $currentyr;

if (in_array($table,array('acctg_2jvmain','acctg_2jvmainforex','acctg_2cvmain','acctg_4futurecvmain','acctg_2purchasemain','acctg_2salemain','acctg_2txfrmain'))){
    $subtable=str_replace('main','sub',$table);
    recordtrail($txnid,$subtable,$linkinfunction,2);
} else {
    switch($table){
        case 'acctg_2depositmain':
            recordtrail($txnid,'acctg_2depositsub',$linkinfunction,2);
            recordtrail($txnid,'acctg_2depencashsub',$linkinfunction,2);
            break;
        case 'acctg_2collectmain':
            recordtrail($txnid,'acctg_2collectsub',$linkinfunction,2);
            recordtrail($txnid,'acctg_2collectsubdeduct',$linkinfunction,2);
            break;
        
    }
}
}
?>