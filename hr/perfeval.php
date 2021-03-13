<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

$showbranches=false;
include_once('../switchboard/contents.php');
  

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

$columnnameslist=array('IDNo','FullName','DateHired','Company','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','SelfEval','SelfRemarks','SelfCompletedTS','Supervisor','SupervisorEval','SuperRemarks','SuperCompletedTS','DeptHeadComment','DeptHeadConfirmTS','Emp_Response','EmpRemarks','EmpResponseEditedBy','HRRemarks','HREncodedBy','HRTimestamp','HR_Status','HRStatusTS'); //'HowLongWithUsinYrs',,'EncodedBy','TimeStamp' 
$columnstoadd=array('SelfCompleted','Supervisor','SuperCompleted','DeptHeadConfirm','HRRemarks');

$which=(!isset($_GET['w'])?'List':$_GET['w']);

if (in_array($which,array('List','EditSpecifics','History'))){
   echo comboBox($link,'SELECT IDNo, CONCAT(Nickname, " ", Surname) AS FullName FROM `1employees` ORDER BY Nickname;','IDNo','FullName','employees');
   } 
   
if (in_array($which,array('MyEval','ForEval','History'))){   
   $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','SelfEval','SelfRemarks','Supervisor','SupervisorEval','SuperRemarks','DeptHeadComment','Emp_Response','EmpRemarks','HRRemarks','HR_Status');
}
if (in_array($which,array('Add','Edit'))){
   $idno=comboBoxValue($link,'`1employees`','CONCAT(Nickname, " ", Surname)',addslashes($_POST['FullName']),'IDNo');
   }
	
$sql='SELECT pf.*, CONCAT(e1.FirstName, " ", e1.Surname) AS FullName, e1.DateHired, TRUNCATE(((TO_DAYS(NOW()) - TO_DAYS(`e1`.`DateHired`)) / 365),2) AS `HowLongWithUsinYrs`, e.Nickname as HREncodedBy, Position AS CurrentPosition, b.Branch AS CurrentBranch, c.Company, CONCAT(e2.Nickname, " ", e2.Surname) AS Supervisor,
    SelfEval, SupervisorEval, IF(HRStatus=1,"Filed","Pending") AS HR_Status, IF(EmpResponse=0,"",IF(EmpResponse=1,"Agree","Disagree")) AS Emp_Response, IF((EmpResponseEncByIDNo<>pf.IDNo),e3.Nickname,"") AS `EmpResponseEditedBy`
	       FROM hr_2perfevalmain pf   
	       LEFT JOIN `1employees` e ON e.IDNo=pf.HREncodedByNo
	       JOIN `1employees` e1 ON e1.IDNo=pf.IDNo
	       LEFT JOIN `1employees` e2 ON e2.IDNo=pf.SupervisorIDNo
                 LEFT  JOIN `1employees` e3 ON e3.IDNo=pf.EmpResponseEncByIDNo
	       JOIN `1branches` b ON b.BranchNo=pf.CurrentBranchNo
	       JOIN `1companies` c ON c.CompanyNo=e1.RCompanyNo
	       LEFT JOIN `attend_0positions` p ON p.PositionID=pf.CurrentPositionID
	       '; 

