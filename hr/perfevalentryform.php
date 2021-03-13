<?php
$path=$_SERVER['DOCUMENT_ROOT']; 
if(session_id()==''){
	session_start();
}
if(!isset($_SESSION['oss'])){
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;
if (!isset($_REQUEST['print'])){include_once('../switchboard/contents.php'); $printcondition='';} else { include_once $path.'/acrossyrs/dbinit/userinit.php'; $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
  $printcondition=' AND `DeptHeadConfirm` IN (0,1)';
 
 echo '<div id="hidethis"><button onclick="printEvalForm()" style="background-color:green;color:white;padding:5px;">Print this form</button>
</div>
<script>
function printEvalForm() {
  window.print();
}
</script>';
echo '<style>@media print
	{
		#hidethis { display: none; }
	}</style>';
 }
} else {
	include_once $path.'/acrossyrs/dbinit/userinit.php';
	$link=!isset($link)?connect_db(date('Y').'_1rtc',0):$link;
	$printcondition='';
	
	function html_escape($raw_input) { 
		return htmlspecialchars($raw_input, ENT_QUOTES | ENT_HTML401, 'UTF-8'); 
	}
	if ((time() - $_SESSION['LAST_ACTIVITY'] > 1800)){ //30 minutes 60*30=1800; 
		$nologin=5;  
		include $path.'/logout.php'; exit();
	}
} 
// exit();
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
  

$title='Performance Evaluation Form';
include_once 'perfevalentrymain.php';
//if ((allowedToOpen(683,'1rtc')) or ($result['DeptHeadIDNo']==$_SESSION['(ak0)'])){
	
	/*if (!isset($_GET['print'])){
	echo '<h3>Add Other Evaluator</h3> <i>(only if weights have been set)</i><br/><br/>';
	echo '<div>';
	echo '<div style="float:left;"><form action="#" method="post">';
		
		// $sql='SELECT PositionID, Position FROM attend_0positions ORDER BY Position';
		$sql='SELECT p.PositionID, p.Position FROM attend_0positions p JOIN attend_30currentpositions cp ON p.PositionID=cp.PositionID JOIN attend_1branchgroups bg ON cp.BranchNo=bg.BranchNo WHERE bg.TeamLeader='.$result['IDNo'].' GROUP BY Position ORDER BY Position';
		// echo $sql;
		$stmt = $link->query($sql);
		
		echo 'Multiple Evaluator: <select name="OtherEvaluatorPosition">';
		echo '<option value="invalid">--Please Select--</option>';
		while($row= $stmt->fetch()) {
			echo '<option value="'.$row['PositionID'].'">'.$row['Position'].'</option>';
		}
		echo '</select>';
		
		echo ' <input type="submit" name="btnOtherEvaluator" value="Add"/>';
		echo '</form>';

		echo '</div><div style="margin-left:22%;"><form action="perfeval.php?w=AddAnotherEvaluator" method="post">';
		
		echo comboBox($link,'SELECT IDNo, CONCAT(Nickname, " ", Surname) AS FullName FROM `1employees` WHERE Resigned=0 ORDER BY Nickname','IDNo','FullName','employees');
		echo 'Single Evaluator: <input type="text" name="AnotherEvaluator" list="employees">';
		echo '<input type="hidden" name=TxnID value="'.$txnid.'"> <input type="submit" name="btnOtherEvaluator" value="Add"/>';
		echo '</form></div></div>';
	} */
  
//}




$sql0='SELECT DATE_FORMAT(MAX(SuperCompletedTS),\'%Y %M %d\') AS LastEval FROM `hr_2perfevalmain` WHERE IDNo='.$result['IDNo'];  $stmt0=$link->query($sql0); $result0=$stmt0->fetch();
/*
if (isset($_POST['btnOtherEvaluator'])){
	if ($_POST['OtherEvaluatorPosition']=='invalid'){
		echo '<font color="red">Please select position of evaluator.</font>';
	}
	else {
	$sql0 = 'SELECT e.IDNo FROM `attend_1defaultbranchassign` dba JOIN `attend_1branchgroups` bg ON dba.DefaultBranchAssignNo=bg.BranchNo JOIN `1employees` e ON e.IDNo=dba.IDNo JOIN `attend_30currentpositions` cp ON cp.IDNo=dba.IDNo WHERE TeamLeader='.$result['IDNo'].' AND e.Resigned=0 AND cp.PositionID IN ('.$_POST['OtherEvaluatorPosition'].');';
	$stmt0 = $link->query($sql0);
	
	$checkcnt = $row0 = $stmt0->rowCount();
	
		if ($checkcnt>0) {
			while($row0 = $stmt0->fetch()) {
				$sql='INSERT INTO `hr_2perfevalsub` (`TxnID`,`PSID`)  SELECT pe.`TxnID`, ps.`PSID` FROM `hr_2perfevalmain` pe JOIN `hr_1evaluatorpercentage` ep ON ep.ToEvaluate=pe.CurrentPositionID JOIN `hr_1positionstatement` ps ON ep.EPID=ps.EPID JOIN `1employees` e ON pe.IDNo=e.IDNo WHERE ep.ToEvaluate='.$result['CurrentPositionID'].' AND Evaluator='.$_POST['OtherEvaluatorPosition'].' AND TxnID='.$_GET['TxnID'].' AND ep.EvalMonth=1;';
				
				$stmt=$link->prepare($sql); $stmt->execute();
				// echo $sql;
			}
			echo '<font color="green">Evaluator added successfully.</font><br/>';
		}
		
	}
} */

