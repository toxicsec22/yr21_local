<?php
// check if allowed
$allowed=array(100,1500,5237,5238,5239,5240); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowedrcp; } else { $allow=$allow; }}
if ($allow==0) { goto norcpapp;}
allowedrcp:
// end of check
//sqltobecalled
$sqlforall = 'SELECT cpr.TxnID, DATE_FORMAT(DateRequest, "%m-%d-%Y") AS
DateRequest,DATE_FORMAT(DatePayment, "%m-%d-%Y") AS
DatePayment,Particulars, Payee, Amount, b.Branch FROM `approvals_4checkpayment` cpr LEFT JOIN
`1employees` e ON e.IDNo=cpr.RequestedByNo JOIN
`1branches` b ON b.BranchNo=cpr.BranchNo';

$lookupaddress='../approvals/checkpaymentrequest.php?w=Lookup&TxnID=';

$thead='<tr><td>Payee</td><td>Date
of Request</td><td>Date
of Payment</td><td>Particulars</td><td>Amount</td><td>Expense of</td></tr>';

//DeptHead
if (allowedToOpen(100,'1rtc')){
    
//    $sqlsp=$sqlforall.' JOIN `attend_30currentpositions` cp ON cp.IDNo=cpr.RequestedByNo
//WHERE RequestCompleted=1 AND Approved=0 AND (SELECT IDNo FROM `attend_30currentpositions` WHERE PositionID=cp.deptheadpositionid)='.$_SESSION['(ak0)'];
     $sqlsp=$sqlforall.' JOIN `attend_30currentpositions` cp ON cp.IDNo=cpr.RequestedByNo
WHERE RequestCompleted=1 AND Approved=0 AND RequestedByNo<>'.$_SESSION['(ak0)'].' AND IF(cp.PositionID=cp.deptheadpositionid,cp.supervisorpositionid,IF(cp.deptid=0,cp.supervisorpositionid,cp.deptheadpositionid))='.$_SESSION['&pos'];
 $stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;

    if ($stmtsp->rowCount()>0){
        $msgcb='<div><br>Check Payment Request (For Approval - DeptHead)<table bgcolor="FFFFF">'
                . $thead;
    foreach($datatoshowsp as $rows){
        $sp++;

$msgcb.='<tr><td>'.htmlcharwithbr($fromBRtoN,$rows['Payee']).'</td><td>'.$rows['DateRequest']

.'</td><td>'.$rows['DatePayment'].'</td><td>'.htmlcharwithbr($fromBRtoN,$rows['Particulars']).'</td>'
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

//RCE/JYE if recurring is = 1, di na po dadaan kay rce/jye
if (allowedToOpen(5240,'1rtc')){
    
    $sqlsp=$sqlforall.'
WHERE Approved=1 AND Approved2=0 AND Recurring=0';

 $stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;

    if ($stmtsp->rowCount()>0){
        $msgcb='<div><br>Check Payment Request (For Approval-RCE/JYE)<table bgcolor="FFFFF">'
                . $thead;
    foreach($datatoshowsp as $rows){
        $sp++;

$msgcb.='<tr><td>'.htmlcharwithbr($fromBRtoN,$rows['Payee']).'</td><td>'.$rows['DateRequest']

.'</td><td>'.$rows['DatePayment'].'</td><td>'.htmlcharwithbr($fromBRtoN,$rows['Particulars']).'</td>'
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

// Acknowledge by Acctg
if (allowedToOpen(5237,'1rtc')){
    
    $sqlsp=$sqlforall.'
WHERE Approved=1 AND Approved2=1 AND Recurring=0 AND Acknowledge=0 UNION ALL '.$sqlforall.'
WHERE Approved=1 AND Approved2=0 AND Recurring=1 AND Acknowledge=0';

 $stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;

    if ($stmtsp->rowCount()>0){
        $msgcb='<div><br>Check Payment Request (To Acknowledge)<table bgcolor="FFFFF">'
                . $thead;
    foreach($datatoshowsp as $rows){
        $sp++;

$msgcb.='<tr><td>'.htmlcharwithbr($fromBRtoN,$rows['Payee']).'</td><td>'.$rows['DateRequest']

.'</td><td>'.$rows['DatePayment'].'</td><td>'.htmlcharwithbr($fromBRtoN,$rows['Particulars']).'</td>'
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

// Receipt Received
if (allowedToOpen(5239,'1rtc')){
    
    $sqlsp=$sqlforall.'
WHERE CheckIssued=1 AND ReceiptReceived=0';

 $stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;

    if ($stmtsp->rowCount()>0){
        $msgcb='<div><br>Check Payment Request (Waiting for Receipt)<table bgcolor="FFFFF">'
                . $thead;
    foreach($datatoshowsp as $rows){
        $sp++;

$msgcb.='<tr><td>'.htmlcharwithbr($fromBRtoN,$rows['Payee']).'</td><td>'.$rows['DateRequest']

.'</td><td>'.$rows['DatePayment'].'</td><td>'.htmlcharwithbr($fromBRtoN,$rows['Particulars']).'</td>'
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
norcpapp:
?>