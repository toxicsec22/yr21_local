<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(5230,5231,5232,100); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$which=!isset($_GET['w'])?'Unliquidated':$_GET['w'];
    if($which<>'Print'){ $showbranches=FALSE; include_once('../switchboard/contents.php'); include_once('../backendphp/layout/showencodedbybutton.php'); } else { $hidecontents=1; 
		include_once($path.'/acrossyrs/dbinit/userinit.php'); $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
	}
include_once('../backendphp/layout/regulartablestyle.php');
?>
</head>
<body>
<?php

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
 

if(in_array($which,array('Unliquidated','Lookup','EditSpecifics','Print','SetAsLiquidated'))){
$sql0='SELECT bl.*, DATE_FORMAT(bl.`TimeStamp`, "%Y-%m-%d") AS DateofRequest, CONCAT(e.Nickname," ",e.SurName) AS Requester, e1.Nickname AS ApprovedBy, 
                e2.Nickname AS SetLiquidatedBy, Duration AS DurationInDays,
                IF(RequestCompleted<>0,"Done","") AS RequestCompleted,IF(FundsReleased<>0,CONCAT("Released By ",e3.Nickname),"") AS FundsReleased,
                IF(FundsAccepted<>0,"Accepted","") AS FundsAccepted, IF(ForLiqSubmission<>0,"ForSubmission","") AS ForSubmission, 
                IF(DocsComplete<>0,CONCAT("Docs Received By ",e4.Nickname),"") AS DocsComplete, DATE_FORMAT(bl.`DocsCompleteTS`, "%m-%d-%Y") AS DocsCompleteDate, 
                IF(Liquidated<>0,"Completed","") AS Liquidated, bl.EncodedByNo AS EncodedByNum, b.Branch,
                (SELECT FORMAT(IFNULL(SUM(Amount),0),0) FROM `approvals_3budgetrequestsub` WHERE TxnID=bl.TxnID) AS RequestAmt,
                (SELECT FORMAT(IFNULL(SUM(Amount),0),0) FROM `approvals_3budgetrequestsub` WHERE TxnID=bl.TxnID AND CashorCard=0) AS Cash,
                (SELECT FORMAT(IFNULL(SUM(Amount),0),0) FROM `approvals_3budgetrequestsub` WHERE TxnID=bl.TxnID AND CashorCard<>0) AS Card,
                IF(Approved=0,"Pending",IF(Approved=1,"Approved","Denied")) AS ApprovalStatus
                FROM `approvals_3budgetandliq` bl LEFT JOIN `1employees` e ON e.IDNo=bl.EncodedByNo 
                LEFT JOIN `1employees` e1 ON e1.IDNo=bl.ApprovedByNo 
                LEFT JOIN `1employees` e2 ON e2.IDNo=bl.SetLiqByNo
                LEFT JOIN `1employees` e3 ON e3.IDNo=bl.ReleasedByNo
                LEFT JOIN `1employees` e4 ON e4.IDNo=bl.DocsCompleteByNo
                LEFT JOIN `1branches` b ON b.BranchNo=bl.BranchNo ';

$columnnames=array('DateofRequest','DateNeeded','Purpose','DurationInDays','RequestAmt','Cash','Card','Branch','RequestCompleted','ApprovalStatus','FundsReleased',
    'FundsAccepted','ForSubmission','DocsComplete','Liquidated');
}


$txnidname='TxnID';
$txnid=!isset($_REQUEST['TxnID'])?0:$_REQUEST['TxnID'];

// if(!isset($_REQUEST['TxnID'])){  $editok=0; $editliqok=0; $requester=0;} else {
$sql3='SELECT bl.*,deptid,  (RequestCompleted+Approved+FundsReleased+FundsAccepted+DocsComplete+Liquidated) AS Editable FROM approvals_3budgetandliq bl JOIN attend_30currentpositions cp ON cp.IDNo=bl.EncodedByNo WHERE bl.TxnID='.$txnid;

$stmt=$link->query($sql3);$res3=$stmt->fetch();
$editok=($res3['Editable']==0?1:0);    
$editliqok=(($res3['ForLiqSubmission']+$res3['DocsComplete']+$res3['Liquidated'])==0)?1:0;
$editcondition=' AND RequestCompleted=0 AND Approved=0 AND FundsReleased=0 AND FundsAccepted=0 AND DocsComplete=0 AND Liquidated=0 ';
$requester=$res3['EncodedByNo'];

if(in_array($which,array('Unliquidated','Lookup','SetApprove','EditSubSpecifics','Print','Claim'))){ 
    $deptid=$res3['deptid'];
    $sql2='SELECT IDNo as DeptHead FROM attend_30currentpositions WHERE PositionID=(SELECT deptheadpositionid FROM 1departments WHERE deptid=\''.$deptid.'\')';
    $stmt=$link->query($sql2);$result=$stmt->fetch();
    $forapprovalby=(($requester==$result['DeptHead']) OR ($result['DeptHead']=='1001'))?'1002':$result['DeptHead'];            

// }



if (allowedToOpen(5231,'1rtc')){ $condition='';} 
elseif (allowedToOpen(100,'1rtc') or allowedToOpen(5232,'1rtc')) { 
    if(in_array($which,array('Unliquidated'))){ 
        $sqldept=(allowedToOpen(100,'1rtc'))?'SELECT GROUP_CONCAT(d.deptid) AS depts FROM 1departments d JOIN attend_30currentpositions cp ON cp.PositionID=d.deptheadpositionid WHERE d.deptid<>10 AND cp.IDNo='.$_SESSION['(ak0)']:'SELECT deptid AS depts FROM attend_30currentpositions WHERE deptid<>10 AND IDNo='.$_SESSION['(ak0)'];
        $stmt=$link->query($sqldept);$result=$stmt->fetch(); $deptid=$result['depts']; 
    } 
    $sql3='SELECT GROUP_CONCAT(IDNo) AS DeptEmp FROM attend_30currentpositions WHERE deptid IN ('.$deptid.')';
    $stmt=$link->query($sql3);$result=$stmt->fetch(); $deptemployees=$result['DeptEmp'];
    $condition=' AND bl.EncodedByNo IN ('.$deptemployees.')';
}
else { $condition=' AND bl.EncodedByNo='.$_SESSION['(ak0)'];}

}