$SetHRStatus='';
$ToImprove='<br><br><h4>Improvement Plan:</h4> '.$result['ToImprove'];
$ToDevelop='<br><h4>Development Plan:</h4> '.$result['ToDevelop'];
 $SelfRemarks='<div id="headings">Remarks on self-evaluation:</div>&nbsp; &nbsp;<div id="comments">'.$result['SelfRemarks'].'</div><br><br>';
    $SuperRemarks='<div id="main"><br><br>  <div id="headings">Supervisor\'s comments:</div>&nbsp; &nbsp;<div id="comments">'.$result['SuperRemarks'].'</div></div><br><br>'; 
    //TO Show if agree/disagree
        if($result['EmpResponse']==1){$resp = "Agree";} else if($result['EmpResponse']==-1){ $resp = '<font color="red">Disagree</font>';} else {$resp='<font color="blue">Please respond.</font>';}
    $EmpResponse='<div id="headings">Employee Response:</div>&nbsp; &nbsp;<div id="comments">'.$resp.'</div>';
  
    $EmpRemarks='<div id="headings">Employee\'s comments/commitment:</div>&nbsp; &nbsp;<div id="comments">'.$result['EmpRemarks'].'</div><br><br>'; 
    $HRRemarks=$result['HRRemarks']; 
    if (!isset($_REQUEST['print'])){
    $HRStatus=$result['HRStatus']==0?'UNFINISHED':'HR has received and filed.';$HRStatusfont=$result['HRStatus']==0?'red':'green';
    $HRStatusText='Evaluation Status: &nbsp; &nbsp; <div id="comments"><font color="'.$HRStatusfont.'">'.$HRStatus.'</font></div>';
    }
    else { $HRStatusText=''; }
    $DeptHeadComment=$result['DeptHeadConfirm']==0?'':'<div id="main"><br><br>  <div id="headings">Department Head\'s comments:</div>&nbsp; &nbsp;<div id="comments">'.$result['DeptHeadComment'].'</div><br>';
	$Recommendation='<div id="main"><br><br>  <div id="headings">Recommendation: </div>&nbsp; &nbsp;<div id="comments">'.$result['Recommendation'].'</div>';
    