switch ($which){
   /* case 'AutoEncodeEvalDue': 
        if (!allowedToOpen(6831,'1rtc')) {   echo 'No permission'; exit;} 
      $month=$_POST['Month'];
      $sql0='SELECT e.`IDNo`, `PositionID`, `DefaultBranchAssignNo`, DATE_ADD(`e`.`DateHired`, INTERVAL ';
      $sql1=' DAY) AS `EvalDueDate`, ';
      $sql2=' AS `EvalAfterDays`, '.$_SESSION['(ak0)'].' AS `EncodedByNo`, Now() AS `Timestamp`
	    FROM `1employees` e JOIN `attend_30currentpositions` p ON e.IDNo=p.IDNo
	    JOIN `attend_1defaultbranchassign` dba ON e.IDNo=dba.IDNo
	    HAVING MONTH(`EvalDueDate`)='.$month.' AND YEAR(`EvalDueDate`)='.$currentyr.''; 
      $sql='INSERT INTO `hr_2perfevalmain` (`IDNo`,`CurrentPositionID`,`CurrentBranchNo`,`EvalDueDate`, `EvalAfterDays`,`HREncodedByNo`,`HRTimestamp`) '.
	    $sql0.'90'.$sql1.'90'.$sql2.' UNION  '.$sql0.'150'.$sql1.'150'.$sql2.' UNION  '.$sql0.'365'.$sql1.'365'.$sql2;
		// echo $sql; break;
      $stmt=$link->prepare($sql); $stmt->execute();
      
	  
      $sql='UPDATE hr_2perfevalmain pf JOIN attend_30currentpositions cp ON pf.IDNo=cp.IDNo SET 
SupervisorIDNo=(IF((cp.deptid<>10),(SELECT cp3.LatestSupervisorIDNo FROM `attend_30currentpositions` cp3 WHERE cp3.IDNo=pf.IDNo),(SELECT OpsSpecialist FROM attend_30currentpositions cp4 JOIN attend_1branchgroups bg ON cp4.BranchNo=bg.BranchNo WHERE cp4.IDNo=pf.IDNo))),
DeptHeadIDNo=(SELECT cp2.IDNo FROM `attend_30currentpositions` cp2 WHERE cp2.PositionID=(SELECT cp2.PositionID FROM `attend_30currentpositions` cp2 
WHERE cp2.PositionID=(SELECT IF((cp1.deptheadpositionid=cp1.PositionID),cp1.supervisorpositionid,cp1.deptheadpositionid) FROM `attend_30currentpositions` cp1 WHERE cp1.IDNo=pf.IDNo))) WHERE DATE(`HRTimestamp`)=CURDATE();';
	
      $stmt=$link->prepare($sql); $stmt->execute();
      // echo $sql; exit();
	  
	  $sql='CREATE TEMPORARY TABLE tempEval AS SELECT pf.IDNo,(SELECT PositionID FROM attend_30currentpositions cp WHERE cp.IDNo=pf.IDNo) AS ToEvaluate, (SELECT PositionID FROM attend_30currentpositions cp WHERE cp.IDNo=pf.SupervisorIDNo) AS Evaluator FROM hr_2perfevalmain pf WHERE DATE(`HRTimestamp`)=CURDATE();';
      $stmt=$link->prepare($sql); $stmt->execute();
	  
	  
	   $sql='INSERT INTO `hr_2perfevalsub` (`TxnID`,`PSID`)
SELECT pf.`TxnID`, ps.`PSID` FROM `hr_2perfevalmain` pf JOIN `hr_1evaluatorpercentage` ep ON ep.ToEvaluate=pf.CurrentPositionID
JOIN `hr_1positionstatement` ps ON ep.EPID=ps.EPID JOIN tempEval te ON pf.IDNo=te.IDNo WHERE ep.ToEvaluate=te.ToEvaluate AND ep.Evaluator=te.Evaluator AND DATE(HRTimestamp)=CURDATE() AND ep.EvalMonth=ROUND(pf.EvalAfterDays/30);'; //echo $sql;
	   
      $stmt=$link->prepare($sql); $stmt->execute();
      
      header("Location:".$_SERVER['HTTP_REFERER']);
      break; */
	  
	  
   case 'List':
       if (!allowedToOpen(683,'1rtc')) {   echo 'No permission'; exit;}           
           $condition=''; $editprocess='perfeval.php?w=EditSpecifics&TxnID='; $editprocesslabel='Edit'; 
           // $delprocess='perfeval.php?w=Delete&TxnID=';
           $addlprocess='perfeval.php?w=SetasComplete&TxnID='; $addlprocesslabel='Completed';
      ?>
      <!--<form method="post" action="perfeval.php?w=AutoEncodeEvalDue" enctype="multipart/form-data">
		Auto Encode Evaluations Due within the Month (1 - 12):  <input type="number" name="Month" value="<?php //echo date('m'); ?>" min="1" max="12"></input>
		<input type="submit" name="autoencode" value="Encode"> </form>-->
		
        <form method="post" action="perfeval.php?w=AutoEncodeYrEval" enctype="multipart/form-data">
		<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>"></input>
		<input type="submit" name="autoencodeyrend" value="Encode Year End Evaluations"> </form>
		<?php if (allowedToOpen(683,'1rtc')) { 
		?>
		<br><a href="smsperfeval.php"><b>Send SMS for Pending Evaluations</b></a>
		
		<?php
		}
       echo '<datalist id="evalmonth"><option value="3">3rd Month Statements</option><option value="5">5th Month Statements</option><option value="12">12th Month Statements</option><option value="1">Annual Evaluation</option></datalist>';
         $title='Unfinished Performance Evaluations'; $method='POST';
         $columnnames=array(
                    array('field'=>'FullName','caption'=>'Add Special Evaluation for ','type'=>'text','size'=>20,'required'=>true, 'list'=>'employees'),
                    array('field'=>'EvalDueDate','caption'=>'Evaluation Date','type'=>'date','size'=>6,'required'=>true),
                    array('field'=>'EvalMonth','caption'=>'Choose Statements','type'=>'text','size'=>6,'required'=>true,'list'=>'evalmonth')
		     );
      $action='perfeval.php?w=Add';
      $liststoshow=array(); $fieldsinrow=4;
     include('../backendphp/layout/inputmainform.php');
      $formdesc='<a href="perfeval.php?w=DeletePage">Click here to delete performance evaluation.</a>';
      $title=''; $columnnames=array_diff($columnnameslist,array('Company','SelfRemarks','CurrentPosition','HREncodedBy','HR_Status','HRStatusTS'));;
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'EvalDueDate,FullName'); $columnsub=$columnnameslist;
        $sql=$sql.' WHERE pf.HRStatus<>1 AND e1.Resigned<>1 '.$condition.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC'); 
		// echo $sql;
        $addlprocess2='perfevalentryform.php?TxnID='; $addlprocesslabel2='Lookup';
        $txnid='TxnID';
      include('../backendphp/layout/displayastable.php'); 
    
        break;
    case 'Add':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		
        $sql='INSERT INTO `hr_2perfevalmain` (`IDNo`,`CurrentPositionID`,`CurrentBranchNo`,`EvalDueDate`, `EvalAfterDays`,`HRRemarks`,`HREncodedByNo`,`HRTimeStamp`)
	    SELECT '.$idno.', `PositionID`, `BranchNo`, \''.$_POST['EvalDueDate'].'\',  DateDiff(\''.$_POST['EvalDueDate'].'\',DateHired),"'.(isset($_POST['Purpose'])?"Purpose: ".$_POST['Purpose']:"").'", '.$_SESSION['(ak0)'].', Now() FROM `attend_30currentpositions`  cp JOIN 1employees e ON e.IDNo=cp.IDNo WHERE cp.IDNo='.$idno;
		// echo $sql; exit();
        $stmt=$link->prepare($sql); $stmt->execute();
        
        $sql0='SELECT TxnID, CurrentPositionID FROM `hr_2perfevalmain` WHERE IDNo='.$idno.' AND EvalDueDate=\''.$_POST['EvalDueDate'].'\';';
        $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
        
      $sql='UPDATE hr_2perfevalmain pf JOIN `attend_30currentpositions` cp ON pf.IDNo=cp.IDNo SET 
