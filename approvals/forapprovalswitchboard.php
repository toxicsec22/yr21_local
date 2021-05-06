<?php
ob_start();
include_once($path.'/yr21/switchboard/scrollcss.php');
$cntres=0; $sp=0;
$switchboard='';
//function fromBRtoN called in checkifloggedon
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

// if (allowedToOpen(2201,'1rtc')){
// include_once('approvals/updateempaddress.php');


//AWOL
include_once('attendance/noattendtoday.php');
if ($cntawol>0){
    $msgcb='<br><div style="background-color: white; " >No TIME-IN Today<table bgcolor="FFFFF">';
foreach($dataawol as $rows){
    $sp++;
    $msgcb.='<tr><td>'.$rows['FullName'].'</td></tr>';
}
$switchboard = $switchboard . $msgcb.'</table></div>';
}
$cntres=$cntres + $sp;



//answer pre existing condition
$sqlpec='SELECT IDExpiry FROM 1employees WHERE IDNo='.$_SESSION['(ak0)'].' AND IDExpiry IS NOT NULL;';
$stmtpec=$link->query($sqlpec);
if($stmtpec->rowCount()==0){
echo '<div style="background-color:white;padding:10px;text-align:center;border:1px solid black;"><form action="generalinfo/idinfo.php?w=UpdateIDExpiry" method="POST">
<strong style="color:red;">ID Validity Period:</strong> <input type="month" name="IDExpiry" value="'.date('Y-m').'">
<input style="background-color:blue;color:white;padding:3px" type="submit" name="btnUpdateValidity" value="Update Validity"></h2></a></div>';
}

// }
// CLIENT BDAYS TODAY  
if (allowedToOpen(212,'1rtc')) {
 include('generalinfo/unionlists/clientbdayssql.php');
 $countcb=0;
    $sqlcb='Select * from `gen_info_clientbdays` WHERE `ToSort`=curdate() ORDER BY Company'; $stmtcb=$link->query($sqlcb); $datatoshowcb=$stmtcb->fetchAll();
    if ($stmtcb->rowCount()>0){
        $msgcb='<div><br><table bgcolor="FFFFF"><tr><td>Client birthdays today:</td></tr>';
    foreach($datatoshowcb as $rows){
        $countcb++;
        $msgcb=$msgcb.'<tr><td style="font-size: small;">'.str_repeat('&nbsp;',4).htmlspecialchars($rows['Name']).', ('.$rows['Position'].') of '.$rows['Company'].' -- '.$rows['Branches'].'</td></tr>';
   }
      
   $switchboard.= $msgcb.'<br></table><br><br></div>';
   }
   $cntres=$countcb;
   $stmtcb=null;
}

// Addons
 
if (allowedToOpen(array(7004,7006),'1rtc')){ 

if (allowedToOpen(7004,'1rtc')){ //SAM
	$condition='WHERE FApproved=0';
}else{ //SC
	$condition='WHERE FApproved=1 AND Approved=0';
}
    $sqlsp='SELECT Branch,SaleNo,ao.ItemCode,CONCAT(Category,\' \',ItemDesc) as ItemDesc,ao.TxnID,txndesc as txntype,Qty,case when Approved=0 then "For Approval" when Approved=1 then "Approved" when Approved=2 then "Rejected" end as Status from invty_2salesubaddons ao join invty_2sale s on s.TxnID=ao.TxnID join invty_1items i on i.ItemCode=ao.ItemCode join invty_1category c on c.CatNo=i.CatNo join 1branches b on b.BranchNo=s.BranchNo join invty_0txntype t on t.txntypeid=s.txntype '.$condition.'';
  // echo $sqlsp; exit();
     $stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll();    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<br>Add-on for approval:<table><tr><td>Branch</td><td>ItemCode</td><td>ItemDesc</td></tr>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        $msgsp=$msgsp.'<tr><td>'.$rows['Branch'].'</td><td>'.$rows['ItemCode'].'</td><td>'.$rows['ItemDesc'].'</td><td><a style="color:blue;text-decoration:none;" href="/yr21/invty/addons.php?w=Addons&TxnID='.$rows['TxnID'].'&Lookup=1&Branch='.$rows['Branch'].'&SaleNo='.$rows['SaleNo'].'&txntype='.$rows['txntype'].'" target=_blank>Look up</a></td></tr>';
        
   }
   echo $msgsp.'</tr></table><br>';
   }
   
 } 

 if (allowedToOpen(array(6081,6082,6110),'1rtc')) {

    if(allowedToOpen(6082,'1rtc')){ //direct sa AWOL Report
        $dlink='attendance/lookupperteam.php?w=AWOLCount';
        $dcondi='';
    }  else if(allowedToOpen(6110,'1rtc')){ //direct sa remarks
        $stmt0=$link->query('SELECT deptid FROM `attend_30currentpositions` WHERE IDNo='.$_SESSION['(ak0)']);
        $res0=$stmt0->fetch();
        
        $dcondi='AND deptid IN ('.(($res0['deptid']==70)?'70,10':$res0['deptid']).')';
        $dlink='attendance/encodeattend.php?w=RemarksOfDept';
    } 
    else { //direct sa remarks
        $dlink='attendance/encodeattend.php?w=RemarksOfDept';
        $dcondi='AND (LatestSupervisorIDNo='.$_SESSION['(ak0)'].' OR deptheadpositionid='.$_SESSION['&pos'].')';
    }

    $sql='select COUNT(TxnID) AS cntreq FROM attend_2attendance a JOIN attend_30currentpositions cp ON a.IDNo=cp.IDNo WHERE DateToday=CURDATE() AND LeaveNo=18 AND HOUR(NOW())>=Shift '.$dcondi.';';
    
    $stmt=$link->query($sql); $res=$stmt->fetch();

        if ($res['cntreq']>0){ 
        $msgcb='<br><div id="table-wrapper" style="width:80%;">Number of AWOL Today: '.$res['cntreq'].'<div><table bgcolor="FFFFF">'
            .'';
                    $sp++;
                    
                    $msgcb.='<tr><td><a href = "'.$dlink.'">View more reports.</a>'.'</td>'

                            .'</tr>';
            $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
        }
        $cntres=$cntres + $sp;
    
}


