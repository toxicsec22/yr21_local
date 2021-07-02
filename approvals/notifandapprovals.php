<title>Notifications & Approvals</title>
    <br><br>
    <H3>Notifications & Approvals</H3>
    <?php
    session_start();
$path=$_SERVER['DOCUMENT_ROOT'];
if (!isset($_SESSION['(ak0)'])){
    //include_once $path.'/yr21/backendphp/functions/allallowedid.php';
    //TEMPORARILY PLACED THIS WHILE TESTING:
    include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
} else {
    include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
    
    $showbranches=false; 
    include_once('../switchboard/contents.php');
    include_once('../switchboard/scrollcss.php');

    
}

$cntres=0; $sp=0;
$switchboard='';


//List Of Request Repairs
include_once('../admin/motorvehiclesapproval.php');
//Leave approvals
include_once('../attendance/leaveapprovals.php');



// Sale Addons
 
if (allowedToOpen(array(7004,7006),'1rtc')){ 
$subtitle='Add-on for approval';
    if (allowedToOpen(7004,'1rtc')){ //SAM
        $condition='WHERE FApproved=0';
    }else{ //SC
        $condition='WHERE FApproved=1 AND Approved=0';
    }
        $sql='SELECT Branch,SaleNo,ao.ItemCode,CONCAT(Category,\' \',ItemDesc) as ItemDesc,ao.TxnID,txndesc as txntype,Qty,case when Approved=0 then "For Approval" when Approved=1 then "Approved" when Approved=2 then "Rejected" end as Status from invty_2salesubaddons ao join invty_2sale s on s.TxnID=ao.TxnID join invty_1items i on i.ItemCode=ao.ItemCode join invty_1category c on c.CatNo=i.CatNo join 1branches b on b.BranchNo=s.BranchNo join invty_0txntype t on t.txntypeid=s.txntype '.$condition.'';
        $columnnames=array('Branch','ItemCode','ItemDesc');
        $editprocess='../invty/addons.php?w=Addons&TxnID=';
        $editprocesslabel='Lookup';
        $txnidname='TxnID';
        include '../backendphp/layout/displayastableonlynoheaders.php';
       }

// No encoded Salary Rate
if (allowedToOpen(624,'1rtc')) { 
    $sql='SELECT COUNT(IDNo) AS cntreq FROM 1employees WHERE IDNo NOT IN (SELECT DISTINCT(IDNo) FROM payroll_22rates);';
    $stmt=$link->query($sql); $res=$stmt->fetch();

    if ($res['cntreq']>0){
        $cntres+=$res['cntreq'];
    $msgcb='<br><div id="table-wrapper" style="width:80%;">For Encoding Salary Rates :<div><table bgcolor="FFFFF">'
         .'';
                $msgcb.='<tr><td><a href = "../attendance/newemployee.php?w=ForSalaryRate"> No encoded salary rate for '.$res['cntreq'].' employee(s).</a>'.'</td>'

                        .'</tr>';
          $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
    }
	  
}

// Employee Approval Approval
if (allowedToOpen(62411,'1rtc')) { 
    $sql='SELECT COUNT(IDNo) AS cntreq FROM 1employeesforapproval;';
    $stmt=$link->query($sql); $res=$stmt->fetch();

    if ($res['cntreq']>0){
        $cntres+=$res['cntreq'];
    $msgcb='<br><div id="table-wrapper" style="width:80%;">Employee For Approval :<div><table bgcolor="FFFFF">'
         .'';
                $msgcb.='<tr><td><a href = "../attendance/newemployee.php?w=ForApprovalList"> '.$res['cntreq'].' employee(s) waiting for approval.</a>'.'</td>'

                        .'</tr>';
          $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
    }
	  
}