if ($result['HRStatus']==0){ 

	
    $startofform='<form method=post action="perfevalentry.php?w=EditScores" display="inline">';
    $who=$result['IDNo']==$_SESSION['(ak0)']?'Self':'Super';
	
	// Submit Scores
    $endofform=str_repeat('&nbsp;',10).'<input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'">'
        .'<input type="hidden" name="TxnID" value="'. $txnid.'">'
		
        .'<input type="hidden" name="who" value="'.$who.'"><input type="submit" value="Submit Scores" ></form><br>';
	//Submit Self-Remarks
	$endofformsf=str_repeat('&nbsp;',10).'<input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'">'
        .'<input type="hidden" name="TxnID" value="'. $txnid.'">'
		
        .'<input type="hidden" name="who" value="'.$who.'"><input type="submit" value="Submit Self-Remarks" ></form><br>';
		
	//Set As Complete
	$endofformcomplete=str_repeat('&nbsp;',10).'<input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'">'
        .'<input type="hidden" name="TxnID" value="'. $txnid.'">'
		
        .'<input type="hidden" name="who" value="'.$who.'"><input type="submit" value="Set As Complete (POST)" ></form><br>';
		
	//Submit Improvement Plan
	$endofformimprovement=str_repeat('&nbsp;',10).'<input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'">'
        .'<input type="hidden" name="TxnID" value="'. $txnid.'">'
		
        .'<input type="hidden" name="who" value="'.$who.'"><input type="submit" value="Submit Improvement Plan" ></form><br>';
    	
	//Submit Development  Plan
	$endofformdevelopment=str_repeat('&nbsp;',10).'<input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'">'
        .'<input type="hidden" name="TxnID" value="'. $txnid.'">'
		
        .'<input type="hidden" name="who" value="'.$who.'"><input type="submit" value="Submit Development Plan" ></form><br>';
    	
	//Submit Development  Plan
	$endofformsupercom=str_repeat('&nbsp;',10).'<input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'">'
        .'<input type="hidden" name="TxnID" value="'. $txnid.'">'
		
        .'<input type="hidden" name="who" value="'.$who.'"><input type="submit" value="Submit Comment" ></form><br>';
		
	//Submit Development  Plan
	$endofformresponse=str_repeat('&nbsp;',10).'<input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'">'
        .'<input type="hidden" name="TxnID" value="'. $txnid.'">'
		
        .'<input type="hidden" name="who" value="'.$who.'"><input type="submit" value="Submit Response" ></form><br>';
    
	$endofforminc=str_repeat('&nbsp;',10).'<input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'">'
        .'<input type="hidden" name="TxnID" value="'. $txnid.'">'
        . '<input type="hidden" name="who" value="'.$who.'"><input type="submit" value="Set as Incomplete (UNPost)" ></form><br>';
    
	if(!isset($_SESSION['oss'])){
    if (allowedToOpen(6832,'1rtc')){
    $HRRemarks='<hr><br><div id="headings">For HR use:</div><form method=post action="perfevalentry.php" display="inline"><input type="text" id="textboxid" name="HRRemarks" value="'.$result['HRRemarks'].'"></input>'.$endofform; 
    $SetHRStatus=(!isset($_REQUEST['print'])?'<div><form method=post action="perfevalentry.php?w=HRStatus" display="inline">Set as Completed and Filed in 201'.$endofformcomplete:'');}
	}
    
    if ($result['IDNo']==$_SESSION['(ak0)'] AND ($result['SelfCompleted']==0)){
    $ToImprove=''; $ToDevelop='';
	//score checking self
		$sqlscss = 'SELECT TxnID FROM hr_2perfevalsub WHERE SelfScore IS NULL  AND TxnID='.$_GET['TxnID'].'';
		$stmtscss = $link->query($sqlscss);
		$cntscss= $rowscss = $stmtscss->rowCount();
	
		$SelfRemarks='<i>Submit as completed when finished.  Please acknowledge your supervisor\'s evaluation of you AFTER he has completed his section.</i><div id="main"><br>  <div id="headings">Remarks on self-evaluation:</div>&nbsp; &nbsp;<font color="blue"><i><b>'.$result['SelfRemarks'].'</b></i></font><div id="comments"><form method="post" action="perfevalentry.php?w=Remarks" ><textarea  rows="4" cols="180" maxlength="400"  name="SelfRemarks" placeholder="(Text here will replace what has been entered, if any.) (Up to 400 characters)" ></textarea><br/><font color="black">Step 2:</font>'.$endofformsf.'<form method="post" action="perfevalentry.php?w=Completed" style="display:inline"><font color="black"><b>Step 3:</b></font>' . ($cntscss == 0 ? $endofformcomplete: str_repeat('&nbsp;',10).'<font color="red">Cannot post. Please check your scores.</font>') . '<br></div></div>';
		

                $columnnames=array('SelfRemarks');
    
    
	$SuperRemarks='';$EmpRemarks=''; $supereval='';
  
    // } elseif ($result['IDNo']==$_SESSION['(ak0)'] AND ($result['SelfCompleted']==1)  AND ($result['SuperCompleted']==1) AND ($result['DeptHeadConfirm']==0)){ 
    } elseif ($result['IDNo']==$_SESSION['(ak0)'] AND ($result['SelfCompleted']==1)  AND ($result['SuperCompleted']==1) AND ($result['DeptHeadConfirm']==1)){ 
    $EmpResponse='<div id="headings">Employee\'s response:</div>&nbsp; &nbsp;<form method="post" action="perfevalentry.php?w=EmpResponse" style="display:inline">(Choose one, and click <i>enter</i>.)<br><br>&nbsp; &nbsp; &nbsp; <input type="checkbox" name="EmpResponse" value=1 '.($result['EmpResponse']==1?'checked':'').'><b><font color="darkgreen">&nbsp; &nbsp; I AGREE</font></b> and I commit myself to the improvement/development plan.</input><br><br>&nbsp; &nbsp; &nbsp; <input type="checkbox" name="EmpResponse" value=-1 '.($result['EmpResponse']==-1?'checked':'').'><b><font color="maroon">&nbsp; &nbsp; I DISAGREE</font></b> and I accept full responsibility for any consequence that may arise from my non-agreement.</input><br/><br/><font color="black"><b>Step 1:</b></font>'.$endofformresponse.'</div><br><br>';
    $EmpRemarks='<div id="headings">Employee\'s comments/commitment:</div>&nbsp; &nbsp; <font color="blue"><i><b>'.$result['EmpRemarks'].'</b></i></font>&nbsp; &nbsp; &nbsp;<form method="post" action="perfevalentry.php?w=EmpRemarks" ><textarea  rows="5" cols="90" maxlength="400"  name="EmpRemarks" placeholder="(Text here will replace what has been entered, if any.)" ></textarea><br/><b>Step 2:</b>'.$endofformsupercom;
    } elseif (($result['SupervisorIDNo']==$_SESSION['(ak0)'] )  AND ($result['SuperCompleted']==0)) {
		
    $sqlscsssup = 'SELECT TxnID FROM hr_2perfevalsub WHERE SuperScore IS NULL  AND TxnID='.$_GET['TxnID'].'';
	$stmtscsssup = $link->query($sqlscsssup);
	$cntscsssup= $rowscsssup = $stmtscsssup->rowCount();
		
    $SuperRemarks='<div id="main"><div id="headings">Supervisor\'s comments:</div>&nbsp; &nbsp;<font color="blue"><i><b>'.$result['SuperRemarks'].'</b></i></font>&nbsp; &nbsp;<div id="comments"><form method="post" action="perfevalentry.php?w=Remarks" ><textarea  rows="5" cols="100" maxlength="500"  name="SuperRemarks" placeholder="(Text here will replace what has been entered, if any.)" ></textarea><br/><b><font color="black">Step 4:</font></b>'.$endofformsupercom.'&nbsp; &nbsp; &nbsp;<div><div style="float:left;"><form method="post" action="perfevalentry.php?w=Completed" style="display:inline"><br/><b><font color="black">Step 5:</font></b>'.($cntscsssup == 0 ? $endofformcomplete: str_repeat('&nbsp;',10).'<font color="red">Cannot post. Please check your scores.</font></form>').'
	</div><div style="float:right"><form method="post" action="perfevalentry.php?w=SetIncompleteSuper" style="display:inline">&nbsp; &nbsp;&nbsp; &nbsp; Set As Incomplete? &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$endofforminc.'</div></div></div></div><br><br>'; 
    
	$ToImprove='<div id="headings">Improvement Plan (such as specific skills for the current job)</div>&nbsp; &nbsp;<font color="blue"><i><b>'.$result['ToImprove'].'</b></i></font><br><form action="perfevalentry.php?w=Improve" style="display:inline;" method="post"><textarea  rows="4" cols="60" maxlength="100" name="Improve" placeholder="(Up to 100 characters)" ></textarea><br/><b>Step 2:</b>'.$endofformimprovement.'<br/>';//.$ToImproveList
    
    $ToDevelop='<div id="headings">Development Plan (for career advancement)</div>&nbsp; &nbsp;<font color="blue"><i><b>'.$result['ToDevelop'].'</b></i></font><br><form action="perfevalentry.php?w=Develop" style="display:inline;" method="post"><textarea rows="4" cols="60" maxlength="100"  name="Develop" placeholder="(Up to 100 characters)" ></textarea><br/><b>Step 3:</b>'.$endofformdevelopment.'<br>';//.$ToDevelopList
	
     $EmpResponse=''; $EmpRemarks=''; 
    }  
    
    if ($result['DeptHeadIDNo']==$_SESSION['(ak0)'] AND ($result['SelfCompleted']==1)  AND ($result['SuperCompleted']==1) AND ($result['DeptHeadConfirm']==0)){ 
        $DeptHeadComment='<div id="headings">Department Head\'s comments:</div>&nbsp; &nbsp;<font color="blue"><i><b>'.$result['DeptHeadComment'].'</b></i></font>&nbsp; &nbsp;<div id="comments"><form method="post" action="perfevalentry.php?w=DeptHeadComment" ><textarea  rows="3" cols="175" maxlength="500"  name="DeptHeadComment" placeholder="(Text here will replace what has been entered, if any.)" ></textarea><br/><b><font color="black">Step 1:</font><b>'.$endofformsupercom.'</div><br><div id="headings">Recommendation:  (<i>cannot be seen by employee</i>)</div>&nbsp; &nbsp;<font color="blue"><i><b>'.$result['Recommendation'].'</b></i></font>&nbsp; &nbsp;<div id="comments"><form method="post" action="perfevalentry.php?w=Recommendation" ><textarea  rows="3" cols="175" maxlength="500"  name="Recommendation" placeholder="(Text here will replace what has been entered, if any.)" ></textarea><br/><b><font color="black">Step 2:</font><b>'.$endofformsupercom.'</div>&nbsp; &nbsp; &nbsp;<div><div style="float:left;"><form method="post" action="perfevalentry.php?w=DeptHeadConfirm" style="display:inline"><b><font color="black">Step 3:</font></b>'.$endofformcomplete.'
	</div><div style="float:right"><form method="post" action="perfevalentry.php?w=SetIncompleteDeptHead" style="display:inline">&nbsp; &nbsp;&nbsp; &nbsp; Set As Incomplete? &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$endofforminc.'</div></div></div></div><br><br>'; }
}	
        
