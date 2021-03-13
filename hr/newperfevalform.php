<?php
$path=$_SERVER['DOCUMENT_ROOT']; 
if(session_id()==''){
	session_start();
}
// if(!isset($_SESSION['oss'])){
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;
include_once('../switchboard/contents.php'); $printcondition='';

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
  

$title='Performance Evaluation Form';
?>

<title><?php echo $title;?></title>
<center><h3><?php echo $title;?></h3></center><br><br>
<style>
    body {font-family:sans-serif;}
    
    table,th,td {border:1px solid black;border-collapse:collapse; padding: 5px !important; font-size: small !important; overflow:auto;}
    #legendcell { font-size: 8pt;}
    #main {  width:100%;  margin:0 auto; clear: both;}
   
	
</style>
<?php
$txnid=intval($_REQUEST['TxnID']);  
$sql='SELECT pf.*, e.Nickname, e.FirstName, LEFT(e.MiddleName,1) AS MI, e.SurName, DATE_FORMAT(EvalDueDate,\'%Y %M %d\') AS EvalDue, '
        . ' DATE_FORMAT(e.DateHired,\'%Y %M %d\') AS DateHired, Position, Branch, Department, '
        . ' CONCAT(e1.Nickname, " ",e1.SurName) AS EvaluatedBy, FORMAT((TO_DAYS(EvalDueDate) - TO_DAYS(`e`.`DateHired`)) / 365,2) AS `InYears(as of EvalDueDate)`,'
        . ' IF(EvalAfterDays=YEAR(EvalDueDate),"Annual Evaluation",CONCAT("Evaluation after ",FORMAT(EvalAfterDays/30,0)," months")) AS Reason '
        . ' FROM `hr_82perfevalmain` pf '
        . ' JOIN `1employees` e ON pf.IDNo=e.IDNo '
        . ' JOIN attend_0positions p ON p.PositionID=pf.CurrentPositionID JOIN `1branches` b ON b.BranchNo=pf.CurrentBranchNo '
        . ' JOIN `1departments` d ON d.deptid=p.deptid LEFT JOIN `1employees` e1 ON pf.SIDNo=e1.IDNo WHERE pf.TxnID='.$txnid.$printcondition;
$stmt=$link->query($sql); $result=$stmt->fetch(); 


$estat=$result['EStat'];
$sstat=$result['SStat'];
$dstat=$result['DStat'];
$ack=$result['Ack'];
$ecomment=$result['EComment'];
$scomment=$result['SComment'];
$dcomment=$result['DComment'];
$empremarks=$result['EmpRemarks'];

$eidno=$result['IDNo'];
$sidno=$result['SIDNo'];
$didno=$result['DIDNo'];
$evalduedate=$result['EvalDueDate'];





$recommendation=$result['Recommendation'];

if (!in_array($_SESSION['(ak0)'],array($eidno,$sidno,$didno)) AND (!allowedToOpen(686,'1rtc'))){
	echo 'Not Allowed To View';
	exit();
}



$sql0='SELECT DATE_FORMAT(MAX(SCommentTS),\'%Y %M %d\') AS LastEval FROM `hr_82perfevalmain` WHERE IDNo='.$result['IDNo'];  $stmt0=$link->query($sql0); $result0=$stmt0->fetch();

