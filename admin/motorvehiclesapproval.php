<?php
if(isset($_SESSION['(ak0)'])){
    if (!allowedToOpen(8288,'1rtc')) { goto skip;} 
    $editprocess='../admin/motorvehicles.php?w=Lookup&TxnID=';
    $editprocesslabel='Lookup';
    $columnnames=array('DateRequest','Particulars','Amount','Branch');

    $sql1 = 'SELECT rr.TxnID, DATE_FORMAT(DateRequest, "%m-%d-%Y") AS DateRequest,Particulars, FORMAT(Amount,2) AS Amount, b.Branch, 
(SELECT IFNULL(IDNo,0) FROM attend_30currentpositions WHERE PositionID=(SELECT deptheadpositionid FROM attend_30currentpositions cp WHERE cp.IDNo=rr.RequestedByNo)) AS NotifIDNo FROM `admin_2repairrequest` rr JOIN `1branches` b ON b.BranchNo=rr.BranchNo ';
    $txnidname='TxnID';
} else {

    $sql1='SELECT COUNT(rr.TxnID) AS CountTxn, rr.RequestedByNo, (SELECT IFNULL(IDNo,0) FROM attend_30currentpositions WHERE PositionID=(SELECT deptheadpositionid FROM attend_30currentpositions cp WHERE cp.IDNo=rr.RequestedByNo)) AS NotifIDNo FROM `admin_2repairrequest` rr ';
}

// OpsHead /SupplyChain Head
    $sqlfilter=' WHERE RequestCompleted=1 AND Approved=0 AND Approved2=0 ';
    if(isset($_SESSION['(ak0)'])){
        if (allowedToOpen(8289,'1rtc')) { 
        $subtitle='Repair Request For Approval';
        $sql=$sql1. $sqlfilter.' HAVING NotifIDNo='.$_SESSION['(ak0)'];
        include '../backendphp/layout/displayastableonlynoheaders.php';}
    } else { 
        $sql='INSERT INTO approvals_notif (IDNo,CountNotif) SELECT (SELECT IFNULL(IDNo,0) FROM attend_30currentpositions WHERE PositionID=(SELECT deptheadpositionid FROM attend_30currentpositions cp WHERE cp.IDNo=rr.RequestedByNo)) AS IDNo, COUNT(rr.TxnID) AS CountTxn FROM `admin_2repairrequest` rr '.$sqlfilter.' GROUP BY IDNo HAVING IDNo IN ('.allAllowedID(8289).') AND CountTxn>0'; 
        $stmt=$link->prepare($sql); $stmt->execute();
    }

//GenAdminHead
    
    $sqlfilter=' WHERE RequestCompleted=1 AND Approved=1 AND Approved2=0'; 
    if(isset($_SESSION['(ak0)'])){ 
        if (allowedToOpen(8290,'1rtc')) { 
            $sql=$sql1.$sqlfilter;
    $subtitle='Repair Request (For Approval - GenAdminHead)';
    include '../backendphp/layout/displayastableonlynoheaders.php';}
} else {
    $sql='INSERT INTO approvals_notif (IDNo,CountNotif) SELECT '.allAllowedID(8289).' AS IDNo, COUNT(rr.TxnID) AS CountTxn FROM `admin_2repairrequest` rr '.$sqlfilter.'  HAVING CountTxn>0';
}

//Acknowledge by Requester /BranchHead

    $sqlfilter=' WHERE RequestCompleted=1 AND Approved=1 AND Approved2=1 AND Acknowledged=0 ';

    if(isset($_SESSION['(ak0)'])){
    if (allowedToOpen(8287,'1rtc')){ 
    $sql= $sql1.$sqlfilter.' AND rr.RequestedByNo='.$_SESSION['(ak0)'];
    $subtitle='Repair Request For Acknowledgement';
    include '../backendphp/layout/displayastableonlynoheaders.php';}
} else {
    $sql='INSERT INTO approvals_notif (IDNo,CountNotif) SELECT RequestedByNo AS IDNo, COUNT(rr.TxnID) AS CountTxn FROM `admin_2repairrequest` rr '.$sqlfilter.' AND rr.RequestedByNo IN ('.allAllowedID(8287).') GROUP BY RequestedByNo HAVING CountTxn>0';
}
skip:
?>