// APPROVE RATES -- will now open a new page
if (allowedToOpen(7911,7912,'1rtc')) { 
    $sql='SELECT COUNT(r.IDNo) AS cntreq FROM payroll_22rates r JOIN 1employees e ON e.IDNo=r.IDNo  WHERE Resigned=0 AND (ApprovedByNo IS NULL OR ApprovedByNo=0)';
    $stmt=$link->query($sql); $res=$stmt->fetch();

    if ($res['cntreq']>0){
        $cntres+=$res['cntreq'];
    $msgcb='<br><div id="table-wrapper" style="width:80%;">Salary Rates for Approval :<div><table bgcolor="FFFFF">'
         .'';
                $msgcb.='<tr><td><a href = "../payroll/ratesforapproval.php"> '.$res['cntreq'].' record(s).</a>'.'</td>'

                        .'</tr>';
          $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
    }
	  
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
            .'<td><a href="../approvals/requestforfinancialasst.php?w=Lookup&TxnID='.$rows['TxnID'].'" target=blank>Lookup Request</a></td>'.'</tr>';}
    }else{
        $msgcb.='<tr><td>'.$rows['DateofRequest'].'</td><td>'.$rows['DateNeeded']
                .'</td><td>'.$rows['Duration'].'</td><td>'.$rows['Requester'].'</td>'
                . '<td>'.htmlcharwithbr($fromBRtoN,$rows['Purpose']).'</td><td>'.$rows['Amount'].'</td>'
                . '<td>'.$rows['Branch'].'</td>'
                .'<td>'.$rows['Status'].'</td>'
            .'<td><a href="../approvals/requestforbudget.php?w=Lookup&TxnID='.$rows['TxnID'].'" target=blank>Lookup Request</a></td>'.'</tr>';
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
        $msgcb='<br><div style="background-color: white; " >Pending liquidations beyond 2 days:&nbsp; &nbsp;<a href="../approvals/requestforbudget.php?w=SetAsLiquidated" target=blank>Open List</a><table bgcolor="FFFFF">'
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
        $msgcb.='<a href="../approvals/requestforbudget.php?w=Lookup&TxnID='.$rows['TxnID'].'" target=blank>Lookup Request</a>&nbsp; &nbsp;';
   }
   $switchboard = $switchboard . $msgcb.'</div>';
   }
   $cntres=$cntres + $sp;
   $stmtsp=null;
}


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
            .'<td><a href="../hr/scores.php?w=Lookup&TxnID='.$rows['TxnID'].'" target=blank>Lookup</a></td>'.'</tr>';
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
            .'<td><a href="../hr/scores.php?w=Lookup&TxnID='.$rows['TxnID'].'" target=blank>Lookup</a></td>'.'</tr>';
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
					<td><a href="../payroll/requestoicallowance.php">Lookup</a></td>
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
					<td><a href="../approvals/relocation.php">Lookup</a></td>
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
href="../approvals/requestforbudget.php?w=Lookup&TxnID='.$rows['TxnID'].'"  
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
                    $msgcb.='<tr><td><a href = "../hr/formrequest.php">Look up '.$res['cntreq'].' request(s).</a>'.'</td>'

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
                    $msgcb.='<tr><td><a href = "../approvals/payadjrequest.php">Look up '.$res['cntreq'].' request(s).</a>'.'</td>'

                            .'</tr>';
            $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
        }
        $cntres=$cntres + $sp;


}



// Request for personnel action
if (allowedToOpen(6302,'1rtc')) {
    $addcondlist='';
    
    if (allowedToOpen(6303,'1rtc')){
        $addcondlist=' AND ReqStatus=1 AND ApprovedByEO<>2 AND Served=0';
        $wlink='?Status=4';
    } else if (allowedToOpen(101,'1rtc')){
        $addcondlist=' AND ReqStatus=1 AND ApprovedByEO=0 AND Served=0';
        $wlink='?Status=1';
    } else { //others or heads
        $addcondlist=' AND (cp.deptheadpositionid='.$_SESSION['&pos'].' OR cp.deptid=(SELECT deptid FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].')) AND cp.IDNo<>'.$_SESSION['(ak0)'].' AND ReqStatus=0 AND Served=0 ';
        if (allowedToOpen(100,'1rtc')){
            $wlink='?Status=0';
        } else {
            $wlink='';
        }
    }

    $sql='SELECT COUNT(RPAID) AS cntreq FROM attend_30currentpositions cp JOIN hr_2requestpa rpa ON cp.IDNo=rpa.IDNo WHERE 1=1 '.$addcondlist.';';
    $stmt=$link->query($sql); $res=$stmt->fetch();
// echo $sql;
        if ($res['cntreq']>0){ 
        $msgcb='<br><div id="table-wrapper" style="width:80%;">Request For Personnel Action:<div><table bgcolor="FFFFF">'
            .'';
                    $sp++;
                    $msgcb.='<tr><td><a href = "../hr/requestforpa.php'.$wlink.'">Look up '.$res['cntreq'].' request(s).</a>'.'</td>'

                            .'</tr>';
            $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
        }
        $cntres=$cntres + $sp;
    

}
//End