if(in_array($which,array('Add','Lookup','EditSpecifics','EditMain'))){    $columnstoadd=array('Purpose','Duration','DateNeeded'); }

if(in_array($which,array('Add','EditMain'))){ $branchno=comboBoxValue($link, ' `1branches` ', 'Branch', $_POST['Branch'], 'BranchNo'); }

if(in_array($which,array('Lookup','AddSub','EditSubSpecifics','EditSub','Print'))){     
    $columnsub=array('Particulars','BudgetType','Amount','CashorCard');
    $columnsubtoedit=array('Particulars','Amount');  
    $fieldsinrow=6; $fieldsinrowsub=6;
}

if(in_array($which,array('Lookup','EditSubSpecifics','EditLiqSubSpecifics'))){ 
    echo comboBox($link, 'SELECT 0 AS CashorCard, "Cash" AS CashorCardDesc UNION SELECT 1, "Card";', 'CashorCard', 'CashorCardDesc', 'cashorcard');
    echo comboBox($link, 'SELECT BudgetType, Details FROM `approvals_1travelbudgettypes`;', 'Details', 'BudgetType', 'budgettype');
}

if(in_array($which,array('Lookup','EditSubSpecifics','Print'))){ 
    $sqlsub='SELECT brs.*,BudgetType,IF(CashorCard=1,"Card","Cash") AS CashorCard, e.Nickname AS EncodedBy 
        FROM approvals_3budgetrequestsub brs JOIN `approvals_1travelbudgettypes` tb ON brs.BudgetTypeID=tb.BudgetTypeID
        JOIN `1employees` e ON e.IDNo=brs.EncodedByNo
        JOIN `approvals_3budgetandliq` bl ON bl.TxnID=brs.TxnID WHERE bl.TxnID='.$txnid.$condition;
}

if(in_array($which,array('Lookup','AddLiqSub','EditLiqSubSpecifics','EditLiqSub','Print'))){     
    $columnnamesliq=array('No.','BudgetType','Date','InvNo','Payee','TIN','Particulars','Card','Cash');
    $columnstoeditliq=array('ExpenseNo','Date','InvNo','Payee','TIN','Particulars','Amount'); 
    $sqlliqsub='SELECT bls.*,BudgetType, (CASE WHEN CashorCard=1 THEN Amount END) AS Card, (CASE WHEN CashorCard<>1 THEN Amount END) AS Cash,ExpenseNo AS `No.` 
        FROM `approvals_3budgetliquidatesub` bls JOIN `approvals_1travelbudgettypes` tb ON bls.BudgetTypeID=tb.BudgetTypeID
        JOIN `approvals_3budgetandliq` bl ON bl.TxnID=bls.TxnID WHERE bl.TxnID='.$txnid;
}

if(in_array($which,array('Lookup','Print'))){
    $sqltotalbudget='SELECT FORMAT(SUM(Amount),2) AS Total,TRUNCATE(SUM(Amount),2) AS TotalBudget,TRUNCATE(SUM(CASE WHEN CashorCard=0 THEN Amount END),2) AS TotalCashBudget,TRUNCATE(SUM(CASE WHEN CashorCard<>0 THEN Amount END),2) AS TotalCardBudget  FROM `approvals_3budgetrequestsub` brs JOIN `approvals_3budgetandliq` bl ON bl.TxnID=brs.TxnID WHERE brs.TxnID='.$txnid.$condition;
    $sqlcash='SELECT cc.* FROM `approvals_3budgetandliqcashcount` cc WHERE cc.TxnID='.$txnid;
		$stmt=$link->query($sqlcash); $resultcash=$stmt->fetch();
    $sqltotalused='SELECT TRUNCATE(IFNULL(SUM(Amount),0),2) AS TotalUsed FROM `approvals_3budgetliquidatesub` bls JOIN `approvals_3budgetandliq` bl ON bl.TxnID=bls.TxnID WHERE bls.TxnID='.$txnid.$condition;  
    $stmt=$link->query($sqltotalused); $resultsum=$stmt->fetch();
    
    
    $sqlspent='CREATE TEMPORARY TABLE BudgetandSpent AS SELECT TxnID, BudgetTypeID, Amount AS Budget, 0 AS Spent FROM approvals_3budgetrequestsub WHERE TxnID='.$txnid
                    .' UNION ALL SELECT TxnID, BudgetTypeID, 0 AS Budget, Amount AS Spent FROM approvals_3budgetliquidatesub WHERE TxnID='.$txnid;
    $stmt=$link->prepare($sqlspent); $stmt->execute();
    $sqlspent='SELECT TxnID, BudgetType, SUM(Budget) AS Budget, SUM(Spent) AS Spent, SUM(Budget)-SUM(Spent) AS Balance FROM BudgetandSpent bs LEFT JOIN  `approvals_1travelbudgettypes` tb ON tb.BudgetTypeID=bs.BudgetTypeID GROUP BY TxnID, tb.BudgetTypeID;';
    $columnnamesspent=array('BudgetType','Budget','Spent','Balance'); $hidecount=true;

    $sqltotalspent='SELECT FORMAT(SUM(Amount),2) AS Total,TRUNCATE(SUM(CASE WHEN CashorCard=0 THEN Amount END),2) AS TotalCash, '
            . ' FORMAT(SUM(CASE WHEN CashorCard<>0 THEN Amount END),2) AS TotalCard FROM `approvals_3budgetliquidatesub` bls JOIN `approvals_3budgetandliq` bl ON bl.TxnID=bls.TxnID '
            . ' WHERE bls.TxnID='.$txnid.$condition;
}

