<?php
function recordtrail($txnid,$table,$linkinfunction,$editordel){
global $currentyr;
//$currentyr='2020';
//0 edit, 1 delete, 2 record sub for deletions of main
if((strpos($table,'sub') !== false) OR ($editordel==2)){ goto sub;}
switch($table){
    case 'invty_2mrr':
        $ctlno='MRRNo'; $suppclientno='SupplierNo'; $forpono='ForPONo'; 
        $remarks='CONCAT("Inv",SuppInvNo," ",SuppInvDate," ",Terms," days ", IFNULL(CONCAT("RCo:",RCompany),"")," Acctg:",SenttoAcctg,IFNULL(Remarks,""))';
        break;
    case 'invty_2sale':
        $ctlno='SaleNo'; $suppclientno='ClientNo'; $forpono='PONo'; 
        $remarks='CONCAT("PayType ",PaymentType," ",CheckDetails," ",DateofCheck," SoldBy:",SoldByNo," TL:",IFNULL(CONCAT(TeamLeader," "),""),IFNULL(Remarks,""))';
        break;
    case 'invty_2transfer':
        $ctlno='TransferNo'; $suppclientno='CONCAT("To:",ToBranchNo)'; $forpono='ForRequestNo'; $ts='FROMTimeStamp'; $encby='FROMEncodedByNo'; $datefield='DateOUT';
        $remarks='CONCAT(IFNULL(CONCAT(Remarks," "),""),IF(ISNULL(DateIN),"",CONCAT("DateIn ", DateIN, " InBy ",TOEncodedByNo, " TS ", TOTimeStamp)),'
                . '" ReqTxnID ", ReqTxnID, IFNULL(CONCAT(Waybill," "),""))'; 
        break;
    case 'invty_4adjust':
        $ctlno='AdjNo'; $suppclientno='""'; $forpono='""'; $txntype='AdjType';$remarks='""';
        break;
	case 'invty_2pr':
        $ctlno='PRNo'; $suppclientno='SupplierNo'; $forpono='""'; 
		$remarks='CONCAT(IFNULL(CONCAT("RCo:",RCompany),"")," Acctg:",SenttoAcctg,IFNULL(Remarks,""))';
        break;
}

$branchno=!isset($branchno)?'BranchNo':$branchno; $txntype=!isset($txntype)?'txntype':$txntype; $remarks=!isset($remarks)?'Remarks':$remarks;
$ts=!isset($ts)?'TimeStamp':$ts; $encby=!isset($encby)?'EncodedByNo':$encby; $datefield=!isset($datefield)?'`Date`':$datefield;
$sqltrail='INSERT INTO `'.$currentyr.'_trail`.`invtytxnsmain` SELECT \''.$table.'\' AS `whichtable`, `TxnID`, 
        '.$datefield.' AS `Date`, '.$ctlno.' AS `ControlNo`, '.$suppclientno.' AS `SuppNo/ClientNo`, `'.$branchno.'` AS `BranchNo`, `'.$txntype.'` AS `txntype`, '
        .$forpono.' AS `ForPO/Request`, '.$remarks.' AS `Remarks`, '.$encby.', `'.$ts.'`, `PostedByNo`,'
        .$editordel.' AS EditOrDel, '.$_SESSION['(ak0)'].' AS `EditOrDelByNo`, Now() FROM `'.$table.'` WHERE `TxnID`='.$txnid;
goto skipsub;

sub: 
switch($table){
    case 'invty_2mrrsub':
        $ctlno='MRRNo'; $remarks='""'; $price='""';
        break;
    case 'invty_2salesub':
        $ctlno='SaleNo'; $remarks='""'; $cost='""'; 
        break;
    case 'invty_2transfersub':
        $ctlno='TransferNo'; $qty='QtySent'; $ts='s.FROMTimeStamp'; $encby='s.FROMEncodedByNo';
        $remarks='CONCAT(IFNULL(CONCAT("Recvd ",QtyReceived," "),""),IF(ISNULL(DateIN),"",CONCAT("InBy ",s.TOEncodedByNo, " TS ", s.TOTimeStamp)))'; 
        break;
    case 'invty_4adjustsub':
        $ctlno='AdjNo'; $cost='""'; $price='UnitPrice'; $remarks='Remarks';
        break;
	case 'invty_2prsub':
        $ctlno='PRNo'; $remarks='CONCAT ("EncBy ",DecisionEncByNo, " TS ", DecisionTS)'; $price='""';
        break;
    
}
$main=!isset($main)?str_replace('sub','',$table):$main;   
$cost=!isset($cost)?'`UnitCost`':$cost; $price=!isset($price)?'`UnitPrice`':$price; $amt=!isset($amt)?'`Amount`':$amt;
$encby=!isset($encby)?'s.EncodedByNo':$encby; $ts=!isset($ts)?'s.`TimeStamp`':$ts;
$qty=!isset($qty)?'s.Qty':$qty;

if ($editordel==2){ //record sub for deletions of main
    $sqltrail='INSERT INTO `'.$currentyr.'_trail`.`invtytxnssub` SELECT \''.$table.'\' AS `whichtable`, m.`TxnID`,`TxnSubId`,
        '.$ctlno.' AS `ControlNo`, '.$remarks.' AS `Remarks`, `ItemCode`, '.$qty.' AS `Qty`,'.$cost.' AS `UnitCost`, '.$price.' AS `UnitPrice`,
`SerialNo`,`Defective`,  '.$encby.', '.$ts.', '
        .$editordel.' AS EditOrDel, '.$_SESSION['(ak0)'].' AS `EditOrDelByNo`, Now() FROM `'.$main.'` m JOIN `'.$table.'` s ON m.`TxnID`=s.`TxnID` WHERE s.`TxnID`='.$txnid;
} else {
$sqltrail='INSERT INTO `'.$currentyr.'_trail`.`invtytxnssub` SELECT \''.$table.'\' AS `whichtable`, m.`TxnID`,`TxnSubId`,
        '.$ctlno.' AS `ControlNo`, `ItemCode`, '.$qty.' AS `Qty`,'.$cost.' AS `UnitCost`, '.$price.' AS `UnitPrice`,
`SerialNo`, '.$remarks.' AS `Remarks`,`Defective`,  '.$encby.', '.$ts.', '
        .$editordel.' AS EditOrDel, '.$_SESSION['(ak0)'].' AS `EditOrDelByNo`, Now() FROM `'.$main.'` m JOIN `'.$table.'` s ON m.`TxnID`=s.`TxnID` WHERE `TxnSubId`='.$txnid;    
} 
    
skipsub:
    if($_SESSION['(ak0)']==1002){ echo $sqltrail;}
$stmttrail=$linkinfunction->prepare($sqltrail); $stmttrail->execute();
 $link=null; $stmt=null;
}

function recordtrailallsub($txnid,$table,$link){
global $currentyr;
$subtable=$table.'sub';
recordtrail($txnid,$subtable,$link,2);

}
?>