// LEAVE REQUESTS -- HR 
if (allowedToOpen(215,'1rtc')) {
    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
    echo comboBox($link,'SELECT * FROM `attend_0leavetype` WHERE LeaveNo NOT IN (11,12,13,15) ORDER BY LeaveName;','LeaveNo','LeaveName','leavetype');
    
    $stmthr=$link->query('SELECT lr.*, FullName, if(p.deptid IN (1,2,3,10),Branch,dept) AS `Branch/Dept`, LeaveName as LeaveType, CONCAT("SLBal: ", IFNULL(SLBal,0)," VLBal: ",IFNULL(VLBal,0)," BirthdayBal: ",IFNULL(BirthdayBal,0)) AS LeaveBalBeforeThisLeave, IF(SupervisorApproved=1,"Approved","Denied") AS SupervisorResponse, IF(Approved=1,"Approved","Denied") AS DeptHeadResponse FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo JOIN `attend_0leavetype` lt ON lt.LeaveNo=lr.LeaveNo LEFT JOIN `attend_61leavebal` lb ON lb.IDNo=lr.IDNo WHERE MarkasReadByDeptHead=1 AND HRVerifiedByNo IS NULL');// Acknowledged=1 AND
    
$datatoshowhr=$stmthr->fetchAll();
    if ($stmthr->rowCount()>0){
        $colorcount=0;
        $rcolor[0]="FFFFCC";
        $rcolor[1]="FFFFFF";
        
        $colstoshow=array('FullName', 'Branch/Dept', 'FromDate', 'ToDate', 'LeaveType', 'Reason', 'LeaveBalBeforeThisLeave', 'SupervisorComment', 'SupervisorResponse', 'ApproveComment', 'DeptHeadResponse'); $coltitle='';
        foreach ($colstoshow as $field) {$coltitle=$coltitle.'<td style="border: 1px solid black;">' . $field.'</td>'; }
        $msghr='<br><div id="table-wrapper" style="width:89%">Verification of HR<div id="table-scroll" style="height:150px"><table style="border-collapse: collapse; border: 1px solid black;"><tr>'.$coltitle.'</tr><tr bgcolor='. $rcolor[$colorcount%2].'>';
		// $countrec=0;
    foreach($datatoshowhr as $rows){
        $cols=''; $colorcount++; 
        foreach ($colstoshow as $field) {$cols=$cols.'<td style="border: 1px solid black;">' . htmlcharwithbr($fromBRtoN,$rows[$field]).'</td>'; }
        $msghr.='<form method="post" action="/yr21/attendance/leaverequest.php?w=HRVerified&TxnID='.$rows['TxnID'].'">'.$cols.'<td style="padding: 2px; border-bottom: 1px solid black;">';
        if ($rows['Acknowledged']<>0) { $msghr.='<input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'" />
        Revise Leave Type, if needed <input type="text" name="LeaveType" list="leavetype" size=8></td>
        <td style="padding: 2px;">Comments, if any: <input type="text" size=15 name="HRComment" placeholder="blank if no comment"></td>
        <td style="padding: 2px;">&nbsp &nbsp &nbsp<input type="submit" name="submit" value="Verified & Recorded">';
        } else {$msghr.='Requester must acknowledge';}
        $msghr.='</td></form></tr><tr bgcolor='. $rcolor[$colorcount%2].'>';
        // $countrec++;
   }
   $switchboard = $switchboard . $msghr.'<b style="color:red;">'.$colorcount.' unfinished leave'.($colorcount>1?'s':'').'.</b><br></tr></table></div></div>';
    }
    $cntres = $cntres + $stmthr->rowCount();
    $stmthr=null;
}





// APPROVE RATES -- will now open a new page
if (allowedToOpen(7911,7912,'1rtc')) { 
    $sql='SELECT COUNT(r.IDNo) AS cntreq FROM payroll_22rates r JOIN 1employees e ON e.IDNo=r.IDNo  WHERE Resigned=0 AND (ApprovedByNo IS NULL OR ApprovedByNo=0)';
    $stmt=$link->query($sql); $res=$stmt->fetch();

    if ($res['cntreq']>0){
        $cntres+=$res['cntreq'];
    $msgcb='<br><div id="table-wrapper" style="width:80%;">Salary Rates for Approval :<div><table bgcolor="FFFFF">'
         .'';
                $msgcb.='<tr><td><a href = "payroll/ratesforapproval.php"> '.$res['cntreq'].' record(s).</a>'.'</td>'

                        .'</tr>';
          $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
    }
	  
}


// UNREAD CONFIDENTIAL REPORTS
if (allowedToOpen(6503,'1rtc')){
    $sqlsp='SELECT cr.TxnID,cr.Report, IFNULL(ReIDNo,"0") AS Re_IDNo, IFNULL(CONCAT(eb.Nickname, " ",eb.SurName),"Others") AS Regarding, b.Branch, 
        CONCAT(e.Nickname, " ",e.SurName) AS ReportedBy,1 AS ReportType FROM `hr_3confireports` cr 
            LEFT JOIN `1employees` `eb` ON `eb`.`IDNo` = `cr`.`ReIDNo`
            LEFT JOIN `attend_1defaultbranchassign` `db` ON `cr`.`ReIDNo`=db.IDNo
            LEFT JOIN `1branches` `b` ON `db`.`DefaultBranchAssignNo` = `b`.`BranchNo`
            LEFT JOIN `1employees` e ON e.IDNo=cr.EncodedByNo 
            WHERE ReadbyMgt=0';
    $stmtsp=$link->query($sqlsp);
    $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $cols=array('Re_IDNo', 'Regarding', 'Branch', 'Report', 'ReportedBy'); 
    $coltitle=''; foreach ($cols as $col) { $coltitle=$coltitle.'<td>'.$col.'</td>';}
    $msgsp='<div><br>Unread confidential reports: <a href="/yr21/generalinfo/confireportjye.php">Open List</a> <table bgcolor="FFFFF"><tr>'.$coltitle.'<td>Mark as Read</td></tr><tr>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        $colsql=''; foreach ($cols as $col) { $colsql=$colsql.'<td>'.htmlcharwithbr($fromBRtoN,$rows[$col]).'</td>';}
        $msgsp=$msgsp.$colsql.'<td><a href="/yr21/generalinfo/confireportjye.php?w=SetAsRead&ReportType='.$rows['ReportType'].'&ReadbyMgt=1&TxnID='.$rows['TxnID'].'&action_token='.$_SESSION['action_token'].'">Read</a></td></tr><tr>';
    }
       $switchboard = $switchboard . $msgsp.'</tr></table></div>';               
   }   
   $cntres=$cntres + $countsp;
    $stmtsp=null;
   } 
   
// UNREAD INCIDENT REPORTS
if (allowedToOpen(6505,'1rtc')){
    if (allowedToOpen(217,'1rtc')) { $condition=' WHERE 1=1 ';}
        else { $condition=' JOIN `attend_30currentpositions` cp ON cr.ReIDNo=cp.IDNo WHERE deptheadpositionid='.$_SESSION['&pos'];}
    $sqlmain='SELECT cr.TxnID,DateofIncident,cr.Summary AS Report, IFNULL(ReIDNo,"0") AS Re_IDNo, IFNULL(CONCAT(eb.Nickname, " ",eb.SurName),"Others") AS Regarding, b.Branch, 
    CONCAT(e.Nickname, " ",e.SurName) AS ReportedBy,2 AS ReportType FROM `hr_3incidentreports` cr 
        LEFT JOIN `1employees` `eb` ON `eb`.`IDNo` = `cr`.`ReIDNo`
        LEFT JOIN `attend_1defaultbranchassign` `db` ON `cr`.`ReIDNo`=db.IDNo
        LEFT JOIN `1branches` `b` ON `db`.`DefaultBranchAssignNo` = `b`.`BranchNo`
        LEFT JOIN `1employees` e ON e.IDNo=cr.EncodedByNo '.$condition.'';
    
    //Unresolved
    $sqlsp=$sqlmain.' AND Resolved=0 ';
    // echo $sqlsp;
    $stmtsp=$link->query($sqlsp);
    $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
    if ($stmtsp->rowCount()>0){
        $msgsp='<div><br>Unresolved incident reports (<b>'.$stmtsp->rowCount().'</b>): <a href="/yr21/hr/incidentreporthr.php">Open List</a></div>';

        $switchboard = $switchboard . $msgsp;

        $cntres=$cntres + $countsp;
        $stmtsp=null;
    }
    

    //last 5days unresolved
    $sqlsp=$sqlmain.' AND DATE(cr.`TimeStamp`) > (CURDATE() - INTERVAL 5 DAY) AND Resolved=0 ';
    $stmtsp=$link->query($sqlsp);
    $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $cols=array('Re_IDNo', 'Regarding', 'Branch', 'Report','DateofIncident', 'ReportedBy'); 
    $coltitle=''; foreach ($cols as $col) { $coltitle=$coltitle.'<td>'.$col.'</td>';}
    $msgsp='<div><table bgcolor="FFFFF"><tr>'.$coltitle.'</tr><tr>'; //<td>Mark as Read</td>
    foreach($datatoshowsp as $rows){
        $countsp++;
        $colsql=''; foreach ($cols as $col) { $colsql=$colsql.'<td style="font-size: small;">'.htmlcharwithbr($fromBRtoN,$rows[$col]).'</td>';}
        // if (allowedToOpen(217,'1rtc')) { $setread='<td><a href="/yr21/hr/incidentreporthr.php?w=SetAsRead&ReportType='.$rows['ReportType'].'&ReadbyMgt=1&TxnID='.$rows['TxnID'].'&action_token='.$_SESSION['action_token'].'">Read</a></td>';} else {$setread='<td>Unread by HR</td>';}
        $msgsp=$msgsp.$colsql.'</tr><tr>'; //$setread
    }
        // echo $msgsp.'</tr></table></div>';
$switchboard = $switchboard . $msgsp.'</tr></table></div>';	
// echo $switchboard;
   }  
   $cntres=$cntres + $countsp;
    $stmtsp=null;
   } 
   
