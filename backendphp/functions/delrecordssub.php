<?php 
$path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;


$prog=$_REQUEST['l'];
if ($prog=='acctg'){ $closedbydate=$_SESSION['nb4A'];}else{ $closedbydate=$_SESSION['nb4'];}
//$datefield=!isset($datefield)?'Date':$datefield;
$txnidfield=!isset($txnidfield)?'TxnID':$txnidfield;
$txnsubidfield=!isset($txnsubidfield)?'TxnSubId':$txnsubidfield;
include_once('../../'.$prog.'/trail'.$prog.'.php');
$txnid=intval($_REQUEST['TxnID']); $txnsubid=$_REQUEST['TxnSubId']; 
$table=strtolower($_REQUEST['w']); $main=!isset($_GET['m'])?($prog=='invty'?str_replace('sub','',$table):str_replace('sub','main',$table)):$_GET['m'];

switch($main){
	
	case'acctg_2salemain':
		if (!allowedToOpen(20001,'1rtc')) { echo 'No permission'; exit();}
		$datefield='Date';
	break;
	
	case'acctg_2collectmain':
		if (!allowedToOpen(20002,'1rtc')) { echo 'No permission'; exit();}
		$datefield='Date';
	break;
	
	case'acctg_2bouncedmain':
		if (!allowedToOpen(20003,'1rtc')) { echo 'No permission'; exit();}
		$datefield='Date';
	break;
	
	case'acctg_2depositmain':
		if (!allowedToOpen(20004,'1rtc')) { echo 'No permission'; exit();}
		$datefield='Date';
	break;
	
	case'acctg_2cvmain':
		if (!allowedToOpen(20005,'1rtc')) { echo 'No permission'; exit();}
		$datefield='Date';
		$txnidfield='CVNo'; 
	break;
	
	case'acctg_4futurecvmain':
		if (!allowedToOpen(20006,'1rtc')) { echo 'No permission'; exit();}
		$datefield='Date';
		$txnidfield='CVNo'; 
	break;
	
	case'acctg_2purchasemain':
		if (!allowedToOpen(20007,'1rtc')) { echo 'No permission'; exit();}
		$datefield='Date';
	break;
	
	case'acctg_2txfrmain':
		if (!allowedToOpen(20008,'1rtc')) { echo 'No permission'; exit();}
		$datefield='Date';
	break;
	
	case'acctg_2jvmain':
		if (!allowedToOpen(20009,'1rtc')) { echo 'No permission'; exit();}
		$datefield='JVDate';
		$txnidfield='JVNo';
	break;
	
	case'invty_2mrr':
		if (!allowedToOpen(20011,'1rtc')) { echo 'No permission'; exit();}
		$datefield='Date';
	break;
	
	case'invty_2sale':
		if (!allowedToOpen(20012,'1rtc')) { echo 'No permission'; exit();}
		$datefield='Date';
	break;
	
    case 'invty_2transfer': 
		if (!allowedToOpen(20013,'1rtc')) { echo 'No permission'; exit();}
		$datefield='DateOUT'; 
	break;
	
	case'invty_4adjust':
		if (!allowedToOpen(20014,'1rtc')) { echo 'No permission'; exit();}
		$datefield='Date';
	break;
	
	case'acctg_2provmain':
		if (!allowedToOpen(20016,'1rtc')) { echo 'No permission'; exit();}
		$datefield='Date';
	break;
	
	case'acctg_2collectsubbounced':
		if (!allowedToOpen(20017,'1rtc')) { echo 'No permission'; exit();}
		$datefield='DateBounced';
	break;
	
	case'acctg_3undepositedpdcfromlastperiodbounced':
		if (!allowedToOpen(20018,'1rtc')) { echo 'No permission'; exit();}
		$datefield='DateBounced'; 
		$txnidfield='UndepPDCId'; 
	break;
	
	default : $datefield='Date';
	
}
        recordtrail($txnsubid,$table,$link,1);
        
	$sql='Delete from `'.$table.'` WHERE `'.$txnsubidfield.'`='.$txnsubid.' AND `'.$txnidfield.'` '
                . ' IN (SELECT `'.$txnidfield.'` FROM `'.strtolower($main).'` WHERE Posted=0 AND `'.$datefield.'`>\''.$closedbydate.'\');';
        if($_SESSION['(ak0)']==1002){ echo '<br>'.$sql;}
	$stmt=$link->prepare($sql); $stmt->execute();
		//spec price
				if($table=='invty_2salesub'){
					$sqlchecker='select * from invty_2salesub where TxnSubId=\''.$txnsubid.'\'';
					$stmtchecker=$link->query($sqlchecker);
					if($stmtchecker->rowCount()==0){
						$sqldelete='delete from invty_7specdisctapproval where TxnID='.$_GET['TxnID'].' and Approved=0';
						// echo $sqldelete; exit();
						$stmtdelete=$link->prepare($sqldelete); $stmtdelete->execute();
					}
				}
			//
	
         header("Location:".$_SERVER['HTTP_REFERER']);

?>