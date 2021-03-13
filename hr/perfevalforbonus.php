<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(687,'1rtc')) {   echo 'No permission'; exit;} 
include_once('../switchboard/contents.php');

 
include_once('../backendphp/layout/regulartablestyle.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

$columnnameslist=array('Department','CurrentPosition','IDNo','FullName','DateHired','EvalDueDate','SelfEval','AveSupervisorEval',
    'Teamwork','CustFocus','Comm','Integrity','Driven','JobKnow','WorkQlty','Adapt','SelfRemarks','SupervisorRemarks'); 
$columnstoedit=array('Teamwork','CustFocus','Comm','Integrity','Driven','JobKnow','WorkQlty','Adapt');

$which=(!isset($_GET['which'])?'List':$_GET['which']);

$sql0='SELECT pf.*, 
SuperTeamwork AS Teamwork, SuperCustFocus as CustFocus, SuperComm AS Comm, SuperIntegrity AS Integrity, SuperDriven AS Driven,
SuperJobKnow AS JobKnow, SuperWorkQlty AS WorkQlty, SuperAdapt AS Adapt, SuperRemarks AS SupervisorRemarks,
CONCAT(e1.FirstName, " ", e1.Surname) AS FullName, e1.DateHired, TRUNCATE(((TO_DAYS(NOW()) - TO_DAYS(`e1`.`DateHired`)) / 365),2) AS `HowLongWithUsinYrs`, e.Nickname as EncodedBy, Position AS CurrentPosition, b.Branch AS CurrentBranch, CONCAT(e2.Nickname, " ", e2.Surname) AS Supervisor, Department,
(IFNULL(SelfJobKnow,0)+IFNULL(SelfWorkQlty,0)+IFNULL(SelfIntegrity,0)+IFNULL(SelfTeamwork,0)+IFNULL(SelfComm,0)+IFNULL(SelfAdapt,0)+IFNULL(SelfCustFocus,0)+IFNULL(SelfDriven,0))/8 AS SelfEval, 
    (IFNULL(SelfJobKnow,0)+IFNULL(SelfWorkQlty,0)+IFNULL(SelfIntegrity,0)+IFNULL(SelfTeamwork,0)+IFNULL(SelfComm,0)+IFNULL(SelfAdapt,0)+IFNULL(SelfCustFocus,0)+IFNULL(SelfDriven,0))/8 AS SelfEval, (IFNULL(SuperJobKnow,0)+IFNULL(SuperWorkQlty,0)+IFNULL(SuperIntegrity,0)+IFNULL(SuperTeamwork,0)+IFNULL(SuperComm,0)+IFNULL(SuperAdapt,0)+IFNULL(SuperCustFocus,0)+IFNULL(SuperDriven,0))/8 AS AveSupervisorEval, EmpRemarks AS Commitment, IF(EmpResponse=1,"Agree",(IF(EmpResponse=0,"","Disagree"))) AS Reaction
	       FROM 2perfeval pf   
	       JOIN `1employees` e ON e.IDNo=pf.HREncodedByNo
	       JOIN `1employees` e1 ON e1.IDNo=pf.IDNo
	       LEFT JOIN `1employees` e2 ON e2.IDNo=pf.SupervisorIDNo
	       JOIN `1branches` b ON b.BranchNo=pf.CurrentBranchNo
	       LEFT JOIN `attend_0positions` p ON p.PositionID=pf.CurrentPositionID 
                JOIN `1departments` d ON d.deptid=p.deptid 
	       ';

$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Department,Branch,FullName'); 
$condition1=' WHERE pf.EvalAfterDays='.$currentyr.' ';
$orderby=' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
if (allowedToOpen(6871,'1rtc')){ $condition=$orderby;} else {
$condition=' AND (SupervisorIDNo='.$_SESSION['(ak0)'].' OR DeptHeadIDNo='.$_SESSION['(ak0)'].')'.$orderby;}
$formdesc='';
?><div style="margin: 15px; background: #FFFFFF;">
<h4><br>Complete Evaluation Process</h4><ol style="margin: 20px; ">
            <li>Self-evaluation - must end with "Set as Completed"</li>
            <li>Supervisor evaluation - evaluation is available to supervisor only if employee has finished. Supervisor must also set as completed.</li>
            <li>Dept Head confirmation - Department head comments and confirms evaluation.</li>
            <li>Employee acknowledgement and commitment - The employee must set as AGREE or DISAGREE to the evaluation of the supervisor. He/she is encouraged to write his/her commitment in reaction to the supervisor's evaluation.</li>
            <li>HR status - HR must print, file into 201, and set status online evaluation as FINISHED. Both HR and respective department heads must sign on the print out so they are aware of improvement plans.</li>
</ol><br></div>
<?php
switch ($which){
    case 'List':
        $txnid='TxnID'; $addlprocess='perfevalentryform.php?TxnID='; $addlprocesslabel='Edit_Specifics';
       $title='Evaluations for Performance Bonuses'; $method='POST';
       $formdesc='<br><br><h4>Step 1. Unfinished Self-Evaluations</h4>';
       $columnsub=$columnnameslist;
        $columnnames=array('Department','CurrentPosition','IDNo','FullName','DateHired','EvalDueDate');         
        $sql=$sql0.$condition1.' AND pf.SelfCompleted=0 '.$condition; 
        include('../backendphp/layout/displayastable.php'); 
        
        $subtitle='<br><br><h4>Step 2. Incomplete Supervisor Evaluations</h4>';
        $columnnames=$columnnameslist;         
        $sql=$sql0.$condition1.' AND pf.SelfCompleted<>0 AND pf.SuperCompleted=0 '.$condition; 
        include('../backendphp/layout/displayastableonlynoheaders.php'); 
        
        $subtitle='<br><br><h4>Step 3. Unconfirmed by Dept Head</h4>';
        $columnnames=array('Department','CurrentPosition','IDNo','FullName','DateHired','SelfEval','AveSupervisorEval','SelfRemarks','SupervisorRemarks','DeptHeadComment');
        $sql=$sql0.$condition1.' AND pf.SelfCompleted<>0 AND pf.SuperCompleted<>0 AND pf.DeptHeadConfirm=0 '.$condition; 
        include('../backendphp/layout/displayastableonlynoheaders.php'); 
       
        $subtitle='<br><br><h4>Step 4. No Acknowledgement</h4>';
        $columnnames=array('Department','CurrentPosition','IDNo','FullName','DateHired','SelfEval','AveSupervisorEval','SelfRemarks','SupervisorRemarks','DeptHeadComment','Reaction','Commitment');
        $sql=$sql0.$condition1.' AND pf.SelfCompleted<>0 AND pf.SuperCompleted<>0 AND pf.DeptHeadConfirm<>0 AND pf.EmpResponse=0 '.$condition;         
        include('../backendphp/layout/displayastableonlynoheaders.php'); 
        
        $subtitle='<br><br><h4>Step 5. HR has not finalized</h4>';
        $columnnames=array('Department','CurrentPosition','IDNo','FullName','DateHired','SelfEval','AveSupervisorEval','SelfRemarks','SupervisorRemarks','DeptHeadComment','Reaction','Commitment');
        if (allowedToOpen(6871,'1rtc')) {$addlprocess='perfevalentryform.php?print=1&TxnID='; $addlprocesslabel='Print_Preview?';}
        if (allowedToOpen(687,'1rtc')) {$addlprocess2='perfevalentry.php?w=HRStatus&TxnID='; $addlprocesslabel2='Set_As_Completed'; }
        $sql=$sql0.$condition1.' AND pf.SelfCompleted<>0 AND pf.SuperCompleted<>0 AND pf.DeptHeadConfirm<>0 AND pf.EmpResponse<>0 AND pf.HRStatus=0 '.$condition; 
        include('../backendphp/layout/displayastableonlynoheaders.php'); 
        
        $subtitle='<br><br><h4>Completed!</h4>';
        $columnnames=array('Department','CurrentPosition','IDNo','FullName','DateHired','SelfEval','AveSupervisorEval','SelfRemarks','SupervisorRemarks','DeptHeadComment','Reaction','Commitment');     
        $sql=$sql0.$condition1.' AND pf.SelfCompleted<>0 AND pf.SuperCompleted<>0 AND pf.DeptHeadConfirm<>0 AND pf.EmpResponse<>0 AND pf.HRStatus<>0 '.$condition;
        include('../backendphp/layout/displayastableonlynoheaders.php'); 
        break;
   
    
}
$link = null; $link = null;
?>