// UNREAD BRANCH CONCERN REPORTS
if (allowedToOpen(7101,'1rtc')){
    $sqlsp='SELECT rc.TxnID,rc.BranchConcern, b.Branch, e.Nickname as ReportedBy, rc.TimeStamp
FROM `comments_33branchconcerns` rc join `1branches` b on b.BranchNo=rc.BranchNo
join `1employees` e on e.IDNo=rc.EncodedByNo WHERE ReadByOps=0';
    $stmtsp=$link->query($sqlsp);
    $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $cols=array('Branch', 'BranchConcern','ReportedBy', 'TimeStamp'); 
    $coltitle=''; foreach ($cols as $col) { $coltitle=$coltitle.'<td>'.$col.'</td>';}
    $msgsp='<div><br>Unread branch concerns: <a href="/yr21/invty/branchreports.php?w=BranchConcerns">Open List</a> <table bgcolor="FFFFF"><tr>'.$coltitle.'<td>Mark as Read</td></tr><tr>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        $colsql=''; foreach ($cols as $col) { $colsql=$colsql.'<td>'.htmlcharwithbr($fromBRtoN,$rows[$col]).'</td>';}
        if (allowedToOpen(7102,'1rtc')) { $setread='<td><a href="/yr21/invty/branchreports.php?w=SetAsRead&TxnID='.$rows['TxnID'].'&action_token='.$_SESSION['action_token'].'">Set As Read</a></td>';} else {$setread='<td>Unread by Ops</td>';}
        $msgsp=$msgsp.$colsql.$setread.'</tr><tr>';
    }
        $switchboard = $switchboard . $msgsp.'</tr></table></div>';               
   }  
   $cntres=$cntres + $countsp;
   $stmtsp=null;
   } 
  
// APPROVED BUDGET REQUESTS FOR CHECK PAYMENTS