SupervisorIDNo=(IF((cp.deptid<>10),(SELECT cp3.LatestSupervisorIDNo FROM `attend_30currentpositions` cp3 WHERE cp3.IDNo=pf.IDNo),(SELECT OpsSpecialist FROM attend_30currentpositions cp4 JOIN attend_1branchgroups bg ON cp4.BranchNo=bg.BranchNo WHERE cp4.IDNo=pf.IDNo))),
DeptHeadIDNo=(SELECT cp2.IDNo FROM `attend_30currentpositions` cp2 WHERE cp2.PositionID=(SELECT cp2.PositionID FROM `attend_30currentpositions` cp2 
WHERE cp2.PositionID=(SELECT IF((cp1.deptheadpositionid=cp1.PositionID),cp1.supervisorpositionid,cp1.deptheadpositionid) FROM `attend_30currentpositions` cp1 WHERE cp1.IDNo=pf.IDNo))) WHERE pf.TxnID='.$res0['TxnID'];

      $stmt=$link->prepare($sql); $stmt->execute();
      
	  $sql='CREATE TEMPORARY TABLE tempEval AS SELECT pf.IDNo,(SELECT PositionID FROM attend_30currentpositions cp WHERE cp.IDNo=pf.IDNo) AS ToEvaluate, (SELECT PositionID FROM attend_30currentpositions cp WHERE cp.IDNo=pf.SupervisorIDNo) AS Evaluator FROM hr_2perfevalmain pf WHERE pf.TxnID='.$res0['TxnID'].';';
      $stmt=$link->prepare($sql); $stmt->execute();
	  /* 
      $sql='INSERT INTO `hr_2perfevalsub` (`TxnID`,`PSID`)
SELECT '.$res0['TxnID'].', ps.`PSID` FROM `hr_2perfevalmain` pf JOIN `hr_1evaluatorpercentage` ep ON ep.ToEvaluate=pf.CurrentPositionID
JOIN `hr_1positionstatement` ps ON ep.EPID=ps.EPID JOIN `1employees` e ON pf.IDNo=e.IDNo JOIN tempEval te ON pf.IDNo=te.IDNo WHERE ep.ToEvaluate=te.ToEvaluate AND ep.Evaluator=te.Evaluator AND DATE(HRTimestamp)=CURDATE() AND ep.EvalMonth='.$_POST['EvalMonth'].' AND pf.TxnID='.$res0['TxnID'].';'; //echo $sql; break; */
      $sql='INSERT INTO `hr_2perfevalsub` (`TxnID`,`PSID`)
