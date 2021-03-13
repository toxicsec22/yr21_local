<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!isset($_REQUEST['print'])){include_once('../switchboard/contents.php'); $printcondition='';} else {$printcondition=' AND `DeptHeadConfirm`=1';}

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
 

$title= 'Performance Evaluation Form' . ' (Evaluator: ' . comboBoxValue($link,'attend_0positions','PositionID',$_SESSION['&pos'],'Position') . ')';
include_once 'perfevalentrymain.php';

?>
  
<div id="main" >
<?php
$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
$rcolor[1]="FFFFFF";
$num=0;
		$sqlcheck='SELECT COUNT(ps.PSID) AS cnt FROM hr_1positionstatement ps JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID WHERE ToEvaluate = '.$result['CurrentPositionID'].' AND Evaluator='.$_SESSION['&pos'].'';
		$stmtc=$link->query($sqlcheck);
		$rowc = $stmtc->fetch();
		
		// echo $rowc['cnt'];
		
		 $sql3='SELECT COUNT(*) AS cnt FROM hr_2perfevalsub WHERE TxnID = '.$_GET['TxnID'].' AND Evaluator='.$_SESSION['(ak0)'].''; 
		$stmt3=$link->query($sql3);
		$row3 = $stmt3->fetch();
		
		if ($rowc['cnt']==0) // is this needed? -- jye
		{
			if ($row3['cnt']==0)
			{
				$sql='SELECT * FROM hr_1statement s JOIN hr_1positionstatement ps ON s.StatementID=ps.StatementID JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID WHERE ToEvaluate = -1 AND Evaluator = -1 ORDER BY PerfComID,ps.StatementID';
			}
			else {
			$sql='SELECT * FROM hr_1statement s JOIN hr_1positionstatement ps ON s.StatementID=ps.StatementID JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID JOIN hr_2perfevalsub pes ON ps.PSID=pes.PSID WHERE TxnID='.$_GET['TxnID'].' ORDER BY PerfComID,ps.StatementID';
			
			if ($result['SelfCompleted']==1)
			{
				$disabled = 'disabled'; $show='';
			}
			}
		}
		 else
		 {
			 if ($row3['cnt']!=0)
			 {//AND ep.Evaluator='.$_SESSION['&pos'].'
				 
				 $sql='SELECT * FROM hr_1statement s JOIN hr_1positionstatement ps ON s.StatementID=ps.StatementID JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID JOIN hr_2perfevalsub pes ON ps.PSID=pes.PSID WHERE pes.Evaluator='.$_SESSION['(ak0)'].'  AND TxnID='.$_GET['TxnID'].' ORDER BY PerfComID,ps.StatementID';
				// echo $sql; 
			 }
			 else {
				 $sql='SELECT * FROM hr_1statement s JOIN hr_1positionstatement ps ON s.StatementID=ps.StatementID JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID WHERE ToEvaluate = '.$result['CurrentPositionID'].' AND ep.Evaluator='.$_SESSION['&pos'].' ORDER BY PerfComID,ps.StatementID';
				 $newcon = ''; 
			
			}
				$disabled = 'disabled'; $show='';
			
		 }
	
		if ($row3['cnt']>0)
		{
			 echo '<form method=\'POST\' action=\'perfevalentry.php?w=EditScoresOthers\'>';
		}
			else if ($row3['cnt']==0) {
			echo '<form method=\'POST\' action=\'perfevalentry.php?w=InsertScoreOthers\'>'; }
					echo '<table class=\'\'><thead><th>Competency</th><th>Statement</th><th></th><th>Assessment</th></thead>';
					echo '<tbody>';
					$stmt = $link->query($sql);
					
					$previous_heading = false;
					$num=0;
					$superscore=0;
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
							
							echo '<tr bgcolor="'. $rcolor[$num%2].'"><td>'.$Competency.'</td><td>' .($num + 1).'. ' .comboBoxValue($link,'hr_1statement','StatementID',$row['StatementID'],'Statement') . '</td><td>'.$row['Weight'].'%</td><td>';
							
									echo '<input type=\'number\' name=\'score2'.$num.'\' min=\'1\' max=\'5\' value='.$row['SuperScore'].' required '.$show.'/>';
							
							
							echo '</td>
								<input type=\'text\' name=\'psid'.$num.'\' value=\''.$row['StatementID'].'\' hidden/>
								<input type=\'text\' name=\'perfevalid'.$num.'\' value=\''.$_GET['TxnID'].'\' hidden/>
								<input type=\'text\' name=\'evaluator'.$num.'\' value=\''.$_SESSION['(ak0)'].'\' hidden/>
								
								
									</tr>';
							$previous_heading = $row['PerfComID'];
							
							$superscore = $superscore + (($row['Weight']/100) * $row['SuperScore']);
							$num++;
							
						}
						echo $row['SuperScore'];
						echo '<input type=\'text\' name=\'num\' value=\''.$num.'\' hidden/>';
						echo '<input type=\'text\' name=\'txnid\' value=\''.$_GET['TxnID'].'\' hidden/>';
						echo '<input type=\'text\' name=\'perfevalid\' value=\''.$_GET['TxnID'].'\' hidden/>';
					
						$sqlc1='SELECT COUNT(TxnID) AS cnt FROM hr_2perfevalsub WHERE Evaluator='.$_SESSION['(ak0)'].' AND SelfScore=-1';
						
						$stmtc1=$link->query($sqlc1);
						$rowc1 = $stmtc1->fetch();
						
						echo '<tr><td align=\'right\' colspan=\'3\'></td><td><h2>'.$superscore.'</h2></td>';
						if ($rowc1['cnt']==0)
						{
							echo '<tr><td align=\'right\' colspan=\'3\'>'.$endofform.'</td><td></td>';
						}
						echo	'</tr>';
						
						echo '</table>';
						echo '</form><br/>';
						
						if ($rowc1['cnt']==0)
						{
							echo '<form action="perfevalentry.php"><input type="text" name="w" value="SetAsCompletedOthers" hidden><input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'"><input type="text" name="perfevalid" value="'.$_GET['TxnID'].'" hidden><input type="submit" value="Set as Completed"/></form>';
						}
?>

</div>
    <br><br>
    <?php //echo $SelfRemarks; echo $ToImprove;  echo $ToDevelop; echo $SuperRemarks; echo $EmpResponse.'<br><br>'; echo $EmpRemarks; echo $DeptHeadComment;?>
    <hr>
<?php //if (allowedToOpen(6832,'1rtc')) { echo $SetHRStatus; if ($result['HRStatus']==1) { $who=$result['IDNo']==$_SESSION['(ak0)']?'Self':'Super'; echo '<form method="post" action="perfevalentry.php?w=SetIncompleteHR" style="display:inline;color:red;">Set As Incomplete? &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$endofforminc.''; } }
 
?>
