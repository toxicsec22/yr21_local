<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(6840,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');



 
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once('../backendphp/layout/linkstyle.php');
?>
<br><div id="section" style="display: block;">

    <div><a id='link' href="perfevalsettings.php">Competency List</a>
        <a id='link' href="perfevalsettings.php?w=Statement">Statement List</a>
		<a id='link' href="perfevalsettingsmain.php?w=PercentPerEvaluator">Percent Per Evaluator</a>
        <a id='link' href="perfevalsettings.php?w=StatementAssign">Assign Statements to Positions</a>
        <a id='link' href="perfevalsettings.php?w=StatementAssignMultiple">Assign Multiple Statements</a>
	<a id='link' href="perfevalsettings.php?w=StatementSummary&PSID=1">Evaluation List per Position</a>
	<a id='link' href="perfevalsettings.php?w=StatementSumEvaluator&PSID=1">Evaluation List per Evaluator</a>
        <a id='link' href="perfevalsettings.php?w=Missing">Missing Evaluations for Existing Positions</a>
    </div><br/><br/>