//Printed Invoices
if ((allowedToOpen(78833,'1rtc')) OR (allowedToOpen(78834,'1rtc'))){
	if (allowedToOpen(78833,'1rtc')){
			$viewcon = 'IssuedTo IN (SELECT BranchNo FROM attend_1branchgroups WHERE '.$_SESSION['(ak0)'].' IN (FieldSpecialist,BranchSupport) OR OpsManager='.$_SESSION['(ak0)'].')';
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
        $sqlsp = 'select sp.*,ForPositionID AS TxnID,Position AS ForPosition,NickName As RequestedBy FROM approvals_systempermission sp JOIN 1_gamit.0idinfo id ON sp.RequestedByNo=id.IDNo JOIN attend_1positions p ON sp.ForPositionID=p.PositionID '.$reqcondi.' ORDER BY Position ASC';
        $stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;

        if ($stmtsp->rowCount()>0){
        $msgcb='<br><div id="table-wrapper" style="width:40%;">Request Access For Position<div id="table-scroll"><table bgcolor="FFFFF">'
                        .  
		'<thead><tr><td>ForPosition</td><td>RequestedBy</td><td></td></tr></thead>';
        foreach($datatoshowsp as $rows){
                $sp++;
                $msgcb.='<tr><td>'.$rows['ForPosition'].'</td><td>'.$rows['RequestedBy']
                                .'</td><td><a href="../sysadmin/assignpermission.php?w=AccessPerPosition&Request=1&ForPositionID='.$rows['TxnID'].'">Lookup</a></td>'
                        .'</tr>';
    }
    $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
    }
    $cntres=$cntres + $sp;

}

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
                                . '<td>' .'<a href = "../approvals/requestforfinancialasst.php?w=Lookup&TxnID='.$res['TxnID'].'">Look up </a>'.'</td>'

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
               $msgcb.='<tr><td><a href = "../mktg/shoutout.php?w=List">Look up '.$res['cntreq'].' shoutout(s).</a>'.'</td>'
                       .'</tr>';
         $switchboard = $switchboard . $msgcb.'<br></table></div></div>';
   }
   $cntres=$cntres + $sp;
}
//end shoutout

//AWOL
include_once('../attendance/noattendtoday.php');
if ($cntawol>0){
    $msgcb='<br><div style="background-color: white; " >No TIME-IN Today<table bgcolor="FFFFF">';
foreach($dataawol as $rows){
    $sp++;
    $msgcb.='<tr><td>'.$rows['FullName'].'</td></tr>';
}
$switchboard = $switchboard . $msgcb.'</table></div>';
}
$cntres=$cntres + $sp;

