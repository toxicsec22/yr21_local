<?php
function editOk($table,$txnid,$linkinfunction,$txntype){
    global $currentyr;
    if(in_array($table,array('acctg_2salemain','acctg_2collectmain','acctg_2txfrmain','acctg_2depositmain','acctg_2purchasemain','acctg_2cvmain','acctg_2jvmain'))){
    $closedate=(!allowedToOpen(300,'1rtc'))?$_SESSION['nb4']:$_SESSION['nb4A'];    
    } else { $closedate=$_SESSION['nb4']; }
    switch ($table){
        case 'invty_2transfer':
            $sqlmain='Select s.`DateOUT`, s.`DateIN`,s.Posted, s.PostedIn, ToBranchNo, BranchNo from `'.$table.'`as s where TxnId='.$txnid;
            break;
        case 'audit_2countmain':
        case 'audit_2toolscountmain':
        case 'audit_2countfreonmain':
        case 'audit_3vacuum':
            $sqlmain='Select s.`Date`, s.Posted from `'.$table.'`as s where CountID='.$txnid;
            $date='Date';
            break;
        case 'audit_2countcash':
            $sqlmain='Select s.`DateCounted`, s.Posted from `'.$table.'`as s where CashCountID='.$txnid;
            $date='DateCounted';
            break;
        case 'acctg_2collectsubbounced':
            $sqlmain='Select s.`DateBounced`, s.Posted from `'.$table.'`as s where TxnID='.$txnid;
            $date='DateBounced';
            break;
		case 'acctg_3undepositedpdcfromlastperiodbounced':
            $sqlmain='Select s.`DateBounced`, s.Posted from `'.$table.'`as s where UndepPDCId='.$txnid;
            $date='DateBounced'; 
            break;
        case 'acctg_2cvmain':
            $sqlmain='Select s.`Date`, s.Posted from `'.$table.'`as s where CVNo='.$txnid;
            $date='Date';
            break;
        case 'acctg_2jvmain':
            $sqlmain='Select s.`JVDate`, s.Posted from `'.$table.'`as s where JVNo='.$txnid;
            $date='JVDate';
            break;
		case 'acctg_4futurecvmain':
            $sqlmain='Select s.`Date`, s.Posted from `'.$table.'`as s where CVNo='.$txnid;
            $date='Date';
            break;	
			
        default:
            $sqlmain='Select s.`Date`, s.Posted from `'.$table.'`as s where TxnID='.$txnid;
            $date='Date';
            break;
    }
    
       
    $stmt=$linkinfunction->query($sqlmain);
    $result=$stmt->fetch();
	
    if ($txntype=='Out' or $txntype==4 or $txntype=='Repack' or $txntype==12){
        $condition=(($result['DateOUT']>$_SESSION['nb4']) AND ($result['Posted']==0) and date('Y', strtotime($result['DateOUT']))==$currentyr and ($result['BranchNo']==$_SESSION['bnum'])) ;
    } elseif ($txntype=='In' or $txntype==7){
        $condition=((is_null($result['DateIN'])?$result['DateOUT']:$result['DateIN'])>$_SESSION['nb4'] and date('Y', strtotime($result['DateIN']))==$currentyr AND ($result['PostedIn']==0) and ($result['ToBranchNo']==$_SESSION['bnum']));
    } elseif ($table==='acctg_4futurecvmain') { $condition=($result['Posted']==0);}
    else {
        $condition=(strtotime($result[$date])>strtotime($closedate) and date('Y', strtotime($result[$date]))==$currentyr AND ($result['Posted']==0));
    }
	
    if ($condition){
        return true;
    } else{
        return false; 
    }
	
$linkinfunction=null;
}

?>