<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!isset($_REQUEST['print'])) {
    include_once('../switchboard/contents.php');
    $printcondition = '';
} else {
    $printcondition = ' AND `DeptHeadConfirm`=1';
}

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

$title = 'Performance Evaluation Form' . ' (Evaluator: ' . comboBoxValue($link, 'attend_0positions', 'PositionID', $_GET['Evaluator'], 'Position') . ')';
include_once 'perfevalentrymain.php';

// check if there is special evaluator
    $sql0='SELECT * FROM hr_2perfevalsub WHERE TxnID='.$txnid.' AND EvaluatorIDNo='.$_SESSION['(ak0)'];
    $stmt0=$link->query($sql0); $row0=$stmt0->fetchAll();
    if($stmt0->rowCount()>0) { $specialeval=true;} else { $specialeval=false;}
// echo $specialeval;
// echo $sql0;

if ((!allowedToOpen(683, '1rtc')) AND ( $result['IDNo'] <> $_SESSION['(ak0)']) AND ( $result['SupervisorIDNo'] <> $_SESSION['(ak0)']) AND ( $result['DeptHeadIDNo'] <> $_SESSION['(ak0)']) AND ( $_GET['Evaluator'] <> $_SESSION['&pos']) AND ($specialeval==false)) {  
    exit();
}

if ($result['EmpResponse'] <> 0 AND ( !allowedToOpen(683, '1rtc')) AND ( $result['IDNo'] <> $_SESSION['(ak0)']) AND ( $result['SupervisorIDNo'] <> $_SESSION['(ak0)']) AND ( $result['DeptHeadIDNo'] <> $_SESSION['(ak0)'])) {
    exit();
}

if (($result['EmpResponse'] == 0) AND ( $_GET['Evaluator'] == $_SESSION['&pos'])) {
    $condition = ' AND IF(Multi=0,(EvaluatorIDNo IS NULL OR EvaluatorIDNo=' . $_SESSION['(ak0)'] . '),(EvaluatorIDNo=' . $_SESSION['(ak0)'] . '))'; 
} elseif($stmt0->rowCount()>0) {
    $condition = ' AND EvaluatorIDNo=' . $_SESSION['(ak0)'];
} else {
    $condition = '';
}


$sqlc1 = 'SELECT COUNT(TxnID) AS cnt FROM hr_2perfevalsub WHERE EvaluatorIDNo=' . $_SESSION['(ak0)'] . ' AND SelfScore=-1 AND TxnID=' . $txnid;
$stmtc1 = $link->query($sqlc1);
$rowc1 = $stmtc1->fetch();
if ($rowc1['cnt'] == 0) {
    $editable = true;
} else {
    $editable = false;
}
if ((($_GET['Evaluator'] == $_SESSION['&pos']) OR $specialeval) AND $editable AND $result['EmpResponse'] == 0) {
	if ($result['SupervisorIDNo']!=$_SESSION['(ak0)'] AND $result['DeptHeadIDNo']!=$_SESSION['(ak0)']){
		$disabled = '';
		$startofform = '<form method=\'POST\' action=\'perfevalentry.php?w=EditScoresOthers\'>';
		
		$endofform = '<strong>Step 1:</strong> <input type="hidden" name="action_token" value="' . html_escape($_SESSION['action_token']) . '">'
				. '<input type="submit" value="Submit Scores" ></form>';
		$endofformcomplete = '<form action="perfevalentry.php"><input type="text" name="w" value="SetAsCompletedOthers" hidden><input type="hidden" name="action_token" value="' . html_escape($_SESSION['action_token']) . '"><input type=\'text\' name=\'who\' value=\'' . ($result['IDNo'] == $_SESSION['(ak0)'] ? 'Self' : 'Super') . '\' hidden/><input type=\'text\' name=\'TxnID\' value=\'' . $_GET['TxnID'] . '\' hidden/><strong>Step 2:</strong> <input type="submit" value="Set as Completed (Post)"/></form>';
	} else {$disabled='disabled';$endofformcomplete='';}
} elseif ((($_GET['Evaluator'] == $_SESSION['&pos']) OR $specialeval) AND $result['EmpResponse'] == 0) {
    $endofform = str_repeat('&nbsp;', 10) . '<br/><form action="perfevalentry.php"><input type="text" name="w" value="UnsetAsCompletedOthers" hidden><input type="hidden" name="action_token" value="' . html_escape($_SESSION['action_token']) . '"><input type=\'text\' name=\'who\' value=\'' . ($result['IDNo'] == $_SESSION['(ak0)'] ? 'Self' : 'Super') . '\' hidden/>'
            . '<input type=\'text\' name=\'TxnID\' value=\'' . $_GET['TxnID'] . '\' hidden/><strong>UNPOST?</strong><br/><input type="submit" value="Set as Incomplete"/></form>'; $endofformcomplete='';
    $disabled = 'disabled';
} else {
    $disabled = 'disabled'; $endofform=''; $endofformcomplete='';
	// echo 'test';
}
// echo $endofform;
?>