// AWOL info
 if (allowedToOpen(array(6081,6082,6110),'1rtc')) {

    if(allowedToOpen(6082,'1rtc')){ //direct sa AWOL Report
        $dlink='../attendance/lookupperteam.php?w=AWOLCount';
        $dcondi='';
    }  else if(allowedToOpen(6110,'1rtc')){ //direct sa remarks
        $stmt0=$link->query('SELECT deptid FROM `attend_30currentpositions` WHERE IDNo='.$_SESSION['(ak0)']);
        $res0=$stmt0->fetch();
        
        $dcondi='AND deptid IN ('.(($res0['deptid']==70)?'70,10':$res0['deptid']).')';
        $dlink='../attendance/encodeattend.php?w=RemarksOfDept';
    } 
    else { //direct sa remarks
        $dlink='../attendance/encodeattend.php?w=RemarksOfDept';
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


// CLIENT BDAYS TODAY  
if (allowedToOpen(212,'1rtc')) {
    include_once('../generalinfo/unionlists/clientbdayssql.php');
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
    $msgsp='<div><br>Unread confidential reports: <a href="../generalinfo/confireportjye.php">Open List</a> <table bgcolor="FFFFF"><tr>'.$coltitle.'<td>Mark as Read</td></tr><tr>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        $colsql=''; foreach ($cols as $col) { $colsql=$colsql.'<td>'.htmlcharwithbr($fromBRtoN,$rows[$col]).'</td>';}
        $msgsp=$msgsp.$colsql.'<td><a href="../generalinfo/confireportjye.php?w=SetAsRead&ReportType='.$rows['ReportType'].'&ReadbyMgt=1&TxnID='.$rows['TxnID'].'&action_token='.$_SESSION['action_token'].'">Read</a></td></tr><tr>';
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
        $msgsp='<div><br>Unresolved incident reports (<b>'.$stmtsp->rowCount().'</b>): <a href="../hr/incidentreporthr.php">Open List</a></div>';

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
    $msgsp='<div><br>Unread branch concerns: <a href="../invty/branchreports.php?w=BranchConcerns">Open List</a> <table bgcolor="FFFFF"><tr>'.$coltitle.'<td>Mark as Read</td></tr><tr>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        $colsql=''; foreach ($cols as $col) { $colsql=$colsql.'<td>'.htmlcharwithbr($fromBRtoN,$rows[$col]).'</td>';}
        if (allowedToOpen(7102,'1rtc')) { $setread='<td><a href="../invty/branchreports.php?w=SetAsRead&TxnID='.$rows['TxnID'].'&action_token='.$_SESSION['action_token'].'">Set As Read</a></td>';} else {$setread='<td>Unread by Ops</td>';}
        $msgsp=$msgsp.$colsql.$setread.'</tr><tr>';
    }
        $switchboard = $switchboard . $msgsp.'</tr></table></div>';               
   }  
   $cntres=$cntres + $countsp;
   $stmtsp=null;
   } 


 $sql='SELECT COUNT(IDNo) AS cntreq FROM approvals_5ot ot WHERE Approved=0 AND (IDNo='.$_SESSION['(ak0)'].' OR RequestedByNo='.$_SESSION['(ak0)'].' OR '.$_SESSION['&pos'].'=(SELECT deptheadpositionid FROM attend_30currentpositions cp WHERE cp.IDNo=ot.IDNo));';
 $stmt=$link->query($sql); $res=$stmt->fetch();

    if ($res['cntreq']>0){ 
    $msgcb='<br><div id="table-wrapper" style="width:80%;">Pre-Approved OT Request :<div><table bgcolor="FFFFF">'
         .'';
                $sp++;
                $msgcb.='<tr><td><a href = "../approvals/otrequest.php">Look up '.$res['cntreq'].' request(s).</a>'.'</td>'

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
                $msgcb.='<tr><td><a href = "../approvals/wfhrequest.php">Look up '.$res['cntreq'].' request(s).</a>'.'</td>'

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





// UNBUDGETTED EXPENSES 
if (allowedToOpen(205,'1rtc')){
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT TypeID, BudgetDesc FROM acctg_1branchpreapprovedbudgetlist ','TypeID','BudgetDesc','typelist');	
echo comboBox($link,'SELECT EntityID, Entity FROM acctg_1budgetentities ','EntityID','Entity','entities');	
    $sqlsp1='Select a.*,Entity AS FromBudgetOf, eeswitch,IF(eeswitch IN (1,2,3,4,5,6,7),IF(eeswitch IN (1,4),"Finance Asst",IF(eeswitch=2,"Acctg TL - GA",IF(eeswitch IN (3,6),"SC Dept Head",IF(eeswitch=7,"Planning Assoc","Admin Dept Head")))),"") AS Approver,b.Branch,BudgetDesc from `approvals_2encashedexpenses` a  join `1branches` b on b.BranchNo=a.BranchNo left join acctg_1branchpreapprovedbudgetlist bl on bl.TypeID=a.TypeID left join acctg_1budgetentities be on be.EntityID=a.FromBudgetOf where isnull(Approval) ';
    $user=$_SESSION['&pos'];  $condition='';
    
      
    $stmtsp=$link->query($sqlsp1.$condition); $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<br><div><br>Expenses for approval:<table border="1px solid black" style="border-collapse:collapse;"><tr>';
    foreach($datatoshowsp as $rows){
        $msgsp=$msgsp.'<form method="post" action="../acctg/praddmain.php?w=ApproveRequestforExpense" enctype="multipart/form-data">'
                . '<td style="width:85px;">'.$rows['Date'].'</td><td>'.$rows['TimeStamp'].'</td><td>'.$rows['Branch'].'</td>
        <td>&nbsp &nbsp '.($rows['eeswitch']==4?'WaybillNo':($rows['eeswitch']==7?'Txfr Receipt':'Particulars')).' &nbsp<input type=text size=45 name=Particulars value="'.htmlcharwithbr($fromBRtoN,$rows['Particulars']).'" autocomplete="off" onclick="IsEmpty(Particulars);"></td>
		'.(!empty($rows['BudgetDesc'])?'<td style="white-space: pre;">&nbsp &nbsp Excess from Pre-approved for <b>'.$rows['BudgetDesc'].'</b></td>':'').'
        <td>&nbsp &nbsp Amount &nbsp<input type=text size=5 name=Amount value='.$rows['Amount'].' autocomplete="off" required=1 onclick="IsEmpty(Amount);"></td>'
                .((allowedToOpen(2051,'1rtc'))?'
					'.(empty($rows['BudgetDesc'])?'
				<td>&nbsp &nbsp Choose AccountID<input type=text size=10 name=AccountID list=accounts  autocomplete="off"  required=1 onclick="IsEmpty(AccountID);"></td><td>&nbsp &nbsp Change Branch To<input type=text size=10 name=Branch list="branches"  autocomplete="off"  required=1 value="'.$rows['Branch'].'"></td>':'').'
        <td>From Budget Of: <input type="text" name="FromBudgetOf" list="entities" value="'.($rows['eeswitch']==7?$rows['FromBudgetOf']:$rows['Branch']).'"></td><td>Approver: '.$rows['Approver'].'</td><td><input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'" /><input type=hidden name=ApprovalID value='.$rows['ApprovalId'].'>
        <input type=hidden name=Approval value='.mt_rand(1000,99999).'>&nbsp &nbsp &nbsp<input type="submit" name="submit" value="Approve">':'')
                .'</form></tr><tr>';
        
   }
   echo $msgsp.'</tr></table><br></div>';   
   include_once('../acctg/acctglists.inc');   renderotherlist('accounts','');
   }
   $stmtsp=null;
   }
   
 
 // FREIGHT-CLIENT EXPENSES 
if (allowedToOpen(206,'1rtc')){
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';	
echo comboBox($link,'SELECT EntityID, Entity FROM acctg_1budgetentities ','EntityID','Entity','entities');	
    $sqlsp='Select a.*, concat(Particulars,(if(a.txntype=2," Charge ", " Cash ")) , " Invoice No. ", ForInvoiceNo, " ", IF(PriceFreightInclusive=1,"PriceINCLUDESFreight","PriceNOFreight")) as Particulars,b.Branch from `approvals_2freightclients` a  join `1branches` b on b.BranchNo=a.BranchNo where isnull(Approval);';
     
    $stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<div><br>Freight-Client for approval:<table><tr>';
    foreach($datatoshowsp as $rows){
        $msgsp=$msgsp.'<form method="post" action="../acctg/praddmain.php?w=ApproveFreightClientExpense" enctype="multipart/form-data"><td>'.$rows['Branch'].'</td>
        <td>&nbsp &nbsp &nbsp'.$rows['Date'].'</td><td>&nbsp &nbsp &nbsp'.htmlcharwithbr($fromBRtoN,$rows['Particulars']).'</td>
        <td>&nbsp &nbsp Amount &nbsp<input type=text size=5 name=Amount value='.$rows['Amount'].' autocomplete="off" required=1 onclick="IsEmpty(Amount);"></td>
        <td>From Budget Of: <input type="text" name="FromBudgetOf" list="entities" value="'.$rows['Branch'].'"></td><td><input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'" /><input type=hidden name=ApprovalID value='.$rows['ApprovalId'].'>
        <input type=hidden name=Approval value="'.mt_rand(1000,99999).'FC">&nbsp &nbsp &nbsp<input type="submit" name="submit" value="Approve"></td></form></tr><tr>';
        
   }
   echo $msgsp.'</tr></table><br></div>';
   
   }
   $stmtsp=null;
 }
 
  
 // SPECIAL DISCOUNTS  
 