SELECT '.$res0['TxnID'].', ps.`PSID` FROM `hr_2perfevalmain` pf JOIN `hr_1evaluatorpercentage` ep ON ep.ToEvaluate=pf.CurrentPositionID
JOIN `hr_1positionstatement` ps ON ep.EPID=ps.EPID JOIN `1employees` e ON pf.IDNo=e.IDNo JOIN tempEval te ON pf.IDNo=te.IDNo WHERE ep.ToEvaluate=te.ToEvaluate AND ep.Evaluator=te.Evaluator AND DATE(HRTimestamp)=CURDATE() AND ep.EvalMonth='.(isset($_GET['FromMancom'])?(isset($_POST['EvalMonth'])?$_POST['EvalMonth']:1):$_POST['EvalMonth']).' AND pf.TxnID='.$res0['TxnID'].';'; //echo $sql; break;

      $stmt=$link->prepare($sql); $stmt->execute();
      
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
		
		
    case 'Delete':
        if (!allowedToOpen(683,'1rtc')) {   echo 'No permission'; exit;}
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	   
	   
	    $sqlcheck='SELECT TxnID FROM `hr_2perfevalmain` WHERE IDNo='.$_POST['IDNo'].' AND EvalDueDate="'.$_POST['EvalDueDate'].'" AND SelfCompleted=0';
		$stmtcheck=$link->query($sqlcheck); $rescheck=$stmtcheck->fetch();
		if($stmtcheck->rowCount()>0){
			
			$sql='DELETE FROM `hr_2perfevalsub` WHERE TxnID='.$rescheck['TxnID'].'';
			$stmt=$link->prepare($sql); $stmt->execute();
			
			$sql='DELETE FROM `hr_2perfevalmain` WHERE TxnID='.$rescheck['TxnID'].'';
			$stmt=$link->prepare($sql); $stmt->execute();
			
		} else {
			echo 'No Records'; exit();
		}
		echo '<h4 style="color:green;">Deleted Successfully.</h4>';
        break;
   case 'EditSpecifics':
       if (!allowedToOpen(683,'1rtc')) {   echo 'No permission'; exit;}
         $title='Edit Specifics';
	 $txnid=intval($_GET['TxnID']); $main='hr_2perfevalmain';
		 $columnstoedit=array('Supervisor','HRRemarks','EvalAfterDays','EvalDueDate');
	 $sql=$sql.'WHERE pf.HRStatus<>1 AND TxnID='.$txnid;
	 $columnnames=$columnnameslist;
	 $columnswithlists=array('Supervisor');$listsname=array('Supervisor'=>'employees');
	 $editprocess='perfeval.php?w=Edit&TxnID='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
         break;
    case 'Edit':
        if (!allowedToOpen(683,'1rtc')) {   echo 'No permission'; exit;}
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $superidno=comboBoxValue($link,'`1employees`','CONCAT(Nickname, " ", Surname)',addslashes($_POST['Supervisor']),'IDNo');
        $sql=''; 
        
        
		$columnstoedit=array('HRRemarks','EvalAfterDays','EvalDueDate');
        foreach ($columnstoedit as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; $supercond='';}
               
        $sql='UPDATE `hr_2perfevalmain` SET HREncodedByNo='.$_SESSION['(ak0)'].','.$sql.'SupervisorIDNo='.$superidno.', HRTimeStamp=Now() WHERE HRStatus<>1 '.$supercond.' AND TxnID='.$_GET['TxnID'];
		
        $stmt=$link->prepare($sql); $stmt->execute();
		
        header("Location:perfeval.php");
        break;
   case 'SetasComplete':
       if (!allowedToOpen(683,'1rtc')) {   echo 'No permission'; exit;}
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql0='SELECT HRStatus FROM `hr_2perfevalmain` WHERE TxnID='.$_GET['TxnID']; $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
        $sql='UPDATE `hr_2perfevalmain` SET HRStatus='.($res0['HRStatus']==0?1:0).', HRStatusTS=Now(), HREncodedByNo='.$_SESSION['(ak0)'].' WHERE EmpResponse<>0 AND TxnID='.$_GET['TxnID']; 
		// echo $sql; break;
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
   case 'History':
       if (!allowedToOpen(685,'1rtc') and !allowedToOpen(100,'1rtc')) {   echo 'No permission'; exit;}
       
      $title='Evaluation History';
      ?>
      <form method="post" action="perfeval.php?w=History" enctype="multipart/form-data">
		Name:  <input type="text" name="FullName" list='employees'></input>
		<input type="submit" name="lookup" value="Lookup"><br>
      <?php
      if (isset($_POST['FullName'])){
	 $title=$title.' - '.$_POST['FullName'];
	 $idno=comboBoxValue($link,'`1employees`','CONCAT(Nickname, " ", Surname)',addslashes($_POST['FullName']),'IDNo');
	 $columnnames=array_diff($columnnameslist,array('IDNo','FullName','DateHired','Company')); $columnnames[]='HR_Status';
         if(allowedToOpen(683,'1rtc')) { $cond='';}
         elseif (allowedToOpen(100,'1rtc')) { $cond=' AND pf.DeptHeadIDNo='.$_SESSION['(ak0)'];}
         else { $cond=' AND pf.SupervisorIDNo='.$_SESSION['(ak0)'];}
	 $sql=$sql.'WHERE pf.IDNo='.$idno.$cond.' ORDER BY EvalDueDate DESC'; 
         $addlprocess='perfevalentryform.php?TxnID='; $addlprocesslabel='Lookup';
         if (allowedToOpen(6871,'1rtc')) {$addlprocess2='perfevalentryform.php?print=1&TxnID='; $addlprocesslabel2='Print_Preview?';}
	 include('../backendphp/layout/displayastablenosort.php'); 
      }
	  // echo $sql;
      break;
      
    case 'AutoEncodeYrEval':
       if (!allowedToOpen(6831,'1rtc')) {   echo 'No permission'; exit;}
	   
	   if(date('m')<11){
		echo 'Month No should be greater than or equal to 11.'; exit();
	}
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
      $sql='INSERT INTO `hr_2perfevalmain` 
          (`IDNo`,`CurrentPositionID`,`CurrentBranchNo`,`SupervisorIDNo`,`DeptHeadIDNo`,`EvalDueDate`, `EvalAfterDays`,`HREncodedByNo`,`HRTimestamp`)  
          SELECT e.`IDNo`, `PositionID`, `DefaultBranchAssignNo`, (IF((p.deptid<>10),(SELECT cp3.LatestSupervisorIDNo FROM `attend_30currentpositions` cp3 WHERE cp3.IDNo=p.IDNo),(SELECT OpsSpecialist FROM attend_30currentpositions cp4 JOIN attend_1branchgroups bg ON cp4.BranchNo=bg.BranchNo WHERE cp4.IDNo=p.IDNo))) AS SupervisorIDNo, 
          (SELECT IF(a.IDNo=e.IDNo,a.LatestSupervisorIDNo,a.IDNo) FROM `attend_30currentpositions` a 
          WHERE a.positionid=(SELECT `deptheadpositionid` FROM `attend_30currentpositions` b WHERE b.IDNo=e.IDNo)) AS `DeptHeadIDNo`,
          \''.$currentyr.'-11-27\' AS `EvalDueDate`, (SELECT YEAR(`DataClosedBy`) FROM `00dataclosedby` WHERE `ForDB`=1) AS `EvalAfterDays`, 
          '.$_SESSION['(ak0)'].' AS `EncodedByNo`, Now() AS `Timestamp`
	    FROM `1employees` e JOIN `attend_30currentpositions` p ON e.IDNo=p.IDNo
	    JOIN `attend_1defaultbranchassign` dba ON e.IDNo=dba.IDNo WHERE e.Resigned=0 AND e.IDNo>1002;';
		// echo $sql; exit();
       $stmt=$link->prepare($sql); $stmt->execute();
      
   
      
      $sql='INSERT INTO `hr_2perfevalsub` (`TxnID`,`PSID`)
	SELECT pf.`TxnID`, ps.`PSID` FROM `hr_2perfevalmain` pf JOIN `hr_1evaluatorpercentage` ep ON ep.ToEvaluate=pf.CurrentPositionID AND  ep.EvalMonth=1 AND pf.EvalAfterDays='.$currentyr.'
	JOIN `hr_1positionstatement` ps ON ep.EPID=ps.EPID ;';
	
	// echo $sql; exit();
      $stmt=$link->prepare($sql); $stmt->execute();
      