<div id="main" >

    <?php
    $rcolor[0] = (!isset($_REQUEST['print']) ? (isset($alternatecolor) ? $alternatecolor : "FFFFCC") : "FFFFFF");
    $rcolor[1] = "FFFFFF";
    $num = 0;
    //Check if there's an evaluatee statement 
    $sql0 = 'CREATE TEMPORARY TABLE othereval AS '
            . 'SELECT pes.*,s.Statement,s.PerfComID,ps.StatementID, ps.Weight, CONCAT(e.Nickname, " ", e.Surname) AS EvaluatedBy  FROM hr_2perfevalsub pes JOIN hr_2perfevalmain pf ON pf.TxnID=pes.TxnID JOIN hr_1positionstatement ps ON ps.PSID=pes.PSID JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID JOIN hr_1statement s ON s.StatementID=ps.StatementID '
            . ' LEFT JOIN `1employees` e ON e.IDNo=pes.EvaluatorIDNo '
            . ' WHERE Evaluator=' . $_GET['Evaluator'] . $condition . ' AND pf.TxnID=' . $_GET['TxnID'] . ' ORDER BY PerfComID,ps.StatementID';
     
    $stmt0 = $link->prepare($sql0);
    $stmt0->execute();

    $sql = 'SELECT * FROM othereval';

    echo $startofform . '<table class=\'\'>';

    echo '<thead><th>Competency</th><th>Statement</th><th>Weight</th><th>Assessment<BR>(1 to 5)</th><th>Evaluated By</th></thead>';
    echo '<tbody>';
    $stmt = $link->query($sql);

    $previous_heading = false;
    $num = 0;
    $selfscore = 0;
    $superscore = 0;
    $otherweight = 0;
    echo comboBox($link, 'SELECT 5 AS OptionValue, "5 - Exceeds" AS Description UNION SELECT 4, "4 - Meets" UNION SELECT 3, "3 - Mediocre"
 UNION SELECT 2, "2 - Below" UNION SELECT 1, "1 - Fail"', 'OptionValue', 'Description', 'scale');
    while ($row = $stmt->fetch()) {


        if ($previous_heading != $row['PerfComID']) {
            $Competency = comboBoxValue($link, 'hr_1competency', 'PerfComID', $row['PerfComID'], 'Competency');
        } else {
            $Competency = '';
        }

        echo '<tr bgcolor="' . $rcolor[$num % 2] . '"><td>' . $Competency . '</td><td>' . ($num + 1) . '. '
        . (comboBoxValue($link, 'hr_1statement', 'StatementID', $row['StatementID'], 'Statement')) . '</td><td>' . $row['Weight'] . '%</td>'
        . '<td>';



        $sqlgrade = 'SELECT * FROM hr_1perfscale ORDER BY GradeID DESC';
        $stmtgrade = $link->query($sqlgrade);
        echo '<select name="score' . $num . '" ' . $disabled . '>';
        echo '<option value="NULL"></option>';
        while ($rowgrade = $stmtgrade->fetch()) {
            if ($row['SuperScore'] == $rowgrade['GradeID']) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            echo '<option value="' . $rowgrade['GradeID'] . '" ' . $selected . '>' . $rowgrade['GradeDesc'] . '</option>';
        }
        echo '</select>';


        echo '</td><td>' . $row['EvaluatedBy'] . '</td><input type=\'number\' name=\'weight' . $num . '\' value="' . $row['Weight'] . '" hidden/>
<input type=\'text\' name=\'sgid' . $num . '\' value=\'' . $row['TxnSubId'] . '\' hidden/>                                                                    
</td></tr>';
        $previous_heading = $row['PerfComID'];
        $num++;

        $superscore = $superscore + (($row['Weight'] / 100) * $row['SuperScore']);
        $otherweight = $otherweight + $row['Weight'];
    }

	$sqlscssothers = 'SELECT TxnID FROM hr_2perfevalsub WHERE SuperScore IS NULL AND TxnID='.$_GET['TxnID'].' AND EvaluatorIDNo='.$_SESSION['(ak0)'].'';
	$stmtscssothers = $link->query($sqlscssothers);
	$cntscssothers= $rowscssothers = $stmtscssothers->rowCount();
	// echo $cntscssothers;
    $sql1 = 'SELECT COUNT(DISTINCT EvaluatorIDNo) AS NoofEvaluators FROM othereval';
    $stmt1 = $link->query($sql1);
    $res1 = $stmt1->fetch();
    echo '<tr><td align=\'right\' colspan=\'3\'>' . (($otherweight <> '100%') ? '<H3>INCORRECT TOTAL: ' . ($otherweight / $res1['NoofEvaluators']) . '</H3>' : ($otherweight / $res1['NoofEvaluators'])) . '%</td>'
    . '<td><h2>' . ($superscore / $res1['NoofEvaluators']) . '</h2></td><td>'.$endofform.'</td>'
    // . '<tr><td align="right" colspan="5">'.$endofformcomplete.'</td></tr>';
    . '<tr><td align="right" colspan="5">'.($cntscssothers == 0 ? $endofformcomplete: str_repeat('&nbsp;',10).'<b>Step 2: <font color="red">Cannot post. Please check your scores.</b></font>').'</td></tr>';
    echo '<input type=\'text\' name=\'num\' value=\'' . $num . '\' hidden/>';
    echo '<input type=\'text\' name=\'TxnID\' value=\'' . $_GET['TxnID'] . '\' hidden/>';
    echo '<input type=\'text\' name=\'who\' value=\'' . ($result['IDNo'] == $_SESSION['(ak0)'] ? 'Self' : 'Super') . '\' hidden/>';
    echo '</tr>';
    echo '</table>';
	
    
    if ($_SESSION['(ak0)'] == 1002) {
        echo 'No. of evaluators: ' . $res1['NoofEvaluators'] . '<br/>'
        . 'Other weight total: ' . ($otherweight) . '<br/>'
        . 'Score total: ' . ($superscore) . '<br/>';
    }
    ?>

</div>

<?php
$link = null;
$link = null;
?>
