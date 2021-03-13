<?php
// check if allowed
$allowed=array(8288); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowedmotor; } else { $allow=$allow; }}
if ($allow==0) { goto nomotor;}
allowedmotor:
// end of check
//sqltobecalled
$sqlforall = 'SELECT rr.TxnID, DATE_FORMAT(DateRequest, "%m-%d-%Y") AS DateRequest,Particulars, Amount, b.Branch FROM `admin_2repairrequest` rr LEFT JOIN `1_gamit`.`0idinfo` id ON id.IDNo=rr.RequestedByNo JOIN `1branches` b ON b.BranchNo=rr.BranchNo';

$lookupaddress='../admin/motorvehicles.php?w=Lookup&TxnID=';
// echo $_SESSION['&pos'];
$thead='<tr><td>Date
of Request</td><td>Particulars</td><td>Amount</td><td>Expense of</td></tr>';
// echo 'test';
//Ops Head
if (allowedToOpen(8289,'1rtc')){ //OpsHead /SupplyChain
    
    $sqlsp=$sqlforall.' WHERE RequestCompleted=1 AND Approved=0 AND Approved2=0 AND ((SELECT deptheadpositionid FROM attend_30currentpositions WHERE IDNo=rr.RequestedByNo)=(SELECT PositionID FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].'))';

	$stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;

    if ($stmtsp->rowCount()>0){
        $msgcb='<div><br>Repair Request (For Approval)<table bgcolor="FFFFF">'
                . $thead;
    foreach($datatoshowsp as $rows){
        $sp++;

$msgcb.='<tr><td>'.$rows['DateRequest']

.'</td><td>'.htmlcharwithbr($fromBRtoN,$rows['Particulars']).'</td>'
                .
'<td>'.number_format($rows['Amount'],2).'</td>'
                . '<td>'.$rows['Branch'].'</td>'
            .'<td><a
href="'.$lookupaddress.$rows['TxnID'].'"
target=blank>Lookup Request</a></td>'.'</tr>';
   }
   echo $msgcb.'<br></table></div>';
   }
}

if (allowedToOpen(8290,'1rtc')){ //GenAdminHead
    
    $sqlsp=$sqlforall.' WHERE RequestCompleted=1 AND Approved=1 AND Approved2=0';

	$stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;

    if ($stmtsp->rowCount()>0){
        $msgcb='<div><br>Repair Request (For Approval - GenAdminHead)<table bgcolor="FFFFF">'
                . $thead;
    foreach($datatoshowsp as $rows){
        $sp++;

$msgcb.='<tr><td>'.$rows['DateRequest']

.'</td><td>'.htmlcharwithbr($fromBRtoN,$rows['Particulars']).'</td>'
                .
'<td>'.number_format($rows['Amount'],2).'</td>'
                . '<td>'.$rows['Branch'].'</td>'
            .'<td><a
href="'.$lookupaddress.$rows['TxnID'].'"
target=blank>Lookup Request</a></td>'.'</tr>';
   }
   echo $msgcb.'<br></table></div>';
   }
}

if (allowedToOpen(8287,'1rtc')){ //Acknowledge by Requester /BranchHead
    
    $sqlsp=$sqlforall.' WHERE RequestCompleted=1 AND Approved=1 AND Approved2=1 AND Acknowledged=0 AND RequestedByNo='.$_SESSION['(ak0)'];

	$stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;

    if ($stmtsp->rowCount()>0){
        $msgcb='<div><br>Repair Request (For Approval - GenAdminHead)<table bgcolor="FFFFF">'
                . $thead;
    foreach($datatoshowsp as $rows){
        $sp++;

$msgcb.='<tr><td>'.$rows['DateRequest']

.'</td><td>'.htmlcharwithbr($fromBRtoN,$rows['Particulars']).'</td>'
                .
'<td>'.number_format($rows['Amount'],2).'</td>'
                . '<td>'.$rows['Branch'].'</td>'
            .'<td><a
href="'.$lookupaddress.$rows['TxnID'].'"
target=blank>Lookup Request</a></td>'.'</tr>';
   }
   echo $msgcb.'<br></table></div>';
   }
}



nomotor:
?>