if (allowedToOpen(5233,'1rtc')){
    $sqlbr='SELECT bl.TxnID, DATE_FORMAT(DateNeeded, "%m-%d-%Y") AS DateNeeded, Duration,Purpose, DATE_FORMAT(bl.`TimeStamp`, "%m-%d-%Y") AS DateofRequest, CONCAT(e.FirstName," ",e.SurName) AS Requester, FORMAT(SUM(Amount),0) AS Amount, Branch,
"For check issuance" AS Status FROM `approvals_3budgetandliq` bl LEFT JOIN `1employees` e ON e.IDNo=bl.EncodedByNo 
JOIN `approvals_3budgetrequestsub` brs ON bl.TxnID=brs.TxnID JOIN `1branches` b ON b.BranchNo=bl.BranchNo

WHERE FundsReleased=0 AND Approved=1 GROUP BY bl.TxnID 
UNION SELECT  af.TxnID, DATE_FORMAT(`RequestTS`, "%m-%d-%Y") as DateNeeded,null,Reason,DATE_FORMAT(`RequestTS`, "%m-%d-%Y") AS DateofRequest,CONCAT(e.FirstName," ",e.SurName) AS Requester,FORMAT((FinalAmount),0) AS Amount,null,"For check issuance" as Status from `approvals_4financial` af JOIN `1employees` e ON af.RequestByNo=e.IDNo where af.FinalApproval = 1 and af.vchStatus = 0';

    if (allowedToOpen(5235,'1rtc')){
$sqlbr.=' UNION ALL
SELECT bl.TxnID, DATE_FORMAT(DateNeeded, "%m-%d-%Y") AS DateNeeded, Duration,Purpose, DATE_FORMAT(bl.`TimeStamp`, "%m-%d-%Y") AS DateofRequest, CONCAT(e.FirstName," ",e.SurName) AS Requester, FORMAT(SUM(Amount),0) AS Amount, Branch,
"Waiting for acceptance" AS Status FROM `approvals_3budgetandliq` bl LEFT JOIN `1employees` e ON e.IDNo=bl.EncodedByNo 
JOIN `approvals_3budgetrequestsub` brs ON bl.TxnID=brs.TxnID JOIN `1branches` b ON b.BranchNo=bl.BranchNo
    WHERE FundsAccepted=0 AND FundsReleased=1 GROUP BY bl.TxnID 
     UNION ALL
SELECT bl.TxnID, DATE_FORMAT(DateNeeded, "%m-%d-%Y") AS DateNeeded, Duration,Purpose, DATE_FORMAT(bl.`TimeStamp`, "%m-%d-%Y") AS DateofRequest, CONCAT(e.FirstName," ",e.SurName) AS Requester, FORMAT(SUM(Amount),0) AS Amount, Branch,
"Documents complete?" AS Status FROM `approvals_3budgetandliq` bl LEFT JOIN `1employees` e ON e.IDNo=bl.EncodedByNo 
JOIN `approvals_3budgetrequestsub` brs ON bl.TxnID=brs.TxnID JOIN `1branches` b ON b.BranchNo=bl.BranchNo
WHERE DocsComplete=0 AND ForLiqSubmission=1 GROUP BY bl.TxnID '; }
$sqlbr.=' UNION ALL
SELECT bl.TxnID, DATE_FORMAT(DateNeeded, "%m-%d-%Y") AS DateNeeded, Duration,Purpose, DATE_FORMAT(bl.`TimeStamp`, "%m-%d-%Y") AS DateofRequest, CONCAT(e.FirstName," ",e.SurName) AS Requester, FORMAT(SUM(Amount),0) AS Amount, Branch,
"No liquidation?" AS Status FROM `approvals_3budgetandliq` bl LEFT JOIN `1employees` e ON e.IDNo=bl.EncodedByNo 
JOIN `approvals_3budgetrequestsub` brs ON bl.TxnID=brs.TxnID JOIN `1branches` b ON b.BranchNo=bl.BranchNo
WHERE FundsAccepted=1 AND ForLiqSubmission=0 AND DATEDIFF(CURDATE(),DATE_ADD(DateNeeded, INTERVAL Duration DAY))>10 GROUP BY bl.TxnID;';
    
 $stmtbr=$link->query($sqlbr);
  $datatoshowsp=$stmtbr->fetchAll(); $sp=0;
 
    if ($stmtbr->rowCount()>0){
        $msgcb='<div><br>Approved budget requests:<table bgcolor="FFFFF">'
                . '<tr><td>Date of Request</td><td>Date Needed</td><td>Duration (days)</td><td>Requester</td><td>Purpose</td><td>Amount</td><td>Charge to Branch</td><td>Status</td></tr>';
    foreach($datatoshowsp as $rows){
        $sp++;
       if(empty($rows['Duration'])){
            if(empty($rows['TxnID'])){

            }else{


     $msgcb.='<tr><td>'.$rows['DateofRequest'].'</td><td>'.$rows['DateNeeded']
                .'</td><td>'.$rows['Duration'].'</td><td>'.$rows['Requester'].'</td>'
                . '<td>'.htmlcharwithbr($fromBRtoN,$rows['Purpose']).'</td><td>'.$rows['Amount'].'</td>'
                . '<td>'.$rows['Branch'].'</td>'
                .'<td>'.$rows['Status'].'</td>'
            .'<td><a href="/yr21/approvals/requestforfinancialasst.php?w=Lookup&TxnID='.$rows['TxnID'].'" target=blank>Lookup Request</a></td>'.'</tr>';}
    }else{
        $msgcb.='<tr><td>'.$rows['DateofRequest'].'</td><td>'.$rows['DateNeeded']
                .'</td><td>'.$rows['Duration'].'</td><td>'.$rows['Requester'].'</td>'
                . '<td>'.htmlcharwithbr($fromBRtoN,$rows['Purpose']).'</td><td>'.$rows['Amount'].'</td>'
                . '<td>'.$rows['Branch'].'</td>'
                .'<td>'.$rows['Status'].'</td>'
            .'<td><a href="/yr21/approvals/requestforbudget.php?w=Lookup&TxnID='.$rows['TxnID'].'" target=blank>Lookup Request</a></td>'.'</tr>';
    }
 
   }
   $switchboard = $switchboard . $msgcb.'<br></table></div>';
   }else{
    
   }
   $cntres=$cntres + $sp;
} 

//List of pending liquidations
if(allowedToOpen(5234,'1rtc')){ 
        $sqlsp='SELECT bl.TxnID, DATE_FORMAT(DateNeeded, "%m-%d-%Y") AS DateNeeded,Purpose, CONCAT(e.FirstName," ",e.SurName) AS Requester, 
FORMAT(SUM(Amount),0) AS Amount, Branch, DATE_FORMAT(bl.`DocsCompleteTS`, "%m-%d-%Y") AS DocsCompleteDate, CONCAT(e4.Nickname, " ", e4.Surname) AS DocsComplete FROM `approvals_3budgetandliq` bl LEFT JOIN `1employees` e ON e.IDNo=bl.EncodedByNo 
JOIN `approvals_3budgetrequestsub` brs ON bl.TxnID=brs.TxnID JOIN `1branches` b ON b.BranchNo=bl.BranchNo
LEFT JOIN `1employees` e4 ON e4.IDNo=bl.DocsCompleteByNo
WHERE DocsComplete=1 AND Liquidated=0 AND DATEDIFF(CURDATE(),DocsCompleteTS)>2 GROUP BY bl.TxnID';
$stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;
if ($stmtsp->rowCount()>0){
        $msgcb='<br><div style="background-color: white; " >Pending liquidations beyond 2 days:&nbsp; &nbsp;<a href="/yr21/approvals/requestforbudget.php?w=SetAsLiquidated" target=blank>Open List</a><table bgcolor="FFFFF">'
                . '<tr><td>Date Needed</td><td>Requester</td><td>Purpose</td><td>Amount</td><td>Charge to Branch</td><td>Docs Received On</td><td>Docs Received By</td></tr>';
    foreach($datatoshowsp as $rows){
        $sp++;
        $msgcb.='<tr><td>'.$rows['DateNeeded'].'</td><td>'.$rows['Requester']
                .'</td><td>'.htmlcharwithbr($fromBRtoN,$rows['Purpose']).'</td><td>'.$rows['Amount'].'</td>'
                . '<td>'.$rows['Branch'].'</td><td>'.$rows['DocsCompleteDate'].'</td>'
                . '<td>'.$rows['DocsComplete'].'</td></tr>';
   }
   $switchboard = $switchboard . $msgcb.'</table></div>';
   }
   $cntres=$cntres + $sp;
} 
// FUNDS RELEASED FOR BUDGET REQUESTS
if (allowedToOpen(5230,'1rtc')){
    $sqlsp='SELECT bl.TxnID, DATE_FORMAT(DateNeeded, "%m-%d-%Y") AS DateNeeded, Duration,Purpose, DATE_FORMAT(bl.`TimeStamp`, "%m-%d-%Y") AS DateofRequest, CONCAT(e.FirstName," ",e.SurName) AS Requester, FORMAT(SUM(Amount),0) AS Amount, Branch,
"Waiting for acceptance" AS Status FROM `approvals_3budgetandliq` bl LEFT JOIN `1employees` e ON e.IDNo=bl.EncodedByNo 
JOIN `approvals_3budgetrequestsub` brs ON bl.TxnID=brs.TxnID JOIN `1branches` b ON b.BranchNo=bl.BranchNo
WHERE FundsAccepted=0 AND FundsReleased=1 AND bl.EncodedByNo='.$_SESSION['(ak0)'].' GROUP BY bl.TxnID
UNION ALL
SELECT bl.TxnID, DATE_FORMAT(DateNeeded, "%m-%d-%Y") AS DateNeeded, Duration,Purpose, DATE_FORMAT(bl.`TimeStamp`, "%m-%d-%Y") AS DateofRequest, CONCAT(e.FirstName," ",e.SurName) AS Requester, FORMAT(SUM(Amount),0) AS Amount, Branch,
"Documents complete?" AS Status FROM `approvals_3budgetandliq` bl LEFT JOIN `1employees` e ON e.IDNo=bl.EncodedByNo 
JOIN `approvals_3budgetrequestsub` brs ON bl.TxnID=brs.TxnID JOIN `1branches` b ON b.BranchNo=bl.BranchNo
WHERE DocsComplete=0 AND ForLiqSubmission=1 AND bl.EncodedByNo='.$_SESSION['(ak0)'].' GROUP BY bl.TxnID;';
 $stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;
 
    if ($stmtsp->rowCount()>0){
        $msgcb='<br><div style="background-color: white; " >Released funds for budget requests:&nbsp; &nbsp;';
    foreach($datatoshowsp as $rows){
        $sp++;
        $msgcb.='<a href="/yr21/approvals/requestforbudget.php?w=Lookup&TxnID='.$rows['TxnID'].'" target=blank>Lookup Request</a>&nbsp; &nbsp;';
   }
   $switchboard = $switchboard . $msgcb.'</div>';
   }
   $cntres=$cntres + $sp;
   $stmtsp=null;
}

/* REMOVED THIS; DEPT HEADS WILL MAKE DECISIONS
if(allowedToOpen(64902,'1rtc')){
		
			 
$sqlsp='SELECT pr.*, Entity, Position, TargetDate, e.Nickname as RequestedBy, pr.TimeStamp as RequestTS, e1.Nickname as ApprovedBy, pr.ApproveTS
        FROM hr_2personnelrequest pr
        JOIN attend_0positions p ON p.PositionID=pr.PositionID        
        JOIN `1employees` e ON e.IDNo=pr.EncodedByNo
        LEFT JOIN `1employees` e1 ON e1.IDNo=pr.ApprovedByNo
        JOIN `acctg_1budgetentities` be ON be.EntityID=pr.EntityID
        WHERE Approved=0 ORDER BY RequestTS DESC';
  
    $stmt=$link->query($sqlsp); $res=$stmt->fetchAll();
        $msg='';                              
        
		if ($stmt->rowCount()>0){
			foreach ($res as $req){
			   // $msg=$msg.'<tr><td>'.$req['Entity'].'</td><td>'.$req['Position'].'</td><td>'.htmlspecialchars($req['Remarks']).'</td><td>'.$req['TargetDate'].'</td>
			   // <td>'.$req['RequestedBy'].'</td>';
			   $msg=$msg.'<tr><td>'.$req['Entity'].'</td><td>'.$req['Position'].'</td><td>'.htmlcharwithbr($fromBRtoN,$req['Remarks']).'</td><td>'.$req['TargetDate'].'</td>
			   
			   <td>'.$req['RequestedBy'].'</td>';
			   $msg.='<td>
			   <form method=post action="hr/personnelrequest.php?which=Approve&TxnID='.$req['TxnID'].'">
			   Comment <input type="text" size=20 name="ApproveComment" placeholder="blank if no comment">
			   <input type="submit" name="Approve" value="Approve">  <input type="submit" name="Approve" value="Deny">
			   </form></td>';
			   $msg.='</tr>'; 
			}
			$msg='<br><table bgcolor="FFFFF">Request for Personnel:<br><th>Entity</th><th>Position</th><th>Remarks</th><th>TargetDate</th><th>RequestedBy</th><th>Approve?</th>'.$msg.'</table>';
		}
        $switchboard = $switchboard . $msg;
		$cntres=$cntres + $stmt->rowCount();
   
}
*/
//MERITS
if (allowedToOpen(6507,'1rtc')){
    
	
	$sqldept = 'SELECT deptid FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].'';
	$stmtdept = $link->query($sqldept);
	$rowdept = $stmtdept->fetch();
	
    $sqlsp='SELECT s.*, (CASE 
	WHEN ReporterHeadStatus = 0 THEN "Reporter\'s Head Acknowledgement"
	WHEN (ReporterHeadStatus<>0 AND ReporteeHeadStatus=0) THEN "Reportee\'s Head Reply" 
	WHEN (ReporteeStatus=3 AND ReporteeHeadStatus<>4) THEN "Employee\'s Acknowledgement"
	ELSE "No Decision Yet" END) AS Status, 
	Statement, Branch, WeightinPoints, CONCAT(Nickname, " ", Surname) AS EncodedBy, ss.PointID, cp.FullName AS Employee FROM hr_72scores s JOIN hr_71scorestmt ss ON s.SSID=ss.SSID JOIN attend_30currentpositions cp ON s.ReporteeNo=cp.IDNo LEFT JOIN 1_gamit.0idinfo id ON s.EncodedByNo=id.IDNo JOIN hr_70points p ON ss.PointID=p.PointID WHERE ('.((allowedToOpen(65071,'1rtc'))?'ss.deptid='.$rowdept['deptid'].' OR':'').' ('.$_SESSION['(ak0)'].' in (s.EncodedByNo,ReporteeNo,ReporterHeadNo,ReporteeHeadNo))) AND IF(ReporteeNo='.$_SESSION['(ak0)'].', ReporterHeadStatus=3, 1=1) AND stmtcat=1 AND DecisionStatus=0';
    
	$stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;
 
    if ($stmtsp->rowCount()>0){
        $msgcb='<br><div id="table-wrapper" style="width:89%;">List of Merits:<div id="table-scroll"><table bgcolor="FFFFF">'
                . '<tr><td>Branch</td><td>Employee</td><td>Statement</td><td>WeightinPoints</td><td>DateofIncident</td><td>Status (Waiting for:)</td></tr>';
    foreach($datatoshowsp as $rows){
        $sp++;
        $msgcb.='<tr><td>'.$rows['Branch']
                .'</td><td>'.$rows['Employee'].'</td>'
                . '<td>'.htmlcharwithbr($fromBRtoN,$rows['Statement']).'</td>'
                . '<td>'.$rows['WeightinPoints'].'</td>'
                .'<td>'.$rows['DateofIncident'].'</td>'
                .'<td>'.$rows['Status'].'</td>'
            .'<td><a href="hr/scores.php?w=Lookup&TxnID='.$rows['TxnID'].'" target=blank>Lookup</a></td>'.'</tr>';
   }
  $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
   }
   $cntres=$cntres + $sp;
	//DEMERITS
    $sqlsp='SELECT s.*, (CASE 
	WHEN ReporterHeadStatus = 0 THEN "Reporter\'s Head Remarks"
	WHEN (ReporterHeadStatus<>0 AND ReporteeStatus=0) THEN "Employee\'s Reply" 
	WHEN (ReporteeStatus<>0 AND ReporteeHeadStatus=0) THEN "Reportee\'s Head Comments/Recommendation" 
	WHEN (ReporteeHeadStatus<>0 AND DecisionStatus=0) THEN "Final Decision" 
	ELSE "No Decision Yet" END) AS Status, 
	Statement, Branch, WeightinPoints, CONCAT(Nickname, " ", Surname) AS EncodedBy, ss.PointID, cp.FullName AS Employee, (CASE WHEN ReporterHeadStatus = 1 THEN "GO" WHEN ReporterHeadStatus = 2 THEN "NO GO" ELSE "Pending" END) AS ReporterHeadStatus FROM hr_72scores s JOIN hr_71scorestmt ss ON s.SSID=ss.SSID JOIN attend_30currentpositions cp ON s.ReporteeNo=cp.IDNo LEFT JOIN 1_gamit.0idinfo id ON s.EncodedByNo=id.IDNo JOIN hr_70points p ON ss.PointID=p.PointID WHERE ('.((allowedToOpen(65071,'1rtc'))?'ss.deptid='.$rowdept['deptid'].' OR':'').' ('.$_SESSION['(ak0)'].' in (s.EncodedByNo,ReporteeNo,ReporterHeadNo,ReporteeHeadNo,DecisionByNo))) AND IF(ReporteeNo='.$_SESSION['(ak0)'].', ReporterHeadStatus=1, 1=1) AND stmtcat=0 AND DecisionStatus=0';
    
	$stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;
 
    if ($stmtsp->rowCount()>0){
        $msgcb='<br><div id="table-wrapper" style="width:89%;">List of Demerits:<div id="table-scroll"><table bgcolor="FFFFF">'
                . '<tr><td>Branch</td><td>Employee</td><td>Statement</td><td>WeightinPoints</td><td>DateofIncident</td><td>Status (Waiting for:)</td></tr>';
    foreach($datatoshowsp as $rows){
        $sp++;
        $msgcb.='<tr><td>'.$rows['Branch']
                .'</td><td>'.$rows['Employee'].'</td>'
                . '<td>'.htmlcharwithbr($fromBRtoN,$rows['Statement']).'</td>'
                . '<td>'.$rows['WeightinPoints'].'</td>'
                .'<td>'.$rows['DateofIncident'].'</td>'
                .'<td>'.$rows['Status'].'</td>'
            .'<td><a href="hr/scores.php?w=Lookup&TxnID='.$rows['TxnID'].'" target=blank>Lookup</a></td>'.'</tr>';
   }
   $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
   }
   $cntres=$cntres + $sp;
}
if (allowedToOpen(8195,'1rtc')){
//OIC Allowance

if (allowedToOpen(8191,'1rtc')){
	$condition='and OpsStatus=1 and ExecStatus=0';
}else{
	$condition='';
}
	$sqlroa='select *, Date as DateOfEffectivity,Amount as AmountPerMonth,FullName,Duration as `DurationInMonths`,roa.Remarks,case when ExecStatus=0 then "Pending" when ExecStatus=1 then "Approved" when ExecStatus=2 then "Rejected" end as ExecStatus,case when OpsStatus=0 then "Pending" when OpsStatus=1 then "Approved" when OpsStatus=2 then "Rejected" end as OpsStatus,b.Branch,if(Valid=0,"ToValidate","Validated") as Valid from payroll_2requestoicallowance roa left join attend_30currentpositions cp on cp.IDNo=roa.IDNo left join 1branches b on b.BranchNo=roa.BranchNo where Valid=0 '.$condition.''; 
	// echo $sqlroa; exit();
	$stmtroa=$link->query($sqlroa); $resultroa=$stmtroa->fetchAll(); $sp=0;
 
    if ($stmtroa->rowCount()>0){
        $msgcb='<br><div id="table-wrapper" style="width:89%;">OIC Allowance:<div id="table-scroll"><table bgcolor="FFFFF">
		<tr>	<td>FullName</td><td>Branch</td><td>AmountPerMonth</td><td>DurationInMonths</td><td>DateOfEffectivity</td><td>Remarks</td><td>OpsStatus</td><td>ExecStatus</td><td>Valid</td>
		</tr>';
    foreach($resultroa as $resroa){
		$sp++;
        $msgcb.='<tr>
					<td>'.$resroa['FullName'].'</td>
					<td>'.$resroa['Branch'].'</td>
					<td>'.$resroa['AmountPerMonth'].'</td>
					<td>'.$resroa['DurationInMonths'].'</td>
					<td>'.$resroa['DateOfEffectivity'].'</td>
					<td>'.$resroa['Remarks'].'</td>
					<td>'.$resroa['OpsStatus'].'</td>
					<td>'.$resroa['ExecStatus'].'</td>
					<td>'.$resroa['Valid'].'</td>
					<td><a href="payroll/requestoicallowance.php">Lookup</a></td>
			</tr>';
   }
   $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
   }
   $cntres=$cntres + $sp;
}