evalform:
//include_once 'perfevalentrymain.php';
echo '<br/><div id="main">'.$HRStatusText.'</div>'; 

//if not supervisor/depthead/self/HR //will exit
if (($result['IDNo']!=$_SESSION['(ak0)']) AND
($result['SupervisorIDNo']!=$_SESSION['(ak0)']) AND
($result['DeptHeadIDNo']!=$_SESSION['(ak0)']) AND (!allowedToOpen(683, '1rtc')))
        {
                exit();
        }


?>

<?php

//Conditions

//default
if ($result['IDNo']<>$_SESSION['(ak0)']) { $disabled = 'disabled'; $show = 'disabled'; $required = ''; }
else { //Self evaluation
if ($result['SelfCompleted']==0){ $disabled = ''; $show = 'hidden'; $required = ''; goto form;
} elseif ($result['SuperCompleted']==0)  //SelfCompleted=1
    { $disabled = 'disabled'; $show = 'hidden'; $required = ''; goto form;} else { $disabled = 'disabled'; $show = 'disabled'; $required = ''; goto form;}
}

if ($result['SupervisorIDNo']==$_SESSION['(ak0)'] AND $result['SuperCompleted']==0 ){
	$disabled = 'disabled'; $show = ''; $required = 'required'; goto form;
} else { $disabled = 'disabled'; $show = 'disabled'; $required = ''; goto form;}
form:
if ($result['HRStatus']==1){ 
 $who=$result['IDNo']==$_SESSION['(ak0)']?'Self':'Super';
 $endofforminc=str_repeat('&nbsp;',10).'<input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'">'
        .'<input type="hidden" name="TxnID" value="'. $txnid.'">'
        . '<input type="hidden" name="who" value="'.$who.'"><input type="submit" value="Submit" ></form><br>';
		
	$disabled = 'disabled';
$show = 'disabled'; $required = ''; }



?>
<div id="main" >
   
