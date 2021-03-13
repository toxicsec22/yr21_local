<?php 
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
$path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;


$prog=$_REQUEST['l'];
if ($prog=='acctg'){ $closedbydate=$_SESSION['nb4A'];}else{ $closedbydate=$_SESSION['nb4'];}
$txnidfield=!isset($txnidfield)?'TxnID':$txnidfield;
include_once('../../'.$prog.'/trail'.$prog.'.php');
$txnid=intval($_REQUEST['TxnID']); 
$table=strtolower($_REQUEST['w']);
$subtable='';

switch ($table) {
	
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
		$subtable='acctg_2jvsub';
	break;
	
	case'invty_2mrr':
		if (!allowedToOpen(20011,'1rtc')) { echo 'No permission'; exit();}
		$datefield='Date';
	break;
	
	case'invty_2sale':
		if (!allowedToOpen(20012,'1rtc')) { echo 'No permission'; exit();}
		$datefield='Date';
		
		// special condition 
            $sql1='SELECT * FROM invty_7opapproval WHERE TxnID='.$txnid; 
            $stmt1=$link->query($sql1); $res1=$stmt1->fetch();
            if($stmt1->rowCount()>0){ echo 'Please delete OP approval first.'; exit();}
        // end of condition
		
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
    
        recordtrail($txnid,$table,$link,1);
        recordtrailallsub($txnid,$table,$link);
        if ($table=='acctg_2purchasemain') { $stmt=$link->prepare('UPDATE `invty_2mrr` SET `SenttoAcctg`=0 WHERE MRRNo LIKE (SELECT MRRNo FROM `acctg_2purchasemain` WHERE TxnID='.$txnid.')'); $stmt->execute();
	//addedpr
		$sqlpr='UPDATE `invty_2pr` SET `SenttoAcctg`=0 WHERE PRNo LIKE (SELECT MRRNo FROM `acctg_2purchasemain` WHERE TxnID='.$txnid.')';
		$stmtpr=$link->prepare($sqlpr); $stmtpr->execute();
	//
		}
    
	//delete sub first before main
	if($subtable<>''){
		$sqlsub='Delete '.$subtable.'.* from `'.$subtable.'` JOIN '.$table.' ON '.$subtable.'.'.$txnidfield.'='.$table.'.'.$txnidfield.' WHERE Posted=0 AND `'.$datefield.'`>\''.$closedbydate.'\' AND '.$subtable.'.`'.$txnidfield.'`='.$txnid;
		// echo $sqlsub;
		$stmtsub=$link->prepare($sqlsub); $stmtsub->execute();
	}

	//main	
	$sql='Delete from `'.$table.'` WHERE Posted=0 AND `'.$datefield.'`>\''.$closedbydate.'\' AND `'.$txnidfield.'`='.$txnid;

        if($_SESSION['(ak0)']==1002){ echo '<br><br>'.$sql.'<br><br>'.strrchr($table,'_').str_replace('main','',substr(strrchr($table,'_'),2));}
	$stmt=$link->prepare($sql); $stmt->execute();
        
        switch ($table){
        case 'invty_4adjust': header('Location:../../audit/lookupaudit.php?w=Adjust'); break;
        case 'invty_2mrr': header('Location:../../invty/txnsmrrperday.php?w=MRR&perday=0'); break;
        case 'invty_2transfer': header('Location:../../invty/txnsinterperday.php?w=Transfers&perday=0'); break;  
        case 'acctg_2collectsubbounced': header('Location:../../acctg/txnsperday.php?w=BouncedfromCR'); break;
        case 'acctg_3undepositedpdcfromlastperiodbounced': header('Location:../../acctg/txnsperday.php?w=Bounced'); break;
        default:
        header('Location:../../'.$prog.'/txnsperday.php?perday=0&w='.ucfirst(str_replace('main','',substr(strrchr($table,'_'),2))));
            break;
        }
?>