/*	 
      // Additional evaluators if Team Leader / Ops
		$sql='SELECT pf.`IDNo`,CurrentPositionID FROM hr_2perfevalmain pf WHERE CurrentPositionID IN (36,71) ORDER BY CurrentPositionID;';
		$stmt = $link->query($sql);
	
		while($row = $stmt->fetch()) {
			// $sqlstl='SELECT BranchNo FROM attend_1branchgroups WHERE '.($row['CurrentPositionID']==36?'TeamLeader=':'OpsSpecialist=').''.$row['IDNo'].';';
			$sqlstl='SELECT BranchNo FROM attend_1branchgroups WHERE '.($row['CurrentPositionID']==36?'TeamLeader=':'OpsSpecialist=').''.$row['IDNo'].';';
			$stmtstl = $link->query($sqlstl); //branch handle
				
			while($rowstl = $stmtstl->fetch()) {
				$sqlhp='SELECT IDNo,PositionID FROM attend_30currentpositions WHERE BranchNo='.$rowstl['BranchNo'].' ORDER BY Rank DESC LIMIT 1;';
				$stmthp = $link->query($sqlhp); //branch highest pos
				
				while ($rowhp = $stmthp->fetch()) { //insert with statements
					$sqlinsertsub='INSERT INTO `hr_2perfevalsub` (`TxnID`,`PSID`)
					SELECT pf.`TxnID`, ps.`PSID`,'.$rowhp['IDNo'].'  FROM `hr_2perfevalmain` pf JOIN `hr_1evaluatorpercentage` ep ON ep.ToEvaluate=pf.CurrentPositionID
					JOIN `hr_1positionstatement` ps ON ep.EPID=ps.EPID WHERE pf.CurrentPositionID='.$row['CurrentPositionID'].' AND Evaluator='.$rowhp['PositionID'].' AND IDNo='.$row['IDNo'].' AND ep.EvalMonth=1;';
					$stmtinsertsub=$link->prepare($sqlinsertsub); $stmtinsertsub->execute();
				}
			}
		}
		*/
      header("Location:".$_SERVER['HTTP_REFERER']);
      break;
      
      case 'ForEval':
          if (!allowedToOpen(array(684,6845),'1rtc')) {   echo 'No permission'; exit;}
	    $title='Pending for Evaluation per Dept';
	  ?><div style="margin: 15px; background: #FFFFFF;padding:5px;">