if (allowedToOpen(8201,'1rtc')){
//Relocation Allowance
	if (allowedToOpen(8198,'1rtc')){
		$condition='where OpsStatus=\'0\'';
	}elseif(allowedToOpen(8199,'1rtc')){
		$condition='where HRStatus=\'0\'';
	}elseif(allowedToOpen(8200,'1rtc')){
		$condition='where FinanceStatus=\'0\'';
	}
	$sqlra='select *, Date as DateOfTransfer,Amount as AmountPerMonth,BudgetDesc,FullName,Duration as `DurationInMonths`,roa.Remarks,
	case when HRStatus=0 then "Pending" when HRStatus=1 then "Approved" when HRStatus=2 then "Rejected" end as HRStatus,
	case when OpsStatus=0 then "Pending" when OpsStatus=1 then "Approved" when OpsStatus=2 then "Rejected" end as OpsStatus,
	case when FinanceStatus=0 then "Pending" when FinanceStatus=1 then "Verified" when FinanceStatus=2 then "Rejected" end as FinanceStatus,
	b.Branch as BranchTransferred,b1.Branch as BranchOrigin from approvals_2requestbudget roa left join attend_30currentpositions cp on cp.IDNo=roa.IDNo left join 1branches b on b.BranchNo=roa.BranchNo left join 1branches b1 on b1.BranchNo=cp.BranchNo left join acctg_1branchpreapprovedbudgetlist bl on bl.TypeID=roa.TypeID '.$condition.' '; 
	// echo $sqlra; exit();
	$stmtra=$link->query($sqlra); $resultra=$stmtra->fetchAll(); $sp=0;
 	$counter=0;
    if ($stmtra->rowCount()>0){
        $msgcb='<br><div id="table-wrapper" style="width:89%;">Relocation Allowance:<div id="table-scroll"><table bgcolor="FFFFF">
		<tr>	<td>FullName</td><td>BranchOrigin</td><td>BranchTransferred</td><td>AmountPerMonth</td><td>BudgetDesc</td><td>DurationInMonths</td><td>DateOfTransfer</td><td>Remarks</td><td>OpsStatus</td><td>HRStatus</td><td>FinanceStatus</td>
		</tr>';
    foreach($resultra as $resra){
		$counter++;
        $msgcb.='<tr>
					<td>'.$resra['FullName'].'</td>
					<td>'.$resra['BranchOrigin'].'</td>
					<td>'.$resra['BranchTransferred'].'</td>
					<td>'.$resra['AmountPerMonth'].'</td>
					<td>'.$resra['BudgetDesc'].'</td>
					<td>'.$resra['DurationInMonths'].'</td>
					<td>'.$resra['DateOfTransfer'].'</td>
					<td>'.$resra['Remarks'].'</td>
					<td>'.$resra['OpsStatus'].'</td>
					<td>'.$resra['HRStatus'].'</td>
					<td>'.$resra['FinanceStatus'].'</td>
					<td><a href="approvals/relocation.php">Lookup</a></td>
			</tr>';
   }
   $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
   }
   $cntres=$cntres + $counter;
}
//Budget Request Approval
if (allowedToOpen(100,'1rtc')){
        $sqlidno = 'SELECT (IDNo) AS DeptEmp FROM attend_30currentpositions WHERE ';

        if (allowedToOpen(101,'1rtc')){ //1002 Approver Only
                // $sqlidno .=' FIND_IN_SET(PositionID, (SELECT AllowedPos FROM  permissions_2allprocesses WHERE ProcessID=100))'; //IDNo of DeptHeads
                $sqlidno .=' (LatestSupervisorIDNo='.$_SESSION['(ak0)'].' OR FIND_IN_SET(PositionID, (SELECT AllowedPos FROM  permissions_2allprocesses WHERE ProcessID=100)))'; //IDNo of DeptHeads
                goto nextstep;
        }
        if (allowedToOpen(100,'1rtc')){ //Other Heads
                $sqlidno .= 'deptheadpositionid='.$_SESSION['&pos'];
                goto nextstep;
        }

        nextstep:

 
        $sqlsp = 'SELECT TxnID, DATE_FORMAT(bl.`TimeStamp`, "%Y-%m-%d") AS  DateofRequest, bl.DateNeeded, Purpose, CONCAT(e.Nickname,"  
",e.SurName) AS Requester, Duration AS DurationInDays,  IF(RequestCompleted<>0,"Done","") AS RequestCompleted, b.Branch,  
(SELECT FORMAT(IFNULL(SUM(Amount),0),0) FROM `approvals_3budgetrequestsub` WHERE  TxnID=bl.TxnID) AS RequestAmt, (SELECT FORMAT(IFNULL(SUM(Amount),0),0)  
FROM `approvals_3budgetrequestsub` WHERE TxnID=bl.TxnID AND CashorCard=0) AS  Cash, (SELECT FORMAT(IFNULL(SUM(Amount),0),0) FROM `approvals_3budgetrequestsub`  
WHERE TxnID=bl.TxnID AND CashorCard<>0) AS Card FROM `approvals_3budgetandliq` bl LEFT JOIN `1employees` e ON e.IDNo=bl.EncodedByNo  
LEFT JOIN `1employees` e1 ON e1.IDNo=bl.ApprovedByNo  LEFT JOIN `1employees` e2 ON e2.IDNo=bl.SetLiqByNo LEFT  
JOIN `1employees` e3 ON e3.IDNo=bl.ReleasedByNo LEFT  JOIN `1employees` e4 ON e4.IDNo=bl.DocsCompleteByNo LEFT  
JOIN `1branches` b ON b.BranchNo=bl.BranchNo WHERE  (RequestCompleted=1 AND Approved=0) AND bl.EncodedByNo IN  
('.$sqlidno.') AND bl.EncodedByNo<>'.$_SESSION['(ak0)'].'';
        $stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;

        if ($stmtsp->rowCount()>0){
        $msgcb='<br><div>Budget Request Approval:<table bgcolor="FFFFF">'
                        .  
'<tr><td>Requester</td><td>DateofRequest</td><td>DateNeeded</td><td>Purpose</td><td>DurationInDays</td><td>RequestAmt</td><td>Cash</td><td>Card</td><td>Branch</td><td></td></tr>';
        foreach($datatoshowsp as $rows){
                $sp++;
                $msgcb.='<tr><td>'.$rows['Requester'].'</td><td>'.$rows['Branch']
                                .'</td><td>'.$rows['DateNeeded'].'</td>'
                                . '<td>'.htmlcharwithbr($fromBRtoN,$rows['Purpose']).'</td>'
                                . '<td>'.$rows['DurationInDays'].'</td>'
                                . '<td>'.$rows['RequestAmt'].'</td>'
                                . '<td>'.$rows['Cash'].'</td>'
                                . '<td>'.$rows['Card'].'</td>'
                                . '<td>'.$rows['Branch'].'</td>'
                        .'<td><a  
href="approvals/requestforbudget.php?w=Lookup&TxnID='.$rows['TxnID'].'"  
target=blank>Lookup</a></td>'.'</tr>';
    }
    $switchboard = $switchboard . $msgcb.'<br></table></div>';
    }
    $cntres=$cntres + $sp;

}

