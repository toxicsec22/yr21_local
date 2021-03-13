<?php
// error_reporting(E_ALL);
	// ini_set('display_errors', 1);
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6831,'1rtc')) { echo 'No permission'; exit; }

$which=!isset($_GET['w'])?'Lists':$_GET['w'];

$showbranches=false;
if($which<>'PrintAll'){
	include_once('../switchboard/contents.php');
} else {
	include_once($path.'/acrossyrs/dbinit/userinit.php');
	$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
}

?>


<?php


switch($which){
	case 'Lists':
	if (!allowedToOpen(6831,'1rtc')) { echo 'No permission'; exit; }
	
	
	$title='Print Annual Performance Evaluation';
	echo '<title>'.$title.'</title>';
	echo '<br><br><h3>'.$title.'</h3>';
	$sql='select pem.IDNo,cp.FullName,IF(deptid=10,Branch,dept) AS Branch,Position,HRStatusTS from hr_2perfevalmain pem JOIN attend_30currentpositions cp ON pem.IDNo=cp.IDNo WHERE EvalAfterDays='.$currentyr.' AND HRStatus=1 ORDER BY HRStatusTS DESC,Branch;';
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
		$colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
	
	echo'<form action="perfevalprint.php?w=PrintAll" method="POST"><table style="font-size:9.5pt;width:70%;background-color:white;padding:5px;">
 <tr><th width="50px">All? <input type="checkbox" class="chk_boxes" onclick="toggle(this);" /></th><th>Full Name</th><th>Branch</th><th>Position</th><th>HR TimeStamp</th></tr>';
		foreach($result as $res){
			echo '<tr bgcolor='. $rcolor[$colorcount%2].'><td style="text-align:right;"><input type="checkbox" value="'.$res['IDNo'].'" name="idno[]" /></td><td>'.$res['FullName'].'</td><td>'.$res['Branch'].'</td><td>'.$res['Position'].'</td><td>'.$res['HRStatusTS'].'</td></tr>';
			$colorcount++;
		}
		echo '<tr><td colspan=5 align="center"><input style="background-color:green;color:white;width:200px" type="submit" value="Print All Selected" name="btnPrint" OnClick="return confirm(\'Print All Selected?\');"></td></tr></table></form>';
		
	break;
	
	case 'PrintAll':
	
	if(isset($_POST['idno'])){
		include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
		echo '<div id="hidethis"><button onclick="printEvalForm()" style="background-color:green;color:white;padding:5px;">Print All</button>
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
	echo '<style>
    body {font-family:sans-serif;}
    #scores { font-family: sans-serif; align-content: center; font-size:14pt; font-weight: 400;}
    #values { font-family: sans-serif; align-content: center; font-size:14pt; font-weight: 400; color: blue;}
    #textboxid { height:30px; width:60px; font-size:14pt; text-align: center; }
    #wrap {  width:80%; margin: 0 20 0 20;}
    #left {   float:left;   width:55%; overflow: auto;}
    #right {   float:right;   width:45%; overflow: auto;}
    table,th,td {border:1px solid black;border-collapse:collapse; padding: 5px !important; font-size: small !important; overflow:auto;}
    #legendcell { font-size: 8pt;}
    #main {  width:100%;  margin:0 auto; clear: both;}
    #comments { width:55%; height:auto; font-size: 11pt; color:green; padding: 2px; display: inline;}
    #headings { font-size: 12pt; font-weight: bold; display: inline;}
	
</style>';
	
		echo '<title>Print All</title>';
		$slidno=''; //shortlists idno
		foreach($_POST['idno'] AS $idnos){
			 $slidno.=$idnos.',';
		}
		$slidno=substr($slidno, 0, -1);
		
		$sql='SELECT TxnID FROM hr_2perfevalmain WHERE IDNo IN ('.$slidno.') AND EvalAfterDays='.$currentyr.' ORDER BY HRStatusTS DESC';
		// echo $sql;
		
		$stmt=$link->query($sql); $resall=$stmt->fetchAll();
	
	$evalform=''; $disabled = 'disabled'; $show = 'disabled'; $required = '';
	foreach($resall as $res){
		$txnid=$res['TxnID'];
	
	$sql='SELECT pf.*, e.Nickname, e.FirstName, LEFT(e.MiddleName,1) AS MI, e.SurName, DATE_FORMAT(EvalDueDate,\'%Y %M %d\') AS EvalDue, '
			. ' DATE_FORMAT(e.DateHired,\'%Y %M %d\') AS DateHired, Position, Branch, Department, '
			. ' CONCAT(e1.Nickname, " ",e1.SurName) AS EvaluatedBy, FORMAT((TO_DAYS(EvalDueDate) - TO_DAYS(`e`.`DateHired`)) / 365,2) AS `InYears(as of EvalDueDate)`,'
			. ' IF(EvalAfterDays=YEAR(EvalDueDate),"Annual Evaluation",CONCAT("Evaluation after ",FORMAT(EvalAfterDays/30,0)," months")) AS Reason '
			. ' FROM `hr_2perfevalmain` pf '
			. ' JOIN `1employees` e ON pf.IDNo=e.IDNo '
			. ' JOIN attend_0positions p ON p.PositionID=pf.CurrentPositionID JOIN `1branches` b ON b.BranchNo=pf.CurrentBranchNo '
			. ' JOIN `1departments` d ON d.deptid=p.deptid LEFT JOIN `1employees` e1 ON pf.SupervisorIDNo=e1.IDNo WHERE pf.TxnID='.$txnid;
	$stmt=$link->query($sql); $result=$stmt->fetch(); 

	$sql0='SELECT DATE_FORMAT(MAX(SuperCompletedTS),\'%Y %M %d\') AS LastEval FROM `hr_2perfevalmain` WHERE IDNo='.$result['IDNo'];  $stmt0=$link->query($sql0); $result0=$stmt0->fetch();
	$startofform=''; $endofform=''; 


echo '<b><center>Performance Evaluation</center></b><br>';
echo '<table bgcolor="white">
    <tr><td>Employee Number<br><h4>'.$result['IDNo'].'</h4></td><td>Name<br><h4>'.$result['FirstName'].' '.$result['MI'].'. '.$result['SurName'].'</h4></td>
        <td>Position<br><h4>'.$result['Position'].'</h4></td><td>Department/Branch<br><h4>'.$result['Department'].'/'.$result['Branch'].'</h4></td>
        <td>Evaluated By<br><h4>'.$result['EvaluatedBy'].'</h4></td>
    <td><h4>'.$result['Reason'].'</h4></td>
    <td>Due Date of Evaluation<br><h4><font color="red">'.$result['EvalDue'].'</font></h4></td>
        <td>Date of Last Evaluation<br><h4>'.$result0['LastEval'].'</h4></td>
        <td>Date Hired<br><h4>'.$result['DateHired'].'</h4></td><td>How Long With Us <br>(Years, as of Eval Due Date)?<br><h4>'.$result['InYears(as of EvalDueDate)'].'</h4></td>
    </tr>
</table>';

$SetHRStatus='';
$ToImprove='<br><br><h4>Improvement Plan:</h4> '.$result['ToImprove'];
$ToDevelop='<br><h4>Development Plan:</h4> '.$result['ToDevelop'];
 $SelfRemarks='<div id="headings">Remarks on self-evaluation:</div>&nbsp; &nbsp;<div id="comments">'.$result['SelfRemarks'].'</div><br><br>';
    $SuperRemarks='<div id="main"><br><br>  <div id="headings">Supervisor\'s comments:</div>&nbsp; &nbsp;<div id="comments">'.$result['SuperRemarks'].'</div></div><br><br>';
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
?>
<div id="main" >
   
<?php
$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
$rcolor[1]="FFFFFF";
$num=0;
	
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
		 $sql3='SELECT COUNT(*) AS cnt FROM hr_2perfevalsub WHERE TxnID = '.$txnid.''; 
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
			$sql='SELECT * FROM hr_1statement s JOIN hr_1positionstatement ps ON s.StatementID=ps.StatementID JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID JOIN hr_2perfevalsub pes ON ps.PSID=pes.PSID WHERE TxnID='.$txnid.'  AND (SelfScore>=0 OR ISNULL(SelfScore)) ORDER BY PerfComID, ps.StatementID'; }
                        if ($result['SelfCompleted']==1) { $disabled = 'disabled'; }
                        if ($result['IDNo']==$_SESSION['(ak0)']) { $show='disabled';}
		}
		 else //if there's an evaluatee statement
		 {
			 if ($row3['cnt']!=0) //if theres an entry in 2 perfevalsub
			 {
				 
				 $sql='SELECT * FROM hr_1statement s JOIN hr_1positionstatement ps ON s.StatementID=ps.StatementID JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID JOIN hr_2perfevalsub pes ON ps.PSID=pes.PSID WHERE ToEvaluate = '.$result['CurrentPositionID'].' AND ep.Evaluator=(SELECT PositionID FROM attend_30currentpositions WHERE IDNo='.$result['SupervisorIDNo'].') AND TxnID='.$txnid.' AND (SelfScore>=0 OR SelfScore IS NULL) ORDER BY PerfComID, ps.StatementID';
				 // echo $sql;
			 }
			 else { //No entries
				
					
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
		
		echo '<br>';
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
								<input type=\'text\' name=\'perfevalid'.$num.'\' value=\''.$txnid.'\' hidden/>
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
						echo '<input type=\'text\' name=\'txnid\' value=\''.$txnid.'\' hidden/>';
						echo '<input type=\'text\' name=\'perfevalid\' value=\''.$txnid.'\' hidden/>';
						
						$cs=5;
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
						
echo $ToImprove;  echo $ToDevelop; echo $SuperRemarks; echo $DeptHeadComment;
echo $EmpResponse;
                echo '<br><br>'; echo $EmpRemarks;
						
?>

</div>


<?php	

		echo '<p style="page-break-before: always"></p>';
	}
		
	}
	
	break;
	
}
?>
<script>
	function toggle(source) {
		var checkboxes = document.querySelectorAll('input[type="checkbox"]');
		for (var i = 0; i < checkboxes.length; i++) {
			if (checkboxes[i] != source)
				checkboxes[i].checked = source.checked;
		}
	}
</script>