<h4><br>Complete Evaluation Process</h4><ol style="margin: 20px; ">
            <li><b>Self-evaluation</b> - must end with "Set as Completed"</li>
            <li><b>Supervisor evaluation</b> - evaluation is available to supervisor only if employee has finished. Supervisor must also set as completed.</li>
<!--            <li><b>Other Departments</b> - some positions are evaluated by other departments. This can be done anytime before the employee acknowledges.</li>-->
            <li><b>Dept Head confirmation</b> - department head comments and confirms evaluation.</li>
            <li><b>Employee acknowledgement and commitment</b> - The employee must set as AGREE or DISAGREE to the evaluation of the supervisor. He/she is encouraged to write his/her commitment in reaction to the supervisor's evaluation.</li>
            <li><b>HR status</b> - HR must print, file into 201, and set status online evaluation as FINISHED. Both HR and respective department heads must sign on the print out so they are aware of improvement plans.</li>
			
</ol><hr><?php
if (allowedToOpen(1614,'1rtc')){
$method='POST';
echo '<title>'.$title.'</title>';
echo '<br><h3>Add Special Evaluation</h3>';

// $sqlcheck='select deptid,dept from attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].' OR LatestSupervisorIDNo='.$_SESSION['(ak0)'].' GROUP BY deptid ORDER BY dept;';

 // echo comboBox($link,'SELECT e.IDNo, CONCAT(Nickname, " ", Surname) AS FullName FROM `1employees` e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo WHERE deptid IN (select deptid from attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].' OR LatestSupervisorIDNo='.$_SESSION['(ak0)'].' OR deptheadpositionid='.$_SESSION['&pos'].') ORDER BY Nickname;','IDNo','FullName','employees');
 echo comboBox($link,'SELECT e.IDNo, CONCAT(Nickname, " ", Surname) AS FullName FROM `1employees` e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo WHERE deptheadpositionid='.$_SESSION['&pos'].' ORDER BY Nickname;','IDNo','FullName','employees');

echo '<datalist id="evalmonth"><option value="3">3rd Month</option><option value="5">5th Month</option><option value="12">12th Month</option><option value="1">Annual Evaluation</option></datalist>';

echo '<form action="perfeval.php?w=Add&FromMancom=1&action_token='.$_SESSION['action_token'].'" method="POST">Add Special Evaluation for <input type="text" value="" name="FullName" list="employees" required> Evaluation Date: <input type="date" value="" name="EvalDueDate" value="" required> Purpose (promotion, lateral promotion, etc.) <input type="text" value="" name="Purpose" value="" autocomplete="off" required> EvalMonth: <input type="text" size="5" name="EvalMonth" autocomplete="off" list="evalmonth" required> <input type="submit" name="btnAdd" value="Add new"></form>';
	 
}
?></div>
<?php
		if (allowedToOpen(100,'1rtc')) 
		{
			$condi = ' WHERE (DeptHeadIDNo='.$_SESSION['(ak0)'].' OR SupervisorIDNo='.$_SESSION['(ak0)'].')';
         
		}
		else {
			$condi = ' WHERE ((((SupervisorIDNo='.$_SESSION['(ak0)'].'))))';
		}
		if(allowedToOpen(683,'1rtc')){
			$addlprocess='perfeval.php?w=EditSpecifics&TxnID='; $addlprocesslabel='Edit';
		}
			  $sql0 = $sql . $condi. ' AND e1.Resigned=0 '; 
		  
         $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','Supervisor','Emp_Response','HR_Status');
         $editprocess='perfevalentryform.php?TxnID='; $editprocesslabel='Lookup';
         $subtitle='Unfinished SELF-Evaluation';
         $sql=$sql0.' AND SelfCompleted=0';
         $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','Supervisor');
	 include('../backendphp/layout/displayastablenosort.php'); 
         $subtitle='<br/>Unfinished SUPERVISOR Evaluation';
         $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','Supervisor');
         if (allowedToOpen(100,'1rtc')){ unset($addlprocess,$addlprocesslabel);}
         $sql=$sql0.' AND SelfCompleted=1 AND SuperCompleted=0';
	 include('../backendphp/layout/displayastableonlynoheaders.php');
      /*   $subtitle='<br/>Unfinished OTHER Departments';
         $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','SelfEval','SelfRemarks','Supervisor','SupervisorEval','SuperRemarks');
         $sql=$sql0.' AND TxnID IN (SELECT TxnID FROM hr_2perfevalsub WHERE SelfScore=0 OR ISNULL(SelfScore) GROUP BY TxnID)';
	 include('../backendphp/layout/displayastableonlynoheaders.php');*/
         $subtitle='<br/>Unfinished DEPT HEAD Confirmation';
         $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','Supervisor');
         $sql=$sql0.' AND SelfCompleted=1 AND SuperCompleted=1 AND DeptHeadConfirm=0';
	 include('../backendphp/layout/displayastableonlynoheaders.php');
         $subtitle='<br/>No EMPLOYEE Acknowledgment';
         $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','Supervisor');
         $sql=$sql0.' AND SelfCompleted=1 AND SuperCompleted=1 AND DeptHeadConfirm=1 AND EmpResponse=0';
	 include('../backendphp/layout/displayastableonlynoheaders.php');
         $subtitle='<br/>HR to Finalize';
         $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','Supervisor','Emp_Response','HR_Status');
         $sql=$sql0.' AND SelfCompleted=1 AND SuperCompleted=1 AND DeptHeadConfirm=1 AND EmpResponse<>0 AND HRStatus=0';
	 include('../backendphp/layout/displayastableonlynoheaders.php');
         
      break;
      
      
	  
  case 'MyEval':
      $title='My Evaluation History';
      $sql.=' WHERE pf.IDNo='.$_SESSION['(ak0)'];	
         $addlprocess='perfevalentryform.php?TxnID='; $addlprocesslabel='Lookup';
	 include('../backendphp/layout/displayastablenosort.php'); 
      
      break;
  
    case 'SelfEval':
	
      $title='For Evaluation';       
	 $sql=$sql.' WHERE ((pf.EmpResponse=0  AND pf.IDNo='.$_SESSION['(ak0)'].')) ';
	 // echo $sql;
         $columnnames=array('IDNo','FullName','EvalAfterDays','EvalDueDate','SelfEval','SelfRemarks','Supervisor','SupervisorEval','SuperRemarks','Emp_Response','EmpRemarks');
         $addlprocess='perfevalentryform.php?TxnID='; $addlprocesslabel='Lookup';
	 include('../backendphp/layout/displayastablenosort.php'); 
      
      break;