<?php
$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
$rcolor[1]="FFFFFF";
$num=0;
	//Check if there's an evaluatee statement 
		// $sqlcheck='SELECT COUNT(*) AS cnt FROM hr_1positionstatement ps JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID WHERE ToEvaluate = '.$result['CurrentPositionID'].' AND Evaluator=(SELECT supervisorpositionid FROM attend_30currentpositions WHERE IDNo='.$result['IDNo'].')'; 
		// $sqlcheck='SELECT COUNT(*) AS cnt FROM hr_1positionstatement ps JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID WHERE ToEvaluate = '.$result['CurrentPositionID'].' AND Evaluator=(SELECT PositionID FROM attend_30currentpositions WHERE IDNo='.$result['SupervisorIDNo'].') AND EvalMonth= 
												// (CASE
													// WHEN (ROUND(('.$result['InYears(as of EvalDueDate)'].'*12))) <=3 THEN 3
													// WHEN (ROUND(('.$result['InYears(as of EvalDueDate)'].'*12))) <=5 THEN 5
													// WHEN (ROUND(('.$result['InYears(as of EvalDueDate)'].'*12))) <=12 THEN 12
													// ELSE 1
												// END)';
												$sqlcheck='SELECT COUNT(*) AS cnt FROM hr_1positionstatement ps JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID WHERE ToEvaluate = '.$result['CurrentPositionID'].' AND Evaluator=(SELECT PositionID FROM attend_30currentpositions WHERE IDNo='.$result['SupervisorIDNo'].') AND EvalMonth= 
												(CASE
													WHEN (ROUND('.$result['EvalAfterDays'].'/30)) <=3 THEN 3
													WHEN (ROUND('.$result['EvalAfterDays'].'/30)) <=5 THEN 5
													WHEN (ROUND('.$result['EvalAfterDays'].'/30)) <=12 THEN 12
													ELSE 1
												END)'; 
	
		$stmtc=$link->query($sqlcheck);
		$rowc = $stmtc->fetch();
		// echo $rowc['cnt'];
		//Check if theres an entry in hr_2perfevalsub
		 $sql3='SELECT COUNT(*) AS cnt FROM hr_2perfevalsub WHERE TxnID = '.$_GET['TxnID'].''; 
		$stmt3=$link->query($sql3);
		$row3 = $stmt3->fetch();
		// echo $sql3;
		
		if ($rowc['cnt']==0)
		{
			if ($row3['cnt']==0)
			{
				echo 'No Statements<br>';
				exit();
			}
			else {
			$sql='SELECT * FROM hr_1statement s JOIN hr_1positionstatement ps ON s.StatementID=ps.StatementID JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID JOIN hr_2perfevalsub pes ON ps.PSID=pes.PSID WHERE TxnID='.$_GET['TxnID'].'  AND (SelfScore>=0 OR ISNULL(SelfScore)) ORDER BY PerfComID, ps.StatementID'; }
                        if ($result['SelfCompleted']==1) { $disabled = 'disabled'; }
                        if ($result['IDNo']==$_SESSION['(ak0)']) { $show='disabled';}
		}
		 else //if there's an evaluatee statement
		 {
			 if ($row3['cnt']!=0) //if theres an entry in 2 perfevalsub
			 {
				 
				 // $sql='SELECT * FROM hr_1statement s JOIN hr_1positionstatement ps ON s.StatementID=ps.StatementID JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID JOIN hr_2perfevalsub pes ON ps.PSID=pes.PSID WHERE ToEvaluate = '.$result['CurrentPositionID'].' AND ep.Evaluator=(SELECT PositionID FROM attend_30currentpositions WHERE IDNo='.$result['SupervisorIDNo'].') AND TxnID='.$_GET['TxnID'].' AND (SelfScore>=0 OR SelfScore IS NULL) ORDER BY PerfComID, ps.StatementID';
				 $sql='SELECT * FROM hr_1statement s JOIN hr_1positionstatement ps ON s.StatementID=ps.StatementID JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID JOIN hr_2perfevalsub pes ON ps.PSID=pes.PSID WHERE TxnID='.$_GET['TxnID'].' AND (SelfScore>=0 OR SelfScore IS NULL) ORDER BY PerfComID, ps.StatementID';
				 // echo $sql;
			 }
			 else { //No entries
				 // $sql='SELECT * FROM hr_1statement s JOIN hr_1positionstatement ps ON s.StatementID=ps.StatementID JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID WHERE ToEvaluate = '.$result['CurrentPositionID'].' AND ep.Evaluator=(SELECT supervisorpositionid FROM attend_30currentpositions WHERE IDNo='.$result['IDNo'].') ORDER BY PerfComID, ps.StatementID';
				 echo 'No Statements'; 
				 
				 if (allowedToOpen(6864,'1rtc') AND !isset($_POST['btnGenerateStmt'])){
					
					$sql01='SELECT SUM(Weight) AS TotSum,ToEvaluate FROM hr_1positionstatement ps JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID WHERE ToEvaluate=(SELECT PositionID FROM attend_30currentpositions WHERE IDNo='.$result['IDNo'].') AND Evaluator=(SELECT PositionID FROM attend_30currentpositions WHERE IDNo='.$result['SupervisorIDNo'].') AND EvalMonth=(CASE
						WHEN (ROUND('.$result['EvalAfterDays'].'/30)) =3 THEN 3
						WHEN (ROUND('.$result['EvalAfterDays'].'/30)) =5 THEN 5
						WHEN (ROUND('.$result['EvalAfterDays'].'/30)) =12 THEN 12
						ELSE 1
					END)'; $stmt01 = $link->query($sql01);
					$row01=$stmt01->fetch(); 
					
					if($row01['TotSum']<>100){
						echo '<br><br>Total Weight: <font color="red">'.$row01['TotSum']."%</font>";
						echo '<br>You must set the total weight to 100% <a href="perfevalsettings.php?w=StatementAssign&positionid='.$row01['ToEvaluate'].'&filterbtn=Filter"><b>here</b></a>.';
						
					} else {
						echo '<form action="#" method="POST"><input type="submit" name="btnGenerateStmt" value="Generate statements based on settings."/></form>';
					
					}
					
					
					
				} else {
					echo ' or <a href="perfevalentryform.php?TxnID='.$_GET['TxnID'].'&action_token='.$_SESSION['action_token'].'">Please refresh to check.</a>';
				}
					if (isset($_POST['btnGenerateStmt'])){
						$sql='CREATE TEMPORARY TABLE tempEval AS SELECT pf.IDNo,(SELECT PositionID FROM attend_30currentpositions cp WHERE cp.IDNo=pf.IDNo) AS ToEvaluate, (SELECT PositionID FROM attend_30currentpositions cp WHERE cp.IDNo=pf.SupervisorIDNo) AS Evaluator FROM hr_2perfevalmain pf WHERE pf.TxnID='.$_GET['TxnID'].';';
							 
							 $stmt=$link->prepare($sql); $stmt->execute();
							  
							  
							  $sql='INSERT INTO `hr_2perfevalsub` (`TxnID`,`PSID`)
						SELECT '.$_GET['TxnID'].', ps.`PSID` FROM `hr_2perfevalmain` pf JOIN `hr_1evaluatorpercentage` ep ON ep.ToEvaluate=pf.CurrentPositionID
						JOIN `hr_1positionstatement` ps ON ep.EPID=ps.EPID JOIN `1employees` e ON pf.IDNo=e.IDNo JOIN tempEval te ON pf.IDNo=te.IDNo WHERE ep.ToEvaluate=te.ToEvaluate AND ep.Evaluator=te.Evaluator AND ep.EvalMonth=(CASE
		WHEN (ROUND('.$result['EvalAfterDays'].'/30)) =3 THEN 3
		WHEN (ROUND('.$result['EvalAfterDays'].'/30)) =5 THEN 5
		WHEN (ROUND('.$result['EvalAfterDays'].'/30)) =12 THEN 12
		ELSE 1
	END) AND pf.TxnID='.$_GET['TxnID'].';'; 
							  $stmt=$link->prepare($sql); $stmt->execute();
					}
					
				 exit();
			}
			// echo $sql;
                        
		 }
		//if there are entries in hr_2perfevalsub, will go to this case //EditScores in perfevalentry
		if ($row3['cnt']>0 AND $result['SelfCompleted']==0)
		{
			 echo '<form method=\'POST\' action=\'perfevalentry.php?w=EditScores\'><table class=\'\'>';
		}
		//if there are no entries in hr_2perfevalsub, will go to this case //InsertScores in perfevalentry
		else if ($row3['cnt']==0 AND $result['SelfCompleted']==0)
			{ echo '<form method=\'POST\' action=\'perfevalentry.php?w=InsertScore\'><table class=\'\'>'; }
		//To update score of evaluator //EditScoresSuper in perfevalentry
		else if ($row3['cnt']>0 AND $result['SelfCompleted']==1)
		{
			echo '<form method=\'POST\' action=\'perfevalentry.php?w=EditScoresSuper\'>';
		} 
		// if($result['SelfCompleted']==1 AND $result['SuperCompleted']==0){
			
		// } 
					echo '<table class=\'\'><thead><th>Competency</th><th>Statement</th><th>Weight</th>'.(($result['SelfCompleted']==1 AND $result['SuperCompleted']==0)?'':'<th>Self-Assessment<BR>(1 to 5)</th>').'<th>Supervisor\'s Assessment<BR>(1 to 5)</th></thead>';
					echo '<tbody>'; 
					$stmt = $link->query($sql);
					
					$previous_heading = false;
					$num=0;
					$selfscore = 0;
					$superscore = 0;
					$totweight = 0;
						while($row = $stmt->fetch()) {
							
							if ($row3['cnt']>0){
								
								echo '<input type=\'text\' name=\'sgid'.$num.'\' value=\''.$row['TxnSubId'].'\' hidden/>';
								
								if ($row['SuperScore']==0)
								{
									$row['SuperScore']=null;
								}
								
							}
							else {$row['SelfScore']=null; $row['SuperScore']=null;}
							
							if($previous_heading != $row['PerfComID']) {
								
								$Competency = comboBoxValue($link,'hr_1competency','PerfComID',$row['PerfComID'],'Competency');
							}
							else { $Competency = '';}
							
							echo '<tr bgcolor="'. $rcolor[$num%2].'"><td>'.$Competency.'</td><td>' .($num + 1).'. ' . comboBoxValue($link,'hr_1statement','StatementID',$row['StatementID'],'Statement') . '</td><td>'.$row['Weight'].'%</td>';
							
							// echo '<input type=\'number\' name=\'score'.$num.'\' min=\'1\' max=\'5\' required value="'.$row['SelfScore'].'" '.$disabled.'/>';
							
							$sqlgrade = 'SELECT * FROM hr_1perfscale ORDER BY GradeID';
							
							$stmtgrade = $link->query($sqlgrade);
							if($result['SelfCompleted']==1 AND $result['SuperCompleted']==0){
							
							} else {
								echo '<td><select name="score'.$num.'" '.$disabled.'>';
								echo '<option value="NULL"></option>';
								while($rowgrade = $stmtgrade->fetch())
								{
									if( $row['SelfScore'] == $rowgrade['GradeID'] )
									{
										$selected = 'selected';
									} else { $selected = ''; }
									echo '<option value="'.$rowgrade['GradeID'].'" '.$selected.'>'.$rowgrade['GradeDesc'].'</option>';
								}
								echo '</select>';
								
								
								
								echo '<input type=\'number\' name=\'weight'.$num.'\' value='.$row['Weight'].' hidden/></td>';
							}
							
							$stmtgrade = $link->query($sqlgrade);
							echo '<td><select name="score2'.$num.'" '.$required.' '.$show.'>';
							echo '<option value="NULL"></option>';
							while($rowgrade = $stmtgrade->fetch())
							{
								if( $row['SuperScore'] == $rowgrade['GradeID'] )
								{
									$selected = 'selected';
								} else { $selected = ''; }
								echo '<option value="'.$rowgrade['GradeID'].'" '.$selected.'>'.$rowgrade['GradeDesc'].'</option>';
							}
							echo '</select>';
							
							echo '</td>
								<input type=\'text\' name=\'psid'.$num.'\' value=\''.$row['StatementID'].'\' hidden/>
								<input type=\'text\' name=\'perfevalid'.$num.'\' value=\''.$_GET['TxnID'].'\' hidden/>
								<input type=\'text\' name=\'evaluator'.$num.'\' value=\''.$_SESSION['(ak0)'].'\' hidden/>
								
									</tr>';
							$previous_heading = $row['PerfComID'];
							$num++;
							
							$selfscore = $selfscore + (($row['Weight']/100) * $row['SelfScore']);
							$totweight = $totweight + $row['Weight'];
							$superscore = $superscore + (($row['Weight']/100) * $row['SuperScore']);
						}
						if($result['SelfCompleted']==1 AND $result['SuperCompleted']==0){
							echo '<tr><td align="right" colspan=3><h2>'.$totweight.'%</h2></td><td><h2>'.$superscore.'</h2></td>';
						} else {
							echo '<tr><td align="right" colspan=3><h2>'.$totweight.'%</h2></td><td><h2>'.$selfscore.'</h2></td><td><h2>'.$superscore.'</h2></td>';
						}
						echo '<input type=\'text\' name=\'num\' value=\''.$num.'\' hidden/>';
						echo '<input type=\'text\' name=\'txnid\' value=\''.$_GET['TxnID'].'\' hidden/>';
						echo '<input type=\'text\' name=\'perfevalid\' value=\''.$_GET['TxnID'].'\' hidden/>';
						
						if ($who=='Self'){$cs=4;} else {$cs=5;}
						if ($result['IDNo']==$_SESSION['(ak0)'] AND $result['SelfCompleted']==0 AND $result['DeptHeadConfirm']==0 AND $result['EmpResponse']==0)
						{
							
							echo '<tr><td align=\'right\' colspan=\''.$cs.'\'><b>Step 1:</b>'.$endofform.'</td>';
						}
						else if ($result['IDNo']!=$_SESSION['(ak0)'] AND $result['SelfCompleted']==0 AND $result['SuperCompleted']==0 AND $result['DeptHeadConfirm']==0 AND $result['EmpResponse']==0)
						{
							if($result['SelfCompleted']==1){
								echo '<tr><td align=\'right\' colspan=\''.$cs.'\'><b>Step 1:</b>'.$endofform.'</td>';
							}
						}
						else if (($result['DeptHeadConfirm']==1 OR $result['EmpResponse']==1) OR ($result['IDNo']==$_SESSION['(ak0)'] AND $result['SelfCompleted']==1) OR ($result['SupervisorIDNo']==$_SESSION['(ak0)'] AND $result['SelfCompleted']==1 AND $result['SuperCompleted']==1))
						{
							
						} 
						
						else { echo '<tr><td align=\'right\' colspan=\''.$cs.'\'><b>Step 1:</b>'.$endofform.'</td>'; }
						// echo $who;
						echo	'</tr>';
						echo '</table>';
						echo '</form><br/>';
						