//Request for Certificate of Employment / Printed Payslips

if (allowedToOpen(53601,'1rtc')) {
    $sql='SELECT COUNT(ERFID) AS cntreq FROM attend_30currentpositions cp JOIN hr_2employeerequestform erf ON cp.IDNo=erf.RequestedByNo WHERE ReqStatus=0;';
    $stmt=$link->query($sql); $res=$stmt->fetch();

        if ($res['cntreq']>0){ 
        $msgcb='<br><div id="table-wrapper" style="width:80%;">Request for Certificate of Employment / Printed Payslips:<div><table bgcolor="FFFFF">'
            .'';
                    $sp++;
                    $msgcb.='<tr><td><a href = "hr/formrequest.php">Look up '.$res['cntreq'].' request(s).</a>'.'</td>'

                            .'</tr>';
            $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
        }
        $cntres=$cntres + $sp;
    
}


//Report Incorrect Payroll
if (allowedToOpen(array(63001,100),'1rtc')) {

    if (allowedToOpen(63001,'1rtc')){
        $reqcondi=' DeptHeadStat=1 AND HRStat=0 ';
    } elseif(allowedToOpen(100,'1rtc')) {
        $reqcondi=' DeptHeadStat=0 AND HRStat=0 AND cp.deptheadpositionid='.$_SESSION['&pos'].'';
    } else {
        $reqcondi='1<>1';
    }

    $sql='SELECT COUNT(PAID) AS cntreq FROM attend_30currentpositions cp JOIN approvals_5payadjreq par ON cp.IDNo=par.EncodedByNo WHERE '.$reqcondi.';';
    $stmt=$link->query($sql); $res=$stmt->fetch();

        if ($res['cntreq']>0){ 
        $msgcb='<br><div id="table-wrapper" style="width:80%;">Report Incorrect Payroll:<div><table bgcolor="FFFFF">'
            .'';
                    $sp++;
                    $msgcb.='<tr><td><a href = "approvals/payadjrequest.php">Look up '.$res['cntreq'].' request(s).</a>'.'</td>'

                            .'</tr>';
            $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
        }
        $cntres=$cntres + $sp;


}



