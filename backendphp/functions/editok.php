<?php
function editOk($table,$txnid,$linkinfunction,$txntype){
    global $currentyr;
    if(in_array($table,array('acctg_2salemain','acctg_2collectmain','acctg_2txfrmain','acctg_2depositmain','acctg_2purchasemain','acctg_2cvmain','acctg_2jvmain'))){
    $closedate=(!allowedToOpen(300,'1rtc'))?$_SESSION['nb4']:$_SESSION['nb4A'];    
    } else { $closedate=$_SESSION['nb4']; }
    switch ($table){
        case 'invty_2transfer':
            $sqlmain='SELECT s.`DateOUT`, s.`DateIN`,s.Posted, s.PostedIn, ToBranchNo, BranchNo FROM `'.$table.'` s WHERE TxnId='.$txnid;
            break;
        case 'audit_2countmain':
        case 'audit_2toolscountmain':
        case 'audit_2countfreonmain':
        case 'audit_3vacuum':
            $sqlmain='SELECT s.`Date`, s.Posted FROM `'.$table.'` s WHERE CountID='.$txnid;
            $date='Date';
            break;
        case 'audit_2countcash':
            $sqlmain='SELECT s.`DateCounted`, s.Posted FROM `'.$table.'` s WHERE CashCountID='.$txnid;
            $date='DateCounted';
            break;
        case 'acctg_2collectsubbounced':
            $sqlmain='SELECT s.`DateBounced`, s.Posted FROM `'.$table.'` s WHERE TxnID='.$txnid;
            $date='DateBounced';
            break;
		case 'acctg_3undepositedpdcfromlastperiodbounced':
            $sqlmain='SELECT s.`DateBounced`, s.Posted FROM `'.$table.'` s WHERE UndepPDCId='.$txnid;
            $date='DateBounced'; 
            break;
        case 'acctg_2cvmain':
        case 'acctg_4futurecvmain':
            if(allowedToOpen(5432,'1rtc')){ // treasury
                $sqlmain='SELECT s.`Date`, s.Posted AS `Posted` FROM `'.$table.'` s WHERE APVPosted=1 AND (CreditAccountID=403 OR CreditAccountID IN (SELECT AccountID FROM banktxns_1maintaining)) AND CVNo='.$txnid;
            } else { //acctg
                $sqlmain='SELECT s.`Date`, s.APVPosted AS `Posted` FROM `'.$table.'` s WHERE s.Posted=0 AND (CreditAccountID NOT IN (SELECT AccountID FROM banktxns_1maintaining)) AND CVNo='.$txnid;
            }
            $date='Date';
            break;
        case 'acctg_2jvmain':
            $sqlmain='SELECT s.`JVDate`, s.Posted FROM `'.$table.'` s WHERE JVNo='.$txnid;
            $date='JVDate';
            break;
	
        default:
            $sqlmain='SELECT s.`Date`, s.Posted FROM `'.$table.'` s WHERE TxnID='.$txnid;
            $date='Date';
            break;
    }
    
       
    $stmt=$linkinfunction->query($sqlmain);
    $result=$stmt->fetch();
	
    if($stmt->rowCount()==0) { $condition=false; goto endreturn;}

    if ($txntype=='Out' or $txntype==4 or $txntype=='Repack' or $txntype==12){
        $condition=(($result['DateOUT']>$_SESSION['nb4']) AND ($result['Posted']==0) and date('Y', strtotime($result['DateOUT']))==$currentyr and ($result['BranchNo']==$_SESSION['bnum'])) ;
    } elseif ($txntype=='In' or $txntype==7){
        $condition=((is_null($result['DateIN'])?$result['DateOUT']:$result['DateIN'])>$_SESSION['nb4'] and date('Y', strtotime($result['DateIN']))==$currentyr AND ($result['PostedIn']==0) and ($result['ToBranchNo']==$_SESSION['bnum']));
    } elseif ($table==='acctg_4futurecvmain') { $condition=($result['Posted']==0);}
    else {
        $condition=(strtotime($result[$date])>strtotime($closedate) and date('Y', strtotime($result[$date]))==$currentyr AND ($result['Posted']==0));
    }
	
    endreturn:
    if ($condition){
        return true;
    } else{
        return false; 
    }
	
$linkinfunction=null;
}

?>