/*						
		echo '<div style="width:100%;">';

$sql221='SELECT COUNT(TxnSubId) AS cnt FROM hr_2perfevalsub WHERE TxnID='.$_GET['TxnID'].' AND Multi=1';
$stmt221=$link->query($sql221);
$res221 = $stmt221->fetch();

if ($res221['cnt']>0){
echo '<div style="float:left;">';						
						//Table For Multi
						
$sql222='SELECT EvaluatorIDNo, b.Branch, TxnID,ep.Evaluator AS Evaluator, ep.ToEvaluate,
Percentage, CONCAT(Nickname, " ", Surname) AS Name,SUM(Weight/100 * SuperScore) AS Grade FROM hr_1positionstatement
ps JOIN hr_1evaluatorpercentage ep ON ps.EPID=ep.EPID JOIN hr_2perfevalsub pes
ON pes.PSID=ps.PSID JOIN `1employees` e ON pes.EvaluatorIDNo=e.IDNo JOIN `attend_1defaultbranchassign` cp ON pes.EvaluatorIDNo=cp.IDNo JOIN `1branches` b ON cp.DefaultBranchAssignNo=b.BranchNo WHERE TxnID='.$_GET['TxnID'].' AND Multi=1 AND ep.Evaluator IN (32,37) GROUP BY TxnID,pes.EvaluatorIDNo ORDER BY Branch;';
                                                $stmt222=$link->query($sql222);

                                                echo '<strong>Other Evaluators (Branch Heads and OIC\'s)</strong>';
                                                echo
'<table><tr><th></th><th>EvaluatorName</th><th>EvaluatorPosition</th><th>Branch</th><th>Grade</th></tr>';
                                                $overall = 0;
                                                $gradegroup = 0;
                                                $newgroup = 0;
												$rowsarr = [];
												$prev_heading = false;
                                                while ($res = $stmt222->fetch())
                                                        {
															$rowsarr[]=$res['Evaluator'];
                                                                $gradeperevaluator = number_format($res['Grade'],2);
                                                                echo '<tr><td><a
href="perfevalentryview.php?TxnID='.$_GET['TxnID'].'&Evaluator='.$res['Evaluator'].'">Look
up
details</a></td></td>
<td>'.$res['Name'].'</td><td>'.comboBoxValue($link,'attend_0positions','PositionID',$res['Evaluator'],'Position').'<td>'.$res['Branch'].'</td><td
align="right">'.$gradeperevaluator.'</td>';


if ((allowedToOpen(683,'1rtc')) or ($result['DeptHeadIDNo']==$_SESSION['(ak0)'])){
echo '<td><a href="perfevalentry.php?w=DeleteEvaluatorMulti&EvaluatorIDNo='.$res['EvaluatorIDNo'].'&TxnID='.$_GET['TxnID'].'" onclick="return confirm(\'Are you sure? This action cannot be undone.\')"><font color="red">x</font></a></td>'; }//end

// echo '<td>'.$overall.'</td>';
                                                                echo '</tr>';
					
                                                                $overall = $overall + $gradeperevaluator;
																
                                                        }
														//all positions in multi
														$arrmulti = implode(",", array_unique($rowsarr)); //Multi
														// echo $arrmulti;
														
														$avemulti = $res = $stmt222->rowCount();
														$multiave = number_format(($overall / $avemulti),2);
                                                        echo '<tr><td colspan="4"
align="right"><h3>Average</h3></td><td align="right"><h3>'.$multiave.'</h3></td></tr>';
                                                echo '</table>'; //End of Multi

												echo '</div>';
												$width="53%";
                                                $unionsql=' UNION ALL SELECT '.$_GET['TxnID'].' AS TxnID, "Branch Head/OIC", 0 AS Evaluator,Percentage,"", '.$multiave.' AS Grade, (Percentage*'.$multiave.'/100) AS Weighted FROM hr_1evaluatorpercentage ep WHERE Evaluator=32 AND ToEvaluate='.$result['CurrentPositionID'].' AND EvalMonth= 
												(CASE
													WHEN (ROUND(('.$result['InYears(as of EvalDueDate)'].'*12))) <=3 THEN 3
													WHEN (ROUND(('.$result['InYears(as of EvalDueDate)'].'*12))) <=5 THEN 5
													ELSE 12
												END)
												';
}		else { $width=''; $unionsql='';}	
*/
//if(isset($arrmulti)){
//	$condi = ' AND Evaluator NOT IN ('.$arrmulti.') ';
//} else {$condi = '';}
//										// echo $overall;
//
//												echo '<div >';//style="margin-left:'.$width.';">';
						                                                