switch ($which){

	case 'Unliquidated': 
            
            $f=!isset($_REQUEST['f'])?'0':$_REQUEST['f'];
            switch ($f){
                case 'all': $filter=' 1=1 '; $title='All Budget Requests'; break; //show all
                case 'request': $filter=' (RequestCompleted=0) '; $title='Unfinished Requests'; break; 
                case 'denied': $filter=' (Approved=2) '; $title='Denied Requests'; break; 
                case 'approval': $filter=' RequestCompleted<>0 AND (Approved=0) '; $title='Pending Approvals'; break;
                case 'funds': $filter=' RequestCompleted<>0 AND Approved<>0 AND (FundsReleased=0 OR FundsAccepted=0) '; $title='No Funds Released/Accepted'; break;
                case 'unliq': $filter=' RequestCompleted<>0 AND Approved<>0 AND FundsAccepted<>0 AND Liquidated=0 '; $title='Unliquidated Budgets'; break;
            default: //unliquidated & unfinished
                $filter=' (RequestCompleted=0 OR Approved=0 OR FundsReleased=0 OR FundsAccepted=0 OR DocsComplete=0 OR Liquidated=0) '; $title='Pending Budgets';
            }
                
            $formdesc='</i><br><a href=requestforbudget.php?w=NewRequest>Add New Request or Claim Another Request</a><br><br>'
                    .'<h4><form action="requestforbudget.php" method=post style="display:in-line; border: solid 1px; padding: 10px;" >Filter by: '.str_repeat('&nbsp;', 10)
        . 'Unfinished Requests &nbsp; <input type=radio name=f value="request" ></input>'.str_repeat('&nbsp;', 8)
        . 'Denied Requests &nbsp; <input type=radio name=f value="denied" ></input>'.str_repeat('&nbsp;', 8)
        . 'Approval Pending &nbsp; <input type=radio name=f value="approval"></input>'.str_repeat('&nbsp;', 8)
        . 'No Funds Released &nbsp; <input type=radio name=f value="funds" ></input>'.str_repeat('&nbsp;', 8)
        . 'Unliquidated &nbsp; <input type=radio name=f value="unliq" ></input>'.str_repeat('&nbsp;', 8)
        . 'Show All &nbsp; <input type=radio name=f value="all" ></input>'.str_repeat('&nbsp;', 8)
        . '<input type=submit name="filter" value="Set filter">'.str_repeat('&nbsp;', 8)
        .'</form></h4><i>';
            $sql1='SELECT bl.EncodedByNo, CONCAT(e.Nickname," ",e.SurName) AS Requester, bl.EncodedByNo AS EncodedByNum FROM `approvals_3budgetandliq` bl LEFT JOIN `1employees` e ON e.IDNo=bl.EncodedByNo WHERE '.$filter.$condition.' GROUP BY bl.EncodedByNo';
            $sql2=$sql0.' WHERE '.$filter.$condition;
            $groupby='EncodedByNum'; $secondcondition=' AND Liquidated=0 ';
            if ($showenc==1) { array_push($columnnames,'TimeStamp','ApprovedBy','ApprovedTS','AcceptedTS','ForLiqSubTS','SetLiquidatedBy','SetLiqTS'); } 
            else { $columnnames=$columnnames; }
            $columnnames1=array('Requester');
            $columnnames2=$columnnames;
            $orderby=' ORDER BY Requester';
            $editprocess='requestforbudget.php?w=Lookup&TxnID='; $editprocesslabel='Lookup';
//if($_SESSION['(ak0)']==1002){echo $sql1.'<br>'.$sql2; break;}
            include('../backendphp/layout/displayastablewithsubHAVING.php');
	    
		break;
	
        case 'NewRequest':
            $title='New Request for Budget';
            ?><title><?php  echo $title; ?></title><br><br>
            <div style='background-color: dcdedc; width: 85%; padding: 15px;'>
                <h4><?php  echo $title; ?></h4><br><br>
                
		<form method='POST' action='requestforbudget.php?w=Add' style='display: inline;' >
                    Purpose <input type='text' name='Purpose' size=100 required=true><br><br>
                    Duration in days <input type='text' name='Duration' size=5 required=true> &nbsp; &nbsp; 
                    Charge to Branch <input type='text' name='Branch' size=10 required=true list='branchnames'> &nbsp; &nbsp;
                    Date Needed <input type='date' name='DateNeeded' size=5 required=true> &nbsp &nbsp
			<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>"> &nbsp &nbsp
            <br><br><div style='background-color: edf0ed; width: 65%; border: solid 1px black; margin-left: 5%; padding: 5px;'>
            <input type="checkbox"  onchange="document.getElementById('addrequest').disabled = !this.checked;" /> By submitting this request, I acknowledge that if I fail to submit my liquidation five (5) days after the end of the purpose of my budget request, the entire amount will be charged to me, and will be paid via salary deduction.  I am also aware that any delay in liquidation is an offense in the Company's Code of Conduct.
            <br><br>
			<input type='submit' size=10 name='submit' value='Submit Request' id="addrequest" disabled="true"></div>&nbsp &nbsp &nbsp</form>
            </div>    
                <br><br>
                <div style='background-color: dcdedc; width: 85%; padding: 15px;'>
                <h4>Claim another request</h4><br>
                <form method='POST' action='requestforbudget.php?w=Claim' style='display: inline;' >
                    TxnID of Budget Request <input type='text' name='TxnID' size=5 required=true>&nbsp; &nbsp; 
                    ID Number of Original Requester <input type='text' name='EncodedByNo' size=5 required=true><br>
			<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>"> &nbsp &nbsp
            <div style='background-color: edf0ed; width: 65%; border: solid 1px black; margin-left: 5%; padding: 5px;'>
            <input type="checkbox"  onchange="document.getElementById('claimrequest').disabled = !this.checked;" /> By submitting this request, I acknowledge that if I fail to submit my liquidation five (5) days after the end of the purpose of my budget request, the entire amount will be charged to me, and will be paid via salary deduction.  I am also aware that any delay in liquidation is an offense in the Company's Code of Conduct.
            <br><br>
			<input type='submit' size=10 name='submit' id="claimrequest" value='Claim Request' disabled="true"></div></form>
            </div>
<?php
		echo comboBox($link,'SELECT BranchNo, Branch FROM `1branches` WHERE Active=1 AND BranchNo>=0 AND BranchNo NOT IN (95)','BranchNo','Branch','branchnames');
                ?>
                <BR><BR><hr>
                <BR><b>Complete Process:</b>
    <BR><BR>
    <ol style="padding-left: 100px; font-size: normal;">
        <li>Requester must fill out the online form, then submit.</li>
        <li>The requester's Dept Head shall approve online. The request will no longer be editable once approved.</li>
        <li>Accounting will prepare the check and release the funds to the requester. Payee of the check is the requester.</li>
        <li>Requester shall indicate on the system that funds have been received. </li>
        <li>Requester shall encode all expenses with the corresponding details.  Best would be everyday encoding so budget balance is updated.  Only the requester/payee can encode expenses for liquidation.</li>
        <li>After the trip/event, requester shall organize the receipts on bond paper to coincide with encoded report. Requester must number and initialize each page. </li>
        <li>Requester shall print the report from the system and submit the report with the arranged receipts to the Dept Head for approval.</li>
        <li>Dept Head checks receipts and expenses, and signs approval.  Returns the report to the requester.</li>
        <li>Requester submits the report, arranged receipts, and excess cash to Acctg.  Deadline for submission is 3 days after the end of the trip/event.</li>
        <li>Accounting checks if all are in order, and tags the system report as "documents complete". If this has to be edited, only the requester may unset.</li>
        <li>Accounting will continue the process as usual.  All cash returns must be deposited immediately. Encoding in vouchers must be completed in 2 days.</li>
        <li>After the voucher has been checked and cleared, Acctg Team Leaders must finally set as "liquidated".  This cannot be undone.</li>        
    </ol><BR><BR>
    <b>Special cases:</b>
    <ol style="padding-left: 100px; font-size: normal;">
        <li>Dept Head may remove approval for further editing only if the funds were not yet received.</li>
        <li>To transfer the payee to another person, the new payee will have the option to claim the request before the approval of the Dept Head.  After this, only the new payee will be able to encode for liquidation.</li>
        <li>If a Dept Head claims the request, the approval will be reset.</li>
        <li>To cancel a request, all details must be zeroed out, and the rest of the liquidation process must be followed until tagged as liquidated.</li>
                <?php
            break;
                
	case 'Add':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$sql=''; 
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; } 
		$sql='INSERT INTO `approvals_3budgetandliq` SET BranchNo='.$branchno.', '.$sql.' TimeStamp=Now(), EncodedByNo=\''.$_SESSION['(ak0)'].'\''; //echo $sql;
		$stmt=$link->prepare($sql); $stmt->execute();
                $sql='SELECT TxnID FROM `approvals_3budgetandliq` WHERE EncodedByNo='.$_SESSION['(ak0)'].' AND BranchNo='.$branchno.' AND DATE_FORMAT(TimeStamp,"%Y-%m-%d") LIKE \''.date('Y-m-d').'\'';
                $stmt=$link->query($sql);$result=$stmt->fetch();
		header('Location:requestforbudget.php?w=Lookup&TxnID='.$result['TxnID']);
		break;
                
        case 'Lookup':
            $title=($res3['Approved']==1)?'Approved Budget':'Budget Request';
            $sql2='SELECT EncodedByNo,RequestCompleted, Approved,FundsReleased,FundsAccepted,ForLiqSubmission, DocsComplete,Liquidated FROM approvals_3budgetandliq bl WHERE TxnID='.$txnid.$condition; 
            $stmt=$link->query($sql2);$result=$stmt->fetch();
            $edit=(($result['EncodedByNo']==$_SESSION['(ak0)']) and ($editok==1))?2:0;
            $sqlmain=$sql0.' WHERE TxnID='.$txnid.$condition;
            $nopost=1; $skippost=1; 
            if ($showenc==1) { array_push($columnnames,'TimeStamp','ApprovedBy','ApprovedTS','AcceptedTS','ForLiqSubTS','DocsCompleteBy','DocsCompleteTS','SetLiquidatedBy','SetLiqTS'); } 
            else { $columnnames=$columnnames; }
            $columnnamesmain=$columnnames;
            array_unshift($columnnamesmain,'Requester'); $fieldsinrowmain=3;
            
            if($edit==2){
            $editprocess='requestforbudget.php?w=EditSpecifics&edit='.$edit.'&TxnID='.$txnid;
            $delprocess='requestforbudget.php?w=DelMain&TxnID='.$txnid;
            if($result['RequestCompleted']==0) { $addlprocess='requestforbudget.php?w=RequestComplete&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid; $addlprocesslabel='Set_Request_as_Completed';}
                        
            //$addlprocess=str_repeat('&nbsp',8).'<a href="'.$addlprocess.'">'.$addlprocesslabel.'</a>';
            
            $columnstoeditmain=$columnstoadd;
            
            $columnnames=array(
                    array('field'=>'Particulars', 'type'=>'text','size'=>30,'required'=>true,'autofocus'=>true),
                    array('field'=>'BudgetType', 'type'=>'text','size'=>10, 'required'=>true, 'list'=>'budgettype'),
                    array('field'=>'Amount', 'type'=>'text','size'=>7, 'required'=>true, 'value'=>0),
                    array('field'=>'CashorCard', 'type'=>'text','size'=>5, 'required'=>true, 'list'=>'cashorcard', 'value'=>'Cash'),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
            $addsub='requestforbudget.php?w=AddSub&TxnID='.$txnid;
            $editprocesssub='requestforbudget.php?w=EditSubSpecifics&edit='.$edit.'&TxnID='.$txnid.'&TxnSubId=';
            $delprocesssub='requestforbudget.php?w=DelSub&TxnID='.$txnid.'&TxnSubId=';
            } else {
                if(($result['EncodedByNo']==$_SESSION['(ak0)']) and ($result['RequestCompleted']<>0) and ($result['Approved']==0) and ($result['FundsReleased']==0) and ($result['FundsAccepted']==0) and ($result['Liquidated']==0)){ $formdesc='<a href="requestforbudget.php?w=RequestComplete&Set=0&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'">Set_Request_as_Unfinished</a>'; }                if(($forapprovalby==$_SESSION['(ak0)']) and ($result['RequestCompleted']<>0) and ($result['Approved']==0) and ($result['FundsReleased']==0) and ($result['FundsAccepted']==0) and ($result['Liquidated']==0)){ $formdesc='<a href="requestforbudget.php?w=SetApprove&Set=1&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'">Approve</a>'.  str_repeat('&nbsp;',5)
                        .'<a href="requestforbudget.php?w=SetApprove&Set=2&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'">Deny</a>'; }
                elseif(($forapprovalby==$_SESSION['(ak0)']) and ($result['RequestCompleted']<>0) and ($result['Approved']<>0) and ($result['FundsReleased']==0) and ($result['FundsAccepted']==0) and ($result['Liquidated']==0)) { $formdesc='<a href="requestforbudget.php?w=SetApprove&Set=0&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'">Unset Approval</a>';}
                elseif(allowedToOpen(5231,'1rtc') and ($result['FundsReleased']==0)) { 
                    $formdesc='<a href="../acctg/praddmain.php?w=AutoVchBudget&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'">I have confirmed accuracy of values. Make check payment.</a>';}
                elseif(allowedToOpen(52311,'1rtc') and ($result['FundsReleased']<>0) and ($result['FundsAccepted']==0)) { 
                    $formdesc='<a href="../approvals/requestforbudget.php?w=UnsetRel&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'"><font color="red">Unset confirmation of accuracy (Voucher must be DELETED separately.)</font></a>';}
                elseif(($result['EncodedByNo']==$_SESSION['(ak0)']) and ($result['FundsReleased']<>0) and ($result['FundsAccepted']==0)){ $formdesc='<a href="requestforbudget.php?w=AcceptFunds&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'">Accept Funds</a>';}
                $columnnames=array(); $editprocess=''; $delprocess=''; $columnstoeditmain=array();}
            $sqltotal=$sqltotalbudget; 
            echo '<div id="wrapper" ><div style="float:left; width:50%;">';
            include('../backendphp/layout/addeditform.php');
            echo str_repeat('&nbsp;', 10).'Total Cash: '.number_format($resulttotal['TotalCashBudget'],2).str_repeat('&nbsp;', 10).'Total Card: '.number_format($resulttotal['TotalCardBudget'],2);
            // Cash calc
            $pcf=$resulttotal['TotalCashBudget'];
            $action='requestforbudget.php?w=EditBill&TxnID='.$txnid; 
            include('../backendphp/layout/calcbillsforpettycash.php'); 
            // end of cash calc
            echo '</div>'; // left
            
            echo '<div style="float:right;width:45%;">';
            if(($result['FundsAccepted']=='Accepted')){ 
            echo '<h4>Liquidation</h4><br>';
            if(($result['EncodedByNo']==$_SESSION['(ak0)']) and ($editliqok==1)){
                $edit=2;
            $columnstoeditmain=$columnstoadd;
           
            $columnnames=array(
                    array('field'=>'ExpenseNo','caption'=>'Expense Number (for list order only)', 'type'=>'text','size'=>3, 'required'=>true, 'value'=>'1','autofocus'=>true),
                    array('field'=>'CashorCard', 'type'=>'text','size'=>5, 'required'=>true, 'list'=>'cashorcard', 'value'=>'Cash'),
                    array('field'=>'BudgetType', 'type'=>'text','size'=>10, 'required'=>true, 'list'=>'budgettype'),
                    array('field'=>'Date', 'type'=>'date','size'=>10,'required'=>true, 'value'=>date('Y-m-d')),
                    array('field'=>'InvNo', 'type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'Payee', 'type'=>'text','size'=>15,'required'=>true),
                    array('field'=>'TIN', 'type'=>'text','size'=>10,'required'=>false),
                    array('field'=>'Particulars', 'caption'=>'Expense details', 'type'=>'text','size'=>20,'required'=>true),
                    array('field'=>'Amount', 'type'=>'text','size'=>7, 'required'=>true, 'value'=>0),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
            $fieldsinrow=3; $withsub=true; $outside=true;
            
            $action='requestforbudget.php?w=AddLiqSub&TxnID='.$txnid; $method='POST';
            $formdesc='</i><a href="requestforbudget.php?w=LiqCompleteByRequester&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'">Set Liquidation as Complete</a><i>';;
            include('../backendphp/layout/inputmainform.php');
            $columnstoedit=$columnstoeditliq;
            
            $editprocess='requestforbudget.php?w=EditLiqSubSpecifics&edit='.$edit.'&TxnID='.$txnid.'&TxnSubId='; $editprocesslabel='Edit';
            $delprocess='requestforbudget.php?w=DelLiqSub&TxnID='.$txnid.'&TxnSubId='; 
            } else { unset($editprocess,$delprocess); }
            
            if(($result['ForLiqSubmission']<>0) and ($res3['Liquidated']==0)){ 
            if(($requester==$_SESSION['(ak0)'])){ 
                echo ' <a href="requestforbudget.php?w=Print&print=1&TxnID='.$txnid.'">Print and Sign</a>';
                if($res3['DocsComplete']==0){ echo str_repeat('&nbsp;', 10).'<i>OR</i>'.str_repeat('&nbsp;', 10).'<a href="requestforbudget.php?w=LiqCompleteByRequester&Set=0&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'">Set Liquidation as Unfinished</a>';}                
                if($res3['DocsComplete']==1){ echo str_repeat('&nbsp;', 10).'<i>OR</i>'.str_repeat('&nbsp;', 10).'<a href="requestforbudget.php?w=DocsReceived&Set=0&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'">Documents were returned to me.</a>';}
            } elseif((allowedToOpen(5231,'1rtc')) and ($requester<>$_SESSION['(ak0)'])  and ($res3['DocsComplete']==0)) {
                echo '<a href="requestforbudget.php?w=DocsReceived&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'">All receipts, excess cash, and documents have been submitted completely to me.</a>'.  str_repeat('&nbsp;', 10);
            } elseif((allowedToOpen(5231,'1rtc')) and ($requester<>$_SESSION['(ak0)'])  and ($res3['DocsComplete']==1)) {
                echo '<a href="../acctg/praddmain.php?w=AutoVchLiq&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'">Send To Voucher</a>'.  str_repeat('&nbsp;', 10);
                if((allowedToOpen(5234,'1rtc')) and ($requester<>$_SESSION['(ak0)'])) { echo '<a href="requestforbudget.php?w=SetLiquidated&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'">Set Liquidation as Complete</a>';}
                
            }
            }
             elseif (($result['ForLiqSubmission']<>0) and $res3['Liquidated']<>0) { echo 'Liquidated successfully';}
            elseif (($result['ForLiqSubmission']<>0) and $res3['Liquidated']<>0) { echo 'Liquidated successfully';}
            
            $sql=$sqlliqsub.' ORDER BY ExpenseNo';
            $txnidname='TxnSubId'; $columnnames=$columnnamesliq; $title='';$formdesc='';
            
            include('../backendphp/layout/displayastable.php');
            
            // moved sqls to top
            $sql=$sqlspent;
            $stmt=$link->query($sqltotalspent);$res=$stmt->fetch();
            $sqltotal=$sqltotalspent;
            $columnnames=$columnnamesspent;
            
            $totalstext='<br><br><div style="border: solid 1px; padding: 3px; width: 50%;">Total Liquidation: '.str_repeat('&nbsp;', 3).$res['Total']
                    .'<br><br>Total Card Expenses: '.str_repeat('&nbsp;', 3).$res['TotalCard']
                    .'<br>Total Cash Expenses: '.str_repeat('&nbsp;', 3).number_format($res['TotalCash'],2)
                    .'<br><br><b>Cash Balance for Return: '.str_repeat('&nbsp;', 3).  ((($pcf-$res['TotalCash'])<0)?0:number_format(($pcf-$res['TotalCash']),2)).'</b></div>';
            
            echo '<br><br>';unset($editprocess,$delprocess);
            include('../backendphp/layout/displayastablenosort.php');}
            echo '</div>'; // right
            echo '</div>'; // wrapper
            
            break;
	
        case 'EditSpecifics':
            $title='Edit Budget Request';
            $sql=$sql0.' WHERE bl.EncodedByNo='.$_SESSION['(ak0)'].' AND TxnID='.$txnid;
            if($editok==1){
            $columnstoedit=$columnstoadd; $columnstoedit[]='Branch';
            $columnslist=array('Branch');
            $listsname=array('Branch'=>'branchnames'); $liststoshow=array('branchnames'); $listcondition=''; $processlabelblank='';
            $action='requestforbudget.php?w=EditMain&TxnID='.$txnid; $method='POST';}
            include('../backendphp/layout/rendersubform.php');
            break;
	case 'EditMain':
                require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                if($editok==1){
		$sql='';
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; }
		$sql='UPDATE `approvals_3budgetandliq` SET BranchNo='.$branchno.', '.$sql.' TimeStamp=Now(), EncodedByNo=\''.$_SESSION['(ak0)'].'\' WHERE TxnID='.$txnid.' AND EncodedByNo=\''.$_SESSION['(ak0)'].'\' '.$editcondition; 
// echo $sql;
                $stmt=$link->prepare($sql); $stmt->execute();}
		header('Location:requestforbudget.php?w=Lookup&TxnID='.$txnid);
		break;
	
	case 'DelMain':
                require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
            if($editok==1){
            $sql='DELETE FROM `approvals_3budgetandliq` WHERE TxnID='.$txnid.' AND EncodedByNo=\''.$_SESSION['(ak0)'].'\' '.$editcondition; $stmt=$link->prepare($sql); $stmt->execute();}
		header('Location:requestforbudget.php');
		break;
                
        case 'AddSub':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                if($editok==1){
		$sql=''; 
                $budgettypeno=comboBoxValue($link, 'approvals_1travelbudgettypes', 'BudgetType', $_POST['BudgetType'], 'BudgetTypeID');
		foreach ($columnsubtoedit as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; } 
		$sql='INSERT INTO `approvals_3budgetrequestsub` SET TxnID='.$txnid.', BudgetTypeID='.$budgettypeno.', CashorCard='.($_POST['CashorCard']=='Cash'?0:1).','.$sql.' TimeStamp=Now(), EncodedByNo=\''.$_SESSION['(ak0)'].'\''; 
                $stmt=$link->prepare($sql); $stmt->execute();  }              
		header('Location:requestforbudget.php?w=Lookup&TxnID='.$txnid);
		break;
                
        case 'EditSubSpecifics':
            $title='Edit Budget Request';
            if($editok==1){
             $txnsubid=$_GET['TxnSubId'];
            $sql=$sqlsub.' AND TxnSubId='.$txnsubid;  
            $columnstoedit=$columnsub; $columnnames=$columnsub;
            //$columnstoedit[]='BudgetType';$columnstoedit[]='CashorCard';
            $columnslist=array('BudgetType','CashorCard');
            $listsname=array('BudgetType'=>'budgettype','CashorCard'=>'cashorcard'); $liststoshow=array(); $listcondition=''; $processlabelblank='';
            $action='requestforbudget.php?w=EditSub&TxnID='.$txnid.'&TxnSubId='.$txnsubid; $method='POST';}
            include('../backendphp/layout/rendersubform.php');
            break;
        
        case 'EditSub':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
            if($editok==1){
                 $txnsubid=$_GET['TxnSubId'];
		$sql=''; 
                $budgettypeno=comboBoxValue($link, 'approvals_1travelbudgettypes', 'BudgetType', $_POST['BudgetType'], 'BudgetTypeID');
		foreach ($columnsubtoedit as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; } 
		$sql='UPDATE `approvals_3budgetrequestsub` SET BudgetTypeID='.$budgettypeno.', CashorCard='.($_POST['CashorCard']=='Cash'?0:1).','.$sql.' TimeStamp=Now(), EncodedByNo=\''.$_SESSION['(ak0)'].'\'  WHERE TxnSubId='.$txnsubid.' AND EncodedByNo=(SELECT EncodedByNo FROM approvals_3budgetandliq WHERE TxnID='.$txnid.')'; 
            $stmt=$link->prepare($sql); $stmt->execute();      }          
		header('Location:requestforbudget.php?w=Lookup&TxnID='.$txnid);
		break;
        
        case 'DelSub':
                require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
            if($editok==1){
                $sql='DELETE FROM `approvals_3budgetrequestsub` WHERE TxnSubId='.$_REQUEST['TxnSubId'].' AND EncodedByNo=\''.$_SESSION['(ak0)'].'\''; 
            $stmt=$link->prepare($sql); $stmt->execute();}
		header('Location:requestforbudget.php?w=Lookup&TxnID='.$txnid);
		break; 
            
        case 'RequestComplete':
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                $set=!isset($_GET['Set'])?1:$_GET['Set'];
                $sql='UPDATE `approvals_3budgetandliq` SET RequestCompleted='.$set.' WHERE TxnID='.$txnid.' AND EncodedByNo=\''.$_SESSION['(ak0)'].'\' AND Approved=0 AND FundsAccepted=0 AND Liquidated=0'; 
                $stmt=$link->prepare($sql); $stmt->execute();
		header('Location:requestforbudget.php?w=Lookup&TxnID='.$txnid);
            break;
        
        case 'SetApprove':
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
            if($forapprovalby==$_SESSION['(ak0)']){
                $set=$_GET['Set'];
                $sql='UPDATE `approvals_3budgetandliq` SET Approved='.$set.', ApprovedByNo=\''.$_SESSION['(ak0)'].'\', ApprovedTS=Now() WHERE TxnID='.$txnid.' AND FundsAccepted=0 AND Liquidated=0'; 
            $stmt=$link->prepare($sql); $stmt->execute();}
		header('Location:requestforbudget.php');
            break;
        
        case 'UnsetRel':
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                $sql='UPDATE `approvals_3budgetandliq` SET `FundsReleased`=0, ReleasedByNo=\''.$_SESSION['(ak0)'].'\', ReleasedTS=Now() WHERE TxnID='.$txnid; 
                $stmt=$link->prepare($sql); $stmt->execute();
		header('Location:requestforbudget.php?w=Lookup&TxnID='.$txnid);
            break;
            
        case 'AcceptFunds':
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                $sql='UPDATE `approvals_3budgetandliq` SET FundsAccepted=1, AcceptedByNo=\''.$_SESSION['(ak0)'].'\', AcceptedTS=Now() WHERE TxnID='.$txnid.' AND EncodedByNo=\''.$_SESSION['(ak0)'].'\''; 
                $stmt=$link->prepare($sql); $stmt->execute();
		header('Location:requestforbudget.php?w=Lookup&TxnID='.$txnid);
            break;
        
        case 'Claim':
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                $sql0='SELECT ApprovedByNo FROM `approvals_3budgetandliq` WHERE TxnID='.$txnid; $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
                $sql='UPDATE `approvals_3budgetandliq` SET EncodedByNo=\''.$_SESSION['(ak0)'].'\''
                        .(($res0['ApprovedByNo']==$_SESSION['(ak0)'])?',ApprovedByNo=0, Approved=0 ':'')
                        .', TimeStamp=Now() WHERE TxnID='.$txnid.' AND EncodedByNo=\''.$_REQUEST['EncodedByNo'].'\''; 
                $stmt=$link->prepare($sql); $stmt->execute();
		header('Location:requestforbudget.php?w=Lookup&TxnID='.$txnid);
            break;
                
	  
	case 'EditBill':
                if ($requester==$_SESSION['(ak0)'] and $editliqok==1){ 
                    $sql1='SELECT TxnID FROM `approvals_3budgetandliqcashcount` WHERE TxnID='.$txnid;
                    $stmt=$link->query($sql1);$result=$stmt->fetch();
		$bills=array('1000','500','200','100','50','20','10','5','1','025','010','005');
		$sql=''; 
		foreach ($bills as $bill){ $sql=$sql.' `' . $bill. '`='.$_POST[$bill].', '; }
                if($stmt->rowCount()>0){
		$sql='UPDATE `approvals_3budgetandliqcashcount` SET '.$sql.'  EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() WHERE TxnID='.$txnid; 
                } else { $sql='INSERT INTO `approvals_3budgetandliqcashcount` SET '.$sql.'  EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now(),TxnID='.$txnid;}
                $stmt=$link->prepare($sql);$stmt->execute();} 
		header('Location:requestforbudget.php?w=Lookup&TxnID='.$txnid);
	    break;

        
        case 'AddLiqSub':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                if($editliqok==1){
		$sql=''; 
                $budgettypeno=comboBoxValue($link, 'approvals_1travelbudgettypes', 'BudgetType', $_POST['BudgetType'], 'BudgetTypeID');
		foreach ($columnstoeditliq as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; } 
		$sql='INSERT INTO `approvals_3budgetliquidatesub` SET TxnID='.$txnid.', BudgetTypeID='.$budgettypeno.', CashorCard='.($_POST['CashorCard']=='Cash'?0:1).','.$sql.' TimeStamp=Now(), EncodedByNo=\''.$_SESSION['(ak0)'].'\''; 
                $stmt=$link->prepare($sql); $stmt->execute();  }              
		header('Location:requestforbudget.php?w=Lookup&TxnID='.$txnid);
		break;
                
        case 'EditLiqSubSpecifics':
            $title='Edit Budget Request'; 
            if($editliqok==1){
             $txnsubid=$_GET['TxnSubId'];
            $sql=$sqlliqsub.' AND TxnSubId='.$txnsubid; 
            $columnstoedit=$columnstoeditliq; $columnstoedit[]='BudgetType'; $columnstoedit[]='CashorCard'; $columnnames=$columnnamesliq;
            $columnslist=array('BudgetType','CashorCard');
            $listsname=array('BudgetType'=>'budgettype','CashorCard'=>'cashorcard'); $liststoshow=array(); $listcondition=''; $processlabelblank='';
            $action='requestforbudget.php?w=EditLiqSub&TxnID='.$txnid.'&TxnSubId='.$txnsubid; $method='POST';}
            include('../backendphp/layout/rendersubform.php');
            break;
        
        case 'EditLiqSub':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
            if($editliqok==1){
                 $txnsubid=$_GET['TxnSubId'];
		$sql=''; 
                $budgettypeno=comboBoxValue($link, 'approvals_1travelbudgettypes', 'BudgetType', $_POST['BudgetType'], 'BudgetTypeID');
		foreach ($columnstoeditliq as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; } 
		$sql='UPDATE `approvals_3budgetliquidatesub` SET BudgetTypeID='.$budgettypeno.', CashorCard='.($_POST['CashorCard']=='Cash'?0:1).','.$sql.' TimeStamp=Now(), EncodedByNo=\''.$_SESSION['(ak0)'].'\'  WHERE TxnSubId='.$txnsubid.' AND EncodedByNo=(SELECT EncodedByNo FROM approvals_3budgetandliq WHERE TxnID='.$txnid.')'; //echo $sql;
            $stmt=$link->prepare($sql); $stmt->execute();      }          
		header('Location:requestforbudget.php?w=Lookup&TxnID='.$txnid);
		break;
        
        case 'DelLiqSub':
                require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
            if($editliqok==1){
                $sql='DELETE FROM `approvals_3budgetliquidatesub` WHERE TxnSubId='.$_REQUEST['TxnSubId'].' AND EncodedByNo=\''.$_SESSION['(ak0)'].'\''; 
            $stmt=$link->prepare($sql); $stmt->execute();}
		header('Location:requestforbudget.php?w=Lookup&TxnID='.$txnid);
		break;     
        
        case 'LiqCompleteByRequester':
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                $set=!isset($_GET['Set'])?1:$_GET['Set'];
                $sql='UPDATE `approvals_3budgetandliq` SET ForLiqSubmission='.$set.', ForLiqSubByNo=\''.$_SESSION['(ak0)'].'\',ForLiqSubTS=Now() WHERE TxnID='.$txnid.' AND EncodedByNo=\''.$_SESSION['(ak0)'].'\' AND DocsComplete=0 AND Liquidated=0 '; 
                $stmt=$link->prepare($sql); $stmt->execute();
		header('Location:requestforbudget.php?w=Lookup&TxnID='.$txnid);
            break;
        
        case 'DocsReceived':
            if (allowedToOpen(5231,'1rtc')){
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                $set=!isset($_GET['Set'])?1:$_GET['Set'];
                $sql='UPDATE `approvals_3budgetandliq` SET DocsComplete='.$set.', DocsCompleteByNo=\''.$_SESSION['(ak0)'].'\',DocsCompleteTS=Now() WHERE TxnID='.$txnid.' AND Liquidated=0'; 
            $stmt=$link->prepare($sql); $stmt->execute();}
		header('Location:requestforbudget.php?w=Lookup&TxnID='.$txnid);
            break;
        
        case 'Print':
            $title='Liquidation'; $formdesc='<a href="javascript:window.print()">Print</a>';            
            $sql=$sql0.' WHERE bl.TxnID='.$txnid.$condition; $hidecontents=1;
            $columnnames=array('Requester','AcceptedTS','Purpose','DurationInDays','RequestAmt','Branch','FundsReleased',
    );
            
            include('../backendphp/layout/displayastable.php');
            
            $subtitle='<br><br>Budget request';            
            $sql=$sqlsub; $columnnames=$columnsub;  
            include('../backendphp/layout/displayastableonlynoheaders.php');
            
            $stmt=$link->query($sqltotalbudget); $resulttotal=$stmt->fetch();
            $pcf=$resulttotal['TotalCashBudget'];
            
            $subtitle='<br><br>Expenses';
            $sql=$sqlliqsub; $columnnames=$columnnamesliq; $title='';
            include('../backendphp/layout/displayastableonlynoheaders.php');
            
            $sql=$sqlspent;
            $stmt=$link->query($sqltotalspent);$res=$stmt->fetch();
            $sqltotal=$sqltotalspent;
            $columnnames=$columnnamesspent;
            $stmt=$link->query($sqltotal); $resulttotal=$stmt->fetch();
            
            $totalstext='<br><br><div style="border: solid 1px; padding: 3px; width:60%;">Total Liquidation: '.str_repeat('&nbsp;', 3).$res['Total']
                    .'<br><br>Total Card Expenses: '.str_repeat('&nbsp;', 3).$res['TotalCard']
                    .'<br>Total Cash Expenses: '.str_repeat('&nbsp;', 3).$res['TotalCash']
                    .'<br><br><b>Cash Balance for Return: '.str_repeat('&nbsp;', 3).  ((($pcf-$res['TotalCash'])<0)?0:number_format(($pcf-$res['TotalCash']),2)).'</b></div>'
                    .'</div><div style="float:right; width:40%;">';
            $subtitle='<br><br>Summary';
            echo '<div style="max-width: 100%;" ><div style="float:left; width:60%;">';
            include('../backendphp/layout/displayastableonlynoheaders.php');
             
            $cashcounttitle='<br><br>Cash returned';
            include('../backendphp/layout/calcbillsforpettycash.php'); 
            
			$sqlu='SELECT Nickname,SurName FROM `1employees` WHERE IDNo='.$_SESSION['(ak0)'];
            $stmtu=$link->query($sqlu);$resultuser=$stmtu->fetch();
            $user=$resultuser['Nickname'].' '.$resultuser['SurName'];
            
            $sql1='SELECT CONCAT(FirstName, " ", SurName) AS DeptHeadName FROM `1employees` WHERE IDNo='.$forapprovalby;
            $stmt=$link->query($sql1);$res1=$stmt->fetch();
            echo str_repeat('<br>',2).'Liquidation Submitted By'.str_repeat('<br>',4).'____________________________<br>Signature above printed name<br><br>'
                    . 'Approved By'.str_repeat('<br>',4).'<u>'.$res1['DeptHeadName'].'</u><br>Department Head<br>'
                    . '</div>'; // right
            echo ''; // wrapper
            
            
            
            
            echo '<div style="float: left;"><br><br>Liquidation Received by Accounting'.str_repeat('<br>',4).'____________________________<br>Signature above printed name<br></div></div>';
          echo '<div style="position: fixed;bottom: 10px; " >Printed by '.$user.' on '.date('Y-m-d h:i:s').'</div>';
            break;
        
            
        case 'SetAsLiquidated':
            if (!allowedToOpen(5234,'1rtc')){echo 'No permission'; exit;}
            $title='Set as liquidated successfully ';
            $formdesc='Only requests with complete documents are seen here.';
            $sql=$sql0.' WHERE DocsComplete=1 AND Liquidated=0';
            $columnnames=array('Requester','DateNeeded','Purpose','DurationInDays','RequestAmt','Cash','Card','Branch','FundsReleased','DocsComplete','DocsCompleteDate');
            $editprocess='requestforbudget.php?w=SetLiquidated&action_token='.$_SESSION['action_token'].'&List=1&TxnID=';$editprocesslabel='Set Liquidation as Complete';
		include('../backendphp/layout/displayastable.php');
            break;
            
            
        case 'SetLiquidated':
            if (allowedToOpen(5234,'1rtc')){
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                $set=!isset($_GET['Set'])?1:$_GET['Set'];
                $sql='UPDATE `approvals_3budgetandliq` SET Liquidated='.$set.', SetLiqByNo=\''.$_SESSION['(ak0)'].'\',SetLiqTS=Now() WHERE TxnID='.$txnid.' AND EncodedByNo<>\''.$_SESSION['(ak0)'].'\''; 
            $stmt=$link->prepare($sql); $stmt->execute();}
            header("Location:".$_SERVER['HTTP_REFERER']);
		//header('Location:requestforbudget.php?w=Lookup&TxnID='.$txnid);
            break;
        
}
noform:
      $link=null; $stmt=null;
?>