if (allowedToOpen(6929,'1rtc')){ 
    $sqlsp='SELECT a.TxnID, SaleNo, txntype, b.Branch FROM `invty_7specdisctapproval` a join invty_2sale m on m.TxnID=a.TxnID join `1branches` b on b.BranchNo=m.BranchNo where Approved=0 group by m.TxnID;';
  // echo $sqlsp; break;
     $stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<br><div id="table-wrapper" >SPECIAL PRICE for approval:<div id="table-scroll"><table>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        
        $msgsp=$msgsp.'<tr><td>'.$rows['Branch'].'</td><td>'.$rows['SaleNo'].'</td><td><a style="color:blue;text-decoration:none;" href="../invty/addeditsale.php?TxnID='.$rows['TxnID'].'&txntype='.$rows['txntype'].'&specprice=1" target=_blank>Look up</a></td></tr>';
        
   }
   echo $msgsp.'</tr></table><br></div></div>';
   }
   
 }
 // RETURNS 
if (allowedToOpen(208,'1rtc')){  
 $sqlsp='Select b.Branch,sm.Date from `approvals_2salesreturns` sr join invty_2sale sm on sm.TxnID=sr.TxnID join `1branches` b on b.BranchNo=sr.BranchNo where sr.Approval is null';
 
$sqlsp=$sqlsp.' UNION SELECT b.Branch,sm.Date from `approvals_2salesreturns` sr join `'.$lastyr.'_1rtc`.`invty_2sale` sm ON (Select CONCAT("'.substr($lastyr, -2).'00",sm.TxnID))=sr.TxnID join `1branches` b on b.BranchNo=sr.BranchNo where sr.Approval is null';


     
    
    $stmtsp=$link->query($sqlsp);
    $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<br><div><br>RETURNS for approval:<table><tr>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        $msgsp=$msgsp.'<td><a href="../invty/addsalemain.php?w=Return&saletype=5"</a>'.$rows['Branch'].' from Sale on '.$rows['Date'].'</td>'.($countsp%15==0?'</tr><tr>':'');
        
   }
   echo $msgsp.'</tr></table><br><br></div>';
   }
   
 }
 // Cancelled SRS 