?>

 <!--<table style="font-size:9pt;border-collapse:collapse;border:1px solid;padding:15px;">
	<tr><th style="width:180px">RATING</th><th style="width:100px">SCORE</th><th colspan=2>Definition of Competency</th><th style="width:100px">% Rate</th></tr>
	<tr>
		<td>5 - EXCELLENT</td>
		<td>4.01 – 5.00</td><td>Exceeds many of the responsibilities of the position and the goals established for the year, while successfully achieving all others.</td><td>Submit 1-2 days  prior with 0 errors.</td><td>94-100%</td>
		</tr>
	<tr>
	<td>4 - VERY GOOD</td>
		<td>3.01 – 4.00</td>
		<td>Consistently meets position requirements, objectives and expectations. Employee performs good, solid, commendable work. Also, recommended for employees who have assumed new or additional responsibilities, and have demonstrated development, learning and progress towards performance expectations.</td>
		<td>Submit report on time with 1-2 errors.</td><td>87-93%</td>
	</tr>
	<tr>
	<td>3 - GOOD</td>
		<td>2.50-3.00</td>
		<td>Meets some, but not all of the responsibilities of the position and established goals for the year. Consistent monitoring and follow-through is needed.</td>
		<td>Submit report on time with 3-4 errors.</td>
		<td>80-86%</td>
	</tr>
	<tr>
	<td>2 - POOR</td>
		<td>2.00 - 2. 49</td>
		<td>Performance is far from what is expected and wasn’t able to fulfill most of the responsibilities of the position. The manager will develop a Performance Improvement Plan within 30 days of completing the review.</td>
		<td>Report is delayed in 1-2 days with 5 or more errors.</td>
		<td>76-79%</td>
	</tr>
	<tr>
	<td>1 - NEEDS IMPROVEMENT</td>
		<td>0 - 1.99</td>
		<td>Performance is far from what is expected and wasn’t able to fulfill most of the responsibilities of the position. The manager will develop a Performance Improvement Plan within 30 days of completing the review.</td>
		<td>No report submitted.</td>
		<td>< 75%</td>
	</tr>
 </table>
 <br>-->

 <div id="hidethis">
        <br><br>
<table bgcolor="white">
    <tr><td>Self-Evaluation:<br><h4><?php echo ($result['EStat']==1?'<font color="green">Completed</font>':'<font color="red">Unfinished</font>');?></h4></td>
        <td>Evaluator: <br><h4><?php echo ($result['SStat']==1?'<font color="green">Completed</font>':'<font color="red">Unfinished</font>');?></h4></td>
        <td>Dept Head: <br><h4><?php echo ($result['DStat']==1?'<font color="green">Completed</font>':'<font color="red">Unfinished</font>');?></h4></td>
        <td>Employee Response: <br><h4><?php echo ($result['Ack']==1?'Agree':($result['Ack']==-1?'Disagree':'<font color="red">Unfinished</font>'));?></h4></td>
        <td>HR Status<br><h4><?php echo ($result['HRStatus']==1?'<font color="green">Filed</font>':'<font color="red">Unfinished</font>');?></h4></td>
    </tr>
</table>
    </div></div><br/>
	</div>

	
<table bgcolor="white">

<?php if((allowedToOpen(array(100,685,686),'1rtc'))){ ?>
    <tr><td>Employee Number<br><h4><?php echo $result['IDNo'];?></h4></td><td>Name<br><h4><?php echo $result['FirstName'].' '.$result['MI'].'. '.$result['SurName'];?></h4></td>

        <td>Position<br><h4><?php echo $result['Position'];?></h4></td><td>Department/Branch<br><h4><?php echo $result['Department'].'/'.$result['Branch'];?></h4></td>
        <td>Evaluated By<br><h4><?php echo $result['EvaluatedBy'];?></h4></td>
	<?php } ?>

    <td><h4><?php echo $result['Reason'];?></h4></td>
    <td>Due Date of Evaluation<br><h4><font color="red"><?php echo $result['EvalDue'];?></font></h4></td>
	<?php if((allowedToOpen(array(100,685,686),'1rtc'))){ ?>
        <td>Date of Last Evaluation<br><h4><?php echo $result0['LastEval'];?></h4></td>
        <td>Date Hired<br><h4><?php echo $result['DateHired'];?></h4></td><td>How Long With Us <br>(Years, as of Eval Due Date)?<br><h4><?php echo $result['InYears(as of EvalDueDate)'];?></h4></td>
		<?php } ?>
    </tr>
</table>




<div id="main" >


<?php	




$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
$rcolor[1]="FFFFFF";
$colorcount=0;


//startfc
echo '<br><br><b>Functional Competencies</b>';
echo '<br><table>';
echo '<tr><th>Statement</th><th>Weight</th>';
$selfth='<th>Super-Score</th></tr>';