/*       
case 'EvaluateOthers':
	 if (!allowedToOpen(6845,'1rtc')) {   echo 'No permission'; exit;} 
	  $title='For Evaluation (Others)';       
		
		$sql='SELECT pf.*, CONCAT(e1.FirstName, " ", e1.Surname) AS FullName, Position AS CurrentPosition, b.Branch AS CurrentBranch, CONCAT(e2.Nickname, " ", e2.Surname) AS Supervisor, Evaluator
	       FROM hr_2perfevalmain pf  
	       JOIN `1employees` e1 ON e1.IDNo=pf.IDNo
	       LEFT JOIN `1employees` e2 ON e2.IDNo=pf.SupervisorIDNo
	       JOIN `1branches` b ON b.BranchNo=pf.CurrentBranchNo
	       LEFT JOIN `attend_0positions` p ON p.PositionID=pf.CurrentPositionID JOIN `hr_2perfevalsub` pes ON pf.TxnID=pes.TxnID JOIN `hr_1positionstatement` ps ON ps.PSID=pes.PSID JOIN `hr_1evaluatorpercentage` ep ON ep.EPID=ps.EPID WHERE (pes.SelfScore<1 OR pes.SelfScore is NULL) AND (ep.Evaluator='.$_SESSION['&pos'].' OR pes.EvaluatorIDNo='.$_SESSION['(ak0)'].') AND pf.SupervisorIDNo<>'.$_SESSION['(ak0)'].' GROUP BY pf.TxnID ORDER BY Position ASC, IDNo ASC';
		
        $columnnames=array('IDNo','FullName','CurrentPosition','CurrentBranch','Supervisor','EvalAfterDays','EvalDueDate');
        $addlfield='Evaluator';
        $editprocess='perfevalentryview.php?TxnID='; $editprocesslabel='Lookup';
		 
	 include('../backendphp/layout/displayastablenosort.php');
	 
      break;
     
case 'AddAnotherEvaluator':
	 if (!allowedToOpen(100,'1rtc')) {   echo 'No permission'; exit;} 
	 $txnid=intval($_REQUEST['TxnID']); 
         $anotheridno=comboBoxValue($link,'`1employees`','CONCAT(Nickname, " ", Surname)',addslashes($_POST['AnotherEvaluator']),'IDNo');
         $anotherevaluator=comboBoxValue($link,'`attend_30currentpositions`','IDNo',$anotheridno,'PositionID');
	 $sql='INSERT INTO `hr_2perfevalsub` (`TxnID`,`PSID`, EvaluatorIDNo)
             SELECT '.$txnid.', ps.`PSID`, '.$anotheridno.' AS EvaluatorIDNo FROM `hr_2perfevalmain` pe 
JOIN `hr_1evaluatorpercentage` ep ON ep.ToEvaluate=pe.CurrentPositionID  AND ep.Evaluator='.$anotherevaluator.'
JOIN `hr_1positionstatement` ps ON ep.EPID=ps.EPID WHERE pe.TxnID='.$txnid.' AND ep.EvalMonth=ROUND(pe.EvalAfterDays/30);'; 

      $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:perfevalentryview.php?Evaluator=".$anotherevaluator."&TxnID=".$txnid);
      break;
*/ 
case 'FinishedEval':
	 if (!allowedToOpen(6862,'1rtc')) {   echo 'No permission'; exit;}
	 include_once '../backendphp/layout/linkstyle.php';
	echo '<br><a id="link" href="perfevalprint.php">Print Annual Performance Evaluation</a><br>';
	
         if (allowedToOpen(6832,'1rtc')) { $cond='';} else { $cond=' AND DeptHeadIDNo='.$_SESSION['(ak0)']; }
	 $title='Finished Evaluations';
  
	 $editprocess='perfevalentryform.php?TxnID='; $editprocesslabel='Lookup';
	 
	 $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','Supervisor','SupervisorEval','Emp_Response');
	
	 $monthno=date("m"); $ytoday=$currentyr;
	 if (isset($_POST['btnShow']))
	 {
		$monthno = $_POST["monthno"];
	 }
	 $formdesc='</i><form action="#" method="POST" style="display: inline;">Month: <input type="number" name="monthno" size=3 min="1" max="12" value="'.$monthno.'"/>&nbsp;<input type="submit" value="Show per Month" name="btnShow"></form>&nbsp;&nbsp;<form action="#" method="POST" style="display: inline;"><input type="submit" value="Show All" name="btnShowAll"></form><i>';
         
         if (isset($_POST['btnShowAll'])) { 
             $sql=$sql.' WHERE SelfCompleted=1 AND SuperCompleted=1 AND DeptHeadConfirm=1 AND EmpResponse<>0 AND HRStatus=1 '.$cond.' '; 
	 } else {
	 
	 $sql=$sql.' WHERE SelfCompleted=1 AND SuperCompleted=1 AND DeptHeadConfirm=1 AND EmpResponse<>0 AND HRStatus=1 '.$cond.' AND (EvalDueDate>="'.$ytoday.'-'.$monthno.'-01'.'" AND EvalDueDate<=LAST_DAY("'.$ytoday.'-'.$monthno.'-01'.'"))'; 
         }
	 $width='100%';
	 include('../backendphp/layout/displayastable.php'); 
	  