if (allowedToOpen(208,'1rtc')){  
 $sqlsp='Select b.Branch,Reason from `approvals_2salesreturns` sr join `1branches` b on b.BranchNo=sr.BranchNo where TxnID=1 AND ApprovedByNo IS NULL';

    $stmtsp=$link->query($sqlsp);
    $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<br><div><br>Cancelled SRS:<table><tr>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        $msgsp=$msgsp.'<td><a href="../invty/addsalemain.php?w=Cancel&saletype=cancel5"</a>'.$rows['Branch'].'</td>'.($countsp%15==0?'</tr><tr>':'');
        
   }
   echo $msgsp.'</tr></table><br><br></div>';
   }
   
 }
 
  // STORE USED  
if (allowedToOpen(209,'1rtc')){  
 $sqlsp='SELECT su.*, ItemDesc,Category,Branch, Nickname as EncodedBy FROM approvals_2storeused su join invty_1items i on su.ItemCode=i.ItemCode join invty_1category c on c.CatNo=i.CatNo join `1branches` b on b.BranchNo=su.BranchNo join `1employees` e on e.IDNo=su.EncodedByNo where Approved=0';
     
    
    $stmtsp=$link->query($sqlsp);
    $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<br><div><br>STORE USED for approval:<table bgcolor="FFFFF"><tr>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        $msgsp=$msgsp.'<td><a href="../invty/addmrrmain.php?w=StoreUsed&saletype=9"</a>'.$rows['Branch'].' -  '.htmlcharwithbr($fromBRtoN,$rows['Category']).' '.htmlcharwithbr($fromBRtoN,$rows['ItemDesc']).'</td>'.($countsp%15==0?'</tr><tr>':'');
        
   }
   echo $msgsp.'</tr></table><br><br></div>';
   }
   
 }   

// DEFECTIVE  
if (allowedToOpen(210,'1rtc')){  
 $sqlsp='Select b.Branch,sd.ItemCode, Category, ItemDesc, sd.Qty, Unit from `approvals_2setasdefective` sd join `1branches` b on b.BranchNo=sd.BranchNo join invty_1items i on sd.ItemCode=i.ItemCode join invty_1category c on c.CatNo=i.CatNo 
        where sd.Approval is null';
     
    
    $stmtsp=$link->query($sqlsp);
    $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<br><div><br>DEFECTIVE for approval:<table bgcolor="FFFFF"><tr>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        $msgsp=$msgsp.'<td><a href="../invty/setasdefective.php?w=RequestSetDefective"</a>'.$rows['Branch'].': '.$rows['Qty'].' '.$rows['Unit'].' '.htmlcharwithbr($fromBRtoN,$rows['Category']).' '.htmlcharwithbr($fromBRtoN,$rows['ItemDesc']).'</td>'.($countsp%15==0?'</tr><tr>':'');
        
   }
   echo $msgsp.'</tr></table><br><br></div>';
   }
   
 } 
 $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
  