echo $selfth;
$sqlcore='SELECT pems.TxnSubId,pemm.Posted,pems.FCID,SuperScore,Statement,`Weight` FROM hr_82perfevalmonthlymain pemm JOIN hr_82perfevalmonthlysub pems ON pemm.TxnID=pems.TxnID JOIN hr_82fcsub fv ON pems.FCID=fv.FCID WHERE pemm.IDNo = '.$eidno.' AND MonthNo='.date('m',strtotime($evalduedate)).' ORDER BY OrderBy'; 
	
	$stmtcore=$link->query($sqlcore);
	$rowcore = $stmtcore->fetchALL();
	
	
	$totalweight=0; $superscore=0;
	foreach($rowcore AS $rowco){
		echo '<tr bgcolor="'. $rcolor[$colorcount%2].'"><td>'.$rowco['Statement'].'</td><td>'.$rowco['Weight'].'%</td>';
		
		
		echo '<td>'.$rowco['SuperScore'].'</td>';
	
		
		echo '</tr>';
		$colorcount++;
		$totalweight=$totalweight+$rowco['Weight'];
		$superscore = $superscore + (($rowco['Weight']/100) * $rowco['SuperScore']);
	}

	echo '</table>';
//end fc




$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
$rcolor[1]="FFFFFF";
$colorcount=0;

// echo '<h3><a href="newperfevalformmonthly.php?TxnID='.$_GET['TxnID'].'">Monthly Evaluation</a></h3>';
echo '<br><b>Core Competencies</b>';

if($estat==0 AND $sstat==0){
	$actionprocess='SelfScore';
	$showselfaction=1;
	$showsuperfaction=0;
} else if($estat==1 AND $sstat==0){
	$actionprocess='SuperScore';
	$showselfaction=0;
	$showsuperfaction=1;
} else if($estat==1 AND $sstat==1){
	$actionprocess='DComment';
	$showselfaction=1;
	$showsuperfaction=1;
}

echo '<table style="width:80%;">';

echo '<th>Competency</th><th>Interpretation</th><th>Weight</th>';
$selfth='<th>Self-Score</th>';
$superth='<th>Supervisor-Score</th>';

if($showselfaction==1 AND $showsuperfaction==0 AND $estat==0 AND $sstat==0 AND $_SESSION['(ak0)']==$eidno){
	echo $selfth;
}

if($estat==1 AND $sstat==0 AND $_SESSION['(ak0)']==$eidno){
	echo $selfth;
}

if($showselfaction==0 AND $showsuperfaction==1 AND $estat==1 AND $sstat==0 AND $_SESSION['(ak0)']==$sidno){
	echo $superth;
}
if($showselfaction==1 AND $showsuperfaction==1 AND $estat==1 AND $sstat==1){
	echo $selfth.$superth;
}



$sqlcore='SELECT pes.*,Competency,Interpretation,`Weight` FROM hr_82perfevalsub pes JOIN hr_81corecompetencies cv ON pes.CID=cv.CID WHERE pes.TxnID = '.$_GET['TxnID'].' ORDER BY OrderBy'; 
$stmtcore=$link->query($sqlcore);
$rowcore = $stmtcore->fetchALL();