break;

case 'DeletePage':

if (!allowedToOpen(683,'1rtc')) {   echo 'No permission'; exit;}           
          
		$title='Delete Performance Evaluation';
		$columnnames=array('IDNo','FullName','DateHired','CurrentBranch','EvalAfterDays','EvalDueDate','Supervisor');
		
		echo comboBox($link,$sql.' WHERE pf.SelfCompleted=0 AND e1.Resigned<>1 ORDER BY FullName;','FullName','IDNo','employeeswithsuperzero');
		echo '<title>'.$title.'</title>';
		echo '<h3>'.$title.'</h3><br>';
		echo '<form action="perfeval.php?w=Delete" method="POST">';
		echo '<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">';
		echo 'IDNo: <input type="text" name="IDNo" list="employeeswithsuperzero" autocomplete="off" required>';
		echo ' EvalDueDate: <input type="date" name="EvalDueDate" value="'.date('Y-m-d').'">';
		echo ' <input type="submit" value="Delete Evaluation" name="btnDelete" OnClick="return confirm(\'Are you sure you want to delete this application?\');">';
		echo '</form>';
		 
	  
break;


case 'EvaluatorGuide':

if (!allowedToOpen(array(684,6845),'1rtc')) {   echo 'No permission'; exit;}

?>
        <title>Evaluator Guide</title>
        <div style=' background-color: FFFFFF;'>            
        <div style='margin-left: 30%;'><br><br>
	<h3>Evaluator Guide for One-on-One</h3><br><br>
        <h3 style='color: darkgreen;'>Every performance evaluation is an opportunity to help our people improve. Let us maximize this.</h3><br><br>
        <i>You may use these statements as starting points in discussing your evaluatee's performance for the year:</i><br><br>
        <div style='margin-left: 5%; font-size: large; line-height: 200%;'>
        <ol>
            <li>In general, you did well/not well because ...</li>
            <li>Specific examples this year that showed your performance are ...</li>
            <li>Your biggest achievement this year was ...</li>
            <li>For next year, I challenge you ...</li>
        </ol>
            <br><br>
        </div></div></div>
<?php		 
	  
break;
  
}
 
?>
</body></html>