//$sql111='SELECT pem.TxnID, p.Position AS EvaluatorPosition, Percentage, IFNULL(CONCAT(Nickname, " ", Surname),"Not Yet Evaluated") AS Name, SUM(Weight/100 * SuperScore) AS Grade, Percentage*(SUM(Weight/100 * SuperScore)/100) AS Weighted  FROM hr_1positionstatement
//ps JOIN hr_1evaluatorpercentage ep ON ps.EPID=ep.EPID JOIN hr_2perfevalsub pes
//ON pes.PSID=ps.PSID JOIN hr_2perfevalmain pem ON pem.TxnID=pes.TxnID LEFT JOIN `1employees` e ON pem.SupervisorIDNo=e.IDNo JOIN attend_0positions p ON p.PositionID=ep.Evaluator WHERE pem.TxnID='.$_GET['TxnID'].'  GROUP BY pem.TxnID ORDER BY Percentage DESC;';//,ep.Evaluator 
//$stmt111=$link->query($sql111); $res = $stmt111->fetch();
//
//echo '<strong>Evaluation Summary</strong>';
//echo '<table><tr><th>EvaluatorName</th><th>Position</th><th>Grade</th><th>%</th></tr>';//<th>Weighted Grade</th>
                                             //   $overall = 0; $totalpercent=0;
												
												// $sql111='SELECT PositionID FROM attend_30currentpositions WHERE IDNo='.$result['SupervisorIDNo'];
                                                // $stmt111=$link->query($sql111);
												
                                           //     while ($res = $stmt111->fetch())
                                             //           {
															//echo $res['Evaluator'];
															
                                                             //   $gradeperevaluator = number_format(($res['Percentage']/100) *$res['Grade'],2);
                                                                //<td>'
                    //                                            .(($res['Evaluator']==0)?'':'<a