// Request for personnel action
if (allowedToOpen(6302,'1rtc')) {
    $addcondlist='';
    
    if (allowedToOpen(6303,'1rtc')){
        $addcondlist=' AND ReqStatus=1 AND Served=0';
    } else { //others or heads
        $addcondlist=' AND (cp.deptheadpositionid='.$_SESSION['&pos'].' OR cp.deptid=(SELECT deptid FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].')) AND cp.IDNo<>'.$_SESSION['(ak0)'].' AND ReqStatus=0 AND Served=0 ';
    }

    $sql='SELECT COUNT(RPAID) AS cntreq FROM attend_30currentpositions cp JOIN hr_2requestpa rpa ON cp.IDNo=rpa.IDNo WHERE 1=1 '.$addcondlist.';';
    $stmt=$link->query($sql); $res=$stmt->fetch();

        if ($res['cntreq']>0){ 
        $msgcb='<br><div id="table-wrapper" style="width:80%;">Request For Personnel Action:<div><table bgcolor="FFFFF">'
            .'';
                    $sp++;
                    $msgcb.='<tr><td><a href = "hr/requestforpa.php">Look up '.$res['cntreq'].' request(s).</a>'.'</td>'

                            .'</tr>';
            $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
        }
        $cntres=$cntres + $sp;
    

}
//End



//Printed Invoices
if ((allowedToOpen(78833,'1rtc')) OR (allowedToOpen(78834,'1rtc'))){
	if (allowedToOpen(78833,'1rtc')){
			$viewcon = 'IssuedTo IN (SELECT BranchNo FROM attend_1branchgroups WHERE OpsSpecialist='.$_SESSION['(ak0)'].')';
	} else {
		$viewcon = '1=1 ';
	}
        $sqlsp = 'SELECT fm.`TxnID`,SeriesFrom,TxnSubId,IssuedTo,b2.Branch AS IssuedToBranch,DateIssued,`txndesc` as `InvoiceType`,CEIL(`SeriesFrom`/50)*50 AS SeriesTo FROM monitor_2fromsuppliermain fm JOIN `1branches` b ON b.BranchNo=fm.BranchNo JOIN `1employees` e ON e.IDNo=fm.EncodedByNo JOIN `1suppliers` s ON s.SupplierNo=fm.SupplierNo JOIN `invty_0txntype` tt ON tt.txntypeid=fm.InvType JOIN monitor_2fromsuppliersub fs ON fm.TxnID=fs.TxnID JOIN 1branches b2 ON fs.IssuedTo=b2.BranchNo WHERE '.$viewcon.' AND (AcceptedByNo=0 OR AcceptedByNo IS NULL) ORDER BY Date DESC';
        $stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;

        if ($stmtsp->rowCount()>0){
        $msgcb='<br><div id="table-wrapper" style="width:40%;">Monitoring of Printed Invoices:<div id="table-scroll"><table bgcolor="FFFFF">'
                        .  
		'<thead><tr><td>InvoiceType</td><td>SeriesFrom</td><td>SeriesTo</td><td>IssuedTo</td><td>DateIssued</td></tr></thead>';
        foreach($datatoshowsp as $rows){
                $sp++;
                $msgcb.='<tr><td>'.$rows['InvoiceType'].'</td><td>'.$rows['SeriesFrom']
                                .'</td><td>'.$rows['SeriesTo'].'</td>'
                                . '<td>'.$rows['IssuedToBranch'].'</td>'
                                . '<td>'.$rows['DateIssued'].'</td>'
                        .'</tr>';
    }
    $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
    }
    $cntres=$cntres + $sp;

}

