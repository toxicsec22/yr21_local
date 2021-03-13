<?php
        $path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	if (!allowedToOpen(6246,'1rtc')) {   echo 'No permission'; exit;}      
	include_once('../switchboard/contents.php');
	 
   
$title='Import Bank Txns';
$colnames=array('AccountID','TxnDate','Particulars','BankBranch','CheckNo','BankTransCode','WithdrawAmt','DepositAmt','Balance','Remarks');
$requiredcol=array('AccountID','TxnDate');
$required='';  foreach($requiredcol as $req){ $required=$required.'<li>'.$req.'</li>'; }
$allowed=''; foreach($colnames as $col){ $allowed=$allowed.'<li>'.$col.'</li>'; }
$specific_instruct='<i>Required columns</i><ol>'.$required.'</ol><br><i>Allowed column titles</i><ol>'.$allowed.'</ol>';
$tblname='banktxns_banktxns'; $firstcolumnname='AccountID';
$DOWNLOAD_DIR="../../uploads/";
include('../backendphp/layout/uploaddata.php');
 $link=null; $stmt=null;
?>