$totalweight=0; $selfscore=0; $superscore=0;
echo '<form action="newperfevalprocess.php?w='.$actionprocess.'" method="POST" autocomplete=off>';
foreach($rowcore AS $rowco){
	echo '<tr bgcolor="'. $rcolor[$colorcount%2].'"><td>'.$rowco['Competency'].'</td><td>'.$rowco['Interpretation'].'</td><td>'.$rowco['Weight'].'%</td>';
	if($showselfaction==1 AND $showsuperfaction==0 AND $estat==0 AND $sstat==0 AND $_SESSION['(ak0)']==$eidno){

		echo '<input type="hidden" name="TxnSubId'.$colorcount.'" value="'.$rowco['TxnSubId'].'" />';
		echo '<td '.($rowco['SelfScore']==''?'style="background-color:red;"':'').'><input type="number" name="SelfScore'.$colorcount.'" size="5" value="'.$rowco['SelfScore'].'" min=0 max=5 step=".1" style="width:70px;"></td>';
	}
	if($estat==1 AND $sstat==0 AND $_SESSION['(ak0)']==$eidno){
		echo '<td>'.$rowco['SelfScore'].'</td>';
	}
	if($showselfaction==0 AND $showsuperfaction==1 AND $estat==1 AND $sstat==0 AND $_SESSION['(ak0)']==$sidno){
		echo '<input type="hidden" name="TxnSubId'.$colorcount.'" value="'.$rowco['TxnSubId'].'" />';
		echo '<td '.($rowco['SuperScore']==''?'style="background-color:red;"':'').'><input type="number" name="SuperScore'.$colorcount.'" size="5" value="'.$rowco['SuperScore'].'" min=0 max=5 step=".1" style="width:70px;"></td>';
	}
	
	if(($showselfaction==1 AND $showsuperfaction==1 AND $estat==1 AND $sstat==1)){
		echo '<td>'.$rowco['SelfScore'].'</td>';
		echo '<td>'.$rowco['SuperScore'].'</td>';
	}
	echo '</tr>';
	$colorcount++;
	$totalweight=$totalweight+$rowco['Weight'];
	$selfscore = $selfscore + (($rowco['Weight']/100) * $rowco['SelfScore']);
	$superscore = $superscore + (($rowco['Weight']/100) * $rowco['SuperScore']);
}
if($showselfaction==1 AND $showsuperfaction==0 AND $estat==0 AND $sstat==0 AND $_SESSION['(ak0)']==$eidno){
echo '<input type="hidden" name="selfnum" value="'.($colorcount).'">';

echo '<tr><td colspan=3 align="right"><b>Total: '.$totalweight.'%</b></td><td colspan=2 align="right"><b>Self-Score: '.$selfscore.'</b></td></tr><td colspan="5" align="right"><b>STEP 1: </b><input type="submit" name="btnEnter" value="Enter" style="background-color:blue;color:white;padding:3px;width:100px"></td></tr><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">';
}


if($showselfaction==0 AND $showsuperfaction==1 AND $estat==1 AND $sstat==0 AND $_SESSION['(ak0)']==$sidno){
	echo '<input type="hidden" name="supernum" value="'.($colorcount).'">';

	echo '<tr><td colspan=3 align="right"><b>Total: '.$totalweight.'%</b></td><td colspan=2 align="right"><b>Super-Score: '.$superscore.'</b></td></tr><td colspan="5" align="right"><b>STEP 1: </b><input type="submit" name="btnEnter" value="Enter" style="background-color:blue;color:white;padding:3px;width:100px"></td></tr><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">';
}


echo '</form>';
echo '</table>';

echo '<br><br>';

$sqlcntscore='SELECT SUM(IF(SelfScore IS NULL,1,0)) AS MissingSelfScore,SUM(IF(SuperScore IS NULL,1,0)) AS MissingSuperScore FROM hr_82perfevalsub WHERE TxnID='.$txnid;
$stmtcntscore=$link->query($sqlcntscore); $resultscore=$stmtcntscore->fetch();