//href="perfevalentryview.php?TxnID='.$_GET['TxnID'].'&Evaluator='.$res['Evaluator'].'">Look up details</a>').'</td>
//<td align="right">'.$res['Percentage'].'%</td>

//$overall = $overall + $gradeperevaluator;
//echo '<tr></td>
//<td>'.$res['Name'].'</td><td>'.$res['EvaluatorPosition'].'</td><td align="right">'.number_format($res['Grade'],2).'</td>'
//        . '<td align="right">'.number_format(($res['Percentage']/100) *$res['Grade'],2).'</td></tr>';

                                                                
                                                              //  $totalpercent = $totalpercent + $res['Percentage'];
                                                    //    } <td align="right"><b>'.$totalpercent.' %</b></td>
//echo '<tr><td colspan="4"
//align="right"><h3>Final Overall Score</h3></td><td align="right"><h3>'.$overall.'</h3></td></tr>';
                                               // echo '</table>';

						//End of Others Dept
						
					//	echo '</div></div>';


						
?>

</div>
<div id="main"></div>
    <br><br>
<?php echo $SelfRemarks; 
if ($result['SelfCompleted']==1) { echo $ToImprove;  echo $ToDevelop; echo $SuperRemarks; echo $DeptHeadComment; }
        //check if completed na lahat ng evaluators
        $sql001='SELECT * FROM hr_2perfevalsub WHERE TxnID='.$txnid.' AND (ISNULL(SelfScore) or (SelfScore=0));';
        $stmt001=$link->query($sql001);
        $row001 = $stmt001->fetch();
        if ($stmt001->rowCount()==0)
        {
                echo $EmpResponse;
                echo '<br><br>'; echo $EmpRemarks;
				if(!isset($_GET['print']) AND (allowedToOpen(6864,'1rtc'))){
					echo $Recommendation;
				}
        }
        else {
                if (($result['SuperCompleted']==1) and ($result['EmpResponse']==0)) { echo '<font color="red">Cannot respond yet. Waiting for Other Evaluators</font><br/><br/>';}
        }
		/* if(isset($_GET['print']) AND $result['Recommendation']<>''){
			echo $Recommendation;
		} */
?>      
    <hr>
<?php 
if(!isset($_SESSION['oss'])){
	if (allowedToOpen(6832,'1rtc')) { 
		
	if ($result['EmpResponse']==1 and $result['HRStatus']==0) {   echo $SetHRStatus; }

	if (!isset($_GET['print'])){
		if ($result['HRStatus']==1) { $who=$result['IDNo']==$_SESSION['(ak0)']?'Self':'Super'; echo '<a href="perfevalentryform.php?print=1&TxnID='.$_GET['TxnID'].'">Print a copy?</a> &nbsp'; echo str_repeat('&nbsp;',250).'<form method="post" action="perfevalentry.php?w=SetIncompleteHR" style="display:inline;color:red;">Set As Incomplete? &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$endofforminc.''; } }
	}
}
?>