// Direct Deposits for approval
if (allowedToOpen(5211,'1rtc')){
    $sqlsp='Select TxnSubId, c.ClientName, mt.ShortAcctID as Bank, a.RequestRemarks, a.Amount, a.DirectDepDate, b.Branch, CONCAT(e2.Nickname, " ",Surname) AS RequestedBy from approvals_2directdeposits a 
join `1employees` e2 on a.EncodedByNo=e2.IDNo
join `1branches` b on b.BranchNo=a.BranchNo
join `banktxns_1maintaining` mt on mt.AccountID=a.Bank
left join `1clients` c on c.ClientNo=a.ClientNo
where a.`Approved?`=0 Order by DirectDepDate, a.TimeStamp, b.Branch';
     
    $stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);  
    
    if ($stmtsp->rowCount()>0){
        $submitprocess='../acctg/praddmain.php?w=DDApproval';
        $columnnamessp=array('Branch','ClientName','Bank','DirectDepDate','Amount','RequestedBy','RequestRemarks'); 
        $fieldlist=''; $rows=''; $textfordisplay='';
        echo '<style>table, td, th {padding: 3px; border-collapse: collapse; border: 1px brown solid;"} </style>';
        foreach($columnnamessp as $field){ $fieldlist.='<th style="font-weight: bold; font-size: normal;">'.$field.'</th>';}
        $fieldlist.='<th>Response</th>';
        $msgcb='';
    foreach($datatoshowsp as $rows){        
        $textfordisplay.='<tr>';
        foreach($columnnamessp as $col){ $textfordisplay.='<td>'. htmlcharwithbr($fromBRtoN,addslashes($rows[$col])) . '</td>';}
        $textfordisplay.='<td><form method=post action="'.$submitprocess.'"><input type=text name=Remarks placeholder="Remarks (not required)">&nbsp; &nbsp;'
                . '<input type=hidden name=action_token value='.$_SESSION['action_token'].'>'
                . '<input type=hidden name=TxnSubId value='.$rows['TxnSubId'].'>'
                . '<input type=submit name=submit value=Approve>&nbsp; &nbsp;'
                . '<input type=submit name=submit value=Deny>'
                . '</form></td></tr>';
   }
   echo '<br><br><div style="background-color: fff2e6; font-size: normal; font-weight: bolder;" >Direct Deposits &nbsp; &nbsp;<table>'
            .$fieldlist.$textfordisplay.'</table></div><br><br>';
   }
    
   $stmtsp=null;
}

// PURCHASE ORDERS 
if (allowedToOpen(4031,'1rtc')){
    $sqlsp='Select m.TxnID, b.Branch,m.PONo, s.SupplierName AS Supplier, FORMAT(SUM(Qty*UnitCost),0) AS POAmount FROM invty_3order m join invty_3ordersub os on m.TxnID=os.TxnID
    JOIN `1branches` b ON b.BranchNo=m.BranchNo JOIN `1suppliers` AS s ON m.SupplierNo=s.SupplierNo
    WHERE m.Approved=0 AND m.Posted=1 GROUP BY m.TxnID ORDER BY m.BranchNo;';
    
    $stmtsp=$link->query($sqlsp);
    $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<br><div style="float:left"><br>PO\'s for approval:<table bgcolor="FFFFF"><tr>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        $msgsp=$msgsp.'<td><a href="../invty/praddext.php?w=ApprovePO&TxnID='.$rows['TxnID'].'"</a>'.$rows['Branch'].' PO No.: '.$rows['PONo'].' '.$rows['Supplier'].' <B>'.$rows['POAmount'].'</B></td>'.($countsp%5==0?'</tr><tr>':'');        
   }
   echo $msgsp.'</tr></table></div><br><br>';
   }
}

// Request for Check Payment
include_once('../approvals/checkpaymentrequestapproval.php');