//Unfinished Self Eval
if($estat==0 AND $sstat==0 AND $dstat==0 AND $_SESSION['(ak0)']==$eidno){

	if($resultscore['MissingSelfScore']==0){
		echo '<form action="newperfevalprocess.php?w=SelfOverAllComment&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'" method="POST"><b>Employee Overall Comments</b><br><textarea cols=80 rows="5" name="EComment">'.$ecomment.'</textarea><br><b>Step 2: </b><input type="submit" name="btnSubmit" value="Submit Comment"></form>';
		echo '<form action="newperfevalprocess.php?w=SelfComplete&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'" method="POST"><br><b>Step 3: </b><input type="submit" name="btnSubmit" value="Set As Complete (POST)" onclick="return confirm(\'Are you sure? This action cannot be undone.\')"></form>';
	} else {
		echo '<font color="red"><b>Incomplete Self-Score</b></font>'; exit();
	}
}
if($estat==1){
	echo 'Employee Overall Comments:<br><b><font color="blue">'.$ecomment.'</font></b><br><br>';
}
//Unfinished Super Eval
if($estat==1 AND $sstat==0 AND $dstat==0 AND $_SESSION['(ak0)']==$sidno){


	$superincbutton='</div>';
	$superincbutton.='<div style="float:right;">';
	$superincbutton.='<form action="newperfevalprocess.php?w=SetIncSuper&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'" method="POST"><input type="submit" value="Set as INCOMPLETE" name="btnSubmit" onclick="return confirm(\'Are you sure to set as incomplete?.\')"></form>';
	$superincbutton.='</div>';
	$superincbutton.='</div>';

echo '<div>';
echo '<div style="float:left;">';
	if($resultscore['MissingSuperScore']==0){
		echo '<form action="newperfevalprocess.php?w=SuperOverAllComment&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'" method="POST"><b>Supervisor Overall Comments</b><br><textarea cols=80 rows="5" name="SComment">'.$scomment.'</textarea><br><br><b>Recommendation</b><br>
		
		<input type="radio" name="Recommendation" value=1 '.($recommendation==1?'checked':'').'> For Continous Employment<br>
		<input type="radio" name="Recommendation" value=2 '.($recommendation==2?'checked':'').'> For Regularization<br>
		<input type="radio" name="Recommendation" value=3 '.($recommendation==3?'checked':'').'> For Performance Improvement Plan (PIP)-(3mos.extension if employee receives below 2.00 rating)<br><br>
		<b>Step 2: </b><input type="submit" name="btnSubmit" value="Submit Comment"></form>';
		echo '<form action="newperfevalprocess.php?w=SuperComplete&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'" method="POST"><br><b>Step 3: </b><input type="submit" name="btnSubmit" value="Set As Complete (POST)" onclick="return confirm(\'Are you sure? This action cannot be undone.\')"></form>';;
		echo $superincbutton;
	} else {
		echo '<font color="red"><b>Incomplete Super-Score</b></font>'; 
		echo $superincbutton;
		echo '<br><br>';
		exit();
	}


}

if($sstat==1){
	echo 'Supervisor Overall Comments:<br><b><font color="maroon">'.$scomment.'</font></b><br><br>';
	echo 'Recommendation:<br><b><font color="green">'.($recommendation==1?'For Continous Employment':($recommendation==2?'For Regularization':'For Performance Improvement Plan (PIP)-(3mos.extension if employee receives below 2.00 rating)')).'</font></b><br><br>';
}

if($estat==1 AND $sstat==1 AND $dstat==0 AND $_SESSION['(ak0)']==$didno){

	$dheadincbutton='</div>';
	$dheadincbutton.='<div style="float:right;">';
	$dheadincbutton.='<form action="newperfevalprocess.php?w=SetIncDHead&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'" method="POST"><input type="submit" value="Set as INCOMPLETE" name="btnSubmit" onclick="return confirm(\'Are you sure to set as incomplete?.\')"></form>';
	$dheadincbutton.='</div>';
	$dheadincbutton.='</div>';

	echo '<div>';
	echo '<div style="float:left;">';
	echo '<form action="newperfevalprocess.php?w=DeptHeadComment&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'" method="POST"><b>Dept Head Comments</><br><textarea cols=80 rows="5" name="DComment"></textarea><br><input type="submit" name="btnSubmit" value="Set As Complete"></form>';
	echo $dheadincbutton;
	echo '<br><br>';
}

if($dstat==1){
	echo 'Dept Head Comments:<br><b><font color="black">'.$dcomment.'</font></b><br><br>';
}

if($estat==1 AND $sstat==1 AND $dstat==1 AND $ack==0 AND $_SESSION['(ak0)']==$eidno){

	echo '<form action="newperfevalprocess.php?w=Acknowledge&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'" method="POST"><b>Acknowledge</b><br>
		<input type="radio" name="Acknowledge" value=1> <font color="green"><b>I AGREE</b></font> and I commit myself to the improvement/development plan. <br>
		<input type="radio" name="Acknowledge" value=-1> <font color="maroon"><b>I DISAGREE</b></font> and I accept full responsibility for any consequence that may arise from my non-agreement.<br><br>
		Employee Remarks:<br><textarea rows="5" cols="90" maxlength="400"  name="EmpRemarks" placeholder="(Text here will replace what has been entered, if any.)" ></textarea><br><br>

		<b>Step 2:</b> <input type="submit" name="btnSubmit" value="Set As Complete"></form>';
}

if($ack<>0){
	echo 'Acknowledgement: '.($ack==1?'<font color="blue"><b>AGREE</b></font>':'<font color="red"><b>DISAGREE</b></font>').'<br><br>';
	echo 'Employee Remarks:<br><b><font color="blue">'.$empremarks.'</font></b><br><br>';
	
}

?>

</div>  