//System Permission
if ((allowedToOpen(220,'1rtc'))){
		if(allowedToOpen(3000,'1rtc')){
			$reqcondi='';
			goto here;
		} 
		if(allowedToOpen(220,'1rtc')){
			$reqcondi='WHERE RequestedByNo='.$_SESSION['(ak0)'].'';
			goto here;
		}
		here:
        $sqlsp = 'select sp.*,ForPositionID AS TxnID,Position AS ForPosition,NickName As RequestedBy FROM approvals_systempermission sp JOIN 1_gamit.0idinfo id ON sp.RequestedByNo=id.IDNo JOIN attend_0positions p ON sp.ForPositionID=p.PositionID '.$reqcondi.' ORDER BY Position ASC';
        $stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;

        if ($stmtsp->rowCount()>0){
        $msgcb='<br><div id="table-wrapper" style="width:40%;">Request Access For Position<div id="table-scroll"><table bgcolor="FFFFF">'
                        .  
		'<thead><tr><td>ForPosition</td><td>RequestedBy</td><td></td></tr></thead>';
        foreach($datatoshowsp as $rows){
                $sp++;
                $msgcb.='<tr><td>'.$rows['ForPosition'].'</td><td>'.$rows['RequestedBy']
                                .'</td><td><a href="sysadmin/assignpermission.php?w=AccessPerPosition&Request=1&ForPositionID='.$rows['TxnID'].'">Lookup</a></td>'
                        .'</tr>';
    }
    $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
    }
    $cntres=$cntres + $sp;

}

// for info update for gcash DEACTIVATED FOR NOW
//include 'generalinfo/gcashupdateinfo.php';


// <----------------------------------------------------------------------------------------->
// For Financial Assistance 

if (allowedToOpen(5353,'1rtc') or allowedToOpen(5354,'1rtc') or allowedToOpen(5355,'1rtc')){

        $sql='SELECT deptStatus,RequestTS,cpr.*, DATE_FORMAT(RequestTS,"%M %d, %Y") AS `DateRequested`,
    (CASE WHEN FinalApproval = 1 THEN "Approved"
        WHEN FinalApproval = 2 THEN "Denied"
        ELSE "Pending" 
        END) 
         AS FinalApproval,
         (CASE
    WHEN deptStatus = 1 THEN "Approve"
        WHEN deptStatus = 2 THEN "Denied"
        ELSE "Pending"
        END) 
     AS DeptStatus,
         (CASE
    WHEN HRStatus = 1 THEN "Approve"
    WHEN HRStatus = 2 THEN "Denied"
        ELSE "Pending"
        END) 
        
     AS HRStatus,
         

          CONCAT(e.Nickname, " ", e.Surname) AS RequestedBy FROM approvals_4financial cpr  JOIN `1employees` e ON cpr.RequestByNo=e.IDNo where ('.$_SESSION['(ak0)'].' IN (DeptApproveByID) and DeptStatus=0) OR '.((allowedToOpen(5354,'1rtc'))?'(DeptStatus=1 and HRStatus=0)':(allowedToOpen(5355,'1rtc')?'(HRStatus=1 and FinalApproval=0)' :'1=0')).'';
          $stmt=$link->query($sql); 

    if ($stmt->rowCount()>0){ 
    $msgcb='<br><div id="table-wrapper" style="width:80%;">Financial Assistance :<div id="table-scroll"><table bgcolor="FFFFF">'
         .'<thead><tr><td>Reason</td><td>Requested By</td><td>Date</td><td>Status</td><td>Action</td></tr></thead>';
    

    while ($res=$stmt->fetch()){ 
                $sp++;
                $msgcb.='<tr><td>'.$res['Reason'].'</td><td>'.$res['RequestedBy']
                                .'</td>'
                                . '<td>'.$res['DateRequested'].'</td>'
                                . '<td>'.$res['FinalApproval'].'</td>'
                                . '<td>' .'<a href = "approvals/requestforfinancialasst.php?w=Lookup&TxnID='.$res['TxnID'].'">Look up </a>'.'</td>'

                        .'</tr>';
    }
          $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
    }
	$cntres=$cntres + $sp;
}

//shoutout
if(allowedToOpen(5363,'1rtc')){
$sql='SELECT COUNT(TxnID) AS cntreq FROM mktg_2shoutouts WHERE ShoutStat=0;';
$stmt=$link->query($sql); $res=$stmt->fetch();

   if ($res['cntreq']>0){ 
   $msgcb='<br><div id="table-wrapper" style="width:80%;">Shoutout For Approval:<div><table bgcolor="FFFFF">'
        .'';
               $sp++;
               $msgcb.='<tr><td><a href = "mktg/shoutout.php?w=List">Look up '.$res['cntreq'].' shoutout(s).</a>'.'</td>'
                       .'</tr>';
         $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
   }
   $cntres=$cntres + $sp;
}
//end shoutout


 $sql='SELECT COUNT(IDNo) AS cntreq FROM approvals_5ot ot WHERE Approved=0 AND (IDNo='.$_SESSION['(ak0)'].' OR RequestedByNo='.$_SESSION['(ak0)'].' OR '.$_SESSION['&pos'].'=(SELECT deptheadpositionid FROM attend_30currentpositions cp WHERE cp.IDNo=ot.IDNo));';
 $stmt=$link->query($sql); $res=$stmt->fetch();

    if ($res['cntreq']>0){ 
    $msgcb='<br><div id="table-wrapper" style="width:80%;">Pre-Approved OT Request :<div><table bgcolor="FFFFF">'
         .'';
                $sp++;
                $msgcb.='<tr><td><a href = "approvals/otrequest.php">Look up '.$res['cntreq'].' request(s).</a>'.'</td>'

                        .'</tr>';
          $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
    }
	$cntres=$cntres + $sp;
	
	
 // $sql='SELECT COUNT(IDNo) AS cntreq FROM approvals_5wfh wfh WHERE Approved=0 AND (IDNo='.$_SESSION['(ak0)'].' OR RequestedByNo='.$_SESSION['(ak0)'].' OR '.$_SESSION['&pos'].'=(SELECT deptheadpositionid FROM attend_30currentpositions cp WHERE cp.IDNo=wfh.IDNo));';
 $sql='SELECT COUNT(IDNo) AS cntreq FROM approvals_5wfh wfh WHERE Approved=0 AND (IDNo='.$_SESSION['(ak0)'].' OR RequestedByNo='.$_SESSION['(ak0)'].' OR '.$_SESSION['&pos'].'=(SELECT '.((allowedToOpen(6220,'1rtc'))?'99':'deptheadpositionid').' FROM attend_30currentpositions cp WHERE cp.IDNo=wfh.IDNo));';
 $stmt=$link->query($sql); $res=$stmt->fetch();

    if ($res['cntreq']>0){ 
    $msgcb='<br><div id="table-wrapper" style="width:80%;">Pre-Approved WFH Request :<div><table bgcolor="FFFFF">'
         .'';
                $sp++;
                $msgcb.='<tr><td><a href = "approvals/wfhrequest.php">Look up '.$res['cntreq'].' request(s).</a>'.'</td>'

                        .'</tr>';
          $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
    }
	$cntres=$cntres + $sp;
	
	

$notifcolor=!isset($notifcolor)?'f2e6d9':$notifcolor;

if ($cntres>0){
	echo '<div style="padding:5px;border:  1px solid red;width:1500px;background-color:#'.$notifcolor.';">';
					echo '<h3>Notifications</h3>';
					echo $switchboard;
					echo '</div>';
}
 ?>
