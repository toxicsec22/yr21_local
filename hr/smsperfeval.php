<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
if (!allowedToOpen(683,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false; include_once('../switchboard/contents.php');
include_once '../backendphp/layout/linkstyle.php';

$title="Pending Performance Evaluations";
echo '<title>'.$title.'</title>';
echo '<br><h3>'.$title.'</h3><br>';
$whichqry=isset($_GET['w'])?$_GET['w']:'List';

// $condition=' AND MONTH(EvalDueDate)>='.date('m').'';
$condition='';
$width='100%';

$perfevallink='You%20may%20complete%20your%20evaluation%20on%20any%20gadget.%20Just%20go%20to%20https://www.arwan.biz/eval.';

switch ($whichqry){


case 'List':

echo '<table style="width:100%;background-color:#ebebfa;">';
echo '<tr>';
	echo '<td style="padding:10px;width:25%;" valign="top">';
	$title="Self-Evaluation";
	$sqlm='SELECT FullName,EvalDueDate,MobileNo FROM hr_2perfevalmain pem JOIN attend_30currentpositions cp ON pem.IDNo=cp.IDNo JOIN 1_gamit.0idinfo id ON pem.IDNo=id.IDNo WHERE SelfCompleted=0 AND LENGTH(MobileNo)=11 '.$condition.'';
	$stmtno=$link->query($sqlm); $resno=$stmtno->fetchAll(); $numbers='';
	foreach ($resno as $rno){
		$numbers.=$rno['MobileNo'].';';
	}
	$numbers=substr($numbers, 0, -1);
	$formdesc='</i><br><b><a href="sms:'.$numbers.'?body=Good%20day!%20It%20is%20time%20for%20your%20performance%20evaluation.'.$perfevallink.'%0D%0A%0D%0A-%20HR%20Team">Send SMS</a></b><i>';
	$sql=$sqlm;
	$columnnames=array('FullName','MobileNo','EvalDueDate');
	include('../backendphp/layout/displayastablenosort.php');
	echo '</td>';
	
	echo '<td style="padding:10px;width:25%;" valign="top">';
		$title="Supervisor Evaluation";
		$sqlm='SELECT cp.FullName AS Supervisor,MobileNo FROM hr_2perfevalmain pem JOIN attend_30currentpositions cp ON pem.SupervisorIDNo=cp.IDNo JOIN 1_gamit.0idinfo id ON pem.SupervisorIDNo=id.IDNo JOIN 1employees cp2 ON pem.IDNo=cp2.IDNo WHERE SelfCompleted=1 AND SuperCompleted=0 AND cp2.Resigned=0 AND LENGTH(MobileNo)=11 '.$condition.' GROUP BY SupervisorIDNo';
		$stmtno=$link->query($sqlm); $resno=$stmtno->fetchAll(); $numbers='';
		foreach ($resno as $rno){
			$numbers.=$rno['MobileNo'].';';
		}
		$numbers=substr($numbers, 0, -1);
		$formdesc='</i><br><b><a href="sms:'.$numbers.'?body=Good%20day!%20Your%20team%20member%20has%20a%20scheduled%20performance%20evaluation.%0D%0A%0D%0A-%20HR%20Team">Send SMS</a></b><i>';
		$sql=$sqlm;
		$columnnames=array('Supervisor','MobileNo');
		include('../backendphp/layout/displayastablenosort.php');
	echo '</td>';
	
	echo '<td style="padding:10px;width:25%;" valign="top">';
		$title="Dept Head Confirmation";
		$sqlm='SELECT cp.FullName AS DeptHead,MobileNo FROM hr_2perfevalmain pem JOIN attend_30currentpositions cp ON pem.DeptHeadIDNo=cp.IDNo JOIN 1_gamit.0idinfo id ON pem.DeptHeadIDNo=id.IDNo JOIN 1employees cp2 ON pem.IDNo=cp2.IDNo WHERE SelfCompleted=1 AND SuperCompleted=1 AND cp2.Resigned=0 AND DeptHeadConfirm=0 AND LENGTH(MobileNo)=11 '.$condition.' GROUP BY DeptHeadIDNo';
		$stmtno=$link->query($sqlm); $resno=$stmtno->fetchAll(); $numbers='';
		foreach ($resno as $rno){
			$numbers.=$rno['MobileNo'].';';
		}
		$numbers=substr($numbers, 0, -1);
		$formdesc='</i><br><b><a href="sms:'.$numbers.'?body=Good%20day!%20Please%20finalize%20the%20performance%20evaluation%20for%20your%20team%20member.%0D%0A%0D%0A-%20HR%20Team">Send SMS</a></b><i>';
		$sql=$sqlm;
		$columnnames=array('DeptHead','MobileNo');
		include('../backendphp/layout/displayastablenosort.php');
	echo '</td>';
	
	echo '<td style="padding:10px;width:25%;" valign="top">';
		$title="Employee Response";
		$sqlm='SELECT FullName,MobileNo,EvalDueDate FROM hr_2perfevalmain pem JOIN attend_30currentpositions cp ON pem.IDNo=cp.IDNo JOIN 1_gamit.0idinfo id ON pem.IDNo=id.IDNo WHERE SelfCompleted=1 AND SuperCompleted=1 AND DeptHeadConfirm=1 AND EmpResponse=0 AND LENGTH(MobileNo)=11 '.$condition.'';
		$stmtno=$link->query($sqlm); $resno=$stmtno->fetchAll(); $numbers='';
		foreach ($resno as $rno){
			$numbers.=$rno['MobileNo'].';';
		}
		$numbers=substr($numbers, 0, -1);
		$formdesc='</i><br><b><a href="sms:'.$numbers.'?body=Good%20day!%20Please%20give%20your%20final%20response%20regarding%20your%20performance%20evaluation.'.$perfevallink.'%0D%0A%0D%0A-%20HR%20Team">Send SMS</a></b><i>';
		$sql=$sqlm;
		$columnnames=array('FullName','MobileNo','EvalDueDate');
		include('../backendphp/layout/displayastablenosort.php');
	echo '</td>';
echo '</tr>';
echo '</table>';

break;




}

      $link=null; $stmt=null;
?>